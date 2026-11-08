<x-app-layout>
    <x-slot name="title">Pengadaan & Goods Received (PO - GR)</x-slot>
    <x-slot name="headerTitle">Purchase Order & Goods Received</x-slot>
    <x-slot name="badge">PO &rarr; GR</x-slot>

    <!-- Top Action Bar -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-extrabold text-slate-900 tracking-tight">Purchase Order & Penerimaan Barang</h2>
            <p class="text-xs text-slate-500">Pencatatan pengadaan ke supplier dan penerimaan fisik ke Gudang MAIN</p>
        </div>

        <button @click="createPoOpen = !createPoOpen" 
                class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-sm transition-all active:scale-95 shrink-0">
            <i data-lucide="plus-circle" class="w-4 h-4 text-emerald-400"></i>
            <span x-text="createPoOpen ? 'Tutup Form' : '+ Buat PO Baru'"></span>
        </button>
    </div>

    <div x-data="procurementMasterDetail()" x-init="initData()">
        
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
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
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
                                    <th class="py-2.5 px-4 w-5/12">Produk</th>
                                    <th class="py-2.5 px-4 w-2/12">Tracking</th>
                                    <th class="py-2.5 px-4 w-2/12 text-right">Harga Modal</th>
                                    <th class="py-2.5 px-4 w-1/12 text-center">Qty</th>
                                    <th class="py-2.5 px-4 w-2/12 text-right">Subtotal</th>
                                    <th class="py-2.5 px-4 w-10 text-center"></th>
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
                                                @foreach($products as $p)
                                                    <option value="{{ $p->id }}" 
                                                            data-cost="{{ $p->cost_price }}" 
                                                            data-sku="{{ $p->sku }}" 
                                                            data-tracking="{{ $p->tracking_method }}" 
                                                            data-type="{{ $p->type }}">
                                                        [{{ $p->type }}] {{ $p->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td class="py-2.5 px-4">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono"
                                                  :class="row.tracking_method === 'ICCID' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'"
                                                  x-text="row.tracking_method || '-'">
                                            </span>
                                        </td>

                                        <td class="py-2.5 px-4 text-right">
                                            <input type="number" 
                                                   :name="`items[${index}][cost_price]`" 
                                                   x-model.number="row.cost_price" 
                                                   @input="calculateTotal()"
                                                   class="w-28 text-xs font-mono text-right bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 focus:outline-none">
                                        </td>

                                        <td class="py-2.5 px-4 text-center">
                                            <input type="number" 
                                                   :name="`items[${index}][quantity]`" 
                                                   x-model.number="row.quantity" 
                                                   @input="calculateTotal()"
                                                   min="1" 
                                                   required 
                                                   class="w-20 text-xs font-bold text-center bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 focus:outline-none">
                                        </td>

                                        <td class="py-2.5 px-4 text-right font-mono font-bold text-slate-900">
                                            Rp <span x-text="(row.cost_price * row.quantity).toLocaleString('id-ID')"></span>
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
                        <span class="text-xs font-bold text-slate-500 uppercase">Total:</span>
                        <span class="text-base font-extrabold text-slate-900 font-mono">
                            Rp <span x-text="grandTotal.toLocaleString('id-ID')"></span>
                        </span>
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
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($orders as $idx => $po)
                            <tr @click="selectPo({{ json_encode($po) }})"
                                :class="selectedPo && selectedPo.id === {{ $po->id }} ? 'bg-rose-50/70 border-l-4 border-rose-600' : 'hover:bg-slate-50/60 cursor-pointer'"
                                class="transition-all cursor-pointer">
                                
                                <td class="py-3.5 px-5 font-mono font-bold text-slate-900">
                                    <div class="flex items-center gap-2">
                                        <span class="px-1.5 py-0.5 rounded text-[10px] bg-slate-900 text-white font-mono">
                                            {{ $po->doc_type }}
                                        </span>
                                        <span>{{ $po->doc_number }}</span>
                                    </div>
                                </td>

                                <td class="py-3.5 px-4 font-bold text-slate-800">{{ $po->supplier_name }}</td>
                                
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold text-[11px]">
                                        {{ $po->tap_name }}
                                    </span>
                                </td>

                                <td class="py-3.5 px-4 text-slate-500">{{ date('d M Y', strtotime($po->order_date)) }}</td>

                                <td class="py-3.5 px-4 text-center font-bold text-slate-800">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-mono text-[11px]">
                                        {{ count($po->items) }} item ({{ $po->total_qty }} pcs)
                                    </span>
                                </td>

                                <td class="py-3.5 px-4 text-right font-mono font-extrabold text-slate-900">
                                    Rp {{ number_format($po->total_amount, 0, ',', '.') }}
                                </td>

                                <td class="py-3.5 px-4">
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
            <div class="bg-white rounded-2xl border-2 border-slate-200/80 shadow-sm p-6">
                
                <!-- Detail Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-4 border-b border-slate-100 gap-3">
                    <div class="flex items-center gap-3">
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-900 text-white">
                            DETAIL
                        </span>
                        <h4 class="text-base font-extrabold text-slate-900" x-text="selectedPo ? selectedPo.doc_number : ''"></h4>
                    </div>

                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-slate-400">TAP:</span>
                        <span class="font-bold text-slate-800" x-text="selectedPo ? selectedPo.tap_name : ''"></span>
                        <span class="text-slate-300">&bull;</span>
                        <span class="text-slate-400">Status:</span>
                        <span class="font-bold px-2 py-0.5 rounded text-[10px]" 
                              :class="selectedPo && selectedPo.status === 'RECEIVED' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
                              x-text="selectedPo ? selectedPo.status : ''"></span>
                    </div>
                </div>

                <!-- Detail Line Items Table -->
                <div class="mb-5">
                    <div class="border border-slate-200 rounded-xl overflow-hidden bg-white">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
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
                                    <tr>
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

                <!-- ============================================== -->
                <!-- PROSES FISIK GR (GOODS RECEIVED)              -->
                <!-- ============================================== -->
                <template x-if="selectedPo && selectedPo.status !== 'RECEIVED'">
                    <div class="mt-4 bg-slate-50 rounded-2xl border border-slate-200 p-5">
                        <div class="flex items-center justify-between mb-3">
                            <h5 class="font-bold text-slate-900 text-xs uppercase tracking-wider flex items-center gap-1.5">
                                <i data-lucide="boxes" class="w-4 h-4 text-emerald-600"></i>
                                <span>Penerimaan Fisik Barang (GR)</span>
                            </h5>
                            <span class="text-[11px] text-slate-500 font-semibold">Tujuan: Gudang MAIN</span>
                        </div>

                        <form :action="`{{ url('procurement/process-gr') }}/${selectedPo.id}`" method="POST"
                              data-loading-title="Penerimaan Fisik (GR)"
                              data-loading-sub="Menerbitkan dokumen GR & mencatat stok ke Gudang MAIN..."
                              data-doctype="GR">
                            @csrf

                            <!-- Section Input Serial ICCID jika ada produk SA -->
                            <template x-if="selectedPo.has_iccid">
                                <div class="mb-4 space-y-3">
                                    <template x-for="item in selectedPo.items.filter(i => i.tracking_method === 'ICCID')" :key="item.id">
                                        <div class="p-3 bg-white rounded-xl border border-slate-200">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-xs font-bold text-slate-800" x-text="item.product_name"></span>
                                                <span class="text-xs font-mono font-bold text-purple-700" x-text="`${item.quantity} Unit ICCID`"></span>
                                            </div>
                                            
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-[11px] font-semibold text-slate-500 mb-1">Nomor Awal ICCID (Rentang Otomatis):</label>
                                                    <input type="text" 
                                                           :name="`iccids_${item.product_id}_range_start`" 
                                                           :value="`89621000${String(new Date().getFullYear()).slice(-2)}000001`" 
                                                           class="w-full text-xs font-mono font-bold bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-purple-900 focus:outline-none">
                                                </div>

                                                <div>
                                                    <label class="block text-[11px] font-semibold text-slate-500 mb-1">Paste Serial (Opsional):</label>
                                                    <textarea :name="`iccids_${item.product_id}_paste`" 
                                                              rows="1" 
                                                              placeholder="Paste serial jika ada..."
                                                              class="w-full text-xs font-mono bg-slate-50 border border-slate-200 rounded-lg p-1.5 focus:outline-none"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <div class="flex justify-end pt-2">
                                <button type="submit" 
                                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-sm transition-all active:scale-95">
                                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                                    <span>Terima Fisik & Terbitkan GR</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </template>

                <!-- Feedback jika PO sudah RECEIVED -->
                <template x-if="selectedPo && selectedPo.status === 'RECEIVED'">
                    <div class="mt-4 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-between text-xs text-emerald-800 font-bold">
                        <div class="flex items-center gap-2">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                            <span>Barang Fisik Sudah Diterima (GR Selesai)</span>
                        </div>
                        <span class="font-mono text-[11px] text-emerald-700">Tercatat di Gudang MAIN</span>
                    </div>
                </template>

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
