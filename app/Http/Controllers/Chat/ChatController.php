<?php

declare(strict_types=1);

namespace App\Http\Controllers\Chat;

use App\DataTransferObjects\Chat\SendMessageData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\SendMessageRequest;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Services\Repositories\Chat\ChatLogicRepository;
use App\Services\Repositories\Chat\ConversationDbRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    public function __construct(
        private readonly ChatLogicRepository    $chatRepository,
        private readonly ConversationDbRepository $conversationDb,
    ) {}

    public function index(Request $request): Response
    {
        $conversations = $this->chatRepository->getConversationsForUser($request->user()->getId());

        return Inertia::render('Chat/Index', [
            'conversations' => ConversationResource::collection($conversations)->resolve($request),
        ]);
    }

    public function show(Request $request, Conversation $conversation): Response
    {
        $authId = $request->user()->getId();

        if ($conversation->getSenderId() !== $authId && $conversation->getReceiverId() !== $authId) {
            abort(403);
        }

        $conversation->load(['sender', 'receiver', 'messages' => fn ($q) => $q->latest()->limit(1)]);
        $messages = $this->chatRepository->getMessages($conversation->getId(), $authId);
        $conversations = $this->chatRepository->getConversationsForUser($authId);
        $relatedJob = $this->conversationDb->findRelatedJobForConversation($conversation->getId());

        return Inertia::render('Chat/Show', [
            'conversation' => (new ConversationResource($conversation))->toArray($request),
            'messages' => MessageResource::collection($messages)->resolve($request),
            'conversations' => ConversationResource::collection($conversations)->resolve($request),
            'auth_user_id' => $authId,
            'related_job' => $relatedJob ? [
                'id' => $relatedJob->getId(),
                'title' => $relatedJob->getTitle(),
                'status' => $relatedJob->getStatus()->value,
            ] : null,
        ]);
    }

    public function store(SendMessageRequest $request, Conversation $conversation): JsonResponse
    {
        $authId = $request->user()->getId();

        if ($conversation->getSenderId() !== $authId && $conversation->getReceiverId() !== $authId) {
            abort(403);
        }

        $message = $this->chatRepository->sendMessage($request->toDTO($conversation->getId()));

        return response()->json(new MessageResource($message), 201);
    }

    public function startConversation(Request $request): JsonResponse
    {
        $request->validate([
            'receiver_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $conversation = $this->chatRepository->getOrCreateConversation(
            $request->user()->getId(),
            (int) $request->input('receiver_id'),
        );

        return response()->json(['conversation_id' => $conversation->getId()]);
    }
}
