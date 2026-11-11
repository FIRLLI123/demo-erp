<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterController extends Controller
{
    public function organization()
    {
        $areas = DB::table('areas')->get();
        $clusters = DB::table('clusters')->join('areas', 'clusters.area_id', '=', 'areas.id')
            ->select('clusters.*', 'areas.name as area_name')->get();
        $taps = DB::table('taps')->join('clusters', 'taps.cluster_id', '=', 'clusters.id')
            ->select('taps.*', 'clusters.name as cluster_name')->get();
        $sales = DB::table('sales')->join('taps', 'sales.tap_id', '=', 'taps.id')
            ->select('sales.*', 'taps.name as tap_name')->get();

        return view('master.organization', compact('areas', 'clusters', 'taps', 'sales'));
    }

    public function products()
    {
        $products = DB::table('products')
            ->leftJoin('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->select('products.*', 'product_categories.name as category_name')
            ->get();

        return view('master.products', compact('products'));
    }

    public function warehouses()
    {
        $warehouses = DB::table('warehouses')
            ->join('taps', 'warehouses.tap_id', '=', 'taps.id')
            ->leftJoin('sales', 'warehouses.sales_id', '=', 'sales.id')
            ->select('warehouses.*', 'taps.name as tap_name', 'sales.name as sales_name')
            ->get();

        return view('master.warehouses', compact('warehouses'));
    }

    /**
     * Master Tim Sales Kanvaser (PT Wahana Putra Yudha)
     */
    public function sales()
    {
        $salesList = DB::table('sales')
            ->join('taps', 'sales.tap_id', '=', 'taps.id')
            ->leftJoin('warehouses', function ($join) {
                $join->on('sales.id', '=', 'warehouses.sales_id')
                     ->where('warehouses.type', '=', 'SALES');
            })
            ->select(
                'sales.*',
                'taps.name as tap_name',
                'warehouses.name as warehouse_name',
                'warehouses.id as warehouse_id'
            )
            ->orderBy('sales.id')
            ->get();

        // Hitung total order yang ditangani masing-masing sales
        $soCounts = DB::table('sales_orders')
            ->select('sales_id', DB::raw('count(*) as total_orders'), DB::raw('sum(grand_total) as total_sales_amount'))
            ->groupBy('sales_id')
            ->get()
            ->keyBy('sales_id');

        foreach ($salesList as $s) {
            $s->total_orders = $soCounts[$s->id]->total_orders ?? 0;
            $s->total_sales_amount = $soCounts[$s->id]->total_sales_amount ?? 0;
        }

        $taps = DB::table('taps')->where('status', 'ACTIVE')->orderBy('name')->get();

        return view('master.sales', compact('salesList', 'taps'));
    }

    public function storeSales(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'tap_id' => 'required|exists:taps,id',
            'phone' => 'nullable|string|max:20',
        ]);

        $nextId = DB::table('sales')->count() + 1;
        $salesCode = 'SLS-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            $salesId = DB::table('sales')->insertGetId([
                'tap_id' => $request->tap_id,
                'code' => $salesCode,
                'name' => $request->name,
                'phone' => $request->phone ?? '0812' . rand(10000000, 99999999),
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Buat otomatis Gudang Kanvas Sales Motor untuk sales ini
            DB::table('warehouses')->insert([
                'tap_id' => $request->tap_id,
                'sales_id' => $salesId,
                'code' => 'WHS-' . str_pad($salesId, 2, '0', STR_PAD_LEFT),
                'name' => "Gudang Kanvas Sales - {$request->name}",
                'type' => 'SALES',
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            return redirect()->route('master.sales-team')->with('success', "Sales [{$salesCode}] {$request->name} berhasil didaftarkan dan Gudang Kanvas Motor otomatis dibentuk.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mendaftarkan sales: ' . $e->getMessage());
        }
    }

    public function updateSales(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'tap_id' => 'required|exists:taps,id',
            'phone' => 'nullable|string|max:20',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        DB::table('sales')->where('id', $id)->update([
            'name' => $request->name,
            'tap_id' => $request->tap_id,
            'phone' => $request->phone,
            'status' => $request->status,
            'updated_at' => now(),
        ]);

        return redirect()->route('master.sales-team')->with('success', "Data Sales [{$request->name}] berhasil diperbarui.");
    }

    /**
     * Master Pelanggan / Toko Outlet (PT Wahana Putra Yudha)
     */
    public function customers()
    {
        $customers = DB::table('customers')
            ->join('taps', 'customers.tap_id', '=', 'taps.id')
            ->select('customers.*', 'taps.name as tap_name')
            ->orderBy('customers.id')
            ->get();

        // Hitung outstanding piutang untuk tiap customer
        $outstandingAr = DB::table('accounts_receivable')
            ->where('status', '!=', 'PAID')
            ->where('balance_amount', '>', 0)
            ->select('customer_id', DB::raw('sum(balance_amount) as total_unpaid'), DB::raw('count(*) as unpaid_count'))
            ->groupBy('customer_id')
            ->get()
            ->keyBy('customer_id');

        foreach ($customers as $c) {
            $c->unpaid_ar_amount = $outstandingAr[$c->id]->total_unpaid ?? 0;
            $c->unpaid_ar_count = $outstandingAr[$c->id]->unpaid_count ?? 0;
        }

        $taps = DB::table('taps')->where('status', 'ACTIVE')->orderBy('name')->get();

        return view('master.customers', compact('customers', 'taps'));
    }

    public function storeCustomer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'tap_id' => 'required|exists:taps,id',
            'type' => 'required|in:RETAIL,GROSIR',
            'digipos_id' => 'nullable|string|max:50',
            'nomor_rs' => 'nullable|string|max:30',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        $nextId = DB::table('customers')->count() + 1;
        $customerCode = 'CST-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        DB::table('customers')->insert([
            'tap_id' => $request->tap_id,
            'code' => $customerCode,
            'digipos_id' => $request->digipos_id ?? ('DGP-' . rand(100000, 999999)),
            'nomor_rs' => $request->nomor_rs ?? ($request->phone ?? '0812' . rand(10000000, 99999999)),
            'name' => $request->name,
            'type' => $request->type,
            'phone' => $request->phone ?? '0812' . rand(10000000, 99999999),
            'address' => $request->address ?? 'Area Operasional TAP',
            'credit_limit' => $request->credit_limit ?? 10000000,
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('master.customers')->with('success', "Outlet / Toko [{$customerCode}] {$request->name} berhasil ditambahkan ke database.");
    }

    public function updateCustomer(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'tap_id' => 'required|exists:taps,id',
            'type' => 'required|in:RETAIL,GROSIR',
            'digipos_id' => 'nullable|string|max:50',
            'nomor_rs' => 'nullable|string|max:30',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        DB::table('customers')->where('id', $id)->update([
            'name' => $request->name,
            'tap_id' => $request->tap_id,
            'type' => $request->type,
            'digipos_id' => $request->digipos_id,
            'nomor_rs' => $request->nomor_rs,
            'phone' => $request->phone,
            'address' => $request->address,
            'credit_limit' => $request->credit_limit ?? 0,
            'status' => $request->status,
            'updated_at' => now(),
        ]);

        return redirect()->route('master.customers')->with('success', "Data Outlet / Toko [{$request->name}] berhasil diperbarui.");
    }

    public function users(Request $request)
    {
        $users = DB::table('users')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->leftJoin('sales', 'users.sales_id', '=', 'sales.id')
            ->leftJoin('taps', 'sales.tap_id', '=', 'taps.id')
            ->select(
                'users.*',
                'roles.name as role_name',
                'roles.slug as role_slug',
                'sales.code as sales_code',
                'sales.name as sales_name',
                'sales.phone as sales_phone',
                'taps.name as sales_tap'
            )
            ->orderBy('users.id')
            ->get();

        $roles = DB::table('roles')->orderBy('id')->get();
        $salesList = DB::table('sales')
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get();

        return view('master.users', compact('users', 'roles', 'salesList'));
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email|max:150',
            'password' => 'required|string|min:6',
            'role_id' => 'required|exists:roles,id',
            'phone' => 'nullable|string|max:20',
            'sales_id' => 'nullable|exists:sales,id',
        ]);

        $userId = DB::table('users')->insertGetId([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role_id' => $request->role_id,
            'sales_id' => $request->sales_id,
            'phone' => $request->phone,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($request->sales_id) {
            DB::table('sales')->where('id', $request->sales_id)->update([
                'user_id' => $userId,
                'updated_at' => now()
            ]);
        }

        return redirect()->route('master.users')->with('success', "Akun pengguna [{$request->name}] berhasil dibuat dan dipetakan.");
    }

    public function updateUser(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email,' . $id,
            'role_id' => 'required|exists:roles,id',
            'phone' => 'nullable|string|max:20',
            'sales_id' => 'nullable|exists:sales,id',
            'is_active' => 'required|in:0,1',
            'password' => 'nullable|string|min:6',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'sales_id' => $request->sales_id,
            'phone' => $request->phone,
            'is_active' => $request->is_active,
            'updated_at' => now(),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = bcrypt($request->password);
        }

        DB::table('users')->where('id', $id)->update($updateData);

        if ($request->sales_id) {
            DB::table('sales')->where('id', $request->sales_id)->update([
                'user_id' => $id,
                'updated_at' => now()
            ]);
        }

        return redirect()->route('master.users')->with('success', "Data akun pengguna [{$request->name}] berhasil diperbarui.");
    }

    // ========================================================
    // MASTER TAMBAHAN 1: REKENING BANK & KAS PT
    // ========================================================
    public function banks(Request $request)
    {
        if (DB::table('company_banks')->count() === 0) {
            DB::table('company_banks')->insert([
                [
                    'bank_name' => 'Bank Central Asia (BCA)',
                    'account_number' => '8830-192-831',
                    'account_holder' => 'PT WAHANA PUTRA YUDHA',
                    'branch_name' => 'KCU Tangerang Kisamaun',
                    'account_type' => 'BANK',
                    'balance' => 450000000,
                    'status' => 'ACTIVE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'bank_name' => 'Bank Mandiri',
                    'account_number' => '155-00-9823101-2',
                    'account_holder' => 'PT WAHANA PUTRA YUDHA',
                    'branch_name' => 'KC Bintaro Sektor 7',
                    'account_type' => 'BANK',
                    'balance' => 280000000,
                    'status' => 'ACTIVE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'bank_name' => 'Bank Syariah Indonesia (BSI)',
                    'account_number' => '712-093-8491',
                    'account_holder' => 'PT WAHANA PUTRA YUDHA',
                    'branch_name' => 'KC BSD City Serpong',
                    'account_type' => 'BANK',
                    'balance' => 125000000,
                    'status' => 'ACTIVE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'bank_name' => 'Kas Operasional Kasir',
                    'account_number' => 'KAS-TGR-01',
                    'account_holder' => 'Bendahara Kasir TAP Tangerang',
                    'branch_name' => 'Kantor TAP Tangerang',
                    'account_type' => 'CASH',
                    'balance' => 15450000,
                    'status' => 'ACTIVE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'bank_name' => 'Kas Operasional Kasir',
                    'account_number' => 'KAS-SRP-02',
                    'account_holder' => 'Bendahara Kasir TAP Serpong',
                    'branch_name' => 'Kantor TAP Serpong',
                    'account_type' => 'CASH',
                    'balance' => 8750000,
                    'status' => 'ACTIVE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        $banks = DB::table('company_banks')->orderBy('account_type')->orderBy('bank_name')->get();

        $kpi = [
            'total_accounts' => $banks->count(),
            'total_balance' => $banks->where('status', 'ACTIVE')->sum('balance'),
            'bank_count' => $banks->where('account_type', 'BANK')->count(),
            'cash_count' => $banks->where('account_type', 'CASH')->count(),
        ];

        return view('master.banks', compact('banks', 'kpi'));
    }

    public function storeBank(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string|max:60',
            'account_number' => 'nullable|string|max:50',
            'account_holder' => 'required|string|max:100',
            'branch_name' => 'nullable|string|max:100',
            'account_type' => 'required|in:BANK,CASH',
            'balance' => 'required|numeric|min:0',
        ]);

        DB::table('company_banks')->insert([
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number ?: 'KAS-' . strtoupper(substr(uniqid(), -4)),
            'account_holder' => $request->account_holder,
            'branch_name' => $request->branch_name,
            'account_type' => $request->account_type,
            'balance' => $request->balance,
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('master.banks')->with('success', "Rekening [{$request->bank_name} - {$request->account_holder}] berhasil ditambahkan.");
    }

    // ========================================================
    // MASTER TAMBAHAN 2: VENDOR & PRINCIPAL
    // ========================================================
    public function vendors(Request $request)
    {
        if (DB::table('vendors')->count() === 0) {
            DB::table('vendors')->insert([
                [
                    'code' => 'VDR-001',
                    'name' => 'PT Telekomunikasi Selular (Telkomsel)',
                    'category' => 'PRINCIPAL_TELCO',
                    'contact_person' => 'Bpk. Arya Wibowo (Account Manager)',
                    'phone' => '021-5240123',
                    'email' => 'corporate.sales@telkomsel.co.id',
                    'address' => 'Telkom Landmark Tower, Jl. Gatot Subroto No. 52, Jakarta Selatan',
                    'status' => 'ACTIVE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'VDR-002',
                    'name' => 'CV Mandiri Perkasa Printing',
                    'category' => 'PRINTING_VOUCHER',
                    'contact_person' => 'Bpk. Bambang Sutrisno',
                    'phone' => '0812-8899-2211',
                    'email' => 'sales@mandiriprinting.com',
                    'address' => 'Kawasan Industri Manis Blok C No. 4, Jatiuwung, Tangerang',
                    'status' => 'ACTIVE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'VDR-003',
                    'name' => 'PT Graha Media Distribusi',
                    'category' => 'MERCHANDISE_POSM',
                    'contact_person' => 'Ibu Ratna Dewi',
                    'phone' => '021-5830911',
                    'email' => 'cs@grahamedia.co.id',
                    'address' => 'Ruko ITC BSD Sektor 7 No. 12, Serpong, Tangerang Selatan',
                    'status' => 'ACTIVE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'VDR-004',
                    'name' => 'PT Logistik Nusantara Express',
                    'category' => 'LOGISTIK_ARMADA',
                    'contact_person' => 'Bpk. Dedi Kurniawan',
                    'phone' => '0811-9022-3344',
                    'email' => 'ops@logistiknusantara.co.id',
                    'address' => 'Jl. Daan Mogot Km. 19 No. 88, Batuceper, Tangerang',
                    'status' => 'ACTIVE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        $vendors = DB::table('vendors')->orderBy('code')->get();

        $kpi = [
            'total_vendors' => $vendors->count(),
            'active_vendors' => $vendors->where('status', 'ACTIVE')->count(),
            'principal_count' => $vendors->where('category', 'PRINCIPAL_TELCO')->count(),
            'supplies_count' => $vendors->whereIn('category', ['PRINTING_VOUCHER', 'MERCHANDISE_POSM', 'LOGISTIK_ARMADA'])->count(),
        ];

        return view('master.vendors', compact('vendors', 'kpi'));
    }

    public function storeVendor(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'category' => 'required|string|max:60',
            'contact_person' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
        ]);

        $nextNum = DB::table('vendors')->count() + 1;
        $code = 'VDR-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

        DB::table('vendors')->insert([
            'code' => $code,
            'name' => $request->name,
            'category' => $request->category,
            'contact_person' => $request->contact_person,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('master.vendors')->with('success', "Vendor [{$code} - {$request->name}] berhasil didaftarkan.");
    }

    // ========================================================
    // MASTER TAMBAHAN 3: SKEMA HARGA (PRICE PROFILE)
    // ========================================================
    public function priceProfiles(Request $request)
    {
        if (DB::table('price_profiles')->count() === 0) {
            DB::table('price_profiles')->insert([
                [
                    'code' => 'PRC-RS',
                    'name' => 'Tier Retail Outlet (RS)',
                    'segment' => 'RS',
                    'discount_percentage' => 0.00,
                    'min_order_qty' => 1,
                    'description' => 'Skema harga normal untuk konter pulsa retail umum / outlet sell-through.',
                    'status' => 'ACTIVE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'PRC-BOS',
                    'name' => 'Tier Grosir / Bakul (BOS)',
                    'segment' => 'BOS',
                    'discount_percentage' => 2.50,
                    'min_order_qty' => 50,
                    'description' => 'Skema harga khusus pembelian grosir partai (Bakul Kuota) dengan volume order tinggi.',
                    'status' => 'ACTIVE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'PRC-SD',
                    'name' => 'Tier Sub-Distributor (SD)',
                    'segment' => 'SD',
                    'discount_percentage' => 4.50,
                    'min_order_qty' => 250,
                    'description' => 'Skema harga distributor cabang sub-wilayah dengan komitmen buffer stok wilayah cluster.',
                    'status' => 'ACTIVE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        $profiles = DB::table('price_profiles')->orderBy('code')->get();

        $kpi = [
            'total_profiles' => $profiles->count(),
            'active_profiles' => $profiles->where('status', 'ACTIVE')->count(),
            'rs_count' => DB::table('customers')->where('status', 'ACTIVE')->count(),
        ];

        return view('master.price_profiles', compact('profiles', 'kpi'));
    }

    public function storePriceProfile(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:30|unique:price_profiles,code',
            'name' => 'required|string|max:100',
            'segment' => 'required|in:RS,BOS,SD',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'min_order_qty' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        DB::table('price_profiles')->insert([
            'code' => strtoupper($request->code),
            'name' => $request->name,
            'segment' => $request->segment,
            'discount_percentage' => $request->discount_percentage,
            'min_order_qty' => $request->min_order_qty,
            'description' => $request->description,
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('master.price-profiles')->with('success', "Skema harga [{$request->code} - {$request->name}] berhasil ditambahkan.");
    }

    // ========================================================
    // MASTER TAMBAHAN 4, 5, 6: PREVIEWS & SEGMENTS
    // ========================================================
    public function customerSegments()
    {
        return view('master.customer_segments');
    }

    public function territories()
    {
        return view('master.territories');
    }

    public function approvalRules()
    {
        return view('master.approval_rules');
    }
}
