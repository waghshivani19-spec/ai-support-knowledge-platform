<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Services\AIService;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AIServiceController extends Controller
{
    public function test(AIService $aiService): JsonResponse
    {
        $result = $aiService->testConnection();

        return response()->json([
            'success' => true,
            'message' => 'Laravel successfully communicated with FastAPI',
            'fastapi' => $result,
        ]);
    }

    public function chat(Request $request, ChatService $chatService): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
            'conversation_id' => ['nullable', 'integer'],
            'knowledge_base_id' => ['nullable', 'integer'],
            'top_k' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $user = $request->user();

        try {
            $conversation = $chatService->ensureConversation(
                user: $user,
                conversationId: $validated['conversation_id'] ?? null,
                knowledgeBaseId: $validated['knowledge_base_id'] ?? null,
                title: Str::limit($validated['message'], 80)
            );

            $userMessage = $chatService->recordUserMessage(
                $conversation,
                $user,
                $validated['message']
            );

            $result = $chatService->askAndRecordAssistantReply(
                conversation: $conversation,
                userMessage: $userMessage,
                message: $validated['message'],
                knowledgeBaseId: $validated['knowledge_base_id'] ?? null,
                topK: (int) ($validated['top_k'] ?? 5)
            );
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'AI service is currently unavailable.',
                'error' => $e->getMessage(),
            ], 502);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'conversation_id' => $result['conversation_id'],
                'user_message_id' => $result['user_message_id'],
                'assistant_message_id' => $result['assistant_message_id'],
                'reply' => $result['reply'],
                'sources' => $result['sources'],
                'model' => $result['model'],
                'latency_ms' => $result['latency_ms'],
            ],
        ]);
    }

    public function ask(Request $request, AIService $aiService): JsonResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:4000'],
            'top_k' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        try {
            $result = $aiService->askKnowledgeBase(
                $validated['question'],
                (int) ($validated['top_k'] ?? 5)
            );
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Knowledge base query failed.',
                'error' => $e->getMessage(),
            ], 502);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    public function knowledgeBases(AIService $aiService): JsonResponse
    {
        try {
            $result = $aiService->listKnowledgeBases();
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load knowledge bases.',
                'error' => $e->getMessage(),
            ], 502);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}