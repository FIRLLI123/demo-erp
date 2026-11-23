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

echo "=== START TESTING SALES ORDER FLOW ===\n";

// 1. Ambil Super Admin & Non-Admin
$admin = User::first();
$tapUser = User::skip(1)->first() ?? $admin;

echo "Admin User: " . $admin->name . " (ID: {$admin->id})\n";
echo "TAP User: " . $tapUser->name . " (ID: {$tapUser->id})\n";

// Login sebagai Admin
Auth::login($admin);
TerritoryScope::flush();

// Test 1: Index View rendering dengan ICCID = false
Config::set('erp.enable_iccid', false);
$controller = app(\App\Http\Controllers\SalesController::class);
$indexView = $controller->index();
assert($indexView instanceof \Illuminate\View\View, "Index should return View");
$rendered = $indexView->render();
assert(strpos($rendered, 'Formulir Sales Order (SO) Baru') !== false, "Index rendered SO form");
assert(strpos($rendered, 'Ketik cari SKU atau nama produk...') !== false, "Index rendered searchable product input");
assert(strpos($rendered, 'Metode Serial ICCID:') === false, "Index hides ICCID method when ICCID is disabled");
echo "✓ Test 1: Index view with ICCID=false rendered correctly without ICCID options.\n";

// Test 2: Index View rendering dengan ICCID = true
Config::set('erp.enable_iccid', true);
$indexViewTrue = $controller->index();
$renderedTrue = $indexViewTrue->render();
assert(strpos($renderedTrue, 'Metode Serial ICCID:') !== false, "Index shows ICCID method when ICCID is enabled");
echo "✓ Test 2: Index view with ICCID=true rendered correctly with ICCID options.\n";

// Kembalikan ke mode non-ICCID sesuai .env saat ini
Config::set('erp.enable_iccid', false);

// Test 3: Validasi Territory Scope pada Pembuatan SO
// Cari customer, sales, dan warehouse
$customer = DB::table('customers')->first();
$sales = DB::table('sales')->first();
$warehouse = DB::table('warehouses')->whereIn('type', ['SALES', 'INVENTORY'])->first();
$product = DB::table('products')->first();

$tap = DB::table('taps')->first();
$sales = DB::table('sales')->where('tap_id', $tap->id)->first() ?? DB::table('sales')->first();
$warehouse = DB::table('warehouses')->where('tap_id', $tap->id)->whereIn('type', ['SALES', 'INVENTORY'])->first() ?? DB::table('warehouses')->whereIn('type', ['SALES', 'INVENTORY'])->first();
$product = DB::table('products')->first();

