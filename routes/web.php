<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

// --- Tamu ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

// --- Sudah login ---
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // POS & riwayat transaksi (semua peran)
    Route::get('/pos', [SaleController::class, 'create'])->name('sales.create');
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    Route::get('/sales/{sale}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt');

    // Pelanggan (semua peran, kecuali hapus)
    Route::resource('customers', CustomerController::class)->except(['show', 'destroy']);

    // --- Admin & Manajer ---
    Route::middleware('role:admin,manager')->group(function () {
        Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

        Route::post('products/bulk-update', [ProductController::class, 'bulkUpdate'])->name('products.bulk');
        Route::resource('products', ProductController::class);

        Route::resource('categories', CategoryController::class)->except('show');
        Route::resource('suppliers', SupplierController::class)->except('show');

        Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'show']);
        Route::post('purchases/{purchase}/receive', [PurchaseController::class, 'receive'])->name('purchases.receive');

        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    });

    // --- Admin saja ---
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->except('show');
    });
});
