<?php

namespace App\Modules\KnowledgeBases\Actions;

use App\Modules\KnowledgeBases\Models\KnowledgeBase;
use App\Modules\Users\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class ListKnowledgeBasesAction
{
    public function execute(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return KnowledgeBase::query()
            ->when(! $user->hasRole('admin'), fn ($q) => $q
                ->where('owner_id', $user->id)
                ->orWhere('is_public', true)
                ->orWhereHas('permissions', fn ($p) => $p->where('user_id', $user->id)->where('can_read', true))
            )
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
