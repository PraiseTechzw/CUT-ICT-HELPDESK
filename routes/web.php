<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AuditEventController;
use App\Http\Controllers\Admin\BuildingController;
use App\Http\Controllers\Admin\CampusController;
use App\Http\Controllers\Admin\OperationalZoneController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\Admin\TeamMembershipController;
use App\Http\Controllers\Admin\TechnicianDeploymentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketCatalogueController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check()
    ? redirect()->route('dashboard')
    : redirect()->route('login'))
    ->name('root');

Route::middleware(['auth', 'account.active'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [TicketController::class, 'create'])->middleware('can:tickets.create')->name('tickets.create');
    Route::post('/tickets', [TicketController::class, 'store'])->middleware('can:tickets.create')->name('tickets.store');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{ticket}/comments', [TicketController::class, 'storeComment'])->name('tickets.comments.store');
    Route::patch('/tickets/{ticket}/assignment', [TicketController::class, 'assign'])->name('tickets.assignment.update');
    Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.status.update');
    Route::post('/tickets/{ticket}/reopen', [TicketController::class, 'reopen'])->name('tickets.reopen');

    Route::prefix('admin/ticket-catalogues')->name('admin.ticket-catalogues.')
        ->middleware('can:tickets.catalogues.manage')
        ->group(function (): void {
            Route::get('/', [TicketCatalogueController::class, 'index'])->name('index');
            Route::post('/categories', [TicketCatalogueController::class, 'storeCategory'])->name('categories.store');
            Route::patch('/categories/{category}', [TicketCatalogueController::class, 'updateCategory'])->name('categories.update');
            Route::post('/priorities', [TicketCatalogueController::class, 'storePriority'])->name('priorities.store');
            Route::patch('/priorities/{priority}', [TicketCatalogueController::class, 'updatePriority'])->name('priorities.update');
        });

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

    Route::prefix('admin')->name('admin.')->middleware('can:teams.manage')->group(function (): void {
        Route::resource('teams', TeamController::class)->except(['show', 'destroy']);
        Route::resource('team-memberships', TeamMembershipController::class)
            ->except(['show', 'destroy'])
            ->parameters(['team-memberships' => 'membership']);
        Route::resource('technician-deployments', TechnicianDeploymentController::class)
            ->except(['show', 'destroy'])
            ->parameters(['technician-deployments' => 'deployment']);
    });
});
