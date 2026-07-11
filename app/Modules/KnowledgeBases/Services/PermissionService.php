<?php

namespace App\Modules\KnowledgeBases\Services;

use App\Modules\KnowledgeBases\Models\KnowledgeBase;
use App\Modules\Users\Models\User;
use Illuminate\Support\Facades\Cache;

class PermissionService
{
    public function canRead(User $user, KnowledgeBase $knowledgeBase): bool
    {
        if ($knowledgeBase->owner_id === $user->id || $knowledgeBase->is_public || $user->hasRole('admin')) {
            return true;
        }
        return Cache::remember("user:{$user->id}:kb-permissions", 300, fn () => $knowledgeBase->permissions()->where('user_id', $user->id)->where('can_read', true)->exists());
    }

    public function canWrite(User $user, KnowledgeBase $knowledgeBase): bool
    {
        if ($knowledgeBase->owner_id === $user->id || $user->hasRole('admin')) {
            return true;
        }

        return $knowledgeBase->permissions()->where('user_id', $user->id)->where('can_write', true)->exists();
    }
}
