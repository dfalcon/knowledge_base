<?php

use App\Modules\Documents\Jobs\PublishDocumentUploadedJob;
use App\Modules\KnowledgeBases\Models\KnowledgeBase;
use App\Modules\Users\Jobs\NotifyAdminAboutPendingUserJob;
use App\Modules\Users\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    seedRoles();
    Queue::fake();
});

it('queues the admin notification when a user registers', function () {
    $this->postJson('/api/auth/register', [
        'name'     => 'John Doe',
        'email'    => 'john@example.com',
        'password' => 'secret123',
    ])->assertCreated();

    Queue::assertPushedOn('critical', NotifyAdminAboutPendingUserJob::class,
        fn (NotifyAdminAboutPendingUserJob $job) => $job->user->email === 'john@example.com'
    );
});

it('does not queue the notification when registration fails validation', function () {
    $this->postJson('/api/auth/register', ['email' => 'not-an-email'])
        ->assertStatus(422);

    Queue::assertNotPushed(NotifyAdminAboutPendingUserJob::class);
});

it('queues the publish job when a document is uploaded', function () {
    Storage::fake('s3');
    $owner = User::factory()->create(['status' => 'active']);
    $kb = KnowledgeBase::factory()->create(['owner_id' => $owner->id]);
    Sanctum::actingAs($owner);

    $this->postJson("/api/knowledge-bases/{$kb->id}/documents", [
        'file' => UploadedFile::fake()->create('handbook.pdf', 120, 'application/pdf'),
    ])->assertCreated();

    Queue::assertPushedOn('documents', PublishDocumentUploadedJob::class);
});
