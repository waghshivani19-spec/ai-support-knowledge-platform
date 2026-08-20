<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKnowledgeBaseRequest;
use App\Http\Requests\UpdateKnowledgeBaseRequest;
use App\Http\Resources\KnowledgeBaseResource;
use App\Models\KnowledgeBase;
use App\Services\KnowledgeBaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class KnowledgeBaseController extends Controller
{
    public function __construct(
        private readonly KnowledgeBaseService $service
    ) {
    }

    public function index(
        Request $request
    ): AnonymousResourceCollection {
        $this->authorize(
            'viewAny',
            KnowledgeBase::class
        );

        $knowledgeBases = KnowledgeBase::query()
            ->with('creator')
            ->withCount('documents')
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = $request->string('search');

                    $query->where(function ($query) use ($search) {
                        $query->where(
                            'name',
                            'like',
                            "%{$search}%"
                        )->orWhere(
                            'description',
                            'like',
                            "%{$search}%"
                        );
                    });
                }
            )
            ->when(
                $request->has('is_active'),
                fn ($query) =>
                    $query->where(
                        'is_active',
                        $request->boolean('is_active')
                    )
            )
            ->latest()
            ->paginate(
                $request->integer('per_page', 15)
            );

        return KnowledgeBaseResource::collection(
            $knowledgeBases
        );
    }

    public function store(
        StoreKnowledgeBaseRequest $request
    ): JsonResponse {
        $knowledgeBase = $this->service->create(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' =>
                'Knowledge base created successfully.',
            'data' => new KnowledgeBaseResource(
                $knowledgeBase->load('creator')
            ),
        ], 201);
    }

    public function show(
        KnowledgeBase $knowledgeBase
    ): KnowledgeBaseResource {
        $this->authorize(
            'view',
            $knowledgeBase
        );

        $knowledgeBase->load('creator')
            ->loadCount('documents');

        return new KnowledgeBaseResource(
            $knowledgeBase
        );
    }

    public function update(
        UpdateKnowledgeBaseRequest $request,
        KnowledgeBase $knowledgeBase
    ): JsonResponse {
        $knowledgeBase = $this->service->update(
            $knowledgeBase,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' =>
                'Knowledge base updated successfully.',
            'data' => new KnowledgeBaseResource(
                $knowledgeBase->load('creator')
            ),
        ]);
    }

    public function destroy(
        KnowledgeBase $knowledgeBase
    ): JsonResponse {
        $this->authorize(
            'delete',
            $knowledgeBase
        );

        $this->service->delete(
            $knowledgeBase
        );

        return response()->json([
            'success' => true,
            'message' =>
                'Knowledge base deleted successfully.',
        ]);
    }
}