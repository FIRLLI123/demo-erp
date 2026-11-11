<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrackingController extends Controller
{
    /**
     * Tampilan Visual Live Map Tracking Sales
     */
    public function locations(Request $request)
    {
        // Ambil data sales asli dari database
        $salesDb = DB::table('sales')
            ->join('taps', 'sales.tap_id', '=', 'taps.id')
            ->leftJoin('warehouses', function ($join) {
                $join->on('warehouses.sales_id', '=', 'sales.id')
                     ->where('warehouses.type', '=', 'SALES');
            })
            ->select(
                'sales.id',
                'sales.code',
                'sales.name',
                'sales.phone',
                'sales.status',
                'taps.name as tap_name',
                'warehouses.name as warehouse_name'
            )
            ->where('sales.status', 'ACTIVE')
            ->get();

        // Data koordinat realistis di area operasional Jabodetabek (Jakarta Selatan & sekitarnya)
        $presetLocations = [
            1 => [
                'lat' => -6.2841,
                'lng' => 106.8436,
                'current_location' => 'Jl. Ragunan Raya No. 14, Pasar Minggu',
                'last_outlet' => 'Toko Berkah Cellindo (DGP-JKT-10021)',
                'speed' => '18 km/jam (Motor Kanvas)',
                'battery' => 88,
                'signal' => '5G Telkomsel (Kuat)',
                'visited_today' => 14,
                'target_today' => 20,
                'today_omset' => 4177500,
                'status_field' => 'VISIT TOKO',
                'status_color' => 'emerald',
                'last_ping' => '1 menit yang lalu',
            ],
            2 => [
                'lat' => -6.2995,
                'lng' => 106.8322,
                'current_location' => 'Jl. Kebagusan Wates No. 28, Jagakarsa',
                'last_outlet' => 'Toko Sinar Maju (DGP-JKT-10022)',
                'speed' => '0 km/jam (Sedang Transaksi)',
                'battery' => 74,
                'signal' => '4G+ Telkomsel (Penuh)',
                'visited_today' => 18,
                'target_today' => 22,
                'today_omset' => 6413500,
                'status_field' => 'TRANSAKSI SO',
                'status_color' => 'amber',
                'last_ping' => 'Baru saja',
            ],
            3 => [
                'lat' => -6.2442,
                'lng' => 106.7978,
                'current_location' => 'Jl. Melawai Raya No. 5, Blok M',
                'last_outlet' => 'Toko Jaya Abadi (DGP-JKT-10023)',
                'speed' => '24 km/jam (Dalam Perjalanan)',
                'battery' => 61,
                'signal' => '5G Telkomsel (Kuat)',
                'visited_today' => 11,
                'target_today' => 18,
                'today_omset' => 4875000,
                'status_field' => 'OTW OUTLET',
                'status_color' => 'blue',
                'last_ping' => '3 menit yang lalu',
            ],
        ];

        $trackingData = [];
        foreach ($salesDb as $idx => $s) {
            $preset = $presetLocations[$s->id] ?? [
                'lat' => -6.2700 - ($idx * 0.015),
                'lng' => 106.8200 + ($idx * 0.02),
                'current_location' => 'Wilayah TAP ' . $s->tap_name,
                'last_outlet' => 'Konter Perdana Telkomsel',
                'speed' => '15 km/jam',
                'battery' => 80,
                'signal' => '4G Telkomsel',
                'visited_today' => 8,
                'target_today' => 15,
                'today_omset' => 2500000,
                'status_field' => 'ON FIELD',
                'status_color' => 'emerald',
                'last_ping' => '5 menit yang lalu',
            ];

            $trackingData[] = (object) array_merge((array)$s, $preset);
        }

        return view('tracking.locations', compact('trackingData'));
    }

    /**
     * Informasi Spesifikasi Device Mobile Sales Force
     */
    public function devices(Request $request)
    {
        $salesDb = DB::table('sales')
            ->join('taps', 'sales.tap_id', '=', 'taps.id')
            ->select('sales.*', 'taps.name as tap_name')
            ->where('sales.status', 'ACTIVE')
            ->get();

        $devicePresets = [
            1 => [
                'device_model' => 'Samsung Galaxy A54 5G',
                'brand' => 'Samsung',
                'os_name' => 'Android 14 (One UI 6.1)',
                'android_version' => '14',
                'app_version' => 'v2.4.1 (Build 20261108)',
                'digipos_version' => 'DigiPOS Aja! v4.14.2',
                'installed_at' => '15 Okt 2026',
                'last_sync' => '09 Nov 2026, 16:45 WIB',
                'storage_used' => '42.8 GB / 128 GB',
                'ram' => '8 GB',
                'battery_level' => 88,
                'imei' => '358941209841204',
                'security_status' => 'COMPLIANT & SECURE',
                'mock_gps_detected' => false,
                'is_rooted' => false,
            ],
            2 => [
                'device_model' => 'Xiaomi Redmi Note 13 Pro 5G',
                'brand' => 'Xiaomi',
                'os_name' => 'Android 14 (HyperOS 1.0)',
                'android_version' => '14',
                'app_version' => 'v2.4.1 (Build 20261108)',
                'digipos_version' => 'DigiPOS Aja! v4.14.2',
                'installed_at' => '02 Nov 2026',
                'last_sync' => '09 Nov 2026, 16:58 WIB',
                'storage_used' => '58.2 GB / 256 GB',
                'ram' => '8 GB',
                'battery_level' => 74,
                'imei' => '864219058194012',
                'security_status' => 'COMPLIANT & SECURE',
                'mock_gps_detected' => false,
                'is_rooted' => false,
            ],
            3 => [
                'device_model' => 'OPPO Reno 11F 5G',
                'brand' => 'OPPO',
                'os_name' => 'Android 14 (ColorOS 14.0)',
                'android_version' => '14',
                'app_version' => 'v2.4.0 (Build 20261028)',
                'digipos_version' => 'DigiPOS Aja! v4.13.8',
                'installed_at' => '28 Okt 2026',
                'last_sync' => '09 Nov 2026, 16:30 WIB',
                'storage_used' => '64.1 GB / 256 GB',
                'ram' => '8 GB',
                'battery_level' => 61,
                'imei' => '356781290384192',
                'security_status' => 'COMPLIANT & SECURE',
                'mock_gps_detected' => false,
                'is_rooted' => false,
            ],
        ];

        $devices = [];
        foreach ($salesDb as $idx => $s) {
            $preset = $devicePresets[$s->id] ?? [
                'device_model' => 'Vivo Y27 5G',
                'brand' => 'Vivo',
                'os_name' => 'Android 13 (Funtouch OS 13)',
                'android_version' => '13',
                'app_version' => 'v2.4.1 (Build 20261108)',
                'digipos_version' => 'DigiPOS Aja! v4.14.0',
                'installed_at' => '01 Nov 2026',
                'last_sync' => '09 Nov 2026, 16:15 WIB',
                'storage_used' => '38.0 GB / 128 GB',
                'ram' => '6 GB',
                'battery_level' => 80,
                'imei' => '861948209481920',
                'security_status' => 'COMPLIANT & SECURE',
                'mock_gps_detected' => false,
                'is_rooted' => false,
            ];

            $devices[] = (object) array_merge((array)$s, $preset);
        }

        return view('tracking.devices', compact('devices'));
    }

    /**
     * Tampilan Rencana Kunjungan & Call Plan (PJP - Periodic Journey Plan)
     */
    public function pjp(Request $request)
    {
        $salesList = DB::table('sales')
            ->join('taps', 'sales.tap_id', '=', 'taps.id')
            ->select('sales.id', 'sales.code', 'sales.name', 'sales.phone', 'taps.name as tap_name')
            ->where('sales.status', 'ACTIVE')
            ->get();

        $selectedSalesId = (int)$request->input('sales_id', $salesList->first()->id ?? 1);
        $selectedDay = $request->input('day', 'Senin');

        $activeSales = $salesList->firstWhere('id', $selectedSalesId) ?? $salesList->first();

        // Ambil data customer asli dari DB untuk dipetakan ke rute
        $customersDb = DB::table('customers')->orderBy('id')->get();

        // Rute jadwal mingguan realistis
        $daysSchedule = [
            'Senin' => [
                'area_cluster' => 'Rute Jalur 1 - Ragunan & Pasar Minggu',
                'target_outlets' => 12,
                'target_omset' => 6500000,
            ],
            'Selasa' => [
                'area_cluster' => 'Rute Jalur 2 - Kebagusan, Lenteng Agung & Jagakarsa',
                'target_outlets' => 14,
                'target_omset' => 7500000,
            ],
            'Rabu' => [
                'area_cluster' => 'Rute Jalur 3 - Cilandak, Ampera & Pejaten',
                'target_outlets' => 11,
                'target_omset' => 6000000,
            ],
            'Kamis' => [
                'area_cluster' => 'Rute Jalur 4 - Mampang Prapatan & Warung Buncit',
                'target_outlets' => 13,
                'target_omset' => 7000000,
            ],
            'Jumat' => [
                'area_cluster' => 'Rute Jalur 5 - Tebet Barat & Manggarai',
                'target_outlets' => 10,
                'target_omset' => 5500000,
            ],
            'Sabtu' => [
                'area_cluster' => 'Rute Jalur 6 - Pancoran & Kalibata (Weekend Push)',
                'target_outlets' => 15,
                'target_omset' => 9000000,
            ],
        ];

        // Rencana kunjungan toko untuk hari terpilih (berurutan)
        $routeStops = [
            [
                'stop_no' => 1,
                'time_plan' => '08:30 - 09:00',
                'customer_name' => 'Toko Berkah Cellindo',
                'digipos_id' => 'DGP-JKT-10021',
                'no_rs' => '081299887711',
                'address' => 'Jl. Ragunan Raya No. 14, Pasar Minggu',
                'pic' => 'Haji Mansur',
                'phone' => '081299887711',
                'status' => 'EFFECTIVE_CALL',
                'checkin_time' => '08:42 WIB',
                'checkout_time' => '09:05 WIB',
                'duration' => '23 mnt',
                'so_doc' => 'SO/202611/0014',
                'omset' => 1450000,
                'notes' => 'Restok perdana Telkomsel 5GB (30 pcs) & Voucher 2.5GB (50 pcs). Toko ramai lancar.',
                'lat' => -6.2841,
                'lng' => 106.8436,
                'photo_verified' => true,
            ],
            [
                'stop_no' => 2,
                'time_plan' => '09:15 - 09:45',
                'customer_name' => 'Sinar Maju Komunika',
                'digipos_id' => 'DGP-JKT-10022',
                'no_rs' => '082188776622',
                'address' => 'Jl. Kebagusan Raya No. 45, Pasar Minggu',
                'pic' => 'Bambang S.',
                'phone' => '082188776622',
                'status' => 'EFFECTIVE_CALL',
                'checkin_time' => '09:20 WIB',
                'checkout_time' => '09:41 WIB',
                'duration' => '21 mnt',
                'so_doc' => 'SO/202611/0015',
                'omset' => 890000,
                'notes' => 'Order Voucher Fisik NGRS & saldo Digipos. Minta brosur promo Telkomsel terbaru.',
                'lat' => -6.2895,
                'lng' => 106.8402,
                'photo_verified' => true,
            ],
            [
                'stop_no' => 3,
                'time_plan' => '10:00 - 10:30',
                'customer_name' => 'Mitra Rezeki Pulsa',
                'digipos_id' => 'DGP-JKT-10024',
                'no_rs' => '081155443311',
                'address' => 'Jl. Jatipadang Barat No. 12',
                'pic' => 'Ibu Ratna',
                'phone' => '081155443311',
                'status' => 'VISIT_ONLY',
                'checkin_time' => '10:08 WIB',
                'checkout_time' => '10:19 WIB',
                'duration' => '11 mnt',
                'so_doc' => null,
                'omset' => 0,
                'notes' => 'Stok perdana masih ada 15 pcs. Janji repeat order di hari Kamis pada rute berikutnya.',
                'lat' => -6.2810,
                'lng' => 106.8375,
                'photo_verified' => true,
            ],
            [
                'stop_no' => 4,
                'time_plan' => '10:45 - 11:15',
                'customer_name' => 'Bintang Phone Cell',
                'digipos_id' => 'DGP-JKT-10025',
                'no_rs' => '085233221100',
                'address' => 'Jl. Salihara No. 8, Pasar Minggu',
                'pic' => 'Ko Kevin',
                'phone' => '085233221100',
                'status' => 'EFFECTIVE_CALL',
                'checkin_time' => '10:52 WIB',
                'checkout_time' => '11:14 WIB',
                'duration' => '22 mnt',
                'so_doc' => 'SO/202611/0016',
                'omset' => 2120000,
                'notes' => 'Ambil paket perdana kuota besar SA 14D & voucher Telkomsel. Pembayaran transfer langsung.',
                'lat' => -6.2870,
                'lng' => 106.8480,
                'photo_verified' => true,
            ],
            [
                'stop_no' => 5,
                'time_plan' => '11:30 - 12:00',
                'customer_name' => 'Dua Saudara Telco',
                'digipos_id' => 'DGP-JKT-10026',
                'no_rs' => '081377889922',
                'address' => 'Jl. Sawo Manila No. 19',
                'pic' => 'Pak Joko',
                'phone' => '081377889922',
                'status' => 'CLOSED',
                'checkin_time' => '11:35 WIB',
                'checkout_time' => '11:40 WIB',
                'duration' => '5 mnt',
                'so_doc' => null,
                'omset' => 0,
                'notes' => 'Pintu folding gate tertutup (pemilik sedang melayat). Sudah konfirmasi via WhatsApp.',
                'lat' => -6.2912,
                'lng' => 106.8445,
                'photo_verified' => true,
            ],
            [
                'stop_no' => 6,
                'time_plan' => '13:00 - 13:30',
                'customer_name' => 'Sahabat Cell Community',
                'digipos_id' => 'DGP-JKT-10027',
                'no_rs' => '082266554433',
                'address' => 'Jl. Margasatwa Raya No. 7',
                'pic' => 'Andi Wijaya',
                'phone' => '082266554433',
                'status' => 'EFFECTIVE_CALL',
                'checkin_time' => '13:05 WIB',
                'checkout_time' => '13:28 WIB',
                'duration' => '23 mnt',
                'so_doc' => 'SO/202611/0017',
                'omset' => 1750000,
                'notes' => 'Penggantian poster etalase Telkomsel & drop perdana reguler 20 pcs.',
                'lat' => -6.2950,
                'lng' => 106.8390,
                'photo_verified' => true,
            ],
            [
                'stop_no' => 7,
                'time_plan' => '13:45 - 14:15',
                'customer_name' => 'Jaya Abadi Cell',
                'digipos_id' => 'DGP-JKT-10023',
                'no_rs' => '081311223344',
                'address' => 'Jl. TB Simatupang No. 88',
                'pic' => 'Hendro',
                'phone' => '081311223344',
                'status' => 'PENDING',
                'checkin_time' => null,
                'checkout_time' => null,
                'duration' => null,
                'so_doc' => null,
                'omset' => 0,
                'notes' => 'Rute kunjungan berikutnya sesudah istirahat siang.',
                'lat' => -6.2990,
                'lng' => 106.8420,
                'photo_verified' => false,
            ],
            [
                'stop_no' => 8,
                'time_plan' => '14:30 - 15:00',
                'customer_name' => 'Fajar Telekomunikasi',
                'digipos_id' => 'DGP-JKT-10028',
                'no_rs' => '081288990011',
                'address' => 'Jl. Pejaten Barat Raya No. 25',
                'pic' => 'Fajar Sidik',
                'phone' => '081288990011',
                'status' => 'PENDING',
                'checkin_time' => null,
                'checkout_time' => null,
                'duration' => null,
                'so_doc' => null,
                'omset' => 0,
                'notes' => 'Jadwal sore.',
                'lat' => -6.2750,
                'lng' => 106.8330,
                'photo_verified' => false,
            ],
        ];

        // Hitung metrik KPI PJP
        $totalPlanned = count($routeStops);
        $visitedCount = collect($routeStops)->whereIn('status', ['EFFECTIVE_CALL', 'VISIT_ONLY', 'CLOSED'])->count();
        $effectiveCalls = collect($routeStops)->where('status', 'EFFECTIVE_CALL')->count();
        $visitOnly = collect($routeStops)->where('status', 'VISIT_ONLY')->count();
        $closedCount = collect($routeStops)->where('status', 'CLOSED')->count();
        $pendingCount = collect($routeStops)->where('status', 'PENDING')->count();
        $totalOmsetAchieved = collect($routeStops)->sum('omset');

        $strikeRate = $totalPlanned > 0 ? round(($effectiveCalls / $totalPlanned) * 100, 1) : 0;
        $callCompliance = $totalPlanned > 0 ? round(($visitedCount / $totalPlanned) * 100, 1) : 0;

        return view('tracking.pjp', compact(
            'salesList',
            'activeSales',
            'selectedDay',
            'daysSchedule',
            'routeStops',
            'totalPlanned',
            'visitedCount',
            'effectiveCalls',
            'visitOnly',
            'closedCount',
            'pendingCount',
            'totalOmsetAchieved',
            'strikeRate',
            'callCompliance'
        ));
    }
}

