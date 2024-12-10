<?php

namespace App\Providers;

use App\Repositories\Contracts\AuthRepositoryInterface;
use App\Repositories\Contracts\FavoriteRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\AuthRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\StoreRepositoryInterface;
use App\Repositories\FavoriteRepository;
use App\Repositories\ProductRepository;
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
        $this->app->bind(StoreRepositoryInterface::class, StoreRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(StoreRepositoryInterface::class,StoreRepository::class);
        $this->app->bind(UserRepositoryInterface::class,UserRepository::class);
        $this->app->bind(FavoriteRepositoryInterface::class, FavoriteRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
