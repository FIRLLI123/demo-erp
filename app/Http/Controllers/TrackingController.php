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

        // Rencana rute kunjungan per Sales dan per Hari agar data dinamis dan berbeda tiap sales
        $salesPjpPresets = [
            // SALES 1: Rian Hidayat (Area Pasar Minggu & Ragunan)
            1 => [
                'Senin' => [
                    'area_cluster' => 'Rute Jalur 1 - Ragunan & Pasar Minggu',
                    'target_omset' => 6500000,
                    'stops' => [
                        [
                            'stop_no' => 1,
                            'time_plan' => '08:30 - 09:00',
                            'customer_name' => 'Toko Berkah Cellindo',
                            'digipos_id' => 'DGP-JKT-10021',
                            'no_rs' => '081299887711',
                            'address' => 'Jl. Ragunan Raya No. 14, Pasar Minggu',
                            'pic' => 'Haji Mansur',
                            'status' => 'EFFECTIVE_CALL',
                            'checkin_time' => '08:42 WIB',
                            'duration' => '23 mnt',
                            'omset' => 1450000,
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
                            'address' => 'Jl. Kebagusan Raya No. 45',
                            'pic' => 'Bambang S.',
                            'status' => 'EFFECTIVE_CALL',
                            'checkin_time' => '09:20 WIB',
                            'duration' => '21 mnt',
                            'omset' => 890000,
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
                            'status' => 'VISIT_ONLY',
                            'checkin_time' => '10:08 WIB',
                            'duration' => '11 mnt',
                            'omset' => 0,
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
                            'address' => 'Jl. Salihara No. 8',
                            'pic' => 'Ko Kevin',
                            'status' => 'EFFECTIVE_CALL',
                            'checkin_time' => '10:52 WIB',
                            'duration' => '22 mnt',
                            'omset' => 2120000,
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
                            'status' => 'CLOSED',
                            'checkin_time' => '11:35 WIB',
                            'duration' => '5 mnt',
                            'omset' => 0,
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
                            'status' => 'EFFECTIVE_CALL',
                            'checkin_time' => '13:05 WIB',
                            'duration' => '23 mnt',
                            'omset' => 1750000,
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
                            'status' => 'PENDING',
                            'checkin_time' => null,
                            'duration' => null,
                            'omset' => 0,
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
                            'status' => 'PENDING',
                            'checkin_time' => null,
                            'duration' => null,
                            'omset' => 0,
                            'lat' => -6.2750,
                            'lng' => 106.8330,
                            'photo_verified' => false,
                        ],
                    ]
                ],
                'Selasa' => [
                    'area_cluster' => 'Rute Jalur 2 - Kebagusan & Jagakarsa',
                    'target_omset' => 7200000,
                    'stops' => [
                        [
                            'stop_no' => 1,
                            'time_plan' => '08:30 - 09:00',
                            'customer_name' => 'Jagakarsa Reload Center',
                            'digipos_id' => 'DGP-JKT-10031',
                            'no_rs' => '081299112233',
                            'address' => 'Jl. Jagakarsa Raya No. 10',
                            'pic' => 'Pak Usman',
                            'status' => 'EFFECTIVE_CALL',
                            'checkin_time' => '08:35 WIB',
                            'duration' => '25 mnt',
                            'omset' => 1950000,
                            'lat' => -6.3250,
                            'lng' => 106.8280,
                            'photo_verified' => true,
                        ],
                        [
                            'stop_no' => 2,
                            'time_plan' => '09:15 - 09:45',
                            'customer_name' => 'Lenteng Ponsel 88',
                            'digipos_id' => 'DGP-JKT-10032',
                            'no_rs' => '081388771122',
                            'address' => 'Jl. Lenteng Agung Timur No. 4',
                            'pic' => 'Hengky',
                            'status' => 'EFFECTIVE_CALL',
                            'checkin_time' => '09:22 WIB',
                            'duration' => '18 mnt',
                            'omset' => 1420000,
                            'lat' => -6.3320,
                            'lng' => 106.8350,
                            'photo_verified' => true,
                        ],
                        [
                            'stop_no' => 3,
                            'time_plan' => '10:00 - 10:30',
                            'customer_name' => 'Srengseng Sawah Cell',
                            'digipos_id' => 'DGP-JKT-10033',
                            'no_rs' => '082166554411',
                            'address' => 'Jl. Srengseng Sawah No. 55',
                            'pic' => 'Rudi Hartono',
                            'status' => 'EFFECTIVE_CALL',
                            'checkin_time' => '10:10 WIB',
                            'duration' => '20 mnt',
                            'omset' => 1650000,
                            'lat' => -6.3450,
                            'lng' => 106.8300,
                            'photo_verified' => true,
                        ],
                        [
                            'stop_no' => 4,
                            'time_plan' => '10:45 - 11:15',
                            'customer_name' => 'Cipedak Komunika',
                            'digipos_id' => 'DGP-JKT-10034',
                            'no_rs' => '081233445566',
                            'address' => 'Jl. Mohammad Kahfi II No. 18',
                            'pic' => 'Ibu Maya',
                            'status' => 'VISIT_ONLY',
                            'checkin_time' => '10:50 WIB',
                            'duration' => '12 mnt',
                            'omset' => 0,
                            'lat' => -6.3500,
                            'lng' => 106.8180,
                            'photo_verified' => true,
                        ],
                        [
                            'stop_no' => 5,
                            'time_plan' => '11:30 - 12:00',
                            'customer_name' => 'Kahfi Phone Store',
                            'digipos_id' => 'DGP-JKT-10035',
                            'no_rs' => '085299881122',
                            'address' => 'Jl. Mohammad Kahfi I No. 90',
                            'pic' => 'Dedi',
                            'status' => 'EFFECTIVE_CALL',
                            'checkin_time' => '11:32 WIB',
                            'duration' => '24 mnt',
                            'omset' => 2430000,
                            'lat' => -6.3380,
                            'lng' => 106.8090,
                            'photo_verified' => true,
                        ],
                        [
                            'stop_no' => 6,
                            'time_plan' => '13:00 - 13:30',
                            'customer_name' => 'Warung Silaturahmi Pulsa',
                            'digipos_id' => 'DGP-JKT-10036',
                            'no_rs' => '081344556677',
                            'address' => 'Jl. Sirsak No. 22',
                            'pic' => 'Pak Slamet',
                            'status' => 'PENDING',
                            'checkin_time' => null,
                            'duration' => null,
                            'omset' => 0,
                            'lat' => -6.3300,
                            'lng' => 106.8200,
                            'photo_verified' => false,
                        ],
                    ]
                ]
            ],

            // SALES 2: Budi Santoso (Area Tebet, Manggarai & Pancoran)
            2 => [
                'Senin' => [
                    'area_cluster' => 'Rute Jalur 1 - Tebet & Saharjo',
                    'target_omset' => 8800000,
                    'stops' => [
                        [
                            'stop_no' => 1,
                            'time_plan' => '08:30 - 09:00',
                            'customer_name' => 'Berkah Ponsel Tebet Barat',
                            'digipos_id' => 'DGP-JKT-20011',
                            'no_rs' => '081211224455',
                            'address' => 'Jl. Tebet Barat Dalam Raya No. 30',
                            'pic' => 'Ko Hendra',
                            'status' => 'EFFECTIVE_CALL',
                            'checkin_time' => '08:38 WIB',
                            'duration' => '22 mnt',
                            'omset' => 2850000,
                            'lat' => -6.2370,
                            'lng' => 106.8510,
                            'photo_verified' => true,
                        ],
                        [
                            'stop_no' => 2,
                            'time_plan' => '09:15 - 09:45',
                            'customer_name' => 'Sentral Pulsa Saharjo',
                            'digipos_id' => 'DGP-JKT-20012',
                            'no_rs' => '081399884433',
                            'address' => 'Jl. Dr. Saharjo No. 102',
                            'pic' => 'Bang Ipul',
                            'status' => 'EFFECTIVE_CALL',
                            'checkin_time' => '09:20 WIB',
                            'duration' => '19 mnt',
                            'omset' => 1650000,
                            'lat' => -6.2280,
                            'lng' => 106.8480,
                            'photo_verified' => true,
                        ],
                        [
                            'stop_no' => 3,
                            'time_plan' => '10:00 - 10:30',
                            'customer_name' => 'Kencana Cell Soepomo',
                            'digipos_id' => 'DGP-JKT-20013',
                            'no_rs' => '085277889900',
                            'address' => 'Jl. Prof. Dr. Soepomo No. 45',
                            'pic' => 'Ibu Anita',
                            'status' => 'EFFECTIVE_CALL',
                            'checkin_time' => '10:05 WIB',
                            'duration' => '28 mnt',
                            'omset' => 3420000,
                            'lat' => -6.2330,
                            'lng' => 106.8440,
                            'photo_verified' => true,
                        ],
                        [
                            'stop_no' => 4,
                            'time_plan' => '10:45 - 11:15',
                            'customer_name' => 'Tebet Timur Komunika',
                            'digipos_id' => 'DGP-JKT-20014',
                            'no_rs' => '081288223344',
                            'address' => 'Jl. Tebet Timur Raya No. 18',
                            'pic' => 'Pak Yanto',
                            'status' => 'EFFECTIVE_CALL',
                            'checkin_time' => '10:52 WIB',
                            'duration' => '17 mnt',
                            'omset' => 1250000,
                            'lat' => -6.2410,
                            'lng' => 106.8580,
                            'photo_verified' => true,
                        ],
                        [
                            'stop_no' => 5,
                            'time_plan' => '11:30 - 12:00',
                            'customer_name' => 'Gudang Voucher Cassablanca',
                            'digipos_id' => 'DGP-JKT-20015',
                            'no_rs' => '081322119988',
                            'address' => 'Jl. Casablanca No. 8',
                            'pic' => 'Willy',
                            'status' => 'VISIT_ONLY',
                            'checkin_time' => '11:34 WIB',
                            'duration' => '10 mnt',
                            'omset' => 0,
                            'lat' => -6.2240,
                            'lng' => 106.8550,
                            'photo_verified' => true,
                        ],
                        [
                            'stop_no' => 6,
                            'time_plan' => '13:00 - 13:30',
                            'customer_name' => 'Manggarai Phone Hub',
                            'digipos_id' => 'DGP-JKT-20016',
                            'no_rs' => '082199883322',
                            'address' => 'Jl. Manggarai Utara No. 15',
                            'pic' => 'Zul',
                            'status' => 'PENDING',
                            'checkin_time' => null,
                            'duration' => null,
                            'omset' => 0,
                            'lat' => -6.2110,
                            'lng' => 106.8500,
                            'photo_verified' => false,
                        ],
                        [
                            'stop_no' => 7,
                            'time_plan' => '13:45 - 14:15',
                            'customer_name' => 'Bukit Duri Cellular',
                            'digipos_id' => 'DGP-JKT-20017',
                            'no_rs' => '081277665544',
                            'address' => 'Jl. Bukit Duri Tanjakan No. 8',
                            'pic' => 'Syarif',
                            'status' => 'PENDING',
                            'checkin_time' => null,
                            'duration' => null,
                            'omset' => 0,
                            'lat' => -6.2190,
                            'lng' => 106.8570,
                            'photo_verified' => false,
                        ],
                    ]
                ],
            ],

            // SALES 3: Dimas Pratama (Area Blok M & Kebayoran Baru)
            3 => [
                'Senin' => [
                    'area_cluster' => 'Rute Jalur 1 - Blok M & Melawai',
                    'target_omset' => 6000000,
                    'stops' => [
                        [
                            'stop_no' => 1,
                            'time_plan' => '08:30 - 09:00',
                            'customer_name' => 'Plaza Phone Melawai',
                            'digipos_id' => 'DGP-JKT-30011',
                            'no_rs' => '081288445566',
                            'address' => 'Jl. Melawai Raya No. 12',
                            'pic' => 'Ibu Cynthia',
                            'status' => 'EFFECTIVE_CALL',
                            'checkin_time' => '08:40 WIB',
                            'duration' => '20 mnt',
                            'omset' => 1850000,
                            'lat' => -6.2442,
                            'lng' => 106.7978,
                            'photo_verified' => true,
                        ],
                        [
                            'stop_no' => 2,
                            'time_plan' => '09:15 - 09:45',
                            'customer_name' => 'Mahakam Komunika',
                            'digipos_id' => 'DGP-JKT-30012',
                            'no_rs' => '081377441122',
                            'address' => 'Jl. Mahakam I No. 4',
                            'pic' => 'Pak Erwin',
                            'status' => 'EFFECTIVE_CALL',
                            'checkin_time' => '09:18 WIB',
                            'duration' => '25 mnt',
                            'omset' => 1400000,
                            'lat' => -6.2420,
                            'lng' => 106.7950,
                            'photo_verified' => true,
                        ],
                        [
                            'stop_no' => 3,
                            'time_plan' => '10:00 - 10:30',
                            'customer_name' => 'Barito Reload Corner',
                            'digipos_id' => 'DGP-JKT-30013',
                            'no_rs' => '082155667788',
                            'address' => 'Jl. Barito II No. 25',
                            'pic' => 'Fery',
                            'status' => 'CLOSED',
                            'checkin_time' => '10:05 WIB',
                            'duration' => '6 mnt',
                            'omset' => 0,
                            'lat' => -6.2460,
                            'lng' => 106.7910,
                            'photo_verified' => true,
                        ],
                        [
                            'stop_no' => 4,
                            'time_plan' => '10:45 - 11:15',
                            'customer_name' => 'Kramat Pela Cellular',
                            'digipos_id' => 'DGP-JKT-30014',
                            'no_rs' => '081299554433',
                            'address' => 'Jl. Kyai Maja No. 38',
                            'pic' => 'Hj. Syarifah',
                            'status' => 'EFFECTIVE_CALL',
                            'checkin_time' => '10:50 WIB',
                            'duration' => '18 mnt',
                            'omset' => 950000,
                            'lat' => -6.2410,
                            'lng' => 106.7880,
                            'photo_verified' => true,
                        ],
                        [
                            'stop_no' => 5,
                            'time_plan' => '11:30 - 12:00',
                            'customer_name' => 'Panglima Polim Cell',
                            'digipos_id' => 'DGP-JKT-30015',
                            'no_rs' => '081388221199',
                            'address' => 'Jl. Panglima Polim Raya No. 70',
                            'pic' => 'Anto',
                            'status' => 'PENDING',
                            'checkin_time' => null,
                            'duration' => null,
                            'omset' => 0,
                            'lat' => -6.2520,
                            'lng' => 106.7990,
                            'photo_verified' => false,
                        ],
                    ]
                ]
            ],
        ];

        // Ambil dataset spesifik sales terpilih & hari terpilih
        $salesData = $salesPjpPresets[$selectedSalesId] ?? $salesPjpPresets[1];
        $currentSchedule = $salesData[$selectedDay] ?? ($salesData['Senin'] ?? $salesPjpPresets[1]['Senin']);
        
        $routeStops = $currentSchedule['stops'] ?? [];
        $areaClusterName = $currentSchedule['area_cluster'] ?? "Rute {$activeSales->name}";
        $dayTargetOmset = $currentSchedule['target_omset'] ?? 6000000;

        // Hitung metrik KPI PJP secara dinamis
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
            'areaClusterName',
            'dayTargetOmset',
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

