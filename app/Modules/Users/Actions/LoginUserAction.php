<?php

namespace App\Modules\Users\Actions;

use App\Modules\Users\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginUserAction
{
    public function execute(string $email, string $password): string
    {
        if (!Auth::attempt(['email' => $email, 'password' => $password])) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        if ($user->status !== 'active') {
            Auth::logout();
            throw new AuthenticationException('Your account is pending approval.');
        }

        return $user->createToken('auth')->plainTextToken;
    }
}
