<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * FeedbackVote model represents a user's vote on a feedback
 *
 * @property string $id
 * @property string $feedback_id Foreign key to feedbacks table
 * @property string $user_id Foreign key to users table
 */
class FeedbackVote extends Model
{
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['feedback_id', 'user_id'];

    /**
     * Get the user that cast the vote
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the feedback that was voted on
     */
    public function feedback(): BelongsTo
    {
        return $this->belongsTo(Feedback::class);
    }
}
