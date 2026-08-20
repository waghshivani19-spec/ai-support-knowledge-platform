<?php

namespace App\Services;

use App\Models\KnowledgeBase;
use App\Models\User;
use Illuminate\Support\Str;

class KnowledgeBaseService
{
    public function create(
        User $user,
        array $data
    ): KnowledgeBase {
        $data['created_by'] = $user->id;

        $data['slug'] = $this->generateUniqueSlug(
            $data['name']
        );

        $data['embedding_provider']
            = $data['embedding_provider']
                ?? config(
                    'ai.embedding.provider',
                    'openai'
                );

        $data['embedding_model']
            = $data['embedding_model']
                ?? config(
                    'ai.embedding.model',
                    'text-embedding-3-small'
                );

        $data['chunk_size']
            = $data['chunk_size'] ?? 1000;

        $data['chunk_overlap']
            = $data['chunk_overlap'] ?? 200;

        $data['is_active']
            = $data['is_active'] ?? true;

        $this->validateChunkSettings(
            $data['chunk_size'],
            $data['chunk_overlap']
        );

        return KnowledgeBase::create($data);
    }

    public function update(
        KnowledgeBase $knowledgeBase,
        array $data
    ): KnowledgeBase {
        if (isset($data['name'])) {
            $data['slug'] = $this->generateUniqueSlug(
                $data['name'],
                $knowledgeBase->id
            );
        }

        $chunkSize = $data['chunk_size']
            ?? $knowledgeBase->chunk_size;

        $chunkOverlap = $data['chunk_overlap']
            ?? $knowledgeBase->chunk_overlap;

        $this->validateChunkSettings(
            $chunkSize,
            $chunkOverlap
        );

        $knowledgeBase->update($data);

        return $knowledgeBase->refresh();
    }

    public function delete(
        KnowledgeBase $knowledgeBase
    ): void {
        $knowledgeBase->delete();
    }

    private function generateUniqueSlug(
        string $name,
        ?int $ignoreId = null
    ): string {
        $baseSlug = Str::slug($name);

        $slug = $baseSlug;
        $counter = 1;

        while (
            KnowledgeBase::where('slug', $slug)
                ->when(
                    $ignoreId,
                    fn ($query) =>
                        $query->where('id', '!=', $ignoreId)
                )
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function validateChunkSettings(
        int $chunkSize,
        int $chunkOverlap
    ): void {
        if ($chunkOverlap >= $chunkSize) {
            throw new \InvalidArgumentException(
                'Chunk overlap must be smaller than chunk size.'
            );
        }
    }
}