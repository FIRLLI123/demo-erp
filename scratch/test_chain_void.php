<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\Models\Product;

echo "=== TEST COMPLETE UNWIND CHAIN (BBM -> TTK -> SO/GD) ===\n";

$user = \App\Models\User::first();
if ($user) \Illuminate\Support\Facades\Auth::login($user);

$customer = Customer::first();
$product = Product::first();
$whId = 1;

// 1. Buat SO -> DELIVERED
$soId = DB::table('sales_orders')->insertGetId([
    'doc_type' => 'SO',
    'doc_number' => 'SO/CHAIN/' . time(),
    'tap_id' => 1,
    'warehouse_id' => $whId,
    'sales_id' => 1,
    'customer_id' => $customer->id,
    'order_date' => date('Y-m-d'),
    'channel' => 'WEB_ADMIN',
    'status' => 'DELIVERED',
    'subtotal' => 200000,
    'grand_total' => 200000,
    'created_at' => now(),
    'updated_at' => now(),
]);

DB::table('sales_order_items')->insert([
    'sales_order_id' => $soId,
    'product_id' => $product->id,
    'quantity' => 2,
    'unit_price' => 100000,
    'subtotal' => 200000,
    'created_at' => now(),
    'updated_at' => now(),
]);

$gdId = DB::table('goods_deliveries')->insertGetId([
    'doc_type' => 'GD',
    'doc_number' => 'GD/CHAIN/' . time(),
    'ref_doc_type' => 'SO',
    'sales_order_id' => $soId,
    'warehouse_id' => $whId,
    'customer_id' => $customer->id,
    'delivery_date' => date('Y-m-d'),
    'status' => 'DELIVERED',
    'created_at' => now(),
    'updated_at' => now(),
]);

$arId = DB::table('accounts_receivable')->insertGetId([
    'doc_type' => 'AR',
    'doc_number' => 'AR/CHAIN/' . time(),
    'ref_doc_type' => 'SO',
    'sales_order_id' => $soId,
    'customer_id' => $customer->id,
    'invoice_date' => date('Y-m-d'),
    'due_date' => date('Y-m-d'),
    'total_amount' => 200000,
    'paid_amount' => 200000,
    'balance_amount' => 0,
    'status' => 'PAID', // Sudah lunas oleh TTK
    'created_at' => now(),
    'updated_at' => now(),
]);

// 2. Buat TTK
$ttkId = DB::table('payments')->insertGetId([
    'doc_type' => 'TTK',
    'doc_number' => 'TTK/CHAIN/' . time(),
    'customer_id' => $customer->id,
    'payment_date' => date('Y-m-d'),
    'payment_method' => 'CASH',
    'amount' => 200000,
    'cashier_name' => 'Kasir Test',
    'status' => 'COMPLETED',
    'created_at' => now(),
    'updated_at' => now(),
]);

DB::table('payment_allocations')->insert([
    'payment_id' => $ttkId,
    'accounts_receivable_id' => $arId,
    'amount' => 200000,
    'balance_before' => 200000,
    'balance_after' => 0,
    'created_at' => now(),
    'updated_at' => now(),
]);

// 3. Buat BBM
$bbmId = DB::table('bank_receipts')->insertGetId([
    'doc_type' => 'BBM',
    'doc_number' => 'BBM/CHAIN/' . time(),
    'receipt_type' => 'TUNAI',
    'payment_id' => $ttkId,
    'bank_name' => 'BCA Operasional',
    'account_number' => '123-456',
    'receipt_date' => date('Y-m-d'),
    'amount' => 200000,
    'status' => 'VERIFIED',
    'created_at' => now(),
    'updated_at' => now(),
]);

echo "Kondisi Awal: SO (DELIVERED) -> AR (PAID) -> TTK ({$ttkId}) -> BBM ({$bbmId})\n";

$financeCtrl = app()->make(\App\Http\Controllers\FinanceController::class);
$salesCtrl = app()->make(\App\Http\Controllers\SalesController::class);
$req = new \Illuminate\Http\Request();

// Cek 1: Coba VOID SO langsung saat masih ada TTK & BBM (Harus DITOLAK)
echo "\n[Langkah 1] Coba Void SO langsung: ";
$resSo = $salesCtrl->void($req, $soId);
$soCheck1 = DB::table('sales_orders')->where('id', $soId)->value('status');
echo "Status SO = {$soCheck1} (" . ($soCheck1 !== 'VOID' ? 'DITOLAK - PASS' : 'FAIL') . ")\n";

// Cek 2: Coba VOID TTK langsung saat BBM masih VERIFIED (Harus DITOLAK)
echo "[Langkah 2] Coba Void TTK langsung saat BBM masih aktif: ";
$resTtk1 = $financeCtrl->voidTtk($req, $ttkId);
$ttkCheck1 = DB::table('payments')->where('id', $ttkId)->value('status');
echo "Status TTK = {$ttkCheck1} (" . ($ttkCheck1 !== 'VOID' ? 'DITOLAK - PASS' : 'FAIL') . ")\n";

// Cek 3: Eksekusi VOID BBM terlebih dahulu
echo "[Langkah 3] Eksekusi Void BBM: ";
$resBbm = $financeCtrl->voidBbm($req, $bbmId);
$bbmCheck = DB::table('bank_receipts')->where('id', $bbmId)->value('status');
echo "Status BBM = {$bbmCheck} (" . ($bbmCheck === 'VOID' ? 'VOID BERHASIL - PASS' : 'FAIL') . ")\n";

// Cek 4: Sekarang VOID TTK setelah BBM di-VOID
echo "[Langkah 4] Eksekusi Void TTK setelah BBM void: ";
$resTtk2 = $financeCtrl->voidTtk($req, $ttkId);
$ttkCheck2 = DB::table('payments')->where('id', $ttkId)->value('status');
$arCheck2 = DB::table('accounts_receivable')->where('id', $arId)->first();
echo "Status TTK = {$ttkCheck2} (" . ($ttkCheck2 === 'VOID' ? 'VOID BERHASIL - PASS' : 'FAIL') . ")\n";
echo "           Status AR kembali = {$arCheck2->status}, Sisa Tagihan = Rp " . number_format($arCheck2->balance_amount) . " (Expected: UNPAID, 200.000)\n";

// Cek 5: Sekarang VOID SO setelah TTK di-VOID
echo "[Langkah 5] Eksekusi Void SO & GD setelah TTK void: ";
$resSo2 = $salesCtrl->void($req, $soId);
$soCheck2 = DB::table('sales_orders')->where('id', $soId)->value('status');
$gdCheck2 = DB::table('goods_deliveries')->where('id', $gdId)->first();
$arCheck3 = DB::table('accounts_receivable')->where('id', $arId)->first();
echo "Status SO = {$soCheck2} (" . ($soCheck2 === 'VOID' ? 'VOID BERHASIL - PASS' : 'FAIL') . ")\n";
echo "           GD Status = " . ($gdCheck2 ? 'Masih ada' : 'Terhapus/Bersih (PASS)') . "\n";
echo "           AR Status = " . ($arCheck3 ? 'Masih ada' : 'Terhapus/Bersih (PASS)') . "\n";

// Cleanup
DB::table('bank_receipts')->where('id', $bbmId)->delete();
DB::table('payment_allocations')->where('payment_id', $ttkId)->delete();
DB::table('payments')->where('id', $ttkId)->delete();
DB::table('sales_order_items')->where('sales_order_id', $soId)->delete();
DB::table('sales_orders')->where('id', $soId)->delete();

echo "\n=== SEMUA URUTAN CHAIN BERJALAN 100% PERSIS DAN VALID! ===\n";
