<?php

namespace App\Http\Controllers;

use App\Services\TerritoryScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index()
    {
        // 1. Stock Balances grouped by Warehouse with product details and tap info
        $stockBalances = TerritoryScope::applyWarehouseScope(DB::table('stock_balances'), 'stock_balances.warehouse_id')
            ->join('warehouses', 'stock_balances.warehouse_id', '=', 'warehouses.id')
            ->join('taps', 'warehouses.tap_id', '=', 'taps.id')
            ->join('products', 'stock_balances.product_id', '=', 'products.id')
            ->select(
                'stock_balances.*',
                'warehouses.name as warehouse_name',
                'warehouses.code as warehouse_code',
                'warehouses.type as warehouse_type',
                'taps.name as tap_name',
                'products.name as product_name',
                'products.sku',
                'products.type as product_type',
                'products.tracking_method',
                'products.cost_price'
            )
            ->orderBy('warehouses.type')
            ->orderBy('warehouses.name')
            ->orderBy('products.name')
            ->get();

        // 2. ICCID Units with warehouse & product info
        $iccids = TerritoryScope::applyWarehouseScope(DB::table('iccid_units'), 'iccid_units.warehouse_id')
            ->join('products', 'iccid_units.product_id', '=', 'products.id')
            ->leftJoin('warehouses', 'iccid_units.warehouse_id', '=', 'warehouses.id')
            ->select('iccid_units.*', 'products.name as product_name', 'products.sku', 'warehouses.name as warehouse_name')
            ->orderBy('iccid_units.id', 'desc')
            ->take(100)
            ->get();

        // 3. Stock Movements (SM)
        $movements = TerritoryScope::applyWarehouseScope(DB::table('stock_movements'), 'stock_movements.source_warehouse_id')
            ->join('warehouses as sw', 'stock_movements.source_warehouse_id', '=', 'sw.id')
            ->join('warehouses as dw', 'stock_movements.destination_warehouse_id', '=', 'dw.id')
            ->select('stock_movements.*', 'sw.name as source_wh', 'dw.name as dest_wh')
            ->orderBy('stock_movements.created_at', 'desc')
            ->get();

        // 4. Stock Mutations Ledger (Buku Kartu Stok In - Out)
        $mutations = TerritoryScope::applyWarehouseScope(DB::table('stock_mutations'), 'stock_mutations.warehouse_id')
            ->join('warehouses', 'stock_mutations.warehouse_id', '=', 'warehouses.id')
            ->join('products', 'stock_mutations.product_id', '=', 'products.id')
            ->select(
                'stock_mutations.*',
                'warehouses.name as warehouse_name',
                'warehouses.type as warehouse_type',
                'products.name as product_name',
                'products.sku',
                'products.type as product_type',
                'products.tracking_method'
            )
            ->orderBy('stock_mutations.id', 'desc')
            ->take(150)
            ->get();

        // 5. Master data untuk filter
        $warehouses = TerritoryScope::applyWarehouseScope(
            DB::table('warehouses')
                ->join('taps', 'warehouses.tap_id', '=', 'taps.id')
                ->where('warehouses.status', 'ACTIVE')
                ->select('warehouses.*', 'taps.name as tap_name'),
            'warehouses.id'
        )
            ->orderBy('warehouses.type')
            ->orderBy('warehouses.name')
            ->get();

        $taps = TerritoryScope::applyTapScope(DB::table('taps')->where('status', 'ACTIVE')->orderBy('name'), 'taps.id')->get();

        return view('inventory.index', compact(
            'stockBalances', 
            'iccids', 
            'movements', 
            'mutations',
            'warehouses',
            'taps'
        ));
    }

    public function repack()
    {
        return redirect()->route('inventory.repack');
    }

    public function quickSm(Request $request)
    {
        $mainWh = DB::table('warehouses')->where('type', 'MAIN')->first();
        $slsWh = DB::table('warehouses')->where('type', 'SALES')->first();
        $prod = DB::table('products')->where('tracking_method', 'QUANTITY')->first();

        $nextNum = DB::table('stock_movements')->count() + 1;
        $docNum = 'SM/' . date('Ym') . '/' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            $smId = DB::table('stock_movements')->insertGetId([
                'doc_type' => 'SM',
                'doc_number' => $docNum,
                'source_warehouse_id' => $mainWh->id,
                'destination_warehouse_id' => $slsWh->id,
                'movement_date' => date('Y-m-d'),
                'status' => 'COMPLETED',
                'notes' => 'Alokasi stok kanvas harian ke sales motor',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('stock_movement_items')->insert([
                'stock_movement_id' => $smId,
                'product_id' => $prod->id,
                'quantity' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Deduct source and add to destination
            DB::table('stock_balances')->where('warehouse_id', $mainWh->id)->where('product_id', $prod->id)->decrement('quantity', 50);
            DB::table('stock_balances')->where('warehouse_id', $slsWh->id)->where('product_id', $prod->id)->increment('quantity', 50);

            // Record in Stock Mutations Ledger (OUT from MAIN)
            $balMain = DB::table('stock_balances')->where('warehouse_id', $mainWh->id)->where('product_id', $prod->id)->value('quantity') ?? 0;
            DB::table('stock_mutations')->insert([
                'date' => date('Y-m-d'),
                'warehouse_id' => $mainWh->id,
                'product_id' => $prod->id,
                'doc_type' => 'SM',
                'doc_number' => $docNum,
                'mutation_type' => 'OUT',
                'quantity' => 50,
                'balance_after' => $balMain,
                'notes' => "Mutasi keluar ke {$slsWh->name}",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Record in Stock Mutations Ledger (IN to SALES)
            $balSls = DB::table('stock_balances')->where('warehouse_id', $slsWh->id)->where('product_id', $prod->id)->value('quantity') ?? 0;
            DB::table('stock_mutations')->insert([
                'date' => date('Y-m-d'),
                'warehouse_id' => $slsWh->id,
                'product_id' => $prod->id,
                'doc_type' => 'SM',
                'doc_number' => $docNum,
                'mutation_type' => 'IN',
                'quantity' => 50,
                'balance_after' => $balSls,
                'notes' => "Penerimaan mutasi dari {$mainWh->name}",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            return redirect()->back()->with('success', "Mutasi Stok [{$docNum}] berhasil! 50 pcs dipindahkan & kartu stok dicatat.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mutasi: ' . $e->getMessage());
        }
    }

    /**
     * Halaman Stock Adjustment (+/-)
     */
    public function adjustment(Request $request)
    {
        $query = DB::table('stock_mutations')
            ->join('warehouses', 'stock_mutations.warehouse_id', '=', 'warehouses.id')
            ->join('products', 'stock_mutations.product_id', '=', 'products.id')
            ->leftJoin('taps', 'warehouses.tap_id', '=', 'taps.id')
            ->leftJoin('clusters', 'taps.cluster_id', '=', 'clusters.id')
            ->leftJoin('areas', 'clusters.area_id', '=', 'areas.id')
            ->where('stock_mutations.doc_type', 'ADJ')
            ->select(
                'stock_mutations.*',
                'warehouses.name as warehouse_name',
                'warehouses.code as warehouse_code',
                'warehouses.type as warehouse_type',
                'taps.name as tap_name',
                'clusters.name as cluster_name',
                'products.name as product_name',
                'products.sku',
                'products.type as product_type'
            )
            ->orderByDesc('stock_mutations.id');

        TerritoryScope::applyWarehouseScope($query, 'stock_mutations.warehouse_id');

        $adjustments = $query->get();

        $whQuery = DB::table('warehouses')
            ->where('status', 'ACTIVE')
            ->orderBy('type')
            ->orderBy('name');
        TerritoryScope::applyWarehouseScope($whQuery, 'id');
        $warehouses = $whQuery->get();

        $products = DB::table('products')
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get();

        $balancesQuery = DB::table('stock_balances')
            ->select('warehouse_id', 'product_id', 'quantity');
        TerritoryScope::applyWarehouseScope($balancesQuery, 'warehouse_id');
        $balances = $balancesQuery->get();

        $totalAdj = $adjustments->count();
        $totalPlus = $adjustments->where('mutation_type', 'IN')->sum('quantity');
        $totalMinus = $adjustments->where('mutation_type', 'OUT')->sum('quantity');
        $totalWarehouseAffected = $adjustments->pluck('warehouse_id')->unique()->count();

        return view('inventory.adjustment', compact(
            'adjustments',
            'warehouses',
            'products',
            'balances',
            'totalAdj',
            'totalPlus',
            'totalMinus',
            'totalWarehouseAffected'
        ));
    }

    /**
     * Proses Simpan Stock Adjustment (+/-) dengan Update Stok Realtime
     */
    public function storeAdjustment(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'required|exists:products,id',
            'adjustment_type' => 'required|in:PLUS,MINUS',
            'quantity' => 'required|integer|min:1',
            'notes' => 'required|string|max:255',
        ]);

        $warehouseId = (int) $request->input('warehouse_id');
        $productId = (int) $request->input('product_id');
        $type = $request->input('adjustment_type');
        $qty = (int) $request->input('quantity');
        $notes = $request->input('notes');

        // Validasi hak akses wilayah user terhadap gudang
        if (!TerritoryScope::canAccessWarehouse($warehouseId)) {
            return redirect()->back()->with('error', 'Akses ditolak: Anda tidak memiliki wewenang untuk gudang ini.');
        }

        DB::beginTransaction();
        try {
            $balance = DB::table('stock_balances')
                ->where('warehouse_id', $warehouseId)
                ->where('product_id', $productId)
                ->first();

            $currentStock = $balance ? (int) $balance->quantity : 0;

            if ($type === 'MINUS' && $currentStock < $qty) {
                return redirect()->back()->with('error', "Penyesuaian stok minus gagal! Stok saat ini ({$currentStock}) tidak mencukupi untuk dikurangi sebanyak {$qty}.");
            }

            if (!$balance) {
                DB::table('stock_balances')->insert([
                    'warehouse_id' => $warehouseId,
                    'product_id' => $productId,
                    'quantity' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $count = DB::table('stock_mutations')->where('doc_type', 'ADJ')->count() + 1;
            $docNum = 'ADJ/' . date('Ym') . '/' . str_pad($count, 4, '0', STR_PAD_LEFT);

            if ($type === 'PLUS') {
                DB::table('stock_balances')
                    ->where('warehouse_id', $warehouseId)
                    ->where('product_id', $productId)
                    ->increment('quantity', $qty);
                $mutationType = 'IN';
            } else {
                DB::table('stock_balances')
                    ->where('warehouse_id', $warehouseId)
                    ->where('product_id', $productId)
                    ->decrement('quantity', $qty);
                $mutationType = 'OUT';
            }

            $balanceAfter = DB::table('stock_balances')
                ->where('warehouse_id', $warehouseId)
                ->where('product_id', $productId)
                ->value('quantity') ?? 0;

            // Catat ke Buku Kartu Stok (stock_mutations)
            DB::table('stock_mutations')->insert([
                'date' => date('Y-m-d'),
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'doc_type' => 'ADJ',
                'doc_number' => $docNum,
                'mutation_type' => $mutationType,
                'quantity' => $qty,
                'balance_after' => $balanceAfter,
                'notes' => "[Penyesuaian Fisik " . ($type === 'PLUS' ? '+' : '-') . "] {$notes}",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Catat ke Audit Log jika tabel ada
            if (\Illuminate\Support\Facades\Schema::hasTable('activity_logs')) {
                $wh = DB::table('warehouses')->where('id', $warehouseId)->first();
                $prod = DB::table('products')->where('id', $productId)->first();
                $authUser = auth()->user();

                DB::table('activity_logs')->insert([
                    'user_id' => $authUser ? $authUser->id : 1,
                    'user_name' => $authUser ? $authUser->name : 'Supervisor Logistik / Admin',
                    'user_role' => $authUser && $authUser->role ? $authUser->role->name : 'Warehouse Supervisor',
                    'action' => $type === 'PLUS' ? 'STOCK_ADJUSTMENT_ADD' : 'STOCK_ADJUSTMENT_DEDUCT',
                    'module' => 'INVENTORY',
                    'doc_number' => $docNum,
                    'severity' => $type === 'MINUS' ? 'WARNING' : 'INFO',
                    'description' => "Penyesuaian stok ({$type} {$qty} pcs) untuk '{$prod->name}' di {$wh->name}. Saldo akhir: {$balanceAfter} pcs. Alasan: {$notes}",
                    'ip_address' => $request->ip() ?? '127.0.0.1',
                    'user_agent' => $request->userAgent() ?? 'System Desktop',
                    'created_at' => now(),
                ]);
            }

            DB::commit();
            return redirect()->route('inventory.adjustment')->with('success', "Penyesuaian stok fisik berhasil disimpan! Dokumen: {$docNum}. Saldo akhir: {$balanceAfter} pcs.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', "Gagal menyimpan penyesuaian: " . $e->getMessage());
        }
    }

    /**
     * Halaman Dedicated Inspeksi Detail Nomor Serial ICCID per Produk & Gudang
     */
    public function iccids(Request $request)
    {
        $warehouseId = $request->get('warehouse_id');
        $productId = $request->get('product_id');
        $status = $request->get('status', 'ALL');
        $search = $request->get('q');

        $query = DB::table('iccid_units')
            ->join('products', 'iccid_units.product_id', '=', 'products.id')
            ->leftJoin('warehouses', 'iccid_units.warehouse_id', '=', 'warehouses.id')
            ->leftJoin('taps', 'warehouses.tap_id', '=', 'taps.id')
            ->leftJoin('customers', 'iccid_units.customer_id', '=', 'customers.id')
            ->select(
                'iccid_units.*',
                'products.name as product_name',
                'products.sku',
                'products.type as product_type',
                'products.tracking_method',
                'warehouses.name as warehouse_name',
                'warehouses.code as warehouse_code',
                'warehouses.type as warehouse_type',
                'taps.name as tap_name',
                'customers.name as customer_name'
            );

        if ($warehouseId && $warehouseId !== 'ALL') {
            $query->where('iccid_units.warehouse_id', $warehouseId);
        }
        if ($productId && $productId !== 'ALL') {
            $query->where('iccid_units.product_id', $productId);
        }
        if ($status && $status !== 'ALL') {
            $query->where('iccid_units.status', $status);
        }
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('iccid_units.iccid_number', 'like', "%{$search}%")
                  ->orWhere('iccid_units.last_doc_number', 'like', "%{$search}%");
            });
        }

        $iccids = $query->orderByDesc('iccid_units.id')->paginate(50)->withQueryString();

        // Info Konteks Produk & Gudang jika difilter
        $currentWarehouse = null;
        $currentProduct = null;
        $stockBalance = null;
        $serialRange = null;

        if ($warehouseId && $warehouseId !== 'ALL') {
            $currentWarehouse = DB::table('warehouses')
                ->join('taps', 'warehouses.tap_id', '=', 'taps.id')
                ->where('warehouses.id', $warehouseId)
                ->select('warehouses.*', 'taps.name as tap_name')
                ->first();
        }

        if ($productId && $productId !== 'ALL') {
            $currentProduct = DB::table('products')->where('id', $productId)->first();
        }

        if ($currentWarehouse && $currentProduct) {
            $stockBalance = DB::table('stock_balances')
                ->where('warehouse_id', $warehouseId)
                ->where('product_id', $productId)
                ->first();

            $minSerial = DB::table('iccid_units')
                ->where('warehouse_id', $warehouseId)
                ->where('product_id', $productId)
                ->min('iccid_number');

            $maxSerial = DB::table('iccid_units')
                ->where('warehouse_id', $warehouseId)
                ->where('product_id', $productId)
                ->max('iccid_number');

            if ($minSerial && $maxSerial) {
                $serialRange = (object) [
                    'min' => $minSerial,
                    'max' => $maxSerial
                ];
            }
        }

        // Master Data untuk Filter Dropdown
        $warehouses = DB::table('warehouses')
            ->where('status', 'ACTIVE')
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $products = DB::table('products')
            ->where('tracking_method', 'ICCID')
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get();

        // KPI Ringkasan
        $kpiBaseQuery = DB::table('iccid_units');
        if ($warehouseId && $warehouseId !== 'ALL') {
            $kpiBaseQuery->where('warehouse_id', $warehouseId);
        }
        if ($productId && $productId !== 'ALL') {
            $kpiBaseQuery->where('product_id', $productId);
        }

        $kpi = [
            'total_units' => (clone $kpiBaseQuery)->count(),
            'available' => (clone $kpiBaseQuery)->where('status', 'AVAILABLE')->count(),
            'reserved' => (clone $kpiBaseQuery)->where('status', 'RESERVED')->count(),
            'sold' => (clone $kpiBaseQuery)->where('status', 'SOLD')->count(),
        ];

        return view('inventory.iccids', compact(
            'iccids',
            'warehouses',
            'products',
            'currentWarehouse',
            'currentProduct',
            'stockBalance',
            'serialRange',
            'kpi',
            'warehouseId',
            'productId',
            'status',
            'search'
        ));
    }
}
