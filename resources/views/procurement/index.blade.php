<x-app-layout>
    <x-slot name="title">Pengadaan & Goods Received (PO - GR)</x-slot>
    <x-slot name="headerTitle">Purchase Order & Goods Received</x-slot>
    <x-slot name="badge">PO &rarr; GR</x-slot>

    <div x-data="procurementMasterDetail()" x-init="initData()" class="pb-12">

        <!-- Top Action Bar -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Purchase Order & Penerimaan Barang</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">PT WAHANA PUTRA YUDHA</span>
                </h2>
                <p class="text-xs text-slate-500">Pemesanan ke Vendor/Principal & Penerimaan Fisik (GR) ke Gudang TAP</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('reports.index', ['type' => 'PO_GR']) }}"
                   class="px-3.5 py-2.5 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-xs transition-all shrink-0">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600"></i>
                    <span>Rekap Laporan PO (Excel/PDF)</span>
                </a>
                <button type="button"
                        @click="createPoOpen = !createPoOpen"
                        class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-sm transition-all active:scale-95 shrink-0 cursor-pointer">
                    <i data-lucide="plus-circle" class="w-4 h-4 text-emerald-400"></i>
                    <span x-text="createPoOpen ? 'Tutup Form' : '+ Buat PO Baru'"></span>
                </button>
            </div>
        </div>

        <x-alert />

        <!-- ============================================== -->
        <!-- FORM PEMBUATAN PO MULTI-ITEM (INLINE CARD)     -->
        <!-- ============================================== -->
        <div x-show="createPoOpen" x-cloak x-transition class="mb-6 bg-white rounded-2xl border-2 border-emerald-500/30 p-5 shadow-lg">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <i data-lucide="file-plus" class="w-4 h-4 text-emerald-600"></i>
                    <span>Buat Purchase Order (PO)</span>
                </h3>
                <button type="button" @click="createPoOpen = false" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('procurement.store-po') }}"
                  data-ajax data-confirm="Konfirmasi Penerbitan Purchase Order (PO)"
                  data-confirm-text="Apakah Anda yakin ingin menyimpan dan menerbitkan dokumen Purchase Order (PO) ini?"
                  data-confirm-type="emerald"
                  data-confirm-btn="Ya, Simpan & Terbitkan PO"
                  data-cancel-btn="Tidak, Periksa Lagi"
                  data-loading-title="Menerbitkan PO"
                  data-loading-sub="Menyimpan dokumen Purchase Order baru..."
                  data-doctype="PO">
                @csrf

                <!-- Header Info -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">TAP Pemesan <span class="text-rose-500">*</span></label>
                        <select name="tap_id" x-model="formTapId" required class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                            @foreach($taps as $tap)
                                <option value="{{ $tap->id }}">{{ $tap->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-emerald-700 font-semibold mt-1 flex items-center gap-1" x-show="selectedTapMainWh">
                            <i data-lucide="warehouse" class="w-3 h-3"></i>
                            <span>Tujuan Default GR: <strong x-text="selectedTapMainWh"></strong></span>
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Vendor / Supplier <span class="text-rose-500">*</span></label>
                        <select name="vendor_id" required class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                            @foreach($vendors as $vnd)
                                <option value="{{ $vnd->id }}">[{{ $vnd->code }}] {{ $vnd->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal PO <span class="text-rose-500">*</span></label>
                        <input type="date" name="order_date" value="{{ date('Y-m-d') }}" required class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan / Keterangan</label>
                        <input type="text" name="notes" placeholder="Contoh: PO Pengadaan Awal Bulan" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none">
                    </div>
                </div>

                <!-- Multi-Item Rows Table dengan Searchable Product Picker -->
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold text-slate-800">Daftar Barang</label>
                        <button type="button" @click="addItemRow()" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1 cursor-pointer">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Baris
                        </button>
                    </div>

                    <div class="border border-slate-200 rounded-xl overflow-visible bg-slate-50/50">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-100/70 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200/80">
                                <tr>
                                    <th class="py-2.5 px-4 {{ $iccidEnabled ? 'w-6/12' : 'w-8/12' }}">Produk (Ketik untuk Cari SKU / Nama)</th>
                                    @if($iccidEnabled)
                                        <th class="py-2.5 px-4 w-3/12">Tracking Method</th>
                                    @endif
                                    <th class="py-2.5 px-4 w-3/12 text-center">Kuantitas (Qty)</th>
                                    <th class="py-2.5 px-4 w-1/12 text-center"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200/70 bg-white">
                                <template x-for="(row, index) in items" :key="index">
                                    <tr>
                                        <td class="py-2.5 px-4">
                                            <!-- Hidden input submitted to server -->
                                            <input type="hidden" :name="`items[${index}][product_id]`" :value="row.product_id" required>

                                            <!-- Searchable Combobox -->
                                            <div class="relative" @click.outside="row.dropdownOpen = false">
                                                <div class="flex items-center">
                                                    <input type="text"
                                                           x-model="row.searchText"
                                                           @focus="row.dropdownOpen = true"
                                                           @input="row.dropdownOpen = true"
                                                           placeholder="Ketik cari SKU atau nama produk..."
                                                           class="w-full text-xs font-bold text-slate-800 bg-slate-50 border border-slate-200 rounded-lg pl-3 pr-8 py-1.5 focus:bg-white focus:outline-none focus:border-emerald-500">
                                                    <button type="button"
                                                            x-show="row.product_id"
                                                            @click="clearProductRow(index)"
                                                            class="absolute right-2 text-slate-400 hover:text-rose-600 cursor-pointer"
                                                            title="Ganti produk">
                                                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                                    </button>
                                                </div>

                                                <!-- Dropdown List -->
                                                <div x-show="row.dropdownOpen"
                                                     x-cloak
                                                     class="absolute z-40 left-0 right-0 mt-1 max-h-56 overflow-y-auto bg-white border border-slate-200 rounded-xl shadow-xl divide-y divide-slate-100">
                                                    <template x-for="prod in filterProductsForRow(row.searchText)" :key="prod.id">
                                                        <button type="button"
                                                                @click="selectProductForRow(index, prod)"
                                                                class="w-full text-left px-3 py-2 hover:bg-emerald-50 flex items-center justify-between gap-2 transition-colors cursor-pointer">
                                                            <div>
                                                                <span class="font-bold text-slate-900 block text-xs" x-text="prod.name"></span>
                                                                <span class="font-mono text-[10px] text-slate-500" x-text="prod.sku + ' • Tipe: ' + prod.type"></span>
                                                            </div>
                                                            <span class="font-mono text-[11px] font-bold text-emerald-700 shrink-0" x-text="'Rp ' + Number(prod.cost_price).toLocaleString('id-ID')"></span>
                                                        </button>
                                                    </template>
                                                    <div x-show="filterProductsForRow(row.searchText).length === 0" class="px-3 py-3 text-center text-slate-400 text-xs">
                                                        Produk tidak ditemukan.
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        @if($iccidEnabled)
                                            <td class="py-2.5 px-4">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono"
                                                      :class="row.tracking_method === 'ICCID' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'"
                                                      x-text="row.tracking_method || '-'">
                                                </span>
                                            </td>
                                        @endif

                                        <td class="py-2.5 px-4 text-center">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <input type="number"
                                                       :name="`items[${index}][quantity]`"
                                                       x-model.number="row.quantity"
                                                       @input="calculateTotal()"
                                                       min="1"
                                                       required
                                                       class="w-24 text-xs font-bold text-center bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                                <span class="text-[11px] text-slate-400 font-medium">pcs</span>
                                            </div>
                                        </td>

                                        <td class="py-2.5 px-4 text-center">
                                            <button type="button" @click="removeItemRow(index)" class="text-slate-400 hover:text-rose-600 transition-colors cursor-pointer" x-show="items.length > 1">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-3 border-t border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-500 uppercase">Total Pesanan:</span>
                        <span class="text-sm font-extrabold text-slate-900 font-mono">
                            <span x-text="totalQty.toLocaleString('id-ID')"></span> pcs
                        </span>
                        <span class="text-xs text-slate-400 font-medium">(<span x-text="items.length"></span> baris produk)</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="createPoOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                :disabled="!canSubmitPo"
                                :class="!canSubmitPo ? 'opacity-50 cursor-not-allowed bg-slate-500' : 'bg-slate-900 hover:bg-slate-800 active:scale-95'"
                                class="px-5 py-2 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-sm transition-all cursor-pointer">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                            <span>Simpan PO</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- ============================================================== -->
        <!-- KOMPONEN UI: FILTER CLIENT-SIDE (TANGGAL, WILAYAH & SEARCH)    -->
        <!-- ============================================================== -->
        <div class="space-y-3 mb-5">
            <!-- Filter Tanggal Client-Side (100% Cepat Tanpa Reload Halaman) -->
            <div class="bg-white border border-slate-200/90 rounded-2xl p-3.5 shadow-2xs">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 flex items-center gap-1.5 mr-1">
                            <i data-lucide="calendar" class="w-3.5 h-3.5 text-rose-600"></i>
                            <span>Periode Tanggal:</span>
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
                                   @change="poPage = 1; syncSelectedPo(); refreshIcons()"
                                   class="text-xs font-bold text-slate-800 bg-transparent focus:outline-none cursor-pointer">
                        </div>
                        <span class="text-slate-300 text-xs">-</span>
                        <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1">
                            <span class="text-[11px] text-slate-400 font-semibold">Sampai:</span>
                            <input type="date"
                                   x-model="endDate"
                                   @change="poPage = 1; syncSelectedPo(); refreshIcons()"
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
                tab="po"
                :show-area="true"
                :show-cluster="true"
                :show-tap="true"
                :show-search="true"
                model-search="searchPo"
                search-placeholder="Cari No. PO, DO, Vendor, TAP, catatan..."
                :areas="$areas"
                :clusters="$clusters"
                :taps="$taps" />
        </div>

        <!-- ============================================== -->
        <!-- BAGIAN 1: HEADER TABLE (DAFTAR PO)             -->
        <!-- ============================================== -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-6">
            <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
                <div class="flex items-center gap-2.5">
                    <h3 class="font-extrabold text-slate-900 text-xs flex items-center gap-2">
                        <span>Daftar Purchase Order (PO)</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-200 text-slate-700 font-bold font-mono" x-text="filteredOrders.length + ' data'"></span>
                    </h3>
                </div>

                <!-- Kontrol Checkbox Void & Counter Client-Side -->
                <div class="flex items-center gap-2.5 flex-wrap">
                    <label class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border transition-all cursor-pointer select-none text-xs font-bold"
                           :class="showVoid ? 'bg-rose-50 border-rose-300 text-rose-800 shadow-2xs' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'">
                        <input type="checkbox"
                               x-model="showVoid"
                               @change="onVoidFilterChange()"
                               class="rounded text-rose-600 focus:ring-rose-500 w-4 h-4 cursor-pointer border-slate-300">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="ban" class="w-3.5 h-3.5" :class="showVoid ? 'text-rose-600' : 'text-slate-400'"></i>
                            <span>Tampilkan PO yang Di-VOID</span>
                        </span>
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-mono font-bold"
                              :class="showVoid ? 'bg-rose-200/80 text-rose-900' : 'bg-slate-100 text-slate-500'"
                              x-text="voidCount + ' Void'">
                        </span>
                    </label>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Nomor PO</th>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4 text-center">Item (Qty)</th>
                            <th class="py-3 px-4 text-right">Total Nilai</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($orders as $po)
                            <tr x-show="paginatedOrderIds.includes({{ $po->id }})"
                                @click="selectPoById({{ $po->id }})"
                                :class="selectedPo && selectedPo.id === {{ $po->id }}
                                    ? 'bg-rose-50/90 border-l-4 border-rose-600 shadow-sm'
                                    : 'hover:bg-slate-50/70 cursor-pointer'"
                                class="transition-all cursor-pointer">

                                <td class="py-3.5 px-4 font-mono font-bold text-slate-900 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-mono text-white"
                                              :class="selectedPo && selectedPo.id === {{ $po->id }} ? 'bg-rose-600 font-extrabold' : 'bg-slate-900'">
                                            {{ $po->doc_type }}
                                        </span>
                                        <span class="{{ $po->status === 'CANCELLED' ? 'line-through text-slate-400' : '' }}">{{ $po->doc_number }}</span>
                                    </div>
                                </td>

                                <td class="py-3.5 px-4 text-slate-500 whitespace-nowrap">{{ date('d M Y', strtotime($po->order_date)) }}</td>

                                <td class="py-3.5 px-4 text-center font-bold text-slate-800 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-mono bg-slate-100 text-slate-700">
                                        {{ count($po->items) }} item ({{ number_format($po->total_qty) }} pcs)
                                    </span>
                                </td>

                                <td class="py-3.5 px-4 text-right font-mono font-extrabold text-slate-900 whitespace-nowrap">
                                    <x-money :value="$po->total_amount" />
                                </td>

                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <x-status-badge :status="$po->status" />
                                </td>

                                <td class="py-3.5 px-4 text-center whitespace-nowrap" @click.stop>
                                    <div class="flex items-center justify-center gap-1.5">
                                        @if($po->status === 'ORDERED')
                                            <a href="{{ route('procurement.create-gr', $po->id) }}"
                                               class="px-2 py-1 rounded-lg text-xs font-bold bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 flex items-center gap-1 transition-all shadow-xs">
                                                <span>Terima GR</span>
                                                <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                            </a>

                                            <form method="POST"
                                                  action="{{ route('procurement.void-po', $po->id) }}"
                                                  data-ajax data-confirm="Konfirmasi Void / Pembatalan PO"
                                                  data-confirm-text="Apakah Anda yakin ingin melakukan VOID (Pembatalan) pada dokumen {{ $po->doc_number }}? Dokumen yang sudah di-VOID tidak dapat diproses ke Penerimaan Barang (GR)."
                                                  data-confirm-type="danger"
                                                  data-confirm-btn="Ya, Void PO"
                                                  data-cancel-btn="Tidak, Kembali"
                                                  data-loading-title="Memproses Void PO"
                                                  data-loading-sub="Membatalkan dokumen Purchase Order {{ $po->doc_number }}..."
                                                  data-doctype="VOID">
                                                @csrf
                                                <button type="submit" class="px-2 py-1 rounded-lg text-xs font-bold bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition-all cursor-pointer">
                                                    Void
                                                </button>
                                            </form>
                                        @endif

                                        <button type="button"
                                                @click="selectPoById({{ $po->id }})"
                                                class="px-2 py-1 rounded-lg text-xs font-bold transition-all flex items-center gap-1 shadow-sm cursor-pointer"
                                                :class="selectedPo && selectedPo.id === {{ $po->id }}
                                                    ? 'bg-rose-600 text-white'
                                                    : 'bg-slate-100 hover:bg-slate-200 text-slate-600'">
                                            <span x-text="selectedPo && selectedPo.id === {{ $po->id }} ? 'Aktif' : 'Detail'"></span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                        <tr x-show="filteredOrders.length === 0" x-cloak>
                            <td colspan="6" class="py-6">
                                <x-empty-state title="Tidak Ada Dokumen PO" description="Belum ada Purchase Order yang sesuai dengan filter wilayah, tanggal, atau pencarian Anda." icon="file-search" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Client-Side Pagination Bar PO -->
            <div class="p-4 border-t border-slate-200 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-500">
                <div class="flex items-center gap-3">
                    <div>
                        Menampilkan <strong class="text-slate-800" x-text="poPaginationStart"></strong> - <strong class="text-slate-800" x-text="poPaginationEnd"></strong> dari <strong class="text-slate-800" x-text="filteredOrders.length"></strong> data PO
                    </div>
                    <span class="text-slate-300">|</span>
                    <label class="flex items-center gap-1.5 text-xs">
                        <span>Baris:</span>
                        <select x-model="poPerPage" @change="poPage = 1; refreshIcons()" class="border border-slate-200 rounded-lg px-2 py-1 bg-white text-xs font-semibold text-slate-700 focus:outline-none cursor-pointer">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="ALL">Semua</option>
                        </select>
                    </label>
                </div>

                <div class="flex items-center gap-1" x-show="totalPoPages > 1">
                    <button type="button"
                            @click="poPage = Math.max(1, poPage - 1); refreshIcons()"
                            :disabled="poPage === 1"
                            class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 disabled:opacity-40 text-xs font-bold flex items-center gap-1 cursor-pointer">
                        <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                        <span>Prev</span>
                    </button>

                    <template x-for="(p, idx) in displayedPoPages" :key="idx">
                        <button type="button"
                                @click="if (p !== '...') { poPage = p; refreshIcons(); }"
                                :disabled="p === '...'"
                                :class="poPage === p ? 'bg-rose-600 text-white font-extrabold border-rose-600 shadow-xs' : (p === '...' ? 'cursor-default border-transparent text-slate-400' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100 font-semibold')"
                                class="min-w-[30px] h-7 px-1.5 rounded-lg border text-xs flex items-center justify-center transition-all cursor-pointer"
                                x-text="p">
                        </button>
                    </template>

                    <button type="button"
                            @click="poPage = Math.min(totalPoPages, poPage + 1); refreshIcons()"
                            :disabled="poPage === totalPoPages"
                            class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 disabled:opacity-40 text-xs font-bold flex items-center gap-1 cursor-pointer">
                        <span>Next</span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- BAGIAN 2: DETAIL PANEL INLINE (DI BAWAH TABEL) -->
        <!-- ============================================== -->
        <div x-show="selectedPo" x-cloak class="scroll-mt-4" id="detailSection">
            <div class="rounded-2xl border-2 border-slate-300 shadow-lg overflow-hidden bg-white">

                <div class="bg-slate-900 text-white px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800">
                    <div class="flex items-center gap-3">
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-extrabold bg-rose-500 text-white shadow-sm">RINCIAN PO</span>
                        <h4 class="text-base font-extrabold text-white tracking-tight" x-text="selectedPo ? selectedPo.doc_number : ''"></h4>
                    </div>

                    <div class="flex items-center gap-3 text-xs flex-wrap">
                        <div class="flex items-center gap-1.5 bg-slate-800 px-3 py-1 rounded-lg border border-slate-700">
                            <span class="text-slate-400">Vendor:</span>
                            <span class="font-bold text-white" x-text="selectedPo ? selectedPo.vendor_display : ''"></span>
                        </div>
                        <div class="flex items-center gap-1.5 bg-slate-800 px-3 py-1 rounded-lg border border-slate-700">
                            <span class="text-slate-400">TAP:</span>
                            <span class="font-bold text-white" x-text="selectedPo ? selectedPo.tap_name : ''"></span>
                        </div>
                        <div class="flex items-center gap-1.5 bg-slate-800 px-3 py-1 rounded-lg border border-slate-700">
                            <span class="text-slate-400">Status:</span>
                            <span class="font-bold px-1.5 py-0.5 rounded text-[10px]"
                                  :class="selectedPo && selectedPo.status === 'RECEIVED' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : (selectedPo && selectedPo.status === 'CANCELLED' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'bg-amber-500/20 text-amber-400 border border-amber-500/30')"
                                  x-text="selectedPo ? (selectedPo.status === 'CANCELLED' ? 'VOID / CANCELLED' : selectedPo.status) : ''"></span>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Ringkasan Informasi Dokumen PO -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 mb-5">
                        <!-- Card 1: Vendor -->
                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1">Vendor / Supplier</span>
                            <h5 class="text-xs font-bold text-slate-900 truncate" x-text="selectedPo ? selectedPo.vendor_display : '-'"></h5>
                            <span class="text-[10px] font-mono text-slate-500" x-text="selectedPo && selectedPo.vendor_code ? selectedPo.vendor_code : ''"></span>
                        </div>

                        <!-- Card 2: TAP & Cluster -->
                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1">TAP &amp; Cluster Pemesan</span>
                            <h5 class="text-xs font-bold text-slate-900" x-text="selectedPo ? selectedPo.tap_name : '-'"></h5>
                            <span class="text-[10px] text-slate-500 flex items-center gap-1 mt-0.5">
                                <span x-text="selectedPo ? selectedPo.cluster_name : ''"></span>
                                <span class="text-slate-300">&bull;</span>
                                <span class="text-emerald-700 font-semibold" x-text="selectedPo ? selectedPo.main_warehouse_name : ''"></span>
                            </span>
                        </div>

                        <!-- Card 3: No. DO Telkomsel -->
                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1">No. DO Telkomsel</span>
                            <template x-if="selectedPo && selectedPo.do_number">
                                <span class="font-mono text-xs font-extrabold text-slate-900 bg-slate-200/60 px-2 py-0.5 rounded border border-slate-300/80 inline-block" x-text="selectedPo.do_number"></span>
                            </template>
                            <template x-if="!selectedPo || !selectedPo.do_number">
                                <span class="text-slate-400 italic text-xs">Belum ada No. DO</span>
                            </template>
                        </div>

                        <!-- Card 4: Bukti Fisik DO -->
                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1">Bukti Fisik DO</span>
                            <template x-if="selectedPo && selectedPo.do_file_path">
                                <a :href="'{{ asset('storage') }}/' + selectedPo.do_file_path"
                                   target="_blank"
                                   class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 shadow-2xs transition-all">
                                    <i data-lucide="file-text" class="w-3.5 h-3.5 text-rose-600"></i>
                                    <span>Unduh Bukti DO (PDF)</span>
                                </a>
                            </template>
                            <template x-if="!selectedPo || !selectedPo.do_file_path">
                                <span class="text-slate-400 italic text-xs flex items-center gap-1">
                                    <i data-lucide="file-x" class="w-3.5 h-3.5 text-slate-300"></i>
                                    <span>Belum diunggah</span>
                                </span>
                            </template>
                        </div>
                    </div>

                    <!-- Catatan PO (lengkap) -->
                    <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50/70 px-4 py-3 flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                            <i data-lucide="sticky-note" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-800 block">Catatan / Keterangan PO</span>
                            <p class="text-xs font-semibold text-slate-800 mt-0.5 leading-relaxed whitespace-pre-line break-words"
                               x-text="selectedPo && selectedPo.notes ? selectedPo.notes : 'Tidak ada catatan.'"
                               :class="selectedPo && selectedPo.notes ? '' : 'text-slate-400 italic font-medium'"></p>
                        </div>
                    </div>

                    <div class="mb-5">
                        <div class="border border-slate-200 rounded-xl overflow-hidden bg-white">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider border-b border-slate-200">
                                    <tr>
                                        <th class="py-2.5 px-4">Produk</th>
                                        <th class="py-2.5 px-4">Tipe</th>
                                        @if($iccidEnabled)
                                            <th class="py-2.5 px-4">Tracking</th>
                                        @endif
                                        <th class="py-2.5 px-4 text-center">Qty</th>
                                        <th class="py-2.5 px-4 text-right">Harga Modal</th>
                                        <th class="py-2.5 px-4 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium">
                                    <template x-for="(item, idx) in (selectedPo ? selectedPo.items : [])" :key="idx">
                                        <tr class="hover:bg-slate-50/70">
                                            <td class="py-3 px-4 font-bold text-slate-900">
                                                <span class="font-mono text-[10px] text-slate-400 block" x-text="item.sku"></span>
                                                <span x-text="item.product_name"></span>
                                            </td>
                                            <td class="py-3 px-4">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700" x-text="item.product_type"></span>
                                            </td>
                                            @if($iccidEnabled)
                                                <td class="py-3 px-4 font-mono text-[11px] font-bold"
                                                    :class="item.tracking_method === 'ICCID' ? 'text-purple-700' : 'text-blue-700'"
                                                    x-text="item.tracking_method">
                                                </td>
                                            @endif
                                            <td class="py-3 px-4 text-center font-mono font-bold text-slate-800">
                                                <span x-text="Number(item.quantity).toLocaleString('id-ID')"></span> pcs
                                            </td>
                                            <td class="py-3 px-4 text-right font-mono text-slate-600">
                                                Rp <span x-text="Number(item.cost_price).toLocaleString('id-ID')"></span>
                                            </td>
                                            <td class="py-3 px-4 text-right font-mono font-extrabold text-slate-900">
                                                Rp <span x-text="Number(item.subtotal).toLocaleString('id-ID')"></span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Action jika status ORDERED -->
                    <template x-if="selectedPo && selectedPo.status === 'ORDERED'">
                        <div class="mt-4 p-4 rounded-xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-2 text-xs text-slate-700 font-semibold">
                                <i data-lucide="package-search" class="w-4 h-4 text-amber-600"></i>
                                <span>Barang fisik dari vendor sudah tiba di gudang?</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <form method="POST"
                                      :action="selectedPo ? ('{{ url('procurement/void-po') }}/' + selectedPo.id) : '#'"
                                      data-ajax data-confirm="Konfirmasi Void / Pembatalan PO"
                                      :data-confirm-text="'Apakah Anda yakin ingin melakukan VOID (Pembatalan) pada dokumen ' + (selectedPo ? selectedPo.doc_number : '') + '? Dokumen yang sudah di-VOID tidak dapat diproses ke Penerimaan Barang (GR).'"
                                      data-confirm-type="danger"
                                      data-confirm-btn="Ya, Void PO"
                                      data-cancel-btn="Tidak, Kembali"
                                      data-loading-title="Memproses Void PO"
                                      data-loading-sub="Membatalkan dokumen Purchase Order..."
                                      data-doctype="VOID">
                                    @csrf
                                    <button type="submit" class="px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold rounded-xl text-xs cursor-pointer">
                                        Void PO
                                    </button>
                                </form>
                                <a :href="selectedPo ? ('{{ url('procurement/gr') }}/' + selectedPo.id) : '#'"
                                   class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs flex items-center justify-center gap-2 shadow-sm transition-all active:scale-95">
                                    <i data-lucide="boxes" class="w-4 h-4"></i>
                                    <span>Buka Formulir Penerimaan Fisik (GR)</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </div>
                        </div>
                    </template>

                    <!-- Info jika PO sudah VOID / CANCELLED -->
                    <template x-if="selectedPo && selectedPo.status === 'CANCELLED'">
                        <div class="mt-4 p-4 rounded-xl bg-rose-50 border border-rose-200 flex items-center gap-3 text-xs text-rose-900">
                            <i data-lucide="ban" class="w-5 h-5 text-rose-600 shrink-0"></i>
                            <div>
                                <h5 class="font-extrabold text-rose-950">Dokumen PO Ini Telah Di-VOID (Dibatalkan)</h5>
                                <p class="text-[11px] text-rose-700 mt-0.5" x-text="selectedPo ? (selectedPo.notes || 'PO dibatalkan dan tidak dapat diproses ke Penerimaan Barang (GR).') : ''"></p>
                            </div>
                        </div>
                    </template>

                    <!-- Feedback jika PO sudah RECEIVED -->
                    <template x-if="selectedPo && selectedPo.status === 'RECEIVED'">
                        <div class="mt-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-emerald-900">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                    <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h5 class="font-extrabold text-emerald-950 text-xs">Barang Fisik Sudah Diterima Lengkap (GR Selesai)</h5>
                                    <div class="flex flex-wrap items-center gap-2 mt-1 text-[11px] text-emerald-800">
                                        <span>Dokumen GR: <strong class="font-mono" x-text="selectedPo ? (selectedPo.gr_number || 'Tercatat') : ''"></strong></span>
                                        <span>&bull;</span>
                                        <span>No. DO: <strong class="font-mono bg-white px-1.5 py-0.5 rounded border border-emerald-200" x-text="selectedPo ? (selectedPo.do_number || '-') : ''"></strong></span>
                                    </div>
                                </div>
                            </div>

                            <template x-if="selectedPo && selectedPo.do_file_path">
                                <a :href="'{{ asset('storage') }}/' + (selectedPo ? selectedPo.do_file_path : '')"
                                   target="_blank"
                                   class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl text-xs flex items-center justify-center gap-2 shadow-xs transition-all shrink-0">
                                    <i data-lucide="file-text" class="w-4 h-4"></i>
                                    <span>Lihat Berkas Surat Jalan (PDF)</span>
                                </a>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function procurementMasterDetail() {
            const rawOrders = @json($orders);
            return {
                createPoOpen: false,
                selectedPo: null,
                allOrders: rawOrders,
                allProducts: @json($products),
                allClusters: @json($clusters),
                allTaps: @json($taps),

                // Form Create PO
                formTapId: '{{ $taps->first()->id ?? '' }}',
                items: [
                    { product_id: '', searchText: '', dropdownOpen: false, tracking_method: 'QUANTITY', cost_price: 0, quantity: 100 }
                ],
                grandTotal: 0,

                // Filter Client-Side: Void, Tanggal, Wilayah & Search
                showVoid: false, // Default false: sembunyikan PO yang di-VOID agar PO aktif tampil rapi
                startDate: '{{ request('start_date', '') }}',
                endDate: '{{ request('end_date', '') }}',
                selectedArea: '',
                selectedCluster: '',
                selectedTap: '',
                searchPo: '',

                // Pagination PO (Murni Client-Side)
                poPage: 1,
                poPerPage: '10',

                get voidCount() {
                    return this.allOrders.filter(po => po.status === 'CANCELLED').length;
                },

                get selectedTapMainWh() {
                    const t = this.allTaps.find(x => String(x.id) === String(this.formTapId));
                    return t ? t.main_warehouse_name : '';
                },

                get totalQty() {
                    return this.items.reduce((acc, row) => acc + (parseInt(row.quantity) || 0), 0);
                },

                get canSubmitPo() {
                    return this.items.length > 0 && this.items.every(r => Boolean(r.product_id) && parseInt(r.quantity) > 0);
                },

                initData() {
                    // Pilih PO aktif pertama yang sesuai dengan filter default
                    if (this.filteredOrders.length > 0) {
                        this.selectedPo = this.filteredOrders[0];
                    } else if (this.allOrders.length > 0) {
                        this.selectedPo = this.allOrders[0];
                    }
                    this.$watch('searchPo', () => { this.poPage = 1; this.syncSelectedPo(); this.refreshIcons(); });
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
                    this.poPage = 1;
                    this.syncSelectedPo();
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

                onVoidFilterChange() {
                    this.poPage = 1;
                    this.syncSelectedPo();
                    this.refreshIcons();
                },

                syncSelectedPo() {
                    this.$nextTick(() => {
                        // Jika PO yang sedang dipilih tidak terlihat karena filter, pindah ke PO pertama yang terlihat
                        if (this.selectedPo && !this.isOrderVisible(this.selectedPo)) {
                            this.selectedPo = this.filteredOrders.length > 0 ? this.filteredOrders[0] : null;
                        } else if (!this.selectedPo && this.filteredOrders.length > 0) {
                            this.selectedPo = this.filteredOrders[0];
                        }
                    });
                },

                // Searchable Product Combobox Helpers
                filterProductsForRow(query) {
                    if (!query || !query.trim()) return this.allProducts;
                    const q = query.toLowerCase();
                    return this.allProducts.filter(p =>
                        (p.name && p.name.toLowerCase().includes(q)) ||
                        (p.sku && p.sku.toLowerCase().includes(q)) ||
                        (p.type && p.type.toLowerCase().includes(q)) ||
                        (p.denom && p.denom.toLowerCase().includes(q))
                    );
                },

                selectProductForRow(index, prod) {
                    this.items[index].product_id = prod.id;
                    this.items[index].searchText = `[${prod.sku}] ${prod.name}`;
                    this.items[index].tracking_method = prod.tracking_method || 'QUANTITY';
                    this.items[index].cost_price = parseFloat(prod.cost_price) || 0;
                    this.items[index].dropdownOpen = false;
                    this.calculateTotal();
                    this.refreshIcons();
                },

                clearProductRow(index) {
                    this.items[index].product_id = '';
                    this.items[index].searchText = '';
                    this.items[index].tracking_method = 'QUANTITY';
                    this.items[index].cost_price = 0;
                    this.items[index].dropdownOpen = true;
                    this.calculateTotal();
                },

                // Filter Wilayah & Pagination Getters
                get clusterOptions() {
                    if (!this.selectedArea) return this.allClusters;
                    return this.allClusters.filter(c => String(c.area_id) === String(this.selectedArea));
                },

                get tapOptions() {
                    if (this.selectedCluster) {
                        return this.allTaps.filter(t => String(t.cluster_id) === String(this.selectedCluster));
                    }
                    if (this.selectedArea) {
                        return this.allTaps.filter(t => String(t.area_id) === String(this.selectedArea));
                    }
                    return this.allTaps;
                },

                onAreaChange() {
                    this.selectedCluster = '';
                    this.selectedTap = '';
                    this.poPage = 1;
                    this.syncSelectedPo();
                    this.refreshIcons();
                },

                onClusterChange() {
                    this.selectedTap = '';
                    this.poPage = 1;
                    this.syncSelectedPo();
                    this.refreshIcons();
                },

                onTapChange() {
                    this.poPage = 1;
                    this.syncSelectedPo();
                    this.refreshIcons();
                },

                onSearchChange() {
                    this.poPage = 1;
                    this.syncSelectedPo();
                    this.refreshIcons();
                },

                hasActiveFilter() {
                    return Boolean(this.selectedArea || this.selectedCluster || this.selectedTap || this.searchPo || this.startDate || this.endDate || this.showVoid);
                },

                resetFilter() {
                    this.selectedArea = '';
                    this.selectedCluster = '';
                    this.selectedTap = '';
                    this.searchPo = '';
                    this.startDate = '';
                    this.endDate = '';
                    this.showVoid = false;
                    this.poPage = 1;
                    this.syncSelectedPo();
                    this.refreshIcons();
                },

                isOrderVisible(po) {
                    // Filter Void: jika showVoid false, sembunyikan PO berstatus CANCELLED
                    if (!this.showVoid && po.status === 'CANCELLED') return false;

                    // Filter Wilayah
                    if (this.selectedArea && String(po.area_id) !== String(this.selectedArea)) return false;
                    if (this.selectedCluster && String(po.cluster_id) !== String(this.selectedCluster)) return false;
                    if (this.selectedTap && String(po.tap_id) !== String(this.selectedTap)) return false;

                    // Filter Tanggal Client-Side
                    if (this.startDate) {
                        const poDate = po.order_date ? String(po.order_date).substring(0, 10) : '';
                        if (poDate && poDate < this.startDate) return false;
                    }
                    if (this.endDate) {
                        const poDate = po.order_date ? String(po.order_date).substring(0, 10) : '';
                        if (poDate && poDate > this.endDate) return false;
                    }

                    // Filter Search Text
                    if (this.searchPo) {
                        const q = this.searchPo.toLowerCase().trim();
                        const match = (po.doc_number && po.doc_number.toLowerCase().includes(q)) ||
                                      (po.do_number && po.do_number.toLowerCase().includes(q)) ||
                                      (po.vendor_display && po.vendor_display.toLowerCase().includes(q)) ||
                                      (po.tap_name && po.tap_name.toLowerCase().includes(q)) ||
                                      (po.cluster_name && po.cluster_name.toLowerCase().includes(q)) ||
                                      (po.notes && po.notes.toLowerCase().includes(q));
                        if (!match) return false;
                    }
                    return true;
                },

                get filteredOrders() {
                    return this.allOrders.filter(po => this.isOrderVisible(po));
                },

                get totalPoPages() {
                    if (this.poPerPage === 'ALL') return 1;
                    const perPage = parseInt(this.poPerPage, 10) || 10;
                    return Math.max(1, Math.ceil(this.filteredOrders.length / perPage));
                },

                get paginatedOrderIds() {
                    if (this.poPerPage === 'ALL') return this.filteredOrders.map(po => po.id);
                    const perPage = parseInt(this.poPerPage, 10) || 10;
                    const start = (this.poPage - 1) * perPage;
                    return this.filteredOrders.slice(start, start + perPage).map(po => po.id);
                },

                get poPaginationStart() {
                    if (this.filteredOrders.length === 0) return 0;
                    if (this.poPerPage === 'ALL') return 1;
                    const perPage = parseInt(this.poPerPage, 10) || 10;
                    return (this.poPage - 1) * perPage + 1;
                },

                get poPaginationEnd() {
                    if (this.poPerPage === 'ALL') return this.filteredOrders.length;
                    const perPage = parseInt(this.poPerPage, 10) || 10;
                    return Math.min(this.poPage * perPage, this.filteredOrders.length);
                },

                get displayedPoPages() {
                    const current = this.poPage;
                    const total = this.totalPoPages;
                    if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
                    if (current <= 4) return [1, 2, 3, 4, 5, '...', total];
                    if (current >= total - 3) return [1, '...', total - 4, total - 3, total - 2, total - 1, total];
                    return [1, '...', current - 1, current, current + 1, '...', total];
                },

                selectPo(po) {
                    this.selectedPo = po;
                    this.refreshIcons();
                },

                selectPoById(id) {
                    const found = this.allOrders.find(o => Number(o.id) === Number(id));
                    if (found) {
                        this.selectedPo = found;
                        this.refreshIcons();
                        this.$nextTick(() => {
                            const el = document.getElementById('detailSection');
                            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        });
                    }
                },

                addItemRow() {
                    this.items.push({
                        product_id: '',
                        searchText: '',
                        dropdownOpen: false,
                        tracking_method: 'QUANTITY',
                        cost_price: 0,
                        quantity: 100
                    });
                    this.refreshIcons();
                },

                removeItemRow(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                        this.calculateTotal();
                    }
                },

                calculateTotal() {
                    this.grandTotal = this.items.reduce((acc, row) => {
                        const price = parseFloat(row.cost_price) || 0;
                        const qty = parseInt(row.quantity) || 0;
                        return acc + (price * qty);
                    }, 0);
                },

                refreshIcons() {
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
