<x-app-layout>
    <x-slot name="title">Matriks Hak Akses & Role Pengguna</x-slot>
    <x-slot name="headerTitle">Matriks Role & Hak Akses</x-slot>

    <div x-data="roleMatrixApp()" x-init="initData()" class="pb-12">

        <!-- Top Header & Role Selector -->
        <div class="mb-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <i data-lucide="shield-check" class="w-5 h-5 text-indigo-600"></i>
                    <span>Pengaturan Hak Akses Role</span>
                </h2>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" 
                        @click="openAddRoleModal()" 
                        class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-2xs transition-all cursor-pointer">
                    <i data-lucide="plus" class="w-4 h-4 text-emerald-400"></i>
                    <span>Tambah Role</span>
                </button>
                <a href="{{ route('master.users') }}" 
                   class="px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold rounded-xl text-xs flex items-center gap-2 shadow-2xs transition-all cursor-pointer">
                    <i data-lucide="users" class="w-4 h-4 text-slate-400"></i>
                    <span>Kelola Pengguna (Users)</span>
                </a>
            </div>
        </div>

        <x-alert />

        <!-- Role Tab Pills & Actions Bar -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-3 mb-5 shadow-2xs">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                <div class="flex items-center gap-1.5 flex-wrap overflow-x-auto pb-1 sm:pb-0">
                    <template x-for="r in roles" :key="r.id">
                        <button type="button"
                                @click="selectRole(r.id)"
                                class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer whitespace-nowrap"
                                :class="selectedRoleId === r.id 
                                    ? 'bg-slate-900 text-white shadow-md' 
                                    : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200/80'">
                            <span class="w-2 h-2 rounded-full" 
                                  :class="{
                                      'bg-emerald-400': r.name === 'Super User',
                                      'bg-indigo-400': r.name === 'Manager support',
                                      'bg-cyan-400': r.name === 'SPV',
                                      'bg-amber-400': r.name === 'Admin sales',
                                      'bg-blue-400': r.name === 'Inventory',
                                      'bg-rose-400': r.name === 'Finance'
                                  }"></span>
                            <span x-text="r.name"></span>
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono"
                                  :class="selectedRoleId === r.id ? 'bg-slate-700 text-slate-200' : 'bg-slate-200 text-slate-600'"
                                  x-text="getRolePermCount(r.id)"></span>
                        </button>
                    </template>
                </div>

                <!-- Action Button untuk Role Terpilih: Edit Nama & Hapus -->
                <div class="flex items-center gap-2 shrink-0 pt-2 lg:pt-0 border-t lg:border-t-0 border-slate-100">
                    <button type="button" 
                            @click="openEditRoleModal()" 
                            class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-xs flex items-center gap-1 transition-all cursor-pointer">
                        <i data-lucide="edit-3" class="w-3.5 h-3.5 text-slate-500"></i>
                        <span>Ganti Nama</span>
                    </button>

                    <template x-if="selectedRole && !['super user', 'super_user'].includes(selectedRole.name.toLowerCase())">
                        <form method="POST" 
                              :action="`{{ url('master/roles/delete') }}/${selectedRoleId}`"
                              data-confirm="Konfirmasi Hapus Role"
                              :data-confirm-text="`Apakah Anda yakin ingin menghapus role [${selectedRole.name}]? Pastikan tidak ada akun pengguna yang terikat ke role ini.`"
                              data-confirm-type="danger"
                              data-confirm-btn="Ya, Hapus Role"
                              data-cancel-btn="Batal"
                              class="inline">
                            @csrf
                            <button type="submit" 
                                    class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold rounded-lg text-xs flex items-center gap-1 transition-all border border-rose-200 cursor-pointer">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5 text-rose-600"></i>
                                <span>Hapus Role</span>
                            </button>
                        </form>
                    </template>
                </div>
            </div>
        </div>

        <!-- Matriks Module & Permissions -->
        <div class="space-y-4">
            <template x-for="(modData, modName) in matrix" :key="modName">
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden transition-all">
                    <!-- Module Header -->
                    <div class="p-4 bg-slate-50/70 border-b border-slate-200/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold"
                                 :class="{
                                     'bg-emerald-100 text-emerald-700': modData.meta.color === 'emerald',
                                     'bg-cyan-100 text-cyan-700': modData.meta.color === 'cyan',
                                     'bg-amber-100 text-amber-700': modData.meta.color === 'amber',
                                     'bg-rose-100 text-rose-700': modData.meta.color === 'rose',
                                     'bg-indigo-100 text-indigo-700': modData.meta.color === 'indigo',
                                     'bg-slate-100 text-slate-700': modData.meta.color === 'slate'
                                 }">
                                <i :data-lucide="modData.meta.icon" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-extrabold text-slate-900" x-text="modName"></h3>
                            </div>
                        </div>

                        <!-- Module Quick Actions (Pilih Semua / Kosongkan) -->
                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button" 
                                    @click="toggleAllInModule(modData.permissions, 'grant')"
                                    class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 shadow-2xs cursor-pointer active:scale-95 transition-all">
                                Berikan Semua
                            </button>
                            <button type="button" 
                                    @click="toggleAllInModule(modData.permissions, 'revoke')"
                                    class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-white hover:bg-slate-100 text-slate-500 border border-slate-200 shadow-2xs cursor-pointer active:scale-95 transition-all">
                                Cabut Semua
                            </button>
                        </div>
                    </div>

                    <!-- Permissions Grid -->
                    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        <template x-for="p in modData.permissions" :key="p.id">
                            <div @click="togglePermission(p.name)"
                                 class="p-3 rounded-xl border transition-all flex items-center justify-between gap-3 cursor-pointer select-none group"
                                 :class="hasPerm(p.name) 
                                    ? (p.name.includes('void') ? 'bg-rose-50/70 border-rose-300 shadow-2xs' : 'bg-emerald-50/70 border-emerald-300 shadow-2xs')
                                    : 'bg-white hover:bg-slate-50 border-slate-200/80'">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5 mb-1">
                                        <template x-if="p.name.includes('void')">
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-mono font-extrabold bg-rose-200 text-rose-900">
                                                VOID / BATAL
                                            </span>
                                        </template>
                                        <template x-if="!p.name.includes('void') && p.name.includes('create')">
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-mono font-extrabold bg-blue-100 text-blue-800">
                                                TAMBAH / BUAT
                                            </span>
                                        </template>
                                        <template x-if="!p.name.includes('void') && !p.name.includes('create') && p.name.includes('view')">
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-mono font-extrabold bg-slate-100 text-slate-700">
                                                LIHAT / MONITOR
                                            </span>
                                        </template>
                                        <template x-if="!p.name.includes('void') && !p.name.includes('create') && !p.name.includes('view')">
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-mono font-extrabold bg-indigo-100 text-indigo-800">
                                                AKSES
                                            </span>
                                        </template>
                                        <span class="text-[10px] font-mono font-bold text-slate-400 truncate" x-text="p.name"></span>
                                    </div>
                                    <span class="text-xs font-bold block truncate" 
                                          :class="hasPerm(p.name) ? 'text-slate-900' : 'text-slate-600'"
                                          x-text="getPermLabel(p.name)"></span>
                                </div>

                                <!-- Switch Toggle Visual -->
                                <div class="w-10 h-6 rounded-full transition-colors flex items-center p-1 shrink-0"
                                     :class="hasPerm(p.name) 
                                        ? (p.name.includes('void') ? 'bg-rose-600 justify-end' : 'bg-emerald-600 justify-end') 
                                        : 'bg-slate-300 justify-start'">
                                    <div class="w-4 h-4 rounded-full bg-white shadow-md transform transition-transform"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        <!-- ============================================== -->
        <!-- MODAL TAMBAH ROLE BARU                         -->
        <!-- ============================================== -->
        <div x-show="addRoleModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="addRoleModalOpen = false" 
                 class="bg-white rounded-2xl max-w-sm w-full p-5 shadow-2xl border border-slate-100">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                        <i data-lucide="plus-circle" class="w-4 h-4 text-emerald-600"></i>
                        <span>Tambah Role Baru</span>
                    </h3>
                    <button type="button" @click="addRoleModalOpen = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('master.roles.store') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Role <span class="text-rose-500">*</span></label>
                        <input type="text" 
                               name="name" 
                               x-ref="newRoleInput"
                               required 
                               placeholder="Contoh: Audit Internal, Kasir Lapangan" 
                               class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="addRoleModalOpen = false" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs shadow-xs cursor-pointer">
                            Simpan Role
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- MODAL GANTI NAMA / EDIT ROLE                   -->
        <!-- ============================================== -->
        <div x-show="editRoleModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="editRoleModalOpen = false" 
                 class="bg-white rounded-2xl max-w-sm w-full p-5 shadow-2xl border border-slate-100">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                        <i data-lucide="edit-3" class="w-4 h-4 text-indigo-600"></i>
                        <span>Ganti Nama Role</span>
                    </h3>
                    <button type="button" @click="editRoleModalOpen = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form method="POST" :action="`{{ url('master/roles/update') }}/${selectedRoleId}`">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Role Baru <span class="text-rose-500">*</span></label>
                        <input type="text" 
                               name="name" 
                               x-model="editRoleName"
                               required 
                               class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="editRoleModalOpen = false" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs shadow-xs cursor-pointer">
                            Perbarui Nama
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function roleMatrixApp() {
            return {
                roles: @json($roles),
                matrix: @json($matrix),
                rolePermissions: @json($rolePermissions),
                selectedRoleId: {{ request('role_id') ?? ($roles->first()->id ?? 1) }},
                addRoleModalOpen: false,
                editRoleModalOpen: false,
                editRoleName: '',

                permLabels: {
                    'po.view': 'Melihat Daftar & Detail PO',
                    'po.create': 'Membuat & Menerbitkan PO',
                    'po.void': 'Melakukan VOID / Pembatalan PO',
                    'gr.view': 'Melihat Penerimaan Barang (GR)',
                    'gr.create': 'Menerima Fisik Barang (GR)',
                    'gr.void': 'Melakukan VOID Dokumen GR',
                    'inventory.view': 'Melihat Posisi Stok & Kartu Stok',
                    'stock_movement.view': 'Melihat Riwayat Mutasi Stok (SM)',
                    'stock_movement.create': 'Membuat Mutasi Antar Gudang (SM)',
                    'stock_movement.multi': 'Upload SM Multi (Massal via Excel)',
                    'stock_movement.void': 'Melakukan VOID / Pembatalan SM',
                    'stock_opname.view': 'Melihat Data Stock Opname',
                    'stock_opname.create': 'Melakukan Stock Opname & Penyesuaian',
                    'so.view': 'Melihat Daftar & Detail Sales Order',
                    'so.create': 'Membuat Sales Order (SO)',
                    'so.void': 'Melakukan VOID / Pembatalan SO',
                    'delivery.view': 'Melihat Pengiriman Barang (GD)',
                    'delivery.create': 'Memproses Delivery & Cetak Surat Jalan',
                    'delivery.void': 'Melakukan VOID Dokumen Pengiriman',
                    'finance.view': 'Melihat Kartu Piutang & Buku Kas/Bank',
                    'finance.create_bbm': 'Membuat Bukti Bank Masuk (BBM)',
                    'finance.create_ttk': 'Penerimaan Pembayaran Kasir (TTK)',
                    'finance.void_payment': 'Melakukan VOID Pembayaran / BBM / TTK',
                    'reports.view': 'Melihat & Unduh Laporan (Excel/PDF)',
                    'audit.view': 'Melihat Audit Trail & Log Aktivitas',
                    'approvals.manage': 'Melakukan Persetujuan (Approval Center)',
                    'master.view': 'Melihat Master Data (Produk, Customer, dll)',
                    'master.create': 'Menambah Master Data',
                    'master.edit': 'Mengubah / Nonaktifkan Master Data',
                    'roles.manage': 'Mengatur Role & Hak Akses Pengguna',
                },

                get selectedRole() {
                    return this.roles.find(r => r.id == this.selectedRoleId);
                },

                initData() {
                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                },

                selectRole(id) {
                    this.selectedRoleId = id;
                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                },

                openAddRoleModal() {
                    this.addRoleModalOpen = true;
                    this.$nextTick(() => {
                        lucide.createIcons();
                        if (this.$refs.newRoleInput) this.$refs.newRoleInput.focus();
                    });
                },

                openEditRoleModal() {
                    if (!this.selectedRole) return;
                    this.editRoleName = this.selectedRole.name;
                    this.editRoleModalOpen = true;
                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                },

                hasPerm(name) {
                    const active = this.rolePermissions[this.selectedRoleId] || [];
                    return active.includes(name);
                },

                getRolePermCount(roleId) {
                    const active = this.rolePermissions[roleId] || [];
                    return `${active.length} izin`;
                },

                getPermLabel(name) {
                    return this.permLabels[name] || name;
                },

                async togglePermission(name) {
                    try {
                        const response = await fetch('{{ route('master.roles.matrix.toggle') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                role_id: this.selectedRoleId,
                                permission_name: name
                            })
                        });

                        const res = await response.json();
                        if (res.success) {
                            let perms = this.rolePermissions[this.selectedRoleId] || [];
                            if (res.has_access) {
                                if (!perms.includes(name)) perms.push(name);
                            } else {
                                perms = perms.filter(p => p !== name);
                            }
                            this.rolePermissions[this.selectedRoleId] = perms;

                            if (typeof window.showToast === 'function') {
                                window.showToast(res.message, res.has_access ? 'success' : 'error', 'Hak Akses Diperbarui');
                            }
                        } else {
                            alert(res.message || 'Gagal mengubah hak akses.');
                        }
                    } catch (err) {
                        alert('Terjadi kesalahan jaringan atau server: ' + err.message);
                    }
                },

                async toggleAllInModule(modulePermissions, action) {
                    const names = modulePermissions.map(p => p.name);
                    try {
                        const response = await fetch('{{ route('master.roles.matrix.toggle-module') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                role_id: this.selectedRoleId,
                                permission_names: names,
                                action: action
                            })
                        });

                        const res = await response.json();
                        if (res.success) {
                            this.rolePermissions[this.selectedRoleId] = res.active_permissions;
                            if (typeof window.showToast === 'function') {
                                window.showToast(res.message, 'success', 'Hak Akses Modul');
                            }
                        } else {
                            alert(res.message || 'Gagal mengubah hak akses modul.');
                        }
                    } catch (err) {
                        alert('Terjadi kesalahan jaringan atau server: ' + err.message);
                    }
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
