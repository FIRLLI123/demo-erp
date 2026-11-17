@props([
    'showArea' => true,
    'showCluster' => false,
    'showTap' => false,
    'showType' => false,
    'areas' => [],
    'tab' => 'warehouses',
])

<div class="bg-slate-50/80 border border-slate-200/90 rounded-2xl p-3.5 mb-5 shadow-2xs transition-all">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
        
        <!-- Deretan Filter Spinner / Dropdown (Client-Side Reactive via Alpine.js) -->
        <div class="flex flex-wrap items-center gap-2.5 flex-1">
            <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 flex items-center gap-1.5 mr-1">
                <i data-lucide="filter" class="w-3.5 h-3.5 text-rose-600"></i>
                <span>Filter:</span>
            </span>

            <!-- 1. SPINNER / DROPDOWN AREA -->
            @if($showArea)
                <div class="relative inline-flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-cyan-600">
                        <i data-lucide="map" class="w-3.5 h-3.5"></i>
                    </div>
                    <select x-model="selectedArea" 
                            @change="onAreaChange()"
                            :class="selectedArea ? 'border-rose-500 ring-2 ring-rose-500/10 text-rose-900 bg-rose-50/20' : 'border-slate-200 bg-white text-slate-800'"
                            class="text-xs font-bold border rounded-xl pl-8 pr-7 py-2 focus:outline-none focus:border-rose-500 cursor-pointer shadow-2xs hover:border-slate-300 transition-all appearance-none">
                        <option value="">-- Semua Area --</option>
                        @foreach($areas as $ar)
                            <option value="{{ $ar->id }}">{{ $ar->name }} ({{ $ar->code }})</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="chevron-down" class="w-3 h-3"></i>
                    </div>
                </div>
            @endif

            <!-- 2. SPINNER / DROPDOWN CLUSTER (Reaktif Sesuai Area) -->
            @if($showCluster)
                <div class="relative inline-flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-amber-600">
                        <i data-lucide="grid" class="w-3.5 h-3.5"></i>
                    </div>
                    <select x-model="selectedCluster" 
                            @change="onClusterChange()"
                            :class="selectedCluster ? 'border-rose-500 ring-2 ring-rose-500/10 text-rose-900 bg-rose-50/20' : 'border-slate-200 bg-white text-slate-800'"
                            class="text-xs font-bold border rounded-xl pl-8 pr-7 py-2 focus:outline-none focus:border-rose-500 cursor-pointer shadow-2xs hover:border-slate-300 transition-all appearance-none">
                        <option value="">-- Semua Cluster --</option>
                        <template x-for="cls in clusterOptions" :key="cls.id">
                            <option :value="cls.id" x-text="cls.name + ' (' + cls.code + ')'"></option>
                        </template>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="chevron-down" class="w-3 h-3"></i>
                    </div>
                </div>
            @endif

            <!-- 3. SPINNER / DROPDOWN TAP (Reaktif Sesuai Cluster / Area) -->
            @if($showTap)
                <div class="relative inline-flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-rose-600">
                        <i data-lucide="building-2" class="w-3.5 h-3.5"></i>
                    </div>
                    <select x-model="selectedTap" 
                            :class="selectedTap ? 'border-rose-500 ring-2 ring-rose-500/10 text-rose-900 bg-rose-50/20' : 'border-slate-200 bg-white text-slate-800'"
                            class="text-xs font-bold border rounded-xl pl-8 pr-7 py-2 focus:outline-none focus:border-rose-500 cursor-pointer shadow-2xs hover:border-slate-300 transition-all appearance-none">
                        <option value="">-- Semua Kantor TAP --</option>
                        <template x-for="tp in tapOptions" :key="tp.id">
                            <option :value="tp.id" x-text="tp.name + ' (' + tp.code + ')'"></option>
                        </template>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="chevron-down" class="w-3 h-3"></i>
                    </div>
                </div>
            @endif

            <!-- 4. SPINNER / DROPDOWN TIPE GUDANG -->
            @if($showType)
                <div class="relative inline-flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-emerald-600">
                        <i data-lucide="warehouse" class="w-3.5 h-3.5"></i>
                    </div>
                    <select x-model="selectedType" 
                            :class="selectedType !== 'ALL' ? 'border-rose-500 ring-2 ring-rose-500/10 text-rose-900 bg-rose-50/20' : 'border-slate-200 bg-white text-slate-800'"
                            class="text-xs font-bold border rounded-xl pl-8 pr-7 py-2 focus:outline-none focus:border-rose-500 cursor-pointer shadow-2xs hover:border-slate-300 transition-all appearance-none">
                        <option value="ALL">-- Semua Tipe Gudang --</option>
                        <option value="MAIN">Gudang Utama (MAIN)</option>
                        <option value="INVENTORY">Gudang Persediaan (INV)</option>
                        <option value="SALES">Gudang Kanvas (SALES)</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="chevron-down" class="w-3 h-3"></i>
                    </div>
                </div>
            @endif

            <!-- Tombol Reset Filter (Instan Client-Side) -->
            <button type="button" 
                    x-show="hasActiveFilter()" 
                    x-cloak
                    @click="resetFilter()" 
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-200 hover:bg-rose-100 hover:text-rose-700 text-slate-600 text-xs font-bold transition-all shadow-2xs cursor-pointer active:scale-95"
                    title="Reset semua filter ke default">
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                <span>Reset</span>
            </button>
        </div>

        <!-- Indikator Jumlah Data Terfilter (Real-time di Browser) -->
        <div class="flex items-center gap-2 text-xs font-medium text-slate-500 shrink-0 border-t lg:border-t-0 pt-2 lg:pt-0 border-slate-200/60">
            <span>Hasil:</span>
            <span class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 font-bold text-slate-800 shadow-2xs">
                @if($tab === 'clusters')
                    <strong class="text-rose-600" x-text="filteredClustersCount"></strong>
                    <span class="text-slate-400 font-normal"> / {{ count($clusters ?? []) }} data</span>
                @elseif($tab === 'taps')
                    <strong class="text-rose-600" x-text="filteredTapsCount"></strong>
                    <span class="text-slate-400 font-normal"> / {{ count($taps ?? []) }} data</span>
                @else
                    <strong class="text-rose-600" x-text="filteredWarehousesCount"></strong>
                    <span class="text-slate-400 font-normal"> / {{ count($warehouses ?? []) }} data</span>
                @endif
            </span>
        </div>
    </div>
</div>
