<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{

    use HasApiTokens, HasFactory, Notifiable;

protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function knowledgeBases(): HasMany
    {
        return $this->hasMany(
            KnowledgeBase::class,
            'created_by'
        );
    }

    public function uploadedDocuments(): HasMany
    {
        return $this->hasMany(
            KnowledgeDocument::class,
            'uploaded_by'
        );
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function assignedConversations(): HasMany
    {
        return $this->hasMany(
            Conversation::class,
            'assigned_agent_id'
        );
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(
            SupportTicket::class,
            'customer_id'
        );
    }

    public function assignedTickets(): HasMany
    {
        return $this->hasMany(
            SupportTicket::class,
            'assigned_agent_id'
        );
    }



}