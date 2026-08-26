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
}