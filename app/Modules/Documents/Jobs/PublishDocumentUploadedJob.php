<?php

namespace App\Modules\Documents\Jobs;

use App\Modules\Documents\Events\DocumentUploadedEvent;
use App\Modules\Documents\Models\Document;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\WithoutRelations;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue as QueueFacade;
use Throwable;

class PublishDocumentUploadedJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [5, 30, 60];

    public function __construct(
        #[WithoutRelations]
        public Document $document
    ) {}

    public function handle(): void
    {
        $event = DocumentUploadedEvent::fromDocument($this->document);

        // 'document.uploaded' here is the routing key, not a queue name
        QueueFacade::connection('rabbitmq')->pushRaw($event->toJson(), 'document.uploaded');

        Log::info('Document uploaded event published to RabbitMQ', ['document_id' => $this->document->id]);
    }

    public function failed(?Throwable $e): void
    {
        Log::error('PublishDocumentUploadedJob failed', [
            'document_id' => $this->document->id,
            'error'       => $e?->getMessage(),
        ]);
    }
}
