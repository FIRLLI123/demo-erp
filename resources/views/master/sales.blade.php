<x-app-layout>
    <x-slot name="title">Master Data Tim Sales</x-slot>
    <x-slot name="headerTitle">Master Data Tim Sales</x-slot>
    <x-slot name="badge">Sales Force • Types • Levels</x-slot>

    <div x-data="salesMasterApp()" class="pb-12">
        
        <!-- Tab Navigation Buttons -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs mb-6 overflow-hidden">
            <div class="border-b border-slate-200/80 px-6 pt-4 flex items-center justify-between gap-4 flex-wrap">
                <div class="flex gap-6 text-xs font-bold overflow-x-auto">
                    <button @click="activeTab = 'sales'" 
                            :class="activeTab === 'sales' ? 'text-purple-600 border-b-2 border-purple-600 pb-3 font-extrabold' : 'text-slate-500 hover:text-slate-800 pb-3 cursor-pointer'">
                        1. Data Tim Sales ({{ count($salesList) }})
                    </button>
                    <button @click="activeTab = 'types'" 
                            :class="activeTab === 'types' ? 'text-purple-600 border-b-2 border-purple-600 pb-3 font-extrabold' : 'text-slate-500 hover:text-slate-800 pb-3 cursor-pointer'">
                        2. Master Tipe Sales ({{ count($salesTypes) }})
                    </button>
                    <button @click="activeTab = 'levels'" 
                            :class="activeTab === 'levels' ? 'text-purple-600 border-b-2 border-purple-600 pb-3 font-extrabold' : 'text-slate-500 hover:text-slate-800 pb-3 cursor-pointer'">
                        3. Master Level Sales ({{ count($salesLevels) }})
                    </button>
                </div>

                <!-- Action Button Contextual to Tab -->
                <div class="pb-3">
                    <template x-if="activeTab === 'sales'">
                        <button type="button" 
                                @click="createOpen = !createOpen" 
                                class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-xs transition-all active:scale-95 cursor-pointer">
                            <i data-lucide="user-plus" class="w-4 h-4 text-emerald-400"></i>
                            <span x-text="createOpen ? 'Tutup Form' : '+ Tambah Sales Baru'"></span>
                        </button>
                    </template>
                    <template x-if="activeTab === 'types'">
                        <button type="button" 
                                @click="openTypeModal()" 
                                class="px-3.5 py-2 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-xs transition-all active:scale-95 cursor-pointer">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>+ Tambah Tipe Sales</span>
                        </button>
                    </template>
                    <template x-if="activeTab === 'levels'">
                        <button type="button" 
                                @click="openLevelModal()" 
                                class="px-3.5 py-2 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-xs transition-all active:scale-95 cursor-pointer">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>+ Tambah Level Sales</span>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between shadow-2xs">
                <span class="flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                    {{ session('success') }}
                </span>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 cursor-pointer">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center justify-between shadow-2xs">
                <span class="flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                    {{ session('error') }}
                </span>
                <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800 cursor-pointer">&times;</button>
            </div>
        @endif

        <!-- ============================================================= -->
        <!-- TAB 1: DATA TIM SALES                                         -->
        <!-- ============================================================= -->
        <div x-show="activeTab === 'sales'">
            
            <!-- FORM TAMBAH SALES BARU (COLLAPSIBLE) -->
            <div x-show="createOpen" x-cloak x-transition class="mb-6 bg-white rounded-2xl border-2 border-emerald-500/40 p-6 shadow-lg">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="p-1.5 rounded-lg bg-emerald-100 text-emerald-700">
                            <i data-lucide="user-plus" class="w-4 h-4"></i>
                        </span>
                        <h3 class="font-extrabold text-slate-900 text-sm">Pendaftaran Sales Baru</h3>
                    </div>
                    <button @click="createOpen = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('master.sales-team.store') }}">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 mb-4">
                        
                        <!-- 1. Nama Lengkap -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Sales <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" required placeholder="Contoh: Andi Pratama" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        </div>

                        <!-- 2. No Telepon / WA -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WhatsApp / HP <span class="text-rose-500">*</span></label>
                            <input type="text" name="phone" required placeholder="0812xxxxxxxx" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        </div>

                        <!-- 3. Email Sales -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Email</label>
                            <input type="email" name="email" placeholder="sales@distripos.com" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        </div>

                        <!-- 4. Penugasan TAP -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kantor TAP Induk <span class="text-rose-500">*</span></label>
                            <select name="tap_id" required class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:bg-white focus:outline-none">
                                @foreach($taps as $tap)
                                    <option value="{{ $tap->id }}">{{ $tap->name }} ({{ $tap->cluster_name ?? '' }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 5. Tipe Sales (Dinamis dari Master Tipe) -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs font-bold text-slate-700">Tipe Sales</label>
                                <button type="button" @click="activeTab = 'types'; openTypeModal()" class="text-[10px] font-bold text-purple-600 hover:underline cursor-pointer">
                                    + Kelola Tipe
                                </button>
                            </div>
                            <select name="sales_type" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:bg-white focus:outline-none">
                                @foreach($salesTypes as $st)
                                    <option value="{{ $st->type_name }}">{{ $st->type_name }} {{ $st->type_desc ? '('.$st->type_desc.')' : '' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 6. Level Sales (Dinamis dari Master Level) -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs font-bold text-slate-700">Level Jabatan</label>
                                <button type="button" @click="activeTab = 'levels'; openLevelModal()" class="text-[10px] font-bold text-purple-600 hover:underline cursor-pointer">
                                    + Kelola Level
                                </button>
                            </div>
                            <select name="sales_level" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:bg-white focus:outline-none">
                                @foreach($salesLevels as $sl)
                                    <option value="{{ $sl->level_name }}">{{ $sl->level_name }} {{ $sl->level_desc ? '('.$sl->level_desc.')' : '' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 7. Plafon Limit Stok Kanvas (Rp) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Plafon Limit Stok (Rp)</label>
                            <input type="number" name="sales_limit" value="20000000" min="0" step="500000" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:bg-white focus:outline-none font-mono">
                        </div>

                        <!-- 8. No HP Seller Bakulku / Digipos -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">No HP Digipos / Bakulku</label>
                            <input type="text" name="bakulku_phone" placeholder="0813xxxxxxxx" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:bg-white focus:outline-none font-mono">
                        </div>

                        <!-- 9. Tipe NGRS -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tipe NGRS</label>
                            <select name="ngrs_type" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:bg-white focus:outline-none">
                                <option value="Digipos">Digipos</option>
                                <option value="MKIOS">MKIOS</option>
                                <option value="Bulk">Bulk Transaksi</option>
                            </select>
                        </div>

                        <!-- 10. Alamat Domisili -->
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Domisili</label>
                            <input type="text" name="address" placeholder="Alamat rumah / domisili sales" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:bg-white focus:outline-none">
                        </div>

                        <!-- 11. Status Kehadiran -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kehadiran Hari Ini</label>
                            <select name="hadir" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:bg-white focus:outline-none">
                                <option value="1">HADIR (Aktif Bertugas)</option>
                                <option value="0">LIBUR / CUTI / IZIN</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="createOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-xs transition-all cursor-pointer">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Simpan Sales</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- FILTER WILAYAH & TIPE SALES CLIENT-SIDE (REUSABLE UI) -->
            <x-filter-territory
                tab="sales"
                :show-area="true"
                :show-cluster="true"
                :show-tap="true"
                :show-type="true"
                type-mode="sales"
                :sales-types="$salesTypes"
                :show-search="true"
                model-search="searchSales"
                search-placeholder="Cari nama, kode SLS, no HP, TAP..."
                :areas="$areas"
                :clusters="$clusters"
                :taps="$taps" />

            <!-- TABEL MASTER SALES -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="overflow-x-auto" x-show="filteredCount > 0">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">Sales Force</th>
                                <th class="py-3 px-4">Tipe & Level</th>
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
                                <tr x-show="paginatedSalesIds.includes({{ $s->id }})" 
                                    x-transition
                                    class="hover:bg-slate-50/80 transition-colors">
                                    
                                    <!-- 1. Sales Person Name & Code (BERSIH DARI INFORMASI ALAMAT DUMMY) -->
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-purple-700 to-indigo-800 text-white flex items-center justify-center font-bold text-xs shadow-xs shrink-0">
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
                                            </div>
                                        </div>
                                    </td>

                                    <!-- 2. Tipe & Level Sales -->
                                    <td class="py-3.5 px-4">
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                                {{ $s->sales_type }}
                                            </span>
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                                {{ $s->sales_level }}
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

                                    <!-- 6. Presensi Kehadiran -->
                                    <td class="py-3.5 px-4 text-center">
                                        @if($s->hadir)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 shadow-2xs">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                <span>Hadir</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                <span>Absen</span>
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
                                                class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-purple-50 hover:text-purple-700 hover:border-purple-200 text-slate-700 text-xs font-bold transition-all cursor-pointer">
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Pagination Bar Tim Sales -->
                    <div class="p-4 border-t border-slate-200 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-500">
                        <div class="flex items-center gap-3">
                            <div>
                                Menampilkan <strong class="text-slate-800" x-text="salesPaginationStart"></strong> - <strong class="text-slate-800" x-text="salesPaginationEnd"></strong> dari <strong class="text-slate-800" x-text="filteredCount"></strong> data Sales
                            </div>
                            <span class="text-slate-300">|</span>
                            <label class="flex items-center gap-1.5 text-xs">
                                <span>Baris:</span>
                                <select x-model="salesPerPage" @change="salesPage = 1" class="border border-slate-200 rounded-lg px-2 py-1 bg-white text-xs font-semibold text-slate-700 focus:outline-none focus:ring-1 focus:ring-purple-500">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="ALL">Semua</option>
                                </select>
                            </label>
                        </div>

                        <!-- Page Navigation Buttons -->
                        <div class="flex items-center gap-1" x-show="totalSalesPages > 1">
                            <button type="button" 
                                    @click="salesPage = Math.max(1, salesPage - 1)" 
                                    :disabled="salesPage === 1"
                                    class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed text-xs font-bold transition-all flex items-center gap-1">
                                <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                                <span>Prev</span>
                            </button>

                            <template x-for="(p, idx) in displayedSalesPages" :key="idx">
                                <button type="button"
                                        @click="if (p !== '...') salesPage = p"
                                        :disabled="p === '...'"
                                        :class="salesPage === p ? 'bg-purple-600 text-white font-extrabold border-purple-600 shadow-xs' : (p === '...' ? 'cursor-default border-transparent text-slate-400' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100 font-semibold')"
                                        class="min-w-[30px] h-7 px-1.5 rounded-lg border text-xs flex items-center justify-center transition-all"
                                        x-text="p">
                                </button>
                            </template>

                            <button type="button" 
                                    @click="salesPage = Math.min(totalSalesPages, salesPage + 1)" 
                                    :disabled="salesPage === totalSalesPages"
                                    class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed text-xs font-bold transition-all flex items-center gap-1">
                                <span>Next</span>
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty State jika hasil filter sales nihil -->
                <div x-show="filteredCount === 0" x-cloak class="p-8">
                    <x-empty-state 
                        icon="filter-x"
                        title="Sales Tidak Ditemukan"
                        subtitle="Tidak ada personel sales yang cocok dengan kombinasi filter wilayah dan tipe ini."
                        action-click="resetFilter()"
                        action-label="Reset Semua Filter"
                        action-icon="rotate-ccw" />
                </div>
            </div>

        </div>

        <!-- ============================================================= -->
        <!-- TAB 2: MASTER SALES TYPE                                      -->
        <!-- ============================================================= -->
        <div x-show="activeTab === 'types'" x-cloak>
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-sm">Daftar Tipe Saluran Penjualan (Sales Type)</h3>
                        <p class="text-xs text-slate-500">Tipe saluran kerja sales (misal: SF, DS, Sales Event, Sales NGRS, Sales Indihome).</p>
                    </div>
                    <x-search-input model="searchType" placeholder="Cari tipe sales..." width="w-full sm:w-60" accent="purple" />
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4 w-16">ID</th>
                                <th class="py-3 px-4">Kode Tipe Sales</th>
                                <th class="py-3 px-4">Deskripsi / Keterangan</th>
                                <th class="py-3 px-4 text-center">Jumlah Personel</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-center w-28">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @foreach($salesTypes as $st)
                                @php
                                    $countSales = $salesList->where('sales_type', $st->type_name)->count();
                                @endphp
                                <tr x-show="isTypeVisible({{ json_encode($st) }})"
                                    class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-3.5 px-4 font-mono font-bold text-slate-400">#{{ $st->id }}</td>
                                    <td class="py-3.5 px-4">
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                            {{ $st->type_name }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 font-medium text-slate-700">
                                        {{ $st->type_desc ?? '-' }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-bold font-mono {{ $countSales > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500' }}">
                                            {{ $countSales }} Sales
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <x-master-status-toggle entity="sales_types" :id="$st->id" :name="$st->type_name" :active="$st->status === 'ACTIVE'" />
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <button type="button" 
                                                    @click="openTypeModal({{ json_encode($st) }})" 
                                                    class="p-1.5 rounded-lg border border-slate-200 bg-white hover:bg-purple-50 hover:text-purple-600 text-slate-600 transition-colors cursor-pointer"
                                                    title="Edit Tipe">
                                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                            </button>
                                            <form method="POST" action="{{ route('master.sales-types.delete', $st->id) }}" onsubmit="return confirm('Hapus tipe sales {{ $st->type_name }}?')">
                                                @csrf
                                                <button type="submit" 
                                                        class="p-1.5 rounded-lg border border-slate-200 bg-white hover:bg-rose-50 hover:text-rose-600 text-slate-400 transition-colors cursor-pointer"
                                                        title="Hapus Tipe">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- TAB 3: MASTER SALES LEVEL                                     -->
        <!-- ============================================================= -->
        <div x-show="activeTab === 'levels'" x-cloak>
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-sm">Daftar Jenjang Jabatan Sales (Sales Level)</h3>
                        <p class="text-xs text-slate-500">Struktur level/jabatan tenaga penjualan (misal: SALES, SPV, BM, GM).</p>
                    </div>
                    <x-search-input model="searchLevel" placeholder="Cari level sales..." width="w-full sm:w-60" accent="purple" />
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4 w-16">ID</th>
                                <th class="py-3 px-4">Level Name (Kode)</th>
                                <th class="py-3 px-4">Level Description</th>
                                <th class="py-3 px-4 text-center">Jumlah Personel</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-center w-28">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @foreach($salesLevels as $sl)
                                @php
                                    $countLevel = $salesList->where('sales_level', $sl->level_name)->count();
                                @endphp
                                <tr x-show="isLevelVisible({{ json_encode($sl) }})"
                                    class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-3.5 px-4 font-mono font-bold text-slate-400">#{{ $sl->id }}</td>
                                    <td class="py-3.5 px-4">
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            {{ $sl->level_name }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 font-semibold text-slate-800">
                                        {{ $sl->level_desc ?? '-' }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-bold font-mono {{ $countLevel > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500' }}">
                                            {{ $countLevel }} Sales
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <x-master-status-toggle entity="sales_levels" :id="$sl->id" :name="$sl->level_name" :active="$sl->status === 'ACTIVE'" />
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <button type="button" 
                                                    @click="openLevelModal({{ json_encode($sl) }})" 
                                                    class="p-1.5 rounded-lg border border-slate-200 bg-white hover:bg-purple-50 hover:text-purple-600 text-slate-600 transition-colors cursor-pointer"
                                                    title="Edit Level">
                                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                            </button>
                                            <form method="POST" action="{{ route('master.sales-levels.delete', $sl->id) }}" onsubmit="return confirm('Hapus level sales {{ $sl->level_name }}?')">
                                                @csrf
                                                <button type="submit" 
                                                        class="p-1.5 rounded-lg border border-slate-200 bg-white hover:bg-rose-50 hover:text-rose-600 text-slate-400 transition-colors cursor-pointer"
                                                        title="Hapus Level">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- MODAL EDIT SALES                                              -->
        <!-- ============================================================= -->
        <template x-if="editModalOpen && editData">
            <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                <div class="bg-white rounded-2xl border border-slate-200 max-w-2xl w-full p-6 shadow-2xl max-h-[90vh] flex flex-col" @click.outside="editModalOpen = false">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 shrink-0">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center font-bold">
                                <i data-lucide="user-cog" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-sm">Edit Profil Sales [<span x-text="editData.code"></span>]</h3>
                                <p class="text-[11px] text-slate-400 font-mono" x-text="editData.name"></p>
                            </div>
                        </div>
                        <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">&times;</button>
                    </div>

                    <form :action="'/master/sales-team/update/' + editData.id" method="POST" class="overflow-y-auto pr-1 flex-1 space-y-3.5">
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

                            <!-- Tipe Sales (Dinamis dari Master Tipe) -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Saluran Sales</label>
                                <select name="sales_type" x-model="editData.sales_type" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                    @foreach($salesTypes as $st)
                                        <option value="{{ $st->type_name }}">{{ $st->type_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Level Sales (Dinamis dari Master Level) -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Level Jenjang Jabatan</label>
                                <select name="sales_level" x-model="editData.sales_level" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                    @foreach($salesLevels as $sl)
                                        <option value="{{ $sl->level_name }}">{{ $sl->level_name }} ({{ $sl->level_desc }})</option>
                                    @endforeach
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
                                <label class="block text-xs font-bold text-slate-700 mb-1">Status Kehadiran</label>
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
                                <label class="block text-xs font-bold text-slate-700 mb-1">Status Akun</label>
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

        <!-- ============================================================= -->
        <!-- MODAL FORM: TAMBAH & EDIT SALES TYPE                          -->
        <!-- ============================================================= -->
        <div x-show="showTypeModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl border border-slate-200 max-w-md w-full p-6 shadow-2xl" @click.outside="showTypeModal = false">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <h3 class="font-extrabold text-slate-900 text-sm" x-text="isTypeEdit ? 'Edit Tipe Sales' : 'Tambah Tipe Sales Baru'"></h3>
                    <button @click="showTypeModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">&times;</button>
                </div>

                <form :action="typeFormAction" method="POST" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kode / Nama Tipe Sales <span class="text-rose-500">*</span></label>
                        <input type="text" name="type_name" x-model="typeForm.type_name" required placeholder="Contoh: SF, DS, Sales Event" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:bg-white focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan / Deskripsi</label>
                        <input type="text" name="type_desc" x-model="typeForm.type_desc" placeholder="Contoh: Sales Force Lapangan / Canvasser" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:bg-white focus:outline-none">
                    </div>

                    <div x-show="isTypeEdit">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status</label>
                        <select name="status" x-model="typeForm.status" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:bg-white focus:outline-none">
                            <option value="ACTIVE">ACTIVE (Aktif)</option>
                            <option value="INACTIVE">INACTIVE (Nonaktif)</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="showTypeModal = false" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl text-xs cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl text-xs cursor-pointer transition-all">
                            Simpan Tipe
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- MODAL FORM: TAMBAH & EDIT SALES LEVEL                         -->
        <!-- ============================================================= -->
        <div x-show="showLevelModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl border border-slate-200 max-w-md w-full p-6 shadow-2xl" @click.outside="showLevelModal = false">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <h3 class="font-extrabold text-slate-900 text-sm" x-text="isLevelEdit ? 'Edit Level Sales' : 'Tambah Level Sales Baru'"></h3>
                    <button @click="showLevelModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">&times;</button>
                </div>

                <form :action="levelFormAction" method="POST" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Level Name (Kode Jabatan) <span class="text-rose-500">*</span></label>
                        <input type="text" name="level_name" x-model="levelForm.level_name" required placeholder="Contoh: SALES, SPV, BM, GM" class="w-full text-xs font-bold uppercase bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:bg-white focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Level Description (Nama Lengkap Jabatan)</label>
                        <input type="text" name="level_desc" x-model="levelForm.level_desc" placeholder="Contoh: SUPERVISOR, BRANCH MANAGER" class="w-full text-xs uppercase bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:bg-white focus:outline-none">
                    </div>

                    <div x-show="isLevelEdit">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status</label>
                        <select name="status" x-model="levelForm.status" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:bg-white focus:outline-none">
                            <option value="ACTIVE">ACTIVE (Aktif)</option>
                            <option value="INACTIVE">INACTIVE (Nonaktif)</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="showLevelModal = false" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl text-xs cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl text-xs cursor-pointer transition-all">
                            Simpan Level
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function salesMasterApp() {
            return {
                activeTab: 'sales',
                createOpen: false,
                editModalOpen: false,
                editData: null,

                selectedArea: '',
                selectedCluster: '',
                selectedTap: '',
                selectedType: 'ALL',
                searchSales: '',
                searchType: '',
                searchLevel: '',

                allClusters: @json($clusters),
                allTaps: @json($taps),
                allSales: @json($salesList),

                // Paginasi Tim Sales
                salesPage: 1,
                salesPerPage: 10,

                // Modal State Sales Type
                showTypeModal: false,
                isTypeEdit: false,
                typeFormAction: '',
                typeForm: { id: null, type_name: '', type_desc: '', status: 'ACTIVE' },

                // Modal State Sales Level
                showLevelModal: false,
                isLevelEdit: false,
                levelFormAction: '',
                levelForm: { id: null, level_name: '', level_desc: '', status: 'ACTIVE' },

                init() {
                    const params = new URLSearchParams(window.location.search);
                    if (params.has('tab')) {
                        this.activeTab = params.get('tab');
                    }

                    this.$watch('salesPage', () => this.refreshIcons());
                    this.$watch('salesPerPage', () => { this.salesPage = 1; this.refreshIcons(); });
                    this.$watch('searchSales', () => { this.salesPage = 1; this.refreshIcons(); });
                    this.$watch('activeTab', () => this.refreshIcons());

                    this.refreshIcons();
                },

                // Opsi Cluster Berdasarkan Area
                get clusterOptions() {
                    if (!this.selectedArea) return this.allClusters;
                    return this.allClusters.filter(c => String(c.area_id) === String(this.selectedArea));
                },

                // Opsi TAP Berdasarkan Cluster / Area
                get tapOptions() {
                    if (this.selectedCluster) {
                        return this.allTaps.filter(t => String(t.cluster_id) === String(this.selectedCluster));
                    }
                    if (this.selectedArea) {
                        return this.allTaps.filter(t => String(t.area_id) === String(this.selectedArea));
                    }
                    return this.allTaps;
                },

                onAreaChange() {
                    this.selectedCluster = '';
                    this.selectedTap = '';
                    this.salesPage = 1;
                    this.refreshIcons();
                },

                onClusterChange() {
                    this.selectedTap = '';
                    this.salesPage = 1;
                    this.refreshIcons();
                },

                onTapChange() {
                    this.salesPage = 1;
                    this.refreshIcons();
                },

                onTypeChange() {
                    this.salesPage = 1;
                    this.refreshIcons();
                },

                onSearchChange() {
                    this.salesPage = 1;
                    this.refreshIcons();
                },

                resetFilter() {
                    this.selectedArea = '';
                    this.selectedCluster = '';
                    this.selectedTap = '';
                    this.selectedType = 'ALL';
                    this.searchSales = '';
                    this.salesPage = 1;
                    this.refreshIcons();
                },

                hasActiveFilter() {
                    return Boolean(
                        this.selectedArea || 
                        this.selectedCluster || 
                        this.selectedTap || 
                        (this.selectedType && this.selectedType !== 'ALL') ||
                        this.searchSales
                    );
                },

                isSalesVisible(s) {
                    if (this.selectedArea && String(s.area_id) !== String(this.selectedArea)) return false;
                    if (this.selectedCluster && String(s.cluster_id) !== String(this.selectedCluster)) return false;
                    if (this.selectedTap && String(s.tap_id) !== String(this.selectedTap)) return false;
                    if (this.selectedType !== 'ALL' && s.sales_type !== this.selectedType) return false;
                    if (this.searchSales) {
                        const q = this.searchSales.toLowerCase();
                        const match = (s.name && s.name.toLowerCase().includes(q)) ||
                                      (s.code && s.code.toLowerCase().includes(q)) ||
                                      (s.phone && s.phone.toLowerCase().includes(q)) ||
                                      (s.email && s.email.toLowerCase().includes(q)) ||
                                      (s.tap_name && s.tap_name.toLowerCase().includes(q)) ||
                                      (s.cluster_name && s.cluster_name.toLowerCase().includes(q)) ||
                                      (s.area_name && s.area_name.toLowerCase().includes(q)) ||
                                      (s.sales_type && s.sales_type.toLowerCase().includes(q)) ||
                                      (s.sales_level && s.sales_level.toLowerCase().includes(q)) ||
                                      (s.bakulku_phone && s.bakulku_phone.toLowerCase().includes(q)) ||
                                      (s.warehouse_name && s.warehouse_name.toLowerCase().includes(q));
                        if (!match) return false;
                    }
                    return true;
                },

                isTypeVisible(st) {
                    if (!this.searchType) return true;
                    const q = this.searchType.toLowerCase();
                    return (st.type_name && st.type_name.toLowerCase().includes(q)) ||
                           (st.type_desc && st.type_desc.toLowerCase().includes(q));
                },

                isLevelVisible(sl) {
                    if (!this.searchLevel) return true;
                    const q = this.searchLevel.toLowerCase();
                    return (sl.level_name && sl.level_name.toLowerCase().includes(q)) ||
                           (sl.level_desc && sl.level_desc.toLowerCase().includes(q));
                },

                get filteredSales() {
                    return this.allSales.filter(s => this.isSalesVisible(s));
                },

                get filteredCount() {
                    return this.filteredSales.length;
                },

                // Logika Paginasi Tim Sales
                get totalSalesPages() {
                    if (this.salesPerPage === 'ALL') return 1;
                    const perPage = parseInt(this.salesPerPage, 10) || 10;
                    return Math.max(1, Math.ceil(this.filteredCount / perPage));
                },

                get paginatedSalesIds() {
                    if (this.salesPerPage === 'ALL') {
                        return this.filteredSales.map(s => s.id);
                    }
                    const perPage = parseInt(this.salesPerPage, 10) || 10;
                    const start = (this.salesPage - 1) * perPage;
                    return this.filteredSales.slice(start, start + perPage).map(s => s.id);
                },

                get salesPaginationStart() {
                    if (this.filteredCount === 0) return 0;
                    if (this.salesPerPage === 'ALL') return 1;
                    const perPage = parseInt(this.salesPerPage, 10) || 10;
                    return (this.salesPage - 1) * perPage + 1;
                },

                get salesPaginationEnd() {
                    if (this.salesPerPage === 'ALL') return this.filteredCount;
                    const perPage = parseInt(this.salesPerPage, 10) || 10;
                    return Math.min(this.salesPage * perPage, this.filteredCount);
                },

                get displayedSalesPages() {
                    const current = this.salesPage;
                    const total = this.totalSalesPages;
                    if (total <= 7) {
                        return Array.from({ length: total }, (_, i) => i + 1);
                    }
                    if (current <= 4) {
                        return [1, 2, 3, 4, 5, '...', total];
                    }
                    if (current >= total - 3) {
                        return [1, '...', total - 4, total - 3, total - 2, total - 1, total];
                    }
                    return [1, '...', current - 1, current, current + 1, '...', total];
                },

                openEditModal(s) {
                    this.editData = { ...s };
                    this.editModalOpen = true;
                    this.refreshIcons();
                },

                // Handlers untuk Modal Sales Type
                openTypeModal(item = null) {
                    if (item) {
                        this.isTypeEdit = true;
                        this.typeFormAction = "/master/sales-types/update/" + item.id;
                        this.typeForm = { id: item.id, type_name: item.type_name, type_desc: item.type_desc || '', status: item.status || 'ACTIVE' };
                    } else {
                        this.isTypeEdit = false;
                        this.typeFormAction = "{{ route('master.sales-types.store') }}";
                        this.typeForm = { id: null, type_name: '', type_desc: '', status: 'ACTIVE' };
                    }
                    this.showTypeModal = true;
                },

                // Handlers untuk Modal Sales Level
                openLevelModal(item = null) {
                    if (item) {
                        this.isLevelEdit = true;
                        this.levelFormAction = "/master/sales-levels/update/" + item.id;
                        this.levelForm = { id: item.id, level_name: item.level_name, level_desc: item.level_desc || '', status: item.status || 'ACTIVE' };
                    } else {
                        this.isLevelEdit = false;
                        this.levelFormAction = "{{ route('master.sales-levels.store') }}";
                        this.levelForm = { id: null, level_name: '', level_desc: '', status: 'ACTIVE' };
                    }
                    this.showLevelModal = true;
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
