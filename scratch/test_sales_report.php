<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Services\TerritoryScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;

echo "=== START TESTING SALES REPORT FLOW ===\n";

$admin = User::first();
Auth::login($admin);
TerritoryScope::flush();
Config::set('erp.enable_iccid', false);

$controller = app(\App\Http\Controllers\ReportController::class);

// Test 1: Web View (index) with type=SALES
$reqIndex = Request::create('/reports', 'GET', [
    'type' => 'SALES',
    'start_date' => date('Y-01-01'),
    'end_date' => date('Y-12-31'),
]);
$viewIndex = $controller->index($reqIndex);
assert($viewIndex instanceof \Illuminate\View\View, "Report index must return View");
$htmlIndex = $viewIndex->render();

assert(strpos($htmlIndex, 'reportSalesTable()') !== false, "HTML must initialize reportSalesTable()");
assert(strpos($htmlIndex, 'RINCIAN BARANG PESANAN (SO:') !== false, "HTML must contain expandable detailed items container");
assert(strpos($htmlIndex, 'Total Fisik Terjual:') !== false, "HTML must contain sales summary card");
assert(strpos($htmlIndex, 'Rincian Lengkap (Per Item)') !== false, "HTML must contain dropdown option for detailed Excel/PDF");
echo "✓ Test 1: Web Index View for SALES rendered Master-Detail table, summary cards & dropdowns.\n";

// Test 2: Export Excel Summary
$reqExcelSummary = Request::create('/reports/export-excel', 'GET', [
    'type' => 'SALES',
    'format' => 'summary',
    'start_date' => date('Y-01-01'),
    'end_date' => date('Y-12-31'),
]);
$resExcelSummary = $controller->exportExcel($reqExcelSummary);
assert($resExcelSummary instanceof \Symfony\Component\HttpFoundation\StreamedResponse, "Excel export must return StreamedResponse");
ob_start();
$resExcelSummary->sendContent();
$csvSummary = ob_get_clean();

assert(strpos($csvSummary, 'No. SO') !== false, "CSV summary must have No. SO header");
assert(strpos($csvSummary, 'Total Penjualan (Rp)') !== false, "CSV summary must have Total Penjualan header");
echo "✓ Test 2: Excel Summary Export generated valid CSV with summary headers.\n";

// Test 3: Export Excel Detailed (per item)
$reqExcelDetail = Request::create('/reports/export-excel', 'GET', [
    'type' => 'SALES',
    'format' => 'detailed',
    'start_date' => date('Y-01-01'),
    'end_date' => date('Y-12-31'),
]);
$resExcelDetail = $controller->exportExcel($reqExcelDetail);
ob_start();
$resExcelDetail->sendContent();
$csvDetail = ob_get_clean();

assert(strpos($csvDetail, 'SKU') !== false, "CSV detailed must contain SKU header");
assert(strpos($csvDetail, 'Nama Produk') !== false, "CSV detailed must contain Nama Produk header");
assert(strpos($csvDetail, 'Harga Jual Satuan (Rp)') !== false, "CSV detailed must contain Harga Jual Satuan header");
assert(strpos($csvDetail, 'TOTAL KESELURUHAN') !== false, "CSV detailed must contain TOTAL KESELURUHAN summary");
echo "✓ Test 3: Excel Detailed Export generated valid CSV with item-by-item breakdown.\n";

// Test 4: Print PDF View Summary
$reqPdfSummary = Request::create('/reports/print-pdf', 'GET', [
    'type' => 'SALES',
    'format' => 'summary',
    'start_date' => date('Y-01-01'),
    'end_date' => date('Y-12-31'),
]);
$viewPdfSummary = $controller->printPdf($reqPdfSummary);
assert($viewPdfSummary instanceof \Illuminate\View\View, "PDF summary must return View");
$htmlPdfSummary = $viewPdfSummary->render();
assert(strpos($htmlPdfSummary, 'DOKUMEN LAPORAN RESMI') !== false, "PDF summary must contain official report header");
assert(strpos($htmlPdfSummary, 'Dibuat Oleh (Admin Sales / PIC)') !== false, "PDF summary must have sales signature block");
echo "✓ Test 4: PDF Summary View rendered properly with signatures.\n";

// Test 5: Print PDF View Detailed
$reqPdfDetail = Request::create('/reports/print-pdf', 'GET', [
    'type' => 'SALES',
    'format' => 'detailed',
    'start_date' => date('Y-01-01'),
    'end_date' => date('Y-12-31'),
]);
$viewPdfDetail = $controller->printPdf($reqPdfDetail);
assert($viewPdfDetail instanceof \Illuminate\View\View, "PDF detailed must return View");
$htmlPdfDetail = $viewPdfDetail->render();
assert(strpos($htmlPdfDetail, 'FORMAT RINCIAN LENGKAP RESMI PENJUALAN') !== false, "PDF detailed must contain official sales card format");
assert(strpos($htmlPdfDetail, 'Total Nilai Tagihan SO:') !== false, "PDF detailed must contain itemized tables with footers");
echo "✓ Test 5: PDF Detailed View rendered document cards per SO with line items.\n";

echo "\n=== ALL 5 SALES REPORT TESTS PASSED SUCCESSFULLY! ===\n";
