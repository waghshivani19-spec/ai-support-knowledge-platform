<?php

namespace App\Jobs;

use App\Models\KnowledgeDocument;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessKnowledgeDocument implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(
        public int $documentId
    ) {
    }

    public function handle(): void
    {
        $document = KnowledgeDocument::find(
            $this->documentId
        );

        if (!$document) {
            return;
        }

        $document->update([
            'status' => 'processing',
            'processing_error' => null,
        ]);

        try {
            /*
             * Python FastAPI integration
             * will be added in the next phase.
             */

            Log::info(
                'Knowledge document processing started.',
                [
                    'document_id' => $document->id,
                ]
            );

        } catch (\Throwable $exception) {

            $document->update([
                'status' => 'failed',
                'processing_error' =>
                    $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function failed(
        \Throwable $exception
    ): void {
        KnowledgeDocument::where(
            'id',
            $this->documentId
        )->update([
            'status' => 'failed',
            'processing_error' =>
                $exception->getMessage(),
        ]);
    }
}