<?php

namespace App\Providers;

use App\Services\SSO\Contracts\SSOServiceInterface;
use App\Services\SSO\GithubSSOService;
use App\Services\SSO\GoogleSSOService;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class SSOServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(SSOServiceInterface::class, function ($app) {
            $provider = request()->route('provider');

            return match ($provider) {
                'google' => new GoogleSSOService,
                'github' => new GithubSSOService,
                default => throw new InvalidArgumentException("Unsupported SSO provider: $provider"),
            };
        });
    }
}
