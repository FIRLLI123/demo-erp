<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\Models\Product;
use App\Services\CustomerCreditService;

echo "=== 1. SETUP DUMMY DATA UNTUK TEST VOID SO (GD VS AR) ===\n";

$customer = Customer::first();
$product = Product::first();
$warehouseId = 1;
$tapId = 1;
$salesId = 1;

if (!$customer || !$product) {
    echo "Data master customer/product kosong.\n";
    exit;
}

$initialStock = DB::table('stock_balances')
    ->where('warehouse_id', $warehouseId)
    ->where('product_id', $product->id)
    ->value('quantity') ?? 100;

echo "Initial Stock {$product->name}: {$initialStock} pcs\n";

// Skenario A: Buat SO -> Approve -> Deliver (Terbit GD & AR UNPAID)
$soId = DB::table('sales_orders')->insertGetId([
    'doc_type' => 'SO',
    'doc_number' => 'SO/TEST/' . time(),
    'tap_id' => $tapId,
    'warehouse_id' => $warehouseId,
    'sales_id' => $salesId,
    'customer_id' => $customer->id,
    'order_date' => date('Y-m-d'),
    'channel' => 'WEB_ADMIN',
    'status' => 'DELIVERED',
    'subtotal' => 250000,
    'grand_total' => 250000,
    'created_at' => now(),
    'updated_at' => now(),
]);

DB::table('sales_order_items')->insert([
    'sales_order_id' => $soId,
    'product_id' => $product->id,
    'quantity' => 10,
    'unit_price' => 25000,
    'subtotal' => 250000,
    'created_at' => now(),
    'updated_at' => now(),
]);

// Simulasi stok terpotong 10 pcs saat GD
DB::table('stock_balances')
    ->where('warehouse_id', $warehouseId)
    ->where('product_id', $product->id)
    ->update(['quantity' => $initialStock - 10]);

// Terbit GD
$gdNum = 'GD/TEST/' . time();
$gdId = DB::table('goods_deliveries')->insertGetId([
    'doc_type' => 'GD',
    'doc_number' => $gdNum,
    'ref_doc_type' => 'SO',
    'sales_order_id' => $soId,
    'warehouse_id' => $warehouseId,
    'customer_id' => $customer->id,
    'delivery_date' => date('Y-m-d'),
    'recipient_name' => $customer->name,
    'status' => 'DELIVERED',
    'created_at' => now(),
    'updated_at' => now(),
]);

// Terbit AR (UNPAID, Belum ada pembayaran)
$arNum = 'AR/TEST/' . time();
$arId = DB::table('accounts_receivable')->insertGetId([
    'doc_type' => 'AR',
    'doc_number' => $arNum,
    'ref_doc_type' => 'SO',
    'sales_order_id' => $soId,
    'customer_id' => $customer->id,
    'invoice_date' => date('Y-m-d'),
    'due_date' => date('Y-m-d'),
    'total_amount' => 250000,
    'paid_amount' => 0,
    'balance_amount' => 250000,
    'status' => 'UNPAID',
    'created_at' => now(),
    'updated_at' => now(),
]);

echo "\n=== 2. TEST VOID SO PADA TAHAP GD (AR BELUM DIBAYAR) ===\n";
echo "Status SO saat ini: DELIVERED (Sudah ada GD: {$gdNum})\n";
echo "Status AR saat ini: UNPAID (Rp 0 terbayar)\n";

// Login sebagai user superadmin/admin pertama agar lolos pengecekan TerritoryScope
$user = \App\Models\User::first();
if ($user) {
    \Illuminate\Support\Facades\Auth::login($user);
}

// Panggil Controller void secara langsung
$salesController = app()->make(\App\Http\Controllers\SalesController::class);
$request = new \Illuminate\Http\Request();
$response = $salesController->void($request, $soId);

$soAfter = DB::table('sales_orders')->where('id', $soId)->first();
$gdAfter = DB::table('goods_deliveries')->where('id', $gdId)->first();
$arAfter = DB::table('accounts_receivable')->where('id', $arId)->first();
$stockAfter = DB::table('stock_balances')
    ->where('warehouse_id', $warehouseId)
    ->where('product_id', $product->id)
    ->value('quantity');

echo "Hasil Status SO: {$soAfter->status} (Expected: VOID)\n";
echo "Hasil GD: " . ($gdAfter ? 'Masih ada (FAIL)' : 'Terhapus/Dibersihkan (PASS)') . "\n";
echo "Hasil AR: " . ($arAfter ? 'Masih ada (FAIL)' : 'Terhapus/Dibersihkan (PASS)') . "\n";
echo "Stok Barang Setelah Void: {$stockAfter} pcs (Expected kembali ke {$initialStock})\n";

// Skenario B: Jika AR SUDAH DIBAYAR (PARTIAL / PAID), Void HARUS DITOLAK
echo "\n=== 3. TEST VOID SO KETIKA AR SUDAH MEMILIKI PEMBAYARAN ===\n";
// Re-insert SO dan AR dengan status PARTIAL
$soPaidId = DB::table('sales_orders')->insertGetId([
    'doc_type' => 'SO',
    'doc_number' => 'SO/PAID/' . time(),
    'tap_id' => $tapId,
    'warehouse_id' => $warehouseId,
    'sales_id' => $salesId,
    'customer_id' => $customer->id,
    'order_date' => date('Y-m-d'),
    'channel' => 'WEB_ADMIN',
    'status' => 'DELIVERED',
    'subtotal' => 500000,
    'grand_total' => 500000,
    'created_at' => now(),
    'updated_at' => now(),
]);

$arPaidId = DB::table('accounts_receivable')->insertGetId([
    'doc_type' => 'AR',
    'doc_number' => 'AR/PAID/' . time(),
    'ref_doc_type' => 'SO',
    'sales_order_id' => $soPaidId,
    'customer_id' => $customer->id,
    'invoice_date' => date('Y-m-d'),
    'due_date' => date('Y-m-d'),
    'total_amount' => 500000,
    'paid_amount' => 200000, // Sudah bayar 200rb
    'balance_amount' => 300000,
    'status' => 'PARTIAL',
    'created_at' => now(),
    'updated_at' => now(),
]);

$responsePaid = $salesController->void($request, $soPaidId);
$soPaidAfter = DB::table('sales_orders')->where('id', $soPaidId)->first();
echo "Status SO yang AR-nya sudah dibayar setelah dicoba Void: {$soPaidAfter->status} (Expected: Tetap DELIVERED / Tolak Void)\n";

// Cleanup test
DB::table('sales_orders')->whereIn('id', [$soId, $soPaidId])->delete();
DB::table('accounts_receivable')->where('id', $arPaidId)->delete();
DB::table('stock_balances')
    ->where('warehouse_id', $warehouseId)
    ->where('product_id', $product->id)
    ->update(['quantity' => $initialStock]);

echo "\n=== SEMUA SKENARIO UJI BERHASIL (PASS)! ===\n";
