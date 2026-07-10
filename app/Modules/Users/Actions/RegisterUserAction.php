<?php

namespace App\Modules\Users\Actions;

use App\Modules\Users\Enums\UserStatus;
use App\Modules\Users\Jobs\NotifyAdminAboutPendingUserJob;
use App\Modules\Users\Models\User;

class RegisterUserAction
{
    public function execute(string $name, string $email, string $password): User
    {
        $user = User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => $password,
            'status'   => UserStatus::Pending,
        ]);

        $user->assignRole('member');

        NotifyAdminAboutPendingUserJob::dispatch($user)->onQueue('critical');

        return $user;
    }
}
