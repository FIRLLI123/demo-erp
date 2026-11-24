<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\Models\Product;

echo "=== TEST AUTO-RESOLVE GUDANG DARI SALES DI CONTROLLER ===\n";

$sales = DB::table('sales')->first();
$customer = Customer::first();
$product = Product::first();

// Pastikan customer punya limit kredit cukup
$customer->update(['credit_limit' => 5000000]);

$salesWh = DB::table('warehouses')->where('sales_id', $sales->id)->first();
echo "Sales ID: {$sales->id} ({$sales->name})\n";
echo "Gudang Sales Asli di DB: " . ($salesWh ? "[{$salesWh->id}] {$salesWh->name}" : "Tidak ada") . "\n";

// Buat request TANPA warehouse_id
$requestData = [
    'customer_id' => $customer->id,
    'sales_id' => $sales->id,
    'warehouse_id' => null, // KOSONG / TIDAK DIPILIH USER
    'order_date' => date('Y-m-d'),
    'notes' => 'SO Test Otomatis Gudang Sales',
    'items' => [
        [
            'product_id' => $product->id,
            'quantity' => 1,
            'iccid_mode' => 'SELECT',
        ]
    ]
];

$request = new \Illuminate\Http\Request($requestData);

// Login user
$user = \App\Models\User::first();
if ($user) \Illuminate\Support\Facades\Auth::login($user);

// Beri stok di gudang sales jika belum ada
$currStock = DB::table('stock_balances')
    ->where('warehouse_id', $salesWh->id)
    ->where('product_id', $product->id)
    ->value('quantity') ?? 0;

if ($currStock < 5) {
    DB::table('stock_balances')->updateOrInsert(
        ['warehouse_id' => $salesWh->id, 'product_id' => $product->id],
        ['quantity' => 50, 'updated_at' => now()]
    );
}

$salesController = app()->make(\App\Http\Controllers\SalesController::class);
$response = $salesController->store($request);

// Cek SO terakhir yang baru terbentuk
$latestSo = DB::table('sales_orders')->orderBy('id', 'desc')->first();

echo "SO Baru Terbentuk: ID={$latestSo->id}, Doc={$latestSo->doc_number}\n";
echo "Gudang SO yang tersimpan: ID={$latestSo->warehouse_id}\n";

if ($latestSo->warehouse_id == $salesWh->id) {
    echo "VERIFIKASI BERHASIL (PASS): Gudang otomatis menggunakan Gudang Sales [{$salesWh->id}] {$salesWh->name}!\n";
} else {
    echo "VERIFIKASI GAGAL: Gudang yang tersimpan {$latestSo->warehouse_id} != {$salesWh->id}\n";
}

// Bersihkan dummy SO test
DB::table('sales_order_items')->where('sales_order_id', $latestSo->id)->delete();
DB::table('sales_orders')->where('id', $latestSo->id)->delete();

echo "Cleanup selesai.\n";
