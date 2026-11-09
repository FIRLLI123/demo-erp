<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi Pembayaran TTK - {{ $payment->doc_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 p-4 sm:p-8 font-sans antialiased text-xs">

    <!-- Floating Top Bar -->
    <div class="max-w-3xl mx-auto mb-4 no-print flex items-center justify-between bg-white p-4 rounded-xl shadow-sm border border-slate-200">
        <a href="{{ route('finance.index') }}" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 font-bold hover:bg-slate-200">
            &larr; Kembali ke Keuangan
        </a>
        <button onclick="window.print()" class="px-5 py-2 rounded-xl bg-slate-900 text-white font-bold hover:bg-slate-800 flex items-center gap-2 shadow-sm">
            <span>Cetak Kwitansi TTK</span>
        </button>
    </div>

    <!-- Official TTK Receipt Container -->
    <div class="max-w-3xl mx-auto bg-white p-8 rounded-2xl shadow-md border-2 border-slate-300">
        
        <!-- Header Perusahaan -->
        <div class="flex items-start justify-between pb-4 mb-4 border-b-2 border-slate-900">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xl font-extrabold tracking-tight text-slate-900">WAHANA<span class="text-rose-600">POS</span></span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700">TELKOMSEL AUTHORIZED PARTNER</span>
                </div>
                <p class="text-sm font-extrabold text-slate-900 mt-1">PT WAHANA PUTRA YUDHA</p>
                <p class="text-[10px] text-slate-500 font-medium">Divisi Keuangan & Pelunasan Piutang TAP</p>
            </div>

            <div class="text-right">
                <span class="px-3 py-1 rounded text-xs font-mono font-extrabold bg-emerald-600 text-white">
                    TANDA TERIMA KASIR (TTK)
                </span>
                <h3 class="text-base font-extrabold font-mono text-slate-900 mt-2">{{ $payment->doc_number }}</h3>
                <p class="text-[11px] text-slate-500 font-medium">Tanggal: {{ date('d F Y', strtotime($payment->payment_date)) }}</p>
            </div>
        </div>

        <!-- Detail Pembayaran Grid -->
        <div class="grid grid-cols-2 gap-4 mb-5 bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs">
            <div>
                <span class="text-slate-400 block text-[10px]">Telah Diterima Dari Toko / Pelanggan:</span>
                <span class="font-extrabold text-slate-900 text-sm block">{{ $payment->customer_name }}</span>
                <span class="text-slate-500 block">Kode: {{ $payment->customer_code }} &bull; Telp: {{ $payment->customer_phone ?? '-' }}</span>
                <span class="text-slate-400 block text-[11px] mt-0.5">{{ $payment->customer_address ?? '' }}</span>
            </div>

            <div class="text-right">
                <span class="text-slate-400 block text-[10px]">Metode Pembayaran & Pencatatan:</span>
                <div class="mt-1 flex items-center justify-end gap-2">
                    <span class="px-2.5 py-0.5 rounded text-[10px] font-mono font-extrabold bg-slate-900 text-white">
                        {{ $payment->payment_method }}
                    </span>
                    @if($bbm)
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-extrabold {{ $bbm->receipt_type === 'TUNAI' ? 'bg-emerald-100 text-emerald-800' : 'bg-indigo-100 text-indigo-800' }}">
                            {{ $bbm->receipt_type === 'TUNAI' ? 'BBM TUNAI' : 'BBM NON TUNAI' }}
                        </span>
                    @endif
                </div>
                @if($bbm)
                    <span class="block font-mono text-[11px] font-bold text-slate-700 mt-1">
                        {{ $bbm->bank_name }} (Ref: {{ $bbm->doc_number }})
                    </span>
                @endif
                <span class="block text-slate-500 text-[11px] mt-0.5">Kasir: {{ $payment->cashier_name }}</span>
            </div>
        </div>

        <!-- Tabel Rincian Alokasi Faktur AR yang Dilunasi (Multi-AR) -->
        <div class="mb-5">
            <span class="text-xs font-extrabold text-slate-800 block mb-2">Rincian Faktur Piutang (AR) yang Dibayarkan:</span>
            <div class="border border-slate-200 rounded-xl overflow-hidden">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3">No. Faktur AR</th>
                            <th class="py-2.5 px-3">Ref SO & Channel</th>
                            <th class="py-2.5 px-3">Sales Penanggung Jawab</th>
                            <th class="py-2.5 px-3 text-right">Saldo Sebelum</th>
                            <th class="py-2.5 px-3 text-right">Nominal Bayar</th>
                            <th class="py-2.5 px-3 text-right">Sisa Saldo</th>
                            <th class="py-2.5 px-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($allocations as $alloc)
                            <tr>
                                <td class="py-2.5 px-3 font-mono font-bold text-amber-900">
                                    {{ $alloc->ar_number }}
                                </td>
                                <td class="py-2.5 px-3">
                                    <span class="font-mono text-slate-700">{{ $alloc->so_number ?? '-' }}</span>
                                    @if($alloc->so_channel === 'MOBILE_SALES')
                                        <span class="ml-1 px-1.5 py-0.2 rounded text-[9px] font-bold bg-purple-100 text-purple-700">MOBILE</span>
                                    @else
                                        <span class="ml-1 px-1.5 py-0.2 rounded text-[9px] font-bold bg-slate-100 text-slate-600">OFFICE</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 font-semibold text-slate-800">
                                    {{ $alloc->sales_name ?? 'TAP Admin' }}
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono text-slate-500">
                                    Rp {{ number_format($alloc->balance_before, 0, ',', '.') }}
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono font-extrabold text-emerald-700">
                                    Rp {{ number_format($alloc->amount, 0, ',', '.') }}
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-700">
                                    Rp {{ number_format($alloc->balance_after, 0, ',', '.') }}
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    @if($alloc->ar_status === 'PAID')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">LUNAS</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">PARTIAL</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-4 text-center text-slate-400">
                                    Tidak ada rincian alokasi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Nominal Pembayaran Banner -->
        <div class="p-4 rounded-xl bg-emerald-50 border-2 border-emerald-500/40 mb-6 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-emerald-800 uppercase block">Total Pembayaran Diterima Kasir:</span>
                <div class="text-2xl font-extrabold font-mono text-emerald-950 mt-0.5">
                    Rp {{ number_format($payment->amount, 0, ',', '.') }}
                </div>
            </div>
            <div class="text-right">
                <span class="px-3 py-1 rounded-md text-[11px] font-mono font-bold bg-emerald-600 text-white uppercase">
                    PEMBAYARAN SAH ({{ count($allocations) }} FAKTUR)
                </span>
            </div>
        </div>

        <!-- Keterangan / Notes -->
        @if($payment->notes)
            <div class="mb-6 p-3 rounded-lg bg-slate-50 border border-slate-200 text-slate-600 text-xs">
                <span class="font-bold text-slate-700">Catatan:</span> {{ $payment->notes }}
            </div>
        @endif

        <!-- Tanda Tangan Kasir & Customer -->
        <div class="grid grid-cols-2 gap-8 pt-4 text-center border-t border-slate-200">
            <div>
                <p class="text-slate-500 font-medium mb-16">Penyetor / Toko Pelanggan,</p>
                <div class="border-b border-slate-400 w-44 mx-auto"></div>
                <p class="font-bold text-slate-800 mt-1">( {{ $payment->customer_name }} )</p>
            </div>

            <div>
                <p class="text-slate-500 font-medium mb-16">Kasir / Finance Penerima,</p>
                <div class="border-b border-slate-400 w-44 mx-auto"></div>
                <p class="font-bold text-slate-800 mt-1">( {{ $payment->cashier_name }} )</p>
            </div>
        </div>

        <div class="mt-8 pt-3 border-t border-slate-100 text-center text-[10px] text-slate-400">
            Kwitansi ini merupakan bukti pembayaran sah yang dihasilkan secara otomatis oleh sistem ERP PT WAHANA PUTRA YUDHA.
        </div>
    </div>

</body>
</html>
