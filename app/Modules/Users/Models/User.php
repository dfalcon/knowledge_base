<?php

namespace App\Modules\Users\Models;

use Database\Factories\Modules\Users\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'google_id', 'status', 'approved_by', 'approved_at'])]
#[Hidden(['password', 'remember_token', 'approved_by'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasUuids, HasRoles, HasApiTokens;

    protected $appends = ['approved_by_name'];

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    protected function casts(): array
    {
        return [
            'password'          => 'hashed',
            'email_verified_at' => 'datetime',
            'approved_at'       => 'datetime',
        ];
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    protected function approvedByName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->approvedBy?->name
        );
    }
}
