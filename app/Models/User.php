<?php

namespace App\Models;

use App\Notifications\SendVerificationEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasUuids, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'github_id',
        'email_verified_at',
        'free_prompts',
        'purchased_prompts',
        'purchased_prompts_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'purchased_prompts_expires_at' => 'datetime',
        ];
    }

    public function chats(): HasMany
    {
        return $this->hasMany(UserChat::class);
    }

    public function isPurchasedPromptsNotExpired(): bool
    {
        if ($this->purchased_prompts_expires_at === null) {
            return true;
        }

        return $this->purchased_prompts_expires_at->isFuture();
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new SendVerificationEmail);
    }
}
