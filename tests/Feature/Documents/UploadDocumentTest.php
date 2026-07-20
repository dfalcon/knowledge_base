<?php

use App\Modules\Documents\Models\Document;
use App\Modules\KnowledgeBases\Models\KnowledgeBase;
use App\Modules\Users\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    seedRoles();
    Storage::fake('s3');
    Queue::fake();
});

it('stores the file in s3 and a row in the database', function () {
    $owner = User::factory()->create(['status' => 'active']);
    $kb = KnowledgeBase::factory()->create(['owner_id' => $owner->id]);
    Sanctum::actingAs($owner);

    $file = UploadedFile::fake()->create('handbook.pdf', 120, 'application/pdf');

    $response = $this->postJson("/api/knowledge-bases/{$kb->id}/documents", [
        'file' => $file,
    ]);

    $response->assertCreated()
        ->assertJsonPath('status', 'pending')
        ->assertJsonPath('knowledge_base_id', $kb->id);

    $document = Document::first();
    expect($document)->not->toBeNull();
    expect($document->file_name)->toBe('handbook.pdf');
    expect($document->title)->toBe('handbook');

    Storage::disk('s3')->assertExists($document->file_path);
});

it('uses the provided title over the file name', function () {
    $owner = User::factory()->create(['status' => 'active']);
    $kb = KnowledgeBase::factory()->create(['owner_id' => $owner->id]);
    Sanctum::actingAs($owner);

    $this->postJson("/api/knowledge-bases/{$kb->id}/documents", [
        'file'  => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf'),
        'title' => 'Employee Handbook',
    ])->assertCreated()->assertJsonPath('title', 'Employee Handbook');
});

it('rejects a disallowed file type', function () {
    $owner = User::factory()->create(['status' => 'active']);
    $kb = KnowledgeBase::factory()->create(['owner_id' => $owner->id]);
    Sanctum::actingAs($owner);

    $this->postJson("/api/knowledge-bases/{$kb->id}/documents", [
        'file' => UploadedFile::fake()->create('malware.exe', 10, 'application/x-msdownload'),
    ])->assertStatus(422)->assertJsonValidationErrorFor('file');

    expect(Document::count())->toBe(0);
});

it('forbids a user without write access', function () {
    $stranger = User::factory()->create(['status' => 'active']);
    $stranger->assignRole('member');
    $kb = KnowledgeBase::factory()->create(); // owned by someone else
    Sanctum::actingAs($stranger);

    $this->postJson("/api/knowledge-bases/{$kb->id}/documents", [
        'file' => UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf'),
    ])->assertForbidden();

    expect(Document::count())->toBe(0);
    Storage::disk('s3')->assertDirectoryEmpty('documents');
});
