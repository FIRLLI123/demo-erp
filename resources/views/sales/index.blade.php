<x-app-layout>
    <x-slot name="title">Sales Order</x-slot>
    <x-slot name="headerTitle">Sales Order & Pengiriman</x-slot>
    <x-slot name="badge">SO &bull; GD &bull; AR</x-slot>

    <div x-data="salesApp()" x-init="initData()">
        
        <!-- Flash Alert Feedback -->
        <x-alert />

        <!-- Top Action Bar -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight">
                    Pesanan Penjualan & Pengiriman
                </h2>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('reports.index', ['type' => 'SALES']) }}" 
                   class="px-3.5 py-2.5 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-xs transition-all shrink-0">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-rose-600"></i>
                    <span>Rekap Laporan</span>
                </a>
                <button type="button" 
                        @click="createSoOpen = !createSoOpen" 
                        class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-sm transition-all active:scale-95 shrink-0 cursor-pointer">
                    <i data-lucide="plus-circle" class="w-4 h-4 text-amber-400"></i>
                    <span x-text="createSoOpen ? 'Tutup Form' : '+ Buat SO Baru'"></span>
                </button>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- FORM PEMBUATAN SO BARU (INLINE CARD)           -->
        <!-- ============================================== -->
        <div x-show="createSoOpen" x-cloak x-transition class="mb-6 bg-white rounded-2xl border-2 border-amber-500/40 p-5 shadow-lg">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <i data-lucide="shopping-bag" class="w-4 h-4 text-amber-600"></i>
                    <span>Form Sales Order (SO)</span>
                </h3>
                <button @click="createSoOpen = false" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('sales.store') }}" 
                  enctype="multipart/form-data"
                  @submit="isSubmitting = true"
                  data-loading-title="Menerbitkan Sales Order"
                  data-loading-sub="Menyimpan pesanan dan memproses stok..."
                  data-doctype="SO">
                @csrf

                <!-- Header Info -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Outlet / Customer</label>
                        <select name="customer_id" 
                                x-model="selectedCustomerId" 
                                @change="onCustomerChange()"
                                required 
                                class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                            <option value="">-- Pilih Customer --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">
                                    [{{ $c->code }}] {{ $c->name }} ({{ $c->type }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Sales Kanvaser / PIC</label>
                        <select name="sales_id" 
                                x-model="selectedSalesId"
                                @change="onSalesChange()"
                                required 
                                class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                            <option value="">-- Pilih Sales --</option>
                            @foreach($salesList as $s)
                                <option value="{{ $s->id }}" 
                                        data-wh-id="{{ $s->warehouse_id }}" 
                                        data-wh-name="{{ $s->warehouse_name }}">
                                    [{{ $s->code }}] {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Gudang Sumber Stok <span class="text-rose-500">*</span>
                        </label>
                        <select name="warehouse_id" 
                                x-model="selectedWarehouseId"
                                @change="onWarehouseChange()"
                                required
                                class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 cursor-pointer shadow-2xs">
                            <option value="">-- Pilih Gudang Sumber --</option>
                            <template x-for="wh in warehouses" :key="wh.id">
                                <option :value="wh.id" x-text="`[${wh.type}] ${wh.name} (${wh.tap_name || ''})`"></option>
                            </template>
                        </select>
                        <template x-if="selectedWarehouseInfo">
                            <span class="text-[10px] text-amber-700 font-bold mt-1 block truncate" x-text="selectedWarehouseInfo"></span>
                        </template>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Pesanan</label>
                        <input type="date" name="order_date" value="{{ date('Y-m-d') }}" required class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan / Keterangan (Notes)</label>
                        <input type="text" name="notes" placeholder="e.g. Titip nota asli, kirim sore" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                    </div>
                </div>

                <!-- Live Customer Credit Info Strip -->
                <div x-show="creditInfo" x-cloak class="mb-4 p-3 rounded-xl border text-xs transition-all"
                     :class="creditInfo && (creditInfo.credit_limit <= 0 || grandTotal > creditInfo.remaining_limit) ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-slate-50 border-slate-200 text-slate-700'">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <i data-lucide="shield-alert" class="w-4 h-4 shrink-0" 
                               :class="creditInfo && (creditInfo.credit_limit <= 0 || grandTotal > creditInfo.remaining_limit) ? 'text-rose-600' : 'text-slate-500'"></i>
                            <div>
                                <span class="font-bold text-slate-900" x-text="creditInfo ? creditInfo.customer_name : ''"></span>
                                <span class="text-slate-500 text-[11px] ml-1">
                                    &bull; Plafon Limit: <strong class="font-mono text-slate-800">Rp <span x-text="creditInfo ? creditInfo.credit_limit.toLocaleString('id-ID') : 0"></span></strong>
                                    &bull; Piutang + SO Aktif: <strong class="font-mono text-slate-800">Rp <span x-text="creditInfo ? creditInfo.used_limit.toLocaleString('id-ID') : 0"></span></strong>
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] font-semibold text-slate-500">Sisa Limit Tersedia:</span>
                            <span class="font-mono font-extrabold px-2.5 py-1 rounded-lg text-xs"
                                  :class="creditInfo && (creditInfo.credit_limit <= 0 || grandTotal > creditInfo.remaining_limit) ? 'bg-rose-200/80 text-rose-900 border border-rose-300' : 'bg-emerald-100 text-emerald-800 border border-emerald-300'">
                                Rp <span x-text="creditInfo ? creditInfo.remaining_limit.toLocaleString('id-ID') : 0"></span>
                            </span>
                        </div>
                    </div>
                    <!-- Warning Message Over Limit -->
                    <template x-if="creditInfo && creditInfo.credit_limit <= 0">
                        <div class="mt-2 pt-2 border-t border-rose-200 text-[11px] font-bold text-rose-700 flex items-center gap-1.5">
                            <i data-lucide="alert-octagon" class="w-3.5 h-3.5"></i>
                            <span>Customer memiliki Limit Rp 0. Tidak diizinkan membuat Sales Order!</span>
                        </div>
                    </template>
                    <template x-if="creditInfo && creditInfo.credit_limit > 0 && grandTotal > creditInfo.remaining_limit">
                        <div class="mt-2 pt-2 border-t border-rose-200 text-[11px] font-bold text-rose-700 flex items-center gap-1.5">
                            <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                            <span>Total SO ini melebihi sisa limit kredit customer! Harap kurangi kuantitas item atau lunasi piutang terlebih dahulu.</span>
                        </div>
                    </template>
                </div>

                <!-- Multi-Item Rows Table dengan Searchable Product Selector -->
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold text-slate-800">Daftar Barang yang Dipesan</label>
                        <button type="button" @click="addItemRow()" class="text-xs font-bold text-amber-700 hover:text-amber-800 flex items-center gap-1 cursor-pointer">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Baris
                        </button>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(row, index) in items" :key="index">
                            <div class="border border-slate-200 rounded-xl p-3 bg-slate-50/60">
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start">
                                    
                                    <!-- Searchable Produk -->
                                    <div class="{{ ($iccidEnabled ?? config('erp.enable_iccid', false)) ? 'md:col-span-4' : 'md:col-span-5' }} relative" @click.outside="row.dropdownOpen = false">
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Produk</label>
                                        <input type="hidden" :name="`items[${index}][product_id]`" :value="row.product_id" required>
                                        
                                        <div class="relative flex items-center">
                                            <input type="text"
                                                   x-model="row.searchText"
                                                   @focus="row.dropdownOpen = true"
                                                   @input="row.dropdownOpen = true"
                                                   :disabled="!selectedWarehouseId"
                                                   :placeholder="!selectedWarehouseId ? 'Pilih Sales dulu' : 'Cari produk...'"
                                                   class="w-full text-xs font-bold text-slate-800 bg-white border border-slate-200 rounded-lg pl-3 pr-8 py-1.5 focus:outline-none focus:ring-1 focus:ring-amber-500 disabled:bg-slate-100 disabled:cursor-not-allowed">
                                            <button type="button"
                                                    x-show="row.product_id"
                                                    @click="clearProductRow(index)"
                                                    class="absolute right-2 text-slate-400 hover:text-rose-600 cursor-pointer"
                                                    title="Ganti produk">
                                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </div>

                                        <!-- Dropdown List Hasil Pencarian -->
                                        <div x-show="row.dropdownOpen && selectedWarehouseId"
                                             x-cloak
                                             class="absolute z-40 left-0 right-0 mt-1 max-h-56 overflow-y-auto bg-white border border-slate-200 rounded-xl shadow-xl divide-y divide-slate-100">
                                            <template x-for="prod in filterProductsForRow(row.searchText)" :key="prod.id">
                                                <button type="button"
                                                        @click="selectProductForRow(index, prod)"
                                                        class="w-full text-left px-3 py-2 hover:bg-amber-50 flex items-center justify-between gap-2 transition-colors cursor-pointer">
                                                    <div>
                                                        <span class="font-bold text-slate-900 block text-xs" x-text="prod.name"></span>
                                                        <span class="font-mono text-[10px] text-slate-500" x-text="prod.sku + ' • Tipe: ' + prod.type"></span>
                                                    </div>
                                                    <div class="text-right shrink-0">
                                                        <span class="font-mono text-[11px] font-bold text-amber-700 block" x-text="'Rp ' + Number(prod.selling_price).toLocaleString('id-ID')"></span>
                                                        <span class="text-[10px] font-semibold" :class="getAvailableStock(prod.id) > 0 ? 'text-emerald-600' : 'text-rose-500'" x-text="'Stok: ' + getAvailableStock(prod.id) + ' pcs'"></span>
                                                    </div>
                                                </button>
                                            </template>
                                            <div x-show="filterProductsForRow(row.searchText).length === 0" class="px-3 py-3 text-center text-slate-400 text-xs">
                                                Produk tidak ditemukan di gudang ini.
                                            </div>
                                        </div>
                                    </div>

                                    @if($iccidEnabled ?? config('erp.enable_iccid', false))
                                        <!-- Tracking Info -->
                                        <div class="md:col-span-2">
                                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Tracking</label>
                                            <span class="inline-block px-2 py-1 rounded text-[11px] font-bold font-mono"
                                                  :class="row.tracking_method === 'ICCID' ? 'bg-purple-100 text-purple-700 border border-purple-200' : 'bg-blue-100 text-blue-700 border border-blue-200'"
                                                  x-text="row.tracking_method || '-'">
                                            </span>
                                        </div>
                                    @endif

                                    <!-- Harga Jual Master (Read-only) -->
                                    <div class="md:col-span-2">
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Harga Jual</label>
                                        <div class="text-xs font-mono font-bold text-slate-800 py-1.5">
                                            Rp <span x-text="(row.unit_price || 0).toLocaleString('id-ID')"></span>
                                        </div>
                                    </div>

                                    <!-- Quantity Input / Display -->
                                    <div class="{{ ($iccidEnabled ?? config('erp.enable_iccid', false)) ? 'md:col-span-2' : 'md:col-span-3' }}">
                                        @if($iccidEnabled ?? config('erp.enable_iccid', false))
                                            <template x-if="row.tracking_method === 'QUANTITY' || !row.tracking_method">
                                                <div>
                                                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Kuantitas</label>
                                                    <input type="number" 
                                                           :name="`items[${index}][quantity]`" 
                                                           x-model.number="row.quantity" 
                                                           min="1" 
                                                           :max="getAvailableStock(row.product_id)" 
                                                           required 
                                                           :disabled="!row.product_id"
                                                           class="w-full text-xs font-bold text-center bg-white border border-slate-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-amber-500 disabled:bg-slate-100">
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
                                        @else
                                            <div>
                                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Kuantitas</label>
                                                <div class="flex items-center gap-1.5">
                                                    <input type="number" 
                                                           :name="`items[${index}][quantity]`" 
                                                           x-model.number="row.quantity" 
                                                           min="1" 
                                                           :max="getAvailableStock(row.product_id)" 
                                                           required 
                                                           :disabled="!row.product_id"
                                                           class="w-full text-xs font-bold text-center bg-white border border-slate-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-amber-500 disabled:bg-slate-100">
                                                    <span class="text-[11px] text-slate-400 font-medium shrink-0">pcs</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Subtotal -->
                                    <div class="md:col-span-1">
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Subtotal</label>
                                        <div class="text-xs font-mono font-extrabold text-slate-900 py-1.5 truncate">
                                            Rp <span x-text="getRowSubtotal(row).toLocaleString('id-ID')"></span>
                                        </div>
                                    </div>

                                    <!-- Delete Button -->
                                    <div class="md:col-span-1 pt-6 text-center">
                                        <button type="button" @click="removeItemRow(index)" class="text-slate-400 hover:text-rose-600 transition-colors p-1 cursor-pointer" x-show="items.length > 1">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </div>

                                @if($iccidEnabled ?? config('erp.enable_iccid', false))
                                    <!-- SUB-SECTION: ICCID SELECTION IF PRODUCT IS ICCID -->
                                    <template x-if="row.tracking_method === 'ICCID' && row.product_id && selectedWarehouseId">
                                        <div class="mt-3 pt-3 border-t border-slate-200/80 bg-white rounded-lg p-3">
                                            <div class="flex items-center justify-between mb-2">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-[11px] font-bold text-slate-700">Metode Serial ICCID:</span>
                                                    <div class="inline-flex rounded-lg border border-slate-200 p-0.5 bg-slate-100 text-[10px] font-bold">
                                                        <button type="button" @click="row.iccid_mode = 'SELECT'" :class="row.iccid_mode === 'SELECT' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500'" class="px-2 py-0.5 rounded-md">
                                                            Pilih Stok (<span x-text="getIccidsForProduct(row.product_id).length"></span>)
                                                        </button>
                                                        <button type="button" @click="row.iccid_mode = 'PASTE'" :class="row.iccid_mode === 'PASTE' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500'" class="px-2 py-0.5 rounded-md">
                                                            Paste Serial
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <input type="hidden" :name="`items[${index}][iccid_mode]`" :value="row.iccid_mode">

                                            <!-- Mode Select -->
                                            <div x-show="row.iccid_mode === 'SELECT'">
                                                <div class="flex items-center justify-between mb-1.5 text-[11px]">
                                                    <span class="text-slate-500">Centang nomor serial yang dipesan:</span>
                                                    <div class="flex items-center gap-2">
                                                        <button type="button" @click="selectAllIccids(index)" class="text-amber-600 font-bold hover:underline cursor-pointer">Pilih Semua</button>
                                                        <span>&bull;</span>
                                                        <button type="button" @click="row.selected_iccids = []" class="text-slate-400 font-bold hover:underline cursor-pointer">Reset</button>
                                                    </div>
                                                </div>

                                                <div class="max-h-32 overflow-y-auto border border-slate-200 rounded-lg p-2 grid grid-cols-2 sm:grid-cols-4 gap-1.5 bg-slate-50/50">
                                                    <template x-for="iccid in getIccidsForProduct(row.product_id)" :key="iccid.id">
                                                        <label class="flex items-center gap-1.5 text-[10px] font-mono bg-white p-1 rounded border border-slate-200 cursor-pointer hover:bg-amber-50/50">
                                                            <input type="checkbox" 
                                                                   :name="`items[${index}][selected_iccids][]`" 
                                                                   :value="iccid.iccid_number"
                                                                   x-model="row.selected_iccids"
                                                                   class="rounded text-amber-600 focus:ring-amber-500 w-3.5 h-3.5">
                                                            <span x-text="iccid.iccid_number" class="truncate"></span>
                                                        </label>
                                                    </template>
                                                    <template x-if="getIccidsForProduct(row.product_id).length === 0">
                                                        <div class="col-span-full py-2 text-center text-slate-400 text-xs">
                                                            Tidak ada serial AVAILABLE di gudang ini.
                                                        </div>
                                                    </template>
                                                </div>
                                                <div class="mt-1 text-[11px] text-slate-500">
                                                    Terpilih: <span class="font-bold font-mono text-purple-700" x-text="row.selected_iccids.length"></span> unit.
                                                </div>
                                            </div>

                                            <!-- Mode Paste -->
                                            <div x-show="row.iccid_mode === 'PASTE'">
                                                <textarea :name="`items[${index}][iccid_text]`" 
                                                          x-model="row.iccid_text" 
                                                          rows="2" 
                                                          placeholder="Paste serial ICCID disini (1 per baris)..." 
                                                          class="w-full text-xs font-mono bg-white border border-slate-200 rounded-lg p-2 focus:outline-none"></textarea>
                                            </div>
                                        </div>
                                    </template>
                                @endif

                            </div>
                        </template>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-3 border-t border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-500 uppercase">Total Nilai Tagihan:</span>
                        <span class="text-base font-extrabold text-slate-900 font-mono">
                            Rp <span x-text="grandTotal.toLocaleString('id-ID')"></span>
                        </span>
                        <span class="text-xs text-slate-400 font-medium">(<span x-text="totalQty"></span> pcs)</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="createSoOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" 
                                :disabled="isSubmitting || (creditInfo && (creditInfo.credit_limit <= 0 || grandTotal > creditInfo.remaining_limit))"
                                class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-sm transition-all active:scale-95 cursor-pointer">
                            <template x-if="!isSubmitting">
                                <div class="flex items-center gap-1.5">
                                    <i data-lucide="check" class="w-4 h-4 text-amber-400"></i>
                                    <span>Simpan Sales Order (SO)</span>
                                </div>
                            </template>
                            <template x-if="isSubmitting">
                                <div class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <span>Menyimpan Pesanan...</span>
                                </div>
                            </template>
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
                            <span>Periode Tanggal SO:</span>
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
                                   @change="resetPage(); syncSelection(); refreshIcons()"
                                   class="text-xs font-bold text-slate-800 bg-transparent focus:outline-none cursor-pointer">
                        </div>
                        <span class="text-slate-300 text-xs">-</span>
                        <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1">
                            <span class="text-[11px] text-slate-400 font-semibold">Sampai:</span>
                            <input type="date"
                                   x-model="endDate"
                                   @change="resetPage(); syncSelection(); refreshIcons()"
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
                tab="so"
                :show-area="true"
                :show-cluster="true"
                :show-tap="true"
                :show-search="true"
                model-search="searchSo"
                search-placeholder="Cari No. SO, No. GD, Customer, Sales, Gudang, Catatan..."
                :areas="$areas"
                :clusters="$clusters"
                :taps="$taps" />
        </div>

        <!-- ============================================== -->
        <!-- BAGIAN 1: HEADER TABLE (DAFTAR DOKUMEN SO)     -->
        <!-- ============================================== -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-6">
            <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
                <div class="flex items-center gap-2.5">
                    <h3 class="font-extrabold text-slate-900 text-xs flex items-center gap-2">
                        <i data-lucide="shopping-bag" class="w-4 h-4 text-slate-700"></i>
                        <span>Daftar Sales Order (SO)</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-200 text-slate-700 font-bold font-mono" x-text="filteredOrders.length + ' data'"></span>
                    </h3>
                </div>

                <!-- Kontrol Filter Status & Checkbox Void Client-Side -->
                <div class="flex items-center gap-2.5 flex-wrap">
                    <!-- Dropdown Filter Status SO -->
                    <div class="flex items-center gap-1.5 bg-white border border-slate-200 rounded-xl px-2.5 py-1 text-xs shadow-2xs">
                        <span class="text-slate-400 font-semibold text-[11px]">Status:</span>
                        <select x-model="statusFilter" @change="resetPage(); syncSelection(); refreshIcons()" class="text-xs font-bold text-slate-800 bg-transparent focus:outline-none cursor-pointer">
                            <option value="">Semua Status</option>
                            <option value="DRAFT">DRAFT</option>
                            <option value="PENDING_APPROVAL">PENDING APPROVAL</option>
                            <option value="APPROVED">APPROVED</option>
                            <option value="DELIVERED">DELIVERED</option>
                            <option value="COMPLETED">COMPLETED</option>
                        </select>
                    </div>

                    <!-- Checkbox Void -->
                    <label class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border transition-all cursor-pointer select-none text-xs font-bold"
                           :class="showVoid ? 'bg-rose-50 border-rose-300 text-rose-800 shadow-2xs' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'">
                        <input type="checkbox"
                               x-model="showVoid"
                               @change="resetPage(); syncSelection(); refreshIcons()"
                               class="rounded text-rose-600 focus:ring-rose-500 w-4 h-4 cursor-pointer border-slate-300">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="ban" class="w-3.5 h-3.5" :class="showVoid ? 'text-rose-600' : 'text-slate-400'"></i>
                            <span>Tampilkan SO yang Di-VOID</span>
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
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">No. SO</th>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Customer</th>
                            <th class="py-3 px-4">Sales & Gudang</th>
                            <th class="py-3 px-4">Catatan</th>
                            <th class="py-3 px-4 text-center">Qty</th>
                            <th class="py-3 px-4 text-right">Total</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($orders as $so)
                            <tr x-show="paginatedOrderIds.includes({{ $so->id }})"
                                @click="selectSoById({{ $so->id }})"
                                class="cursor-pointer transition-colors"
                                :class="selectedSo && selectedSo.id === {{ $so->id }} ? 'bg-amber-50/80 border-l-4 border-amber-500' : 'hover:bg-slate-50/70'">
                                
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                    <div class="flex items-center gap-1.5">
                                        <span class="px-1.5 py-0.5 rounded text-[10px] bg-amber-600 text-white font-mono font-bold">SO</span>
                                        <span>{{ $so->doc_number }}</span>
                                    </div>
                                    <span class="text-[10px] text-slate-400 block font-normal">{{ $so->channel }}</span>
                                </td>

                                <td class="py-3.5 px-4 text-slate-600">
                                    {{ date('d M Y', strtotime($so->order_date)) }}
                                </td>

                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-slate-900 block">{{ $so->customer_name }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $so->customer_code }} &bull; {{ $so->customer_type }}</span>
                                </td>

                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-slate-800 block">{{ $so->sales_name }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $so->warehouse_name }}</span>
                                </td>

                                <td class="py-3.5 px-4 text-slate-600 max-w-[170px]">
                                    @if(!empty($so->notes))
                                        <span class="text-[11px] font-medium text-slate-700 block truncate" title="{{ $so->notes }}">
                                            {{ $so->notes }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 font-mono text-xs">-</span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-800">
                                    {{ number_format($so->total_qty) }} pcs
                                </td>

                                <td class="py-3.5 px-4 text-right font-mono font-extrabold text-slate-900">
                                    Rp {{ number_format($so->grand_total, 0, ',', '.') }}
                                </td>

                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold
                                        {{ $so->status === 'DELIVERED' ? 'bg-emerald-100 text-emerald-800' : ($so->status === 'APPROVED' ? 'bg-blue-100 text-blue-800' : ($so->status === 'PENDING_APPROVAL' ? 'bg-amber-100 text-amber-800 border border-amber-300' : ($so->status === 'VOID' ? 'bg-slate-200 text-slate-700 font-mono' : 'bg-rose-100 text-rose-800'))) }}">
                                        {{ $so->status }}
                                    </span>
                                </td>

                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" 
                                                class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all flex items-center gap-1 shadow-xs cursor-pointer"
                                                :class="selectedSo && selectedSo.id === {{ $so->id }} ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-600'">
                                            <span x-text="selectedSo && selectedSo.id === {{ $so->id }} ? 'Aktif' : 'Detail'"></span>
                                            <i data-lucide="chevron-down" class="w-3 h-3 transition-transform" :class="selectedSo && selectedSo.id === {{ $so->id }} ? 'rotate-180 text-white' : ''"></i>
                                        </button>

                                        {{-- Tombol Void Cepat di Baris Tabel jika SO belum lunas/selesai --}}
                                        @can('so.void')
                                        @if(!in_array($so->status, ['COMPLETED', 'VOID']) && (!$so->invoice || ($so->invoice->status === 'UNPAID' && (float)$so->invoice->paid_amount == 0)))
                                            <form method="POST" action="{{ route('sales.void', $so->id) }}"
                                                  data-confirm="Konfirmasi Void / Pembatalan SO"
                                                  data-confirm-text="Apakah Anda yakin ingin melakukan VOID (Pembatalan) pada pesanan {{ $so->doc_number }}?{{ $so->status === 'DELIVERED' ? ' Barang yang sudah terkirim akan dikembalikan ke stok gudang.' : '' }} Limit customer dan kuota order akan dipulihkan."
                                                  data-confirm-type="danger"
                                                  data-confirm-btn="Ya, Void SO"
                                                  data-cancel-btn="Tidak, Kembali"
                                                  data-loading-title="Memproses Void SO"
                                                  data-loading-sub="Membatalkan dokumen Sales Order {{ $so->doc_number }}..."
                                                  data-doctype="VOID"
                                                  class="inline">
                                                @csrf
                                                <button type="submit" 
                                                        title="Batalkan / Void Sales Order"
                                                        class="px-2 py-1 rounded-lg text-xs font-bold bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition-all cursor-pointer flex items-center gap-1">
                                                    <i data-lucide="ban" class="w-3 h-3"></i>
                                                    <span>Void</span>
                                                </button>
                                            </form>
                                        @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                        <tr x-show="filteredOrders.length === 0" x-cloak>
                            <td colspan="9" class="py-8">
                                <x-empty-state title="Tidak Ada Dokumen SO" description="Belum ada Sales Order yang sesuai dengan filter wilayah, tanggal, status, atau pencarian Anda." icon="file-search" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Client-Side Pagination Bar SO -->
            <div class="p-4 border-t border-slate-200 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-500">
                <div class="flex items-center gap-3">
                    <div>
                        Menampilkan <strong class="text-slate-800" x-text="soPaginationStart"></strong> - <strong class="text-slate-800" x-text="soPaginationEnd"></strong> dari <strong class="text-slate-800" x-text="filteredOrders.length"></strong> data SO
                    </div>
                    <span class="text-slate-300">|</span>
                    <label class="flex items-center gap-1.5 text-xs">
                        <span>Baris:</span>
                        <select x-model="soPerPage" @change="soPage = 1; refreshIcons()" class="border border-slate-200 rounded-lg px-2 py-1 bg-white text-xs font-semibold text-slate-700 focus:outline-none cursor-pointer">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="ALL">Semua</option>
                        </select>
                    </label>
                </div>

                <div class="flex items-center gap-1" x-show="totalSoPages > 1">
                    <button type="button"
                            @click="soPage = Math.max(1, soPage - 1); refreshIcons()"
                            :disabled="soPage === 1"
                            class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 disabled:opacity-40 text-xs font-bold flex items-center gap-1 cursor-pointer">
                        <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                        <span>Prev</span>
                    </button>

                    <template x-for="(p, idx) in displayedSoPages" :key="idx">
                        <button type="button"
                                @click="if (p !== '...') { soPage = p; refreshIcons(); }"
                                :disabled="p === '...'"
                                :class="soPage === p ? 'bg-amber-600 text-white font-extrabold border-amber-600 shadow-xs' : (p === '...' ? 'cursor-default border-transparent text-slate-400' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100 font-semibold')"
                                class="min-w-[30px] h-7 px-1.5 rounded-lg border text-xs flex items-center justify-center transition-all cursor-pointer"
                                x-text="p">
                        </button>
                    </template>

                    <button type="button"
                            @click="soPage = Math.min(totalSoPages, soPage + 1); refreshIcons()"
                            :disabled="soPage === totalSoPages"
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
        <div x-show="selectedSo" x-cloak class="scroll-mt-4" id="detailSection">
            <div class="rounded-2xl border-2 border-slate-300 shadow-lg overflow-hidden bg-white mb-8">
                
                <!-- Distinct Dark Header Strip for Detail Panel -->
                <div class="bg-slate-900 text-white px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800">
                    <div class="flex items-center gap-3">
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-extrabold bg-amber-500 text-slate-950 shadow-sm">
                            RINCIAN SO
                        </span>
                        <h4 class="text-base font-extrabold text-white tracking-tight" x-text="selectedSo ? selectedSo.doc_number : ''"></h4>
                        <span class="text-xs text-slate-400">&bull;</span>
                        <span class="text-xs font-bold text-amber-400" x-text="selectedSo ? selectedSo.customer_name : ''"></span>
                    </div>

                    <!-- Workflow Action Buttons in Header Strip -->
                    <div class="flex items-center gap-2">
                        
                        <!-- 1. Tombol Approval Finance -->
                        <template x-if="selectedSo && selectedSo.status === 'PENDING_APPROVAL'">
                            <div class="flex items-center gap-2">
                                <form method="POST" :action="`{{ url('sales/approve') }}/${selectedSo.id}`">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-xs flex items-center gap-1 shadow-sm active:scale-95 cursor-pointer">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                        <span>Setujui SO (Finance)</span>
                                    </button>
                                </form>
                                <form method="POST" :action="`{{ url('sales/reject') }}/${selectedSo.id}`">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-lg text-xs flex items-center gap-1 shadow-sm active:scale-95 cursor-pointer">
                                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                        <span>Tolak SO</span>
                                    </button>
                                </form>
                            </div>
                        </template>

                        <!-- 2. Tombol Sending Good (Gudang) jika sudah APPROVED -->
                        <template x-if="selectedSo && selectedSo.status === 'APPROVED'">
                            <form method="POST" :action="`{{ url('sales/deliver') }}/${selectedSo.id}`"
                                  data-loading-title="Memproses Pengiriman Barang"
                                  data-loading-sub="Menerbitkan Surat Jalan (GD) dan Faktur Piutang (AR)..."
                                  data-doctype="GD">
                                @csrf
                                <button type="submit" class="px-4 py-1.5 bg-cyan-600 hover:bg-cyan-700 text-white font-bold rounded-lg text-xs flex items-center gap-1.5 shadow-sm active:scale-95 animate-pulse cursor-pointer">
                                    <i data-lucide="truck" class="w-4 h-4"></i>
                                    <span>Kirim Barang (Sending Good & GD)</span>
                                </button>
                            </form>
                        </template>

                        <!-- 3. Tombol Cetak Nota jika DELIVERED -->
                        <template x-if="selectedSo && selectedSo.status === 'DELIVERED'">
                            <a :href="`{{ url('sales/print-nota') }}/${selectedSo.id}`" target="_blank"
                               class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-amber-300 font-bold rounded-lg text-xs flex items-center gap-1.5 border border-slate-700">
                                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                <span>Cetak Nota Penjualan</span>
                            </a>
                        </template>

                        <!-- 4. Tombol Void SO (Hadir di PENDING_APPROVAL, APPROVED, hingga DELIVERED selama belum ada pembayaran AR) -->
                        @can('so.void')
                        <template x-if="selectedSo && !['COMPLETED', 'VOID'].includes(selectedSo.status) && (!selectedSo.invoice || (selectedSo.invoice.status === 'UNPAID' && parseFloat(selectedSo.invoice.paid_amount || 0) === 0))">
                            <form method="POST" 
                                  :action="`{{ url('sales/void') }}/${selectedSo.id}`"
                                  data-confirm="Konfirmasi Void / Pembatalan SO"
                                  :data-confirm-text="'Apakah Anda yakin ingin membatalkan (VOID) Sales Order ' + selectedSo.doc_number + '?' + (selectedSo.status === 'DELIVERED' ? ' Barang yang sudah terkirim akan dikembalikan ke gudang.' : '') + ' Limit customer dan kuota order akan dipulihkan.'"
                                  data-confirm-type="danger"
                                  data-confirm-btn="Ya, Void SO Ini"
                                  data-cancel-btn="Tidak, Kembali"
                                  data-loading-title="Memproses Void SO"
                                  data-loading-sub="Membatalkan Sales Order & memulihkan stok/limit..."
                                  data-doctype="VOID">
                                @csrf
                                <button type="submit" class="px-3.5 py-1.5 bg-rose-900/60 hover:bg-rose-700 text-rose-200 hover:text-white font-bold rounded-lg text-xs flex items-center gap-1.5 shadow-sm active:scale-95 border border-rose-600/40 cursor-pointer">
                                    <i data-lucide="ban" class="w-3.5 h-3.5"></i>
                                    <span>Void SO</span>
                                </button>
                            </form>
                        </template>
                        @endcan

                    </div>
                </div>

                <div class="p-6">
                    
                    <!-- WORKFLOW STEPPER CARD -->
                    <div class="mb-6 p-4 rounded-xl bg-slate-50 border border-slate-200">
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs">
                            
                            <!-- Step 1: SO -->
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shrink-0">1</div>
                                <div>
                                    <span class="font-extrabold text-slate-800 block">Sales Order (SO)</span>
                                    <span class="font-mono text-[10px] text-slate-500" x-text="selectedSo ? selectedSo.doc_number : ''"></span>
                                </div>
                            </div>

                            <!-- Step 2: Approval -->
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs shrink-0"
                                     :class="selectedSo && (selectedSo.status === 'APPROVED' || selectedSo.status === 'DELIVERED') ? 'bg-emerald-600 text-white' : (selectedSo && selectedSo.status === 'REJECTED' ? 'bg-rose-600 text-white' : 'bg-amber-500 text-white')">
                                    2
                                </div>
                                <div>
                                    <span class="font-extrabold text-slate-800 block">Finance Approval</span>
                                    <span class="text-[10px] font-bold"
                                          :class="selectedSo && (selectedSo.status === 'APPROVED' || selectedSo.status === 'DELIVERED') ? 'text-emerald-700' : 'text-amber-700'"
                                          x-text="selectedSo ? selectedSo.status : ''"></span>
                                </div>
                            </div>

                            <!-- Step 3: Surat Jalan GD -->
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs shrink-0"
                                     :class="selectedSo && selectedSo.delivery ? 'bg-emerald-600 text-white' : 'bg-slate-300 text-slate-600'">
                                    3
                                </div>
                                <div>
                                    <span class="font-extrabold text-slate-800 block">Surat Jalan (GD)</span>
                                    <span class="font-mono text-[10px] font-bold text-cyan-800"
                                          x-text="selectedSo && selectedSo.delivery ? selectedSo.delivery.doc_number : 'Belum Dikirim'"></span>
                                </div>
                            </div>

                            <!-- Step 4: Piutang AR -->
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs shrink-0"
                                     :class="selectedSo && selectedSo.invoice ? 'bg-emerald-600 text-white' : 'bg-slate-300 text-slate-600'">
                                    4
                                </div>
                                <div>
                                    <span class="font-extrabold text-slate-800 block">Faktur Piutang (AR)</span>
                                    <span class="font-mono text-[10px] font-bold text-rose-800"
                                          x-text="selectedSo && selectedSo.invoice ? selectedSo.invoice.doc_number : 'Belum Terbit'"></span>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Catatan Pesanan (Notes) Banner -->
                    <template x-if="selectedSo && selectedSo.notes">
                        <div class="mb-4 px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl flex items-center gap-2 text-xs">
                            <span class="font-bold text-slate-700">Catatan:</span>
                            <span class="text-slate-600" x-text="selectedSo.notes"></span>
                        </div>
                    </template>

                    <!-- Detail Line Items Table -->
                    <div class="mb-5">
                        <div class="border border-slate-200 rounded-xl overflow-hidden bg-white">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 text-slate-700 font-bold uppercase tracking-wider border-b border-slate-200">
                                    <tr>
                                        <th class="py-2.5 px-4">Produk</th>
                                        <th class="py-2.5 px-4">Tipe</th>
                                        @if($iccidEnabled ?? config('erp.enable_iccid', false))
                                            <th class="py-2.5 px-4">Tracking</th>
                                        @endif
                                        <th class="py-2.5 px-4 text-center">Kuantitas</th>
                                        <th class="py-2.5 px-4 text-right">Harga Jual</th>
                                        <th class="py-2.5 px-4 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium">
                                    <template x-for="(item, idx) in (selectedSo ? selectedSo.items : [])" :key="idx">
                                        <tr class="hover:bg-slate-50/70">
                                            <td class="py-3 px-4 font-bold text-slate-900">
                                                <span class="font-mono text-[10px] text-slate-400 block" x-text="item.sku"></span>
                                                <span x-text="item.product_name"></span>
                                            </td>
                                            <td class="py-3 px-4">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold"
                                                      :class="item.product_type === 'SA' ? 'bg-rose-100 text-rose-700' : 'bg-indigo-100 text-indigo-700'"
                                                      x-text="item.product_type">
                                                </span>
                                            </td>
                                            @if($iccidEnabled ?? config('erp.enable_iccid', false))
                                                <td class="py-3 px-4 font-mono text-[11px] font-bold"
                                                    :class="item.tracking_method === 'ICCID' ? 'text-purple-700' : 'text-blue-700'"
                                                    x-text="item.tracking_method">
                                                </td>
                                            @endif
                                            <td class="py-3 px-4 text-center font-mono font-bold text-slate-800">
                                                <span x-text="item.quantity.toLocaleString('id-ID')"></span> pcs
                                            </td>
                                            <td class="py-3 px-4 text-right font-mono text-slate-600">
                                                Rp <span x-text="Number(item.unit_price).toLocaleString('id-ID')"></span>
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

                    @if($iccidEnabled ?? config('erp.enable_iccid', false))
                        <!-- Accordion Unit ICCID jika ada -->
                        <template x-if="selectedSo && selectedSo.iccids && selectedSo.iccids.length > 0">
                            <div class="mt-4 p-4 rounded-xl bg-purple-50/70 border border-purple-200/80">
                                <span class="text-xs font-bold text-purple-900 block mb-2">
                                    Serial ICCID Terkait Transaksi Ini (<span x-text="selectedSo.iccids.length"></span> unit):
                                </span>
                                <div class="max-h-32 overflow-y-auto grid grid-cols-2 sm:grid-cols-4 gap-1.5 bg-white p-2.5 rounded-lg border border-purple-100">
                                    <template x-for="iccid in selectedSo.iccids" :key="iccid">
                                        <span class="px-2 py-1 rounded bg-purple-50 text-purple-800 font-mono text-[11px] border border-purple-200 truncate" x-text="iccid"></span>
                                    </template>
                                </div>
                            </div>
                        </template>
                    @endif

                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- MODAL SALES RETUR                              -->
        <!-- ============================================== -->
        {{-- MODAL SALES RETUR (DI-HIDE SESUAI INSTRUKSI KLIEN) --}}
        {{--
        <div x-show="returnModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                        <i data-lucide="undo-2" class="w-4 h-4 text-rose-600"></i>
                        <span>Formulir Sales Retur (Pengembalian Barang)</span>
                    </h3>
                    <button @click="returnModalOpen = false" class="text-slate-400 hover:text-slate-700">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('sales.return') }}">
                    @csrf
                    <input type="hidden" name="sales_order_id" :value="selectedSo ? selectedSo.id : ''">

                    <div class="space-y-4 mb-5">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                            <div class="flex justify-between mb-1">
                                <span class="text-slate-500">No. SO:</span>
                                <span class="font-mono font-bold text-slate-800" x-text="selectedSo ? selectedSo.doc_number : ''"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Customer:</span>
                                <span class="font-bold text-slate-800" x-text="selectedSo ? selectedSo.customer_name : ''"></span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Produk yang Diretur</label>
                            <template x-for="(item, idx) in (selectedSo ? selectedSo.items : [])" :key="idx">
                                <div class="flex items-center justify-between p-2 rounded-lg border border-slate-200 mb-2 bg-white">
                                    <div>
                                        <span class="font-bold text-xs text-slate-800 block" x-text="item.product_name"></span>
                                        <span class="text-[10px] text-slate-400 font-mono">Dipesan: <span x-text="item.quantity"></span> pcs</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <input type="hidden" :name="`items[${idx}][product_id]`" :value="item.product_id">
                                        <label class="text-[10px] font-bold text-slate-500">Qty Retur:</label>
                                        <input type="number" 
                                               :name="`items[${idx}][quantity]`" 
                                               value="1" 
                                               min="0" 
                                               :max="item.quantity" 
                                               class="w-16 text-center text-xs font-bold border border-slate-200 rounded px-1.5 py-1">
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Pengembalian (Notes)</label>
                            <textarea name="notes" rows="2" placeholder="Contoh: Fisik rusak / cacat produksi" class="w-full text-xs border border-slate-200 rounded-lg p-2 focus:outline-none"></textarea>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="returnModalOpen = false" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl text-xs">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Konfirmasi Retur</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        --}}

    </div>

    @push('scripts')
    <script>
        function salesApp() {
            const rawOrders = @json($orders);
            const now = new Date();
            const pad = n => String(n).padStart(2, '0');
            const todayStr = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;

            return {
                createSoOpen: false,
                returnModalOpen: false,
                isSubmitting: false,
                iccidEnabled: {{ ($iccidEnabled ?? config('erp.enable_iccid', false)) ? 'true' : 'false' }},
                selectedSo: null,
                selectedWarehouseId: '',
                selectedWarehouseName: '',
                selectedSalesId: '',
                selectedCustomerId: '',
                creditInfo: null,

                // Data Records
                allOrders: rawOrders,
                allClusters: @json($clusters ?? []),
                allTaps: @json($taps ?? []),

                // Filter Client-Side: Tanggal, Wilayah, Status & Search (Default AUTO HARI INI)
                startDate: todayStr,
                endDate: todayStr,
                selectedArea: '',
                selectedCluster: '',
                selectedTap: '',
                searchSo: '',
                statusFilter: '',
                showVoid: false,

                // Pagination SO
                soPage: 1,
                soPerPage: '10',

                items: [
                    {
                        product_id: '',
                        searchText: '',
                        dropdownOpen: false,
                        tracking_method: '',
                        unit_price: 0,
                        quantity: 1,
                        iccid_mode: 'SELECT',
                        selected_iccids: [],
                        iccid_text: ''
                    }
                ],

                salesList: @json($salesList),
                warehouses: @json($warehouses ?? []),
                products: @json($products),
                stockBalances: @json($stockBalances),
                availableIccids: @json($availableIccids),

                get voidCount() {
                    return this.allOrders.filter(so => so.status === 'VOID').length;
                },

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
                    this.resetPage();
                    this.syncSelection();
                    this.refreshIcons();
                },

                onClusterChange() {
                    this.selectedTap = '';
                    this.resetPage();
                    this.syncSelection();
                    this.refreshIcons();
                },

                onTapChange() {
                    this.resetPage();
                    this.syncSelection();
                    this.refreshIcons();
                },

                onSearchChange() {
                    this.resetPage();
                    this.syncSelection();
                    this.refreshIcons();
                },

                hasActiveFilter() {
                    return Boolean(this.selectedArea || this.selectedCluster || this.selectedTap || this.searchSo || this.startDate || this.endDate || this.statusFilter || this.showVoid);
                },

                resetFilter() {
                    this.selectedArea = '';
                    this.selectedCluster = '';
                    this.selectedTap = '';
                    this.searchSo = '';
                    this.statusFilter = '';
                    this.showVoid = false;
                    this.setClientDatePreset('all');
                },

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
                    this.resetPage();
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

                resetPage() {
                    this.soPage = 1;
                },

                refreshIcons() {
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined' && lucide.createIcons) {
                            lucide.createIcons();
                        }
                    });
                },

                isOrderVisible(so) {
                    if (!this.showVoid && so.status === 'VOID') return false;

                    if (this.selectedArea && String(so.area_id) !== String(this.selectedArea)) return false;
                    if (this.selectedCluster && String(so.cluster_id) !== String(this.selectedCluster)) return false;
                    if (this.selectedTap && String(so.tap_id) !== String(this.selectedTap)) return false;

                    if (this.statusFilter && so.status !== this.statusFilter) return false;

                    if (this.startDate) {
                        const soDate = so.order_date ? String(so.order_date).substring(0, 10) : '';
                        if (soDate && soDate < this.startDate) return false;
                    }
                    if (this.endDate) {
                        const soDate = so.order_date ? String(so.order_date).substring(0, 10) : '';
                        if (soDate && soDate > this.endDate) return false;
                    }

                    if (this.searchSo) {
                        const q = this.searchSo.toLowerCase().trim();
                        const gdDoc = so.delivery ? (so.delivery.doc_number || '') : '';
                        const match = (so.doc_number && so.doc_number.toLowerCase().includes(q)) ||
                                      (gdDoc && gdDoc.toLowerCase().includes(q)) ||
                                      (so.customer_name && so.customer_name.toLowerCase().includes(q)) ||
                                      (so.customer_code && so.customer_code.toLowerCase().includes(q)) ||
                                      (so.sales_name && so.sales_name.toLowerCase().includes(q)) ||
                                      (so.warehouse_name && so.warehouse_name.toLowerCase().includes(q)) ||
                                      (so.tap_name && so.tap_name.toLowerCase().includes(q)) ||
                                      (so.cluster_name && so.cluster_name.toLowerCase().includes(q)) ||
                                      (so.notes && so.notes.toLowerCase().includes(q));
                        if (!match) return false;
                    }
                    return true;
                },

                get filteredOrders() {
                    return this.allOrders.filter(so => this.isOrderVisible(so));
                },

                get totalSoPages() {
                    if (this.soPerPage === 'ALL') return 1;
                    const perPage = parseInt(this.soPerPage, 10) || 10;
                    return Math.max(1, Math.ceil(this.filteredOrders.length / perPage));
                },

                get paginatedOrderIds() {
                    if (this.soPerPage === 'ALL') return this.filteredOrders.map(so => so.id);
                    const perPage = parseInt(this.soPerPage, 10) || 10;
                    const start = (this.soPage - 1) * perPage;
                    return this.filteredOrders.slice(start, start + perPage).map(so => so.id);
                },

                get soPaginationStart() {
                    if (this.filteredOrders.length === 0) return 0;
                    if (this.soPerPage === 'ALL') return 1;
                    const perPage = parseInt(this.soPerPage, 10) || 10;
                    return (this.soPage - 1) * perPage + 1;
                },

                get soPaginationEnd() {
                    if (this.filteredOrders.length === 0) return 0;
                    if (this.soPerPage === 'ALL') return this.filteredOrders.length;
                    const perPage = parseInt(this.soPerPage, 10) || 10;
                    return Math.min(this.soPage * perPage, this.filteredOrders.length);
                },

                get displayedSoPages() {
                    const current = this.soPage;
                    const total = this.totalSoPages;
                    if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
                    if (current <= 4) return [1, 2, 3, 4, 5, '...', total];
                    if (current >= total - 3) return [1, '...', total - 4, total - 3, total - 2, total - 1, total];
                    return [1, '...', current - 1, current, current + 1, '...', total];
                },

                syncSelection() {
                    this.$nextTick(() => {
                        if (this.selectedSo && !this.isOrderVisible(this.selectedSo)) {
                            this.selectedSo = this.filteredOrders.length > 0 ? this.filteredOrders[0] : null;
                        } else if (!this.selectedSo && this.filteredOrders.length > 0) {
                            this.selectedSo = this.filteredOrders[0];
                        }
                    });
                },

                selectSoById(soId) {
                    const found = this.allOrders.find(o => o.id === soId);
                    if (found) {
                        this.selectedSo = found;
                        this.$nextTick(() => {
                            this.refreshIcons();
                            const el = document.getElementById('detailSection');
                            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        });
                    }
                },

                get selectedWarehouseInfo() {
                    if (!this.selectedWarehouseId) return '';
                    const wh = this.warehouses.find(w => w.id == this.selectedWarehouseId);
                    if (!wh) return '';
                    return `Tipe: [${wh.type}] • TAP: ${wh.tap_name || '-'} • Cluster: ${wh.cluster_name || '-'}`;
                },

                get grandTotal() {
                    return this.items.reduce((acc, row) => acc + this.getRowSubtotal(row), 0);
                },

                get totalQty() {
                    return this.items.reduce((acc, row) => {
                        if (this.iccidEnabled && row.tracking_method === 'ICCID') {
                            return acc + (this.getIccidCount(row) || 0);
                        }
                        return acc + (parseInt(row.quantity) || 0);
                    }, 0);
                },

                initData() {
                    // Auto Hari Ini
                    this.setClientDatePreset('today');

                    if (this.filteredOrders.length > 0) {
                        this.selectedSo = this.filteredOrders[0];
                    } else if (this.allOrders.length > 0) {
                        this.selectedSo = this.allOrders[0];
                    }

                    this.$watch('searchSo', () => {
                        this.resetPage();
                        this.syncSelection();
                        this.refreshIcons();
                    });

                    this.refreshIcons();
                },

                selectSo(so) {
                    this.selectedSo = so;
                    this.$nextTick(() => {
                        this.refreshIcons();
                        const el = document.getElementById('detailSection');
                        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    });
                },

                openReturnModal() {
                    this.returnModalOpen = true;
                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                },

                addItemRow() {
                    this.items.push({
                        product_id: '',
                        searchText: '',
                        dropdownOpen: false,
                        tracking_method: '',
                        unit_price: 0,
                        quantity: 1,
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
                    this.items.forEach(row => {
                        row.product_id = '';
                        row.searchText = '';
                        row.dropdownOpen = false;
                        row.tracking_method = '';
                        row.unit_price = 0;
                        row.selected_iccids = [];
                        row.iccid_text = '';
                    });
                },

                onSalesChange() {
                    // Ketika sales dipilih, jika belum memilih gudang atau ingin default, 
                    // set gudang kanvas milik sales tersebut jika ada di daftar wewenang user,
                    // tapi user TETAP DAPAT mengganti/memilih gudang lain dari dropdown
                    if (this.selectedSalesId && !this.selectedWarehouseId) {
                        const sales = this.salesList.find(s => s.id == this.selectedSalesId);
                        if (sales && sales.warehouse_id) {
                            const whExists = this.warehouses.some(w => w.id == sales.warehouse_id);
                            if (whExists) {
                                this.selectedWarehouseId = sales.warehouse_id;
                                this.onWarehouseChange();
                            }
                        }
                    }

                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                },

                async onCustomerChange() {
                    if (!this.selectedCustomerId) {
                        this.creditInfo = null;
                        return;
                    }
                    try {
                        const res = await fetch(`{{ url('sales/customer-credit') }}/${this.selectedCustomerId}`);
                        if (res.ok) {
                            this.creditInfo = await res.json();
                            this.$nextTick(() => {
                                lucide.createIcons();
                            });
                        }
                    } catch (e) {
                        console.error('Failed to load customer credit info:', e);
                    }
                },

                getAvailableProductsInWarehouse() {
                    if (!this.selectedWarehouseId) return [];
                    const warehouseBalances = this.stockBalances.filter(b => b.warehouse_id == this.selectedWarehouseId && b.quantity > 0);
                    const prodIds = warehouseBalances.map(b => b.product_id);
                    return this.products.filter(p => prodIds.includes(p.id));
                },

                filterProductsForRow(searchText) {
                    const list = this.getAvailableProductsInWarehouse();
                    if (!searchText || !searchText.trim()) return list;
                    const q = searchText.toLowerCase().trim();
                    return list.filter(p => 
                        (p.name && p.name.toLowerCase().includes(q)) || 
                        (p.sku && p.sku.toLowerCase().includes(q)) ||
                        (p.type && p.type.toLowerCase().includes(q))
                    );
                },

                selectProductForRow(index, prod) {
                    const row = this.items[index];
                    row.product_id = prod.id;
                    row.searchText = `[${prod.type}] ${prod.name} (Stok: ${this.getAvailableStock(prod.id)})`;
                    row.dropdownOpen = false;
                    row.tracking_method = prod.tracking_method;
                    row.unit_price = parseFloat(prod.selling_price) || 0;
                    row.selected_iccids = [];
                    row.iccid_text = '';
                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                },

                clearProductRow(index) {
                    const row = this.items[index];
                    row.product_id = '';
                    row.searchText = '';
                    row.dropdownOpen = false;
                    row.tracking_method = '';
                    row.unit_price = 0;
                    row.selected_iccids = [];
                    row.iccid_text = '';
                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                },

                getAvailableStock(productId) {
                    if (!this.selectedWarehouseId || !productId) return 0;
                    const found = this.stockBalances.find(b => b.warehouse_id == this.selectedWarehouseId && b.product_id == productId);
                    return found ? parseInt(found.quantity) : 0;
                },

                getIccidsForProduct(productId) {
                    if (!this.selectedWarehouseId || !productId) return [];
                    return this.availableIccids.filter(u => u.warehouse_id == this.selectedWarehouseId && u.product_id == productId);
                },

                selectAllIccids(index) {
                    const row = this.items[index];
                    const list = this.getIccidsForProduct(row.product_id);
                    row.selected_iccids = list.map(u => u.iccid_number);
                },

                getIccidCount(row) {
                    if (row.iccid_mode === 'SELECT') {
                        return row.selected_iccids ? row.selected_iccids.length : 0;
                    } else if (row.iccid_mode === 'PASTE') {
                        return row.iccid_text ? row.iccid_text.split(/[\r\n,]+/).map(s => s.trim()).filter(Boolean).length : 0;
                    }
                    return 0;
                },

                getRowSubtotal(row) {
                    const price = parseFloat(row.unit_price) || 0;
                    const qty = (this.iccidEnabled && row.tracking_method === 'ICCID') ? this.getIccidCount(row) : (parseInt(row.quantity) || 0);
                    return price * qty;
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
