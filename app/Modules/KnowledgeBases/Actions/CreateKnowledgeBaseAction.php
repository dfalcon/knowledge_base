<?php

namespace App\Modules\KnowledgeBases\Actions;

use App\Modules\KnowledgeBases\Models\KnowledgeBase;
use App\Modules\Users\Models\User;
use Illuminate\Support\Str;

class CreateKnowledgeBaseAction
{
    public function execute(User $owner, string $name, bool $isPublic = false): KnowledgeBase
    {
        return KnowledgeBase::create([
            'name'      => $name,
            'slug'      => Str::slug($name),
            'is_public' => $isPublic,
            'owner_id'  => $owner->id,
        ]);
    }
}
