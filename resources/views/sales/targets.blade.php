<x-app-layout>
    <x-slot name="title">Target & Leaderboard Komisi Sales</x-slot>
    <x-slot name="headerTitle">Target & Komisi Kanvaser</x-slot>
    <x-slot name="badge">Leaderboard & Incentive</x-slot>

    <div x-data="salesTargetsApp()">
        
        <!-- Header Banner -->
        <div class="mb-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <i data-lucide="trophy" class="w-5 h-5 text-amber-500"></i>
                    <span>Leaderboard & Pencapaian Target Sales Force</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">PERIODE BULAN BERJALAN</span>
                </h2>
                <p class="text-xs text-slate-500">Pemantauan real-time ranking omset kanvaser, pencapaian target sell-in/sell-out, akuisisi toko baru (NOA), dan perhitungan komisi otomatis</p>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" @click="ruleModalOpen = true" class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-2xs transition-all">
                    <i data-lucide="help-circle" class="w-4 h-4 text-amber-600"></i>
                    <span>Skema & Aturan Komisi</span>
                </button>

                <button type="button" onclick="window.print()" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-sm transition-all active:scale-95">
                    <i data-lucide="printer" class="w-4 h-4 text-amber-400"></i>
                    <span>Cetak Rekap Insentif</span>
                </button>
            </div>
        </div>

        <!-- CLUSTER PERFORMANCE SUMMARY CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            
            <!-- 1. Total Target Cluster -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 block mb-1">Target Cluster MTD</span>
                    <span class="text-xl font-extrabold text-slate-900 font-mono">Rp {{ number_format($totalClusterTarget, 0, ',', '.') }}</span>
                    <span class="text-[11px] text-slate-500 font-medium block mt-1">Area Operasional TAP Pasar Minggu</span>
                </div>
                <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
                    <i data-lucide="target" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- 2. Realisasi Revenue Cluster -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 block mb-1">Realisasi Omset Terkumpul</span>
                    <span class="text-xl font-extrabold text-emerald-700 font-mono">Rp {{ number_format($totalClusterRevenue, 0, ',', '.') }}</span>
                    <span class="text-[11px] text-emerald-600 font-bold block mt-1 flex items-center gap-1">
                        <i data-lucide="trending-up" class="w-3.5 h-3.5"></i> {{ $clusterAchievementPct }}% Capaian Cluster
                    </span>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="badge-percent" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- 3. Total Estimasi Pool Komisi -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 block mb-1">Total Pool Insentif Sales</span>
                    <span class="text-xl font-extrabold text-amber-600 font-mono">Rp {{ number_format($totalClusterPoolIncentive, 0, ',', '.') }}</span>
                    <span class="text-[11px] text-amber-700 font-semibold block mt-1">Komisi omset + bonus NOA</span>
                </div>
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="coins" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- 4. Top Performer Sales -->
            <div class="p-4 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-600 text-white shadow-md shadow-amber-500/20 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-amber-100 block mb-0.5 uppercase tracking-wider">Juara 1 Bulan Ini</span>
                    <h3 class="text-base font-extrabold text-white truncate max-w-[140px]">{{ $topSales->name ?? '-' }}</h3>
                    <span class="text-[11px] font-mono font-extrabold text-amber-100 block mt-1">
                        {{ $topSales->achievement_pct ?? 0 }}% dari Target
                    </span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-xs text-white flex items-center justify-center">
                    <i data-lucide="award" class="w-6 h-6"></i>
                </div>
            </div>

        </div>

        <!-- ============================================== -->
        <!-- PODIUM GAMIFIKASI 3 BESAR (TOP 3 KANVASER)     -->
        <!-- ============================================== -->
        <div class="bg-gradient-to-b from-slate-900 via-slate-900 to-slate-950 rounded-2xl p-6 mb-8 text-white shadow-xl border border-slate-800">
            <div class="text-center mb-6">
                <span class="px-3 py-1 rounded-full text-[10px] font-extrabold tracking-widest bg-amber-500/20 text-amber-400 border border-amber-500/40 uppercase">
                    HALL OF FAME &bull; TOP KANVASER TELKOMSEL
                </span>
                <h3 class="text-xl font-extrabold text-white mt-2">Podium Kehormatan Tenaga Penjual Lapangan</h3>
                <p class="text-xs text-slate-400 mt-0.5">Peringkat tertinggi berdasarkan persentase capaian target revenue dan kedisiplinan rute PJP</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end max-w-4xl mx-auto pt-4">
                
                <!-- PODIUM 2 (JUARA 2 - SILVER 🥈) -->
                @if(isset($leaderboard[1]))
                    @php $p2 = $leaderboard[1]; @endphp
                    <div class="order-2 md:order-1 flex flex-col items-center">
                        <!-- Avatar & Silver Medal -->
                        <div class="relative mb-3">
                            <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-slate-400 to-slate-200 p-1 shadow-lg shadow-slate-500/20">
                                <div class="w-full h-full rounded-xl bg-slate-800 flex items-center justify-center font-extrabold text-xl text-white">
                                    {{ substr($p2->name, 0, 2) }}
                                </div>
                            </div>
                            <span class="absolute -bottom-2 -right-2 w-7 h-7 rounded-full bg-slate-300 text-slate-900 font-extrabold text-xs flex items-center justify-center border-2 border-slate-900 shadow-md">
                                2
                            </span>
                        </div>

                        <!-- Card Body -->
                        <div class="w-full bg-slate-800/80 rounded-2xl p-4 text-center border border-slate-700/80 backdrop-blur-xs">
                            <span class="text-[10px] font-bold text-slate-400 block font-mono">{{ $p2->code }} &bull; {{ $p2->tap_name }}</span>
                            <h4 class="font-extrabold text-sm text-white mt-0.5">{{ $p2->name }}</h4>
                            <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-extrabold bg-blue-500/20 text-blue-300 border border-blue-500/30">
                                {{ $p2->badge_title }}
                            </span>

                            <div class="mt-3 pt-3 border-t border-slate-700/60 space-y-1 text-xs">
                                <div class="flex justify-between text-slate-400 text-[11px]">
                                    <span>Pencapaian:</span>
                                    <strong class="text-blue-400 font-mono">{{ $p2->achievement_pct }}%</strong>
                                </div>
                                <div class="flex justify-between text-slate-400 text-[11px]">
                                    <span>Total Omset:</span>
                                    <strong class="text-white font-mono">Rp {{ number_format($p2->final_revenue, 0, ',', '.') }}</strong>
                                </div>
                                <div class="flex justify-between text-slate-400 text-[11px] pt-1 border-t border-slate-700/40">
                                    <span class="text-amber-300 font-bold">Est. Insentif:</span>
                                    <strong class="text-amber-400 font-mono font-extrabold">Rp {{ number_format($p2->total_incentive, 0, ',', '.') }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- PODIUM 1 (JUARA 1 - GOLD 🥇 / CENTER HIGHEST) -->
                @if(isset($leaderboard[0]))
                    @php $p1 = $leaderboard[0]; @endphp
                    <div class="order-1 md:order-2 flex flex-col items-center -mt-6">
                        <!-- Crown / Trophy -->
                        <div class="w-10 h-10 rounded-full bg-amber-500/20 border border-amber-400/50 flex items-center justify-center text-amber-400 mb-2 animate-bounce">
                            <i data-lucide="crown" class="w-6 h-6"></i>
                        </div>

                        <!-- Avatar with Golden Glow Ring -->
                        <div class="relative mb-3">
                            <div class="w-24 h-24 rounded-2xl bg-gradient-to-tr from-amber-600 via-yellow-400 to-amber-200 p-1.5 shadow-xl shadow-amber-500/30">
                                <div class="w-full h-full rounded-xl bg-slate-900 flex items-center justify-center font-extrabold text-2xl text-amber-300">
                                    {{ substr($p1->name, 0, 2) }}
                                </div>
                            </div>
                            <span class="absolute -bottom-2 -right-2 w-8 h-8 rounded-full bg-gradient-to-tr from-amber-500 to-yellow-300 text-slate-950 font-extrabold text-sm flex items-center justify-center border-2 border-slate-900 shadow-md">
                                1
                            </span>
                        </div>

                        <!-- Card Body (Taller & Gold Highlighted) -->
                        <div class="w-full bg-gradient-to-b from-slate-800 to-slate-900 rounded-2xl p-5 text-center border-2 border-amber-500/50 shadow-2xl relative overflow-hidden">
                            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-amber-500 via-yellow-300 to-amber-500"></div>
                            
                            <span class="text-[10px] font-bold text-amber-400 block font-mono">{{ $p1->code }} &bull; {{ $p1->tap_name }}</span>
                            <h4 class="font-extrabold text-base text-white mt-0.5">{{ $p1->name }}</h4>
                            <span class="inline-block mt-1 px-2.5 py-0.5 rounded text-[10px] font-extrabold bg-amber-500 text-slate-950 shadow-xs">
                                🏆 {{ $p1->badge_title }}
                            </span>

                            <div class="mt-4 pt-3 border-t border-slate-700/80 space-y-1.5 text-xs">
                                <div class="flex justify-between text-slate-300 text-xs">
                                    <span>Pencapaian:</span>
                                    <strong class="text-amber-400 font-mono text-sm font-extrabold">{{ $p1->achievement_pct }}%</strong>
                                </div>
                                <div class="flex justify-between text-slate-300 text-xs">
                                    <span>Total Omset:</span>
                                    <strong class="text-white font-mono font-bold">Rp {{ number_format($p1->final_revenue, 0, ',', '.') }}</strong>
                                </div>
                                <div class="flex justify-between text-slate-300 text-xs pt-1.5 border-t border-slate-700/60">
                                    <span class="text-emerald-400 font-bold">Total Insentif:</span>
                                    <strong class="text-emerald-400 font-mono text-sm font-extrabold">Rp {{ number_format($p1->total_incentive, 0, ',', '.') }}</strong>
                                </div>
                            </div>

                            <button type="button" @click="openSlipModal({{ json_encode($p1) }})" class="mt-4 w-full py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold rounded-xl text-xs transition-all shadow-md active:scale-95 flex items-center justify-center gap-1.5">
                                <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
                                <span>Rincian Slip Komisi</span>
                            </button>
                        </div>
                    </div>
                @endif

                <!-- PODIUM 3 (JUARA 3 - BRONZE 🥉) -->
                @if(isset($leaderboard[2]))
                    @php $p3 = $leaderboard[2]; @endphp
                    <div class="order-3 flex flex-col items-center">
                        <!-- Avatar & Bronze Medal -->
                        <div class="relative mb-3">
                            <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-amber-800 to-amber-600 p-1 shadow-lg shadow-amber-900/20">
                                <div class="w-full h-full rounded-xl bg-slate-800 flex items-center justify-center font-extrabold text-xl text-white">
                                    {{ substr($p3->name, 0, 2) }}
                                </div>
                            </div>
                            <span class="absolute -bottom-2 -right-2 w-7 h-7 rounded-full bg-amber-700 text-white font-extrabold text-xs flex items-center justify-center border-2 border-slate-900 shadow-md">
                                3
                            </span>
                        </div>

                        <!-- Card Body -->
                        <div class="w-full bg-slate-800/80 rounded-2xl p-4 text-center border border-slate-700/80 backdrop-blur-xs">
                            <span class="text-[10px] font-bold text-slate-400 block font-mono">{{ $p3->code }} &bull; {{ $p3->tap_name }}</span>
                            <h4 class="font-extrabold text-sm text-white mt-0.5">{{ $p3->name }}</h4>
                            <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-extrabold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                {{ $p3->badge_title }}
                            </span>

                            <div class="mt-3 pt-3 border-t border-slate-700/60 space-y-1 text-xs">
                                <div class="flex justify-between text-slate-400 text-[11px]">
                                    <span>Pencapaian:</span>
                                    <strong class="text-emerald-400 font-mono">{{ $p3->achievement_pct }}%</strong>
                                </div>
                                <div class="flex justify-between text-slate-400 text-[11px]">
                                    <span>Total Omset:</span>
                                    <strong class="text-white font-mono">Rp {{ number_format($p3->final_revenue, 0, ',', '.') }}</strong>
                                </div>
                                <div class="flex justify-between text-slate-400 text-[11px] pt-1 border-t border-slate-700/40">
                                    <span class="text-amber-300 font-bold">Est. Insentif:</span>
                                    <strong class="text-amber-400 font-mono font-extrabold">Rp {{ number_format($p3->total_incentive, 0, ',', '.') }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>

        <!-- ============================================== -->
        <!-- TABEL RINCIAN PERFORMA & KOMISI KANVASER       -->
        <!-- ============================================== -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden mb-8">
            <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
                <div class="flex items-center gap-2">
                    <i data-lucide="calculator" class="w-4 h-4 text-amber-600"></i>
                    <h3 class="font-extrabold text-slate-900 text-xs uppercase tracking-wider">
                        Tabel Rekapitulasi Target, NOA & Estimasi Komisi Tim Sales
                    </h3>
                </div>
                <span class="text-xs text-slate-400 font-mono">{{ count($leaderboard) }} Kanvaser Terdaftar</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4 text-center w-14">Rank</th>
                            <th class="py-3 px-4">Nama Kanvaser</th>
                            <th class="py-3 px-4">Target Revenue</th>
                            <th class="py-3 px-4">Realisasi Omset</th>
                            <th class="py-3 px-4 text-center">Capaian %</th>
                            <th class="py-3 px-4 text-center">Produk (SA / VCR)</th>
                            <th class="py-3 px-4 text-center">NOA (Outlet Baru)</th>
                            <th class="py-3 px-4 text-center">Tier & Rate</th>
                            <th class="py-3 px-4 text-right">Total Insentif</th>
                            <th class="py-3 px-4 text-center">Status Payout</th>
                            <th class="py-3 px-4 text-center w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($leaderboard as $row)
                            <tr class="hover:bg-amber-50/40 transition-colors {{ $row->rank === 1 ? 'bg-amber-50/20' : '' }}">
                                
                                <!-- Rank Badge -->
                                <td class="py-3.5 px-4 text-center">
                                    @if($row->rank === 1)
                                        <span class="w-7 h-7 rounded-xl bg-amber-500 text-white font-extrabold font-mono text-xs inline-flex items-center justify-center shadow-xs">🥇 1</span>
                                    @elseif($row->rank === 2)
                                        <span class="w-7 h-7 rounded-xl bg-slate-400 text-white font-extrabold font-mono text-xs inline-flex items-center justify-center shadow-xs">🥈 2</span>
                                    @elseif($row->rank === 3)
                                        <span class="w-7 h-7 rounded-xl bg-amber-700 text-white font-extrabold font-mono text-xs inline-flex items-center justify-center shadow-xs">🥉 3</span>
                                    @else
                                        <span class="w-6 h-6 rounded-lg bg-slate-100 text-slate-600 font-mono font-bold text-xs inline-flex items-center justify-center">{{ $row->rank }}</span>
                                    @endif
                                </td>

                                <!-- Profile -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-xl {{ $row->avatar_color }} text-white font-extrabold text-xs flex items-center justify-center shrink-0">
                                            {{ substr($row->name, 0, 2) }}
                                        </div>
                                        <div>
                                            <span class="font-extrabold text-slate-900 block">{{ $row->name }}</span>
                                            <span class="text-[10px] text-slate-400 font-mono">{{ $row->code }} &bull; {{ $row->tap_name }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Target -->
                                <td class="py-3.5 px-4 font-mono font-semibold text-slate-600">
                                    Rp {{ number_format($row->target_revenue, 0, ',', '.') }}
                                </td>

                                <!-- Realisasi -->
                                <td class="py-3.5 px-4 font-mono font-extrabold text-slate-900">
                                    Rp {{ number_format($row->final_revenue, 0, ',', '.') }}
                                </td>

                                <!-- Capaian Progress Bar -->
                                <td class="py-3.5 px-4 text-center">
                                    <div class="inline-flex flex-col items-center">
                                        <span class="font-mono font-extrabold text-xs {{ $row->achievement_pct >= 100 ? 'text-emerald-700' : 'text-amber-700' }}">
                                            {{ $row->achievement_pct }}%
                                        </span>
                                        <div class="w-16 h-1.5 bg-slate-100 rounded-full overflow-hidden mt-1">
                                            <div class="h-full rounded-full {{ $row->achievement_pct >= 100 ? 'bg-emerald-500' : 'bg-amber-500' }}" style="width: {{ min(100, $row->achievement_pct) }}%"></div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Qty SA & Voucher -->
                                <td class="py-3.5 px-4 text-center font-mono text-[11px]">
                                    <span class="text-rose-700 font-bold">{{ $row->real_sa_pcs }} SA</span>
                                    <span class="text-slate-300">/</span>
                                    <span class="text-indigo-700 font-bold">{{ $row->real_vcr_pcs }} VCR</span>
                                </td>

                                <!-- NOA -->
                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                        +{{ $row->real_noa }} Toko
                                    </span>
                                </td>

                                <!-- Tier & Rate -->
                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold {{ $row->achievement_pct >= 100 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                        {{ $row->commission_rate }}%
                                    </span>
                                </td>

                                <!-- Total Insentif -->
                                <td class="py-3.5 px-4 text-right font-mono font-extrabold text-emerald-700 text-xs">
                                    Rp {{ number_format($row->total_incentive, 0, ',', '.') }}
                                </td>

                                <!-- Status Payout -->
                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $row->payout_status === 'READY FOR PAYOUT' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $row->payout_status }}
                                    </span>
                                </td>

                                <!-- Aksi -->
                                <td class="py-3.5 px-4 text-center">
                                    <button type="button" @click="openSlipModal({{ json_encode($row) }})" class="px-2.5 py-1 bg-slate-100 hover:bg-amber-600 hover:text-white text-slate-700 rounded-lg text-xs font-bold transition-all shadow-2xs">
                                        Rincian
                                    </button>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- MODAL 1: RINCIAN SLIP PERHITUNGAN KOMISI       -->
        <!-- ============================================== -->
        <div x-show="slipModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="slipModalOpen = false" class="bg-white rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-200">
                <div class="p-4 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800">
                    <div class="flex items-center gap-2">
                        <i data-lucide="receipt" class="w-4 h-4 text-amber-400"></i>
                        <h4 class="font-bold text-xs uppercase tracking-wider text-white">Slip Rincian Insentif Sales Kanvaser</h4>
                    </div>
                    <button type="button" @click="slipModalOpen = false" class="text-slate-400 hover:text-white">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="p-6">
                    <!-- Sales Header -->
                    <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                        <div>
                            <span class="text-[10px] font-mono text-slate-400 uppercase">Tenaga Kanvaser:</span>
                            <h3 class="text-base font-extrabold text-slate-900" x-text="selectedSales ? selectedSales.name : ''"></h3>
                            <span class="text-xs text-slate-500 font-mono" x-text="selectedSales ? (selectedSales.code + ' • ' + selectedSales.tap_name) : ''"></span>
                        </div>
                        <div class="text-right">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-extrabold bg-amber-100 text-amber-800 border border-amber-200" x-text="selectedSales ? ('Rank #' + selectedSales.rank) : ''"></span>
                            <span class="text-[11px] font-bold text-emerald-600 block mt-1" x-text="selectedSales ? (selectedSales.achievement_pct + '% Target') : ''"></span>
                        </div>
                    </div>

                    <!-- Breakdown Komisi Table -->
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 mb-4 space-y-2.5 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-600">Realisasi Total Omset:</span>
                            <span class="font-bold font-mono text-slate-900" x-text="'Rp ' + Number(selectedSales?.final_revenue || 0).toLocaleString('id-ID')"></span>
                        </div>

                        <div class="flex items-center justify-between pt-1 border-t border-slate-200/80">
                            <div>
                                <span class="font-bold text-slate-800">1. Komisi Omset Penjualan</span>
                                <span class="block text-[10px] text-slate-400 font-mono" x-text="'Tarif: ' + selectedSales?.commission_rate + '% (' + selectedSales?.tier_badge + ')'"></span>
                            </div>
                            <span class="font-bold font-mono text-slate-800" x-text="'Rp ' + Number(selectedSales?.commission_amount || 0).toLocaleString('id-ID')"></span>
                        </div>

                        <div class="flex items-center justify-between pt-1 border-t border-slate-200/80">
                            <div>
                                <span class="font-bold text-slate-800">2. Bonus Prestasi Juara</span>
                                <span class="block text-[10px] text-slate-400 font-mono" x-text="selectedSales?.rank <= 2 ? 'Bonus Peringkat Top Kanvaser' : 'Tidak memenuhi syarat podium'"></span>
                            </div>
                            <span class="font-bold font-mono text-emerald-700" x-text="'Rp ' + Number(selectedSales?.bonus_champion || 0).toLocaleString('id-ID')"></span>
                        </div>

                        <div class="flex items-center justify-between pt-1 border-t border-slate-200/80">
                            <div>
                                <span class="font-bold text-slate-800">3. Insentif Toko Baru (NOA)</span>
                                <span class="block text-[10px] text-slate-400 font-mono" x-text="selectedSales?.real_noa + ' Toko Baru x Rp 75.000 / Toko'"></span>
                            </div>
                            <span class="font-bold font-mono text-purple-700" x-text="'Rp ' + Number(selectedSales?.noa_bonus || 0).toLocaleString('id-ID')"></span>
                        </div>

                        <div class="flex items-center justify-between pt-2.5 border-t-2 border-slate-300">
                            <div>
                                <span class="font-extrabold text-sm text-slate-900">Total Take Home Insentif:</span>
                                <span class="block text-[10px] text-slate-500">Akan dicairkan bersamaan dengan payroll</span>
                            </div>
                            <span class="font-mono text-base font-extrabold text-emerald-700" x-text="'Rp ' + Number(selectedSales?.total_incentive || 0).toLocaleString('id-ID')"></span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <span class="text-[11px] text-slate-400">Verifikasi: HRD & Finance Approval</span>
                        <button type="button" @click="slipModalOpen = false" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- MODAL 2: ATURAN & SKEMA TIER KOMISI            -->
        <!-- ============================================== -->
        <div x-show="ruleModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="ruleModalOpen = false" class="bg-white rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-200">
                <div class="p-4 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800">
                    <div class="flex items-center gap-2">
                        <i data-lucide="award" class="w-4 h-4 text-amber-400"></i>
                        <h4 class="font-bold text-xs uppercase tracking-wider text-white">Aturan & Skema Perhitungan Insentif Kanvaser</h4>
                    </div>
                    <button type="button" @click="ruleModalOpen = false" class="text-slate-400 hover:text-white">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="p-6 text-xs space-y-4">
                    <p class="text-slate-600 leading-relaxed">
                        Skema insentif dirancang untuk memacu akselerasi penjualan kartu perdana kuota (SA), voucher fisik Telkomsel, serta penetrasi toko baru (New Open Account) di seluruh cluster:
                    </p>

                    <div class="space-y-2">
                        <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200">
                            <div class="flex justify-between items-center font-bold text-emerald-900">
                                <span>Tier 3: Pencapaian &ge; 115% Target</span>
                                <span class="px-2 py-0.5 rounded bg-emerald-600 text-white font-mono text-[10px]">3.5% + Bonus Rp 1.5 Jt</span>
                            </div>
                            <span class="text-[11px] text-emerald-700 block mt-1">Diberikan untuk kanvaser performa superior dengan kualifikasi podium 1.</span>
                        </div>

                        <div class="p-3 rounded-xl bg-blue-50 border border-blue-200">
                            <div class="flex justify-between items-center font-bold text-blue-900">
                                <span>Tier 2: Pencapaian 100% - 114% Target</span>
                                <span class="px-2 py-0.5 rounded bg-blue-600 text-white font-mono text-[10px]">3.0% + Bonus Rp 750 Rb</span>
                            </div>
                            <span class="text-[11px] text-blue-700 block mt-1">Diberikan untuk kanvaser yang berhasil menembus target bulanan.</span>
                        </div>

                        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200">
                            <div class="flex justify-between items-center font-bold text-amber-900">
                                <span>Tier 1: Pencapaian 90% - 99% Target</span>
                                <span class="px-2 py-0.5 rounded bg-amber-600 text-white font-mono text-[10px]">2.5%</span>
                            </div>
                            <span class="text-[11px] text-amber-700 block mt-1">Kualifikasi komisi standar kanvaser aktif.</span>
                        </div>

                        <div class="p-3 rounded-xl bg-purple-50 border border-purple-200">
                            <div class="flex justify-between items-center font-bold text-purple-900">
                                <span>Bonus Akuisisi Toko Baru (NOA)</span>
                                <span class="px-2 py-0.5 rounded bg-purple-600 text-white font-mono text-[10px]">Rp 75.000 / Outlet</span>
                            </div>
                            <span class="text-[11px] text-purple-700 block mt-1">Berlaku untuk toko baru yang sudah check-in GPS & melakukan order perdana minimal Rp 500.000.</span>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2 border-t border-slate-100">
                        <button type="button" @click="ruleModalOpen = false" class="px-4 py-2 bg-slate-900 text-white font-bold rounded-xl text-xs">
                            Paham & Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function salesTargetsApp() {
            return {
                slipModalOpen: false,
                ruleModalOpen: false,
                selectedSales: null,

                openSlipModal(sales) {
                    this.selectedSales = sales;
                    this.slipModalOpen = true;
                }
            };
        }
    </script>
    @endpush
</x-app-layout>

