<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Cluster;
use App\Models\ClusterSoCutoff;
use App\Services\OrderCutoffService;
use Carbon\Carbon;

echo "--- 1. Testing Default Cutoff Behavior ---\n";
$cluster = Cluster::first();
if (!$cluster) {
    echo "No cluster found in DB!\n";
    exit;
}
echo "Testing with Cluster ID: {$cluster->id} ({$cluster->name})\n";

// Skenario A: Pagi hari (09:00:00) vs Cutoff 17:00:00
$morning = Carbon::today()->setTime(9, 0, 0);
$cutoff = ClusterSoCutoff::updateOrCreate(
    ['cluster_id' => $cluster->id],
    ['close_time' => '17:00:00', 'is_active' => true, 'is_force_closed' => false]
);
$resMorning = $cutoff->checkStatus($morning);
echo "Skenario Pagi (09:00): Status=" . ($resMorning['is_open'] ? 'OPEN (PASS)' : 'CLOSED (FAIL)') . " - {$resMorning['reason']}\n";

// Skenario B: Malam hari (18:00:00) vs Cutoff 17:00:00
$evening = Carbon::today()->setTime(18, 0, 0);
$resEvening = $cutoff->checkStatus($evening);
echo "Skenario Malam (18:00): Status=" . (!$resEvening['is_open'] ? 'CLOSED (PASS)' : 'OPEN (FAIL)') . " - {$resEvening['reason']}\n";

// Skenario C: Besok Pagi (08:00:00) -> Otomatis OPEN kembali
$tomorrowMorning = Carbon::tomorrow()->setTime(8, 0, 0);
$resTomorrow = $cutoff->checkStatus($tomorrowMorning);
echo "Skenario Besok Pagi (08:00): Status=" . ($resTomorrow['is_open'] ? 'OPEN (PASS)' : 'CLOSED (FAIL)') . " - {$resTomorrow['reason']}\n";

// Skenario D: Admin Force Close Hari Ini
echo "\n--- 2. Testing Force Close & Automatic Reset Tomorrow ---\n";
$today = Carbon::today()->toDateString();
$cutoff->update([
    'is_force_closed' => true,
    'force_closed_date' => $today,
    'notes' => 'Tutup mendadak karena stock opname'
]);

// Cek di hari ini saat siang (11:00)
$noonToday = Carbon::today()->setTime(11, 0, 0);
$resForceToday = $cutoff->checkStatus($noonToday);
echo "Force Close Hari Ini (11:00): Status=" . (!$resForceToday['is_open'] ? 'CLOSED (PASS)' : 'OPEN (FAIL)') . " - {$resForceToday['reason']}\n";

// Cek di BESOK HARI saat siang (11:00) -> Harus otomatis OPEN kembali karena beda tanggal
$noonTomorrow = Carbon::tomorrow()->setTime(11, 0, 0);
$resForceTomorrow = $cutoff->checkStatus($noonTomorrow);
echo "Besok Hari (11:00): Status=" . ($resForceTomorrow['is_open'] ? 'OPEN (PASS - Otomatis Reset)' : 'CLOSED (FAIL)') . " - {$resForceTomorrow['reason']}\n";

// Reset kembali ke normal
$cutoff->update([
    'is_force_closed' => false,
    'force_closed_date' => null,
    'notes' => null
]);

echo "\n--- 3. Testing Service with Customer ID ---\n";
$customer = \App\Models\Customer::first();
if ($customer) {
    $custRes = OrderCutoffService::checkByCustomerId($customer->id, $morning);
    echo "Check Customer ID {$customer->id}: Status=" . ($custRes['is_open'] ? 'OPEN' : 'CLOSED') . " - {$custRes['reason']}\n";
}

echo "\nAll Tests Finished Successfully!\n";
