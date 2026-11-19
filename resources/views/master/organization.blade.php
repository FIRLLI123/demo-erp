<x-app-layout>
    <x-slot name="title">Master Organisasi Distribusi</x-slot>
    <x-slot name="headerTitle">Master Hirarki Organisasi</x-slot>
    <x-slot name="badge">Area > Cluster > TAP > Warehouse</x-slot>

    <div x-data="organizationMasterApp()" class="pb-12">
        <!-- Hierarchy Header Card -->
        <div class="mb-8 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white p-6 rounded-2xl shadow-lg relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-rose-500/10 rounded-full blur-3xl"></div>
            <div class="max-w-2xl">
                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30 uppercase tracking-wider">
                    Struktur Hirarki Distribusi
                </span>
                <h2 class="text-xl font-extrabold mt-2 tracking-tight">Area &rarr; Cluster &rarr; TAP &rarr; Warehouse</h2>
                <!-- <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                    Struktur berjenjang dari Area memayungi Cluster, Cluster memayungi TAP (Telkomsel Authorized Partner), dan setiap TAP menaungi unit-unit Gudang fisik operasional (Gudang Utama, Persediaan Buffer, dan Kanvas Sales).
                </p> -->
            </div>

            <!-- Visual Flow Breadcrumbs -->
            <div class="mt-6 flex flex-wrap items-center gap-2 text-xs font-bold">
                <span class="px-3 py-1.5 rounded-xl bg-slate-800 border border-slate-700 text-white flex items-center gap-1.5">
                    <i data-lucide="map" class="w-3.5 h-3.5 text-cyan-400"></i> AREA ({{ count($areas) }})
                </span>
                <i data-lucide="chevron-right" class="w-4 h-4 text-slate-500"></i>
                <span class="px-3 py-1.5 rounded-xl bg-slate-800 border border-slate-700 text-white flex items-center gap-1.5">
                    <i data-lucide="grid" class="w-3.5 h-3.5 text-amber-400"></i> CLUSTER ({{ count($clusters) }})
                </span>
                <i data-lucide="chevron-right" class="w-4 h-4 text-slate-500"></i>
                <span class="px-3 py-1.5 rounded-xl bg-slate-800 border border-slate-700 text-white flex items-center gap-1.5">
                    <i data-lucide="building-2" class="w-3.5 h-3.5 text-rose-400"></i> TAP ({{ count($taps) }})
                </span>
                <i data-lucide="chevron-right" class="w-4 h-4 text-slate-500"></i>
                <span class="px-3 py-1.5 rounded-xl bg-slate-800 border border-slate-700 text-white flex items-center gap-1.5">
                    <i data-lucide="warehouse" class="w-3.5 h-3.5 text-emerald-400"></i> WAREHOUSE ({{ count($warehouses) }})
                </span>
            </div>
        </div>

        <!-- Tabbed Management -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            
            <!-- Tab Navigation Buttons -->
            <div class="border-b border-slate-200/80 px-6 pt-4 flex gap-6 text-xs font-bold overflow-x-auto">
                <button @click="activeTab = 'areas'" 
                        :class="activeTab === 'areas' ? 'text-rose-600 border-b-2 border-rose-600 pb-3 font-extrabold' : 'text-slate-500 hover:text-slate-800 pb-3 cursor-pointer'">
                    1. Data Area ({{ count($areas) }})
                </button>
                <button @click="activeTab = 'clusters'" 
                        :class="activeTab === 'clusters' ? 'text-rose-600 border-b-2 border-rose-600 pb-3 font-extrabold' : 'text-slate-500 hover:text-slate-800 pb-3 cursor-pointer'">
                    2. Data Cluster (<span x-text="filteredClustersCount"></span>/{{ count($clusters) }})
                </button>
                <button @click="activeTab = 'taps'" 
                        :class="activeTab === 'taps' ? 'text-rose-600 border-b-2 border-rose-600 pb-3 font-extrabold' : 'text-slate-500 hover:text-slate-800 pb-3 cursor-pointer'">
                    3. Data TAP (<span x-text="filteredTapsCount"></span>/{{ count($taps) }})
                </button>
                <button @click="activeTab = 'warehouses'" 
                        :class="activeTab === 'warehouses' ? 'text-rose-600 border-b-2 border-rose-600 pb-3 font-extrabold' : 'text-slate-500 hover:text-slate-800 pb-3 cursor-pointer'">
                    4. Data Gudang / Warehouses (<span x-text="filteredWarehousesCount"></span>/{{ count($warehouses) }})
                </button>
            </div>

            <!-- ============================================================= -->
            <!-- TAB 1: AREAS                                                  -->
            <!-- ============================================================= -->
            <div x-show="activeTab === 'areas'" class="p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Daftar Wilayah Operasional (Area)</h3>
                        <p class="text-xs text-slate-500">Tingkat wilayah tertinggi yang memayungi beberapa cluster regional.</p>
                    </div>
                    <x-search-input model="searchArea" placeholder="Cari nama / kode area..." width="w-full sm:w-64" accent="cyan" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($areas as $ar)
                        <div x-show="isAreaVisible({{ json_encode($ar) }})"
                             x-transition
                             class="p-5 rounded-2xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:shadow-md transition-all flex items-center justify-between">
                            <div class="flex items-center gap-3.5">
                                <div class="w-11 h-11 rounded-xl bg-cyan-100 text-cyan-800 flex items-center justify-center font-bold">
                                    <i data-lucide="map" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <span class="font-mono text-[11px] font-bold text-slate-400 block">{{ $ar->code }}</span>
                                    <h4 class="font-extrabold text-slate-900 text-base">{{ $ar->name }}</h4>
                                </div>
                            </div>
                            <x-status-badge :status="$ar->status" />
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- TAB 2: CLUSTERS (FILTER AREA INSTAN CLIENT-SIDE)             -->
            <!-- ============================================================= -->
            <div x-show="activeTab === 'clusters'" class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Daftar Cluster Regional</h3>
                        <p class="text-xs text-slate-500">Sub-wilayah di bawah Area yang menaungi kantor-kantor TAP.</p>
                    </div>
                </div>

                <!-- KOMPONEN REUSABLE FILTER: HANYA AREA (CLIENT-SIDE) -->
                <x-filter-territory 
                    tab="clusters"
                    :show-area="true"
                    :show-cluster="false"
                    :show-tap="false"
                    :show-type="false"
                    :show-search="true"
                    search-placeholder="Cari cluster atau area..."
                    :areas="$areas"
                    :clusters="$clusters" />

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4" x-show="filteredClustersCount > 0">
                    @foreach($clusters as $cls)
                        <div x-show="isClusterVisible({{ json_encode($cls) }})" 
                             x-transition
                             class="p-4 rounded-2xl border border-slate-200 bg-white hover:shadow-md transition-all">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-mono text-xs font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                    {{ $cls->code }}
                                </span>
                                <x-status-badge :status="$cls->status" />
                            </div>
                            <h4 class="font-extrabold text-slate-900 text-sm mt-1">{{ $cls->name }}</h4>
                            <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                                <span>Area Induk:</span>
                                <span class="font-bold text-slate-800">{{ $cls->area_name }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Empty State jika hasil filter cluster nihil -->
                <div x-show="filteredClustersCount === 0" x-cloak>
                    <x-empty-state 
                        icon="filter-x"
                        title="Cluster Tidak Ditemukan"
                        subtitle="Tidak ada data cluster untuk filter area terpilih."
                        action-click="resetFilter()"
                        action-label="Reset Filter Area"
                        action-icon="rotate-ccw" />
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- TAB 3: TAPs (FILTER AREA & CLUSTER INSTAN + PAGINASI)        -->
            <!-- ============================================================= -->
            <div x-show="activeTab === 'taps'" class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Daftar TAP (Telkomsel Authorized Partner)</h3>
                        <p class="text-xs text-slate-500">Kantor operasional cabang. Setiap TAP memiliki 3 gudang fisik (MAIN, INV, SALES).</p>
                    </div>
                </div>

                <!-- KOMPONEN REUSABLE FILTER: AREA & CLUSTER (CLIENT-SIDE) -->
                <x-filter-territory 
                    tab="taps"
                    :show-area="true"
                    :show-cluster="true"
                    :show-tap="false"
                    :show-type="false"
                    :show-search="true"
                    search-placeholder="Cari nama TAP, kode, alamat..."
                    :areas="$areas"
                    :taps="$taps" />

                <div class="overflow-x-auto rounded-2xl border border-slate-200" x-show="filteredTapsCount > 0">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">Kode TAP</th>
                                <th class="py-3 px-4">Nama Kantor TAP</th>
                                <th class="py-3 px-4">Cluster & Area</th>
                                <th class="py-3 px-4">Alamat Kantor</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-center">Gudang Naungan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @foreach($taps as $tap)
                                <tr x-show="paginatedTapIds.includes({{ $tap->id }})"
                                    x-transition
                                    class="hover:bg-slate-50/60 transition-colors">
                                    <td class="py-3.5 px-4 font-mono font-bold text-slate-900">{{ $tap->code }}</td>
                                    <td class="py-3.5 px-4 font-bold text-slate-800">{{ $tap->name }}</td>
                                    <td class="py-3.5 px-4">
                                        <div class="flex flex-col gap-0.5">
                                            <span class="font-bold text-slate-800">{{ $tap->cluster_name }}</span>
                                            <span class="text-[10px] text-slate-400 font-medium">{{ $tap->area_name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-500">{{ $tap->address }}</td>
                                    <td class="py-3.5 px-4 text-center">
                                        <x-master-status-toggle entity="taps" :id="$tap->id" :name="$tap->name" :active="$tap->status === 'ACTIVE'" />
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-flex gap-1 text-[10px] font-mono font-bold">
                                            <span class="px-1.5 py-0.5 bg-emerald-100 text-emerald-800 rounded border border-emerald-200" title="Gudang Utama">MAIN</span>
                                            <span class="px-1.5 py-0.5 bg-blue-100 text-blue-800 rounded border border-blue-200" title="Gudang Persediaan">INV</span>
                                            <span class="px-1.5 py-0.5 bg-purple-100 text-purple-800 rounded border border-purple-200" title="Gudang Kanvas Sales">SALES</span>
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Pagination Bar TAP -->
                    <div class="p-4 border-t border-slate-200 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-500">
                        <div class="flex items-center gap-3">
                            <div>
                                Menampilkan <strong class="text-slate-800" x-text="tapPaginationStart"></strong> - <strong class="text-slate-800" x-text="tapPaginationEnd"></strong> dari <strong class="text-slate-800" x-text="filteredTapsCount"></strong> data TAP
                            </div>
                            <span class="text-slate-300">|</span>
                            <label class="flex items-center gap-1.5 text-xs">
                                <span>Baris:</span>
                                <select x-model="tapPerPage" @change="tapPage = 1" class="border border-slate-200 rounded-lg px-2 py-1 bg-white text-xs font-semibold text-slate-700 focus:outline-none focus:ring-1 focus:ring-rose-500">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="ALL">Semua</option>
                                </select>
                            </label>
                        </div>

                        <!-- Page Navigation Buttons -->
                        <div class="flex items-center gap-1" x-show="totalTapPages > 1">
                            <button type="button" 
                                    @click="tapPage = Math.max(1, tapPage - 1)" 
                                    :disabled="tapPage === 1"
                                    class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed text-xs font-bold transition-all flex items-center gap-1">
                                <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                                <span>Prev</span>
                            </button>

                            <template x-for="(p, idx) in displayedTapPages" :key="idx">
                                <button type="button"
                                        @click="if (p !== '...') tapPage = p"
                                        :disabled="p === '...'"
                                        :class="tapPage === p ? 'bg-rose-600 text-white font-extrabold border-rose-600 shadow-sm' : (p === '...' ? 'cursor-default border-transparent text-slate-400' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100 font-semibold')"
                                        class="min-w-[30px] h-7 px-1.5 rounded-lg border text-xs flex items-center justify-center transition-all"
                                        x-text="p">
                                </button>
                            </template>

                            <button type="button" 
                                    @click="tapPage = Math.min(totalTapPages, tapPage + 1)" 
                                    :disabled="tapPage === totalTapPages"
                                    class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed text-xs font-bold transition-all flex items-center gap-1">
                                <span>Next</span>
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty State jika hasil filter TAP nihil -->
                <div x-show="filteredTapsCount === 0" x-cloak>
                    <x-empty-state 
                        icon="filter-x"
                        title="Kantor TAP Tidak Ditemukan"
                        subtitle="Tidak ada data kantor TAP untuk filter wilayah yang Anda pilih."
                        action-click="resetFilter()"
                        action-label="Reset Filter TAP"
                        action-icon="rotate-ccw" />
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- TAB 4: WAREHOUSES (FILTER, TAMBAH, EDIT & PAGINASI)           -->
            <!-- ============================================================= -->
            <div x-show="activeTab === 'warehouses'" class="p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                            <i data-lucide="warehouse" class="w-4 h-4 text-rose-600"></i>
                            <span>Daftar Gudang Fisik (Warehouses)</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Unit gudang penyimpanan stok fisik per TAP (Gudang Utama, Persediaan Buffer, dan Kanvas Sales).</p>
                    </div>

                    <!-- Tombol Tambah Gudang Baru -->
                    <button @click="openCreateModal()" 
                            type="button" 
                            class="px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs flex items-center gap-1.5 shadow-sm shadow-rose-200 transition-all cursor-pointer">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Tambah Gudang Baru</span>
                    </button>
                </div>

                <!-- KOMPONEN REUSABLE FILTER LENGKAP: AREA, CLUSTER, TAP, TIPE (CLIENT-SIDE) -->
                <x-filter-territory 
                    tab="warehouses"
                    :show-area="true"
                    :show-cluster="true"
                    :show-tap="true"
                    :show-type="true"
                    :show-search="true"
                    search-placeholder="Cari kode gudang, nama, TAP, sales..."
                    :areas="$areas"
                    :warehouses="$warehouses" />

                <!-- Warehouses Table -->
                <div class="overflow-x-auto rounded-2xl border border-slate-200" x-show="filteredWarehousesCount > 0">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">Kode Gudang</th>
                                <th class="py-3 px-4">Nama Gudang</th>
                                <th class="py-3 px-4 text-center">Tipe</th>
                                <th class="py-3 px-4">TAP Induk</th>
                                <th class="py-3 px-4">Cluster & Area</th>
                                <th class="py-3 px-4">Alamat / Sales Kanvas</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @foreach($warehouses as $wh)
                                <tr x-show="paginatedWarehouseIds.includes({{ $wh->id }})"
                                    x-transition
                                    class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                        {{ $wh->code }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="font-extrabold text-slate-800 block text-xs">{{ $wh->name }}</span>
                                        <span class="text-[10px] text-slate-400 font-mono">ID: #{{ str_pad($wh->id, 3, '0', STR_PAD_LEFT) }}</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        @if($wh->type === 'MAIN')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                MAIN (Utama)
                                            </span>
                                        @elseif($wh->type === 'INVENTORY')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                                INVENTORY (Buffer)
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                                SALES (Kanvas)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="font-bold text-slate-800 block">{{ $wh->tap_name }}</span>
                                        <span class="font-mono text-[10px] text-slate-400">{{ $wh->tap_code }}</span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="flex flex-col gap-0.5">
                                            <span class="font-bold text-slate-800">{{ $wh->cluster_name }}</span>
                                            <span class="text-[10px] text-slate-400 font-medium">{{ $wh->area_name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if($wh->type === 'SALES' && $wh->sales_name)
                                            <div class="flex items-center gap-1.5">
                                                <span class="px-1.5 py-0.5 rounded bg-purple-50 text-purple-700 font-mono text-[10px] font-bold border border-purple-200">
                                                    {{ $wh->sales_code }}
                                                </span>
                                                <span class="font-bold text-slate-800">{{ $wh->sales_name }}</span>
                                            </div>
                                            <span class="text-[10px] text-slate-400 block mt-0.5 truncate max-w-[200px]">{{ $wh->address }}</span>
                                        @else
                                            <span class="text-slate-600 text-[11px] block truncate max-w-[220px]">{{ $wh->address ?? '-' }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <x-master-status-toggle entity="warehouses" :id="$wh->id" :name="$wh->name" :active="$wh->status === 'ACTIVE'" />
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <button @click="openEditModal({{ json_encode($wh) }})" 
                                                type="button" 
                                                class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 text-slate-600 font-bold text-xs flex items-center gap-1 mx-auto transition-all cursor-pointer" 
                                                title="Edit Gudang">
                                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                            <span>Edit</span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Pagination Bar Gudang -->
                    <div class="p-4 border-t border-slate-200 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-500">
                        <div class="flex items-center gap-3">
                            <div>
                                Menampilkan <strong class="text-slate-800" x-text="whPaginationStart"></strong> - <strong class="text-slate-800" x-text="whPaginationEnd"></strong> dari <strong class="text-slate-800" x-text="filteredWarehousesCount"></strong> data Gudang
                            </div>
                            <span class="text-slate-300">|</span>
                            <label class="flex items-center gap-1.5 text-xs">
                                <span>Baris:</span>
                                <select x-model="whPerPage" @change="whPage = 1" class="border border-slate-200 rounded-lg px-2 py-1 bg-white text-xs font-semibold text-slate-700 focus:outline-none focus:ring-1 focus:ring-rose-500">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="ALL">Semua</option>
                                </select>
                            </label>
                        </div>

                        <!-- Page Navigation Buttons -->
                        <div class="flex items-center gap-1" x-show="totalWhPages > 1">
                            <button type="button" 
                                    @click="whPage = Math.max(1, whPage - 1)" 
                                    :disabled="whPage === 1"
                                    class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed text-xs font-bold transition-all flex items-center gap-1">
                                <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                                <span>Prev</span>
                            </button>

                            <template x-for="(p, idx) in displayedWhPages" :key="idx">
                                <button type="button"
                                        @click="if (p !== '...') whPage = p"
                                        :disabled="p === '...'"
                                        :class="whPage === p ? 'bg-rose-600 text-white font-extrabold border-rose-600 shadow-sm' : (p === '...' ? 'cursor-default border-transparent text-slate-400' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100 font-semibold')"
                                        class="min-w-[30px] h-7 px-1.5 rounded-lg border text-xs flex items-center justify-center transition-all"
                                        x-text="p">
                                </button>
                            </template>

                            <button type="button" 
                                    @click="whPage = Math.min(totalWhPages, whPage + 1)" 
                                    :disabled="whPage === totalWhPages"
                                    class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed text-xs font-bold transition-all flex items-center gap-1">
                                <span>Next</span>
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty State jika hasil filter Gudang nihil -->
                <div x-show="filteredWarehousesCount === 0" x-cloak>
                    <x-empty-state 
                        icon="filter-x"
                        title="Gudang Tidak Ditemukan"
                        subtitle="Tidak ada gudang fisik yang sesuai dengan kombinasi filter wilayah dan tipe gudang ini."
                        action-click="resetFilter()"
                        action-label="Reset Semua Filter"
                        action-icon="rotate-ccw" />
                </div>
            </div>

        </div>

        <!-- ============================================================= -->
        <!-- MODAL FORM: TAMBAH & EDIT GUDANG (WAREHOUSE)                 -->
        <!-- ============================================================= -->
        <div x-show="showWarehouseModal" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto" 
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true">
            <!-- Backdrop -->
            <div x-show="showWarehouseModal" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
                 @click="closeWarehouseModal()"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div x-show="showWarehouseModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200"
                     @click.away="closeWarehouseModal()">
                    
                    <!-- Header Modal -->
                    <div class="bg-gradient-to-r from-slate-900 to-slate-800 text-white px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-rose-500/20 text-rose-400 flex items-center justify-center border border-rose-500/30">
                                <i data-lucide="warehouse" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-white tracking-tight" x-text="isWarehouseEdit ? 'Edit Data Gudang' : 'Tambah Gudang Baru'"></h3>
                                <p class="text-[11px] text-slate-300">Unit penyimpanan fisik barang di bawah kantor TAP</p>
                            </div>
                        </div>
                        <button type="button" @click="closeWarehouseModal()" class="text-slate-400 hover:text-white transition-colors cursor-pointer">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <!-- Body Modal / Form -->
                    <form :action="warehouseFormAction" method="POST" class="p-6 space-y-4">
                        @csrf

                        <!-- Tipe Gudang (Segmented Buttons) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Tipe Gudang <span class="text-rose-500">*</span>
                            </label>
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" 
                                        @click="warehouseForm.type = 'MAIN'; autoGenerateCode()"
                                        :class="warehouseForm.type === 'MAIN' ? 'bg-emerald-600 text-white font-bold border-emerald-600 shadow-sm' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                                        class="py-2.5 px-2 rounded-xl border text-xs flex flex-col items-center justify-center transition-all cursor-pointer">
                                    <span class="font-extrabold">MAIN</span>
                                    <span class="text-[9px] opacity-80">Gudang Utama TAP</span>
                                </button>
                                <button type="button" 
                                        @click="warehouseForm.type = 'INVENTORY'; autoGenerateCode()"
                                        :class="warehouseForm.type === 'INVENTORY' ? 'bg-blue-600 text-white font-bold border-blue-600 shadow-sm' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                                        class="py-2.5 px-2 rounded-xl border text-xs flex flex-col items-center justify-center transition-all cursor-pointer">
                                    <span class="font-extrabold">INVENTORY</span>
                                    <span class="text-[9px] opacity-80">Buffer Persediaan</span>
                                </button>
                                <button type="button" 
                                        @click="warehouseForm.type = 'SALES'; autoGenerateCode()"
                                        :class="warehouseForm.type === 'SALES' ? 'bg-purple-600 text-white font-bold border-purple-600 shadow-sm' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                                        class="py-2.5 px-2 rounded-xl border text-xs flex flex-col items-center justify-center transition-all cursor-pointer">
                                    <span class="font-extrabold">SALES</span>
                                    <span class="text-[9px] opacity-80">Kanvas Sales</span>
                                </button>
                            </div>
                            <input type="hidden" name="type" :value="warehouseForm.type">
                        </div>

                        <!-- TAP Induk -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Kantor TAP Induk <span class="text-rose-500">*</span>
                            </label>
                            <select name="tap_id" 
                                    x-model="warehouseForm.tap_id" 
                                    @change="autoGenerateCode()"
                                    required 
                                    class="w-full text-xs font-semibold text-slate-800 border border-slate-200 rounded-xl px-3 py-2.5 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition-all">
                                <option value="" disabled>-- Pilih Kantor TAP --</option>
                                @foreach($taps as $tp)
                                    <option value="{{ $tp->id }}">
                                        {{ $tp->code }} - {{ $tp->name }} ({{ $tp->cluster_name }} / {{ $tp->area_name }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Sales Pemegang (Hanya jika tipe SALES) -->
                        <div x-show="warehouseForm.type === 'SALES'" x-cloak>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Sales Force Pemegang Gudang Kanvas <span class="text-rose-500">*</span>
                            </label>
                            <select name="sales_id" 
                                    x-model="warehouseForm.sales_id"
                                    @change="autoGenerateCode()"
                                    class="w-full text-xs font-semibold text-slate-800 border border-slate-200 rounded-xl px-3 py-2.5 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                                <option value="">-- Pilih Sales Force --</option>
                                @foreach($salesList as $sl)
                                    <option value="{{ $sl->id }}">
                                        {{ $sl->code }} - {{ $sl->name }} ({{ $sl->sales_type ?? 'SALES' }} - {{ $sl->sales_level ?? 'SF' }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[10px] text-slate-400 mt-1">Gudang bergerak ini akan menjadi wadah stok kanvas bawaan sales saat kunjungan outlet.</p>
                        </div>

                        <!-- Kode Gudang -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs font-bold text-slate-700">
                                    Kode Gudang <span class="text-rose-500">*</span>
                                </label>
                                <button type="button" @click="autoGenerateCode(true)" class="text-[10px] font-bold text-rose-600 hover:underline cursor-pointer">
                                    Generate Kode Otomatis
                                </button>
                            </div>
                            <input type="text" 
                                   name="code" 
                                   x-model="warehouseForm.code" 
                                   required 
                                   placeholder="Contoh: WH-MAIN-TAJUR"
                                   class="w-full text-xs font-mono font-bold text-slate-800 border border-slate-200 rounded-xl px-3 py-2.5 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 uppercase transition-all">
                        </div>

                        <!-- Nama Gudang -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Nama Gudang <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   x-model="warehouseForm.name" 
                                   required 
                                   placeholder="Contoh: Gudang Utama TAP Tajur"
                                   class="w-full text-xs font-bold text-slate-800 border border-slate-200 rounded-xl px-3 py-2.5 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition-all">
                        </div>

                        <!-- Alamat / Lokasi Fisik -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Alamat / Lokasi Gudang
                            </label>
                            <textarea name="address" 
                                      x-model="warehouseForm.address" 
                                      rows="2" 
                                      placeholder="Alamat fisik gudang atau keterangan motor kanvas..."
                                      class="w-full text-xs text-slate-800 border border-slate-200 rounded-xl px-3 py-2 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition-all"></textarea>
                        </div>

                        <!-- Status Operasional -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Status Operasional <span class="text-rose-500">*</span>
                            </label>
                            <select name="status" 
                                    x-model="warehouseForm.status" 
                                    required 
                                    class="w-full text-xs font-bold text-slate-800 border border-slate-200 rounded-xl px-3 py-2.5 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition-all">
                                <option value="ACTIVE">ACTIVE (Aktif Operasional)</option>
                                <option value="INACTIVE">INACTIVE (Nonaktif / Ditutup)</option>
                            </select>
                        </div>

                        <!-- Modal Footer -->
                        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                            <button type="button" 
                                    @click="closeWarehouseModal()" 
                                    class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 text-xs font-bold transition-all cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-sm shadow-rose-200 flex items-center gap-1.5 transition-all cursor-pointer">
                                <i data-lucide="check" class="w-4 h-4"></i>
                                <span x-text="isWarehouseEdit ? 'Simpan Perubahan' : 'Tambahkan Gudang'"></span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function organizationMasterApp() {
            return {
                activeTab: 'warehouses',
                selectedArea: '',
                selectedCluster: '',
                selectedTap: '',
                selectedType: 'ALL',
                searchQuery: '',
                searchArea: '',

                allClusters: @json($clusters),
                allTaps: @json($taps),
                allWarehouses: @json($warehouses),
                allSales: @json($salesList ?? []),

                // Paginasi TAP
                tapPage: 1,
                tapPerPage: 10,

                // Paginasi Gudang
                whPage: 1,
                whPerPage: 10,

                // State Modal Warehouse
                showWarehouseModal: false,
                isWarehouseEdit: false,
                warehouseFormAction: '',
                warehouseForm: {
                    id: null,
                    code: '',
                    name: '',
                    type: 'MAIN',
                    tap_id: '',
                    sales_id: '',
                    address: '',
                    status: 'ACTIVE'
                },

                init() {
                    // Cek jika ada parameter tab di URL saat pertama buka
                    const params = new URLSearchParams(window.location.search);
                    if (params.has('tab')) {
                        this.activeTab = params.get('tab');
                    }

                    // Watch pagination & per-page changes to re-init Lucide icons
                    this.$watch('tapPage', () => this.refreshIcons());
                    this.$watch('whPage', () => this.refreshIcons());
                    this.$watch('tapPerPage', () => { this.tapPage = 1; this.refreshIcons(); });
                    this.$watch('whPerPage', () => { this.whPage = 1; this.refreshIcons(); });

                    this.refreshIcons();
                },

                // Opsi Cluster Reaktif Sesuai Area yang Dipilih
                get clusterOptions() {
                    if (!this.selectedArea) return this.allClusters;
                    return this.allClusters.filter(c => String(c.area_id) === String(this.selectedArea));
                },

                // Opsi TAP Reaktif Sesuai Cluster / Area yang Dipilih
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
                    this.tapPage = 1;
                    this.whPage = 1;
                    this.refreshIcons();
                },

                onClusterChange() {
                    this.selectedTap = '';
                    this.tapPage = 1;
                    this.whPage = 1;
                    this.refreshIcons();
                },

                onSearchChange() {
                    this.tapPage = 1;
                    this.whPage = 1;
                    this.refreshIcons();
                },

                resetFilter() {
                    this.selectedArea = '';
                    this.selectedCluster = '';
                    this.selectedTap = '';
                    this.selectedType = 'ALL';
                    this.searchQuery = '';
                    this.searchArea = '';
                    this.tapPage = 1;
                    this.whPage = 1;
                    this.refreshIcons();
                },

                hasActiveFilter() {
                    return Boolean(
                        this.selectedArea || 
                        this.selectedCluster || 
                        this.selectedTap || 
                        (this.selectedType && this.selectedType !== 'ALL') ||
                        this.searchQuery
                    );
                },

                // Filter Rules
                isAreaVisible(ar) {
                    if (!this.searchArea) return true;
                    const q = this.searchArea.toLowerCase();
                    return (ar.name && ar.name.toLowerCase().includes(q)) ||
                           (ar.code && ar.code.toLowerCase().includes(q));
                },

                isClusterVisible(cls) {
                    if (this.selectedArea && String(cls.area_id) !== String(this.selectedArea)) return false;
                    if (this.searchQuery) {
                        const q = this.searchQuery.toLowerCase();
                        const match = (cls.name && cls.name.toLowerCase().includes(q)) ||
                                      (cls.code && cls.code.toLowerCase().includes(q)) ||
                                      (cls.area_name && cls.area_name.toLowerCase().includes(q));
                        if (!match) return false;
                    }
                    return true;
                },

                isTapVisible(tap) {
                    if (this.selectedArea && String(tap.area_id) !== String(this.selectedArea)) return false;
                    if (this.selectedCluster && String(tap.cluster_id) !== String(this.selectedCluster)) return false;
                    if (this.searchQuery) {
                        const q = this.searchQuery.toLowerCase();
                        const match = (tap.name && tap.name.toLowerCase().includes(q)) ||
                                      (tap.code && tap.code.toLowerCase().includes(q)) ||
                                      (tap.cluster_name && tap.cluster_name.toLowerCase().includes(q)) ||
                                      (tap.area_name && tap.area_name.toLowerCase().includes(q)) ||
                                      (tap.address && tap.address.toLowerCase().includes(q));
                        if (!match) return false;
                    }
                    return true;
                },

                isWarehouseVisible(wh) {
                    if (this.selectedArea && String(wh.area_id) !== String(this.selectedArea)) return false;
                    if (this.selectedCluster && String(wh.cluster_id) !== String(this.selectedCluster)) return false;
                    if (this.selectedTap && String(wh.tap_id) !== String(this.selectedTap)) return false;
                    if (this.selectedType !== 'ALL' && wh.type !== this.selectedType) return false;
                    if (this.searchQuery) {
                        const q = this.searchQuery.toLowerCase();
                        const match = (wh.name && wh.name.toLowerCase().includes(q)) ||
                                      (wh.code && wh.code.toLowerCase().includes(q)) ||
                                      (wh.tap_name && wh.tap_name.toLowerCase().includes(q)) ||
                                      (wh.cluster_name && wh.cluster_name.toLowerCase().includes(q)) ||
                                      (wh.area_name && wh.area_name.toLowerCase().includes(q)) ||
                                      (wh.sales_name && wh.sales_name.toLowerCase().includes(q)) ||
                                      (wh.address && wh.address.toLowerCase().includes(q));
                        if (!match) return false;
                    }
                    return true;
                },

                // Real-time Filtered Collections
                get filteredClusters() {
                    return this.allClusters.filter(c => this.isClusterVisible(c));
                },

                get filteredTaps() {
                    return this.allTaps.filter(t => this.isTapVisible(t));
                },

                get filteredWarehouses() {
                    return this.allWarehouses.filter(w => this.isWarehouseVisible(w));
                },

                // Real-time Counters
                get filteredClustersCount() {
                    return this.filteredClusters.length;
                },

                get filteredTapsCount() {
                    return this.filteredTaps.length;
                },

                get filteredWarehousesCount() {
                    return this.filteredWarehouses.length;
                },

                // =========================================================
                // LOGIKA PAGINASI TAP
                // =========================================================
                get totalTapPages() {
                    if (this.tapPerPage === 'ALL') return 1;
                    const perPage = parseInt(this.tapPerPage, 10) || 10;
                    return Math.max(1, Math.ceil(this.filteredTapsCount / perPage));
                },

                get paginatedTapIds() {
                    if (this.tapPerPage === 'ALL') {
                        return this.filteredTaps.map(t => t.id);
                    }
                    const perPage = parseInt(this.tapPerPage, 10) || 10;
                    const start = (this.tapPage - 1) * perPage;
                    return this.filteredTaps.slice(start, start + perPage).map(t => t.id);
                },

                get tapPaginationStart() {
                    if (this.filteredTapsCount === 0) return 0;
                    if (this.tapPerPage === 'ALL') return 1;
                    const perPage = parseInt(this.tapPerPage, 10) || 10;
                    return (this.tapPage - 1) * perPage + 1;
                },

                get tapPaginationEnd() {
                    if (this.tapPerPage === 'ALL') return this.filteredTapsCount;
                    const perPage = parseInt(this.tapPerPage, 10) || 10;
                    return Math.min(this.tapPage * perPage, this.filteredTapsCount);
                },

                get displayedTapPages() {
                    return this.generatePaginationPages(this.tapPage, this.totalTapPages);
                },

                // =========================================================
                // LOGIKA PAGINASI GUDANG (WAREHOUSE)
                // =========================================================
                get totalWhPages() {
                    if (this.whPerPage === 'ALL') return 1;
                    const perPage = parseInt(this.whPerPage, 10) || 10;
                    return Math.max(1, Math.ceil(this.filteredWarehousesCount / perPage));
                },

                get paginatedWarehouseIds() {
                    if (this.whPerPage === 'ALL') {
                        return this.filteredWarehouses.map(w => w.id);
                    }
                    const perPage = parseInt(this.whPerPage, 10) || 10;
                    const start = (this.whPage - 1) * perPage;
                    return this.filteredWarehouses.slice(start, start + perPage).map(w => w.id);
                },

                get whPaginationStart() {
                    if (this.filteredWarehousesCount === 0) return 0;
                    if (this.whPerPage === 'ALL') return 1;
                    const perPage = parseInt(this.whPerPage, 10) || 10;
                    return (this.whPage - 1) * perPage + 1;
                },

                get whPaginationEnd() {
                    if (this.whPerPage === 'ALL') return this.filteredWarehousesCount;
                    const perPage = parseInt(this.whPerPage, 10) || 10;
                    return Math.min(this.whPage * perPage, this.filteredWarehousesCount);
                },

                get displayedWhPages() {
                    return this.generatePaginationPages(this.whPage, this.totalWhPages);
                },

                // Helper Pagination Array dengan Ellipsis (...)
                generatePaginationPages(current, total) {
                    if (total <= 7) {
                        return Array.from({ length: total }, (_, i) => i + 1);
                    }
                    if (current <= 4) {
                        return [1, 2, 3, 4, 5, '...', total];
                    }
                    if (current >= total - 3) {
                        return [1, '...', total - 4, total - 3, total - 2, total - 1, total];
                    }
                    return [1, '...', current - 1, current, current + 1, '...', total];
                },

                // =========================================================
                // MODAL TAMBAH & EDIT GUDANG
                // =========================================================
                openCreateModal() {
                    this.isWarehouseEdit = false;
                    this.warehouseFormAction = "{{ route('master.warehouses.store') }}";
                    this.warehouseForm = {
                        id: null,
                        code: '',
                        name: '',
                        type: 'MAIN',
                        tap_id: this.selectedTap || (this.allTaps.length > 0 ? this.allTaps[0].id : ''),
                        sales_id: '',
                        address: '',
                        status: 'ACTIVE'
                    };
                    this.autoGenerateCode(true);
                    this.showWarehouseModal = true;
                    this.refreshIcons();
                },

                openEditModal(wh) {
                    this.isWarehouseEdit = true;
                    this.warehouseFormAction = "/master/warehouses/update/" + wh.id;
                    this.warehouseForm = {
                        id: wh.id,
                        code: wh.code,
                        name: wh.name,
                        type: wh.type,
                        tap_id: wh.tap_id,
                        sales_id: wh.sales_id || '',
                        address: wh.address || '',
                        status: wh.status || 'ACTIVE'
                    };
                    this.showWarehouseModal = true;
                    this.refreshIcons();
                },

                closeWarehouseModal() {
                    this.showWarehouseModal = false;
                },

                autoGenerateCode(force = false) {
                    if (this.isWarehouseEdit && !force) return;

                    const tap = this.allTaps.find(t => String(t.id) === String(this.warehouseForm.tap_id));
                    const tapClean = tap ? tap.code.replace('TAP-', '').replace(/[^a-zA-Z0-9]/g, '') : 'TAP';

                    if (this.warehouseForm.type === 'MAIN') {
                        this.warehouseForm.code = 'WH-MAIN-' + tapClean;
                        if (!this.warehouseForm.name || this.warehouseForm.name.startsWith('Gudang ') || force) {
                            this.warehouseForm.name = 'Gudang Utama ' + (tap ? tap.name : '');
                        }
                    } else if (this.warehouseForm.type === 'INVENTORY') {
                        this.warehouseForm.code = 'WH-INV-' + tapClean;
                        if (!this.warehouseForm.name || this.warehouseForm.name.startsWith('Gudang ') || force) {
                            this.warehouseForm.name = 'Gudang Persediaan ' + (tap ? tap.name : '');
                        }
                    } else if (this.warehouseForm.type === 'SALES') {
                        const sales = this.allSales.find(s => String(s.id) === String(this.warehouseForm.sales_id));
                        const salesClean = sales ? sales.code.replace('SLS-', '').replace(/[^a-zA-Z0-9]/g, '') : 'CANVAS';
                        this.warehouseForm.code = 'WH-SLS-' + salesClean;
                        if (!this.warehouseForm.name || this.warehouseForm.name.startsWith('Gudang ') || force) {
                            this.warehouseForm.name = 'Gudang Kanvas ' + (sales ? sales.name : 'Sales');
                        }
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
