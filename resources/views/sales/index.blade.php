<x-app-layout>
    <x-slot name="title">Sales Order & Goods Delivery (SO - GD - AR)</x-slot>
    <x-slot name="headerTitle">Sales Order & Pengiriman (SO &rarr; GD &rarr; AR)</x-slot>
    <x-slot name="badge">SO &bull; GD &bull; AR &bull; SR</x-slot>

    <div x-data="salesApp()" x-init="initData()">
        
        <!-- Flash Alert Feedback -->
        <x-alert />

        <!-- Top Action Bar -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Pesanan Penjualan & Pengiriman Barang</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">PT WAHANA PUTRA YUDHA</span>
                </h2>
                <p class="text-xs text-slate-500">Operasional Resmi PT Wahana Putra Yudha: Pembuatan SO &rarr; Approval Finance &rarr; Sending Good (GD & AR) &rarr; Sales Retur</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('reports.index', ['type' => 'SALES']) }}" 
                   class="px-3.5 py-2.5 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-xs transition-all shrink-0">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-rose-600"></i>
                    <span>Rekap Penjualan (Excel/PDF)</span>
                </a>
                <button type="button" 
                        @click="createSoOpen = !createSoOpen" 
                        class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-sm transition-all active:scale-95 shrink-0">
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
                    <span>Formulir Sales Order (SO) Baru</span>
                </h3>
                <button @click="createSoOpen = false" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg">
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
                        <label class="block text-xs font-bold text-slate-700 mb-1">Gudang Sumber Stok</label>
                        {{-- Input Hidden untuk warehouse_id yang otomatis diisi dari sales --}}
                        <input type="hidden" name="warehouse_id" :value="selectedWarehouseId">
                        
                        <div class="w-full text-xs font-bold py-2 px-3 rounded-xl border flex items-center justify-between"
                             :class="selectedWarehouseName ? 'bg-amber-50/70 border-amber-200 text-slate-800' : 'bg-slate-100 border-slate-200 text-slate-400'">
                            <div class="flex items-center gap-1.5 truncate">
                                <i data-lucide="warehouse" class="w-3.5 h-3.5 shrink-0" :class="selectedWarehouseName ? 'text-amber-600' : 'text-slate-400'"></i>
                                <span x-text="selectedWarehouseName || 'Otomatis mengikuti Sales'"></span>
                            </div>
                            <span class="text-[9px] px-1.5 py-0.5 rounded font-mono font-bold uppercase shrink-0"
                                  :class="selectedWarehouseName ? 'bg-amber-200 text-amber-900' : 'bg-slate-200 text-slate-500'">
                                KANVAS
                            </span>
                        </div>
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
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">
                                            Produk <span class="text-rose-500">*</span>
                                            <span class="text-slate-400 font-normal">(Ketik SKU / Nama)</span>
                                        </label>
                                        <input type="hidden" :name="`items[${index}][product_id]`" :value="row.product_id" required>
                                        
                                        <div class="relative flex items-center">
                                            <input type="text"
                                                   x-model="row.searchText"
                                                   @focus="row.dropdownOpen = true"
                                                   @input="row.dropdownOpen = true"
                                                   :disabled="!selectedWarehouseId"
                                                   :placeholder="!selectedWarehouseId ? 'Pilih Gudang Sumber dulu...' : 'Ketik cari SKU atau nama produk...'"
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

        <!-- ============================================== -->
        <!-- BAGIAN 1: HEADER TABLE (DAFTAR DOKUMEN SO)     -->
        <!-- ============================================== -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-6">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                    <span>Daftar Sales Order (SO)</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-slate-100 text-slate-700">
                        {{ count($orders) }} Pesanan
                    </span>
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">No. Dokumen SO</th>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Outlet / Customer</th>
                            <th class="py-3 px-4">Sales & Gudang</th>
                            <th class="py-3 px-4">Catatan</th>
                            <th class="py-3 px-4 text-center">Total Qty</th>
                            <th class="py-3 px-4 text-right">Nilai Tagihan</th>
                            <th class="py-3 px-4 text-center">Status Transaksi</th>
                            <th class="py-3 px-4 text-center w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($orders as $so)
                            <tr @click="selectSo({{ json_encode($so) }})"
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
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-400">
                                    Belum ada transaksi Sales Order. Klik "+ Buat SO Baru" untuk memulai.
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
                        <div class="mb-5 px-4 py-3 bg-amber-50/70 border border-amber-200/80 rounded-xl flex items-start gap-3">
                            <i data-lucide="file-text" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5"></i>
                            <div>
                                <span class="text-[11px] font-bold text-amber-900 block">Catatan / Keterangan Pesanan (Notes):</span>
                                <span class="text-xs text-slate-700 font-medium" x-text="selectedSo.notes"></span>
                            </div>
                        </div>
                    </template>

                    <!-- Detail Line Items Table -->
                    <div class="mb-5">
                        <h5 class="text-xs font-bold text-slate-700 uppercase mb-2">Item Barang yang Dipesan:</h5>
                        <div class="border border-slate-200 rounded-xl overflow-hidden bg-white">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider border-b border-slate-200">
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
                products: @json($products),
                stockBalances: @json($stockBalances),
                availableIccids: @json($availableIccids),

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
                    const firstSo = @json($orders->first());
                    if (firstSo) this.selectedSo = firstSo;

                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                },

                selectSo(so) {
                    this.selectedSo = so;
                    this.$nextTick(() => {
                        lucide.createIcons();
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
                    if (!this.selectedSalesId) {
                        this.selectedWarehouseId = '';
                        this.selectedWarehouseName = '';
                        this.onWarehouseChange();
                        return;
                    }

                    const sales = this.salesList.find(s => s.id == this.selectedSalesId);
                    if (sales && sales.warehouse_id) {
                        this.selectedWarehouseId = sales.warehouse_id;
                        this.selectedWarehouseName = sales.warehouse_name;
                    } else {
                        this.selectedWarehouseId = '';
                        this.selectedWarehouseName = '';
                    }

                    // Reset item pilihan agar stok menyesuaikan gudang sales yang baru
                    this.onWarehouseChange();
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
