<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with fresh, clean master data.
     */
    public function run(): void
    {
        // Disable Foreign Key Checks untuk Truncate Bersih
        Schema::disableForeignKeyConstraints();

        $tablesToTruncate = [
            'activity_logs',
            'approvals',
            'bank_receipts',
            'payment_allocations',
            'payments',
            'accounts_receivable',
            'goods_deliveries',
            'sales_returns',
            'sales_order_items',
            'sales_orders',
            'repack_items',
            'repacks',
            'stock_movement_items',
            'stock_movements',
            'goods_receipts',
            'purchase_order_items',
            'purchase_orders',
            'iccid_units',
            'stock_balances',
            'user_has_areas_clusters_taps_warehouses',
            'customer_rs_numbers',
            'customers',
            'products',
            'product_categories',
            'warehouses',
            'sales',
            'taps',
            'clusters',
            'areas',
            'users',
        ];

        foreach ($tablesToTruncate as $tbl) {
            if (Schema::hasTable($tbl)) {
                DB::table($tbl)->truncate();
            }
        }

        // Juga bersihkan tabel mapping legacy jika ada
        if (Schema::hasTable('user_territories')) {
            DB::table('user_territories')->truncate();
        }
        if (Schema::hasTable('user_warehouses')) {
            DB::table('user_warehouses')->truncate();
        }

        Schema::enableForeignKeyConstraints();

        // =========================================================================
        // 1. ROLES
        // =========================================================================
        $roles = [
            ['name' => 'IT Developer / Super Admin', 'slug' => 'super_admin', 'description' => 'Akses penuh seluruh modul dan wilayah'],
            ['name' => 'Admin Operasional', 'slug' => 'admin', 'description' => 'Kelola master data dan transaksi'],
            ['name' => 'Finance & Accounting', 'slug' => 'finance', 'description' => 'Approval SO, monitoring AR, dan pelunasan'],
            ['name' => 'Warehouse / Logistik', 'slug' => 'warehouse', 'description' => 'Kelola PO, GR, mutasi gudang, dan pengiriman'],
            ['name' => 'Sales Manager', 'slug' => 'sales_manager', 'description' => 'Monitoring performa sales dan penetrasi pasar'],
            ['name' => 'Sales Force', 'slug' => 'sales', 'description' => 'Input SO lapangan dan tracking kanvas'],
        ];

        foreach ($roles as $r) {
            DB::table('roles')->updateOrInsert(['slug' => $r['slug']], array_merge($r, ['created_at' => now(), 'updated_at' => now()]));
        }

        $superAdminRoleId = DB::table('roles')->where('slug', 'super_admin')->value('id');
        $adminRoleId = DB::table('roles')->where('slug', 'admin')->value('id');

        // =========================================================================
        // 2. TIGA USER: 1 IT (Akses Penuh) & 2 ADMIN
        // =========================================================================
        
        // User 1: IT Developer (Semua Akses Wilayah)
        $itUserId = DB::table('users')->insertGetId([
            'name' => 'IT Developer',
            'email' => 'it@distripos.com',
            'password' => Hash::make('password'),
            'role_id' => $superAdminRoleId,
            'phone' => '081211002200',
            'has_full_access' => true,
            'is_ho_readonly' => false,
            'auto_include_sales_wh' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // User 2: Admin 1
        $admin1UserId = DB::table('users')->insertGetId([
            'name' => 'Admin 1 (Operasional)',
            'email' => 'admin1@distripos.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRoleId,
            'phone' => '081233445566',
            'has_full_access' => false,
            'is_ho_readonly' => false,
            'auto_include_sales_wh' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // User 3: Admin 2
        $admin2UserId = DB::table('users')->insertGetId([
            'name' => 'Admin 2 (Operasional)',
            'email' => 'admin2@distripos.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRoleId,
            'phone' => '081277889900',
            'has_full_access' => false,
            'is_ho_readonly' => false,
            'auto_include_sales_wh' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // =========================================================================
        // 3. MASTER WILAYAH: AREA -> CLUSTER -> TAP -> WAREHOUSES (3 WH PER TAP)
        // =========================================================================

        $territoryConfig = [
            // -----------------------------------------------------------------
            // AREA 2: JAWA
            // -----------------------------------------------------------------
            [
                'area_code' => 'AREA-02',
                'area_name' => 'AREA 2 Jawa',
                'clusters' => [
                    [
                        'cluster_code' => 'CLS-BGR',
                        'cluster_name' => 'Cluster Bogor',
                        'taps' => [
                            ['code' => 'TAP-TAJUR', 'name' => 'TAP Tajur', 'address' => 'Jl. Raya Tajur No. 12, Bogor'],
                            ['code' => 'TAP-CIBINONG', 'name' => 'TAP Cibinong', 'address' => 'Jl. Tegar Beriman No. 8, Cibinong'],
                            ['code' => 'TAP-JONGGOL', 'name' => 'TAP Jonggol', 'address' => 'Jl. Raya Jonggol No. 45, Jonggol'],
                            ['code' => 'TAP-CILEUNGSI', 'name' => 'TAP Cileungsi', 'address' => 'Jl. Raya Narogong Km 15, Cileungsi'],
                            ['code' => 'TAP-DRAMAGA', 'name' => 'TAP Dramaga', 'address' => 'Jl. Raya Dramaga Km 7, Bogor'],
                            ['code' => 'TAP-LEUWILIANG', 'name' => 'TAP Leuwiliang', 'address' => 'Jl. Raya Leuwiliang No. 30, Bogor Barat'],
                            ['code' => 'TAP-PARUNG', 'name' => 'TAP Parung', 'address' => 'Jl. Raya Parung No. 99, Bogor'],
                            ['code' => 'TAP-TAJURKAB', 'name' => 'TAP Tajur Kab', 'address' => 'Kawasan Sentul & Sekitarnya, Kab. Bogor'],
                            ['code' => 'TAP-TAJURKOTA', 'name' => 'TAP Tajur Kota', 'address' => 'Pusat Kota Bogor, Pajajaran'],
                        ]
                    ],
                    [
                        'cluster_code' => 'CLS-SKB',
                        'cluster_name' => 'Cluster Sukabumi',
                        'taps' => [
                            ['code' => 'TAP-SKB', 'name' => 'TAP Sukabumi', 'address' => 'Jl. Jend. Sudirman No. 25, Kota Sukabumi'],
                            ['code' => 'TAP-PLARA', 'name' => 'TAP Plara', 'address' => 'Jl. Raya Palabuhanratu, Pelabuhan Ratu'],
                            ['code' => 'TAP-CIBADAK', 'name' => 'TAP Cibadak', 'address' => 'Jl. Raya Surya Kencana No. 50, Cibadak'],
                            ['code' => 'TAP-JPKULON', 'name' => 'TAP Jampang Kulon', 'address' => 'Jl. Raya Jampang Kulon No. 18, Sukabumi Selatan'],
                        ]
                    ]
                ]
            ],

            // -----------------------------------------------------------------
            // AREA 1: SUMATERA
            // -----------------------------------------------------------------
            [
                'area_code' => 'AREA-01',
                'area_name' => 'AREA 1 Sumatera',
                'clusters' => [
                    [
                        'cluster_code' => 'CLS-MDN',
                        'cluster_name' => 'Cluster Medan',
                        'taps' => [
                            ['code' => 'TAP-MDNKOTA', 'name' => 'TAP Medan Kota', 'address' => 'Jl. Gatot Subroto No. 88, Medan'],
                            ['code' => 'TAP-DELISERDANG', 'name' => 'TAP Deli Serdang', 'address' => 'Jl. Lintas Medan - Lubuk Pakam, Deli Serdang'],
                            ['code' => 'TAP-BINJAI', 'name' => 'TAP Binjai', 'address' => 'Jl. Soekarno-Hatta No. 42, Kota Binjai'],
                        ]
                    ],
                    [
                        'cluster_code' => 'CLS-PLB',
                        'cluster_name' => 'Cluster Palembang',
                        'taps' => [
                            ['code' => 'TAP-PLBKOTA', 'name' => 'TAP Palembang Kota', 'address' => 'Jl. Jendral Sudirman No. 102, Palembang'],
                            ['code' => 'TAP-BANYUASIN', 'name' => 'TAP Banyuasin', 'address' => 'Jl. Lintas Timur Km 35, Pangkalan Balai, Banyuasin'],
                        ]
                    ]
                ]
            ]
        ];

        $salesCounter = 1;

        foreach ($territoryConfig as $areaData) {
            $areaId = DB::table('areas')->insertGetId([
                'code' => $areaData['area_code'],
                'name' => $areaData['area_name'],
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($areaData['clusters'] as $clsData) {
                $clusterId = DB::table('clusters')->insertGetId([
                    'area_id' => $areaId,
                    'code' => $clsData['cluster_code'],
                    'name' => $clsData['cluster_name'],
                    'status' => 'ACTIVE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($clsData['taps'] as $tapData) {
                    $tapId = DB::table('taps')->insertGetId([
                        'cluster_id' => $clusterId,
                        'code' => $tapData['code'],
                        'name' => $tapData['name'],
                        'address' => $tapData['address'],
                        'status' => 'ACTIVE',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Bikin 1 Tenaga Sales untuk TAP ini (untuk gudang kanvas sales)
                    $salesCode = 'SLS-' . str_pad($salesCounter, 3, '0', STR_PAD_LEFT);
                    $salesName = 'Sales ' . str_replace('TAP ', '', $tapData['name']);
                    
                    $salesId = DB::table('sales')->insertGetId([
                        'tap_id' => $tapId,
                        'user_id' => null,
                        'code' => $salesCode,
                        'name' => $salesName,
                        'phone' => '08' . rand(1100000000, 1999999999),
                        'status' => 'ACTIVE',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $salesCounter++;

                    // -------------------------------------------------------------
                    // 3 WAREHOUSES PER TAP
                    // -------------------------------------------------------------
                    $prefix = str_replace('TAP-', '', $tapData['code']);

                    // WH 1: Gudang Utama (MAIN)
                    DB::table('warehouses')->insert([
                        'code' => 'WH-' . $prefix . '-MAIN',
                        'name' => 'Gudang Utama ' . $tapData['name'],
                        'type' => 'MAIN',
                        'tap_id' => $tapId,
                        'sales_id' => null,
                        'address' => 'Gedung Utama ' . $tapData['name'],
                        'status' => 'ACTIVE',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // WH 2: Gudang Persediaan / Inventory (INVENTORY)
                    DB::table('warehouses')->insert([
                        'code' => 'WH-' . $prefix . '-INV',
                        'name' => 'Gudang Persediaan (Buffer) ' . $tapData['name'],
                        'type' => 'INVENTORY',
                        'tap_id' => $tapId,
                        'sales_id' => null,
                        'address' => 'Ruang Rak Inventory ' . $tapData['name'],
                        'status' => 'ACTIVE',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // WH 3: Gudang Kanvas Sales (SALES)
                    DB::table('warehouses')->insert([
                        'code' => 'WH-' . $prefix . '-SLS',
                        'name' => 'Gudang Kanvas ' . $salesName,
                        'type' => 'SALES',
                        'tap_id' => $tapId,
                        'sales_id' => $salesId,
                        'address' => 'Motor Box Kanvas ' . $salesCode,
                        'status' => 'ACTIVE',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // =========================================================================
        // 4. MASTER PRODUK DASAR (Untuk Kebutuhan Transaksi Dari Awal)
        // =========================================================================
        $catSa = DB::table('product_categories')->insertGetId([
            'name' => 'Starter Pack (SA)',
            'code' => 'CAT-SA',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        $catVcr = DB::table('product_categories')->insertGetId([
            'name' => 'Voucher Fisik',
            'code' => 'CAT-VCR',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        $catMdm = DB::table('product_categories')->insertGetId([
            'name' => 'Modem & Device',
            'code' => 'CAT-MDM',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('products')->insert([
            [
                'category_id' => $catSa,
                'sku' => 'SA-5GB-30D',
                'name' => 'Kartu Perdana Telkomsel Lite 5GB 30 Hari',
                'type' => 'SA',
                'tracking_method' => 'ICCID',
                'cost_price' => 22000,
                'selling_price' => 25000,
                'uom' => 'PCS',
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => $catSa,
                'sku' => 'SA-15GB-30D',
                'name' => 'Kartu Perdana Super Seru 15GB 30 Hari',
                'type' => 'SA',
                'tracking_method' => 'ICCID',
                'cost_price' => 48000,
                'selling_price' => 55000,
                'uom' => 'PCS',
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => $catVcr,
                'sku' => 'VCR-3GB-3D',
                'name' => 'Voucher Fisik Internet 3GB 3 Hari',
                'type' => 'VOUCHER',
                'tracking_method' => 'QUANTITY',
                'cost_price' => 11000,
                'selling_price' => 13500,
                'uom' => 'PCS',
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => $catVcr,
                'sku' => 'VCR-7GB-7D',
                'name' => 'Voucher Fisik Internet 7GB 7 Hari',
                'type' => 'VOUCHER',
                'tracking_method' => 'QUANTITY',
                'cost_price' => 22000,
                'selling_price' => 26000,
                'uom' => 'PCS',
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => $catMdm,
                'sku' => 'MDM-ORBIT-A1',
                'name' => 'Modem Telkomsel Orbit Star A1 4G LTE',
                'type' => 'MODEM',
                'tracking_method' => 'QUANTITY',
                'cost_price' => 285000,
                'selling_price' => 325000,
                'uom' => 'UNIT',
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
