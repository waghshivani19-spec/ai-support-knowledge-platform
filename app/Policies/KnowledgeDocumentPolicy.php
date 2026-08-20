<?php

namespace App\Policies;

use App\Models\KnowledgeDocument;
use App\Models\User;

class KnowledgeDocumentPolicy
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
        KnowledgeDocument $document
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
        KnowledgeDocument $document
    ): bool {
        return $user->isAdmin()
            || $document->uploaded_by === $user->id;
    }

    public function delete(
        User $user,
        KnowledgeDocument $document
    ): bool {
        return $user->isAdmin()
            || $document->uploaded_by === $user->id;
    }
}