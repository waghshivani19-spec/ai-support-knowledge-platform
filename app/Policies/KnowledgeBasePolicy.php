<?php

namespace App\Policies;

use App\Models\KnowledgeBase;
use App\Models\User;

class KnowledgeBasePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array(
            $user->role,
            ['admin', 'agent'],
            true
        );
    }

    public function view(
        User $user,
        KnowledgeBase $knowledgeBase
    ): bool {
        return in_array(
            $user->role,
            ['admin', 'agent'],
            true
        );
    }

    public function create(User $user): bool
    {
        return in_array(
            $user->role,
            ['admin', 'agent'],
            true
        );
    }

    public function update(
        User $user,
        KnowledgeBase $knowledgeBase
    ): bool {
        return $user->isAdmin()
            || $knowledgeBase->created_by === $user->id;
    }

    public function delete(
        User $user,
        KnowledgeBase $knowledgeBase
    ): bool {
        return $user->isAdmin()
            || $knowledgeBase->created_by === $user->id;
    }
}