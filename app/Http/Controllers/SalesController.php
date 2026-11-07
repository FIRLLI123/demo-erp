<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesController extends Controller
{
    public function index()
    {
        $orders = DB::table('sales_orders')
            ->join('customers', 'sales_orders.customer_id', '=', 'customers.id')
            ->join('sales', 'sales_orders.sales_id', '=', 'sales.id')
            ->join('taps', 'sales_orders.tap_id', '=', 'taps.id')
            ->join('warehouses', 'sales_orders.warehouse_id', '=', 'warehouses.id')
            ->select(
                'sales_orders.*',
                'customers.name as customer_name',
                'sales.name as sales_name',
                'taps.name as tap_name',
                'warehouses.name as warehouse_name'
            )
            ->orderBy('sales_orders.created_at', 'desc')
            ->get();

        $deliveries = DB::table('goods_deliveries')
            ->join('customers', 'goods_deliveries.customer_id', '=', 'customers.id')
            ->join('warehouses', 'goods_deliveries.warehouse_id', '=', 'warehouses.id')
            ->select('goods_deliveries.*', 'customers.name as customer_name', 'warehouses.name as warehouse_name')
            ->orderBy('goods_deliveries.created_at', 'desc')
            ->get();

        return view('sales.index', compact('orders', 'deliveries'));
    }

    public function approve(Request $request, $id)
    {
        DB::table('sales_orders')->where('id', $id)->update([
            'status' => 'APPROVED',
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Sales Order disetujui oleh Finance! Siap untuk pengiriman gudang (GD).');
    }

    public function deliver(Request $request, $id)
    {
        $so = DB::table('sales_orders')->where('id', $id)->first();
        if (!$so) return redirect()->back();

        $nextGd = DB::table('goods_deliveries')->count() + 1;
        $gdNum = 'GD/' . date('Ym') . '/' . str_pad($nextGd, 4, '0', STR_PAD_LEFT);

        // 1. Create Goods Delivery (GD)
        DB::table('goods_deliveries')->insert([
            'doc_type' => 'GD',
            'doc_number' => $gdNum,
            'ref_doc_type' => 'SO',
            'ref_doc_number' => $so->doc_number,
            'sales_order_id' => $so->id,
            'warehouse_id' => $so->warehouse_id,
            'customer_id' => $so->customer_id,
            'delivery_date' => date('Y-m-d'),
            'recipient_name' => 'Penerima Toko (Sesuai Nota)',
            'status' => 'DELIVERED',
            'notes' => 'Pengiriman barang pesanan selesai',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Generate Account Receivable (AR)
        $nextAr = DB::table('accounts_receivable')->count() + 1;
        $arNum = 'AR/' . date('Ym') . '/' . str_pad($nextAr, 4, '0', STR_PAD_LEFT);

        DB::table('accounts_receivable')->insert([
            'doc_type' => 'AR',
            'doc_number' => $arNum,
            'ref_doc_type' => 'SO',
            'ref_doc_number' => $so->doc_number,
            'sales_order_id' => $so->id,
            'customer_id' => $so->customer_id,
            'invoice_date' => date('Y-m-d'),
            'due_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'total_amount' => $so->grand_total,
            'paid_amount' => 0,
            'balance_amount' => $so->grand_total,
            'status' => 'UNPAID',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('sales_orders')->where('id', $id)->update([
            'status' => 'DELIVERED',
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', "Surat Jalan [{$gdNum}] dan Faktur Piutang [{$arNum}] otomatis terbentuk!");
    }
}

