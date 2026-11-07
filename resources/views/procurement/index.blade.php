<x-app-layout>
    <x-slot name="title">Pengadaan & Goods Received</x-slot>
    <x-slot name="headerTitle">Pengadaan & Penerimaan Barang</x-slot>
    <x-slot name="badge">DocType: PO & GR</x-slot>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between">
            <span class="flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                {{ session('success') }}
            </span>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800">&times;</button>
        </div>
    @endif

    <!-- Workflow Diagram Header -->
    <div class="mb-8 p-5 bg-white rounded-2xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase font-mono">
                Alur Pengadaan 1: PO &rarr; GR
            </span>
            <h2 class="text-base font-bold text-slate-900 mt-1">Purchase Order ke Supplier & Goods Received ke Gudang Utama</h2>
            <p class="text-xs text-slate-500 mt-0.5">PO belum menambah stok. Stok baru tercatat resmi di Gudang MAIN setelah dokumen GR dikonfirmasi.</p>
        </div>
        <form method="POST" action="{{ route('procurement.quick-po') }}">
            @csrf
            <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-sm transition-all">
                <i data-lucide="plus" class="w-4 h-4 text-emerald-400"></i>
                <span>+ Buat PO Cepat (Demo)</span>
            </button>
        </form>
    </div>

    <!-- Tabbed PO & GR -->
    <div x-data="{ activeTab: 'po' }" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="border-b border-slate-200/80 px-6 pt-4 flex gap-6 text-xs font-bold">
            <button @click="activeTab = 'po'" :class="activeTab === 'po' ? 'text-rose-600 border-b-2 border-rose-600 pb-3 font-extrabold' : 'text-slate-500 hover:text-slate-800 pb-3'">
                1. Daftar Purchase Order (PO) ({{ count($orders) }})
            </button>
            <button @click="activeTab = 'gr'" :class="activeTab === 'gr' ? 'text-rose-600 border-b-2 border-rose-600 pb-3 font-extrabold' : 'text-slate-500 hover:text-slate-800 pb-3'">
                2. Daftar Goods Received (GR) ({{ count($receipts) }})
            </button>
        </div>

        <!-- TAB: Purchase Orders -->
        <div x-show="activeTab === 'po'" class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3.5 px-4">DocType & Nomor PO</th>
                            <th class="py-3.5 px-4">Supplier</th>
                            <th class="py-3.5 px-4">TAP Pemesan</th>
                            <th class="py-3.5 px-4">Tanggal Order</th>
                            <th class="py-3.5 px-4 text-right">Total Nilai</th>
                            <th class="py-3.5 px-4">Status PO</th>
                            <th class="py-3.5 px-4 text-center">Aksi Demo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($orders as $po)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-4 px-4 font-mono font-bold text-slate-900">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] bg-slate-900 text-white mr-1">{{ $po->doc_type }}</span>
                                    {{ $po->doc_number }}
                                </td>
                                <td class="py-4 px-4 font-bold text-slate-800">{{ $po->supplier_name }}</td>
                                <td class="py-4 px-4 font-semibold text-rose-600">{{ $po->tap_name }}</td>
                                <td class="py-4 px-4 text-slate-500">{{ date('d M Y', strtotime($po->order_date)) }}</td>
                                <td class="py-4 px-4 text-right font-mono font-bold text-slate-900">
                                    Rp {{ number_format($po->total_amount, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-4">
                                    @if($po->status === 'RECEIVED')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            RECEIVED (Selesai)
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 animate-pulse">
                                            {{ $po->status }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-center">
                                    @if($po->status !== 'RECEIVED')
                                        <form method="POST" action="{{ route('procurement.confirm-gr', $po->id) }}">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm flex items-center gap-1 mx-auto">
                                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                                <span>Terima Fisik (GR)</span>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-400 font-medium">Sudah Jadi GR</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB: Goods Received -->
        <div x-show="activeTab === 'gr'" class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3.5 px-4">DocType & Nomor GR</th>
                            <th class="py-3.5 px-4">Ref. Doc PO</th>
                            <th class="py-3.5 px-4">Gudang Penerima (MAIN)</th>
                            <th class="py-3.5 px-4">Tanggal Diterima</th>
                            <th class="py-3.5 px-4">Status Fisik</th>
                            <th class="py-3.5 px-4">Catatan Logistik</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($receipts as $gr)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-4 px-4 font-mono font-bold text-slate-900">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] bg-emerald-600 text-white mr-1">{{ $gr->doc_type }}</span>
                                    {{ $gr->doc_number }}
                                </td>
                                <td class="py-4 px-4 font-mono font-bold text-slate-600">
                                    {{ $gr->ref_doc_number }}
                                </td>
                                <td class="py-4 px-4 font-bold text-slate-800">
                                    {{ $gr->warehouse_name }}
                                </td>
                                <td class="py-4 px-4 text-slate-500">{{ date('d M Y', strtotime($gr->received_date)) }}</td>
                                <td class="py-4 px-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        {{ $gr->status }} (Stok Masuk)
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-slate-500">{{ $gr->notes }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

