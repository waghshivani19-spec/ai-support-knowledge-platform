<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AIService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(
            config('services.ai_service.url'),
            '/'
        );
    }

    public function testConnection(): array
    {
        $response = Http::timeout(10)
            ->get($this->baseUrl . '/api/test');

        $response->throw();

        return $response->json();
    }

    public function chat(
        string $message,
        ?int $knowledgeBaseId = null,
        int $topK = 5
    ): array {
        $payload = [
            'message' => $message,
        ];

        if ($knowledgeBaseId) {
            $payload['knowledge_base_id'] = $knowledgeBaseId;
        }

        $payload['top_k'] = $topK;

        $response = Http::timeout(60)
            ->acceptJson()
            ->asJson()
            ->post($this->baseUrl . '/api/ai/chat', $payload);

        $response->throw();

        return $response->json();
    }

    public function askKnowledgeBase(
        string $question,
        int $topK = 5
    ): array {
        $response = Http::timeout(60)
            ->acceptJson()
            ->asJson()
            ->post($this->baseUrl . '/api/knowledge/ask', [
                'question' => $question,
                'top_k' => $topK,
            ]);

        $response->throw();

        return $response->json();
    }

    public function listKnowledgeBases(): array
    {
        $response = Http::timeout(10)
            ->get($this->baseUrl . '/api/knowledge/bases');

        $response->throw();

        return $response->json();
    }
}