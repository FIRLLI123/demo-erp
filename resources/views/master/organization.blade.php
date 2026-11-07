<x-app-layout>
    <x-slot name="title">Master Organisasi Distribusi</x-slot>
    <x-slot name="headerTitle">Master Hirarki Organisasi</x-slot>
    <x-slot name="badge">Area > Cluster > TAP > Sales</x-slot>

    <!-- Hierarchy Header Card -->
    <div class="mb-8 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white p-6 rounded-2xl shadow-lg relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-rose-500/10 rounded-full blur-3xl"></div>
        <div class="max-w-2xl">
            <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30 uppercase tracking-wider">
                Struktur Organisasi Distribusi
            </span>
            <h2 class="text-xl font-extrabold mt-2 tracking-tight">Hierarki 4 Level Telkomsel</h2>
            <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                Struktur berjenjang dari Area memayungi Cluster, Cluster memayungi TAP (Telecommunication Authorization Point), dan TAP menaungi Sales Force lapangan serta Gudang.
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
                <i data-lucide="users" class="w-3.5 h-3.5 text-emerald-400"></i> SALES ({{ count($sales) }})
            </span>
        </div>
    </div>

    <!-- Tabbed Management -->
    <div x-data="{ activeTab: 'taps' }" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="border-b border-slate-200/80 px-6 pt-4 flex gap-6 text-xs font-bold">
            <button @click="activeTab = 'areas'" :class="activeTab === 'areas' ? 'text-rose-600 border-b-2 border-rose-600 pb-3 font-extrabold' : 'text-slate-500 hover:text-slate-800 pb-3'">
                1. Data Area ({{ count($areas) }})
            </button>
            <button @click="activeTab = 'clusters'" :class="activeTab === 'clusters' ? 'text-rose-600 border-b-2 border-rose-600 pb-3 font-extrabold' : 'text-slate-500 hover:text-slate-800 pb-3'">
                2. Data Cluster ({{ count($clusters) }})
            </button>
            <button @click="activeTab = 'taps'" :class="activeTab === 'taps' ? 'text-rose-600 border-b-2 border-rose-600 pb-3 font-extrabold' : 'text-slate-500 hover:text-slate-800 pb-3'">
                3. Data TAP ({{ count($taps) }})
            </button>
            <button @click="activeTab = 'sales'" :class="activeTab === 'sales' ? 'text-rose-600 border-b-2 border-rose-600 pb-3 font-extrabold' : 'text-slate-500 hover:text-slate-800 pb-3'">
                4. Data Sales Force ({{ count($sales) }})
            </button>
        </div>

        <!-- TAB: TAPs (Default Focus) -->
        <div x-show="activeTab === 'taps'" class="p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Daftar TAP (Telecommunication Authorization Point)</h3>
                    <p class="text-xs text-slate-500">Setiap TAP otomatis memiliki 1 Gudang Utama (MAIN) & 1 Gudang Persediaan (INV)</p>
                </div>
                <button class="px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-sm transition-all">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Tambah TAP</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Kode TAP</th>
                            <th class="py-3 px-4">Nama TAP</th>
                            <th class="py-3 px-4">Cluster & Area</th>
                            <th class="py-3 px-4">Alamat Kantor</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-center">Gudang Default</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($taps as $tap)
                            <tr class="hover:bg-slate-50/60">
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-900">{{ $tap->code }}</td>
                                <td class="py-3.5 px-4 font-bold text-slate-800">{{ $tap->name }}</td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-700 font-semibold border border-amber-200 text-[11px]">
                                        {{ $tap->cluster_name }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-500">{{ $tap->address }}</td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        {{ $tap->status }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex gap-1 text-[10px] font-mono font-bold">
                                        <span class="px-1.5 py-0.5 bg-slate-900 text-white rounded">MAIN</span>
                                        <span class="px-1.5 py-0.5 bg-cyan-700 text-white rounded">INV</span>
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB: Sales Force -->
        <div x-show="activeTab === 'sales'" class="p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Daftar Sales Force Lapangan</h3>
                    <p class="text-xs text-slate-500">Sales lapangan pengguna aplikasi Android Native untuk input SO dan kanvas stok</p>
                </div>
                <button class="px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-sm transition-all">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Tambah Sales</span>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($sales as $sls)
                    <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 hover:bg-white hover:shadow-md transition-all">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-rose-500 to-red-600 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                                {{ substr($sls->name, 0, 2) }}
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 text-sm">{{ $sls->name }}</h4>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-mono bg-slate-200 text-slate-700 font-semibold">{{ $sls->code }}</span>
                            </div>
                        </div>
                        <div class="space-y-1.5 text-xs text-slate-600 border-t border-slate-200/60 pt-3">
                            <p class="flex items-center justify-between">
                                <span class="text-slate-400">TAP Induk:</span>
                                <span class="font-bold text-slate-800">{{ $sls->tap_name }}</span>
                            </p>
                            <p class="flex items-center justify-between">
                                <span class="text-slate-400">Gudang Kanvas:</span>
                                <span class="font-mono text-[11px] font-semibold text-rose-600">WH-SLS-{{ $sls->code }}</span>
                            </p>
                            <p class="flex items-center justify-between">
                                <span class="text-slate-400">Kontak WA:</span>
                                <span>{{ $sls->phone }}</span>
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- TAB: Areas -->
        <div x-show="activeTab === 'areas'" class="p-6">
            <div class="space-y-3">
                @foreach($areas as $ar)
                    <div class="p-4 rounded-xl border border-slate-200 flex items-center justify-between">
                        <div>
                            <span class="font-mono text-xs font-bold text-slate-500">{{ $ar->code }}</span>
                            <h4 class="font-bold text-slate-900 text-base">{{ $ar->name }}</h4>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">AKTIF</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- TAB: Clusters -->
        <div x-show="activeTab === 'clusters'" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($clusters as $cls)
                    <div class="p-4 rounded-xl border border-slate-200">
                        <span class="font-mono text-xs font-bold text-slate-400">{{ $cls->code }}</span>
                        <h4 class="font-bold text-slate-900 text-sm mt-1">{{ $cls->name }}</h4>
                        <p class="text-xs text-rose-600 mt-2 font-semibold">Area: {{ $cls->area_name }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>

