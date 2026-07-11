<?php

namespace App\Modules\KnowledgeBases\Actions;

use App\Modules\KnowledgeBases\Models\KnowledgeBase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class UpdateKnowledgeBaseAction
{
    public function execute(KnowledgeBase $knowledgeBase, ?string $name, ?bool $isPublic): KnowledgeBase
    {
        $data = [];

        if ($name !== null) {
            $data['name'] = $name;
            $data['slug'] = Str::slug($name);
        }

        if ($isPublic !== null) {
            $data['is_public'] = $isPublic;
        }

        $knowledgeBase->update($data);

        Cache::tags(["user:{$knowledgeBase->owner_id}"])->flush();

        return $knowledgeBase->fresh();
    }
}
