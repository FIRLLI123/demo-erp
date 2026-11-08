<x-app-layout>
    <x-slot name="title">Sales Order & Goods Delivery (SO - GD - AR)</x-slot>
    <x-slot name="headerTitle">Sales Order & Pengiriman (SO &rarr; GD &rarr; AR)</x-slot>
    <x-slot name="badge">SO &bull; GD &bull; AR &bull; SR</x-slot>

    <div x-data="salesApp()" x-init="initData()">
        
        <!-- Top Action Bar -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight">Pesanan Penjualan & Pengiriman Barang</h2>
                <p class="text-xs text-slate-500">Alur: Pembuatan SO &rarr; Approval Finance &rarr; Sending Good (GD & AR) &rarr; Sales Retur</p>
            </div>

            <button type="button" 
                    @click="createSoOpen = !createSoOpen" 
                    class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-sm transition-all active:scale-95 shrink-0">
                <i data-lucide="plus-circle" class="w-4 h-4 text-amber-400"></i>
                <span x-text="createSoOpen ? 'Tutup Form' : '+ Buat SO Baru'"></span>
            </button>
        </div>

        <!-- ============================================== -->
        <!-- FORM PEMBUATAN SO BARU (INLINE CARD)           -->
        <!-- ============================================== -->
        <div x-show="createSoOpen" x-cloak x-transition class="mb-6 bg-white rounded-2xl border-2 border-amber-500/40 p-5 shadow-lg">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <i data-lucide="shopping-bag" class="w-4 h-4 text-amber-600"></i>
                    <span>Formulir Sales Order (SO) Baru</span>
                </h3>
                <button @click="createSoOpen = false" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('sales.store') }}" 
                  enctype="multipart/form-data"
                  data-loading-title="Menerbitkan Sales Order"
                  data-loading-sub="Menyimpan pesanan dan mereserve serial ICCID..."
                  data-doctype="SO">
                @csrf

                <!-- Header Info -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Outlet / Customer</label>
                        <select name="customer_id" required class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                            <option value="">-- Pilih Customer --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">
                                    [{{ $c->code }}] {{ $c->name }} ({{ $c->type }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Sales Kanvaser / PIC</label>
                        <select name="sales_id" required class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                            <option value="">-- Pilih Sales --</option>
                            @foreach($salesList as $s)
                                <option value="{{ $s->id }}">
                                    [{{ $s->code }}] {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Gudang Sumber Stok</label>
                        <select name="warehouse_id" 
                                x-model="selectedWarehouseId" 
                                @change="onWarehouseChange()"
                                required 
                                class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                            <option value="">-- Pilih Gudang Sumber --</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}">
                                    [{{ $wh->type }}] {{ $wh->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Pesanan</label>
                        <input type="date" name="order_date" value="{{ date('Y-m-d') }}" required class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none">
                    </div>
                </div>

                <!-- Multi-Item Rows Table -->
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold text-slate-800">Daftar Barang yang Dipesan</label>
                        <button type="button" @click="addItemRow()" class="text-xs font-bold text-amber-700 hover:text-amber-800 flex items-center gap-1">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Baris
                        </button>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(row, index) in items" :key="index">
                            <div class="border border-slate-200 rounded-xl p-3 bg-slate-50/60">
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start">
                                    
                                    <!-- Produk -->
                                    <div class="md:col-span-4">
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Produk</label>
                                        <select :name="`items[${index}][product_id]`" 
                                                x-model="row.product_id" 
                                                @change="updateProductRow(index)"
                                                required 
                                                class="w-full text-xs font-bold text-slate-800 bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 focus:outline-none">
                                            <option value="">-- Pilih Produk --</option>
                                            <template x-for="p in getAvailableProductsInWarehouse()" :key="p.id">
                                                <option :value="p.id" 
                                                        :data-price="p.selling_price" 
                                                        :data-tracking="p.tracking_method" 
                                                        :data-type="p.type"
                                                        x-text="`[${p.type}] ${p.name} (Stok: ${getAvailableStock(p.id)} pcs)`">
                                                </option>
                                            </template>
                                        </select>
                                    </div>

                                    <!-- Tracking Info -->
                                    <div class="md:col-span-2">
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Tracking</label>
                                        <span class="inline-block px-2 py-1 rounded text-[11px] font-bold font-mono"
                                              :class="row.tracking_method === 'ICCID' ? 'bg-purple-100 text-purple-700 border border-purple-200' : 'bg-blue-100 text-blue-700 border border-blue-200'"
                                              x-text="row.tracking_method || '-'">
                                        </span>
                                    </div>

                                    <!-- Harga Jual Master (Read-only) -->
                                    <div class="md:col-span-2">
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Harga Jual</label>
                                        <div class="text-xs font-mono font-bold text-slate-800 py-1.5">
                                            Rp <span x-text="(row.unit_price || 0).toLocaleString('id-ID')"></span>
                                        </div>
                                    </div>

                                    <!-- Quantity Input / Display -->
                                    <div class="md:col-span-2">
                                        <template x-if="row.tracking_method === 'QUANTITY'">
                                            <div>
                                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Kuantitas</label>
                                                <input type="number" 
                                                       :name="`items[${index}][quantity]`" 
                                                       x-model.number="row.quantity" 
                                                       min="1" 
                                                       :max="getAvailableStock(row.product_id)" 
                                                       required 
                                                       class="w-full text-xs font-bold text-center bg-white border border-slate-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-amber-500">
                                            </div>
                                        </template>

                                        <template x-if="row.tracking_method === 'ICCID'">
                                            <div>
                                                <label class="block text-[11px] font-bold text-purple-700 mb-1">Total Unit (Otomatis)</label>
                                                <div class="py-1.5 px-2 bg-purple-50 text-purple-700 font-mono font-bold text-xs text-center rounded-lg border border-purple-200">
                                                    <span x-text="getIccidCount(row)"></span> unit
                                                </div>
                                                <input type="hidden" :name="`items[${index}][quantity]`" :value="getIccidCount(row)">
                                            </div>
                                        </template>

                                        <template x-if="!row.tracking_method">
                                            <div>
                                                <label class="block text-[11px] font-bold text-slate-400 mb-1">Qty</label>
                                                <div class="py-1.5 px-2 bg-slate-100 text-slate-400 font-mono text-xs text-center rounded-lg">-</div>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Subtotal -->
                                    <div class="md:col-span-1">
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Subtotal</label>
                                        <div class="text-xs font-mono font-extrabold text-slate-900 py-1.5 truncate">
                                            Rp <span x-text="getRowSubtotal(row).toLocaleString('id-ID')"></span>
                                        </div>
                                    </div>

                                    <!-- Delete Button -->
                                    <div class="md:col-span-1 pt-6 text-center">
                                        <button type="button" @click="removeItemRow(index)" class="text-slate-400 hover:text-rose-600 transition-colors p-1" x-show="items.length > 1">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- SUB-SECTION: ICCID SELECTION IF PRODUCT IS ICCID -->
                                <template x-if="row.tracking_method === 'ICCID' && row.product_id && selectedWarehouseId">
                                    <div class="mt-3 pt-3 border-t border-slate-200/80 bg-white rounded-lg p-3">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center gap-2">
                                                <span class="text-[11px] font-bold text-slate-700">Metode Serial ICCID:</span>
                                                <div class="inline-flex rounded-lg border border-slate-200 p-0.5 bg-slate-100 text-[10px] font-bold">
                                                    <button type="button" @click="row.iccid_mode = 'SELECT'" :class="row.iccid_mode === 'SELECT' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500'" class="px-2 py-0.5 rounded-md">
                                                        Pilih Stok (<span x-text="getIccidsForProduct(row.product_id).length"></span>)
                                                    </button>
                                                    <button type="button" @click="row.iccid_mode = 'PASTE'" :class="row.iccid_mode === 'PASTE' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500'" class="px-2 py-0.5 rounded-md">
                                                        Paste Serial
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <input type="hidden" :name="`items[${index}][iccid_mode]`" :value="row.iccid_mode">

                                        <!-- Mode Select -->
                                        <div x-show="row.iccid_mode === 'SELECT'">
                                            <div class="flex items-center justify-between mb-1.5 text-[11px]">
                                                <span class="text-slate-500">Centang nomor serial yang dipesan:</span>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" @click="selectAllIccids(index)" class="text-amber-600 font-bold hover:underline">Pilih Semua</button>
                                                    <span>&bull;</span>
                                                    <button type="button" @click="row.selected_iccids = []" class="text-slate-400 font-bold hover:underline">Reset</button>
                                                </div>
                                            </div>

                                            <div class="max-h-32 overflow-y-auto border border-slate-200 rounded-lg p-2 grid grid-cols-2 sm:grid-cols-4 gap-1.5 bg-slate-50/50">
                                                <template x-for="iccid in getIccidsForProduct(row.product_id)" :key="iccid.id">
                                                    <label class="flex items-center gap-1.5 text-[10px] font-mono bg-white p-1 rounded border border-slate-200 cursor-pointer hover:bg-amber-50/50">
                                                        <input type="checkbox" 
                                                               :name="`items[${index}][selected_iccids][]`" 
                                                               :value="iccid.iccid_number"
                                                               x-model="row.selected_iccids"
                                                               class="rounded text-amber-600 focus:ring-amber-500 w-3.5 h-3.5">
                                                        <span x-text="iccid.iccid_number" class="truncate"></span>
                                                    </label>
                                                </template>
                                                <template x-if="getIccidsForProduct(row.product_id).length === 0">
                                                    <div class="col-span-full py-2 text-center text-slate-400 text-xs">
                                                        Tidak ada serial AVAILABLE di gudang ini.
                                                    </div>
                                                </template>
                                            </div>
                                            <div class="mt-1 text-[11px] text-slate-500">
                                                Terpilih: <span class="font-bold font-mono text-purple-700" x-text="row.selected_iccids.length"></span> unit.
                                            </div>
                                        </div>

                                        <!-- Mode Paste -->
                                        <div x-show="row.iccid_mode === 'PASTE'">
                                            <textarea :name="`items[${index}][iccid_text]`" 
                                                      x-model="row.iccid_text" 
                                                      rows="2" 
                                                      placeholder="Paste serial ICCID disini (1 per baris)..." 
                                                      class="w-full text-xs font-mono bg-white border border-slate-200 rounded-lg p-2 focus:outline-none"></textarea>
                                        </div>
                                    </div>
                                </template>

                            </div>
                        </template>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-3 border-t border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-500 uppercase">Total Nilai Tagihan:</span>
                        <span class="text-base font-extrabold text-slate-900 font-mono">
                            Rp <span x-text="grandTotal.toLocaleString('id-ID')"></span>
                        </span>
                        <span class="text-xs text-slate-400 font-medium">(<span x-text="totalQty"></span> pcs)</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="createSoOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-sm transition-all active:scale-95">
                            <i data-lucide="check" class="w-4 h-4 text-amber-400"></i>
                            <span>Simpan Sales Order (SO)</span>
                        </button>
                    </div>
                </div>

            </form>
        </div>

        <!-- ============================================== -->
        <!-- BAGIAN 1: HEADER TABLE (DAFTAR DOKUMEN SO)     -->
        <!-- ============================================== -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-6">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                    <span>Daftar Sales Order (SO)</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-slate-100 text-slate-700">
                        {{ count($orders) }} Pesanan
                    </span>
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">No. Dokumen SO</th>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Outlet / Customer</th>
                            <th class="py-3 px-4">Sales & Gudang</th>
                            <th class="py-3 px-4 text-center">Total Qty</th>
                            <th class="py-3 px-4 text-right">Nilai Tagihan</th>
                            <th class="py-3 px-4 text-center">Status Transaksi</th>
                            <th class="py-3 px-4 text-center w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($orders as $so)
                            <tr @click="selectSo({{ json_encode($so) }})"
                                class="cursor-pointer transition-colors"
                                :class="selectedSo && selectedSo.id === {{ $so->id }} ? 'bg-amber-50/80 border-l-4 border-amber-500' : 'hover:bg-slate-50/70'">
                                
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                    <div class="flex items-center gap-1.5">
                                        <span class="px-1.5 py-0.5 rounded text-[10px] bg-amber-600 text-white font-mono font-bold">SO</span>
                                        <span>{{ $so->doc_number }}</span>
                                    </div>
                                    <span class="text-[10px] text-slate-400 block font-normal">{{ $so->channel }}</span>
                                </td>

                                <td class="py-3.5 px-4 text-slate-600">
                                    {{ date('d M Y', strtotime($so->order_date)) }}
                                </td>

                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-slate-900 block">{{ $so->customer_name }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $so->customer_code }} &bull; {{ $so->customer_type }}</span>
                                </td>

                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-slate-800 block">{{ $so->sales_name }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $so->warehouse_name }}</span>
                                </td>

                                <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-800">
                                    {{ number_format($so->total_qty) }} pcs
                                </td>

                                <td class="py-3.5 px-4 text-right font-mono font-extrabold text-slate-900">
                                    Rp {{ number_format($so->grand_total, 0, ',', '.') }}
                                </td>

                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold
                                        {{ $so->status === 'DELIVERED' ? 'bg-emerald-100 text-emerald-800' : ($so->status === 'APPROVED' ? 'bg-blue-100 text-blue-800' : ($so->status === 'PENDING_APPROVAL' ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-rose-100 text-rose-800')) }}">
                                        {{ $so->status }}
                                    </span>
                                </td>

                                <td class="py-3.5 px-4 text-center">
                                    <button type="button" 
                                            class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all flex items-center gap-1 mx-auto shadow-xs"
                                            :class="selectedSo && selectedSo.id === {{ $so->id }} ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-600'">
                                        <span x-text="selectedSo && selectedSo.id === {{ $so->id }} ? 'Aktif' : 'Detail'"></span>
                                        <i data-lucide="chevron-down" class="w-3 h-3 transition-transform" :class="selectedSo && selectedSo.id === {{ $so->id }} ? 'rotate-180 text-white' : ''"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-400">
                                    Belum ada transaksi Sales Order. Klik "+ Buat SO Baru" untuk memulai.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- BAGIAN 2: DETAIL PANEL INLINE (DI BAWAH TABEL) -->
        <!-- ============================================== -->
        <div x-show="selectedSo" x-cloak class="scroll-mt-4" id="detailSection">
            <div class="rounded-2xl border-2 border-slate-300 shadow-lg overflow-hidden bg-white mb-8">
                
                <!-- Distinct Dark Header Strip for Detail Panel -->
                <div class="bg-slate-900 text-white px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800">
                    <div class="flex items-center gap-3">
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-extrabold bg-amber-500 text-slate-950 shadow-sm">
                            RINCIAN SO
                        </span>
                        <h4 class="text-base font-extrabold text-white tracking-tight" x-text="selectedSo ? selectedSo.doc_number : ''"></h4>
                        <span class="text-xs text-slate-400">&bull;</span>
                        <span class="text-xs font-bold text-amber-400" x-text="selectedSo ? selectedSo.customer_name : ''"></span>
                    </div>

                    <!-- Workflow Action Buttons in Header Strip -->
                    <div class="flex items-center gap-2">
                        
                        <!-- 1. Tombol Approval Finance -->
                        <template x-if="selectedSo && selectedSo.status === 'PENDING_APPROVAL'">
                            <div class="flex items-center gap-2">
                                <form method="POST" :action="`{{ url('sales/approve') }}/${selectedSo.id}`">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-xs flex items-center gap-1 shadow-sm active:scale-95">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                        <span>Setujui SO (Finance)</span>
                                    </button>
                                </form>
                                <form method="POST" :action="`{{ url('sales/reject') }}/${selectedSo.id}`">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-lg text-xs flex items-center gap-1 shadow-sm active:scale-95">
                                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                        <span>Tolak SO</span>
                                    </button>
                                </form>
                            </div>
                        </template>

                        <!-- 2. Tombol Sending Good (Gudang) jika sudah APPROVED -->
                        <template x-if="selectedSo && selectedSo.status === 'APPROVED'">
                            <form method="POST" :action="`{{ url('sales/deliver') }}/${selectedSo.id}`"
                                  data-loading-title="Memproses Pengiriman Barang"
                                  data-loading-sub="Menerbitkan Surat Jalan (GD) dan Faktur Piutang (AR)..."
                                  data-doctype="GD">
                                @csrf
                                <button type="submit" class="px-4 py-1.5 bg-cyan-600 hover:bg-cyan-700 text-white font-bold rounded-lg text-xs flex items-center gap-1.5 shadow-sm active:scale-95 animate-pulse">
                                    <i data-lucide="truck" class="w-4 h-4"></i>
                                    <span>Kirim Barang (Sending Good & GD)</span>
                                </button>
                            </form>
                        </template>

                        <!-- 3. Tombol Cetak Nota jika DELIVERED -->
                        <template x-if="selectedSo && selectedSo.status === 'DELIVERED'">
                            <div class="flex items-center gap-2">
                                <a :href="`{{ url('sales/print-nota') }}/${selectedSo.id}`" target="_blank"
                                   class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-amber-300 font-bold rounded-lg text-xs flex items-center gap-1.5 border border-slate-700">
                                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                    <span>Cetak Nota Penjualan</span>
                                </a>
                                <button type="button" @click="openReturnModal()"
                                        class="px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-lg text-xs flex items-center gap-1.5">
                                    <i data-lucide="undo-2" class="w-3.5 h-3.5"></i>
                                    <span>Sales Retur</span>
                                </button>
                            </div>
                        </template>

                    </div>
                </div>

                <div class="p-6">
                    
                    <!-- WORKFLOW STEPPER CARD -->
                    <div class="mb-6 p-4 rounded-xl bg-slate-50 border border-slate-200">
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs">
                            
                            <!-- Step 1: SO -->
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shrink-0">1</div>
                                <div>
                                    <span class="font-extrabold text-slate-800 block">Sales Order (SO)</span>
                                    <span class="font-mono text-[10px] text-slate-500" x-text="selectedSo ? selectedSo.doc_number : ''"></span>
                                </div>
                            </div>

                            <!-- Step 2: Approval -->
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs shrink-0"
                                     :class="selectedSo && (selectedSo.status === 'APPROVED' || selectedSo.status === 'DELIVERED') ? 'bg-emerald-600 text-white' : (selectedSo && selectedSo.status === 'REJECTED' ? 'bg-rose-600 text-white' : 'bg-amber-500 text-white')">
                                    2
                                </div>
                                <div>
                                    <span class="font-extrabold text-slate-800 block">Finance Approval</span>
                                    <span class="text-[10px] font-bold"
                                          :class="selectedSo && (selectedSo.status === 'APPROVED' || selectedSo.status === 'DELIVERED') ? 'text-emerald-700' : 'text-amber-700'"
                                          x-text="selectedSo ? selectedSo.status : ''"></span>
                                </div>
                            </div>

                            <!-- Step 3: Surat Jalan GD -->
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs shrink-0"
                                     :class="selectedSo && selectedSo.delivery ? 'bg-emerald-600 text-white' : 'bg-slate-300 text-slate-600'">
                                    3
                                </div>
                                <div>
                                    <span class="font-extrabold text-slate-800 block">Surat Jalan (GD)</span>
                                    <span class="font-mono text-[10px] font-bold text-cyan-800"
                                          x-text="selectedSo && selectedSo.delivery ? selectedSo.delivery.doc_number : 'Belum Dikirim'"></span>
                                </div>
                            </div>

                            <!-- Step 4: Piutang AR -->
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs shrink-0"
                                     :class="selectedSo && selectedSo.invoice ? 'bg-emerald-600 text-white' : 'bg-slate-300 text-slate-600'">
                                    4
                                </div>
                                <div>
                                    <span class="font-extrabold text-slate-800 block">Faktur Piutang (AR)</span>
                                    <span class="font-mono text-[10px] font-bold text-rose-800"
                                          x-text="selectedSo && selectedSo.invoice ? selectedSo.invoice.doc_number : 'Belum Terbit'"></span>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Detail Line Items Table -->
                    <div class="mb-5">
                        <h5 class="text-xs font-bold text-slate-700 uppercase mb-2">Item Barang yang Dipesan:</h5>
                        <div class="border border-slate-200 rounded-xl overflow-hidden bg-white">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider border-b border-slate-200">
                                    <tr>
                                        <th class="py-2.5 px-4">Produk</th>
                                        <th class="py-2.5 px-4">Tipe</th>
                                        <th class="py-2.5 px-4">Tracking</th>
                                        <th class="py-2.5 px-4 text-center">Kuantitas</th>
                                        <th class="py-2.5 px-4 text-right">Harga Jual</th>
                                        <th class="py-2.5 px-4 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium">
                                    <template x-for="(item, idx) in (selectedSo ? selectedSo.items : [])" :key="idx">
                                        <tr class="hover:bg-slate-50/70">
                                            <td class="py-3 px-4 font-bold text-slate-900">
                                                <span class="font-mono text-[10px] text-slate-400 block" x-text="item.sku"></span>
                                                <span x-text="item.product_name"></span>
                                            </td>
                                            <td class="py-3 px-4">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold"
                                                      :class="item.product_type === 'SA' ? 'bg-rose-100 text-rose-700' : 'bg-indigo-100 text-indigo-700'"
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
                                                Rp <span x-text="Number(item.unit_price).toLocaleString('id-ID')"></span>
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

                    <!-- Accordion Unit ICCID jika ada -->
                    <template x-if="selectedSo && selectedSo.iccids && selectedSo.iccids.length > 0">
                        <div class="mt-4 p-4 rounded-xl bg-purple-50/70 border border-purple-200/80">
                            <span class="text-xs font-bold text-purple-900 block mb-2">
                                Serial ICCID Terkait Transaksi Ini (<span x-text="selectedSo.iccids.length"></span> unit):
                            </span>
                            <div class="max-h-32 overflow-y-auto grid grid-cols-2 sm:grid-cols-4 gap-1.5 bg-white p-2.5 rounded-lg border border-purple-100">
                                <template x-for="iccid in selectedSo.iccids" :key="iccid">
                                    <span class="px-2 py-1 rounded bg-purple-50 text-purple-800 font-mono text-[11px] border border-purple-200 truncate" x-text="iccid"></span>
                                </template>
                            </div>
                        </div>
                    </template>

                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- MODAL SALES RETUR                              -->
        <!-- ============================================== -->
        <div x-show="returnModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                        <i data-lucide="undo-2" class="w-4 h-4 text-rose-600"></i>
                        <span>Formulir Sales Retur (Pengembalian Barang)</span>
                    </h3>
                    <button @click="returnModalOpen = false" class="text-slate-400 hover:text-slate-700">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('sales.return') }}">
                    @csrf
                    <input type="hidden" name="sales_order_id" :value="selectedSo ? selectedSo.id : ''">

                    <div class="space-y-4 mb-5">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                            <div class="flex justify-between mb-1">
                                <span class="text-slate-500">No. SO:</span>
                                <span class="font-mono font-bold text-slate-800" x-text="selectedSo ? selectedSo.doc_number : ''"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Customer:</span>
                                <span class="font-bold text-slate-800" x-text="selectedSo ? selectedSo.customer_name : ''"></span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Produk yang Diretur</label>
                            <template x-for="(item, idx) in (selectedSo ? selectedSo.items : [])" :key="idx">
                                <div class="flex items-center justify-between p-2 rounded-lg border border-slate-200 mb-2 bg-white">
                                    <div>
                                        <span class="font-bold text-xs text-slate-800 block" x-text="item.product_name"></span>
                                        <span class="text-[10px] text-slate-400 font-mono">Dipesan: <span x-text="item.quantity"></span> pcs</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <input type="hidden" :name="`items[${idx}][product_id]`" :value="item.product_id">
                                        <label class="text-[10px] font-bold text-slate-500">Qty Retur:</label>
                                        <input type="number" 
                                               :name="`items[${idx}][quantity]`" 
                                               value="1" 
                                               min="0" 
                                               :max="item.quantity" 
                                               class="w-16 text-center text-xs font-bold border border-slate-200 rounded px-1.5 py-1">
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Pengembalian (Notes)</label>
                            <textarea name="notes" rows="2" placeholder="Contoh: Fisik rusak / cacat produksi" class="w-full text-xs border border-slate-200 rounded-lg p-2 focus:outline-none"></textarea>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="returnModalOpen = false" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl text-xs">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Konfirmasi Retur</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function salesApp() {
            return {
                createSoOpen: false,
                returnModalOpen: false,
                selectedSo: null,
                selectedWarehouseId: '',

                items: [
                    {
                        product_id: '',
                        tracking_method: '',
                        unit_price: 0,
                        quantity: 1,
                        iccid_mode: 'SELECT',
                        selected_iccids: [],
                        iccid_text: ''
                    }
                ],

                products: @json($products),
                stockBalances: @json($stockBalances),
                availableIccids: @json($availableIccids),

                get grandTotal() {
                    return this.items.reduce((acc, row) => acc + this.getRowSubtotal(row), 0);
                },

                get totalQty() {
                    return this.items.reduce((acc, row) => {
                        if (row.tracking_method === 'ICCID') {
                            return acc + (this.getIccidCount(row) || 0);
                        }
                        return acc + (parseInt(row.quantity) || 0);
                    }, 0);
                },

                initData() {
                    const firstSo = @json($orders->first());
                    if (firstSo) this.selectedSo = firstSo;

                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                },

                selectSo(so) {
                    this.selectedSo = so;
                    this.$nextTick(() => {
                        lucide.createIcons();
                        const el = document.getElementById('detailSection');
                        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    });
                },

                openReturnModal() {
                    this.returnModalOpen = true;
                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                },

                addItemRow() {
                    this.items.push({
                        product_id: '',
                        tracking_method: '',
                        unit_price: 0,
                        quantity: 1,
                        iccid_mode: 'SELECT',
                        selected_iccids: [],
                        iccid_text: ''
                    });
                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                },

                removeItemRow(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    }
                },

                onWarehouseChange() {
                    this.items.forEach(row => {
                        row.product_id = '';
                        row.tracking_method = '';
                        row.unit_price = 0;
                        row.selected_iccids = [];
                        row.iccid_text = '';
                    });
                },

                getAvailableProductsInWarehouse() {
                    if (!this.selectedWarehouseId) return [];
                    const warehouseBalances = this.stockBalances.filter(b => b.warehouse_id == this.selectedWarehouseId && b.quantity > 0);
                    const prodIds = warehouseBalances.map(b => b.product_id);
                    return this.products.filter(p => prodIds.includes(p.id));
                },

                getAvailableStock(productId) {
                    if (!this.selectedWarehouseId || !productId) return 0;
                    const found = this.stockBalances.find(b => b.warehouse_id == this.selectedWarehouseId && b.product_id == productId);
                    return found ? parseInt(found.quantity) : 0;
                },

                getIccidsForProduct(productId) {
                    if (!this.selectedWarehouseId || !productId) return [];
                    return this.availableIccids.filter(u => u.warehouse_id == this.selectedWarehouseId && u.product_id == productId);
                },

                updateProductRow(index) {
                    const row = this.items[index];
                    const prod = this.products.find(p => p.id == row.product_id);
                    if (prod) {
                        row.tracking_method = prod.tracking_method;
                        row.unit_price = parseFloat(prod.selling_price) || 0;
                    } else {
                        row.tracking_method = '';
                        row.unit_price = 0;
                    }
                    row.selected_iccids = [];
                    row.iccid_text = '';
                },

                selectAllIccids(index) {
                    const row = this.items[index];
                    const list = this.getIccidsForProduct(row.product_id);
                    row.selected_iccids = list.map(u => u.iccid_number);
                },

                getIccidCount(row) {
                    if (row.iccid_mode === 'SELECT') {
                        return row.selected_iccids ? row.selected_iccids.length : 0;
                    } else if (row.iccid_mode === 'PASTE') {
                        return row.iccid_text ? row.iccid_text.split(/[\r\n,]+/).map(s => s.trim()).filter(Boolean).length : 0;
                    }
                    return 0;
                },

                getRowSubtotal(row) {
                    const price = parseFloat(row.unit_price) || 0;
                    const qty = row.tracking_method === 'ICCID' ? this.getIccidCount(row) : (parseInt(row.quantity) || 0);
                    return price * qty;
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
