<x-app-layout>
    <x-slot name="title">Inventori & Kartu Stok</x-slot>
    <x-slot name="headerTitle">Manajemen Stok & Kartu Mutasi</x-slot>
    <x-slot name="badge">DocType: SM & Kartu Stok</x-slot>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between">
            <span class="flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                {{ session('success') }}
            </span>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800">&times;</button>
        </div>
    @endif

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-extrabold text-slate-900 tracking-tight">Inventori & Histori Mutasi Stok</h2>
            <p class="text-xs text-slate-500">Monitoring saldo fisik di seluruh gudang dan jejak mutasi barang masuk/keluar</p>
        </div>
        <form method="POST" action="{{ route('inventory.quick-sm') }}"
              data-loading-title="Memproses Mutasi Stok (SM)"
              data-loading-sub="Mengurangi saldo Gudang MAIN dan menambah saldo Gudang Kanvas Sales..."
              data-doctype="SM">
            @csrf
            <button type="submit" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-sm transition-all active:scale-95">
                <i data-lucide="arrow-right-left" class="w-4 h-4 text-cyan-400"></i>
                <span>+ Mutasi Cepat (MAIN &rarr; SALES)</span>
            </button>
        </form>
    </div>

    <!-- Tabbed Inventory -->
    <div x-data="{ activeTab: 'balances' }" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="border-b border-slate-200/80 px-6 pt-4 flex flex-wrap gap-6 text-xs font-bold">
            <button @click="activeTab = 'balances'" :class="activeTab === 'balances' ? 'text-rose-600 border-b-2 border-rose-600 pb-3 font-extrabold' : 'text-slate-500 hover:text-slate-800 pb-3'">
                1. Saldo Stok per Gudang ({{ count($stockBalances) }})
            </button>
            <button @click="activeTab = 'mutations'" :class="activeTab === 'mutations' ? 'text-rose-600 border-b-2 border-rose-600 pb-3 font-extrabold' : 'text-slate-500 hover:text-slate-800 pb-3'">
                2. Buku Kartu Stok (In - Out) ({{ count($mutations) }})
            </button>
            <button @click="activeTab = 'iccids'" :class="activeTab === 'iccids' ? 'text-rose-600 border-b-2 border-rose-600 pb-3 font-extrabold' : 'text-slate-500 hover:text-slate-800 pb-3'">
                3. Tracking Serial ICCID ({{ count($iccids) }})
            </button>
            <button @click="activeTab = 'movements'" :class="activeTab === 'movements' ? 'text-rose-600 border-b-2 border-rose-600 pb-3 font-extrabold' : 'text-slate-500 hover:text-slate-800 pb-3'">
                4. Mutasi Antar Gudang (SM) ({{ count($movements) }})
            </button>
        </div>

        <!-- TAB 1: Stock Balances -->
        <div x-show="activeTab === 'balances'" class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Gudang & Tipe</th>
                            <th class="py-3 px-4">SKU</th>
                            <th class="py-3 px-4">Nama Produk</th>
                            <th class="py-3 px-4">Tracking</th>
                            <th class="py-3 px-4 text-right">Stok Fisik Tersedia</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($stockBalances as $sb)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-slate-900 block">{{ $sb->warehouse_name }}</span>
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-mono font-bold
                                        {{ $sb->warehouse_type === 'MAIN' ? 'bg-slate-900 text-white' : ($sb->warehouse_type === 'INVENTORY' ? 'bg-cyan-700 text-white' : 'bg-rose-600 text-white') }}">
                                        {{ $sb->warehouse_type }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-mono font-semibold text-slate-500">{{ $sb->sku }}</td>
                                <td class="py-3.5 px-4 font-bold text-slate-800">{{ $sb->product_name }}</td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-blue-50 text-blue-700 border border-blue-200">
                                        {{ $sb->tracking_method }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-extrabold text-sm text-slate-900">
                                    {{ number_format($sb->quantity) }} <span class="text-xs font-normal text-slate-400">pcs</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 2: Buku Kartu Stok (In - Out Mutations) -->
        <div x-show="activeTab === 'mutations'" class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Gudang</th>
                            <th class="py-3 px-4">Produk</th>
                            <th class="py-3 px-4">Dokumen</th>
                            <th class="py-3 px-4 text-center">Tipe Mutasi</th>
                            <th class="py-3 px-4 text-right">Qty</th>
                            <th class="py-3 px-4 text-right">Saldo Akhir</th>
                            <th class="py-3 px-4">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($mutations as $m)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3 px-4 text-slate-500">{{ date('d M Y', strtotime($m->date)) }}</td>
                                <td class="py-3 px-4 font-bold text-slate-800">{{ $m->warehouse_name }}</td>
                                <td class="py-3 px-4">
                                    <span class="font-bold text-slate-900 block">{{ $m->product_name }}</span>
                                    <span class="font-mono text-[10px] text-slate-400">{{ $m->sku }}</span>
                                </td>
                                <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] text-white mr-1
                                        {{ $m->doc_type === 'GR' ? 'bg-emerald-600' : ($m->doc_type === 'GD' ? 'bg-amber-600' : 'bg-cyan-700') }}">
                                        {{ $m->doc_type }}
                                    </span>
                                    {{ $m->doc_number }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($m->mutation_type === 'IN')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 flex items-center justify-center gap-1 w-20 mx-auto">
                                            <i data-lucide="arrow-down-left" class="w-3 h-3"></i> MASUK
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 flex items-center justify-center gap-1 w-20 mx-auto">
                                            <i data-lucide="arrow-up-right" class="w-3 h-3"></i> KELUAR
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-extrabold text-sm
                                    {{ $m->mutation_type === 'IN' ? 'text-emerald-700' : 'text-rose-600' }}">
                                    {{ $m->mutation_type === 'IN' ? '+' : '-' }}{{ number_format($m->quantity) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-extrabold text-slate-900">
                                    {{ number_format($m->balance_after) }}
                                </td>
                                <td class="py-3 px-4 text-slate-500">{{ $m->notes }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-400">Belum ada riwayat mutasi stok</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 3: ICCID Tracking -->
        <div x-show="activeTab === 'iccids'" class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Nomor Seri ICCID</th>
                            <th class="py-3 px-4">Produk Perdana</th>
                            <th class="py-3 px-4">Gudang</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Doc Terakhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($iccids as $iccid)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3 px-4 font-mono font-extrabold text-purple-900">{{ $iccid->iccid_number }}</td>
                                <td class="py-3 px-4 font-bold text-slate-800">{{ $iccid->product_name }}</td>
                                <td class="py-3 px-4 text-slate-700 font-semibold">{{ $iccid->warehouse_name ?? '-' }}</td>
                                <td class="py-3 px-4">
                                    @if($iccid->status === 'AVAILABLE')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            AVAILABLE
                                        </span>
                                    @elseif($iccid->status === 'RESERVED')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                            RESERVED
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                            {{ $iccid->status }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-mono text-[11px] text-slate-500">
                                    <span class="px-1.5 py-0.5 bg-slate-200 text-slate-800 rounded mr-1">{{ $iccid->last_doc_type }}</span>
                                    {{ $iccid->last_doc_number }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 4: Stock Movement (SM) -->
        <div x-show="activeTab === 'movements'" class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">DocType & Nomor SM</th>
                            <th class="py-3 px-4">Gudang Asal</th>
                            <th class="py-3 px-4">Gudang Tujuan</th>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($movements as $sm)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] bg-cyan-700 text-white mr-1">{{ $sm->doc_type }}</span>
                                    {{ $sm->doc_number }}
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-slate-800">{{ $sm->source_wh }}</td>
                                <td class="py-3.5 px-4 font-bold text-rose-600">{{ $sm->dest_wh }}</td>
                                <td class="py-3.5 px-4 text-slate-500">{{ date('d M Y', strtotime($sm->movement_date)) }}</td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        {{ $sm->status }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-500">{{ $sm->notes }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
