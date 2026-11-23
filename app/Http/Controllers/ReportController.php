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
        $docType = $request->input('doc_type', 'ALL');

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
            'docType' => $docType,
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

        $formatSuffix = (in_array($reportType, ['PO_GR', 'SM', 'REPACK', 'SALES']) && $format === 'detailed') ? 'Detail_Item' : 'Rekap_Ringkas';
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
                return $this->getSmData($start, $end, $tapId, $whId, $format, $areaId, $clusterId);
            case 'REPACK':
                return $this->getRepackData($start, $end, $whId, $format, $areaId, $clusterId, $tapId, request()->input('doc_type', 'ALL'));
            case 'SALES':
                return $this->getSalesData($start, $end, $tapId, $whId, $format, $areaId, $clusterId);
            case 'AR':
                return $this->getArData($start, $end, $tapId);
            case 'CASH_BANK':
                return $this->getCashBankData($start, $end, $tapId);
            case 'STOCK':
                return $this->getStockData($start, $end, $tapId, $whId);
            default:
                return $this->getSalesData($start, $end, $tapId, $whId, $format, $areaId, $clusterId);
        }
    }

    private function getSalesData($start, $end, $tapId, $whId, $format = 'summary', $areaId = '', $clusterId = '')
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
                'customers.type as customer_type',
                'sales.name as sales_name',
                'warehouses.name as warehouse_name',
                'taps.name as tap_name',
                'clusters.name as cluster_name',
                'goods_deliveries.doc_number as gd_number',
                'goods_deliveries.delivery_date as gd_date',
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

        // Ambil item produk untuk masing-masing SO
        $soIds = $records->pluck('id')->toArray();
        $itemsBySo = [];
        if (!empty($soIds)) {
            $soItems = DB::table('sales_order_items')
                ->join('products', 'sales_order_items.product_id', '=', 'products.id')
                ->whereIn('sales_order_items.sales_order_id', $soIds)
                ->select(
                    'sales_order_items.*',
                    'products.sku',
                    'products.name as product_name',
                    'products.type as product_type',
                    'products.tracking_method'
                )
                ->get();

            foreach ($soItems as $item) {
                $itemsBySo[$item->sales_order_id][] = $item;
            }
        }

        $totalNominal = 0;
        $totalQtyAll = 0;
        $totalDeliveredCount = 0;

        $records = $records->map(function ($so) use ($itemsBySo, &$totalQtyAll, &$totalDeliveredCount) {
            $so->items = $itemsBySo[$so->id] ?? [];
            $so->total_qty = collect($so->items)->sum('quantity');
            $totalQtyAll += $so->total_qty;
            if ($so->status === 'DELIVERED') {
                $totalDeliveredCount++;
            }
            return $so;
        });

        $rows = [];

        if ($format === 'detailed') {
            $headers = [
                'No. SO',
                'Tanggal SO',
                'Toko / Outlet',
                'Kode Toko',
                'Sales Kanvaser',
                'Gudang Asal',
                'Channel',
                'No. GD (Surat Jalan)',
                'No. Faktur AR',
                'Status AR',
                'Status SO',
                'SKU',
                'Nama Produk',
                'Tipe Produk',
                'Qty (pcs)',
                'Harga Jual Satuan (Rp)',
                'Subtotal (Rp)',
                'Catatan'
            ];

            foreach ($records as $r) {
                $totalNominal += (float)$r->grand_total;
                if (!empty($r->items)) {
                    foreach ($r->items as $it) {
                        $rows[] = [
                            $r->doc_number,
                            $r->order_date,
                            $r->customer_name,
                            $r->customer_code,
                            $r->sales_name,
                            $r->warehouse_name,
                            $r->channel,
                            $r->gd_number ?? '-',
                            $r->ar_number ?? '-',
                            $r->ar_status ?? '-',
                            $r->status,
                            $it->sku,
                            $it->product_name,
                            $it->product_type,
                            number_format($it->quantity, 0, ',', '.'),
                            number_format($it->unit_price, 0, ',', '.'),
                            number_format($it->subtotal, 0, ',', '.'),
                            $r->notes ?? '-',
                        ];
                    }
                } else {
                    $rows[] = [
                        $r->doc_number,
                        $r->order_date,
                        $r->customer_name,
                        $r->customer_code,
                        $r->sales_name,
                        $r->warehouse_name,
                        $r->channel,
                        $r->gd_number ?? '-',
                        $r->ar_number ?? '-',
                        $r->ar_status ?? '-',
                        $r->status,
                        '-',
                        '-',
                        '-',
                        '0',
                        '0',
                        '0',
                        $r->notes ?? '-',
                    ];
                }
            }

            $excelSummary = ['TOTAL KESELURUHAN', '', '', '', '', '', '', '', '', '', '', '', '', '', number_format($totalQtyAll, 0, ',', '.'), '', number_format($totalNominal, 0, ',', '.'), ''];
        } else {
            // Summary Format
            $headers = ['No. SO', 'Tanggal', 'Toko / Pelanggan', 'Sales Kanvaser', 'Gudang Asal', 'Channel', 'No. GD (Surat Jalan)', 'No. Faktur AR', 'Status SO', 'Total Qty (pcs)', 'Total Penjualan (Rp)'];
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
                    number_format($r->total_qty, 0, ',', '.'),
                    number_format($r->grand_total, 0, ',', '.'),
                ];
            }
            $excelSummary = ['TOTAL PENJUALAN', '', '', '', '', '', '', '', '', number_format($totalQtyAll, 0, ',', '.'), number_format($totalNominal, 0, ',', '.')];
        }

        return [
            'title' => 'Laporan Rekapitulasi Penjualan & Pengiriman Barang (SO - GD - AR)',
            'headers' => $headers,
            'records' => $records,
            'rows' => $rows,
            'summary' => [
                'total_records' => count($records),
                'total_amount' => $totalNominal,
                'total_qty' => $totalQtyAll,
                'total_delivered' => $totalDeliveredCount,
            ],
            'excel_summary' => $excelSummary,
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

    private function getSmData($start, $end, $tapId, $whId, $format = 'summary', $areaId = '', $clusterId = '')
    {
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
            ->whereBetween('stock_movements.movement_date', [$start, $end])
            ->select(
                'stock_movements.*',
                'sw.name as source_wh',
                'sw.code as source_wh_code',
                'sw.type as source_wh_type',
                'dw.name as dest_wh',
                'dw.code as dest_wh_code',
                'dw.type as dest_wh_type',
                'stap.name as source_tap_name',
                'dtap.name as dest_tap_name',
                'scls.name as source_cluster_name',
                'dcls.name as dest_cluster_name',
                'sar.name as source_area_name',
                'dar.name as dest_area_name',
                'creator.name as creator_name'
            )
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

        if (!empty($areaId)) {
            $query->where(function ($q) use ($areaId) {
                $q->where('scls.area_id', $areaId)
                  ->orWhere('dcls.area_id', $areaId);
            });
        }
        if (!empty($clusterId)) {
            $query->where(function ($q) use ($clusterId) {
                $q->where('stap.cluster_id', $clusterId)
                  ->orWhere('dtap.cluster_id', $clusterId);
            });
        }
        if ($tapId !== 'ALL' && !empty($tapId)) {
            $query->where(function ($q) use ($tapId) {
                $q->where('sw.tap_id', $tapId)
                  ->orWhere('dw.tap_id', $tapId);
            });
        }
        if ($whId !== 'ALL' && !empty($whId)) {
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

        // Attach items and compute total quantity per movement
        $records = $records->map(function ($sm) use ($itemsBySm) {
            $sm->items = $itemsBySm[$sm->id] ?? [];
            $sm->total_qty = collect($sm->items)->sum('quantity');
            $sm->creator_display = $sm->creator_name ?: 'Petugas Gudang';
            return $sm;
        });

        $totalPcs = 0;
        $totalItems = 0;
        $rows = [];

        if ($format === 'detailed') {
            // DETAILED (Per Baris Item Produk)
            $headers = [
                'No. Dokumen SM',
                'Tanggal Mutasi',
                'Jam Transaksi',
                'Dibuat Oleh',
                'Gudang Asal',
                'Tipe Gudang Asal',
                'Gudang Tujuan',
                'Tipe Gudang Tujuan',
                'SKU Produk',
                'Nama Produk',
                'Tipe Produk',
                'Qty Dipindahkan (Pcs)',
                'Status',
                'Keterangan / Keperluan',
            ];

            foreach ($records as $sm) {
                $items = $sm->items;
                $formattedTime = $sm->created_at ? date('H:i:s', strtotime($sm->created_at)) : '-';

                if (empty($items)) {
                    $rows[] = [
                        $sm->doc_number,
                        $sm->movement_date,
                        $formattedTime,
                        $sm->creator_display,
                        $sm->source_wh,
                        $sm->source_wh_type,
                        $sm->dest_wh,
                        $sm->dest_wh_type,
                        '-',
                        '-',
                        '-',
                        0,
                        $sm->status,
                        $sm->notes ?? '-',
                    ];
                } else {
                    foreach ($items as $item) {
                        $qty = (int)$item->quantity;
                        $totalPcs += $qty;
                        $totalItems++;

                        $rows[] = [
                            $sm->doc_number,
                            $sm->movement_date,
                            $formattedTime,
                            $sm->creator_display,
                            $sm->source_wh,
                            $sm->source_wh_type,
                            $sm->dest_wh,
                            $sm->dest_wh_type,
                            $item->sku,
                            $item->product_name,
                            $item->product_type,
                            $qty,
                            $sm->status,
                            $sm->notes ?? '-',
                        ];
                    }
                }
            }

            return [
                'title' => 'Laporan Rincian Transfer & Mutasi Antar Gudang (SM) Detail per Item',
                'headers' => $headers,
                'rows' => $rows,
                'records' => $records,
                'format' => 'detailed',
                'summary' => [
                    'total_records' => count($records),
                    'total_amount' => $totalPcs,
                    'total_qty' => $totalPcs,
                    'total_items_count' => $totalItems,
                ],
                'excel_summary' => ['TOTAL RINCIAN MUTASI', '', '', '', '', '', '', '', '', '', '', number_format($totalPcs, 0, ',', '.'), '', ''],
            ];

        } else {
            // SUMMARY (Rekap 1 Baris per Dokumen SM)
            $headers = [
                'No. Dokumen SM',
                'Tanggal Mutasi',
                'Jam Transaksi',
                'Dibuat Oleh',
                'Gudang Asal',
                'Tipe Asal',
                'Gudang Tujuan',
                'Tipe Tujuan',
                'Rincian Item Singkat',
                'Jumlah Jenis Item',
                'Total Qty (Pcs)',
                'Status',
                'Keterangan / Keperluan',
            ];

            foreach ($records as $sm) {
                $itemList = $sm->items;
                $itemDesc = collect($itemList)->map(fn($i) => "{$i->sku} ({$i->quantity} pcs)")->implode(', ');
                $sumQty = $sm->total_qty;
                $totalPcs += $sumQty;
                $formattedTime = $sm->created_at ? date('H:i:s', strtotime($sm->created_at)) : '-';

                $rows[] = [
                    $sm->doc_number,
                    $sm->movement_date,
                    $formattedTime,
                    $sm->creator_display,
                    $sm->source_wh,
                    $sm->source_wh_type,
                    $sm->dest_wh,
                    $sm->dest_wh_type,
                    $itemDesc ?: 'N/A',
                    count($itemList),
                    $sumQty,
                    $sm->status,
                    $sm->notes ?? '-',
                ];
            }

            return [
                'title' => 'Laporan Rekapitulasi Transfer & Mutasi Antar Gudang (SM)',
                'headers' => $headers,
                'rows' => $rows,
                'records' => $records,
                'format' => 'summary',
                'summary' => [
                    'total_records' => count($records),
                    'total_amount' => $totalPcs,
                    'total_qty' => $totalPcs,
                ],
                'excel_summary' => ['TOTAL MUTASI FISIK', '', '', '', '', '', '', '', '', '', number_format($totalPcs, 0, ',', '.'), '', ''],
            ];
        }
    }

    private function getRepackData($start, $end, $whId, $format = 'summary', $areaId = '', $clusterId = '', $tapId = 'ALL', $docType = 'ALL')
    {
        $query = DB::table('repacks')
            ->join('warehouses', 'repacks.warehouse_id', '=', 'warehouses.id')
            ->leftJoin('taps', 'warehouses.tap_id', '=', 'taps.id')
            ->leftJoin('clusters', 'taps.cluster_id', '=', 'clusters.id')
            ->leftJoin('areas', 'clusters.area_id', '=', 'areas.id')
            ->whereBetween('repacks.repack_date', [$start, $end])
            ->select(
                'repacks.*',
                'warehouses.name as warehouse_name',
                'warehouses.type as warehouse_type',
                'taps.name as tap_name',
                'clusters.name as cluster_name',
                'areas.name as area_name'
            )
            ->orderBy('repacks.id', 'desc');

        TerritoryScope::applyWarehouseScope($query, 'repacks.warehouse_id');

        if ($whId !== 'ALL') $query->where('repacks.warehouse_id', $whId);
        if ($tapId !== 'ALL') $query->where('warehouses.tap_id', $tapId);
        if (!empty($clusterId)) $query->where('taps.cluster_id', $clusterId);
        if (!empty($areaId)) $query->where('clusters.area_id', $areaId);

        if ($docType === 'RPKOUT') {
            $query->where('repacks.doc_type', 'RPKOUT');
        } elseif ($docType === 'RPKIN') {
            $query->where('repacks.doc_type', 'RPKIN');
        }

        $records = $query->get();

        // Ambil items untuk seluruh repack terkait
        $repackIds = $records->pluck('id')->toArray();
        $itemsByRepack = [];
        if (!empty($repackIds)) {
            $items = DB::table('repack_items')
                ->join('products', 'repack_items.product_id', '=', 'products.id')
                ->whereIn('repack_items.repack_id', $repackIds)
                ->select(
                    'repack_items.*',
                    'products.name as product_name',
                    'products.sku',
                    'products.type as product_type',
                    'products.tracking_method'
                )
                ->orderBy('repack_items.id')
                ->get();

            foreach ($items as $item) {
                $itemsByRepack[$item->repack_id][] = $item;
            }
        }

        $totalNominal = 0;
        $totalRoCount = 0;
        $totalRiCount = 0;
        $totalComp1Qty = 0;
        $totalOutputQty = 0;
        $totalRoAmount = 0;
        $totalRiAmount = 0;

        foreach ($records as $r) {
            $r->items = $itemsByRepack[$r->id] ?? [];
            $totalNominal += (float)$r->total_amount;

            if ($r->doc_type === 'RPKOUT') {
                $totalRoCount++;
                $totalRoAmount += (float)$r->total_amount;
                // Item pertama / INPUT adalah bahan fisik
                $firstInput = collect($r->items)->firstWhere('item_type', 'INPUT') ?? ($r->items[0] ?? null);
                if ($firstInput) {
                    $totalComp1Qty += (int)$firstInput->quantity;
                }
            } elseif ($r->doc_type === 'RPKIN') {
                $totalRiCount++;
                $totalRiAmount += (float)$r->total_amount;
                // Item pertama / OUTPUT adalah produk jadi
                $firstOutput = collect($r->items)->firstWhere('item_type', 'OUTPUT') ?? ($r->items[0] ?? null);
                if ($firstOutput) {
                    $totalOutputQty += (int)$firstOutput->quantity;
                }
            }
        }

        $summaryData = [
            'total_records' => count($records),
            'total_ro_count' => $totalRoCount,
            'total_ri_count' => $totalRiCount,
            'total_comp1_qty' => $totalComp1Qty,
            'total_output_qty' => $totalOutputQty,
            'total_ro_amount' => $totalRoAmount,
            'total_ri_amount' => $totalRiAmount,
            'total_amount' => $totalNominal,
        ];

        $rows = [];
        if ($format === 'detailed') {
            $headers = [
                'No. Dokumen',
                'Jenis (RO/RI)',
                'Tanggal Repack',
                'Gudang Operasional',
                'Ref Dokumen',
                'Status',
                'Role Item',
                'SKU Produk',
                'Nama Produk',
                'Tracking',
                'Qty (Pcs)',
                'Harga Satuan (Rp)',
                'Subtotal (Rp)',
                'Keterangan',
            ];

            foreach ($records as $r) {
                if (empty($r->items)) {
                    $rows[] = [
                        $r->doc_number,
                        $r->doc_type,
                        $r->repack_date,
                        $r->warehouse_name,
                        $r->ref_doc_number ?? '-',
                        $r->status,
                        '-', '-', '-', '-', 0, 0,
                        number_format($r->total_amount, 0, ',', '.'),
                        $r->notes ?? '-',
                    ];
                } else {
                    foreach ($r->items as $idx => $it) {
                        $roleLabel = ($r->doc_type === 'RPKOUT')
                            ? ($it->item_type === 'INPUT' ? 'Bahan Fisik (0K)' : 'Bahan Saldo (Bulk)')
                            : 'Produk Jadi (Output)';

                        $rows[] = [
                            $idx === 0 ? $r->doc_number : '',
                            $idx === 0 ? $r->doc_type : '',
                            $idx === 0 ? $r->repack_date : '',
                            $idx === 0 ? $r->warehouse_name : '',
                            $idx === 0 ? ($r->ref_doc_number ?? '-') : '',
                            $idx === 0 ? $r->status : '',
                            $roleLabel,
                            $it->sku,
                            $it->product_name,
                            'QUANTITY',
                            number_format($it->quantity, 0, ',', '.'),
                            number_format($it->unit_cost, 0, ',', '.'),
                            number_format($it->subtotal, 0, ',', '.'),
                            $idx === 0 ? ($r->notes ?? '-') : '',
                        ];
                    }
                }
            }

            return [
                'title' => 'Laporan Rincian Lengkap Konversi Produk Repack (RO - RI)',
                'headers' => $headers,
                'records' => $records,
                'rows' => $rows,
                'summary' => $summaryData,
                'excel_summary' => ['TOTAL NILAI KONVERSI', '', '', '', '', '', '', '', '', '', '', '', number_format($totalNominal, 0, ',', '.'), ''],
            ];
        }

        // Summary format
        foreach ($records as $r) {
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
            'summary' => $summaryData,
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

