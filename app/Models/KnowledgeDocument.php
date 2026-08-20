<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgeDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'knowledge_base_id',
        'uploaded_by',
        'title',
        'original_filename',
        'file_path',
        'mime_type',
        'file_size',
        'file_hash',
        'source_type',
        'source_url',
        'status',
        'processing_error',
        'chunk_count',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'file_size' => 'integer',
        'chunk_count' => 'integer',
    ];

    public function knowledgeBase(): BelongsTo
    {
        return $this->belongsTo(KnowledgeBase::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function chunks(): HasMany
    {
        return $this->hasMany(DocumentChunk::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isProcessed(): bool
    {
        return $this->status === 'processed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}