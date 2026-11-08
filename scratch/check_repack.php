<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "REPACKS COLUMNS:\n";
print_r(Schema::getColumnListing('repacks'));

echo "\nREPACK_ITEMS COLUMNS:\n";
print_r(Schema::getColumnListing('repack_items'));
