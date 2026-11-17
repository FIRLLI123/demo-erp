<x-app-layout>
    <x-slot name="title">Master Organisasi Distribusi</x-slot>
    <x-slot name="headerTitle">Master Hirarki Organisasi</x-slot>
    <x-slot name="badge">Area > Cluster > TAP > Warehouse</x-slot>

    <!-- Hierarchy Header Card -->
    <div class="mb-8 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white p-6 rounded-2xl shadow-lg relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-rose-500/10 rounded-full blur-3xl"></div>
        <div class="max-w-2xl">
            <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30 uppercase tracking-wider">
                Struktur Hirarki Distribusi
            </span>
            <h2 class="text-xl font-extrabold mt-2 tracking-tight">Area &rarr; Cluster &rarr; TAP &rarr; Warehouse</h2>
            <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                Struktur berjenjang dari Area memayungi Cluster, Cluster memayungi TAP (Telkomsel Authorized Partner), dan setiap TAP menaungi unit-unit Gudang fisik operasional (Gudang Utama, Persediaan Buffer, dan Kanvas Sales).
            </p>
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
    <div x-data="{ activeTab: 'warehouses', filterType: 'ALL' }" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="border-b border-slate-200/80 px-6 pt-4 flex gap-6 text-xs font-bold overflow-x-auto">
            <button @click="activeTab = 'areas'" :class="activeTab === 'areas' ? 'text-rose-600 border-b-2 border-rose-600 pb-3 font-extrabold' : 'text-slate-500 hover:text-slate-800 pb-3'">
                1. Data Area ({{ count($areas) }})
            </button>
            <button @click="activeTab = 'clusters'" :class="activeTab === 'clusters' ? 'text-rose-600 border-b-2 border-rose-600 pb-3 font-extrabold' : 'text-slate-500 hover:text-slate-800 pb-3'">
                2. Data Cluster ({{ count($clusters) }})
            </button>
            <button @click="activeTab = 'taps'" :class="activeTab === 'taps' ? 'text-rose-600 border-b-2 border-rose-600 pb-3 font-extrabold' : 'text-slate-500 hover:text-slate-800 pb-3'">
                3. Data TAP ({{ count($taps) }})
            </button>
            <button @click="activeTab = 'warehouses'" :class="activeTab === 'warehouses' ? 'text-rose-600 border-b-2 border-rose-600 pb-3 font-extrabold' : 'text-slate-500 hover:text-slate-800 pb-3'">
                4. Data Gudang / Warehouses ({{ count($warehouses) }})
            </button>
        </div>

        <!-- ============================================================= -->
        <!-- TAB 1: AREAS                                                  -->
        <!-- ============================================================= -->
        <div x-show="activeTab === 'areas'" class="p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Daftar Wilayah Operasional (Area)</h3>
                    <!-- <p class="text-xs text-slate-500">Tingkat wilayah tertinggi yang memayungi beberapa cluster regional.</p> -->
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($areas as $ar)
                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:shadow-md transition-all flex items-center justify-between">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-xl bg-cyan-100 text-cyan-800 flex items-center justify-center font-bold">
                                <i data-lucide="map" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <span class="font-mono text-[11px] font-bold text-slate-400 block">{{ $ar->code }}</span>
                                <h4 class="font-extrabold text-slate-900 text-base">{{ $ar->name }}</h4>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            {{ $ar->status }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- TAB 2: CLUSTERS                                               -->
        <!-- ============================================================= -->
        <div x-show="activeTab === 'clusters'" class="p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Daftar Cluster Regional</h3>
                    <!-- <p class="text-xs text-slate-500">Sub-wilayah di bawah Area yang menaungi sekelompok kantor TAP.</p> -->
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($clusters as $cls)
                    <div class="p-4 rounded-2xl border border-slate-200 bg-white hover:shadow-md transition-all">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-mono text-xs font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                {{ $cls->code }}
                            </span>
                            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">
                                {{ $cls->status }}
                            </span>
                        </div>
                        <h4 class="font-extrabold text-slate-900 text-sm mt-1">{{ $cls->name }}</h4>
                        <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                            <span>Area Induk:</span>
                            <span class="font-bold text-slate-800">{{ $cls->area_name }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- TAB 3: TAPs                                                   -->
        <!-- ============================================================= -->
        <div x-show="activeTab === 'taps'" class="p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Daftar TAP (Telkomsel Authorized Partner)</h3>
                    <!-- <p class="text-xs text-slate-500">Kantor operasional cabang. Setiap TAP memiliki 3 gudang fisik (MAIN, INV, SALES).</p> -->
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Kode TAP</th>
                            <th class="py-3 px-4">Nama Kantor TAP</th>
                            <th class="py-3 px-4">Cluster & Area</th>
                            <th class="py-3 px-4">Alamat Kantor</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-center">Gudang Naungan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($taps as $tap)
                            <tr class="hover:bg-slate-50/60">
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-900">{{ $tap->code }}</td>
                                <td class="py-3.5 px-4 font-bold text-slate-800">{{ $tap->name }}</td>
                                <td class="py-3.5 px-4">
                                    <div class="flex flex-col gap-0.5">
                                        <span class="font-bold text-slate-800">{{ $tap->cluster_name }}</span>
                                        <span class="text-[10px] text-slate-400 font-medium">{{ $tap->area_name }}</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-slate-500">{{ $tap->address }}</td>
                                <td class="py-3.5 px-4">
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
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- TAB 4: WAREHOUSES (TURUNAN LEVEL 4 DARI TAP)                  -->
        <!-- ============================================================= -->
        <div x-show="activeTab === 'warehouses'" class="p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
                <div>
                    <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                        <i data-lucide="warehouse" class="w-4 h-4 text-rose-600"></i>
                        <span>Daftar Gudang Fisik (Warehouses)</span>
                    </h3>
                    <!-- <p class="text-xs text-slate-500 mt-0.5">Unit gudang penyimpanan stok fisik per TAP (Gudang Utama, Persediaan Buffer, dan Kanvas Sales).</p> -->
                </div>

                <!-- Type Quick Filter -->
                <div class="flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl text-xs font-bold shrink-0">
                    <button type="button" 
                            @click="filterType = 'ALL'" 
                            :class="filterType === 'ALL' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-800'"
                            class="px-3 py-1.5 rounded-lg transition-all">
                        Semua ({{ count($warehouses) }})
                    </button>
                    <button type="button" 
                            @click="filterType = 'MAIN'" 
                            :class="filterType === 'MAIN' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-500 hover:text-slate-800'"
                            class="px-2.5 py-1.5 rounded-lg transition-all flex items-center gap-1">
                        <span>Utama</span>
                        <span class="text-[10px] opacity-80 font-mono">({{ $warehouses->where('type', 'MAIN')->count() }})</span>
                    </button>
                    <button type="button" 
                            @click="filterType = 'INVENTORY'" 
                            :class="filterType === 'INVENTORY' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-500 hover:text-slate-800'"
                            class="px-2.5 py-1.5 rounded-lg transition-all flex items-center gap-1">
                        <span>Persediaan</span>
                        <span class="text-[10px] opacity-80 font-mono">({{ $warehouses->where('type', 'INVENTORY')->count() }})</span>
                    </button>
                    <button type="button" 
                            @click="filterType = 'SALES'" 
                            :class="filterType === 'SALES' ? 'bg-purple-600 text-white shadow-xs' : 'text-slate-500 hover:text-slate-800'"
                            class="px-2.5 py-1.5 rounded-lg transition-all flex items-center gap-1">
                        <span>Kanvas</span>
                        <span class="text-[10px] opacity-80 font-mono">({{ $warehouses->where('type', 'SALES')->count() }})</span>
                    </button>
                </div>
            </div>

            <!-- Warehouses Table -->
            <div class="overflow-x-auto rounded-2xl border border-slate-200">
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
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($warehouses as $wh)
                            <tr class="hover:bg-slate-50/70 transition-colors"
                                x-show="filterType === 'ALL' || filterType === '{{ $wh->type }}'">
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
                                            <span class="px-1.5 py-0.2 rounded bg-purple-50 text-purple-700 font-mono text-[10px] font-bold border border-purple-200">
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
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        {{ $wh->status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
