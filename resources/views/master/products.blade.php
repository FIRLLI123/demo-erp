<x-app-layout>
    <x-slot name="title">Master Data Produk</x-slot>
    <x-slot name="headerTitle">Master Data Produk</x-slot>
    <x-slot name="badge">{{ $iccidEnabled ? 'ICCID & Quantity Active' : 'Quantity Mode (ICCID Locked)' }}</x-slot>

    <div x-data="productMasterApp()" class="pb-12">

        <!-- Header & Action Button -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Master Produk & Harga Wilayah</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200 uppercase">
                        {{ $iccidEnabled ? 'ICCID ON' : 'NON-ICCID' }}
                    </span>
                </h2>
            </div>

            <div class="flex items-center gap-2">
                <button type="button"
                        x-show="activeTab === 'products'"
                        @click="openProductModal()"
                        class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-xs transition-all cursor-pointer">
                    <i data-lucide="plus" class="w-4 h-4 text-emerald-400"></i>
                    <span>Tambah Produk Baru</span>
                </button>

                <button type="button"
                        x-show="activeTab === 'types'" x-cloak
                        @click="openTypeModal()"
                        class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-xs transition-all cursor-pointer">
                    <i data-lucide="plus" class="w-4 h-4 text-emerald-400"></i>
                    <span>Tambah Tipe Produk</span>
                </button>

                <button type="button"
                        x-show="activeTab === 'prices'" x-cloak
                        @click="openPriceModal()"
                        class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-xs transition-all cursor-pointer">
                    <i data-lucide="plus" class="w-4 h-4 text-emerald-400"></i>
                    <span>Atur Harga Wilayah</span>
                </button>
            </div>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between shadow-2xs">
                <span class="flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                    {{ session('success') }}
                </span>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 cursor-pointer">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center justify-between shadow-2xs">
                <span class="flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                    {{ session('error') }}
                </span>
                <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800 cursor-pointer">&times;</button>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- ============================================== -->
        <!-- TAB NAVIGASI: PRODUK | TIPE | HARGA CLUSTER    -->
        <!-- ============================================== -->
        <div class="mb-5 border-b border-slate-200 flex flex-wrap items-center gap-1">
            <button type="button"
                    @click="activeTab = 'products'"
                    :class="activeTab === 'products' ? 'border-rose-600 text-rose-700 bg-rose-50/50' : 'border-transparent text-slate-500 hover:text-slate-800'"
                    class="px-4 py-2.5 text-xs font-extrabold border-b-2 rounded-t-xl transition-all flex items-center gap-2 cursor-pointer">
                <i data-lucide="package" class="w-4 h-4"></i>
                <span>Daftar Produk</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-mono bg-slate-200/80 text-slate-700">{{ count($products) }}</span>
            </button>

            <button type="button"
                    @click="activeTab = 'types'"
                    :class="activeTab === 'types' ? 'border-rose-600 text-rose-700 bg-rose-50/50' : 'border-transparent text-slate-500 hover:text-slate-800'"
                    class="px-4 py-2.5 text-xs font-extrabold border-b-2 rounded-t-xl transition-all flex items-center gap-2 cursor-pointer">
                <i data-lucide="tags" class="w-4 h-4"></i>
                <span>Tipe Produk</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-mono bg-slate-200/80 text-slate-700">{{ count($productTypes) }}</span>
            </button>

            <button type="button"
                    @click="activeTab = 'prices'"
                    :class="activeTab === 'prices' ? 'border-rose-600 text-rose-700 bg-rose-50/50' : 'border-transparent text-slate-500 hover:text-slate-800'"
                    class="px-4 py-2.5 text-xs font-extrabold border-b-2 rounded-t-xl transition-all flex items-center gap-2 cursor-pointer">
                <i data-lucide="map-pin" class="w-4 h-4"></i>
                <span>Harga per Cluster</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-mono bg-slate-200/80 text-slate-700">{{ count($productPrices) }}</span>
            </button>
        </div>

        <!-- ============================================== -->
        <!-- TAB 1: DAFTAR PRODUK                           -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'products'">
            <!-- Filter Bar -->
            <div class="bg-slate-50/80 border border-slate-200/90 rounded-2xl p-3.5 mb-5 shadow-2xs">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-2.5 flex-1">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 flex items-center gap-1.5 mr-1">
                            <i data-lucide="filter" class="w-3.5 h-3.5 text-rose-600"></i>
                            <span>Filter:</span>
                        </span>

                        <!-- Tipe Produk -->
                        <div class="relative inline-flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-indigo-600">
                                <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                            </div>
                            <select x-model="filterType"
                                    @change="productPage = 1; refreshIcons()"
                                    :class="filterType ? 'border-rose-500 ring-2 ring-rose-500/10 text-rose-900 bg-rose-50/20' : 'border-slate-200 bg-white text-slate-800'"
                                    class="min-w-[165px] text-xs font-bold border rounded-xl pl-9 pr-8 py-2 focus:outline-none cursor-pointer appearance-none">
                                <option value="">-- Semua Tipe --</option>
                                @foreach($productTypes as $pt)
                                    <option value="{{ $pt->code }}">{{ $pt->name }} ({{ $pt->code }})</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                            </div>
                        </div>

                        <!-- Konsinyasi -->
                        <div class="relative inline-flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-amber-600">
                                <i data-lucide="handshake" class="w-3.5 h-3.5"></i>
                            </div>
                            <select x-model="filterConsignment"
                                    @change="productPage = 1; refreshIcons()"
                                    :class="filterConsignment !== 'ALL' ? 'border-rose-500 ring-2 ring-rose-500/10 text-rose-900 bg-rose-50/20' : 'border-slate-200 bg-white text-slate-800'"
                                    class="min-w-[165px] text-xs font-bold border rounded-xl pl-9 pr-8 py-2 focus:outline-none cursor-pointer appearance-none">
                                <option value="ALL">-- Konsinyasi: Semua --</option>
                                <option value="1">Konsinyasi (Ya)</option>
                                <option value="0">Non-Konsinyasi</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                            </div>
                        </div>

                        @if($iccidEnabled)
                            <!-- Tracking Method (Hanya muncul jika ERP_ENABLE_ICCID=true) -->
                            <div class="relative inline-flex items-center">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-purple-600">
                                    <i data-lucide="scan-barcode" class="w-3.5 h-3.5"></i>
                                </div>
                                <select x-model="filterTracking"
                                        @change="productPage = 1; refreshIcons()"
                                        :class="filterTracking !== 'ALL' ? 'border-rose-500 ring-2 ring-rose-500/10 text-rose-900 bg-rose-50/20' : 'border-slate-200 bg-white text-slate-800'"
                                        class="min-w-[165px] text-xs font-bold border rounded-xl pl-9 pr-8 py-2 focus:outline-none cursor-pointer appearance-none">
                                    <option value="ALL">-- Semua Tracking --</option>
                                    <option value="QUANTITY">Quantity Based</option>
                                    <option value="ICCID">ICCID Based</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
                                    <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                                </div>
                            </div>
                        @endif

                        <!-- Search -->
                        <x-search-input model="searchProduct" placeholder="Cari SKU, nama, kode Telkomsel, denom..." width="w-full sm:w-64" accent="rose" />

                        <!-- Reset -->
                        <button type="button"
                                x-show="filterType || filterConsignment !== 'ALL' || filterTracking !== 'ALL' || searchProduct"
                                x-cloak
                                @click="resetProductFilter()"
                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-200 hover:bg-rose-100 hover:text-rose-700 text-slate-600 text-xs font-bold transition-all cursor-pointer">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            <span>Reset</span>
                        </button>
                    </div>

                    <div class="flex items-center gap-2 text-xs font-medium text-slate-500 shrink-0">
                        <span>Menampilkan:</span>
                        <span class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 font-bold text-slate-800 shadow-2xs">
                            <strong class="text-rose-600" x-text="filteredProducts.length"></strong>
                            <span class="text-slate-400 font-normal"> / <span x-text="allProducts.length"></span> data</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Table Products -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">SKU & Produk</th>
                                <th class="py-3 px-4">Tipe & Denom</th>
                                @if($iccidEnabled)
                                    <th class="py-3 px-4 text-center">Tracking</th>
                                @endif
                                <th class="py-3 px-4 text-right">Harga Default</th>
                                <th class="py-3 px-4">Masa Berlaku</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-center w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @foreach($products as $prod)
                                <tr x-show="paginatedProductIds.includes({{ $prod->id }})" class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-3.5 px-4">
                                        <span class="font-extrabold text-slate-900 block text-xs">{{ $prod->name }}</span>
                                        <div class="flex flex-wrap items-center gap-1.5 mt-0.5">
                                            <span class="px-1.5 py-0.5 rounded font-mono font-bold text-[10px] bg-slate-100 text-slate-700 border border-slate-200">{{ $prod->sku }}</span>
                                            @if($prod->item_code_telkomsel)
                                                <span class="px-1.5 py-0.5 rounded font-mono text-[10px] bg-rose-50 text-rose-700 border border-rose-200">Tsel: {{ $prod->item_code_telkomsel }}</span>
                                            @endif
                                            <span class="text-[10px] text-slate-400">UOM: {{ $prod->uom }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            {{ $prod->category_name ?? $prod->type }}
                                        </span>
                                        @if($prod->denom)
                                            <span class="block text-[11px] text-slate-600 font-mono font-bold mt-1">Denom: {{ $prod->denom }}</span>
                                        @endif
                                    </td>
                                    @if($iccidEnabled)
                                        <td class="py-3.5 px-4 text-center">
                                            @if($prod->tracking_method === 'ICCID')
                                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded border border-purple-200">
                                                    <i data-lucide="scan-barcode" class="w-3 h-3"></i> ICCID
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200">
                                                    <i data-lucide="layers" class="w-3 h-3"></i> QTY
                                                </span>
                                            @endif
                                        </td>
                                    @endif
                                    <td class="py-3.5 px-4 text-right font-mono">
                                        <div class="text-[11px] text-slate-500">Modal: Rp {{ number_format($prod->cost_price, 0, ',', '.') }}</div>
                                        <div class="text-xs font-extrabold text-slate-900">Jual: Rp {{ number_format($prod->selling_price, 0, ',', '.') }}</div>
                                        @if($prod->special_price > 0)
                                            <div class="text-[10px] text-emerald-700 font-bold">Spesial: Rp {{ number_format($prod->special_price, 0, ',', '.') }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-[11px] font-mono">
                                        @if($prod->activation_date || $prod->expired_date)
                                            <div class="text-slate-600">Aktif: {{ $prod->activation_date ? date('d/m/Y', strtotime($prod->activation_date)) : '-' }}</div>
                                            <div class="text-rose-600 font-bold">Exp: {{ $prod->expired_date ? date('d/m/Y', strtotime($prod->expired_date)) : '-' }}</div>
                                        @else
                                            <span class="text-slate-400 italic">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <x-master-status-toggle entity="products" :id="$prod->id" :name="$prod->name" :active="$prod->status === 'ACTIVE'" />
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <button type="button"
                                                @click="openProductModal({{ json_encode($prod) }})"
                                                class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 text-slate-700 text-xs font-bold transition-all cursor-pointer">
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            <tr x-show="filteredProducts.length === 0" x-cloak>
                                <td colspan="{{ $iccidEnabled ? 7 : 6 }}" class="py-8 text-center text-slate-400">
                                    Tidak ada data produk yang sesuai filter/pencarian.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Bar Produk -->
                <div class="p-4 border-t border-slate-200 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-500">
                    <div class="flex items-center gap-3">
                        <div>
                            Menampilkan <strong class="text-slate-800" x-text="productPaginationStart"></strong> - <strong class="text-slate-800" x-text="productPaginationEnd"></strong> dari <strong class="text-slate-800" x-text="filteredProducts.length"></strong> data Produk
                        </div>
                        <span class="text-slate-300">|</span>
                        <label class="flex items-center gap-1.5 text-xs">
                            <span>Baris:</span>
                            <select x-model="productPerPage" @change="productPage = 1; refreshIcons()" class="border border-slate-200 rounded-lg px-2 py-1 bg-white text-xs font-semibold text-slate-700 cursor-pointer">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="ALL">Semua</option>
                            </select>
                        </label>
                    </div>

                    <div class="flex items-center gap-1" x-show="totalProductPages > 1">
                        <button type="button" @click="productPage = Math.max(1, productPage - 1); refreshIcons()" :disabled="productPage === 1" class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 disabled:opacity-40 text-xs font-bold flex items-center gap-1 cursor-pointer">
                            <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i> Prev
                        </button>
                        <template x-for="(p, idx) in displayedProductPages" :key="idx">
                            <button type="button" @click="if (p !== '...') { productPage = p; refreshIcons(); }" :disabled="p === '...'" :class="productPage === p ? 'bg-rose-600 text-white font-extrabold border-rose-600' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100 font-semibold'" class="min-w-[30px] h-7 px-1.5 rounded-lg border text-xs flex items-center justify-center cursor-pointer" x-text="p"></button>
                        </template>
                        <button type="button" @click="productPage = Math.min(totalProductPages, productPage + 1); refreshIcons()" :disabled="productPage === totalProductPages" class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 disabled:opacity-40 text-xs font-bold flex items-center gap-1 cursor-pointer">
                            Next <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- TAB 2: MASTER TIPE PRODUK                      -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'types'" x-cloak>
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
                    <span class="text-xs font-extrabold text-slate-800">Master Tipe / Kategori Produk</span>
                    <x-search-input model="searchType" placeholder="Cari kode atau nama tipe..." width="w-full sm:w-64" accent="rose" />
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4 w-32">Kode Tipe</th>
                                <th class="py-3 px-4">Nama Tipe Produk</th>
                                <th class="py-3 px-4">Keterangan</th>
                                <th class="py-3 px-4 text-center">Jumlah Produk</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-center w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @foreach($productTypes as $pt)
                                <tr x-show="isTypeVisible({{ json_encode($pt) }})" class="hover:bg-slate-50/70">
                                    <td class="py-3 px-4 font-mono font-extrabold text-rose-700">{{ $pt->code }}</td>
                                    <td class="py-3 px-4 font-bold text-slate-900">{{ $pt->name }}</td>
                                    <td class="py-3 px-4 text-slate-500">{{ $pt->description ?? '-' }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-slate-100 text-slate-700">{{ $pt->products_count }} Produk</span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <x-master-status-toggle entity="product_categories" :id="$pt->id" :name="$pt->name" :active="($pt->status ?? 'ACTIVE') === 'ACTIVE'" />
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <button type="button" @click="openTypeModal({{ json_encode($pt) }})" class="px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-rose-50 hover:text-rose-700 text-slate-700 text-xs font-bold cursor-pointer">
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- TAB 3: SETTING HARGA PER CLUSTER / WILAYAH     -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'prices'" x-cloak>
            <div class="bg-slate-50/80 border border-slate-200/90 rounded-2xl p-3.5 mb-5 shadow-2xs">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-2.5 flex-1">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 flex items-center gap-1.5 mr-1">
                            <i data-lucide="filter" class="w-3.5 h-3.5 text-rose-600"></i>
                            <span>Filter:</span>
                        </span>

                        <!-- Area -->
                        <div class="relative inline-flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-cyan-600">
                                <i data-lucide="map" class="w-3.5 h-3.5"></i>
                            </div>
                            <select x-model="priceFilterArea" @change="priceFilterCluster = ''; priceFilterTap = ''" :class="priceFilterArea ? 'border-rose-500 ring-2 ring-rose-500/10 text-rose-900 bg-rose-50/20' : 'border-slate-200 bg-white text-slate-800'" class="min-w-[165px] text-xs font-bold border rounded-xl pl-9 pr-8 py-2 focus:outline-none cursor-pointer appearance-none">
                                <option value="">-- Semua Area --</option>
                                @foreach($areas as $ar)
                                    <option value="{{ $ar->id }}">{{ $ar->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                            </div>
                        </div>

                        <!-- Cluster -->
                        <div class="relative inline-flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-amber-600">
                                <i data-lucide="grid" class="w-3.5 h-3.5"></i>
                            </div>
                            <select x-model="priceFilterCluster" @change="priceFilterTap = ''" :class="priceFilterCluster ? 'border-rose-500 ring-2 ring-rose-500/10 text-rose-900 bg-rose-50/20' : 'border-slate-200 bg-white text-slate-800'" class="min-w-[175px] text-xs font-bold border rounded-xl pl-9 pr-8 py-2 focus:outline-none cursor-pointer appearance-none">
                                <option value="">-- Semua Cluster --</option>
                                <template x-for="cls in priceFilterClusterOptions" :key="cls.id">
                                    <option :value="String(cls.id)" x-text="cls.name"></option>
                                </template>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                            </div>
                        </div>

                        <x-search-input model="searchPrice" placeholder="Cari produk / wilayah..." width="w-full sm:w-64" accent="rose" />

                        <button type="button" x-show="priceFilterArea || priceFilterCluster || searchPrice" x-cloak @click="priceFilterArea = ''; priceFilterCluster = ''; searchPrice = ''" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-200 hover:bg-rose-100 hover:text-rose-700 text-slate-600 text-xs font-bold cursor-pointer">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i> Reset
                        </button>
                    </div>

                    <div class="flex items-center gap-2 text-xs font-medium text-slate-500 shrink-0">
                        <span>Menampilkan:</span>
                        <span class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 font-bold text-slate-800 shadow-2xs">
                            <strong class="text-rose-600" x-text="filteredPrices.length"></strong>
                            <span class="text-slate-400 font-normal"> / <span x-text="allPrices.length"></span> data</span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">Produk</th>
                                <th class="py-3 px-4">Cakupan Wilayah</th>
                                <th class="py-3 px-4 text-right">Harga Modal (HPP)</th>
                                <th class="py-3 px-4 text-right">Harga Jual</th>
                                <th class="py-3 px-4 text-right">Harga Spesial</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-center w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($productPrices as $pp)
                                <tr x-show="isPriceVisible({{ json_encode($pp) }})" class="hover:bg-slate-50/70">
                                    <td class="py-3 px-4">
                                        <span class="font-bold text-slate-900 block">{{ $pp->product_name }}</span>
                                        <span class="text-[10px] font-mono text-slate-400">{{ $pp->product_sku }} &bull; Default: Rp {{ number_format($pp->default_selling_price, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($pp->tap_name)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">TAP: {{ $pp->tap_name }}</span>
                                            <span class="block text-[10px] text-slate-400 mt-0.5">{{ $pp->cluster_name }} &bull; {{ $pp->area_name }}</span>
                                        @elseif($pp->cluster_name)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">Cluster: {{ $pp->cluster_name }}</span>
                                            <span class="block text-[10px] text-slate-400 mt-0.5">{{ $pp->area_name }}</span>
                                        @elseif($pp->area_name)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-cyan-50 text-cyan-800 border border-cyan-200">Area: {{ $pp->area_name }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono text-slate-700">Rp {{ number_format($pp->cost_price, 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-right font-mono font-extrabold text-slate-900">Rp {{ number_format($pp->selling_price, 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-right font-mono text-emerald-700 font-bold">
                                        {{ $pp->special_price > 0 ? 'Rp ' . number_format($pp->special_price, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <x-master-status-toggle entity="product_prices" :id="$pp->id" :name="$pp->product_name" :active="$pp->status === 'ACTIVE'" />
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button type="button" @click="openPriceModal({{ json_encode($pp) }})" class="px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-rose-50 hover:text-rose-700 text-slate-700 text-xs font-bold cursor-pointer">
                                                Edit
                                            </button>
                                            <form method="POST" action="{{ route('master.product-prices.delete', $pp->id) }}" onsubmit="return confirm('Hapus setting harga wilayah ini?')">
                                                @csrf
                                                <button type="submit" class="px-2 py-1 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold cursor-pointer">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-slate-400">
                                        Belum ada setting harga khusus per Cluster/Wilayah. Klik <strong>Atur Harga Wilayah</strong> untuk menambahkan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- MODAL TAMBAH / EDIT PRODUK                     -->
        <!-- ============================================== -->
        <template x-if="showProductModal">
            <div @click.self="showProductModal = false" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                <div class="bg-white rounded-2xl border border-slate-200 max-w-3xl w-full p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                        <h3 class="font-extrabold text-slate-900 text-sm" x-text="isProductEdit ? 'Edit Data Produk' : 'Tambah Produk Baru'"></h3>
                        <button type="button" @click="showProductModal = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold cursor-pointer">&times;</button>
                    </div>

                    <form :action="productFormAction" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">SKU Produk <span class="text-rose-500">*</span></label>
                                <input type="text" name="sku" x-model="productForm.sku" required placeholder="SA-5GB-REG" class="w-full text-xs font-mono font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Kode Produk Telkomsel</label>
                                <input type="text" name="item_code_telkomsel" x-model="productForm.item_code_telkomsel" placeholder="Kode DigiPOS / NGRS" class="w-full text-xs font-mono bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Produk <span class="text-rose-500">*</span></label>
                                <select name="category_id" x-model="productForm.category_id" required class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                    <option value="">-- Pilih Tipe --</option>
                                    @foreach($productTypes as $pt)
                                        <option value="{{ $pt->id }}">{{ $pt->name }} ({{ $pt->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Produk <span class="text-rose-500">*</span></label>
                                <input type="text" name="name" x-model="productForm.name" required placeholder="Perdana Telkomsel 5GB Reguler" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Denom / Nominal</label>
                                <input type="text" name="denom" x-model="productForm.denom" placeholder="5GB / 10K / 50K" class="w-full text-xs font-mono bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Harga Modal Default (Rp) <span class="text-rose-500">*</span></label>
                                <input type="number" name="cost_price" x-model="productForm.cost_price" required min="0" step="1" class="w-full text-xs font-mono font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Harga Jual Default (Rp) <span class="text-rose-500">*</span></label>
                                <input type="number" name="selling_price" x-model="productForm.selling_price" required min="0" step="1" class="w-full text-xs font-mono font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Harga Spesial Default (Rp)</label>
                                <input type="number" name="special_price" x-model="productForm.special_price" min="0" step="1" class="w-full text-xs font-mono font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Satuan (UOM) <span class="text-rose-500">*</span></label>
                                <input type="text" name="uom" x-model="productForm.uom" required placeholder="PCS" class="w-full text-xs font-mono font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Stok Minimum</label>
                                <input type="number" name="min_stock" x-model="productForm.min_stock" min="0" class="w-full text-xs font-mono bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Aktivasi</label>
                                <input type="date" name="activation_date" x-model="productForm.activation_date" class="w-full text-xs font-mono bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Expired</label>
                                <input type="date" name="expired_date" x-model="productForm.expired_date" class="w-full text-xs font-mono bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Alias Faktur Pajak</label>
                                <input type="text" name="tax_alias" x-model="productForm.tax_alias" placeholder="Nama item di faktur pajak" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                            </div>

                            @if($iccidEnabled)
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Metode Tracking</label>
                                    <select name="tracking_method" x-model="productForm.tracking_method" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                        <option value="QUANTITY">QUANTITY (Non-ICCID)</option>
                                        <option value="ICCID">ICCID (Serial Number)</option>
                                    </select>
                                </div>
                            @else
                                <input type="hidden" name="tracking_method" value="QUANTITY">
                            @endif

                            <template x-if="isProductEdit">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Status</label>
                                    <select name="status" x-model="productForm.status" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                        <option value="ACTIVE">ACTIVE</option>
                                        <option value="INACTIVE">INACTIVE</option>
                                    </select>
                                </div>
                            </template>
                        </div>

                        <!-- Flag Checkboxes -->
                        <div class="flex flex-wrap items-center gap-6 p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-800">
                                <input type="checkbox" name="is_consignment" value="1" x-model="productForm.is_consignment" class="rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                                <span>Bisa Konsinyasi (TRUE)</span>
                            </label>
                            <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-800">
                                <input type="checkbox" name="is_virtual" value="1" x-model="productForm.is_virtual" class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                                <span>Produk Virtual / Saldo</span>
                            </label>
                            <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-800">
                                <input type="checkbox" name="is_ppn" value="1" x-model="productForm.is_ppn" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                <span>Kena PPN</span>
                            </label>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Produk</label>
                            <textarea name="description" x-model="productForm.description" rows="2" placeholder="Catatan tambahan produk..." class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                            <button type="button" @click="showProductModal = false" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl text-xs cursor-pointer">Batal</button>
                            <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs cursor-pointer" x-text="isProductEdit ? 'Simpan Perubahan' : 'Simpan Produk'"></button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- ============================================== -->
        <!-- MODAL TAMBAH / EDIT TIPE PRODUK                -->
        <!-- ============================================== -->
        <template x-if="showTypeModal">
            <div @click.self="showTypeModal = false" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                <div class="bg-white rounded-2xl border border-slate-200 max-w-md w-full p-6 shadow-2xl">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                        <h3 class="font-extrabold text-slate-900 text-sm" x-text="isTypeEdit ? 'Edit Tipe Produk' : 'Tambah Tipe Produk'"></h3>
                        <button type="button" @click="showTypeModal = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold cursor-pointer">&times;</button>
                    </div>
                    <form :action="typeFormAction" method="POST" class="space-y-3.5">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kode Tipe <span class="text-rose-500">*</span></label>
                            <input type="text" name="code" x-model="typeForm.code" required placeholder="SA / VOUCHER / MODEM / SALDO" class="w-full text-xs font-mono font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Tipe Produk <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" x-model="typeForm.name" required placeholder="Starter Pack (Perdana)" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan</label>
                            <input type="text" name="description" x-model="typeForm.description" placeholder="Deskripsi singkat tipe..." class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                        </div>
                        <template x-if="isTypeEdit">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Status</label>
                                <select name="status" x-model="typeForm.status" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                    <option value="ACTIVE">ACTIVE</option>
                                    <option value="INACTIVE">INACTIVE</option>
                                </select>
                            </div>
                        </template>
                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                            <button type="button" @click="showTypeModal = false" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl text-xs cursor-pointer">Batal</button>
                            <button type="submit" class="px-5 py-2 bg-slate-900 text-white font-bold rounded-xl text-xs cursor-pointer">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- ============================================== -->
        <!-- MODAL TAMBAH / EDIT HARGA PER CLUSTER          -->
        <!-- ============================================== -->
        <template x-if="showPriceModal">
            <div @click.self="showPriceModal = false" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                <div class="bg-white rounded-2xl border border-slate-200 max-w-xl w-full p-6 shadow-2xl">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                        <h3 class="font-extrabold text-slate-900 text-sm" x-text="isPriceEdit ? 'Edit Harga Wilayah' : 'Atur Harga Produk per Cluster / Wilayah'"></h3>
                        <button type="button" @click="showPriceModal = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold cursor-pointer">&times;</button>
                    </div>
                    <form :action="priceFormAction" method="POST" class="space-y-3.5">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Produk <span class="text-rose-500">*</span></label>
                            <select name="product_id" x-model="priceForm.product_id" @change="autoFillDefaultPrice()" required class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                <option value="">-- Pilih Produk --</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}">[{{ $prod->sku }}] {{ $prod->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Area</label>
                                <select name="area_id" x-model="priceForm.area_id" @change="priceForm.cluster_id = ''; priceForm.tap_id = ''" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                    <option value="">-- Pilih Area --</option>
                                    @foreach($areas as $ar)
                                        <option value="{{ $ar->id }}">{{ $ar->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Cluster (Utama)</label>
                                <select name="cluster_id" x-model="priceForm.cluster_id" @change="priceForm.tap_id = ''" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                    <option value="">-- Semua Cluster di Area --</option>
                                    <template x-for="cls in modalClusterOptions" :key="cls.id">
                                        <option :value="String(cls.id)" :selected="String(cls.id) === String(priceForm.cluster_id)" x-text="cls.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">TAP (Opsional)</label>
                                <select name="tap_id" x-model="priceForm.tap_id" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                    <option value="">-- Semua TAP di Cluster --</option>
                                    <template x-for="tp in modalTapOptions" :key="tp.id">
                                        <option :value="String(tp.id)" :selected="String(tp.id) === String(priceForm.tap_id)" x-text="tp.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Harga Modal (Rp) <span class="text-rose-500">*</span></label>
                                <input type="number" name="cost_price" x-model="priceForm.cost_price" required min="0" step="1" class="w-full text-xs font-mono font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Harga Jual (Rp) <span class="text-rose-500">*</span></label>
                                <input type="number" name="selling_price" x-model="priceForm.selling_price" required min="0" step="1" class="w-full text-xs font-mono font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Harga Spesial (Rp)</label>
                                <input type="number" name="special_price" x-model="priceForm.special_price" min="0" step="1" class="w-full text-xs font-mono font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                            </div>
                        </div>

                        <template x-if="isPriceEdit">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Status</label>
                                <select name="status" x-model="priceForm.status" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                    <option value="ACTIVE">ACTIVE</option>
                                    <option value="INACTIVE">INACTIVE</option>
                                </select>
                            </div>
                        </template>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                            <button type="button" @click="showPriceModal = false" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl text-xs cursor-pointer">Batal</button>
                            <button type="submit" class="px-5 py-2 bg-slate-900 text-white font-bold rounded-xl text-xs cursor-pointer">Simpan Harga</button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>

    @push('scripts')
    <script>
        function productMasterApp() {
            return {
                activeTab: 'products',
                allProducts: @json($products),
                allPrices: @json($productPrices),
                allClusters: @json($clusters),
                allTaps: @json($taps),

                // Filter Tab 1: Products
                filterType: '',
                filterConsignment: 'ALL',
                filterTracking: 'ALL',
                searchProduct: '',
                productPage: 1,
                productPerPage: '10',

                // Filter Tab 2: Types
                searchType: '',

                // Filter Tab 3: Prices
                priceFilterArea: '',
                priceFilterCluster: '',
                searchPrice: '',

                // Modal Produk
                showProductModal: false,
                isProductEdit: false,
                productFormAction: '',
                productForm: {},

                // Modal Tipe
                showTypeModal: false,
                isTypeEdit: false,
                typeFormAction: '',
                typeForm: {},

                // Modal Harga Wilayah
                showPriceModal: false,
                isPriceEdit: false,
                priceFormAction: '',
                priceForm: {},

                init() {
                    const params = new URLSearchParams(window.location.search);
                    if (params.has('tab') && ['products', 'types', 'prices'].includes(params.get('tab'))) {
                        this.activeTab = params.get('tab');
                    }
                    this.$watch('searchProduct', () => { this.productPage = 1; this.refreshIcons(); });
                    this.$watch('activeTab', () => this.refreshIcons());
                    this.refreshIcons();
                },

                // Tab 1 Filtering & Pagination
                isProductVisible(p) {
                    if (this.filterType && p.category_code !== this.filterType && p.type !== this.filterType) return false;
                    if (this.filterConsignment !== 'ALL' && Number(Boolean(p.is_consignment)) !== Number(this.filterConsignment)) return false;
                    if (this.filterTracking !== 'ALL' && p.tracking_method !== this.filterTracking) return false;
                    if (this.searchProduct) {
                        const q = this.searchProduct.toLowerCase();
                        const match = (p.name && p.name.toLowerCase().includes(q)) ||
                                      (p.sku && p.sku.toLowerCase().includes(q)) ||
                                      (p.item_code_telkomsel && p.item_code_telkomsel.toLowerCase().includes(q)) ||
                                      (p.denom && p.denom.toLowerCase().includes(q)) ||
                                      (p.category_name && p.category_name.toLowerCase().includes(q));
                        if (!match) return false;
                    }
                    return true;
                },

                get filteredProducts() {
                    return this.allProducts.filter(p => this.isProductVisible(p));
                },

                get totalProductPages() {
                    if (this.productPerPage === 'ALL') return 1;
                    const perPage = parseInt(this.productPerPage, 10) || 10;
                    return Math.max(1, Math.ceil(this.filteredProducts.length / perPage));
                },

                get paginatedProductIds() {
                    if (this.productPerPage === 'ALL') return this.filteredProducts.map(p => p.id);
                    const perPage = parseInt(this.productPerPage, 10) || 10;
                    const start = (this.productPage - 1) * perPage;
                    return this.filteredProducts.slice(start, start + perPage).map(p => p.id);
                },

                get productPaginationStart() {
                    if (this.filteredProducts.length === 0) return 0;
                    if (this.productPerPage === 'ALL') return 1;
                    const perPage = parseInt(this.productPerPage, 10) || 10;
                    return (this.productPage - 1) * perPage + 1;
                },

                get productPaginationEnd() {
                    if (this.productPerPage === 'ALL') return this.filteredProducts.length;
                    const perPage = parseInt(this.productPerPage, 10) || 10;
                    return Math.min(this.productPage * perPage, this.filteredProducts.length);
                },

                get displayedProductPages() {
                    const current = this.productPage;
                    const total = this.totalProductPages;
                    if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
                    if (current <= 4) return [1, 2, 3, 4, 5, '...', total];
                    if (current >= total - 3) return [1, '...', total - 4, total - 3, total - 2, total - 1, total];
                    return [1, '...', current - 1, current, current + 1, '...', total];
                },

                resetProductFilter() {
                    this.filterType = '';
                    this.filterConsignment = 'ALL';
                    this.filterTracking = 'ALL';
                    this.searchProduct = '';
                    this.productPage = 1;
                    this.refreshIcons();
                },

                // Tab 2 Filtering
                isTypeVisible(pt) {
                    if (!this.searchType) return true;
                    const q = this.searchType.toLowerCase();
                    return (pt.code && pt.code.toLowerCase().includes(q)) ||
                           (pt.name && pt.name.toLowerCase().includes(q)) ||
                           (pt.description && pt.description.toLowerCase().includes(q));
                },

                // Tab 3 Filtering
                get priceFilterClusterOptions() {
                    if (!this.priceFilterArea) return this.allClusters;
                    return this.allClusters.filter(c => String(c.area_id) === String(this.priceFilterArea));
                },

                isPriceVisible(pp) {
                    if (this.priceFilterArea && String(pp.area_id) !== String(this.priceFilterArea)) return false;
                    if (this.priceFilterCluster && String(pp.cluster_id) !== String(this.priceFilterCluster)) return false;
                    if (this.searchPrice) {
                        const q = this.searchPrice.toLowerCase();
                        const match = (pp.product_name && pp.product_name.toLowerCase().includes(q)) ||
                                      (pp.product_sku && pp.product_sku.toLowerCase().includes(q)) ||
                                      (pp.cluster_name && pp.cluster_name.toLowerCase().includes(q)) ||
                                      (pp.tap_name && pp.tap_name.toLowerCase().includes(q)) ||
                                      (pp.area_name && pp.area_name.toLowerCase().includes(q));
                        if (!match) return false;
                    }
                    return true;
                },

                get filteredPrices() {
                    return this.allPrices.filter(pp => this.isPriceVisible(pp));
                },

                // Modal Cascading Territory
                get modalClusterOptions() {
                    if (!this.priceForm.area_id) return this.allClusters;
                    return this.allClusters.filter(c => String(c.area_id) === String(this.priceForm.area_id));
                },

                get modalTapOptions() {
                    if (this.priceForm.cluster_id) {
                        return this.allTaps.filter(t => String(t.cluster_id) === String(this.priceForm.cluster_id));
                    }
                    if (this.priceForm.area_id) {
                        return this.allTaps.filter(t => String(t.area_id) === String(this.priceForm.area_id));
                    }
                    return this.allTaps;
                },

                // Modal Openers
                openProductModal(item = null) {
                    if (item) {
                        this.isProductEdit = true;
                        this.productFormAction = '/master/products/update/' + item.id;
                        this.productForm = {
                            sku: item.sku || '',
                            item_code_telkomsel: item.item_code_telkomsel || '',
                            name: item.name || '',
                            category_id: item.category_id ? String(item.category_id) : '',
                            denom: item.denom || '',
                            description: item.description || '',
                            tracking_method: item.tracking_method || 'QUANTITY',
                            cost_price: item.cost_price || 0,
                            selling_price: item.selling_price || 0,
                            special_price: item.special_price || 0,
                            uom: item.uom || 'PCS',
                            min_stock: item.min_stock || 0,
                            tax_alias: item.tax_alias || '',
                            activation_date: item.activation_date || '',
                            expired_date: item.expired_date || '',
                            is_consignment: Boolean(item.is_consignment),
                            is_virtual: Boolean(item.is_virtual),
                            is_ppn: item.is_ppn !== undefined ? Boolean(item.is_ppn) : true,
                            status: item.status || 'ACTIVE',
                        };
                    } else {
                        this.isProductEdit = false;
                        this.productFormAction = '{{ route('master.products.store') }}';
                        this.productForm = {
                            sku: '', item_code_telkomsel: '', name: '', category_id: '', denom: '',
                            description: '', tracking_method: 'QUANTITY', cost_price: 0, selling_price: 0,
                            special_price: 0, uom: 'PCS', min_stock: 0, tax_alias: '',
                            activation_date: '', expired_date: '', is_consignment: false,
                            is_virtual: false, is_ppn: true, status: 'ACTIVE',
                        };
                    }
                    this.showProductModal = true;
                    this.refreshIcons();
                },

                openTypeModal(item = null) {
                    if (item) {
                        this.isTypeEdit = true;
                        this.typeFormAction = '/master/product-types/update/' + item.id;
                        this.typeForm = {
                            code: item.code || '',
                            name: item.name || '',
                            description: item.description || '',
                            status: item.status || 'ACTIVE',
                        };
                    } else {
                        this.isTypeEdit = false;
                        this.typeFormAction = '{{ route('master.product-types.store') }}';
                        this.typeForm = { code: '', name: '', description: '', status: 'ACTIVE' };
                    }
                    this.showTypeModal = true;
                },

                openPriceModal(item = null) {
                    if (item) {
                        this.isPriceEdit = true;
                        this.priceFormAction = '/master/product-prices/update/' + item.id;
                        this.priceForm = {
                            product_id: String(item.product_id),
                            area_id: item.area_id ? String(item.area_id) : '',
                            cluster_id: item.cluster_id ? String(item.cluster_id) : '',
                            tap_id: item.tap_id ? String(item.tap_id) : '',
                            cost_price: item.cost_price || 0,
                            selling_price: item.selling_price || 0,
                            special_price: item.special_price || 0,
                            status: item.status || 'ACTIVE',
                        };
                    } else {
                        this.isPriceEdit = false;
                        this.priceFormAction = '{{ route('master.product-prices.store') }}';
                        this.priceForm = {
                            product_id: '', area_id: '', cluster_id: '', tap_id: '',
                            cost_price: 0, selling_price: 0, special_price: 0, status: 'ACTIVE',
                        };
                    }
                    this.showPriceModal = true;
                },

                autoFillDefaultPrice() {
                    if (this.isPriceEdit || !this.priceForm.product_id) return;
                    const p = this.allProducts.find(x => String(x.id) === String(this.priceForm.product_id));
                    if (p) {
                        this.priceForm.cost_price = p.cost_price || 0;
                        this.priceForm.selling_price = p.selling_price || 0;
                        this.priceForm.special_price = p.special_price || 0;
                    }
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
