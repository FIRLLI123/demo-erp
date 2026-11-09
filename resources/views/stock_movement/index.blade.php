<x-app-layout>
    <x-slot name="title">Stock Movement (SM) - Transfer Antar Gudang</x-slot>
    <x-slot name="headerTitle">Stock Movement (SM)</x-slot>
    <x-slot name="badge">DocType: SM</x-slot>

    <div x-data="stockMovementApp()" x-init="initData()">
        
        <!-- Top Action Bar -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Transfer Fisik Antar Gudang (SM)</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-cyan-100 text-cyan-800 border border-cyan-200">PT WAHANA PUTRA YUDHA</span>
                </h2>
                <p class="text-xs text-slate-500">Mutasi resmi PT Wahana Putra Yudha: MAIN &rarr; INVENTORY, INVENTORY &rarr; SALES, atau retur kanvas</p>
            </div>

            <button type="button" 
                    @click="createSmOpen = !createSmOpen" 
                    class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-sm transition-all active:scale-95 shrink-0">
                <i data-lucide="plus-circle" class="w-4 h-4 text-cyan-400"></i>
                <span x-text="createSmOpen ? 'Tutup Form' : '+ Buat SM Baru'"></span>
            </button>
        </div>

        <!-- ============================================== -->
        <!-- FORM PEMBUATAN SM MULTI-ITEM (INLINE CARD)    -->
        <!-- ============================================== -->
        <div x-show="createSmOpen" x-cloak x-transition class="mb-6 bg-white rounded-2xl border-2 border-cyan-500/30 p-5 shadow-lg">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <i data-lucide="arrow-right-left" class="w-4 h-4 text-cyan-600"></i>
                    <span>Formulir Stock Movement (SM)</span>
                </h3>
                <button @click="createSmOpen = false" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('stock-movement.store') }}" 
                  enctype="multipart/form-data"
                  data-loading-title="Memproses Transfer Stok"
                  data-loading-sub="Memindahkan saldo & update serial ICCID antar gudang..."
                  data-doctype="SM">
                @csrf

                <!-- Header Info -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Gudang Asal (Source)</label>
                        <select name="source_warehouse_id" 
                                x-model="sourceWhId" 
                                @change="onWarehouseChange()"
                                required 
                                class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500/20">
                            <option value="">-- Pilih Gudang Asal --</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}">
                                    [{{ $wh->type }}] {{ $wh->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Gudang Tujuan (Destination)</label>
                        <select name="destination_warehouse_id" 
                                x-model="destWhId" 
                                required 
                                class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500/20">
                            <option value="">-- Pilih Gudang Tujuan --</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" :disabled="destWhId == {{ $wh->id }} && sourceWhId == {{ $wh->id }}">
                                    [{{ $wh->type }}] {{ $wh->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Mutasi</label>
                        <input type="date" name="movement_date" value="{{ date('Y-m-d') }}" required class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Keperluan / Catatan</label>
                        <input type="text" name="notes" placeholder="Contoh: Alokasi kanvas harian" value="Transfer Stok Antar Gudang" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none">
                    </div>
                </div>

                <!-- Multi-Item Rows Table -->
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold text-slate-800">Daftar Barang yang Dipindahkan</label>
                        <button type="button" @click="addItemRow()" class="text-xs font-bold text-cyan-700 hover:text-cyan-800 flex items-center gap-1">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Baris
                        </button>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(row, index) in items" :key="index">
                            <div class="border border-slate-200 rounded-xl p-3 bg-slate-50/60">
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start">
                                    
                                    <!-- Produk -->
                                    <div class="md:col-span-5">
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Produk</label>
                                        <select :name="`items[${index}][product_id]`" 
                                                x-model="row.product_id" 
                                                @change="updateProductRow(index)"
                                                required 
                                                class="w-full text-xs font-bold text-slate-800 bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 focus:outline-none">
                                            <option value="">-- Pilih Produk --</option>
                                            @foreach($products as $p)
                                                <option value="{{ $p->id }}" 
                                                        data-sku="{{ $p->sku }}" 
                                                        data-tracking="{{ $p->tracking_method }}" 
                                                        data-type="{{ $p->type }}">
                                                    [{{ $p->type }}] {{ $p->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Tracking Info -->
                                    <div class="md:col-span-2">
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Tracking</label>
                                        <span class="inline-block px-2 py-1 rounded text-[11px] font-bold font-mono"
                                              :class="row.tracking_method === 'ICCID' ? 'bg-purple-100 text-purple-700 border border-purple-200' : 'bg-blue-100 text-blue-700 border border-blue-200'"
                                              x-text="row.tracking_method || '-'">
                                        </span>
                                    </div>

                                    <!-- Stok Gudang Asal -->
                                    <div class="md:col-span-2">
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Stok Asal</label>
                                        <div class="text-xs font-mono font-bold"
                                             :class="getAvailableStock(row.product_id) > 0 ? 'text-emerald-700' : 'text-slate-400'">
                                            <span x-text="getAvailableStock(row.product_id)"></span> pcs
                                        </div>
                                    </div>

                                    <!-- Quantity Input for Non-ICCID / Auto Unit Display for ICCID -->
                                    <div class="md:col-span-2">
                                        <template x-if="row.tracking_method === 'QUANTITY'">
                                            <div>
                                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Qty Pindah</label>
                                                <input type="number" 
                                                       :name="`items[${index}][quantity]`" 
                                                       x-model.number="row.quantity" 
                                                       min="1" 
                                                       :max="getAvailableStock(row.product_id)" 
                                                       required 
                                                       class="w-full text-xs font-bold text-center bg-white border border-slate-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-cyan-500">
                                            </div>
                                        </template>

                                        <template x-if="row.tracking_method === 'ICCID'">
                                            <div>
                                                <label class="block text-[11px] font-bold text-purple-700 mb-1">Total Unit (Otomatis)</label>
                                                <div class="py-1.5 px-2 bg-purple-50 text-purple-700 font-mono font-bold text-xs text-center rounded-lg border border-purple-200">
                                                    <span x-text="getIccidCount(row)"></span> unit
                                                </div>
                                                <input type="hidden" :name="`items[${index}][quantity]`" :value="getIccidCount(row)">
                                            </div>
                                        </template>

                                        <template x-if="!row.tracking_method">
                                            <div>
                                                <label class="block text-[11px] font-bold text-slate-400 mb-1">Qty</label>
                                                <div class="py-1.5 px-2 bg-slate-100 text-slate-400 font-mono text-xs text-center rounded-lg">-</div>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Delete Button -->
                                    <div class="md:col-span-1 pt-6 text-center">
                                        <button type="button" @click="removeItemRow(index)" class="text-slate-400 hover:text-rose-600 transition-colors p-1" x-show="items.length > 1">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- SUB-SECTION: ICCID SELECTION IF PRODUCT IS ICCID -->
                                <template x-if="row.tracking_method === 'ICCID' && row.product_id && sourceWhId">
                                    <div class="mt-3 pt-3 border-t border-slate-200/80 bg-white rounded-lg p-3">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center gap-2">
                                                <span class="text-[11px] font-bold text-slate-700">Metode Input ICCID:</span>
                                                <div class="inline-flex rounded-lg border border-slate-200 p-0.5 bg-slate-100 text-[10px] font-bold">
                                                    <button type="button" 
                                                            @click="row.iccid_mode = 'SELECT'" 
                                                            :class="row.iccid_mode === 'SELECT' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500'" 
                                                            class="px-2 py-0.5 rounded-md transition-all">
                                                        1. Pilih dari Stok (<span x-text="getIccidsForProduct(row.product_id).length"></span>)
                                                    </button>
                                                    <button type="button" 
                                                            @click="row.iccid_mode = 'PASTE'" 
                                                            :class="row.iccid_mode === 'PASTE' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500'" 
                                                            class="px-2 py-0.5 rounded-md transition-all">
                                                        2. Paste Serial
                                                    </button>
                                                    <button type="button" 
                                                            @click="row.iccid_mode = 'UPLOAD'" 
                                                            :class="row.iccid_mode === 'UPLOAD' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500'" 
                                                            class="px-2 py-0.5 rounded-md transition-all">
                                                        3. Upload CSV
                                                    </button>
                                                </div>
                                            </div>

                                            <template x-if="row.iccid_mode === 'UPLOAD'">
                                                <a href="{{ route('stock-movement.download-template') }}" class="text-[10px] font-bold text-cyan-600 hover:underline flex items-center gap-1">
                                                    <i data-lucide="download" class="w-3 h-3"></i> Download Template CSV
                                                </a>
                                            </template>
                                        </div>

                                        <input type="hidden" :name="`items[${index}][iccid_mode]`" :value="row.iccid_mode">

                                        <!-- Mode 1: Checklist ICCID dari Gudang Asal -->
                                        <div x-show="row.iccid_mode === 'SELECT'">
                                            <div class="flex items-center justify-between mb-1.5 text-[11px]">
                                                <span class="text-slate-500">Centang nomor serial yang akan dipindahkan:</span>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" @click="selectAllIccids(index)" class="text-cyan-600 font-bold hover:underline">Pilih Semua</button>
                                                    <span>&bull;</span>
                                                    <button type="button" @click="row.selected_iccids = []" class="text-slate-400 font-bold hover:underline">Reset</button>
                                                </div>
                                            </div>

                                            <div class="max-h-36 overflow-y-auto border border-slate-200 rounded-lg p-2 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-1.5 bg-slate-50/50">
                                                <template x-for="iccid in getIccidsForProduct(row.product_id)" :key="iccid.id">
                                                    <label class="flex items-center gap-1.5 text-[11px] font-mono bg-white p-1 rounded border border-slate-200 cursor-pointer hover:bg-cyan-50/50">
                                                        <input type="checkbox" 
                                                               :name="`items[${index}][selected_iccids][]`" 
                                                               :value="iccid.iccid_number"
                                                               x-model="row.selected_iccids"
                                                               class="rounded text-cyan-600 focus:ring-cyan-500 w-3.5 h-3.5">
                                                        <span x-text="iccid.iccid_number" class="truncate"></span>
                                                    </label>
                                                </template>
                                                <template x-if="getIccidsForProduct(row.product_id).length === 0">
                                                    <div class="col-span-full py-2 text-center text-slate-400 text-xs">
                                                        Tidak ada ICCID berstatus AVAILABLE di gudang asal ini.
                                                    </div>
                                                </template>
                                            </div>
                                            <div class="mt-1 text-[11px] text-slate-500">
                                                Terpilih: <span class="font-bold font-mono text-purple-700" x-text="row.selected_iccids.length"></span> unit.
                                            </div>
                                        </div>

                                        <!-- Mode 2: Paste Serial Teks -->
                                        <div x-show="row.iccid_mode === 'PASTE'">
                                            <textarea :name="`items[${index}][iccid_text]`" 
                                                      x-model="row.iccid_text"
                                                      @input="onPasteIccidChange(index)"
                                                      rows="3" 
                                                      placeholder="Paste serial ICCID disini (1 serial per baris atau pisahkan dengan koma)..."
                                                      class="w-full text-xs font-mono bg-slate-50 border border-slate-200 rounded-lg p-2 focus:outline-none focus:ring-1 focus:ring-cyan-500"></textarea>
                                            <div class="text-[11px] text-slate-500 mt-1">
                                                Terdeteksi: <span class="font-bold font-mono text-cyan-700" x-text="countPastedIccids(row.iccid_text)"></span> unit.
                                            </div>
                                        </div>

                                        <!-- Mode 3: Upload CSV -->
                                        <div x-show="row.iccid_mode === 'UPLOAD'">
                                            <input type="file" 
                                                   :name="`items[${index}][iccid_file]`" 
                                                   accept=".csv,.txt"
                                                   class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-cyan-50 file:text-cyan-700 hover:file:bg-cyan-100">
                                            <p class="text-[10px] text-slate-400 mt-1">File CSV dengan header kolom: <code class="font-mono font-bold text-slate-600">ICCID</code></p>
                                        </div>

                                    </div>
                                </template>

                            </div>
                        </template>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-3 border-t border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-500 uppercase">Total Pemindahan:</span>
                        <span class="text-sm font-extrabold text-slate-900 font-mono">
                            <span x-text="totalQty.toLocaleString('id-ID')"></span> pcs
                        </span>
                        <span class="text-xs text-slate-400 font-medium">(<span x-text="items.length"></span> produk)</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="createSmOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-sm transition-all active:scale-95">
                            <i data-lucide="check" class="w-4 h-4 text-cyan-400"></i>
                            <span>Proses Transfer SM</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- ============================================== -->
        <!-- BAGIAN 1: HEADER TABLE (DAFTAR DOKUMEN SM)     -->
        <!-- ============================================== -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-6">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                    <span>Daftar Stock Movement (SM)</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-slate-100 text-slate-700">
                        {{ count($movements) }} Dokumen
                    </span>
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">No. Dokumen SM</th>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Rute Gudang (Asal &rarr; Tujuan)</th>
                            <th class="py-3 px-4 text-center">Total Item</th>
                            <th class="py-3 px-4 text-center">Total Qty</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($movements as $sm)
                            <tr @click="selectSm({{ json_encode($sm) }})"
                                class="cursor-pointer transition-colors"
                                :class="selectedSm && selectedSm.id === {{ $sm->id }} ? 'bg-cyan-50/80 border-l-4 border-cyan-600' : 'hover:bg-slate-50/70'">
                                
                                <td class="py-3.5 px-4 font-mono font-extrabold text-slate-900">
                                    <div class="flex items-center gap-1.5">
                                        <span class="px-1.5 py-0.5 rounded text-[10px] bg-cyan-700 text-white font-mono font-bold">SM</span>
                                        <span>{{ $sm->doc_number }}</span>
                                    </div>
                                    @if($sm->notes)
                                        <span class="text-[11px] font-normal text-slate-400 block truncate max-w-xs">{{ $sm->notes }}</span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-slate-600">
                                    {{ date('d M Y', strtotime($sm->movement_date)) }}
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-slate-800">{{ $sm->source_wh_name }}</span>
                                        <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                        <span class="font-bold text-cyan-800">{{ $sm->dest_wh_name }}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                                        {{ $sm->source_wh_type }} &rarr; {{ $sm->dest_wh_type }}
                                    </div>
                                </td>

                                <td class="py-3.5 px-4 text-center font-bold text-slate-700">
                                    {{ count($sm->items) }}
                                </td>

                                <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-900">
                                    {{ number_format($sm->total_qty) }} pcs
                                </td>

                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        {{ $sm->status }}
                                    </span>
                                </td>

                                <td class="py-3.5 px-4 text-center">
                                    <button type="button" 
                                            class="px-2 py-1 rounded-lg text-xs font-bold transition-all flex items-center gap-1 shadow-xs mx-auto"
                                            :class="selectedSm && selectedSm.id === {{ $sm->id }} 
                                                ? 'bg-cyan-700 text-white' 
                                                : 'bg-slate-100 hover:bg-slate-200 text-slate-600'">
                                        <span x-text="selectedSm && selectedSm.id === {{ $sm->id }} ? 'Aktif' : 'Detail'"></span>
                                        <i data-lucide="chevron-down" class="w-3 h-3 transition-transform" 
                                           :class="selectedSm && selectedSm.id === {{ $sm->id }} ? 'rotate-180 text-white' : 'text-slate-400'"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400">
                                    Belum ada transaksi Stock Movement. Klik "+ Buat SM Baru" untuk memulai transfer stok.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- BAGIAN 2: DETAIL PANEL INLINE (DI BAWAH TABEL) -->
        <!-- ============================================== -->
        <div x-show="selectedSm" x-cloak class="scroll-mt-4" id="detailSection">
            <div class="rounded-2xl border-2 border-slate-300 shadow-lg overflow-hidden bg-white">
                
                <!-- Distinct Dark Header Strip for Detail Panel -->
                <div class="bg-slate-900 text-white px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800">
                    <div class="flex items-center gap-3">
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-extrabold bg-cyan-500 text-slate-950 shadow-sm">
                            DETAIL SM
                        </span>
                        <h4 class="text-base font-extrabold text-white tracking-tight" x-text="selectedSm ? selectedSm.doc_number : ''"></h4>
                    </div>

                    <div class="flex items-center gap-3 text-xs">
                        <div class="flex items-center gap-1.5 bg-slate-800 px-3 py-1 rounded-lg border border-slate-700">
                            <span class="text-slate-400">Rute:</span>
                            <span class="font-bold text-white">
                                <span x-text="selectedSm ? selectedSm.source_wh_name : ''"></span> &rarr; <span x-text="selectedSm ? selectedSm.dest_wh_name : ''"></span>
                            </span>
                        </div>
                        <div class="flex items-center gap-1.5 bg-slate-800 px-3 py-1 rounded-lg border border-slate-700">
                            <span class="text-slate-400">Status:</span>
                            <span class="font-bold px-1.5 py-0.5 rounded text-[10px] bg-emerald-500/20 text-emerald-400 border border-emerald-500/30" 
                                  x-text="selectedSm ? selectedSm.status : ''"></span>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Detail Line Items Table -->
                    <div class="mb-5">
                        <h5 class="text-xs font-bold text-slate-700 uppercase mb-2">Item Barang yang Dipindahkan</h5>
                        <div class="border border-slate-200 rounded-xl overflow-hidden bg-white">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider border-b border-slate-200">
                                    <tr>
                                        <th class="py-2.5 px-4">Produk</th>
                                        <th class="py-2.5 px-4">Tipe</th>
                                        <th class="py-2.5 px-4">Tracking</th>
                                        <th class="py-2.5 px-4 text-center">Kuantitas Dipindahkan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium">
                                    <template x-for="(item, idx) in (selectedSm ? selectedSm.items : [])" :key="idx">
                                        <tr class="hover:bg-slate-50/70">
                                            <td class="py-3 px-4 font-bold text-slate-900">
                                                <span class="font-mono text-[10px] text-slate-400 block" x-text="item.sku"></span>
                                                <span x-text="item.product_name"></span>
                                            </td>
                                            <td class="py-3 px-4">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold"
                                                      :class="item.product_type === 'SA' ? 'bg-rose-100 text-rose-700' : (item.product_type === 'VOUCHER' ? 'bg-indigo-100 text-indigo-700' : 'bg-cyan-100 text-cyan-700')"
                                                      x-text="item.product_type">
                                                </span>
                                            </td>
                                            <td class="py-3 px-4 font-mono text-[11px] font-bold"
                                                :class="item.tracking_method === 'ICCID' ? 'text-purple-700' : 'text-blue-700'"
                                                x-text="item.tracking_method">
                                            </td>
                                            <td class="py-3 px-4 text-center font-mono font-bold text-slate-800">
                                                <span x-text="item.quantity.toLocaleString('id-ID')"></span> pcs
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Accordion ICCID Units jika ada yang dipindahkan -->
                    <template x-if="selectedSm && selectedSm.iccids && selectedSm.iccids.length > 0">
                        <div class="mt-4 p-4 rounded-xl bg-purple-50/70 border border-purple-200/80">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold text-purple-900 flex items-center gap-1.5">
                                    <i data-lucide="tag" class="w-4 h-4 text-purple-600"></i>
                                    Daftar Serial ICCID yang Dipindahkan (<span x-text="selectedSm.iccids.length"></span> unit)
                                </span>
                            </div>
                            <div class="max-h-40 overflow-y-auto grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-1.5 bg-white p-2.5 rounded-lg border border-purple-100">
                                <template x-for="(iccid, iIdx) in selectedSm.iccids" :key="iIdx">
                                    <span class="px-2 py-1 rounded bg-purple-50 text-purple-800 font-mono text-[11px] border border-purple-200 truncate">
                                        <span x-text="iccid"></span>
                                    </span>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Status Banner -->
                    <div class="mt-4 p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs text-slate-700">
                        <div class="flex items-center gap-2 font-medium">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                            <span>Mutasi stok telah selesai dicatat di Buku Kartu Stok (OUT pada gudang asal, IN pada gudang tujuan).</span>
                        </div>
                        <span class="font-mono text-[11px] font-bold text-cyan-800" x-text="selectedSm ? selectedSm.doc_number : ''"></span>
                    </div>

                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function stockMovementApp() {
            return {
                createSmOpen: false,
                selectedSm: null,
                sourceWhId: '',
                destWhId: '',
                stockBalances: @json($stockBalances),
                availableIccids: @json($availableIccids),
                items: [
                    { 
                        product_id: '', 
                        tracking_method: '', 
                        quantity: 10, 
                        iccid_mode: 'SELECT',
                        selected_iccids: [],
                        iccid_text: ''
                    }
                ],

                getIccidCount(row) {
                    if (row.iccid_mode === 'SELECT') {
                        return row.selected_iccids ? row.selected_iccids.length : 0;
                    } else if (row.iccid_mode === 'PASTE') {
                        return this.countPastedIccids(row.iccid_text);
                    } else if (row.iccid_mode === 'UPLOAD') {
                        return 1;
                    }
                    return 0;
                },

                get totalQty() {
                    return this.items.reduce((acc, row) => {
                        if (row.tracking_method === 'ICCID') {
                            return acc + (parseInt(this.getIccidCount(row)) || 0);
                        }
                        return acc + (parseInt(row.quantity) || 0);
                    }, 0);
                },

                initData() {
                    const first = @json($movements->first());
                    if (first) {
                        this.selectedSm = first;
                    }
                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                },

                selectSm(sm) {
                    this.selectedSm = sm;
                    this.$nextTick(() => {
                        lucide.createIcons();
                        const el = document.getElementById('detailSection');
                        if (el) {
                            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    });
                },

                addItemRow() {
                    this.items.push({
                        product_id: '',
                        tracking_method: '',
                        quantity: 10,
                        iccid_mode: 'SELECT',
                        selected_iccids: [],
                        iccid_text: ''
                    });
                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                },

                removeItemRow(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    }
                },

                onWarehouseChange() {
                    this.items.forEach((row, idx) => {
                        row.selected_iccids = [];
                        row.iccid_text = '';
                    });
                },

                updateProductRow(index) {
                    const select = document.querySelectorAll('select[name^="items["]')[index];
                    if (!select) return;

                    const option = select.options[select.selectedIndex];
                    if (option && option.value) {
                        this.items[index].tracking_method = option.getAttribute('data-tracking') || '';
                    } else {
                        this.items[index].tracking_method = '';
                    }
                    this.items[index].selected_iccids = [];
                    this.items[index].iccid_text = '';
                },

                getAvailableStock(productId) {
                    if (!this.sourceWhId || !productId) return 0;
                    const found = this.stockBalances.find(b => b.warehouse_id == this.sourceWhId && b.product_id == productId);
                    return found ? parseInt(found.quantity) : 0;
                },

                getIccidsForProduct(productId) {
                    if (!this.sourceWhId || !productId) return [];
                    return this.availableIccids.filter(u => u.warehouse_id == this.sourceWhId && u.product_id == productId);
                },

                selectAllIccids(index) {
                    const row = this.items[index];
                    const list = this.getIccidsForProduct(row.product_id);
                    row.selected_iccids = list.map(u => u.iccid_number);
                },

                countPastedIccids(text) {
                    if (!text) return 0;
                    const items = text.split(/[\r\n,]+/).map(s => s.trim()).filter(Boolean);
                    return items.length;
                }
            }
        }
    </script>
    @endpush
</x-app-layout>

