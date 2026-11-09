<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProcurementController extends Controller
{
    public function index()
    {
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

        $orders = $ordersRaw->map(function ($po) use ($itemsByPo) {
            $po->items = $itemsByPo[$po->id] ?? [];
            $po->total_qty = collect($po->items)->sum('quantity');
            $po->has_iccid = collect($po->items)->contains('tracking_method', 'ICCID');
            return $po;
        });

        $receipts = DB::table('goods_receipts')
            ->join('taps', 'goods_receipts.tap_id', '=', 'taps.id')
            ->join('warehouses', 'goods_receipts.warehouse_id', '=', 'warehouses.id')
            ->select('goods_receipts.*', 'taps.name as tap_name', 'warehouses.name as warehouse_name')
            ->orderBy('goods_receipts.id', 'desc')
            ->get();

        $products = DB::table('products')->where('status', 'ACTIVE')->get();
        $taps = DB::table('taps')->where('status', 'ACTIVE')->get();

        return view('procurement.index', compact('orders', 'receipts', 'products', 'taps'));
    }

    public function storePo(Request $request)
    {
        $tapId = $request->input('tap_id');
        $supplierName = $request->input('supplier_name', 'PT TELKOMSEL DISTRIBUSI PUSAT');
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
                $costPrice = (float)$prod->cost_price; // Harga modal terkunci resmi dari Master Produk
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
            return redirect()->route('procurement.index')->with('success', "PO Baru [{$docNumber}] berhasil diterbitkan!");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membuat PO: ' . $e->getMessage());
        }
    }

    /**
     * Dedicated Screen: Form Penerimaan Barang (GR)
     */
    public function createGr($id)
    {
        $po = DB::table('purchase_orders')
            ->join('taps', 'purchase_orders.tap_id', '=', 'taps.id')
            ->where('purchase_orders.id', $id)
            ->select('purchase_orders.*', 'taps.name as tap_name')
            ->first();

        if (!$po) {
            return redirect()->route('procurement.index')->with('error', 'PO tidak ditemukan!');
        }

        if ($po->status === 'RECEIVED') {
            return redirect()->route('procurement.index')->with('error', 'PO ini sudah pernah diterima lengkap (RECEIVED)!');
        }

        $items = DB::table('purchase_order_items')
            ->join('products', 'purchase_order_items.product_id', '=', 'products.id')
            ->where('purchase_order_items.purchase_order_id', $po->id)
            ->select(
                'purchase_order_items.*',
                'products.name as product_name',
                'products.sku',
                'products.type as product_type',
                'products.tracking_method'
            )
            ->get();

        $mainWh = DB::table('warehouses')
            ->where('tap_id', $po->tap_id)
            ->where('type', 'MAIN')
            ->first() ?? DB::table('warehouses')->where('type', 'MAIN')->first();

        $nextGrNum = DB::table('goods_receipts')->count() + 1;
        $predictedGrNumber = 'GR/' . date('Ym') . '/' . str_pad($nextGrNum, 4, '0', STR_PAD_LEFT);

        return view('procurement.gr-form', compact('po', 'items', 'mainWh', 'predictedGrNumber'));
    }

    /**
     * Download template CSV for ICCID upload
     */
    public function downloadIccidTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_upload_iccid.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['iccid']);
            fputcsv($handle, ['8962100026110800001']);
            fputcsv($handle, ['8962100026110800002']);
            fputcsv($handle, ['8962100026110800003']);
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function processGr(Request $request, $id)
    {
        $po = DB::table('purchase_orders')->where('id', $id)->first();
        if (!$po) {
            return redirect()->route('procurement.index')->with('error', 'Data PO tidak ditemukan!');
        }

        if ($po->status === 'RECEIVED') {
            return redirect()->route('procurement.index')->with('error', 'PO ini sudah pernah diterima (RECEIVED)!');
        }

        $mainWh = DB::table('warehouses')
            ->where('tap_id', $po->tap_id)
            ->where('type', 'MAIN')
            ->first() ?? DB::table('warehouses')->where('type', 'MAIN')->first();

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
                'received_date' => $request->input('received_date', date('Y-m-d')),
                'status' => 'CONFIRMED',
                'notes' => $request->input('notes', 'Penerimaan fisik barang lengkap ke Gudang MAIN'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

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
                    $customIccids = [];

                    // Check file upload first (CSV)
                    if ($request->hasFile("iccids_{$item->product_id}_file")) {
                        $file = $request->file("iccids_{$item->product_id}_file");
                        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
                            $header = fgetcsv($handle);
                            while (($data = fgetcsv($handle)) !== false) {
                                if (!empty($data[0])) {
                                    $customIccids[] = trim($data[0]);
                                }
                            }
                            fclose($handle);
                        }
                    }

                    // Check pasted textarea
                    $pastedText = $request->input("iccids_{$item->product_id}_paste");
                    if (!empty($pastedText)) {
                        $lines = preg_split('/[\r\n,]+/', trim($pastedText));
                        foreach ($lines as $line) {
                            $clean = trim($line);
                            if (!empty($clean)) $customIccids[] = $clean;
                        }
                    }

                    // Fallback to auto-range start
                    $needed = $item->quantity;
                    if (count($customIccids) < $needed) {
                        $rangeStart = $request->input("iccids_{$item->product_id}_range_start");
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

                    // Update stock_balances for ICCID-based products in MAIN Warehouse
                    $existingSb = DB::table('stock_balances')
                        ->where('warehouse_id', $mainWh->id)
                        ->where('product_id', $item->product_id)
                        ->first();

                    if ($existingSb) {
                        DB::table('stock_balances')
                            ->where('id', $existingSb->id)
                            ->update([
                                'quantity' => $existingSb->quantity + $needed,
                                'updated_at' => now(),
                            ]);
                    } else {
                        DB::table('stock_balances')->insert([
                            'warehouse_id' => $mainWh->id,
                            'product_id' => $item->product_id,
                            'quantity' => $needed,
                            'reserved_quantity' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    // Record in Stock Mutation Ledger
                    DB::table('stock_mutations')->insert([
                        'date' => date('Y-m-d'),
                        'warehouse_id' => $mainWh->id,
                        'product_id' => $item->product_id,
                        'doc_type' => 'GR',
                        'doc_number' => $grNumber,
                        'mutation_type' => 'IN',
                        'quantity' => $needed,
                        'balance_after' => DB::table('iccid_units')->where('warehouse_id', $mainWh->id)->where('product_id', $item->product_id)->where('status', 'AVAILABLE')->count(),
                        'notes' => "Penerimaan fisik serial ICCID (Ref: {$po->doc_number})",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    // Quantity-based product: update stock_balances in MAIN Warehouse
                    $existing = DB::table('stock_balances')
                        ->where('warehouse_id', $mainWh->id)
                        ->where('product_id', $item->product_id)
                        ->first();

                    if ($existing) {
                        $newQty = $existing->quantity + $item->quantity;
                        DB::table('stock_balances')
                            ->where('id', $existing->id)
                            ->update(['quantity' => $newQty, 'updated_at' => now()]);
                    } else {
                        $newQty = $item->quantity;
                        DB::table('stock_balances')->insert([
                            'warehouse_id' => $mainWh->id,
                            'product_id' => $item->product_id,
                            'quantity' => $item->quantity,
                            'reserved_quantity' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    // Record in Stock Mutation Ledger
                    DB::table('stock_mutations')->insert([
                        'date' => date('Y-m-d'),
                        'warehouse_id' => $mainWh->id,
                        'product_id' => $item->product_id,
                        'doc_type' => 'GR',
                        'doc_number' => $grNumber,
                        'mutation_type' => 'IN',
                        'quantity' => $item->quantity,
                        'balance_after' => $newQty,
                        'notes' => "Penerimaan fisik barang non-serial (Ref: {$po->doc_number})",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // 3. Update PO status to RECEIVED
            DB::table('purchase_orders')->where('id', $id)->update([
                'status' => 'RECEIVED',
                'updated_at' => now(),
            ]);

            DB::commit();
            return redirect()->route('procurement.index')->with('success', "Dokumen [{$grNumber}] terbit! Stok resmi tercatat di {$mainWh->name} & buku mutasi diperbarui.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses GR: ' . $e->getMessage());
        }
    }
}
