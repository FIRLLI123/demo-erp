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
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UiShowcaseController;

// ============================================================
// 1. PUBLIC AUTHENTICATION ROUTES (GUEST ONLY)
// ============================================================
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// ============================================================
// 2. PROTECTED ENTERPRISE ERP ROUTES (WAJIB LOGIN / AUTH)
// ============================================================
Route::middleware(['auth'])->group(function () {
    // Sesi & Switch Area TAP (Khusus IT DEV & Super Admin)
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/switch-tap', [AuthController::class, 'switchTap'])->name('switch-tap');

    // Dashboard Utama
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Master Data & Organization
    Route::prefix('master')->name('master.')->group(function () {
        Route::get('/organization', [MasterController::class, 'organization'])->name('organization');
        Route::get('/products', [MasterController::class, 'products'])->name('products');
        Route::post('/products', [MasterController::class, 'storeProduct'])->name('products.store');
        Route::post('/products/update/{id}', [MasterController::class, 'updateProduct'])->name('products.update');
        Route::post('/product-types', [MasterController::class, 'storeProductType'])->name('product-types.store');
        Route::post('/product-types/update/{id}', [MasterController::class, 'updateProductType'])->name('product-types.update');
        Route::post('/product-prices', [MasterController::class, 'storeProductPrice'])->name('product-prices.store');
        Route::post('/product-prices/update/{id}', [MasterController::class, 'updateProductPrice'])->name('product-prices.update');
        Route::post('/product-prices/delete/{id}', [MasterController::class, 'deleteProductPrice'])->name('product-prices.delete');
        Route::get('/programs', [MasterController::class, 'programs'])->name('programs');
        Route::post('/programs', [MasterController::class, 'storeProgram'])->name('programs.store');
        Route::post('/programs/update/{id}', [MasterController::class, 'updateProgram'])->name('programs.update');
        Route::post('/program-items', [MasterController::class, 'storeProgramItem'])->name('program-items.store');
        Route::post('/program-items/update/{id}', [MasterController::class, 'updateProgramItem'])->name('program-items.update');
        Route::post('/program-items/delete/{id}', [MasterController::class, 'deleteProgramItem'])->name('program-items.delete');
        Route::get('/warehouses', [MasterController::class, 'warehouses'])->name('warehouses');
        Route::post('/warehouses', [MasterController::class, 'storeWarehouse'])->name('warehouses.store');
        Route::post('/warehouses/update/{id}', [MasterController::class, 'updateWarehouse'])->name('warehouses.update');
        Route::get('/sales-team', [MasterController::class, 'sales'])->name('sales-team');
        Route::post('/sales-team', [MasterController::class, 'storeSales'])->name('sales-team.store');
        Route::post('/sales-team/update/{id}', [MasterController::class, 'updateSales'])->name('sales-team.update');
        Route::post('/sales-types', [MasterController::class, 'storeSalesType'])->name('sales-types.store');
        Route::post('/sales-types/update/{id}', [MasterController::class, 'updateSalesType'])->name('sales-types.update');
        Route::post('/sales-types/delete/{id}', [MasterController::class, 'deleteSalesType'])->name('sales-types.delete');
        Route::post('/sales-levels', [MasterController::class, 'storeSalesLevel'])->name('sales-levels.store');
        Route::post('/sales-levels/update/{id}', [MasterController::class, 'updateSalesLevel'])->name('sales-levels.update');
        Route::post('/sales-levels/delete/{id}', [MasterController::class, 'deleteSalesLevel'])->name('sales-levels.delete');
        Route::get('/customers', [MasterController::class, 'customers'])->name('customers');
        Route::post('/customers', [MasterController::class, 'storeCustomer'])->name('customers.store');
        Route::post('/customers/update/{id}', [MasterController::class, 'updateCustomer'])->name('customers.update');
        
        // Manajemen Multi Nomor RS Customer (1 Toko Multi Chip)
        Route::get('/customers/{customerId}/rs-numbers', [MasterController::class, 'getRsNumbers'])->name('customers.rs-numbers');
        Route::post('/customers/rs-numbers', [MasterController::class, 'storeRsNumber'])->name('customers.rs-numbers.store');
        Route::post('/customers/rs-numbers/update/{id}', [MasterController::class, 'updateRsNumber'])->name('customers.rs-numbers.update');
        Route::post('/customers/rs-numbers/delete/{id}', [MasterController::class, 'deleteRsNumber'])->name('customers.rs-numbers.delete');
        Route::post('/customers/rs-numbers/set-primary/{id}', [MasterController::class, 'setPrimaryRsNumber'])->name('customers.rs-numbers.set-primary');
        Route::get('/users', [MasterController::class, 'users'])->name('users');
        Route::post('/users', [MasterController::class, 'storeUser'])->name('users.store');
        Route::post('/users/update/{id}', [MasterController::class, 'updateUser'])->name('users.update');
        Route::get('/users/{id}/mapping', [MasterController::class, 'userMapping'])->name('users.mapping');
        Route::post('/users/{id}/mapping', [MasterController::class, 'saveUserMapping'])->name('users.save-mapping');
        
        // Master Tambahan (Bank, Vendor, Price Profile, Segmen, Wilayah, Approval Matrix)
        Route::get('/banks', [MasterController::class, 'banks'])->name('banks');
        Route::post('/banks', [MasterController::class, 'storeBank'])->name('banks.store');
        Route::get('/vendors', [MasterController::class, 'vendors'])->name('vendors');
        Route::post('/vendors', [MasterController::class, 'storeVendor'])->name('vendors.store');
        Route::get('/price-profiles', [MasterController::class, 'priceProfiles'])->name('price-profiles');
        Route::post('/price-profiles', [MasterController::class, 'storePriceProfile'])->name('price-profiles.store');
        Route::get('/customer-segments', [MasterController::class, 'customerSegments'])->name('customer-segments');
        Route::get('/territories', [MasterController::class, 'territories'])->name('territories');
        Route::get('/approval-rules', [MasterController::class, 'approvalRules'])->name('approval-rules');
        Route::post('/toggle-status', [MasterController::class, 'toggleStatus'])->name('toggle-status');
    });

    // Procurement (PO & GR)
    Route::prefix('procurement')->name('procurement.')->group(function () {
        Route::get('/', [ProcurementController::class, 'index'])->name('index');
        Route::get('/returns', [ProcurementController::class, 'returns'])->name('returns');
        Route::post('/store-po', [ProcurementController::class, 'storePo'])->name('store-po')->middleware(\App\Http\Middleware\JsonFlashResponse::class);
        Route::post('/void-po/{id}', [ProcurementController::class, 'voidPo'])->name('void-po')->middleware(\App\Http\Middleware\JsonFlashResponse::class);
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
        Route::get('/iccids', [InventoryController::class, 'iccids'])->name('iccids');
        Route::get('/adjustment', [InventoryController::class, 'adjustment'])->name('adjustment');
        Route::post('/adjustment', [InventoryController::class, 'storeAdjustment'])->name('adjustment.store');
        Route::get('/repack', [RepackController::class, 'index'])->name('repack');
        Route::post('/repack/store-ro', [RepackController::class, 'storeRo'])->name('repack.store-ro');
        Route::post('/repack/store-ri', [RepackController::class, 'storeRi'])->name('repack.store-ri');
        Route::get('/repack/download-template', [RepackController::class, 'downloadTemplate'])->name('repack.download-template');
        Route::post('/quick-sm', [InventoryController::class, 'quickSm'])->name('quick-sm');
    });

    // Sales (SO, Approval, GD, Cetak Nota, Retur, Target & Komisi)
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('/', [SalesController::class, 'index'])->name('index');
        Route::get('/returns', [SalesController::class, 'returnsIndex'])->name('returns');
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

    // Master UI Components Showcase & Testing Lab
    Route::get('/ui-showcase', [UiShowcaseController::class, 'index'])->name('ui.showcase');
    Route::post('/ui-showcase/trigger-alert', [UiShowcaseController::class, 'triggerAlert'])->name('ui.showcase.trigger-alert');
});
