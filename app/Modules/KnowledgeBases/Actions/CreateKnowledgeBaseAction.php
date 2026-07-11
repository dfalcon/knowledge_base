<?php

namespace App\Modules\KnowledgeBases\Actions;

use App\Modules\KnowledgeBases\Models\KnowledgeBase;
use App\Modules\Users\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CreateKnowledgeBaseAction
{
    public function execute(User $owner, string $name, bool $isPublic = false): KnowledgeBase
    {
        $kb = KnowledgeBase::create([
            'name'      => $name,
            'slug'      => Str::slug($name),
            'is_public' => $isPublic,
            'owner_id'  => $owner->id,
        ]);

        Cache::tags(["user:{$owner->id}"])->flush();

        return $kb;
    }
}
