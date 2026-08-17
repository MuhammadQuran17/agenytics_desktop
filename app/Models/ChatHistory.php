<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatHistory extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_chat_session_id',
        'job_id',
        'job_status',
        'user_input',
        'role',
        'message',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'message' => 'json',
        ];
    }

    public function userChat(): BelongsTo
    {
        return $this->belongsTo(UserChat::class, 'user_chat_session_id', 'session_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ChatHistoryStep::class)->orderBy('created_at');
    }
}
