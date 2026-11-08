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
    <div class="max-w-2xl mx-auto mb-4 no-print flex items-center justify-between bg-white p-4 rounded-xl shadow-sm border border-slate-200">
        <a href="{{ route('finance.index') }}" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 font-bold hover:bg-slate-200">
            &larr; Kembali ke Keuangan
        </a>
        <button onclick="window.print()" class="px-5 py-2 rounded-xl bg-slate-900 text-white font-bold hover:bg-slate-800 flex items-center gap-2 shadow-sm">
            <span>Cetak Kwitansi TTK</span>
        </button>
    </div>

    <!-- Official TTK Receipt Container -->
    <div class="max-w-2xl mx-auto bg-white p-8 rounded-2xl shadow-md border-2 border-slate-300">
        
        <!-- Header Perusahaan -->
        <div class="flex items-start justify-between pb-4 mb-4 border-b-2 border-slate-900">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xl font-extrabold tracking-tight text-slate-900">DISTRI<span class="text-rose-600">POS</span></span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700">TELKOMSEL AUTHORIZED</span>
                </div>
                <p class="text-[11px] text-slate-500 font-medium mt-1">PT DISTRIBUSI TELEKOMUNIKASI NUSANTARA</p>
                <p class="text-[10px] text-slate-400">Kasir Keuangan TAP &bull; Divisi Finance & Piutang</p>
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
        <div class="space-y-3 mb-6 bg-slate-50 p-4 rounded-xl border border-slate-200">
            <div class="grid grid-cols-3 gap-2">
                <span class="text-slate-500 font-medium">Telah Diterima Dari:</span>
                <span class="col-span-2 font-bold text-slate-900">{{ $payment->customer_name }} ({{ $payment->customer_code }})</span>
            </div>

            <div class="grid grid-cols-3 gap-2">
                <span class="text-slate-500 font-medium">Untuk Pembayaran:</span>
                <div class="col-span-2">
                    <span class="font-bold text-slate-800">Pelunasan Faktur Piutang: </span>
                    <span class="font-mono font-extrabold text-amber-700">{{ $payment->ar_number }}</span>
                    @if($payment->so_number)
                        <span class="text-slate-400"> (Ref SO: {{ $payment->so_number }})</span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2">
                <span class="text-slate-500 font-medium">Metode Pembayaran:</span>
                <div class="col-span-2 flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-900 text-white">
                        {{ $payment->payment_method }}
                    </span>
                    @if($bbm)
                        <span class="font-mono text-xs text-indigo-700 font-bold">
                            &bull; {{ $bbm->bank_name }} (No. BBM: {{ $bbm->doc_number }})
                        </span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2">
                <span class="text-slate-500 font-medium">Keterangan:</span>
                <span class="col-span-2 text-slate-700 italic">{{ $payment->notes ?? '-' }}</span>
            </div>
        </div>

        <!-- Nominal Pembayaran Banner -->
        <div class="p-4 rounded-xl bg-emerald-50 border-2 border-emerald-500/40 mb-6 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-emerald-800 uppercase block">Jumlah Pembayaran Diterima:</span>
                <div class="text-2xl font-extrabold font-mono text-emerald-950 mt-0.5">
                    Rp {{ number_format($payment->amount, 0, ',', '.') }}
                </div>
            </div>
            <div class="text-right">
                <span class="px-2.5 py-1 rounded-md text-[10px] font-mono font-bold bg-emerald-600 text-white uppercase">
                    LUNAS SAH
                </span>
            </div>
        </div>

        <!-- Tanda Tangan Kasir & Customer -->
        <div class="grid grid-cols-2 gap-8 pt-4 text-center border-t border-slate-200">
            <div>
                <p class="text-slate-500 font-medium mb-16">Penyetor / Toko Pelanggan,</p>
                <div class="border-b border-slate-400 w-36 mx-auto"></div>
                <p class="font-bold text-slate-800 mt-1">( {{ $payment->customer_name }} )</p>
            </div>

            <div>
                <p class="text-slate-500 font-medium mb-16">Kasir / Finance Penerima,</p>
                <div class="border-b border-slate-400 w-36 mx-auto"></div>
                <p class="font-bold text-slate-800 mt-1">( {{ $payment->cashier_name }} )</p>
            </div>
        </div>

        <div class="mt-8 pt-3 border-t border-slate-100 text-center text-[10px] text-slate-400">
            Kwitansi ini merupakan bukti pembayaran sah yang dihasilkan oleh sistem DISTRIPOS ERP.
        </div>
    </div>

</body>
</html>

