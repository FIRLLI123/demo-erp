<x-app-layout>
    <x-slot name="title">Katalog Produk</x-slot>
    <x-slot name="headerTitle">Master Data Produk</x-slot>
    <x-slot name="badge">ICCID vs Quantity Tracking</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900">Katalog Barang Telkomsel</h2>
            <p class="text-xs text-slate-500">Mendukung 2 metode inventory: ICCID Serial (SA/Perdana) dan Quantity Based (Voucher & Modem)</p>
        </div>
        <button class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-sm transition-all">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Tambah Produk Baru</span>
        </button>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-6">SKU & Nama Produk</th>
                        <th class="py-3.5 px-4">Kategori / Tipe</th>
                        <th class="py-3.5 px-4">Metode Tracking</th>
                        <th class="py-3.5 px-4 text-right">Harga Modal</th>
                        <th class="py-3.5 px-4 text-right">Harga Jual</th>
                        <th class="py-3.5 px-4 text-center">Margin</th>
                        <th class="py-3.5 px-6 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @foreach($products as $prod)
                        @php
                            $margin = $prod->selling_price > 0 ? (($prod->selling_price - $prod->cost_price) / $prod->selling_price) * 100 : 0;
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-4 px-6">
                                <span class="font-mono text-[11px] font-bold text-slate-500 block">{{ $prod->sku }}</span>
                                <span class="font-bold text-slate-900 text-sm mt-0.5 block">{{ $prod->name }}</span>
                            </td>
                            <td class="py-4 px-4">
                                @if($prod->type === 'SA')
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Starter Pack (SA)
                                    </span>
                                @elseif($prod->type === 'VOUCHER')
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        Voucher Fisik
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-cyan-50 text-cyan-700 border border-cyan-200">
                                        Modem & Device
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                @if($prod->tracking_method === 'ICCID')
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded border border-purple-200">
                                        <i data-lucide="scan-barcode" class="w-3.5 h-3.5 text-purple-600"></i> ICCID Based
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200">
                                        <i data-lucide="layers" class="w-3.5 h-3.5 text-blue-600"></i> Quantity Based
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-right font-mono text-slate-600">
                                Rp {{ number_format($prod->cost_price, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-4 text-right font-mono font-bold text-slate-900">
                                Rp {{ number_format($prod->selling_price, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    +{{ number_format($margin, 1) }}%
                                </span>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    {{ $prod->status }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

