<?php

namespace App\Providers;

use App\Models\PersonalAccessToken;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\EloquentUserRepository;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        Queue::route([
            'App\\Jobs\\ProcessResumeUpload' => 'files',
            'App\\Jobs\\ProcessCompanyLogoUpload' => 'files',
            'App\\Jobs\\NotifyEmployerOfApplication' => 'notifications',
            'App\\Jobs\\StoreApplicationStatusNotification' => 'notifications',
            'App\\Jobs\\BroadcastApplicationStatusChanged' => 'broadcasts',
            'App\\Jobs\\WarmJobListingCache' => 'cache',
        ]);
    }
}
