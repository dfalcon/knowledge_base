<?php

namespace App\Modules\Users\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Users\Actions\LoginUserAction;
use App\Modules\Users\Actions\RegisterUserAction;
use App\Modules\Users\Requests\LoginRequest;
use App\Modules\Users\Requests\RegisterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: '/api/auth/register',
        summary: 'Register a new user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'secret123', minLength: 8),
                ]
            )
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'User registered, awaiting approval',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'email', type: 'string'),
                    new OA\Property(property: 'status', type: 'string', example: 'pending'),
                ])
            ),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function register(RegisterRequest $request, RegisterUserAction $action): JsonResponse
    {
        $user = $action->execute(
            $request->input('name'),
            $request->input('email'),
            $request->input('password'),
        );

        return response()->json($user, 201);
    }

    #[OA\Post(
        path: '/api/auth/login',
        summary: 'Login and get Sanctum token',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'secret123'),
                ]
            )
        ),
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
            new OA\Response(response: 422, description: 'Invalid credentials'),
        ]
    )]
    public function login(LoginRequest $request, LoginUserAction $action): JsonResponse
    {
        $token = $action->execute(
            $request->input('email'),
            $request->input('password'),
        );

        return response()->json(['token' => $token]);
    }

    #[OA\Post(
        path: '/api/auth/logout',
        summary: 'Revoke current token',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 204, description: 'Logged out'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(null, 204);
    }

    #[OA\Get(
        path: '/api/auth/me',
        summary: 'Get current user',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Current user'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}