$customer = DB::table('customers')->first();
if (!$customer) {
    $custId = DB::table('customers')->insertGetId([
        'tap_id' => $tap->id,
        'code' => 'CST-001',
        'name' => 'Toko Berkah Cell',
        'type' => 'RETAIL',
        'phone' => '081234567890',
        'address' => 'Jl. Merdeka No. 10',
        'credit_limit' => 50000000,
        'status' => 'ACTIVE',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $customer = DB::table('customers')->where('id', $custId)->first();
}

assert($customer && $sales && $warehouse && $product, "Seed data must exist");

// Pastikan stok ada di warehouse untuk product ini
DB::table('stock_balances')->updateOrInsert(
    ['warehouse_id' => $warehouse->id, 'product_id' => $product->id],
    ['quantity' => 500, 'updated_at' => now()]
);

// Uji User yang tidak punya akses ke warehouse tersebut
$restrictedUser = new User();
$restrictedUser->id = 99999;
$restrictedUser->role_id = 99;
$restrictedUser->has_full_access = false;
$restrictedUser->is_ho_readonly = false;
$restrictedUser->sales_id = null;
Auth::login($restrictedUser);
TerritoryScope::flush();

$badReq = Request::create('/sales', 'POST', [
    'customer_id' => $customer->id,
    'sales_id' => $sales->id,
    'warehouse_id' => $warehouse->id,
    'order_date' => date('Y-m-d'),
    'items' => [
        ['product_id' => $product->id, 'quantity' => 10]
    ]
]);

$badRes = $controller->store($badReq);
assert(session('error') !== null, "Restricted user must be blocked with error");
echo "✓ Test 3: Territory enforcement on SO store blocked unauthorized user: " . session('error') . "\n";

// Test 4: Pembuatan SO oleh Admin yang Sah (Non-ICCID Mode)
Auth::login($admin);
TerritoryScope::flush();

$initialStock = (int) DB::table('stock_balances')
    ->where('warehouse_id', $warehouse->id)
    ->where('product_id', $product->id)
    ->value('quantity');

$goodReq = Request::create('/sales', 'POST', [
    'customer_id' => $customer->id,
    'sales_id' => $sales->id,
    'warehouse_id' => $warehouse->id,
    'order_date' => date('Y-m-d'),
    'notes' => 'Testing SO Otomatis Non-ICCID',
    'items' => [
        ['product_id' => $product->id, 'quantity' => 5]
    ]
]);

$goodRes = $controller->store($goodReq);
assert(session('success') !== null, "Store SO should succeed: " . session('error'));
echo "✓ Test 4: Store SO succeeded: " . session('success') . "\n";

// Ambil SO yang baru dibuat
$newSo = DB::table('sales_orders')->orderBy('id', 'desc')->first();
assert($newSo && $newSo->status === 'PENDING_APPROVAL', "SO status should be PENDING_APPROVAL");
assert($newSo->notes === 'Testing SO Otomatis Non-ICCID', "SO notes matched");

// Stok di gudang belum boleh berkurang pada tahap SO
$stockAfterSo = (int) DB::table('stock_balances')
    ->where('warehouse_id', $warehouse->id)
    ->where('product_id', $product->id)
    ->value('quantity');
assert($stockAfterSo === $initialStock, "Stock should NOT decrease during SO creation");
echo "✓ Test 5: Stock correctly untouched during SO creation ({$stockAfterSo} pcs).\n";

// Test 6: Approval Finance
$approveReq = Request::create("/sales/approve/{$newSo->id}", 'POST');
$approveRes = $controller->approve($approveReq, $newSo->id);
$soApproved = DB::table('sales_orders')->where('id', $newSo->id)->first();
assert($soApproved->status === 'APPROVED', "SO status should be APPROVED");
echo "✓ Test 6: Finance Approval succeeded. Status: {$soApproved->status}\n";

// Test 7: Sending Good (Deliver & GD / AR)
$deliverReq = Request::create("/sales/deliver/{$newSo->id}", 'POST');
$deliverRes = $controller->deliver($deliverReq, $newSo->id);
assert(session('success') !== null, "Deliver should succeed: " . session('error'));

$soDelivered = DB::table('sales_orders')->where('id', $newSo->id)->first();
assert($soDelivered->status === 'DELIVERED', "SO status should be DELIVERED");

// Cek dokumen GD dan AR
$gd = DB::table('goods_deliveries')->where('sales_order_id', $newSo->id)->first();
assert($gd !== null, "Goods Delivery (GD) document must be generated");
assert($gd->status === 'DELIVERED', "GD status DELIVERED");

$ar = DB::table('accounts_receivable')->where('sales_order_id', $newSo->id)->first();
assert($ar !== null, "Accounts Receivable (AR) invoice must be generated");
assert((float)$ar->total_amount === (float)$newSo->grand_total, "AR total amount matches SO grand total");

// Cek stok berkurang
$stockAfterDeliver = (int) DB::table('stock_balances')
    ->where('warehouse_id', $warehouse->id)
    ->where('product_id', $product->id)
    ->value('quantity');
assert($stockAfterDeliver === ($initialStock - 5), "Stock should decrease by 5 pcs upon delivery");

// Cek mutasi OUT tercatat
$mutation = DB::table('stock_mutations')
    ->where('doc_number', $gd->doc_number)
    ->where('product_id', $product->id)
    ->first();
assert($mutation !== null && $mutation->mutation_type === 'OUT' && (int)$mutation->quantity === 5, "Stock mutation OUT recorded");
echo "✓ Test 7: Sending Good (Deliver) processed successfully! GD: {$gd->doc_number}, AR: {$ar->doc_number}, Stock updated to {$stockAfterDeliver} pcs.\n";

// Test 8: Print Nota
$printView = $controller->printNota($newSo->id);
assert($printView instanceof \Illuminate\View\View, "Print nota must return View");
$printHtml = $printView->render();
assert(strpos($printHtml, $newSo->doc_number) !== false, "Print nota displays SO doc number");
assert(strpos($printHtml, $gd->doc_number) !== false, "Print nota displays GD doc number");
assert(strpos($printHtml, $ar->doc_number) !== false, "Print nota displays AR doc number");
assert(strpos($printHtml, '(Tracking:') === false, "Print nota hides tracking when ICCID is disabled");
echo "✓ Test 8: Print Nota rendered cleanly with all doc numbers and signatures.\n";

// Test 9: Rejection Flow
$soRejectReq = Request::create('/sales', 'POST', [
    'customer_id' => $customer->id,
    'sales_id' => $sales->id,
    'warehouse_id' => $warehouse->id,
    'order_date' => date('Y-m-d'),
    'notes' => 'Testing SO Reject',
    'items' => [
        ['product_id' => $product->id, 'quantity' => 2]
    ]
]);
$controller->store($soRejectReq);
$soToReject = DB::table('sales_orders')->orderBy('id', 'desc')->first();
assert($soToReject->status === 'PENDING_APPROVAL');

$rejectReq = Request::create("/sales/reject/{$soToReject->id}", 'POST');
$controller->reject($rejectReq, $soToReject->id);
$soRejected = DB::table('sales_orders')->where('id', $soToReject->id)->first();
assert($soRejected->status === 'REJECTED', "SO status should be REJECTED");
echo "✓ Test 9: SO Rejection flow verified. Status: {$soRejected->status}\n";

echo "\n=== ALL 9 SO TESTS PASSED SUCCESSFULLY! ===\n";
