<?php

namespace App\Modules\Documents\Models;

use App\Modules\KnowledgeBases\Models\KnowledgeBase;
use App\Modules\Users\Models\User;
use Database\Factories\Modules\Documents\DocumentFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    /** @use HasFactory<DocumentFactory> */
    use HasFactory, HasUuids;

    protected static function newFactory(): DocumentFactory
    {
        return DocumentFactory::new();
    }

    protected $fillable = [
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
        'indexed_at',
        'error_message',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'indexed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function knowledgeBase(): BelongsTo
    {
        return $this->belongsTo(KnowledgeBase::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
