<?php

namespace App\Modules\Documents\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Documents\Actions\ListDocumentsAction;
use App\Modules\Documents\Actions\UploadDocumentAction;
use App\Modules\Documents\Requests\ListDocumentsRequest;
use App\Modules\Documents\Requests\UploadDocumentRequest;
use App\Modules\KnowledgeBases\Models\KnowledgeBase;
use App\Modules\KnowledgeBases\Services\PermissionService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class DocumentController extends Controller
{
    #[OA\Get(
        path: '/api/documents',
        summary: 'List documents with optional filters',
        security: [['bearerAuth' => []]],
        tags: ['Documents'],
        parameters: [
            new OA\Parameter(name: 'knowledge_base_id', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['indexed'])),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15, minimum: 1, maximum: 100)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list of documents'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function index(ListDocumentsRequest $request, ListDocumentsAction $action): JsonResponse
    {
        $documents = $action->execute(
            $request->input('knowledge_base_id'),
            $request->input('status'),
            (int) $request->input('per_page', 15),
        );

        return response()->json($documents);
    }

    #[OA\Post(
        path: '/api/knowledge-bases/{knowledgeBase}/documents',
        summary: 'Upload a document to a knowledge base',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['file'],
                    properties: [
                        new OA\Property(property: 'file', type: 'string', format: 'binary', description: 'Documents, spreadsheets, images, audio or video — max 500 MB'),
                        new OA\Property(property: 'title', type: 'string', description: 'Defaults to the uploaded file name'),
                    ]
                )
            )
        ),
        tags: ['Documents'],
        parameters: [
            new OA\Parameter(name: 'knowledgeBase', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 201, description: 'Document uploaded'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(KnowledgeBase $knowledgeBase, UploadDocumentRequest $request, UploadDocumentAction $action, PermissionService $permissions): JsonResponse
    {
        abort_unless($permissions->canWrite($request->user(), $knowledgeBase), 403);

        $document = $action->execute(
            $knowledgeBase,
            $request->user(),
            $request->file('file'),
            $request->input('title'),
        );

        return response()->json($document, 201);
    }
}
