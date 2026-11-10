<x-app-layout>
    <x-slot name="title">Pengadaan & Goods Received (PO - GR)</x-slot>
    <x-slot name="headerTitle">Purchase Order & Goods Received</x-slot>
    <x-slot name="badge">PO &rarr; GR</x-slot>

    <div x-data="procurementMasterDetail()" x-init="initData()">
        
        <!-- Top Action Bar -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Purchase Order & Penerimaan Barang</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">PT WAHANA PUTRA YUDHA</span>
                </h2>
                <p class="text-xs text-slate-500">Pengadaan Resmi PT Wahana Putra Yudha: Pemesanan ke Supplier Pusat & Penerimaan Fisik ke Gudang Utama TAP</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('reports.index', ['type' => 'PO_GR']) }}" 
                   class="px-3.5 py-2.5 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-xs transition-all shrink-0">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600"></i>
                    <span>Rekap Laporan PO (Excel/PDF)</span>
                </a>
                <button type="button" 
                        @click="createPoOpen = !createPoOpen" 
                        class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-sm transition-all active:scale-95 shrink-0">
                    <i data-lucide="plus-circle" class="w-4 h-4 text-emerald-400"></i>
                    <span x-text="createPoOpen ? 'Tutup Form' : '+ Buat PO Baru'"></span>
                </button>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- FORM PEMBUATAN PO MULTI-ITEM (INLINE CARD)    -->
        <!-- ============================================== -->
        <div x-show="createPoOpen" x-cloak x-transition class="mb-6 bg-white rounded-2xl border-2 border-emerald-500/30 p-5 shadow-lg">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <i data-lucide="file-plus" class="w-4 h-4 text-emerald-600"></i>
                    <span>Buat Purchase Order (PO)</span>
                </h3>
                <button @click="createPoOpen = false" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('procurement.store-po') }}" 
                  data-loading-title="Menerbitkan PO"
                  data-loading-sub="Menyimpan dokumen Purchase Order baru..."
                  data-doctype="PO">
                @csrf

                <!-- Header Info -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">TAP Pemesan</label>
                        <select name="tap_id" required class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                            @foreach($taps as $tap)
                                <option value="{{ $tap->id }}">{{ $tap->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Supplier</label>
                        <input type="text" name="supplier_name" value="PT TELKOMSEL DISTRIBUSI PUSAT" required class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal</label>
                        <input type="date" name="order_date" value="{{ date('Y-m-d') }}" required class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan / Keterangan (Notes)</label>
                        <input type="text" name="notes" placeholder="e.g. PO Pengadaan Awal Bulan" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none">
                    </div>
                </div>

                <!-- Multi-Item Rows Table -->
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold text-slate-800">Daftar Barang</label>
                        <button type="button" @click="addItemRow()" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Baris
                        </button>
                    </div>

                    <div class="border border-slate-200 rounded-xl overflow-hidden bg-slate-50/50">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-100/70 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200/80">
                                <tr>
                                    <th class="py-2.5 px-4 w-6/12">Produk</th>
                                    <th class="py-2.5 px-4 w-3/12">Tracking Method</th>
                                    <th class="py-2.5 px-4 w-2/12 text-center">Kuantitas (Qty)</th>
                                    <th class="py-2.5 px-4 w-1/12 text-center"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200/70 bg-white">
                                <template x-for="(row, index) in items" :key="index">
                                    <tr>
                                        <td class="py-2.5 px-4">
                                            <select :name="`items[${index}][product_id]`" 
                                                    x-model="row.product_id" 
                                                    @change="updateProductRow(index)"
                                                    required 
                                                    class="w-full text-xs font-bold text-slate-800 bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 focus:outline-none">
                                                <option value="">-- Pilih Produk --</option>
                                                <optgroup label="🏷️ BAHAN BAKU REPACK (0K & SALDO INJEKSI NGRS)">
                                                    @foreach($products->filter(fn($p) => str_starts_with($p->sku, 'RAW-') || str_starts_with($p->sku, 'BULK-')) as $p)
                                                        <option value="{{ $p->id }}" 
                                                                data-cost="{{ $p->cost_price }}" 
                                                                data-sku="{{ $p->sku }}" 
                                                                data-tracking="{{ $p->tracking_method }}" 
                                                                data-type="{{ $p->type }}">
                                                            [{{ $p->sku }}] {{ $p->name }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                                <optgroup label="📦 PRODUK JADI / SIAP JUAL (KARTU PERDANA, VOUCHER, MODEM)">
                                                    @foreach($products->filter(fn($p) => !str_starts_with($p->sku, 'RAW-') && !str_starts_with($p->sku, 'BULK-')) as $p)
                                                        <option value="{{ $p->id }}" 
                                                                data-cost="{{ $p->cost_price }}" 
                                                                data-sku="{{ $p->sku }}" 
                                                                data-tracking="{{ $p->tracking_method }}" 
                                                                data-type="{{ $p->type }}">
                                                            [{{ $p->type }}] {{ $p->name }} ({{ $p->sku }})
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            </select>
                                        </td>

                                        <td class="py-2.5 px-4">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono"
                                                  :class="row.tracking_method === 'ICCID' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'"
                                                  x-text="row.tracking_method || '-'">
                                            </span>
                                        </td>

                                        <td class="py-2.5 px-4 text-center">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <input type="number" 
                                                       :name="`items[${index}][quantity]`" 
                                                       x-model.number="row.quantity" 
                                                       @input="calculateTotal()"
                                                       min="1" 
                                                       required 
                                                       class="w-24 text-xs font-bold text-center bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                                <span class="text-[11px] text-slate-400 font-medium">pcs</span>
                                            </div>
                                        </td>

                                        <td class="py-2.5 px-4 text-center">
                                            <button type="button" @click="removeItemRow(index)" class="text-slate-400 hover:text-rose-600 transition-colors" x-show="items.length > 1">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-3 border-t border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-500 uppercase">Total Pesanan:</span>
                        <span class="text-sm font-extrabold text-slate-900 font-mono">
                            <span x-text="totalQty.toLocaleString('id-ID')"></span> pcs
                        </span>
                        <span class="text-xs text-slate-400 font-medium">(<span x-text="items.length"></span> produk)</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="createPoOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-sm transition-all active:scale-95">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                            <span>Simpan PO</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- ============================================== -->
        <!-- BAGIAN 1: HEADER TABLE (DAFTAR PO)            -->
        <!-- ============================================== -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-6">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                    <span>Daftar Purchase Order (PO)</span>
                    <span class="text-[11px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 font-bold font-mono">{{ count($orders) }}</span>
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/80 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-5">Nomor PO</th>
                            <th class="py-3 px-4">Supplier</th>
                            <th class="py-3 px-4">TAP</th>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4 text-center">Item</th>
                            <th class="py-3 px-4 text-right">Total Nilai</th>
                            <th class="py-3 px-4">Catatan (Notes)</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($orders as $idx => $po)
                            <tr @click="selectPo({{ json_encode($po) }})"
                                :class="selectedPo && selectedPo.id === {{ $po->id }} 
                                    ? 'bg-rose-50/90 border-l-4 border-rose-600 shadow-sm' 
                                    : 'hover:bg-slate-50/70 cursor-pointer'"
                                class="transition-all cursor-pointer">
                                
                                <td class="py-3.5 px-5 font-mono font-bold text-slate-900">
                                    <div class="flex items-center gap-2">
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-mono text-white"
                                              :class="selectedPo && selectedPo.id === {{ $po->id }} ? 'bg-rose-600 font-extrabold' : 'bg-slate-900'">
                                            {{ $po->doc_type }}
                                        </span>
                                        <span :class="selectedPo && selectedPo.id === {{ $po->id }} ? 'text-rose-950 font-extrabold' : ''">{{ $po->doc_number }}</span>
                                    </div>
                                </td>

                                <td class="py-3.5 px-4 font-bold text-slate-800">{{ $po->supplier_name }}</td>
                                
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold"
                                          :class="selectedPo && selectedPo.id === {{ $po->id }} ? 'bg-rose-100 text-rose-800 font-bold' : 'bg-slate-100 text-slate-700'">
                                        {{ $po->tap_name }}
                                    </span>
                                </td>

                                <td class="py-3.5 px-4 text-slate-500">{{ date('d M Y', strtotime($po->order_date)) }}</td>

                                <td class="py-3.5 px-4 text-center font-bold text-slate-800">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-mono"
                                          :class="selectedPo && selectedPo.id === {{ $po->id }} ? 'bg-rose-200/70 text-rose-900 font-bold' : 'bg-slate-100 text-slate-700'">
                                        {{ count($po->items) }} item ({{ $po->total_qty }} pcs)
                                    </span>
                                </td>

                                <td class="py-3.5 px-4 text-right font-mono font-extrabold text-slate-900">
                                    Rp {{ number_format($po->total_amount, 0, ',', '.') }}
                                </td>

                                <td class="py-3.5 px-4 text-slate-600 max-w-[180px]">
                                    @if(!empty($po->notes))
                                        <span class="text-[11px] font-medium text-slate-700 block truncate" title="{{ $po->notes }}">
                                            {{ $po->notes }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 font-mono text-xs">-</span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-center">
                                    @if($po->status === 'RECEIVED')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            RECEIVED
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                            ORDERED
                                        </span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        @if($po->status === 'ORDERED')
                                            <a href="{{ route('procurement.create-gr', $po->id) }}" 
                                               @click.stop
                                               class="px-2 py-1 rounded-lg text-xs font-bold bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 flex items-center gap-1 transition-all shadow-xs">
                                                <span>Terima GR</span>
                                                <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                            </a>
                                        @endif
                                        <button type="button" 
                                                class="px-2 py-1 rounded-lg text-xs font-bold transition-all flex items-center gap-1 shadow-sm"
                                                :class="selectedPo && selectedPo.id === {{ $po->id }} 
                                                    ? 'bg-rose-600 text-white' 
                                                    : 'bg-slate-100 hover:bg-slate-200 text-slate-600'">
                                            <span x-text="selectedPo && selectedPo.id === {{ $po->id }} ? 'Aktif' : 'Detail'"></span>
                                            <i data-lucide="chevron-down" class="w-3 h-3 transition-transform" 
                                               :class="selectedPo && selectedPo.id === {{ $po->id }} ? 'rotate-180 text-white' : 'text-slate-400'"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- BAGIAN 2: DETAIL PANEL INLINE (DI BAWAH TABEL) -->
        <!-- ============================================== -->
        <div x-show="selectedPo" x-cloak class="scroll-mt-4" id="detailSection">
            <div class="rounded-2xl border-2 border-slate-300 shadow-lg overflow-hidden bg-white">
                
                <!-- Distinct Dark Header Strip for Detail Panel -->
                <div class="bg-slate-900 text-white px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800">
                    <div class="flex items-center gap-3">
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-extrabold bg-rose-500 text-white shadow-sm">
                            RINCIAN PO
                        </span>
                        <h4 class="text-base font-extrabold text-white tracking-tight" x-text="selectedPo ? selectedPo.doc_number : ''"></h4>
                    </div>

                    <div class="flex items-center gap-3 text-xs flex-wrap">
                        <template x-if="selectedPo && selectedPo.notes">
                            <div class="flex items-center gap-1.5 bg-slate-800 px-3 py-1 rounded-lg border border-slate-700">
                                <span class="text-slate-400">Catatan:</span>
                                <span class="font-bold text-amber-300" x-text="selectedPo.notes"></span>
                            </div>
                        </template>
                        <div class="flex items-center gap-1.5 bg-slate-800 px-3 py-1 rounded-lg border border-slate-700">
                            <span class="text-slate-400">TAP:</span>
                            <span class="font-bold text-white" x-text="selectedPo ? selectedPo.tap_name : ''"></span>
                        </div>
                        <div class="flex items-center gap-1.5 bg-slate-800 px-3 py-1 rounded-lg border border-slate-700">
                            <span class="text-slate-400">Status:</span>
                            <span class="font-bold px-1.5 py-0.5 rounded text-[10px]" 
                                  :class="selectedPo && selectedPo.status === 'RECEIVED' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-400 border border-amber-500/30'"
                                  x-text="selectedPo ? selectedPo.status : ''"></span>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Detail Line Items Table -->
                    <div class="mb-5">
                        <div class="border border-slate-200 rounded-xl overflow-hidden bg-white">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider border-b border-slate-200">
                                    <tr>
                                        <th class="py-2.5 px-4">Produk</th>
                                        <th class="py-2.5 px-4">Tipe</th>
                                        <th class="py-2.5 px-4">Tracking</th>
                                        <th class="py-2.5 px-4 text-center">Qty</th>
                                        <th class="py-2.5 px-4 text-right">Harga Modal</th>
                                        <th class="py-2.5 px-4 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium">
                                    <template x-for="(item, idx) in (selectedPo ? selectedPo.items : [])" :key="idx">
                                        <tr class="hover:bg-slate-50/70">
                                            <td class="py-3 px-4 font-bold text-slate-900">
                                                <span class="font-mono text-[10px] text-slate-400 block" x-text="item.sku"></span>
                                                <span x-text="item.product_name"></span>
                                            </td>
                                            <td class="py-3 px-4">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold"
                                                      :class="item.product_type === 'SA' ? 'bg-rose-100 text-rose-700' : (item.product_type === 'VOUCHER' ? 'bg-indigo-100 text-indigo-700' : 'bg-cyan-100 text-cyan-700')"
                                                      x-text="item.product_type">
                                                </span>
                                            </td>
                                            <td class="py-3 px-4 font-mono text-[11px] font-bold"
                                                :class="item.tracking_method === 'ICCID' ? 'text-purple-700' : 'text-blue-700'"
                                                x-text="item.tracking_method">
                                            </td>
                                            <td class="py-3 px-4 text-center font-mono font-bold text-slate-800">
                                                <span x-text="item.quantity.toLocaleString('id-ID')"></span> pcs
                                            </td>
                                            <td class="py-3 px-4 text-right font-mono text-slate-600">
                                                Rp <span x-text="Number(item.cost_price).toLocaleString('id-ID')"></span>
                                            </td>
                                            <td class="py-3 px-4 text-right font-mono font-extrabold text-slate-900">
                                                Rp <span x-text="Number(item.subtotal).toLocaleString('id-ID')"></span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Action jika status ORDERED: Tombol Buka Layar GR -->
                    <template x-if="selectedPo && selectedPo.status !== 'RECEIVED'">
                        <div class="mt-4 p-4 rounded-xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-2 text-xs text-slate-700 font-semibold">
                                <i data-lucide="package-search" class="w-4 h-4 text-amber-600"></i>
                                <span>Barang fisik dari supplier sudah tiba di gudang?</span>
                            </div>
                            <a :href="'{{ url('procurement/gr') }}/' + selectedPo.id" 
                               class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs flex items-center justify-center gap-2 shadow-sm transition-all active:scale-95">
                                <i data-lucide="boxes" class="w-4 h-4"></i>
                                <span>Buka Formulir Penerimaan Fisik (GR) & Registrasi ICCID</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </template>

                    <!-- Feedback jika PO sudah RECEIVED -->
                    <template x-if="selectedPo && selectedPo.status === 'RECEIVED'">
                        <div class="mt-4 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-between text-xs text-emerald-800 font-bold">
                            <div class="flex items-center gap-2">
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                                <span>Barang Fisik Sudah Diterima Lengkap (GR Selesai)</span>
                            </div>
                            <span class="font-mono text-[11px] text-emerald-700">Tercatat di Gudang MAIN</span>
                        </div>
                    </template>

                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function procurementMasterDetail() {
            return {
                createPoOpen: false,
                selectedPo: null,
                items: [
                    { product_id: '', tracking_method: '', cost_price: 0, quantity: 100 }
                ],
                grandTotal: 0,

                get totalQty() {
                    return this.items.reduce((acc, row) => acc + (parseInt(row.quantity) || 0), 0);
                },

                initData() {
                    const firstPo = @json($orders->first());
                    if (firstPo) {
                        this.selectedPo = firstPo;
                    }
                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                },

                selectPo(po) {
                    this.selectedPo = po;
                    this.$nextTick(() => {
                        lucide.createIcons();
                        const el = document.getElementById('detailSection');
                        if (el) {
                            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    });
                },

                addItemRow() {
                    this.items.push({
                        product_id: '',
                        tracking_method: '',
                        cost_price: 0,
                        quantity: 100
                    });
                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                },

                removeItemRow(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                        this.calculateTotal();
                    }
                },

                updateProductRow(index) {
                    const select = document.querySelectorAll('select[name^="items["]')[index];
                    if (!select) return;

                    const option = select.options[select.selectedIndex];
                    if (option && option.value) {
                        this.items[index].cost_price = parseFloat(option.getAttribute('data-cost')) || 0;
                        this.items[index].tracking_method = option.getAttribute('data-tracking') || '';
                    } else {
                        this.items[index].cost_price = 0;
                        this.items[index].tracking_method = '';
                    }
                    this.calculateTotal();
                },

                calculateTotal() {
                    this.grandTotal = this.items.reduce((acc, row) => {
                        const price = parseFloat(row.cost_price) || 0;
                        const qty = parseInt(row.quantity) || 0;
                        return acc + (price * qty);
                    }, 0);
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
