<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKnowledgeDocumentRequest;
use App\Http\Resources\KnowledgeDocumentResource;
use App\Models\KnowledgeBase;
use App\Models\KnowledgeDocument;
use App\Services\KnowledgeDocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class KnowledgeDocumentController extends Controller
{
    public function __construct(
        private readonly KnowledgeDocumentService $service
    ) {
    }

    public function index(
        Request $request,
        KnowledgeBase $knowledgeBase
    ): AnonymousResourceCollection {
        $this->authorize(
            'view',
            $knowledgeBase
        );

        $documents = $knowledgeBase
            ->documents()
            ->with('uploader')
            ->latest()
            ->paginate(
                $request->integer(
                    'per_page',
                    15
                )
            );

        return KnowledgeDocumentResource::collection(
            $documents
        );
    }

    public function store(
        StoreKnowledgeDocumentRequest $request,
        KnowledgeBase $knowledgeBase
    ): JsonResponse {
        $this->authorize(
            'create',
            KnowledgeDocument::class
        );

        $document = $this->service->upload(
            $request->user(),
            $knowledgeBase,
            $request->file('file'),
            $request->input('title')
        );

        return response()->json([
            'success' => true,

            'message' =>
                'Document uploaded and queued for processing.',

            'data' =>
                new KnowledgeDocumentResource(
                    $document->load('uploader')
                ),
        ], 201);
    }

    public function show(
        KnowledgeBase $knowledgeBase,
        KnowledgeDocument $document
    ): KnowledgeDocumentResource {
        $this->authorize(
            'view',
            $document
        );

        $this->ensureDocumentBelongsToKnowledgeBase(
            $knowledgeBase,
            $document
        );

        return new KnowledgeDocumentResource(
            $document->load('uploader')
        );
    }

    public function destroy(
        KnowledgeBase $knowledgeBase,
        KnowledgeDocument $document
    ): JsonResponse {
        $this->authorize(
            'delete',
            $document
        );

        $this->ensureDocumentBelongsToKnowledgeBase(
            $knowledgeBase,
            $document
        );

        $this->service->delete(
            $document
        );

        return response()->json([
            'success' => true,

            'message' =>
                'Document deleted successfully.',
        ]);
    }

    private function ensureDocumentBelongsToKnowledgeBase(
        KnowledgeBase $knowledgeBase,
        KnowledgeDocument $document
    ): void {
        abort_unless(
            $document->knowledge_base_id
                === $knowledgeBase->id,
            404
        );
    }
}