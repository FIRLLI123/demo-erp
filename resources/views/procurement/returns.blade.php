<x-app-layout>
    <x-slot name="title">Purchase Retur (Retur PO ke Vendor / Principal)</x-slot>
    <x-slot name="headerTitle">Purchase Retur (Retur Pembelian)</x-slot>
    <x-slot name="badge">Pengadaan & Principal</x-slot>

    <div x-data="purchaseReturnApp()" x-init="init()" class="space-y-6">

        <!-- Top Header & Action -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Purchase Retur (Pengembalian Barang ke Vendor)</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">RETUR PO</span>
                </h2>
                <p class="text-xs text-slate-500">Pencatatan pengembalian stok cacat pabrik, rusak segel, atau kadaluarsa batch ke Principal Telkomsel / Vendor resmi.</p>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('procurement.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 shadow-xs transition-all">
                    <i data-lucide="package-check" class="w-4 h-4 text-emerald-600"></i>
                    <span>Daftar PO & Penerimaan (GR)</span>
                </a>
                <button @click="isModalOpen = true" type="button" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white flex items-center gap-2 shadow-sm hover:shadow transition-all">
                    <i data-lucide="plus-circle" class="w-4 h-4 text-rose-400"></i>
                    <span>Buat Dokumen Retur PO</span>
                </button>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- 1. Total Dokumen Retur -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Retur PO</span>
                    <span class="p-1 rounded-lg bg-slate-100 text-slate-700">
                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                    </span>
                </div>
                <div class="text-2xl font-extrabold font-mono text-slate-900">
                    {{ $kpi['total_returns'] }}
                    <span class="text-xs font-normal text-slate-400">dokumen</span>
                </div>
                <span class="text-[10px] text-slate-400 block mt-1">Pengajuan pengembalian ke vendor</span>
            </div>

            <!-- 2. Disetujui Principal -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">Disetujui (Approved)</span>
                    <span class="p-1 rounded-lg bg-emerald-100 text-emerald-700">
                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                    </span>
                </div>
                <div class="text-2xl font-extrabold font-mono text-emerald-600">
                    {{ $kpi['approved_count'] }}
                    <span class="text-xs font-normal text-slate-400">nota</span>
                </div>
                <span class="text-[10px] text-slate-400 block mt-1">Stok & credit note disetujui</span>
            </div>

            <!-- 3. Menunggu Persetujuan -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] font-bold text-amber-700 uppercase tracking-wider">Menunggu Review</span>
                    <span class="p-1 rounded-lg bg-amber-100 text-amber-700">
                        <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                    </span>
                </div>
                <div class="text-2xl font-extrabold font-mono text-amber-600">
                    {{ $kpi['pending_count'] }}
                    <span class="text-xs font-normal text-slate-400">dokumen</span>
                </div>
                <span class="text-[10px] text-slate-400 block mt-1">Dalam proses verifikasi fisik</span>
            </div>

            <!-- 4. Estimasi Nilai Retur -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] font-bold text-rose-700 uppercase tracking-wider">Nilai Retur</span>
                    <span class="p-1 rounded-lg bg-rose-100 text-rose-700">
                        <i data-lucide="banknote" class="w-3.5 h-3.5"></i>
                    </span>
                </div>
                <div class="text-xl font-extrabold font-mono text-rose-600">
                    Rp {{ number_format($kpi['total_value'], 0, ',', '.') }}
                </div>
                <span class="text-[10px] text-slate-400 block mt-1">Potensi tagihan kembali (credit note)</span>
            </div>
        </div>

        <!-- Filter & Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <!-- Filter Bar -->
            <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-700">Daftar Dokumen Purchase Return (PR)</span>
                    <span class="text-[11px] font-semibold text-slate-400">&bull; Standar SLA Retur Vendor Max. 7 Hari Kerja</span>
                </div>

                <div class="relative w-full md:w-64">
                    <input type="text" x-model="search" placeholder="Cari no. PR, ref PO, vendor..." class="w-full pl-9 pr-3 py-1.5 text-xs bg-white border border-slate-300 rounded-xl focus:ring-slate-900 focus:border-slate-900">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2"></i>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                            <th class="py-3 px-4">No. Dokumen PR</th>
                            <th class="py-3 px-4">Tanggal Retur</th>
                            <th class="py-3 px-4">Ref. Dokumen PO</th>
                            <th class="py-3 px-4">Vendor / Principal</th>
                            <th class="py-3 px-4">Gudang Asal</th>
                            <th class="py-3 px-4 text-right">Qty Retur</th>
                            <th class="py-3 px-4 text-right">Total Nilai</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4">Alasan Retur</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <template x-for="item in filteredList" :key="item.id">
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3 px-4 font-mono font-bold text-slate-900" x-text="item.doc_number"></td>
                                <td class="py-3 px-4 text-slate-500 whitespace-nowrap" x-text="item.return_date"></td>
                                <td class="py-3 px-4 font-mono font-bold text-cyan-700" x-text="item.ref_po_number"></td>
                                <td class="py-3 px-4 font-medium text-slate-800" x-text="item.vendor_name"></td>
                                <td class="py-3 px-4 text-slate-600" x-text="item.warehouse_name"></td>
                                <td class="py-3 px-4 text-right font-mono font-extrabold text-slate-900">
                                    <span x-text="item.total_qty.toLocaleString('id-ID')"></span>
                                    <span class="text-[10px] text-slate-400 font-normal">pcs</span>
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">
                                    Rp <span x-text="item.total_amount.toLocaleString('id-ID')"></span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span x-show="item.status === 'APPROVED'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        APPROVED
                                    </span>
                                    <span x-show="item.status === 'PENDING_APPROVAL'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        REVIEW PRINCIPAL
                                    </span>
                                    <span x-show="item.status === 'DRAFT'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        DRAFT
                                    </span>
                                </td>
                                <td class="py-3 px-4 max-w-xs text-slate-500 truncate" :title="item.reason" x-text="item.reason"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- MODAL PLACEHOLDER INFORMASI RETUR PO          -->
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
                            <div class="w-10 h-10 rounded-2xl bg-rose-100 text-rose-700 flex items-center justify-center">
                                <i data-lucide="package-x" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-900">Form Pengajuan Retur PO Vendor</h3>
                                <p class="text-[11px] text-slate-500">Integrasi Alur Goods Receipt & Approval Principal</p>
                            </div>
                        </div>
                        <button type="button" @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <div class="p-6 space-y-4 text-xs">
                        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200/80 text-amber-900 space-y-2">
                            <div class="flex items-center gap-2 font-bold text-amber-800">
                                <i data-lucide="info" class="w-4 h-4 text-amber-600"></i>
                                <span>Alur Standard Operasional Prosedur (SOP) Retur PO:</span>
                            </div>
                            <ol class="list-decimal list-inside text-[11px] space-y-1 text-amber-900/90 leading-relaxed">
                                <li>Pilih dokumen referensi <strong>Purchase Order (PO)</strong> yang barang fisiknya telah di-GR.</li>
                                <li>Verifikasi nomor serial / batch produk yang mengalami reject pabrikasi.</li>
                                <li>Dokumen diterbitkan dengan status <strong>Review Principal Telkomsel</strong>.</li>
                                <li>Setelah disetujui, stok fisik otomatis dipotong dan diterbitkan <strong>Credit Note</strong> ke akun vendor.</li>
                            </ol>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Pilih Referensi Purchase Order (PO)</label>
                            <select class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-slate-900 focus:border-slate-900">
                                <option value="">-- Pilih Dokumen PO (Status RECEIVED) --</option>
                                @foreach ($receivedPos as $po)
                                    <option value="{{ $po->id }}">{{ $po->doc_number }} &bull; {{ $po->tap_name }} &bull; {{ date('d/m/Y', strtotime($po->order_date)) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Alasan Pengembalian Barang</label>
                            <textarea rows="3" placeholder="Jelaskan alasan retur fisik ke vendor secara detail..." class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-slate-900 focus:border-slate-900"></textarea>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-end gap-2.5">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-bold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50">
                            Tutup
                        </button>
                        <button type="button" @click="isModalOpen = false" class="px-5 py-2 rounded-xl text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white shadow-xs transition-all">
                            Simpan Draft Dokumen
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        function purchaseReturnApp() {
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
                        (r.ref_po_number || '').toLowerCase().includes(q) ||
                        (r.vendor_name || '').toLowerCase().includes(q) ||
                        (r.warehouse_name || '').toLowerCase().includes(q)
                    );
                }
            }
        }
    </script>
</x-app-layout>
