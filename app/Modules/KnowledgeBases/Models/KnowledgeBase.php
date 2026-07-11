<?php

namespace App\Modules\KnowledgeBases\Models;

use App\Modules\Documents\Models\Tag;
use App\Modules\Users\Models\User;
use Database\Factories\Modules\KnowledgeBases\KnowledgeBaseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

#[UseFactory(KnowledgeBaseFactory::class)]
#[Fillable(['name', 'slug', 'is_public', 'owner_id'])]
class KnowledgeBase extends Model
{
    /** @use HasFactory<KnowledgeBaseFactory> */
    use HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::deleted(function (KnowledgeBase $knowledgeBase): void {
            Cache::tags(["kb:{$knowledgeBase->id}"])->flush();
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(KnowledgeBasePermission::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }
}
