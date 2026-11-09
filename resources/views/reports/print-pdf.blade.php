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
    <div class="max-w-6xl mx-auto mb-4 no-print flex items-center justify-between bg-white p-4 rounded-xl shadow-sm border border-slate-200">
        <a href="{{ url()->previous() ?: route('reports.index') }}" class="px-3.5 py-1.5 rounded-lg bg-slate-100 text-slate-700 font-bold hover:bg-slate-200">
            &larr; Kembali ke Pusat Laporan
        </a>
        <div class="flex items-center gap-2">
            <span class="text-xs text-slate-500 font-medium">Tips: Di dialog print, pilih "Save as PDF" untuk mengunduh PDF</span>
            <button onclick="window.print()" class="px-5 py-2 rounded-xl bg-slate-900 text-white font-bold hover:bg-slate-800 flex items-center gap-2 shadow-sm">
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

        <!-- Tabel Data Laporan -->
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

        <!-- Ringkasan Eksekutif -->
        <div class="mb-8 p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
            <span class="font-bold text-slate-700">Total Baris Data: {{ number_format($summary['total_records']) }} Transaksi</span>
            @if(isset($summary['total_amount']))
                <span class="font-extrabold text-slate-900 font-mono">
                    Total Nilai / Volume: 
                    {{ $reportType === 'SM' ? number_format($summary['total_amount']) . ' pcs' : 'Rp ' . number_format($summary['total_amount'], 0, ',', '.') }}
                </span>
            @endif
        </div>

        <!-- Kolom Tanda Tangan 3 Pihak -->
        <div class="grid grid-cols-3 gap-6 pt-6 text-center border-t border-slate-200">
            <div>
                <p class="text-slate-500 font-medium mb-16">Dibuat Oleh (Admin Operasional),</p>
                <div class="border-b border-slate-400 w-36 mx-auto"></div>
                <p class="font-bold text-slate-800 mt-1">( Admin TAP )</p>
            </div>

            <div>
                <p class="text-slate-500 font-medium mb-16">Diperiksa Oleh (Finance/Akunting),</p>
                <div class="border-b border-slate-400 w-36 mx-auto"></div>
                <p class="font-bold text-slate-800 mt-1">( Finance TAP )</p>
            </div>

            <div>
                <p class="text-slate-500 font-medium mb-16">Disetujui Oleh (Pimpinan Cabang),</p>
                <div class="border-b border-slate-400 w-36 mx-auto"></div>
                <p class="font-bold text-slate-800 mt-1">( Pimpinan PT Wahana Putra Yudha )</p>
            </div>
        </div>

        <div class="mt-8 pt-3 border-t border-slate-100 text-center text-[10px] text-slate-400">
            Dokumen ini dicetak otomatis secara terintegrasi melalui Sistem ERP PT Wahana Putra Yudha.
        </div>
    </div>

</body>
</html>

