<?php

namespace App\Services\SSO;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as ProviderUser;
use Laravel\Socialite\Facades\Socialite;

abstract class AbstractSSOService
{
    abstract protected function driver(): string;

    protected function findOrCreateUser(ProviderUser $providerUser, string $providerField): User
    {
        $user = User::where('email', $providerUser->getEmail())->first();

        if (! $user) {
            $user = User::create([
                $providerField => $providerUser->getId(),
                'name' => $providerUser->getName(),
                'email' => $providerUser->getEmail(),
                'password' => Str::password(12),
                'email_verified_at' => now(),
            ]);
        }

        return $user;
    }

    public function redirect()
    {
        return Socialite::driver($this->driver())->redirect();
    }

    public function callback()
    {
        $providerUser = Socialite::driver($this->driver())->user();
        $providerField = $this->driver().'_id';

        $user = $this->findOrCreateUser($providerUser, $providerField);
        Auth::login($user);

        return redirect()->intended();
    }
}
