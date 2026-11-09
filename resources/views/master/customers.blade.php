<x-app-layout>
    <x-slot name="title">Master Data Toko & Outlet Pelanggan</x-slot>
    <x-slot name="headerTitle">Toko & Outlet Pelanggan</x-slot>
    <x-slot name="badge">Master Customer</x-slot>

    <div x-data="customerMasterApp()">
        
        <!-- Top Action & Navigation Banner -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Master Toko, Outlet & Sub Dealer</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">PT WAHANA PUTRA YUDHA</span>
                </h2>
                <p class="text-xs text-slate-500">Database seluruh mitra toko konter, modern outlet, dan sub dealer resmi yang dilayani oleh armada penjualan</p>
            </div>

            <button type="button" 
                    @click="createOpen = !createOpen" 
                    class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-sm transition-all active:scale-95 shrink-0">
                <i data-lucide="store" class="w-4 h-4 text-amber-400"></i>
                <span x-text="createOpen ? 'Tutup Formulir' : '+ Tambah Toko Baru'"></span>
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
        <!-- FORM TAMBAH TOKO BARU (INLINE CARD)            -->
        <!-- ============================================== -->
        <div x-show="createOpen" x-cloak x-transition class="mb-6 bg-white rounded-2xl border-2 border-amber-500/30 p-5 shadow-lg">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-amber-100 text-amber-700">
                        <i data-lucide="store" class="w-4 h-4"></i>
                    </span>
                    <h3 class="font-bold text-slate-900 text-sm">Registrasi Outlet / Toko Baru</h3>
                </div>
                <button @click="createOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('master.customers.store') }}">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Toko / Outlet <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required placeholder="Contoh: Toko Berkah Cell" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Outlet <span class="text-rose-500">*</span></label>
                        <select name="type" required class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                            <option value="RETAIL">Retail (Outlet / Konter Reguler)</option>
                            <option value="GROSIR">Grosir (Sub-Dealer / Toko Besar)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Wilayah TAP <span class="text-rose-500">*</span></label>
                        <select name="tap_id" required class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                            @foreach($taps as $tap)
                                <option value="{{ $tap->id }}">{{ $tap->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Telepon / WA Toko</label>
                        <input type="text" name="phone" placeholder="0812xxxxxxxx" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Limit Plafon Kredit (Rp)</label>
                        <input type="number" name="credit_limit" value="10000000" min="0" step="500000" class="w-full text-xs font-mono font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Lengkap Toko</label>
                        <input type="text" name="address" placeholder="Nama Jalan, Kelurahan, Kecamatan" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="createOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-sm transition-all">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Data Toko</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- ============================================== -->
        <!-- MODAL EDIT CUSTOMER                            -->
        <!-- ============================================== -->
        <template x-if="editModalOpen && editData">
            <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                <div class="bg-white rounded-2xl border border-slate-200 max-w-lg w-full p-6 shadow-2xl" @click.outside="editModalOpen = false">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                        <h3 class="font-bold text-slate-900 text-sm">Edit Data Toko [<span x-text="editData.code"></span>]</h3>
                        <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600">&times;</button>
                    </div>

                    <form :action="'/master/customers/update/' + editData.id" method="POST">
                        @csrf
                        <div class="space-y-3 mb-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Toko / Outlet</label>
                                <input type="text" name="name" x-model="editData.name" required class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Outlet</label>
                                    <select name="type" x-model="editData.type" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                        <option value="RETAIL">RETAIL (Outlet Konter)</option>
                                        <option value="GROSIR">GROSIR (Sub Dealer)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Wilayah TAP</label>
                                    <select name="tap_id" x-model="editData.tap_id" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                        @foreach($taps as $tap)
                                            <option value="{{ $tap->id }}">{{ $tap->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">No. Telepon / WA</label>
                                    <input type="text" name="phone" x-model="editData.phone" class="w-full text-xs font-medium bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Limit Kredit (Rp)</label>
                                    <input type="number" name="credit_limit" x-model.number="editData.credit_limit" class="w-full text-xs font-mono font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Alamat</label>
                                <textarea name="address" x-model="editData.address" rows="2" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-2.5"></textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Status Keaktifan</label>
                                <select name="status" x-model="editData.status" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                    <option value="ACTIVE">ACTIVE (Dapat Melakukan Pembelian)</option>
                                    <option value="INACTIVE">INACTIVE (Diblokir / Tutup)</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                            <button type="button" @click="editModalOpen = false" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl text-xs">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 bg-slate-900 text-white font-bold rounded-xl text-xs">
                                Perbarui Toko
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
                    <span class="text-[11px] font-bold text-slate-400 block uppercase">Total Toko Terdaftar:</span>
                    <span class="text-2xl font-extrabold font-mono text-slate-900">{{ count($customers) }} Toko</span>
                </div>
                <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
                    <i data-lucide="store" class="w-6 h-6"></i>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 block uppercase">Outlet Status Aktif:</span>
                    <span class="text-2xl font-extrabold font-mono text-emerald-600">{{ $customers->where('status', 'ACTIVE')->count() }} Outlet</span>
                </div>
                <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                    <i data-lucide="check-circle" class="w-6 h-6"></i>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 block uppercase">Total Piutang Berjalan:</span>
                    <span class="text-2xl font-extrabold font-mono text-rose-600">Rp {{ number_format($customers->sum('unpaid_ar_amount'), 0, ',', '.') }}</span>
                </div>
                <div class="p-3 bg-rose-50 text-rose-600 rounded-xl">
                    <i data-lucide="receipt" class="w-6 h-6"></i>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- TABEL MASTER TOKO                              -->
        <!-- ============================================== -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <span class="text-xs font-extrabold text-slate-800">Daftar Toko & Outlet Pelanggan</span>
                <span class="text-xs text-slate-500 font-mono">{{ count($customers) }} Toko</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Kode Toko</th>
                            <th class="py-3 px-4">Nama Toko & Alamat</th>
                            <th class="py-3 px-4 text-center">Tipe Outlet</th>
                            <th class="py-3 px-4">Wilayah TAP</th>
                            <th class="py-3 px-4">No. Telepon / WA</th>
                            <th class="py-3 px-4 text-right">Limit Kredit</th>
                            <th class="py-3 px-4 text-right">Sisa Piutang (AR)</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($customers as $c)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-3.5 px-4 font-mono font-bold text-amber-900">
                                    {{ $c->code }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-extrabold text-slate-900 block text-xs">{{ $c->name }}</span>
                                    <span class="text-[11px] text-slate-500 block truncate max-w-xs">{{ $c->address }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold 
                                        {{ $c->type === 'GROSIR' ? 'bg-purple-100 text-purple-700 border border-purple-200' : 'bg-blue-100 text-blue-700 border border-blue-200' }}">
                                        {{ $c->type }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-700 font-semibold">
                                    {{ $c->tap_name }}
                                </td>
                                <td class="py-3.5 px-4 font-mono text-slate-600">
                                    {{ $c->phone ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-800">
                                    Rp {{ number_format($c->credit_limit, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    @if($c->unpaid_ar_amount > 0)
                                        <span class="font-mono font-extrabold text-rose-600 block">
                                            Rp {{ number_format($c->unpaid_ar_amount, 0, ',', '.') }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 block">{{ $c->unpaid_ar_count }} Faktur</span>
                                    @else
                                        <span class="text-emerald-700 font-bold text-[11px]">Lunas (Rp 0)</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($c->status === 'ACTIVE')
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
                                            @click="openEditModal({{ json_encode($c) }})" 
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
        function customerMasterApp() {
            return {
                createOpen: false,
                editModalOpen: false,
                editData: null,

                openEditModal(customer) {
                    this.editData = Object.assign({}, customer);
                    this.editModalOpen = true;
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
