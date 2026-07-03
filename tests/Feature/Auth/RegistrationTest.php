<?php

use App\Modules\Users\Models\User;

beforeEach(fn () => seedRoles());

it('registers a user as pending', function () {
    $response = $this->postJson('/api/auth/register', [
        'name'     => 'John Doe',
        'email'    => 'john@example.com',
        'password' => 'secret123',
    ]);

    $response->assertCreated()
        ->assertJsonPath('email', 'john@example.com')
        ->assertJsonPath('status', 'pending');

    $user = User::where('email', 'john@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->status)->toBe('pending');
    expect($user->hasRole('member'))->toBeTrue();

    expect($user->password)->not->toBe('secret123'); // pass should be hashed
});

it('rejects invalid data', function (array $payload) {
    $this->postJson('/api/auth/register', $payload)
        ->assertStatus(422);
})->with([
    'missing name'  => [['email' => 'a@b.com', 'password' => 'secret123']],
    'bad email'     => [['name' => 'A', 'email' => 'not-an-email', 'password' => 'secret123']],
    'short password'=> [['name' => 'A', 'email' => 'a@b.com', 'password' => 'short']],
]);

it('rejects a duplicate email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->postJson('/api/auth/register', [
        'name'     => 'Someone',
        'email'    => 'taken@example.com',
        'password' => 'secret123',
    ])->assertStatus(422)->assertJsonValidationErrorFor('email');
});
