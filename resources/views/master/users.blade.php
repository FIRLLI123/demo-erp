<x-app-layout>
    <x-slot name="title">Master Pengguna & Pemetaan Sales</x-slot>
    <x-slot name="headerTitle">Master Akun & Pengguna</x-slot>
    <x-slot name="badge">User Management & Sales Mapping</x-slot>

    <div x-data="userMasterApp()">
        
        <!-- Top Action & Navigation Banner -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Master Akun Pengguna & Hak Akses</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-purple-100 text-purple-800 border border-purple-200">PT WAHANA PUTRA YUDHA</span>
                </h2>
                <p class="text-xs text-slate-500">Kelola kredensial login staf kantor, otorisasi peran (Role), serta pemetaan akun mobile ke tenaga sales kanvas lapangan</p>
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

        @if($errors->any())
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
                    <h3 class="font-bold text-slate-900 text-sm">Registrasi Akun Pengguna & Otorisasi Hak Akses</h3>
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
                        <input type="email" name="email" required placeholder="nama@wahanaputrayudha.co.id" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
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
        <!-- MODAL EDIT PENGGUNA                            -->
        <!-- ============================================== -->
        <template x-if="editModalOpen && editData">
            <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                <div class="bg-white rounded-2xl border border-slate-200 max-w-lg w-full p-6 shadow-2xl" @click.outside="editModalOpen = false">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                        <h3 class="font-bold text-slate-900 text-sm">Edit Akun Pengguna [<span x-text="editData.email"></span>]</h3>
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
        <!-- KPI METRICS SUMMARY                            -->
        <!-- ============================================== -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <span class="text-xs font-bold text-slate-400 block mb-1">Total Akun Pengguna</span>
                <span class="text-2xl font-extrabold text-slate-900">{{ count($users) }}</span>
                <span class="text-[11px] text-slate-500 block mt-1">Staf kantor & tenaga sales lapangan</span>
            </div>

            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <span class="text-xs font-bold text-slate-400 block mb-1">Akun Berstatus Aktif</span>
                <span class="text-2xl font-extrabold text-emerald-600">
                    {{ $users->where('is_active', 1)->count() }}
                </span>
                <span class="text-[11px] text-emerald-600/80 block mt-1">Dapat melakukan otorisasi login</span>
            </div>

            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <span class="text-xs font-bold text-slate-400 block mb-1">Akun Sales Force Terpetakan</span>
                <span class="text-2xl font-extrabold text-indigo-600">
                    {{ $users->whereNotNull('sales_id')->count() }}
                </span>
                <span class="text-[11px] text-indigo-500 block mt-1">Sinkron dengan gudang kanvas motor</span>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- TABEL MASTER USER                              -->
        <!-- ============================================== -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <span class="text-xs font-extrabold text-slate-800">Daftar Pengguna Sistem & Pemetaan Sales</span>
                <span class="text-xs text-slate-500 font-mono">{{ count($users) }} Pengguna Terdaftar</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Pengguna (Nama & Email)</th>
                            <th class="py-3 px-4">Peran (Role)</th>
                            <th class="py-3 px-4">Pemetaan ke Sales Force</th>
                            <th class="py-3 px-4">Wilayah TAP Sales</th>
                            <th class="py-3 px-4">No. HP / WA</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center w-24">Aksi</th>
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
                                        {{ $u->role_slug === 'super_admin' ? 'bg-rose-100 text-rose-800 border border-rose-200' : ($u->role_slug === 'sales' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : ($u->role_slug === 'finance' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-slate-100 text-slate-800 border border-slate-200')) }}">
                                        {{ $u->role_name ?? 'Staf' }}
                                    </span>
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
                                        <span class="text-slate-400 italic text-[11px]">Bukan Sales Lapangan</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-slate-600">
                                    {{ $u->sales_tap ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4 font-mono text-slate-600">
                                    {{ $u->phone ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($u->is_active)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            ACTIVE
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                            INACTIVE
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <button type="button" 
                                            @click="openEditModal({{ json_encode($u) }})" 
                                            class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold transition-all">
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

    @push('scripts')
    <script>
        function userMasterApp() {
            return {
                createOpen: false,
                editModalOpen: false,
                editData: null,

                openEditModal(data) {
                    this.editData = { ...data };
                    this.editModalOpen = true;
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
