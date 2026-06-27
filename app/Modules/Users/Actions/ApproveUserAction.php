<?php

namespace App\Modules\Users\Actions;

use App\Modules\Users\Models\User;

class ApproveUserAction
{
    public function execute(User $user, User $approvedBy, string $role = 'member'): User
    {
        $user->update([
            'status'      => 'active',
            'approved_by' => $approvedBy->id,
            'approved_at' => now(),
        ]);

        $user->syncRoles($role);

        return $user;
    }
}
