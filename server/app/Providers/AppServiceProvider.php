<?php

namespace App\Providers;

use App\Events\ApplicationStatusChanged;
use App\Listeners\BroadcastApplicationStatusChanged;
use App\Models\PersonalAccessToken;
use App\Repositories\Contracts\ApplicationRepositoryInterface;
use App\Repositories\Contracts\EmployerProfileRepositoryInterface;
use App\Repositories\Contracts\JobListingRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\EloquentApplicationRepository;
use App\Repositories\EloquentEmployerProfileRepository;
use App\Repositories\EloquentJobListingRepository;
use App\Repositories\EloquentUserRepository;
use Illuminate\Support\Facades\Event;
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
        $this->app->bind(EmployerProfileRepositoryInterface::class, EloquentEmployerProfileRepository::class);
        $this->app->bind(JobListingRepositoryInterface::class, EloquentJobListingRepository::class);
        $this->app->bind(ApplicationRepositoryInterface::class, EloquentApplicationRepository::class);
        $this->app->bind(\App\Repositories\Contracts\CandidateProfileRepositoryInterface::class, \App\Repositories\EloquentCandidateProfileRepository::class);
        $this->app->bind(\App\Repositories\Contracts\ResumeRepositoryInterface::class, \App\Repositories\EloquentResumeRepository::class);
        $this->app->bind(\App\Repositories\Contracts\NotificationRepositoryInterface::class, \App\Repositories\EloquentNotificationRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        config(['logging.default' => 'stderr']);
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
        Event::listen(ApplicationStatusChanged::class, BroadcastApplicationStatusChanged::class);

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
