<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "sales_returns exists? " . (Schema::hasTable('sales_returns') ? 'YES' : 'NO') . "\n";
echo "sales_orders columns:\n";
print_r(Schema::getColumnListing('sales_orders'));
echo "goods_deliveries columns:\n";
print_r(Schema::getColumnListing('goods_deliveries'));
echo "accounts_receivable columns:\n";
print_r(Schema::getColumnListing('accounts_receivable'));

