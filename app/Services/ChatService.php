<?php

namespace App\Services;

use App\Models\AiRun;
use App\Models\Conversation;
use App\Models\KnowledgeBase;
use App\Models\Message;
use App\Models\MessageSource;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChatService
{
    public function __construct(
        private readonly AIService $ai
    ) {
    }

    public function ensureConversation(
        User $user,
        ?int $conversationId = null,
        ?int $knowledgeBaseId = null,
        ?string $title = null
    ): Conversation {
        if ($conversationId) {
            $conversation = Conversation::query()
                ->where('user_id', $user->id)
                ->find($conversationId);

            if ($conversation) {
                return $conversation;
            }
        }

        $resolvedKbId = $knowledgeBaseId
            ?: KnowledgeBase::query()->where('is_active', true)->value('id');

        return Conversation::create([
            'user_id' => $user->id,
            'knowledge_base_id' => $resolvedKbId,
            'session_id' => (string) Str::uuid(),
            'title' => $title ?: 'New conversation',
            'status' => 'open',
            'is_ai_enabled' => true,
            'last_message_at' => now(),
        ]);
    }

    public function recordUserMessage(
        Conversation $conversation,
        User $user,
        string $content
    ): Message {
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'sender_type' => 'customer',
            'content' => $content,
        ]);

        $this->touchConversation($conversation);

        return $message;
    }

    public function askAndRecordAssistantReply(
        Conversation $conversation,
        Message $userMessage,
        string $message,
        ?int $knowledgeBaseId = null,
        int $topK = 5
    ): array {
        $start = microtime(true);

        $aiPayload = ['message' => $message, 'top_k' => $topK];
        if ($knowledgeBaseId) {
            $aiPayload['knowledge_base_id'] = $knowledgeBaseId;
        }

        try {
            $result = $this->ai->chat(
                $message,
                $knowledgeBaseId,
                $topK
            );
        } catch (\Throwable $e) {
            $latencyMs = (int) ((microtime(true) - $start) * 1000);

            AiRun::create([
                'conversation_id' => $conversation->id,
                'message_id' => $userMessage->id,
                'operation' => 'chat',
                'latency_ms' => $latencyMs,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'metadata' => ['payload' => $aiPayload],
            ]);

            throw $e;
        }

        $latencyMs = (int) ((microtime(true) - $start) * 1000);

        $replyText = $result['response']
            ?? $result['answer']
            ?? $result['message']
            ?? '';

        $sources = $result['sources'] ?? [];
        $model = $result['model'] ?? null;

        $assistantMessage = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => null,
            'sender_type' => 'ai',
            'content' => $replyText,
            'metadata' => [
                'model' => $model,
                'sources' => $sources,
            ],
            'response_time_ms' => $latencyMs,
        ]);

        $this->storeSources($assistantMessage, $sources);

        AiRun::create([
            'conversation_id' => $conversation->id,
            'message_id' => $assistantMessage->id,
            'provider' => 'fastapi',
            'model' => 'fastapi',
            'operation' => 'chat',
            'retrieval_count' => is_array($sources) ? count($sources) : 0,
            'latency_ms' => $latencyMs,
            'status' => 'success',
            'metadata' => [
                'fastapi_response' => $result,
            ],
        ]);

        $this->touchConversation($conversation);

        return [
            'reply' => $replyText,
            'sources' => $sources,
            'model' => $model,
            'conversation_id' => $conversation->id,
            'user_message_id' => $userMessage->id,
            'assistant_message_id' => $assistantMessage->id,
            'latency_ms' => $latencyMs,
        ];
    }

    private function storeSources(Message $message, array $sources): void
    {
        if (!is_array($sources)) {
            return;
        }

        $rows = [];
        foreach (array_values($sources) as $rank => $source) {
            $rows[] = [
                'message_id' => $message->id,
                'similarity_score' => isset($source['score']) ? (float) $source['score'] : null,
                'rank' => $rank,
                'metadata' => json_encode([
                    'document_id' => $source['document_id'] ?? null,
                    'filename' => $source['filename'] ?? null,
                    'chunk_index' => $source['chunk_index'] ?? null,
                    'text' => $source['text'] ?? null,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($rows) {
            MessageSource::insert($rows);
        }
    }

    private function touchConversation(Conversation $conversation): void
    {
        $conversation->forceFill([
            'last_message_at' => now(),
        ])->save();
    }
}