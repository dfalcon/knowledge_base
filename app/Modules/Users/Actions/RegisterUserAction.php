<?php

namespace App\Modules\Users\Actions;

use App\Modules\Users\Models\User;

class RegisterUserAction
{
    public function execute(string $name, string $email, string $password): User
    {
        $user = User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => $password,
            'status'   => 'pending',
        ]);

        $user->assignRole('member');

        return $user;
    }
}
