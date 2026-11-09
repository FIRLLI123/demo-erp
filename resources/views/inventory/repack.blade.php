<x-app-layout>
    <x-slot name="title">Repack Out & Repack In (RO - RI)</x-slot>
    <x-slot name="headerTitle">Repack Out & Repack In (RO - RI)</x-slot>
    <x-slot name="badge">DocType: RPKOUT & RPKIN</x-slot>

    <div x-data="repackApp()" x-init="initData()">
        
        <!-- Top Action & Navigation Tabs -->
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Konversi & Pengemasan Ulang (Repack)</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">PT WAHANA PUTRA YUDHA</span>
                </h2>
                <p class="text-xs text-slate-500">Operasional Repack Resmi PT Wahana Putra Yudha: Penggabungan 2 komponen bahan (Fisik 0K + Saldo Bulk) menjadi 1 produk baru bernilai setara</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('reports.index', ['type' => 'REPACK']) }}" 
                   class="px-3.5 py-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-xs transition-all shrink-0">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-amber-600"></i>
                    <span>Rekap Repack (Excel/PDF)</span>
                </a>

                <!-- Tab Switcher with Distinct Active Colors -->
                <div class="flex items-center gap-2 bg-slate-200/70 p-1 rounded-2xl shrink-0">
                    <button type="button" 
                            @click="activeTab = 'RO'"
                            class="px-4 py-2 rounded-xl text-xs font-extrabold flex items-center gap-2 transition-all shadow-xs"
                            :class="activeTab === 'RO' ? 'bg-amber-500 text-white shadow-md' : 'text-slate-600 hover:text-slate-900'">
                        <i data-lucide="arrow-up-right" class="w-4 h-4"></i>
                        <span>1. Repack Out (RO)</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-white/20">{{ count($roList) }}</span>
                    </button>

                    <button type="button" 
                            @click="activeTab = 'RI'"
                            class="px-4 py-2 rounded-xl text-xs font-extrabold flex items-center gap-2 transition-all shadow-xs"
                            :class="activeTab === 'RI' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-600 hover:text-slate-900'">
                        <i data-lucide="arrow-down-left" class="w-4 h-4"></i>
                        <span>2. Repack In (RI)</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-white/20">{{ count($riList) }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- TAB 1: REPACK OUT (RO / RPKOUT)                -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'RO'" x-cloak>
            
            <div class="mb-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-100 text-amber-800 border border-amber-300">
                        DocType: RPKOUT
                    </span>
                    <span class="text-xs text-slate-500">Keluarkan 2 SKU komponen pembentuk dari stok gudang</span>
                </div>

                <button type="button" 
                        @click="createRoOpen = !createRoOpen" 
                        class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-sm transition-all active:scale-95">
                    <i data-lucide="plus-circle" class="w-4 h-4 text-amber-400"></i>
                    <span x-text="createRoOpen ? 'Tutup Form' : '+ Buat RO Baru (RPKOUT)'"></span>
                </button>
            </div>

            <!-- FORM BUAT RO BARU (2 KOMPONEN) -->
            <div x-show="createRoOpen" x-cloak x-transition class="mb-6 bg-white rounded-2xl border-2 border-amber-500/40 p-5 shadow-lg">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                        <i data-lucide="boxes" class="w-4 h-4 text-amber-600"></i>
                        <span>Formulir Pengeluaran Bahan Repack (RO - RPKOUT)</span>
                    </h3>
                    <button @click="createRoOpen = false" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('inventory.repack.store-ro') }}" 
                      enctype="multipart/form-data"
                      data-loading-title="Memproses Repack Out"
                      data-loading-sub="Mengurangi stok 2 komponen bahan & merilis dokumen RO..."
                      data-doctype="RPKOUT">
                    @csrf

                    <!-- Header Form -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Lokasi Gudang Bahan</label>
                            <select name="warehouse_id" 
                                    x-model="roWarehouseId" 
                                    @change="onRoWarehouseChange()"
                                    required 
                                    class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                                <option value="">-- Pilih Gudang --</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">
                                        [{{ $wh->type }}] {{ $wh->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Transaksi</label>
                            <input type="date" name="repack_date" value="{{ date('Y-m-d') }}" required class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan / Notes</label>
                            <input type="text" name="notes" value="Repack Komponen Bahan Baru" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none">
                        </div>
                    </div>

                    <!-- 2 KOMPONEN BAHAN REPACK -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                        
                        <!-- KOMPONEN 1: BAHAN FISIK 0K (ICCID) -->
                        <div class="border-2 border-slate-200 rounded-2xl p-4 bg-slate-50/50">
                            <div class="flex items-center justify-between pb-2 mb-3 border-b border-slate-200">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-5 h-5 rounded-full bg-amber-500 text-white font-mono font-bold text-[11px] flex items-center justify-center">1</span>
                                    <span class="text-xs font-extrabold text-slate-800">Komponen Fisik (Kartu 0K)</span>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-purple-100 text-purple-700">Tracking: ICCID</span>
                            </div>

                            <div class="space-y-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Pilih Produk Bahan 0K</label>
                                    <select name="comp1_product_id" 
                                            x-model="comp1Id" 
                                            @change="onComp1Change()"
                                            required 
                                            class="w-full text-xs font-bold text-slate-800 bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 focus:outline-none">
                                        <option value="">-- Pilih Produk Fisik 0K --</option>
                                        @foreach($rawPhysicalProducts as $rp)
                                            <option value="{{ $rp->id }}" data-cost="{{ $rp->cost_price }}" data-sku="{{ $rp->sku }}">
                                                [{{ $rp->type }}] {{ $rp->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="flex items-center justify-between text-xs font-mono">
                                    <span class="text-slate-500">Harga Modal:</span>
                                    <span class="font-bold text-slate-800">Rp <span x-text="comp1Cost.toLocaleString('id-ID')"></span></span>
                                </div>

                                <!-- Input ICCID -->
                                <div class="pt-2 border-t border-slate-200">
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="text-[11px] font-bold text-slate-700">Metode Serial ICCID:</label>
                                        <div class="inline-flex rounded-lg border border-slate-200 p-0.5 bg-slate-100 text-[10px] font-bold">
                                            <button type="button" @click="comp1IccidMode = 'SELECT'" :class="comp1IccidMode === 'SELECT' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500'" class="px-2 py-0.5 rounded-md">Pilih Stok</button>
                                            <button type="button" @click="comp1IccidMode = 'PASTE'" :class="comp1IccidMode === 'PASTE' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500'" class="px-2 py-0.5 rounded-md">Paste Teks</button>
                                            <button type="button" @click="comp1IccidMode = 'UPLOAD'" :class="comp1IccidMode === 'UPLOAD' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500'" class="px-2 py-0.5 rounded-md">Upload CSV</button>
                                        </div>
                                    </div>

                                    <input type="hidden" name="comp1_iccid_mode" :value="comp1IccidMode">

                                    <!-- Mode Select -->
                                    <div x-show="comp1IccidMode === 'SELECT'">
                                        <div class="flex items-center justify-between text-[11px] mb-1">
                                            <span class="text-slate-500">Centang unit di gudang asal:</span>
                                            <div class="flex items-center gap-2">
                                                <button type="button" @click="selectAllComp1Iccids()" class="text-amber-600 font-bold hover:underline">Pilih Semua</button>
                                                <span>&bull;</span>
                                                <button type="button" @click="comp1SelectedIccids = []" class="text-slate-400 font-bold hover:underline">Reset</button>
                                            </div>
                                        </div>
                                        <div class="max-h-32 overflow-y-auto border border-slate-200 rounded-lg p-2 grid grid-cols-2 gap-1.5 bg-white">
                                            <template x-for="iccid in getComp1AvailableIccids()" :key="iccid.id">
                                                <label class="flex items-center gap-1.5 text-[10px] font-mono bg-slate-50 p-1 rounded border border-slate-200 cursor-pointer hover:bg-amber-50">
                                                    <input type="checkbox" 
                                                           name="comp1_selected_iccids[]" 
                                                           :value="iccid.iccid_number"
                                                           x-model="comp1SelectedIccids"
                                                           class="rounded text-amber-600 focus:ring-amber-500 w-3.5 h-3.5">
                                                    <span x-text="iccid.iccid_number" class="truncate"></span>
                                                </label>
                                            </template>
                                            <template x-if="getComp1AvailableIccids().length === 0">
                                                <div class="col-span-full py-2 text-center text-slate-400 text-xs">
                                                    Tidak ada ICCID berstatus AVAILABLE di gudang ini.
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Mode Paste -->
                                    <div x-show="comp1IccidMode === 'PASTE'">
                                        <textarea name="comp1_iccid_text" 
                                                  x-model="comp1IccidText" 
                                                  rows="3" 
                                                  placeholder="Paste serial ICCID disini (1 serial per baris)..." 
                                                  class="w-full text-xs font-mono bg-white border border-slate-200 rounded-lg p-2 focus:outline-none"></textarea>
                                    </div>

                                    <!-- Mode Upload -->
                                    <div x-show="comp1IccidMode === 'UPLOAD'">
                                        <input type="file" name="comp1_iccid_file" accept=".csv,.txt" class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-amber-50 file:text-amber-700">
                                        <div class="mt-1 text-[10px]">
                                            <a href="{{ route('inventory.repack.download-template') }}" class="text-amber-600 hover:underline font-bold">Download Template CSV</a>
                                        </div>
                                    </div>

                                    <div class="mt-2 flex items-center justify-between text-xs font-bold pt-2 border-t border-slate-200">
                                        <span class="text-slate-600">Total Unit Terpilih:</span>
                                        <span class="font-mono text-purple-700 font-extrabold"><span x-text="comp1Qty"></span> pcs</span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs font-bold mt-1">
                                        <span class="text-slate-600">Subtotal Komponen 1:</span>
                                        <span class="font-mono text-slate-900">Rp <span x-text="(comp1Qty * comp1Cost).toLocaleString('id-ID')"></span></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- KOMPONEN 2: BAHAN SALDO BULK (QUANTITY) -->
                        <div class="border-2 border-slate-200 rounded-2xl p-4 bg-slate-50/50">
                            <div class="flex items-center justify-between pb-2 mb-3 border-b border-slate-200">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-5 h-5 rounded-full bg-amber-500 text-white font-mono font-bold text-[11px] flex items-center justify-center">2</span>
                                    <span class="text-xs font-extrabold text-slate-800">Komponen Saldo (NGRS Bulk)</span>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-blue-100 text-blue-700">Tracking: QUANTITY</span>
                            </div>

                            <div class="space-y-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Pilih Produk Saldo Bulk</label>
                                    <select name="comp2_product_id" 
                                            x-model="comp2Id" 
                                            @change="onComp2Change()"
                                            required 
                                            class="w-full text-xs font-bold text-slate-800 bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 focus:outline-none">
                                        <option value="">-- Pilih Produk NGRS Bulk --</option>
                                        @foreach($bulkProducts as $bp)
                                            <option value="{{ $bp->id }}" data-cost="{{ $bp->cost_price }}" data-sku="{{ $bp->sku }}">
                                                [{{ $bp->type }}] {{ $bp->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="flex items-center justify-between text-xs font-mono">
                                    <span class="text-slate-500">Harga Modal / Saldo:</span>
                                    <span class="font-bold text-slate-800">Rp <span x-text="comp2Cost.toLocaleString('id-ID')"></span></span>
                                </div>

                                <div class="flex items-center justify-between text-xs font-mono">
                                    <span class="text-slate-500">Stok Saldo Tersedia:</span>
                                    <span class="font-bold text-emerald-700"><span x-text="getComp2AvailableStock()"></span> saldo</span>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Jumlah Saldo yang Dihabiskan (Qty)</label>
                                    <input type="number" 
                                           name="comp2_quantity" 
                                           x-model.number="comp2Qty" 
                                           min="1" 
                                           required 
                                           placeholder="Contoh: 14900"
                                           class="w-full text-xs font-mono font-bold text-center bg-white border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-amber-500">
                                </div>

                                <div class="pt-4 border-t border-slate-200">
                                    <div class="flex items-center justify-between text-xs font-bold">
                                        <span class="text-slate-600">Subtotal Komponen 2:</span>
                                        <span class="font-mono text-slate-900">Rp <span x-text="(comp2Qty * comp2Cost).toLocaleString('id-ID')"></span></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- TOTAL NILAI RO STRIP -->
                    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <i data-lucide="calculator" class="w-5 h-5 text-amber-700"></i>
                            <div>
                                <span class="text-xs font-bold text-slate-700">Total Nilai RO (Repack Out):</span>
                                <div class="text-lg font-extrabold text-amber-900 font-mono">
                                    Rp <span x-text="totalRoAmount.toLocaleString('id-ID')"></span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="createRoOpen = false" class="px-4 py-2 bg-white hover:bg-slate-100 text-slate-700 font-bold rounded-xl text-xs border border-slate-200">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-sm transition-all active:scale-95">
                                <i data-lucide="check" class="w-4 h-4"></i>
                                <span>Simpan & Rilis RO (RPKOUT)</span>
                            </button>
                        </div>
                    </div>

                </form>
            </div>

            <!-- TABEL MASTER REPACK OUT (RO) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-6">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <h4 class="font-extrabold text-slate-900 text-xs uppercase tracking-wider">Histori Dokumen Repack Out (RO)</h4>
                    <span class="text-xs text-slate-400 font-mono">{{ count($roList) }} Transaksi</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                            <tr>
                                <th class="py-3 px-4">No. Dokumen RO</th>
                                <th class="py-3 px-4">Tanggal</th>
                                <th class="py-3 px-4">Gudang</th>
                                <th class="py-3 px-4 text-center">Komponen Keluar</th>
                                <th class="py-3 px-4 text-right">Total Nilai RO</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-center w-28">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($roList as $ro)
                                <tr @click="selectRo({{ json_encode($ro) }})" 
                                    class="cursor-pointer transition-colors"
                                    :class="selectedRo && selectedRo.id === {{ $ro->id }} ? 'bg-amber-50/80 border-l-4 border-amber-500' : 'hover:bg-slate-50/70'">
                                    
                                    <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-1.5 py-0.5 rounded text-[10px] bg-amber-600 text-white font-mono font-bold">RO</span>
                                            <span>{{ $ro->doc_number }}</span>
                                        </div>
                                    </td>

                                    <td class="py-3.5 px-4 text-slate-600">
                                        {{ date('d M Y', strtotime($ro->repack_date)) }}
                                    </td>

                                    <td class="py-3.5 px-4 font-bold text-slate-800">
                                        {{ $ro->warehouse_name }}
                                    </td>

                                    <td class="py-3.5 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                            {{ count($ro->items) }} Komponen
                                        </span>
                                    </td>

                                    <td class="py-3.5 px-4 text-right font-mono font-extrabold text-slate-900">
                                        Rp {{ number_format($ro->total_amount, 0, ',', '.') }}
                                    </td>

                                    <td class="py-3.5 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold 
                                            {{ $ro->status === 'OPEN' ? 'bg-amber-100 text-amber-800 border border-amber-300 animate-pulse' : 'bg-emerald-100 text-emerald-800' }}">
                                            {{ $ro->status === 'OPEN' ? 'OPEN (Siap RI)' : 'COMPLETED' }}
                                        </span>
                                    </td>

                                    <td class="py-3.5 px-4 text-center">
                                        <button type="button" 
                                                class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all flex items-center gap-1 mx-auto shadow-xs"
                                                :class="selectedRo && selectedRo.id === {{ $ro->id }} ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-600'">
                                            <span x-text="selectedRo && selectedRo.id === {{ $ro->id }} ? 'Aktif' : 'Detail'"></span>
                                            <i data-lucide="chevron-down" class="w-3 h-3 transition-transform" :class="selectedRo && selectedRo.id === {{ $ro->id }} ? 'rotate-180 text-white' : ''"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-slate-400">
                                        Belum ada dokumen Repack Out (RO). Klik "+ Buat RO Baru" untuk memulai.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- INLINE DETAIL PANEL RO -->
            <div x-show="selectedRo" x-cloak class="scroll-mt-4" id="roDetailSection">
                <div class="rounded-2xl border-2 border-slate-300 shadow-lg overflow-hidden bg-white mb-8">
                    <div class="bg-slate-900 text-white px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-extrabold bg-amber-500 text-white">
                                DETAIL RO (RPKOUT)
                            </span>
                            <h4 class="text-base font-extrabold text-white tracking-tight" x-text="selectedRo ? selectedRo.doc_number : ''"></h4>
                        </div>
                        <div class="flex items-center gap-3 text-xs">
                            <span class="text-slate-400">Gudang: <strong class="text-white" x-text="selectedRo ? selectedRo.warehouse_name : ''"></strong></span>
                            <span class="font-mono font-bold text-amber-400">Total: Rp <span x-text="selectedRo ? Number(selectedRo.total_amount).toLocaleString('id-ID') : '0'"></span></span>
                        </div>
                    </div>

                    <div class="p-6">
                        <h5 class="text-xs font-bold text-slate-700 uppercase mb-2">2 Komponen Bahan yang Dikeluarkan dari Gudang:</h5>
                        <div class="border border-slate-200 rounded-xl overflow-hidden bg-white mb-4">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider border-b border-slate-200">
                                    <tr>
                                        <th class="py-2.5 px-4">Komponen</th>
                                        <th class="py-2.5 px-4">Tipe & Tracking</th>
                                        <th class="py-2.5 px-4 text-center">Kuantitas</th>
                                        <th class="py-2.5 px-4 text-right">Harga Modal</th>
                                        <th class="py-2.5 px-4 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium">
                                    <template x-for="(item, idx) in (selectedRo ? selectedRo.items : [])" :key="idx">
                                        <tr class="hover:bg-slate-50/70">
                                            <td class="py-3 px-4 font-bold text-slate-900">
                                                <span class="font-mono text-[10px] text-slate-400 block" x-text="item.sku"></span>
                                                <span x-text="item.product_name"></span>
                                            </td>
                                            <td class="py-3 px-4">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold"
                                                      :class="item.tracking_method === 'ICCID' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'"
                                                      x-text="item.tracking_method">
                                                </span>
                                            </td>
                                            <td class="py-3 px-4 text-center font-mono font-bold text-slate-800">
                                                <span x-text="item.quantity.toLocaleString('id-ID')"></span>
                                            </td>
                                            <td class="py-3 px-4 text-right font-mono text-slate-600">
                                                Rp <span x-text="Number(item.unit_cost).toLocaleString('id-ID')"></span>
                                            </td>
                                            <td class="py-3 px-4 text-right font-mono font-extrabold text-slate-900">
                                                Rp <span x-text="Number(item.subtotal).toLocaleString('id-ID')"></span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- Accordion ICCID jika ada -->
                        <template x-if="selectedRo && selectedRo.iccids && selectedRo.iccids.length > 0">
                            <div class="p-3.5 rounded-xl bg-purple-50/60 border border-purple-200">
                                <span class="text-xs font-bold text-purple-900 block mb-2">
                                    Daftar ICCID Komponen Fisik yang Dikeluarkan (<span x-text="selectedRo.iccids.length"></span> unit):
                                </span>
                                <div class="max-h-32 overflow-y-auto grid grid-cols-2 sm:grid-cols-4 gap-1 bg-white p-2 rounded-lg border border-purple-100">
                                    <template x-for="iccid in selectedRo.iccids" :key="iccid">
                                        <span class="text-[10px] font-mono font-semibold text-purple-800 bg-purple-50 px-1.5 py-0.5 rounded truncate" x-text="iccid"></span>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

        </div>

        <!-- ============================================== -->
        <!-- TAB 2: REPACK IN (RI / RPKIN)                  -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'RI'" x-cloak>
            
            <div class="mb-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-indigo-100 text-indigo-800 border border-indigo-300">
                        DocType: RPKIN
                    </span>
                    <span class="text-xs text-slate-500">Pemasukan 1 SKU produk hasil repack ke gudang (Nilai konversi sama dengan RO)</span>
                </div>

                <button type="button" 
                        @click="createRiOpen = !createRiOpen" 
                        class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-sm transition-all active:scale-95">
                    <i data-lucide="plus-circle" class="w-4 h-4 text-indigo-400"></i>
                    <span x-text="createRiOpen ? 'Tutup Form' : '+ Buat RI Baru (RPKIN)'"></span>
                </button>
            </div>

            <!-- FORM BUAT RI BARU (MENGAMBIL DATA RO OPEN) -->
            <div x-show="createRiOpen" x-cloak x-transition class="mb-6 bg-white rounded-2xl border-2 border-indigo-500/40 p-5 shadow-lg">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                        <i data-lucide="package-plus" class="w-4 h-4 text-indigo-600"></i>
                        <span>Formulir Pemasukan Hasil Repack (RI - RPKIN)</span>
                    </h3>
                    <button @click="createRiOpen = false" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('inventory.repack.store-ri') }}" 
                      data-loading-title="Memproses Repack In"
                      data-loading-sub="Memasukkan produk hasil repack & update serial ICCID..."
                      data-doctype="RPKIN">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Dokumen RO Referensi</label>
                            <select name="ref_ro_doc_number" 
                                    x-model="selectedRoDocNumber" 
                                    @change="onSelectedRoDocChange()"
                                    required 
                                    class="w-full text-xs font-bold text-indigo-900 bg-indigo-50/70 border border-indigo-200 rounded-xl px-3 py-2 focus:outline-none">
                                <option value="">-- Pilih Dokumen RO yang Siap di-Repack --</option>
                                @foreach($openRoList as $openRo)
                                    <option value="{{ $openRo->doc_number }}">
                                        {{ $openRo->doc_number }} (Rp {{ number_format($openRo->total_amount, 0, ',', '.') }}) - {{ $openRo->warehouse_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Produk Hasil Repack</label>
                            <select name="output_product_id" 
                                    required 
                                    class="w-full text-xs font-bold text-slate-800 bg-white border border-slate-200 rounded-xl px-3 py-2 focus:outline-none">
                                <option value="">-- Pilih Produk Baru Jadi --</option>
                                @foreach($outputProducts as $op)
                                    <option value="{{ $op->id }}">
                                        [{{ $op->type }}] {{ $op->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Pemasukan</label>
                            <input type="date" name="repack_date" value="{{ date('Y-m-d') }}" required class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none">
                        </div>
                    </div>

                    <!-- PREVIEW NILAI & KUANTITAS REPACK -->
                    <template x-if="currentRoObj">
                        <div class="mb-5 p-4 rounded-xl bg-indigo-50/70 border border-indigo-200">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                                <div>
                                    <span class="text-slate-500 block">Kuantitas Produk Hasil:</span>
                                    <span class="text-base font-extrabold font-mono text-indigo-950">
                                        <span x-text="getRoOutputQty()"></span> pcs
                                    </span>
                                    <span class="text-[10px] text-slate-400 block">(Sesuai kuantitas kartu fisik 0K)</span>
                                </div>
                                <div>
                                    <span class="text-slate-500 block">Total Nilai RI (Sama dengan RO):</span>
                                    <span class="text-base font-extrabold font-mono text-indigo-950">
                                        Rp <span x-text="Number(currentRoObj.total_amount).toLocaleString('id-ID')"></span>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-slate-500 block">Harga Modal Satuan Baru:</span>
                                    <span class="text-base font-extrabold font-mono text-emerald-700">
                                        Rp <span x-text="Math.round(currentRoObj.total_amount / (getRoOutputQty() || 1)).toLocaleString('id-ID')"></span> / pcs
                                    </span>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="createRiOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-sm transition-all active:scale-95">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Simpan & Masukkan ke Stok (RPKIN)</span>
                        </button>
                    </div>

                </form>
            </div>

            <!-- TABEL MASTER REPACK IN (RI) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-6">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <h4 class="font-extrabold text-slate-900 text-xs uppercase tracking-wider">Histori Dokumen Repack In (RI)</h4>
                    <span class="text-xs text-slate-400 font-mono">{{ count($riList) }} Transaksi</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                            <tr>
                                <th class="py-3 px-4">No. Dokumen RI</th>
                                <th class="py-3 px-4">Ref Dokumen RO</th>
                                <th class="py-3 px-4">Tanggal</th>
                                <th class="py-3 px-4">Gudang</th>
                                <th class="py-3 px-4 text-center">Produk Hasil</th>
                                <th class="py-3 px-4 text-right">Total Nilai RI</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-center w-28">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($riList as $ri)
                                <tr @click="selectRi({{ json_encode($ri) }})" 
                                    class="cursor-pointer transition-colors"
                                    :class="selectedRi && selectedRi.id === {{ $ri->id }} ? 'bg-indigo-50/80 border-l-4 border-indigo-600' : 'hover:bg-slate-50/70'">
                                    
                                    <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-1.5 py-0.5 rounded text-[10px] bg-indigo-600 text-white font-mono font-bold">RI</span>
                                            <span>{{ $ri->doc_number }}</span>
                                        </div>
                                    </td>

                                    <td class="py-3.5 px-4 font-mono font-bold text-amber-700">
                                        {{ $ri->ref_doc_number }}
                                    </td>

                                    <td class="py-3.5 px-4 text-slate-600">
                                        {{ date('d M Y', strtotime($ri->repack_date)) }}
                                    </td>

                                    <td class="py-3.5 px-4 font-bold text-slate-800">
                                        {{ $ri->warehouse_name }}
                                    </td>

                                    <td class="py-3.5 px-4 text-center font-bold text-indigo-900">
                                        @if(isset($ri->items[0]))
                                            {{ $ri->items[0]->product_name }} ({{ number_format($ri->items[0]->quantity) }} pcs)
                                        @else
                                            1 Produk Baru
                                        @endif
                                    </td>

                                    <td class="py-3.5 px-4 text-right font-mono font-extrabold text-slate-900">
                                        Rp {{ number_format($ri->total_amount, 0, ',', '.') }}
                                    </td>

                                    <td class="py-3.5 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            {{ $ri->status }}
                                        </span>
                                    </td>

                                    <td class="py-3.5 px-4 text-center">
                                        <button type="button" 
                                                class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all flex items-center gap-1 mx-auto shadow-xs"
                                                :class="selectedRi && selectedRi.id === {{ $ri->id }} ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600'">
                                            <span x-text="selectedRi && selectedRi.id === {{ $ri->id }} ? 'Aktif' : 'Detail'"></span>
                                            <i data-lucide="chevron-down" class="w-3 h-3 transition-transform" :class="selectedRi && selectedRi.id === {{ $ri->id }} ? 'rotate-180 text-white' : ''"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-slate-400">
                                        Belum ada dokumen Repack In (RI). Selesaikan proses Repack Out terlebih dahulu.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- INLINE DETAIL PANEL RI -->
            <div x-show="selectedRi" x-cloak class="scroll-mt-4" id="riDetailSection">
                <div class="rounded-2xl border-2 border-slate-300 shadow-lg overflow-hidden bg-white mb-8">
                    <div class="bg-slate-900 text-white px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-extrabold bg-indigo-500 text-white">
                                DETAIL RI (RPKIN)
                            </span>
                            <h4 class="text-base font-extrabold text-white tracking-tight" x-text="selectedRi ? selectedRi.doc_number : ''"></h4>
                        </div>
                        <div class="flex items-center gap-3 text-xs">
                            <span class="text-slate-400">Ref RO: <strong class="text-amber-400 font-mono" x-text="selectedRi ? selectedRi.ref_doc_number : ''"></strong></span>
                            <span class="font-mono font-bold text-emerald-400">Total Nilai Masuk: Rp <span x-text="selectedRi ? Number(selectedRi.total_amount).toLocaleString('id-ID') : '0'"></span></span>
                        </div>
                    </div>

                    <div class="p-6">
                        <h5 class="text-xs font-bold text-slate-700 uppercase mb-2">Produk Hasil Repack yang Masuk ke Gudang:</h5>
                        <div class="border border-slate-200 rounded-xl overflow-hidden bg-white mb-4">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider border-b border-slate-200">
                                    <tr>
                                        <th class="py-2.5 px-4">Produk Hasil</th>
                                        <th class="py-2.5 px-4">Tipe & Tracking</th>
                                        <th class="py-2.5 px-4 text-center">Kuantitas Masuk</th>
                                        <th class="py-2.5 px-4 text-right">Harga Modal Satuan</th>
                                        <th class="py-2.5 px-4 text-right">Total Nilai RI</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium">
                                    <template x-for="(item, idx) in (selectedRi ? selectedRi.items : [])" :key="idx">
                                        <tr class="hover:bg-slate-50/70">
                                            <td class="py-3 px-4 font-bold text-slate-900">
                                                <span class="font-mono text-[10px] text-slate-400 block" x-text="item.sku"></span>
                                                <span x-text="item.product_name"></span>
                                            </td>
                                            <td class="py-3 px-4">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-purple-100 text-purple-700" x-text="item.tracking_method"></span>
                                            </td>
                                            <td class="py-3 px-4 text-center font-mono font-bold text-slate-800">
                                                <span x-text="item.quantity.toLocaleString('id-ID')"></span> pcs
                                            </td>
                                            <td class="py-3 px-4 text-right font-mono text-emerald-700 font-bold">
                                                Rp <span x-text="Number(item.unit_cost).toLocaleString('id-ID')"></span>
                                            </td>
                                            <td class="py-3 px-4 text-right font-mono font-extrabold text-slate-900">
                                                Rp <span x-text="Number(item.subtotal).toLocaleString('id-ID')"></span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- Accordion ICCID yang kini dimiliki produk baru -->
                        <template x-if="selectedRi && selectedRi.iccids && selectedRi.iccids.length > 0">
                            <div class="p-3.5 rounded-xl bg-purple-50/60 border border-purple-200">
                                <span class="text-xs font-bold text-purple-900 block mb-2">
                                    Unit Serial ICCID Hasil Konversi (<span x-text="selectedRi.iccids.length"></span> unit):
                                </span>
                                <div class="max-h-32 overflow-y-auto grid grid-cols-2 sm:grid-cols-4 gap-1 bg-white p-2 rounded-lg border border-purple-100">
                                    <template x-for="iccid in selectedRi.iccids" :key="iccid">
                                        <span class="text-[10px] font-mono font-semibold text-purple-800 bg-purple-50 px-1.5 py-0.5 rounded truncate" x-text="iccid"></span>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

        </div>

    </div>

    @push('scripts')
    <script>
        function repackApp() {
            return {
                activeTab: 'RO',
                createRoOpen: false,
                createRiOpen: false,
                selectedRo: null,
                selectedRi: null,

                // RO Form States
                roWarehouseId: '',
                comp1Id: '',
                comp1Cost: 0,
                comp1IccidMode: 'SELECT',
                comp1SelectedIccids: [],
                comp1IccidText: '',

                comp2Id: '',
                comp2Cost: 0,
                comp2Qty: 1000,

                // RI Form States
                selectedRoDocNumber: '',
                currentRoObj: null,

                stockBalances: @json($stockBalances),
                availableIccids: @json($availableIccids),
                openRoList: @json($openRoList),

                get comp1Qty() {
                    if (this.comp1IccidMode === 'SELECT') {
                        return this.comp1SelectedIccids.length;
                    } else if (this.comp1IccidMode === 'PASTE') {
                        return this.comp1IccidText ? this.comp1IccidText.split(/[\r\n,]+/).map(s => s.trim()).filter(Boolean).length : 0;
                    } else {
                        return 1;
                    }
                },

                get totalRoAmount() {
                    const sub1 = (this.comp1Qty || 0) * (this.comp1Cost || 0);
                    const sub2 = (this.comp2Qty || 0) * (this.comp2Cost || 0);
                    return sub1 + sub2;
                },

                initData() {
                    const firstRo = @json($roList->first());
                    if (firstRo) this.selectedRo = firstRo;

                    const firstRi = @json($riList->first());
                    if (firstRi) this.selectedRi = firstRi;

                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                },

                selectRo(ro) {
                    this.selectedRo = ro;
                    this.$nextTick(() => {
                        lucide.createIcons();
                        const el = document.getElementById('roDetailSection');
                        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    });
                },

                selectRi(ri) {
                    this.selectedRi = ri;
                    this.$nextTick(() => {
                        lucide.createIcons();
                        const el = document.getElementById('riDetailSection');
                        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    });
                },

                onRoWarehouseChange() {
                    this.comp1SelectedIccids = [];
                },

                onComp1Change() {
                    const select = document.querySelector('select[name="comp1_product_id"]');
                    if (select && select.selectedOptions[0]) {
                        this.comp1Cost = parseFloat(select.selectedOptions[0].getAttribute('data-cost')) || 0;
                    } else {
                        this.comp1Cost = 0;
                    }
                    this.comp1SelectedIccids = [];
                },

                onComp2Change() {
                    const select = document.querySelector('select[name="comp2_product_id"]');
                    if (select && select.selectedOptions[0]) {
                        this.comp2Cost = parseFloat(select.selectedOptions[0].getAttribute('data-cost')) || 0;
                    } else {
                        this.comp2Cost = 0;
                    }
                },

                getComp1AvailableIccids() {
                    if (!this.roWarehouseId || !this.comp1Id) return [];
                    return this.availableIccids.filter(u => u.warehouse_id == this.roWarehouseId && u.product_id == this.comp1Id);
                },

                selectAllComp1Iccids() {
                    const list = this.getComp1AvailableIccids();
                    this.comp1SelectedIccids = list.map(u => u.iccid_number);
                },

                getComp2AvailableStock() {
                    if (!this.roWarehouseId || !this.comp2Id) return 0;
                    const found = this.stockBalances.find(b => b.warehouse_id == this.roWarehouseId && b.product_id == this.comp2Id);
                    return found ? parseInt(found.quantity) : 0;
                },

                onSelectedRoDocChange() {
                    this.currentRoObj = this.openRoList.find(r => r.doc_number === this.selectedRoDocNumber) || null;
                },

                getRoOutputQty() {
                    if (!this.currentRoObj || !this.currentRoObj.items) return 1;
                    const item1 = this.currentRoObj.items.find(i => i.tracking_method === 'ICCID');
                    return item1 ? parseInt(item1.quantity) : 1;
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
