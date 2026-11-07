<x-app-layout>
    <x-slot name="title">Repack Out & Repack In</x-slot>
    <x-slot name="headerTitle">Proses Repack (Pecah / Kemas Paket)</x-slot>
    <x-slot name="badge">DocType: RPKOUT & RPKIN</x-slot>

    <div class="mb-8 p-5 bg-white rounded-2xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 uppercase font-mono">
                Alur Repack: RPKOUT &rarr; RPKIN
            </span>
            <h2 class="text-base font-bold text-slate-900 mt-1">Pembongkaran Bundle & Pengemasan Ulang Barang</h2>
            <p class="text-xs text-slate-500 mt-0.5">Misalnya membongkar 1 Box (isi 100 voucher) menjadi 10 Pack Eceran untuk dibagikan ke motor kanvas sales.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-mono font-bold">RO: RPKOUT</span>
            <i data-lucide="arrow-right" class="w-4 h-4 text-slate-400"></i>
            <span class="px-3 py-1.5 rounded-xl bg-indigo-700 text-white text-xs font-mono font-bold">RI: RPKIN</span>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-900 text-sm">Daftar Transaksi Repack</h3>
            <span class="text-xs text-slate-400">Tercatat di Gudang Persediaan TAP</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-6">DocType & Nomor Repack</th>
                        <th class="py-3.5 px-4">Referensi Dokumen Asal</th>
                        <th class="py-3.5 px-4">Lokasi Gudang</th>
                        <th class="py-3.5 px-4">Tanggal Repack</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-6">Deskripsi Aktivitas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($repacks as $rpk)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-4 px-6 font-mono font-bold text-slate-900">
                                @if($rpk->doc_type === 'RPKOUT')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500 text-white mr-1">RPKOUT</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-600 text-white mr-1">RPKIN</span>
                                @endif
                                {{ $rpk->doc_number }}
                            </td>
                            <td class="py-4 px-4 font-mono font-bold text-slate-500">
                                {{ $rpk->ref_doc_number ?? '-' }}
                            </td>
                            <td class="py-4 px-4 font-bold text-slate-800">{{ $rpk->warehouse_name }}</td>
                            <td class="py-4 px-4 text-slate-500">{{ date('d M Y', strtotime($rpk->repack_date)) }}</td>
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    {{ $rpk->status }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-slate-600">{{ $rpk->notes }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Belum ada data repack</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

