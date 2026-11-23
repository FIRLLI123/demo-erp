<?php

namespace App\Http\Controllers;

use App\Services\TerritoryScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $reportType = $request->input('type', 'SALES');
        $format = $request->input('format', 'summary');
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-d'));
        $areaId = $request->input('area_id', '');
        $clusterId = $request->input('cluster_id', '');
        $tapId = $request->input('tap_id', 'ALL');
        $warehouseId = $request->input('warehouse_id', 'ALL');

        // Master data filter dibatasi sesuai TerritoryScope user
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

        $whQuery = DB::table('warehouses')
            ->join('taps', 'warehouses.tap_id', '=', 'taps.id')
            ->join('clusters', 'taps.cluster_id', '=', 'clusters.id')
            ->where('warehouses.status', 'ACTIVE')
            ->select('warehouses.*', 'taps.cluster_id', 'clusters.area_id');

        if ($reportType === 'PO_GR') {
            $whQuery->where('warehouses.type', 'MAIN');
        }

        $warehouses = TerritoryScope::applyWarehouseScope(
            $whQuery->orderBy('warehouses.name'),
            'warehouses.id'
        )->get();

        $data = $this->getReportData($reportType, $startDate, $endDate, $tapId, $warehouseId, $format, $areaId, $clusterId);

        return view('reports.index', [
            'reportType' => $reportType,
            'format' => $format,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'areaId' => $areaId,
            'clusterId' => $clusterId,
            'tapId' => $tapId,
            'warehouseId' => $warehouseId,
            'areas' => $areas,
            'clusters' => $clusters,
            'taps' => $taps,
            'warehouses' => $warehouses,
            'records' => $data['records'],
            'summary' => $data['summary'],
            'title' => $data['title'],
        ]);
    }

    public function exportExcel(Request $request)
    {
        $reportType = $request->input('type', 'SALES');
        $format = $request->input('format', 'summary');
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-d'));
        $areaId = $request->input('area_id', '');
        $clusterId = $request->input('cluster_id', '');
        $tapId = $request->input('tap_id', 'ALL');
        $warehouseId = $request->input('warehouse_id', 'ALL');

        $data = $this->getReportData($reportType, $startDate, $endDate, $tapId, $warehouseId, $format, $areaId, $clusterId);

        $formatSuffix = ($reportType === 'PO_GR' && $format === 'detailed') ? 'Detail_Item' : 'Rekap_Ringkas';
        $fileName = "Laporan_{$reportType}_{$formatSuffix}_PT_Wahana_Putra_Yudha_" . date('Ymd_His') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($data) {
            $handle = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel
            fputs($handle, "\xEF\xBB\xBF");

            // Title and Company Header
            fputcsv($handle, ['PT WAHANA PUTRA YUDHA']);
            fputcsv($handle, ['TELKOMSEL AUTHORIZED PARTNER']);
            fputcsv($handle, [$data['title']]);
            fputcsv($handle, ['Tanggal Cetak', date('d-m-Y H:i:s')]);
            fputcsv($handle, []);

            // Columns Header
            fputcsv($handle, $data['headers']);

            // Rows
            foreach ($data['rows'] as $row) {
                fputcsv($handle, $row);
            }

            // Summary row if any
            if (!empty($data['excel_summary'])) {
                fputcsv($handle, []);
                fputcsv($handle, $data['excel_summary']);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function printPdf(Request $request)
    {
        $reportType = $request->input('type', 'SALES');
        $format = $request->input('format', 'summary');
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-d'));
        $areaId = $request->input('area_id', '');
        $clusterId = $request->input('cluster_id', '');
        $tapId = $request->input('tap_id', 'ALL');
        $warehouseId = $request->input('warehouse_id', 'ALL');

        $data = $this->getReportData($reportType, $startDate, $endDate, $tapId, $warehouseId, $format, $areaId, $clusterId);

        return view('reports.print-pdf', [
            'reportType' => $reportType,
            'format' => $format,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'title' => $data['title'],
            'headers' => $data['headers'],
            'rows' => $data['rows'],
            'summary' => $data['summary'],
            'records' => $data['records'],
        ]);
    }

    private function getReportData($type, $start, $end, $tapId, $whId, $format = 'summary', $areaId = '', $clusterId = '')
    {
        switch ($type) {
            case 'PO_GR':
                return $this->getPoGrData($start, $end, $tapId, $whId, $format, $areaId, $clusterId);
            case 'SM':
                return $this->getSmData($start, $end, $whId);
            case 'REPACK':
                return $this->getRepackData($start, $end, $whId);
            case 'SALES':
                return $this->getSalesData($start, $end, $tapId, $whId, $areaId, $clusterId);
            case 'AR':
                return $this->getArData($start, $end, $tapId);
            case 'CASH_BANK':
                return $this->getCashBankData($start, $end, $tapId);
            case 'STOCK':
                return $this->getStockData($start, $end, $tapId, $whId);
            default:
                return $this->getSalesData($start, $end, $tapId, $whId, $areaId, $clusterId);
        }
    }

    private function getSalesData($start, $end, $tapId, $whId, $areaId = '', $clusterId = '')
    {
        $query = DB::table('sales_orders')
            ->join('customers', 'sales_orders.customer_id', '=', 'customers.id')
            ->join('sales', 'sales_orders.sales_id', '=', 'sales.id')
            ->join('warehouses', 'sales_orders.warehouse_id', '=', 'warehouses.id')
            ->join('taps', 'sales_orders.tap_id', '=', 'taps.id')
            ->join('clusters', 'taps.cluster_id', '=', 'clusters.id')
            ->leftJoin('goods_deliveries', 'sales_orders.id', '=', 'goods_deliveries.sales_order_id')
            ->leftJoin('accounts_receivable', 'sales_orders.id', '=', 'accounts_receivable.sales_order_id')
            ->whereBetween('sales_orders.order_date', [$start, $end])
            ->select(
                'sales_orders.*',
                'customers.name as customer_name',
                'customers.code as customer_code',
                'sales.name as sales_name',
                'warehouses.name as warehouse_name',
                'goods_deliveries.doc_number as gd_number',
                'accounts_receivable.doc_number as ar_number',
                'accounts_receivable.status as ar_status'
            )
            ->orderBy('sales_orders.id', 'desc');

        TerritoryScope::applyTapScope($query, 'sales_orders.tap_id');
        TerritoryScope::applyWarehouseScope($query, 'sales_orders.warehouse_id');

        if (!empty($areaId)) {
            $query->where('clusters.area_id', $areaId);
        }
        if (!empty($clusterId)) {
            $query->where('taps.cluster_id', $clusterId);
        }
        if ($tapId !== 'ALL' && !empty($tapId)) $query->where('sales_orders.tap_id', $tapId);
        if ($whId !== 'ALL' && !empty($whId)) $query->where('sales_orders.warehouse_id', $whId);

        $records = $query->get();

        $rows = [];
        $totalNominal = 0;
        foreach ($records as $r) {
            $totalNominal += (float)$r->grand_total;
            $rows[] = [
                $r->doc_number,
                $r->order_date,
                $r->customer_name . " ({$r->customer_code})",
                $r->sales_name,
                $r->warehouse_name,
                $r->channel,
                $r->gd_number ?? '-',
                $r->ar_number ?? '-',
                $r->status,
                number_format($r->grand_total, 0, ',', '.'),
            ];
        }

        return [
            'title' => 'Laporan Rekapitulasi Penjualan & Pengiriman Barang (SO - GD - AR)',
            'headers' => ['No. SO', 'Tanggal', 'Toko / Pelanggan', 'Sales Kanvaser', 'Gudang Asal', 'Channel', 'No. GD (Surat Jalan)', 'No. Faktur AR', 'Status SO', 'Total Penjualan (Rp)'],
            'records' => $records,
            'rows' => $rows,
            'summary' => [
                'total_records' => count($records),
                'total_amount' => $totalNominal,
            ],
            'excel_summary' => ['TOTAL PENJUALAN', '', '', '', '', '', '', '', '', number_format($totalNominal, 0, ',', '.')],
        ];
    }

    private function getPoGrData($start, $end, $tapId, $whId = 'ALL', $format = 'summary', $areaId = '', $clusterId = '')
    {
        $query = DB::table('purchase_orders')
            ->join('taps', 'purchase_orders.tap_id', '=', 'taps.id')
            ->join('clusters', 'taps.cluster_id', '=', 'clusters.id')
            ->join('areas', 'clusters.area_id', '=', 'areas.id')
            ->leftJoin('vendors', 'purchase_orders.vendor_id', '=', 'vendors.id')
            ->leftJoin('users as creator', 'purchase_orders.created_by', '=', 'creator.id')
            ->leftJoin('goods_receipts', 'purchase_orders.id', '=', 'goods_receipts.purchase_order_id')
            ->leftJoin('users as receiver', 'goods_receipts.received_by', '=', 'receiver.id')
            ->leftJoin('warehouses', 'goods_receipts.warehouse_id', '=', 'warehouses.id')
            ->whereBetween('purchase_orders.order_date', [$start, $end])
            ->select(
                'purchase_orders.*',
                'taps.name as tap_name',
                'taps.code as tap_code',
                'clusters.name as cluster_name',
                'areas.name as area_name',
                'vendors.name as vendor_name',
                'vendors.code as vendor_code',
                'creator.name as creator_name',
                'goods_receipts.id as gr_id',
                'goods_receipts.doc_number as gr_number',
                'goods_receipts.received_date',
                'goods_receipts.created_at as gr_created_at',
                'goods_receipts.do_number',
                'goods_receipts.do_file_path',
                'receiver.name as receiver_name',
                'warehouses.name as destination_warehouse'
            )
            ->orderBy('purchase_orders.id', 'desc');

        TerritoryScope::applyTapScope($query, 'purchase_orders.tap_id');

        if (!empty($areaId)) {
            $query->where('clusters.area_id', $areaId);
        }
        if (!empty($clusterId)) {
            $query->where('taps.cluster_id', $clusterId);
        }
        if ($tapId !== 'ALL' && !empty($tapId)) $query->where('purchase_orders.tap_id', $tapId);
        if ($whId !== 'ALL' && !empty($whId)) {
            $whTapId = DB::table('warehouses')->where('id', $whId)->value('tap_id');
            $query->where(function($q) use ($whId, $whTapId) {
                $q->where('goods_receipts.warehouse_id', $whId);
                if ($whTapId) {
                    $q->orWhere(function($sq) use ($whTapId) {
                        $sq->whereNull('goods_receipts.id')->where('purchase_orders.tap_id', $whTapId);
                    });
                }
            });
        }

        $records = $query->get();

        $poIds = $records->pluck('id')->toArray();
        $grIds = $records->pluck('gr_id')->filter()->unique()->toArray();

        $itemsByPo = [];
        if (!empty($poIds)) {
            $poItems = DB::table('purchase_order_items')
                ->join('products', 'purchase_order_items.product_id', '=', 'products.id')
                ->whereIn('purchase_order_items.purchase_order_id', $poIds)
                ->select(
                    'purchase_order_items.*',
                    'products.sku',
                    'products.name as product_name',
                    'products.type as product_type'
                )
                ->get();

            $grItemsMap = [];
            if (!empty($grIds)) {
                $rawGrItems = DB::table('goods_receipt_items')
                    ->whereIn('goods_receipt_id', $grIds)
                    ->select('goods_receipt_id', 'product_id', 'quantity_received')
                    ->get();
                foreach ($rawGrItems as $gi) {
                    $grItemsMap[$gi->goods_receipt_id][$gi->product_id] = (int)$gi->quantity_received;
                }
            }

            foreach ($poItems as $item) {
                $parentPo = $records->firstWhere('id', $item->purchase_order_id);
                $grId = $parentPo->gr_id ?? null;
                $item->quantity_received = ($grId && isset($grItemsMap[$grId][$item->product_id]))
                    ? $grItemsMap[$grId][$item->product_id]
                    : 0;
                $itemsByPo[$item->purchase_order_id][] = $item;
            }
        }

        // Attach items and compute aggregations
        $records = $records->map(function ($po) use ($itemsByPo) {
            $po->items = $itemsByPo[$po->id] ?? [];
            $po->total_po_qty = collect($po->items)->sum('quantity');
            $po->total_gr_qty = collect($po->items)->sum('quantity_received');
            $po->vendor_display = $po->vendor_name ?: ($po->supplier_name ?: 'Vendor Utama');
            return $po;
        });

        $totalNominal = 0;
        $totalPoQtyAll = 0;
        $totalGrQtyAll = 0;
        $rows = [];

        if ($format === 'detailed') {
            $headers = [
                'No. PO',
                'Waktu PO',
                'Pembuat PO',
                'Vendor / Supplier',
                'TAP',
                'Cluster',
                'No. GR (Penerimaan)',
                'Waktu GR',
                'Petugas Gudang',
                'No. DO Telkomsel',
                'Gudang Penerima',
                'Status PO',
                'SKU',
                'Nama Produk',
                'Tipe Produk',
                'Qty Dipesan (PO)',
                'Qty Diterima (GR)',
                'Harga Modal (Rp)',
                'Subtotal (Rp)'
            ];

            foreach ($records as $r) {
                $totalNominal += (float)$r->total_amount;
                $totalPoQtyAll += (int)$r->total_po_qty;
                $totalGrQtyAll += (int)$r->total_gr_qty;

                if (!empty($r->items)) {
                    foreach ($r->items as $item) {
                        $rows[] = [
                            $r->doc_number,
                            date('d/m/Y H:i', strtotime($r->created_at ?? $r->order_date)),
                            $r->creator_name ?? 'Admin',
                            $r->vendor_display,
                            $r->tap_name,
                            $r->cluster_name,
                            $r->gr_number ?? '-',
                            $r->gr_created_at ? date('d/m/Y H:i', strtotime($r->gr_created_at)) : ($r->received_date ? date('d/m/Y', strtotime($r->received_date)) : '-'),
                            $r->receiver_name ?? '-',
                            $r->do_number ?? '-',
                            $r->destination_warehouse ?? '-',
                            $r->status,
                            $item->sku,
                            $item->product_name,
                            $item->product_type,
                            (int)$item->quantity,
                            $r->gr_number ? (int)$item->quantity_received : 0,
                            number_format($item->cost_price, 0, ',', '.'),
                            number_format($item->subtotal, 0, ',', '.'),
                        ];
                    }
                } else {
                    $rows[] = [
                        $r->doc_number,
                        date('d/m/Y H:i', strtotime($r->created_at ?? $r->order_date)),
                        $r->creator_name ?? 'Admin',
                        $r->vendor_display,
                        $r->tap_name,
                        $r->cluster_name,
                        $r->gr_number ?? '-',
                        $r->gr_created_at ? date('d/m/Y H:i', strtotime($r->gr_created_at)) : ($r->received_date ? date('d/m/Y', strtotime($r->received_date)) : '-'),
                        $r->receiver_name ?? '-',
                        $r->do_number ?? '-',
                        $r->destination_warehouse ?? '-',
                        $r->status,
                        '-',
                        'Tidak ada item',
                        '-',
                        0,
                        0,
                        '0',
                        number_format($r->total_amount, 0, ',', '.'),
                    ];
                }
            }

            return [
                'title' => 'Laporan Rincian Detail Pengadaan & Penerimaan Barang (PO - GR per Item)',
                'headers' => $headers,
                'records' => $records,
                'rows' => $rows,
                'format' => 'detailed',
                'summary' => [
                    'total_records' => count($records),
                    'total_amount' => $totalNominal,
                    'total_po_qty' => $totalPoQtyAll,
                    'total_gr_qty' => $totalGrQtyAll,
                ],
                'excel_summary' => ['TOTAL RINCIAN', '', '', '', '', '', '', '', '', '', '', '', '', '', '', number_format($totalPoQtyAll, 0, ',', '.'), number_format($totalGrQtyAll, 0, ',', '.'), '', number_format($totalNominal, 0, ',', '.')],
            ];
        } else {
            // Mode Summary
            $headers = [
                'No. PO',
                'Waktu PO',
                'Pembuat PO',
                'Vendor / Supplier',
                'TAP & Cluster',
                'No. GR (Penerimaan)',
                'Waktu GR',
                'Petugas Gudang',
                'No. DO Telkomsel',
                'Gudang Penerima',
                'Total Qty PO',
                'Total Qty GR',
                'Status PO',
                'Total Nilai Pengadaan (Rp)'
            ];

            foreach ($records as $r) {
                $totalNominal += (float)$r->total_amount;
                $totalPoQtyAll += (int)$r->total_po_qty;
                $totalGrQtyAll += (int)$r->total_gr_qty;

                $rows[] = [
                    $r->doc_number,
                    date('d/m/Y H:i', strtotime($r->created_at ?? $r->order_date)),
                    $r->creator_name ?? 'Admin',
                    $r->vendor_display,
                    $r->tap_name . ' (' . $r->cluster_name . ')',
                    $r->gr_number ?? '-',
                    $r->gr_created_at ? date('d/m/Y H:i', strtotime($r->gr_created_at)) : ($r->received_date ? date('d/m/Y', strtotime($r->received_date)) : '-'),
                    $r->receiver_name ?? '-',
                    $r->do_number ?? '-',
                    $r->destination_warehouse ?? '-',
                    number_format($r->total_po_qty, 0, ',', '.') . ' pcs',
                    $r->gr_number ? number_format($r->total_gr_qty, 0, ',', '.') . ' pcs' : '-',
                    $r->status,
                    number_format($r->total_amount, 0, ',', '.'),
                ];
            }

            return [
                'title' => 'Laporan Rekapitulasi Pengadaan Barang & Penerimaan Fisik (PO - GR)',
                'headers' => $headers,
                'records' => $records,
                'rows' => $rows,
                'format' => 'summary',
                'summary' => [
                    'total_records' => count($records),
                    'total_amount' => $totalNominal,
                    'total_po_qty' => $totalPoQtyAll,
                    'total_gr_qty' => $totalGrQtyAll,
                ],
                'excel_summary' => ['TOTAL PENGADAAN', '', '', '', '', '', '', '', '', '', number_format($totalPoQtyAll, 0, ',', '.'), number_format($totalGrQtyAll, 0, ',', '.'), '', number_format($totalNominal, 0, ',', '.')],
            ];
        }
    }

    private function getSmData($start, $end, $whId)
    {
        $query = DB::table('stock_movements')
            ->join('warehouses as sw', 'stock_movements.source_warehouse_id', '=', 'sw.id')
            ->join('warehouses as dw', 'stock_movements.destination_warehouse_id', '=', 'dw.id')
            ->whereBetween('stock_movements.movement_date', [$start, $end])
            ->select('stock_movements.*', 'sw.name as source_wh', 'dw.name as dest_wh')
            ->orderBy('stock_movements.id', 'desc');

        $scope = TerritoryScope::for();
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

        if ($whId !== 'ALL') {
            $query->where(function ($q) use ($whId) {
                $q->where('stock_movements.source_warehouse_id', $whId)
                  ->orWhere('stock_movements.destination_warehouse_id', $whId);
            });
        }

        $records = $query->get();
        $smIds = $records->pluck('id')->toArray();
        $itemsBySm = [];
        if (!empty($smIds)) {
            $items = DB::table('stock_movement_items')
                ->join('products', 'stock_movement_items.product_id', '=', 'products.id')
                ->whereIn('stock_movement_items.stock_movement_id', $smIds)
                ->select('stock_movement_items.*', 'products.name as product_name', 'products.sku')
                ->get();
            foreach ($items as $item) {
                $itemsBySm[$item->stock_movement_id][] = $item;
            }
        }

        $rows = [];
        $totalPcs = 0;
        foreach ($records as $r) {
            $itemList = $itemsBySm[$r->id] ?? [];
            $itemDesc = collect($itemList)->map(fn($i) => "{$i->sku} ({$i->quantity} pcs)")->implode(', ');
            $sumQty = collect($itemList)->sum('quantity');
            $totalPcs += $sumQty;

            $rows[] = [
                $r->doc_number,
                $r->movement_date,
                $r->source_wh,
                $r->dest_wh,
                $itemDesc ?: 'N/A',
                $sumQty,
                $r->status,
                $r->notes ?? '-',
            ];
        }

        return [
            'title' => 'Laporan Rekapitulasi Transfer & Mutasi Antar Gudang (SM)',
            'headers' => ['No. Dokumen SM', 'Tanggal Mutasi', 'Gudang Asal', 'Gudang Tujuan', 'Rincian Produk & SKU', 'Total Qty (Pcs)', 'Status', 'Keterangan'],
            'records' => $records,
            'rows' => $rows,
            'summary' => [
                'total_records' => count($records),
                'total_amount' => $totalPcs,
            ],
            'excel_summary' => ['TOTAL MUTASI FISIK', '', '', '', '', $totalPcs . ' pcs', '', ''],
        ];
    }

    private function getRepackData($start, $end, $whId)
    {
        $query = DB::table('repacks')
            ->join('warehouses', 'repacks.warehouse_id', '=', 'warehouses.id')
            ->whereBetween('repacks.repack_date', [$start, $end])
            ->select('repacks.*', 'warehouses.name as warehouse_name')
            ->orderBy('repacks.id', 'desc');

        TerritoryScope::applyWarehouseScope($query, 'repacks.warehouse_id');

        if ($whId !== 'ALL') $query->where('repacks.warehouse_id', $whId);

        $records = $query->get();
        $rows = [];
        $totalNominal = 0;

        foreach ($records as $r) {
            $totalNominal += (float)$r->total_amount;
            $rows[] = [
                $r->doc_number,
                $r->doc_type,
                $r->repack_date,
                $r->warehouse_name,
                $r->ref_doc_number ?? '-',
                $r->status,
                number_format($r->total_amount, 0, ',', '.'),
                $r->notes ?? '-',
            ];
        }

        return [
            'title' => 'Laporan Rekapitulasi Konversi Produk Repack (RO - RI)',
            'headers' => ['No. Dokumen', 'Jenis (RO/RI)', 'Tanggal Repack', 'Gudang Operasional', 'Ref Dokumen', 'Status', 'Total Nilai Konversi (Rp)', 'Keterangan'],
            'records' => $records,
            'rows' => $rows,
            'summary' => [
                'total_records' => count($records),
                'total_amount' => $totalNominal,
            ],
            'excel_summary' => ['TOTAL NILAI REPACK', '', '', '', '', '', number_format($totalNominal, 0, ',', '.'), ''],
        ];
    }

    private function getArData($start, $end, $tapId = 'ALL')
    {
        $query = DB::table('accounts_receivable')
            ->join('customers', 'accounts_receivable.customer_id', '=', 'customers.id')
            ->leftJoin('sales_orders', 'accounts_receivable.sales_order_id', '=', 'sales_orders.id')
            ->leftJoin('sales', 'sales_orders.sales_id', '=', 'sales.id')
            ->whereBetween('accounts_receivable.invoice_date', [$start, $end])
            ->select(
                'accounts_receivable.*',
                'customers.name as customer_name',
                'customers.code as customer_code',
                'customers.phone as customer_phone',
                'sales.name as sales_name',
                'sales_orders.doc_number as so_number'
            )
            ->orderBy('accounts_receivable.id', 'desc');

        TerritoryScope::applyTapScope($query, 'customers.tap_id');
        if ($tapId !== 'ALL') $query->where('customers.tap_id', $tapId);

        $records = $query->get();

        $rows = [];
        $totalPiutang = 0;
        $totalTerbayar = 0;
        $totalSisa = 0;

        foreach ($records as $r) {
            $totalPiutang += (float)$r->total_amount;
            $totalTerbayar += (float)$r->paid_amount;
            $totalSisa += (float)$r->balance_amount;

            $rows[] = [
                $r->doc_number,
                $r->so_number ?? '-',
                $r->customer_name . " ({$r->customer_code})",
                $r->sales_name ?? 'TAP Admin',
                $r->invoice_date,
                $r->due_date,
                number_format($r->total_amount, 0, ',', '.'),
                number_format($r->paid_amount, 0, ',', '.'),
                number_format($r->balance_amount, 0, ',', '.'),
                $r->status,
            ];
        }

        return [
            'title' => 'Laporan Rekapitulasi Piutang Toko / Outlet (Account Receivable - AR)',
            'headers' => ['No. Faktur AR', 'Ref SO', 'Toko / Pelanggan', 'Sales Penanggung Jawab', 'Tgl Faktur', 'Jatuh Tempo', 'Total Tagihan (Rp)', 'Terbayar (Rp)', 'Sisa Piutang (Rp)', 'Status AR'],
            'records' => $records,
            'rows' => $rows,
            'summary' => [
                'total_records' => count($records),
                'total_amount' => $totalSisa,
                'total_invoice' => $totalPiutang,
                'total_paid' => $totalTerbayar,
            ],
            'excel_summary' => ['TOTAL SISA PIUTANG OUTSTANDING', '', '', '', '', '', number_format($totalPiutang, 0, ',', '.'), number_format($totalTerbayar, 0, ',', '.'), number_format($totalSisa, 0, ',', '.'), ''],
        ];
    }

    private function getCashBankData($start, $end, $tapId = 'ALL')
    {
        $query = DB::table('bank_receipts')
            ->join('payments', 'bank_receipts.payment_id', '=', 'payments.id')
            ->join('customers', 'payments.customer_id', '=', 'customers.id')
            ->whereBetween('bank_receipts.receipt_date', [$start, $end])
            ->select(
                'bank_receipts.*',
                'payments.doc_number as ttk_number',
                'payments.payment_method',
                'payments.cashier_name',
                'customers.name as customer_name'
            )
            ->orderBy('bank_receipts.id', 'desc');

        TerritoryScope::applyTapScope($query, 'customers.tap_id');
        if ($tapId !== 'ALL') $query->where('customers.tap_id', $tapId);

        $records = $query->get();

        $rows = [];
        $totalMasuk = 0;

        foreach ($records as $r) {
            $totalMasuk += (float)$r->amount;
            $rows[] = [
                $r->doc_number,
                $r->receipt_type,
                $r->ttk_number,
                $r->receipt_date,
                $r->customer_name,
                $r->bank_name,
                $r->account_number ?? '-',
                $r->payment_method,
                $r->cashier_name,
                number_format($r->amount, 0, ',', '.'),
            ];
        }

        return [
            'title' => 'Laporan Rekapitulasi Penerimaan Kas & Bank (TTK Multi-AR & BBM)',
            'headers' => ['No. Dokumen BBM', 'Tipe BBM', 'Ref TTK', 'Tanggal Masuk', 'Toko Pelanggan', 'Kas / Bank Penerima', 'No. Rekening', 'Metode Bayar', 'Kasir Penerima', 'Total Nominal (Rp)'],
            'records' => $records,
            'rows' => $rows,
            'summary' => [
                'total_records' => count($records),
                'total_amount' => $totalMasuk,
            ],
            'excel_summary' => ['TOTAL KAS/BANK MASUK', '', '', '', '', '', '', '', '', number_format($totalMasuk, 0, ',', '.')],
        ];
    }

    /**
     * Laporan Mutasi Stok Ringkas (Stok Awal | Masuk IN | Keluar OUT | Stok Akhir)
     * Dihitung akurat dari buku besar `stock_mutations` dan dikunci oleh TerritoryScope.
     */
    private function getStockData($start, $end, $tapId, $whId)
    {
        $query = DB::table('stock_mutations')
            ->join('warehouses', 'stock_mutations.warehouse_id', '=', 'warehouses.id')
            ->join('taps', 'warehouses.tap_id', '=', 'taps.id')
            ->join('products', 'stock_mutations.product_id', '=', 'products.id')
            ->where('stock_mutations.date', '<=', $end)
            ->select(
                'stock_mutations.warehouse_id',
                'stock_mutations.product_id',
                'warehouses.name as warehouse_name',
                'warehouses.type as warehouse_type',
                'taps.name as tap_name',
                'products.sku as product_sku',
                'products.name as product_name',
                'products.uom',
                DB::raw("SUM(CASE WHEN stock_mutations.date < ? THEN (CASE WHEN stock_mutations.mutation_type = 'IN' THEN stock_mutations.quantity ELSE -stock_mutations.quantity END) ELSE 0 END) as opening_stock"),
                DB::raw("SUM(CASE WHEN stock_mutations.date BETWEEN ? AND ? AND stock_mutations.mutation_type = 'IN' THEN stock_mutations.quantity ELSE 0 END) as qty_in"),
                DB::raw("SUM(CASE WHEN stock_mutations.date BETWEEN ? AND ? AND stock_mutations.mutation_type = 'OUT' THEN stock_mutations.quantity ELSE 0 END) as qty_out")
            )
            ->addBinding([$start, $start, $end, $start, $end], 'select')
            ->groupBy(
                'stock_mutations.warehouse_id',
                'stock_mutations.product_id',
                'warehouses.name',
                'warehouses.type',
                'taps.name',
                'products.sku',
                'products.name',
                'products.uom'
            )
            ->orderBy('warehouses.name')
            ->orderBy('products.name');

        TerritoryScope::applyTapScope($query, 'warehouses.tap_id');
        TerritoryScope::applyWarehouseScope($query, 'stock_mutations.warehouse_id');

        if ($tapId !== 'ALL') $query->where('warehouses.tap_id', $tapId);
        if ($whId !== 'ALL') $query->where('stock_mutations.warehouse_id', $whId);

        $records = $query->get()->map(function ($r) {
            $r->opening_stock = (int) $r->opening_stock;
            $r->qty_in = (int) $r->qty_in;
            $r->qty_out = (int) $r->qty_out;
            $r->closing_stock = $r->opening_stock + $r->qty_in - $r->qty_out;
            return $r;
        });

        $rows = [];
        $totalOpening = 0;
        $totalIn = 0;
        $totalOut = 0;
        $totalClosing = 0;

        foreach ($records as $r) {
            $totalOpening += $r->opening_stock;
            $totalIn += $r->qty_in;
            $totalOut += $r->qty_out;
            $totalClosing += $r->closing_stock;

            $rows[] = [
                $r->tap_name,
                "[{$r->warehouse_type}] {$r->warehouse_name}",
                $r->product_sku,
                $r->product_name,
                number_format($r->opening_stock, 0, ',', '.'),
                number_format($r->qty_in, 0, ',', '.'),
                number_format($r->qty_out, 0, ',', '.'),
                number_format($r->closing_stock, 0, ',', '.'),
            ];
        }

        return [
            'title' => 'Laporan Mutasi Stok Ringkas (Stok Awal - Masuk - Keluar - Stok Akhir)',
            'headers' => ['TAP', 'Gudang', 'SKU', 'Nama Produk', 'Stok Awal', 'Masuk (IN)', 'Keluar (OUT)', 'Stok Akhir'],
            'records' => $records,
            'rows' => $rows,
            'summary' => [
                'total_records' => count($records),
                'total_amount' => $totalClosing,
                'total_opening' => $totalOpening,
                'total_in' => $totalIn,
                'total_out' => $totalOut,
                'total_closing' => $totalClosing,
            ],
            'excel_summary' => [
                'TOTAL STOK',
                '',
                '',
                '',
                number_format($totalOpening, 0, ',', '.'),
                number_format($totalIn, 0, ',', '.'),
                number_format($totalOut, 0, ',', '.'),
                number_format($totalClosing, 0, ',', '.'),
            ],
        ];
    }
}

