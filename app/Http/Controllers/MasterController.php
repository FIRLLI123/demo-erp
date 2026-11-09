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
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        $nextId = DB::table('customers')->count() + 1;
        $customerCode = 'CST-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        DB::table('customers')->insert([
            'tap_id' => $request->tap_id,
            'code' => $customerCode,
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
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        DB::table('customers')->where('id', $id)->update([
            'name' => $request->name,
            'tap_id' => $request->tap_id,
            'type' => $request->type,
            'phone' => $request->phone,
            'address' => $request->address,
            'credit_limit' => $request->credit_limit ?? 0,
            'status' => $request->status,
            'updated_at' => now(),
        ]);

        return redirect()->route('master.customers')->with('success', "Data Outlet / Toko [{$request->name}] berhasil diperbarui.");
    }
}
