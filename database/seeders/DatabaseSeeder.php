<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Roles
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super_admin', 'description' => 'Akses penuh seluruh modul'],
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

        // 2. Users
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@distripos.com'],
            [
                'name' => 'Administrator Utama',
                'password' => Hash::make('password'),
                'role_id' => $superAdminRoleId,
                'phone' => '081122334455',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // 3. Organization: Area -> Cluster -> TAP -> Sales
        // Area
        $areaJaboId = DB::table('areas')->insertGetId([
            'code' => 'AREA-JABO',
            'name' => 'Area Jabodetabek',
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $areaJabarId = DB::table('areas')->insertGetId([
            'code' => 'AREA-JABAR',
            'name' => 'Area Jawa Barat',
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Clusters
        $clsJakselId = DB::table('clusters')->insertGetId([
            'area_id' => $areaJaboId,
            'code' => 'CLS-JKTSEL',
            'name' => 'Cluster Jakarta Selatan',
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $clsJaktimId = DB::table('clusters')->insertGetId([
            'area_id' => $areaJaboId,
            'code' => 'CLS-JKTTMR',
            'name' => 'Cluster Jakarta Timur',
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $clsDepokId = DB::table('clusters')->insertGetId([
            'area_id' => $areaJaboId,
            'code' => 'CLS-DEPOK',
            'name' => 'Cluster Depok',
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // TAPs
        $tapPasarMingguId = DB::table('taps')->insertGetId([
            'cluster_id' => $clsJakselId,
            'code' => 'TAP-PASARMINGGU',
            'name' => 'TAP Pasar Minggu',
            'address' => 'Jl. Raya Pasar Minggu No. 45, Jakarta Selatan',
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tapBlokMId = DB::table('taps')->insertGetId([
            'cluster_id' => $clsJakselId,
            'code' => 'TAP-BLOKM',
            'name' => 'TAP Blok M',
            'address' => 'Kebayoran Baru, Jakarta Selatan',
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tapRawamangunId = DB::table('taps')->insertGetId([
            'cluster_id' => $clsJaktimId,
            'code' => 'TAP-RAWAMANGUN',
            'name' => 'TAP Rawamangun',
            'address' => 'Jl. Pemuda No. 12, Jakarta Timur',
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Sales
        $salesRianId = DB::table('sales')->insertGetId([
            'tap_id' => $tapPasarMingguId,
            'code' => 'SLS-001',
            'name' => 'Rian Hidayat',
            'phone' => '081234567890',
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $salesBudiId = DB::table('sales')->insertGetId([
            'tap_id' => $tapPasarMingguId,
            'code' => 'SLS-002',
            'name' => 'Budi Santoso',
            'phone' => '081298765432',
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $salesDimasId = DB::table('sales')->insertGetId([
            'tap_id' => $tapBlokMId,
            'code' => 'SLS-003',
            'name' => 'Dimas Pratama',
            'phone' => '081311223344',
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Warehouses (MAIN & INVENTORY per TAP, plus SALES Warehouse)
        // TAP Pasar Minggu
        $whMainPM = DB::table('warehouses')->insertGetId([
            'code' => 'WH-MAIN-PM',
            'name' => 'Gudang Utama (MAIN) TAP Pasar Minggu',
            'type' => 'MAIN',
            'tap_id' => $tapPasarMingguId,
            'sales_id' => null,
            'address' => 'Lantai 1 Kantor TAP Pasar Minggu',
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $whInvPM = DB::table('warehouses')->insertGetId([
            'code' => 'WH-INV-PM',
            'name' => 'Gudang Persediaan (INV) TAP Pasar Minggu',
            'type' => 'INVENTORY',
            'tap_id' => $tapPasarMingguId,
            'sales_id' => null,
            'address' => 'Ruang Rak Inventory TAP Pasar Minggu',
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $whSlsRian = DB::table('warehouses')->insertGetId([
            'code' => 'WH-SLS-RIAN',
            'name' => 'Gudang Kanvas Sales - Rian Hidayat',
            'type' => 'SALES',
            'tap_id' => $tapPasarMingguId,
            'sales_id' => $salesRianId,
            'address' => 'Motor Box Kanvas B-4819-SLS',
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $whSlsBudi = DB::table('warehouses')->insertGetId([
            'code' => 'WH-SLS-BUDI',
            'name' => 'Gudang Kanvas Sales - Budi Santoso',
            'type' => 'SALES',
            'tap_id' => $tapPasarMingguId,
            'sales_id' => $salesBudiId,
            'address' => 'Motor Box Kanvas B-5920-SLS',
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // TAP Blok M
        $whMainBM = DB::table('warehouses')->insertGetId([
            'code' => 'WH-MAIN-BM',
            'name' => 'Gudang Utama (MAIN) TAP Blok M',
            'type' => 'MAIN',
            'tap_id' => $tapBlokMId,
            'sales_id' => null,
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $whInvBM = DB::table('warehouses')->insertGetId([
            'code' => 'WH-INV-BM',
            'name' => 'Gudang Persediaan (INV) TAP Blok M',
            'type' => 'INVENTORY',
            'tap_id' => $tapBlokMId,
            'sales_id' => null,
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 5. Product Categories & Products
        $catSa = DB::table('product_categories')->insertGetId(['name' => 'Starter Pack (SA)', 'code' => 'CAT-SA', 'created_at' => now(), 'updated_at' => now()]);
        $catVcr = DB::table('product_categories')->insertGetId(['name' => 'Voucher Fisik', 'code' => 'CAT-VCR', 'created_at' => now(), 'updated_at' => now()]);
        $catMdm = DB::table('product_categories')->insertGetId(['name' => 'Modem & Device', 'code' => 'CAT-MDM', 'created_at' => now(), 'updated_at' => now()]);

        $prodSa5 = DB::table('products')->insertGetId([
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
        ]);

        $prodSa15 = DB::table('products')->insertGetId([
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
        ]);

        $prodVcr3 = DB::table('products')->insertGetId([
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
        ]);

        $prodVcr7 = DB::table('products')->insertGetId([
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
        ]);

        $prodOrbit = DB::table('products')->insertGetId([
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
        ]);

        // 6. Customers (Outlets)
        $cst1 = DB::table('customers')->insertGetId([
            'tap_id' => $tapPasarMingguId,
            'code' => 'CST-001',
            'name' => 'Toko Berkah Cellindo',
            'type' => 'RETAIL',
            'phone' => '085711223344',
            'address' => 'Jl. Kebagusan Raya No. 18, Ps. Minggu',
            'credit_limit' => 5000000,
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cst2 = DB::table('customers')->insertGetId([
            'tap_id' => $tapPasarMingguId,
            'code' => 'CST-002',
            'name' => 'Mitra Reload Grosir',
            'type' => 'GROSIR',
            'phone' => '087812345678',
            'address' => 'Komp. Ruko Fatmawati Blok B-4',
            'credit_limit' => 25000000,
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cst3 = DB::table('customers')->insertGetId([
            'tap_id' => $tapBlokMId,
            'code' => 'CST-003',
            'name' => 'Sentral Phone Plaza',
            'type' => 'RETAIL',
            'phone' => '081899887766',
            'address' => 'Blok M Square Lt. 3 No. 45',
            'credit_limit' => 10000000,
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 7. Seed Stock Balances (Quantity based)
        $stocks = [
            // WH MAIN Pasar Minggu
            ['warehouse_id' => $whMainPM, 'product_id' => $prodVcr3, 'quantity' => 1200, 'reserved_quantity' => 0],
            ['warehouse_id' => $whMainPM, 'product_id' => $prodVcr7, 'quantity' => 850, 'reserved_quantity' => 0],
            ['warehouse_id' => $whMainPM, 'product_id' => $prodOrbit, 'quantity' => 45, 'reserved_quantity' => 0],
            
            // WH INV Pasar Minggu
            ['warehouse_id' => $whInvPM, 'product_id' => $prodVcr3, 'quantity' => 400, 'reserved_quantity' => 0],
            ['warehouse_id' => $whInvPM, 'product_id' => $prodVcr7, 'quantity' => 300, 'reserved_quantity' => 0],
            ['warehouse_id' => $whInvPM, 'product_id' => $prodOrbit, 'quantity' => 15, 'reserved_quantity' => 0],

            // WH Sales Rian (Kanvas)
            ['warehouse_id' => $whSlsRian, 'product_id' => $prodVcr3, 'quantity' => 150, 'reserved_quantity' => 0],
            ['warehouse_id' => $whSlsRian, 'product_id' => $prodVcr7, 'quantity' => 100, 'reserved_quantity' => 0],
            ['warehouse_id' => $whSlsRian, 'product_id' => $prodOrbit, 'quantity' => 5, 'reserved_quantity' => 0],

            // WH Sales Budi (Kanvas)
            ['warehouse_id' => $whSlsBudi, 'product_id' => $prodVcr3, 'quantity' => 80, 'reserved_quantity' => 0],
            ['warehouse_id' => $whSlsBudi, 'product_id' => $prodVcr7, 'quantity' => 60, 'reserved_quantity' => 0],
        ];

        foreach ($stocks as $s) {
            DB::table('stock_balances')->insert(array_merge($s, ['created_at' => now(), 'updated_at' => now()]));
        }

        // 8. Seed Sample ICCID Units (SA Kartu Perdana)
        // Insert 20 ICCID in WH Main PM
        for ($i = 1; $i <= 20; $i++) {
            $num = str_pad($i, 5, '0', STR_PAD_LEFT);
            DB::table('iccid_units')->insert([
                'iccid_number' => '89621000000000' . $num,
                'product_id' => $prodSa5,
                'warehouse_id' => $whMainPM,
                'status' => 'AVAILABLE',
                'customer_id' => null,
                'last_doc_type' => 'GR',
                'last_doc_number' => 'GR/202611/0001',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Insert 15 ICCID in WH Inv PM
        for ($i = 21; $i <= 35; $i++) {
            $num = str_pad($i, 5, '0', STR_PAD_LEFT);
            DB::table('iccid_units')->insert([
                'iccid_number' => '89621000000000' . $num,
                'product_id' => $prodSa5,
                'warehouse_id' => $whInvPM,
                'status' => 'AVAILABLE',
                'customer_id' => null,
                'last_doc_type' => 'SM',
                'last_doc_number' => 'SM/202611/0001',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Insert 10 ICCID in WH Sales Rian (Kanvas)
        for ($i = 36; $i <= 45; $i++) {
            $num = str_pad($i, 5, '0', STR_PAD_LEFT);
            DB::table('iccid_units')->insert([
                'iccid_number' => '89621000000000' . $num,
                'product_id' => $prodSa5,
                'warehouse_id' => $whSlsRian,
                'status' => 'AVAILABLE',
                'customer_id' => null,
                'last_doc_type' => 'SM',
                'last_doc_number' => 'SM/202611/0002',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 9. Sample PO & GR (Procurement)
        $poId = DB::table('purchase_orders')->insertGetId([
            'doc_type' => 'PO',
            'doc_number' => 'PO/202611/0001',
            'tap_id' => $tapPasarMingguId,
            'supplier_name' => 'PT TELKOMSEL LOGISTIK REGIONAL',
            'order_date' => Carbon::now()->subDays(3)->format('Y-m-d'),
            'total_amount' => 45000000,
            'status' => 'RECEIVED',
            'notes' => 'Pengadaan batch mingguan SA Lite dan Voucher',
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(2),
        ]);

        DB::table('purchase_order_items')->insert([
            ['purchase_order_id' => $poId, 'product_id' => $prodSa5, 'quantity' => 1000, 'cost_price' => 22000, 'subtotal' => 22000000, 'created_at' => now(), 'updated_at' => now()],
            ['purchase_order_id' => $poId, 'product_id' => $prodVcr3, 'quantity' => 1500, 'cost_price' => 11000, 'subtotal' => 16500000, 'created_at' => now(), 'updated_at' => now()],
            ['purchase_order_id' => $poId, 'product_id' => $prodOrbit, 'quantity' => 25, 'cost_price' => 260000, 'subtotal' => 6500000, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $grId = DB::table('goods_receipts')->insertGetId([
            'doc_type' => 'GR',
            'doc_number' => 'GR/202611/0001',
            'ref_doc_type' => 'PO',
            'ref_doc_number' => 'PO/202611/0001',
            'purchase_order_id' => $poId,
            'tap_id' => $tapPasarMingguId,
            'warehouse_id' => $whMainPM,
            'received_date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'status' => 'CONFIRMED',
            'notes' => 'Penerimaan fisik barang 100% lengkap',
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        // 10. Sample Stock Movement (SM)
        $smId = DB::table('stock_movements')->insertGetId([
            'doc_type' => 'SM',
            'doc_number' => 'SM/202611/0001',
            'source_warehouse_id' => $whMainPM,
            'destination_warehouse_id' => $whInvPM,
            'movement_date' => Carbon::now()->subDays(1)->format('Y-m-d'),
            'status' => 'COMPLETED',
            'notes' => 'Supply buffer stock ke gudang inventory',
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subDays(1),
        ]);

        DB::table('stock_movement_items')->insert([
            ['stock_movement_id' => $smId, 'product_id' => $prodVcr3, 'quantity' => 200, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 11. Sample Repack Transactions (RO & RI)
        $roId = DB::table('repacks')->insertGetId([
            'doc_type' => 'RPKOUT',
            'doc_number' => 'RO/202611/0001',
            'ref_doc_type' => null,
            'ref_doc_number' => null,
            'warehouse_id' => $whInvPM,
            'repack_date' => Carbon::now()->subDays(1)->format('Y-m-d'),
            'status' => 'COMPLETED',
            'notes' => 'Bongkar Box Bundle Voucher untuk diecer ke sales kanvas',
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subDays(1),
        ]);

        DB::table('repack_items')->insert([
            ['repack_id' => $roId, 'product_id' => $prodVcr3, 'item_type' => 'INPUT', 'quantity' => 100, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $riId = DB::table('repacks')->insertGetId([
            'doc_type' => 'RPKIN',
            'doc_number' => 'RI/202611/0001',
            'ref_doc_type' => 'RPKOUT',
            'ref_doc_number' => 'RO/202611/0001',
            'warehouse_id' => $whInvPM,
            'repack_date' => Carbon::now()->subDays(1)->format('Y-m-d'),
            'status' => 'COMPLETED',
            'notes' => 'Masuk paket eceran voucher pack 10 pcs',
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subDays(1),
        ]);

        // 12. Sample Sales Orders, GD, AR, FP, BBM
        // Transaksi 1: SO Mobile Sales Rian -> Sudah GD -> AR Lunas (FP & BBM)
        $so1Id = DB::table('sales_orders')->insertGetId([
            'doc_type' => 'SO',
            'doc_number' => 'SO/202611/0001',
            'tap_id' => $tapPasarMingguId,
            'warehouse_id' => $whSlsRian,
            'sales_id' => $salesRianId,
            'customer_id' => $cst1,
            'order_date' => Carbon::now()->subDay()->format('Y-m-d'),
            'channel' => 'MOBILE_SALES',
            'status' => 'COMPLETED',
            'subtotal' => 2600000,
            'discount' => 50000,
            'grand_total' => 2550000,
            'notes' => 'Penjualan kanvas rutin outlet Berkah Cellindo',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        DB::table('sales_order_items')->insert([
            ['sales_order_id' => $so1Id, 'product_id' => $prodVcr3, 'quantity' => 100, 'unit_price' => 13500, 'subtotal' => 1350000, 'created_at' => now(), 'updated_at' => now()],
            ['sales_order_id' => $so1Id, 'product_id' => $prodSa5, 'quantity' => 50, 'unit_price' => 25000, 'subtotal' => 1250000, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // GD for SO 1
        DB::table('goods_deliveries')->insert([
            'doc_type' => 'GD',
            'doc_number' => 'GD/202611/0001',
            'ref_doc_type' => 'SO',
            'ref_doc_number' => 'SO/202611/0001',
            'sales_order_id' => $so1Id,
            'warehouse_id' => $whSlsRian,
            'customer_id' => $cst1,
            'delivery_date' => Carbon::now()->subDay()->format('Y-m-d'),
            'recipient_name' => 'Pak Haji Berkah (Owner)',
            'status' => 'DELIVERED',
            'notes' => 'Diserahkan langsung di outlet oleh Sales Rian',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        // AR for SO 1
        $ar1Id = DB::table('accounts_receivable')->insertGetId([
            'doc_type' => 'AR',
            'doc_number' => 'AR/202611/0001',
            'ref_doc_type' => 'SO',
            'ref_doc_number' => 'SO/202611/0001',
            'sales_order_id' => $so1Id,
            'customer_id' => $cst1,
            'invoice_date' => Carbon::now()->subDay()->format('Y-m-d'),
            'due_date' => Carbon::now()->addDays(6)->format('Y-m-d'),
            'total_amount' => 2550000,
            'paid_amount' => 2550000,
            'balance_amount' => 0,
            'status' => 'PAID',
            'created_at' => now()->subDay(),
            'updated_at' => now(),
        ]);

        // Payment / Pelunasan (FP) for AR 1
        $fp1Id = DB::table('payments')->insertGetId([
            'doc_type' => 'FP',
            'doc_number' => 'FP/202611/0001',
            'ref_doc_type' => 'AR',
            'ref_doc_number' => 'AR/202611/0001',
            'accounts_receivable_id' => $ar1Id,
            'customer_id' => $cst1,
            'payment_date' => Carbon::now()->format('Y-m-d'),
            'payment_method' => 'TRANSFER',
            'amount' => 2550000,
            'cashier_name' => 'Kasir TAP Pasar Minggu',
            'notes' => 'Pelunasan via transfer BCA',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // BBM for FP 1
        DB::table('bank_receipts')->insert([
            'doc_type' => 'BBM',
            'doc_number' => 'BBM/202611/0001',
            'ref_doc_type' => 'FP',
            'ref_doc_number' => 'FP/202611/0001',
            'payment_id' => $fp1Id,
            'bank_name' => 'BCA Rekening Operasional TAP',
            'account_number' => '711-0988-234',
            'receipt_date' => Carbon::now()->format('Y-m-d'),
            'amount' => 2550000,
            'status' => 'VERIFIED',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Transaksi 2: SO Grosir (Web Admin) -> GD Terkirim -> AR Outstanding Belum Lunas
        $so2Id = DB::table('sales_orders')->insertGetId([
            'doc_type' => 'SO',
            'doc_number' => 'SO/202611/0002',
            'tap_id' => $tapPasarMingguId,
            'warehouse_id' => $whInvPM,
            'sales_id' => $salesBudiId,
            'customer_id' => $cst2,
            'order_date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'channel' => 'WEB_ADMIN',
            'status' => 'DELIVERED',
            'subtotal' => 6500000,
            'discount' => 100000,
            'grand_total' => 6400000,
            'notes' => 'Order Grosir 500 pcs Voucher 3GB',
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(1),
        ]);

        DB::table('sales_order_items')->insert([
            ['sales_order_id' => $so2Id, 'product_id' => $prodVcr3, 'quantity' => 500, 'unit_price' => 13000, 'subtotal' => 6500000, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('goods_deliveries')->insert([
            'doc_type' => 'GD',
            'doc_number' => 'GD/202611/0002',
            'ref_doc_type' => 'SO',
            'ref_doc_number' => 'SO/202611/0002',
            'sales_order_id' => $so2Id,
            'warehouse_id' => $whInvPM,
            'customer_id' => $cst2,
            'delivery_date' => Carbon::now()->subDays(1)->format('Y-m-d'),
            'recipient_name' => 'Gudang Mitra Reload Fatmawati',
            'status' => 'DELIVERED',
            'notes' => 'Terkirim via kurir internal TAP',
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subDays(1),
        ]);

        DB::table('accounts_receivable')->insert([
            'doc_type' => 'AR',
            'doc_number' => 'AR/202611/0002',
            'ref_doc_type' => 'SO',
            'ref_doc_number' => 'SO/202611/0002',
            'sales_order_id' => $so2Id,
            'customer_id' => $cst2,
            'invoice_date' => Carbon::now()->subDays(1)->format('Y-m-d'),
            'due_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'total_amount' => 6400000,
            'paid_amount' => 2000000,
            'balance_amount' => 4400000,
            'status' => 'PARTIAL',
            'created_at' => now()->subDays(1),
            'updated_at' => now(),
        ]);

        // Transaksi 3: SO Pending Approval (untuk bahan demo interaktif)
        $so3Id = DB::table('sales_orders')->insertGetId([
            'doc_type' => 'SO',
            'doc_number' => 'SO/202611/0003',
            'tap_id' => $tapBlokMId,
            'warehouse_id' => $whInvBM,
            'sales_id' => $salesDimasId,
            'customer_id' => $cst3,
            'order_date' => Carbon::now()->format('Y-m-d'),
            'channel' => 'MOBILE_SALES',
            'status' => 'PENDING_APPROVAL',
            'subtotal' => 4875000,
            'discount' => 0,
            'grand_total' => 4875000,
            'notes' => 'Pesanan 15 Unit Modem Orbit Star A1 - Menunggu Approval Finance',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('sales_order_items')->insert([
            ['sales_order_id' => $so3Id, 'product_id' => $prodOrbit, 'quantity' => 15, 'unit_price' => 325000, 'subtotal' => 4875000, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
