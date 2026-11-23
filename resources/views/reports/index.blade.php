<x-app-layout>
    <x-slot name="title">Pusat Laporan & Penarikan Data (Excel & PDF)</x-slot>
    <x-slot name="headerTitle">Pusat Laporan & Ekspor Data</x-slot>
    <x-slot name="badge">Reports & Analytics</x-slot>

    <div>
        <!-- Top Header Banner -->
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Pusat Laporan Operasional & Keuangan</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">PT WAHANA PUTRA YUDHA</span>
                </h2>
                <p class="text-xs text-slate-500">Penarikan data rekapitulasi pengadaan, mutasi stok, konversi repack, penjualan, dan keuangan dalam format Excel & PDF</p>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- PILIHAN MODUL LAPORAN (TABS PILLS)             -->
        <!-- ============================================== -->
        <div class="mb-6 bg-white p-2 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-1.5 overflow-x-auto">
            <a href="{{ route('reports.index', array_merge(request()->all(), ['type' => 'SALES'])) }}"
               class="px-3.5 py-2 rounded-xl text-xs font-extrabold flex items-center gap-1.5 shrink-0 transition-all {{ $reportType === 'SALES' ? 'bg-rose-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                <span>1. Penjualan (SO & GD)</span>
            </a>

            <a href="{{ route('reports.index', array_merge(request()->all(), ['type' => 'PO_GR'])) }}"
               class="px-3.5 py-2 rounded-xl text-xs font-extrabold flex items-center gap-1.5 shrink-0 transition-all {{ $reportType === 'PO_GR' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                <i data-lucide="truck" class="w-4 h-4"></i>
                <span>2. Pengadaan (PO & GR)</span>
            </a>

            <a href="{{ route('reports.index', array_merge(request()->all(), ['type' => 'SM'])) }}"
               class="px-3.5 py-2 rounded-xl text-xs font-extrabold flex items-center gap-1.5 shrink-0 transition-all {{ $reportType === 'SM' ? 'bg-cyan-700 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                <i data-lucide="arrow-left-right" class="w-4 h-4"></i>
                <span>3. Mutasi Gudang (SM)</span>
            </a>

            <a href="{{ route('reports.index', array_merge(request()->all(), ['type' => 'REPACK'])) }}"
               class="px-3.5 py-2 rounded-xl text-xs font-extrabold flex items-center gap-1.5 shrink-0 transition-all {{ $reportType === 'REPACK' ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                <i data-lucide="repeat" class="w-4 h-4"></i>
                <span>4. Repack (RO & RI)</span>
            </a>

            <a href="{{ route('reports.index', array_merge(request()->all(), ['type' => 'AR'])) }}"
               class="px-3.5 py-2 rounded-xl text-xs font-extrabold flex items-center gap-1.5 shrink-0 transition-all {{ $reportType === 'AR' ? 'bg-purple-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                <i data-lucide="receipt" class="w-4 h-4"></i>
                <span>5. Piutang Usaha (AR)</span>
            </a>

            <a href="{{ route('reports.index', array_merge(request()->all(), ['type' => 'CASH_BANK'])) }}"
               class="px-3.5 py-2 rounded-xl text-xs font-extrabold flex items-center gap-1.5 shrink-0 transition-all {{ $reportType === 'CASH_BANK' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                <i data-lucide="landmark" class="w-4 h-4"></i>
                <span>6. Kas & Bank (TTK & BBM)</span>
            </a>

            <a href="{{ route('reports.index', array_merge(request()->all(), ['type' => 'STOCK'])) }}"
               class="px-3.5 py-2 rounded-xl text-xs font-extrabold flex items-center gap-1.5 shrink-0 transition-all {{ $reportType === 'STOCK' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                <i data-lucide="boxes" class="w-4 h-4"></i>
                <span>7. Mutasi Stok (Awal/In/Out/Akhir)</span>
            </a>
        </div>

        <!-- ============================================== -->
        <!-- FILTER FORM & ACTION BUTTONS                   -->
        <!-- ============================================== -->
        <div class="mb-6 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs"
             x-data="reportFilterComponent()">
            <form method="GET" action="{{ route('reports.index') }}" class="space-y-4">
                <input type="hidden" name="type" value="{{ $reportType }}">

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5 items-end">
                    <!-- 1. SINGLE-FIELD SMART DATE RANGE PICKER -->
                    <div>
                        <x-filter-date-range
                            :standalone="false"
                            :start-date="$startDate"
                            :end-date="$endDate"
                            label="Periode Tanggal" />
                    </div>

                    <!-- 2. FILTER AREA -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Area</label>
                        <select name="area_id"
                                x-model="selectedArea"
                                @change="onAreaChange()"
                                class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:border-rose-500 cursor-pointer shadow-2xs">
                            <option value="">-- Semua Area --</option>
                            @foreach($areas as $ar)
                                <option value="{{ $ar->id }}" {{ ($areaId ?? '') == $ar->id ? 'selected' : '' }}>
                                    {{ $ar->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 3. FILTER CLUSTER -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Cluster</label>
                        <select name="cluster_id"
                                x-model="selectedCluster"
                                @change="onClusterChange()"
                                class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:border-rose-500 cursor-pointer shadow-2xs">
                            <option value="">-- Semua Cluster --</option>
                            <template x-for="c in clusterOptions" :key="c.id">
                                <option :value="String(c.id)" :selected="String(c.id) === String(selectedCluster)" x-text="c.name"></option>
                            </template>
                        </select>
                    </div>

                    <!-- 4. FILTER TAP (SEARCHABLE SPINNER) -->
                    <div class="relative" @click.outside="tapDropdownOpen = false; syncTapDisplay()">
                        <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                            <span>Kantor TAP</span>
                            <span class="text-[10px] text-slate-400 font-normal">Ketik untuk cari</span>
                        </label>
                        <input type="hidden" name="tap_id" :value="selectedTap">
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="building-2" class="w-3.5 h-3.5"></i>
                            </div>
                            <input type="text"
                                   x-model="tapSearchText"
                                   @focus="tapDropdownOpen = true"
                                   @input="tapDropdownOpen = true"
                                   placeholder="-- Semua Kantor TAP --"
                                   class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl pl-8 pr-7 py-2.5 text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-rose-500 shadow-2xs cursor-pointer">
                            <button type="button"
                                     x-show="selectedTap && selectedTap !== 'ALL'"
                                     @click="clearTap()"
                                     class="absolute right-2 text-slate-400 hover:text-rose-600 cursor-pointer p-0.5"
                                     title="Reset TAP">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>

                        <!-- Dropdown List Hasil Pencarian TAP -->
                        <div x-show="tapDropdownOpen"
                             x-cloak
                             class="absolute z-50 left-0 right-0 mt-1 max-h-56 overflow-y-auto bg-white border border-slate-200 rounded-xl shadow-xl divide-y divide-slate-100">
                            <button type="button"
                                    @click="selectTap(null)"
                                    class="w-full text-left px-3 py-2 hover:bg-rose-50 flex items-center justify-between gap-2 transition-colors cursor-pointer"
                                    :class="selectedTap === 'ALL' ? 'bg-rose-50/70 font-bold text-rose-900' : 'text-slate-700'">
                                <span class="text-xs">-- Semua TAP --</span>
                                <svg x-show="selectedTap === 'ALL'" class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </button>
                            <template x-for="t in filteredTapOptions" :key="t.id">
                                <button type="button"
                                        @click="selectTap(t)"
                                        class="w-full text-left px-3 py-2 hover:bg-rose-50 flex items-center justify-between gap-2 transition-colors cursor-pointer"
                                        :class="String(selectedTap) === String(t.id) ? 'bg-rose-50/70 font-bold text-rose-900' : 'text-slate-700'">
                                    <div>
                                        <span class="text-xs block font-bold" x-text="t.name"></span>
                                        <span class="text-[10px] text-slate-400 font-mono" x-text="t.code || ''"></span>
                                    </div>
                                    <svg x-show="String(selectedTap) === String(t.id)" class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                            </template>
                            <div x-show="filteredTapOptions.length === 0" class="px-3 py-3 text-center text-slate-400 text-xs italic">
                                Kantor TAP tidak ditemukan.
                            </div>
                        </div>
                    </div>

                    <!-- 5. FILTER GUDANG (SEARCHABLE SPINNER) -->
                    <div class="relative" @click.outside="warehouseDropdownOpen = false; syncWarehouseDisplay()">
                        <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                            <span>Gudang @if($reportType === 'PO_GR') <span class="text-emerald-700 font-extrabold">(Hanya MAIN)</span> @endif</span>
                            <span class="text-[10px] text-slate-400 font-normal">Ketik untuk cari</span>
                        </label>
                        <input type="hidden" name="warehouse_id" :value="selectedWarehouse">
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="warehouse" class="w-3.5 h-3.5"></i>
                            </div>
                            <input type="text"
                                   x-model="warehouseSearchText"
                                   @focus="warehouseDropdownOpen = true"
                                   @input="warehouseDropdownOpen = true"
                                   placeholder="-- Semua Gudang @if($reportType === 'PO_GR') MAIN @endif --"
                                   class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl pl-8 pr-7 py-2.5 text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-rose-500 shadow-2xs cursor-pointer">
                            <button type="button"
                                    x-show="selectedWarehouse && selectedWarehouse !== 'ALL'"
                                    @click="clearWarehouse()"
                                    class="absolute right-2 text-slate-400 hover:text-rose-600 cursor-pointer p-0.5"
                                    title="Reset Gudang">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>

                        <!-- Dropdown List Hasil Pencarian Gudang -->
                        <div x-show="warehouseDropdownOpen"
                             x-cloak
                             class="absolute z-50 left-0 right-0 mt-1 max-h-56 overflow-y-auto bg-white border border-slate-200 rounded-xl shadow-xl divide-y divide-slate-100">
                            <button type="button"
                                    @click="selectWarehouse(null)"
                                    class="w-full text-left px-3 py-2 hover:bg-rose-50 flex items-center justify-between gap-2 transition-colors cursor-pointer"
                                    :class="selectedWarehouse === 'ALL' ? 'bg-rose-50/70 font-bold text-rose-900' : 'text-slate-700'">
                                <span class="text-xs">-- Semua Gudang @if($reportType === 'PO_GR') MAIN @endif --</span>
                                <svg x-show="selectedWarehouse === 'ALL'" class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </button>
                            <template x-for="w in filteredWarehouseOptions" :key="w.id">
                                <button type="button"
                                        @click="selectWarehouse(w)"
                                        class="w-full text-left px-3 py-2 hover:bg-rose-50 flex items-center justify-between gap-2 transition-colors cursor-pointer"
                                        :class="String(selectedWarehouse) === String(w.id) ? 'bg-rose-50/70 font-bold text-rose-900' : 'text-slate-700'">
                                    <div>
                                        <span class="text-xs block font-bold text-slate-900" x-text="w.name"></span>
                                        <span class="text-[10px] text-slate-400 font-mono" x-text="'[' + w.type + '] ' + (w.code || '')"></span>
                                    </div>
                                    <svg x-show="String(selectedWarehouse) === String(w.id)" class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                            </template>
                            <div x-show="filteredWarehouseOptions.length === 0" class="px-3 py-3 text-center text-slate-400 text-xs italic">
                                Gudang tidak ditemukan.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-3 border-t border-slate-100">
                    <div class="flex items-center gap-2">
                        <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-xs transition-all cursor-pointer">
                            <i data-lucide="filter" class="w-4 h-4 text-emerald-400"></i>
                            <span>Terapkan Filter Laporan</span>
                        </button>

                        <a href="{{ route('reports.index', ['type' => $reportType]) }}"
                           class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold flex items-center gap-1.5 transition-all cursor-pointer">
                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5 text-slate-500"></i>
                            <span>Reset Filter</span>
                        </a>
                    </div>

                    <!-- Export Actions -->
                    <div class="flex items-center gap-2">
                        @if($reportType === 'PO_GR')
                            <!-- Dropdown Export Excel PO-GR (Rekap & Detail Item) -->
                            <div class="relative inline-block text-left" x-data="{ openExcel: false }">
                                <button type="button" @click="openExcel = !openExcel" @click.outside="openExcel = false"
                                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-extrabold flex items-center gap-1.5 shadow-sm transition-all cursor-pointer">
                                    <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                                    <span>Download Excel</span>
                                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 ml-0.5"></i>
                                </button>
                                <div x-show="openExcel" x-cloak class="absolute right-0 mt-1.5 w-60 rounded-xl bg-white shadow-xl border border-slate-200 py-1.5 z-50 text-xs">
                                    <a href="{{ route('reports.export-excel', array_merge(request()->all(), ['format' => 'summary'])) }}" 
                                       class="px-3.5 py-2.5 hover:bg-emerald-50 text-slate-800 flex items-start gap-2.5 transition-colors">
                                        <i data-lucide="file-text" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
                                        <div>
                                            <span class="font-bold block text-slate-900">Rekap Ringkas (Header)</span>
                                            <span class="text-[10px] text-slate-500">1 baris per PO (Nominal & Status)</span>
                                        </div>
                                    </a>
                                    <a href="{{ route('reports.export-excel', array_merge(request()->all(), ['format' => 'detailed'])) }}" 
                                       class="px-3.5 py-2.5 hover:bg-emerald-50 text-slate-800 flex items-start gap-2.5 transition-colors border-t border-slate-100">
                                        <i data-lucide="table-properties" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
                                        <div>
                                            <span class="font-bold block text-slate-900">Rincian Lengkap (Per Item)</span>
                                            <span class="text-[10px] text-slate-500">Header + Detail SKU barang lengkap</span>
                                        </div>
                                    </a>
                                </div>
                            </div>

                            <!-- Dropdown Cetak PDF PO-GR (Rekap & Detail Item) -->
                            <div class="relative inline-block text-left" x-data="{ openPdf: false }">
                                <button type="button" @click="openPdf = !openPdf" @click.outside="openPdf = false"
                                        class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-extrabold flex items-center gap-1.5 shadow-sm transition-all cursor-pointer">
                                    <i data-lucide="printer" class="w-4 h-4"></i>
                                    <span>Cetak PDF</span>
                                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 ml-0.5"></i>
                                </button>
                                <div x-show="openPdf" x-cloak class="absolute right-0 mt-1.5 w-64 rounded-xl bg-white shadow-xl border border-slate-200 py-1.5 z-50 text-xs">
                                    <a href="{{ route('reports.print-pdf', array_merge(request()->all(), ['format' => 'summary'])) }}" target="_blank"
                                       class="px-3.5 py-2.5 hover:bg-rose-50 text-slate-800 flex items-start gap-2.5 transition-colors">
                                        <i data-lucide="file-text" class="w-4 h-4 text-rose-600 shrink-0 mt-0.5"></i>
                                        <div>
                                            <span class="font-bold block text-slate-900">Cetak Rekap Ringkas</span>
                                            <span class="text-[10px] text-slate-500">Tabel 1 halaman landscape</span>
                                        </div>
                                    </a>
                                    <a href="{{ route('reports.print-pdf', array_merge(request()->all(), ['format' => 'detailed'])) }}" target="_blank"
                                       class="px-3.5 py-2.5 hover:bg-rose-50 text-slate-800 flex items-start gap-2.5 transition-colors border-t border-slate-100">
                                        <i data-lucide="layers" class="w-4 h-4 text-rose-600 shrink-0 mt-0.5"></i>
                                        <div>
                                            <span class="font-bold block text-slate-900">Cetak Rincian Lengkap Resmi</span>
                                            <span class="text-[10px] text-slate-500">Format berjenjang per PO + rincian item</span>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        @elseif($reportType === 'SM')
                            <!-- Dropdown Export Excel SM (Rekap & Detail Item) -->
                            <div class="relative inline-block text-left" x-data="{ openExcel: false }">
                                <button type="button" @click="openExcel = !openExcel" @click.outside="openExcel = false"
                                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-extrabold flex items-center gap-1.5 shadow-sm transition-all cursor-pointer">
                                    <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                                    <span>Download Excel</span>
                                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 ml-0.5"></i>
                                </button>
                                <div x-show="openExcel" x-cloak class="absolute right-0 mt-1.5 w-64 rounded-xl bg-white shadow-xl border border-slate-200 py-1.5 z-50 text-xs">
                                    <a href="{{ route('reports.export-excel', array_merge(request()->all(), ['format' => 'summary'])) }}" 
                                       class="px-3.5 py-2.5 hover:bg-emerald-50 text-slate-800 flex items-start gap-2.5 transition-colors">
                                        <i data-lucide="file-text" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
                                        <div>
                                            <span class="font-bold block text-slate-900">Rekap Ringkas (Header)</span>
                                            <span class="text-[10px] text-slate-500">1 baris per Dokumen SM (Rute & Total Qty)</span>
                                        </div>
                                    </a>
                                    <a href="{{ route('reports.export-excel', array_merge(request()->all(), ['format' => 'detailed'])) }}" 
                                       class="px-3.5 py-2.5 hover:bg-emerald-50 text-slate-800 flex items-start gap-2.5 transition-colors border-t border-slate-100">
                                        <i data-lucide="table-properties" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
                                        <div>
                                            <span class="font-bold block text-slate-900">Rincian Lengkap (Per Item)</span>
                                            <span class="text-[10px] text-slate-500">Header + Detail SKU barang dipindahkan</span>
                                        </div>
                                    </a>
                                </div>
                            </div>

                            <!-- Dropdown Cetak PDF SM (Rekap & Detail Item) -->
                            <div class="relative inline-block text-left" x-data="{ openPdf: false }">
                                <button type="button" @click="openPdf = !openPdf" @click.outside="openPdf = false"
                                        class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-extrabold flex items-center gap-1.5 shadow-sm transition-all cursor-pointer">
                                    <i data-lucide="printer" class="w-4 h-4"></i>
                                    <span>Cetak PDF</span>
                                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 ml-0.5"></i>
                                </button>
                                <div x-show="openPdf" x-cloak class="absolute right-0 mt-1.5 w-64 rounded-xl bg-white shadow-xl border border-slate-200 py-1.5 z-50 text-xs">
                                    <a href="{{ route('reports.print-pdf', array_merge(request()->all(), ['format' => 'summary'])) }}" target="_blank"
                                       class="px-3.5 py-2.5 hover:bg-rose-50 text-slate-800 flex items-start gap-2.5 transition-colors">
                                        <i data-lucide="file-text" class="w-4 h-4 text-rose-600 shrink-0 mt-0.5"></i>
                                        <div>
                                            <span class="font-bold block text-slate-900">Cetak Rekap Ringkas</span>
                                            <span class="text-[10px] text-slate-500">Tabel ringkasan landscape 1 baris per SM</span>
                                        </div>
                                    </a>
                                    <a href="{{ route('reports.print-pdf', array_merge(request()->all(), ['format' => 'detailed'])) }}" target="_blank"
                                       class="px-3.5 py-2.5 hover:bg-rose-50 text-slate-800 flex items-start gap-2.5 transition-colors border-t border-slate-100">
                                        <i data-lucide="layers" class="w-4 h-4 text-rose-600 shrink-0 mt-0.5"></i>
                                        <div>
                                            <span class="font-bold block text-slate-900">Cetak Rincian Lengkap Resmi</span>
                                            <span class="text-[10px] text-slate-500">Format berjenjang per SM + rincian item</span>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('reports.export-excel', request()->all()) }}" 
                               class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-extrabold flex items-center gap-1.5 shadow-sm transition-all">
                                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                                <span>Download Excel / CSV</span>
                            </a>

                            <a href="{{ route('reports.print-pdf', request()->all()) }}" 
                               target="_blank"
                               class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-extrabold flex items-center gap-1.5 shadow-sm transition-all">
                                <i data-lucide="printer" class="w-4 h-4"></i>
                                <span>Cetak PDF / Print View</span>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- ============================================== -->
        <!-- REPORT TITLE & EXECUTIVE SUMMARY BANNER        -->
        <!-- ============================================== -->
        <div class="mb-4 p-4 rounded-2xl bg-white border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <span class="text-[10px] font-mono font-extrabold uppercase px-2 py-0.5 rounded bg-slate-100 text-slate-600">
                    LAPORAN RESMI
                </span>
                <h3 class="font-extrabold text-slate-900 text-sm mt-1">{{ $title }}</h3>
                <p class="text-xs text-slate-500">Periode: {{ date('d M Y', strtotime($startDate)) }} s/d {{ date('d M Y', strtotime($endDate)) }}</p>
            </div>

            <div class="flex items-center gap-6 text-right flex-wrap">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 block uppercase">Jumlah Transaksi:</span>
                    <span class="font-mono text-base font-extrabold text-slate-900">{{ number_format($summary['total_records']) }}</span>
                </div>

                @if($reportType === 'PO_GR')
                    @if(isset($summary['total_po_qty']))
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 block uppercase">Fisik Dipesan (PO):</span>
                            <span class="font-mono text-base font-extrabold text-slate-800">
                                {{ number_format($summary['total_po_qty']) }} pcs
                            </span>
                        </div>
                    @endif
                    @if(isset($summary['total_gr_qty']))
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 block uppercase">Fisik Diterima (GR):</span>
                            <span class="font-mono text-base font-extrabold text-emerald-700">
                                {{ number_format($summary['total_gr_qty']) }} pcs
                            </span>
                        </div>
                    @endif
                @endif

                @if($reportType === 'SM')
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 block uppercase">Total Fisik Dipindahkan:</span>
                        <span class="font-mono text-base font-extrabold text-cyan-700">
                            {{ number_format($summary['total_amount'] ?? 0) }} pcs
                        </span>
                    </div>
                    @if(isset($summary['total_items_count']))
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 block uppercase">Total Baris Item:</span>
                            <span class="font-mono text-base font-extrabold text-slate-700">
                                {{ number_format($summary['total_items_count']) }} baris
                            </span>
                        </div>
                    @endif
                @elseif(isset($summary['total_amount']))
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 block uppercase">Total Nilai / Volume:</span>
                        <span class="font-mono text-base font-extrabold text-rose-600">
                            {{ in_array($reportType, ['STOCK']) ? number_format($summary['total_amount']) . ' pcs' : 'Rp ' . number_format($summary['total_amount'], 0, ',', '.') }}
                        </span>
                    </div>
                @endif
            </div>
        </div>

        <!-- ============================================== -->
        <!-- TABLE PREVIEW OF REPORT                        -->
        <!-- ============================================== -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden mb-8">
            <div class="overflow-x-auto">
                @if($reportType === 'SALES')
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">No. SO</th>
                                <th class="py-3 px-4">Tanggal</th>
                                <th class="py-3 px-4">Toko / Outlet</th>
                                <th class="py-3 px-4">Sales Kanvaser</th>
                                <th class="py-3 px-4">Gudang</th>
                                <th class="py-3 px-4 text-center">Channel</th>
                                <th class="py-3 px-4">No. GD (Surat Jalan)</th>
                                <th class="py-3 px-4">No. Faktur AR</th>
                                <th class="py-3 px-4 text-center">Status SO</th>
                                <th class="py-3 px-4 text-right">Total Penjualan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($records as $r)
                                <tr class="hover:bg-slate-50">
                                    <td class="py-3 px-4 font-mono font-bold text-rose-800">{{ $r->doc_number }}</td>
                                    <td class="py-3 px-4 text-slate-600">{{ date('d M Y', strtotime($r->order_date)) }}</td>
                                    <td class="py-3 px-4 font-bold text-slate-900">{{ $r->customer_name }}</td>
                                    <td class="py-3 px-4 text-slate-800 font-semibold">{{ $r->sales_name }}</td>
                                    <td class="py-3 px-4 text-slate-600">{{ $r->warehouse_name }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $r->channel === 'MOBILE_SALES' ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-700' }}">
                                            {{ $r->channel }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 font-mono text-slate-600">{{ $r->gd_number ?? '-' }}</td>
                                    <td class="py-3 px-4 font-mono text-amber-800 font-bold">{{ $r->ar_number ?? '-' }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                            {{ $r->status }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-extrabold text-slate-900">
                                        Rp {{ number_format($r->grand_total, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="py-8 text-center text-slate-400">Tidak ada transaksi pada filter periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                @elseif($reportType === 'PO_GR')
                    <div x-data="reportPoGrTable()">
                        <!-- Toolbar Quick Expand / Collapse -->
                        <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded font-mono font-bold bg-emerald-100 text-emerald-800 text-[10px]">
                                    PO &rarr; GR
                                </span>
                                <span class="text-slate-600 font-medium">
                                    Klik tombol <strong class="text-slate-900 font-bold">[+]</strong> atau baris tabel untuk melihat rincian produk yang dipesan &amp; diterima.
                                </span>
                            </div>

                            @if(count($records) > 0)
                                <div class="flex items-center gap-2 shrink-0">
                                    <button type="button" @click="expandAll()"
                                            class="px-2.5 py-1 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 rounded-lg text-xs font-bold flex items-center gap-1 shadow-2xs transition-all cursor-pointer">
                                        <i data-lucide="chevrons-down" class="w-3.5 h-3.5 text-emerald-600"></i>
                                        <span>Buka Semua Item</span>
                                    </button>
                                    <button type="button" @click="collapseAll()"
                                            class="px-2.5 py-1 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 rounded-lg text-xs font-bold flex items-center gap-1 shadow-2xs transition-all cursor-pointer">
                                        <i data-lucide="chevrons-up" class="w-3.5 h-3.5 text-slate-400"></i>
                                        <span>Tutup Semua</span>
                                    </button>
                                </div>
                            @endif
                        </div>

                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                                <tr>
                                    <th class="py-3 px-3 w-10 text-center"></th>
                                    <th class="py-3 px-4">No. PO</th>
                                    <th class="py-3 px-4">Waktu PO &amp; Pembuat</th>
                                    <th class="py-3 px-4">Vendor &amp; Wilayah</th>
                                    <th class="py-3 px-4">No. GR &amp; Waktu Terima</th>
                                    <th class="py-3 px-4">Surat Jalan (DO)</th>
                                    <th class="py-3 px-4">Gudang Tujuan</th>
                                    <th class="py-3 px-4 text-center">Qty (PO vs GR)</th>
                                    <th class="py-3 px-4 text-center">Status PO</th>
                                    <th class="py-3 px-4 text-right">Nilai Pengadaan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                @forelse($records as $r)
                                    <!-- Baris Utama (Header Dokumen) -->
                                    <tr @click="toggleRow({{ $r->id }})"
                                        :class="expanded[{{ $r->id }}] ? 'bg-emerald-50/40' : 'hover:bg-slate-50'"
                                        class="cursor-pointer transition-colors">
                                        
                                        <!-- Tombol Toggle Expand -->
                                        <td class="py-3 px-3 text-center" @click.stop="toggleRow({{ $r->id }})">
                                            <button type="button"
                                                    class="w-6 h-6 rounded-lg flex items-center justify-center border transition-all cursor-pointer mx-auto"
                                                    :class="expanded[{{ $r->id }}] ? 'bg-emerald-600 text-white border-emerald-600 shadow-2xs' : 'bg-white text-slate-500 border-slate-300 hover:bg-slate-100'">
                                                <i data-lucide="chevron-right" class="w-3.5 h-3.5 transition-transform"
                                                   :class="expanded[{{ $r->id }}] ? 'rotate-90' : ''"></i>
                                            </button>
                                        </td>

                                        <!-- No. PO -->
                                        <td class="py-3 px-4 font-mono font-bold text-slate-900 whitespace-nowrap">
                                            <div class="flex items-center gap-1.5">
                                                <span class="px-1.5 py-0.5 rounded text-[10px] bg-slate-900 text-white font-mono">PO</span>
                                                <span>{{ $r->doc_number }}</span>
                                            </div>
                                        </td>

                                        <!-- Waktu PO & Pembuat -->
                                        <td class="py-3 px-4 whitespace-nowrap">
                                            <div class="flex flex-col">
                                                <span class="font-bold text-slate-800 flex items-center gap-1">
                                                    <i data-lucide="clock" class="w-3 h-3 text-slate-400"></i>
                                                    <span>{{ date('d M Y, H:i', strtotime($r->created_at ?? $r->order_date)) }}</span>
                                                </span>
                                                <span class="text-[11px] text-slate-500 mt-0.5">
                                                    Oleh: <strong class="text-slate-700">{{ $r->creator_name ?? 'Admin' }}</strong>
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Vendor & Wilayah -->
                                        <td class="py-3 px-4 whitespace-nowrap">
                                            <div class="flex flex-col">
                                                <span class="font-bold text-slate-900">{{ $r->vendor_display }}</span>
                                                <span class="text-[11px] text-slate-500">{{ $r->tap_name }} • {{ $r->cluster_name }}</span>
                                            </div>
                                        </td>

                                        <!-- No. GR & Waktu Terima -->
                                        <td class="py-3 px-4 whitespace-nowrap">
                                            @if($r->gr_number)
                                                <div class="flex flex-col">
                                                    <span class="font-mono font-bold text-emerald-800 flex items-center gap-1">
                                                        <span class="px-1 py-0.2 rounded text-[9px] bg-emerald-600 text-white font-mono">GR</span>
                                                        <span>{{ $r->gr_number }}</span>
                                                    </span>
                                                    <span class="text-[11px] text-slate-500 mt-0.5">
                                                        {{ $r->gr_created_at ? date('d M Y, H:i', strtotime($r->gr_created_at)) : ($r->received_date ? date('d M Y', strtotime($r->received_date)) : '-') }}
                                                        &bull; Petugas: <strong class="text-slate-700">{{ $r->receiver_name ?? 'Gudang' }}</strong>
                                                    </span>
                                                </div>
                                            @else
                                                <span class="text-slate-400 italic text-[11px] flex items-center gap-1">
                                                    <i data-lucide="clock" class="w-3 h-3"></i>
                                                    <span>Belum Diterima (GR)</span>
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Surat Jalan / No. DO Telkomsel -->
                                        <td class="py-3 px-4 whitespace-nowrap">
                                            @if($r->do_number)
                                                <div class="flex flex-col gap-0.5">
                                                    <span class="font-mono text-xs font-bold text-slate-800 bg-slate-100 px-2 py-0.5 rounded border border-slate-200 inline-block w-fit">
                                                        {{ $r->do_number }}
                                                    </span>
                                                    @if($r->do_file_path)
                                                        <a href="{{ asset('storage/' . $r->do_file_path) }}" target="_blank" @click.stop class="text-[10px] text-rose-600 hover:underline font-bold flex items-center gap-1">
                                                            <i data-lucide="file-text" class="w-3 h-3"></i>
                                                            <span>Lihat Berkas DO</span>
                                                        </a>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-slate-400 text-xs">-</span>
                                            @endif
                                        </td>

                                        <!-- Gudang Penerima -->
                                        <td class="py-3 px-4 text-slate-700 whitespace-nowrap">
                                            {{ $r->destination_warehouse ?? '-' }}
                                        </td>

                                        <!-- Qty (PO vs GR) -->
                                        <td class="py-3 px-4 text-center whitespace-nowrap">
                                            <div class="flex flex-col items-center gap-0.5">
                                                <span class="px-2 py-0.5 rounded text-[11px] font-mono bg-slate-100 text-slate-800 font-bold">
                                                    Pesan: {{ number_format($r->total_po_qty ?? 0) }} pcs
                                                </span>
                                                @if($r->gr_number)
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold {{ ($r->total_gr_qty ?? 0) >= ($r->total_po_qty ?? 0) ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                                        Masuk: {{ number_format($r->total_gr_qty ?? 0) }} pcs
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Status PO -->
                                        <td class="py-3 px-4 text-center whitespace-nowrap">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $r->status === 'RECEIVED' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : ($r->status === 'CANCELLED' ? 'bg-rose-100 text-rose-800 border border-rose-200' : 'bg-amber-100 text-amber-800 border border-amber-200') }}">
                                                {{ $r->status }}
                                            </span>
                                        </td>

                                        <!-- Nilai Pengadaan -->
                                        <td class="py-3 px-4 text-right font-mono font-extrabold text-emerald-700 whitespace-nowrap">
                                            Rp {{ number_format($r->total_amount, 0, ',', '.') }}
                                        </td>
                                    </tr>

                                    <!-- Sub-Baris Expandable (Rincian Item Produk) -->
                                    <tr x-show="expanded[{{ $r->id }}]" x-cloak class="bg-slate-50/80 border-b border-emerald-200">
                                        <td colspan="10" class="p-4 pl-12">
                                            <div class="bg-white rounded-xl border border-slate-200/90 shadow-sm p-4">
                                                <div class="flex items-center justify-between pb-2 mb-3 border-b border-slate-100 flex-wrap gap-2">
                                                    <div class="flex items-center gap-2">
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 flex items-center gap-1">
                                                            <i data-lucide="boxes" class="w-3 h-3"></i>
                                                            <span>RINCIAN BARANG PENGADAAN (PO: {{ $r->doc_number }})</span>
                                                        </span>
                                                        <span class="text-xs text-slate-500 font-medium">
                                                            Total {{ count($r->items ?? []) }} varian produk
                                                        </span>
                                                    </div>
                                                    <div class="text-xs text-slate-600 font-bold">
                                                        Total Qty PO: <span class="font-mono text-slate-900">{{ number_format($r->total_po_qty ?? 0) }} pcs</span>
                                                        @if($r->gr_number)
                                                            &bull; Qty Masuk GR: <span class="font-mono text-emerald-700">{{ number_format($r->total_gr_qty ?? 0) }} pcs</span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- Sub-Table Items -->
                                                <div class="overflow-x-auto">
                                                    <table class="w-full text-left text-xs border border-slate-100 rounded-lg overflow-hidden">
                                                        <thead class="bg-slate-100 text-slate-600 font-bold uppercase tracking-wider text-[10px]">
                                                            <tr>
                                                                <th class="py-2 px-3 w-10 text-center">No</th>
                                                                <th class="py-2 px-3">Kode SKU</th>
                                                                <th class="py-2 px-3">Nama Produk</th>
                                                                <th class="py-2 px-3">Tipe</th>
                                                                <th class="py-2 px-3 text-center">Qty Dipesan (PO)</th>
                                                                <th class="py-2 px-3 text-center">Qty Diterima Fisik (GR)</th>
                                                                <th class="py-2 px-3 text-center">Status Qty</th>
                                                                <th class="py-2 px-3 text-right">Harga Modal Satuan</th>
                                                                <th class="py-2 px-3 text-right">Subtotal</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-slate-100 font-medium">
                                                            @forelse($r->items as $iIdx => $item)
                                                                <tr class="hover:bg-slate-50">
                                                                    <td class="py-2 px-3 text-center text-slate-400 font-mono">{{ $iIdx + 1 }}</td>
                                                                    <td class="py-2 px-3 font-mono font-bold text-slate-700">{{ $item->sku }}</td>
                                                                    <td class="py-2 px-3 font-bold text-slate-900">{{ $item->product_name }}</td>
                                                                    <td class="py-2 px-3"><span class="px-2 py-0.5 rounded text-[10px] bg-slate-100 text-slate-700 font-semibold">{{ $item->product_type }}</span></td>
                                                                    <td class="py-2 px-3 text-center font-mono font-bold text-slate-800">{{ number_format($item->quantity) }} pcs</td>
                                                                    <td class="py-2 px-3 text-center font-mono font-bold {{ $r->gr_number ? 'text-emerald-700' : 'text-slate-400' }}">
                                                                        {{ $r->gr_number ? number_format($item->quantity_received) . ' pcs' : '-' }}
                                                                    </td>
                                                                    <td class="py-2 px-3 text-center">
                                                                        @if(!$r->gr_number)
                                                                            <span class="text-[10px] text-amber-600 font-bold">Menunggu GR</span>
                                                                        @elseif($item->quantity_received >= $item->quantity)
                                                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Lengkap</span>
                                                                        @else
                                                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">Kurang {{ number_format($item->quantity - $item->quantity_received) }} pcs</span>
                                                                        @endif
                                                                    </td>
                                                                    <td class="py-2 px-3 text-right font-mono text-slate-600">Rp {{ number_format($item->cost_price, 0, ',', '.') }}</td>
                                                                    <td class="py-2 px-3 text-right font-mono font-extrabold text-slate-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="9" class="py-3 text-center text-slate-400 italic">Tidak ada item tercatat pada dokumen ini.</td>
                                                                </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="py-8 text-center text-slate-400">Tidak ada transaksi pengadaan (PO - GR) pada filter periode ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                @elseif($reportType === 'SM')
                    <div x-data="reportSmTable()">
                        <!-- Toolbar Quick Expand / Collapse -->
                        <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded font-mono font-bold bg-cyan-100 text-cyan-800 text-[10px]">
                                    SM &bull; MUTASI ANTAR GUDANG
                                </span>
                                <span class="text-slate-600 font-medium">
                                    Klik tombol <strong class="text-slate-900 font-bold">[+]</strong> atau baris tabel untuk melihat rincian produk yang dipindahkan.
                                </span>
                            </div>

                            @if(count($records) > 0)
                                <div class="flex items-center gap-2 shrink-0">
                                    <button type="button" @click="expandAll()"
                                            class="px-2.5 py-1 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 rounded-lg text-xs font-bold flex items-center gap-1 shadow-2xs transition-all cursor-pointer">
                                        <i data-lucide="chevrons-down" class="w-3.5 h-3.5 text-cyan-600"></i>
                                        <span>Buka Semua Item</span>
                                    </button>
                                    <button type="button" @click="collapseAll()"
                                            class="px-2.5 py-1 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 rounded-lg text-xs font-bold flex items-center gap-1 shadow-2xs transition-all cursor-pointer">
                                        <i data-lucide="chevrons-up" class="w-3.5 h-3.5 text-slate-400"></i>
                                        <span>Tutup Semua</span>
                                    </button>
                                </div>
                            @endif
                        </div>

                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                                <tr>
                                    <th class="py-3 px-3 w-10 text-center"></th>
                                    <th class="py-3 px-4">No. SM</th>
                                    <th class="py-3 px-4">Waktu Mutasi &amp; Pembuat</th>
                                    <th class="py-3 px-4">Rute Gudang (Asal &rarr; Tujuan)</th>
                                    <th class="py-3 px-4">Wilayah Operasional</th>
                                    <th class="py-3 px-4 text-center">Total Varian</th>
                                    <th class="py-3 px-4 text-center">Total Qty Fisik</th>
                                    <th class="py-3 px-4 text-center">Status</th>
                                    <th class="py-3 px-4">Keterangan / Keperluan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                @forelse($records as $r)
                                    <!-- Baris Utama (Header Dokumen SM) -->
                                    <tr @click="toggleRow({{ $r->id }})"
                                        :class="expanded[{{ $r->id }}] ? 'bg-cyan-50/40' : 'hover:bg-slate-50'"
                                        class="cursor-pointer transition-colors">
                                        
                                        <!-- Tombol Toggle Expand -->
                                        <td class="py-3 px-3 text-center" @click.stop="toggleRow({{ $r->id }})">
                                            <button type="button"
                                                    class="w-6 h-6 rounded-lg flex items-center justify-center border transition-all cursor-pointer mx-auto"
                                                    :class="expanded[{{ $r->id }}] ? 'bg-cyan-600 text-white border-cyan-600 shadow-2xs' : 'bg-white text-slate-500 border-slate-300 hover:bg-slate-100'">
                                                <i data-lucide="chevron-right" class="w-3.5 h-3.5 transition-transform"
                                                   :class="expanded[{{ $r->id }}] ? 'rotate-90' : ''"></i>
                                            </button>
                                        </td>

                                        <!-- No. SM -->
                                        <td class="py-3 px-4 font-mono font-bold text-slate-900 whitespace-nowrap">
                                            <div class="flex items-center gap-1.5">
                                                <span class="px-1.5 py-0.5 rounded text-[10px] bg-cyan-700 text-white font-mono">SM</span>
                                                <span class="text-cyan-900">{{ $r->doc_number }}</span>
                                            </div>
                                        </td>

                                        <!-- Waktu Mutasi & Pembuat -->
                                        <td class="py-3 px-4 whitespace-nowrap">
                                            <div class="flex flex-col">
                                                <span class="font-bold text-slate-800 flex items-center gap-1">
                                                    <i data-lucide="calendar" class="w-3 h-3 text-slate-400"></i>
                                                    <span>{{ date('d M Y', strtotime($r->movement_date)) }}</span>
                                                    @if($r->created_at)
                                                        <span class="text-slate-400 font-normal text-[11px]">({{ date('H:i', strtotime($r->created_at)) }})</span>
                                                    @endif
                                                </span>
                                                <span class="text-[11px] text-slate-500 mt-0.5">
                                                    Oleh: <strong class="text-slate-700">{{ $r->creator_display }}</strong>
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Rute Gudang (Asal -> Tujuan) -->
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <div class="flex items-center gap-1">
                                                    <span class="font-semibold text-slate-800">{{ $r->source_wh }}</span>
                                                    <span class="text-[9px] px-1 py-0.2 rounded bg-slate-100 text-slate-600 font-bold uppercase">{{ $r->source_wh_type }}</span>
                                                </div>
                                                <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-cyan-600 shrink-0"></i>
                                                <div class="flex items-center gap-1">
                                                    <span class="font-bold text-rose-700">{{ $r->dest_wh }}</span>
                                                    <span class="text-[9px] px-1 py-0.2 rounded bg-rose-50 text-rose-700 font-bold uppercase">{{ $r->dest_wh_type }}</span>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Wilayah Operasional -->
                                        <td class="py-3 px-4 whitespace-nowrap">
                                            <div class="flex flex-col text-[11px]">
                                                <span class="font-semibold text-slate-800">{{ $r->source_tap_name ?? $r->dest_tap_name ?? '-' }}</span>
                                                <span class="text-slate-500">{{ $r->source_cluster_name ?? $r->dest_cluster_name ?? '-' }}</span>
                                            </div>
                                        </td>

                                        <!-- Total Varian -->
                                        <td class="py-3 px-4 text-center whitespace-nowrap">
                                            <span class="px-2 py-0.5 rounded text-[11px] font-mono bg-slate-100 text-slate-800 font-bold">
                                                {{ count($r->items ?? []) }} varian
                                            </span>
                                        </td>

                                        <!-- Total Qty Fisik -->
                                        <td class="py-3 px-4 text-center whitespace-nowrap">
                                            <span class="px-2.5 py-0.5 rounded text-xs font-mono font-extrabold bg-cyan-100 text-cyan-800">
                                                {{ number_format($r->total_qty ?? 0) }} pcs
                                            </span>
                                        </td>

                                        <!-- Status -->
                                        <td class="py-3 px-4 text-center whitespace-nowrap">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                {{ $r->status }}
                                            </span>
                                        </td>

                                        <!-- Keterangan / Keperluan -->
                                        <td class="py-3 px-4 text-slate-500 max-w-xs truncate">
                                            {{ $r->notes ?? '-' }}
                                        </td>
                                    </tr>

                                    <!-- Sub-Baris Expandable (Rincian Item Mutasi) -->
                                    <tr x-show="expanded[{{ $r->id }}]" x-cloak class="bg-slate-50/80 border-b border-cyan-200">
                                        <td colspan="9" class="p-4 pl-12">
                                            <div class="bg-white rounded-xl border border-slate-200/90 shadow-sm p-4">
                                                <div class="flex items-center justify-between pb-2 mb-3 border-b border-slate-100 flex-wrap gap-2">
                                                    <div class="flex items-center gap-2">
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-cyan-100 text-cyan-800 flex items-center gap-1">
                                                            <i data-lucide="package-open" class="w-3 h-3"></i>
                                                            <span>RINCIAN BARANG MUTASI (SM: {{ $r->doc_number }})</span>
                                                        </span>
                                                        <span class="text-xs text-slate-500 font-medium">
                                                            Rute: <strong>{{ $r->source_wh }}</strong> &rarr; <strong class="text-rose-700">{{ $r->dest_wh }}</strong>
                                                        </span>
                                                    </div>
                                                    <div class="text-xs text-slate-600 font-bold">
                                                        Total {{ count($r->items ?? []) }} varian produk &bull; 
                                                        Volume Fisik: <span class="font-mono text-cyan-800 text-sm font-extrabold">{{ number_format($r->total_qty ?? 0) }} pcs</span>
                                                    </div>
                                                </div>

                                                <!-- Sub-Table Items -->
                                                <div class="overflow-x-auto">
                                                    <table class="w-full text-left text-xs border border-slate-100 rounded-lg overflow-hidden">
                                                        <thead class="bg-slate-100 text-slate-600 font-bold uppercase tracking-wider text-[10px]">
                                                            <tr>
                                                                <th class="py-2 px-3 w-10 text-center">No</th>
                                                                <th class="py-2 px-3">Kode SKU</th>
                                                                <th class="py-2 px-3">Nama Produk Barang</th>
                                                                <th class="py-2 px-3">Tipe Produk</th>
                                                                @if(config('erp.enable_iccid', false))
                                                                    <th class="py-2 px-3 text-center">Metode Tracking</th>
                                                                @endif
                                                                <th class="py-2 px-3 text-right">Kuantitas Dipindahkan</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-slate-100 font-medium">
                                                            @forelse($r->items as $iIdx => $item)
                                                                <tr class="hover:bg-slate-50">
                                                                    <td class="py-2 px-3 text-center text-slate-400 font-mono">{{ $iIdx + 1 }}</td>
                                                                    <td class="py-2 px-3 font-mono font-bold text-slate-700">{{ $item->sku }}</td>
                                                                    <td class="py-2 px-3 font-bold text-slate-900">{{ $item->product_name }}</td>
                                                                    <td class="py-2 px-3">
                                                                        <span class="px-2 py-0.5 rounded text-[10px] bg-slate-100 text-slate-700 font-semibold">{{ $item->product_type }}</span>
                                                                    </td>
                                                                    @if(config('erp.enable_iccid', false))
                                                                        <td class="py-2 px-3 text-center">
                                                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold {{ $item->tracking_method === 'ICCID' ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-600' }}">
                                                                                {{ $item->tracking_method }}
                                                                            </span>
                                                                        </td>
                                                                    @endif
                                                                    <td class="py-2 px-3 text-right font-mono font-extrabold text-cyan-800 text-xs">
                                                                        {{ number_format($item->quantity) }} pcs
                                                                    </td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="{{ config('erp.enable_iccid', false) ? 6 : 5 }}" class="py-3 text-center text-slate-400 italic">Tidak ada item tercatat pada dokumen ini.</td>
                                                                </tr>
                                                            @endforelse
                                                        </tbody>
                                                        <tfoot class="bg-slate-50 border-t border-slate-200 font-bold text-xs">
                                                            <tr>
                                                                <td colspan="{{ config('erp.enable_iccid', false) ? 5 : 4 }}" class="py-2 px-3 text-right uppercase tracking-wider text-slate-600">Total Kuantitas Fisik Dipindahkan:</td>
                                                                <td class="py-2 px-3 text-right font-mono font-extrabold text-cyan-800">{{ number_format($r->total_qty ?? 0) }} pcs</td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>

                                                @if(!empty($r->notes))
                                                    <div class="mt-3 p-2.5 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-600 flex items-start gap-2">
                                                        <i data-lucide="info" class="w-4 h-4 text-cyan-600 shrink-0 mt-0.5"></i>
                                                        <div>
                                                            <strong class="text-slate-800">Catatan / Keperluan Mutasi:</strong>
                                                            <span>{{ $r->notes }}</span>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="py-8 text-center text-slate-400">Tidak ada transaksi transfer stok (SM) pada filter periode ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                @elseif($reportType === 'REPACK')
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">No. Dokumen</th>
                                <th class="py-3 px-4 text-center">Tipe</th>
                                <th class="py-3 px-4">Tanggal Repack</th>
                                <th class="py-3 px-4">Gudang</th>
                                <th class="py-3 px-4">Ref Dokumen</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-right">Nilai Konversi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($records as $r)
                                <tr class="hover:bg-slate-50">
                                    <td class="py-3 px-4 font-mono font-bold text-amber-800">{{ $r->doc_number }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $r->doc_type === 'RPKOUT' ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800' }}">
                                            {{ $r->doc_type }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600">{{ date('d M Y', strtotime($r->repack_date)) }}</td>
                                    <td class="py-3 px-4 text-slate-800 font-semibold">{{ $r->warehouse_name }}</td>
                                    <td class="py-3 px-4 font-mono text-slate-500">{{ $r->ref_doc_number ?? '-' }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                            {{ $r->status }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-extrabold text-slate-900">
                                        Rp {{ number_format($r->total_amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-slate-400">Tidak ada transaksi pada filter periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                @elseif($reportType === 'AR')
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">No. Faktur AR</th>
                                <th class="py-3 px-4">Ref SO</th>
                                <th class="py-3 px-4">Toko / Outlet</th>
                                <th class="py-3 px-4">Sales Penanggung Jawab</th>
                                <th class="py-3 px-4">Tgl Jatuh Tempo</th>
                                <th class="py-3 px-4 text-right">Total Tagihan</th>
                                <th class="py-3 px-4 text-right">Terbayar</th>
                                <th class="py-3 px-4 text-right">Sisa Piutang</th>
                                <th class="py-3 px-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($records as $r)
                                <tr class="hover:bg-slate-50">
                                    <td class="py-3 px-4 font-mono font-bold text-amber-800">{{ $r->doc_number }}</td>
                                    <td class="py-3 px-4 font-mono text-slate-600">{{ $r->so_number ?? '-' }}</td>
                                    <td class="py-3 px-4 font-bold text-slate-900">{{ $r->customer_name }}</td>
                                    <td class="py-3 px-4 text-slate-800">{{ $r->sales_name ?? 'TAP Admin' }}</td>
                                    <td class="py-3 px-4 text-slate-600">{{ date('d M Y', strtotime($r->due_date)) }}</td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-slate-800">Rp {{ number_format($r->total_amount, 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-right font-mono text-emerald-600">Rp {{ number_format($r->paid_amount, 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-right font-mono font-extrabold text-rose-600">Rp {{ number_format($r->balance_amount, 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $r->status === 'PAID' ? 'bg-emerald-100 text-emerald-800' : ($r->status === 'PARTIAL' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                            {{ $r->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-8 text-center text-slate-400">Tidak ada transaksi pada filter periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                @elseif($reportType === 'CASH_BANK')
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">No. Dokumen BBM</th>
                                <th class="py-3 px-4 text-center">Tipe BBM</th>
                                <th class="py-3 px-4">Ref TTK</th>
                                <th class="py-3 px-4">Tgl Masuk</th>
                                <th class="py-3 px-4">Toko / Outlet</th>
                                <th class="py-3 px-4">Kas / Bank Penerima</th>
                                <th class="py-3 px-4">Metode Bayar</th>
                                <th class="py-3 px-4">Kasir</th>
                                <th class="py-3 px-4 text-right">Nominal Masuk</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($records as $r)
                                <tr class="hover:bg-slate-50">
                                    <td class="py-3 px-4 font-mono font-bold text-indigo-900">{{ $r->doc_number }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $r->receipt_type === 'TUNAI' ? 'bg-emerald-100 text-emerald-800' : 'bg-indigo-100 text-indigo-800' }}">
                                            {{ $r->receipt_type }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 font-mono font-bold text-emerald-700">{{ $r->ttk_number }}</td>
                                    <td class="py-3 px-4 text-slate-600">{{ date('d M Y', strtotime($r->receipt_date)) }}</td>
                                    <td class="py-3 px-4 font-bold text-slate-900">{{ $r->customer_name }}</td>
                                    <td class="py-3 px-4 text-slate-800 font-semibold">{{ $r->bank_name }}</td>
                                    <td class="py-3 px-4 text-slate-700">{{ $r->payment_method }}</td>
                                    <td class="py-3 px-4 text-slate-600">{{ $r->cashier_name }}</td>
                                    <td class="py-3 px-4 text-right font-mono font-extrabold text-indigo-950">
                                        Rp {{ number_format($r->amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-8 text-center text-slate-400">Tidak ada transaksi pada filter periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                @elseif($reportType === 'STOCK')
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">TAP & Gudang</th>
                                <th class="py-3 px-4">SKU & Produk</th>
                                <th class="py-3 px-4 text-right">Stok Awal</th>
                                <th class="py-3 px-4 text-right text-emerald-700">Masuk (IN)</th>
                                <th class="py-3 px-4 text-right text-rose-700">Keluar (OUT)</th>
                                <th class="py-3 px-4 text-right">Stok Akhir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($records as $r)
                                <tr class="hover:bg-slate-50">
                                    <td class="py-3 px-4">
                                        <span class="font-bold text-slate-900 block">{{ $r->warehouse_name }}</span>
                                        <span class="text-[10px] font-mono text-slate-400">{{ $r->tap_name }} &bull; {{ $r->warehouse_type }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="font-bold text-slate-900 block">{{ $r->product_name }}</span>
                                        <span class="text-[10px] font-mono text-slate-400">{{ $r->product_sku }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono text-slate-700">
                                        {{ number_format($r->opening_stock, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-emerald-700">
                                        +{{ number_format($r->qty_in, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-rose-600">
                                        -{{ number_format($r->qty_out, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-extrabold text-slate-900">
                                        {{ number_format($r->closing_stock, 0, ',', '.') }} {{ $r->uom ?? 'PCS' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400">
                                        Belum ada mutasi stok pada filter wilayah dan periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(count($records) > 0)
                            <tfoot class="bg-slate-50 border-t-2 border-slate-200 font-mono font-extrabold text-slate-900">
                                <tr>
                                    <td colspan="2" class="py-3 px-4 text-right uppercase text-[11px] text-slate-600">Total Mutasi Periode:</td>
                                    <td class="py-3 px-4 text-right">{{ number_format($summary['total_opening'] ?? 0, 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-right text-emerald-700">+{{ number_format($summary['total_in'] ?? 0, 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-right text-rose-600">-{{ number_format($summary['total_out'] ?? 0, 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-right">{{ number_format($summary['total_closing'] ?? 0, 0, ',', '.') }} pcs</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                @endif
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function reportFilterComponent() {
            return {
                selectedArea: '{{ $areaId ?? '' }}',
                selectedCluster: '{{ $clusterId ?? '' }}',
                selectedTap: '{{ $tapId ?? 'ALL' }}',
                selectedWarehouse: '{{ $warehouseId ?? 'ALL' }}',
                allClusters: @json($clusters ?? []),
                allTaps: @json($taps ?? []),
                allWarehouses: @json($warehouses ?? []),

                tapSearchText: '',
                tapDropdownOpen: false,

                warehouseSearchText: '',
                warehouseDropdownOpen: false,

                init() {
                    this.syncTapDisplay();
                    this.syncWarehouseDisplay();
                },

                syncTapDisplay() {
                    if (!this.selectedTap || this.selectedTap === 'ALL') {
                        this.tapSearchText = '';
                    } else {
                        var self = this;
                        var found = this.allTaps.find(function(t) {
                            return String(t.id) === String(self.selectedTap);
                        });
                        this.tapSearchText = found ? found.name : '';
                    }
                },

                syncWarehouseDisplay() {
                    if (!this.selectedWarehouse || this.selectedWarehouse === 'ALL') {
                        this.warehouseSearchText = '';
                    } else {
                        var self = this;
                        var found = this.allWarehouses.find(function(w) {
                            return String(w.id) === String(self.selectedWarehouse);
                        });
                        this.warehouseSearchText = found ? found.name : '';
                    }
                },

                get clusterOptions() {
                    if (!this.selectedArea) return this.allClusters;
                    var self = this;
                    return this.allClusters.filter(function(c) {
                        return String(c.area_id) === String(self.selectedArea);
                    });
                },

                get tapOptions() {
                    var self = this;
                    if (this.selectedCluster) {
                        return this.allTaps.filter(function(t) {
                            return String(t.cluster_id) === String(self.selectedCluster);
                        });
                    }
                    if (this.selectedArea) {
                        return this.allTaps.filter(function(t) {
                            return String(t.area_id) === String(self.selectedArea);
                        });
                    }
                    return this.allTaps;
                },

                get filteredTapOptions() {
                    var list = this.tapOptions;
                    var q = (this.tapSearchText || '').trim().toLowerCase();
                    if (!q) return list;
                    return list.filter(function(t) {
                        var name = (t.name || '').toLowerCase();
                        var code = (t.code || '').toLowerCase();
                        return name.indexOf(q) !== -1 || code.indexOf(q) !== -1;
                    });
                },

                selectTap(tap) {
                    if (!tap) {
                        this.selectedTap = 'ALL';
                        this.tapSearchText = '';
                    } else {
                        this.selectedTap = String(tap.id);
                        this.tapSearchText = tap.name;
                    }
                    this.tapDropdownOpen = false;
                    // Reset warehouse selection if TAP changes
                    this.selectedWarehouse = 'ALL';
                    this.warehouseSearchText = '';
                },

                clearTap() {
                    this.selectedTap = 'ALL';
                    this.tapSearchText = '';
                    this.tapDropdownOpen = false;
                    this.selectedWarehouse = 'ALL';
                    this.warehouseSearchText = '';
                },

                get warehouseOptions() {
                    var self = this;
                    var list = this.allWarehouses;
                    if (this.selectedTap && this.selectedTap !== 'ALL') {
                        return list.filter(function(w) {
                            return String(w.tap_id) === String(self.selectedTap);
                        });
                    }
                    if (this.selectedCluster) {
                        return list.filter(function(w) {
                            return String(w.cluster_id) === String(self.selectedCluster);
                        });
                    }
                    if (this.selectedArea) {
                        return list.filter(function(w) {
                            return String(w.area_id) === String(self.selectedArea);
                        });
                    }
                    return list;
                },

                get filteredWarehouseOptions() {
                    var list = this.warehouseOptions;
                    var q = (this.warehouseSearchText || '').trim().toLowerCase();
                    if (!q) return list;
                    return list.filter(function(w) {
                        var name = (w.name || '').toLowerCase();
                        var code = (w.code || '').toLowerCase();
                        var type = (w.type || '').toLowerCase();
                        return name.indexOf(q) !== -1 || code.indexOf(q) !== -1 || type.indexOf(q) !== -1;
                    });
                },

                selectWarehouse(wh) {
                    if (!wh) {
                        this.selectedWarehouse = 'ALL';
                        this.warehouseSearchText = '';
                    } else {
                        this.selectedWarehouse = String(wh.id);
                        this.warehouseSearchText = wh.name;
                    }
                    this.warehouseDropdownOpen = false;
                },

                clearWarehouse() {
                    this.selectedWarehouse = 'ALL';
                    this.warehouseSearchText = '';
                    this.warehouseDropdownOpen = false;
                },

                onAreaChange() {
                    this.selectedCluster = '';
                    this.selectedTap = 'ALL';
                    this.tapSearchText = '';
                    this.selectedWarehouse = 'ALL';
                    this.warehouseSearchText = '';
                },

                onClusterChange() {
                    this.selectedTap = 'ALL';
                    this.tapSearchText = '';
                    this.selectedWarehouse = 'ALL';
                    this.warehouseSearchText = '';
                }
            };
        }

        function reportPoGrTable() {
            return {
                expanded: {},
                toggleRow(id) {
                    this.expanded[id] = !this.expanded[id];
                    this.$nextTick(function() {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },
                expandAll() {
                    var all = {};
                    @if(isset($records) && $reportType === 'PO_GR')
                        @foreach($records as $r)
                            all[{{ $r->id }}] = true;
                        @endforeach
                    @endif
                    this.expanded = all;
                    this.$nextTick(function() {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },
                collapseAll() {
                    this.expanded = {};
                    this.$nextTick(function() {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                }
            };
        }

        function reportSmTable() {
            return {
                expanded: {},
                toggleRow(id) {
                    this.expanded[id] = !this.expanded[id];
                    this.$nextTick(function() {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },
                expandAll() {
                    var all = {};
                    @if(isset($records) && $reportType === 'SM')
                        @foreach($records as $r)
                            all[{{ $r->id }}] = true;
                        @endforeach
                    @endif
                    this.expanded = all;
                    this.$nextTick(function() {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },
                collapseAll() {
                    this.expanded = {};
                    this.$nextTick(function() {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                }
            };
        }
    </script>
    @endpush
</x-app-layout>

