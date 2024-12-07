<?php

namespace App\Providers;

use App\Repositories\Contracts\OTPRepositoryInterface;
use App\Repositories\Contracts\AuthRepositoryInterface;
use App\Repositories\OTPRepository;
use App\Repositories\AuthRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\StoreRepositoryInterface;
use App\Repositories\ProductRepository;
use App\Repositories\StoreRepository;
use App\Services\OTPService;
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
        $this->app->bind(StoreRepositoryInterface::class, StoreRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
