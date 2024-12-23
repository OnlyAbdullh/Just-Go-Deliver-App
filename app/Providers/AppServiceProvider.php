<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
                if (app()->environment('local', 'staging')) {
                    DB::listen(function ($query) {
                        if ($query->time > 2) {
                          //  Log::info('hEREEEEEE');
                            Log::warning('Slow Query Detected', [
                                'sql' => $query->sql,
                                'bindings' => $query->bindings,
                                'time' => $query->time,
                            ]);
                        }
                    });
                }*/
    }
}
