<?php

use App\Modules\KnowledgeBases\Actions\GrantPermissionAction;
use App\Modules\KnowledgeBases\Models\KnowledgeBase;
use App\Modules\KnowledgeBases\Services\PermissionService;
use App\Modules\Users\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    seedRoles();

    // not owner, not admin, KB not public → canRead falls through to the cached branch
    $this->owner = User::factory()->create(['status' => 'active']);
    $this->user = User::factory()->create(['status' => 'active']);
    $this->user->assignRole('member');
    $this->kb = KnowledgeBase::factory()->create(['owner_id' => $this->owner->id, 'is_public' => false]);

    app(GrantPermissionAction::class)->execute($this->kb, $this->user, $this->owner, canRead: true);
});

it('hits the database only once, then serves from cache', function () {
    $service = app(PermissionService::class);

    // count only queries against the permissions table
    $queries = 0;
    DB::listen(function ($q) use (&$queries) {
        if (str_contains($q->sql, 'knowledge_base_permissions')) {
            $queries++;
        }
    });

    expect($service->canRead($this->user, $this->kb))->toBeTrue();  // miss → DB
    expect($service->canRead($this->user, $this->kb))->toBeTrue();  // hit → cache
    expect($service->canRead($this->user, $this->kb))->toBeTrue();  // hit → cache

    expect($queries)->toBe(1);
});

it('returns the cached result even after the row is gone', function () {
    $service = app(PermissionService::class);

    expect($service->canRead($this->user, $this->kb))->toBeTrue();  // cache true

    // delete the row directly — bypassing GrantPermissionAction, so no Cache::forget
    $this->kb->permissions()->where('user_id', $this->user->id)->delete();

    // the DB would now say false, but the answer comes from cache → still true
    expect($service->canRead($this->user, $this->kb))->toBeTrue();
});

it('re-reads the database after invalidation', function () {
    $service = app(PermissionService::class);

    expect($service->canRead($this->user, $this->kb))->toBeTrue();

    // revoke access with cache invalidation
    $this->kb->permissions()->where('user_id', $this->user->id)->delete();
    Cache::forget("user:{$this->user->id}:kb-permissions");

    expect($service->canRead($this->user, $this->kb))->toBeFalse();
});
