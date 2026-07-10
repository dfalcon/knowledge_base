<?php

use App\Modules\Users\Enums\UserStatus;
use App\Modules\Users\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(fn () => seedRoles());

function admin(): User
{
    $admin = User::factory()->create(['status' => 'active']);
    $admin->assignRole('admin');

    return $admin;
}

it('lets an admin approve a pending user', function () {
    Sanctum::actingAs(admin());
    $pending = User::factory()->create(['status' => 'pending']);

    $this->postJson("/api/admin/users/{$pending->id}/approve", ['role' => 'member'])
        ->assertOk()
        ->assertJsonPath('status', 'active');

    $pending->refresh();
    expect($pending->status)->toBe(UserStatus::Active);
    expect($pending->approved_at)->not->toBeNull();
    expect($pending->hasRole('member'))->toBeTrue();
});

it('forbids a non-admin from approving', function () {
    $member = User::factory()->create(['status' => 'active']);
    $member->assignRole('member');
    Sanctum::actingAs($member);

    $pending = User::factory()->create(['status' => 'pending']);

    $this->postJson("/api/admin/users/{$pending->id}/approve")
        ->assertForbidden();

    expect($pending->fresh()->status)->toBe(UserStatus::Pending);
});

it('rejects an unauthenticated request', function () {
    $pending = User::factory()->create(['status' => 'pending']);

    $this->postJson("/api/admin/users/{$pending->id}/approve")
        ->assertUnauthorized();
});
