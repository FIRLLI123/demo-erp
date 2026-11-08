<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "PAYMENTS (TTK) COLUMNS:\n";
print_r(Schema::getColumnListing('payments'));

echo "\nBANK_RECEIPTS (BBM) COLUMNS:\n";
print_r(Schema::getColumnListing('bank_receipts'));

echo "\nACCOUNTS RECEIVABLE COUNT:\n";
$ar = DB::table('accounts_receivable')->get();
foreach ($ar as $a) {
    echo "- [{$a->id}] {$a->doc_number} | Customer: {$a->customer_id} | Total: {$a->total_amount} | Paid: {$a->paid_amount} | Bal: {$a->balance_amount} | Status: {$a->status}\n";
}

