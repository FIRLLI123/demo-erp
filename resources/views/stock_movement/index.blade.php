<x-app-layout>
    <x-slot name="title">Stock Movement (SM) - Transfer Antar Gudang</x-slot>
    <x-slot name="headerTitle">Stock Movement (SM)</x-slot>
    <x-slot name="badge">DocType: SM</x-slot>

    <div x-data="stockMovementApp()" x-init="initData()">

        <!-- Alert Notification (Inline & Toast Support) -->
        <x-alert />

        <!-- Top Action Bar -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Transfer Fisik Antar Gudang (SM)</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-cyan-100 text-cyan-800 border border-cyan-200">PT WAHANA PUTRA YUDHA</span>
                </h2>
                <p class="text-xs text-slate-500">Mutasi resmi PT Wahana Putra Yudha: MAIN &rarr; INVENTORY, INVENTORY &rarr; SALES, atau retur kanvas</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('reports.index', ['type' => 'SM']) }}" 
                   class="px-3.5 py-2.5 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-xs transition-all shrink-0">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-cyan-600"></i>
                    <span>Rekap Laporan SM (Excel/PDF)</span>
                </a>
                <button type="button" 
                        @click="openMultiModal()" 
                        class="px-3.5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-sm transition-all active:scale-95 shrink-0 cursor-pointer">
                    <i data-lucide="upload-cloud" class="w-4 h-4 text-emerald-100"></i>
                    <span>Upload SM Multi (Excel)</span>
                </button>
                <button type="button" 
                        @click="createSmOpen = !createSmOpen; multiModalOpen = false" 
                        class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-sm transition-all active:scale-95 shrink-0 cursor-pointer">
                    <i data-lucide="plus-circle" class="w-4 h-4 text-cyan-400"></i>
                    <span x-text="createSmOpen ? 'Tutup Formulir' : '+ Buat SM Manual'"></span>
                </button>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- KOMPONEN UI: FILTER CLIENT-SIDE (TANGGAL, WILAYAH & SEARCH)    -->
        <!-- SAMA SEPERTI DI PO/GR (100% CEPAT TANPA RELOAD HALAMAN)        -->
        <!-- ============================================================== -->
        <div class="space-y-3 mb-5">
            <!-- Filter Tanggal Client-Side -->
            <div class="bg-white border border-slate-200/90 rounded-2xl p-3.5 shadow-2xs">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 flex items-center gap-1.5 mr-1">
                            <i data-lucide="calendar" class="w-3.5 h-3.5 text-rose-600"></i>
                            <span>Periode Tanggal Mutasi:</span>
                        </span>
                        <!-- Preset Tanggal Client-Side -->
                        <div class="inline-flex items-center p-0.5 bg-slate-100 rounded-xl gap-0.5">
                            <button type="button"
                                    @click="setClientDatePreset('all')"
                                    :class="!startDate && !endDate ? 'bg-slate-900 text-white font-extrabold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                                    class="px-3 py-1 rounded-lg text-xs transition-all cursor-pointer">
                                Semua
                            </button>
                            <button type="button"
                                    @click="setClientDatePreset('today')"
                                    :class="isPresetActive('today') ? 'bg-rose-600 text-white font-extrabold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                                    class="px-3 py-1 rounded-lg text-xs transition-all cursor-pointer">
                                Hari Ini
                            </button>
                            <button type="button"
                                    @click="setClientDatePreset('7days')"
                                    :class="isPresetActive('7days') ? 'bg-rose-600 text-white font-extrabold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                                    class="px-3 py-1 rounded-lg text-xs transition-all cursor-pointer">
                                7 Hari
                            </button>
                            <button type="button"
                                    @click="setClientDatePreset('this_month')"
                                    :class="isPresetActive('this_month') ? 'bg-rose-600 text-white font-extrabold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                                    class="px-3 py-1 rounded-lg text-xs transition-all cursor-pointer">
                                Bulan Ini
                            </button>
                        </div>
                    </div>

                    <!-- Input Tanggal Custom (Client-Side) -->
                    <div class="flex items-center gap-2 flex-wrap">
                        <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1">
                            <span class="text-[11px] text-slate-400 font-semibold">Dari:</span>
                            <input type="date"
                                   x-model="startDate"
                                   @change="syncSelection(); refreshIcons()"
                                   class="text-xs font-bold text-slate-800 bg-transparent focus:outline-none cursor-pointer">
                        </div>
                        <span class="text-slate-300 text-xs">-</span>
                        <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1">
                            <span class="text-[11px] text-slate-400 font-semibold">Sampai:</span>
                            <input type="date"
                                   x-model="endDate"
                                   @change="syncSelection(); refreshIcons()"
                                   class="text-xs font-bold text-slate-800 bg-transparent focus:outline-none cursor-pointer">
                        </div>
                        <button type="button"
                                x-show="startDate || endDate"
                                @click="setClientDatePreset('all')"
                                title="Reset Filter Tanggal"
                                class="px-2.5 py-1 text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-xl border border-rose-200 flex items-center gap-1 transition-colors cursor-pointer">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            <span>Reset Tanggal</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filter Wilayah & Search (Sinkron Client-Side dengan x-filter-territory) -->
            <x-filter-territory
                tab="sm"
                :show-area="true"
                :show-cluster="true"
                :show-tap="true"
                :show-search="true"
                model-search="searchQuery"
                search-placeholder="Cari No. SM, Gudang Asal, Gudang Tujuan, Catatan, Petugas..."
                :areas="$areas"
                :clusters="$clusters"
                :taps="$taps" />
        </div>

        <!-- ============================================== -->
        <!-- FORM PEMBUATAN SM MULTI-ITEM (INLINE CARD)    -->
        <!-- ============================================== -->
        <div x-show="createSmOpen" x-cloak x-transition class="mb-6 bg-white rounded-2xl border-2 border-cyan-500/30 p-5 shadow-lg">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <i data-lucide="arrow-right-left" class="w-4 h-4 text-cyan-600"></i>
                    <span>Formulir Stock Movement (SM) Baru</span>
                </h3>
                <button @click="createSmOpen = false" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('stock-movement.store') }}" 
                  enctype="multipart/form-data"
                  data-confirm="Konfirmasi Transfer Stok (Stock Movement)"
                  data-confirm-text="Apakah Anda yakin ingin memproses mutasi pemindahan stok ini? Saldo stok gudang asal akan langsung dipotong dan ditambahkan ke gudang tujuan."
                  data-confirm-type="emerald"
                  data-confirm-btn="Ya, Proses Transfer Stok"
                  data-cancel-btn="Tidak, Periksa Lagi"
                  data-loading-title="Memproses Transfer Stok"
                  data-loading-sub="Memindahkan saldo & update serial ICCID antar gudang..."
                  data-doctype="SM">
                @csrf

                <!-- Header Info (Rute & Tanggal) -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Gudang Asal (Source) <span class="text-rose-500">*</span>
                        </label>
                        <select name="source_warehouse_id" 
                                x-model="sourceWhId" 
                                @change="onSourceWarehouseChange()"
                                required 
                                class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 cursor-pointer shadow-2xs">
                            <option value="">-- Pilih Gudang Asal --</option>
                            <template x-for="wh in allWarehouses" :key="wh.id">
                                <option :value="wh.id" x-text="`[${wh.type}] ${wh.name} (${wh.tap_name} - ${wh.cluster_name || ''})`"></option>
                            </template>
                        </select>
                        <template x-if="sourceWhClusterName">
                            <span class="text-[10px] text-cyan-700 font-bold mt-1 block">
                                Wilayah Cluster: <span x-text="sourceWhClusterName"></span> (<span x-text="sourceWhAreaName"></span>)
                            </span>
                        </template>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Gudang Tujuan (Destination) <span class="text-rose-500">*</span>
                        </label>
                        <select name="destination_warehouse_id" 
                                x-model="destWhId" 
                                required 
                                :disabled="!sourceWhId"
                                class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 cursor-pointer shadow-2xs disabled:bg-slate-100 disabled:text-slate-400">
                            <option value="" x-text="sourceWhId ? '-- Pilih Gudang Tujuan (1 Area yang Sama) --' : '-- Pilih Gudang Asal Dulu --'"></option>
                            <template x-for="wh in availableDestinationWarehouses" :key="wh.id">
                                <option :value="wh.id" :disabled="wh.id == sourceWhId" x-text="`[${wh.type}] ${wh.name} (${wh.tap_name} - ${wh.cluster_name})`"></option>
                            </template>
                        </select>
                        <template x-if="sourceWhId">
                            <!-- <span class="text-[10px] text-slate-500 font-medium mt-1 block">
                                Menampilkan <strong class="text-slate-800" x-text="availableDestinationWarehouses.length"></strong> gudang tujuan dalam Area <strong class="text-cyan-800" x-text="sourceWhAreaName"></strong> (termasuk seluruh Cluster)
                            </span> -->
                        </template>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Mutasi <span class="text-rose-500">*</span></label>
                        <input type="date" name="movement_date" value="{{ date('Y-m-d') }}" required class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:border-cyan-500 shadow-2xs">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Keperluan / Catatan</label>
                        <input type="text" name="notes" placeholder="Contoh: Alokasi kanvas harian, dropping stok" value="Transfer Stok Antar Gudang" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:border-cyan-500 shadow-2xs">
                    </div>
                </div>

                <!-- Multi-Item Rows Table (Daftar Barang yang Dipindahkan) -->
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold text-slate-800">Daftar Barang yang Dipindahkan</label>
                        <button type="button" @click="addItemRow()" class="text-xs font-bold text-cyan-700 hover:text-cyan-800 flex items-center gap-1 cursor-pointer">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Baris Produk
                        </button>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(row, index) in items" :key="index">
                            <div class="border border-slate-200 rounded-xl p-3.5 bg-slate-50/70">
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start">
                                    
                                    <!-- 1. SEARCHABLE SPINNER PRODUK -->
                                    <div class="{{ $iccidEnabled ? 'md:col-span-5' : 'md:col-span-6' }}">
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1 flex items-center justify-between">
                                            <span>Produk</span>
                                            <span class="text-[10px] text-slate-400 font-normal">Ketik SKU / Nama</span>
                                        </label>
                                        <input type="hidden" :name="`items[${index}][product_id]`" :value="row.product_id" required>

                                        <div class="relative" @click.outside="row.dropdownOpen = false">
                                            <div class="relative flex items-center">
                                                <input type="text"
                                                       x-model="row.searchText"
                                                       @focus="row.dropdownOpen = true"
                                                       @input="row.dropdownOpen = true"
                                                       placeholder="Ketik cari SKU atau nama produk..."
                                                       class="w-full text-xs font-bold text-slate-800 bg-white border border-slate-200 rounded-lg pl-3 pr-8 py-2 focus:outline-none focus:border-cyan-500 shadow-2xs cursor-pointer">
                                                <button type="button"
                                                        x-show="row.product_id"
                                                        @click="clearProductRow(index)"
                                                        class="absolute right-2 text-slate-400 hover:text-rose-600 cursor-pointer p-0.5"
                                                        title="Ganti produk">
                                                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                                </button>
                                            </div>

                                            <!-- Dropdown Hasil Pencarian Produk -->
                                            <div x-show="row.dropdownOpen"
                                                 x-cloak
                                                 class="absolute z-50 left-0 right-0 mt-1 max-h-56 overflow-y-auto bg-white border border-slate-200 rounded-xl shadow-xl divide-y divide-slate-100">
                                                <template x-for="prod in filterProducts(row.searchText)" :key="prod.id">
                                                    <button type="button"
                                                            @click="selectProduct(index, prod)"
                                                            class="w-full text-left px-3 py-2 hover:bg-cyan-50/70 flex items-center justify-between gap-2 transition-colors cursor-pointer"
                                                            :class="String(row.product_id) === String(prod.id) ? 'bg-cyan-50 font-bold text-cyan-900' : 'text-slate-800'">
                                                        <div>
                                                            <div class="flex items-center gap-1.5">
                                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold"
                                                                      :class="prod.type === 'SA' ? 'bg-rose-100 text-rose-700' : (prod.type === 'VOUCHER' ? 'bg-indigo-100 text-indigo-700' : 'bg-cyan-100 text-cyan-700')"
                                                                      x-text="prod.type"></span>
                                                                <span class="text-xs font-bold" x-text="prod.name"></span>
                                                            </div>
                                                            <div class="text-[10px] text-slate-400 font-mono flex items-center gap-2 mt-0.5">
                                                                <span x-text="prod.sku"></span>
                                                                @if($iccidEnabled)
                                                                    <span>&bull;</span>
                                                                    <span x-text="'Tracking: ' + prod.tracking_method"></span>
                                                                @endif
                                                                <template x-if="sourceWhId">
                                                                    <span class="text-emerald-600 font-semibold" x-text="'• Stok Tersedia: ' + getAvailableStock(prod.id) + ' pcs'"></span>
                                                                </template>
                                                            </div>
                                                        </div>
                                                        <svg x-show="String(row.product_id) === String(prod.id)" class="w-4 h-4 text-cyan-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </button>
                                                </template>
                                                <div x-show="filterProducts(row.searchText).length === 0" class="px-3 py-3 text-center text-slate-400 text-xs italic">
                                                    Produk tidak ditemukan.
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @if($iccidEnabled)
                                    <!-- 2. TRACKING INFO (Hanya Jika Feature Flag Aktif) -->
                                    <div class="md:col-span-2">
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Tracking</label>
                                        <div class="py-2">
                                            <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold font-mono"
                                                  :class="row.tracking_method === 'ICCID' ? 'bg-purple-100 text-purple-700 border border-purple-200' : (row.tracking_method === 'QUANTITY' ? 'bg-blue-100 text-blue-700 border border-blue-200' : 'bg-slate-100 text-slate-400 border border-slate-200')"
                                                  x-text="row.tracking_method || '-'">
                                            </span>
                                        </div>
                                    </div>
                                    @endif

                                    <!-- 3. STOK GUDANG ASAL -->
                                    <div class="{{ $iccidEnabled ? 'md:col-span-2' : 'md:col-span-3' }}">
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Stok Gudang Asal</label>
                                        <div class="py-1.5 text-xs font-mono font-bold"
                                             :class="getAvailableStock(row.product_id) > 0 ? 'text-emerald-700' : 'text-slate-400'">
                                            <span x-text="getAvailableStock(row.product_id).toLocaleString('id-ID')"></span> pcs
                                        </div>
                                    </div>

                                    <!-- 4. QUANTITY INPUT -->
                                    <div class="md:col-span-2">
                                        <template x-if="!iccidEnabled || row.tracking_method === 'QUANTITY'">
                                            <div>
                                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Qty Pindah</label>
                                                <input type="number" 
                                                       :name="`items[${index}][quantity]`" 
                                                       x-model.number="row.quantity" 
                                                       min="1" 
                                                       :max="getAvailableStock(row.product_id)" 
                                                       required 
                                                       class="w-full text-xs font-bold text-center bg-white border border-slate-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-cyan-500 shadow-2xs">
                                            </div>
                                        </template>

                                        <template x-if="iccidEnabled && row.tracking_method === 'ICCID'">
                                            <div>
                                                <label class="block text-[11px] font-bold text-purple-700 mb-1">Total Unit (Otomatis)</label>
                                                <div class="py-1.5 px-2 bg-purple-50 text-purple-700 font-mono font-bold text-xs text-center rounded-lg border border-purple-200">
                                                    <span x-text="getIccidCount(row)"></span> unit
                                                </div>
                                                <input type="hidden" :name="`items[${index}][quantity]`" :value="getIccidCount(row)">
                                            </div>
                                        </template>

                                        <template x-if="iccidEnabled && !row.tracking_method">
                                            <div>
                                                <label class="block text-[11px] font-bold text-slate-400 mb-1">Qty</label>
                                                <div class="py-1.5 px-2 bg-slate-100 text-slate-400 font-mono text-xs text-center rounded-lg">-</div>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- 5. HAPUS BARIS -->
                                    <div class="md:col-span-1 pt-6 text-center">
                                        <button type="button" @click="removeItemRow(index)" class="text-slate-400 hover:text-rose-600 transition-colors p-1 cursor-pointer" x-show="items.length > 1" title="Hapus baris">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </div>

                                @if($iccidEnabled)
                                <!-- SUB-SECTION: ICCID SELECTION JIKA PRODUK BER-ICCID -->
                                <template x-if="row.tracking_method === 'ICCID' && row.product_id && sourceWhId">
                                    <div class="mt-3 pt-3 border-t border-slate-200 bg-white rounded-lg p-3">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center gap-2">
                                                <span class="text-[11px] font-bold text-slate-700">Metode Input Serial ICCID:</span>
                                                <div class="inline-flex rounded-lg border border-slate-200 p-0.5 bg-slate-100 text-[10px] font-bold">
                                                    <button type="button" 
                                                            @click="row.iccid_mode = 'SELECT'" 
                                                            :class="row.iccid_mode === 'SELECT' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500'" 
                                                            class="px-2 py-0.5 rounded-md transition-all cursor-pointer">
                                                        1. Pilih dari Stok (<span x-text="getIccidsForProduct(row.product_id).length"></span>)
                                                    </button>
                                                    <button type="button" 
                                                            @click="row.iccid_mode = 'PASTE'" 
                                                            :class="row.iccid_mode === 'PASTE' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500'" 
                                                            class="px-2 py-0.5 rounded-md transition-all cursor-pointer">
                                                        2. Paste Serial
                                                    </button>
                                                    <button type="button" 
                                                            @click="row.iccid_mode = 'UPLOAD'" 
                                                            :class="row.iccid_mode === 'UPLOAD' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500'" 
                                                            class="px-2 py-0.5 rounded-md transition-all cursor-pointer">
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
                                                    <button type="button" @click="selectAllIccids(index)" class="text-cyan-600 font-bold hover:underline cursor-pointer">Pilih Semua</button>
                                                    <span>&bull;</span>
                                                    <button type="button" @click="row.selected_iccids = []" class="text-slate-400 font-bold hover:underline cursor-pointer">Reset</button>
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
                                                        Tidak ada nomor ICCID berstatus AVAILABLE di gudang asal ini.
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
                                @endif

                            </div>
                        </template>
                    </div>
                </div>

                <!-- Footer Form & Submit Actions -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-3 border-t border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-500 uppercase">Total Pemindahan:</span>
                        <span class="text-sm font-extrabold text-slate-900 font-mono">
                            <span x-text="totalQty.toLocaleString('id-ID')"></span> pcs
                        </span>
                        <span class="text-xs text-slate-400 font-medium">(<span x-text="items.length"></span> jenis barang)</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="createSmOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" 
                                :disabled="isSubmitting"
                                class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 disabled:bg-slate-400 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-sm transition-all active:scale-95 cursor-pointer">
                            <template x-if="isSubmitting">
                                <i data-lucide="loader-2" class="w-4 h-4 animate-spin text-cyan-400"></i>
                            </template>
                            <template x-if="!isSubmitting">
                                <i data-lucide="check" class="w-4 h-4 text-cyan-400"></i>
                            </template>
                            <span x-text="isSubmitting ? 'Memproses Transfer...' : 'Proses Transfer SM'"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- ============================================== -->
        <!-- BAGIAN 1: HEADER TABLE (DAFTAR DOKUMEN SM)     -->
        <!-- ============================================== -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-6">
            <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
                <div class="flex items-center gap-2.5">
                    <h3 class="font-extrabold text-slate-900 text-xs flex items-center gap-2">
                        <i data-lucide="arrow-right-left" class="w-4 h-4 text-cyan-700"></i>
                        <span>Daftar Stock Movement (SM)</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-200 text-slate-700 font-bold font-mono" x-text="filteredMovements.length + ' data'"></span>
                    </h3>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">No. Dokumen SM</th>
                            <th class="py-3 px-4">Tanggal & Jam</th>
                            <th class="py-3 px-4">Dibuat Oleh</th>
                            <th class="py-3 px-4">Rute Gudang (Asal &rarr; Tujuan)</th>
                            <th class="py-3 px-4">Catatan</th>
                            <th class="py-3 px-4 text-center">Total Item</th>
                            <th class="py-3 px-4 text-center">Total Qty</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <template x-for="sm in filteredMovements" :key="sm.id">
                            <tr @click="selectSm(sm)"
                                class="cursor-pointer transition-colors"
                                :class="selectedSm && selectedSm.id === sm.id ? 'bg-cyan-50/80 border-l-4 border-cyan-600' : 'hover:bg-slate-50/70'">
                                
                                <td class="py-3.5 px-4 font-mono font-extrabold text-slate-900">
                                    <div class="flex items-center gap-1.5">
                                        <span class="px-1.5 py-0.5 rounded text-[10px] bg-cyan-700 text-white font-mono font-bold">SM</span>
                                        <span x-text="sm.doc_number"></span>
                                    </div>
                                </td>

                                <td class="py-3.5 px-4 text-slate-600">
                                    <div class="font-bold text-slate-800" x-text="sm.movement_date"></div>
                                    <div class="text-[10px] text-slate-400 font-mono" x-text="sm.created_at ? sm.created_at.substring(11, 16) + ' WIB' : ''"></div>
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-5 h-5 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-[10px] font-bold"
                                             x-text="(sm.creator_name || 'S').substring(0, 1).toUpperCase()">
                                        </div>
                                        <span class="text-xs font-semibold text-slate-800" x-text="sm.creator_name || 'Petugas Gudang'"></span>
                                    </div>
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-slate-800" x-text="sm.source_wh_name"></span>
                                        <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                        <span class="font-bold text-cyan-800" x-text="sm.dest_wh_name"></span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono mt-0.5" x-text="sm.source_wh_type + ' → ' + sm.dest_wh_type"></div>
                                </td>

                                <td class="py-3.5 px-4 text-slate-600 max-w-[170px]">
                                    <span class="text-[11px] font-medium text-slate-700 block truncate" :title="sm.notes" x-text="sm.notes || '-'"></span>
                                </td>

                                <td class="py-3.5 px-4 text-center font-bold text-slate-700">
                                    <span x-text="(sm.items ? sm.items.length : 0) + ' jenis'"></span>
                                </td>

                                <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-900">
                                    <span x-text="Number(sm.total_qty || 0).toLocaleString('id-ID') + ' pcs'"></span>
                                </td>

                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200" x-text="sm.status"></span>
                                </td>

                                <td class="py-3.5 px-4 text-center">
                                    <button type="button" 
                                            class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all flex items-center gap-1 shadow-xs mx-auto cursor-pointer"
                                            :class="selectedSm && selectedSm.id === sm.id 
                                                ? 'bg-cyan-700 text-white' 
                                                : 'bg-slate-100 hover:bg-slate-200 text-slate-600'">
                                        <span x-text="selectedSm && selectedSm.id === sm.id ? 'Aktif' : 'Detail'"></span>
                                        <i data-lucide="chevron-down" class="w-3 h-3 transition-transform" 
                                           :class="selectedSm && selectedSm.id === sm.id ? 'rotate-180 text-white' : 'text-slate-400'"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>

                        <tr x-show="filteredMovements.length === 0" x-cloak>
                            <td colspan="9" class="py-10 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <i data-lucide="inbox" class="w-8 h-8 text-slate-300"></i>
                                    <span>Tidak ada transaksi Stock Movement pada periode dan filter yang dipilih.</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- BAGIAN 2: DETAIL PANEL INLINE (DI BAWAH TABEL) -->
        <!-- ============================================== -->
        <div x-show="selectedSm" x-cloak class="scroll-mt-4 mb-8" id="detailSection">
            <div class="rounded-2xl border-2 border-slate-300 shadow-lg overflow-hidden bg-white">
                
                <!-- Distinct Dark Header Strip for Detail Panel -->
                <div class="bg-slate-900 text-white px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800">
                    <div class="flex items-center gap-3">
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-extrabold bg-cyan-500 text-slate-950 shadow-sm">
                            DETAIL SM
                        </span>
                        <div>
                            <h4 class="text-base font-extrabold text-white tracking-tight" x-text="selectedSm ? selectedSm.doc_number : ''"></h4>
                            <p class="text-[11px] text-slate-400 font-mono mt-0.5">
                                <span x-text="selectedSm ? 'Dibuat: ' + (selectedSm.creator_name || 'Petugas Gudang') : ''"></span>
                                <span>&bull;</span>
                                <span x-text="selectedSm ? selectedSm.movement_date : ''"></span>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 text-xs">
                        <div class="flex items-center gap-1.5 bg-slate-800 px-3 py-1.5 rounded-lg border border-slate-700">
                            <span class="text-slate-400">Rute:</span>
                            <span class="font-bold text-white">
                                <span x-text="selectedSm ? selectedSm.source_wh_name : ''"></span> &rarr; <span x-text="selectedSm ? selectedSm.dest_wh_name : ''"></span>
                            </span>
                        </div>
                        <div class="flex items-center gap-1.5 bg-slate-800 px-3 py-1.5 rounded-lg border border-slate-700">
                            <span class="text-slate-400">Status:</span>
                            <span class="font-bold px-1.5 py-0.5 rounded text-[10px] bg-emerald-500/20 text-emerald-400 border border-emerald-500/30" 
                                  x-text="selectedSm ? selectedSm.status : ''"></span>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Catatan SM Banner -->
                    <template x-if="selectedSm && selectedSm.notes">
                        <div class="mb-5 px-4 py-3 bg-cyan-50/70 border border-cyan-200/80 rounded-xl flex items-start gap-3">
                            <i data-lucide="file-text" class="w-4 h-4 text-cyan-600 shrink-0 mt-0.5"></i>
                            <div>
                                <span class="text-[11px] font-bold text-cyan-900 block">Catatan / Keperluan Stock Movement:</span>
                                <span class="text-xs text-slate-700 font-medium" x-text="selectedSm.notes"></span>
                            </div>
                        </div>
                    </template>

                    <!-- Detail Line Items Table -->
                    <div class="mb-5">
                        <h5 class="text-xs font-bold text-slate-700 uppercase mb-2">Item Barang yang Dipindahkan</h5>
                        <div class="border border-slate-200 rounded-xl overflow-hidden bg-white">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider border-b border-slate-200">
                                    <tr>
                                        <th class="py-2.5 px-4">Produk</th>
                                        <th class="py-2.5 px-4">Tipe</th>
                                        @if($iccidEnabled)
                                            <th class="py-2.5 px-4">Tracking</th>
                                        @endif
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
                                            @if($iccidEnabled)
                                            <td class="py-3 px-4 font-mono text-[11px] font-bold"
                                                :class="item.tracking_method === 'ICCID' ? 'text-purple-700' : 'text-blue-700'"
                                                x-text="item.tracking_method">
                                            </td>
                                            @endif
                                            <td class="py-3 px-4 text-center font-mono font-bold text-slate-800">
                                                <span x-text="Number(item.quantity || 0).toLocaleString('id-ID') + ' pcs'"></span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if($iccidEnabled)
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
                    @endif

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

        <!-- ============================================== -->
        <!-- MODAL SM MULTI (UPLOAD EXCEL & VALIDASI)       -->
        <!-- ============================================== -->
        <template x-if="multiModalOpen">
            <div @click.self="if (!isProcessingMulti) multiModalOpen = false"
                 class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                <div class="bg-white rounded-3xl border border-slate-200 max-w-4xl w-full p-6 shadow-2xl max-h-[92vh] overflow-y-auto">
                    
                    <!-- Header Modal -->
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                        <div>
                            <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                                <i data-lucide="layers" class="w-4 h-4 text-emerald-600"></i>
                                <span>Transfer Massal Antar Gudang (SM Multi)</span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Pindahkan banyak jenis barang sekaligus menggunakan file Excel/CSV</p>
                        </div>
                        <button type="button" 
                                :disabled="isProcessingMulti"
                                @click="multiModalOpen = false" 
                                class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer disabled:opacity-50">&times;</button>
                    </div>

                    <!-- Step 1: Pengaturan Sumber & File Upload -->
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-4 rounded-2xl bg-slate-50 border border-slate-200">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">
                                    Gudang Asal (Sumber Stok) <span class="text-rose-500">*</span>
                                </label>
                                <select x-model="multiSourceWhId" 
                                        :disabled="isProcessingMulti || multiPreviewData !== null"
                                        @change="onMultiSourceChange()"
                                        class="w-full text-xs font-semibold bg-white border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500 cursor-pointer disabled:bg-slate-100">
                                    <option value="">-- Pilih Gudang Asal --</option>
                                    <template x-for="wh in allWarehouses" :key="wh.id">
                                        <option :value="wh.id" x-text="`[${wh.type}] ${wh.name} (${wh.tap_name})`"></option>
                                    </template>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">
                                    Tanggal Mutasi <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" 
                                       x-model="multiMovementDate" 
                                       :disabled="isProcessingMulti || multiPreviewData !== null"
                                       class="w-full text-xs font-semibold bg-white border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500 disabled:bg-slate-100">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Dokumen</label>
                                <input type="text" 
                                       x-model="multiNotes" 
                                       :disabled="isProcessingMulti || multiPreviewData !== null"
                                       placeholder="e.g. Dropping Stok Massal Cluster" 
                                       class="w-full text-xs font-medium bg-white border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500 disabled:bg-slate-100">
                            </div>
                        </div>

                        <!-- Panel File Upload & Download Template -->
                        <div x-show="!multiPreviewData" class="p-6 rounded-2xl border-2 border-dashed border-slate-300 hover:border-emerald-500 text-center transition-all bg-white">
                            <div class="max-w-md mx-auto">
                                <i data-lucide="file-spreadsheet" class="w-10 h-10 text-emerald-600 mx-auto mb-2"></i>
                                <h4 class="font-bold text-slate-800 text-xs mb-1">Pilih File Excel / CSV Hasil Pengisian Template</h4>
                                <p class="text-[11px] text-slate-500 mb-4">Format kolom: <code>id_item | qty | warehouse_tujuan</code></p>
                                
                                <div class="flex items-center justify-center gap-3 flex-wrap">
                                    <a href="{{ route('stock-movement.download-multi-template') }}" 
                                       class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold flex items-center gap-1.5 transition-all">
                                        <i data-lucide="download" class="w-3.5 h-3.5 text-slate-600"></i>
                                        <span>Unduh Template Excel (2 Sheet)</span>
                                    </a>

                                    <label class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-sm transition-all cursor-pointer"
                                           :class="!multiSourceWhId ? 'opacity-50 pointer-events-none' : ''">
                                        <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                                        <span>Pilih File & Preview</span>
                                        <input type="file" 
                                               accept=".xlsx,.xls,.csv" 
                                               @change="handleFileUpload($event)" 
                                               class="hidden">
                                    </label>
                                </div>
                                <span x-show="!multiSourceWhId" class="text-[10px] text-amber-700 font-bold block mt-2">
                                    *Pilih Gudang Asal terlebih dahulu sebelum mengunggah file
                                </span>
                            </div>
                        </div>

                        <!-- Panel Loading / Validasi File -->
                        <div x-show="isValidatingFile" class="p-8 text-center bg-slate-50 rounded-2xl border border-slate-200">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-emerald-500 border-t-transparent mb-2"></div>
                            <h5 class="text-xs font-extrabold text-slate-800">Memeriksa & Memvalidasi Isi File...</h5>
                            <p class="text-[11px] text-slate-500 mt-0.5">Memeriksa ketersediaan stok di gudang asal dan validasi area gudang tujuan</p>
                        </div>

                        <!-- Step 2: TABEL PREVIEW HASIL VALIDASI -->
                        <div x-show="multiPreviewData && !isValidatingFile" class="space-y-3">
                            <!-- Summary Bar -->
                            <div class="p-3.5 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs"
                                 :class="multiPreviewData && multiPreviewData.has_errors ? 'bg-rose-50 border border-rose-200 text-rose-900' : 'bg-emerald-50 border border-emerald-200 text-emerald-900'">
                                <div class="flex items-center gap-2">
                                    <i :data-lucide="multiPreviewData && multiPreviewData.has_errors ? 'alert-octagon' : 'check-circle-2'" 
                                       class="w-5 h-5"
                                       :class="multiPreviewData && multiPreviewData.has_errors ? 'text-rose-600' : 'text-emerald-600'"></i>
                                    <div>
                                        <span class="font-extrabold block" x-text="multiPreviewData && multiPreviewData.has_errors ? 'Terdapat data tidak valid! Perbaiki file sebelum melanjutkan.' : 'Seluruh data valid dan siap dipindahkan!'"></span>
                                        <span class="text-[11px] opacity-80" x-text="`Total: ${multiPreviewData ? multiPreviewData.total_rows : 0} baris (${multiPreviewData ? multiPreviewData.valid_rows_count : 0} Valid, ${multiPreviewData ? (multiPreviewData.total_rows - multiPreviewData.valid_rows_count) : 0} Error) &bull; Volume: ${multiPreviewData ? multiPreviewData.total_pcs.toLocaleString('id-ID') : 0} pcs`"></span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <button type="button" 
                                            @click="resetMultiUpload()" 
                                            class="px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-700 rounded-xl text-xs font-bold border border-slate-200">
                                        Ganti File
                                    </button>
                                </div>
                            </div>

                            <!-- Preview Table -->
                            <div class="border border-slate-200 rounded-2xl overflow-hidden max-h-72 overflow-y-auto">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200 sticky top-0 z-10">
                                        <tr>
                                            <th class="py-2.5 px-3 w-12 text-center">Baris</th>
                                            <th class="py-2.5 px-3">SKU & Nama Produk</th>
                                            <th class="py-2.5 px-3 text-right">Stok Asal</th>
                                            <th class="py-2.5 px-3 text-center">Qty Pindah</th>
                                            <th class="py-2.5 px-3">Gudang Tujuan</th>
                                            <th class="py-2.5 px-3 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 font-medium">
                                        <template x-if="multiPreviewData">
                                            <template x-for="row in multiPreviewData.rows" :key="row.line">
                                                <tr :class="row.is_valid ? 'hover:bg-slate-50' : 'bg-rose-50/60'">
                                                    <td class="py-2.5 px-3 text-center font-mono text-slate-400" x-text="row.line"></td>
                                                    <td class="py-2.5 px-3">
                                                        <span class="font-bold text-slate-900 block" x-text="row.product_name"></span>
                                                        <span class="font-mono text-[10px] text-slate-500" x-text="`ID: ${row.item_id} &bull; ${row.sku}`"></span>
                                                    </td>
                                                    <td class="py-2.5 px-3 text-right font-mono font-semibold text-slate-700" x-text="`${row.available_stock.toLocaleString('id-ID')} pcs`"></td>
                                                    <td class="py-2.5 px-3 text-center font-mono font-extrabold text-cyan-800" x-text="`${row.qty.toLocaleString('id-ID')} pcs`"></td>
                                                    <td class="py-2.5 px-3">
                                                        <span class="font-semibold text-slate-800 text-xs block" x-text="row.dest_wh_name"></span>
                                                    </td>
                                                    <td class="py-2.5 px-3 text-center">
                                                        <template x-if="row.is_valid">
                                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                                VALID
                                                            </span>
                                                        </template>
                                                        <template x-if="!row.is_valid">
                                                            <div>
                                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 block mb-1">
                                                                    ERROR
                                                                </span>
                                                                <span class="text-[10px] text-rose-700 font-semibold block text-left" x-text="row.errors.join('; ')"></span>
                                                            </div>
                                                        </template>
                                                    </td>
                                                </tr>
                                            </template>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Step 3: ANIMASI MODERN PROGRESS BAR PROSES SM MULTI -->
                        <div x-show="isProcessingMulti" class="p-6 rounded-2xl bg-slate-900 text-white shadow-xl space-y-4">
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full bg-emerald-400 animate-ping"></div>
                                    <span class="font-extrabold tracking-wide text-emerald-400 uppercase text-[10px]" x-text="progressStatusText"></span>
                                </div>
                                <span class="font-mono font-extrabold text-white text-sm" x-text="`${progressPercent}%`"></span>
                            </div>

                            <!-- Progress Track -->
                            <div class="w-full h-3 bg-slate-800 rounded-full overflow-hidden p-0.5 border border-slate-700">
                                <div class="h-full bg-gradient-to-r from-teal-400 via-emerald-400 to-cyan-400 rounded-full transition-all duration-300 ease-out shadow-xs"
                                     :style="`width: ${progressPercent}%`"></div>
                            </div>

                            <p class="text-[11px] text-slate-400 text-center font-medium">
                                Harap tunggu, transaksi atomik sedang mengeksekusi perpindahan saldo & pencatatan kartu stok...
                            </p>
                        </div>
                    </div>

                    <!-- Footer Action -->
                    <div class="flex items-center justify-end gap-3 pt-4 mt-4 border-t border-slate-100">
                        <button type="button" 
                                :disabled="isProcessingMulti"
                                @click="multiModalOpen = false" 
                                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs cursor-pointer disabled:opacity-50">
                            Tutup
                        </button>

                        <button type="button" 
                                x-show="multiPreviewData && !multiPreviewData.has_errors && !isProcessingMulti"
                                @click="confirmAndExecuteMulti()"
                                class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold rounded-xl text-xs flex items-center gap-1.5 shadow-sm transition-all cursor-pointer">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            <span>Konfirmasi & Eksekusi Transfer</span>
                        </button>
                    </div>

                </div>
            </div>
        </template>

    </div>

    @push('scripts')
    <script>
        function stockMovementApp() {
            return {
                createSmOpen: false,
                isSubmitting: false,
                iccidEnabled: {{ $iccidEnabled ? 'true' : 'false' }},
                selectedSm: null,
                sourceWhId: '',
                destWhId: '',

                // Master Data
                allWarehouses: @json($warehouses ?? []),
                destinationWarehouses: @json($destinationWarehouses ?? ($warehouses ?? [])),
                allMovements: @json($movements ?? []),
                allAreas: @json($areas ?? []),
                allClusters: @json($clusters ?? []),
                allTaps: @json($taps ?? []),
                allProducts: @json($products ?? []),
                stockBalances: @json($stockBalances ?? []),
                availableIccids: @json($availableIccids ?? []),

                // SM Multi State
                multiModalOpen: false,
                multiSourceWhId: '',
                multiMovementDate: '{{ date('Y-m-d') }}',
                multiNotes: 'Transfer Stok Massal (SM Multi)',
                multiPreviewData: null,
                isValidatingFile: false,
                isProcessingMulti: false,
                progressPercent: 0,
                progressStatusText: 'Menyiapkan Data...',

                // Filter States (Client-Side)
                startDate: '',
                endDate: '',
                selectedArea: '',
                selectedCluster: '',
                selectedTap: '',
                searchQuery: '',

                items: [
                    { 
                        product_id: '', 
                        searchText: '',
                        dropdownOpen: false,
                        tracking_method: 'QUANTITY', 
                        quantity: 10, 
                        iccid_mode: 'SELECT',
                        selected_iccids: [],
                        iccid_text: ''
                    }
                ],

                initData() {
                    this.syncSelection();
                    this.refreshIcons();
                },

                // Preset Tanggal Client-Side (Tanpa Reload Form)
                setClientDatePreset(type) {
                    const now = new Date();
                    const pad = n => String(n).padStart(2, '0');
                    const fmt = d => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

                    if (type === 'all') {
                        this.startDate = '';
                        this.endDate = '';
                    } else if (type === 'today') {
                        const todayStr = fmt(now);
                        this.startDate = todayStr;
                        this.endDate = todayStr;
                    } else if (type === '7days') {
                        const past = new Date();
                        past.setDate(past.getDate() - 6);
                        this.startDate = fmt(past);
                        this.endDate = fmt(now);
                    } else if (type === 'this_month') {
                        const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
                        const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                        this.startDate = fmt(firstDay);
                        this.endDate = fmt(lastDay);
                    }
                    this.syncSelection();
                    this.refreshIcons();
                },

                isPresetActive(type) {
                    const now = new Date();
                    const pad = n => String(n).padStart(2, '0');
                    const fmt = d => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
                    if (type === 'today') {
                        const todayStr = fmt(now);
                        return this.startDate === todayStr && this.endDate === todayStr;
                    }
                    if (type === '7days') {
                        const past = new Date();
                        past.setDate(past.getDate() - 6);
                        return this.startDate === fmt(past) && this.endDate === fmt(now);
                    }
                    if (type === 'this_month') {
                        const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
                        const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                        return this.startDate === fmt(firstDay) && this.endDate === fmt(lastDay);
                    }
                    return false;
                },

                get clusterOptions() {
                    if (!this.selectedArea) return this.allClusters;
                    var self = this;
                    return this.allClusters.filter(function(c) {
                        return String(c.area_id) === String(self.selectedArea);
                    });
                },

                get tapOptions() {
                    var self = this;
                    if (this.selectedCluster) {
                        return this.allTaps.filter(function(t) {
                            return String(t.cluster_id) === String(self.selectedCluster);
                        });
                    }
                    if (this.selectedArea) {
                        return this.allTaps.filter(function(t) {
                            return String(t.area_id) === String(self.selectedArea);
                        });
                    }
                    return this.allTaps;
                },

                onAreaChange() {
                    this.selectedCluster = '';
                    this.selectedTap = '';
                    this.syncSelection();
                    this.refreshIcons();
                },

                onClusterChange() {
                    this.selectedTap = '';
                    this.syncSelection();
                    this.refreshIcons();
                },

                onTapChange() {
                    this.syncSelection();
                    this.refreshIcons();
                },

                onSearchChange() {
                    this.syncSelection();
                    this.refreshIcons();
                },

                onSearchClear() {
                    this.searchQuery = '';
                    this.syncSelection();
                    this.refreshIcons();
                },

                hasActiveFilter() {
                    return Boolean(this.selectedArea || this.selectedCluster || this.selectedTap || this.searchQuery || this.startDate || this.endDate);
                },

                resetFilter() {
                    this.selectedArea = '';
                    this.selectedCluster = '';
                    this.selectedTap = '';
                    this.searchQuery = '';
                    this.startDate = '';
                    this.endDate = '';
                    this.syncSelection();
                    this.refreshIcons();
                },

                isMovementVisible(sm) {
                    if (this.selectedArea) {
                        const srcMatch = sm.source_area_id && String(sm.source_area_id) === String(this.selectedArea);
                        const dstMatch = sm.dest_area_id && String(sm.dest_area_id) === String(this.selectedArea);
                        if (!srcMatch && !dstMatch) return false;
                    }
                    if (this.selectedCluster) {
                        const srcMatch = sm.source_cluster_id && String(sm.source_cluster_id) === String(this.selectedCluster);
                        const dstMatch = sm.dest_cluster_id && String(sm.dest_cluster_id) === String(this.selectedCluster);
                        if (!srcMatch && !dstMatch) return false;
                    }
                    if (this.selectedTap) {
                        const srcMatch = sm.source_tap_id && String(sm.source_tap_id) === String(this.selectedTap);
                        const dstMatch = sm.dest_tap_id && String(sm.dest_tap_id) === String(this.selectedTap);
                        if (!srcMatch && !dstMatch) return false;
                    }

                    if (this.startDate) {
                        const smDate = sm.movement_date ? String(sm.movement_date).substring(0, 10) : '';
                        if (smDate && smDate < this.startDate) return false;
                    }
                    if (this.endDate) {
                        const smDate = sm.movement_date ? String(sm.movement_date).substring(0, 10) : '';
                        if (smDate && smDate > this.endDate) return false;
                    }

                    if (this.searchQuery) {
                        const q = this.searchQuery.toLowerCase().trim();
                        const docMatch = sm.doc_number && sm.doc_number.toLowerCase().includes(q);
                        const srcMatch = sm.source_wh_name && sm.source_wh_name.toLowerCase().includes(q);
                        const dstMatch = sm.dest_wh_name && sm.dest_wh_name.toLowerCase().includes(q);
                        const creatorMatch = sm.creator_name && sm.creator_name.toLowerCase().includes(q);
                        const notesMatch = sm.notes && sm.notes.toLowerCase().includes(q);
                        const itemsMatch = sm.items && sm.items.some(function(it) {
                            return (it.product_name && it.product_name.toLowerCase().includes(q)) || 
                                   (it.sku && it.sku.toLowerCase().includes(q));
                        });
                        if (!docMatch && !srcMatch && !dstMatch && !creatorMatch && !notesMatch && !itemsMatch) return false;
                    }
                    return true;
                },

                get filteredMovements() {
                    var self = this;
                    return this.allMovements.filter(function(sm) {
                        return self.isMovementVisible(sm);
                    });
                },

                syncSelection() {
                    if (this.selectedSm) {
                        var self = this;
                        const stillVisible = this.filteredMovements.some(function(sm) {
                            return sm.id === self.selectedSm.id;
                        });
                        if (!stillVisible) {
                            this.selectedSm = this.filteredMovements.length > 0 ? this.filteredMovements[0] : null;
                        }
                    } else if (this.filteredMovements.length > 0) {
                        this.selectedSm = this.filteredMovements[0];
                    }
                },

                refreshIcons() {
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                filterProducts(q) {
                    if (!q) return this.allProducts.slice(0, 30);
                    const query = q.toLowerCase().trim();
                    return this.allProducts.filter(function(p) {
                        const name = (p.name || '').toLowerCase();
                        const sku = (p.sku || '').toLowerCase();
                        const type = (p.type || '').toLowerCase();
                        return name.includes(query) || sku.includes(query) || type.includes(query);
                    }).slice(0, 30);
                },

                selectProduct(index, prod) {
                    this.items[index].product_id = prod.id;
                    this.items[index].searchText = `[${prod.sku}] ${prod.name}`;
                    this.items[index].tracking_method = this.iccidEnabled ? (prod.tracking_method || 'QUANTITY') : 'QUANTITY';
                    this.items[index].selected_iccids = [];
                    this.items[index].iccid_text = '';
                    this.items[index].dropdownOpen = false;

                    this.refreshIcons();
                },

                clearProductRow(index) {
                    this.items[index].product_id = '';
                    this.items[index].searchText = '';
                    this.items[index].tracking_method = '';
                    this.items[index].selected_iccids = [];
                    this.items[index].iccid_text = '';
                    this.items[index].dropdownOpen = true;
                },

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
                    var self = this;
                    return this.items.reduce(function(acc, row) {
                        if (row.tracking_method === 'ICCID') {
                            return acc + (parseInt(self.getIccidCount(row)) || 0);
                        }
                        return acc + (parseInt(row.quantity) || 0);
                    }, 0);
                },

                selectSm(sm) {
                    this.selectedSm = sm;
                    this.refreshIcons();
                    const el = document.getElementById('detailSection');
                    if (el) {
                        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                },

                addItemRow() {
                    this.items.push({
                        product_id: '',
                        searchText: '',
                        dropdownOpen: false,
                        tracking_method: 'QUANTITY',
                        quantity: 10,
                        iccid_mode: 'SELECT',
                        selected_iccids: [],
                        iccid_text: ''
                    });
                    this.refreshIcons();
                },

                removeItemRow(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    }
                },

                onSourceWarehouseChange() {
                    const self = this;
                    // Reset tujuan jika tidak satu area dengan sumber yang baru dipilih
                    if (this.sourceWhId && this.destWhId) {
                        const srcWh = this.allWarehouses.find(w => w.id == self.sourceWhId);
                        const dstWh = this.destinationWarehouses.find(w => w.id == self.destWhId);
                        if (!srcWh || !dstWh || srcWh.area_id !== dstWh.area_id || self.sourceWhId == self.destWhId) {
                            this.destWhId = '';
                        }
                    } else {
                        this.destWhId = '';
                    }

                    this.items.forEach(function(row) {
                        row.selected_iccids = [];
                        row.iccid_text = '';
                    });
                },

                get currentSourceWarehouse() {
                    if (!this.sourceWhId) return null;
                    return this.allWarehouses.find(w => w.id == this.sourceWhId);
                },

                get sourceWhClusterName() {
                    const src = this.currentSourceWarehouse;
                    return src ? (src.cluster_name || '') : '';
                },

                get sourceWhAreaName() {
                    const src = this.currentSourceWarehouse;
                    return src ? (src.area_name || '') : '';
                },

                get availableDestinationWarehouses() {
                    if (!this.sourceWhId) return [];
                    const src = this.currentSourceWarehouse;
                    if (!src || !src.area_id) return [];
                    // Filter: SELURUH gudang dalam Area yang sama (misal jika pilih Bogor di Area 2, maka Sukabumi yang sama-sama Area 2 juga ikut muncul)
                    return this.destinationWarehouses.filter(w => w.area_id == src.area_id && w.id != src.id);
                },

                onWarehouseChange() {
                    this.onSourceWarehouseChange();
                },

                getAvailableStock(productId) {
                    if (!this.sourceWhId || !productId) return 0;
                    var self = this;
                    const found = this.stockBalances.find(function(b) {
                        return b.warehouse_id == self.sourceWhId && b.product_id == productId;
                    });
                    return found ? parseInt(found.quantity) : 0;
                },

                getIccidsForProduct(productId) {
                    if (!this.sourceWhId || !productId) return [];
                    var self = this;
                    return this.availableIccids.filter(function(u) {
                        return u.warehouse_id == self.sourceWhId && u.product_id == productId;
                    });
                },

                selectAllIccids(index) {
                    const row = this.items[index];
                    const list = this.getIccidsForProduct(row.product_id);
                    row.selected_iccids = list.map(function(u) {
                        return u.iccid_number;
                    });
                },

                countPastedIccids(text) {
                    if (!text) return 0;
                    const items = text.split(/[\r\n,]+/).map(function(s) {
                        return s.trim();
                    }).filter(Boolean);
                    return items.length;
                },

                // SM Multi Methods
                openMultiModal() {
                    this.multiModalOpen = true;
                    this.createSmOpen = false;
                    this.resetMultiUpload();
                    this.refreshIcons();
                },

                onMultiSourceChange() {
                    this.multiPreviewData = null;
                },

                resetMultiUpload() {
                    this.multiPreviewData = null;
                    this.isValidatingFile = false;
                    this.isProcessingMulti = false;
                    this.progressPercent = 0;
                    this.refreshIcons();
                },

                async handleFileUpload(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    if (!this.multiSourceWhId) {
                        if (typeof window.showErrorAlert === 'function') {
                            window.showErrorAlert('Silakan pilih Gudang Asal terlebih dahulu sebelum mengunggah file Excel.', 'Gudang Asal Belum Dipilih');
                        } else {
                            alert('Silakan pilih Gudang Asal terlebih dahulu!');
                        }
                        event.target.value = '';
                        return;
                    }

                    this.isValidatingFile = true;
                    this.multiPreviewData = null;

                    const formData = new FormData();
                    formData.append('source_warehouse_id', this.multiSourceWhId);
                    formData.append('file', file);
                    formData.append('_token', '{{ csrf_token() }}');

                    try {
                        const response = await fetch('{{ route('stock-movement.preview-multi') }}', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                            },
                            body: formData
                        });

                        const res = await response.json();

                        if (!response.ok || !res.success) {
                            if (typeof window.showErrorAlert === 'function') {
                                window.showErrorAlert(res.message || 'Gagal membaca atau memvalidasi file Excel.', 'Validasi File Gagal');
                            } else {
                                alert(res.message || 'Gagal membaca atau memvalidasi file Excel.');
                            }
                            this.resetMultiUpload();
                        } else {
                            this.multiPreviewData = res;
                        }
                    } catch (err) {
                        const errMsg = 'Terjadi kesalahan jaringan atau server saat membaca file: ' + err.message;
                        if (typeof window.showErrorAlert === 'function') {
                            window.showErrorAlert(errMsg, 'Kesalahan Jaringan');
                        } else {
                            alert(errMsg);
                        }
                        this.resetMultiUpload();
                    } finally {
                        this.isValidatingFile = false;
                        event.target.value = '';
                        this.refreshIcons();
                    }
                },

                confirmAndExecuteMulti() {
                    if (!this.multiPreviewData || this.multiPreviewData.has_errors) {
                        if (typeof window.showErrorAlert === 'function') {
                            window.showErrorAlert('Pastikan seluruh data valid sebelum mengeksekusi transfer!', 'Data Belum Valid');
                        } else {
                            alert('Pastikan seluruh data valid sebelum mengeksekusi transfer!');
                        }
                        return;
                    }

                    const totalPcs = this.multiPreviewData.total_pcs.toLocaleString('id-ID');
                    const totalRows = this.multiPreviewData.valid_rows_count;
                    const sourceWh = this.allWarehouses.find(w => w.id == this.multiSourceWhId);
                    const sourceName = sourceWh ? sourceWh.name : 'Gudang Terpilih';

                    const confirmMessage = `Apakah Anda yakin ingin memproses transfer massal sebanyak ${totalRows} baris produk (${totalPcs} pcs) dari "${sourceName}"? Saldo stok akan langsung dipindahkan ke masing-masing gudang tujuan secara atomik.`;

                    // Panggil Global Confirm Modal yang sama dengan PO
                    if (typeof window.dispatchEvent === 'function') {
                        window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                            detail: {
                                title: 'Konfirmasi Transfer Massal (SM Multi)',
                                message: confirmMessage,
                                type: 'emerald',
                                badge: 'KONFIRMASI SM MULTI',
                                confirmText: 'Ya, Eksekusi Transfer',
                                cancelText: 'Batal, Periksa Lagi',
                                onConfirm: () => {
                                    this.executeMultiStockMovement();
                                }
                            }
                        }));
                    } else {
                        if (confirm(confirmMessage)) {
                            this.executeMultiStockMovement();
                        }
                    }
                },

                async executeMultiStockMovement() {
                    this.isProcessingMulti = true;
                    this.progressPercent = 10;
                    this.progressStatusText = 'Memeriksa Kunci Baris Database...';

                    // Tampilkan juga Global Loading Modal (sama seperti di PO)
                    if (typeof window.showLoading === 'function') {
                        window.showLoading(
                            'Memproses Transfer SM Multi...',
                            'Memindahkan saldo stok massal & memvalidasi transaksi atomik...',
                            'SM-MULTI'
                        );
                    }

                    // Simulasi smooth progress animation
                    const progressTimer = setInterval(() => {
                        if (this.progressPercent < 85) {
                            this.progressPercent += Math.floor(Math.random() * 15) + 5;
                            if (this.progressPercent > 40 && this.progressPercent < 70) {
                                this.progressStatusText = 'Memotong Saldo Asal & Menambah Saldo Tujuan...';
                            } else if (this.progressPercent >= 70) {
                                this.progressStatusText = 'Mencatat Kartu Stok & Menomori Dokumen...';
                            }
                        }
                    }, 250);

                    try {
                        const payload = {
                            source_warehouse_id: this.multiSourceWhId,
                            movement_date: this.multiMovementDate,
                            notes: this.multiNotes,
                            rows: this.multiPreviewData.rows.filter(r => r.is_valid).map(r => ({
                                item_id: r.item_id,
                                qty: r.qty,
                                dest_wh_id: r.dest_wh_id
                            })),
                            _token: '{{ csrf_token() }}'
                        };

                        const response = await fetch('{{ route('stock-movement.store-multi') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify(payload)
                        });

                        clearInterval(progressTimer);
                        const result = await response.json();

                        // Sembunyikan global loading
                        if (typeof window.hideLoading === 'function') {
                            window.hideLoading();
                        }

                        if (!response.ok || !result.success) {
                            this.progressPercent = 100;
                            this.progressStatusText = 'Gagal Diproses!';
                            setTimeout(() => {
                                this.isProcessingMulti = false;
                                this.progressPercent = 0;
                                if (typeof window.showErrorAlert === 'function') {
                                    window.showErrorAlert(result.message || 'Terjadi kesalahan saat memproses transfer massal.', 'Transfer Gagal');
                                } else {
                                    alert(result.message || 'Terjadi kesalahan saat memproses transfer massal.');
                                }
                            }, 300);
                        } else {
                            this.progressPercent = 100;
                            this.progressStatusText = 'Transfer Selesai 100%!';
                            setTimeout(() => {
                                this.multiModalOpen = false;
                                if (typeof window.showSuccessAlert === 'function') {
                                    window.showSuccessAlert(result.message, 'Transfer Multi Berhasil!', { reload: true });
                                } else {
                                    alert(result.message);
                                    window.location.reload();
                                }
                            }, 400);
                        }
                    } catch (err) {
                        clearInterval(progressTimer);
                        if (typeof window.hideLoading === 'function') {
                            window.hideLoading();
                        }
                        this.progressPercent = 0;
                        this.isProcessingMulti = false;
                        const errorMsg = 'Koneksi terputus atau server error: ' + err.message + '\n\nSeluruh data dibatalkan secara aman (tidak ada saldo gantung).';
                        if (typeof window.showErrorAlert === 'function') {
                            window.showErrorAlert(errorMsg, 'Kesalahan Jaringan / Server');
                        } else {
                            alert(errorMsg);
                        }
                    }
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
