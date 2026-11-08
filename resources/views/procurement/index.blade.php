<x-app-layout>
    <x-slot name="title">Pengadaan & Goods Received (PO - GR)</x-slot>
    <x-slot name="headerTitle">Pengadaan & Penerimaan Barang</x-slot>
    <x-slot name="badge">DocType: PO &rarr; GR</x-slot>

    <!-- Top Workflow Banner -->
    <div class="mb-8 p-6 bg-white rounded-3xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase">
                    Tahap 1: Pengadaan & Persediaan
                </span>
                <span class="text-xs text-slate-400">&bull;</span>
                <span class="text-xs font-semibold text-slate-500">Alur: PO &rarr; GR Fisik &rarr; Upload ICCID &rarr; Stok MAIN</span>
            </div>
            <h2 class="text-lg font-extrabold text-slate-900 mt-1">Purchase Order (PO) & Goods Received (GR)</h2>
            <p class="text-xs text-slate-500 mt-0.5">PO belum menambah stok. Stok resmi bertambah di Gudang MAIN setelah Admin Gudang memvalidasi dan menerima fisik barang (GR).</p>
        </div>

        <button @click="createPoOpen = !createPoOpen" 
                class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-2xl text-xs flex items-center gap-2 shadow-sm transition-all active:scale-95 shrink-0">
            <i data-lucide="plus-circle" class="w-4 h-4 text-emerald-400"></i>
            <span x-text="createPoOpen ? 'Tutup Form PO' : '+ Buat Purchase Order (PO) Baru'"></span>
        </button>
    </div>

    <div x-data="procurementMasterDetail()" x-init="initData()">
        
        <!-- ============================================== -->
        <!-- FORM PEMBUATAN PO MULTI-ITEM (INLINE CARD)    -->
        <!-- ============================================== -->
        <div x-show="createPoOpen" x-cloak x-transition class="mb-8 bg-white rounded-3xl border-2 border-emerald-500/30 p-6 shadow-xl relative overflow-hidden">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                        <i data-lucide="file-plus" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Formulir Penerbitan Purchase Order (PO) Baru</h3>
                        <p class="text-xs text-slate-500">Dapat memilih banyak item sekaligus dengan harga modal otomatis terhitung</p>
                    </div>
                </div>
                <button @click="createPoOpen = false" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('procurement.store-po') }}" 
                  data-loading-title="Menerbitkan Purchase Order (PO)"
                  data-loading-sub="Menghubungkan ke supplier Telkomsel & membuat nomor dokumen PO baru..."
                  data-doctype="PO">
                @csrf

                <!-- Header Info (TAP, Supplier, Date) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">TAP Pemesan <span class="text-rose-500">*</span></label>
                        <select name="tap_id" required class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                            @foreach($taps as $tap)
                                <option value="{{ $tap->id }}">{{ $tap->name }} ({{ $tap->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Supplier Tujuan</label>
                        <input type="text" name="supplier_name" value="PT TELKOMSEL DISTRIBUSI PUSAT" required class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Order</label>
                        <input type="date" name="order_date" value="{{ date('Y-m-d') }}" required class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    </div>
                </div>

                <!-- Multi-Item Rows Table -->
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold text-slate-800">Daftar Item Barang Dipesan</label>
                        <button type="button" @click="addItemRow()" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Baris Barang
                        </button>
                    </div>

                    <div class="border border-slate-200 rounded-2xl overflow-hidden bg-slate-50/50">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-100/70 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200/80">
                                <tr>
                                    <th class="py-2.5 px-4 w-5/12">Pilih Produk</th>
                                    <th class="py-2.5 px-4 w-2/12">Tracking</th>
                                    <th class="py-2.5 px-4 w-2/12 text-right">Harga Modal</th>
                                    <th class="py-2.5 px-4 w-1/12 text-center">Qty</th>
                                    <th class="py-2.5 px-4 w-2/12 text-right">Subtotal</th>
                                    <th class="py-2.5 px-4 w-12 text-center"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200/70 bg-white">
                                <template x-for="(row, index) in items" :key="index">
                                    <tr>
                                        <!-- Product Selector -->
                                        <td class="py-3 px-4">
                                            <select :name="`items[${index}][product_id]`" 
                                                    x-model="row.product_id" 
                                                    @change="updateProductRow(index)"
                                                    required 
                                                    class="w-full text-xs font-bold text-slate-800 bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 focus:outline-none">
                                                <option value="">-- Pilih Produk Telkomsel --</option>
                                                @foreach($products as $p)
                                                    <option value="{{ $p->id }}" 
                                                            data-cost="{{ $p->cost_price }}" 
                                                            data-sku="{{ $p->sku }}" 
                                                            data-tracking="{{ $p->tracking_method }}" 
                                                            data-type="{{ $p->type }}">
                                                        [{{ $p->type }}] {{ $p->name }} (Rp {{ number_format($p->cost_price, 0, ',', '.') }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <!-- Tracking Method Badge -->
                                        <td class="py-3 px-4">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono"
                                                  :class="row.tracking_method === 'ICCID' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'"
                                                  x-text="row.tracking_method || '-'">
                                            </span>
                                        </td>

                                        <!-- Cost Price (Readonly/Customizable) -->
                                        <td class="py-3 px-4 text-right">
                                            <input type="number" 
                                                   :name="`items[${index}][cost_price]`" 
                                                   x-model.number="row.cost_price" 
                                                   @input="calculateTotal()"
                                                   class="w-28 text-xs font-mono text-right bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 focus:outline-none">
                                        </td>

                                        <!-- Quantity -->
                                        <td class="py-3 px-4 text-center">
                                            <input type="number" 
                                                   :name="`items[${index}][quantity]`" 
                                                   x-model.number="row.quantity" 
                                                   @input="calculateTotal()"
                                                   min="1" 
                                                   required 
                                                   class="w-20 text-xs font-bold text-center bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 focus:outline-none">
                                        </td>

                                        <!-- Subtotal Live -->
                                        <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">
                                            Rp <span x-text="(row.cost_price * row.quantity).toLocaleString('id-ID')"></span>
                                        </td>

                                        <!-- Delete Row -->
                                        <td class="py-3 px-4 text-center">
                                            <button type="button" @click="removeItemRow(index)" class="text-slate-400 hover:text-rose-600 transition-colors p-1" x-show="items.length > 1">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer Summary & Submit -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-4 border-t border-slate-100">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-bold text-slate-500 uppercase">Grand Total Nilai PO:</span>
                        <span class="text-lg font-extrabold text-emerald-700 font-mono">
                            Rp <span x-text="grandTotal.toLocaleString('id-ID')"></span>
                        </span>
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="button" @click="createPoOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-sm transition-all active:scale-95">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Terbitkan Dokumen PO</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- ============================================== -->
        <!-- BAGIAN 1: HEADER TABLE (DAFTAR PO - BISA KLIK) -->
        <!-- ============================================== -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden mb-8">
            <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="font-extrabold text-slate-900 text-base flex items-center gap-2">
                        <span>Daftar Dokumen Purchase Order (PO)</span>
                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 font-bold font-mono">{{ count($orders) }} Total</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        <span class="text-rose-600 font-bold">&larr; Klik pada salah satu baris</span> untuk melihat rincian detail dan proses penerimaan barang fisik (GR) di bawah.
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/80 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3.5 px-6">DocType & Nomor PO</th>
                            <th class="py-3.5 px-4">Supplier</th>
                            <th class="py-3.5 px-4">TAP Pemesan</th>
                            <th class="py-3.5 px-4">Tgl Order</th>
                            <th class="py-3.5 px-4 text-center">Banyak Item</th>
                            <th class="py-3.5 px-4 text-right">Total Nilai</th>
                            <th class="py-3.5 px-4">Status Dokumen</th>
                            <th class="py-3.5 px-6 text-center">Aksi Cepat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($orders as $idx => $po)
                            <tr @click="selectPo({{ json_encode($po) }})"
                                :class="selectedPo && selectedPo.id === {{ $po->id }} ? 'bg-rose-50/80 border-l-4 border-rose-600 shadow-inner' : 'hover:bg-slate-50/60 cursor-pointer'"
                                class="transition-all cursor-pointer">
                                
                                <td class="py-4 px-6 font-mono font-bold text-slate-900">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded text-[10px] bg-slate-900 text-white font-mono">
                                            {{ $po->doc_type }}
                                        </span>
                                        <span class="text-sm tracking-tight">{{ $po->doc_number }}</span>
                                    </div>
                                    <span class="text-[10px] text-slate-400 font-sans block mt-0.5">{{ $po->notes }}</span>
                                </td>

                                <td class="py-4 px-4 font-bold text-slate-800">{{ $po->supplier_name }}</td>
                                
                                <td class="py-4 px-4">
                                    <span class="px-2 py-0.5 rounded bg-rose-50 text-rose-700 font-semibold border border-rose-200 text-[11px]">
                                        {{ $po->tap_name }}
                                    </span>
                                </td>

                                <td class="py-4 px-4 text-slate-500">{{ date('d M Y', strtotime($po->order_date)) }}</td>

                                <td class="py-4 px-4 text-center font-bold text-slate-800">
                                    <span class="px-2 py-1 rounded-lg bg-slate-100 text-slate-700 font-mono text-xs">
                                        {{ count($po->items) }} Item ({{ $po->total_qty }} pcs)
                                    </span>
                                </td>

                                <td class="py-4 px-4 text-right font-mono font-extrabold text-slate-900 text-sm">
                                    Rp {{ number_format($po->total_amount, 0, ',', '.') }}
                                </td>

                                <td class="py-4 px-4">
                                    @if($po->status === 'RECEIVED')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            <i data-lucide="check-check" class="w-3 h-3 text-emerald-600"></i> RECEIVED (Gudang MAIN)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 animate-pulse">
                                            <i data-lucide="clock" class="w-3 h-3 text-amber-600"></i> ORDERED (Menunggu Fisik)
                                        </span>
                                    @endif
                                </td>

                                <td class="py-4 px-6 text-center">
                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-rose-600 group-hover:underline">
                                        <span x-text="selectedPo && selectedPo.id === {{ $po->id }} ? 'Aktif Terbuka' : 'Lihat Detail'"></span>
                                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform" :class="selectedPo && selectedPo.id === {{ $po->id }} ? 'rotate-180 text-rose-600' : 'text-slate-400'"></i>
                                    </span>
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
        <div x-show="selectedPo" x-cloak class="scroll-mt-6" id="detailSection">
            <div class="bg-gradient-to-b from-white to-slate-50/70 rounded-3xl border-2 border-slate-200 shadow-md p-7">
                
                <!-- Detail Header -->
                <div class="flex flex-col lg:flex-row lg:items-center justify-between pb-6 mb-6 border-b border-slate-200/80 gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-mono font-extrabold bg-slate-900 text-white">
                                DETAIL PO AKTIF
                            </span>
                            <span class="font-mono text-sm font-extrabold text-slate-800" x-text="selectedPo ? selectedPo.doc_number : ''"></span>
                        </div>
                        <h4 class="text-lg font-extrabold text-slate-900 mt-1">Rincian Pembelian & Verifikasi Penerimaan Barang Fisik</h4>
                        <p class="text-xs text-slate-500">Periksa kuantitas fisik sebelum melakukan konfirmasi Goods Received (GR) ke Gudang MAIN.</p>
                    </div>

                    <!-- Meta Badges -->
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <div class="bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-sm">
                            <span class="text-slate-400 block text-[10px] font-bold">TAP Pemesan:</span>
                            <span class="font-bold text-slate-800" x-text="selectedPo ? selectedPo.tap_name : ''"></span>
                        </div>
                        <div class="bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-sm">
                            <span class="text-slate-400 block text-[10px] font-bold">Supplier:</span>
                            <span class="font-bold text-slate-800" x-text="selectedPo ? selectedPo.supplier_name : ''"></span>
                        </div>
                        <div class="bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-sm">
                            <span class="text-slate-400 block text-[10px] font-bold">Status:</span>
                            <span class="font-bold" :class="selectedPo && selectedPo.status === 'RECEIVED' ? 'text-emerald-600' : 'text-amber-600'" x-text="selectedPo ? selectedPo.status : ''"></span>
                        </div>
                    </div>
                </div>

                <!-- Detail Line Items Table -->
                <div class="mb-6">
                    <h5 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <i data-lucide="package" class="w-4 h-4 text-slate-400"></i>
                        <span>Item Produk dalam PO Ini:</span>
                    </h5>

                    <div class="border border-slate-200/80 rounded-2xl overflow-hidden bg-white shadow-sm">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                                <tr>
                                    <th class="py-3 px-4">SKU & Nama Produk</th>
                                    <th class="py-3 px-4">Tipe Produk</th>
                                    <th class="py-3 px-4">Metode Tracking</th>
                                    <th class="py-3 px-4 text-center">Qty Pesan</th>
                                    <th class="py-3 px-4 text-right">Harga Modal</th>
                                    <th class="py-3 px-4 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                <template x-for="(item, idx) in (selectedPo ? selectedPo.items : [])" :key="idx">
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="py-3.5 px-4 font-bold text-slate-900">
                                            <span class="font-mono text-[11px] text-slate-400 block" x-text="item.sku"></span>
                                            <span class="text-sm text-slate-900" x-text="item.product_name"></span>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold"
                                                  :class="item.product_type === 'SA' ? 'bg-rose-100 text-rose-700' : (item.product_type === 'VOUCHER' ? 'bg-indigo-100 text-indigo-700' : 'bg-cyan-100 text-cyan-700')"
                                                  x-text="item.product_type">
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <template x-if="item.tracking_method === 'ICCID'">
                                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded border border-purple-200">
                                                    <i data-lucide="scan-barcode" class="w-3 h-3 text-purple-600"></i> Wajib Serial ICCID
                                                </span>
                                            </template>
                                            <template x-if="item.tracking_method !== 'ICCID'">
                                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200">
                                                    <i data-lucide="layers" class="w-3 h-3 text-blue-600"></i> Quantity Balances
                                                </span>
                                            </template>
                                        </td>
                                        <td class="py-3.5 px-4 text-center font-mono font-extrabold text-sm text-slate-800">
                                            <span x-text="item.quantity.toLocaleString('id-ID')"></span> pcs
                                        </td>
                                        <td class="py-3.5 px-4 text-right font-mono text-slate-600">
                                            Rp <span x-text="Number(item.cost_price).toLocaleString('id-ID')"></span>
                                        </td>
                                        <td class="py-3.5 px-4 text-right font-mono font-extrabold text-slate-900">
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
                    <div class="mt-8 bg-emerald-50/60 rounded-3xl border-2 border-emerald-500/30 p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-bold shadow-md shadow-emerald-600/20">
                                <i data-lucide="boxes" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h4 class="font-extrabold text-slate-900 text-base">Proses Penerimaan Fisik Barang (Goods Received / GR)</h4>
                                <p class="text-xs text-slate-600">Admin Gudang telah menerima fisik kardus/paket barang. Klik untuk memasukkan stok ke Gudang MAIN.</p>
                            </div>
                        </div>

                        <form :action="`{{ url('procurement/process-gr') }}/${selectedPo.id}`" method="POST"
                              data-loading-title="Penerimaan Barang Fisik (GR)"
                              data-loading-sub="Menerbitkan dokumen GR, mendaftarkan serial ICCID & menambah stok Gudang MAIN..."
                              data-doctype="GR">
                            @csrf

                            <!-- Section: Input Serial ICCID jika ada produk SA -->
                            <template x-if="selectedPo.has_iccid">
                                <div class="mb-5 bg-white p-5 rounded-2xl border border-emerald-200">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="scan-barcode" class="w-4 h-4 text-purple-600"></i>
                                            <span class="text-xs font-bold text-slate-800">Registrasi Nomor Serial ICCID Kartu Perdana (SA)</span>
                                        </div>
                                        <span class="text-[11px] font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded border border-purple-200 font-mono">
                                            ICCID Tracker
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500 mb-3">
                                        Pilih salah satu metode registrasi ICCID: Gunakan <strong>Generator Rentang Otomatis</strong> (input nomor awal berurutan) atau <strong>Copy-Paste Baris</strong> dari file scanner/Excel.
                                    </p>

                                    <!-- Range Generator Tool -->
                                    <template x-for="item in selectedPo.items.filter(i => i.tracking_method === 'ICCID')" :key="item.id">
                                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 mb-3">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-xs font-bold text-slate-800" x-text="item.product_name"></span>
                                                <span class="text-xs font-mono font-bold text-rose-600" x-text="`Perlu: ${item.quantity} Unit ICCID`"></span>
                                            </div>
                                            
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-[11px] font-semibold text-slate-500 mb-1">Metode 1: Rentang Serial (Input Nomor Awal 16-20 Digit)</label>
                                                    <input type="text" 
                                                           :name="`iccids_${item.product_id}_range_start`" 
                                                           :value="`89621000${String(new Date().getFullYear()).slice(-2)}000001`" 
                                                           placeholder="Contoh: 8962100000000101"
                                                           class="w-full text-xs font-mono font-bold bg-white border border-slate-200 rounded-lg px-3 py-2 text-purple-900 focus:outline-none">
                                                    <span class="text-[10px] text-slate-400 mt-1 block">Sistem otomatis generate urut sebanyak <span x-text="item.quantity"></span> unit dari nomor awal ini.</span>
                                                </div>

                                                <div>
                                                    <label class="block text-[11px] font-semibold text-slate-500 mb-1">Metode 2: Paste List dari Excel (Opsional / Manual)</label>
                                                    <textarea :name="`iccids_${item.product_id}_paste`" 
                                                              rows="2" 
                                                              placeholder="Paste serial ICCID (1 baris 1 serial)..."
                                                              class="w-full text-xs font-mono bg-white border border-slate-200 rounded-lg p-2 focus:outline-none"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-2">
                                <div class="text-xs text-slate-600 flex items-center gap-2">
                                    <i data-lucide="warehouse" class="w-4 h-4 text-emerald-600"></i>
                                    <span>Tujuan Alokasi Fisik: <strong>Gudang Utama (MAIN) TAP</strong></span>
                                </div>

                                <button type="submit" 
                                        class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold rounded-2xl text-xs flex items-center justify-center gap-2 shadow-lg shadow-emerald-600/30 transition-all active:scale-95">
                                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                                    <span>Konfirmasi Penerimaan Fisik & Terbitkan GR</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </template>

                <!-- Feedback jika PO sudah RECEIVED -->
                <template x-if="selectedPo && selectedPo.status === 'RECEIVED'">
                    <div class="mt-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center gap-3 text-xs text-emerald-800 font-semibold">
                        <i data-lucide="badge-check" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                        <div>
                            <span class="font-bold">Barang Fisik Sudah Diterima Lengkap (RECEIVED).</span>
                            <p class="text-[11px] text-emerald-700 font-normal mt-0.5">
                                Seluruh kuantitas dan serial ICCID pada PO ini telah sukses dicatat di Gudang Utama (MAIN) TAP dan siap didistribusikan ke Gudang Inventory / Kanvas Sales.
                            </p>
                        </div>
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
                    // Otomatis pilih PO pertama jika ada data
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
                        // Smooth scroll ke detail section
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
