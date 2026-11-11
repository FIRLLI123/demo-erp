<x-app-layout>
    <x-slot name="title">Target & Insentif Sales</x-slot>
    <x-slot name="headerTitle">Pencapaian & Insentif Sales</x-slot>
    <x-slot name="badge">Target & Komisi</x-slot>

    <div x-data="salesTargetsApp()">
        
        <!-- Header Halaman Bersih & Ringkas -->
        <div class="mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <i data-lucide="target" class="w-5 h-5 text-slate-700"></i>
                    <span>Target & Pencapaian Sales Kanvaser</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Realisasi omset penjualan, target bulanan, dan perhitungan insentif sales lapangan</p>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" @click="ruleModalOpen = true" class="px-3 py-1.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-bold rounded-xl text-xs flex items-center gap-1.5 transition-all">
                    <i data-lucide="info" class="w-3.5 h-3.5 text-slate-500"></i>
                    <span>Skema Komisi</span>
                </button>

                <button type="button" onclick="window.print()" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 transition-all">
                    <i data-lucide="printer" class="w-3.5 h-3.5 text-slate-300"></i>
                    <span>Cetak Rekap</span>
                </button>
            </div>
        </div>

        <!-- 4 KPI Card Ringkas (Tema Putih Bersih ERP) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 mb-5">
            <div class="p-3.5 rounded-xl bg-white border border-slate-200/90 shadow-2xs">
                <span class="text-[11px] font-bold text-slate-500 block">Target Cluster</span>
                <span class="text-lg font-extrabold text-slate-900 font-mono mt-0.5 block">
                    Rp {{ number_format($totalClusterTarget, 0, ',', '.') }}
                </span>
                <span class="text-[10px] text-slate-400 mt-0.5 block">Periode Bulan Ini</span>
            </div>

            <div class="p-3.5 rounded-xl bg-white border border-slate-200/90 shadow-2xs">
                <span class="text-[11px] font-bold text-slate-500 block">Realisasi Omset</span>
                <span class="text-lg font-extrabold text-slate-900 font-mono mt-0.5 block">
                    Rp {{ number_format($totalClusterRevenue, 0, ',', '.') }}
                </span>
                <span class="text-[10px] text-emerald-600 font-bold mt-0.5 block">
                    {{ $clusterAchievementPct }}% Tercapai
                </span>
            </div>

            <div class="p-3.5 rounded-xl bg-white border border-slate-200/90 shadow-2xs">
                <span class="text-[11px] font-bold text-slate-500 block">Total Pool Insentif</span>
                <span class="text-lg font-extrabold text-emerald-700 font-mono mt-0.5 block">
                    Rp {{ number_format($totalClusterPoolIncentive, 0, ',', '.') }}
                </span>
                <span class="text-[10px] text-slate-400 mt-0.5 block">Komisi omset + insentif NOA</span>
            </div>

            <div class="p-3.5 rounded-xl bg-white border border-slate-200/90 shadow-2xs">
                <span class="text-[11px] font-bold text-slate-500 block">Top Sales Saat Ini</span>
                <span class="text-sm font-extrabold text-slate-900 truncate block mt-0.5">
                    {{ $topSales->name ?? '-' }}
                </span>
                <span class="text-[10px] text-slate-500 font-mono mt-0.5 block">
                    Capaian: <strong class="text-slate-800">{{ $topSales->achievement_pct ?? 0 }}%</strong>
                </span>
            </div>
        </div>

        <!-- 3 Peringkat Teratas (Clean Corporate Card) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 mb-5">
            @foreach(array_slice($leaderboard, 0, 3) as $top)
                <div class="bg-white rounded-xl border p-4 shadow-2xs {{ $top->rank === 1 ? 'border-amber-300 bg-amber-50/10 ring-1 ring-amber-200/50' : 'border-slate-200/90' }}">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-md font-mono font-extrabold text-xs flex items-center justify-center {{ $top->rank === 1 ? 'bg-amber-500 text-white' : ($top->rank === 2 ? 'bg-slate-300 text-slate-800' : 'bg-amber-700 text-white') }}">
                                #{{ $top->rank }}
                            </span>
                            <div>
                                <h4 class="font-bold text-xs text-slate-900">{{ $top->name }}</h4>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $top->code }} &bull; {{ $top->tap_name }}</span>
                            </div>
                        </div>
                        <span class="text-xs font-mono font-extrabold {{ $top->achievement_pct >= 100 ? 'text-emerald-700' : 'text-slate-700' }}">
                            {{ $top->achievement_pct }}%
                        </span>
                    </div>

                    <!-- Progress Bar Simpel -->
                    <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden mb-3">
                        <div class="h-full rounded-full {{ $top->achievement_pct >= 100 ? 'bg-emerald-500' : 'bg-slate-400' }}" style="width: {{ min(100, $top->achievement_pct) }}%"></div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs pt-2 border-t border-slate-100">
                        <div>
                            <span class="text-[10px] text-slate-400 block">Omset:</span>
                            <span class="font-mono font-bold text-slate-800 text-[11px]">Rp {{ number_format($top->final_revenue, 0, ',', '.') }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] text-slate-400 block">Est. Insentif:</span>
                            <span class="font-mono font-extrabold text-emerald-700 text-[11px]">Rp {{ number_format($top->total_incentive, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Tabel Performa & Rekapitulasi Komisi (Gaya ERP Standar) -->
        <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs overflow-hidden mb-6">
            <div class="p-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <span class="font-extrabold text-xs text-slate-800 uppercase tracking-wider">
                    Daftar Pencapaian & Perhitungan Komisi Kanvaser
                </span>
                <span class="text-[11px] text-slate-400 font-mono">{{ count($leaderboard) }} Kanvaser</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3 text-center w-12">No</th>
                            <th class="py-2.5 px-3">Sales Kanvaser</th>
                            <th class="py-2.5 px-3">Target Omset</th>
                            <th class="py-2.5 px-3">Realisasi Omset</th>
                            <th class="py-2.5 px-3 text-center">Capaian</th>
                            <th class="py-2.5 px-3 text-center">Perdana (SA)</th>
                            <th class="py-2.5 px-3 text-center">Voucher</th>
                            <th class="py-2.5 px-3 text-center">Toko Baru</th>
                            <th class="py-2.5 px-3 text-center">Tarif</th>
                            <th class="py-2.5 px-3 text-right">Total Insentif</th>
                            <th class="py-2.5 px-3 text-center w-20">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($leaderboard as $row)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-2.5 px-3 text-center font-mono text-slate-500">
                                    {{ $row->rank }}
                                </td>

                                <td class="py-2.5 px-3">
                                    <span class="font-bold text-slate-900 block">{{ $row->name }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $row->code }} &bull; {{ $row->tap_name }}</span>
                                </td>

                                <td class="py-2.5 px-3 font-mono text-slate-600">
                                    Rp {{ number_format($row->target_revenue, 0, ',', '.') }}
                                </td>

                                <td class="py-2.5 px-3 font-mono font-bold text-slate-900">
                                    Rp {{ number_format($row->final_revenue, 0, ',', '.') }}
                                </td>

                                <td class="py-2.5 px-3 text-center">
                                    <span class="font-mono font-extrabold {{ $row->achievement_pct >= 100 ? 'text-emerald-700' : 'text-slate-700' }}">
                                        {{ $row->achievement_pct }}%
                                    </span>
                                </td>

                                <td class="py-2.5 px-3 text-center font-mono">
                                    {{ number_format($row->real_sa_pcs) }} pcs
                                </td>

                                <td class="py-2.5 px-3 text-center font-mono">
                                    {{ number_format($row->real_vcr_pcs) }} pcs
                                </td>

                                <td class="py-2.5 px-3 text-center font-mono">
                                    +{{ $row->real_noa }}
                                </td>

                                <td class="py-2.5 px-3 text-center font-mono text-slate-600">
                                    {{ $row->commission_rate }}%
                                </td>

                                <td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-700">
                                    Rp {{ number_format($row->total_incentive, 0, ',', '.') }}
                                </td>

                                <td class="py-2.5 px-3 text-center">
                                    <button type="button" @click="openSlipModal({{ json_encode($row) }})" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition-all">
                                        Slip
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- MODAL: SLIP RINCIAN PERHITUNGAN INSENTIF       -->
        <!-- ============================================== -->
        <div x-show="slipModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="slipModalOpen = false" class="bg-white rounded-xl max-w-md w-full overflow-hidden shadow-xl border border-slate-200">
                <div class="px-5 py-3.5 bg-slate-900 text-white flex items-center justify-between">
                    <span class="font-bold text-xs uppercase tracking-wider">Slip Rincian Insentif Sales</span>
                    <button type="button" @click="slipModalOpen = false" class="text-slate-400 hover:text-white">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <div class="p-5">
                    <div class="pb-3 mb-3 border-b border-slate-100 flex justify-between items-start">
                        <div>
                            <span class="text-xs text-slate-400 block">Nama Sales:</span>
                            <h4 class="font-extrabold text-sm text-slate-900" x-text="selectedSales ? selectedSales.name : ''"></h4>
                            <span class="text-xs text-slate-500 font-mono" x-text="selectedSales ? (selectedSales.code + ' - ' + selectedSales.tap_name) : ''"></span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-slate-400 block">Capaian:</span>
                            <span class="text-xs font-bold font-mono text-emerald-700" x-text="selectedSales ? (selectedSales.achievement_pct + '%') : ''"></span>
                        </div>
                    </div>

                    <div class="space-y-2 text-xs mb-4">
                        <div class="flex justify-between py-1 border-b border-slate-100">
                            <span class="text-slate-500">Target Revenue:</span>
                            <span class="font-mono font-bold text-slate-800" x-text="'Rp ' + Number(selectedSales?.target_revenue || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100">
                            <span class="text-slate-500">Realisasi Omset:</span>
                            <span class="font-mono font-bold text-slate-900" x-text="'Rp ' + Number(selectedSales?.final_revenue || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100">
                            <span class="text-slate-500">1. Komisi Omset (<span x-text="selectedSales?.commission_rate"></span>%):</span>
                            <span class="font-mono font-bold text-slate-800" x-text="'Rp ' + Number(selectedSales?.commission_amount || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100">
                            <span class="text-slate-500">2. Bonus Capaian Target:</span>
                            <span class="font-mono font-bold text-slate-800" x-text="'Rp ' + Number(selectedSales?.bonus_champion || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100">
                            <span class="text-slate-500">3. Insentif Toko Baru (NOA):</span>
                            <span class="font-mono font-bold text-slate-800" x-text="'Rp ' + Number(selectedSales?.noa_bonus || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-slate-200">
                            <span class="font-bold text-slate-900">Total Insentif:</span>
                            <span class="font-mono font-extrabold text-sm text-emerald-700" x-text="'Rp ' + Number(selectedSales?.total_incentive || 0).toLocaleString('id-ID')"></span>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2 border-t border-slate-100">
                        <button type="button" @click="slipModalOpen = false" class="px-4 py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-lg text-xs">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- MODAL: ATURAN & SKEMA TIER KOMISI              -->
        <!-- ============================================== -->
        <div x-show="ruleModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="ruleModalOpen = false" class="bg-white rounded-xl max-w-md w-full overflow-hidden shadow-xl border border-slate-200">
                <div class="px-5 py-3.5 bg-slate-900 text-white flex items-center justify-between">
                    <span class="font-bold text-xs uppercase tracking-wider">Skema Perhitungan Insentif</span>
                    <button type="button" @click="ruleModalOpen = false" class="text-slate-400 hover:text-white">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <div class="p-5 text-xs space-y-3">
                    <table class="w-full text-left border border-slate-200 rounded-lg overflow-hidden">
                        <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-2 px-3">Capaian Target</th>
                                <th class="py-2 px-3">Tarif Komisi</th>
                                <th class="py-2 px-3">Bonus Tambahan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr>
                                <td class="py-2 px-3">&ge; 115%</td>
                                <td class="py-2 px-3 font-mono font-bold text-emerald-700">3.5%</td>
                                <td class="py-2 px-3 font-mono text-slate-700">+ Rp 1.500.000</td>
                            </tr>
                            <tr>
                                <td class="py-2 px-3">100% - 114%</td>
                                <td class="py-2 px-3 font-mono font-bold text-blue-700">3.0%</td>
                                <td class="py-2 px-3 font-mono text-slate-700">+ Rp 750.000</td>
                            </tr>
                            <tr>
                                <td class="py-2 px-3">90% - 99%</td>
                                <td class="py-2 px-3 font-mono font-bold text-slate-700">2.5%</td>
                                <td class="py-2 px-3 font-mono text-slate-400">-</td>
                            </tr>
                            <tr>
                                <td class="py-2 px-3">&lt; 90%</td>
                                <td class="py-2 px-3 font-mono text-slate-500">1.5%</td>
                                <td class="py-2 px-3 font-mono text-slate-400">-</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200 text-slate-600 text-[11px]">
                        <strong>Insentif Toko Baru (NOA):</strong> Tambahan Rp 75.000 untuk setiap outlet baru yang berhasil bertransaksi perdana.
                    </div>

                    <div class="flex justify-end pt-2 border-t border-slate-100">
                        <button type="button" @click="ruleModalOpen = false" class="px-4 py-1.5 bg-slate-900 text-white font-bold rounded-lg text-xs">
                            Tutup
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
