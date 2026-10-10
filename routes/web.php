<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AuditEventController;
use App\Http\Controllers\Admin\BuildingController;
use App\Http\Controllers\Admin\CampusController;
use App\Http\Controllers\Admin\OperationalZoneController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check()
    ? redirect()->route('dashboard')
    : redirect()->route('login'))
    ->name('root');

Route::middleware(['auth', 'account.active'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function (): void {
        Route::resource('users', UserController::class)->except(['destroy']);
        Route::put('users/{user}/roles', [UserRoleController::class, 'update'])->name('users.roles.update');
        Route::patch('users/{user}/status', [UserController::class, 'updateStatus'])->name('users.status');
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::resource('campuses', CampusController::class)->except(['show', 'destroy']);
        Route::resource('zones', OperationalZoneController::class)->except(['show', 'destroy']);
        Route::resource('buildings', BuildingController::class)->except(['show', 'destroy']);
        Route::resource('rooms', RoomController::class)->except(['show', 'destroy']);
        Route::get('audit', [AuditEventController::class, 'index'])->name('audit.index');
    });
});
