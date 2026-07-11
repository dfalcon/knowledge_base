<?php

use App\Modules\Chat\Models\Conversation;
use App\Modules\KnowledgeBases\Models\KnowledgeBase;
use App\Modules\Users\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    seedRoles();

    $this->user = User::factory()->create(['status' => 'active']);
    $kb = KnowledgeBase::factory()->create(['owner_id' => $this->user->id]);
    $this->conversation = Conversation::create([
        'user_id' => $this->user->id,
        'knowledge_base_id' => $kb->id,
        'title' => 'Test',
    ]);

    Sanctum::actingAs($this->user);
});

it('allows 10 chat requests per minute and blocks the 11th with 429', function () {
    $url = "/api/conversations/{$this->conversation->id}/messages";

    foreach (range(1, 10) as $i) {
        $this->postJson($url, ['content' => "q{$i}"])->assertCreated();
    }

    $this->postJson($url, ['content' => 'over the limit'])
        ->assertStatus(429)
        ->assertHeader('Retry-After');
});
