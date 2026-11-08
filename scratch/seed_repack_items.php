<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// 1. Insert Products if not exist
$itemsToAdd = [
    [
        'category_id' => 1,
        'sku' => 'RAW-SP-0K',
        'name' => 'S261 Telkomsel SIMPATI Territory BKS 0K (Bahan)',
        'type' => 'SA',
        'tracking_method' => 'ICCID',
        'cost_price' => 10000.00,
        'selling_price' => 0.00,
        'status' => 'ACTIVE',
    ],
    [
        'category_id' => 2,
        'sku' => 'RAW-VCR-0K',
        'name' => 'Voucher Fisik Telkomsel Blank 0K (Bahan)',
        'type' => 'VOUCHER',
        'tracking_method' => 'ICCID',
        'cost_price' => 1000.00,
        'selling_price' => 0.00,
        'status' => 'ACTIVE',
    ],
    [
        'category_id' => 1,
        'sku' => 'BULK-NGRS-SA',
        'name' => 'NGRS BULK SA NEW (Saldo Injeksi)',
        'type' => 'SA',
        'tracking_method' => 'QUANTITY',
        'cost_price' => 920.00,
        'selling_price' => 1000.00,
        'status' => 'ACTIVE',
    ],
    [
        'category_id' => 2,
        'sku' => 'BULK-NGRS-VCR',
        'name' => 'NGRS BULK VOUCHER NEW (Saldo Injeksi)',
        'type' => 'VOUCHER',
        'tracking_method' => 'QUANTITY',
        'cost_price' => 1000.00,
        'selling_price' => 1100.00,
        'status' => 'ACTIVE',
    ],
    [
        'category_id' => 1,
        'sku' => 'SA-5GB-14D-BKS',
        'name' => 'SA 5GB 14D BKS NOV (Hasil Repack)',
        'type' => 'SA',
        'tracking_method' => 'ICCID',
        'cost_price' => 23708.00,
        'selling_price' => 28000.00,
        'status' => 'ACTIVE',
    ]
];

foreach ($itemsToAdd as $item) {
    $existing = DB::table('products')->where('sku', $item['sku'])->first();
    if (!$existing) {
        DB::table('products')->insert(array_merge($item, ['created_at' => now(), 'updated_at' => now()]));
        echo "Inserted product: {$item['name']}\n";
    }
}

// 2. Beri saldo di Gudang MAIN TAP Pasar Minggu (id: 1)
$mainWh = DB::table('warehouses')->where('type', 'MAIN')->first();
if ($mainWh) {
    $rawSp = DB::table('products')->where('sku', 'RAW-SP-0K')->first();
    $rawVcr = DB::table('products')->where('sku', 'RAW-VCR-0K')->first();
    $bulkSa = DB::table('products')->where('sku', 'BULK-NGRS-SA')->first();
    $bulkVcr = DB::table('products')->where('sku', 'BULK-NGRS-VCR')->first();

    // Saldo Bulk
    DB::table('stock_balances')->updateOrInsert(
        ['warehouse_id' => $mainWh->id, 'product_id' => $bulkSa->id],
        ['quantity' => 100000, 'reserved_quantity' => 0, 'updated_at' => now()]
    );
    DB::table('stock_balances')->updateOrInsert(
        ['warehouse_id' => $mainWh->id, 'product_id' => $bulkVcr->id],
        ['quantity' => 50000, 'reserved_quantity' => 0, 'updated_at' => now()]
    );

    // Saldo Fisik 0K
    DB::table('stock_balances')->updateOrInsert(
        ['warehouse_id' => $mainWh->id, 'product_id' => $rawSp->id],
        ['quantity' => 50, 'reserved_quantity' => 0, 'updated_at' => now()]
    );
    DB::table('stock_balances')->updateOrInsert(
        ['warehouse_id' => $mainWh->id, 'product_id' => $rawVcr->id],
        ['quantity' => 50, 'reserved_quantity' => 0, 'updated_at' => now()]
    );

    // Generate ICCID units untuk bahan 0K
    for ($i = 1; $i <= 30; $i++) {
        $iccidSp = '896200000000' . str_pad($i, 4, '0', STR_PAD_LEFT);
        DB::table('iccid_units')->updateOrInsert(
            ['iccid_number' => $iccidSp],
            [
                'product_id' => $rawSp->id,
                'warehouse_id' => $mainWh->id,
                'status' => 'AVAILABLE',
                'last_doc_type' => 'GR',
                'last_doc_number' => 'GR/202611/0001',
                'updated_at' => now()
            ]
        );

        $iccidVcr = '896211110000' . str_pad($i, 4, '0', STR_PAD_LEFT);
        DB::table('iccid_units')->updateOrInsert(
            ['iccid_number' => $iccidVcr],
            [
                'product_id' => $rawVcr->id,
                'warehouse_id' => $mainWh->id,
                'status' => 'AVAILABLE',
                'last_doc_type' => 'GR',
                'last_doc_number' => 'GR/202611/0001',
                'updated_at' => now()
            ]
        );
    }
    echo "Seeded stock balance & 60 ICCIDs for raw repack components in {$mainWh->name}!\n";
}
