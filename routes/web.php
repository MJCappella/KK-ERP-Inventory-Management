<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

// Role Switcher
Route::post('/switch-user/{user}', [AuthController::class, 'switchUser'])->name('switch-user');

// Authenticated ERP Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Default redirect to dashboard
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // POS & Sales
    Route::get('/sales/pos', [SaleController::class, 'pos'])->name('sales.pos');
    Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');

    // Inventory Management
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/receive', [InventoryController::class, 'receiveForm'])->name('inventory.receive.form');
    Route::post('/inventory/receive', [InventoryController::class, 'receive'])->name('inventory.receive');
    Route::get('/inventory/adjust', [InventoryController::class, 'adjustForm'])->name('inventory.adjust.form');
    Route::post('/inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
    Route::get('/api/stores/{store}/products/{product}/stock', [InventoryController::class, 'getStock'])->name('api.stock.get');

    // Inter-Store Transfers
    Route::get('/transfers', [TransferController::class, 'index'])->name('transfers.index');
    Route::get('/transfers/create', [TransferController::class, 'create'])->name('transfers.create');
    Route::post('/transfers', [TransferController::class, 'store'])->name('transfers.store');
    Route::get('/transfers/{transfer}', [TransferController::class, 'show'])->name('transfers.show');

    // Stock Movement Audit Ledger & Timeline
    Route::get('/movements', [StockMovementController::class, 'index'])->name('movements.index');

    // Products Catalog
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    // Branches & Stores
    Route::get('/branches', [BranchController::class, 'index'])->name('branches.index');
    Route::get('/branches/create', [BranchController::class, 'create'])->name('branches.create');
    Route::post('/branches', [BranchController::class, 'store'])->name('branches.store');
    Route::get('/branches/{branch}/edit', [BranchController::class, 'edit'])->name('branches.edit');
    Route::put('/branches/{branch}', [BranchController::class, 'update'])->name('branches.update');

    Route::get('/stores/create', [StoreController::class, 'create'])->name('stores.create');
    Route::post('/stores', [StoreController::class, 'store'])->name('stores.store');
    Route::get('/stores/{store}', [StoreController::class, 'show'])->name('stores.show');
    Route::get('/stores/{store}/edit', [StoreController::class, 'edit'])->name('stores.edit');
    Route::put('/stores/{store}', [StoreController::class, 'update'])->name('stores.update');

    // Admin Users Management
    Route::middleware('role:admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
