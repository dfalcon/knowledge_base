<?php

namespace App\Modules\Documents\Jobs;

use App\Modules\Documents\Enums\DocumentStatus;
use App\Modules\Documents\Events\DocumentIndexedEvent;
use App\Modules\Documents\Mail\DocumentIndexed;
use App\Modules\Documents\Models\Document;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use JsonException;
use Throwable;
use TypeError;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Jobs\RabbitMQJob;

/**
 * consume rabbitmq from artisan queue:work -queue="queue_name"
 */
class NotifyDocumentIndexedJob extends RabbitMQJob
{
    private const EVENT = 'document.indexed';

    private const TRIES = 3;

    private const BACKOFF = [5, 30, 300];

    public function fire(): void
    {
        try {
            $event = DocumentIndexedEvent::fromJson($this->getRawBody());
        } catch (JsonException|TypeError $e) {
            Log::warning('NotifyDocumentIndexedJob: malformed payload', [
                'body'  => $this->getRawBody(),
                'error' => $e->getMessage(),
            ]);
            $this->delete();

            return;
        }

        if ($event->version !== '1.0') {
            Log::warning('NotifyDocumentIndexedJob: unexpected event version', ['version' => $event->version]);
        }

        // Insert-first, not check-then-insert: the unique constraint on message_id makes
        // "already processed?" atomic across concurrent workers, no separate SELECT needed.
        $isFirstDelivery = DB::table('processed_message_ids')->insertOrIgnore([
            'message_id'   => $event->messageId,
            'event'        => self::EVENT,
            'processed_at' => now(),
        ]) > 0;

        if (! $isFirstDelivery) {
            Log::info('NotifyDocumentIndexedJob: duplicate delivery, skipping', ['message_id' => $event->messageId]);
            $this->delete();

            return;
        }

        $document = Document::find($event->documentId);

        if (! $document) {
            Log::warning('NotifyDocumentIndexedJob: document not found', ['document_id' => $event->documentId]);
            $this->delete();

            return;
        }

        try {
            $document->update([
                'status'     => DocumentStatus::Indexed,
                'indexed_at' => now(),
            ]);

            if ($document->uploadedBy) {
                Mail::to($document->uploadedBy->email)->send(new DocumentIndexed($document));
            }

            $this->delete();
        } catch (Throwable $e) {
            // Processing didn't actually complete — undo the dedup mark so the retry below isn't
            // mistaken for a duplicate delivery of an already-finished event.
            DB::table('processed_message_ids')->where('message_id', $event->messageId)->delete();

            Log::error('NotifyDocumentIndexedJob failed', [
                'document_id' => $document->id,
                'error'       => $e->getMessage(),
            ]);

            if ($this->attempts() < self::TRIES) {
                $this->release(self::BACKOFF[$this->attempts() - 1]);
            } else {
                $this->fail($e);
            }
        }
    }

    public function getName(): string
    {
        return self::EVENT;
    }
}
