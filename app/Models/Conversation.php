<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'knowledge_base_id',
        'user_id',
        'assigned_agent_id',
        'session_id',
        'title',
        'status',
        'is_ai_enabled',
        'last_message_at',
        'closed_at',
    ];

    protected $casts = [
        'is_ai_enabled' => 'boolean',
        'last_message_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function knowledgeBase(): BelongsTo
    {
        return $this->belongsTo(KnowledgeBase::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'assigned_agent_id'
        );
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function aiRuns(): HasMany
    {
        return $this->hasMany(AiRun::class);
    }

    public function ticket()
    {
        return $this->hasOne(SupportTicket::class);
    }
}