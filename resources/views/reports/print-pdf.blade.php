<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - PT Wahana Putra Yudha</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 p-4 sm:p-8 font-sans antialiased text-xs">

    <!-- Floating Top Bar -->
    <div class="max-w-6xl mx-auto mb-4 no-print flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-4 rounded-xl shadow-sm border border-slate-200">
        <a href="{{ url()->previous() ?: route('reports.index') }}" class="px-3.5 py-1.5 rounded-lg bg-slate-100 text-slate-700 font-bold hover:bg-slate-200 text-xs">
            &larr; Kembali ke Pusat Laporan
        </a>

        @if(in_array($reportType, ['PO_GR', 'SM']))
            <div class="flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl">
                <span class="text-[10px] text-slate-500 font-bold uppercase px-2">Mode:</span>
                <a href="{{ route('reports.print-pdf', array_merge(request()->all(), ['format' => 'summary'])) }}"
                   class="px-3 py-1 rounded-lg text-xs font-bold transition-all {{ ($format ?? 'summary') === 'summary' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                   Rekap Ringkas
                </a>
                <a href="{{ route('reports.print-pdf', array_merge(request()->all(), ['format' => 'detailed'])) }}"
                   class="px-3 py-1 rounded-lg text-xs font-bold transition-all {{ ($format ?? 'summary') === 'detailed' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                   Rincian Lengkap per Item
                </a>
            </div>
        @endif

        <div class="flex items-center gap-2">
            <span class="text-xs text-slate-500 font-medium hidden md:inline">Tips: Pilih "Save as PDF" di dialog cetak</span>
            <button onclick="window.print()" class="px-5 py-2 rounded-xl bg-slate-900 text-white font-bold hover:bg-slate-800 flex items-center gap-2 shadow-sm text-xs cursor-pointer">
                <span>🖨️ Cetak / Simpan PDF</span>
            </button>
        </div>
    </div>

    <!-- Official Report Document Container -->
    <div class="max-w-6xl mx-auto bg-white p-8 rounded-2xl shadow-md border-2 border-slate-300">
        
        <!-- Header Perusahaan Resmi -->
        <div class="flex items-start justify-between pb-4 mb-4 border-b-2 border-slate-900">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xl font-extrabold tracking-tight text-slate-900">WAHANA<span class="text-red-600">POS</span></span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700">TELKOMSEL AUTHORIZED PARTNER</span>
                </div>
                <h2 class="text-base font-extrabold text-slate-900 mt-1">PT WAHANA PUTRA YUDHA</h2>
                <p class="text-[11px] text-slate-500 font-medium">Mitra Distributor Resmi Telkomsel &bull; Operasional Wilayah Jabodetabek</p>
                <p class="text-[10px] text-slate-400">Divisi Logistik, Penjualan & Keuangan TAP</p>
            </div>

            <div class="text-right">
                <span class="px-3 py-1 rounded text-xs font-mono font-extrabold bg-slate-900 text-white uppercase">
                    DOKUMEN LAPORAN RESMI
                </span>
                <p class="text-[11px] text-slate-500 font-medium mt-2">Tanggal Cetak: {{ date('d F Y H:i:s') }}</p>
                <p class="text-[11px] text-slate-600 font-bold">Periode: {{ date('d/m/Y', strtotime($startDate)) }} s/d {{ date('d/m/Y', strtotime($endDate)) }}</p>
            </div>
        </div>

        <!-- Judul Laporan -->
        <div class="mb-4 text-center">
            <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wide">{{ $title }}</h3>
            <p class="text-[11px] text-slate-500">PT WAHANA PUTRA YUDHA</p>
        </div>

        @if($reportType === 'PO_GR' && ($format ?? 'summary') === 'detailed')
            <!-- ============================================== -->
            <!-- FORMAT RINCIAN LENGKAP RESMI (GROUPED BY PO)   -->
            <!-- ============================================== -->
            <div class="space-y-6 mb-8">
                @forelse($records as $idx => $po)
                    <div class="border border-slate-300 rounded-xl overflow-hidden bg-white shadow-2xs" style="page-break-inside: avoid;">
                        <!-- Header Dokumen PO & GR -->
                        <div class="bg-slate-100/90 border-b border-slate-300 px-4 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-extrabold bg-slate-900 text-white">#{{ $idx + 1 }}</span>
                                <h4 class="font-mono font-bold text-slate-900 text-xs flex items-center gap-1.5">
                                    <span>PO: {{ $po->doc_number }}</span>
                                    <span class="text-slate-400 font-sans font-normal">&bull; {{ date('d M Y, H:i', strtotime($po->created_at ?? $po->order_date)) }}</span>
                                </h4>
                            </div>
                            <div class="flex items-center gap-3 text-[10px] flex-wrap">
                                <span>Pembuat: <strong class="text-slate-800">{{ $po->creator_name ?? 'Admin' }}</strong></span>
                                <span>&bull;</span>
                                <span>Vendor: <strong class="text-slate-800">{{ $po->vendor_display }}</strong></span>
                                <span>&bull;</span>
                                <span class="px-2 py-0.5 rounded font-bold {{ $po->status === 'RECEIVED' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">{{ $po->status }}</span>
                            </div>
                        </div>

                        <!-- Info Strip Penerimaan (GR) & Pengiriman (DO) -->
                        <div class="px-4 py-2 bg-slate-50 border-b border-slate-200 text-[10px] text-slate-600 flex flex-wrap items-center justify-between gap-2">
                            <div class="flex items-center gap-3 flex-wrap">
                                <span>TAP: <strong class="text-slate-800">{{ $po->tap_name }} ({{ $po->cluster_name }})</strong></span>
                                <span>&bull;</span>
                                <span>No. GR: <strong class="font-mono text-emerald-800">{{ $po->gr_number ?? 'Belum Ada' }}</strong></span>
                                @if($po->gr_number)
                                    <span>&bull;</span>
                                    <span>Diterima: <strong class="text-slate-800">{{ $po->gr_created_at ? date('d/m/Y H:i', strtotime($po->gr_created_at)) : ($po->received_date ? date('d/m/Y', strtotime($po->received_date)) : '-') }}</strong> (Petugas: {{ $po->receiver_name ?? '-' }})</span>
                                @endif
                                <span>&bull;</span>
                                <span>Gudang: <strong class="text-slate-800">{{ $po->destination_warehouse ?? '-' }}</strong></span>
                            </div>
                            <div>
                                <span>No. DO Telkomsel: <strong class="font-mono text-slate-900">{{ $po->do_number ?? '-' }}</strong></span>
                            </div>
                        </div>

                        <!-- Tabel Item Barang -->
                        <table class="w-full text-left text-[11px] border-collapse">
                            <thead class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200 text-[10px] uppercase">
                                <tr>
                                    <th class="py-2 px-3 text-center w-8">No</th>
                                    <th class="py-2 px-3">Kode SKU</th>
                                    <th class="py-2 px-3">Nama Produk Barang</th>
                                    <th class="py-2 px-3">Tipe</th>
                                    <th class="py-2 px-3 text-center">Qty Pesan (PO)</th>
                                    <th class="py-2 px-3 text-center">Qty Fisik (GR)</th>
                                    <th class="py-2 px-3 text-center">Status Qty</th>
                                    <th class="py-2 px-3 text-right">Harga Modal</th>
                                    <th class="py-2 px-3 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($po->items as $iIdx => $item)
                                    <tr>
                                        <td class="py-1.5 px-3 text-center text-slate-400 font-mono">{{ $iIdx + 1 }}</td>
                                        <td class="py-1.5 px-3 font-mono font-bold text-slate-700">{{ $item->sku }}</td>
                                        <td class="py-1.5 px-3 font-bold text-slate-900">{{ $item->product_name }}</td>
                                        <td class="py-1.5 px-3 text-slate-600">{{ $item->product_type }}</td>
                                        <td class="py-1.5 px-3 text-center font-mono font-bold text-slate-900">{{ number_format($item->quantity) }} pcs</td>
                                        <td class="py-1.5 px-3 text-center font-mono font-bold text-emerald-800">
                                            {{ $po->gr_number ? number_format($item->quantity_received) . ' pcs' : '-' }}
                                        </td>
                                        <td class="py-1.5 px-3 text-center">
                                            @if(!$po->gr_number)
                                                <span class="text-amber-700 font-bold text-[10px]">Menunggu GR</span>
                                            @elseif($item->quantity_received >= $item->quantity)
                                                <span class="text-emerald-700 font-bold text-[10px]">Lengkap</span>
                                            @else
                                                <span class="text-rose-700 font-bold text-[10px]">Kurang {{ number_format($item->quantity - $item->quantity_received) }}</span>
                                            @endif
                                        </td>
                                        <td class="py-1.5 px-3 text-right font-mono text-slate-600">Rp {{ number_format($item->cost_price, 0, ',', '.') }}</td>
                                        <td class="py-1.5 px-3 text-right font-mono font-bold text-slate-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="py-2.5 text-center text-slate-400 italic">Tidak ada item tercatat.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-slate-100/70 border-t border-slate-300 font-bold text-[11px]">
                                <tr>
                                    <td colspan="4" class="py-2 px-3 text-right uppercase tracking-wider text-slate-700">Subtotal PO ({{ count($po->items) }} item):</td>
                                    <td class="py-2 px-3 text-center font-mono text-slate-900">{{ number_format($po->total_po_qty ?? 0) }} pcs</td>
                                    <td class="py-2 px-3 text-center font-mono text-emerald-800">{{ $po->gr_number ? number_format($po->total_gr_qty ?? 0) . ' pcs' : '-' }}</td>
                                    <td colspan="2" class="py-2 px-3 text-right uppercase text-slate-700">Total Nilai PO:</td>
                                    <td class="py-2 px-3 text-right font-mono font-extrabold text-emerald-800">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-400 border border-slate-300 rounded-xl bg-slate-50">
                        Tidak ada transaksi pengadaan (PO - GR) yang ditemukan untuk periode ini.
                    </div>
                @endforelse
            </div>
        @elseif($reportType === 'SM' && ($format ?? 'summary') === 'detailed')
            <!-- ============================================== -->
            <!-- FORMAT RINCIAN LENGKAP RESMI MUTASI STOK (SM)  -->
            <!-- ============================================== -->
            <div class="space-y-6 mb-8">
                @forelse($records as $idx => $sm)
                    <div class="border border-slate-300 rounded-xl overflow-hidden bg-white shadow-2xs" style="page-break-inside: avoid;">
                        <!-- Header Dokumen SM -->
                        <div class="bg-slate-100/90 border-b border-slate-300 px-4 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-extrabold bg-cyan-900 text-white">#{{ $idx + 1 }}</span>
                                <h4 class="font-mono font-bold text-slate-900 text-xs flex items-center gap-1.5">
                                    <span>SM: {{ $sm->doc_number }}</span>
                                    <span class="text-slate-400 font-sans font-normal">&bull; {{ date('d M Y', strtotime($sm->movement_date)) }}{{ $sm->created_at ? ', ' . date('H:i', strtotime($sm->created_at)) : '' }}</span>
                                </h4>
                            </div>
                            <div class="flex items-center gap-3 text-[10px] flex-wrap">
                                <span>Petugas: <strong class="text-slate-800">{{ $sm->creator_display }}</strong></span>
                                <span>&bull;</span>
                                <span class="px-2 py-0.5 rounded font-bold bg-emerald-100 text-emerald-800">{{ $sm->status }}</span>
                            </div>
                        </div>

                        <!-- Info Strip Rute & Wilayah -->
                        <div class="px-4 py-2 bg-slate-50 border-b border-slate-200 text-[10px] text-slate-600 flex flex-wrap items-center justify-between gap-2">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span>Rute: <strong class="text-slate-900">{{ $sm->source_wh }}</strong> ({{ $sm->source_wh_type }}) &rarr; <strong class="text-rose-700">{{ $sm->dest_wh }}</strong> ({{ $sm->dest_wh_type }})</span>
                                <span>&bull;</span>
                                <span>Wilayah: <strong class="text-slate-800">{{ $sm->source_tap_name ?? $sm->dest_tap_name ?? '-' }} ({{ $sm->source_cluster_name ?? $sm->dest_cluster_name ?? '-' }})</strong></span>
                            </div>
                            @if(!empty($sm->notes))
                                <div>
                                    <span>Keperluan/Catatan: <strong class="text-slate-800">{{ $sm->notes }}</strong></span>
                                </div>
                            @endif
                        </div>

                        <!-- Tabel Item Barang SM -->
                        <table class="w-full text-left text-[11px] border-collapse">
                            <thead class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200 text-[10px] uppercase">
                                <tr>
                                    <th class="py-2 px-3 text-center w-8">No</th>
                                    <th class="py-2 px-3">Kode SKU</th>
                                    <th class="py-2 px-3">Nama Produk Barang</th>
                                    <th class="py-2 px-3">Tipe</th>
                                    @if(config('erp.enable_iccid', false))
                                        <th class="py-2 px-3 text-center">Tracking</th>
                                    @endif
                                    <th class="py-2 px-3 text-right">Kuantitas Dipindahkan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($sm->items as $iIdx => $item)
                                    <tr>
                                        <td class="py-1.5 px-3 text-center text-slate-400 font-mono">{{ $iIdx + 1 }}</td>
                                        <td class="py-1.5 px-3 font-mono font-bold text-slate-700">{{ $item->sku }}</td>
                                        <td class="py-1.5 px-3 font-bold text-slate-900">{{ $item->product_name }}</td>
                                        <td class="py-1.5 px-3 text-slate-600">{{ $item->product_type }}</td>
                                        @if(config('erp.enable_iccid', false))
                                            <td class="py-1.5 px-3 text-center font-mono text-[10px] text-slate-600">{{ $item->tracking_method }}</td>
                                        @endif
                                        <td class="py-1.5 px-3 text-right font-mono font-bold text-cyan-900">{{ number_format($item->quantity) }} pcs</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ config('erp.enable_iccid', false) ? 6 : 5 }}" class="py-2.5 text-center text-slate-400 italic">Tidak ada item tercatat.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-slate-100/70 border-t border-slate-300 font-bold text-[11px]">
                                <tr>
                                    <td colspan="{{ config('erp.enable_iccid', false) ? 5 : 4 }}" class="py-2 px-3 text-right uppercase tracking-wider text-slate-700">Subtotal Mutasi ({{ count($sm->items) }} varian):</td>
                                    <td class="py-2 px-3 text-right font-mono font-extrabold text-cyan-900">{{ number_format($sm->total_qty ?? 0) }} pcs</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-400 border border-slate-300 rounded-xl bg-slate-50">
                        Tidak ada data transfer stok (SM) yang ditemukan untuk periode ini.
                    </div>
                @endforelse
            </div>
        @else
            <!-- Tabel Data Laporan (Mode Standar / Rekap Ringkas) -->
            <div class="mb-6 overflow-x-auto">
                <table class="w-full text-left text-[11px] border-collapse border border-slate-300">
                    <thead class="bg-slate-100 text-slate-800 font-bold">
                        <tr>
                            <th class="py-2 px-2.5 border border-slate-300 text-center w-8">No</th>
                            @foreach($headers as $header)
                                <th class="py-2 px-2.5 border border-slate-300">{{ $header }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($rows as $idx => $row)
                            <tr class="{{ $idx % 2 === 1 ? 'bg-slate-50/70' : 'bg-white' }}">
                                <td class="py-1.5 px-2.5 border border-slate-300 text-center font-mono text-slate-500">{{ $idx + 1 }}</td>
                                @foreach($row as $cell)
                                    <td class="py-1.5 px-2.5 border border-slate-300">{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($headers) + 1 }}" class="py-6 text-center text-slate-400 border border-slate-300">
                                    Tidak ada data yang ditemukan untuk periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Ringkasan Eksekutif -->
        <div class="mb-8 p-3 rounded-xl bg-slate-50 border border-slate-200 flex flex-wrap items-center justify-between text-xs gap-3">
            <span class="font-bold text-slate-700">Total Dokumen: {{ number_format($summary['total_records']) }} Transaksi</span>

            @if($reportType === 'PO_GR')
                @if(isset($summary['total_po_qty']))
                    <span class="font-bold text-slate-800 font-mono">
                        Total Pesan: {{ number_format($summary['total_po_qty']) }} pcs
                    </span>
                @endif
                @if(isset($summary['total_gr_qty']))
                    <span class="font-bold text-emerald-800 font-mono">
                        Total Masuk Fisik: {{ number_format($summary['total_gr_qty']) }} pcs
                    </span>
                @endif
            @endif

            @if($reportType === 'SM')
                <span class="font-extrabold text-cyan-900 font-mono">
                    Total Fisik Dipindahkan: {{ number_format($summary['total_amount'] ?? 0) }} pcs
                </span>
            @elseif(isset($summary['total_amount']))
                <span class="font-extrabold text-slate-900 font-mono">
                    Total Nilai: Rp {{ number_format($summary['total_amount'], 0, ',', '.') }}
                </span>
            @endif
        </div>

        <!-- Kolom Tanda Tangan 3 Pihak -->
        @php
            $authSignUser = auth()->user();
            $financeSignUser = \Illuminate\Support\Facades\DB::table('users')
                ->join('roles', 'users.role_id', '=', 'roles.id')
                ->where('roles.slug', 'finance')
                ->whereNotNull('users.signature')
                ->select('users.*')
                ->first();
            $headSignUser = \Illuminate\Support\Facades\DB::table('users')
                ->join('roles', 'users.role_id', '=', 'roles.id')
                ->whereIn('roles.slug', ['super_admin', 'branch_manager', 'director'])
                ->whereNotNull('users.signature')
                ->select('users.*')
                ->first();
        @endphp
        <div class="grid grid-cols-3 gap-6 pt-6 text-center border-t border-slate-200">
            <div>
                <p class="text-slate-500 font-medium mb-2">
                    {{ $reportType === 'SM' ? 'Dibuat Oleh (Petugas Mutasi / Admin),' : 'Dibuat Oleh (Admin Operasional),' }}
                </p>
                <div class="h-14 flex items-center justify-center">
                    @if($authSignUser && $authSignUser->signature && file_exists(public_path($authSignUser->signature)))
                        <img src="{{ asset($authSignUser->signature) }}" class="max-h-14 max-w-full object-contain" alt="TTD {{ $authSignUser->name }}">
                    @endif
                </div>
                <div class="border-b border-slate-400 w-36 mx-auto"></div>
                <p class="font-bold text-slate-800 mt-1">( {{ $authSignUser ? $authSignUser->name : 'Admin Operasional' }} )</p>
            </div>

            <div>
                <p class="text-slate-500 font-medium mb-2">
                    {{ $reportType === 'SM' ? 'Diserahkan Oleh (Kepala Gudang Asal),' : 'Diperiksa Oleh (Finance/Akunting),' }}
                </p>
                <div class="h-14 flex items-center justify-center">
                    @if($reportType !== 'SM' && $financeSignUser && file_exists(public_path($financeSignUser->signature)))
                        <img src="{{ asset($financeSignUser->signature) }}" class="max-h-14 max-w-full object-contain" alt="TTD Finance">
                    @endif
                </div>
                <div class="border-b border-slate-400 w-36 mx-auto"></div>
                <p class="font-bold text-slate-800 mt-1">
                    ( {{ $reportType === 'SM' ? 'Kepala Gudang Asal' : ($financeSignUser ? $financeSignUser->name : 'Finance TAP') }} )
                </p>
            </div>

            <div>
                <p class="text-slate-500 font-medium mb-2">
                    {{ $reportType === 'SM' ? 'Diterima Oleh (Kepala Gudang Tujuan),' : 'Disetujui Oleh (Pimpinan Cabang),' }}
                </p>
                <div class="h-14 flex items-center justify-center">
                    @if($reportType !== 'SM' && $headSignUser && file_exists(public_path($headSignUser->signature)))
                        <img src="{{ asset($headUser->signature ?? $headSignUser->signature) }}" class="max-h-14 max-w-full object-contain" alt="TTD Pimpinan">
                    @endif
                </div>
                <div class="border-b border-slate-400 w-36 mx-auto"></div>
                <p class="font-bold text-slate-800 mt-1">
                    ( {{ $reportType === 'SM' ? 'Kepala Gudang Tujuan' : ($headSignUser ? $headSignUser->name : 'Pimpinan Cabang') }} )
                </p>
            </div>
        </div>

        <div class="mt-8 pt-3 border-t border-slate-100 text-center text-[10px] text-slate-400">
            Dokumen ini dicetak otomatis secara terintegrasi melalui Sistem ERP PT Wahana Putra Yudha.
        </div>
    </div>

</body>
</html>

