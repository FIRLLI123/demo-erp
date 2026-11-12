<x-app-layout>
    <x-slot name="title">Inspeksi Serial ICCID - Laporan Stok Unit</x-slot>
    <x-slot name="headerTitle">Tracking & Inspeksi Nomor Serial ICCID</x-slot>
    <x-slot name="badge">Serial Fisik Unit</x-slot>

    <div class="space-y-6">

        <!-- Top Header & Breadcrumb Navigation -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <!-- Breadcrumb -->
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                    <a href="{{ route('inventory.index') }}" class="hover:text-slate-600 transition-colors">Laporan Stok</a>
                    <span>/</span>
                    <span class="text-slate-700">Inspeksi Serial ICCID</span>
                </div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Inspeksi Detail Serial ICCID</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-purple-100 text-purple-800 border border-purple-200">SERIALIZED INVENTORY</span>
                </h2>
                <p class="text-xs text-slate-500">Daftar lengkap nomor serial fisik kartu perdana & modem per gudang penyimpanan PT Wahana Putra Yudha.</p>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('inventory.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 flex items-center gap-2 shadow-xs transition-all">
                    <i data-lucide="arrow-left" class="w-4 h-4 text-slate-500"></i>
                    <span>Kembali ke Ringkasan Stok</span>
                </a>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- CONTEXT CARD: JIKA MEMILIH GUDANG & PRODUK     -->
        <!-- ============================================== -->
        @if ($currentWarehouse && $currentProduct)
            <div class="p-5 rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white shadow-md relative overflow-hidden">
                <div class="absolute right-0 top-0 bottom-0 opacity-10 flex items-center pr-8 pointer-events-none">
                    <i data-lucide="scan-barcode" class="w-48 h-48 text-white"></i>
                </div>
                
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-rose-500 text-white">
                                {{ $currentWarehouse->type }}
                            </span>
                            <span class="text-xs font-bold text-slate-300">{{ $currentWarehouse->tap_name }}</span>
                        </div>
                        <h3 class="text-base font-extrabold text-white flex items-center gap-2">
                            <span>{{ $currentProduct->name }}</span>
                            <span class="text-xs font-mono font-normal text-rose-300">[{{ $currentProduct->sku }}]</span>
                        </h3>
                        <p class="text-xs text-slate-300 flex items-center gap-2">
                            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-rose-400"></i>
                            <span>Lokasi Gudang: <strong>{{ $currentWarehouse->name }}</strong></span>
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 bg-white/10 p-3 rounded-xl border border-white/10 backdrop-blur-xs">
                        <!-- Saldo Stok Fisik -->
                        <div class="text-right pr-3 border-r border-white/20">
                            <span class="text-[10px] font-bold text-slate-300 block uppercase tracking-wider">Stok Fisik Tersedia</span>
                            <span class="text-xl font-extrabold font-mono text-emerald-400">
                                {{ number_format($stockBalance ? $stockBalance->quantity : 0, 0, ',', '.') }}
                                <span class="text-xs font-normal text-slate-300">pcs</span>
                            </span>
                        </div>

                        <!-- Info Terjual -->
                        <div class="text-right pr-3 border-r border-white/20">
                            <span class="text-[10px] font-bold text-slate-300 block uppercase tracking-wider">Laku Terjual (SOLD)</span>
                            <span class="text-xl font-extrabold font-mono text-slate-300">
                                {{ number_format($kpi['sold'], 0, ',', '.') }}
                                <span class="text-xs font-normal text-slate-400">pcs</span>
                            </span>
                        </div>

                        <!-- Rentang Serial (Range ICCID) -->
                        <div>
                            <span class="text-[10px] font-bold text-slate-300 block uppercase tracking-wider">Rentang Serial (Range Batch)</span>
                            @if ($serialRange)
                                <div class="text-xs font-mono font-bold text-emerald-300 flex items-center gap-1.5">
                                    <span>{{ $serialRange->min }}</span>
                                    <span class="text-slate-400">&rarr;</span>
                                    <span>{{ $serialRange->max }}</span>
                                </div>
                            @else
                                <span class="text-xs font-mono text-slate-400">-</span>
                            @endif
                        </div>

                        <div class="pl-2">
                            <a href="{{ route('inventory.iccids') }}" class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold bg-white/20 hover:bg-white/30 text-white transition-all flex items-center gap-1" title="Lihat semua serial tanpa filter konteks">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                <span>Reset Filter</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- KPI Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- 1. Total Unit Terdaftar -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Serial Terdata</span>
                    <span class="p-1 rounded-lg bg-slate-100 text-slate-700">
                        <i data-lucide="scan-barcode" class="w-3.5 h-3.5"></i>
                    </span>
                </div>
                <div class="text-2xl font-extrabold font-mono text-slate-900">
                    {{ number_format($kpi['total_units'], 0, ',', '.') }}
                    <span class="text-xs font-normal text-slate-400">unit</span>
                </div>
                <span class="text-[10px] text-slate-400 block mt-1">Total nomor fisik tercatat</span>
            </div>

            <!-- 2. Tersedia (AVAILABLE) -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">Tersedia di Rak</span>
                    <span class="p-1 rounded-lg bg-emerald-100 text-emerald-700">
                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                    </span>
                </div>
                <div class="text-2xl font-extrabold font-mono text-emerald-600">
                    {{ number_format($kpi['available'], 0, ',', '.') }}
                    <span class="text-xs font-normal text-slate-400">unit</span>
                </div>
                <span class="text-[10px] text-slate-400 block mt-1">Status AVAILABLE siap order</span>
            </div>

            <!-- 3. Terpesan / Siap Kirim (RESERVED) -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] font-bold text-amber-700 uppercase tracking-wider">Terpesan / Alokasi</span>
                    <span class="p-1 rounded-lg bg-amber-100 text-amber-700">
                        <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                    </span>
                </div>
                <div class="text-2xl font-extrabold font-mono text-amber-600">
                    {{ number_format($kpi['reserved'], 0, ',', '.') }}
                    <span class="text-xs font-normal text-slate-400">unit</span>
                </div>
                <span class="text-[10px] text-slate-400 block mt-1">Teralokasi SO / Delivery kanvas</span>
            </div>

            <!-- 4. Terjual (SOLD) -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">Terjual ke Outlet</span>
                    <span class="p-1 rounded-lg bg-slate-100 text-slate-600">
                        <i data-lucide="store" class="w-3.5 h-3.5"></i>
                    </span>
                </div>
                <div class="text-2xl font-extrabold font-mono text-slate-700">
                    {{ number_format($kpi['sold'], 0, ',', '.') }}
                    <span class="text-xs font-normal text-slate-400">unit</span>
                </div>
                <span class="text-[10px] text-slate-400 block mt-1">Sudah terdistribusi ke toko</span>
            </div>
        </div>

        <!-- Quick Status Filter Bar (Pills) -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1">
            <span class="text-xs font-bold text-slate-500 mr-1 flex items-center gap-1.5 shrink-0">
                <i data-lucide="filter" class="w-3.5 h-3.5 text-slate-400"></i> Status Fisik:
            </span>
            <a href="{{ route('inventory.iccids', array_merge(request()->query(), ['status' => 'AVAILABLE'])) }}" 
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shrink-0 {{ $status === 'AVAILABLE' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                <span class="w-2 h-2 rounded-full {{ $status === 'AVAILABLE' ? 'bg-white' : 'bg-emerald-500' }}"></span>
                <span>Tersedia di Gudang (AVAILABLE)</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $status === 'AVAILABLE' ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $kpi['available'] }}</span>
            </a>
            <a href="{{ route('inventory.iccids', array_merge(request()->query(), ['status' => 'ALL'])) }}" 
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shrink-0 {{ ($status === 'ALL' || empty($status)) ? 'bg-slate-900 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                <span>Semua Riwayat Serial</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($status === 'ALL' || empty($status)) ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $kpi['total_units'] }}</span>
            </a>
            <a href="{{ route('inventory.iccids', array_merge(request()->query(), ['status' => 'SOLD'])) }}" 
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shrink-0 {{ $status === 'SOLD' ? 'bg-slate-700 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                <span class="w-2 h-2 rounded-full {{ $status === 'SOLD' ? 'bg-white' : 'bg-slate-400' }}"></span>
                <span>Laku Terjual ke Toko (SOLD)</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $status === 'SOLD' ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $kpi['sold'] }}</span>
            </a>
        </div>

        <!-- ============================================== -->
        <!-- FILTER FORM & SEARCH BAR                       -->
        <!-- ============================================== -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <form action="{{ route('inventory.iccids') }}" method="GET" class="p-4 border-b border-slate-100 bg-slate-50/50">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                    <!-- Filter Gudang -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Gudang Penyimpanan</label>
                        <select name="warehouse_id" class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-slate-900 focus:border-slate-900">
                            <option value="ALL">-- Semua Gudang --</option>
                            @foreach ($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ (string)$warehouseId === (string)$wh->id ? 'selected' : '' }}>
                                    [{{ $wh->type }}] {{ $wh->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Produk (Hanya Tracking ICCID) -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Pilih Produk Serial</label>
                        <select name="product_id" class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-slate-900 focus:border-slate-900">
                            <option value="ALL">-- Semua Produk Serial --</option>
                            @foreach ($products as $p)
                                <option value="{{ $p->id }}" {{ (string)$productId === (string)$p->id ? 'selected' : '' }}>
                                    {{ $p->name }} [{{ $p->sku }}]
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Status -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Status Serial</label>
                        <select name="status" class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-slate-900 focus:border-slate-900">
                            <option value="ALL" {{ $status === 'ALL' ? 'selected' : '' }}>-- Semua Status --</option>
                            <option value="AVAILABLE" {{ $status === 'AVAILABLE' ? 'selected' : '' }}>AVAILABLE (Tersedia)</option>
                            <option value="RESERVED" {{ $status === 'RESERVED' ? 'selected' : '' }}>RESERVED (Terpesan)</option>
                            <option value="SOLD" {{ $status === 'SOLD' ? 'selected' : '' }}>SOLD (Terjual)</option>
                        </select>
                    </div>

                    <!-- Search Input & Submit -->
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <input type="text" name="q" value="{{ $search }}" placeholder="Cari nomor ICCID..." class="w-full pl-8 pr-3 py-2 text-xs bg-white border border-slate-300 rounded-xl focus:ring-slate-900 focus:border-slate-900 font-mono">
                            <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5"></i>
                        </div>
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white shadow-xs transition-all shrink-0">
                            Filter
                        </button>
                        @if ($warehouseId || $productId || ($status && $status !== 'ALL') || $search)
                            <a href="{{ route('inventory.iccids') }}" class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-slate-100 transition-colors shrink-0" title="Reset Filter">
                                <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>

            <!-- ============================================== -->
            <!-- TABEL DATA SERIAL ICCID                        -->
            <!-- ============================================== -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                            <th class="py-3 px-4 w-12 text-center">No</th>
                            <th class="py-3 px-4">Nomor Serial ICCID</th>
                            <th class="py-3 px-4">Gudang Penyimpanan</th>
                            <th class="py-3 px-4">Nama Produk & SKU</th>
                            <th class="py-3 px-4 text-center">Status Fisik</th>
                            <th class="py-3 px-4">Dokumen Masuk / Terakhir</th>
                            <th class="py-3 px-4">Toko / Outlet Tujuan</th>
                            <th class="py-3 px-4 text-right">Terakhir Update</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($iccids as $idx => $item)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3 px-4 text-center text-slate-400 font-mono text-[11px]">
                                    {{ $iccids->firstItem() + $idx }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <span class="p-1 rounded bg-purple-50 text-purple-700">
                                            <i data-lucide="scan-barcode" class="w-3.5 h-3.5"></i>
                                        </span>
                                        <span class="font-mono font-extrabold text-sm text-slate-900 tracking-wide select-all">
                                            {{ $item->iccid_number }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-bold text-slate-800 block">{{ $item->warehouse_name ?? 'Transit / Di Luar Gudang' }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono uppercase">{{ $item->warehouse_type ?? '-' }} &bull; {{ $item->tap_name ?? '-' }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-bold text-slate-900 block">{{ $item->product_name }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $item->sku }}</span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if ($item->status === 'AVAILABLE')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            <i data-lucide="check" class="w-3 h-3"></i> AVAILABLE
                                        </span>
                                    @elseif ($item->status === 'RESERVED')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                            <i data-lucide="clock" class="w-3 h-3"></i> RESERVED
                                        </span>
                                    @elseif ($item->status === 'SOLD')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                            <i data-lucide="check-check" class="w-3 h-3"></i> SOLD
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                            {{ $item->status }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-mono text-xs">
                                    @if ($item->last_doc_number)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-800 mr-1">{{ $item->last_doc_type }}</span>
                                        <span class="font-bold text-slate-800">{{ $item->last_doc_number }}</span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    @if ($item->customer_name)
                                        <span class="font-bold text-slate-800 block">{{ $item->customer_name }}</span>
                                    @else
                                        <span class="text-slate-400 text-[11px] italic">Di Gudang (Internal)</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right text-slate-500 whitespace-nowrap text-[11px]">
                                    {{ date('d/m/Y H:i', strtotime($item->updated_at ?? $item->created_at)) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-2">
                                        <i data-lucide="inbox" class="w-6 h-6"></i>
                                    </div>
                                    <p class="text-xs font-semibold">Tidak ada nomor serial ICCID yang cocok dengan filter pencarian.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <div class="p-4 border-t border-slate-100 bg-slate-50/50 flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs text-slate-500">
                <div>
                    Menampilkan <strong>{{ $iccids->firstItem() ?? 0 }}</strong> sampai <strong>{{ $iccids->lastItem() ?? 0 }}</strong> dari <strong>{{ number_format($iccids->total(), 0, ',', '.') }}</strong> nomor serial
                </div>
                <div>
                    {{ $iccids->links() }}
                </div>
            </div>
        </div>

    </div>
</x-app-layout>

