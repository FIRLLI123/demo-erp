<x-app-layout>
    <x-slot name="title">Master Data Tim Sales Kanvaser</x-slot>
    <x-slot name="headerTitle">Tim Sales & Kanvaser</x-slot>
    <x-slot name="badge">Master Sales Force</x-slot>

    <div x-data="salesMasterApp()">
        
        <!-- Top Action & Navigation Banner -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Master Tim Sales & Kanvaser Motor</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-purple-100 text-purple-800 border border-purple-200">PT WAHANA PUTRA YUDHA</span>
                </h2>
                <p class="text-xs text-slate-500">Daftar tenaga penjual lapangan resmi distributor Telkomsel beserta alokasi gudang kanvas motor masing-masing</p>
            </div>

            <button type="button" 
                    @click="createOpen = !createOpen" 
                    class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-sm transition-all active:scale-95 shrink-0">
                <i data-lucide="user-plus" class="w-4 h-4 text-emerald-400"></i>
                <span x-text="createOpen ? 'Tutup Formulir' : '+ Tambah Sales Baru'"></span>
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

        <!-- ============================================== -->
        <!-- FORM TAMBAH SALES BARU (INLINE CARD)           -->
        <!-- ============================================== -->
        <div x-show="createOpen" x-cloak x-transition class="mb-6 bg-white rounded-2xl border-2 border-emerald-500/30 p-5 shadow-lg">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-emerald-100 text-emerald-700">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                    </span>
                    <h3 class="font-bold text-slate-900 text-sm">Pendaftaran Sales Force & Pembuatan Gudang Kanvas</h3>
                </div>
                <button @click="createOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('master.sales-team.store') }}">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap Sales <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required placeholder="Contoh: Andi Pratama" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WhatsApp / Telepon <span class="text-rose-500">*</span></label>
                        <input type="text" name="phone" required placeholder="0812xxxxxxxx" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Penugasan TAP / Wilayah <span class="text-rose-500">*</span></label>
                        <select name="tap_id" required class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                            @foreach($taps as $tap)
                                <option value="{{ $tap->id }}">{{ $tap->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="p-3 bg-emerald-50/70 border border-emerald-200 rounded-xl text-xs text-emerald-800 mb-4 flex items-center gap-2">
                    <i data-lucide="info" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                    <span>Sistem akan secara otomatis membentuk <strong>Gudang Kanvas Mobile (SALES)</strong> baru untuk menampung stok harian sales ini.</span>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="createOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-sm transition-all">
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
                <div class="bg-white rounded-2xl border border-slate-200 max-w-lg w-full p-6 shadow-2xl" @click.outside="editModalOpen = false">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                        <h3 class="font-bold text-slate-900 text-sm">Edit Data Salesman [<span x-text="editData.code"></span>]</h3>
                        <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600">&times;</button>
                    </div>

                    <form :action="'/master/sales-team/update/' + editData.id" method="POST">
                        @csrf
                        <div class="space-y-4 mb-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Sales</label>
                                <input type="text" name="name" x-model="editData.name" required class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Telepon / WA</label>
                                <input type="text" name="phone" x-model="editData.phone" required class="w-full text-xs font-medium bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
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
                                <label class="block text-xs font-bold text-slate-700 mb-1">Status Keaktifan</label>
                                <select name="status" x-model="editData.status" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                    <option value="ACTIVE">ACTIVE (Aktif Bertugas)</option>
                                    <option value="INACTIVE">INACTIVE (Nonaktif / Cuti)</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                            <button type="button" @click="editModalOpen = false" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl text-xs">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 bg-slate-900 text-white font-bold rounded-xl text-xs">
                                Perbarui Sales
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- ============================================== -->
        <!-- KPI METRICS SUMMARY                            -->
        <!-- ============================================== -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
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
                    <span class="text-[11px] font-bold text-slate-400 block uppercase">Sales Berstatus Aktif:</span>
                    <span class="text-2xl font-extrabold font-mono text-emerald-600">{{ $salesList->where('status', 'ACTIVE')->count() }} Orang</span>
                </div>
                <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                    <i data-lucide="user-check" class="w-6 h-6"></i>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 block uppercase">Gudang Kanvas Terhubung:</span>
                    <span class="text-2xl font-extrabold font-mono text-cyan-700">{{ $salesList->whereNotNull('warehouse_id')->count() }} Gudang</span>
                </div>
                <div class="p-3 bg-cyan-50 text-cyan-600 rounded-xl">
                    <i data-lucide="truck" class="w-6 h-6"></i>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- TABEL MASTER SALES                             -->
        <!-- ============================================== -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <span class="text-xs font-extrabold text-slate-800">Daftar Sales Force Resmi Distributor</span>
                <span class="text-xs text-slate-500 font-mono">{{ count($salesList) }} Personel</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Kode Sales</th>
                            <th class="py-3 px-4">Nama Lengkap</th>
                            <th class="py-3 px-4">No. Telepon / WA</th>
                            <th class="py-3 px-4">Wilayah TAP</th>
                            <th class="py-3 px-4">Gudang Kanvas Mobile</th>
                            <th class="py-3 px-4 text-center">Total Order SO</th>
                            <th class="py-3 px-4 text-right">Total Nilai Penjualan</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($salesList as $s)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-3.5 px-4 font-mono font-bold text-purple-900">
                                    {{ $s->code }}
                                </td>
                                <td class="py-3.5 px-4 font-extrabold text-slate-900">
                                    {{ $s->name }}
                                </td>
                                <td class="py-3.5 px-4 font-mono text-slate-600">
                                    {{ $s->phone ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-700 font-semibold">
                                    {{ $s->tap_name }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($s->warehouse_name)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            {{ $s->warehouse_name }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic">Belum dibuat</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-800">
                                    {{ $s->total_orders }} order
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-extrabold text-slate-900">
                                    Rp {{ number_format($s->total_sales_amount, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($s->status === 'ACTIVE')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            ACTIVE
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                            INACTIVE
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <button type="button" 
                                            @click="openEditModal({{ json_encode($s) }})" 
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
        function salesMasterApp() {
            return {
                createOpen: false,
                editModalOpen: false,
                editData: null,

                openEditModal(sales) {
                    this.editData = Object.assign({}, sales);
                    this.editModalOpen = true;
                }
            }
        }
    </script>
    @endpush
</x-app-layout>

