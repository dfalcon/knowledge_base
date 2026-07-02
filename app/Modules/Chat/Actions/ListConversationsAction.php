<?php

namespace App\Modules\Chat\Actions;

use App\Modules\Chat\Models\Conversation;
use App\Modules\Users\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class ListConversationsAction
{
    public function execute(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Conversation::query()
            ->where('user_id', $user->id)
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage);
    }
}
