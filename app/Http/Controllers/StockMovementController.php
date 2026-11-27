<?php

namespace App\Http\Controllers;

use App\Services\TerritoryScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $sourceFilterWhId = $request->input('source_warehouse_id', 'ALL');
        $destFilterWhId = $request->input('destination_warehouse_id', 'ALL');
        $search = $request->input('search');

        // 1. Ambil data Stock Movement (Header) dengan join TAP, Cluster, Area
        $query = DB::table('stock_movements')
            ->join('warehouses as sw', 'stock_movements.source_warehouse_id', '=', 'sw.id')
            ->join('warehouses as dw', 'stock_movements.destination_warehouse_id', '=', 'dw.id')
            ->leftJoin('taps as stap', 'sw.tap_id', '=', 'stap.id')
            ->leftJoin('clusters as scls', 'stap.cluster_id', '=', 'scls.id')
            ->leftJoin('areas as sar', 'scls.area_id', '=', 'sar.id')
            ->leftJoin('taps as dtap', 'dw.tap_id', '=', 'dtap.id')
            ->leftJoin('clusters as dcls', 'dtap.cluster_id', '=', 'dcls.id')
            ->leftJoin('areas as dar', 'dcls.area_id', '=', 'dar.id')
            ->leftJoin('users as creator', 'stock_movements.created_by', '=', 'creator.id')
            ->select(
                'stock_movements.*',
                'sw.name as source_wh_name',
                'sw.code as source_wh_code',
                'sw.type as source_wh_type',
                'sw.tap_id as source_tap_id',
                'stap.name as source_tap_name',
                'scls.id as source_cluster_id',
                'scls.name as source_cluster_name',
                'sar.id as source_area_id',
                'sar.name as source_area_name',
                'dw.name as dest_wh_name',
                'dw.code as dest_wh_code',
                'dw.type as dest_wh_type',
                'dw.tap_id as dest_tap_id',
                'dtap.name as dest_tap_name',
                'dcls.id as dest_cluster_id',
                'dcls.name as dest_cluster_name',
                'dar.id as dest_area_id',
                'dar.name as dest_area_name',
                'creator.name as creator_name'
            );

        // Territory Scope: batasi gudang asal atau gudang tujuan yang dapat diakses user
        $scope = TerritoryScope::for(auth()->user());
        if (!$scope['is_full_access']) {
            if (empty($scope['warehouse_ids'])) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where(function ($q) use ($scope) {
                    $q->whereIn('stock_movements.source_warehouse_id', $scope['warehouse_ids'])
                      ->orWhereIn('stock_movements.destination_warehouse_id', $scope['warehouse_ids']);
                });
            }
        }

        if ($startDate) {
            $query->whereDate('stock_movements.movement_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('stock_movements.movement_date', '<=', $endDate);
        }
        if ($sourceFilterWhId && $sourceFilterWhId !== 'ALL') {
            $query->where('stock_movements.source_warehouse_id', $sourceFilterWhId);
        }
        if ($destFilterWhId && $destFilterWhId !== 'ALL') {
            $query->where('stock_movements.destination_warehouse_id', $destFilterWhId);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('stock_movements.doc_number', 'like', "%{$search}%")
                  ->orWhere('stock_movements.notes', 'like', "%{$search}%")
                  ->orWhere('sw.name', 'like', "%{$search}%")
                  ->orWhere('dw.name', 'like', "%{$search}%");
            });
        }

        $movementsRaw = $query->orderBy('stock_movements.id', 'desc')->get();

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

        // 4. Master Gudang & Produk (Terkunci TerritoryScope & Active Cluster)
        $whQuery = DB::table('warehouses')
            ->join('taps', 'warehouses.tap_id', '=', 'taps.id')
            ->join('clusters', 'taps.cluster_id', '=', 'clusters.id')
            ->join('areas', 'clusters.area_id', '=', 'areas.id')
            ->where('warehouses.status', 'ACTIVE')
            ->select(
                'warehouses.*',
                'taps.name as tap_name',
                'taps.cluster_id',
                'clusters.name as cluster_name',
                'clusters.area_id',
                'areas.name as area_name'
            );

        $warehouses = TerritoryScope::applyWarehouseScope($whQuery, 'warehouses.id')
            ->orderBy('warehouses.type')
            ->orderBy('warehouses.name')
            ->get();

        $iccidEnabled = (bool) config('erp.enable_iccid', false);

        $products = DB::table('products')
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get();

        if (!$iccidEnabled) {
            $products = $products->map(function ($p) {
                $p->tracking_method = 'QUANTITY';
                return $p;
            });
        }

        // 5. Saldo stok per warehouse & product (khusus gudang yang diizinkan)
        $allowedWhIds = $warehouses->pluck('id')->toArray();
        $stockBalances = DB::table('stock_balances')
            ->whereIn('warehouse_id', $allowedWhIds)
            ->select('warehouse_id', 'product_id', 'quantity')
            ->get();

        // 6. Unit ICCID berstatus AVAILABLE per warehouse & product (hanya jika fitur aktif)
        $availableIccids = collect();
        if ($iccidEnabled) {
            $availableIccids = DB::table('iccid_units')
                ->where('status', 'AVAILABLE')
                ->whereIn('warehouse_id', $allowedWhIds)
                ->select('id', 'iccid_number', 'warehouse_id', 'product_id')
                ->orderBy('iccid_number')
                ->get();
        }

        // Master Wilayah (Terkunci TerritoryScope & Active Cluster)
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
        )->get();

        return view('stock_movement.index', compact(
            'movements',
            'warehouses',
            'products',
            'stockBalances',
            'availableIccids',
            'areas',
            'clusters',
            'taps',
            'startDate',
            'endDate',
            'sourceFilterWhId',
            'destFilterWhId',
            'search',
            'iccidEnabled'
        ));
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

        // Territory Scope Security Check
        if (!TerritoryScope::canAccessWarehouse($sourceWhId)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses wilayah untuk memindahkan stok dari gudang asal ini!');
        }

        if (empty($itemsData)) {
            return redirect()->back()->with('error', 'Pilih minimal 1 item produk untuk dipindahkan!');
        }

        $sourceWh = DB::table('warehouses')
            ->join('taps', 'warehouses.tap_id', '=', 'taps.id')
            ->join('clusters', 'taps.cluster_id', '=', 'clusters.id')
            ->where('warehouses.id', $sourceWhId)
            ->select('warehouses.*', 'taps.cluster_id', 'clusters.name as cluster_name', 'clusters.area_id')
            ->first();

        $destWh = DB::table('warehouses')
            ->join('taps', 'warehouses.tap_id', '=', 'taps.id')
            ->join('clusters', 'taps.cluster_id', '=', 'clusters.id')
            ->where('warehouses.id', $destWhId)
            ->select('warehouses.*', 'taps.cluster_id', 'clusters.name as cluster_name', 'clusters.area_id')
            ->first();

        if (!$sourceWh || !$destWh) {
            return redirect()->back()->with('error', 'Data gudang tidak valid!');
        }

        // Validasi Cluster: Mutasi stok (SM) hanya diperbolehkan antar gudang dalam 1 Cluster yang sama
        if ($sourceWh->cluster_id !== $destWh->cluster_id) {
            return redirect()->back()->with('error', "Gudang tujuan harus berada dalam Cluster yang sama ({$sourceWh->cluster_name}) dengan gudang asal!");
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
                'created_by' => auth()->id(),
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

                $isIccidTracking = config('erp.enable_iccid', false) && ($prod->tracking_method === 'ICCID');

                if (!$isIccidTracking) {
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

    /**
     * Unduh Template Excel SM Multi (2 Sheet):
     * Sheet 1: Template Input (id_item | qty | warehouse_tujuan)
     * Sheet 2: Daftar Item Produk beserta ID di Cluster / Wilayah User
     */
    public function downloadMultiTemplate(Request $request)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // -------------------------------------------------------------
        // SHEET 1: FORM INPUT SM MULTI
        // -------------------------------------------------------------
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Template SM Multi');

        // Header Style
        $sheet1->setCellValue('A1', 'id_item');
        $sheet1->setCellValue('B1', 'qty');
        $sheet1->setCellValue('C1', 'warehouse_tujuan');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0F766E'] // Teal / Cyan dark
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
            ]
        ];
        $sheet1->getStyle('A1:C1')->applyFromArray($headerStyle);

        // Contoh Baris Panduan Dummy
        $sheet1->setCellValue('A2', 1);
        $sheet1->setCellValue('B2', 50);
        $sheet1->setCellValue('C2', 4); // ID Warehouse tujuan

        $sheet1->getColumnDimension('A')->setWidth(15);
        $sheet1->getColumnDimension('B')->setWidth(15);
        $sheet1->getColumnDimension('C')->setWidth(25);

        // -------------------------------------------------------------
        // SHEET 2: DAFTAR PRODUK (ID & NAMA)
        // -------------------------------------------------------------
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Daftar Produk & ID');

        $sheet2->setCellValue('A1', 'ID Produk');
        $sheet2->setCellValue('B1', 'SKU');
        $sheet2->setCellValue('C1', 'Nama Produk');
        $sheet2->setCellValue('D1', 'Tipe');
        $sheet2->setCellValue('E1', 'Satuan (UOM)');

        $sheet2->getStyle('A1:E1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E293B'] // Slate 800
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
            ]
        ]);

        $products = DB::table('products')
            ->where('status', 'ACTIVE')
            ->orderBy('id')
            ->get();

        $rowIdx = 2;
        foreach ($products as $p) {
            $sheet2->setCellValue("A{$rowIdx}", $p->id);
            $sheet2->setCellValue("B{$rowIdx}", $p->sku);
            $sheet2->setCellValue("C{$rowIdx}", $p->name);
            $sheet2->setCellValue("D{$rowIdx}", $p->type);
            $sheet2->setCellValue("E{$rowIdx}", $p->uom);
            $rowIdx++;
        }

        $sheet2->getColumnDimension('A')->setWidth(12);
        $sheet2->getColumnDimension('B')->setWidth(20);
        $sheet2->getColumnDimension('C')->setWidth(40);
        $sheet2->getColumnDimension('D')->setWidth(15);
        $sheet2->getColumnDimension('E')->setWidth(15);

        // -------------------------------------------------------------
        // SHEET 3 (BONUS BANTUAN): DAFTAR GUDANG TUJUAN SESUAI AREA
        // -------------------------------------------------------------
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Daftar Gudang Tujuan');

        $sheet3->setCellValue('A1', 'ID Gudang');
        $sheet3->setCellValue('B1', 'Kode');
        $sheet3->setCellValue('C1', 'Nama Gudang');
        $sheet3->setCellValue('D1', 'Tipe');
        $sheet3->setCellValue('E1', 'TAP');
        $sheet3->setCellValue('F1', 'Cluster');
        $sheet3->setCellValue('G1', 'Area');

        $sheet3->getStyle('A1:G1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '334155']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
            ]
        ]);

        $whQuery = DB::table('warehouses')
            ->join('taps', 'warehouses.tap_id', '=', 'taps.id')
            ->join('clusters', 'taps.cluster_id', '=', 'clusters.id')
            ->join('areas', 'clusters.area_id', '=', 'areas.id')
            ->where('warehouses.status', 'ACTIVE')
            ->select(
                'warehouses.id',
                'warehouses.code',
                'warehouses.name',
                'warehouses.type',
                'taps.name as tap_name',
                'clusters.name as cluster_name',
                'areas.name as area_name'
            );

        $warehouses = TerritoryScope::applyWarehouseScope($whQuery, 'warehouses.id')
            ->orderBy('warehouses.id')
            ->get();

        $whRowIdx = 2;
        foreach ($warehouses as $w) {
            $sheet3->setCellValue("A{$whRowIdx}", $w->id);
            $sheet3->setCellValue("B{$whRowIdx}", $w->code);
            $sheet3->setCellValue("C{$whRowIdx}", $w->name);
            $sheet3->setCellValue("D{$whRowIdx}", $w->type);
            $sheet3->setCellValue("E{$whRowIdx}", $w->tap_name);
            $sheet3->setCellValue("F{$whRowIdx}", $w->cluster_name);
            $sheet3->setCellValue("G{$whRowIdx}", $w->area_name);
            $whRowIdx++;
        }

        $sheet3->getColumnDimension('A')->setWidth(12);
        $sheet3->getColumnDimension('B')->setWidth(15);
        $sheet3->getColumnDimension('C')->setWidth(35);
        $sheet3->getColumnDimension('D')->setWidth(12);
        $sheet3->getColumnDimension('E')->setWidth(20);
        $sheet3->getColumnDimension('F')->setWidth(20);
        $sheet3->getColumnDimension('G')->setWidth(20);

        // Balikkan sheet aktif ke Sheet 1
        $spreadsheet->setActiveSheetIndex(0);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'Template_SM_Multi_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Preview & Validasi File Upload SM Multi (JSON response untuk tabel preview)
     */
    public function previewMulti(Request $request)
    {
        $request->validate([
            'source_warehouse_id' => 'required|exists:warehouses,id',
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $sourceWhId = (int)$request->input('source_warehouse_id');

        if (!TerritoryScope::canAccessWarehouse($sourceWhId)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk memindahkan stok dari gudang asal ini.'
            ], 403);
        }

        $sourceWh = DB::table('warehouses')
            ->join('taps', 'warehouses.tap_id', '=', 'taps.id')
            ->join('clusters', 'taps.cluster_id', '=', 'clusters.id')
            ->where('warehouses.id', $sourceWhId)
            ->select('warehouses.*', 'taps.cluster_id', 'clusters.area_id')
            ->first();

        $file = $request->file('file');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        if (empty($rows) || count($rows) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'File kosong atau format tidak sesuai template.'
            ], 422);
        }

        // Header check (case-insensitive & trim)
        $header = array_map(fn($h) => strtolower(trim((string)$h)), $rows[0]);
        $idItemIdx = array_search('id_item', $header);
        $qtyIdx = array_search('qty', $header);
        $destIdx = array_search('warehouse_tujuan', $header);

        if ($idItemIdx === false || $qtyIdx === false || $destIdx === false) {
            return response()->json([
                'success' => false,
                'message' => 'Kolom header tidak cocok! Harus memuat: id_item, qty, warehouse_tujuan'
            ], 422);
        }

        // Cache Master Produk & Gudang untuk efisiensi
        $allProducts = DB::table('products')->where('status', 'ACTIVE')->get()->keyBy('id');
        $allWarehouses = DB::table('warehouses')
            ->join('taps', 'warehouses.tap_id', '=', 'taps.id')
            ->join('clusters', 'taps.cluster_id', '=', 'clusters.id')
            ->where('warehouses.status', 'ACTIVE')
            ->select('warehouses.*', 'taps.cluster_id', 'clusters.area_id', 'taps.name as tap_name')
            ->get()
            ->keyBy('id');

        // Ambil stok terkini di gudang asal
        $stockBalances = DB::table('stock_balances')
            ->where('warehouse_id', $sourceWhId)
            ->pluck('quantity', 'product_id')
            ->toArray();

        $previewRows = [];
        $hasErrors = false;
        $totalItems = 0;
        $totalPcs = 0;

        // Akumulasi Qty yang diminta per product_id untuk deteksi over-capacity stok
        $demandedQtyByProduct = [];

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $rawItemId = trim((string)($row[$idItemIdx] ?? ''));
            $rawQty = trim((string)($row[$qtyIdx] ?? ''));
            $rawDestWhId = trim((string)($row[$destIdx] ?? ''));

            // Abaikan baris kosong
            if ($rawItemId === '' && $rawQty === '' && $rawDestWhId === '') {
                continue;
            }

            $lineNum = $i + 1;
            $errors = [];
            $itemId = (int)$rawItemId;
            $qty = (int)$rawQty;
            $destWhId = (int)$rawDestWhId;

            // 1. Validasi Item
            $product = $allProducts->get($itemId);
            if (!$product) {
                $errors[] = "ID Item [{$rawItemId}] tidak ditemukan / non-aktif";
            }

            // 2. Validasi Qty
            if ($qty <= 0) {
                $errors[] = "Qty harus lebih dari 0";
            }

            // 3. Validasi Gudang Tujuan
            $destWh = $allWarehouses->get($destWhId);
            if (!$destWh) {
                $errors[] = "ID Gudang Tujuan [{$rawDestWhId}] tidak ditemukan";
            } elseif ($destWhId === $sourceWhId) {
                $errors[] = "Gudang tujuan sama dengan gudang asal";
            } elseif ($destWh->cluster_id !== $sourceWh->cluster_id) {
                $errors[] = "Gudang tujuan beda Cluster dengan gudang asal";
            }

            if ($product && $qty > 0) {
                $demandedQtyByProduct[$itemId] = ($demandedQtyByProduct[$itemId] ?? 0) + $qty;
            }

            $previewRows[] = [
                'line' => $lineNum,
                'item_id' => $itemId,
                'sku' => $product->sku ?? '-',
                'product_name' => $product->name ?? "ID: {$rawItemId} (Tidak Diketahui)",
                'qty' => $qty,
                'dest_wh_id' => $destWhId,
                'dest_wh_name' => $destWh ? "[{$destWh->type}] {$destWh->name} ({$destWh->tap_name})" : "ID: {$rawDestWhId}",
                'available_stock' => 0, // diupdate di bawah
                'is_valid' => false,
                'errors' => $errors,
            ];
        }

        // Cek kecukupan stok asal (memperhitungkan total demand seluruh baris)
        foreach ($previewRows as &$pRow) {
            $pid = $pRow['item_id'];
            $currentStock = (int)($stockBalances[$pid] ?? 0);
            $totalDemand = (int)($demandedQtyByProduct[$pid] ?? 0);
            $pRow['available_stock'] = $currentStock;

            if ($totalDemand > $currentStock) {
                $pRow['errors'][] = "Stok gudang asal tidak cukup! Butuh total {$totalDemand} pcs, stok tersedia {$currentStock} pcs";
            }

            if (empty($pRow['errors'])) {
                $pRow['is_valid'] = true;
                $totalItems++;
                $totalPcs += $pRow['qty'];
            } else {
                $pRow['is_valid'] = false;
                $hasErrors = true;
            }
        }

        return response()->json([
            'success' => true,
            'source_warehouse_id' => $sourceWhId,
            'source_warehouse_name' => $sourceWh->name,
            'has_errors' => $hasErrors,
            'total_rows' => count($previewRows),
            'valid_rows_count' => count(array_filter($previewRows, fn($r) => $r['is_valid'])),
            'total_pcs' => $totalPcs,
            'rows' => $previewRows,
        ]);
    }

    /**
     * Eksekusi Transaksi Atomik SM Multi (Batch / Group by Destination)
     * Menggunakan Pessimistic Row Locking & Transaksi DB (All or Nothing)
     */
    public function storeMulti(Request $request)
    {
        $request->validate([
            'source_warehouse_id' => 'required|exists:warehouses,id',
            'movement_date' => 'required|date',
            'notes' => 'nullable|string|max:255',
            'rows' => 'required|array|min:1',
            'rows.*.item_id' => 'required|integer',
            'rows.*.qty' => 'required|integer|min:1',
            'rows.*.dest_wh_id' => 'required|integer|different:source_warehouse_id',
        ]);

        $sourceWhId = (int)$request->input('source_warehouse_id');
        $movementDate = $request->input('movement_date', date('Y-m-d'));
        $notes = $request->input('notes', 'Transfer Stok Massal (SM Multi)');
        $rows = $request->input('rows');

        if (!TerritoryScope::canAccessWarehouse($sourceWhId)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Anda tidak memiliki otoritas atas gudang asal ini.'
            ], 403);
        }

        $sourceWh = DB::table('warehouses')
            ->join('taps', 'warehouses.tap_id', '=', 'taps.id')
            ->join('clusters', 'taps.cluster_id', '=', 'clusters.id')
            ->where('warehouses.id', $sourceWhId)
            ->select('warehouses.*', 'taps.cluster_id', 'clusters.area_id')
            ->first();

        // Group rows per gudang tujuan (1 dokumen SM dibuat per 1 rute gudang tujuan)
        $groupedByDest = [];
        $totalDemandByProduct = [];

        foreach ($rows as $r) {
            $destId = (int)$r['dest_wh_id'];
            $groupedByDest[$destId][] = [
                'item_id' => (int)$r['item_id'],
                'qty' => (int)$r['qty'],
            ];
            $totalDemandByProduct[(int)$r['item_id']] = ($totalDemandByProduct[(int)$r['item_id']] ?? 0) + (int)$r['qty'];
        }

        DB::beginTransaction();
        try {
            // 1. Lock saldo stok gudang asal & validasi final stok
            foreach ($totalDemandByProduct as $prodId => $demandedQty) {
                $sourceBalanceRow = DB::table('stock_balances')
                    ->where('warehouse_id', $sourceWhId)
                    ->where('product_id', $prodId)
                    ->lockForUpdate()
                    ->first();

                $availableQty = $sourceBalanceRow ? (int)$sourceBalanceRow->quantity : 0;
                if ($availableQty < $demandedQty) {
                    $prod = DB::table('products')->find($prodId);
                    $prodName = $prod ? "[{$prod->sku}] {$prod->name}" : "ID {$prodId}";
                    throw new \Exception("Stok tidak mencukupi untuk item {$prodName}! Tersedia {$availableQty} pcs, dibutuhkan {$demandedQty} pcs.");
                }
            }

            $createdDocs = [];
            $totalCreatedSm = 0;
            $totalMovedPcs = 0;

            // 2. Buat dokumen SM per tujuan
            foreach ($groupedByDest as $destWhId => $items) {
                $destWh = DB::table('warehouses')
                    ->join('taps', 'warehouses.tap_id', '=', 'taps.id')
                    ->join('clusters', 'taps.cluster_id', '=', 'clusters.id')
                    ->where('warehouses.id', $destWhId)
                    ->select('warehouses.*', 'taps.cluster_id', 'clusters.area_id')
                    ->first();

                if (!$destWh || $destWh->cluster_id !== $sourceWh->cluster_id) {
                    throw new \Exception("Gudang tujuan ID {$destWhId} tidak valid atau berbeda Cluster wilayah!");
                }

                $nextNum = DB::table('stock_movements')->count() + 1;
                $docNumber = 'SM/' . date('Ym') . '/' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

                $smId = DB::table('stock_movements')->insertGetId([
                    'doc_type' => 'SM',
                    'doc_number' => $docNumber,
                    'source_warehouse_id' => $sourceWhId,
                    'destination_warehouse_id' => $destWhId,
                    'movement_date' => $movementDate,
                    'status' => 'COMPLETED',
                    'notes' => $notes . " (Batch Auto-SM)",
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($items as $item) {
                    $productId = $item['item_id'];
                    $qty = $item['qty'];

                    // Potong saldo asal
                    $sourceBalance = DB::table('stock_balances')
                        ->where('warehouse_id', $sourceWhId)
                        ->where('product_id', $productId)
                        ->first();

                    $newSourceBalance = (int)$sourceBalance->quantity - $qty;
                    DB::table('stock_balances')
                        ->where('warehouse_id', $sourceWhId)
                        ->where('product_id', $productId)
                        ->update([
                            'quantity' => $newSourceBalance,
                            'updated_at' => now(),
                        ]);

                    // Tambah saldo tujuan
                    $destBalance = DB::table('stock_balances')
                        ->where('warehouse_id', $destWhId)
                        ->where('product_id', $productId)
                        ->lockForUpdate()
                        ->first();

                    if ($destBalance) {
                        $newDestBalance = (int)$destBalance->quantity + $qty;
                        DB::table('stock_balances')
                            ->where('warehouse_id', $destWhId)
                            ->where('product_id', $productId)
                            ->update([
                                'quantity' => $newDestBalance,
                                'updated_at' => now(),
                            ]);
                    } else {
                        $newDestBalance = $qty;
                        DB::table('stock_balances')->insert([
                            'warehouse_id' => $destWhId,
                            'product_id' => $productId,
                            'quantity' => $newDestBalance,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    // Catat mutasi OUT & IN
                    DB::table('stock_mutations')->insert([
                        'date' => $movementDate,
                        'warehouse_id' => $sourceWhId,
                        'product_id' => $productId,
                        'doc_type' => 'SM',
                        'doc_number' => $docNumber,
                        'mutation_type' => 'OUT',
                        'quantity' => $qty,
                        'balance_after' => $newSourceBalance,
                        'notes' => "Transfer SM Multi {$qty} pcs ke {$destWh->name}",
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
                        'quantity' => $qty,
                        'balance_after' => $newDestBalance,
                        'notes' => "Terima SM Multi {$qty} pcs dari {$sourceWh->name}",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Insert movement items
                    DB::table('stock_movement_items')->insert([
                        'stock_movement_id' => $smId,
                        'product_id' => $productId,
                        'quantity' => $qty,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $totalMovedPcs += $qty;
                }

                $createdDocs[] = $docNumber;
                $totalCreatedSm++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil memproses {$totalCreatedSm} dokumen Transfer Stok (" . implode(', ', $createdDocs) . ") senilai total {$totalMovedPcs} pcs!",
                'docs' => $createdDocs,
                'total_docs' => $totalCreatedSm,
                'total_pcs' => $totalMovedPcs,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Transaksi Dibatalkan: ' . $e->getMessage()
            ], 422);
        }
    }
}

