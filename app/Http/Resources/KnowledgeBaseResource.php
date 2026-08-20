<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KnowledgeBaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name' => $this->name,

            'slug' => $this->slug,

            'description' => $this->description,

            'embedding' => [
                'provider' => $this->embedding_provider,
                'model' => $this->embedding_model,
            ],

            'chunking' => [
                'size' => $this->chunk_size,
                'overlap' => $this->chunk_overlap,
            ],

            'is_active' => $this->is_active,

            'creator' => $this->whenLoaded(
                'creator',
                fn () => [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                ]
            ),

            'documents_count' => $this->whenCounted(
                'documents'
            ),

            'created_at' => $this->created_at?->toISOString(),

            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}