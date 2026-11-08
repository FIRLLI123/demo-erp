<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RepackController extends Controller
{
    public function index()
    {
        // 1. Ambil transaksi Repack Out (RO)
        $roListRaw = DB::table('repacks')
            ->join('warehouses', 'repacks.warehouse_id', '=', 'warehouses.id')
            ->where('repacks.doc_type', 'RPKOUT')
            ->select('repacks.*', 'warehouses.name as warehouse_name', 'warehouses.type as warehouse_type')
            ->orderBy('repacks.id', 'desc')
            ->get();

        // 2. Ambil transaksi Repack In (RI)
        $riListRaw = DB::table('repacks')
            ->join('warehouses', 'repacks.warehouse_id', '=', 'warehouses.id')
            ->where('repacks.doc_type', 'RPKIN')
            ->select('repacks.*', 'warehouses.name as warehouse_name', 'warehouses.type as warehouse_type')
            ->orderBy('repacks.id', 'desc')
            ->get();

        // 3. Ambil items untuk seluruh repack
        $allRepackIds = $roListRaw->pluck('id')->merge($riListRaw->pluck('id'))->unique()->toArray();
        $itemsByRepack = [];
        if (!empty($allRepackIds)) {
            $items = DB::table('repack_items')
                ->join('products', 'repack_items.product_id', '=', 'products.id')
                ->whereIn('repack_items.repack_id', $allRepackIds)
                ->select(
                    'repack_items.*',
                    'products.name as product_name',
                    'products.sku',
                    'products.type as product_type',
                    'products.tracking_method'
                )
                ->get();

            foreach ($items as $item) {
                $itemsByRepack[$item->repack_id][] = $item;
            }
        }

        // 4. Ambil ICCID yang terkait dengan dokumen RO dan RI
        $allDocNumbers = $roListRaw->pluck('doc_number')->merge($riListRaw->pluck('doc_number'))->unique()->toArray();
        $iccidsByDoc = [];
        if (!empty($allDocNumbers)) {
            $iccids = DB::table('iccid_units')
                ->whereIn('last_doc_type', ['RPKOUT', 'RPKIN'])
                ->whereIn('last_doc_number', $allDocNumbers)
                ->select('iccid_number', 'product_id', 'last_doc_number')
                ->get();

            foreach ($iccids as $u) {
                $iccidsByDoc[$u->last_doc_number][] = $u->iccid_number;
            }
        }

        $roList = $roListRaw->map(function ($ro) use ($itemsByRepack, $iccidsByDoc) {
            $ro->items = $itemsByRepack[$ro->id] ?? [];
            $ro->iccids = $iccidsByDoc[$ro->doc_number] ?? [];
            return $ro;
        });

        $riList = $riListRaw->map(function ($ri) use ($itemsByRepack, $iccidsByDoc) {
            $ri->items = $itemsByRepack[$ri->id] ?? [];
            $ri->iccids = $iccidsByDoc[$ri->doc_number] ?? [];
            return $ri;
        });

        // 5. Daftar RO yang masih OPEN untuk dibuatkan RI
        $openRoList = $roList->filter(function ($ro) {
            return $ro->status === 'OPEN';
        })->values();

        // 6. Master data pendukung
        $warehouses = DB::table('warehouses')->where('status', 'ACTIVE')->get();

        // Produk Bahan 1 (Fisik 0K / ICCID)
        $rawPhysicalProducts = DB::table('products')
            ->where('status', 'ACTIVE')
            ->where('tracking_method', 'ICCID')
            ->where(function ($q) {
                $q->where('sku', 'like', 'RAW%')
                  ->orWhere('name', 'like', '%0K%')
                  ->orWhere('name', 'like', '%Blank%');
            })
            ->get();

        // Produk Bahan 2 (Bulk Saldo / Pulsa Kuota)
        $bulkProducts = DB::table('products')
            ->where('status', 'ACTIVE')
            ->where('tracking_method', 'QUANTITY')
            ->where(function ($q) {
                $q->where('sku', 'like', 'BULK%')
                  ->orWhere('name', 'like', '%BULK%')
                  ->orWhere('name', 'like', '%Saldo%');
            })
            ->get();

        // Produk Hasil Repack (Produk jadi SA / Voucher kuota)
        $outputProducts = DB::table('products')
            ->where('status', 'ACTIVE')
            ->where('sku', 'not like', 'RAW%')
            ->where('sku', 'not like', 'BULK%')
            ->get();

        // Saldo stok & unit ICCID available
        $stockBalances = DB::table('stock_balances')->get();
        $availableIccids = DB::table('iccid_units')
            ->where('status', 'AVAILABLE')
            ->select('id', 'iccid_number', 'warehouse_id', 'product_id')
            ->get();

        return view('inventory.repack', compact(
            'roList',
            'riList',
            'openRoList',
            'warehouses',
            'rawPhysicalProducts',
            'bulkProducts',
            'outputProducts',
            'stockBalances',
            'availableIccids'
        ));
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_iccid_repack_ro.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ICCID']);
            fputcsv($file, ['8962000000000001']);
            fputcsv($file, ['8962000000000002']);
            fputcsv($file, ['8962000000000003']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function storeRo(Request $request)
    {
        $warehouseId = (int)$request->input('warehouse_id');
        $repackDate = $request->input('repack_date', date('Y-m-d'));
        $notes = $request->input('notes', 'Pengeluaran Komponen Bahan Repack');

        // Komponen 1 (Fisik 0K)
        $comp1Id = (int)$request->input('comp1_product_id');
        $iccidMode = $request->input('comp1_iccid_mode', 'SELECT');

        // Komponen 2 (Bulk Kuota)
        $comp2Id = (int)$request->input('comp2_product_id');
        $comp2Qty = (int)$request->input('comp2_quantity');

        if (!$warehouseId || !$comp1Id || !$comp2Id) {
            return redirect()->back()->with('error', 'Lengkapi pilihan Gudang dan 2 Komponen Bahan Repack!');
        }

        if ($comp2Qty <= 0) {
            return redirect()->back()->with('error', 'Isi jumlah saldo/kuantitas Komponen 2 (NGRS Bulk)!');
        }

        $warehouse = DB::table('warehouses')->where('id', $warehouseId)->first();
        $prod1 = DB::table('products')->where('id', $comp1Id)->first();
        $prod2 = DB::table('products')->where('id', $comp2Id)->first();

        // Kumpulkan ICCID untuk Komponen 1
        $targetIccids = [];
        if ($iccidMode === 'SELECT') {
            $targetIccids = $request->input('comp1_selected_iccids', []);
        } elseif ($iccidMode === 'PASTE') {
            $rawText = $request->input('comp1_iccid_text', '');
            $lines = preg_split('/[\r\n,]+/', $rawText);
            foreach ($lines as $line) {
                $clean = trim($line);
                if (!empty($clean)) $targetIccids[] = $clean;
            }
        } elseif ($iccidMode === 'UPLOAD') {
            if ($request->hasFile('comp1_iccid_file')) {
                $file = $request->file('comp1_iccid_file');
                $handle = fopen($file->getRealPath(), 'r');
                $first = true;
                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    if ($first) { $first = false; continue; }
                    if (isset($data[0]) && trim($data[0]) !== '') {
                        $targetIccids[] = trim($data[0]);
                    }
                }
                fclose($handle);
            }
        }

        $targetIccids = array_values(array_unique(array_filter($targetIccids)));
        if (empty($targetIccids)) {
            return redirect()->back()->with('error', "Pilih atau upload nomor ICCID untuk komponen {$prod1->name}!");
        }

        $comp1Qty = count($targetIccids);

        // Hitung Nilai Finansial RO
        $cost1 = (float)$prod1->cost_price;
        $subtotal1 = $comp1Qty * $cost1;

        $cost2 = (float)$prod2->cost_price;
        $subtotal2 = $comp2Qty * $cost2;

        $totalRoAmount = $subtotal1 + $subtotal2;

        DB::beginTransaction();
        try {
            // Validasi saldo Komponen 1 (ICCID)
            $validIccidCount = DB::table('iccid_units')
                ->where('warehouse_id', $warehouseId)
                ->where('product_id', $comp1Id)
                ->where('status', 'AVAILABLE')
                ->whereIn('iccid_number', $targetIccids)
                ->count();

            if ($validIccidCount !== $comp1Qty) {
                throw new \Exception("Beberapa nomor ICCID tidak valid atau bukan status AVAILABLE di {$warehouse->name}!");
            }

            // Validasi saldo Komponen 2 (Bulk)
            $bulkStock = DB::table('stock_balances')
                ->where('warehouse_id', $warehouseId)
                ->where('product_id', $comp2Id)
                ->value('quantity') ?? 0;

            if ($bulkStock < $comp2Qty) {
                throw new \Exception("Saldo {$prod2->name} di {$warehouse->name} tidak mencukupi (Tersedia: {$bulkStock}, Diminta: {$comp2Qty})!");
            }

            // Generate No Dokumen RO
            $nextNum = DB::table('repacks')->where('doc_type', 'RPKOUT')->count() + 1;
            $docNumber = 'RO/' . date('Ym') . '/' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

            $repackId = DB::table('repacks')->insertGetId([
                'doc_type' => 'RPKOUT',
                'doc_number' => $docNumber,
                'warehouse_id' => $warehouseId,
                'repack_date' => $repackDate,
                'status' => 'OPEN',
                'total_amount' => $totalRoAmount,
                'notes' => $notes,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert 2 items input ke repack_items
            DB::table('repack_items')->insert([
                [
                    'repack_id' => $repackId,
                    'product_id' => $comp1Id,
                    'item_type' => 'INPUT',
                    'quantity' => $comp1Qty,
                    'unit_cost' => $cost1,
                    'subtotal' => $subtotal1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'repack_id' => $repackId,
                    'product_id' => $comp2Id,
                    'item_type' => 'INPUT',
                    'quantity' => $comp2Qty,
                    'unit_cost' => $cost2,
                    'subtotal' => $subtotal2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);

            // Update status ICCID menjadi RESERVED untuk repack ini
            DB::table('iccid_units')
                ->whereIn('iccid_number', $targetIccids)
                ->update([
                    'status' => 'RESERVED',
                    'last_doc_type' => 'RPKOUT',
                    'last_doc_number' => $docNumber,
                    'updated_at' => now(),
                ]);

            // Potong Saldo Komponen 1
            $currBal1 = DB::table('stock_balances')->where('warehouse_id', $warehouseId)->where('product_id', $comp1Id)->value('quantity') ?? 0;
            $newBal1 = max(0, $currBal1 - $comp1Qty);
            DB::table('stock_balances')->where('warehouse_id', $warehouseId)->where('product_id', $comp1Id)->update([
                'quantity' => $newBal1,
                'updated_at' => now()
            ]);

            // Potong Saldo Komponen 2
            $newBal2 = max(0, $bulkStock - $comp2Qty);
            DB::table('stock_balances')->where('warehouse_id', $warehouseId)->where('product_id', $comp2Id)->update([
                'quantity' => $newBal2,
                'updated_at' => now()
            ]);

            // Catat Kartu Stok (OUT)
            DB::table('stock_mutations')->insert([
                [
                    'date' => $repackDate,
                    'warehouse_id' => $warehouseId,
                    'product_id' => $comp1Id,
                    'doc_type' => 'RO',
                    'doc_number' => $docNumber,
                    'mutation_type' => 'OUT',
                    'quantity' => $comp1Qty,
                    'balance_after' => $newBal1,
                    'notes' => "Komponen Fisik Repack Out ({$docNumber})",
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'date' => $repackDate,
                    'warehouse_id' => $warehouseId,
                    'product_id' => $comp2Id,
                    'doc_type' => 'RO',
                    'doc_number' => $docNumber,
                    'mutation_type' => 'OUT',
                    'quantity' => $comp2Qty,
                    'balance_after' => $newBal2,
                    'notes' => "Komponen Saldo Bulk Repack Out ({$docNumber})",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);

            DB::commit();
            return redirect()->route('inventory.repack')->with('success', "Dokumen {$docNumber} (Total Nilai: Rp " . number_format($totalRoAmount, 0, ',', '.') . ") berhasil disimpan! Komponen siap diproses menjadi produk baru di tab Repack In (RI).");

        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses Repack Out: ' . $e->getMessage());
        }
    }

    public function storeRi(Request $request)
    {
        $roDocNumber = $request->input('ref_ro_doc_number');
        $outputProductId = (int)$request->input('output_product_id');
        $repackDate = $request->input('repack_date', date('Y-m-d'));
        $notes = $request->input('notes', 'Pemasukan Produk Hasil Repack');

        if (!$roDocNumber || !$outputProductId) {
            return redirect()->back()->with('error', 'Pilih Dokumen RO Referensi dan Produk Hasil Repack!');
        }

        $ro = DB::table('repacks')
            ->where('doc_type', 'RPKOUT')
            ->where('doc_number', $roDocNumber)
            ->first();

        if (!$ro) {
            return redirect()->back()->with('error', "Dokumen RO {$roDocNumber} tidak ditemukan!");
        }

        if ($ro->status !== 'OPEN') {
            return redirect()->back()->with('error', "Dokumen RO {$roDocNumber} sudah selesai di-repack sebelumnya!");
        }

        $outProd = DB::table('products')->where('id', $outputProductId)->first();
        if (!$outProd) {
            return redirect()->back()->with('error', 'Produk hasil tidak valid!');
        }

        // Ambil komponen fisik dari RO ini
        $comp1Item = DB::table('repack_items')
            ->join('products', 'repack_items.product_id', '=', 'products.id')
            ->where('repack_items.repack_id', $ro->id)
            ->where('products.tracking_method', 'ICCID')
            ->select('repack_items.*')
            ->first();

        $outputQty = $comp1Item ? (int)$comp1Item->quantity : 1;
        $totalAmount = (float)$ro->total_amount;
        $unitCost = $outputQty > 0 ? ($totalAmount / $outputQty) : $totalAmount;

        DB::beginTransaction();
        try {
            // Generate No Dokumen RI
            $nextNum = DB::table('repacks')->where('doc_type', 'RPKIN')->count() + 1;
            $docNumber = 'RI/' . date('Ym') . '/' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

            $riId = DB::table('repacks')->insertGetId([
                'doc_type' => 'RPKIN',
                'doc_number' => $docNumber,
                'ref_doc_type' => 'RPKOUT',
                'ref_doc_number' => $ro->doc_number,
                'warehouse_id' => $ro->warehouse_id,
                'repack_date' => $repackDate,
                'status' => 'COMPLETED',
                'total_amount' => $totalAmount,
                'notes' => $notes,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert item output ke repack_items
            DB::table('repack_items')->insert([
                'repack_id' => $riId,
                'product_id' => $outputProductId,
                'item_type' => 'OUTPUT',
                'quantity' => $outputQty,
                'unit_cost' => $unitCost,
                'subtotal' => $totalAmount,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update status RO menjadi COMPLETED
            DB::table('repacks')->where('id', $ro->id)->update([
                'status' => 'COMPLETED',
                'updated_at' => now(),
            ]);

            // Konversi unit ICCID dari bahan 0K menjadi Produk Baru
            DB::table('iccid_units')
                ->where('last_doc_type', 'RPKOUT')
                ->where('last_doc_number', $ro->doc_number)
                ->update([
                    'product_id' => $outputProductId,
                    'status' => 'AVAILABLE',
                    'last_doc_type' => 'RPKIN',
                    'last_doc_number' => $docNumber,
                    'updated_at' => now(),
                ]);

            // Tambah stok Produk Baru di gudang
            $destBalanceRow = DB::table('stock_balances')
                ->where('warehouse_id', $ro->warehouse_id)
                ->where('product_id', $outputProductId)
                ->first();

            if ($destBalanceRow) {
                $newBalance = $destBalanceRow->quantity + $outputQty;
                DB::table('stock_balances')
                    ->where('id', $destBalanceRow->id)
                    ->update(['quantity' => $newBalance, 'updated_at' => now()]);
            } else {
                $newBalance = $outputQty;
                DB::table('stock_balances')->insert([
                    'warehouse_id' => $ro->warehouse_id,
                    'product_id' => $outputProductId,
                    'quantity' => $newBalance,
                    'reserved_quantity' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Catat Kartu Stok (IN)
            DB::table('stock_mutations')->insert([
                'date' => $repackDate,
                'warehouse_id' => $ro->warehouse_id,
                'product_id' => $outputProductId,
                'doc_type' => 'RI',
                'doc_number' => $docNumber,
                'mutation_type' => 'IN',
                'quantity' => $outputQty,
                'balance_after' => $newBalance,
                'notes' => "Hasil Repack In dari {$ro->doc_number} (Nilai: Rp " . number_format($totalAmount, 0, ',', '.') . ")",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            return redirect()->route('inventory.repack')->with('success', "Dokumen {$docNumber} berhasil diterbitkan! {$outputQty} unit {$outProd->name} (Total Nilai: Rp " . number_format($totalAmount, 0, ',', '.') . ") resmi masuk ke stok gudang.");

        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses Repack In: ' . $e->getMessage());
        }
    }
}

