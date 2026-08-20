<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'message_id',
        'provider',
        'model',
        'operation',
        'input_tokens',
        'output_tokens',
        'total_tokens',
        'retrieval_count',
        'latency_ms',
        'temperature',
        'estimated_cost',
        'status',
        'error_message',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'temperature' => 'float',
        'estimated_cost' => 'float',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }
}