<x-app-layout>
    <x-slot name="title">Master Data Tim Sales Kanvaser</x-slot>
    <x-slot name="headerTitle">Tim Sales & Kanvaser Lapangan</x-slot>
    <x-slot name="badge">Master Sales Force</x-slot>

    <div x-data="salesMasterApp()" class="pb-12">
        
        <!-- Top Action & Navigation Banner -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Master Tim Sales & Kanvaser Motor</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-purple-100 text-purple-800 border border-purple-200">DISTRIBUTION ERP</span>
                </h2>
                <!-- <p class="text-xs text-slate-500">Daftar tenaga penjual lapangan resmi distributor Telkomsel, alokasi gudang kanvas motor, limit plafon stok, dan akun Digipos</p> -->
            </div>

            <button type="button" 
                    @click="createOpen = !createOpen" 
                    class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-sm transition-all active:scale-95 shrink-0 cursor-pointer">
                <i data-lucide="user-plus" class="w-4 h-4 text-emerald-400"></i>
                <span x-text="createOpen ? 'Tutup Formulir' : '+ Tambah Sales Baru'"></span>
            </button>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between shadow-xs">
                <span class="flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                    {{ session('success') }}
                </span>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 cursor-pointer">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center justify-between shadow-xs">
                <span class="flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                    {{ session('error') }}
                </span>
                <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800 cursor-pointer">&times;</button>
            </div>
        @endif

        <!-- ============================================== -->
        <!-- FORM TAMBAH SALES BARU (INLINE CARD)           -->
        <!-- ============================================== -->
        <div x-show="createOpen" x-cloak x-transition class="mb-6 bg-white rounded-3xl border-2 border-emerald-500/40 p-6 shadow-xl">
            <div class="flex items-center justify-between pb-3 mb-5 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <span class="p-2 rounded-xl bg-emerald-100 text-emerald-700">
                        <i data-lucide="user-plus" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-sm">Pendaftaran Sales Force Baru & Pembuatan Gudang Kanvas</h3>
                        <p class="text-[11px] text-slate-500">Lengkapi data identitas, akun Digipos, plafon limit, dan wilayah penugasan sales.</p>
                    </div>
                </div>
                <button @click="createOpen = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('master.sales-team.store') }}">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    
                    <!-- 1. Nama Lengkap -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap Sales <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required placeholder="Contoh: Andi Pratama" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    </div>

                    <!-- 2. No WhatsApp / HP -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WhatsApp / Telepon <span class="text-rose-500">*</span></label>
                        <input type="text" name="phone" required placeholder="0812xxxxxxxx" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    </div>

                    <!-- 3. Email Sales -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email Sales</label>
                        <input type="email" name="email" placeholder="andi@distripos.com" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    </div>

                    <!-- 4. Penugasan TAP -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kantor TAP Induk <span class="text-rose-500">*</span></label>
                        <select name="tap_id" required class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                            @foreach($taps as $tap)
                                <option value="{{ $tap->id }}">{{ $tap->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 5. Tipe Sales -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Saluran Sales</label>
                        <select name="sales_type" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none">
                            <option value="CANVASSER">CANVASSER (Kanvaser Motoris Outlet)</option>
                            <option value="DIRECT_SALES">DIRECT SALES (Penjualan Langsung / Event)</option>
                            <option value="MODERN_OUTLET">MODERN OUTLET (Minimarket / Retail Modern)</option>
                            <option value="SPV">SPV (Supervisor / Team Leader)</option>
                        </select>
                    </div>

                    <!-- 6. Level Sales -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Level Jenjang Jabatan</label>
                        <select name="sales_level" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none">
                            <option value="SF">SF (Sales Force Lapangan)</option>
                            <option value="TL">TL (Team Leader)</option>
                            <option value="SPV">SPV (Supervisor Lapangan)</option>
                            <option value="COORD">COORD (Koordinator Area)</option>
                        </select>
                    </div>

                    <!-- 7. Plafon Limit Stok Kanvas (Rp) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Plafon Limit Stok Kanvas (Rp)</label>
                        <input type="number" name="sales_limit" value="20000000" min="0" step="500000" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none font-mono">
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Batas maksimal nilai stok yang boleh dibawa saat kanvas.</span>
                    </div>

                    <!-- 8. No HP Seller Bakulku / Digipos -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">No HP Digipos / Bakulku</label>
                        <input type="text" name="bakulku_phone" placeholder="0813xxxxxxxx" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none font-mono">
                    </div>

                    <!-- 9. Tipe NGRS -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tipe NGRS Telkomsel</label>
                        <select name="ngrs_type" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none">
                            <option value="Digipos">Digipos</option>
                            <option value="MKIOS">MKIOS</option>
                            <option value="Bulk">Bulk Transaksi</option>
                        </select>
                    </div>

                    <!-- 10. Alamat Domisili -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Domisili Sales</label>
                        <input type="text" name="address" placeholder="Contoh: Jl. Raya Tajur No. 25, Bogor Selatan" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none">
                    </div>

                    <!-- 11. Status Kehadiran Hari Ini -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Kehadiran Hari Ini</label>
                        <select name="hadir" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none">
                            <option value="1">HADIR (Bertugas di Lapangan)</option>
                            <option value="0">LIBUR / CUTI / IZIN</option>
                        </select>
                    </div>
                </div>

                <div class="p-3 bg-emerald-50/70 border border-emerald-200 rounded-xl text-xs text-emerald-800 mb-4 flex items-center gap-2">
                    <i data-lucide="info" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                    <span>Sistem akan secara otomatis membentuk <strong>Gudang Kanvas Mobile (SALES)</strong> baru untuk menampung stok fisik harian sales ini.</span>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="createOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-sm transition-all cursor-pointer">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan & Bentuk Gudang Kanvas</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- ============================================== -->
        <!-- MODAL EDIT SALES                               -->
        <!-- ============================================== -->
        <template x-if="editModalOpen && editData">
            <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                <div class="bg-white rounded-3xl border border-slate-200 max-w-2xl w-full p-6 shadow-2xl max-h-[90vh] flex flex-col" @click.outside="editModalOpen = false">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 shrink-0">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center font-bold">
                                <i data-lucide="user-cog" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-sm">Edit Profil Sales [<span x-text="editData.code"></span>]</h3>
                                <p class="text-[11px] text-slate-400 font-mono" x-text="editData.name"></p>
                            </div>
                        </div>
                        <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">&times;</button>
                    </div>

                    <form :action="'/master/sales-team/update/' + editData.id" method="POST" class="overflow-y-auto pr-1 flex-1 space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Sales</label>
                                <input type="text" name="name" x-model="editData.name" required class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Telepon / WA</label>
                                <input type="text" name="phone" x-model="editData.phone" required class="w-full text-xs font-medium bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Email Sales</label>
                                <input type="email" name="email" x-model="editData.email" class="w-full text-xs font-medium bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Penugasan TAP</label>
                                <select name="tap_id" x-model="editData.tap_id" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                    @foreach($taps as $tap)
                                        <option value="{{ $tap->id }}">{{ $tap->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Saluran Sales</label>
                                <select name="sales_type" x-model="editData.sales_type" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                    <option value="CANVASSER">CANVASSER (Kanvaser Motoris)</option>
                                    <option value="DIRECT_SALES">DIRECT SALES (Penjualan Langsung)</option>
                                    <option value="MODERN_OUTLET">MODERN OUTLET (Modern Channel)</option>
                                    <option value="SPV">SPV (Supervisor)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Level Jenjang Jabatan</label>
                                <select name="sales_level" x-model="editData.sales_level" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                    <option value="SF">SF (Sales Force Lapangan)</option>
                                    <option value="TL">TL (Team Leader)</option>
                                    <option value="SPV">SPV (Supervisor)</option>
                                    <option value="COORD">COORD (Koordinator Area)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Plafon Limit Stok (Rp)</label>
                                <input type="number" name="sales_limit" x-model="editData.sales_limit" step="500000" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 font-mono">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">No HP Seller Digipos / Bakulku</label>
                                <input type="text" name="bakulku_phone" x-model="editData.bakulku_phone" class="w-full text-xs font-medium bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 font-mono">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Tipe NGRS Telkomsel</label>
                                <select name="ngrs_type" x-model="editData.ngrs_type" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                    <option value="Digipos">Digipos</option>
                                    <option value="MKIOS">MKIOS</option>
                                    <option value="Bulk">Bulk Transaksi</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Status Kehadiran Hari Ini</label>
                                <select name="hadir" x-model="editData.hadir" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                    <option value="1">HADIR (Aktif Bertugas)</option>
                                    <option value="0">LIBUR / CUTI / IZIN</option>
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Domisili</label>
                                <input type="text" name="address" x-model="editData.address" class="w-full text-xs font-medium bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Status Keaktifan Akun</label>
                                <select name="status" x-model="editData.status" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                    <option value="ACTIVE">ACTIVE (Aktif Bertugas)</option>
                                    <option value="INACTIVE">INACTIVE (Nonaktif / Resign)</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100 shrink-0">
                            <button type="button" @click="editModalOpen = false" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl text-xs cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs cursor-pointer transition-all">
                                Perbarui Data Sales
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- ============================================== -->
        <!-- KPI METRICS SUMMARY                            -->
        <!-- ============================================== -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
            <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 block uppercase">Total Tenaga Sales:</span>
                    <span class="text-2xl font-extrabold font-mono text-slate-900">{{ count($salesList) }} Orang</span>
                </div>
                <div class="p-3 bg-purple-50 text-purple-600 rounded-xl">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 block uppercase">Presensi Hari Ini:</span>
                    <span class="text-2xl font-extrabold font-mono text-emerald-600">{{ $salesList->where('hadir', true)->count() }} Hadir</span>
                </div>
                <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                    <i data-lucide="user-check" class="w-6 h-6"></i>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 block uppercase">Gudang Kanvas:</span>
                    <span class="text-2xl font-extrabold font-mono text-cyan-700">{{ $salesList->whereNotNull('warehouse_id')->count() }} Unit</span>
                </div>
                <div class="p-3 bg-cyan-50 text-cyan-600 rounded-xl">
                    <i data-lucide="truck" class="w-6 h-6"></i>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 block uppercase">Total Plafon Stok:</span>
                    <span class="text-lg font-extrabold font-mono text-slate-900 block truncate">
                        Rp {{ number_format($salesList->sum('sales_limit'), 0, ',', '.') }}
                    </span>
                </div>
                <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
                    <i data-lucide="shield-alert" class="w-6 h-6"></i>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- FILTER WILAYAH CLIENT-SIDE                     -->
        <!-- ============================================== -->
        <div class="bg-slate-50/80 border border-slate-200/90 rounded-2xl p-3.5 mb-5 shadow-2xs transition-all">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2.5 flex-1">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 flex items-center gap-1.5 mr-1">
                        <i data-lucide="filter" class="w-3.5 h-3.5 text-purple-600"></i>
                        <span>Filter Sales:</span>
                    </span>

                    <!-- Dropdown Area -->
                    <div class="relative inline-flex items-center">
                        <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-cyan-600">
                            <i data-lucide="map" class="w-3.5 h-3.5"></i>
                        </div>
                        <select x-model="filterArea" 
                                @change="onAreaChange()"
                                :class="filterArea ? 'border-purple-500 ring-2 ring-purple-500/10 text-purple-900 bg-purple-50/20' : 'border-slate-200 bg-white text-slate-800'"
                                class="text-xs font-bold border rounded-xl pl-8 pr-7 py-2 focus:outline-none cursor-pointer shadow-2xs hover:border-slate-300 transition-all appearance-none">
                            <option value="">-- Semua Area --</option>
                            @foreach($areas as $ar)
                                <option value="{{ $ar->id }}">{{ $ar->name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="chevron-down" class="w-3 h-3"></i>
                        </div>
                    </div>

                    <!-- Dropdown Cluster -->
                    <div class="relative inline-flex items-center">
                        <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-amber-600">
                            <i data-lucide="grid" class="w-3.5 h-3.5"></i>
                        </div>
                        <select x-model="filterCluster" 
                                @change="onClusterChange()"
                                :class="filterCluster ? 'border-purple-500 ring-2 ring-purple-500/10 text-purple-900 bg-purple-50/20' : 'border-slate-200 bg-white text-slate-800'"
                                class="text-xs font-bold border rounded-xl pl-8 pr-7 py-2 focus:outline-none cursor-pointer shadow-2xs hover:border-slate-300 transition-all appearance-none">
                            <option value="">-- Semua Cluster --</option>
                            <template x-for="cls in clusterOptions" :key="cls.id">
                                <option :value="cls.id" x-text="cls.name"></option>
                            </template>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="chevron-down" class="w-3 h-3"></i>
                        </div>
                    </div>

                    <!-- Dropdown TAP -->
                    <div class="relative inline-flex items-center">
                        <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-rose-600">
                            <i data-lucide="building-2" class="w-3.5 h-3.5"></i>
                        </div>
                        <select x-model="filterTap" 
                                :class="filterTap ? 'border-purple-500 ring-2 ring-purple-500/10 text-purple-900 bg-purple-50/20' : 'border-slate-200 bg-white text-slate-800'"
                                class="text-xs font-bold border rounded-xl pl-8 pr-7 py-2 focus:outline-none cursor-pointer shadow-2xs hover:border-slate-300 transition-all appearance-none">
                            <option value="">-- Semua TAP --</option>
                            <template x-for="tp in tapOptions" :key="tp.id">
                                <option :value="tp.id" x-text="tp.name"></option>
                            </template>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="chevron-down" class="w-3 h-3"></i>
                        </div>
                    </div>

                    <!-- Filter Peran Sales -->
                    <div class="relative inline-flex items-center">
                        <select x-model="filterType" 
                                :class="filterType !== 'ALL' ? 'border-purple-500 ring-2 ring-purple-500/10 text-purple-900 bg-purple-50/20' : 'border-slate-200 bg-white text-slate-800'"
                                class="text-xs font-bold border rounded-xl px-3 py-2 focus:outline-none cursor-pointer shadow-2xs hover:border-slate-300 transition-all appearance-none">
                            <option value="ALL">-- Semua Peran --</option>
                            <option value="CANVASSER">Canvasser Motoris</option>
                            <option value="DIRECT_SALES">Direct Sales</option>
                            <option value="MODERN_OUTLET">Modern Outlet</option>
                            <option value="SPV">Supervisor</option>
                        </select>
                    </div>

                    <!-- Tombol Reset -->
                    <button type="button" 
                            x-show="hasFilter()" 
                            x-cloak
                            @click="resetFilter()" 
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-200 hover:bg-rose-100 hover:text-rose-700 text-slate-600 text-xs font-bold transition-all shadow-2xs cursor-pointer">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        <span>Reset</span>
                    </button>
                </div>

                <!-- Real-time Count Badge -->
                <div class="flex items-center gap-2 text-xs font-medium text-slate-500 shrink-0">
                    <span>Menampilkan:</span>
                    <span class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 font-bold text-slate-800 shadow-2xs">
                        <strong class="text-purple-600" x-text="filteredCount"></strong>
                        <span class="text-slate-400 font-normal"> / {{ count($salesList) }} Personel</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- TABEL MASTER SALES                             -->
        <!-- ============================================== -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto" x-show="filteredCount > 0">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Sales Force</th>
                            <th class="py-3 px-4">Peran & Level</th>
                            <th class="py-3 px-4">Wilayah TAP & Gudang</th>
                            <th class="py-3 px-4 text-right">Plafon Limit Stok</th>
                            <th class="py-3 px-4">Akun Digipos / Bakulku</th>
                            <th class="py-3 px-4 text-center">Presensi</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($salesList as $s)
                            <tr x-show="isSalesVisible({{ json_encode($s) }})" 
                                x-transition
                                class="hover:bg-slate-50/80 transition-colors">
                                
                                <!-- 1. Sales Person Name & Code -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-purple-700 to-indigo-800 text-white flex items-center justify-center font-bold text-sm shadow-xs shrink-0">
                                            {{ strtoupper(substr($s->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <span class="font-extrabold text-slate-900 block text-xs">{{ $s->name }}</span>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="px-1.5 py-0.2 rounded font-mono font-bold text-[10px] bg-purple-50 text-purple-700 border border-purple-200">
                                                    {{ $s->code }}
                                                </span>
                                                <span class="text-[11px] text-slate-500 font-mono">{{ $s->phone }}</span>
                                            </div>
                                            <span class="text-[10px] text-slate-400 block truncate max-w-[200px]">{{ $s->address ?? '-' }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- 2. Peran & Level -->
                                <td class="py-3.5 px-4">
                                    <div class="space-y-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold 
                                            {{ $s->sales_type === 'CANVASSER' ? 'bg-blue-50 text-blue-700 border border-blue-200' : ($s->sales_type === 'SPV' ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-amber-50 text-amber-700 border border-amber-200') }}">
                                            {{ $s->sales_type }}
                                        </span>
                                        <span class="inline-block px-1.5 py-0.2 rounded text-[10px] font-mono font-bold bg-slate-100 text-slate-700 border border-slate-200 ml-1">
                                            Level: {{ $s->sales_level }}
                                        </span>
                                    </div>
                                </td>

                                <!-- 3. Wilayah TAP & Gudang Kanvas -->
                                <td class="py-3.5 px-4">
                                    <div>
                                        <span class="font-bold text-slate-800 block">{{ $s->tap_name }}</span>
                                        <span class="text-[10px] text-slate-400 font-medium">{{ $s->cluster_name }} &bull; {{ $s->area_name }}</span>
                                        <div class="mt-1">
                                            @if($s->warehouse_name)
                                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-rose-50 text-rose-700 border border-rose-200 inline-flex items-center gap-1">
                                                    <i data-lucide="truck" class="w-3 h-3"></i>
                                                    <span class="truncate max-w-[140px]">{{ $s->warehouse_name }}</span>
                                                </span>
                                            @else
                                                <span class="text-slate-400 italic text-[10px]">Tanpa Gudang</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- 4. Plafon Limit Stok & Sisa Limit -->
                                <td class="py-3.5 px-4 text-right">
                                    <div class="space-y-0.5">
                                        <span class="text-xs font-bold text-slate-900 block font-mono">
                                            <x-money :value="$s->sales_limit" />
                                        </span>
                                        <span class="text-[10px] text-emerald-700 font-semibold block font-mono">
                                            Sisa: <x-money :value="$s->sisa_limit" />
                                        </span>
                                    </div>
                                </td>

                                <!-- 5. Akun Digipos & Bakulku -->
                                <td class="py-3.5 px-4">
                                    <div class="space-y-0.5">
                                        <span class="font-mono text-xs text-slate-800 block font-semibold">
                                            {{ $s->bakulku_phone ?? '-' }}
                                        </span>
                                        <span class="text-[10px] px-1.5 py-0.2 rounded font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                            {{ $s->ngrs_type ?? 'Digipos' }}
                                        </span>
                                    </div>
                                </td>

                                <!-- 6. Presensi Kehadiran Hari Ini -->
                                <td class="py-3.5 px-4 text-center">
                                    @if($s->hadir)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 shadow-2xs">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            <span>Hadir</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            <span>Absen / Cuti</span>
                                        </span>
                                    @endif
                                </td>

                                <!-- 7. Status Keaktifan Toggle -->
                                <td class="py-3.5 px-4 text-center">
                                    <x-master-status-toggle entity="sales" :id="$s->id" :name="$s->name" :active="$s->status === 'ACTIVE'" />
                                </td>

                                <!-- 8. Tombol Edit -->
                                <td class="py-3.5 px-4 text-center">
                                    <button type="button" 
                                            @click="openEditModal({{ json_encode($s) }})" 
                                            class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold transition-all cursor-pointer active:scale-95">
                                        Edit
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Empty State jika hasil filter sales kosong -->
            <div x-show="filteredCount === 0" x-cloak class="p-6">
                <x-empty-state 
                    icon="user-x"
                    title="Tenaga Sales Tidak Ditemukan"
                    subtitle="Tidak ada personel sales force yang sesuai dengan filter wilayah atau peran terpilih."
                    action-click="resetFilter()"
                    action-label="Reset Semua Filter"
                    action-icon="rotate-ccw" />
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function salesMasterApp() {
            return {
                createOpen: false,
                editModalOpen: false,
                editData: null,

                // Filter State
                filterArea: '',
                filterCluster: '',
                filterTap: '',
                filterType: 'ALL',

                allSales: @json($salesList),
                allClusters: @json($clusters),
                allTaps: @json($taps),

                // Dropdown cluster reaktif sesuai area
                get clusterOptions() {
                    if (!this.filterArea) return this.allClusters;
                    return this.allClusters.filter(c => String(c.area_id) === String(this.filterArea));
                },

                // Dropdown tap reaktif sesuai cluster / area
                get tapOptions() {
                    if (this.filterCluster) {
                        return this.allTaps.filter(t => String(t.cluster_id) === String(this.filterCluster));
                    }
                    if (this.filterArea) {
                        return this.allTaps.filter(t => {
                            const cls = this.allClusters.find(c => c.id === t.cluster_id);
                            return cls && String(cls.area_id) === String(this.filterArea);
                        });
                    }
                    return this.allTaps;
                },

                onAreaChange() {
                    this.filterCluster = '';
                    this.filterTap = '';
                    this.refreshIcons();
                },

                onClusterChange() {
                    this.filterTap = '';
                    this.refreshIcons();
                },

                resetFilter() {
                    this.filterArea = '';
                    this.filterCluster = '';
                    this.filterTap = '';
                    this.filterType = 'ALL';
                    this.refreshIcons();
                },

                hasFilter() {
                    return Boolean(this.filterArea || this.filterCluster || this.filterTap || this.filterType !== 'ALL');
                },

                isSalesVisible(sales) {
                    if (this.filterArea && String(sales.area_id) !== String(this.filterArea)) return false;
                    if (this.filterCluster && String(sales.cluster_id) !== String(this.filterCluster)) return false;
                    if (this.filterTap && String(sales.tap_id) !== String(this.filterTap)) return false;
                    if (this.filterType !== 'ALL' && sales.sales_type !== this.filterType) return false;
                    return true;
                },

                get filteredCount() {
                    return this.allSales.filter(s => this.isSalesVisible(s)).length;
                },

                openEditModal(sales) {
                    this.editData = Object.assign({}, sales);
                    this.editModalOpen = true;
                    this.refreshIcons();
                },

                refreshIcons() {
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
