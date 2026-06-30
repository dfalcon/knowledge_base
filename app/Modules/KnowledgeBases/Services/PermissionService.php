<?php

namespace App\Modules\KnowledgeBases\Services;

use App\Modules\KnowledgeBases\Models\KnowledgeBase;
use App\Modules\Users\Models\User;

class PermissionService
{
    public function canRead(User $user, KnowledgeBase $knowledgeBase): bool
    {
        if ($knowledgeBase->owner_id === $user->id || $knowledgeBase->is_public || $user->hasRole('admin')) {
            return true;
        }

        return $knowledgeBase->permissions()->where('user_id', $user->id)->where('can_read', true)->exists();
    }

    public function canWrite(User $user, KnowledgeBase $knowledgeBase): bool
    {
        if ($knowledgeBase->owner_id === $user->id || $user->hasRole('admin')) {
            return true;
        }

        return $knowledgeBase->permissions()->where('user_id', $user->id)->where('can_write', true)->exists();
    }
}
