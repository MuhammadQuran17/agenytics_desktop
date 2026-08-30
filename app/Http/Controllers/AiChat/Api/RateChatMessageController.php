<?php

namespace App\Http\Controllers\AiChat\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RateChatMessageController extends Controller
{
    public function __invoke(Request $request, string $jobId): JsonResponse
    {
        $validated = $request->validate([
            'rating' => 'required|in:good,bad',
        ]);

        $chatHistory = ChatHistory::where('job_id', $jobId)->where('role', 'assistant')->firstOrFail();

        abort_unless($chatHistory->userChat->user_id === $request->user()->id, 403);

        // Clicking the same rating again clears it, matching a toggle button.
        $chatHistory->rating = $chatHistory->rating === $validated['rating'] ? null : $validated['rating'];
        $chatHistory->save();

        return response()->json(['rating' => $chatHistory->rating]);
    }
}
