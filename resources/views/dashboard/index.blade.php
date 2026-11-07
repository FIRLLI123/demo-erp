<x-app-layout>
    <x-slot name="title">Executive Visual Dashboard</x-slot>
    <x-slot name="headerTitle">Dashboard Operasional & Distribusi</x-slot>
    <x-slot name="badge">Live Monitoring</x-slot>

    <!-- Top Filter & Context Bar -->
    <div class="mb-8 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                <h2 class="text-base font-bold text-slate-800">Filter Hierarki Organisasi</h2>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">Pilih Area, Cluster, dan TAP untuk filtering data dashboard secara instan</p>
        </div>

        <!-- Cascading Filter Controls -->
        <div class="flex flex-wrap items-center gap-3" x-data="{ selectedArea: '1', selectedCluster: '1', selectedTap: '1' }">
            <!-- Level 1: Area -->
            <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5">
                <span class="text-[11px] font-bold text-slate-400 uppercase">1. Area:</span>
                <select x-model="selectedArea" class="bg-transparent text-xs font-bold text-slate-800 focus:outline-none cursor-pointer">
                    @foreach($areas as $area)
                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Level 2: Cluster -->
            <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5">
                <span class="text-[11px] font-bold text-slate-400 uppercase">2. Cluster:</span>
                <select x-model="selectedCluster" class="bg-transparent text-xs font-bold text-slate-800 focus:outline-none cursor-pointer">
                    @foreach($clusters as $cluster)
                        <option value="{{ $cluster->id }}">{{ $cluster->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Level 3: TAP -->
            <div class="flex items-center gap-2 bg-rose-50 border border-rose-200 rounded-xl px-3 py-1.5">
                <span class="text-[11px] font-bold text-rose-500 uppercase">3. TAP:</span>
                <select x-model="selectedTap" class="bg-transparent text-xs font-bold text-rose-700 focus:outline-none cursor-pointer">
                    @foreach($taps as $tap)
                        <option value="{{ $tap->id }}">{{ $tap->name }}</option>
                    @endforeach
                </select>
            </div>

            <button class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold flex items-center gap-2 shadow-sm transition-all">
                <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                <span>Terapkan</span>
            </button>
        </div>
    </div>

    <!-- 4 Primary KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
        <!-- Card 1: Total Sales Omset -->
        <div class="relative bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm overflow-hidden group hover:shadow-md transition-all">
            <div class="absolute top-0 right-0 w-28 h-28 bg-gradient-to-br from-rose-500/10 to-red-500/0 rounded-full blur-2xl"></div>
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Sales (SO)</span>
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center border border-rose-100">
                    <i data-lucide="trending-up" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-extrabold text-slate-900">Rp {{ number_format($totalSales, 0, ',', '.') }}</span>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs">
                <span class="text-emerald-600 font-semibold flex items-center gap-1">
                    <i data-lucide="arrow-up-right" class="w-3.5 h-3.5"></i> +14.8%
                </span>
                <span class="text-slate-400">vs bulan lalu</span>
            </div>
        </div>

        <!-- Card 2: Outstanding AR (Piutang) -->
        <div class="relative bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm overflow-hidden group hover:shadow-md transition-all">
            <div class="absolute top-0 right-0 w-28 h-28 bg-gradient-to-br from-amber-500/10 to-orange-500/0 rounded-full blur-2xl"></div>
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Piutang (AR) Berjalan</span>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-100">
                    <i data-lucide="clock" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-extrabold text-slate-900">Rp {{ number_format($outstandingAR, 0, ',', '.') }}</span>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">
                    1 Invoice Tempo 5 Hari
                </span>
                <span class="text-slate-400 font-medium">DocType: AR</span>
            </div>
        </div>

        <!-- Card 3: Total Fisik Stok Gudang & Kanvas -->
        <div class="relative bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm overflow-hidden group hover:shadow-md transition-all">
            <div class="absolute top-0 right-0 w-28 h-28 bg-gradient-to-br from-cyan-500/10 to-blue-500/0 rounded-full blur-2xl"></div>
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Stok Tersedia</span>
                <div class="w-10 h-10 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center border border-cyan-100">
                    <i data-lucide="package-check" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-extrabold text-slate-900">{{ number_format($totalStockQty + $totalIccidAvailable, 0, ',', '.') }}</span>
                <span class="text-xs font-semibold text-slate-500">Unit Produk</span>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs">
                <span class="text-cyan-700 font-medium">
                    {{ $totalIccidAvailable }} ICCID • {{ $totalStockQty }} Qty
                </span>
                <span class="text-slate-400">di 6 Gudang</span>
            </div>
        </div>

        <!-- Card 4: Kas / Pelunasan Masuk Hari Ini -->
        <div class="relative bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm overflow-hidden group hover:shadow-md transition-all">
            <div class="absolute top-0 right-0 w-28 h-28 bg-gradient-to-br from-emerald-500/10 to-green-500/0 rounded-full blur-2xl"></div>
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kas Masuk (FP & BBM)</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-extrabold text-slate-900">Rp {{ number_format($cashInToday, 0, ',', '.') }}</span>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs">
                <span class="text-emerald-600 font-semibold flex items-center gap-1">
                    <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Terverifikasi BBM
                </span>
                <span class="text-slate-400">100% Lunas</span>
            </div>
        </div>
    </div>

    <!-- Charts Row 1: Sales Velocity & Stock Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Sales Velocity Area Chart (2 Cols) -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Tren Penjualan & Realisasi Target</h3>
                    <p class="text-xs text-slate-500">Perbandingan Sales Order (SO) harian dari Web Admin vs Mobile Sales</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span> Mobile Sales
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-cyan-50 text-cyan-700 border border-cyan-200">
                        <span class="w-2 h-2 rounded-full bg-cyan-500"></span> Web Admin
                    </span>
                </div>
            </div>
            <div id="salesVelocityChart" class="w-full h-72"></div>
        </div>

        <!-- Stock Distribution by Warehouse Type Donut Chart (1 Col) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div class="mb-4">
                <h3 class="font-bold text-slate-900 text-base">Rasio Posisi Stok</h3>
                <p class="text-xs text-slate-500">Main TAP vs Inv TAP vs Kanvas Sales</p>
            </div>
            <div id="stockDistributionChart" class="w-full flex items-center justify-center h-64"></div>
            <div class="grid grid-cols-3 gap-2 pt-4 border-t border-slate-100 text-center text-xs">
                <div class="bg-slate-50 p-2 rounded-xl">
                    <p class="text-[10px] text-slate-400 font-bold uppercase">Main TAP</p>
                    <p class="font-extrabold text-slate-800 text-sm mt-0.5">{{ number_format($mainStock) }}</p>
                </div>
                <div class="bg-slate-50 p-2 rounded-xl">
                    <p class="text-[10px] text-slate-400 font-bold uppercase">Inv TAP</p>
                    <p class="font-extrabold text-slate-800 text-sm mt-0.5">{{ number_format($invStock) }}</p>
                </div>
                <div class="bg-slate-50 p-2 rounded-xl">
                    <p class="text-[10px] text-slate-400 font-bold uppercase">Kanvas</p>
                    <p class="font-extrabold text-rose-600 text-sm mt-0.5">{{ number_format($salesStock) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 & Leaderboard -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Chart 3: Omset per TAP -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Pencapaian per TAP</h3>
                    <p class="text-xs text-slate-500">Pasar Minggu vs Blok M vs Rawamangun</p>
                </div>
                <span class="text-xs font-bold text-rose-600 bg-rose-50 px-2.5 py-1 rounded-lg border border-rose-100">Top: Ps. Minggu</span>
            </div>
            <div id="tapPerformanceChart" class="w-full h-64"></div>
        </div>

        <!-- Sales Force Leaderboard (2 Cols) -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Top Sales Force Leaderboard</h3>
                    <p class="text-xs text-slate-500">Performa penjualan kanvas lapangan real-time</p>
                </div>
                <span class="text-xs font-semibold text-slate-500 flex items-center gap-1.5">
                    <i data-lucide="award" class="w-4 h-4 text-amber-500"></i> Periode Berjalan
                </span>
            </div>

            <div class="space-y-3.5">
                @foreach($salesLeaderboard as $idx => $sls)
                    <div class="flex items-center justify-between p-3.5 rounded-xl border border-slate-100 bg-slate-50/60 hover:bg-slate-50 transition-colors">
                        <div class="flex items-center gap-3.5">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center font-extrabold text-xs shadow-sm
                                {{ $idx === 0 ? 'bg-gradient-to-tr from-amber-400 to-yellow-500 text-slate-950 ring-2 ring-amber-300' : 'bg-slate-200 text-slate-700' }}">
                                #{{ $idx + 1 }}
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                                    {{ $sls->name }}
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-mono bg-slate-200 text-slate-700 font-semibold">{{ $sls->code }}</span>
                                </h4>
                                <p class="text-xs text-slate-500 flex items-center gap-1">
                                    <i data-lucide="map-pin" class="w-3 h-3 text-rose-500"></i> {{ $sls->tap_name }}
                                </p>
                            </div>
                        </div>

                        <div class="text-right">
                            <p class="text-sm font-extrabold text-slate-900">Rp {{ number_format($sls->total_omset, 0, ',', '.') }}</p>
                            <p class="text-[11px] text-slate-500 font-medium">{{ $sls->total_trx }} Transaksi Selesai</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Live Transaction Feeds & Audit Trail (SO, GD, AR, FP) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden" x-data="{ traceModalOpen: false, activeTrace: {} }">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <h3 class="font-bold text-slate-900 text-base">Alur Transaksi & Audit Trail Dokumen</h3>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Jejak lengkap perjalanan transaksi (DocType: SO → GD → AR → FP)</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('sales.index') }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors flex items-center gap-1.5">
                    <span>Lihat Semua SO</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-6">DocType & No. Dokumen</th>
                        <th class="py-3.5 px-4">Channel Input</th>
                        <th class="py-3.5 px-4">Outlet / Customer</th>
                        <th class="py-3.5 px-4">Sales & TAP</th>
                        <th class="py-3.5 px-4">Grand Total</th>
                        <th class="py-3.5 px-4">Status Transaksi</th>
                        <th class="py-3.5 px-6 text-center">Jejak Dokumen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentOrders as $order)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-4 px-6 font-mono font-bold text-slate-900">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-900 text-white">
                                        {{ $order->doc_type }}
                                    </span>
                                    <span>{{ $order->doc_number }}</span>
                                </div>
                                <span class="text-[10px] text-slate-400 font-sans block mt-0.5">Tgl: {{ date('d M Y', strtotime($order->order_date)) }}</span>
                            </td>
                            <td class="py-4 px-4">
                                @if($order->channel === 'MOBILE_SALES')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i data-lucide="smartphone" class="w-3 h-3 text-emerald-600"></i> Mobile Sales
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        <i data-lucide="monitor" class="w-3 h-3 text-indigo-600"></i> Web Admin
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                <span class="font-bold text-slate-900 block">{{ $order->customer_name }}</span>
                                <span class="text-[11px] text-slate-400">Regular Partner</span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="font-semibold text-slate-800 block">{{ $order->sales_name }}</span>
                                <span class="text-[11px] text-rose-600 font-medium">{{ $order->tap_name }}</span>
                            </td>
                            <td class="py-4 px-4 font-mono font-bold text-slate-900">
                                Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-4">
                                @if($order->status === 'COMPLETED')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        COMPLETED (Lunas)
                                    </span>
                                @elseif($order->status === 'DELIVERED')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-cyan-100 text-cyan-800">
                                        DELIVERED (Terkirim)
                                    </span>
                                @elseif($order->status === 'PENDING_APPROVAL')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 animate-pulse">
                                        PENDING FINANCE
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                        {{ $order->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-center">
                                <button 
                                    @click="traceModalOpen = true; activeTrace = {
                                        so: '{{ $order->doc_number }}',
                                        total: 'Rp {{ number_format($order->grand_total, 0, ',', '.') }}',
                                        customer: '{{ $order->customer_name }}',
                                        status: '{{ $order->status }}'
                                    }" 
                                    class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg text-xs font-bold transition-all inline-flex items-center gap-1.5 border border-rose-200">
                                    <i data-lucide="git-merge" class="w-3.5 h-3.5"></i>
                                    <span>Lacak Jejak</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">Belum ada transaksi sales order</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Traceability Modal for Demo -->
        <div x-show="traceModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" @click.away="traceModalOpen = false">
            <div class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl border border-slate-100" @click.stop>
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <span class="text-xs font-bold text-rose-600 uppercase tracking-wider">Audit Trail Traceability</span>
                        <h4 class="text-base font-extrabold text-slate-900">Perjalanan Dokumen Transaksi</h4>
                    </div>
                    <button @click="traceModalOpen = false" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="py-6">
                    <div class="p-3 bg-slate-50 rounded-xl mb-6 flex justify-between items-center text-xs">
                        <div>
                            <span class="text-slate-400 block">Customer:</span>
                            <span class="font-bold text-slate-900" x-text="activeTrace.customer"></span>
                        </div>
                        <div class="text-right">
                            <span class="text-slate-400 block">Nilai Transaksi:</span>
                            <span class="font-extrabold text-rose-600" x-text="activeTrace.total"></span>
                        </div>
                    </div>

                    <!-- Flow Diagram -->
                    <div class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                        <!-- Step 1: SO -->
                        <div class="relative flex items-start gap-4">
                            <span class="absolute -left-6 mt-1 w-5 h-5 rounded-full bg-slate-900 text-white flex items-center justify-center text-[10px] font-bold ring-4 ring-white">1</span>
                            <div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-900 text-white font-mono">DocType: SO</span>
                                <h5 class="font-bold text-slate-800 text-xs mt-1" x-text="activeTrace.so"></h5>
                                <p class="text-[11px] text-slate-500">Sales Order dibuat dan dikonfirmasi</p>
                            </div>
                        </div>

                        <!-- Step 2: GD -->
                        <div class="relative flex items-start gap-4">
                            <span class="absolute -left-6 mt-1 w-5 h-5 rounded-full bg-cyan-600 text-white flex items-center justify-center text-[10px] font-bold ring-4 ring-white">2</span>
                            <div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-cyan-600 text-white font-mono">DocType: GD</span>
                                <h5 class="font-bold text-slate-800 text-xs mt-1">GD/202611/0001 (Surat Jalan)</h5>
                                <p class="text-[11px] text-slate-500">Barang fisik dikeluarkan dari gudang & diterima outlet</p>
                            </div>
                        </div>

                        <!-- Step 3: AR -->
                        <div class="relative flex items-start gap-4">
                            <span class="absolute -left-6 mt-1 w-5 h-5 rounded-full bg-amber-500 text-white flex items-center justify-center text-[10px] font-bold ring-4 ring-white">3</span>
                            <div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500 text-white font-mono">DocType: AR</span>
                                <h5 class="font-bold text-slate-800 text-xs mt-1">AR/202611/0001 (Faktur Piutang)</h5>
                                <p class="text-[11px] text-slate-500">Pencatatan tagihan invoice ke customer</p>
                            </div>
                        </div>

                        <!-- Step 4: FP & BBM -->
                        <div class="relative flex items-start gap-4">
                            <span class="absolute -left-6 mt-1 w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[10px] font-bold ring-4 ring-white">4</span>
                            <div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-600 text-white font-mono">DocType: FP & BBM</span>
                                <h5 class="font-bold text-slate-800 text-xs mt-1">FP/202611/0001 (Tanda Terima Kasir)</h5>
                                <p class="text-[11px] text-slate-500">Pelunasan via Transfer Bank terverifikasi BBM</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex justify-end">
                    <button @click="traceModalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // 1. Sales Velocity Area Chart
            var optionsVelocity = {
                series: [{
                    name: 'Mobile Sales Apps',
                    data: [1200000, 2400000, 1800000, 3100000, 4200000, 3800000, 5200000]
                }, {
                    name: 'Web Admin',
                    data: [800000, 1500000, 2100000, 1900000, 2800000, 3400000, 4100000]
                }],
                chart: {
                    type: 'area',
                    height: 270,
                    toolbar: { show: false },
                    fontFamily: 'Plus Jakarta Sans, sans-serif'
                },
                colors: ['#ED1C24', '#06B6D4'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.45,
                        opacityTo: 0.05,
                        stops: [20, 100]
                    }
                },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 3 },
                xaxis: {
                    categories: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],
                    labels: { style: { colors: '#64748B', fontSize: '11px', fontWeight: 600 } }
                },
                yaxis: {
                    labels: {
                        formatter: function(val) {
                            return 'Rp ' + (val / 1000000).toFixed(1) + 'M';
                        },
                        style: { colors: '#64748B', fontSize: '11px' }
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return 'Rp ' + val.toLocaleString('id-ID');
                        }
                    }
                },
                legend: { show: false }
            };
            var chartVelocity = new ApexCharts(document.querySelector("#salesVelocityChart"), optionsVelocity);
            chartVelocity.render();

            // 2. Stock Distribution Donut
            var optionsStock = {
                series: [{{ $mainStock }}, {{ $invStock }}, {{ $salesStock }}],
                labels: ['Main TAP', 'Inventory TAP', 'Kanvas Sales'],
                chart: {
                    type: 'donut',
                    height: 250,
                    fontFamily: 'Plus Jakarta Sans, sans-serif'
                },
                colors: ['#0F172A', '#3B82F6', '#ED1C24'],
                legend: {
                    position: 'bottom',
                    fontSize: '11px',
                    fontWeight: 600
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '68%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total Stok',
                                    fontSize: '12px',
                                    fontWeight: 700,
                                    color: '#64748B',
                                    formatter: function(w) {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString('id-ID') + ' pcs';
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: { enabled: false }
            };
            var chartStock = new ApexCharts(document.querySelector("#stockDistributionChart"), optionsStock);
            chartStock.render();

            // 3. TAP Performance Bar Chart
            var optionsTap = {
                series: [{
                    name: 'Omset Penjualan',
                    data: [8950000, 4875000, 3200000]
                }],
                chart: {
                    type: 'bar',
                    height: 240,
                    toolbar: { show: false },
                    fontFamily: 'Plus Jakarta Sans, sans-serif'
                },
                colors: ['#ED1C24', '#F59E0B', '#6366F1'],
                plotOptions: {
                    bar: {
                        distributed: true,
                        borderRadius: 8,
                        columnWidth: '45%'
                    }
                },
                dataLabels: { enabled: false },
                xaxis: {
                    categories: ['Ps. Minggu', 'Blok M', 'Rawamangun'],
                    labels: { style: { colors: '#64748B', fontSize: '11px', fontWeight: 600 } }
                },
                yaxis: {
                    labels: {
                        formatter: function(val) {
                            return 'Rp ' + (val / 1000000).toFixed(1) + 'M';
                        },
                        style: { colors: '#64748B', fontSize: '11px' }
                    }
                },
                legend: { show: false }
            };
            var chartTap = new ApexCharts(document.querySelector("#tapPerformanceChart"), optionsTap);
            chartTap.render();
        });
    </script>
    @endpush
</x-app-layout>

