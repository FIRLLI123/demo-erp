@props([
    'action' => null,
    'tab' => 'warehouses',
    'showArea' => true,
    'showCluster' => false,
    'showTap' => false,
    'showType' => false,
    'areas' => [],
    'clusters' => [],
    'taps' => [],
    'selectedArea' => null,
    'selectedCluster' => null,
    'selectedTap' => null,
    'selectedType' => null,
    'filteredCount' => null,
    'totalCount' => null,
])

@php
    $targetUrl = $action ?? request()->url();
    $hasActiveFilter = !empty($selectedArea) || !empty($selectedCluster) || !empty($selectedTap) || (!empty($selectedType) && $selectedType !== 'ALL');
@endphp

<div class="bg-slate-50/80 border border-slate-200/90 rounded-2xl p-3.5 mb-5 shadow-2xs transition-all">
    <form method="GET" action="{{ $targetUrl }}" class="flex flex-col lg:flex-row lg:items-center justify-between gap-3" data-no-loading>
        <!-- Parameter Tab Aktif -->
        <input type="hidden" name="tab" value="{{ $tab }}">

        <!-- Deretan Filter Spinner / Dropdown -->
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
                    <select name="area_id" 
                            onchange="this.form.cluster_id ? this.form.cluster_id.value = '' : null; this.form.tap_id ? this.form.tap_id.value = '' : null; this.form.submit();"
                            class="text-xs font-bold bg-white text-slate-800 border {{ $selectedArea ? 'border-rose-500 ring-2 ring-rose-500/10 text-rose-900 bg-rose-50/20' : 'border-slate-200' }} rounded-xl pl-8 pr-7 py-2 focus:outline-none focus:border-rose-500 cursor-pointer shadow-2xs hover:border-slate-300 transition-all appearance-none">
                        <option value="">-- Semua Area --</option>
                        @foreach($areas as $ar)
                            <option value="{{ $ar->id }}" {{ (string)$selectedArea === (string)$ar->id ? 'selected' : '' }}>
                                {{ $ar->name }} ({{ $ar->code }})
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="chevron-down" class="w-3 h-3"></i>
                    </div>
                </div>
            @endif

            <!-- 2. SPINNER / DROPDOWN CLUSTER -->
            @if($showCluster)
                <div class="relative inline-flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-amber-600">
                        <i data-lucide="grid" class="w-3.5 h-3.5"></i>
                    </div>
                    <select name="cluster_id" 
                            onchange="this.form.tap_id ? this.form.tap_id.value = '' : null; this.form.submit();"
                            class="text-xs font-bold bg-white text-slate-800 border {{ $selectedCluster ? 'border-rose-500 ring-2 ring-rose-500/10 text-rose-900 bg-rose-50/20' : 'border-slate-200' }} rounded-xl pl-8 pr-7 py-2 focus:outline-none focus:border-rose-500 cursor-pointer shadow-2xs hover:border-slate-300 transition-all appearance-none">
                        <option value="">-- Semua Cluster --</option>
                        @foreach($clusters as $cls)
                            <option value="{{ $cls->id }}" {{ (string)$selectedCluster === (string)$cls->id ? 'selected' : '' }}>
                                {{ $cls->name }} ({{ $cls->code }})
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="chevron-down" class="w-3 h-3"></i>
                    </div>
                </div>
            @endif

            <!-- 3. SPINNER / DROPDOWN TAP -->
            @if($showTap)
                <div class="relative inline-flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-rose-600">
                        <i data-lucide="building-2" class="w-3.5 h-3.5"></i>
                    </div>
                    <select name="tap_id" 
                            onchange="this.form.submit();"
                            class="text-xs font-bold bg-white text-slate-800 border {{ $selectedTap ? 'border-rose-500 ring-2 ring-rose-500/10 text-rose-900 bg-rose-50/20' : 'border-slate-200' }} rounded-xl pl-8 pr-7 py-2 focus:outline-none focus:border-rose-500 cursor-pointer shadow-2xs hover:border-slate-300 transition-all appearance-none">
                        <option value="">-- Semua Kantor TAP --</option>
                        @foreach($taps as $tp)
                            <option value="{{ $tp->id }}" {{ (string)$selectedTap === (string)$tp->id ? 'selected' : '' }}>
                                {{ $tp->name }} ({{ $tp->code }})
                            </option>
                        @endforeach
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
                    <select name="wh_type" 
                            onchange="this.form.submit();"
                            class="text-xs font-bold bg-white text-slate-800 border {{ !empty($selectedType) && $selectedType !== 'ALL' ? 'border-rose-500 ring-2 ring-rose-500/10 text-rose-900 bg-rose-50/20' : 'border-slate-200' }} rounded-xl pl-8 pr-7 py-2 focus:outline-none focus:border-rose-500 cursor-pointer shadow-2xs hover:border-slate-300 transition-all appearance-none">
                        <option value="ALL" {{ empty($selectedType) || $selectedType === 'ALL' ? 'selected' : '' }}>-- Semua Tipe Gudang --</option>
                        <option value="MAIN" {{ $selectedType === 'MAIN' ? 'selected' : '' }}>Gudang Utama (MAIN)</option>
                        <option value="INVENTORY" {{ $selectedType === 'INVENTORY' ? 'selected' : '' }}>Gudang Persediaan (INV)</option>
                        <option value="SALES" {{ $selectedType === 'SALES' ? 'selected' : '' }}>Gudang Kanvas (SALES)</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="chevron-down" class="w-3 h-3"></i>
                    </div>
                </div>
            @endif

            <!-- Tombol Reset Filter (Hanya Muncul Bila Filter Aktif) -->
            @if($hasActiveFilter)
                <a href="{{ $targetUrl }}?tab={{ $tab }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-200 hover:bg-rose-100 hover:text-rose-700 text-slate-600 text-xs font-bold transition-all shadow-2xs cursor-pointer"
                   title="Reset semua filter ke default">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    <span>Reset</span>
                </a>
            @endif
        </div>

        <!-- Indikator Jumlah Data Terfilter -->
        @if($filteredCount !== null && $totalCount !== null)
            <div class="flex items-center gap-2 text-xs font-medium text-slate-500 shrink-0 border-t lg:border-t-0 pt-2 lg:pt-0 border-slate-200/60">
                <span>Hasil:</span>
                <span class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 font-bold text-slate-800 shadow-2xs">
                    <strong class="text-rose-600">{{ $filteredCount }}</strong>
                    <span class="text-slate-400 font-normal"> / {{ $totalCount }} data</span>
                </span>
            </div>
        @endif
    </form>
</div>
