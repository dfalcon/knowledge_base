<?php

namespace App\Modules\Users\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Users\Actions\HandleGoogleCallbackAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use OpenApi\Attributes as OA;

class SocialAuthController extends Controller
{
    #[OA\Get(
        path: '/api/auth/google',
        summary: 'Redirect to the Google consent screen',
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 302, description: 'Redirect to Google'),
        ]
    )]
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    #[OA\Get(
        path: '/api/auth/google/callback',
        summary: 'Google OAuth callback — resolves the user and returns a Sanctum token',
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Authenticated',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'token', type: 'string'),
                ])
            ),
            new OA\Response(response: 401, description: 'Account pending approval'),
        ]
    )]
    public function callback(HandleGoogleCallbackAction $action): JsonResponse
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        return response()->json(['token' => $action->execute($googleUser)]);
    }
}
