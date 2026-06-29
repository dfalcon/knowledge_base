<?php

namespace App\Modules\Documents\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Documents\Actions\ListDocumentsAction;
use App\Modules\Documents\Requests\ListDocumentsRequest;
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
}
