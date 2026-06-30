<?php

namespace App\Modules\KnowledgeBases\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\KnowledgeBases\Actions\CreateKnowledgeBaseAction;
use App\Modules\KnowledgeBases\Actions\GrantPermissionAction;
use App\Modules\KnowledgeBases\Actions\ListKnowledgeBasesAction;
use App\Modules\KnowledgeBases\Actions\UpdateKnowledgeBaseAction;
use App\Modules\KnowledgeBases\Models\KnowledgeBase;
use App\Modules\KnowledgeBases\Requests\CreateKnowledgeBaseRequest;
use App\Modules\KnowledgeBases\Requests\GrantPermissionRequest;
use App\Modules\KnowledgeBases\Requests\UpdateKnowledgeBaseRequest;
use App\Modules\KnowledgeBases\Services\PermissionService;
use App\Modules\Users\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class KnowledgeBaseController extends Controller
{
    #[OA\Get(
        path: '/api/knowledge-bases',
        summary: 'List knowledge bases visible to the current user',
        security: [['bearerAuth' => []]],
        tags: ['KnowledgeBases'],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list of knowledge bases'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index(Request $request, ListKnowledgeBasesAction $action): JsonResponse
    {
        return response()->json($action->execute($request->user()));
    }

    #[OA\Post(
        path: '/api/knowledge-bases',
        summary: 'Create a knowledge base',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'HR Policies'),
                    new OA\Property(property: 'is_public', type: 'boolean', example: false),
                ]
            )
        ),
        tags: ['KnowledgeBases'],
        responses: [
            new OA\Response(response: 201, description: 'Knowledge base created'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(CreateKnowledgeBaseRequest $request, CreateKnowledgeBaseAction $action): JsonResponse
    {
        $knowledgeBase = $action->execute(
            $request->user(),
            $request->input('name'),
            $request->boolean('is_public'),
        );

        return response()->json($knowledgeBase, 201);
    }

    #[OA\Get(
        path: '/api/knowledge-bases/{knowledgeBase}',
        summary: 'Get a knowledge base',
        security: [['bearerAuth' => []]],
        tags: ['KnowledgeBases'],
        parameters: [
            new OA\Parameter(name: 'knowledgeBase', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Knowledge base'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function show(KnowledgeBase $knowledgeBase, Request $request, PermissionService $permissions): JsonResponse
    {
        abort_unless($permissions->canRead($request->user(), $knowledgeBase), 403);

        return response()->json($knowledgeBase);
    }

    #[OA\Put(
        path: '/api/knowledge-bases/{knowledgeBase}',
        summary: 'Update a knowledge base',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'is_public', type: 'boolean'),
            ])
        ),
        tags: ['KnowledgeBases'],
        parameters: [
            new OA\Parameter(name: 'knowledgeBase', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Knowledge base updated'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(KnowledgeBase $knowledgeBase, UpdateKnowledgeBaseRequest $request, UpdateKnowledgeBaseAction $action): JsonResponse
    {
        $this->authorizeOwner($request->user(), $knowledgeBase);

        $knowledgeBase = $action->execute(
            $knowledgeBase,
            $request->input('name'),
            $request->has('is_public') ? $request->boolean('is_public') : null,
        );

        return response()->json($knowledgeBase);
    }

    #[OA\Delete(
        path: '/api/knowledge-bases/{knowledgeBase}',
        summary: 'Delete a knowledge base',
        security: [['bearerAuth' => []]],
        tags: ['KnowledgeBases'],
        parameters: [
            new OA\Parameter(name: 'knowledgeBase', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Knowledge base deleted'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function destroy(KnowledgeBase $knowledgeBase, Request $request): JsonResponse
    {
        $this->authorizeOwner($request->user(), $knowledgeBase);

        $knowledgeBase->delete();

        return response()->json(null, 204);
    }

    #[OA\Post(
        path: '/api/knowledge-bases/{knowledgeBase}/permissions',
        summary: 'Grant or update a user\'s permission on a knowledge base',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['user_id'],
                properties: [
                    new OA\Property(property: 'user_id', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'can_read', type: 'boolean', example: true),
                    new OA\Property(property: 'can_write', type: 'boolean', example: false),
                ]
            )
        ),
        tags: ['KnowledgeBases'],
        parameters: [
            new OA\Parameter(name: 'knowledgeBase', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Permission granted'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function grantPermission(KnowledgeBase $knowledgeBase, GrantPermissionRequest $request, GrantPermissionAction $action): JsonResponse
    {
        $this->authorizeOwner($request->user(), $knowledgeBase);

        $target = User::findOrFail($request->input('user_id'));

        $permission = $action->execute(
            $knowledgeBase,
            $target,
            $request->user(),
            $request->boolean('can_read', true),
            $request->boolean('can_write', false),
        );

        return response()->json($permission);
    }

    #[OA\Delete(
        path: '/api/knowledge-bases/{knowledgeBase}/permissions/{user}',
        summary: 'Revoke a user\'s permission on a knowledge base',
        security: [['bearerAuth' => []]],
        tags: ['KnowledgeBases'],
        parameters: [
            new OA\Parameter(name: 'knowledgeBase', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Permission revoked'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function revokePermission(KnowledgeBase $knowledgeBase, User $user, Request $request): JsonResponse
    {
        $this->authorizeOwner($request->user(), $knowledgeBase);

        $knowledgeBase->permissions()->where('user_id', $user->id)->delete();

        return response()->json(null, 204);
    }

    private function authorizeOwner(User $user, KnowledgeBase $knowledgeBase): void
    {
        abort_unless($knowledgeBase->owner_id === $user->id || $user->hasRole('admin'), 403);
    }
}
