<?php

namespace App\Modules\KnowledgeBases\Actions;

use App\Modules\KnowledgeBases\Models\KnowledgeBase;
use App\Modules\KnowledgeBases\Models\KnowledgeBasePermission;
use App\Modules\Users\Models\User;
use Illuminate\Support\Facades\Cache;

class GrantPermissionAction
{
    public function execute(KnowledgeBase $knowledgeBase, User $target, User $grantedBy, bool $canRead = true, bool $canWrite = false): KnowledgeBasePermission
    {
        Cache::tags(["user:{$target->id}"])->flush();

        return $knowledgeBase->permissions()->updateOrCreate(
            ['user_id' => $target->id],
            ['can_read' => $canRead, 'can_write' => $canWrite, 'granted_by' => $grantedBy->id, 'granted_at' => now(), 'updated_at' => now()],
        );
    }
}
