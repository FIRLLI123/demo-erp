<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$bbmCols = DB::select('SHOW COLUMNS FROM bank_receipts');
echo "Columns in bank_receipts:\n";
foreach ($bbmCols as $col) {
    echo " - {$col->Field} ({$col->Type})\n";
}

$payCols = DB::select('SHOW COLUMNS FROM payments');
echo "\nColumns in payments:\n";
foreach ($payCols as $col) {
    echo " - {$col->Field} ({$col->Type})\n";
}
