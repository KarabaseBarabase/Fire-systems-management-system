<?php

namespace App\Providers;

use App\Core\Database;
use App\Core\AuthInterface;
use App\Core\SessionAuth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Регистрируем Database
        $this->app->singleton(Database::class, function ($app) {
            return new Database([
                'host' => env('DB_HOST'),
                'port' => env('DB_PORT'),
                'database' => env('DB_DATABASE'),
                'username' => env('DB_USERNAME'),
                'password' => env('DB_PASSWORD'),
                'persistent' => env('DB_PERSISTENT', false)
            ]);
        });

        // Регистрируем AuthInterface с SessionAuth
        $this->app->singleton(AuthInterface::class, function ($app) {
            return new SessionAuth(
                env('SESSION_NAME', 'app_session'),
                env('SESSION_LIFETIME', 3600)
            );
        });

        // Регистрируем репозитории
        $this->app->bind(\App\Data\Repositories\FireSystemRepository::class, function ($app) {
            return new \App\Data\Repositories\FireSystemRepository($app->make(Database::class));
        });
    }

    public function boot()
    {
        //
    }
}