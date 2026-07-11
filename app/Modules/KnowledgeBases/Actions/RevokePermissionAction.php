<?php

namespace App\Modules\KnowledgeBases\Actions;

use App\Modules\KnowledgeBases\Models\KnowledgeBase;
use App\Modules\Users\Models\User;
use Illuminate\Support\Facades\Cache;

class RevokePermissionAction
{
    public function execute(KnowledgeBase $knowledgeBase, User $target): void
    {
        $knowledgeBase->permissions()->where('user_id', $target->id)->delete();

        Cache::tags(["user:{$target->id}"])->flush();
    }
}
