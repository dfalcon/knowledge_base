<?php

namespace App\Modules\Documents\Jobs;

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
        // routing key 'document.uploaded' — уже забинжен на intellibase.events
        // → очередь ai.document-processing (ADR-0002), топология настроена вручную в День 2
        QueueFacade::connection('rabbitmq')->pushRaw(
            json_encode([
                'document_id'       => $this->document->id,
                'knowledge_base_id' => $this->document->knowledge_base_id,
                'file_path'         => $this->document->file_path,
                'mime_type'         => $this->document->mime_type,
                'uploaded_at'       => now()->toIso8601String(),
            ]),
            'document.uploaded',
        );

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
