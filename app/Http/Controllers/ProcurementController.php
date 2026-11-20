<?php

namespace App\Http\Controllers;

use App\Services\StockService;
use App\Services\TerritoryScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProcurementController extends Controller
{
    public function index(Request $request)
    {
        $iccidEnabled = (bool) config('erp.enable_iccid', false);

        $poQuery = DB::table('purchase_orders')
            ->join('taps', 'purchase_orders.tap_id', '=', 'taps.id')
            ->join('clusters', 'taps.cluster_id', '=', 'clusters.id')
            ->join('areas', 'clusters.area_id', '=', 'areas.id')
            ->leftJoin('vendors', 'purchase_orders.vendor_id', '=', 'vendors.id')
            ->leftJoin('goods_receipts', 'goods_receipts.purchase_order_id', '=', 'purchase_orders.id')
            ->select(
                'purchase_orders.*',
                'taps.name as tap_name',
                'taps.code as tap_code',
                'taps.cluster_id',
                'clusters.name as cluster_name',
                'clusters.area_id',
                'areas.name as area_name',
                'vendors.name as vendor_name',
                'vendors.code as vendor_code',
                'goods_receipts.doc_number as gr_number',
                'goods_receipts.do_number',
                'goods_receipts.do_file_path',
                'goods_receipts.received_date as gr_received_date'
            )
            ->orderBy('purchase_orders.id', 'desc');

        if ($request->filled('start_date')) {
            $poQuery->whereDate('purchase_orders.order_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $poQuery->whereDate('purchase_orders.order_date', '<=', $request->end_date);
        }

        $ordersRaw = TerritoryScope::applyTapScope($poQuery, 'purchase_orders.tap_id')->get();

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
                if (!$iccidEnabled) {
                    $item->tracking_method = 'QUANTITY';
                }
                $itemsByPo[$item->purchase_order_id][] = $item;
            }
        }

        // Ambil Gudang MAIN per TAP untuk informasi tujuan default PO
        $mainWhByTap = DB::table('warehouses')
            ->where('type', 'MAIN')
            ->where('status', 'ACTIVE')
            ->pluck('name', 'tap_id');

        $orders = $ordersRaw->map(function ($po) use ($itemsByPo, $iccidEnabled, $mainWhByTap) {
            $po->items = $itemsByPo[$po->id] ?? [];
            $po->total_qty = collect($po->items)->sum('quantity');
            $po->has_iccid = $iccidEnabled && collect($po->items)->contains('tracking_method', 'ICCID');
            $po->vendor_display = $po->vendor_name ?: $po->supplier_name;
            $po->main_warehouse_name = $mainWhByTap[$po->tap_id] ?? ('Gudang Utama ' . $po->tap_name);
            return $po;
        });

        $receipts = TerritoryScope::applyWarehouseScope(
            DB::table('goods_receipts')
                ->join('taps', 'goods_receipts.tap_id', '=', 'taps.id')
                ->join('warehouses', 'goods_receipts.warehouse_id', '=', 'warehouses.id')
                ->select('goods_receipts.*', 'taps.name as tap_name', 'warehouses.name as warehouse_name')
                ->orderBy('goods_receipts.id', 'desc'),
            'goods_receipts.warehouse_id'
        )->get();

        $products = DB::table('products')
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get()
            ->map(function ($p) use ($iccidEnabled) {
                if (!$iccidEnabled) {
                    $p->tracking_method = 'QUANTITY';
                }
                return $p;
            });

        $areas = TerritoryScope::applyAreaScope(
            DB::table('areas')->where('status', 'ACTIVE')->orderBy('name'),
            'areas.id'
        )->get();
        $clusters = TerritoryScope::applyClusterScope(
            DB::table('clusters')->where('status', 'ACTIVE')->orderBy('name'),
            'clusters.id'
        )->get();
        $taps = TerritoryScope::applyTapScope(
            DB::table('taps')
                ->join('clusters', 'taps.cluster_id', '=', 'clusters.id')
                ->where('taps.status', 'ACTIVE')
                ->select('taps.*', 'clusters.area_id')
                ->orderBy('taps.name'),
            'taps.id'
        )->get()->map(function ($t) use ($mainWhByTap) {
            $t->main_warehouse_name = $mainWhByTap[$t->id] ?? ('Gudang Utama ' . $t->name);
            return $t;
        });

        $vendors = DB::table('vendors')->where('status', 'ACTIVE')->orderBy('name')->get();

        return view('procurement.index', compact(
            'orders',
            'receipts',
            'products',
            'areas',
            'clusters',
            'taps',
            'vendors',
            'iccidEnabled'
        ));
    }

    public function storePo(Request $request)
    {
        if (Auth::user()?->is_ho_readonly) {
            return redirect()->back()->with('error', 'Akun HO Read-Only tidak memiliki izin membuat transaksi PO.');
        }

        $request->validate([
            'tap_id' => 'required|exists:taps,id',
            'vendor_id' => 'required|exists:vendors,id',
            'order_date' => 'required|date',
        ], [
            'tap_id.required' => 'TAP Pemesan wajib dipilih!',
            'vendor_id.required' => 'Vendor / Supplier wajib dipilih!',
        ]);

        $tapId = (int) $request->input('tap_id');
        if (!TerritoryScope::canAccessTap($tapId)) {
            return redirect()->back()->with('error', 'Akses ditolak: Anda tidak memiliki otorisasi wilayah untuk TAP tersebut.');
        }

        $vendor = DB::table('vendors')->where('id', $request->input('vendor_id'))->first();
        $supplierName = $vendor ? $vendor->name : 'PT TELKOMSEL DISTRIBUSI PUSAT';
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
                $resolvedPrice = StockService::resolveProductPrice((int) $prod->id, $tapId);
                $costPrice = (float) $resolvedPrice['cost_price'];
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
                'vendor_id' => $vendor ? $vendor->id : null,
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
            return redirect()->route('procurement.index')->with('success', "PO Baru [{$docNumber}] ke {$supplierName} berhasil diterbitkan!");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membuat PO: ' . $e->getMessage());
        }
    }

    public function voidPo(Request $request, $id)
    {
        if (Auth::user()?->is_ho_readonly) {
            return redirect()->back()->with('error', 'Akun HO Read-Only tidak memiliki izin melakukan Void PO.');
        }

        $po = DB::table('purchase_orders')->where('id', $id)->first();
        if (!$po) {
            return redirect()->route('procurement.index')->with('error', 'Dokumen PO tidak ditemukan!');
        }

        if (!TerritoryScope::canAccessTap((int) $po->tap_id)) {
            return redirect()->route('procurement.index')->with('error', 'Akses ditolak: PO ini berada di luar cakupan wilayah Anda.');
        }

        if ($po->status === 'RECEIVED') {
            return redirect()->route('procurement.index')->with('error', "PO [{$po->doc_number}] sudah diterima (RECEIVED) sehingga tidak dapat di-VOID!");
        }

        if ($po->status === 'CANCELLED') {
            return redirect()->route('procurement.index')->with('error', "PO [{$po->doc_number}] sudah berstatus VOID / CANCELLED.");
        }

        $reason = trim((string) $request->input('void_reason', 'Dibatalkan (VOID) oleh pengguna'));
        $updatedNotes = trim(($po->notes ? $po->notes . ' | ' : '') . "[VOID: {$reason}]");

        DB::table('purchase_orders')->where('id', $id)->update([
            'status' => 'CANCELLED',
            'notes' => $updatedNotes,
            'updated_at' => now(),
        ]);

        return redirect()->route('procurement.index')->with('success', "Dokumen PO [{$po->doc_number}] berhasil di-VOID (Dibatalkan).");
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

        if (!TerritoryScope::canAccessTap((int) $po->tap_id)) {
            return redirect()->route('procurement.index')->with('error', 'Akses ditolak: PO ini berada di luar cakupan wilayah Anda.');
        }

        if ($po->status === 'RECEIVED') {
            return redirect()->route('procurement.index')->with('error', 'PO ini sudah pernah diterima lengkap (RECEIVED)!');
        }

        if ($po->status === 'CANCELLED') {
            return redirect()->route('procurement.index')->with('error', 'PO ini sudah di-VOID sehingga tidak dapat diproses GR!');
        }

        $iccidEnabled = (bool) config('erp.enable_iccid', false);

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

        // Daftar gudang tujuan di TAP ini yang boleh diakses user (default gudang MAIN)
        $allowedWarehouses = TerritoryScope::applyWarehouseScope(
            DB::table('warehouses')
                ->where('tap_id', $po->tap_id)
                ->where('status', 'ACTIVE')
                ->whereIn('type', ['MAIN', 'INVENTORY'])
                ->orderByRaw("CASE WHEN type = 'MAIN' THEN 0 ELSE 1 END")
                ->orderBy('name'),
            'warehouses.id'
        )->get();

        if ($allowedWarehouses->isEmpty()) {
            return redirect()->route('procurement.index')->with('error', 'Anda tidak memiliki akses gudang penerima (MAIN/INVENTORY) pada TAP ini.');
        }

        $mainWh = $allowedWarehouses->first();

        $nextGrNum = DB::table('goods_receipts')->count() + 1;
        $predictedGrNumber = 'GR/' . date('Ym') . '/' . str_pad($nextGrNum, 4, '0', STR_PAD_LEFT);

        return view('procurement.gr-form', compact('po', 'items', 'mainWh', 'allowedWarehouses', 'predictedGrNumber', 'iccidEnabled'));
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
        if (Auth::user()?->is_ho_readonly) {
            return redirect()->route('procurement.index')->with('error', 'Akun HO Read-Only tidak memiliki izin memproses GR.');
        }

        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'do_number' => 'required|string|max:100',
            'do_file' => 'required|file|mimes:pdf|max:10240',
            'received_date' => 'required|date',
        ], [
            'warehouse_id.required' => 'Gudang tujuan penerimaan wajib dipilih!',
            'do_number.required' => 'Nomor DO / Surat Jalan Telkomsel wajib diisi!',
            'do_file.required' => 'Wajib mengunggah berkas bukti Surat Jalan fisik (PDF)!',
            'do_file.mimes' => 'Format berkas bukti harus berupa dokumen PDF (.pdf)!',
            'do_file.max' => 'Ukuran berkas PDF maksimal 10MB!',
            'received_date.required' => 'Tanggal penerimaan fisik wajib diisi!',
        ]);

        $po = DB::table('purchase_orders')->where('id', $id)->first();
        if (!$po) {
            return redirect()->route('procurement.index')->with('error', 'Data PO tidak ditemukan!');
        }

        if (!TerritoryScope::canAccessTap((int) $po->tap_id)) {
            return redirect()->route('procurement.index')->with('error', 'Akses ditolak: PO ini di luar cakupan wilayah Anda.');
        }

        if ($po->status === 'RECEIVED') {
            return redirect()->route('procurement.index')->with('error', 'PO ini sudah pernah diterima (RECEIVED)!');
        }

        if ($po->status === 'CANCELLED') {
            return redirect()->route('procurement.index')->with('error', 'PO ini sudah di-VOID sehingga tidak dapat diproses GR!');
        }

        $warehouseId = (int) $request->input('warehouse_id');
        if (!TerritoryScope::canAccessWarehouse($warehouseId)) {
            return redirect()->back()->with('error', 'Akses ditolak: Anda tidak memiliki otorisasi untuk gudang tujuan yang dipilih.');
        }

        $targetWh = DB::table('warehouses')->where('id', $warehouseId)->first();
        if (!$targetWh || (int) $targetWh->tap_id !== (int) $po->tap_id) {
            return redirect()->back()->with('error', 'Gudang tujuan harus berada pada TAP yang sama dengan PO.');
        }

        // Upload berkas DO (PDF)
        $doFilePath = null;
        if ($request->hasFile('do_file')) {
            $file = $request->file('do_file');
            $cleanDo = preg_replace('/[^A-Za-z0-9_-]/', '_', $request->input('do_number'));
            $filename = 'DO_' . $cleanDo . '_' . date('Ymd_His') . '.' . $file->getClientOriginalExtension();
            $doFilePath = $file->storeAs('attachments/gr', $filename, 'public');
        }

        $iccidEnabled = (bool) config('erp.enable_iccid', false);
        $receivedDate = $request->input('received_date', date('Y-m-d'));

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
                'do_number' => trim($request->input('do_number')),
                'do_file_path' => $doFilePath,
                'purchase_order_id' => $po->id,
                'tap_id' => $po->tap_id,
                'warehouse_id' => $targetWh->id,
                'received_date' => $receivedDate,
                'status' => 'CONFIRMED',
                'notes' => $request->input('notes', 'Penerimaan fisik barang ke ' . $targetWh->name),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($poItems as $item) {
                // Insert GR item detail
                DB::table('goods_receipt_items')->insert([
                    'goods_receipt_id' => $grId,
                    'product_id' => $item->product_id,
                    'quantity_received' => $item->quantity,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $iccidsToRecord = [];
                if ($iccidEnabled && $item->tracking_method === 'ICCID') {
                    $customIccids = [];

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

                    $pastedText = $request->input("iccids_{$item->product_id}_paste");
                    if (!empty($pastedText)) {
                        $lines = preg_split('/[\r\n,]+/', trim($pastedText));
                        foreach ($lines as $line) {
                            $clean = trim($line);
                            if (!empty($clean)) $customIccids[] = $clean;
                        }
                    }

                    $needed = (int) $item->quantity;
                    if (count($customIccids) < $needed) {
                        $rangeStart = $request->input("iccids_{$item->product_id}_range_start");
                        $basePrefix = !empty($rangeStart) && is_numeric($rangeStart)
                            ? (string) $rangeStart
                            : '89621000' . date('ymd') . str_pad($item->product_id, 2, '0', STR_PAD_LEFT);

                        $startNumber = (int) substr($basePrefix, -5);
                        $prefixPart = substr($basePrefix, 0, -5);

                        while (count($customIccids) < $needed) {
                            $customIccids[] = $prefixPart . str_pad($startNumber, 5, '0', STR_PAD_LEFT);
                            $startNumber++;
                        }
                    }

                    $iccidsToRecord = array_slice($customIccids, 0, $needed);
                }

                // 2. Catat stok masuk atomik melalui StockService (stock_balances + stock_mutations + iccid jika ON)
                StockService::recordIn(
                    warehouseId: (int) $targetWh->id,
                    productId: (int) $item->product_id,
                    quantity: (int) $item->quantity,
                    docType: 'GR',
                    docNumber: $grNumber,
                    date: $receivedDate,
                    notes: "Penerimaan fisik barang (Ref: {$po->doc_number} | DO: " . trim($request->input('do_number')) . ")",
                    iccids: $iccidsToRecord,
                    unitCostPrice: (float) $item->cost_price
                );
            }

            // 3. Update PO status to RECEIVED
            DB::table('purchase_orders')->where('id', $id)->update([
                'status' => 'RECEIVED',
                'updated_at' => now(),
            ]);

            DB::commit();
            return redirect()->route('procurement.index')->with('success', "Dokumen [{$grNumber}] terbit! Stok resmi tercatat di {$targetWh->name} & buku mutasi diperbarui.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses GR: ' . $e->getMessage());
        }
    }

    /**
     * Halaman Purchase Retur (Retur Pembelian ke Vendor/Principal)
     */
    public function returns(Request $request)
    {
        // Ambil daftar purchase order yang sudah diterima (RECEIVED) sebagai referensi retur
        $receivedPos = DB::table('purchase_orders')
            ->join('taps', 'purchase_orders.tap_id', '=', 'taps.id')
            ->whereIn('purchase_orders.status', ['RECEIVED', 'COMPLETED'])
            ->select('purchase_orders.*', 'taps.name as tap_name')
            ->orderByDesc('purchase_orders.id')
            ->get();

        // Sample / initial records Purchase Returns untuk visualisasi Clean Corporate ERP
        $sampleReturns = [
            (object) [
                'id' => 1,
                'doc_number' => 'PR/202611/0001',
                'return_date' => '2026-11-10',
                'ref_po_number' => 'PO/202611/0001',
                'vendor_name' => 'PT Telekomunikasi Selular (Telkomsel Regional)',
                'warehouse_name' => 'Gudang Utama TAP Depok',
                'total_qty' => 150,
                'total_amount' => 4500000,
                'reason' => 'Kerusakan fisik segel voucher & masa kadaluarsa batch pendek',
                'status' => 'APPROVED',
                'status_badge' => 'emerald',
            ],
            (object) [
                'id' => 2,
                'doc_number' => 'PR/202611/0002',
                'return_date' => '2026-11-11',
                'ref_po_number' => 'PO/202611/0002',
                'vendor_name' => 'PT Distribusi Logistik Perdana',
                'warehouse_name' => 'Gudang Utama TAP Bogor Kota',
                'total_qty' => 50,
                'total_amount' => 1250000,
                'reason' => 'Chip perdana tidak terbaca sistem HLR saat sampling QC masuk',
                'status' => 'PENDING_APPROVAL',
                'status_badge' => 'amber',
            ],
            (object) [
                'id' => 3,
                'doc_number' => 'PR/202611/0003',
                'return_date' => '2026-11-12',
                'ref_po_number' => 'PO/202611/0003',
                'vendor_name' => 'PT Sarana Distribusi Nusantara',
                'warehouse_name' => 'Gudang Satelit TAP Cibinong',
                'total_qty' => 25,
                'total_amount' => 750000,
                'reason' => 'Perbedaan barcode master karton dengan fisik barang',
                'status' => 'DRAFT',
                'status_badge' => 'slate',
            ],
        ];

        $returns = collect($sampleReturns);

        $kpi = [
            'total_returns' => $returns->count(),
            'approved_count' => $returns->where('status', 'APPROVED')->count(),
            'pending_count' => $returns->where('status', 'PENDING_APPROVAL')->count(),
            'total_value' => $returns->sum('total_amount'),
        ];

        return view('procurement.returns', compact('returns', 'receivedPos', 'kpi'));
    }
}
