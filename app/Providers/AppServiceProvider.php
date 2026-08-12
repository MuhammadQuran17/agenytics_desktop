<?php

namespace App\Providers;

use App\Services\AiChat\Contracts\MessageProcessingContract;
use App\Services\AiChat\DefaultMessageProcessing;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application Services.
     */
    public function register(): void
    {
        $this->app->bind(MessageProcessingContract::class, DefaultMessageProcessing::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

    }
}
