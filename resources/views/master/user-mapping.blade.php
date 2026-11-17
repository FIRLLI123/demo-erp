<x-app-layout>
    <x-slot name="title">Otorisasi Wilayah & Gudang - {{ $user->name }}</x-slot>
    <x-slot name="headerTitle">Otorisasi Wilayah & Gudang Pengguna</x-slot>
    <x-slot name="badge">{{ $user->name }}</x-slot>

    <div x-data="userMappingApp()" class="max-w-5xl mx-auto pb-16">
        
        <!-- Top Navigation Bar -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <a href="{{ route('master.users') }}" 
               class="px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-bold inline-flex items-center gap-2 transition-all shadow-xs">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke Master Pengguna</span>
            </a>

            <div class="flex items-center gap-2">
                <span class="text-xs font-mono font-bold text-slate-500">ID Pengguna:</span>
                <span class="px-2.5 py-1 rounded-lg bg-slate-900 text-white font-mono text-xs font-extrabold shadow-xs">
                    #{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}
                </span>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between shadow-xs">
                <span class="flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                    {{ session('success') }}
                </span>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center justify-between shadow-xs">
                <span class="flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                    {{ session('error') }}
                </span>
                <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800">&times;</button>
            </div>
        @endif

        <!-- User Profile Strip Card -->
        <div class="bg-white rounded-3xl border border-slate-200 p-5 md:p-6 mb-6 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-indigo-900 text-white flex items-center justify-center font-extrabold text-lg shadow-sm">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-lg font-extrabold text-slate-900 tracking-tight">{{ $user->name }}</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                            {{ $user->role_name }}
                        </span>
                        @if($user->is_active)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Aktif</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">Nonaktif</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 font-mono mt-0.5">{{ $user->email }} &bull; {{ $user->phone ?? 'Tanpa Nomor HP' }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3 text-xs">
                <div class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-right">
                    <span class="text-[10px] font-bold text-slate-400 block uppercase">Status Saat Ini</span>
                    <span class="font-bold text-slate-800" x-text="hasFullAccess ? 'Akses Penuh (Semua Wilayah)' : selectedTaps.length + ' TAP Terpilih'"></span>
                </div>
            </div>
        </div>

        <!-- Main Form -->
        <form method="POST" action="{{ route('master.users.save-mapping', $user->id) }}">
            @csrf

            <!-- Hidden Inputs for Boolean Flags -->
            <input type="hidden" name="has_full_access" :value="hasFullAccess ? '1' : '0'">
            <input type="hidden" name="is_ho_readonly" :value="isHoReadOnly ? '1' : '0'">

            <!-- 1. Pilihan Mode Otorisasi: Akses Penuh vs Wilayah Tertentu -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                
                <!-- Opsi A: Akses Penuh -->
                <div @click="setAccessMode('full')" 
                     class="p-5 rounded-3xl border-2 cursor-pointer transition-all flex items-start gap-4 shadow-xs"
                     :class="hasFullAccess ? 'bg-indigo-50/80 border-indigo-600 ring-4 ring-indigo-500/10' : 'bg-white border-slate-200 hover:border-slate-300'">
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 mt-0.5"
                         :class="hasFullAccess ? 'border-indigo-600 bg-indigo-600' : 'border-slate-300 bg-white'">
                        <div x-show="hasFullAccess" class="w-2 h-2 rounded-full bg-white"></div>
                    </div>
                    <div>
                        <span class="font-extrabold text-sm text-slate-900 block flex items-center gap-2">
                            <span>Akses Penuh</span>
                            <span x-show="hasFullAccess" class="px-2 py-0.2 rounded text-[10px] font-bold bg-indigo-600 text-white">Dipilih</span>
                        </span>
                        <p class="text-xs text-slate-500 mt-1">
                            Membuka seluruh area, cluster, TAP, dan gudang transaksi tanpa batasan wilayah.
                        </p>
                    </div>
                </div>

                <!-- Opsi B: Batasi Sesuai Wilayah Tertentu -->
                <div @click="setAccessMode('custom')" 
                     class="p-5 rounded-3xl border-2 cursor-pointer transition-all flex items-start gap-4 shadow-xs"
                     :class="!hasFullAccess ? 'bg-indigo-50/80 border-indigo-600 ring-4 ring-indigo-500/10' : 'bg-white border-slate-200 hover:border-slate-300'">
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 mt-0.5"
                         :class="!hasFullAccess ? 'border-indigo-600 bg-indigo-600' : 'border-slate-300 bg-white'">
                        <div x-show="!hasFullAccess" class="w-2 h-2 rounded-full bg-white"></div>
                    </div>
                    <div>
                        <span class="font-extrabold text-sm text-slate-900 block flex items-center gap-2">
                            <span>Pilih Wilayah Tertentu</span>
                            <span x-show="!hasFullAccess" class="px-2 py-0.2 rounded text-[10px] font-bold bg-indigo-600 text-white">Dipilih</span>
                        </span>
                        <p class="text-xs text-slate-500 mt-1">
                            Tentukan Area, Cluster, TAP, dan Gudang mana saja yang boleh diakses pengguna ini.
                        </p>
                    </div>
                </div>
            </div>

            <!-- 2. Mode Monitoring (Hanya Lihat Laporan) -->
            <div class="p-4 rounded-2xl border transition-all flex items-center justify-between mb-6 shadow-xs"
                 :class="isHoReadOnly ? 'bg-amber-50 border-amber-300' : 'bg-white border-slate-200'">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-xl" :class="isHoReadOnly ? 'bg-amber-500 text-white' : 'bg-slate-100 text-slate-600'">
                        <i data-lucide="eye" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="text-xs font-extrabold text-slate-900 block">Mode Monitoring (Hanya Lihat Laporan)</span>
                        <span class="text-[11px] text-slate-500 block">Hanya memantau report & grafik wilayah yang dipilih, tanpa hak transaksi barang fisik.</span>
                    </div>
                </div>

                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" x-model="isHoReadOnly" class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-600"></div>
                </label>
            </div>

            <!-- 3. Pohon Wilayah Bertingkat (Cascading Tree) -->
            <div x-show="!hasFullAccess" x-transition class="space-y-4 mb-8">
                
                <!-- Tree Section Header Bar -->
                <div class="bg-slate-900 text-white p-4 px-6 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-sm">
                    <div>
                        <h3 class="text-sm font-extrabold flex items-center gap-2 text-white">
                            <i data-lucide="network" class="w-4 h-4 text-emerald-400"></i>
                            <span>Pohon Wilayah & Otorisasi Gudang</span>
                        </h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">Centang Area, Cluster, atau TAP. Gudang otomatis ikut atau bisa disesuaikan.</p>
                    </div>

                    <div class="flex items-center gap-2 text-xs">
                        <button type="button" 
                                @click="toggleSelectAllAreas(true)" 
                                class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-emerald-400 font-bold border border-slate-700 transition-all">
                            Pilih Semua
                        </button>
                        <button type="button" 
                                @click="toggleSelectAllAreas(false)" 
                                class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold border border-slate-700 transition-all">
                            Kosongkan
                        </button>
                    </div>
                </div>

                <!-- Tree Nodes -->
                <div class="space-y-4">
                    <template x-for="area in territoryTree" :key="area.id">
                        <div class="rounded-3xl border border-slate-200 bg-white overflow-hidden shadow-xs">
                            
                            <!-- Header Level 1: Area -->
                            <div class="p-4 bg-slate-50 border-b border-slate-200/80 flex items-center justify-between">
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" 
                                           @change="toggleArea(area)"
                                           :checked="isAreaSelected(area)"
                                           class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                                    <span class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
                                        <i data-lucide="globe" class="w-4 h-4 text-indigo-600"></i>
                                        <span x-text="area.name"></span>
                                    </span>
                                </label>

                                <div class="flex items-center gap-3">
                                    <span class="text-[11px] font-mono text-slate-400" x-text="area.clusters.length + ' Cluster'"></span>
                                    <button type="button" 
                                            @click="expandedAreas[area.id] = (expandedAreas[area.id] === false) ? true : false" 
                                            class="text-xs font-bold text-slate-500 hover:text-slate-800 flex items-center gap-1 p-1 rounded-lg">
                                        <span x-text="expandedAreas[area.id] === false ? 'Buka' : 'Tutup'"></span>
                                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 transition-transform" :class="expandedAreas[area.id] === false ? '' : 'rotate-180'"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Level 2: Clusters List -->
                            <div x-show="expandedAreas[area.id] !== false" class="p-4 space-y-4 bg-slate-50/30">
                                <template x-for="cluster in area.clusters" :key="cluster.id">
                                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-2xs">
                                        
                                        <!-- Header Level 2: Cluster -->
                                        <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                                            <label class="flex items-center gap-2.5 cursor-pointer">
                                                <input type="checkbox" 
                                                       @change="toggleCluster(cluster)"
                                                       :checked="isClusterSelected(cluster)"
                                                       class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                                                <span class="font-extrabold text-xs text-slate-800 flex items-center gap-1.5">
                                                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-amber-600"></i>
                                                    <span x-text="'Cluster ' + cluster.name"></span>
                                                </span>
                                            </label>

                                            <span class="text-[10px] font-mono text-slate-400" x-text="cluster.taps.length + ' Kantor TAP'"></span>
                                        </div>

                                        <!-- Level 3 & 4: TAPs & Warehouses -->
                                        <div class="space-y-3">
                                            <template x-for="tap in cluster.taps" :key="tap.id">
                                                <div class="p-3.5 rounded-xl border transition-all"
                                                     :class="isTapSelected(tap.id) ? 'bg-indigo-50/20 border-indigo-200 shadow-2xs' : 'bg-slate-50/60 border-slate-200'">
                                                    
                                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                                        
                                                        <!-- Checkbox TAP -->
                                                        <label class="flex items-center gap-2.5 cursor-pointer">
                                                            <input type="checkbox" 
                                                                   name="selected_taps[]"
                                                                   :value="tap.id"
                                                                   @change="toggleTap(tap)"
                                                                   :checked="isTapSelected(tap.id)"
                                                                   class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                                                            <div>
                                                                <span class="font-extrabold text-xs text-slate-900 block" x-text="tap.name"></span>
                                                                <span class="text-[10px] text-slate-400 font-mono" x-text="tap.code"></span>
                                                            </div>
                                                        </label>

                                                        <!-- Mode Otorisasi Gudang TAP (Auto vs Custom) -->
                                                        <template x-if="isTapSelected(tap.id)">
                                                            <div class="flex items-center gap-2 text-xs">
                                                                <label class="flex items-center gap-1.5 cursor-pointer px-2.5 py-1 rounded-lg border font-bold transition-all"
                                                                       :class="isAutoTap(tap.id) ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 'bg-white text-slate-600 border-slate-200'">
                                                                    <input type="checkbox" 
                                                                           name="auto_taps[]" 
                                                                           :value="tap.id"
                                                                           @change="setAutoTap(tap, $event.target.checked)"
                                                                           :checked="isAutoTap(tap.id)"
                                                                           class="rounded border-emerald-400 text-emerald-600 focus:ring-emerald-500">
                                                                    <span>⚡ Semua Gudang (+Sales Baru)</span>
                                                                </label>

                                                                <button type="button" 
                                                                        @click="expandedTaps[tap.id] = !expandedTaps[tap.id]" 
                                                                        class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-600 hover:text-slate-900 font-bold flex items-center gap-1 text-[11px] shadow-2xs">
                                                                    <span x-text="expandedTaps[tap.id] ? 'Sembunyikan' : 'Rincian (' + tap.warehouses.length + ')'"></span>
                                                                    <i data-lucide="chevron-down" class="w-3 h-3 transition-transform" :class="expandedTaps[tap.id] ? 'rotate-180' : ''"></i>
                                                                </button>
                                                            </div>
                                                        </template>
                                                    </div>

                                                    <!-- Level 4: Rincian Gudang TAP -->
                                                    <div x-show="isTapSelected(tap.id) && (expandedTaps[tap.id] || !isAutoTap(tap.id))" 
                                                         x-cloak x-transition 
                                                         class="mt-3 pt-3 border-t border-slate-200/60 pl-6 space-y-1.5">
                                                        
                                                        <div class="flex items-center justify-between text-[11px] text-slate-500 mb-1">
                                                            <span class="font-bold">Daftar Gudang Fisik:</span>
                                                            <span x-show="isAutoTap(tap.id)" class="text-emerald-700 font-bold text-[10px]">
                                                                * Seluruh gudang aktif otomatis. Matikan "⚡ Semua Gudang" jika ingin mengecualikan sales tertentu.
                                                            </span>
                                                        </div>

                                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                            <template x-for="wh in tap.warehouses" :key="wh.id">
                                                                <label class="flex items-center justify-between p-2 rounded-lg bg-white border border-slate-200/80 hover:border-indigo-400 cursor-pointer text-xs transition-all shadow-2xs">
                                                                    <div class="flex items-center gap-2 overflow-hidden">
                                                                        <input type="checkbox" 
                                                                               name="custom_warehouses[]" 
                                                                               :value="wh.id"
                                                                               :disabled="isAutoTap(tap.id)"
                                                                               @change="toggleWh(wh.id)"
                                                                               :checked="isWhSelected(wh.id, tap.id)"
                                                                               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                                                        <div class="truncate">
                                                                            <span class="font-bold text-slate-800 block truncate" x-text="wh.name"></span>
                                                                            <span class="font-mono text-[10px] text-slate-400" x-text="wh.code"></span>
                                                                        </div>
                                                                    </div>
                                                                    <span class="text-[9px] px-1.5 py-0.2 rounded font-mono font-bold shrink-0 ml-2"
                                                                          :class="wh.type === 'MAIN' ? 'bg-emerald-100 text-emerald-800' : (wh.type === 'INVENTORY' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800')"
                                                                          x-text="wh.type"></span>
                                                                </label>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Sticky Bottom Actions Bar -->
            <div class="fixed bottom-0 left-0 right-0 z-30 bg-white/95 backdrop-blur-md border-t border-slate-200 py-3.5 px-6 shadow-xl">
                <div class="max-w-5xl mx-auto flex items-center justify-between gap-4">
                    <div class="text-xs text-slate-600 font-medium">
                        Pengguna: <strong class="text-slate-900">{{ $user->name }}</strong> &bull; 
                        Mode: <span class="font-bold" :class="hasFullAccess ? 'text-indigo-600' : 'text-slate-900'" x-text="hasFullAccess ? 'Akses Penuh' : 'Wilayah Tertentu (' + selectedTaps.length + ' TAP)'"></span>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('master.users') }}" 
                           class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-all">
                            Batal
                        </a>
                        <button type="submit" 
                                class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold rounded-xl text-xs flex items-center gap-2 shadow-md shadow-indigo-600/20 transition-all active:scale-95">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Simpan Otorisasi Wilayah & Gudang</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>

    </div>

    @push('scripts')
    <script>
        function userMappingApp() {
            return {
                territoryTree: @json($territoryTree),
                hasFullAccess: {{ $user->has_full_access ? 'true' : 'false' }},
                isHoReadOnly: {{ $user->is_ho_readonly ? 'true' : 'false' }},
                selectedTaps: @json($mappedTapIds),
                autoTaps: @json($autoTapIds),
                customWhs: @json($customWhIds),
                expandedAreas: {},
                expandedTaps: {},

                init() {
                    // Jika baru buka dan belum ada TAP terpilih serta bukan akses penuh, set autoTaps dari selectedTaps
                    if (this.autoTaps.length === 0 && this.selectedTaps.length > 0) {
                        this.autoTaps = [...this.selectedTaps];
                    }
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                setAccessMode(mode) {
                    if (mode === 'full') {
                        this.hasFullAccess = true;
                    } else {
                        this.hasFullAccess = false;
                        // Pastikan jika belum ada tap yang dipilih, set default
                        if (this.selectedTaps.length === 0) {
                            // Expand area pertama agar langsung terlihat
                            if (this.territoryTree.length > 0) {
                                this.expandedAreas[this.territoryTree[0].id] = true;
                            }
                        }
                    }
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                isTapSelected(tapId) {
                    return this.selectedTaps.includes(tapId);
                },

                toggleTap(tap) {
                    const idx = this.selectedTaps.indexOf(tap.id);
                    if (idx > -1) {
                        this.selectedTaps.splice(idx, 1);
                        const aIdx = this.autoTaps.indexOf(tap.id);
                        if (aIdx > -1) this.autoTaps.splice(aIdx, 1);
                        const whIds = tap.warehouses.map(w => w.id);
                        this.customWhs = this.customWhs.filter(wId => !whIds.includes(wId));
                    } else {
                        this.selectedTaps.push(tap.id);
                        if (!this.autoTaps.includes(tap.id)) {
                            this.autoTaps.push(tap.id);
                        }
                    }
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                isAutoTap(tapId) {
                    return this.autoTaps.includes(tapId);
                },

                setAutoTap(tap, isAuto) {
                    const idx = this.autoTaps.indexOf(tap.id);
                    if (isAuto) {
                        if (idx === -1) this.autoTaps.push(tap.id);
                    } else {
                        if (idx > -1) this.autoTaps.splice(idx, 1);
                        tap.warehouses.forEach(w => {
                            if (!this.customWhs.includes(w.id)) {
                                this.customWhs.push(w.id);
                            }
                        });
                        this.expandedTaps[tap.id] = true;
                    }
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                isWhSelected(whId, tapId) {
                    if (this.isAutoTap(tapId)) return true;
                    return this.customWhs.includes(whId);
                },

                toggleWh(whId) {
                    const idx = this.customWhs.indexOf(whId);
                    if (idx > -1) {
                        this.customWhs.splice(idx, 1);
                    } else {
                        this.customWhs.push(whId);
                    }
                },

                isClusterSelected(cluster) {
                    if (!cluster.taps || cluster.taps.length === 0) return false;
                    return cluster.taps.every(t => this.isTapSelected(t.id));
                },

                toggleCluster(cluster) {
                    const allSelected = this.isClusterSelected(cluster);
                    cluster.taps.forEach(t => {
                        const idx = this.selectedTaps.indexOf(t.id);
                        if (allSelected) {
                            if (idx > -1) this.selectedTaps.splice(idx, 1);
                            const aIdx = this.autoTaps.indexOf(t.id);
                            if (aIdx > -1) this.autoTaps.splice(aIdx, 1);
                        } else {
                            if (idx === -1) this.selectedTaps.push(t.id);
                            if (!this.autoTaps.includes(t.id)) this.autoTaps.push(t.id);
                        }
                    });
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                isAreaSelected(area) {
                    if (!area.clusters || area.clusters.length === 0) return false;
                    return area.clusters.every(c => this.isClusterSelected(c));
                },

                toggleArea(area) {
                    const allSelected = this.isAreaSelected(area);
                    area.clusters.forEach(c => {
                        c.taps.forEach(t => {
                            const idx = this.selectedTaps.indexOf(t.id);
                            if (allSelected) {
                                if (idx > -1) this.selectedTaps.splice(idx, 1);
                                const aIdx = this.autoTaps.indexOf(t.id);
                                if (aIdx > -1) this.autoTaps.splice(aIdx, 1);
                            } else {
                                if (idx === -1) this.selectedTaps.push(t.id);
                                if (!this.autoTaps.includes(t.id)) this.autoTaps.push(t.id);
                            }
                        });
                    });
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                toggleSelectAllAreas(selectAll) {
                    this.territoryTree.forEach(area => {
                        area.clusters.forEach(c => {
                            c.taps.forEach(t => {
                                const idx = this.selectedTaps.indexOf(t.id);
                                if (selectAll) {
                                    if (idx === -1) this.selectedTaps.push(t.id);
                                    if (!this.autoTaps.includes(t.id)) this.autoTaps.push(t.id);
                                } else {
                                    this.selectedTaps = [];
                                    this.autoTaps = [];
                                    this.customWhs = [];
                                }
                            });
                        });
                    });
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
