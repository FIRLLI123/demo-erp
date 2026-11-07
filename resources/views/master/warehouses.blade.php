<x-app-layout>
    <x-slot name="title">Master Data Gudang</x-slot>
    <x-slot name="headerTitle">Gudang & Lokasi Stok</x-slot>
    <x-slot name="badge">MAIN • INVENTORY • SALES</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900">Struktur Gudang 3 Kategori</h2>
            <p class="text-xs text-slate-500">Mencakup Gudang Utama TAP (MAIN), Gudang Rak Penyimpanan (INV), dan Motor Kanvas Sales (SALES)</p>
        </div>
        <button class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-sm transition-all">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Tambah Gudang Baru</span>
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
        <div class="bg-slate-900 text-white p-5 rounded-2xl border border-slate-800 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Gudang Penerimaan</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-white/10 text-white font-mono">MAIN</span>
            </div>
            <h3 class="text-lg font-bold">Gudang Utama TAP</h3>
            <p class="text-xs text-slate-400 mt-1">Pintu masuk penerimaan barang dari supplier (Goods Received / GR).</p>
        </div>

        <div class="bg-cyan-900 text-white p-5 rounded-2xl border border-cyan-800 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-cyan-200">Gudang Penyangga</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-white/10 text-white font-mono">INVENTORY</span>
            </div>
            <h3 class="text-lg font-bold">Gudang Persediaan TAP</h3>
            <p class="text-xs text-cyan-200 mt-1">Penyimpanan buffer stock TAP untuk disiapkan ke sales force.</p>
        </div>

        <div class="bg-rose-900 text-white p-5 rounded-2xl border border-rose-800 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-rose-200">Gudang Kanvas</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-white/10 text-white font-mono">SALES</span>
            </div>
            <h3 class="text-lg font-bold">Gudang Mobile Sales</h3>
            <p class="text-xs text-rose-200 mt-1">Stok bergerak yang dibawa oleh sales force saat canvasing outlet.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-6">Kode Gudang</th>
                        <th class="py-3.5 px-4">Nama Gudang</th>
                        <th class="py-3.5 px-4">Tipe Gudang</th>
                        <th class="py-3.5 px-4">TAP Terkait</th>
                        <th class="py-3.5 px-4">Sales Pemegang</th>
                        <th class="py-3.5 px-6 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @foreach($warehouses as $wh)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-4 px-6 font-mono font-bold text-slate-900">{{ $wh->code }}</td>
                            <td class="py-4 px-4 font-bold text-slate-800 text-sm">{{ $wh->name }}</td>
                            <td class="py-4 px-4">
                                @if($wh->type === 'MAIN')
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-slate-900 text-white font-mono">
                                        MAIN WAREHOUSE
                                    </span>
                                @elseif($wh->type === 'INVENTORY')
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-cyan-700 text-white font-mono">
                                        INVENTORY BUFFER
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-rose-600 text-white font-mono">
                                        SALES CANVAS
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-slate-700 font-semibold">{{ $wh->tap_name }}</td>
                            <td class="py-4 px-4">
                                @if($wh->sales_name)
                                    <span class="font-bold text-rose-600">{{ $wh->sales_name }}</span>
                                @else
                                    <span class="text-slate-400 italic">Internal TAP</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    {{ $wh->status }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

