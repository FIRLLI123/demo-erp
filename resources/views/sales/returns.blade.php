<x-app-layout>
    <x-slot name="title">Sales Retur (Retur Penjualan Outlet)</x-slot>
    <x-slot name="headerTitle">Sales Retur (Retur Penjualan)</x-slot>
    <x-slot name="badge">Penjualan Lapangan</x-slot>

    <div x-data="salesReturnApp()" x-init="init()" class="space-y-6">

        <!-- Top Header & Action -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Sales Retur (Pengembalian Barang dari Outlet)</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">RETUR SO</span>
                </h2>
                <p class="text-xs text-slate-500">Penerimaan kembali barang dari mitra outlet (rusak, tukar denominasi, atau retur nota tagihan piutang AR).</p>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('sales.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 shadow-xs transition-all">
                    <i data-lucide="shopping-bag" class="w-4 h-4 text-amber-600"></i>
                    <span>Sales Order & Delivery (SO-GD)</span>
                </a>
                <button @click="isModalOpen = true" type="button" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white flex items-center gap-2 shadow-sm hover:shadow transition-all">
                    <i data-lucide="plus-circle" class="w-4 h-4 text-rose-400"></i>
                    <span>Buat Sales Retur</span>
                </button>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- 1. Total Dokumen Retur -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Sales Retur</span>
                    <span class="p-1 rounded-lg bg-slate-100 text-slate-700">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    </span>
                </div>
                <div class="text-2xl font-extrabold font-mono text-slate-900">
                    {{ $kpi['total_returns'] }}
                    <span class="text-xs font-normal text-slate-400">dokumen</span>
                </div>
                <span class="text-[10px] text-slate-400 block mt-1">Pengembalian barang dari toko</span>
            </div>

            <!-- 2. Selesai (Completed) -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">Selesai (Completed)</span>
                    <span class="p-1 rounded-lg bg-emerald-100 text-emerald-700">
                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                    </span>
                </div>
                <div class="text-2xl font-extrabold font-mono text-emerald-600">
                    {{ $kpi['completed_count'] }}
                    <span class="text-xs font-normal text-slate-400">nota</span>
                </div>
                <span class="text-[10px] text-slate-400 block mt-1">Stok fisik masuk & piutang dipotong</span>
            </div>

            <!-- 3. Pending Review -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] font-bold text-amber-700 uppercase tracking-wider">Pending Review</span>
                    <span class="p-1 rounded-lg bg-amber-100 text-amber-700">
                        <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                    </span>
                </div>
                <div class="text-2xl font-extrabold font-mono text-amber-600">
                    {{ $kpi['pending_count'] }}
                    <span class="text-xs font-normal text-slate-400">dokumen</span>
                </div>
                <span class="text-[10px] text-slate-400 block mt-1">Menunggu persetujuan SPV / Admin</span>
            </div>

            <!-- 4. Total Nilai Retur -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] font-bold text-rose-700 uppercase tracking-wider">Total Nilai Retur</span>
                    <span class="p-1 rounded-lg bg-rose-100 text-rose-700">
                        <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
                    </span>
                </div>
                <div class="text-xl font-extrabold font-mono text-rose-600">
                    Rp {{ number_format($kpi['total_amount'], 0, ',', '.') }}
                </div>
                <span class="text-[10px] text-slate-400 block mt-1">Koreksi omset & saldo tagihan</span>
            </div>
        </div>

        <!-- Filter & Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <!-- Filter Bar -->
            <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-700">Daftar Dokumen Sales Return (SR)</span>
                    <span class="text-[11px] font-semibold text-slate-400">&bull; Dokumen Resmi Potong Piutang Outlet</span>
                </div>

                <div class="relative w-full md:w-64">
                    <input type="text" x-model="search" placeholder="Cari no. SR, outlet, ref SO..." class="w-full pl-9 pr-3 py-1.5 text-xs bg-white border border-slate-300 rounded-xl focus:ring-slate-900 focus:border-slate-900">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2"></i>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                            <th class="py-3 px-4">No. Dokumen SR</th>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Ref. Dokumen SO</th>
                            <th class="py-3 px-4">Nama Outlet / Mitra</th>
                            <th class="py-3 px-4">Gudang Penerima</th>
                            <th class="py-3 px-4 text-right">Total Nilai Retur</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4">Keterangan / Alasan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <template x-for="item in filteredList" :key="item.id">
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3 px-4 font-mono font-bold text-slate-900" x-text="item.doc_number"></td>
                                <td class="py-3 px-4 text-slate-500 whitespace-nowrap" x-text="item.return_date"></td>
                                <td class="py-3 px-4 font-mono font-bold text-amber-700" x-text="item.so_number || item.ref_doc_number || '-'"></td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900" x-text="item.customer_name"></div>
                                    <span class="text-[10px] text-slate-400 font-mono" x-text="item.customer_code"></span>
                                </td>
                                <td class="py-3 px-4 text-slate-600" x-text="item.warehouse_name"></td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">
                                    Rp <span x-text="Number(item.total_amount).toLocaleString('id-ID')"></span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span x-show="item.status === 'COMPLETED'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        SELESAI
                                    </span>
                                    <span x-show="item.status !== 'COMPLETED'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        PENDING
                                    </span>
                                </td>
                                <td class="py-3 px-4 max-w-xs text-slate-500 truncate" :title="item.notes" x-text="item.notes || '-'"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- MODAL PLACEHOLDER INFORMASI SALES RETUR        -->
        <!-- ============================================== -->
        <div x-show="isModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto" 
             data-no-loading>
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" @click="isModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100">
                    <div class="px-6 pt-6 pb-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center">
                                <i data-lucide="rotate-ccw" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-900">Form Pembuatan Sales Retur</h3>
                                <p class="text-[11px] text-slate-500">Koreksi Stok Fisik & Pemotongan Piutang Tagihan</p>
                            </div>
                        </div>
                        <button type="button" @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <div class="p-6 space-y-4 text-xs">
                        <div class="p-4 rounded-2xl bg-blue-50 border border-blue-200/80 text-blue-900 space-y-2">
                            <div class="flex items-center gap-2 font-bold text-blue-800">
                                <i data-lucide="info" class="w-4 h-4 text-blue-600"></i>
                                <span>Ketentuan Kebijakan Sales Retur Outlet:</span>
                            </div>
                            <ul class="list-disc list-inside text-[11px] space-y-1 text-blue-900/90 leading-relaxed">
                                <li>Retur SO dapat diajukan setelah barang berstatus <strong>TERKIRIM (DELIVERED)</strong>.</li>
                                <li>Stok yang diretur akan otomatis masuk kembali ke saldo fisik gudang kanvas/gudang utama.</li>
                                <li>Nominal retur akan langsung memotong sisa piutang di modul <strong>Finance & AR</strong>.</li>
                            </ul>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Pilih Referensi Sales Order (SO)</label>
                            <select class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-slate-900 focus:border-slate-900">
                                <option value="">-- Pilih Sales Order Terkirim --</option>
                                @foreach ($deliveredSos as $dSo)
                                    <option value="{{ $dSo->id }}">{{ $dSo->doc_number }} &bull; {{ $dSo->customer_name }} &bull; Rp {{ number_format($dSo->grand_total, 0, ',', '.') }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Catatan / Alasan Retur dari Outlet</label>
                            <textarea rows="3" placeholder="Contoh: Voucher fisik tergores / kemasan basah saat hujan..." class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-slate-900 focus:border-slate-900"></textarea>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-end gap-2.5">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-bold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50">
                            Tutup
                        </button>
                        <button type="button" @click="isModalOpen = false" class="px-5 py-2 rounded-xl text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white shadow-xs transition-all">
                            Simpan Draft Retur
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        function salesReturnApp() {
            return {
                isModalOpen: false,
                search: '',
                returns: @json($returns),

                init() {
                    this.$nextTick(() => {
                        if (window.lucide) {
                            window.lucide.createIcons();
                        }
                    });
                },

                get filteredList() {
                    if (!this.search.trim()) return this.returns;
                    const q = this.search.toLowerCase();
                    return this.returns.filter(r => 
                        (r.doc_number || '').toLowerCase().includes(q) ||
                        (r.so_number || r.ref_doc_number || '').toLowerCase().includes(q) ||
                        (r.customer_name || '').toLowerCase().includes(q) ||
                        (r.warehouse_name || '').toLowerCase().includes(q)
                    );
                }
            }
        }
    </script>
</x-app-layout>
