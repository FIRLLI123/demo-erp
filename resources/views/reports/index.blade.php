<x-app-layout>
    <x-slot name="title">Pusat Laporan & Penarikan Data (Excel & PDF)</x-slot>
    <x-slot name="headerTitle">Pusat Laporan & Ekspor Data</x-slot>
    <x-slot name="badge">Reports & Analytics</x-slot>

    <div>
        <!-- Top Header Banner -->
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Pusat Laporan Operasional & Keuangan</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">PT WAHANA PUTRA YUDHA</span>
                </h2>
                <p class="text-xs text-slate-500">Penarikan data rekapitulasi pengadaan, mutasi stok, konversi repack, penjualan, dan keuangan dalam format Excel & PDF</p>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- PILIHAN MODUL LAPORAN (TABS PILLS)             -->
        <!-- ============================================== -->
        <div class="mb-6 bg-white p-2 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-1.5 overflow-x-auto">
            <a href="{{ route('reports.index', array_merge(request()->all(), ['type' => 'SALES'])) }}"
               class="px-3.5 py-2 rounded-xl text-xs font-extrabold flex items-center gap-1.5 shrink-0 transition-all {{ $reportType === 'SALES' ? 'bg-rose-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                <span>1. Penjualan (SO & GD)</span>
            </a>

            <a href="{{ route('reports.index', array_merge(request()->all(), ['type' => 'PO_GR'])) }}"
               class="px-3.5 py-2 rounded-xl text-xs font-extrabold flex items-center gap-1.5 shrink-0 transition-all {{ $reportType === 'PO_GR' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                <i data-lucide="truck" class="w-4 h-4"></i>
                <span>2. Pengadaan (PO & GR)</span>
            </a>

            <a href="{{ route('reports.index', array_merge(request()->all(), ['type' => 'SM'])) }}"
               class="px-3.5 py-2 rounded-xl text-xs font-extrabold flex items-center gap-1.5 shrink-0 transition-all {{ $reportType === 'SM' ? 'bg-cyan-700 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                <i data-lucide="arrow-left-right" class="w-4 h-4"></i>
                <span>3. Mutasi Gudang (SM)</span>
            </a>

            <a href="{{ route('reports.index', array_merge(request()->all(), ['type' => 'REPACK'])) }}"
               class="px-3.5 py-2 rounded-xl text-xs font-extrabold flex items-center gap-1.5 shrink-0 transition-all {{ $reportType === 'REPACK' ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                <i data-lucide="repeat" class="w-4 h-4"></i>
                <span>4. Repack (RO & RI)</span>
            </a>

            <a href="{{ route('reports.index', array_merge(request()->all(), ['type' => 'AR'])) }}"
               class="px-3.5 py-2 rounded-xl text-xs font-extrabold flex items-center gap-1.5 shrink-0 transition-all {{ $reportType === 'AR' ? 'bg-purple-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                <i data-lucide="receipt" class="w-4 h-4"></i>
                <span>5. Piutang Usaha (AR)</span>
            </a>

            <a href="{{ route('reports.index', array_merge(request()->all(), ['type' => 'CASH_BANK'])) }}"
               class="px-3.5 py-2 rounded-xl text-xs font-extrabold flex items-center gap-1.5 shrink-0 transition-all {{ $reportType === 'CASH_BANK' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                <i data-lucide="landmark" class="w-4 h-4"></i>
                <span>6. Kas & Bank (TTK & BBM)</span>
            </a>
        </div>

        <!-- ============================================== -->
        <!-- FILTER FORM & ACTION BUTTONS                   -->
        <!-- ============================================== -->
        <div class="mb-6 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <form method="GET" action="{{ route('reports.index') }}" class="space-y-4">
                <input type="hidden" name="type" value="{{ $reportType }}">

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Periode Mulai (Dari)</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Periode Selesai (Sampai)</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Filter TAP / Area</label>
                        <select name="tap_id" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none">
                            <option value="ALL">-- Semua TAP --</option>
                            @foreach($taps as $tap)
                                <option value="{{ $tap->id }}" {{ $tapId == $tap->id ? 'selected' : '' }}>
                                    {{ $tap->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Filter Gudang Spesifik</label>
                        <select name="warehouse_id" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none">
                            <option value="ALL">-- Semua Gudang --</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ $warehouseId == $wh->id ? 'selected' : '' }}>
                                    [{{ $wh->type }}] {{ $wh->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-3 border-t border-slate-100">
                    <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-xs transition-all">
                        <i data-lucide="filter" class="w-4 h-4"></i>
                        <span>Terapkan Filter Laporan</span>
                    </button>

                    <!-- Export Actions -->
                    <div class="flex items-center gap-2">
                        <a href="{{ route('reports.export-excel', request()->all()) }}" 
                           class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-extrabold flex items-center gap-1.5 shadow-sm transition-all">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                            <span>Download Excel / CSV</span>
                        </a>

                        <a href="{{ route('reports.print-pdf', request()->all()) }}" 
                           target="_blank"
                           class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-extrabold flex items-center gap-1.5 shadow-sm transition-all">
                            <i data-lucide="printer" class="w-4 h-4"></i>
                            <span>Cetak PDF / Print View</span>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- ============================================== -->
        <!-- REPORT TITLE & EXECUTIVE SUMMARY BANNER        -->
        <!-- ============================================== -->
        <div class="mb-4 p-4 rounded-2xl bg-white border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <span class="text-[10px] font-mono font-extrabold uppercase px-2 py-0.5 rounded bg-slate-100 text-slate-600">
                    LAPORAN RESMI
                </span>
                <h3 class="font-extrabold text-slate-900 text-sm mt-1">{{ $title }}</h3>
                <p class="text-xs text-slate-500">Periode: {{ date('d M Y', strtotime($startDate)) }} s/d {{ date('d M Y', strtotime($endDate)) }}</p>
            </div>

            <div class="flex items-center gap-6 text-right">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 block uppercase">Jumlah Transaksi:</span>
                    <span class="font-mono text-base font-extrabold text-slate-900">{{ number_format($summary['total_records']) }}</span>
                </div>

                @if(isset($summary['total_amount']))
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 block uppercase">Total Nilai / Volume:</span>
                        <span class="font-mono text-base font-extrabold text-rose-600">
                            {{ $reportType === 'SM' ? number_format($summary['total_amount']) . ' pcs' : 'Rp ' . number_format($summary['total_amount'], 0, ',', '.') }}
                        </span>
                    </div>
                @endif
            </div>
        </div>

        <!-- ============================================== -->
        <!-- TABLE PREVIEW OF REPORT                        -->
        <!-- ============================================== -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden mb-8">
            <div class="overflow-x-auto">
                @if($reportType === 'SALES')
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">No. SO</th>
                                <th class="py-3 px-4">Tanggal</th>
                                <th class="py-3 px-4">Toko / Outlet</th>
                                <th class="py-3 px-4">Sales Kanvaser</th>
                                <th class="py-3 px-4">Gudang</th>
                                <th class="py-3 px-4 text-center">Channel</th>
                                <th class="py-3 px-4">No. GD (Surat Jalan)</th>
                                <th class="py-3 px-4">No. Faktur AR</th>
                                <th class="py-3 px-4 text-center">Status SO</th>
                                <th class="py-3 px-4 text-right">Total Penjualan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($records as $r)
                                <tr class="hover:bg-slate-50">
                                    <td class="py-3 px-4 font-mono font-bold text-rose-800">{{ $r->doc_number }}</td>
                                    <td class="py-3 px-4 text-slate-600">{{ date('d M Y', strtotime($r->order_date)) }}</td>
                                    <td class="py-3 px-4 font-bold text-slate-900">{{ $r->customer_name }}</td>
                                    <td class="py-3 px-4 text-slate-800 font-semibold">{{ $r->sales_name }}</td>
                                    <td class="py-3 px-4 text-slate-600">{{ $r->warehouse_name }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $r->channel === 'MOBILE_SALES' ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-700' }}">
                                            {{ $r->channel }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 font-mono text-slate-600">{{ $r->gd_number ?? '-' }}</td>
                                    <td class="py-3 px-4 font-mono text-amber-800 font-bold">{{ $r->ar_number ?? '-' }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                            {{ $r->status }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-extrabold text-slate-900">
                                        Rp {{ number_format($r->grand_total, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="py-8 text-center text-slate-400">Tidak ada transaksi pada filter periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                @elseif($reportType === 'PO_GR')
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">No. PO</th>
                                <th class="py-3 px-4">Tgl PO</th>
                                <th class="py-3 px-4">Supplier</th>
                                <th class="py-3 px-4">TAP Tujuan</th>
                                <th class="py-3 px-4">No. GR (Penerimaan)</th>
                                <th class="py-3 px-4">Tgl Diterima</th>
                                <th class="py-3 px-4">Gudang Penerima</th>
                                <th class="py-3 px-4 text-center">Status PO</th>
                                <th class="py-3 px-4 text-right">Nilai Pengadaan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($records as $r)
                                <tr class="hover:bg-slate-50">
                                    <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ $r->doc_number }}</td>
                                    <td class="py-3 px-4 text-slate-600">{{ date('d M Y', strtotime($r->order_date)) }}</td>
                                    <td class="py-3 px-4 font-bold text-slate-900">{{ $r->supplier_name }}</td>
                                    <td class="py-3 px-4 text-slate-700">{{ $r->tap_name }}</td>
                                    <td class="py-3 px-4 font-mono font-bold text-emerald-800">{{ $r->gr_number ?? '-' }}</td>
                                    <td class="py-3 px-4 text-slate-600">{{ $r->received_date ? date('d M Y', strtotime($r->received_date)) : '-' }}</td>
                                    <td class="py-3 px-4 text-slate-600">{{ $r->destination_warehouse ?? '-' }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                            {{ $r->status }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-extrabold text-emerald-700">
                                        Rp {{ number_format($r->total_amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-8 text-center text-slate-400">Tidak ada transaksi pada filter periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                @elseif($reportType === 'SM')
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">No. SM</th>
                                <th class="py-3 px-4">Tanggal Mutasi</th>
                                <th class="py-3 px-4">Gudang Asal</th>
                                <th class="py-3 px-4">Gudang Tujuan</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($records as $r)
                                <tr class="hover:bg-slate-50">
                                    <td class="py-3 px-4 font-mono font-bold text-cyan-800">{{ $r->doc_number }}</td>
                                    <td class="py-3 px-4 text-slate-600">{{ date('d M Y', strtotime($r->movement_date)) }}</td>
                                    <td class="py-3 px-4 font-semibold text-slate-800">{{ $r->source_wh }}</td>
                                    <td class="py-3 px-4 font-bold text-rose-600">{{ $r->dest_wh }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            {{ $r->status }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-500">{{ $r->notes ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400">Tidak ada transaksi pada filter periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                @elseif($reportType === 'REPACK')
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">No. Dokumen</th>
                                <th class="py-3 px-4 text-center">Tipe</th>
                                <th class="py-3 px-4">Tanggal Repack</th>
                                <th class="py-3 px-4">Gudang</th>
                                <th class="py-3 px-4">Ref Dokumen</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-right">Nilai Konversi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($records as $r)
                                <tr class="hover:bg-slate-50">
                                    <td class="py-3 px-4 font-mono font-bold text-amber-800">{{ $r->doc_number }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $r->doc_type === 'RPKOUT' ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800' }}">
                                            {{ $r->doc_type }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600">{{ date('d M Y', strtotime($r->repack_date)) }}</td>
                                    <td class="py-3 px-4 text-slate-800 font-semibold">{{ $r->warehouse_name }}</td>
                                    <td class="py-3 px-4 font-mono text-slate-500">{{ $r->ref_doc_number ?? '-' }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                            {{ $r->status }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-extrabold text-slate-900">
                                        Rp {{ number_format($r->total_amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-slate-400">Tidak ada transaksi pada filter periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                @elseif($reportType === 'AR')
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">No. Faktur AR</th>
                                <th class="py-3 px-4">Ref SO</th>
                                <th class="py-3 px-4">Toko / Outlet</th>
                                <th class="py-3 px-4">Sales Penanggung Jawab</th>
                                <th class="py-3 px-4">Tgl Jatuh Tempo</th>
                                <th class="py-3 px-4 text-right">Total Tagihan</th>
                                <th class="py-3 px-4 text-right">Terbayar</th>
                                <th class="py-3 px-4 text-right">Sisa Piutang</th>
                                <th class="py-3 px-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($records as $r)
                                <tr class="hover:bg-slate-50">
                                    <td class="py-3 px-4 font-mono font-bold text-amber-800">{{ $r->doc_number }}</td>
                                    <td class="py-3 px-4 font-mono text-slate-600">{{ $r->so_number ?? '-' }}</td>
                                    <td class="py-3 px-4 font-bold text-slate-900">{{ $r->customer_name }}</td>
                                    <td class="py-3 px-4 text-slate-800">{{ $r->sales_name ?? 'TAP Admin' }}</td>
                                    <td class="py-3 px-4 text-slate-600">{{ date('d M Y', strtotime($r->due_date)) }}</td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-slate-800">Rp {{ number_format($r->total_amount, 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-right font-mono text-emerald-600">Rp {{ number_format($r->paid_amount, 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-right font-mono font-extrabold text-rose-600">Rp {{ number_format($r->balance_amount, 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $r->status === 'PAID' ? 'bg-emerald-100 text-emerald-800' : ($r->status === 'PARTIAL' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                            {{ $r->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-8 text-center text-slate-400">Tidak ada transaksi pada filter periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                @elseif($reportType === 'CASH_BANK')
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">No. Dokumen BBM</th>
                                <th class="py-3 px-4 text-center">Tipe BBM</th>
                                <th class="py-3 px-4">Ref TTK</th>
                                <th class="py-3 px-4">Tgl Masuk</th>
                                <th class="py-3 px-4">Toko / Outlet</th>
                                <th class="py-3 px-4">Kas / Bank Penerima</th>
                                <th class="py-3 px-4">Metode Bayar</th>
                                <th class="py-3 px-4">Kasir</th>
                                <th class="py-3 px-4 text-right">Nominal Masuk</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($records as $r)
                                <tr class="hover:bg-slate-50">
                                    <td class="py-3 px-4 font-mono font-bold text-indigo-900">{{ $r->doc_number }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $r->receipt_type === 'TUNAI' ? 'bg-emerald-100 text-emerald-800' : 'bg-indigo-100 text-indigo-800' }}">
                                            {{ $r->receipt_type }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 font-mono font-bold text-emerald-700">{{ $r->ttk_number }}</td>
                                    <td class="py-3 px-4 text-slate-600">{{ date('d M Y', strtotime($r->receipt_date)) }}</td>
                                    <td class="py-3 px-4 font-bold text-slate-900">{{ $r->customer_name }}</td>
                                    <td class="py-3 px-4 text-slate-800 font-semibold">{{ $r->bank_name }}</td>
                                    <td class="py-3 px-4 text-slate-700">{{ $r->payment_method }}</td>
                                    <td class="py-3 px-4 text-slate-600">{{ $r->cashier_name }}</td>
                                    <td class="py-3 px-4 text-right font-mono font-extrabold text-indigo-950">
                                        Rp {{ number_format($r->amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-8 text-center text-slate-400">Tidak ada transaksi pada filter periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

    </div>
</x-app-layout>

