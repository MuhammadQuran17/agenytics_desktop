<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
