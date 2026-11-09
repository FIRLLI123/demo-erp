<x-app-layout>
    <x-slot name="title">Inventori & Saldo Stok per Gudang</x-slot>
    <x-slot name="headerTitle">Inventori, Stok per Gudang & Kartu Mutasi</x-slot>
    <x-slot name="badge">Stok & Kartu Mutasi</x-slot>

    <div x-data="inventoryApp()" x-init="initData()">

        <!-- Top Header & Quick Action -->
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Monitoring Stok Fisik & Kartu Mutasi</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">PT WAHANA PUTRA YUDHA</span>
                </h2>
                <p class="text-xs text-slate-500">Laporan posisi saldo stok resmi PT Wahana Putra Yudha di seluruh gudang (MAIN, Persediaan & Kanvas Sales)</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('stock-movement.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 shadow-xs transition-all">
                    <i data-lucide="arrow-left-right" class="w-4 h-4 text-cyan-600"></i>
                    <span>Transfer Antar Gudang (SM)</span>
                </a>
                <a href="{{ route('inventory.repack') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 shadow-xs transition-all">
                    <i data-lucide="repeat" class="w-4 h-4 text-amber-600"></i>
                    <span>Repack 0K (RO &bull; RI)</span>
                </a>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- EXECUTIVE SUMMARY CARDS (RINGKASAN EKSEKUTIF) -->
        <!-- ============================================== -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <!-- 1. Stok Gudang Utama (MAIN) -->
            <div @click="setWarehouseTypeFilter('MAIN')" 
                 class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs cursor-pointer hover:border-slate-400 hover:shadow-md transition-all"
                 :class="selectedWhType === 'MAIN' ? 'ring-2 ring-slate-900 border-slate-900' : ''">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Gudang Utama (MAIN)</span>
                    <span class="w-2.5 h-2.5 rounded-full bg-slate-900"></span>
                </div>
                <div class="text-2xl font-extrabold font-mono text-slate-900">
                    <span x-text="kpiMainStock.toLocaleString('id-ID')"></span>
                    <span class="text-xs font-normal text-slate-400">unit</span>
                </div>
                <span class="text-[10px] text-slate-400 block mt-1">Stok induk hasil penerimaan PO</span>
            </div>

            <!-- 2. Stok Gudang Persediaan (INV) -->
            <div @click="setWarehouseTypeFilter('INVENTORY')" 
                 class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs cursor-pointer hover:border-cyan-400 hover:shadow-md transition-all"
                 :class="selectedWhType === 'INVENTORY' ? 'ring-2 ring-cyan-600 border-cyan-600' : ''">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] font-bold text-cyan-700 uppercase tracking-wider">Gudang Persediaan (INV)</span>
                    <span class="w-2.5 h-2.5 rounded-full bg-cyan-600"></span>
                </div>
                <div class="text-2xl font-extrabold font-mono text-cyan-950">
                    <span x-text="kpiInvStock.toLocaleString('id-ID')"></span>
                    <span class="text-xs font-normal text-slate-400">unit</span>
                </div>
                <span class="text-[10px] text-slate-400 block mt-1">Buffer stok persiapan distribusi</span>
            </div>

            <!-- 3. Stok Kanvas Sales (SALES) -->
            <div @click="setWarehouseTypeFilter('SALES')" 
                 class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs cursor-pointer hover:border-rose-400 hover:shadow-md transition-all"
                 :class="selectedWhType === 'SALES' ? 'ring-2 ring-rose-600 border-rose-600' : ''">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] font-bold text-rose-700 uppercase tracking-wider">Kanvas Sales (Mobile)</span>
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-600"></span>
                </div>
                <div class="text-2xl font-extrabold font-mono text-rose-950">
                    <span x-text="kpiSalesStock.toLocaleString('id-ID')"></span>
                    <span class="text-xs font-normal text-slate-400">unit</span>
                </div>
                <span class="text-[10px] text-slate-400 block mt-1">Stok bergerak di motor sales</span>
            </div>

            <!-- 4. Bahan Repack & Saldo Bulk -->
            <div @click="setProductTypeFilter('RAW_BULK')" 
                 class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs cursor-pointer hover:border-amber-400 hover:shadow-md transition-all"
                 :class="selectedProductType === 'RAW_BULK' ? 'ring-2 ring-amber-600 border-amber-600' : ''">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] font-bold text-amber-700 uppercase tracking-wider">Bahan Baku 0K & Saldo</span>
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                </div>
                <div class="text-2xl font-extrabold font-mono text-amber-950">
                    <span x-text="kpiRawStock.toLocaleString('id-ID')"></span>
                    <span class="text-xs font-normal text-slate-400">0K / Saldo</span>
                </div>
                <span class="text-[10px] text-slate-400 block mt-1">Komponen untuk proses Repack RO &bull; RI</span>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- TABBED CONTAINER                               -->
        <!-- ============================================== -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-8">
            
            <!-- Navigation Tabs Header -->
            <div class="border-b border-slate-200/80 px-6 pt-3 flex flex-wrap items-center justify-between gap-4 bg-slate-50/50">
                <div class="flex items-center gap-6 text-xs font-bold">
                    <button type="button" 
                            @click="activeTab = 'balances'" 
                            class="pb-3 border-b-2 flex items-center gap-1.5 transition-all"
                            :class="activeTab === 'balances' ? 'text-rose-600 border-rose-600 font-extrabold' : 'text-slate-500 border-transparent hover:text-slate-800'">
                        <i data-lucide="boxes" class="w-4 h-4"></i>
                        <span>1. Saldo Stok per Gudang</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-200" x-text="filteredBalances.length"></span>
                    </button>

                    <button type="button" 
                            @click="activeTab = 'mutations'" 
                            class="pb-3 border-b-2 flex items-center gap-1.5 transition-all"
                            :class="activeTab === 'mutations' ? 'text-rose-600 border-rose-600 font-extrabold' : 'text-slate-500 border-transparent hover:text-slate-800'">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                        <span>2. Buku Kartu Stok (In - Out)</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-200" x-text="filteredMutations.length"></span>
                    </button>

                    <button type="button" 
                            @click="activeTab = 'iccids'" 
                            class="pb-3 border-b-2 flex items-center gap-1.5 transition-all"
                            :class="activeTab === 'iccids' ? 'text-rose-600 border-rose-600 font-extrabold' : 'text-slate-500 border-transparent hover:text-slate-800'">
                        <i data-lucide="scan-barcode" class="w-4 h-4"></i>
                        <span>3. Tracking Serial ICCID</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-200" x-text="filteredIccids.length"></span>
                    </button>

                    <button type="button" 
                            @click="activeTab = 'movements'" 
                            class="pb-3 border-b-2 flex items-center gap-1.5 transition-all"
                            :class="activeTab === 'movements' ? 'text-rose-600 border-rose-600 font-extrabold' : 'text-slate-500 border-transparent hover:text-slate-800'">
                        <i data-lucide="arrow-left-right" class="w-4 h-4"></i>
                        <span>4. Dokumen Mutasi (SM)</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-200">{{ count($movements) }}</span>
                    </button>
                </div>

                <div class="pb-2">
                    <button type="button" @click="resetFilters()" class="text-[11px] font-bold text-slate-400 hover:text-rose-600 flex items-center gap-1 transition-colors">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        <span>Reset Semua Filter</span>
                    </button>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- TAB 1: SALDO STOK PER GUDANG                   -->
            <!-- ============================================== -->
            <div x-show="activeTab === 'balances'" class="p-6">
                
                <!-- MULTI-LEVEL FILTER BAR -->
                <div class="mb-5 p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3.5">
                    
                    <!-- Baris 1: Filter Tipe Gudang (Pills) & Search -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                        <!-- Tipe Gudang Pills -->
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-xs font-bold text-slate-500 mr-1 flex items-center gap-1">
                                <i data-lucide="warehouse" class="w-3.5 h-3.5 text-slate-400"></i> Tipe Gudang:
                            </span>
                            <button type="button" 
                                    @click="selectedWhType = 'ALL'" 
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all"
                                    :class="selectedWhType === 'ALL' ? 'bg-slate-900 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-200 border border-slate-200'">
                                Semua Gudang
                            </button>
                            <button type="button" 
                                    @click="selectedWhType = 'MAIN'" 
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5"
                                    :class="selectedWhType === 'MAIN' ? 'bg-slate-900 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'">
                                <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                Gudang Utama (MAIN)
                            </button>
                            <button type="button" 
                                    @click="selectedWhType = 'INVENTORY'" 
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5"
                                    :class="selectedWhType === 'INVENTORY' ? 'bg-cyan-700 text-white shadow-xs' : 'bg-white text-cyan-800 hover:bg-cyan-50 border border-cyan-200'">
                                <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                                Gudang Persediaan (INV)
                            </button>
                            <button type="button" 
                                    @click="selectedWhType = 'SALES'" 
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5"
                                    :class="selectedWhType === 'SALES' ? 'bg-rose-600 text-white shadow-xs' : 'bg-white text-rose-800 hover:bg-rose-50 border border-rose-200'">
                                <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                                Kanvas Sales (SALES)
                            </button>
                        </div>

                        <!-- Live Search Input -->
                        <div class="relative w-full lg:w-72">
                            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                            <input type="text" 
                                   x-model="searchQuery" 
                                   placeholder="Cari barang, SKU, atau nama gudang..." 
                                   class="w-full text-xs font-medium pl-9 pr-3 py-2 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                            <button x-show="searchQuery" @click="searchQuery = ''" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 text-xs">&times;</button>
                        </div>
                    </div>

                    <!-- Baris 2: Filter Kategori Produk & Dropdown Gudang Spesifik -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 pt-3 border-t border-slate-200/70">
                        <!-- Kategori Produk Pills -->
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-xs font-bold text-slate-500 mr-1 flex items-center gap-1">
                                <i data-lucide="tag" class="w-3.5 h-3.5 text-slate-400"></i> Kategori Produk:
                            </span>
                            <button type="button" 
                                    @click="selectedProductType = 'ALL'" 
                                    class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all"
                                    :class="selectedProductType === 'ALL' ? 'bg-rose-600 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100'">
                                Semua Kategori
                            </button>
                            <button type="button" 
                                    @click="selectedProductType = 'SA'" 
                                    class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all"
                                    :class="selectedProductType === 'SA' ? 'bg-purple-600 text-white' : 'bg-white text-purple-700 border border-purple-200 hover:bg-purple-50'">
                                Kartu Perdana (SA)
                            </button>
                            <button type="button" 
                                    @click="selectedProductType = 'VOUCHER'" 
                                    class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all"
                                    :class="selectedProductType === 'VOUCHER' ? 'bg-indigo-600 text-white' : 'bg-white text-indigo-700 border border-indigo-200 hover:bg-indigo-50'">
                                Voucher Fisik
                            </button>
                            <button type="button" 
                                    @click="selectedProductType = 'MODEM'" 
                                    class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all"
                                    :class="selectedProductType === 'MODEM' ? 'bg-cyan-600 text-white' : 'bg-white text-cyan-700 border border-cyan-200 hover:bg-cyan-50'">
                                Modem Orbit
                            </button>
                            <button type="button" 
                                    @click="selectedProductType = 'RAW_BULK'" 
                                    class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all"
                                    :class="selectedProductType === 'RAW_BULK' ? 'bg-amber-600 text-white' : 'bg-white text-amber-700 border border-amber-200 hover:bg-amber-50'">
                                Bahan 0K & NGRS
                            </button>
                        </div>

                        <!-- Dropdown Spesifik Gudang & Toggle Sembunyikan Stok 0 -->
                        <div class="flex items-center gap-3">
                            <select x-model="selectedSpecificWh" 
                                    class="text-xs font-bold text-slate-800 bg-white border border-slate-200 rounded-xl px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                                <option value="ALL">-- Semua Gudang Spesifik --</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">
                                        [{{ $wh->type }}] {{ $wh->name }}
                                    </option>
                                @endforeach
                            </select>

                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-600 cursor-pointer select-none shrink-0">
                                <input type="checkbox" x-model="hideZeroStock" class="rounded text-rose-600 focus:ring-rose-500 w-3.5 h-3.5">
                                <span>Sembunyikan Stok 0</span>
                            </label>
                        </div>
                    </div>

                </div>

                <!-- TABEL DATA SALDO STOK -->
                <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-xs">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">Gudang & Lokasi TAP</th>
                                <th class="py-3 px-4 text-center">Tipe Gudang</th>
                                <th class="py-3 px-4">Kategori</th>
                                <th class="py-3 px-4">SKU Produk</th>
                                <th class="py-3 px-4">Nama Produk</th>
                                <th class="py-3 px-4 text-center">Tracking</th>
                                <th class="py-3 px-4 text-right">Stok Fisik Tersedia</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            <template x-for="item in filteredBalances" :key="item.id">
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-3 px-4">
                                        <span class="font-bold text-slate-900 block" x-text="item.warehouse_name"></span>
                                        <span class="text-[11px] text-slate-400 block" x-text="'Area TAP: ' + item.tap_name"></span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <template x-if="item.warehouse_type === 'MAIN'">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-extrabold bg-slate-900 text-white">
                                                MAIN TAP
                                            </span>
                                        </template>
                                        <template x-if="item.warehouse_type === 'INVENTORY'">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-extrabold bg-cyan-700 text-white">
                                                INVENTORY
                                            </span>
                                        </template>
                                        <template x-if="item.warehouse_type === 'SALES'">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-extrabold bg-rose-600 text-white">
                                                SALES MOTOR
                                            </span>
                                        </template>
                                    </td>
                                    <td class="py-3 px-4">
                                        <template x-if="item.sku.startsWith('RAW-') || item.sku.startsWith('BULK-')">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                BAHAN REPACK
                                            </span>
                                        </template>
                                        <template x-if="!item.sku.startsWith('RAW-') && !item.sku.startsWith('BULK-')">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold"
                                                  :class="item.product_type === 'SA' ? 'bg-purple-100 text-purple-700 border border-purple-200' : (item.product_type === 'VOUCHER' ? 'bg-indigo-100 text-indigo-700 border border-indigo-200' : 'bg-cyan-100 text-cyan-700 border border-cyan-200')"
                                                  x-text="item.product_type">
                                            </span>
                                        </template>
                                    </td>
                                    <td class="py-3 px-4 font-mono font-semibold text-slate-600" x-text="item.sku"></td>
                                    <td class="py-3 px-4 font-bold text-slate-900" x-text="item.product_name"></td>
                                    <td class="py-3 px-4 text-center">
                                        <template x-if="item.tracking_method === 'ICCID'">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-purple-50 text-purple-700 border border-purple-200">
                                                SERIAL ICCID
                                            </span>
                                        </template>
                                        <template x-if="item.tracking_method === 'QUANTITY'">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-blue-50 text-blue-700 border border-blue-200">
                                                QUANTITY
                                            </span>
                                        </template>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <span class="font-mono font-extrabold text-sm"
                                              :class="item.quantity > 50 ? 'text-slate-900' : (item.quantity > 0 ? 'text-amber-700' : 'text-slate-400')"
                                              x-text="Number(item.quantity).toLocaleString('id-ID')"></span>
                                        <span class="text-[11px] font-normal text-slate-400" x-text="item.sku.startsWith('BULK-') ? 'saldo' : 'pcs'"></span>
                                    </td>
                                </tr>
                            </template>

                            <template x-if="filteredBalances.length === 0">
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-400">
                                        <i data-lucide="package-x" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                                        <p class="font-bold">Tidak ada data stok yang sesuai dengan filter yang dipilih.</p>
                                        <button type="button" @click="resetFilters()" class="text-xs text-rose-600 hover:underline mt-1 font-semibold">Reset Filter</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 flex items-center justify-between text-xs text-slate-500 font-medium">
                    <span>Menampilkan <strong class="text-slate-800" x-text="filteredBalances.length"></strong> posisi saldo stok</span>
                    <span>Total Stok pada Filter Ini: <strong class="font-mono text-slate-800" x-text="totalFilteredQuantity.toLocaleString('id-ID')"></strong> unit/saldo</span>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- TAB 2: BUKU KARTU STOK (MUTASI IN - OUT)        -->
            <!-- ============================================== -->
            <div x-show="activeTab === 'mutations'" class="p-6">
                
                <!-- FILTER KARTU STOK -->
                <div class="mb-5 p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex flex-wrap items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-bold text-slate-600">Filter Tipe Mutasi:</span>
                        <button type="button" @click="mutationTypeFilter = 'ALL'" class="px-2.5 py-1 rounded-lg font-bold" :class="mutationTypeFilter === 'ALL' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 border border-slate-200'">Semua</button>
                        <button type="button" @click="mutationTypeFilter = 'IN'" class="px-2.5 py-1 rounded-lg font-bold" :class="mutationTypeFilter === 'IN' ? 'bg-emerald-600 text-white' : 'bg-white text-emerald-700 border border-emerald-200'">+ Masuk (IN)</button>
                        <button type="button" @click="mutationTypeFilter = 'OUT'" class="px-2.5 py-1 rounded-lg font-bold" :class="mutationTypeFilter === 'OUT' ? 'bg-rose-600 text-white' : 'bg-white text-rose-700 border border-rose-200'">- Keluar (OUT)</button>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="font-bold text-slate-600">Dokumen:</span>
                        <select x-model="mutationDocFilter" class="bg-white border border-slate-200 rounded-lg px-2.5 py-1 text-xs font-semibold focus:outline-none">
                            <option value="ALL">Semua Dokumen</option>
                            <option value="GR">GR (Penerimaan PO)</option>
                            <option value="GD">GD (Pengiriman Sales)</option>
                            <option value="SM">SM (Transfer Gudang)</option>
                            <option value="RPKOUT">RPKOUT (Repack Out)</option>
                            <option value="RPKIN">RPKIN (Repack In)</option>
                            <option value="SR">SR (Retur Penjualan)</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-xs">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">Tanggal</th>
                                <th class="py-3 px-4">Gudang</th>
                                <th class="py-3 px-4">Produk</th>
                                <th class="py-3 px-4">Dokumen</th>
                                <th class="py-3 px-4 text-center">Tipe</th>
                                <th class="py-3 px-4 text-right">Qty</th>
                                <th class="py-3 px-4 text-right">Saldo Akhir</th>
                                <th class="py-3 px-4">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            <template x-for="m in filteredMutations" :key="m.id">
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="py-3 px-4 text-slate-500" x-text="m.date"></td>
                                    <td class="py-3 px-4 font-bold text-slate-800" x-text="m.warehouse_name"></td>
                                    <td class="py-3 px-4">
                                        <span class="font-bold text-slate-900 block" x-text="m.product_name"></span>
                                        <span class="font-mono text-[10px] text-slate-400" x-text="m.sku"></span>
                                    </td>
                                    <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                        <span class="px-1.5 py-0.5 rounded text-[10px] text-white mr-1"
                                              :class="m.doc_type === 'GR' ? 'bg-emerald-600' : (m.doc_type === 'GD' ? 'bg-amber-600' : (m.doc_type === 'SM' ? 'bg-cyan-700' : 'bg-purple-600'))"
                                              x-text="m.doc_type">
                                        </span>
                                        <span x-text="m.doc_number"></span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <template x-if="m.mutation_type === 'IN'">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                MASUK
                                            </span>
                                        </template>
                                        <template x-if="m.mutation_type === 'OUT'">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                                KELUAR
                                            </span>
                                        </template>
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-extrabold text-sm"
                                        :class="m.mutation_type === 'IN' ? 'text-emerald-700' : 'text-rose-600'"
                                        x-text="(m.mutation_type === 'IN' ? '+' : '-') + Number(m.quantity).toLocaleString('id-ID')">
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-extrabold text-slate-900" x-text="Number(m.balance_after).toLocaleString('id-ID')"></td>
                                    <td class="py-3 px-4 text-slate-500" x-text="m.notes || '-'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- TAB 3: TRACKING SERIAL ICCID                   -->
            <!-- ============================================== -->
            <div x-show="activeTab === 'iccids'" class="p-6">
                
                <!-- FILTER ICCID -->
                <div class="mb-5 p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex flex-wrap items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-bold text-slate-600">Filter Status:</span>
                        <button type="button" @click="iccidStatusFilter = 'ALL'" class="px-2.5 py-1 rounded-lg font-bold" :class="iccidStatusFilter === 'ALL' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 border border-slate-200'">Semua</button>
                        <button type="button" @click="iccidStatusFilter = 'AVAILABLE'" class="px-2.5 py-1 rounded-lg font-bold" :class="iccidStatusFilter === 'AVAILABLE' ? 'bg-emerald-600 text-white' : 'bg-white text-emerald-700 border border-emerald-200'">AVAILABLE</button>
                        <button type="button" @click="iccidStatusFilter = 'RESERVED'" class="px-2.5 py-1 rounded-lg font-bold" :class="iccidStatusFilter === 'RESERVED' ? 'bg-amber-600 text-white' : 'bg-white text-amber-700 border border-amber-200'">RESERVED</button>
                        <button type="button" @click="iccidStatusFilter = 'SOLD'" class="px-2.5 py-1 rounded-lg font-bold" :class="iccidStatusFilter === 'SOLD' ? 'bg-blue-600 text-white' : 'bg-white text-blue-700 border border-blue-200'">SOLD</button>
                        <button type="button" @click="iccidStatusFilter = 'REPACKED'" class="px-2.5 py-1 rounded-lg font-bold" :class="iccidStatusFilter === 'REPACKED' ? 'bg-purple-600 text-white' : 'bg-white text-purple-700 border border-purple-200'">REPACKED</button>
                    </div>

                    <div class="w-64">
                        <input type="text" x-model="iccidSearch" placeholder="Cari nomor ICCID..." class="w-full text-xs font-mono font-bold px-3 py-1.5 bg-white border border-slate-200 rounded-lg focus:outline-none">
                    </div>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-xs">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">Nomor Seri ICCID</th>
                                <th class="py-3 px-4">Produk Perdana</th>
                                <th class="py-3 px-4">Gudang</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4">Doc Terakhir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            <template x-for="u in filteredIccids" :key="u.id">
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="py-3 px-4 font-mono font-extrabold text-purple-900" x-text="u.iccid_number"></td>
                                    <td class="py-3 px-4 font-bold text-slate-800" x-text="u.product_name"></td>
                                    <td class="py-3 px-4 text-slate-700 font-semibold" x-text="u.warehouse_name || '-'"></td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                              :class="u.status === 'AVAILABLE' ? 'bg-emerald-100 text-emerald-800' : (u.status === 'RESERVED' ? 'bg-amber-100 text-amber-800' : (u.status === 'SOLD' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'))"
                                              x-text="u.status"></span>
                                    </td>
                                    <td class="py-3 px-4 font-mono text-[11px] text-slate-500">
                                        <span class="px-1.5 py-0.5 bg-slate-200 text-slate-800 rounded mr-1" x-text="u.last_doc_type"></span>
                                        <span x-text="u.last_doc_number"></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- TAB 4: STOCK MOVEMENTS (SM)                    -->
            <!-- ============================================== -->
            <div x-show="activeTab === 'movements'" class="p-6">
                <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-xs">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">DocType & Nomor SM</th>
                                <th class="py-3 px-4">Gudang Asal</th>
                                <th class="py-3 px-4">Gudang Tujuan</th>
                                <th class="py-3 px-4">Tanggal</th>
                                <th class="py-3 px-4 text-center">Status</th>
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
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
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

    </div>

    @push('scripts')
    <script>
        function inventoryApp() {
            return {
                activeTab: 'balances',

                // Master Raw Data from Controller
                allBalances: @json($stockBalances),
                allMutations: @json($mutations),
                allIccids: @json($iccids),

                // Filters for Tab 1 (Balances)
                selectedWhType: 'ALL',       // 'ALL', 'MAIN', 'INVENTORY', 'SALES'
                selectedSpecificWh: 'ALL',   // 'ALL' or warehouse_id
                selectedProductType: 'ALL',  // 'ALL', 'SA', 'VOUCHER', 'MODEM', 'RAW_BULK'
                searchQuery: '',
                hideZeroStock: true,

                // Filters for Tab 2 (Mutations)
                mutationTypeFilter: 'ALL',
                mutationDocFilter: 'ALL',

                // Filters for Tab 3 (ICCIDs)
                iccidStatusFilter: 'ALL',
                iccidSearch: '',

                initData() {
                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                },

                // KPI Calculations
                get kpiMainStock() {
                    return this.allBalances
                        .filter(b => b.warehouse_type === 'MAIN' && !b.sku.startsWith('BULK-'))
                        .reduce((sum, b) => sum + (parseInt(b.quantity) || 0), 0);
                },

                get kpiInvStock() {
                    return this.allBalances
                        .filter(b => b.warehouse_type === 'INVENTORY' && !b.sku.startsWith('BULK-'))
                        .reduce((sum, b) => sum + (parseInt(b.quantity) || 0), 0);
                },

                get kpiSalesStock() {
                    return this.allBalances
                        .filter(b => b.warehouse_type === 'SALES' && !b.sku.startsWith('BULK-'))
                        .reduce((sum, b) => sum + (parseInt(b.quantity) || 0), 0);
                },

                get kpiRawStock() {
                    return this.allBalances
                        .filter(b => b.sku.startsWith('RAW-') || b.sku.startsWith('BULK-'))
                        .reduce((sum, b) => sum + (parseInt(b.quantity) || 0), 0);
                },

                // Filter Actions
                setWarehouseTypeFilter(type) {
                    this.activeTab = 'balances';
                    this.selectedWhType = type;
                    this.$nextTick(() => {
                        window.scrollTo({ top: 300, behavior: 'smooth' });
                    });
                },

                setProductTypeFilter(type) {
                    this.activeTab = 'balances';
                    this.selectedProductType = type;
                    this.$nextTick(() => {
                        window.scrollTo({ top: 300, behavior: 'smooth' });
                    });
                },

                resetFilters() {
                    this.selectedWhType = 'ALL';
                    this.selectedSpecificWh = 'ALL';
                    this.selectedProductType = 'ALL';
                    this.searchQuery = '';
                    this.hideZeroStock = false;
                    this.mutationTypeFilter = 'ALL';
                    this.mutationDocFilter = 'ALL';
                    this.iccidStatusFilter = 'ALL';
                    this.iccidSearch = '';
                },

                // Tab 1 Filter Logic
                get filteredBalances() {
                    return this.allBalances.filter(item => {
                        // 1. Filter Tipe Gudang
                        if (this.selectedWhType !== 'ALL' && item.warehouse_type !== this.selectedWhType) {
                            return false;
                        }

                        // 2. Filter Gudang Spesifik
                        if (this.selectedSpecificWh !== 'ALL' && item.warehouse_id != this.selectedSpecificWh) {
                            return false;
                        }

                        // 3. Filter Tipe / Kategori Produk
                        if (this.selectedProductType !== 'ALL') {
                            if (this.selectedProductType === 'RAW_BULK') {
                                if (!item.sku.startsWith('RAW-') && !item.sku.startsWith('BULK-')) return false;
                            } else {
                                if (item.sku.startsWith('RAW-') || item.sku.startsWith('BULK-')) return false;
                                if (item.product_type !== this.selectedProductType) return false;
                            }
                        }

                        // 4. Sembunyikan Stok Kosong
                        if (this.hideZeroStock && parseInt(item.quantity) <= 0) {
                            return false;
                        }

                        // 5. Search Query
                        if (this.searchQuery) {
                            const q = this.searchQuery.toLowerCase();
                            const matchWh = item.warehouse_name.toLowerCase().includes(q);
                            const matchProd = item.product_name.toLowerCase().includes(q);
                            const matchSku = item.sku.toLowerCase().includes(q);
                            const matchTap = item.tap_name.toLowerCase().includes(q);
                            if (!matchWh && !matchProd && !matchSku && !matchTap) return false;
                        }

                        return true;
                    });
                },

                get totalFilteredQuantity() {
                    return this.filteredBalances.reduce((sum, item) => sum + (parseInt(item.quantity) || 0), 0);
                },

                // Tab 2 Filter Logic
                get filteredMutations() {
                    return this.allMutations.filter(m => {
                        if (this.mutationTypeFilter !== 'ALL' && m.mutation_type !== this.mutationTypeFilter) return false;
                        if (this.mutationDocFilter !== 'ALL' && m.doc_type !== this.mutationDocFilter) return false;
                        return true;
                    });
                },

                // Tab 3 Filter Logic
                get filteredIccids() {
                    return this.allIccids.filter(u => {
                        if (this.iccidStatusFilter !== 'ALL' && u.status !== this.iccidStatusFilter) return false;
                        if (this.iccidSearch && !u.iccid_number.toLowerCase().includes(this.iccidSearch.toLowerCase())) return false;
                        return true;
                    });
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
