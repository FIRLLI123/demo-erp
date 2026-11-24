<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$salesList = DB::table('sales')->get();
echo "Total Sales: " . count($salesList) . "\n";
foreach ($salesList as $s) {
    // Cari gudang SALES yang terhubung ke sales_id ini
    $whSales = DB::table('warehouses')->where('sales_id', $s->id)->first();
    // Cari gudang utama TAP jika belum punya gudang khusus sales
    $whTap = DB::table('warehouses')->where('tap_id', $s->tap_id)->first();

    echo "Sales ID {$s->id} ({$s->code} - {$s->name}) TAP={$s->tap_id}: " . 
         ($whSales ? "Gudang Khusus: [{$whSales->id}] {$whSales->name} ({$whSales->type})" : "Gudang TAP: [{$whTap->id}] {$whTap->name} ({$whTap->type})") . "\n";
}
