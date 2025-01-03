<?php

namespace App\Policies;

use App\Models\ChatLog;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ChatLogPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny($user)
    {
        return in_array($user->permission_group, ['admin', 'superadmin', 'developer']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ChatLog $chatLog): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ChatLog $chatLog): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete($user, ChatLog $chatLog)
    {
        return in_array($user->permission_group, ['admin', 'superadmin', 'developer']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ChatLog $chatLog): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ChatLog $chatLog): bool
    {
        return false;
    }
}
