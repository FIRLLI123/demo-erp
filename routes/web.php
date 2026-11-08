<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\ProcurementController;
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

// Inventory (Stock, ICCID, SM, Repack)
Route::prefix('inventory')->name('inventory.')->group(function () {
    Route::get('/', [InventoryController::class, 'index'])->name('index');
    Route::get('/repack', [InventoryController::class, 'repack'])->name('repack');
    Route::post('/quick-sm', [InventoryController::class, 'quickSm'])->name('quick-sm');
});

// Sales (SO & GD)
Route::prefix('sales')->name('sales.')->group(function () {
    Route::get('/', [SalesController::class, 'index'])->name('index');
    Route::post('/approve/{id}', [SalesController::class, 'approve'])->name('approve');
    Route::post('/deliver/{id}', [SalesController::class, 'deliver'])->name('deliver');
});

// Finance (AR, FP, BBM)
Route::prefix('finance')->name('finance.')->group(function () {
    Route::get('/', [FinanceController::class, 'index'])->name('index');
    Route::post('/pay/{id}', [FinanceController::class, 'pay'])->name('pay');
});
