<?php

namespace App\Modules\Documents\Actions;

use App\Modules\Documents\Models\Document;
use Illuminate\Pagination\LengthAwarePaginator;

class ListDocumentsAction
{
    public function execute(?string $knowledgeBaseId, ?string $status, int $perPage): LengthAwarePaginator
    {
        return Document::query()
            ->when($knowledgeBaseId !== null, fn ($q) => $q->where('knowledge_base_id', $knowledgeBaseId))
            ->when($status !== null, fn ($q) => $q->where('status', $status))
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
