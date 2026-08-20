<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'document_chunk_id',
        'similarity_score',
        'rank',
    ];

    protected $casts = [
        'similarity_score' => 'float',
        'rank' => 'integer',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function documentChunk(): BelongsTo
    {
        return $this->belongsTo(DocumentChunk::class);
    }
}