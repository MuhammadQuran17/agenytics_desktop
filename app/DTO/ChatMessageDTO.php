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
            ];
        }

        return [
            'content' => $message->message,
            'role' => 'assistant',
            'created_at' => $message->created_at->toIso8601String(),
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
