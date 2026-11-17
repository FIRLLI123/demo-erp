<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterController extends Controller
{
    public function organization()
    {
        // Ambil data utuh untuk client-side filtering instan (tanpa reload / loading berulang)
        $areas = DB::table('areas')->orderBy('id')->get();

        $clusters = DB::table('clusters')
            ->join('areas', 'clusters.area_id', '=', 'areas.id')
            ->select('clusters.*', 'areas.name as area_name', 'areas.code as area_code')
            ->orderBy('clusters.id')
            ->get();

        $taps = DB::table('taps')
            ->join('clusters', 'taps.cluster_id', '=', 'clusters.id')
            ->join('areas', 'clusters.area_id', '=', 'areas.id')
            ->select(
                'taps.*',
                'clusters.name as cluster_name',
                'clusters.code as cluster_code',
                'clusters.area_id',
                'areas.name as area_name',
                'areas.code as area_code'
            )
            ->orderBy('taps.id')
            ->get();

        $warehouses = DB::table('warehouses')
            ->join('taps', 'warehouses.tap_id', '=', 'taps.id')
            ->join('clusters', 'taps.cluster_id', '=', 'clusters.id')
            ->join('areas', 'clusters.area_id', '=', 'areas.id')
            ->leftJoin('sales', 'warehouses.sales_id', '=', 'sales.id')
            ->select(
                'warehouses.*',
                'taps.name as tap_name',
                'taps.code as tap_code',
                'taps.cluster_id',
                'clusters.name as cluster_name',
                'clusters.code as cluster_code',
                'clusters.area_id',
                'areas.name as area_name',
                'areas.code as area_code',
                'sales.name as sales_name',
                'sales.code as sales_code'
            )
            ->orderBy('warehouses.id')
            ->get();

        return view('master.organization', compact('areas', 'clusters', 'taps', 'warehouses'));
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
            ->leftJoin('taps', 'customers.tap_id', '=', 'taps.id')
            ->leftJoin('sales', 'customers.sales_id', '=', 'sales.id')
            ->select(
                'customers.*',
                'taps.name as tap_name',
                'sales.name as sales_name',
                'sales.code as sales_code'
            )
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

        // Ambil semua nomor RS terdaftar per customer
        $allRs = DB::table('customer_rs_numbers')
            ->orderBy('is_primary', 'desc')
            ->orderBy('id', 'asc')
            ->get()
            ->groupBy('customer_id');

        foreach ($customers as $c) {
            $c->unpaid_ar_amount = $outstandingAr[$c->id]->total_unpaid ?? 0;
            $c->unpaid_ar_count = $outstandingAr[$c->id]->unpaid_count ?? 0;

            $c->rs_list = $allRs[$c->id] ?? collect([]);
            $primaryRs = $c->rs_list->firstWhere('is_primary', 1);
            $c->primary_rs_number = $primaryRs ? $primaryRs->rs_number : ($c->nomor_rs ?? '-');
            $c->primary_rs_name = $primaryRs ? $primaryRs->rs_name : 'RS Utama';
            $c->total_rs_count = $c->rs_list->count();
        }

        $taps = DB::table('taps')->where('status', 'ACTIVE')->orderBy('name')->get();
        $salesList = DB::table('sales')->where('status', 'ACTIVE')->orderBy('name')->get();

        return view('master.customers', compact('customers', 'taps', 'salesList'));
    }

    /**
     * Ambil list nomor RS untuk 1 customer (AJAX)
     */
    public function getRsNumbers($customerId)
    {
        $customer = DB::table('customers')->where('id', $customerId)->first();
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Outlet tidak ditemukan.'], 404);
        }

        $rsNumbers = DB::table('customer_rs_numbers')
            ->where('customer_id', $customerId)
            ->orderBy('is_primary', 'desc')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'customer' => $customer,
            'rs_numbers' => $rsNumbers,
        ]);
    }

    /**
     * Tambah Nomor RS / Chip Mkios baru ke Customer
     */
    public function storeRsNumber(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'rs_number' => 'required|string|max:30',
            'rs_name' => 'nullable|string|max:100',
            'chip_type' => 'nullable|string|max:30',
            'is_primary' => 'nullable|boolean',
            'notes' => 'nullable|string|max:255',
        ]);

        $customerId = $request->customer_id;
        $isPrimary = $request->boolean('is_primary');

        // Jika nomor pertama untuk customer ini, otomatis jadikan primary
        $existingCount = DB::table('customer_rs_numbers')->where('customer_id', $customerId)->count();
        if ($existingCount === 0) {
            $isPrimary = true;
        }

        if ($isPrimary) {
            // Unset primary nomor lain milik customer ini
            DB::table('customer_rs_numbers')->where('customer_id', $customerId)->update(['is_primary' => false]);
            // Sinkron ke customers.nomor_rs
            DB::table('customers')->where('id', $customerId)->update(['nomor_rs' => $request->rs_number, 'updated_at' => now()]);
        }

        $id = DB::table('customer_rs_numbers')->insertGetId([
            'customer_id' => $customerId,
            'rs_number' => $request->rs_number,
            'rs_name' => $request->rs_name ?: 'Chip RS #' . ($existingCount + 1),
            'chip_type' => $request->chip_type ?: 'ALL',
            'is_primary' => $isPrimary,
            'status' => 'ACTIVE',
            'notes' => $request->notes,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $rsItem = DB::table('customer_rs_numbers')->where('id', $id)->first();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Nomor RS {$request->rs_number} berhasil ditambahkan!",
                'item' => $rsItem,
            ]);
        }

        return redirect()->back()->with('success', "Nomor RS {$request->rs_number} berhasil ditambahkan!");
    }

    /**
     * Update Nomor RS
     */
    public function updateRsNumber(Request $request, $id)
    {
        $request->validate([
            'rs_number' => 'required|string|max:30',
            'rs_name' => 'nullable|string|max:100',
            'chip_type' => 'nullable|string|max:30',
            'status' => 'required|in:ACTIVE,INACTIVE',
            'notes' => 'nullable|string|max:255',
        ]);

        $rsItem = DB::table('customer_rs_numbers')->where('id', $id)->first();
        if (!$rsItem) {
            return response()->json(['success' => false, 'message' => 'Data chip RS tidak ditemukan.'], 404);
        }

        DB::table('customer_rs_numbers')->where('id', $id)->update([
            'rs_number' => $request->rs_number,
            'rs_name' => $request->rs_name,
            'chip_type' => $request->chip_type ?: 'ALL',
            'status' => $request->status,
            'notes' => $request->notes,
            'updated_at' => now(),
        ]);

        if ($rsItem->is_primary) {
            DB::table('customers')->where('id', $rsItem->customer_id)->update(['nomor_rs' => $request->rs_number, 'updated_at' => now()]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Nomor RS {$request->rs_number} berhasil diperbarui!",
            ]);
        }

        return redirect()->back()->with('success', "Nomor RS {$request->rs_number} berhasil diperbarui!");
    }

    /**
     * Set Nomor RS sebagai Nomor RS Utama (Primary)
     */
    public function setPrimaryRsNumber($id)
    {
        $rsItem = DB::table('customer_rs_numbers')->where('id', $id)->first();
        if (!$rsItem) {
            return response()->json(['success' => false, 'message' => 'Data chip RS tidak ditemukan.'], 404);
        }

        // Reset semua nomor RS milik customer ini
        DB::table('customer_rs_numbers')->where('customer_id', $rsItem->customer_id)->update(['is_primary' => false]);
        // Set item ini jadi primary
        DB::table('customer_rs_numbers')->where('id', $id)->update(['is_primary' => true, 'updated_at' => now()]);
        // Sinkron ke customers.nomor_rs
        DB::table('customers')->where('id', $rsItem->customer_id)->update(['nomor_rs' => $rsItem->rs_number, 'updated_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => "Nomor RS {$rsItem->rs_number} berhasil dijadikan sebagai Nomor RS Utama toko!",
        ]);
    }

    /**
     * Hapus Nomor RS
     */
    public function deleteRsNumber($id)
    {
        $rsItem = DB::table('customer_rs_numbers')->where('id', $id)->first();
        if (!$rsItem) {
            return response()->json(['success' => false, 'message' => 'Data chip RS tidak ditemukan.'], 404);
        }

        $customerId = $rsItem->customer_id;
        $totalCount = DB::table('customer_rs_numbers')->where('customer_id', $customerId)->count();

        if ($totalCount <= 1) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus! Outlet minimal harus memiliki 1 nomor RS terdaftar.'], 422);
        }

        $wasPrimary = $rsItem->is_primary;
        DB::table('customer_rs_numbers')->where('id', $id)->delete();

        // Jika yang dihapus adalah primary, jadikan nomor berikutnya sebagai primary
        if ($wasPrimary) {
            $nextRs = DB::table('customer_rs_numbers')->where('customer_id', $customerId)->first();
            if ($nextRs) {
                DB::table('customer_rs_numbers')->where('id', $nextRs->id)->update(['is_primary' => true]);
                DB::table('customers')->where('id', $customerId)->update(['nomor_rs' => $nextRs->rs_number]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Nomor RS berhasil dihapus dari outlet.",
        ]);
    }

    public function storeCustomer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'tap_id' => 'required|exists:taps,id',
            'sales_id' => 'nullable|exists:sales,id',
            'type' => 'required|in:RETAIL,GROSIR',
            'digipos_id' => 'nullable|string|max:50',
            'nomor_rs' => 'nullable|string|max:30',
            'phone' => 'nullable|string|max:20',
            'customer_pic' => 'nullable|string|max:100',
            'customer_ktp' => 'nullable|string|max:30',
            'hari_pjp' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'kelurahan_name' => 'nullable|string|max:100',
            'kecamatan_name' => 'nullable|string|max:100',
            'kota_name' => 'nullable|string|max:100',
            'provinsi_name' => 'nullable|string|max:100',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        $nextId = DB::table('customers')->count() + 1;
        $customerCode = 'CST-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        DB::table('customers')->insert([
            'tap_id' => $request->tap_id,
            'sales_id' => $request->sales_id,
            'code' => $customerCode,
            'digipos_id' => $request->digipos_id ?? ('DGP-' . rand(100000, 999999)),
            'nomor_rs' => $request->nomor_rs ?? ($request->phone ?? '0812' . rand(10000000, 99999999)),
            'name' => $request->name,
            'type' => $request->type,
            'phone' => $request->phone ?? '0812' . rand(10000000, 99999999),
            'customer_pic' => $request->customer_pic,
            'customer_ktp' => $request->customer_ktp,
            'hari_pjp' => $request->hari_pjp,
            'address' => $request->address ?? 'Area Operasional TAP',
            'kelurahan_name' => $request->kelurahan_name,
            'kecamatan_name' => $request->kecamatan_name,
            'kota_name' => $request->kota_name,
            'provinsi_name' => $request->provinsi_name,
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
            'sales_id' => 'nullable|exists:sales,id',
            'type' => 'required|in:RETAIL,GROSIR',
            'digipos_id' => 'nullable|string|max:50',
            'nomor_rs' => 'nullable|string|max:30',
            'phone' => 'nullable|string|max:20',
            'customer_pic' => 'nullable|string|max:100',
            'customer_ktp' => 'nullable|string|max:30',
            'hari_pjp' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'kelurahan_name' => 'nullable|string|max:100',
            'kecamatan_name' => 'nullable|string|max:100',
            'kota_name' => 'nullable|string|max:100',
            'provinsi_name' => 'nullable|string|max:100',
            'credit_limit' => 'nullable|numeric|min:0',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        DB::table('customers')->where('id', $id)->update([
            'name' => $request->name,
            'tap_id' => $request->tap_id,
            'sales_id' => $request->sales_id,
            'type' => $request->type,
            'digipos_id' => $request->digipos_id,
            'nomor_rs' => $request->nomor_rs,
            'phone' => $request->phone,
            'customer_pic' => $request->customer_pic,
            'customer_ktp' => $request->customer_ktp,
            'hari_pjp' => $request->hari_pjp,
            'address' => $request->address,
            'kelurahan_name' => $request->kelurahan_name,
            'kecamatan_name' => $request->kecamatan_name,
            'kota_name' => $request->kota_name,
            'provinsi_name' => $request->provinsi_name,
            'credit_limit' => $request->credit_limit ?? 0,
            'status' => $request->status,
            'updated_at' => now(),
        ]);

        return redirect()->route('master.customers')->with('success', "Data Outlet / Toko [{$request->name}] berhasil diperbarui.");
    }

    public function users(Request $request)
    {
        $usersRaw = DB::table('users')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->leftJoin('sales', 'users.sales_id', '=', 'sales.id')
            ->leftJoin('taps as sales_taps', 'sales.tap_id', '=', 'sales_taps.id')
            ->select(
                'users.*',
                'roles.name as role_name',
                'roles.slug as role_slug',
                'sales.code as sales_code',
                'sales.name as sales_name',
                'sales.phone as sales_phone',
                'sales_taps.name as sales_tap'
            )
            ->orderBy('users.id')
            ->get();

        $userIds = $usersRaw->pluck('id')->toArray();

        // Ambil mapping dari tabel tunggal user_has_areas_clusters_taps_warehouses
        $allMappings = DB::table('user_has_areas_clusters_taps_warehouses')
            ->whereIn('user_id', $userIds)
            ->get()
            ->groupBy('user_id');

        $users = $usersRaw->map(function ($u) use ($allMappings) {
            $userMaps = $allMappings->get($u->id, collect([]));
            $u->mapped_area_ids = $userMaps->pluck('area_id')->filter()->unique()->values()->map('intval')->toArray();
            $u->mapped_cluster_ids = $userMaps->pluck('cluster_id')->filter()->unique()->values()->map('intval')->toArray();
            $u->mapped_tap_ids = $userMaps->pluck('tap_id')->filter()->unique()->values()->map('intval')->toArray();
            
            // TAP yang mode auto all warehouses (+sales baru)
            $u->auto_tap_ids = $userMaps->where('is_all_warehouses', 1)->pluck('tap_id')->filter()->unique()->values()->map('intval')->toArray();
            
            // Gudang spesifik yang dicentang manual
            $u->mapped_warehouse_ids = $userMaps->where('is_all_warehouses', 0)->pluck('warehouse_id')->filter()->unique()->values()->map('intval')->toArray();

            $u->taps_count = count($u->mapped_tap_ids);
            $u->warehouses_count = count($u->mapped_warehouse_ids);
            return $u;
        });

        $roles = DB::table('roles')->orderBy('id')->get();
        $salesList = DB::table('sales')->where('status', 'ACTIVE')->orderBy('name')->get();

        // Buat struktur pohon wilayah: Area -> Cluster -> TAP -> Warehouses
        $areas = DB::table('areas')->where('status', 'ACTIVE')->orderBy('name')->get();
        $clusters = DB::table('clusters')->where('status', 'ACTIVE')->orderBy('name')->get()->groupBy('area_id');
        $taps = DB::table('taps')->where('status', 'ACTIVE')->orderBy('name')->get()->groupBy('cluster_id');
        $warehouses = DB::table('warehouses')
            ->leftJoin('sales', 'warehouses.sales_id', '=', 'sales.id')
            ->select('warehouses.*', 'sales.name as sales_person_name')
            ->where('warehouses.status', 'ACTIVE')
            ->orderBy('warehouses.type')
            ->orderBy('warehouses.name')
            ->get()
            ->groupBy('tap_id');

        $territoryTree = $areas->map(function ($area) use ($clusters, $taps, $warehouses) {
            $areaClusters = $clusters->get($area->id, collect([]))->map(function ($cluster) use ($taps, $warehouses) {
                $clusterTaps = $taps->get($cluster->id, collect([]))->map(function ($tap) use ($warehouses) {
                    $tapWhs = $warehouses->get($tap->id, collect([]))->values();
                    return [
                        'id' => (int)$tap->id,
                        'name' => $tap->name,
                        'code' => $tap->code,
                        'warehouses' => $tapWhs,
                    ];
                })->values();

                return [
                    'id' => (int)$cluster->id,
                    'name' => $cluster->name,
                    'code' => $cluster->code,
                    'taps' => $clusterTaps,
                ];
            })->values();

            return [
                'id' => (int)$area->id,
                'name' => $area->name,
                'code' => $area->code,
                'clusters' => $areaClusters,
            ];
        })->values();

        return view('master.users', compact('users', 'roles', 'salesList', 'territoryTree'));
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
            'has_full_access' => 0,
            'is_ho_readonly' => 0,
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

        return redirect()->route('master.users')->with('success', "Akun pengguna [{$request->name}] berhasil dibuat. Silakan atur wilayah akses melalui tombol Mapping.");
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

        return redirect()->route('master.users')->with('success', "Data profil akun [{$request->name}] berhasil diperbarui.");
    }

    /**
     * Halaman Dedicated: Otorisasi Wilayah & Gudang Pengguna
     */
    public function userMapping($id)
    {
        $user = DB::table('users')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->select('users.*', 'roles.name as role_name', 'roles.slug as role_slug')
            ->where('users.id', $id)
            ->first();

        if (!$user) {
            return redirect()->route('master.users')->with('error', 'Pengguna tidak ditemukan!');
        }

        $userMappings = DB::table('user_has_areas_clusters_taps_warehouses')
            ->where('user_id', $id)
            ->get();

        $mappedAreaIds = $userMappings->pluck('area_id')->filter()->unique()->values()->map('intval')->toArray();
        $mappedClusterIds = $userMappings->pluck('cluster_id')->filter()->unique()->values()->map('intval')->toArray();
        $mappedTapIds = $userMappings->pluck('tap_id')->filter()->unique()->values()->map('intval')->toArray();
        $autoTapIds = $userMappings->where('is_all_warehouses', 1)->pluck('tap_id')->filter()->unique()->values()->map('intval')->toArray();
        $customWhIds = $userMappings->where('is_all_warehouses', 0)->pluck('warehouse_id')->filter()->unique()->values()->map('intval')->toArray();

        // Buat struktur pohon wilayah: Area -> Cluster -> TAP -> Warehouses
        $areas = DB::table('areas')->where('status', 'ACTIVE')->orderBy('name')->get();
        $clusters = DB::table('clusters')->where('status', 'ACTIVE')->orderBy('name')->get()->groupBy('area_id');
        $taps = DB::table('taps')->where('status', 'ACTIVE')->orderBy('name')->get()->groupBy('cluster_id');
        $warehouses = DB::table('warehouses')
            ->leftJoin('sales', 'warehouses.sales_id', '=', 'sales.id')
            ->select('warehouses.*', 'sales.name as sales_person_name')
            ->where('warehouses.status', 'ACTIVE')
            ->orderBy('warehouses.type')
            ->orderBy('warehouses.name')
            ->get()
            ->groupBy('tap_id');

        $territoryTree = $areas->map(function ($area) use ($clusters, $taps, $warehouses) {
            $areaClusters = $clusters->get($area->id, collect([]))->map(function ($cluster) use ($taps, $warehouses) {
                $clusterTaps = $taps->get($cluster->id, collect([]))->map(function ($tap) use ($warehouses) {
                    $tapWhs = $warehouses->get($tap->id, collect([]))->values();
                    return [
                        'id' => (int)$tap->id,
                        'name' => $tap->name,
                        'code' => $tap->code,
                        'warehouses' => $tapWhs,
                    ];
                })->values();

                return [
                    'id' => (int)$cluster->id,
                    'name' => $cluster->name,
                    'code' => $cluster->code,
                    'taps' => $clusterTaps,
                ];
            })->values();

            return [
                'id' => (int)$area->id,
                'name' => $area->name,
                'code' => $area->code,
                'clusters' => $areaClusters,
            ];
        })->values();

        return view('master.user-mapping', compact(
            'user',
            'territoryTree',
            'mappedAreaIds',
            'mappedClusterIds',
            'mappedTapIds',
            'autoTapIds',
            'customWhIds'
        ));
    }

    /**
     * Otorisasi Wilayah & Gudang Transaksi (Pohon Cascading Tree)
     */
    public function saveUserMapping(Request $request, $id)
    {
        $user = DB::table('users')->where('id', $id)->first();
        if (!$user) {
            return redirect()->route('master.users')->with('error', 'Data pengguna tidak ditemukan!');
        }

        $hasFullAccess = $request->input('has_full_access') == '1';
        $isHo = $request->input('is_ho_readonly') == '1';

        // 1. Update status akun pengguna
        DB::table('users')->where('id', $id)->update([
            'has_full_access' => $hasFullAccess ? 1 : 0,
            'is_ho_readonly' => $isHo ? 1 : 0,
            'updated_at' => now(),
        ]);

        // 2. Bersihkan riwayat mapping lama di tabel tunggal
        DB::table('user_has_areas_clusters_taps_warehouses')->where('user_id', $id)->delete();

        // 3. Jika bukan akses penuh, simpan pohon wilayah terpilih
        if (!$hasFullAccess) {
            $selectedTaps = array_filter(array_map('intval', $request->input('selected_taps', [])));
            $autoTapIds = array_filter(array_map('intval', $request->input('auto_taps', [])));
            $customWhs = array_filter(array_map('intval', $request->input('custom_warehouses', [])));

            $allTaps = DB::table('taps')
                ->join('clusters', 'taps.cluster_id', '=', 'clusters.id')
                ->select('taps.id as tap_id', 'taps.cluster_id', 'clusters.area_id')
                ->get()
                ->keyBy('tap_id');

            $recordsToInsert = [];

            foreach ($selectedTaps as $tId) {
                if (!isset($allTaps[$tId])) continue;
                $tapInfo = $allTaps[$tId];

                // Cek apakah TAP ini memilih mode auto all warehouses (+sales baru)
                $isAutoAll = in_array($tId, $autoTapIds);

                if ($isAutoAll) {
                    $recordsToInsert[] = [
                        'user_id' => $id,
                        'area_id' => $tapInfo->area_id,
                        'cluster_id' => $tapInfo->cluster_id,
                        'tap_id' => $tId,
                        'warehouse_id' => null,
                        'is_all_warehouses' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                } else {
                    // Ambil gudang spesifik yang dicentang di TAP ini
                    $tapWhs = DB::table('warehouses')->where('tap_id', $tId)->pluck('id')->toArray();
                    $checkedWhs = array_intersect($tapWhs, $customWhs);

                    if (!empty($checkedWhs)) {
                        foreach ($checkedWhs as $whId) {
                            $recordsToInsert[] = [
                                'user_id' => $id,
                                'area_id' => $tapInfo->area_id,
                                'cluster_id' => $tapInfo->cluster_id,
                                'tap_id' => $tId,
                                'warehouse_id' => $whId,
                                'is_all_warehouses' => 0,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    } else {
                        // Jika 0 gudang dicentang (misal TAP monitoring)
                        $recordsToInsert[] = [
                            'user_id' => $id,
                            'area_id' => $tapInfo->area_id,
                            'cluster_id' => $tapInfo->cluster_id,
                            'tap_id' => $tId,
                            'warehouse_id' => null,
                            'is_all_warehouses' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }

            if (!empty($recordsToInsert)) {
                DB::table('user_has_areas_clusters_taps_warehouses')->insert($recordsToInsert);
            }
        }

        $statusMsg = $hasFullAccess 
            ? "Hak akses akun [{$user->name}] diatur ke: Akses Penuh (Semua Wilayah & Gudang)."
            : ($isHo 
                ? "Akun [{$user->name}] dikonfigurasi sebagai Mode Monitoring (Read-Only: hanya melihat laporan wilayah)."
                : "Hak akses wilayah dan gudang untuk akun [{$user->name}] berhasil diperbarui.");

        return redirect()->route('master.users.mapping', $id)->with('success', $statusMsg);
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

    /**
     * Universal Switch Toggle Status (ACTIVE <-> INACTIVE) untuk Semua Data Master
     */
    public function toggleStatus(Request $request)
    {
        $request->validate([
            'entity' => 'required|string|in:customers,sales,products,warehouses,banks,vendors,taps,price_profiles,users,customer_rs',
            'id' => 'required|integer',
        ]);

        $entity = $request->entity;
        $id = $request->id;

        $mapping = [
            'customers' => ['table' => 'customers', 'name_col' => 'name', 'status_col' => 'status', 'label' => 'Toko / Outlet'],
            'sales' => ['table' => 'sales', 'name_col' => 'name', 'status_col' => 'status', 'label' => 'Salesman'],
            'products' => ['table' => 'products', 'name_col' => 'name', 'status_col' => 'status', 'label' => 'Produk'],
            'warehouses' => ['table' => 'warehouses', 'name_col' => 'name', 'status_col' => 'status', 'label' => 'Gudang'],
            'banks' => ['table' => 'company_banks', 'name_col' => 'bank_name', 'status_col' => 'status', 'label' => 'Rekening Bank'],
            'vendors' => ['table' => 'vendors', 'name_col' => 'name', 'status_col' => 'status', 'label' => 'Vendor'],
            'taps' => ['table' => 'taps', 'name_col' => 'name', 'status_col' => 'status', 'label' => 'Kantor Cabang TAP'],
            'price_profiles' => ['table' => 'price_profiles', 'name_col' => 'name', 'status_col' => 'status', 'label' => 'Skema Harga'],
            'users' => ['table' => 'users', 'name_col' => 'name', 'status_col' => 'is_active', 'label' => 'Pengguna', 'is_boolean' => true],
            'customer_rs' => ['table' => 'customer_rs_numbers', 'name_col' => 'rs_number', 'status_col' => 'status', 'label' => 'Nomor Chip RS'],
        ];

        $config = $mapping[$entity];
        $item = DB::table($config['table'])->where('id', $id)->first();

        if (!$item) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
            }
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        $isBoolean = $config['is_boolean'] ?? false;
        $statusCol = $config['status_col'];
        $nameCol = $config['name_col'];
        $itemName = $item->$nameCol ?? "ID #{$id}";

        if ($isBoolean) {
            $newVal = $item->$statusCol ? 0 : 1;
            $newStatusText = $newVal ? 'ACTIVE' : 'INACTIVE';
            $isActive = (bool) $newVal;
        } else {
            $currentVal = strtoupper($item->$statusCol ?? 'ACTIVE');
            $newVal = ($currentVal === 'ACTIVE') ? 'INACTIVE' : 'ACTIVE';
            $newStatusText = $newVal;
            $isActive = ($newVal === 'ACTIVE');
        }

        DB::table($config['table'])->where('id', $id)->update([
            $statusCol => $newVal,
            'updated_at' => now(),
        ]);

        // Catat jejak audit log jika tabel activity_logs ada
        if (\Illuminate\Support\Facades\Schema::hasTable('activity_logs')) {
            DB::table('activity_logs')->insert([
                'user_id' => 1,
                'user_name' => 'Administrator ERP',
                'user_role' => 'Super Admin',
                'action' => 'TOGGLE_STATUS_' . strtoupper($entity),
                'module' => 'MASTER',
                'doc_number' => "MST-{$id}",
                'severity' => $isActive ? 'INFO' : 'WARNING',
                'description' => "Mengubah status {$config['label']} '{$itemName}' menjadi " . ($isActive ? 'AKTIF (ACTIVE/00)' : 'NON-AKTIF (INACTIVE/99)'),
                'ip_address' => $request->ip() ?? '127.0.0.1',
                'user_agent' => $request->userAgent() ?? 'System',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $statusLabel = $isActive ? 'AKTIF' : 'NON-AKTIF';
        $message = "Status {$config['label']} '{$itemName}' berhasil diubah menjadi {$statusLabel}!";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_active' => $isActive,
                'status' => $newStatusText,
                'message' => $message,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }
}
