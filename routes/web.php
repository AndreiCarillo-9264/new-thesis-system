<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\JobOrderController;
use App\Http\Controllers\FinishedGoodController;
use App\Http\Controllers\DistributionController;
use App\Http\Controllers\ActualInventoryController;
use App\Http\Controllers\InventoryTransferController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SalesController;

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

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
})->name('welcome');

// Authentication routes (login/logout from Breeze)
require __DIR__.'/auth.php';

// All routes below require authentication
Route::middleware('auth')->group(function () {

    // =============================================================
    // Main Dashboard – Viewable by ALL authenticated users
    // =============================================================
    Route::get('/dashboard', [DashboardController::class, 'main'])
        ->name('dashboard.main');

    // =============================================================
    // Department-specific Dashboards
    // All can VIEW, edit permissions handled by policies
    // =============================================================
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('sales',      [DashboardController::class, 'sales'])     ->name('sales');
        
        // NEW: Sales Report (accessible from Sales Dashboard)
        Route::get('sales/report', [DashboardController::class, 'salesReport'])
            ->name('sales.report');

        Route::get('production', [DashboardController::class, 'production'])->name('production');
        Route::get('inventory',  [DashboardController::class, 'inventory']) ->name('inventory');
        Route::get('logistics',  [DashboardController::class, 'logistics']) ->name('logistics');
    });

    // =============================================================
    // Products (mostly managed by Inventory/Admin)
    // =============================================================
    Route::resource('products', ProductController::class)
        ->names('products');

    // =============================================================
    // Job Orders – mainly Sales department
    // =============================================================
    Route::resource('job-orders', JobOrderController::class)
        ->names('job_orders');

    // =============================================================
    // Finished Goods – mainly Production department
    // =============================================================
    Route::resource('finished-goods', FinishedGoodController::class)
        ->names('finished_goods')
        ->parameters(['finished-goods' => 'finished_good']);

    // =============================================================
    // Distributions – Sales + Logistics
    // =============================================================
    Route::resource('distributions', DistributionController::class)
        ->names('distributions');

    // =============================================================
    // Actual Inventory – mainly Inventory department
    // Usually only index/show/edit/update (no mass create/destroy)
    // =============================================================
    Route::resource('actual-inventories', ActualInventoryController::class)
        ->names('actual_inventories')
        ->parameters(['actual-inventories' => 'actual_inventory'])
        ->only(['index', 'show', 'edit', 'update']);

    // =============================================================
    // Inventory Transfers – mainly Logistics / Inventory
    // =============================================================
    Route::resource('inventory-transfers', InventoryTransferController::class)
        ->names('inventory_transfers')
        ->parameters(['inventory-transfers' => 'inventory_transfer']);

    // =============================================================
    // Admin-only section (protected by 'admin' middleware)
    // =============================================================
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {

        // User Management
        Route::resource('users', UserController::class)
            ->names('users');

        // Activity Logs
        Route::get('activity-logs', [ActivityLogController::class, 'index'])
            ->name('activity-logs.index');

        // Optional: Admin full access overrides (uncomment if needed)
        // Route::resource('job-orders', JobOrderController::class)->names('admin.job_orders');
        // Route::resource('products', ProductController::class)->names('admin.products');
    });

    // =============================================================
    // Optional: User Profile (enable when you want to allow photo/name edit)
    // =============================================================
    // Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    // Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
   
    Route::prefix('sales')->group(function () {
        Route::get('/orders', [SalesController::class, 'index']);
        Route::post('/orders', [SalesController::class, 'store']);
        Route::get('/orders/search', [SalesController::class, 'search']);
        Route::post('/reports/generate', [SalesController::class, 'generateReport']);
    });
});