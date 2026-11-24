<?php

namespace App\Http\Controllers;

use App\Services\TerritoryScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesController extends Controller
{
    public function index()
    {
        // 1. Ambil daftar Sales Orders (Header) terfilter TerritoryScope
        $ordersRaw = TerritoryScope::applyTapScope(DB::table('sales_orders'), 'sales_orders.tap_id')
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

        // Ambil ICCID terkait SO (jika fitur ICCID diaktifkan)
        $iccidEnabled = (bool) config('erp.enable_iccid', false);
        $soDocNumbers = $ordersRaw->pluck('doc_number')->toArray();
        $gdDocNumbers = $gdList->pluck('doc_number')->toArray();
        $allDocRefs = array_unique(array_merge($soDocNumbers, $gdDocNumbers));

        $iccidsByDoc = [];
        if ($iccidEnabled && !empty($allDocRefs)) {
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

        // 2. Daftar Surat Jalan (GD) terfilter TerritoryScope
        $deliveries = TerritoryScope::applyWarehouseScope(DB::table('goods_deliveries'), 'goods_deliveries.warehouse_id')
            ->join('customers', 'goods_deliveries.customer_id', '=', 'customers.id')
            ->join('warehouses', 'goods_deliveries.warehouse_id', '=', 'warehouses.id')
            ->select('goods_deliveries.*', 'customers.name as customer_name', 'warehouses.name as warehouse_name')
            ->orderBy('goods_deliveries.id', 'desc')
            ->get();

        // 3. Daftar Retur (SR) terfilter TerritoryScope
        $returns = TerritoryScope::applyWarehouseScope(DB::table('sales_returns'), 'sales_returns.warehouse_id')
            ->join('customers', 'sales_returns.customer_id', '=', 'customers.id')
            ->join('warehouses', 'sales_returns.warehouse_id', '=', 'warehouses.id')
            ->select('sales_returns.*', 'customers.name as customer_name', 'warehouses.name as warehouse_name')
            ->orderBy('sales_returns.id', 'desc')
            ->get();

        // 4. Master Data Pendukung Pembuatan SO (Terfilter TerritoryScope)
        $customers = TerritoryScope::applyTapScope(DB::table('customers')->where('status', 'ACTIVE'), 'customers.tap_id')->orderBy('name')->get();
        
        // Ambil daftar sales beserta gudang kanvas/sales miliknya
        $salesList = TerritoryScope::applyTapScope(
            DB::table('sales')
                ->leftJoin('warehouses', function($join) {
                    $join->on('sales.id', '=', 'warehouses.sales_id')
                         ->where('warehouses.status', '=', 'ACTIVE');
                })
                ->where('sales.status', 'ACTIVE')
                ->select(
                    'sales.*',
                    'warehouses.id as warehouse_id',
                    'warehouses.name as warehouse_name',
                    'warehouses.type as warehouse_type'
                ),
            'sales.tap_id'
        )->orderBy('sales.name')->get();

        // Jika ada sales yang belum punya gudang khusus sales_id, pasangkan ke gudang default TAP
        foreach ($salesList as $s) {
            if (!$s->warehouse_id) {
                $tapWh = DB::table('warehouses')
                    ->where('tap_id', $s->tap_id)
                    ->where('status', 'ACTIVE')
                    ->orderByRaw("FIELD(type, 'SALES', 'INVENTORY', 'MAIN')")
                    ->first();
                if ($tapWh) {
                    $s->warehouse_id = $tapWh->id;
                    $s->warehouse_name = $tapWh->name;
                    $s->warehouse_type = $tapWh->type;
                }
            }
        }
        
        // Gudang untuk penjualan: SALES (Kanvas) dan INVENTORY (Stok TAP)
        $warehouses = TerritoryScope::applyWarehouseScope(
            DB::table('warehouses')
                ->where('status', 'ACTIVE')
                ->whereIn('type', ['SALES', 'INVENTORY']),
            'warehouses.id'
        )
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $products = DB::table('products')->where('status', 'ACTIVE')->orderBy('name')->get();
        $stockBalances = TerritoryScope::applyWarehouseScope(DB::table('stock_balances'), 'stock_balances.warehouse_id')->get();
        $availableIccids = $iccidEnabled
            ? TerritoryScope::applyWarehouseScope(
                DB::table('iccid_units')->where('status', 'AVAILABLE'),
                'iccid_units.warehouse_id'
            )
                ->select('id', 'iccid_number', 'warehouse_id', 'product_id')
                ->get()
            : collect([]);

        return view('sales.index', compact(
            'orders',
            'deliveries',
            'returns',
            'customers',
            'salesList',
            'warehouses',
            'products',
            'stockBalances',
            'availableIccids',
            'iccidEnabled'
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

        if (!$customerId || !$salesId) {
            return redirect()->back()->with('error', 'Lengkapi pilihan Customer dan Sales!');
        }

        // Jika warehouse_id tidak dikirim dari form, otomatis cari gudang milik sales tersebut
        if (!$warehouseId) {
            $salesWh = DB::table('warehouses')
                ->where('sales_id', $salesId)
                ->where('status', 'ACTIVE')
                ->first();

            if ($salesWh) {
                $warehouseId = $salesWh->id;
            } else {
                // Fallback ke gudang TAP milik sales
                $salesData = DB::table('sales')->where('id', $salesId)->first();
                if ($salesData && $salesData->tap_id) {
                    $tapWh = DB::table('warehouses')
                        ->where('tap_id', $salesData->tap_id)
                        ->where('status', 'ACTIVE')
                        ->orderByRaw("FIELD(type, 'SALES', 'INVENTORY', 'MAIN')")
                        ->first();
                    if ($tapWh) $warehouseId = $tapWh->id;
                }
            }
        }

        if (!$warehouseId) {
            return redirect()->back()->with('error', 'Gudang kanvas/stok untuk sales terpilih tidak ditemukan!');
        }

        if (empty($itemsData)) {
            return redirect()->back()->with('error', 'Pilih minimal 1 item produk untuk dipesan!');
        }

        $customer = DB::table('customers')->where('id', $customerId)->first();
        if (!$customer) {
            return redirect()->back()->with('error', 'Customer tidak ditemukan!');
        }
        if ($customer->tap_id && !TerritoryScope::canAccessTap((int)$customer->tap_id)) {
            return redirect()->back()->with('error', 'Akses ditolak: Customer berada di luar wilayah wewenang Anda!');
        }

        // Validasi Jam Close SO (Cutoff Per Cluster)
        $cutoffCheck = \App\Services\OrderCutoffService::checkByCustomerId($customerId);
        if (!$cutoffCheck['is_open']) {
            return redirect()->back()->with('error', 'Pemesanan Ditolak: ' . $cutoffCheck['reason']);
        }

        $sales = DB::table('sales')->where('id', $salesId)->first();
        if (!$sales) {
            return redirect()->back()->with('error', 'Sales tidak ditemukan!');
        }
        if ($sales->tap_id && !TerritoryScope::canAccessTap((int)$sales->tap_id)) {
            return redirect()->back()->with('error', 'Akses ditolak: Sales berada di luar wilayah wewenang Anda!');
        }

        $warehouse = DB::table('warehouses')->where('id', $warehouseId)->first();
        if (!$warehouse) {
            return redirect()->back()->with('error', 'Gudang tidak ditemukan!');
        }
        if (!TerritoryScope::canAccessWarehouse((int)$warehouseId)) {
            return redirect()->back()->with('error', 'Akses ditolak: Gudang berada di luar wilayah wewenang Anda!');
        }

        $iccidEnabled = (bool) config('erp.enable_iccid', false);

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

                if (!$iccidEnabled || $prod->tracking_method === 'QUANTITY') {
                    $qty = max(1, (int)($row['quantity'] ?? 1));
                    if ($currentStock < $qty) {
                        throw new \Exception("Stok {$prod->name} di {$warehouse->name} tidak mencukupi (Tersedia: {$currentStock} pcs, Diminta: {$qty} pcs)!");
                    }
                } else {
                    // Tracking: ICCID (hanya jika fitur ICCID aktif)
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

            // Validasi Limit Kredit Customer
            $creditCheck = \App\Services\CustomerCreditService::validateOrder($customerId, (float)$grandTotal);
            if (!$creditCheck['allowed']) {
                throw new \Exception($creditCheck['message']);
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

            // Kunci status serial ICCID menjadi RESERVED untuk SO ini (jika fitur ICCID aktif)
            if ($iccidEnabled && !empty($allReservedIccids)) {
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

        if (!TerritoryScope::canAccessWarehouse((int)$so->warehouse_id) && !TerritoryScope::canAccessTap((int)$so->tap_id)) {
            return redirect()->back()->with('error', 'Akses ditolak: Dokumen SO berada di luar wilayah wewenang Anda!');
        }

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

        if (!TerritoryScope::canAccessWarehouse((int)$so->warehouse_id) && !TerritoryScope::canAccessTap((int)$so->tap_id)) {
            return redirect()->back()->with('error', 'Akses ditolak: Dokumen SO berada di luar wilayah wewenang Anda!');
        }

        $iccidEnabled = (bool) config('erp.enable_iccid', false);

        DB::beginTransaction();
        try {
            DB::table('sales_orders')->where('id', $id)->update([
                'status' => 'REJECTED',
                'updated_at' => now(),
            ]);

            // Lepas ICCID yang sempat di-reserve kembali jadi AVAILABLE jika fitur ICCID aktif
            if ($iccidEnabled) {
                DB::table('iccid_units')
                    ->where('last_doc_type', 'SO')
                    ->where('last_doc_number', $so->doc_number)
                    ->where('status', 'RESERVED')
                    ->update([
                        'status' => 'AVAILABLE',
                        'updated_at' => now(),
                    ]);
            }

            DB::commit();
            return redirect()->back()->with('success', "Sales Order [{$so->doc_number}] telah DITOLAK Finance. Stok dan serial ICCID telah dikembalikan.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menolak SO: ' . $e->getMessage());
        }
    }

    /**
     * Endpoint AJAX / JSON untuk mengambil informasi limit kredit customer secara real-time.
     */
    public function getCustomerCreditInfo($customerId)
    {
        $info = \App\Services\CustomerCreditService::getCreditSummary((int)$customerId);
        return response()->json($info);
    }

    /**
     * Membatalkan / Void Sales Order:
     * - Dapat di-VOID hingga status DELIVERED (sudah terbit GD),
     * - TETAPI TIDAK BISA di-VOID jika sudah ada pembayaran pada Faktur Piutang (AR).
     * - Jika di-VOID pada tahap GD: surat jalan (GD) dan piutang (AR) dibatalkan/dihapus,
     *   stok barang yang sempat terpotong saat GD dikembalikan utuh ke gudang,
     *   dan status serial ICCID dipulihkan ke AVAILABLE.
     */
    public function void(Request $request, $id)
    {
        $so = DB::table('sales_orders')->where('id', $id)->first();
        if (!$so) return redirect()->back()->with('error', 'SO tidak ditemukan!');

        if (!TerritoryScope::canAccessWarehouse((int)$so->warehouse_id) && !TerritoryScope::canAccessTap((int)$so->tap_id)) {
            return redirect()->back()->with('error', 'Akses ditolak: Dokumen SO berada di luar wilayah wewenang Anda!');
        }

        if (in_array($so->status, ['COMPLETED', 'VOID'])) {
            return redirect()->back()->with('error', "Dokumen SO sudah berstatus {$so->status} dan tidak dapat dibatalkan!");
        }

        // Cek apakah ada AR terkait
        $ar = DB::table('accounts_receivable')->where('sales_order_id', $so->id)->first();
        if ($ar) {
            // Jika AR sudah ada pembayaran (status PARTIAL atau PAID, atau ada paid_amount > 0)
            if ($ar->status === 'PAID' || $ar->status === 'PARTIAL' || (float)$ar->paid_amount > 0) {
                return redirect()->back()->with('error', "SO tidak dapat di-VOID karena Faktur Piutang ({$ar->doc_number}) sudah memiliki riwayat pembayaran!");
            }

            // Cek apakah ada payment_allocations ke AR ini dari TTK yang AKTIF (status bukan VOID)
            $hasActivePayment = DB::table('payment_allocations')
                ->join('payments', 'payment_allocations.payment_id', '=', 'payments.id')
                ->where('payment_allocations.accounts_receivable_id', $ar->id)
                ->where('payments.status', '!=', 'VOID')
                ->exists();

            if ($hasActivePayment) {
                return redirect()->back()->with('error', "SO tidak dapat di-VOID karena Faktur Piutang ({$ar->doc_number}) masih terhubung ke Tanda Terima Kasir (TTK) aktif!");
            }
        }

        $iccidEnabled = (bool) config('erp.enable_iccid', false);
        $soItems = DB::table('sales_order_items')->where('sales_order_id', $so->id)->get();
        $gd = DB::table('goods_deliveries')->where('sales_order_id', $so->id)->first();

        DB::beginTransaction();
        try {
            // 1. Jika barang sudah dikirim (ada GD / DELIVERED), kembalikan fisik stok ke gudang
            if ($gd || $so->status === 'DELIVERED') {
                foreach ($soItems as $item) {
                    $currStock = DB::table('stock_balances')
                        ->where('warehouse_id', $so->warehouse_id)
                        ->where('product_id', $item->product_id)
                        ->value('quantity') ?? 0;

                    $restoredStock = $currStock + $item->quantity;
                    DB::table('stock_balances')
                        ->where('warehouse_id', $so->warehouse_id)
                        ->where('product_id', $item->product_id)
                        ->update([
                            'quantity' => $restoredStock,
                            'updated_at' => now(),
                        ]);

                    // Catat mutasi pembatalan (IN kembali)
                    DB::table('stock_mutations')->insert([
                        'date' => date('Y-m-d'),
                        'warehouse_id' => $so->warehouse_id,
                        'product_id' => $item->product_id,
                        'doc_type' => 'VOID_SO',
                        'doc_number' => $so->doc_number,
                        'mutation_type' => 'IN',
                        'quantity' => $item->quantity,
                        'balance_after' => $restoredStock,
                        'notes' => "Pengembalian stok dari VOID SO ({$so->doc_number}) & GD (" . ($gd->doc_number ?? '-') . ")",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Hapus data GD atau tandai CANCELLED
                DB::table('goods_deliveries')->where('sales_order_id', $so->id)->delete();
            }

            // 2. Hapus dokumen piutang AR (jika belum ada pembayaran sama sekali)
            if ($ar) {
                DB::table('accounts_receivable')->where('id', $ar->id)->delete();
            }

            // 3. Kembalikan status serial ICCID menjadi AVAILABLE (jika fitur aktif)
            if ($iccidEnabled) {
                // Untuk serial yang sempat SOLD di GD atau RESERVED di SO
                DB::table('iccid_units')
                    ->where(function($q) use ($so, $gd) {
                        $q->where('last_doc_number', $so->doc_number);
                        if ($gd) {
                            $q->orWhere('last_doc_number', $gd->doc_number);
                        }
                    })
                    ->update([
                        'status' => 'AVAILABLE',
                        'customer_id' => null,
                        'last_doc_type' => 'VOID_SO',
                        'last_doc_number' => $so->doc_number,
                        'updated_at' => now(),
                    ]);
            }

            // 4. Update status SO menjadi VOID
            DB::table('sales_orders')->where('id', $id)->update([
                'status' => 'VOID',
                'notes' => trim(($so->notes ? $so->notes . ' | ' : '') . 'DIBATALKAN (VOID) pada ' . now()->format('d/m/Y H:i')),
                'updated_at' => now(),
            ]);

            DB::commit();
            return redirect()->back()->with('success', "Sales Order [{$so->doc_number}] berhasil di-VOID (Dibatalkan). Stok barang dan limit kredit customer telah dipulihkan.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membatalkan SO: ' . $e->getMessage());
        }
    }

    public function deliver(Request $request, $id)
    {
        $so = DB::table('sales_orders')->where('id', $id)->first();
        if (!$so) return redirect()->back()->with('error', 'SO tidak ditemukan!');

        if (!TerritoryScope::canAccessWarehouse((int)$so->warehouse_id) && !TerritoryScope::canAccessTap((int)$so->tap_id)) {
            return redirect()->back()->with('error', 'Akses ditolak: Dokumen SO berada di luar wilayah wewenang Anda!');
        }

        if ($so->status !== 'APPROVED') {
            return redirect()->back()->with('error', 'Hanya SO yang sudah disetujui Finance (APPROVED) yang dapat dikirimkan!');
        }

        $iccidEnabled = (bool) config('erp.enable_iccid', false);
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

            // 4. Ubah status ICCID dari RESERVED menjadi SOLD jika fitur ICCID aktif
            if ($iccidEnabled) {
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
            }

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

        if (!TerritoryScope::canAccessWarehouse((int)$so->warehouse_id) && !TerritoryScope::canAccessTap((int)$so->tap_id)) {
            abort(403, 'Akses ditolak: Dokumen SO berada di luar wilayah wewenang Anda!');
        }

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

        if (!TerritoryScope::canAccessWarehouse((int)$so->warehouse_id) && !TerritoryScope::canAccessTap((int)$so->tap_id)) {
            return redirect()->back()->with('error', 'Akses ditolak: Dokumen SO berada di luar wilayah wewenang Anda!');
        }

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

    /**
     * Tampilan Target, Komisi & Leaderboard Sales Kanvaser
     */
    public function targets(Request $request)
    {
        $salesRaw = DB::table('sales')
            ->join('taps', 'sales.tap_id', '=', 'taps.id')
            ->select('sales.*', 'taps.name as tap_name')
            ->where('sales.status', 'ACTIVE')
            ->get();

        // Ambil omset riil dari sales_orders
        $soTotals = DB::table('sales_orders')
            ->whereNotIn('status', ['REJECTED'])
            ->select('sales_id', DB::raw('SUM(grand_total) as total_omset'), DB::raw('COUNT(id) as total_so'))
            ->groupBy('sales_id')
            ->get()
            ->keyBy('sales_id');

        // Target & Capaian Bulanan Realistis untuk Demo Distributor
        $salesProfiles = [
            1 => [
                'target_revenue' => 110000000,
                'base_revenue' => 129450000, // Di atas target
                'target_sa_pcs' => 1200,
                'real_sa_pcs' => 1380,
                'target_vcr_pcs' => 2500,
                'real_vcr_pcs' => 2850,
                'target_noa' => 8,
                'real_noa' => 11, // Toko baru
                'avatar_color' => 'bg-amber-500',
                'badge_title' => 'TOP GUN KANVASER',
            ],
            2 => [
                'target_revenue' => 100000000,
                'base_revenue' => 108320000,
                'target_sa_pcs' => 1000,
                'real_sa_pcs' => 1120,
                'target_vcr_pcs' => 2200,
                'real_vcr_pcs' => 2340,
                'target_noa' => 8,
                'real_noa' => 9,
                'avatar_color' => 'bg-blue-600',
                'badge_title' => 'STAR ACHIEVER',
            ],
            3 => [
                'target_revenue' => 95000000,
                'base_revenue' => 91800000,
                'target_sa_pcs' => 950,
                'real_sa_pcs' => 910,
                'target_vcr_pcs' => 2000,
                'real_vcr_pcs' => 1980,
                'target_noa' => 6,
                'real_noa' => 7,
                'avatar_color' => 'bg-emerald-600',
                'badge_title' => 'SOLID PERFORMER',
            ],
        ];

        $leaderboard = [];

        foreach ($salesRaw as $idx => $s) {
            $profile = $salesProfiles[$s->id] ?? [
                'target_revenue' => 80000000,
                'base_revenue' => 72000000,
                'target_sa_pcs' => 800,
                'real_sa_pcs' => 750,
                'target_vcr_pcs' => 1600,
                'real_vcr_pcs' => 1500,
                'target_noa' => 5,
                'real_noa' => 4,
                'avatar_color' => 'bg-slate-700',
                'badge_title' => 'CANVAS PRO',
            ];

            // Tambahkan omset transaksi riil di DB jika ada
            $realFromDb = (float)($soTotals[$s->id]->total_omset ?? 0);
            $finalRevenue = $profile['base_revenue'] + $realFromDb;
            $targetRevenue = $profile['target_revenue'];

            $achievementPct = $targetRevenue > 0 ? round(($finalRevenue / $targetRevenue) * 100, 1) : 0;

            // Hitung Komisi & Bonus Insentif berdasarkan Tier Capaian
            if ($achievementPct >= 115) {
                $commissionRate = 3.5;
                $bonusChampion = 1500000;
                $tierBadge = 'TIER 3 (SUPERIOR)';
                $tierColor = 'emerald';
            } elseif ($achievementPct >= 100) {
                $commissionRate = 3.0;
                $bonusChampion = 750000;
                $tierBadge = 'TIER 2 (TARGET MET)';
                $tierColor = 'blue';
            } elseif ($achievementPct >= 90) {
                $commissionRate = 2.5;
                $bonusChampion = 0;
                $tierBadge = 'TIER 1 (QUALIFIED)';
                $tierColor = 'amber';
            } else {
                $commissionRate = 1.5;
                $bonusChampion = 0;
                $tierBadge = 'TIER DASAR';
                $tierColor = 'slate';
            }

            $commissionAmount = round($finalRevenue * ($commissionRate / 100));
            $noaBonus = $profile['real_noa'] * 75000; // Rp 75rb per New Open Account
            $totalTakeHomeIncentive = $commissionAmount + $bonusChampion + $noaBonus;

            $leaderboard[] = (object) [
                'id' => $s->id,
                'code' => $s->code,
                'name' => $s->name,
                'phone' => $s->phone,
                'tap_name' => $s->tap_name,
                'target_revenue' => $targetRevenue,
                'final_revenue' => $finalRevenue,
                'achievement_pct' => $achievementPct,
                'target_sa_pcs' => $profile['target_sa_pcs'],
                'real_sa_pcs' => $profile['real_sa_pcs'],
                'target_vcr_pcs' => $profile['target_vcr_pcs'],
                'real_vcr_pcs' => $profile['real_vcr_pcs'],
                'target_noa' => $profile['target_noa'],
                'real_noa' => $profile['real_noa'],
                'commission_rate' => $commissionRate,
                'commission_amount' => $commissionAmount,
                'bonus_champion' => $bonusChampion,
                'noa_bonus' => $noaBonus,
                'total_incentive' => $totalTakeHomeIncentive,
                'tier_badge' => $tierBadge,
                'tier_color' => $tierColor,
                'avatar_color' => $profile['avatar_color'],
                'badge_title' => $profile['badge_title'],
                'payout_status' => $achievementPct >= 100 ? 'READY FOR PAYOUT' : 'PENDING REVIEW',
            ];
        }

        // Urutkan peringkat berdasarkan persentase capaian tertinggi
        usort($leaderboard, function ($a, $b) {
            return $b->achievement_pct <=> $a->achievement_pct;
        });

        // Tetapkan Rank 1, 2, 3 .. N
        foreach ($leaderboard as $pos => $item) {
            $item->rank = $pos + 1;
        }

        // Ringkasan Cluster Level
        $totalClusterTarget = collect($leaderboard)->sum('target_revenue');
        $totalClusterRevenue = collect($leaderboard)->sum('final_revenue');
        $totalClusterPoolIncentive = collect($leaderboard)->sum('total_incentive');
        $clusterAchievementPct = $totalClusterTarget > 0 ? round(($totalClusterRevenue / $totalClusterTarget) * 100, 1) : 0;

        $topSales = $leaderboard[0] ?? null;

        return view('sales.targets', compact(
            'leaderboard',
            'totalClusterTarget',
            'totalClusterRevenue',
            'totalClusterPoolIncentive',
            'clusterAchievementPct',
            'topSales'
        ));
    }

    /**
     * Halaman Sales Retur (Retur Penjualan / Retur SO dari Outlet)
     */
    public function returnsIndex(Request $request)
    {
        $dbReturns = DB::table('sales_returns')
            ->join('customers', 'sales_returns.customer_id', '=', 'customers.id')
            ->join('warehouses', 'sales_returns.warehouse_id', '=', 'warehouses.id')
            ->leftJoin('sales_orders', 'sales_returns.sales_order_id', '=', 'sales_orders.id')
            ->select(
                'sales_returns.*',
                'customers.name as customer_name',
                'customers.code as customer_code',
                'warehouses.name as warehouse_name',
                'sales_orders.doc_number as so_number'
            )
            ->orderByDesc('sales_returns.id')
            ->get();

        // Jika tabel sales_returns masih belum memiliki baris, buat tampilan data ilustratif awal
        if ($dbReturns->isEmpty()) {
            $sampleReturns = [
                (object) [
                    'id' => 1,
                    'doc_type' => 'SR',
                    'doc_number' => 'SR/202611/0001',
                    'ref_doc_type' => 'SO',
                    'ref_doc_number' => 'SO/202611/0002',
                    'customer_name' => 'Outlet Berkah Cell (Cimanggis)',
                    'customer_code' => 'OUT-002',
                    'warehouse_name' => 'Gudang Sales Motor (SM-01)',
                    'return_date' => '2026-11-10',
                    'total_amount' => 450000,
                    'status' => 'COMPLETED',
                    'notes' => 'Fisik voucher gesek tergores saat display etalase outlet',
                ],
                (object) [
                    'id' => 2,
                    'doc_type' => 'SR',
                    'doc_number' => 'SR/202611/0002',
                    'ref_doc_type' => 'SO',
                    'ref_doc_number' => 'SO/202611/0005',
                    'customer_name' => 'Pojok Pulsa Express (Depok Timur)',
                    'customer_code' => 'OUT-005',
                    'warehouse_name' => 'Gudang Utama TAP Depok',
                    'return_date' => '2026-11-11',
                    'total_amount' => 1250000,
                    'status' => 'PENDING',
                    'notes' => 'Tukar tipe denominasi paket data Telkomsel Lite 10GB',
                ]
            ];
            $returns = collect($sampleReturns);
        } else {
            $returns = $dbReturns;
        }

        $kpi = [
            'total_returns' => $returns->count(),
            'total_amount' => $returns->sum('total_amount'),
            'completed_count' => $returns->where('status', 'COMPLETED')->count(),
            'pending_count' => $returns->where('status', '!=', 'COMPLETED')->count(),
        ];

        // Daftar Sales Orders yang sudah lunas / terkirim
        $deliveredSos = DB::table('sales_orders')
            ->join('customers', 'sales_orders.customer_id', '=', 'customers.id')
            ->whereIn('sales_orders.status', ['DELIVERED', 'COMPLETED'])
            ->select('sales_orders.*', 'customers.name as customer_name')
            ->orderByDesc('sales_orders.id')
            ->take(10)
            ->get();

        return view('sales.returns', compact('returns', 'kpi', 'deliveredSos'));
    }
}
