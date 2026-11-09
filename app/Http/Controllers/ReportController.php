<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $reportType = $request->input('type', 'SALES');
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-d'));
        $tapId = $request->input('tap_id', 'ALL');
        $warehouseId = $request->input('warehouse_id', 'ALL');

        // Master data untuk filter
        $taps = DB::table('taps')->where('status', 'ACTIVE')->orderBy('name')->get();
        $warehouses = DB::table('warehouses')->where('status', 'ACTIVE')->orderBy('name')->get();

        $data = $this->getReportData($reportType, $startDate, $endDate, $tapId, $warehouseId);

        return view('reports.index', [
            'reportType' => $reportType,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'tapId' => $tapId,
            'warehouseId' => $warehouseId,
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
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-d'));
        $tapId = $request->input('tap_id', 'ALL');
        $warehouseId = $request->input('warehouse_id', 'ALL');

        $data = $this->getReportData($reportType, $startDate, $endDate, $tapId, $warehouseId);

        $fileName = "Laporan_{$reportType}_PT_Wahana_Putra_Yudha_" . date('Ymd_His') . ".csv";

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
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-d'));
        $tapId = $request->input('tap_id', 'ALL');
        $warehouseId = $request->input('warehouse_id', 'ALL');

        $data = $this->getReportData($reportType, $startDate, $endDate, $tapId, $warehouseId);

        return view('reports.print-pdf', [
            'reportType' => $reportType,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'title' => $data['title'],
            'headers' => $data['headers'],
            'rows' => $data['rows'],
            'summary' => $data['summary'],
            'records' => $data['records'],
        ]);
    }

    private function getReportData($type, $start, $end, $tapId, $whId)
    {
        switch ($type) {
            case 'PO_GR':
                return $this->getPoGrData($start, $end, $tapId);
            case 'SM':
                return $this->getSmData($start, $end, $whId);
            case 'REPACK':
                return $this->getRepackData($start, $end, $whId);
            case 'SALES':
                return $this->getSalesData($start, $end, $tapId, $whId);
            case 'AR':
                return $this->getArData($start, $end);
            case 'CASH_BANK':
                return $this->getCashBankData($start, $end);
            default:
                return $this->getSalesData($start, $end, $tapId, $whId);
        }
    }

    private function getSalesData($start, $end, $tapId, $whId)
    {
        $query = DB::table('sales_orders')
            ->join('customers', 'sales_orders.customer_id', '=', 'customers.id')
            ->join('sales', 'sales_orders.sales_id', '=', 'sales.id')
            ->join('warehouses', 'sales_orders.warehouse_id', '=', 'warehouses.id')
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

        if ($tapId !== 'ALL') $query->where('sales_orders.tap_id', $tapId);
        if ($whId !== 'ALL') $query->where('sales_orders.warehouse_id', $whId);

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

    private function getPoGrData($start, $end, $tapId)
    {
        $query = DB::table('purchase_orders')
            ->join('taps', 'purchase_orders.tap_id', '=', 'taps.id')
            ->leftJoin('goods_receipts', 'purchase_orders.id', '=', 'goods_receipts.purchase_order_id')
            ->leftJoin('warehouses', 'goods_receipts.warehouse_id', '=', 'warehouses.id')
            ->whereBetween('purchase_orders.order_date', [$start, $end])
            ->select(
                'purchase_orders.*',
                'taps.name as tap_name',
                'goods_receipts.doc_number as gr_number',
                'goods_receipts.received_date',
                'warehouses.name as destination_warehouse'
            )
            ->orderBy('purchase_orders.id', 'desc');

        if ($tapId !== 'ALL') $query->where('purchase_orders.tap_id', $tapId);

        $records = $query->get();
        $rows = [];
        $totalNominal = 0;

        foreach ($records as $r) {
            $totalNominal += (float)$r->total_amount;
            $rows[] = [
                $r->doc_number,
                $r->order_date,
                $r->supplier_name,
                $r->tap_name,
                $r->gr_number ?? '-',
                $r->received_date ?? '-',
                $r->destination_warehouse ?? '-',
                $r->status,
                number_format($r->total_amount, 0, ',', '.'),
            ];
        }

        return [
            'title' => 'Laporan Rekapitulasi Pengadaan Barang & Penerimaan Fisik (PO - GR)',
            'headers' => ['No. PO', 'Tgl PO', 'Supplier', 'TAP Tujuan', 'No. GR (Fisik Masuk)', 'Tgl Diterima', 'Gudang Penerima', 'Status PO', 'Total Nilai Pengadaan (Rp)'],
            'records' => $records,
            'rows' => $rows,
            'summary' => [
                'total_records' => count($records),
                'total_amount' => $totalNominal,
            ],
            'excel_summary' => ['TOTAL PENGADAAN', '', '', '', '', '', '', '', number_format($totalNominal, 0, ',', '.')],
        ];
    }

    private function getSmData($start, $end, $whId)
    {
        $query = DB::table('stock_movements')
            ->join('warehouses as sw', 'stock_movements.source_warehouse_id', '=', 'sw.id')
            ->join('warehouses as dw', 'stock_movements.destination_warehouse_id', '=', 'dw.id')
            ->whereBetween('stock_movements.movement_date', [$start, $end])
            ->select('stock_movements.*', 'sw.name as source_wh', 'dw.name as dest_wh')
            ->orderBy('stock_movements.id', 'desc');

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

    private function getArData($start, $end)
    {
        $records = DB::table('accounts_receivable')
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
            ->orderBy('accounts_receivable.id', 'desc')
            ->get();

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

    private function getCashBankData($start, $end)
    {
        $records = DB::table('bank_receipts')
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
            ->orderBy('bank_receipts.id', 'desc')
            ->get();

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
}

