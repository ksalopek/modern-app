<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NotePolicy
{
    /**
     * Perform pre-authorization checks.
     * This method is called before any other policy method.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isAdmin()) {
            return true; // Admins can do anything
        }

        return null; // Let the policy method handle authorization
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // The 'before' method handles admin access.
        // Regular users can view their own notes, which is filtered in the controller.
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Note $note): bool
    {
        // The 'before' method handles admin access.
        return $user->id === $note->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // The 'before' method handles admin access.
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Note $note): bool
    {
        // The 'before' method handles admin access.
        return $user->id === $note->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Note $note): bool
    {
        // The 'before' method handles admin access.
        return $user->id === $note->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Note $note): bool
    {
        // The 'before' method handles admin access.
        return $user->id === $note->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Note $note): bool
    {
        // The 'before' method handles admin access.
        return $user->id === $note->user_id;
    }
}
