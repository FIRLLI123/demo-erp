<x-app-layout>
    <x-slot name="title">Master Pengguna & Otorisasi Wilayah</x-slot>
    <x-slot name="headerTitle">Master Akun & Pengguna</x-slot>
    <x-slot name="badge">User Management & Territory Mapping</x-slot>

    <div x-data="userMasterApp()">
        
        <!-- Top Action & Navigation Banner -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Master Pengguna & Otorisasi Wilayah</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-purple-100 text-purple-800 border border-purple-200">PT WAHANA PUTRA YUDHA</span>
                </h2>
                <p class="text-xs text-slate-500">Kelola akun pengguna, cakupan wilayah, dan otorisasi gudang transaksi.</p>
            </div>

            <button type="button" 
                    @click="createOpen = !createOpen" 
                    class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-sm transition-all active:scale-95 shrink-0">
                <i data-lucide="user-plus" class="w-4 h-4 text-emerald-400"></i>
                <span x-text="createOpen ? 'Tutup Formulir' : '+ Tambah Pengguna Baru'"></span>
            </button>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between">
                <span class="flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                    {{ session('success') }}
                </span>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center justify-between">
                <span class="flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                    {{ session('error') }}
                </span>
                <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800">&times;</button>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- ============================================== -->
        <!-- FORM TAMBAH USER BARU (INLINE CARD)            -->
        <!-- ============================================== -->
        <div x-show="createOpen" x-cloak x-transition class="mb-6 bg-white rounded-2xl border-2 border-indigo-500/30 p-5 shadow-lg">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-indigo-100 text-indigo-700">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                    </span>
                    <h3 class="font-bold text-slate-900 text-sm">Registrasi Akun Pengguna Baru</h3>
                </div>
                <button @click="createOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('master.users.store') }}">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap Pengguna <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required placeholder="Contoh: Rian Hidayat" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Email Login <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" required placeholder="nama@wahanapos.com" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kata Sandi (Password) <span class="text-rose-500">*</span></label>
                        <input type="password" name="password" required minlength="6" placeholder="Minimal 6 karakter" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Peran / Otorisasi Role <span class="text-rose-500">*</span></label>
                        <select name="role_id" required class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }} ({{ $role->description }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pemetaan ke Tenaga Sales (Opsional)</label>
                        <select name="sales_id" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                            <option value="">-- Bukan Akun Sales / Non-Kanvas --</option>
                            @foreach($salesList as $s)
                                <option value="{{ $s->id }}">[{{ $s->code }}] {{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Telepon / WhatsApp</label>
                        <input type="text" name="phone" placeholder="0812xxxxxxxx" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="createOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-sm transition-all">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Akun Pengguna</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- ============================================== -->
        <!-- MODAL EDIT PENGGUNA (PROFIL & PASSWORD)        -->
        <!-- ============================================== -->
        <template x-if="editModalOpen && editData">
            <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="editModalOpen = false"></div>
                <div class="relative bg-white rounded-2xl border border-slate-200 max-w-lg w-full p-6 shadow-2xl z-10">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                        <h3 class="font-bold text-slate-900 text-sm">Edit Profil Pengguna [<span x-text="editData.email"></span>]</h3>
                        <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600">&times;</button>
                    </div>

                    <form :action="'/master/users/update/' + editData.id" method="POST">
                        @csrf
                        <div class="space-y-3 mb-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap</label>
                                <input type="text" name="name" x-model="editData.name" required class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Email</label>
                                    <input type="email" name="email" x-model="editData.email" required class="w-full text-xs font-medium bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">No. HP / WA</label>
                                    <input type="text" name="phone" x-model="editData.phone" class="w-full text-xs font-medium bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Peran (Role)</label>
                                    <select name="role_id" x-model="editData.role_id" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Status Keaktifan</label>
                                    <select name="is_active" x-model="editData.is_active" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                        <option value="1">Aktif (Dapat Login)</option>
                                        <option value="0">Nonaktif (Diblokir)</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Pemetaan ke Sales Force</label>
                                <select name="sales_id" x-model="editData.sales_id" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                    <option value="">-- Bukan Akun Sales / Non-Kanvas --</option>
                                    @foreach($salesList as $s)
                                        <option value="{{ $s->id }}">[{{ $s->code }}] {{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Ganti Kata Sandi (Kosongkan bila tidak diubah)</label>
                                <input type="password" name="password" minlength="6" placeholder="Masukkan password baru..." class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                            <button type="button" @click="editModalOpen = false" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl text-xs">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- ============================================== -->
        <!-- MODAL CASCADING TREE: AREA -> CLUSTER -> TAP   -->
        <!-- ============================================== -->
        <template x-if="mappingModalOpen && mappingUser">
            <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-xs transition-opacity" @click="mappingModalOpen = false"></div>
                <div class="relative bg-white rounded-3xl border border-slate-200 max-w-3xl w-full p-6 md:p-7 shadow-2xl overflow-hidden z-10 max-h-[90vh] flex flex-col">
                    
                    <!-- Modal Header -->
                    <div class="flex items-start justify-between pb-4 mb-4 border-b border-slate-100 shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold">
                                <i data-lucide="shield-check" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-extrabold text-slate-900 text-base" x-text="mappingUser.name"></h3>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 font-mono" x-text="mappingUser.role_name"></span>
                                </div>
                                <p class="text-xs text-slate-500 font-mono" x-text="mappingUser.email"></p>
                            </div>
                        </div>
                        <button @click="mappingModalOpen = false" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition-all">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <form :action="'/master/users/mapping/' + mappingUser.id" method="POST" class="overflow-y-auto pr-1 flex-1 space-y-4">
                        @csrf

                        <!-- ============================================ -->
                        <!-- 1. OPSI UTAMA: AKSES PENUH VS WILAYAH KHUSUS -->
                        <!-- ============================================ -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Akses Penuh -->
                            <label class="p-4 rounded-2xl border-2 cursor-pointer transition-all flex items-start gap-3"
                                   :class="hasFullAccess ? 'bg-indigo-50/80 border-indigo-600 shadow-xs' : 'bg-slate-50 border-slate-200 hover:border-slate-300'">
                                <input type="radio" name="has_full_access" value="1" x-model="hasFullAccess" :value="true" class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                                <div>
                                    <span class="font-extrabold text-xs text-slate-900 block">Akses Penuh</span>
                                    <span class="text-[11px] text-slate-500 block mt-0.5">Membuka seluruh area, cluster, TAP, dan gudang transaksi.</span>
                                </div>
                            </label>

                            <!-- Batasi Sesuai Wilayah Tertentu -->
                            <label class="p-4 rounded-2xl border-2 cursor-pointer transition-all flex items-start gap-3"
                                   :class="!hasFullAccess ? 'bg-indigo-50/80 border-indigo-600 shadow-xs' : 'bg-slate-50 border-slate-200 hover:border-slate-300'">
                                <input type="radio" name="has_full_access" value="0" x-model="hasFullAccess" :value="false" class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                                <div>
                                    <span class="font-extrabold text-xs text-slate-900 block">Pilih Wilayah Tertentu</span>
                                    <span class="text-[11px] text-slate-500 block mt-0.5">Dibatasi sesuai checklist Area, Cluster, TAP, dan Gudang di bawah.</span>
                                </div>
                            </label>
                        </div>

                        <!-- ============================================ -->
                        <!-- 2. MODE MONITORING HO (READ-ONLY)            -->
                        <!-- ============================================ -->
                        <div class="p-3.5 rounded-2xl border transition-all flex items-center justify-between"
                             :class="isHoReadOnly ? 'bg-amber-50 border-amber-300' : 'bg-slate-50 border-slate-200'">
                            <div class="flex items-center gap-2.5">
                                <div class="p-1.5 rounded-xl" :class="isHoReadOnly ? 'bg-amber-500 text-white' : 'bg-slate-200 text-slate-600'">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-slate-900 block">Mode Monitoring (Hanya Lihat Laporan)</span>
                                    <span class="text-[11px] text-slate-500 block">Hanya memantau report wilayah, tanpa hak transaksi fisik.</span>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_ho_readonly" value="1" x-model="isHoReadOnly" class="sr-only peer">
                                <div class="w-10 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-amber-600"></div>
                            </label>
                        </div>

                        <!-- ============================================ -->
                        <!-- 3. POHON WILAYAH: AREA -> CLUSTER -> TAP     -->
                        <!-- ============================================ -->
                        <div x-show="!hasFullAccess" x-transition class="space-y-3 pt-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-extrabold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                                    <i data-lucide="network" class="w-4 h-4 text-indigo-600"></i>
                                    <span>Pohon Struktur Wilayah & Gudang</span>
                                </span>
                                <span class="text-[11px] text-slate-400 font-mono">Centang TAP untuk mengizinkan transaksi</span>
                            </div>

                            <div class="space-y-3">
                                <template x-for="area in territoryTree" :key="area.id">
                                    <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-2xs">
                                        
                                        <!-- Header Area -->
                                        <div class="p-3 bg-slate-100/70 border-b border-slate-200 flex items-center justify-between">
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="checkbox" 
                                                       @change="toggleArea(area)"
                                                       :checked="isAreaSelected(area)"
                                                       class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                                <span class="font-extrabold text-slate-900 text-xs flex items-center gap-1.5">
                                                    <i data-lucide="globe" class="w-3.5 h-3.5 text-indigo-600"></i>
                                                    <span x-text="area.name"></span>
                                                </span>
                                            </label>

                                            <button type="button" 
                                                    @click="expandedAreas[area.id] = !expandedAreas[area.id]" 
                                                    class="text-[11px] font-bold text-slate-500 hover:text-slate-800 flex items-center gap-1">
                                                <span x-text="expandedAreas[area.id] ? 'Tutup' : 'Buka Cluster'"></span>
                                                <i data-lucide="chevron-down" class="w-3 h-3 transition-transform" :class="expandedAreas[area.id] ? 'rotate-180' : ''"></i>
                                            </button>
                                        </div>

                                        <!-- Clusters List -->
                                        <div x-show="expandedAreas[area.id] !== false" class="p-3 space-y-3">
                                            <template x-for="cluster in area.clusters" :key="cluster.id">
                                                <div class="rounded-xl border border-slate-200/80 bg-slate-50/50 p-3">
                                                    
                                                    <!-- Header Cluster -->
                                                    <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-200/60">
                                                        <label class="flex items-center gap-2 cursor-pointer">
                                                            <input type="checkbox" 
                                                                   @change="toggleCluster(cluster)"
                                                                   :checked="isClusterSelected(cluster)"
                                                                   class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                                            <span class="font-bold text-slate-800 text-xs flex items-center gap-1">
                                                                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-amber-600"></i>
                                                                <span x-text="'Cluster ' + cluster.name"></span>
                                                            </span>
                                                        </label>

                                                        <span class="text-[10px] text-slate-400 font-mono" x-text="cluster.taps.length + ' TAP'"></span>
                                                    </div>

                                                    <!-- TAPs & Warehouses -->
                                                    <div class="grid grid-cols-1 gap-2.5 pt-1">
                                                        <template x-for="tap in cluster.taps" :key="tap.id">
                                                            <div class="p-2.5 rounded-lg bg-white border border-slate-200"
                                                                 :class="isTapSelected(tap.id) ? 'border-indigo-300 shadow-2xs' : ''">
                                                                
                                                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                                                    <!-- Checkbox TAP -->
                                                                    <label class="flex items-center gap-2 cursor-pointer">
                                                                        <input type="checkbox" 
                                                                               name="selected_taps[]"
                                                                               :value="tap.id"
                                                                               @change="toggleTap(tap)"
                                                                               :checked="isTapSelected(tap.id)"
                                                                               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                                                        <span class="font-extrabold text-xs text-slate-900" x-text="tap.name"></span>
                                                                    </label>

                                                                    <!-- Mode Gudang Toggle (Auto vs Custom) -->
                                                                    <template x-if="isTapSelected(tap.id)">
                                                                        <div class="flex items-center gap-2 text-[10px] font-bold">
                                                                            <label class="flex items-center gap-1 cursor-pointer bg-emerald-50 text-emerald-800 px-2 py-0.5 rounded border border-emerald-200">
                                                                                <input type="checkbox" 
                                                                                       name="auto_taps[]" 
                                                                                       :value="tap.id"
                                                                                       @change="setAutoTap(tap, $event.target.checked)"
                                                                                       :checked="isAutoTap(tap.id)"
                                                                                       class="rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500">
                                                                                <span>⚡ Semua Gudang (+Sales Baru)</span>
                                                                            </label>

                                                                            <button type="button" 
                                                                                    @click="expandedTaps[tap.id] = !expandedTaps[tap.id]" 
                                                                                    class="text-slate-500 hover:text-slate-800 flex items-center gap-0.5">
                                                                                <span x-text="expandedTaps[tap.id] ? 'Sembunyikan' : 'Rincian Gudang'"></span>
                                                                                <i data-lucide="chevron-down" class="w-3 h-3" :class="expandedTaps[tap.id] ? 'rotate-180' : ''"></i>
                                                                            </button>
                                                                        </div>
                                                                    </template>
                                                                </div>

                                                                <!-- Daftar Rincian Gudang TAP (Bisa dipilih satuan jika ingin kecualikan sales tertentu) -->
                                                                <div x-show="isTapSelected(tap.id) && (expandedTaps[tap.id] || !isAutoTap(tap.id))" 
                                                                     x-cloak x-transition 
                                                                     class="mt-2.5 pt-2 border-t border-slate-100 space-y-1 pl-6">
                                                                    
                                                                    <template x-for="wh in tap.warehouses" :key="wh.id">
                                                                        <label class="flex items-center justify-between p-1.5 rounded hover:bg-slate-50 cursor-pointer text-xs">
                                                                            <div class="flex items-center gap-2">
                                                                                <input type="checkbox" 
                                                                                       name="custom_warehouses[]" 
                                                                                       :value="wh.id"
                                                                                       :disabled="isAutoTap(tap.id)"
                                                                                       @change="toggleWh(wh.id)"
                                                                                       :checked="isWhSelected(wh.id, tap.id)"
                                                                                       class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                                                                <span class="font-medium text-slate-800" x-text="wh.name"></span>
                                                                            </div>
                                                                            <span class="text-[9px] px-1.5 py-0.2 rounded font-mono font-bold"
                                                                                  :class="wh.type === 'MAIN' ? 'bg-emerald-100 text-emerald-800' : (wh.type === 'INVENTORY' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800')"
                                                                                  x-text="wh.type"></span>
                                                                        </label>
                                                                    </template>

                                                                    <template x-if="isAutoTap(tap.id)">
                                                                        <p class="text-[10px] text-emerald-700 italic pt-1">
                                                                            * Seluruh gudang di atas dan sales baru otomatis aktif. Hilangkan centang "⚡ Semua Gudang" di atas jika ingin mengecualikan sales tertentu.
                                                                        </p>
                                                                    </template>
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

                        <!-- Modal Actions Footer -->
                        <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100 shrink-0">
                            <button type="button" @click="mappingModalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-all">
                                Batal
                            </button>
                            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold rounded-xl text-xs flex items-center gap-2 shadow-md shadow-indigo-600/20 transition-all active:scale-95">
                                <i data-lucide="check" class="w-4 h-4"></i>
                                <span>Simpan Otorisasi</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- ============================================== -->
        <!-- TABEL MASTER USER                              -->
        <!-- ============================================== -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <span class="text-xs font-extrabold text-slate-800">Daftar Pengguna Sistem</span>
                <span class="text-xs text-slate-500 font-mono">{{ count($users) }} Pengguna</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Pengguna</th>
                            <th class="py-3 px-4">Peran (Role)</th>
                            <th class="py-3 px-4">Cakupan Wilayah</th>
                            <th class="py-3 px-4">Otorisasi Transaksi</th>
                            <th class="py-3 px-4">Sales Force</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center w-36">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($users as $u)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-slate-700 to-slate-900 flex items-center justify-center text-white font-bold text-xs shadow-xs">
                                            {{ strtoupper(substr($u->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <span class="font-extrabold text-slate-900 block text-xs">{{ $u->name }}</span>
                                            <span class="text-[11px] text-slate-500 font-mono">{{ $u->email }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold 
                                        {{ in_array($u->role_slug, ['it_dev', 'super_admin']) ? 'bg-rose-100 text-rose-800 border border-rose-200' : ($u->role_slug === 'sales' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : ($u->role_slug === 'finance' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-slate-100 text-slate-800 border border-slate-200')) }}">
                                        {{ $u->role_name ?? 'Staf' }}
                                    </span>
                                </td>

                                <td class="py-3.5 px-4">
                                    @if($u->has_full_access || in_array($u->role_slug, ['it_dev', 'super_admin']))
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200 font-bold text-[10px]">
                                            <i data-lucide="globe" class="w-3.5 h-3.5 text-indigo-600"></i>
                                            <span>Akses Penuh</span>
                                        </span>
                                    @elseif($u->taps_count > 0)
                                        <span class="inline-flex items-center gap-1 font-bold text-slate-800 text-xs bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
                                            <i data-lucide="building-2" class="w-3.5 h-3.5 text-indigo-600"></i>
                                            <span>{{ $u->taps_count }} TAP Wilayah</span>
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">Belum Diatur</span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4">
                                    @if($u->is_ho_readonly)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-300 font-bold text-[10px]">
                                            <i data-lucide="eye" class="w-3 h-3 text-amber-700"></i>
                                            <span>Monitoring (Read-Only)</span>
                                        </span>
                                    @elseif($u->sales_id)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-purple-100 text-purple-800 border border-purple-200 font-bold text-[10px]">
                                            <i data-lucide="bike" class="w-3 h-3 text-purple-600"></i>
                                            <span>Gudang Sales Sendiri</span>
                                        </span>
                                    @elseif($u->has_full_access)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 font-bold text-[10px]">
                                            <i data-lucide="check-check" class="w-3 h-3 text-emerald-600"></i>
                                            <span>Semua Gudang</span>
                                        </span>
                                    @elseif(!empty($u->auto_tap_ids) && count($u->auto_tap_ids) > 0)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 font-bold text-[10px]">
                                            <i data-lucide="zap" class="w-3 h-3 text-emerald-600"></i>
                                            <span>Otomatis TAP (+Sales Baru)</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 border border-blue-200 font-bold text-[10px]">
                                            <i data-lucide="check-square" class="w-3 h-3 text-blue-600"></i>
                                            <span>Custom {{ $u->warehouses_count }} Gudang</span>
                                        </span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4">
                                    @if($u->sales_code)
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 border border-indigo-200 font-mono font-bold text-[10px]">
                                                {{ $u->sales_code }}
                                            </span>
                                            <span class="font-bold text-slate-800">{{ $u->sales_name }}</span>
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">-</span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-center">
                                    <x-master-status-toggle entity="users" :id="$u->id" :name="$u->name" :active="(bool)$u->is_active" />
                                </td>

                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" 
                                                @click.stop="openMappingModal({{ $u->id }})" 
                                                class="px-2.5 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-[11px] font-bold transition-all flex items-center gap-1.5 shadow-2xs cursor-pointer active:scale-95"
                                                title="Atur Otorisasi Wilayah & Gudang">
                                            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                                            <span>Mapping</span>
                                        </button>

                                        <button type="button" 
                                                @click.stop="openEditModal({{ $u->id }})" 
                                                class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold transition-all cursor-pointer active:scale-95">
                                            Edit
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function userMasterApp() {
            return {
                createOpen: false,
                editModalOpen: false,
                editData: null,
                mappingModalOpen: false,
                mappingUser: null,
                territoryTree: @json($territoryTree),
                usersList: @json($users),

                // Form State
                hasFullAccess: false,
                isHoReadOnly: false,
                selectedTaps: [],
                autoTaps: [],
                customWhs: [],
                expandedAreas: {},
                expandedClusters: {},
                expandedTaps: {},

                openEditModal(userId) {
                    const u = this.usersList.find(x => x.id === userId);
                    if (!u) return;
                    this.editData = { ...u };
                    this.editModalOpen = true;
                },

                openMappingModal(userId) {
                    const u = this.usersList.find(x => x.id === userId);
                    if (!u) return;
                    this.mappingUser = u;
                    this.hasFullAccess = Boolean(u.has_full_access);
                    this.isHoReadOnly = Boolean(u.is_ho_readonly);
                    this.selectedTaps = Array.isArray(u.mapped_tap_ids) ? [...u.mapped_tap_ids] : [];
                    this.autoTaps = Array.isArray(u.auto_tap_ids) ? [...u.auto_tap_ids] : [];
                    this.customWhs = Array.isArray(u.mapped_warehouse_ids) ? [...u.mapped_warehouse_ids] : [];
                    
                    // Reset expanded
                    this.expandedAreas = {};
                    this.expandedClusters = {};
                    this.expandedTaps = {};
                    this.mappingModalOpen = true;

                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    });
                },

                // Tree Helper Methods
                isTapSelected(tapId) {
                    return this.selectedTaps.includes(tapId);
                },

                toggleTap(tap) {
                    const idx = this.selectedTaps.indexOf(tap.id);
                    if (idx > -1) {
                        this.selectedTaps.splice(idx, 1);
                        // Hapus dari autoTaps
                        const aIdx = this.autoTaps.indexOf(tap.id);
                        if (aIdx > -1) this.autoTaps.splice(aIdx, 1);
                        // Hapus whs
                        const whIds = tap.warehouses.map(w => w.id);
                        this.customWhs = this.customWhs.filter(wId => !whIds.includes(wId));
                    } else {
                        this.selectedTaps.push(tap.id);
                        // Default saat tap dicentang: auto all warehouses (+sales baru)
                        if (!this.autoTaps.includes(tap.id)) {
                            this.autoTaps.push(tap.id);
                        }
                    }
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
                        // Salin semua wh milik tap ini ke customWhs agar user bisa uncheck spesifik
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
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
