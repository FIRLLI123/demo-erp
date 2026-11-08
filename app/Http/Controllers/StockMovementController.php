<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockMovementController extends Controller
{
    public function index()
    {
        // 1. Ambil data Stock Movement (Header)
        $movementsRaw = DB::table('stock_movements')
            ->join('warehouses as sw', 'stock_movements.source_warehouse_id', '=', 'sw.id')
            ->join('warehouses as dw', 'stock_movements.destination_warehouse_id', '=', 'dw.id')
            ->select(
                'stock_movements.*',
                'sw.name as source_wh_name',
                'sw.code as source_wh_code',
                'sw.type as source_wh_type',
                'dw.name as dest_wh_name',
                'dw.code as dest_wh_code',
                'dw.type as dest_wh_type'
            )
            ->orderBy('stock_movements.id', 'desc')
            ->get();

        // 2. Ambil items untuk setiap movement
        $itemsBySm = [];
        $smIds = $movementsRaw->pluck('id')->toArray();
        if (!empty($smIds)) {
            $items = DB::table('stock_movement_items')
                ->join('products', 'stock_movement_items.product_id', '=', 'products.id')
                ->whereIn('stock_movement_items.stock_movement_id', $smIds)
                ->select(
                    'stock_movement_items.*',
                    'products.name as product_name',
                    'products.sku',
                    'products.type as product_type',
                    'products.tracking_method'
                )
                ->get();

            foreach ($items as $item) {
                $itemsBySm[$item->stock_movement_id][] = $item;
            }
        }

        // 3. Ambil ICCID yang dipindahkan per movement
        $iccidsByDoc = [];
        $docNumbers = $movementsRaw->pluck('doc_number')->toArray();
        if (!empty($docNumbers)) {
            $iccids = DB::table('iccid_units')
                ->where('last_doc_type', 'SM')
                ->whereIn('last_doc_number', $docNumbers)
                ->select('iccid_number', 'product_id', 'last_doc_number')
                ->get();

            foreach ($iccids as $iccid) {
                $iccidsByDoc[$iccid->last_doc_number][] = $iccid->iccid_number;
            }
        }

        $movements = $movementsRaw->map(function ($sm) use ($itemsBySm, $iccidsByDoc) {
            $sm->items = $itemsBySm[$sm->id] ?? [];
            $sm->total_qty = collect($sm->items)->sum('quantity');
            $sm->has_iccid = collect($sm->items)->contains('tracking_method', 'ICCID');
            $sm->iccids = $iccidsByDoc[$sm->doc_number] ?? [];
            return $sm;
        });

        // 4. Master Gudang & Produk
        $warehouses = DB::table('warehouses')
            ->where('status', 'ACTIVE')
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $products = DB::table('products')
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get();

        // 5. Saldo stok per warehouse & product (untuk validasi & live view di client)
        $stockBalances = DB::table('stock_balances')
            ->select('warehouse_id', 'product_id', 'quantity')
            ->get();

        // 6. Unit ICCID berstatus AVAILABLE per warehouse & product
        $availableIccids = DB::table('iccid_units')
            ->where('status', 'AVAILABLE')
            ->select('id', 'iccid_number', 'warehouse_id', 'product_id')
            ->orderBy('iccid_number')
            ->get();

        return view('stock_movement.index', compact('movements', 'warehouses', 'products', 'stockBalances', 'availableIccids'));
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_transfer_iccid_sm.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ICCID']);
            fputcsv($file, ['896210000001']);
            fputcsv($file, ['896210000002']);
            fputcsv($file, ['896210000003']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function store(Request $request)
    {
        $sourceWhId = (int)$request->input('source_warehouse_id');
        $destWhId = (int)$request->input('destination_warehouse_id');
        $movementDate = $request->input('movement_date', date('Y-m-d'));
        $notes = $request->input('notes', 'Transfer Stok Antar Gudang');
        $itemsData = $request->input('items', []);

        if (!$sourceWhId || !$destWhId) {
            return redirect()->back()->with('error', 'Pilih Gudang Asal dan Gudang Tujuan!');
        }

        if ($sourceWhId === $destWhId) {
            return redirect()->back()->with('error', 'Gudang Asal dan Gudang Tujuan tidak boleh sama!');
        }

        if (empty($itemsData)) {
            return redirect()->back()->with('error', 'Pilih minimal 1 item produk untuk dipindahkan!');
        }

        $sourceWh = DB::table('warehouses')->where('id', $sourceWhId)->first();
        $destWh = DB::table('warehouses')->where('id', $destWhId)->first();

        if (!$sourceWh || !$destWh) {
            return redirect()->back()->with('error', 'Data gudang tidak valid!');
        }

        DB::beginTransaction();
        try {
            // Generate No Dokumen SM
            $nextNum = DB::table('stock_movements')->count() + 1;
            $docNumber = 'SM/' . date('Ym') . '/' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

            $smId = DB::table('stock_movements')->insertGetId([
                'doc_type' => 'SM',
                'doc_number' => $docNumber,
                'source_warehouse_id' => $sourceWhId,
                'destination_warehouse_id' => $destWhId,
                'movement_date' => $movementDate,
                'status' => 'COMPLETED',
                'notes' => $notes,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($itemsData as $idx => $row) {
                if (empty($row['product_id'])) {
                    continue;
                }

                $productId = (int)$row['product_id'];
                $prod = DB::table('products')->where('id', $productId)->first();

                if (!$prod) continue;

                // 1. Cek saldo gudang sumber
                $currentSourceBalance = DB::table('stock_balances')
                    ->where('warehouse_id', $sourceWhId)
                    ->where('product_id', $productId)
                    ->value('quantity') ?? 0;

                if ($prod->tracking_method === 'QUANTITY') {
                    $requestedQty = max(1, (int)($row['quantity'] ?? 1));
                    if ($currentSourceBalance < $requestedQty) {
                        throw new \Exception("Stok {$prod->name} di {$sourceWh->name} tidak mencukupi! Tersedia: {$currentSourceBalance} pcs, Diminta: {$requestedQty} pcs.");
                    }

                    // Update Saldo Sumber
                    $newSourceBalance = $currentSourceBalance - $requestedQty;
                    DB::table('stock_balances')
                        ->where('warehouse_id', $sourceWhId)
                        ->where('product_id', $productId)
                        ->update(['quantity' => $newSourceBalance, 'updated_at' => now()]);

                    // Update Saldo Tujuan
                    $destBalanceRow = DB::table('stock_balances')
                        ->where('warehouse_id', $destWhId)
                        ->where('product_id', $productId)
                        ->first();

                    if ($destBalanceRow) {
                        $newDestBalance = $destBalanceRow->quantity + $requestedQty;
                        DB::table('stock_balances')
                            ->where('id', $destBalanceRow->id)
                            ->update(['quantity' => $newDestBalance, 'updated_at' => now()]);
                    } else {
                        $newDestBalance = $requestedQty;
                        DB::table('stock_balances')->insert([
                            'warehouse_id' => $destWhId,
                            'product_id' => $productId,
                            'quantity' => $newDestBalance,
                            'reserved_quantity' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    // Catat Kartu Stok (OUT & IN)
                    DB::table('stock_mutations')->insert([
                        'date' => $movementDate,
                        'warehouse_id' => $sourceWhId,
                        'product_id' => $productId,
                        'doc_type' => 'SM',
                        'doc_number' => $docNumber,
                        'mutation_type' => 'OUT',
                        'quantity' => $requestedQty,
                        'balance_after' => $newSourceBalance,
                        'notes' => "Transfer SM ke {$destWh->name}",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('stock_mutations')->insert([
                        'date' => $movementDate,
                        'warehouse_id' => $destWhId,
                        'product_id' => $productId,
                        'doc_type' => 'SM',
                        'doc_number' => $docNumber,
                        'mutation_type' => 'IN',
                        'quantity' => $requestedQty,
                        'balance_after' => $newDestBalance,
                        'notes' => "Terima SM dari {$sourceWh->name}",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                } else {
                    // TRACKING METHOD: ICCID
                    $inputMode = $row['iccid_mode'] ?? 'SELECT';
                    $targetIccids = [];

                    if ($inputMode === 'SELECT') {
                        $targetIccids = $row['selected_iccids'] ?? [];
                    } elseif ($inputMode === 'UPLOAD') {
                        if ($request->hasFile("items.{$idx}.iccid_file")) {
                            $file = $request->file("items.{$idx}.iccid_file");
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
                    } elseif ($inputMode === 'PASTE') {
                        $rawText = $row['iccid_text'] ?? '';
                        $lines = preg_split('/[\r\n,]+/', $rawText);
                        foreach ($lines as $line) {
                            $clean = trim($line);
                            if (!empty($clean)) {
                                $targetIccids[] = $clean;
                            }
                        }
                    }

                    $targetIccids = array_values(array_unique(array_filter($targetIccids)));

                    if (empty($targetIccids)) {
                        throw new \Exception("Harap masukkan atau pilih nomor ICCID untuk produk {$prod->name}!");
                    }

                    $actualQty = count($targetIccids);
                    $requestedQty = $actualQty; // Kuantitas otomatis dihitung dari banyaknya ICCID

                    // Validasi kepemilikan dan status ICCID di gudang sumber
                    $validCount = DB::table('iccid_units')
                        ->where('warehouse_id', $sourceWhId)
                        ->where('product_id', $productId)
                        ->where('status', 'AVAILABLE')
                        ->whereIn('iccid_number', $targetIccids)
                        ->count();

                    if ($validCount !== $actualQty) {
                        throw new \Exception("Beberapa nomor ICCID tidak valid atau bukan status AVAILABLE di {$sourceWh->name}!");
                    }

                    // Pindahkan lokasi warehouse_id ICCID ke gudang tujuan
                    DB::table('iccid_units')
                        ->whereIn('iccid_number', $targetIccids)
                        ->update([
                            'warehouse_id' => $destWhId,
                            'last_doc_type' => 'SM',
                            'last_doc_number' => $docNumber,
                            'updated_at' => now(),
                        ]);

                    // Update Saldo Sumber
                    $newSourceBalance = max(0, $currentSourceBalance - $actualQty);
                    DB::table('stock_balances')
                        ->where('warehouse_id', $sourceWhId)
                        ->where('product_id', $productId)
                        ->update(['quantity' => $newSourceBalance, 'updated_at' => now()]);

                    // Update Saldo Tujuan
                    $destBalanceRow = DB::table('stock_balances')
                        ->where('warehouse_id', $destWhId)
                        ->where('product_id', $productId)
                        ->first();

                    if ($destBalanceRow) {
                        $newDestBalance = $destBalanceRow->quantity + $actualQty;
                        DB::table('stock_balances')
                            ->where('id', $destBalanceRow->id)
                            ->update(['quantity' => $newDestBalance, 'updated_at' => now()]);
                    } else {
                        $newDestBalance = $actualQty;
                        DB::table('stock_balances')->insert([
                            'warehouse_id' => $destWhId,
                            'product_id' => $productId,
                            'quantity' => $newDestBalance,
                            'reserved_quantity' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    // Catat Kartu Stok (OUT & IN)
                    DB::table('stock_mutations')->insert([
                        'date' => $movementDate,
                        'warehouse_id' => $sourceWhId,
                        'product_id' => $productId,
                        'doc_type' => 'SM',
                        'doc_number' => $docNumber,
                        'mutation_type' => 'OUT',
                        'quantity' => $actualQty,
                        'balance_after' => $newSourceBalance,
                        'notes' => "Transfer SM {$actualQty} ICCID ke {$destWh->name}",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('stock_mutations')->insert([
                        'date' => $movementDate,
                        'warehouse_id' => $destWhId,
                        'product_id' => $productId,
                        'doc_type' => 'SM',
                        'doc_number' => $docNumber,
                        'mutation_type' => 'IN',
                        'quantity' => $actualQty,
                        'balance_after' => $newDestBalance,
                        'notes' => "Terima SM {$actualQty} ICCID dari {$sourceWh->name}",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Simpan item movement
                DB::table('stock_movement_items')->insert([
                    'stock_movement_id' => $smId,
                    'product_id' => $productId,
                    'quantity' => $requestedQty,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return redirect()->route('stock-movement.index')->with('success', "Dokumen {$docNumber} berhasil diproses. Stok telah berpindah dari {$sourceWh->name} ke {$destWh->name}!");

        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses Stock Movement: ' . $e->getMessage());
        }
    }
}

