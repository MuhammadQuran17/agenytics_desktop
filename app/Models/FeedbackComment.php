<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * FeedbackComment model representing feedback comments
 *
 * @property string $id UUID of the comment
 * @property string $feedback_id Foreign key to feedback
 * @property string $user_id Foreign key to user
 * @property string $message The comment message
 */
class FeedbackComment extends Model
{
    use HasUuids;

    protected $fillable = ['feedback_id', 'user_id', 'message'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function feedback()
    {
        return $this->belongsTo(Feedback::class);
    }
}
