<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserChat;

class UserChatPolicy
{
    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, UserChat $userChat): bool
    {
        return $user->id === $userChat->user_id;
    }
}
