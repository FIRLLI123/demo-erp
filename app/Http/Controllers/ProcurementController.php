<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProcurementController extends Controller
{
    public function index()
    {
        $orders = DB::table('purchase_orders')
            ->join('taps', 'purchase_orders.tap_id', '=', 'taps.id')
            ->select('purchase_orders.*', 'taps.name as tap_name')
            ->orderBy('purchase_orders.created_at', 'desc')
            ->get();

        $receipts = DB::table('goods_receipts')
            ->join('taps', 'goods_receipts.tap_id', '=', 'taps.id')
            ->join('warehouses', 'goods_receipts.warehouse_id', '=', 'warehouses.id')
            ->select('goods_receipts.*', 'taps.name as tap_name', 'warehouses.name as warehouse_name')
            ->orderBy('goods_receipts.created_at', 'desc')
            ->get();

        return view('procurement.index', compact('orders', 'receipts'));
    }

    public function createQuickPo(Request $request)
    {
        $tapId = DB::table('taps')->value('id');
        $mainWhId = DB::table('warehouses')->where('tap_id', $tapId)->where('type', 'MAIN')->value('id');
        $prodId = DB::table('products')->where('sku', 'SA-5GB-30D')->value('id');

        $nextNum = DB::table('purchase_orders')->count() + 1;
        $docNumber = 'PO/' . date('Ym') . '/' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        $poId = DB::table('purchase_orders')->insertGetId([
            'doc_type' => 'PO',
            'doc_number' => $docNumber,
            'tap_id' => $tapId,
            'supplier_name' => 'PT TELKOMSEL LOGISTIK PUSAT',
            'order_date' => date('Y-m-d'),
            'total_amount' => 11000000,
            'status' => 'ORDERED',
            'notes' => 'PO Batch Demo Otomatis',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('purchase_order_items')->insert([
            'purchase_order_id' => $poId,
            'product_id' => $prodId,
            'quantity' => 500,
            'cost_price' => 22000,
            'subtotal' => 11000000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', "PO Baru [{$docNumber}] berhasil dibuat!");
    }

    public function confirmGr(Request $request, $id)
    {
        $po = DB::table('purchase_orders')->where('id', $id)->first();
        if (!$po) return redirect()->back();

        $mainWhId = DB::table('warehouses')->where('tap_id', $po->tap_id)->where('type', 'MAIN')->value('id');
        $nextGrNum = DB::table('goods_receipts')->count() + 1;
        $grNumber = 'GR/' . date('Ym') . '/' . str_pad($nextGrNum, 4, '0', STR_PAD_LEFT);

        DB::table('goods_receipts')->insert([
            'doc_type' => 'GR',
            'doc_number' => $grNumber,
            'ref_doc_type' => 'PO',
            'ref_doc_number' => $po->doc_number,
            'purchase_order_id' => $po->id,
            'tap_id' => $po->tap_id,
            'warehouse_id' => $mainWhId,
            'received_date' => date('Y-m-d'),
            'status' => 'CONFIRMED',
            'notes' => 'Penerimaan fisik barang lengkap untuk demo',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('purchase_orders')->where('id', $id)->update(['status' => 'RECEIVED', 'updated_at' => now()]);

        return redirect()->back()->with('success', "Barang diterima! Dokumen {$grNumber} terbentuk dan stok MAIN bertambah.");
    }
}

