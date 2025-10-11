<?php

namespace App\Providers;

use App\Core\Database;
use App\Core\AuthInterface;
use App\Core\SessionAuth;
use Illuminate\Support\ServiceProvider;
use App\Services\{
    SystemManagementService,
    FireSystemService,
    ProtectionObjectService,
    EquipmentService,
    RegulationService
};

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

        $this->app->singleton('system.management', function ($app) {
            return new SystemManagementService(
                $app->make(FireSystemService::class),
                $app->make(ProtectionObjectService::class),
                $app->make(EquipmentService::class),
                $app->make(RegulationService::class)
            );
        });
    }

    public function boot()
    {
        //
    }
}