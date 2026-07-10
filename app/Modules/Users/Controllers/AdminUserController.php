<?php

namespace App\Modules\Users\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Users\Actions\ApproveUserAction;
use App\Modules\Users\Enums\UserStatus;
use App\Modules\Users\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class AdminUserController extends Controller
{
    #[OA\Get(
        path: '/api/admin/users/pending',
        summary: 'List pending users',
        security: [['bearerAuth' => []]],
        tags: ['Admin'],
        responses: [
            new OA\Response(response: 200, description: 'List of pending users'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function pending(): JsonResponse
    {
        return response()->json(User::where('status', UserStatus::Pending)->get());
    }

    #[OA\Post(
        path: '/api/admin/users/{user}/approve',
        summary: 'Approve a pending user',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'role', type: 'string', example: 'member', enum: ['member', 'admin']),
            ])
        ),
        tags: ['Admin'],
        parameters: [
            new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'User approved'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function approve(User $user, Request $request, ApproveUserAction $action): JsonResponse
    {
        $data = $request->validate(['role' => ['sometimes', Rule::in(['member', 'admin'])]]);

        $action->execute($user, $request->user(), $data['role'] ?? 'member');

        return response()->json($user->fresh());
    }

    #[OA\Put(
        path: '/api/admin/users/{user}/role',
        summary: 'Change role of an active user',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['role'],
                properties: [
                    new OA\Property(property: 'role', type: 'string', example: 'admin', enum: ['member', 'admin']),
                ]
            )
        ),
        tags: ['Admin'],
        parameters: [
            new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Role updated'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function updateRole(User $user, Request $request): JsonResponse
    {
        $data = $request->validate(['role' => ['required', Rule::in(['member', 'admin'])]]);

        $user->syncRoles($data['role']);

        return response()->json($user->fresh());
    }
}
