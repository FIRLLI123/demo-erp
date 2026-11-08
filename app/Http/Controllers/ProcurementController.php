<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProcurementController extends Controller
{
    public function index()
    {
        // 1. Fetch Purchase Orders with their items
        $ordersRaw = DB::table('purchase_orders')
            ->join('taps', 'purchase_orders.tap_id', '=', 'taps.id')
            ->select('purchase_orders.*', 'taps.name as tap_name')
            ->orderBy('purchase_orders.id', 'desc')
            ->get();

        $poIds = $ordersRaw->pluck('id')->toArray();

        $itemsByPo = [];
        if (!empty($poIds)) {
            $items = DB::table('purchase_order_items')
                ->join('products', 'purchase_order_items.product_id', '=', 'products.id')
                ->whereIn('purchase_order_items.purchase_order_id', $poIds)
                ->select(
                    'purchase_order_items.*',
                    'products.name as product_name',
                    'products.sku',
                    'products.type as product_type',
                    'products.tracking_method'
                )
                ->get();

            foreach ($items as $item) {
                $itemsByPo[$item->purchase_order_id][] = $item;
            }
        }

        // Attach items array and total item count to each PO
        $orders = $ordersRaw->map(function ($po) use ($itemsByPo) {
            $po->items = $itemsByPo[$po->id] ?? [];
            $po->total_qty = collect($po->items)->sum('quantity');
            $po->has_iccid = collect($po->items)->contains('tracking_method', 'ICCID');
            return $po;
        });

        // 2. Fetch Goods Receipts with items
        $receipts = DB::table('goods_receipts')
            ->join('taps', 'goods_receipts.tap_id', '=', 'taps.id')
            ->join('warehouses', 'goods_receipts.warehouse_id', '=', 'warehouses.id')
            ->select('goods_receipts.*', 'taps.name as tap_name', 'warehouses.name as warehouse_name')
            ->orderBy('goods_receipts.id', 'desc')
            ->get();

        // 3. Master data for creating new PO
        $products = DB::table('products')->where('status', 'ACTIVE')->get();
        $taps = DB::table('taps')->where('status', 'ACTIVE')->get();

        return view('procurement.index', compact('orders', 'receipts', 'products', 'taps'));
    }

    public function storePo(Request $request)
    {
        $tapId = $request->input('tap_id');
        $supplierName = $request->input('supplier_name', 'PT TELKOMSEL LOGISTIK PUSAT');
        $orderDate = $request->input('order_date', date('Y-m-d'));
        $notes = $request->input('notes', 'PO Pengadaan Rutin');
        $itemsData = $request->input('items', []);

        if (empty($itemsData)) {
            return redirect()->back()->with('error', 'Pilih minimal 1 item produk untuk PO!');
        }

        DB::beginTransaction();
        try {
            $nextNum = DB::table('purchase_orders')->count() + 1;
            $docNumber = 'PO/' . date('Ym') . '/' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

            $totalAmount = 0;
            $preparedItems = [];

            foreach ($itemsData as $row) {
                if (!isset($row['product_id']) || empty($row['quantity'])) continue;

                $prod = DB::table('products')->where('id', $row['product_id'])->first();
                if (!$prod) continue;

                $qty = max(1, (int)$row['quantity']);
                $costPrice = (float)($row['cost_price'] ?? $prod->cost_price);
                $subtotal = $qty * $costPrice;
                $totalAmount += $subtotal;

                $preparedItems[] = [
                    'product_id' => $prod->id,
                    'quantity' => $qty,
                    'cost_price' => $costPrice,
                    'subtotal' => $subtotal,
                ];
            }

            if (empty($preparedItems)) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Item produk tidak valid!');
            }

            $poId = DB::table('purchase_orders')->insertGetId([
                'doc_type' => 'PO',
                'doc_number' => $docNumber,
                'tap_id' => $tapId,
                'supplier_name' => $supplierName,
                'order_date' => $orderDate,
                'total_amount' => $totalAmount,
                'status' => 'ORDERED',
                'notes' => $notes,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($preparedItems as $item) {
                DB::table('purchase_order_items')->insert(array_merge($item, [
                    'purchase_order_id' => $poId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }

            DB::commit();
            return redirect()->back()->with('success', "PO Baru [{$docNumber}] berhasil diterbitkan dengan " . count($preparedItems) . " item barang!");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membuat PO: ' . $e->getMessage());
        }
    }

    public function processGr(Request $request, $id)
    {
        $po = DB::table('purchase_orders')->where('id', $id)->first();
        if (!$po) {
            return redirect()->back()->with('error', 'Data PO tidak ditemukan!');
        }

        if ($po->status === 'RECEIVED') {
            return redirect()->back()->with('error', 'PO ini sudah pernah diterima (RECEIVED)!');
        }

        $mainWh = DB::table('warehouses')
            ->where('tap_id', $po->tap_id)
            ->where('type', 'MAIN')
            ->first();

        if (!$mainWh) {
            $mainWh = DB::table('warehouses')->where('type', 'MAIN')->first();
        }

        $poItems = DB::table('purchase_order_items')
            ->join('products', 'purchase_order_items.product_id', '=', 'products.id')
            ->where('purchase_order_items.purchase_order_id', $po->id)
            ->select('purchase_order_items.*', 'products.tracking_method', 'products.name as product_name')
            ->get();

        DB::beginTransaction();
        try {
            $nextGrNum = DB::table('goods_receipts')->count() + 1;
            $grNumber = 'GR/' . date('Ym') . '/' . str_pad($nextGrNum, 4, '0', STR_PAD_LEFT);

            // 1. Create Goods Receipt Header
            $grId = DB::table('goods_receipts')->insertGetId([
                'doc_type' => 'GR',
                'doc_number' => $grNumber,
                'ref_doc_type' => 'PO',
                'ref_doc_number' => $po->doc_number,
                'purchase_order_id' => $po->id,
                'tap_id' => $po->tap_id,
                'warehouse_id' => $mainWh->id,
                'received_date' => date('Y-m-d'),
                'status' => 'CONFIRMED',
                'notes' => $request->input('notes', 'Penerimaan fisik barang lengkap ke Gudang MAIN'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Process each item (Stock Balances & ICCID Generation/Insertion)
            $totalIccidCreated = 0;

            foreach ($poItems as $item) {
                // Insert GR item detail
                DB::table('goods_receipt_items')->insert([
                    'goods_receipt_id' => $grId,
                    'product_id' => $item->product_id,
                    'quantity_received' => $item->quantity,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($item->tracking_method === 'ICCID') {
                    // Collect ICCIDs from input: either auto-range or pasted list
                    $customIccids = [];
                    $pastedText = $request->input("iccids_{$item->product_id}_paste");
                    $rangeStart = $request->input("iccids_{$item->product_id}_range_start");

                    if (!empty($pastedText)) {
                        $lines = preg_split('/[\r\n,]+/', trim($pastedText));
                        foreach ($lines as $line) {
                            $clean = trim($line);
                            if (!empty($clean)) $customIccids[] = $clean;
                        }
                    }

                    // If insufficient or using range start, generate sequentially
                    $needed = $item->quantity;
                    if (count($customIccids) < $needed) {
                        $basePrefix = !empty($rangeStart) && is_numeric($rangeStart) 
                            ? (string)$rangeStart 
                            : '89621000' . date('ymd') . str_pad($item->product_id, 2, '0', STR_PAD_LEFT);
                        
                        $startNumber = (int)substr($basePrefix, -5);
                        $prefixPart = substr($basePrefix, 0, -5);

                        while (count($customIccids) < $needed) {
                            $generated = $prefixPart . str_pad($startNumber, 5, '0', STR_PAD_LEFT);
                            $startNumber++;
                            $customIccids[] = $generated;
                        }
                    }

                    // Insert ICCID units into database
                    foreach (array_slice($customIccids, 0, $needed) as $iccidNum) {
                        DB::table('iccid_units')->updateOrInsert(
                            ['iccid_number' => $iccidNum],
                            [
                                'product_id' => $item->product_id,
                                'warehouse_id' => $mainWh->id,
                                'status' => 'AVAILABLE',
                                'customer_id' => null,
                                'last_doc_type' => 'GR',
                                'last_doc_number' => $grNumber,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        );
                        $totalIccidCreated++;
                    }
                } else {
                    // Quantity-based product: update stock_balances in MAIN Warehouse
                    $existing = DB::table('stock_balances')
                        ->where('warehouse_id', $mainWh->id)
                        ->where('product_id', $item->product_id)
                        ->first();

                    if ($existing) {
                        DB::table('stock_balances')
                            ->where('id', $existing->id)
                            ->increment('quantity', $item->quantity);
                    } else {
                        DB::table('stock_balances')->insert([
                            'warehouse_id' => $mainWh->id,
                            'product_id' => $item->product_id,
                            'quantity' => $item->quantity,
                            'reserved_quantity' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // 3. Update PO status to RECEIVED
            DB::table('purchase_orders')->where('id', $id)->update([
                'status' => 'RECEIVED',
                'updated_at' => now(),
            ]);

            DB::commit();
            $msg = "Dokumen [{$grNumber}] terbit! Stok resmi masuk ke {$mainWh->name}";
            if ($totalIccidCreated > 0) {
                $msg .= " ({$totalIccidCreated} serial ICCID terdaftar).";
            }
            return redirect()->back()->with('success', $msg);
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses GR: ' . $e->getMessage());
        }
    }
}
