<?php

namespace App\Modules\KnowledgeBases\Actions;

use App\Modules\KnowledgeBases\Models\KnowledgeBase;
use App\Modules\Users\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;

class ListKnowledgeBasesAction
{
    public function execute(User $user, int $perPage = 15): LengthAwarePaginator
    {
        $page = Paginator::resolveCurrentPage();
        $tags = ["user:{$user->id}"];
        $key = "kb-list:{$user->id}:{$perPage}:".$page;

        // Fast path: a cache hit returns without touching the lock, so warm reads run
        // in parallel instead of serializing through it. Only a miss falls through to lock.
        $cached = Cache::tags($tags)->get($key);
        if ($cached !== null) {
            return $cached;
        }

        // On a miss one worker builds the query; concurrent requests wait for the warm cache.
        return Cache::lock("lock:$key", 10)->block(5, fn () => Cache::tags($tags)->remember(
            $key,
            60,
            fn () => $this->query($user, $perPage),
        ));
    }

    private function query(User $user, int $perPage): LengthAwarePaginator
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
