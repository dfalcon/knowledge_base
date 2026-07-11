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

        // ponytail: тег user:X флашится при изменениях самого юзера (grant/revoke,
        // create/update/delete своих баз). Появление чужой публичной базы у этого
        // юзера отстаёт до TTL 60с — точную инвалидацию добавим, если понадобится.
        return Cache::tags(["user:{$user->id}"])->remember(
            "kb-list:{$user->id}:{$perPage}:{$page}",
            60,
            fn () => KnowledgeBase::query()
                ->when(! $user->hasRole('admin'), fn ($q) => $q
                    ->where('owner_id', $user->id)
                    ->orWhere('is_public', true)
                    ->orWhereHas('permissions', fn ($p) => $p->where('user_id', $user->id)->where('can_read', true))
                )
                ->orderBy('created_at', 'desc')
                ->paginate($perPage),
        );
    }
}
