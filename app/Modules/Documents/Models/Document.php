<?php

namespace App\Modules\Documents\Models;

use App\Modules\Documents\Enums\DocumentStatus;
use App\Modules\KnowledgeBases\Models\KnowledgeBase;
use App\Modules\Users\Models\User;
use Database\Factories\Modules\Documents\DocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[UseFactory(DocumentFactory::class)]
#[Fillable([
    'knowledge_base_id',
    'uploaded_by',
    'title',
    'content',
    'file_name',
    'file_path',
    'mime_type',
    'file_size_bytes',
    'status',
    'metadata',
    'language',
    'indexed_at',
    'error_message',
])]
#[Hidden(['search_vector'])]
class Document extends Model
{
    /** @use HasFactory<DocumentFactory> */
    use HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'metadata'   => 'array',
            'indexed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'status'     => DocumentStatus::class,
        ];
    }

    public function knowledgeBase(): BelongsTo
    {
        return $this->belongsTo(KnowledgeBase::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'document_tags');
    }
}
