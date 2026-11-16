<x-app-layout>
    <x-slot name="title">Master Pengguna & Otorisasi Wilayah</x-slot>
    <x-slot name="headerTitle">Master Akun & Pengguna</x-slot>
    <x-slot name="badge">User Management, Multi-Cluster & Warehouse Mapping</x-slot>

    <div x-data="userMasterApp()">
        
        <!-- Top Action & Navigation Banner -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Master Akun Pengguna & Hak Akses</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-purple-100 text-purple-800 border border-purple-200">PT WAHANA PUTRA YUDHA</span>
                </h2>
                <p class="text-xs text-slate-500">Kelola otorisasi wilayah (Cluster/TAP), hak transaksi gudang fisik (auto/custom), mode monitoring HO, serta pemetaan sales kanvas</p>
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
                    <h3 class="font-bold text-slate-900 text-sm">Registrasi Akun Pengguna & Otorisasi Wilayah</h3>
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
                        <input type="text" name="name" required placeholder="Contoh: Rian Hidayat / Firman" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
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
                        <label class="block text-xs font-bold text-slate-700 mb-1">Cakupan Wilayah Cluster (Opsional)</label>
                        <select name="cluster_id" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                            <option value="">-- Tanpa Batasan Cluster Tertentu --</option>
                            @foreach($clusters as $c)
                                <option value="{{ $c->id }}">📍 Cluster {{ $c->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-slate-400 mt-1">Pilih jika akun ini adalah Manager/Admin tingkat Cluster</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Penugasan TAP Utama</label>
                        <select name="tap_id" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                            <option value="">🌟 Akses Semua Area (IT DEV & Super Admin)</option>
                            @foreach($taps as $tap)
                                <option value="{{ $tap->id }}">🏢 {{ $tap->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-slate-400 mt-1">Kosongkan jika akun ini adalah IT DEV yang berhak membuka seluruh area</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pemetaan ke Tenaga Sales (Opsional)</label>
                        <select name="sales_id" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                            <option value="">-- Bukan Akun Sales / Non-Kanvas --</option>
                            @foreach($salesList as $s)
                                <option value="{{ $s->id }}">[{{ $s->code }}] {{ $s->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-slate-400 mt-1">Pilih bila akun ini digunakan sales saat input SO mobile di lapangan</p>
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

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Cluster Wilayah</label>
                                    <select name="cluster_id" x-model="editData.cluster_id" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                        <option value="">-- Tanpa Cluster --</option>
                                        @foreach($clusters as $c)
                                            <option value="{{ $c->id }}">📍 Cluster {{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">TAP Utama</label>
                                    <select name="tap_id" x-model="editData.tap_id" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                        <option value="">🌟 Semua Area (IT DEV)</option>
                                        @foreach($taps as $tap)
                                            <option value="{{ $tap->id }}">🏢 {{ $tap->name }}</option>
                                        @endforeach
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
        <!-- MODAL OTORISASI WILAYAH & GUDANG TRANSAKSI     -->
        <!-- ============================================== -->
        <template x-if="mappingModalOpen && mappingUser">
            <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-xs transition-opacity" @click="mappingModalOpen = false"></div>
                <div class="relative bg-white rounded-3xl border border-slate-200 max-w-2xl w-full p-6 md:p-7 shadow-2xl overflow-hidden z-10">
                    
                    <!-- Modal Header -->
                    <div class="flex items-start justify-between pb-4 mb-5 border-b border-slate-100">
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

                    <form :action="'/master/users/mapping/' + mappingUser.id" method="POST">
                        @csrf

                        <!-- ============================================ -->
                        <!-- 1. MODE KHUSUS: HO MONITORING (READ-ONLY)    -->
                        <!-- ============================================ -->
                        <div class="mb-5 p-4 rounded-2xl border-2 transition-all"
                             :class="mappingIsHo ? 'bg-amber-50/70 border-amber-300' : 'bg-slate-50 border-slate-200'">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div class="p-2 rounded-xl" :class="mappingIsHo ? 'bg-amber-500 text-white' : 'bg-slate-200 text-slate-600'">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-extrabold text-slate-900">Mode Akun HO / Monitoring (Read-Only)</h4>
                                        <p class="text-[11px] text-slate-500">Hanya melihat data laporan & grafik statistik, <strong>tanpa hak transaksi barang fisik</strong>.</p>
                                    </div>
                                </div>
                                
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_ho_readonly" value="1" x-model="mappingIsHo" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-600"></div>
                                </label>
                            </div>

                            <template x-if="mappingIsHo">
                                <div class="mt-3 pt-3 border-t border-amber-200/80 text-[11px] text-amber-900 flex items-center gap-2">
                                    <i data-lucide="info" class="w-4 h-4 shrink-0 text-amber-700"></i>
                                    <span>Akun ini akan terkunci dari transaksi GR, SO, GD, TTK, dan mutasi. Seluruh gudang akan bernilai 0 (Aman dari kesalahan input mutasi stok fisik).</span>
                                </div>
                            </template>
                        </div>

                        <!-- ============================================ -->
                        <!-- 2. PEMETAAN WILAYAH (CLUSTER & MULTI-TAP)    -->
                        <!-- ============================================ -->
                        <div class="mb-5 p-4 rounded-2xl bg-white border border-slate-200 shadow-xs">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h4 class="text-xs font-extrabold text-slate-900 flex items-center gap-1.5">
                                        <i data-lucide="map-pin" class="w-4 h-4 text-indigo-600"></i>
                                        <span>Cakupan Wilayah Kantor Cabang (TAP)</span>
                                    </h4>
                                    <p class="text-[11px] text-slate-500">Tentukan kantor TAP mana saja yang boleh dibuka/diswitch oleh pengguna ini.</p>
                                </div>
                                <div class="flex items-center gap-1 text-[11px]">
                                    <button type="button" @click="toggleAllTaps(true)" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-indigo-700 font-bold transition-all">Pilih Semua</button>
                                    <button type="button" @click="toggleAllTaps(false)" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold transition-all">Kosongkan</button>
                                </div>
                            </div>

                            <!-- Cluster Scope Selection -->
                            <div class="mb-3">
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Cakupan Induk Cluster (Opsional):</label>
                                <select name="cluster_id" x-model="mappingClusterId" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none">
                                    <option value="">-- Semua Cluster / Pilihan Bebas --</option>
                                    @foreach($clusters as $c)
                                        <option value="{{ $c->id }}">📍 Cluster {{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Multi-TAP Checkbox Grid -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-40 overflow-y-auto p-2 bg-slate-50 rounded-xl border border-slate-200/80">
                                <template x-for="tap in allTaps" :key="tap.id">
                                    <label class="flex items-center gap-2 p-2 rounded-lg bg-white border border-slate-200/60 hover:border-indigo-400 cursor-pointer text-xs transition-all shadow-2xs">
                                        <input type="checkbox" 
                                               name="tap_ids[]" 
                                               :value="tap.id" 
                                               x-model="selectedTapIds"
                                               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="font-bold text-slate-800" x-text="tap.name"></span>
                                    </label>
                                </template>
                            </div>
                        </div>

                        <!-- ============================================ -->
                        <!-- 3. OTORISASI GUDANG TRANSAKSI                -->
                        <!-- ============================================ -->
                        <div x-show="!mappingIsHo" x-transition class="mb-5 p-4 rounded-2xl bg-white border border-slate-200 shadow-xs">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 mb-3 border-b border-slate-100 gap-2">
                                <div>
                                    <h4 class="text-xs font-extrabold text-slate-900 flex items-center gap-1.5">
                                        <i data-lucide="boxes" class="w-4 h-4 text-emerald-600"></i>
                                        <span>Otorisasi Gudang Transaksi (PO, GR, SO, GD, Mutasi)</span>
                                    </h4>
                                    <p class="text-[11px] text-slate-500">Tentukan gudang fisik mana saja yang boleh diproses oleh user.</p>
                                </div>
                            </div>

                            <!-- Opsi Otomatis vs Custom Checklist -->
                            <div class="mb-4 p-3.5 rounded-xl bg-emerald-50/60 border border-emerald-200 flex items-start gap-3">
                                <input type="checkbox" 
                                       id="auto_include_sales_wh" 
                                       name="auto_include_sales_wh" 
                                       value="1" 
                                       x-model="mappingAutoInclude" 
                                       class="mt-1 rounded border-emerald-400 text-emerald-600 focus:ring-emerald-500">
                                <div>
                                    <label for="auto_include_sales_wh" class="block text-xs font-extrabold text-emerald-950 cursor-pointer">
                                        ⚡ Otomatis Masukkan Seluruh Gudang TAP & Seluruh Sales Baru (Rekomendasi)
                                    </label>
                                    <p class="text-[11px] text-emerald-800/90 mt-0.5">
                                        Seluruh Gudang Utama (MAIN), Gudang Inventory (INV), dan <strong>seluruh tas motor sales lama maupun sales baru yang akan datang</strong> otomatis diizinkan tanpa perlu setting ulang!
                                    </p>
                                </div>
                            </div>

                            <!-- Daftar Pilihan Gudang Spesifik (Jika Custom / auto_include_sales_wh = false) -->
                            <div x-show="!mappingAutoInclude" x-cloak x-transition class="space-y-3">
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="font-bold text-slate-700">Daftar Gudang Spesifik (Pengecualian Aktif):</span>
                                    <div class="flex items-center gap-1">
                                        <button type="button" @click="toggleAllWhs(true)" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-emerald-700 font-bold transition-all">Centang Semua</button>
                                        <button type="button" @click="toggleAllWhs(false)" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold transition-all">Kosongkan</button>
                                    </div>
                                </div>

                                <div class="max-h-56 overflow-y-auto space-y-1.5 p-2 bg-slate-50 rounded-xl border border-slate-200">
                                    <template x-for="wh in filteredWarehouses" :key="wh.id">
                                        <label class="flex items-center justify-between p-2 rounded-lg bg-white border border-slate-200/80 hover:border-emerald-500 cursor-pointer text-xs transition-all shadow-2xs">
                                            <div class="flex items-center gap-2">
                                                <input type="checkbox" 
                                                       name="warehouse_ids[]" 
                                                       :value="wh.id" 
                                                       x-model="selectedWhIds"
                                                       class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                                <div>
                                                    <span class="font-bold text-slate-900 block" x-text="wh.name"></span>
                                                    <span class="font-mono text-[10px] text-slate-400" x-text="wh.code"></span>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-[10px] px-1.5 py-0.5 rounded font-bold font-mono"
                                                      :class="wh.type === 'MAIN' ? 'bg-emerald-100 text-emerald-800' : (wh.type === 'INVENTORY' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800')"
                                                      x-text="wh.type"></span>
                                                <span class="text-[10px] font-bold text-slate-500" x-text="wh.tap_name"></span>
                                            </div>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Actions Footer -->
                        <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                            <button type="button" @click="mappingModalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-all">
                                Batal
                            </button>
                            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold rounded-xl text-xs flex items-center gap-2 shadow-md shadow-indigo-600/20 transition-all active:scale-95">
                                <i data-lucide="check" class="w-4 h-4"></i>
                                <span>Simpan Otorisasi Wilayah & Gudang</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- ============================================== -->
        <!-- KPI METRICS SUMMARY                            -->
        <!-- ============================================== -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <span class="text-xs font-bold text-slate-400 block mb-1">Total Pengguna</span>
                <span class="text-2xl font-extrabold text-slate-900">{{ count($users) }}</span>
                <span class="text-[11px] text-slate-500 block mt-1">Staf kantor & sales</span>
            </div>

            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <span class="text-xs font-bold text-slate-400 block mb-1">Akun Berstatus Aktif</span>
                <span class="text-2xl font-extrabold text-emerald-600">
                    {{ $users->where('is_active', 1)->count() }}
                </span>
                <span class="text-[11px] text-emerald-600/80 block mt-1">Dapat melakukan login</span>
            </div>

            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <span class="text-xs font-bold text-slate-400 block mb-1">Akun HO (Monitoring)</span>
                <span class="text-2xl font-extrabold text-amber-600">
                    {{ $users->where('is_ho_readonly', 1)->count() }}
                </span>
                <span class="text-[11px] text-amber-600/80 block mt-1">Read-Only laporan, 0 transaksi</span>
            </div>

            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <span class="text-xs font-bold text-slate-400 block mb-1">Sales Kanvas Terpetakan</span>
                <span class="text-2xl font-extrabold text-indigo-600">
                    {{ $users->whereNotNull('sales_id')->count() }}
                </span>
                <span class="text-[11px] text-indigo-500 block mt-1">Gudang motor otomatis terkunci</span>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- TABEL MASTER USER                              -->
        <!-- ============================================== -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <span class="text-xs font-extrabold text-slate-800">Daftar Pengguna Sistem & Hak Akses Wilayah</span>
                <span class="text-xs text-slate-500 font-mono">{{ count($users) }} Pengguna Terdaftar</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Pengguna (Nama & Email)</th>
                            <th class="py-3 px-4">Peran (Role)</th>
                            <th class="py-3 px-4">Cakupan Wilayah (Cluster / TAP)</th>
                            <th class="py-3 px-4">Otorisasi Gudang Transaksi</th>
                            <th class="py-3 px-4">Pemetaan Sales</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center w-36">Aksi Otorisasi</th>
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
                                    @if(in_array($u->role_slug, ['it_dev', 'super_admin']))
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200 font-bold text-[10px] font-mono">
                                            <i data-lucide="shield" class="w-3 h-3 text-rose-600"></i>
                                            <span>ALL AREAS (IT DEV)</span>
                                        </span>
                                    @elseif(!empty($u->assigned_cluster_name))
                                        <div>
                                            <span class="font-bold text-indigo-700 text-xs block">📍 Cluster {{ $u->assigned_cluster_name }}</span>
                                            <span class="text-[10px] text-slate-500 font-mono">{{ $u->taps_count }} TAP Terpetakan</span>
                                        </div>
                                    @elseif($u->taps_count > 1)
                                        <span class="inline-flex items-center gap-1 font-bold text-slate-800 text-xs bg-indigo-50 text-indigo-800 px-2 py-0.5 rounded border border-indigo-200">
                                            <i data-lucide="building-2" class="w-3.5 h-3.5 text-indigo-600"></i>
                                            <span>{{ $u->taps_count }} TAP Wilayah</span>
                                        </span>
                                    @elseif($u->assigned_tap_name)
                                        <span class="inline-flex items-center gap-1 font-bold text-slate-800 text-xs">
                                            <i data-lucide="building-2" class="w-3.5 h-3.5 text-amber-600"></i>
                                            <span>{{ $u->assigned_tap_name }}</span>
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">Belum Ditugaskan</span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4">
                                    @if($u->is_ho_readonly)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-300 font-bold text-[10px]">
                                            <i data-lucide="eye" class="w-3 h-3 text-amber-700"></i>
                                            <span>HO Read-Only (0 Transaksi)</span>
                                        </span>
                                    @elseif($u->sales_id)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-purple-100 text-purple-800 border border-purple-200 font-bold text-[10px]">
                                            <i data-lucide="bike" class="w-3 h-3 text-purple-600"></i>
                                            <span>Gudang Motor Sendiri</span>
                                        </span>
                                    @elseif($u->auto_include_sales_wh)
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
                                        <span class="text-slate-400 italic text-[11px]">Bukan Sales</span>
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
                                                title="Atur Otorisasi Wilayah & Gudang Transaksi">
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
                usersList: @json($users),
                allWarehouses: @json($warehouses),
                allTaps: @json($taps),

                // State form mapping
                mappingClusterId: '',
                mappingIsHo: false,
                mappingAutoInclude: true,
                selectedTapIds: [],
                selectedWhIds: [],

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
                    this.mappingClusterId = u.cluster_id || '';
                    this.mappingIsHo = Boolean(u.is_ho_readonly);
                    this.mappingAutoInclude = Boolean(u.auto_include_sales_wh);
                    this.selectedTapIds = Array.isArray(u.mapped_tap_ids) ? [...u.mapped_tap_ids] : [];
                    this.selectedWhIds = Array.isArray(u.mapped_warehouse_ids) ? [...u.mapped_warehouse_ids] : [];
                    this.mappingModalOpen = true;

                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    });
                },

                toggleAllTaps(checkAll) {
                    if (checkAll) {
                        this.selectedTapIds = this.allTaps.map(t => t.id);
                    } else {
                        this.selectedTapIds = [];
                    }
                },

                toggleAllWhs(checkAll) {
                    if (checkAll) {
                        this.selectedWhIds = this.filteredWarehouses.map(w => w.id);
                    } else {
                        this.selectedWhIds = [];
                    }
                },

                get filteredWarehouses() {
                    if (this.selectedTapIds.length === 0) {
                        return this.allWarehouses;
                    }
                    return this.allWarehouses.filter(w => this.selectedTapIds.includes(w.tap_id));
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
