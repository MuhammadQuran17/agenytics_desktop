<?php

namespace App\DTO;

use App\Models\ChatHistory;
use Illuminate\Support\Collection;

class ChatMessageDTO
{
    /**
     * Transform a ChatHistory model to a Message DTO for frontend consumption
     */
    public static function fromModel(ChatHistory $message): array
    {
        if ($message->role === 'user') {
            return [
                'content' => $message->user_input,
                'role' => 'user',
                'created_at' => $message->created_at->toIso8601String(),
                'jobId' => $message->job_id,
            ];
        }

        return [
            'content' => $message->message,
            'role' => 'assistant',
            'created_at' => $message->created_at->toIso8601String(),
            'steps' => $message->steps->map->only(['message', 'status'])->all(),
            'jobId' => $message->job_id,
            'rating' => $message->rating,
            // A failed turn has no content to render, so the frontend needs
            // to know it failed (and why) to show that instead of a blank
            // reply - otherwise it silently vanishes the moment the app
            // restarts and the in-memory "Retry" banner is gone with it.
            'jobStatus' => $message->job_status,
            'error' => $message->error,
        ];
    }

    /**
     * Transform a collection of ChatHistory models to Message DTOs
     */
    public static function fromCollection(Collection $messages): Collection
    {
        return $messages->map(fn (ChatHistory $message) => self::fromModel($message));
    }
}
