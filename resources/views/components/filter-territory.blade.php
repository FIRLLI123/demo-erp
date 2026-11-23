@props([
    'showArea' => true,
    'showCluster' => false,
    'showTap' => false,
    'showType' => false,
    'typeMode' => 'warehouse', // warehouse | customer | sales
    'salesTypes' => [],
    'showSearch' => true,
    'searchPlaceholder' => 'Cari data...',
    'modelSearch' => 'searchQuery',
    'areas' => [],
    'clusters' => [],
    'taps' => [],
    'warehouses' => [],
    'tab' => 'warehouses',
    'serverSide' => false,
    'action' => '',
    'customersCount' => 0,
    'totalCount' => 0,
    'showCount' => true,
])

<div class="bg-slate-50/80 border border-slate-200/90 rounded-2xl p-3.5 mb-5 shadow-2xs transition-all">
    @if($serverSide)
        <form method="GET" action="{{ $action }}" x-ref="filterForm">
            <input type="hidden" name="per_page" :value="perPage">
    @endif
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
        
        <!-- Deretan Filter Spinner / Dropdown -->
        <div class="flex flex-wrap items-center gap-2.5 flex-1">
            <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 flex items-center gap-1.5 mr-1">
                <i data-lucide="filter" class="w-3.5 h-3.5 text-rose-600"></i>
                <span>Filter:</span>
            </span>

            <!-- 1. SPINNER / DROPDOWN AREA -->
            @if($showArea)
                <div class="relative inline-flex items-center flex-1 sm:flex-initial min-w-[140px] sm:min-w-[165px]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-cyan-600">
                        <i data-lucide="map" class="w-3.5 h-3.5"></i>
                    </div>
                    <select @if($serverSide) name="area_id" @endif
                            x-model="selectedArea" 
                            @change="onAreaChange()"
                            :class="selectedArea ? 'border-rose-500 ring-2 ring-rose-500/10 text-rose-900 bg-rose-50/20' : 'border-slate-200 bg-white text-slate-800'"
                            class="w-full text-xs font-bold border rounded-xl pl-9 pr-8 py-2 focus:outline-none focus:border-rose-500 cursor-pointer shadow-2xs hover:border-slate-300 transition-all appearance-none">
                        <option value="">-- Semua Area --</option>
                        @foreach($areas as $ar)
                            <option value="{{ $ar->id }}">{{ $ar->name }} ({{ $ar->code }})</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                    </div>
                </div>
            @endif

            <!-- 2. SPINNER / DROPDOWN CLUSTER (Reaktif Sesuai Area) -->
            @if($showCluster)
                <div class="relative inline-flex items-center flex-1 sm:flex-initial min-w-[140px] sm:min-w-[170px]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-amber-600">
                        <i data-lucide="grid" class="w-3.5 h-3.5"></i>
                    </div>
                    <select @if($serverSide) name="cluster_id" @endif
                            x-model="selectedCluster" 
                            @change="onClusterChange()"
                            :class="selectedCluster ? 'border-rose-500 ring-2 ring-rose-500/10 text-rose-900 bg-rose-50/20' : 'border-slate-200 bg-white text-slate-800'"
                            class="w-full text-xs font-bold border rounded-xl pl-9 pr-8 py-2 focus:outline-none focus:border-rose-500 cursor-pointer shadow-2xs hover:border-slate-300 transition-all appearance-none">
                        <option value="">-- Semua Cluster --</option>
                        <template x-for="cls in clusterOptions" :key="cls.id">
                            <option :value="String(cls.id)" :selected="String(cls.id) === String(selectedCluster)" x-text="cls.name + ' (' + cls.code + ')'"></option>
                        </template>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                    </div>
                </div>
            @endif

            <!-- 3. SPINNER / DROPDOWN TAP (Reaktif Sesuai Cluster / Area) -->
            @if($showTap)
                <div class="relative inline-flex items-center flex-1 sm:flex-initial min-w-[140px] sm:min-w-[175px]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-rose-600">
                        <i data-lucide="building-2" class="w-3.5 h-3.5"></i>
                    </div>
                    <select @if($serverSide) name="tap_id" @endif
                            x-model="selectedTap" 
                            @change="if (typeof onTapChange === 'function') onTapChange()"
                            :class="selectedTap ? 'border-rose-500 ring-2 ring-rose-500/10 text-rose-900 bg-rose-50/20' : 'border-slate-200 bg-white text-slate-800'"
                            class="w-full text-xs font-bold border rounded-xl pl-9 pr-8 py-2 focus:outline-none focus:border-rose-500 cursor-pointer shadow-2xs hover:border-slate-300 transition-all appearance-none">
                        <option value="">-- Semua Kantor TAP --</option>
                        <template x-for="tp in tapOptions" :key="tp.id">
                            <option :value="String(tp.id)" :selected="String(tp.id) === String(selectedTap)" x-text="tp.name + ' (' + tp.code + ')'"></option>
                        </template>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                    </div>
                </div>
            @endif

            <!-- 4. SPINNER / DROPDOWN TIPE -->
            @if($showType)
                <div class="relative inline-flex items-center flex-1 sm:flex-initial min-w-[140px] sm:min-w-[165px]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-emerald-600">
                        <i data-lucide="{{ $typeMode === 'customer' ? 'store' : ($typeMode === 'sales' ? 'tag' : 'warehouse') }}" class="w-3.5 h-3.5"></i>
                    </div>
                    <select @if($serverSide) name="type" @endif
                            x-model="selectedType" 
                            @change="if (typeof onTypeChange === 'function') onTypeChange()"
                            :class="selectedType !== 'ALL' && selectedType !== '' ? 'border-rose-500 ring-2 ring-rose-500/10 text-rose-900 bg-rose-50/20' : 'border-slate-200 bg-white text-slate-800'"
                            class="w-full text-xs font-bold border rounded-xl pl-9 pr-8 py-2 focus:outline-none focus:border-rose-500 cursor-pointer shadow-2xs hover:border-slate-300 transition-all appearance-none">
                        @if($typeMode === 'customer')
                            <option value="ALL">-- Semua Tipe Toko --</option>
                            <option value="RETAIL">RETAIL (Outlet Reguler)</option>
                            <option value="GROSIR">GROSIR (Partai Besar)</option>
                        @elseif($typeMode === 'sales')
                            <option value="ALL">-- Semua Tipe Sales --</option>
                            @foreach($salesTypes as $st)
                                <option value="{{ $st->type_name }}">{{ $st->type_name }}</option>
                            @endforeach
                        @else
                            <option value="ALL">-- Semua Tipe Gudang --</option>
                            <option value="MAIN">Gudang Utama (MAIN)</option>
                            <option value="INVENTORY">Gudang Persediaan (INV)</option>
                            <option value="SALES">Gudang Kanvas (SALES)</option>
                        @endif
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                    </div>
                </div>
            @endif

            <!-- 5. SEARCH TEKS FILTER -->
            @if($showSearch)
                <div class="relative inline-flex items-center min-w-[200px] flex-1 sm:flex-initial">
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="search" class="w-3.5 h-3.5"></i>
                    </div>
                    <input type="text" 
                           @if($serverSide) name="search" @endif
                           x-model="{{ $modelSearch }}" 
                           @if(!$serverSide) @input="if (typeof onSearchChange === 'function') onSearchChange()" @endif
                           placeholder="{{ $searchPlaceholder }}" 
                           class="w-full text-xs font-semibold bg-white border border-slate-200 rounded-xl pl-8 pr-7 py-2 text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-500/10 shadow-2xs transition-all">
                    <button type="button" 
                            x-show="{{ $modelSearch }}" 
                            x-cloak
                            @click="{{ $modelSearch }} = ''; if (typeof onSearchClear === 'function') { onSearchClear(); } else if (typeof onSearchChange === 'function') { onSearchChange(); }" 
                            class="absolute inset-y-0 right-0 pr-2 flex items-center text-slate-400 hover:text-slate-600 transition-colors cursor-pointer"
                            title="Hapus pencarian">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            @endif

            <!-- Tombol Reset Filter -->
            <button type="button" 
                    x-show="typeof hasActiveFilter === 'function' ? hasActiveFilter() : Boolean(selectedArea || selectedCluster || selectedTap || (typeof dummySearch !== 'undefined' && dummySearch))" 
                    x-cloak
                    @click="if (typeof resetFilter === 'function') { resetFilter(); } else { selectedArea = ''; selectedCluster = ''; selectedTap = ''; }" 
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-200 hover:bg-rose-100 hover:text-rose-700 text-slate-600 text-xs font-bold transition-all shadow-2xs cursor-pointer active:scale-95"
                    title="Reset semua filter ke default">
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                <span>Reset</span>
            </button>
        </div>

        <!-- Indikator Jumlah Data Terfilter -->
        @if($showCount)
        <div class="flex items-center gap-2 text-xs font-medium text-slate-500 shrink-0 border-t lg:border-t-0 pt-2 lg:pt-0 border-slate-200/60">
            <span>Menampilkan:</span>
            <span class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 font-bold text-slate-800 shadow-2xs">
                @if($tab === 'clusters')
                    <strong class="text-rose-600" x-text="typeof filteredClustersCount !== 'undefined' ? filteredClustersCount : 0"></strong>
                    <span class="text-slate-400 font-normal"> / <span x-text="typeof allClusters !== 'undefined' ? allClusters.length : 0"></span> data</span>
                @elseif($tab === 'taps')
                    <strong class="text-rose-600" x-text="typeof filteredTapsCount !== 'undefined' ? filteredTapsCount : 0"></strong>
                    <span class="text-slate-400 font-normal"> / <span x-text="typeof allTaps !== 'undefined' ? allTaps.length : 0"></span> data</span>
                @elseif($tab === 'customers')
                    <strong class="text-rose-600">{{ number_format($customersCount) }}</strong>
                    <span class="text-slate-400 font-normal"> / {{ number_format($totalCount) }} data</span>
                @elseif($tab === 'sales')
                    <strong class="text-rose-600" x-text="typeof filteredCount !== 'undefined' ? filteredCount : 0"></strong>
                    <span class="text-slate-400 font-normal"> / <span x-text="typeof allSales !== 'undefined' ? allSales.length : 0"></span> data</span>
                @elseif($tab === 'po')
                    <strong class="text-rose-600" x-text="typeof filteredOrders !== 'undefined' ? filteredOrders.length : 0"></strong>
                    <span class="text-slate-400 font-normal"> / <span x-text="typeof allOrders !== 'undefined' ? allOrders.length : 0"></span> data</span>
                @elseif($tab === 'sm')
                    <strong class="text-rose-600" x-text="typeof filteredMovements !== 'undefined' ? filteredMovements.length : 0"></strong>
                    <span class="text-slate-400 font-normal"> / <span x-text="typeof allMovements !== 'undefined' ? allMovements.length : 0"></span> data</span>
                @else
                    <strong class="text-rose-600" x-text="typeof filteredWarehousesCount !== 'undefined' ? filteredWarehousesCount : 0"></strong>
                    <span class="text-slate-400 font-normal"> / <span x-text="typeof allWarehouses !== 'undefined' ? allWarehouses.length : 0"></span> data</span>
                @endif
            </span>
        </div>
        @endif
    </div>
    @if($serverSide)
        </form>
    @endif
</div>
