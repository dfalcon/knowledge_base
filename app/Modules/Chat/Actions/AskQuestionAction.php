<?php

namespace App\Modules\Chat\Actions;

use App\Modules\Chat\Models\Conversation;
use App\Modules\Chat\Models\Message;
use Illuminate\Support\Facades\DB;

class AskQuestionAction
{
    /**
     * Store the user's question and return a stubbed assistant reply.
     *
     *fake answer until the Python AI Service lands.
     * Real RAG will replace the body here, the persistence around it stays.
     */
    public function execute(Conversation $conversation, string $question): Message
    {
        return DB::transaction(function () use ($conversation, $question) {
            $conversation->messages()->create([
                'role'    => 'user',
                'content' => $question,
            ]);

            return $conversation->messages()->create([
                'role'    => 'assistant',
                'content' => 'AI service is not connected yet — this is a placeholder answer',
                'sources' => [],
            ]);
        });
    }
}
