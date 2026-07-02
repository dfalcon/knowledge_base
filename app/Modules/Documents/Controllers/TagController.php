<?php

namespace App\Modules\Documents\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Documents\Actions\AttachTagsAction;
use App\Modules\Documents\Models\Document;
use App\Modules\Documents\Models\Tag;
use App\Modules\Documents\Requests\CreateTagRequest;
use App\Modules\Documents\Requests\SyncDocumentTagsRequest;
use App\Modules\KnowledgeBases\Models\KnowledgeBase;
use App\Modules\KnowledgeBases\Services\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TagController extends Controller
{
    #[OA\Get(
        path: '/api/knowledge-bases/{knowledgeBase}/tags',
        summary: 'List tags in a knowledge base with document counts',
        security: [['bearerAuth' => []]],
        tags: ['Tags'],
        parameters: [
            new OA\Parameter(name: 'knowledgeBase', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of tags with documents_count'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(KnowledgeBase $knowledgeBase, Request $request, PermissionService $permissions): JsonResponse
    {
        abort_unless($permissions->canRead($request->user(), $knowledgeBase), 403);

        $tags = $knowledgeBase->tags()->withCount('documents')->orderBy('name')->get();

        return response()->json($tags);
    }

    #[OA\Post(
        path: '/api/knowledge-bases/{knowledgeBase}/tags',
        summary: 'Create a tag in a knowledge base',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'policy'),
                ]
            )
        ),
        tags: ['Tags'],
        parameters: [
            new OA\Parameter(name: 'knowledgeBase', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 201, description: 'Tag created'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(KnowledgeBase $knowledgeBase, CreateTagRequest $request, PermissionService $permissions): JsonResponse
    {
        abort_unless($permissions->canWrite($request->user(), $knowledgeBase), 403);

        $tag = $knowledgeBase->tags()->firstOrCreate(['name' => $request->input('name')]);

        return response()->json($tag, 201);
    }

    #[OA\Delete(
        path: '/api/knowledge-bases/{knowledgeBase}/tags/{tag}',
        summary: 'Delete a tag and detach it from all documents',
        security: [['bearerAuth' => []]],
        tags: ['Tags'],
        parameters: [
            new OA\Parameter(name: 'knowledgeBase', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'tag', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Tag deleted'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Tag not found in this knowledge base'),
        ]
    )]
    public function destroy(KnowledgeBase $knowledgeBase, Tag $tag, Request $request, PermissionService $permissions): JsonResponse
    {
        abort_unless($permissions->canWrite($request->user(), $knowledgeBase), 403);
        abort_unless($tag->knowledge_base_id === $knowledgeBase->id, 404);

        $tag->delete();

        return response()->json(null, 204);
    }

    #[OA\Put(
        path: '/api/documents/{document}/tags',
        summary: 'Replace the tags on a document',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['tags'],
                properties: [
                    new OA\Property(property: 'tags', type: 'array', items: new OA\Items(type: 'string'), example: ['policy', 'leave']),
                ]
            )
        ),
        tags: ['Tags'],
        parameters: [
            new OA\Parameter(name: 'document', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Updated list of tags on the document'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function syncDocument(Document $document, SyncDocumentTagsRequest $request, AttachTagsAction $action, PermissionService $permissions): JsonResponse
    {
        abort_unless($permissions->canWrite($request->user(), $document->knowledgeBase), 403);

        $tags = $action->execute($document, $request->input('tags'));

        return response()->json($tags);
    }
}
