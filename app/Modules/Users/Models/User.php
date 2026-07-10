<?php

namespace App\Modules\Users\Models;

use App\Modules\Users\Enums\UserStatus;
use Database\Factories\Modules\Users\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[UseFactory(UserFactory::class)]
#[Fillable(['name', 'email', 'password', 'google_id', 'status', 'approved_by', 'approved_at'])]
#[Hidden(['password', 'remember_token', 'approved_by'])]
#[Appends(['approved_by_name'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasUuids, HasRoles, HasApiTokens;

    protected function casts(): array
    {
        return [
            'password'          => 'hashed',
            'email_verified_at' => 'datetime',
            'approved_at'       => 'datetime',
            'status'            => UserStatus::class,
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
