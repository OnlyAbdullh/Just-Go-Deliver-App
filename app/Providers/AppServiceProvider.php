<?php

namespace App\Providers;

use App\Repositories\Contracts\OTPRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\OTPRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind the UserRepositoryInterface to UserRepository
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(OTPRepositoryInterface::class, OTPRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
