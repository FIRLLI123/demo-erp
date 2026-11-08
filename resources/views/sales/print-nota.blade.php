<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Penjualan - {{ $so->doc_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 p-4 sm:p-8 font-sans antialiased text-xs">

    <!-- Print Button Floating Header -->
    <div class="max-w-3xl mx-auto mb-4 no-print flex items-center justify-between bg-white p-4 rounded-xl shadow-sm border border-slate-200">
        <a href="{{ route('sales.index') }}" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 font-bold hover:bg-slate-200">
            &larr; Kembali ke Sales Order
        </a>
        <button onclick="window.print()" class="px-5 py-2 rounded-xl bg-slate-900 text-white font-bold hover:bg-slate-800 flex items-center gap-2 shadow-sm">
            <span>Cetak Nota Sekarang</span>
        </button>
    </div>

    <!-- Official Invoice / Nota Container -->
    <div class="max-w-3xl mx-auto bg-white p-8 rounded-2xl shadow-md border border-slate-200">
        
        <!-- Header Perusahaan & Nota -->
        <div class="flex items-start justify-between pb-6 mb-6 border-b-2 border-slate-900">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xl font-extrabold tracking-tight text-slate-900">DISTRI<span class="text-red-600">POS</span></span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700">TELKOMSEL AUTHORIZED</span>
                </div>
                <p class="text-[11px] text-slate-500 font-medium mt-1">PT DISTRIBUSI TELEKOMUNIKASI NUSANTARA</p>
                <p class="text-[11px] text-slate-400">Gedung Graha Distribusi Lt. 3, Jakarta Selatan</p>
            </div>

            <div class="text-right">
                <span class="px-3 py-1 rounded-md text-xs font-mono font-extrabold bg-slate-900 text-white">
                    NOTA PENJUALAN & SURAT JALAN
                </span>
                <h3 class="text-base font-extrabold font-mono text-slate-900 mt-2">{{ $so->doc_number }}</h3>
                <p class="text-[11px] text-slate-500 font-medium">Tanggal: {{ date('d F Y', strtotime($so->order_date)) }}</p>
            </div>
        </div>

        <!-- Meta Penerima & Petugas -->
        <div class="grid grid-cols-2 gap-6 mb-6">
            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80">
                <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block mb-1">Kepada Outlet / Pelanggan:</span>
                <p class="text-sm font-bold text-slate-900">{{ $so->customer_name }}</p>
                <p class="text-[11px] font-mono text-slate-500">Kode: {{ $so->customer_code }}</p>
                <p class="text-[11px] text-slate-600 mt-1">{{ $so->customer_address }}</p>
                <p class="text-[11px] text-slate-500">Telp: {{ $so->customer_phone ?? '-' }}</p>
            </div>

            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80">
                <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block mb-1">Informasi Pengiriman:</span>
                <div class="space-y-1">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Sales Kanvaser:</span>
                        <span class="font-bold text-slate-800">{{ $so->sales_name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Gudang Asal:</span>
                        <span class="font-bold text-slate-800">{{ $so->warehouse_name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">No. Surat Jalan (GD):</span>
                        <span class="font-mono font-bold text-slate-900">{{ $gd ? $gd->doc_number : '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">No. Faktur (AR):</span>
                        <span class="font-mono font-bold text-slate-900">{{ $ar ? $ar->doc_number : '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Barang -->
        <div class="border border-slate-200 rounded-xl overflow-hidden mb-6">
            <table class="w-full text-left">
                <thead class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-2.5 px-4 w-12 text-center">No</th>
                        <th class="py-2.5 px-4">Nama Produk & Deskripsi</th>
                        <th class="py-2.5 px-4 text-center">Qty</th>
                        <th class="py-2.5 px-4 text-right">Harga Jual Satuan</th>
                        <th class="py-2.5 px-4 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @foreach($items as $idx => $it)
                        <tr>
                            <td class="py-3 px-4 text-center text-slate-400 font-mono">{{ $idx + 1 }}</td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-900">{{ $it->product_name }}</span>
                                <span class="text-[10px] font-mono text-slate-400 block">{{ $it->sku }} (Tracking: {{ $it->tracking_method }})</span>
                            </td>
                            <td class="py-3 px-4 text-center font-mono font-bold text-slate-800">{{ number_format($it->quantity) }} pcs</td>
                            <td class="py-3 px-4 text-right font-mono text-slate-700">Rp {{ number_format($it->unit_price, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right font-mono font-extrabold text-slate-900">Rp {{ number_format($it->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50 font-bold border-t-2 border-slate-300">
                    <tr>
                        <td colspan="4" class="py-3 px-4 text-right uppercase text-slate-600">Total Tagihan (Grand Total):</td>
                        <td class="py-3 px-4 text-right text-base font-extrabold font-mono text-slate-900">
                            Rp {{ number_format($so->grand_total, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Tanda Tangan 3 Kolom -->
        <div class="grid grid-cols-3 gap-6 pt-6 text-center border-t border-slate-200">
            <div>
                <p class="text-slate-500 font-medium mb-16">Penerima Toko / Outlet,</p>
                <div class="border-b border-slate-400 w-36 mx-auto"></div>
                <p class="font-bold text-slate-800 mt-1">( {{ $so->customer_name }} )</p>
            </div>

            <div>
                <p class="text-slate-500 font-medium mb-16">Sales Kanvaser,</p>
                <div class="border-b border-slate-400 w-36 mx-auto"></div>
                <p class="font-bold text-slate-800 mt-1">( {{ $so->sales_name }} )</p>
            </div>

            <div>
                <p class="text-slate-500 font-medium mb-16">Gudang / Petugas TAP,</p>
                <div class="border-b border-slate-400 w-36 mx-auto"></div>
                <p class="font-bold text-slate-800 mt-1">( Admin TAP )</p>
            </div>
        </div>

        <div class="mt-8 pt-4 border-t border-slate-100 text-center text-[10px] text-slate-400">
            Dokumen sah dicetak secara otomatis oleh sistem DISTRIPOS ERP. Harap simpan sebagai bukti transaksi penjualan.
        </div>
    </div>

</body>
</html>

