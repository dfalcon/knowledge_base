<?php

namespace App\Modules\Documents\Actions;

use App\Modules\Documents\Enums\DocumentStatus;
use App\Modules\Documents\Jobs\PublishDocumentUploadedJob;
use App\Modules\Documents\Models\Document;
use App\Modules\KnowledgeBases\Models\KnowledgeBase;
use App\Modules\Users\Models\User;
use Illuminate\Http\UploadedFile;

class UploadDocumentAction
{
    public function execute(KnowledgeBase $knowledgeBase, User $uploader, UploadedFile $file, ?string $title = null): Document
    {
        $path = $file->store("documents/{$knowledgeBase->id}", 's3');

        $document = Document::create([
            'knowledge_base_id' => $knowledgeBase->id,
            'uploaded_by'       => $uploader->id,
            'title'             => $title ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'file_name'         => $file->getClientOriginalName(),
            'file_path'         => $path,
            'mime_type'         => $file->getClientMimeType(),
            'file_size_bytes'   => $file->getSize(),
            'status'            => DocumentStatus::Pending,
        ]);
        PublishDocumentUploadedJob::dispatch($document)->onQueue('documents');
        return $document;
    }
}
