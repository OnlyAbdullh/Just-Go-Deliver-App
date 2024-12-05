<?php

namespace App\Providers;

use App\Repositories\Contracts\OTPRepositoryInterface;
use App\Repositories\Contracts\AuthRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\OTPRepository;
use App\Repositories\AuthRepository;
use App\Repositories\Contracts\StoreRepositoryInterface;
use App\Repositories\StoreRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind the AuthRepositoryInterface to AuthRepository
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(OTPRepositoryInterface::class, OTPRepository::class);
        $this->app->bind(StoreRepositoryInterface::class,StoreRepository::class);
        $this->app->bind(UserRepositoryInterface::class,UserRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
