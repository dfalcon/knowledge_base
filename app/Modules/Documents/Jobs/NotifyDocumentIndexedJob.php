<?php

namespace App\Modules\Documents\Jobs;

use App\Modules\Documents\Enums\DocumentStatus;
use App\Modules\Documents\Mail\DocumentIndexed;
use App\Modules\Documents\Models\Document;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Jobs\RabbitMQJob;

/**
 * Consumer события `document.indexed` из RabbitMQ (routing key `document.indexed`,
 * очередь `laravel.notifications`). Публикуется не Laravel'ом (Python AI-сервисом,
 * либо вручную через Management UI), поэтому payload — сырой JSON, а не
 * Laravel-конверт джобы. Отсюда переопределение fire() вместо обычного handle().
 */
class NotifyDocumentIndexedJob extends RabbitMQJob
{
    private const TRIES = 3;

    private const BACKOFF = [5, 30, 60];

    public function fire(): void
    {
        $payload = json_decode($this->getRawBody(), true);

        $document = Document::find($payload['document_id'] ?? null);

        if (! $document) {
            Log::warning('NotifyDocumentIndexedJob: document not found', ['payload' => $payload]);
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
        return 'document.indexed';
    }
}
