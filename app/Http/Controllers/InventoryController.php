<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index()
    {
        // 1. Stock Balances grouped by Warehouse with product details and tap info
        $stockBalances = DB::table('stock_balances')
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
        $iccids = DB::table('iccid_units')
            ->join('products', 'iccid_units.product_id', '=', 'products.id')
            ->leftJoin('warehouses', 'iccid_units.warehouse_id', '=', 'warehouses.id')
            ->select('iccid_units.*', 'products.name as product_name', 'products.sku', 'warehouses.name as warehouse_name')
            ->orderBy('iccid_units.id', 'desc')
            ->take(100)
            ->get();

        // 3. Stock Movements (SM)
        $movements = DB::table('stock_movements')
            ->join('warehouses as sw', 'stock_movements.source_warehouse_id', '=', 'sw.id')
            ->join('warehouses as dw', 'stock_movements.destination_warehouse_id', '=', 'dw.id')
            ->select('stock_movements.*', 'sw.name as source_wh', 'dw.name as dest_wh')
            ->orderBy('stock_movements.created_at', 'desc')
            ->get();

        // 4. Stock Mutations Ledger (Buku Kartu Stok In - Out)
        $mutations = DB::table('stock_mutations')
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
        $warehouses = DB::table('warehouses')
            ->join('taps', 'warehouses.tap_id', '=', 'taps.id')
            ->where('warehouses.status', 'ACTIVE')
            ->select('warehouses.*', 'taps.name as tap_name')
            ->orderBy('warehouses.type')
            ->orderBy('warehouses.name')
            ->get();

        $taps = DB::table('taps')->where('status', 'ACTIVE')->orderBy('name')->get();

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
}
