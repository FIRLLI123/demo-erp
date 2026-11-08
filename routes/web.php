<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\ProcurementController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\RepackController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\FinanceController;

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Master Data & Organization
Route::prefix('master')->name('master.')->group(function () {
    Route::get('/organization', [MasterController::class, 'organization'])->name('organization');
    Route::get('/products', [MasterController::class, 'products'])->name('products');
    Route::get('/warehouses', [MasterController::class, 'warehouses'])->name('warehouses');
});

// Procurement (PO & GR)
Route::prefix('procurement')->name('procurement.')->group(function () {
    Route::get('/', [ProcurementController::class, 'index'])->name('index');
    Route::post('/store-po', [ProcurementController::class, 'storePo'])->name('store-po');
    Route::get('/gr/{id}', [ProcurementController::class, 'createGr'])->name('create-gr');
    Route::get('/download-iccid-template', [ProcurementController::class, 'downloadIccidTemplate'])->name('download-iccid-template');
    Route::post('/process-gr/{id}', [ProcurementController::class, 'processGr'])->name('process-gr');
});

// Stock Movement (SM - Transfer Antar Gudang)
Route::prefix('stock-movement')->name('stock-movement.')->group(function () {
    Route::get('/', [StockMovementController::class, 'index'])->name('index');
    Route::post('/store', [StockMovementController::class, 'store'])->name('store');
    Route::get('/download-template', [StockMovementController::class, 'downloadTemplate'])->name('download-template');
});

// Inventory (Stock, ICCID, SM, Repack)
Route::prefix('inventory')->name('inventory.')->group(function () {
    Route::get('/', [InventoryController::class, 'index'])->name('index');
    Route::get('/repack', [RepackController::class, 'index'])->name('repack');
    Route::post('/repack/store-ro', [RepackController::class, 'storeRo'])->name('repack.store-ro');
    Route::post('/repack/store-ri', [RepackController::class, 'storeRi'])->name('repack.store-ri');
    Route::get('/repack/download-template', [RepackController::class, 'downloadTemplate'])->name('repack.download-template');
    Route::post('/quick-sm', [InventoryController::class, 'quickSm'])->name('quick-sm');
});

// Sales (SO, Approval, GD, Cetak Nota, Retur)
Route::prefix('sales')->name('sales.')->group(function () {
    Route::get('/', [SalesController::class, 'index'])->name('index');
    Route::post('/store', [SalesController::class, 'store'])->name('store');
    Route::post('/approve/{id}', [SalesController::class, 'approve'])->name('approve');
    Route::post('/reject/{id}', [SalesController::class, 'reject'])->name('reject');
    Route::post('/deliver/{id}', [SalesController::class, 'deliver'])->name('deliver');
    Route::get('/print-nota/{id}', [SalesController::class, 'printNota'])->name('print-nota');
    Route::post('/return', [SalesController::class, 'storeReturn'])->name('return');
});

// Finance (AR, TTK, BBM)
Route::prefix('finance')->name('finance.')->group(function () {
    Route::get('/', [FinanceController::class, 'index'])->name('index');
    Route::post('/store-ttk', [FinanceController::class, 'storeTtk'])->name('store-ttk');
    Route::get('/print-kwitansi/{id}', [FinanceController::class, 'printKwitansi'])->name('print-kwitansi');
});
