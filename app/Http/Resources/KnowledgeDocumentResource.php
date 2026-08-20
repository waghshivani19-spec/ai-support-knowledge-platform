<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KnowledgeDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'knowledge_base_id' =>
                $this->knowledge_base_id,

            'title' => $this->title,

            'original_filename' =>
                $this->original_filename,

            'mime_type' => $this->mime_type,

            'file_size' => $this->file_size,

            'file_size_human' =>
                $this->formatFileSize($this->file_size),

            'source_type' => $this->source_type,

            'source_url' => $this->source_url,

            'status' => $this->status,

            'processing_error' =>
                $this->processing_error,

            'chunk_count' => $this->chunk_count,

            'processed_at' =>
                $this->processed_at?->toISOString(),

            'uploader' => $this->whenLoaded(
                'uploader',
                fn () => [
                    'id' => $this->uploader->id,
                    'name' => $this->uploader->name,
                ]
            ),

            'created_at' =>
                $this->created_at?->toISOString(),

            'updated_at' =>
                $this->updated_at?->toISOString(),
        ];
    }

    private function formatFileSize(?int $bytes): ?string
    {
        if ($bytes === null) {
            return null;
        }

        if ($bytes >= 1024 * 1024) {
            return round(
                $bytes / (1024 * 1024),
                2
            ) . ' MB';
        }

        if ($bytes >= 1024) {
            return round(
                $bytes / 1024,
                2
            ) . ' KB';
        }

        return $bytes . ' bytes';
    }
}