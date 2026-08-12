<?php

namespace App\Services\AiChat;

use App\Models\UserChat;
use Illuminate\Support\Facades\Auth;

class AiChatService
{
    // [Tested manually]
    public function getLatestOrCreateNewUserChat(): UserChat
    {
        if ($latestUserChat = $this->getLatestUserChat()) {
            return $latestUserChat;
        }

        return Auth::user()->chats()->create();
    }

    // [Tested manually]
    private function getLatestUserChat(): ?UserChat
    {
        return Auth::user()->chats()->latest()->first();
    }

    /**
     * if null returns false. We check for 2 records: 1st is user and 2nd is ai response
     * [Tested manually]
     */
    public function hasUserLatestActiveChat(): bool
    {
        return $this->getLatestUserChat()?->chatHistories()->count() >= 1 ?? false;
    }

}
