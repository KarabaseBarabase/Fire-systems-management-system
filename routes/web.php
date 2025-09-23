<?php

use App\Http\Controllers\Custom\FireSystemController;
use App\Http\Controllers\Custom\AuthController;
use App\Http\Controllers\Custom\AnalyticsController;
use App\Http\Controllers\DashboardController;
use App\Http\Middleware\AuthMiddleware;

// Главная страница
Route::get('/', [DashboardController::class, 'index'])->name('home');

// Маршруты аутентификации
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Защищенные маршруты с middleware
//Route::middleware(['authenticated'])->group(function () {
Route::get('/systems', [FireSystemController::class, 'list']);
Route::get('/systems/{id}', [FireSystemController::class, 'show']);
Route::delete('/systems/{uuid}', [FireSystemController::class, 'destroy'])->name('system.destroy');
Route::get('/analytics', [AnalyticsController::class, 'index']);
//});

// Защищенные маршруты dashboard 
Route::middleware([AuthMiddleware::class])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/modal/{id}', [DashboardController::class, 'modal'])->name('modal');
});

Route::get('/system/{id}', [FireSystemController::class, 'show'])->name('system.show');
