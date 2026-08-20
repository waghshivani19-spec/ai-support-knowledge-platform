<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentChunk extends Model
{
    use HasFactory;

    protected $fillable = [
        'knowledge_document_id',
        'chunk_index',
        'content',
        'token_count',
        'vector_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'chunk_index' => 'integer',
        'token_count' => 'integer',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(
            KnowledgeDocument::class,
            'knowledge_document_id'
        );
    }

    public function messageSources(): HasMany
    {
        return $this->hasMany(MessageSource::class);
    }
}