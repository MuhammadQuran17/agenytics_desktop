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

        // Order by turn (job_id), not by each row's own created_at: a slow-to-process
        // job's assistant row can otherwise get a later timestamp than a faster,
        // later-sent message's rows, making an old answer appear to jump out of order.
        $chatHistory = ChatMessageDTO::fromCollection(
            $currentChat->chatHistories()->with('steps')->orderBy('created_at')->get()
                ->groupBy('job_id')
                ->sortBy(fn ($rows) => $rows->min('created_at'))
                ->flatMap(fn ($rows) => $rows->sortBy(fn ($row) => $row->role === 'user' ? 0 : 1))
                ->values()
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
