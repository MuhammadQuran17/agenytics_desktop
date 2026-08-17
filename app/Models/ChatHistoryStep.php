<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatHistoryStep extends Model
{
    protected $fillable = [
        'chat_history_id',
        'message',
        'status',
    ];

    public function chatHistory(): BelongsTo
    {
        return $this->belongsTo(ChatHistory::class);
    }
}
