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
}
