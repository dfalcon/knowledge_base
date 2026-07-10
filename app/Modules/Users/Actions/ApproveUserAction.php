<?php

namespace App\Modules\Users\Actions;

use App\Modules\Users\Enums\UserStatus;
use App\Modules\Users\Jobs\SendWelcomeEmailJob;
use App\Modules\Users\Models\User;

class ApproveUserAction
{
    public function execute(User $user, User $approvedBy, string $role = 'member'): User
    {
        $user->update([
            'status'      => UserStatus::Active,
            'approved_by' => $approvedBy->id,
            'approved_at' => now(),
        ]);

        $user->syncRoles($role);

        SendWelcomeEmailJob::dispatch($user)->onQueue('notifications');

        return $user;
    }
}
