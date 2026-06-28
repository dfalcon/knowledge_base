<?php

namespace App\Modules\KnowledgeBases\Models;

use App\Modules\Users\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeBasePermission extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'knowledge_base_id',
        'user_id',
        'can_read',
        'can_write',
        'granted_by',
    ];

    protected $casts = [
        'can_read'   => 'boolean',
        'can_write'  => 'boolean',
        'granted_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function knowledgeBase(): BelongsTo
    {
        return $this->belongsTo(KnowledgeBase::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }
}
