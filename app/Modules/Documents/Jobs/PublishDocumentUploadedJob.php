<?php

namespace App\Modules\Documents\Jobs;

use App\Modules\Documents\Models\Document;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\WithoutRelations;
use Illuminate\Support\Facades\Log;

class PublishDocumentUploadedJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        #[WithoutRelations]
        public Document $document
    ) {}

    public function handle(): void
    {
        //it shopuld be feature interact with fast api python service
        Log::info('Document uploaded, pending publish', ['document_id' => $this->document->id]);
    }
}
