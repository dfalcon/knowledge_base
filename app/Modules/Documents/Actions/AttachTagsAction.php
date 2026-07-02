<?php

namespace App\Modules\Documents\Actions;

use App\Modules\Documents\Models\Document;
use App\Modules\Documents\Models\Tag;
use Illuminate\Support\Collection;

class AttachTagsAction
{
    /**
     * Replace a document's tags. Tag names are find-or-created within the
     * document's knowledge base, then synced onto the document.
     *
     * @param  array<int, string>  $names
     */
    public function execute(Document $document, array $names): Collection
    {
        $tagIds = collect($names)
            ->map(fn (string $name) => trim($name))
            ->filter()
            ->unique()
            ->map(fn (string $name) => Tag::firstOrCreate([
                'knowledge_base_id' => $document->knowledge_base_id,
                'name'              => $name,
            ])->id);

        $document->tags()->sync($tagIds);

        return $document->tags()->get();
    }
}
