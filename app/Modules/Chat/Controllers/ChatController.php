<?php

namespace App\Modules\Chat\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Chat\Actions\AskQuestionAction;
use App\Modules\Chat\Actions\ListConversationsAction;
use App\Modules\Chat\Models\Conversation;
use App\Modules\Chat\Requests\AskQuestionRequest;
use App\Modules\Chat\Requests\CreateConversationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ChatController extends Controller
{
    #[OA\Get(
        path: '/api/conversations',
        summary: 'List the current user\'s conversations',
        security: [['bearerAuth' => []]],
        tags: ['Chat'],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list of conversations, most recent first'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index(Request $request, ListConversationsAction $action): JsonResponse
    {
        return response()->json($action->execute($request->user()));
    }

    #[OA\Post(
        path: '/api/conversations',
        summary: 'Start a conversation',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'knowledge_base_id', type: 'string', format: 'uuid', nullable: true),
                new OA\Property(property: 'title', type: 'string', nullable: true),
            ])
        ),
        tags: ['Chat'],
        responses: [
            new OA\Response(response: 201, description: 'Conversation created'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(CreateConversationRequest $request): JsonResponse
    {
        $conversation = Conversation::create([
            'user_id'           => $request->user()->id,
            'knowledge_base_id' => $request->input('knowledge_base_id'),
            'title'             => $request->input('title'),
        ]);

        return response()->json($conversation, 201);
    }

    #[OA\Get(
        path: '/api/conversations/{conversation}/messages',
        summary: 'List messages in a conversation',
        security: [['bearerAuth' => []]],
        tags: ['Chat'],
        parameters: [
            new OA\Parameter(name: 'conversation', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Messages ordered oldest first'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function messages(Conversation $conversation, Request $request): JsonResponse
    {
        $this->authorizeOwner($request, $conversation);

        return response()->json($conversation->messages()->orderBy('created_at')->get());
    }

    #[OA\Post(
        path: '/api/conversations/{conversation}/messages',
        summary: 'Ask a question (stub — returns a placeholder answer until the AI service lands)',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['content'],
                properties: [
                    new OA\Property(property: 'content', type: 'string', example: 'What is our vacation policy?'),
                ]
            )
        ),
        tags: ['Chat'],
        parameters: [
            new OA\Parameter(name: 'conversation', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 201, description: 'Assistant reply'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function ask(Conversation $conversation, AskQuestionRequest $request, AskQuestionAction $action): JsonResponse
    {
        $this->authorizeOwner($request, $conversation);

        $reply = $action->execute($conversation, $request->input('content'));

        return response()->json($reply, 201);
    }

    #[OA\Delete(
        path: '/api/conversations/{conversation}',
        summary: 'Delete a conversation and its messages',
        security: [['bearerAuth' => []]],
        tags: ['Chat'],
        parameters: [
            new OA\Parameter(name: 'conversation', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Conversation deleted'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function destroy(Conversation $conversation, Request $request): JsonResponse
    {
        $this->authorizeOwner($request, $conversation);

        $conversation->delete();

        return response()->json(null, 204);
    }

    private function authorizeOwner(Request $request, Conversation $conversation): void
    {
        abort_unless($conversation->user_id === $request->user()->id, 403);
    }
}
