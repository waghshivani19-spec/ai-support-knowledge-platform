<?php

namespace App\Http\Controllers;

use App\Services\AIService;
use Illuminate\Http\JsonResponse;

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
}