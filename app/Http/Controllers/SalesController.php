<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesController extends Controller
{
    public function index()
    {
        // 1. Ambil daftar Sales Orders (Header)
        $ordersRaw = DB::table('sales_orders')
            ->join('customers', 'sales_orders.customer_id', '=', 'customers.id')
            ->join('sales', 'sales_orders.sales_id', '=', 'sales.id')
            ->join('taps', 'sales_orders.tap_id', '=', 'taps.id')
            ->join('warehouses', 'sales_orders.warehouse_id', '=', 'warehouses.id')
            ->select(
                'sales_orders.*',
                'customers.name as customer_name',
                'customers.code as customer_code',
                'customers.type as customer_type',
                'sales.name as sales_name',
                'taps.name as tap_name',
                'warehouses.name as warehouse_name',
                'warehouses.type as warehouse_type'
            )
            ->orderBy('sales_orders.id', 'desc')
            ->get();

        $soIds = $ordersRaw->pluck('id')->toArray();
        $itemsBySo = [];
        if (!empty($soIds)) {
            $items = DB::table('sales_order_items')
                ->join('products', 'sales_order_items.product_id', '=', 'products.id')
                ->whereIn('sales_order_items.sales_order_id', $soIds)
                ->select(
                    'sales_order_items.*',
                    'products.name as product_name',
                    'products.sku',
                    'products.type as product_type',
                    'products.tracking_method'
                )
                ->get();

            foreach ($items as $it) {
                $itemsBySo[$it->sales_order_id][] = $it;
            }
        }

        // Ambil data GD terkait
        $deliveriesBySo = [];
        $gdList = DB::table('goods_deliveries')->whereIn('sales_order_id', $soIds)->get();
        foreach ($gdList as $gd) {
            $deliveriesBySo[$gd->sales_order_id] = $gd;
        }

        // Ambil data AR terkait
        $invoicesBySo = [];
        $arList = DB::table('accounts_receivable')->whereIn('sales_order_id', $soIds)->get();
        foreach ($arList as $ar) {
            $invoicesBySo[$ar->sales_order_id] = $ar;
        }

        // Ambil ICCID terkait SO
        $soDocNumbers = $ordersRaw->pluck('doc_number')->toArray();
        $gdDocNumbers = $gdList->pluck('doc_number')->toArray();
        $allDocRefs = array_unique(array_merge($soDocNumbers, $gdDocNumbers));

        $iccidsByDoc = [];
        if (!empty($allDocRefs)) {
            $iccids = DB::table('iccid_units')
                ->whereIn('last_doc_number', $allDocRefs)
                ->select('iccid_number', 'product_id', 'last_doc_number', 'status')
                ->get();

            foreach ($iccids as $u) {
                $iccidsByDoc[$u->last_doc_number][] = $u->iccid_number;
            }
        }

        $orders = $ordersRaw->map(function ($so) use ($itemsBySo, $deliveriesBySo, $invoicesBySo, $iccidsByDoc) {
            $so->items = $itemsBySo[$so->id] ?? [];
            $so->delivery = $deliveriesBySo[$so->id] ?? null;
            $so->invoice = $invoicesBySo[$so->id] ?? null;
            
            // ICCID list can come from SO (if reserved) or GD (if delivered)
            $iccids = $iccidsByDoc[$so->doc_number] ?? [];
            if (empty($iccids) && $so->delivery) {
                $iccids = $iccidsByDoc[$so->delivery->doc_number] ?? [];
            }
            $so->iccids = $iccids;
            $so->total_qty = collect($so->items)->sum('quantity');
            return $so;
        });

        // 2. Daftar Surat Jalan (GD)
        $deliveries = DB::table('goods_deliveries')
            ->join('customers', 'goods_deliveries.customer_id', '=', 'customers.id')
            ->join('warehouses', 'goods_deliveries.warehouse_id', '=', 'warehouses.id')
            ->select('goods_deliveries.*', 'customers.name as customer_name', 'warehouses.name as warehouse_name')
            ->orderBy('goods_deliveries.id', 'desc')
            ->get();

        // 3. Daftar Retur (SR)
        $returns = DB::table('sales_returns')
            ->join('customers', 'sales_returns.customer_id', '=', 'customers.id')
            ->join('warehouses', 'sales_returns.warehouse_id', '=', 'warehouses.id')
            ->select('sales_returns.*', 'customers.name as customer_name', 'warehouses.name as warehouse_name')
            ->orderBy('sales_returns.id', 'desc')
            ->get();

        // 4. Master Data Pendukung Pembuatan SO
        $customers = DB::table('customers')->where('status', 'ACTIVE')->orderBy('name')->get();
        $salesList = DB::table('sales')->where('status', 'ACTIVE')->orderBy('name')->get();
        
        // Gudang untuk penjualan: SALES (Kanvas) dan INVENTORY (Stok TAP)
        $warehouses = DB::table('warehouses')
            ->where('status', 'ACTIVE')
            ->whereIn('type', ['SALES', 'INVENTORY'])
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $products = DB::table('products')->where('status', 'ACTIVE')->orderBy('name')->get();
        $stockBalances = DB::table('stock_balances')->get();
        $availableIccids = DB::table('iccid_units')
            ->where('status', 'AVAILABLE')
            ->select('id', 'iccid_number', 'warehouse_id', 'product_id')
            ->get();

        return view('sales.index', compact(
            'orders',
            'deliveries',
            'returns',
            'customers',
            'salesList',
            'warehouses',
            'products',
            'stockBalances',
            'availableIccids'
        ));
    }

    public function store(Request $request)
    {
        $customerId = (int)$request->input('customer_id');
        $salesId = (int)$request->input('sales_id');
        $warehouseId = (int)$request->input('warehouse_id');
        $orderDate = $request->input('order_date', date('Y-m-d'));
        $notes = $request->input('notes', 'Pesanan Penjualan (SO)');
        $itemsData = $request->input('items', []);

        if (!$customerId || !$salesId || !$warehouseId) {
            return redirect()->back()->with('error', 'Lengkapi pilihan Customer, Sales, dan Gudang Sumber!');
        }

        if (empty($itemsData)) {
            return redirect()->back()->with('error', 'Pilih minimal 1 item produk untuk dipesan!');
        }

        $customer = DB::table('customers')->where('id', $customerId)->first();
        $sales = DB::table('sales')->where('id', $salesId)->first();
        $warehouse = DB::table('warehouses')->where('id', $warehouseId)->first();

        DB::beginTransaction();
        try {
            $nextNum = DB::table('sales_orders')->count() + 1;
            $docNumber = 'SO/' . date('Ym') . '/' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

            $grandTotal = 0;
            $preparedItems = [];
            $allReservedIccids = [];

            foreach ($itemsData as $idx => $row) {
                if (empty($row['product_id'])) continue;

                $productId = (int)$row['product_id'];
                $prod = DB::table('products')->where('id', $productId)->first();
                if (!$prod) continue;

                // Cek stok tersedia di gudang sumber
                $currentStock = DB::table('stock_balances')
                    ->where('warehouse_id', $warehouseId)
                    ->where('product_id', $productId)
                    ->value('quantity') ?? 0;

                if ($prod->tracking_method === 'QUANTITY') {
                    $qty = max(1, (int)($row['quantity'] ?? 1));
                    if ($currentStock < $qty) {
                        throw new \Exception("Stok {$prod->name} di {$warehouse->name} tidak mencukupi (Tersedia: {$currentStock} pcs, Diminta: {$qty} pcs)!");
                    }
                } else {
                    // Tracking: ICCID
                    $inputMode = $row['iccid_mode'] ?? 'SELECT';
                    $targetIccids = [];

                    if ($inputMode === 'SELECT') {
                        $targetIccids = $row['selected_iccids'] ?? [];
                    } elseif ($inputMode === 'PASTE') {
                        $rawText = $row['iccid_text'] ?? '';
                        $lines = preg_split('/[\r\n,]+/', $rawText);
                        foreach ($lines as $line) {
                            $clean = trim($line);
                            if (!empty($clean)) $targetIccids[] = $clean;
                        }
                    } elseif ($inputMode === 'UPLOAD') {
                        if ($request->hasFile("items.{$idx}.iccid_file")) {
                            $file = $request->file("items.{$idx}.iccid_file");
                            $handle = fopen($file->getRealPath(), 'r');
                            $first = true;
                            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                                if ($first) { $first = false; continue; }
                                if (isset($data[0]) && trim($data[0]) !== '') $targetIccids[] = trim($data[0]);
                            }
                            fclose($handle);
                        }
                    }

                    $targetIccids = array_values(array_unique(array_filter($targetIccids)));
                    if (empty($targetIccids)) {
                        throw new \Exception("Harap tentukan/pilih nomor serial ICCID untuk produk {$prod->name}!");
                    }

                    $qty = count($targetIccids);
                    if ($currentStock < $qty) {
                        throw new \Exception("Stok {$prod->name} di {$warehouse->name} tidak mencukupi!");
                    }

                    // Validasi status ICCID
                    $validCount = DB::table('iccid_units')
                        ->where('warehouse_id', $warehouseId)
                        ->where('product_id', $productId)
                        ->where('status', 'AVAILABLE')
                        ->whereIn('iccid_number', $targetIccids)
                        ->count();

                    if ($validCount !== $qty) {
                        throw new \Exception("Sebagian nomor serial ICCID untuk {$prod->name} tidak valid atau sedang tidak AVAILABLE di {$warehouse->name}!");
                    }

                    $allReservedIccids = array_merge($allReservedIccids, $targetIccids);
                }

                $sellingPrice = (float)$prod->selling_price;
                $subtotal = $qty * $sellingPrice;
                $grandTotal += $subtotal;

                $preparedItems[] = [
                    'product_id' => $productId,
                    'quantity' => $qty,
                    'unit_price' => $sellingPrice,
                    'subtotal' => $subtotal,
                ];
            }

            if (empty($preparedItems)) {
                throw new \Exception('Tidak ada item valid yang dipesan!');
            }

            // Simpan Header SO
            $soId = DB::table('sales_orders')->insertGetId([
                'doc_type' => 'SO',
                'doc_number' => $docNumber,
                'tap_id' => $sales->tap_id,
                'warehouse_id' => $warehouseId,
                'sales_id' => $salesId,
                'customer_id' => $customerId,
                'order_date' => $orderDate,
                'channel' => 'WEB_ADMIN',
                'status' => 'PENDING_APPROVAL',
                'subtotal' => $grandTotal,
                'discount' => 0,
                'grand_total' => $grandTotal,
                'notes' => $notes,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Simpan Items SO
            foreach ($preparedItems as $item) {
                DB::table('sales_order_items')->insert([
                    'sales_order_id' => $soId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Kunci status serial ICCID menjadi RESERVED untuk SO ini
            if (!empty($allReservedIccids)) {
                DB::table('iccid_units')
                    ->whereIn('iccid_number', $allReservedIccids)
                    ->update([
                        'status' => 'RESERVED',
                        'last_doc_type' => 'SO',
                        'last_doc_number' => $docNumber,
                        'updated_at' => now(),
                    ]);
            }

            DB::commit();
            return redirect()->route('sales.index')->with('success', "Sales Order [{$docNumber}] senilai Rp " . number_format($grandTotal, 0, ',', '.') . " berhasil dibuat! Menunggu Approval Finance.");

        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membuat Sales Order: ' . $e->getMessage());
        }
    }

    public function approve(Request $request, $id)
    {
        $so = DB::table('sales_orders')->where('id', $id)->first();
        if (!$so) return redirect()->back()->with('error', 'Sales Order tidak ditemukan!');

        DB::table('sales_orders')->where('id', $id)->update([
            'status' => 'APPROVED',
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', "Sales Order [{$so->doc_number}] telah disetujui Finance! Gudang siap memproses pengiriman barang (Sending Good).");
    }

    public function reject(Request $request, $id)
    {
        $so = DB::table('sales_orders')->where('id', $id)->first();
        if (!$so) return redirect()->back()->with('error', 'Sales Order tidak ditemukan!');

        DB::beginTransaction();
        try {
            DB::table('sales_orders')->where('id', $id)->update([
                'status' => 'REJECTED',
                'updated_at' => now(),
            ]);

            // Lepas ICCID yang sempat di-reserve kembali jadi AVAILABLE
            DB::table('iccid_units')
                ->where('last_doc_type', 'SO')
                ->where('last_doc_number', $so->doc_number)
                ->where('status', 'RESERVED')
                ->update([
                    'status' => 'AVAILABLE',
                    'updated_at' => now(),
                ]);

            DB::commit();
            return redirect()->back()->with('success', "Sales Order [{$so->doc_number}] telah DITOLAK Finance. Stok dan serial ICCID telah dikembalikan.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menolak SO: ' . $e->getMessage());
        }
    }

    public function deliver(Request $request, $id)
    {
        $so = DB::table('sales_orders')->where('id', $id)->first();
        if (!$so) return redirect()->back()->with('error', 'SO tidak ditemukan!');

        if ($so->status !== 'APPROVED') {
            return redirect()->back()->with('error', 'Hanya SO yang sudah disetujui Finance (APPROVED) yang dapat dikirimkan!');
        }

        $soItems = DB::table('sales_order_items')->where('sales_order_id', $so->id)->get();
        $warehouse = DB::table('warehouses')->where('id', $so->warehouse_id)->first();
        $customer = DB::table('customers')->where('id', $so->customer_id)->first();

        DB::beginTransaction();
        try {
            // 1. Terbitkan Dokumen Good Delivery (GD)
            $nextGd = DB::table('goods_deliveries')->count() + 1;
            $gdNum = 'GD/' . date('Ym') . '/' . str_pad($nextGd, 4, '0', STR_PAD_LEFT);

            DB::table('goods_deliveries')->insert([
                'doc_type' => 'GD',
                'doc_number' => $gdNum,
                'ref_doc_type' => 'SO',
                'ref_doc_number' => $so->doc_number,
                'sales_order_id' => $so->id,
                'warehouse_id' => $so->warehouse_id,
                'customer_id' => $so->customer_id,
                'delivery_date' => date('Y-m-d'),
                'recipient_name' => $customer->name,
                'status' => 'DELIVERED',
                'notes' => 'Pengiriman fisik barang selesai kepada customer',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Terbitkan Dokumen Piutang / Invoice (AR)
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

            // 3. Potong Stok Gudang & Catat Kartu Stok (OUT)
            foreach ($soItems as $item) {
                $currStock = DB::table('stock_balances')
                    ->where('warehouse_id', $so->warehouse_id)
                    ->where('product_id', $item->product_id)
                    ->value('quantity') ?? 0;

                $newStock = max(0, $currStock - $item->quantity);
                DB::table('stock_balances')
                    ->where('warehouse_id', $so->warehouse_id)
                    ->where('product_id', $item->product_id)
                    ->update([
                        'quantity' => $newStock,
                        'updated_at' => now()
                    ]);

                // Mutasi OUT GD
                DB::table('stock_mutations')->insert([
                    'date' => date('Y-m-d'),
                    'warehouse_id' => $so->warehouse_id,
                    'product_id' => $item->product_id,
                    'doc_type' => 'GD',
                    'doc_number' => $gdNum,
                    'mutation_type' => 'OUT',
                    'quantity' => $item->quantity,
                    'balance_after' => $newStock,
                    'notes' => "Pengiriman Penjualan ({$so->doc_number}) ke {$customer->name}",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 4. Ubah status ICCID dari RESERVED menjadi SOLD
            DB::table('iccid_units')
                ->where('last_doc_type', 'SO')
                ->where('last_doc_number', $so->doc_number)
                ->update([
                    'status' => 'SOLD',
                    'customer_id' => $so->customer_id,
                    'last_doc_type' => 'GD',
                    'last_doc_number' => $gdNum,
                    'updated_at' => now(),
                ]);

            // 5. Update Status SO menjadi DELIVERED
            DB::table('sales_orders')->where('id', $id)->update([
                'status' => 'DELIVERED',
                'updated_at' => now(),
            ]);

            DB::commit();
            return redirect()->route('sales.index')->with('success', "Sending Good Sukses! Surat Jalan [{$gdNum}] dan Tagihan Piutang [{$arNum}] otomatis terbentuk.");

        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses pengiriman: ' . $e->getMessage());
        }
    }

    public function printNota($id)
    {
        $so = DB::table('sales_orders')
            ->join('customers', 'sales_orders.customer_id', '=', 'customers.id')
            ->join('sales', 'sales_orders.sales_id', '=', 'sales.id')
            ->join('warehouses', 'sales_orders.warehouse_id', '=', 'warehouses.id')
            ->where('sales_orders.id', $id)
            ->select(
                'sales_orders.*',
                'customers.name as customer_name',
                'customers.code as customer_code',
                'customers.phone as customer_phone',
                'customers.address as customer_address',
                'sales.name as sales_name',
                'warehouses.name as warehouse_name'
            )
            ->first();

        if (!$so) abort(404);

        $items = DB::table('sales_order_items')
            ->join('products', 'sales_order_items.product_id', '=', 'products.id')
            ->where('sales_order_items.sales_order_id', $so->id)
            ->select('sales_order_items.*', 'products.name as product_name', 'products.sku', 'products.tracking_method')
            ->get();

        $gd = DB::table('goods_deliveries')->where('sales_order_id', $so->id)->first();
        $ar = DB::table('accounts_receivable')->where('sales_order_id', $so->id)->first();

        return view('sales.print-nota', compact('so', 'items', 'gd', 'ar'));
    }

    public function storeReturn(Request $request)
    {
        $soId = (int)$request->input('sales_order_id');
        $returnDate = $request->input('return_date', date('Y-m-d'));
        $notes = $request->input('notes', 'Sales Retur Barang');
        $itemsData = $request->input('items', []);

        $so = DB::table('sales_orders')->where('id', $soId)->first();
        if (!$so) return redirect()->back()->with('error', 'Sales Order tidak ditemukan!');

        $ar = DB::table('accounts_receivable')->where('sales_order_id', $so->id)->first();

        if (empty($itemsData)) {
            return redirect()->back()->with('error', 'Pilih minimal 1 item barang yang diretur!');
        }

        DB::beginTransaction();
        try {
            $nextNum = DB::table('sales_returns')->count() + 1;
            $docNumber = 'SR/' . date('Ym') . '/' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

            $totalReturnAmount = 0;
            $preparedReturnItems = [];

            foreach ($itemsData as $row) {
                if (empty($row['product_id']) || empty($row['quantity'])) continue;

                $prod = DB::table('products')->where('id', $row['product_id'])->first();
                $qty = (int)$row['quantity'];
                $price = (float)$prod->selling_price;
                $subtotal = $qty * $price;
                $totalReturnAmount += $subtotal;

                $preparedReturnItems[] = [
                    'product_id' => $row['product_id'],
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'subtotal' => $subtotal,
                ];
            }

            $srId = DB::table('sales_returns')->insertGetId([
                'doc_type' => 'SR',
                'doc_number' => $docNumber,
                'ref_doc_type' => 'AR',
                'ref_doc_number' => $ar ? $ar->doc_number : $so->doc_number,
                'sales_order_id' => $so->id,
                'customer_id' => $so->customer_id,
                'warehouse_id' => $so->warehouse_id,
                'return_date' => $returnDate,
                'total_amount' => $totalReturnAmount,
                'status' => 'COMPLETED',
                'notes' => $notes,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($preparedReturnItems as $pItem) {
                DB::table('sales_return_items')->insert([
                    'sales_return_id' => $srId,
                    'product_id' => $pItem['product_id'],
                    'quantity' => $pItem['quantity'],
                    'unit_price' => $pItem['unit_price'],
                    'subtotal' => $pItem['subtotal'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Kembalikan stok ke gudang asal (IN)
                $currStock = DB::table('stock_balances')
                    ->where('warehouse_id', $so->warehouse_id)
                    ->where('product_id', $pItem['product_id'])
                    ->value('quantity') ?? 0;

                $newStock = $currStock + $pItem['quantity'];
                DB::table('stock_balances')
                    ->where('warehouse_id', $so->warehouse_id)
                    ->where('product_id', $pItem['product_id'])
                    ->update([
                        'quantity' => $newStock,
                        'updated_at' => now()
                    ]);

                // Catat mutasi IN di Buku Kartu Stok
                DB::table('stock_mutations')->insert([
                    'date' => $returnDate,
                    'warehouse_id' => $so->warehouse_id,
                    'product_id' => $pItem['product_id'],
                    'doc_type' => 'SR',
                    'doc_number' => $docNumber,
                    'mutation_type' => 'IN',
                    'quantity' => $pItem['quantity'],
                    'balance_after' => $newStock,
                    'notes' => "Sales Retur ({$docNumber}) dari {$so->doc_number}",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Potong piutang customer di AR jika ada
            if ($ar) {
                $newBalance = max(0, (float)$ar->balance_amount - $totalReturnAmount);
                $newStatus = $newBalance <= 0 ? 'PAID' : ($ar->paid_amount > 0 ? 'PARTIAL' : 'UNPAID');

                DB::table('accounts_receivable')->where('id', $ar->id)->update([
                    'balance_amount' => $newBalance,
                    'status' => $newStatus,
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return redirect()->route('sales.index')->with('success', "Dokumen Sales Retur [{$docNumber}] berhasil diproses! Stok fisik dikembalikan dan piutang dipotong Rp " . number_format($totalReturnAmount, 0, ',', '.') . ".");

        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses Sales Retur: ' . $e->getMessage());
        }
    }
}
