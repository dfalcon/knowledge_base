<?php

namespace App\Modules\Users\Actions;

use App\Modules\Users\Enums\UserStatus;
use App\Modules\Users\Models\User;
use Illuminate\Auth\AuthenticationException;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class HandleGoogleCallbackAction
{
    public function execute(SocialiteUser $googleUser): string
    {
        $user = User::where('google_id', $googleUser->getId())->first();

        if (! $user && $googleUser->getEmail()) {
            $user = User::where('email', $googleUser->getEmail())->first();
            $user?->update(['google_id' => $googleUser->getId()]);
        }

        if (! $user) {
            $user = User::create([
                'name'      => $googleUser->getName() ?: $googleUser->getEmail(),
                'email'     => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'password'  => null,
                'status'    => UserStatus::Pending,
            ]);
            $user->assignRole('member');
        }

        if ($user->status !== UserStatus::Active) {
            throw new AuthenticationException('Your account is pending approval.');
        }

        return $user->createToken('google')->plainTextToken;
    }
}
