<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivityLogController;
// use App\Http\Controllers\ProfileController; // Uncomment if you have this controller

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
| These routes are loaded by the RouteServiceProvider and all are
| assigned to the "web" middleware group.
|
*/

// Public welcome page (optional - can be removed if not needed)
Route::get('/', function () {
    return view('auth/login');
})->name('auth/login');

// Authentication routes (login, logout, etc.)
require __DIR__.'/auth.php';

// Protected routes - all require authentication
Route::middleware('auth')->group(function () {

    // ==============================================
    // Main Dashboard (View-Only - accessible to all)
    // ==============================================
    Route::get('/dashboard', [DashboardController::class, 'main'])
        ->name('dashboard.main');

    // ==============================================
    // Department-specific Dashboards
    // These are the post-login landing pages based on user department
    // ==============================================
    Route::prefix('dashboard')->group(function () {
        Route::get('sales', [DashboardController::class, 'sales'])
            ->name('dashboard.sales');

        Route::get('production', [DashboardController::class, 'production'])
            ->name('dashboard.production');

        Route::get('inventory', [DashboardController::class, 'inventory'])
            ->name('dashboard.inventory');

        Route::get('logistics', [DashboardController::class, 'logistics'])
            ->name('dashboard.logistics');
    });

    // ==============================================
    // Admin-only Routes
    // ==============================================
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        // User Management (full CRUD ready for future expansion)
        Route::resource('users', UserController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

        // Activity Logs (view-only for now)
        Route::get('activity-logs', [ActivityLogController::class, 'index'])
            ->name('activity-logs.index');

        // You can easily add more admin sections later, e.g.:
        // Route::resource('products', ProductController::class);
        // Route::get('settings', [SettingsController::class, 'index'])->name('settings');
    });

    // ==============================================
    // User Profile Routes (optional - if you want to keep them)
    // ==============================================
    // Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    // Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});