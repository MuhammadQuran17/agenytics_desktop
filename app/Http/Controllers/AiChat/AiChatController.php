<?php

namespace App\Http\Controllers\AiChat;

use App\DTO\ChatMessageDTO;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserChat;
use App\Services\AiChat\AiChatService;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class AiChatController extends Controller
{
    /**
     * Route name : chat.index
     * Route path : /chat/{userChat?}
     */
    public function index(UserChat $userChat, AiChatService $chatService)
    {
        // if userChat was provided then show that one, otherwise return latest one if it has more than 2 records, otherwise create a new one
        $currentChat = $userChat->exists ? $userChat : $chatService->getLatestOrCreateNewUserChat();

        // Get chat history and transform to DTO for frontend
        $chatHistory = ChatMessageDTO::fromCollection(
            $currentChat->chatHistories()->orderBy('created_at')->get()
        );

        return Inertia::render('AiChat', [
            'currentChatSessionId' => $currentChat->session_id,
            'chatHistory' => $chatHistory,
        ]);
    }

    public function create(AiChatService $chatService, #[CurrentUser] User $user)
    {
        if ($chatService->hasUserLatestActiveChat()) {
            return redirect()->route('chat.index', $user->chats()->create()->session_id);
        }

        return redirect()->route('chat.index');
    }

    public function destroy(UserChat $userChat): RedirectResponse
    {
        Gate::authorize('delete', $userChat);

        $userChat->delete();

        return redirect()->route('chat.index');
    }
}
