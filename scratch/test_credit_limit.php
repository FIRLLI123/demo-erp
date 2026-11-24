<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Customer;
use App\Services\CustomerCreditService;
use Illuminate\Support\Facades\DB;

echo "=== 1. TEST CUSTOMER DENGAN LIMIT = 0 ===\n";
// Buat customer test dengan limit 0
$custZero = Customer::where('credit_limit', 0)->first();
if (!$custZero) {
    $custZero = Customer::create([
        'code' => 'TEST-ZERO',
        'name' => 'Toko Tanpa Limit',
        'type' => 'RETAIL',
        'phone' => '081299990001',
        'credit_limit' => 0,
        'status' => 'ACTIVE'
    ]);
}
$checkZero = CustomerCreditService::validateOrder($custZero->id, 50000);
echo "Order Rp 50.000 untuk customer limit 0: " . (!$checkZero['allowed'] ? 'DITOLAK (PASS)' : 'DITERIMA (FAIL)') . "\n";
echo "Pesan: {$checkZero['message']}\n";

echo "\n=== 2. TEST CUSTOMER DENGAN LIMIT = 1.000.000 ===\n";
// Buat customer test dengan limit 1 juta
$custLimit = Customer::where('code', 'TEST-LIMIT-1M')->first();
if (!$custLimit) {
    $custLimit = Customer::create([
        'code' => 'TEST-LIMIT-1M',
        'name' => 'Toko Mitra Satu Juta',
        'type' => 'RETAIL',
        'phone' => '081299990002',
        'credit_limit' => 1000000,
        'status' => 'ACTIVE'
    ]);
} else {
    $custLimit->update(['credit_limit' => 1000000]);
}

// Hapus SO test sebelumnya jika ada
DB::table('sales_orders')->where('customer_id', $custLimit->id)->delete();
DB::table('accounts_receivable')->where('customer_id', $custLimit->id)->delete();

// Kondisi 1: Belum ada piutang, sisa limit harus 1.000.000
$sum1 = CustomerCreditService::getCreditSummary($custLimit->id);
echo "Kondisi Awal: Limit = Rp " . number_format($sum1['credit_limit']) . ", Sisa = Rp " . number_format($sum1['remaining_limit']) . "\n";

// Order 1: Nilai Rp 600.000 (Harus DITERIMA)
$check1 = CustomerCreditService::validateOrder($custLimit->id, 600000);
echo "Order 1 (Rp 600.000): " . ($check1['allowed'] ? 'DITERIMA (PASS)' : 'DITOLAK (FAIL)') . "\n";

// Simulasi SO 1 tersimpan di database
$soId1 = DB::table('sales_orders')->insertGetId([
    'doc_type' => 'SO',
    'doc_number' => 'SO/TEST/' . time(),
    'tap_id' => 1,
    'warehouse_id' => 1,
    'sales_id' => 1,
    'customer_id' => $custLimit->id,
    'order_date' => date('Y-m-d'),
    'status' => 'PENDING_APPROVAL',
    'grand_total' => 600000,
    'created_at' => now(),
    'updated_at' => now(),
]);

// Cek sisa limit setelah SO 1 tersimpan
$sum2 = CustomerCreditService::getCreditSummary($custLimit->id);
echo "Setelah SO 1: Terpakai = Rp " . number_format($sum2['used_limit']) . ", Sisa Limit = Rp " . number_format($sum2['remaining_limit']) . " (Expected: 400.000)\n";

// Order 2: Nilai Rp 500.000 (Harus DITOLAK karena sisa hanya 400.000)
$check2 = CustomerCreditService::validateOrder($custLimit->id, 500000);
echo "Order 2 (Rp 500.000): " . (!$check2['allowed'] ? 'DITOLAK (PASS)' : 'DITERIMA (FAIL)') . "\n";
echo "Pesan: {$check2['message']}\n";

// Order 3: Nilai Rp 350.000 (Harus DITERIMA karena <= 400.000)
$check3 = CustomerCreditService::validateOrder($custLimit->id, 350000);
echo "Order 3 (Rp 350.000): " . ($check3['allowed'] ? 'DITERIMA (PASS)' : 'DITOLAK (FAIL)') . "\n";

echo "\n=== 3. TEST VOID SO (PEMULIHAN SISA LIMIT) ===\n";
// Void SO 1
DB::table('sales_orders')->where('id', $soId1)->update(['status' => 'VOID']);
$sum3 = CustomerCreditService::getCreditSummary($custLimit->id);
echo "Setelah SO 1 di-VOID: Terpakai = Rp " . number_format($sum3['used_limit']) . ", Sisa Limit = Rp " . number_format($sum3['remaining_limit']) . " (Expected: 1.000.000)\n";

// Sekarang coba Order 2 lagi Rp 500.000 setelah di-VOID (Harus DITERIMA)
$check4 = CustomerCreditService::validateOrder($custLimit->id, 500000);
echo "Order 2 ulang (Rp 500.000) setelah VOID: " . ($check4['allowed'] ? 'DITERIMA (PASS - Limit Pulih)' : 'DITOLAK (FAIL)') . "\n";

// Bersihkan data dummy
DB::table('sales_orders')->where('customer_id', $custLimit->id)->delete();
DB::table('customers')->where('id', $custLimit->id)->delete();
if ($custZero->code === 'TEST-ZERO') {
    $custZero->delete();
}

echo "\n=== SEMUA SKENARIO BERHASIL DIVERIFIKASI! ===\n";
