<x-app-layout>
    <x-slot name="title">Stock Adjustment (+/-) - Penyesuaian Stok Fisik</x-slot>
    <x-slot name="headerTitle">Stock Adjustment (Penyesuaian Stok)</x-slot>
    <x-slot name="badge">Inventori & Logistik</x-slot>

    <div x-data="stockAdjustmentApp()" x-init="init()" class="space-y-6">

        <!-- Top Header & Action -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Penyesuaian Stok Fisik (Stock Adjustment)</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-purple-100 text-purple-800 border border-purple-200">REALTIME UPDATE</span>
                </h2>
                <p class="text-xs text-slate-500">Penyesuaian saldo fisik gudang (+ Penambahan / - Pengurangan) akibat hasil opname, barang rusak, atau koreksi audit.</p>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('inventory.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 shadow-xs transition-all">
                    <i data-lucide="boxes" class="w-4 h-4 text-slate-500"></i>
                    <span>Saldo Stok Seluruh Gudang</span>
                </a>
                <button @click="openModal()" type="button" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white flex items-center gap-2 shadow-sm hover:shadow transition-all">
                    <i data-lucide="plus-circle" class="w-4 h-4 text-rose-400"></i>
                    <span>Buat Penyesuaian Stok</span>
                </button>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- 1. Total Penyesuaian -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Penyesuaian</span>
                    <span class="p-1 rounded-lg bg-slate-100 text-slate-700">
                        <i data-lucide="sliders-horizontal" class="w-3.5 h-3.5"></i>
                    </span>
                </div>
                <div class="text-2xl font-extrabold font-mono text-slate-900">
                    {{ number_format($totalAdj, 0, ',', '.') }}
                    <span class="text-xs font-normal text-slate-400">dokumen</span>
                </div>
                <span class="text-[10px] text-slate-400 block mt-1">Total riwayat mutasi penyesuaian</span>
            </div>

            <!-- 2. Stok Masuk (+) -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">Koreksi Tambah (+)</span>
                    <span class="p-1 rounded-lg bg-emerald-100 text-emerald-700">
                        <i data-lucide="arrow-up-right" class="w-3.5 h-3.5"></i>
                    </span>
                </div>
                <div class="text-2xl font-extrabold font-mono text-emerald-600">
                    +{{ number_format($totalPlus, 0, ',', '.') }}
                    <span class="text-xs font-normal text-slate-400">pcs</span>
                </div>
                <span class="text-[10px] text-slate-400 block mt-1">Temuan fisik lebih opname</span>
            </div>

            <!-- 3. Stok Keluar (-) -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] font-bold text-rose-700 uppercase tracking-wider">Koreksi Kurang (-)</span>
                    <span class="p-1 rounded-lg bg-rose-100 text-rose-700">
                        <i data-lucide="arrow-down-right" class="w-3.5 h-3.5"></i>
                    </span>
                </div>
                <div class="text-2xl font-extrabold font-mono text-rose-600">
                    -{{ number_format($totalMinus, 0, ',', '.') }}
                    <span class="text-xs font-normal text-slate-400">pcs</span>
                </div>
                <span class="text-[10px] text-slate-400 block mt-1">Barang rusak / hilang / selisih</span>
            </div>

            <!-- 4. Gudang Terdampak -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] font-bold text-purple-700 uppercase tracking-wider">Gudang Terdampak</span>
                    <span class="p-1 rounded-lg bg-purple-100 text-purple-700">
                        <i data-lucide="warehouse" class="w-3.5 h-3.5"></i>
                    </span>
                </div>
                <div class="text-2xl font-extrabold font-mono text-purple-950">
                    {{ $totalWarehouseAffected }}
                    <span class="text-xs font-normal text-slate-400">lokasi</span>
                </div>
                <span class="text-[10px] text-slate-400 block mt-1">Titik simpan dengan adjustment</span>
            </div>
        </div>

        <!-- Filter & Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <!-- Filter Bar -->
            <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Filter Tipe Penyesuaian -->
                    <div class="inline-flex rounded-xl p-0.5 bg-slate-200/70 text-xs font-bold">
                        <button type="button" @click="typeFilter = 'ALL'" 
                                :class="typeFilter === 'ALL' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                                class="px-3 py-1.5 rounded-lg transition-all">Semua Tipe</button>
                        <button type="button" @click="typeFilter = 'IN'" 
                                :class="typeFilter === 'IN' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                                class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1">
                            <span>(+) Tambah</span>
                        </button>
                        <button type="button" @click="typeFilter = 'OUT'" 
                                :class="typeFilter === 'OUT' ? 'bg-rose-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                                class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1">
                            <span>(-) Kurang</span>
                        </button>
                    </div>

                    <!-- Filter Gudang -->
                    <select x-model="whFilter" class="text-xs font-semibold bg-white border border-slate-300 rounded-xl px-3 py-1.5 text-slate-700 focus:ring-slate-900 focus:border-slate-900">
                        <option value="ALL">Semua Gudang</option>
                        @foreach ($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->type }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Search -->
                <div class="relative w-full md:w-64">
                    <input type="text" x-model="searchQuery" placeholder="Cari no. dok, produk, catatan..." class="w-full pl-9 pr-3 py-1.5 text-xs bg-white border border-slate-300 rounded-xl focus:ring-slate-900 focus:border-slate-900">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2"></i>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                            <th class="py-3 px-4">No. Dokumen</th>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Gudang</th>
                            <th class="py-3 px-4">Produk</th>
                            <th class="py-3 px-4 text-center">Jenis</th>
                            <th class="py-3 px-4 text-right">Qty Penyesuaian</th>
                            <th class="py-3 px-4 text-right">Saldo Akhir</th>
                            <th class="py-3 px-4">Keterangan / Alasan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <template x-for="item in filteredAdjustments" :key="item.id">
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3 px-4 font-mono font-bold text-slate-900" x-text="item.doc_number"></td>
                                <td class="py-3 px-4 text-slate-500 whitespace-nowrap" x-text="item.date"></td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-800" x-text="item.warehouse_name"></div>
                                    <span class="text-[10px] text-slate-400 uppercase font-mono" x-text="item.warehouse_type"></span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900" x-text="item.product_name"></div>
                                    <span class="text-[10px] text-slate-400 font-mono" x-text="item.sku"></span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span x-show="item.mutation_type === 'IN'" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <i data-lucide="plus" class="w-3 h-3"></i> PENAMBAHAN
                                    </span>
                                    <span x-show="item.mutation_type === 'OUT'" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        <i data-lucide="minus" class="w-3 h-3"></i> PENGURANGAN
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-extrabold" :class="item.mutation_type === 'IN' ? 'text-emerald-600' : 'text-rose-600'">
                                    <span x-text="item.mutation_type === 'IN' ? '+' + item.quantity.toLocaleString('id-ID') : '-' + item.quantity.toLocaleString('id-ID')"></span>
                                    <span class="text-[10px] text-slate-400 font-normal">pcs</span>
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">
                                    <span x-text="item.balance_after.toLocaleString('id-ID')"></span>
                                    <span class="text-[10px] text-slate-400 font-normal">pcs</span>
                                </td>
                                <td class="py-3 px-4 max-w-xs text-slate-600 text-xs truncate" :title="item.notes" x-text="item.notes"></td>
                            </tr>
                        </template>
                        <tr x-show="filteredAdjustments.length === 0">
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-2">
                                    <i data-lucide="inbox" class="w-6 h-6"></i>
                                </div>
                                <p class="text-xs font-semibold">Tidak ada riwayat penyesuaian stok yang sesuai filter.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- MODAL FORM: BUAT STOCK ADJUSTMENT REALTIME     -->
        <!-- ============================================== -->
        <div x-show="isModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto" 
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true"
             data-no-loading>
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Backdrop -->
                <div x-show="isModalOpen" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" 
                     @click="closeModal()"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal Panel -->
                <div x-show="isModalOpen" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100">
                    
                    <form action="{{ route('inventory.adjustment.store') }}" method="POST" data-no-loading>
                        @csrf
                        <!-- Header Modal -->
                        <div class="px-6 pt-6 pb-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-purple-100 text-purple-700 flex items-center justify-center">
                                    <i data-lucide="sliders-horizontal" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-extrabold text-slate-900">Form Stock Adjustment Fisik</h3>
                                    <p class="text-[11px] text-slate-500">Saldo stok fisik di database akan langsung disesuaikan.</p>
                                </div>
                            </div>
                            <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        <!-- Form Body -->
                        <div class="p-6 space-y-4 text-xs">
                            <!-- 1. Pilihan Gudang -->
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Pilih Gudang Target <span class="text-rose-500">*</span></label>
                                <select name="warehouse_id" x-model="formWarehouse" @change="updateCurrentStock()" required class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-slate-900 focus:border-slate-900">
                                    <option value="">-- Pilih Gudang Fisik --</option>
                                    @foreach ($warehouses as $wh)
                                        <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->type }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- 2. Pilihan Produk -->
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Pilih Produk <span class="text-rose-500">*</span></label>
                                <select name="product_id" x-model="formProduct" @change="updateCurrentStock()" required class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-slate-900 focus:border-slate-900">
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach ($products as $prod)
                                        <option value="{{ $prod->id }}">{{ $prod->name }} [SKU: {{ $prod->sku }}]</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Info Saldo Stok Saat Ini -->
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Saldo Stok Fisik Saat Ini</span>
                                    <span class="text-base font-extrabold font-mono text-slate-900" x-text="currentStock.toLocaleString('id-ID') + ' pcs'"></span>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Estimasi Stok Akhir</span>
                                    <span class="text-base font-extrabold font-mono" :class="estimatedFinalStock < 0 ? 'text-rose-600' : 'text-purple-700'" x-text="estimatedFinalStock.toLocaleString('id-ID') + ' pcs'"></span>
                                </div>
                            </div>

                            <!-- Peringatan jika stok minus melebihi saldo -->
                            <div x-show="formType === 'MINUS' && formQty > currentStock" class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 flex items-center gap-2">
                                <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0 text-rose-600"></i>
                                <span class="text-[11px] font-semibold">Pengurangan melebihi stok yang ada! Sistem menolak penyesuaian minus yang membuat saldo di bawah 0.</span>
                            </div>

                            <!-- 3. Jenis Penyesuaian (+ / -) -->
                            <div>
                                <label class="block font-bold text-slate-700 mb-1.5">Tipe Penyesuaian <span class="text-rose-500">*</span></label>
                                <div class="grid grid-cols-2 gap-3">
                                    <!-- Option Plus -->
                                    <label class="cursor-pointer border-2 rounded-2xl p-3 flex items-start gap-2.5 transition-all"
                                           :class="formType === 'PLUS' ? 'border-emerald-500 bg-emerald-50/50 text-emerald-950 shadow-xs' : 'border-slate-200 hover:border-slate-300 text-slate-600'">
                                        <input type="radio" name="adjustment_type" value="PLUS" x-model="formType" class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                                        <div>
                                            <div class="font-extrabold text-xs text-emerald-700 flex items-center gap-1">
                                                <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i> Penambahan (+)
                                            </div>
                                            <p class="text-[10px] text-slate-500 mt-0.5">Temuan fisik lebih opname atau koreksi tambah.</p>
                                        </div>
                                    </label>

                                    <!-- Option Minus -->
                                    <label class="cursor-pointer border-2 rounded-2xl p-3 flex items-start gap-2.5 transition-all"
                                           :class="formType === 'MINUS' ? 'border-rose-500 bg-rose-50/50 text-rose-950 shadow-xs' : 'border-slate-200 hover:border-slate-300 text-slate-600'">
                                        <input type="radio" name="adjustment_type" value="MINUS" x-model="formType" class="mt-0.5 text-rose-600 focus:ring-rose-500">
                                        <div>
                                            <div class="font-extrabold text-xs text-rose-700 flex items-center gap-1">
                                                <i data-lucide="minus-circle" class="w-3.5 h-3.5"></i> Pengurangan (-)
                                            </div>
                                            <p class="text-[10px] text-slate-500 mt-0.5">Barang rusak, hilang, atau selisih kurang opname.</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- 4. Jumlah Qty -->
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Jumlah Fisik yang Disesuaikan (Qty) <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <input type="number" name="quantity" min="1" x-model.number="formQty" required placeholder="Contoh: 10" class="w-full text-xs font-mono font-bold bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-slate-900 focus:border-slate-900">
                                    <span class="absolute right-3 top-2 text-[11px] font-bold text-slate-400">PCS</span>
                                </div>
                            </div>

                            <!-- 5. Alasan / Catatan Penyesuaian -->
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Keterangan / Alasan Penyesuaian <span class="text-rose-500">*</span></label>
                                <textarea name="notes" rows="2" required placeholder="Contoh: Hasil Stock Opname Fisik Akhir Bulan / Segel kemasan rusak saat bongkar muat..." class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-slate-900 focus:border-slate-900"></textarea>
                                <p class="text-[10px] text-slate-400 mt-1">Catatan ini akan tersimpan permanen di Kartu Mutasi dan Audit Trail sistem.</p>
                            </div>
                        </div>

                        <!-- Footer Modal -->
                        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-end gap-2.5">
                            <button type="button" @click="closeModal()" class="px-4 py-2 rounded-xl text-xs font-bold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50">
                                Batal
                            </button>
                            <button type="submit" 
                                    :disabled="formType === 'MINUS' && formQty > currentStock"
                                    :class="(formType === 'MINUS' && formQty > currentStock) ? 'opacity-50 cursor-not-allowed bg-slate-400' : 'bg-slate-900 hover:bg-slate-800'"
                                    class="px-5 py-2 rounded-xl text-xs font-bold text-white shadow-xs transition-all flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                                <span>Simpan & Update Stok</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <!-- Alpine App Logic -->
    <script>
        function stockAdjustmentApp() {
            return {
                isModalOpen: false,
                typeFilter: 'ALL',
                whFilter: 'ALL',
                searchQuery: '',
                adjustments: @json($adjustments),
                balances: @json($balances),

                // Form states
                formWarehouse: '',
                formProduct: '',
                formType: 'PLUS',
                formQty: 1,
                currentStock: 0,

                init() {
                    // refresh lucide icons
                    this.$nextTick(() => {
                        if (window.lucide) {
                            window.lucide.createIcons();
                        }
                    });
                },

                openModal() {
                    this.isModalOpen = true;
                    this.updateCurrentStock();
                    this.$nextTick(() => {
                        if (window.lucide) {
                            window.lucide.createIcons();
                        }
                    });
                },

                closeModal() {
                    this.isModalOpen = false;
                },

                updateCurrentStock() {
                    if (!this.formWarehouse || !this.formProduct) {
                        this.currentStock = 0;
                        return;
                    }

                    const match = this.balances.find(b => 
                        String(b.warehouse_id) === String(this.formWarehouse) && 
                        String(b.product_id) === String(this.formProduct)
                    );

                    this.currentStock = match ? Number(match.quantity) : 0;
                },

                get estimatedFinalStock() {
                    const qty = Number(this.formQty) || 0;
                    if (this.formType === 'PLUS') {
                        return this.currentStock + qty;
                    } else {
                        return this.currentStock - qty;
                    }
                },

                get filteredAdjustments() {
                    return this.adjustments.filter(item => {
                        // Filter Type
                        if (this.typeFilter !== 'ALL' && item.mutation_type !== this.typeFilter) {
                            return false;
                        }

                        // Filter Warehouse
                        if (this.whFilter !== 'ALL' && String(item.warehouse_id) !== String(this.whFilter)) {
                            return false;
                        }

                        // Search
                        if (this.searchQuery.trim() !== '') {
                            const q = this.searchQuery.toLowerCase();
                            const doc = (item.doc_number || '').toLowerCase();
                            const prod = (item.product_name || '').toLowerCase();
                            const wh = (item.warehouse_name || '').toLowerCase();
                            const notes = (item.notes || '').toLowerCase();

                            return doc.includes(q) || prod.includes(q) || wh.includes(q) || notes.includes(q);
                        }

                        return true;
                    });
                }
            }
        }
    </script>
</x-app-layout>

