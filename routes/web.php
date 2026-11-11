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
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AiAssistantController;

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Master Data & Organization
Route::prefix('master')->name('master.')->group(function () {
    Route::get('/organization', [MasterController::class, 'organization'])->name('organization');
    Route::get('/products', [MasterController::class, 'products'])->name('products');
    Route::get('/warehouses', [MasterController::class, 'warehouses'])->name('warehouses');
    Route::get('/sales-team', [MasterController::class, 'sales'])->name('sales-team');
    Route::post('/sales-team', [MasterController::class, 'storeSales'])->name('sales-team.store');
    Route::post('/sales-team/update/{id}', [MasterController::class, 'updateSales'])->name('sales-team.update');
    Route::get('/customers', [MasterController::class, 'customers'])->name('customers');
    Route::post('/customers', [MasterController::class, 'storeCustomer'])->name('customers.store');
    Route::post('/customers/update/{id}', [MasterController::class, 'updateCustomer'])->name('customers.update');
    Route::get('/users', [MasterController::class, 'users'])->name('users');
    Route::post('/users', [MasterController::class, 'storeUser'])->name('users.store');
    Route::post('/users/update/{id}', [MasterController::class, 'updateUser'])->name('users.update');
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

// Sales (SO, Approval, GD, Cetak Nota, Retur, Target & Komisi)
Route::prefix('sales')->name('sales.')->group(function () {
    Route::get('/', [SalesController::class, 'index'])->name('index');
    Route::get('/targets', [SalesController::class, 'targets'])->name('targets');
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
    Route::post('/store-bbm-tunai', [FinanceController::class, 'storeBbmTunai'])->name('store-bbm-tunai');
    Route::get('/print-kwitansi/{id}', [FinanceController::class, 'printKwitansi'])->name('print-kwitansi');
});

// Reports & Export Center (Excel & PDF)
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/audit', [AuditLogController::class, 'index'])->name('audit');
    Route::get('/export-excel', [ReportController::class, 'exportExcel'])->name('export-excel');
    Route::get('/print-pdf', [ReportController::class, 'printPdf'])->name('print-pdf');
});

// Tracking & Informasi Sales Force
Route::prefix('tracking')->name('tracking.')->group(function () {
    Route::get('/locations', [TrackingController::class, 'locations'])->name('locations');
    Route::get('/pjp', [TrackingController::class, 'pjp'])->name('pjp');
    Route::get('/devices', [TrackingController::class, 'devices'])->name('devices');
});

// Approval Center (Pusat Persetujuan Terpadu)
Route::prefix('approvals')->name('approvals.')->group(function () {
    Route::get('/', [ApprovalController::class, 'index'])->name('index');
    Route::post('/process', [ApprovalController::class, 'process'])->name('process');
});

// AI Assistant Copilot (Nisa)
Route::post('/ai/chat', [AiAssistantController::class, 'chat'])->name('ai.chat');
