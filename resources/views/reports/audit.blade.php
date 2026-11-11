<x-app-layout>
    <x-slot name="title">Audit Trail & Log Aktivitas</x-slot>
    <x-slot name="headerTitle">Audit Trail & Log Aktivitas Sistem</x-slot>
    <x-slot name="badge">Kepatuhan & Audit</x-slot>

    <div class="space-y-6" x-data="auditTrailApp()">

        <!-- Header Halaman Bersih & Ringkas -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        Audit Trail & Kepatuhan
                    </span>
                    <span class="text-xs text-slate-400">ISO 27001 & Compliance Standar ERP</span>
                </div>
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Audit Trail & Log Aktivitas Sistem</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Rekam jejak kronologis setiap otorisasi, mutasi inventori, transaksi penjualan, dan autentikasi pengguna.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                    <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i>
                    <span>Sistem Audit Aktif (24/7)</span>
                </span>
            </div>
        </div>

        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Log Hari Ini -->
            <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Aktivitas Hari Ini</p>
                    <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ $kpi['total_today'] }} <span class="text-xs font-normal text-slate-400">event</span></h3>
                    <p class="text-[11px] text-slate-400 mt-1">Tercatat secara real-time</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0 border border-slate-200">
                    <i data-lucide="activity" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- Kritikal & Otorisasi -->
            <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Aksi Kritikal & Otorisasi</p>
                    <h3 class="text-2xl font-bold text-rose-600 mt-1">{{ $kpi['critical_count'] }} <span class="text-xs font-normal text-slate-400">event</span></h3>
                    <p class="text-[11px] text-rose-500 font-semibold mt-1">Approval plafon & dokumen</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 border border-rose-100">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- Mutasi Stok & Finansial -->
            <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Transaksi Finansial & Stok</p>
                    <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ $kpi['financial_stock'] }} <span class="text-xs font-normal text-slate-400">rekaman</span></h3>
                    <p class="text-[11px] text-slate-400 mt-1">SO, BBM, GR & Transfer</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100">
                    <i data-lucide="database" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- Pengguna Unik Aktif -->
            <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Pengguna Beraktivitas</p>
                    <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ $kpi['active_users'] }} <span class="text-xs font-normal text-slate-400">personil</span></h3>
                    <p class="text-[11px] text-slate-400 mt-1">Sales, kasir, gudang & staf</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 border border-purple-100">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
            </div>
        </div>

        <!-- Main Content Box -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            
            <!-- Filter Toolbar -->
            <div class="p-4 border-b border-slate-200 bg-slate-50/50">
                <form action="{{ route('reports.audit') }}" method="GET" class="flex flex-wrap items-center justify-between gap-3">
                    
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Filter Modul -->
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs text-slate-500 font-semibold">Modul:</span>
                            <select name="module" onchange="this.form.submit()" class="text-xs bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-slate-700 font-medium focus:outline-none focus:ring-1 focus:ring-slate-400">
                                <option value="ALL" {{ $currentModule === 'ALL' ? 'selected' : '' }}>Semua Modul</option>
                                <option value="SALES" {{ $currentModule === 'SALES' ? 'selected' : '' }}>Penjualan (Sales)</option>
                                <option value="FINANCE" {{ $currentModule === 'FINANCE' ? 'selected' : '' }}>Keuangan & Kas (Finance)</option>
                                <option value="INVENTORY" {{ $currentModule === 'INVENTORY' ? 'selected' : '' }}>Gudang & Stok (Inventory)</option>
                                <option value="PROCUREMENT" {{ $currentModule === 'PROCUREMENT' ? 'selected' : '' }}>Pengadaan (PO & GR)</option>
                                <option value="MASTER" {{ $currentModule === 'MASTER' ? 'selected' : '' }}>Master Data</option>
                                <option value="SECURITY" {{ $currentModule === 'SECURITY' ? 'selected' : '' }}>Keamanan & Akun</option>
                            </select>
                        </div>

                        <!-- Filter Tingkat Kepentingan (Severity) -->
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs text-slate-500 font-semibold">Tingkat:</span>
                            <select name="severity" onchange="this.form.submit()" class="text-xs bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-slate-700 font-medium focus:outline-none focus:ring-1 focus:ring-slate-400">
                                <option value="ALL" {{ $currentSeverity === 'ALL' ? 'selected' : '' }}>Semua Tingkat</option>
                                <option value="INFO" {{ $currentSeverity === 'INFO' ? 'selected' : '' }}>INFO (Standar)</option>
                                <option value="WARNING" {{ $currentSeverity === 'WARNING' ? 'selected' : '' }}>PERINGATAN (Warning)</option>
                                <option value="CRITICAL" {{ $currentSeverity === 'CRITICAL' ? 'selected' : '' }}>KRITIKAL (Otorisasi)</option>
                            </select>
                        </div>

                        <!-- Filter Waktu -->
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs text-slate-500 font-semibold">Periode:</span>
                            <select name="date" onchange="this.form.submit()" class="text-xs bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-slate-700 font-medium focus:outline-none focus:ring-1 focus:ring-slate-400">
                                <option value="ALL" {{ $currentDate === 'ALL' ? 'selected' : '' }}>Semua Riwayat</option>
                                <option value="TODAY" {{ $currentDate === 'TODAY' ? 'selected' : '' }}>Hari Ini</option>
                                <option value="WEEK" {{ $currentDate === 'WEEK' ? 'selected' : '' }}>7 Hari Terakhir</option>
                            </select>
                        </div>
                    </div>

                    <!-- Search Input -->
                    <div class="flex items-center gap-2">
                        <div class="relative">
                            <input type="text" name="q" value="{{ $search }}" placeholder="Cari user, dokumen, aksi, IP..." 
                                   class="text-xs bg-white border border-slate-200 rounded-lg pl-8 pr-3 py-1.5 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-400 w-56 sm:w-64">
                            <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5"></i>
                        </div>

                        @if(!empty($search) || $currentModule !== 'ALL' || $currentSeverity !== 'ALL' || $currentDate !== 'ALL')
                            <a href="{{ route('reports.audit') }}" class="text-xs text-rose-600 hover:text-rose-700 px-2 py-1 font-medium">Reset</a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Table Listing -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100/70 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider text-[10px]">
                            <th class="py-3 px-4">Waktu & IP Address</th>
                            <th class="py-3 px-4">Pengguna & Peran</th>
                            <th class="py-3 px-4">Modul & Aksi</th>
                            <th class="py-3 px-4">Dokumen Terkait</th>
                            <th class="py-3 px-4">Deskripsi Aktivitas</th>
                            <th class="py-3 px-4 text-center">Tingkat</th>
                            <th class="py-3 px-4 text-right">Data Payload</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($logs as $log)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                
                                <!-- Waktu & IP Address -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="font-mono font-bold text-slate-900 block text-[11px]">
                                        {{ \Carbon\Carbon::parse($log->created_at)->format('d M Y, H:i:s') }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 flex items-center gap-1 mt-0.5 truncate max-w-[190px]" title="{{ $log->ip_address }} | {{ $log->user_agent }}">
                                        <i data-lucide="globe" class="w-3 h-3 text-slate-400 shrink-0"></i>
                                        {{ $log->ip_address ?: '-' }}
                                    </span>
                                </td>

                                <!-- Pengguna & Peran -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-[10px]">
                                            {{ substr($log->user_name, 0, 2) }}
                                        </div>
                                        <div>
                                            <span class="font-semibold text-slate-900 block">{{ $log->user_name }}</span>
                                            <span class="text-[10px] text-slate-500 block">{{ $log->user_role }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Modul & Aksi -->
                                <td class="py-3.5 px-4">
                                    <div class="space-y-1">
                                        @if($log->module === 'SALES')
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                                Penjualan
                                            </span>
                                        @elseif($log->module === 'FINANCE')
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                                Keuangan
                                            </span>
                                        @elseif($log->module === 'INVENTORY')
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-cyan-50 text-cyan-700 border border-cyan-200">
                                                Gudang
                                            </span>
                                        @elseif($log->module === 'PROCUREMENT')
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Pengadaan
                                            </span>
                                        @elseif($log->module === 'SECURITY')
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                                Keamanan
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                                                {{ $log->module }}
                                            </span>
                                        @endif
                                        <p class="font-mono text-[10px] text-slate-600 block">{{ $log->action }}</p>
                                    </div>
                                </td>

                                <!-- Dokumen Terkait -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if($log->doc_number)
                                        <span class="font-mono font-bold text-slate-800 bg-slate-100 px-2 py-0.5 rounded border border-slate-200 text-[11px]">
                                            {{ $log->doc_number }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">-</span>
                                    @endif
                                </td>

                                <!-- Deskripsi Aktivitas -->
                                <td class="py-3.5 px-4 max-w-sm">
                                    <p class="text-slate-700 text-[11px] leading-relaxed line-clamp-2" title="{{ $log->description }}">
                                        {{ $log->description }}
                                    </p>
                                </td>

                                <!-- Tingkat Kepentingan (Severity) -->
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    @if($log->severity === 'CRITICAL')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-600 animate-pulse"></span>
                                            KRITIKAL
                                        </span>
                                    @elseif($log->severity === 'WARNING')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            PERINGATAN
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600">
                                            INFO
                                        </span>
                                    @endif
                                </td>

                                <!-- Tombol Data Payload -->
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    @if($log->old_values || $log->new_values)
                                        <button type="button" 
                                                @click="viewDetail({{ json_encode($log) }})"
                                                class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200 transition border border-slate-200 flex items-center gap-1 ml-auto">
                                            <i data-lucide="eye" class="w-3.5 h-3.5 text-slate-500"></i>
                                            <span>Snapshot</span>
                                        </button>
                                    @else
                                        <span class="text-[10px] text-slate-400 italic">No snapshot</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                        <i data-lucide="file-clock" class="w-6 h-6"></i>
                                    </div>
                                    <p class="text-xs font-medium text-slate-600">Tidak ada riwayat aktivitas yang cocok</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Silakan sesuaikan filter pencarian atau periode tanggal.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($logs->hasPages())
                <div class="p-4 border-t border-slate-200 bg-slate-50/50 flex items-center justify-between">
                    <p class="text-xs text-slate-500">
                        Menampilkan <span class="font-semibold text-slate-800">{{ $logs->firstItem() }}</span> - <span class="font-semibold text-slate-800">{{ $logs->lastItem() }}</span> dari <span class="font-semibold text-slate-800">{{ $logs->total() }}</span> rekaman audit
                    </p>
                    <div>
                        {{ $logs->links() }}
                    </div>
                </div>
            @endif
        </div>

        <!-- Modal Detail Perubahan Data (Snapshot Before-After) -->
        <div x-show="modalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-xl w-full p-6 text-left"
                 @click.away="modalOpen = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                
                <!-- Modal Header -->
                <div class="flex items-start justify-between pb-3 border-b border-slate-100 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-100 flex items-center justify-center shrink-0">
                            <i data-lucide="database" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h4 class="text-base font-bold text-slate-900">Snapshot Perubahan Data (Audit Log)</h4>
                            <p class="text-xs text-slate-500 mt-0.5">
                                <span class="font-mono font-semibold text-slate-800" x-text="selectedLog.doc_number || selectedLog.action"></span> &bull; 
                                <span x-text="selectedLog.user_name"></span>
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Info Ringkas -->
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 mb-4 text-xs space-y-1">
                    <p class="text-slate-600"><strong class="text-slate-900">Deskripsi:</strong> <span x-text="selectedLog.description"></span></p>
                    <p class="text-slate-500 text-[11px]"><strong class="text-slate-700">Perangkat / IP:</strong> <span x-text="selectedLog.ip_address + ' (' + selectedLog.user_agent + ')'"></span></p>
                </div>

                <!-- Perbandingan Before vs After -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                    <!-- Data Sebelum (Old Values) -->
                    <div class="border border-slate-200 rounded-xl p-3 bg-slate-50/70">
                        <div class="flex items-center gap-1.5 text-xs font-bold text-slate-600 mb-2 pb-1.5 border-b border-slate-200">
                            <i data-lucide="history" class="w-3.5 h-3.5 text-slate-400"></i>
                            <span>Data Sebelum (Before)</span>
                        </div>
                        <template x-if="oldValuesParsed">
                            <dl class="space-y-1.5 text-xs">
                                <template x-for="(val, key) in oldValuesParsed" :key="key">
                                    <div class="flex flex-col">
                                        <dt class="text-[10px] font-mono text-slate-400 uppercase" x-text="key.replace(/_/g, ' ')"></dt>
                                        <dd class="font-semibold text-slate-700 break-words" x-text="val"></dd>
                                    </div>
                                </template>
                            </dl>
                        </template>
                        <template x-if="!oldValuesParsed">
                            <p class="text-xs text-slate-400 italic">Tidak ada data sebelumnya (Entri Baru)</p>
                        </template>
                    </div>

                    <!-- Data Sesudah (New Values) -->
                    <div class="border border-emerald-200 rounded-xl p-3 bg-emerald-50/30">
                        <div class="flex items-center gap-1.5 text-xs font-bold text-emerald-800 mb-2 pb-1.5 border-b border-emerald-200">
                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-600"></i>
                            <span>Data Sesudah (After)</span>
                        </div>
                        <template x-if="newValuesParsed">
                            <dl class="space-y-1.5 text-xs">
                                <template x-for="(val, key) in newValuesParsed" :key="key">
                                    <div class="flex flex-col">
                                        <dt class="text-[10px] font-mono text-emerald-600/70 uppercase" x-text="key.replace(/_/g, ' ')"></dt>
                                        <dd class="font-bold text-emerald-900 break-words" x-text="val"></dd>
                                    </div>
                                </template>
                            </dl>
                        </template>
                        <template x-if="!newValuesParsed">
                            <p class="text-xs text-slate-400 italic">Tidak ada data sesudah</p>
                        </template>
                    </div>
                </div>

                <!-- Footer Action -->
                <div class="flex items-center justify-end pt-3 border-t border-slate-100">
                    <button type="button" @click="modalOpen = false" 
                            class="px-4 py-2 rounded-lg text-xs font-semibold bg-slate-900 text-white hover:bg-slate-800 transition">
                        Tutup Snapshot
                    </button>
                </div>
            </div>
        </div>

    </div>

    <script>
        function auditTrailApp() {
            return {
                modalOpen: false,
                selectedLog: {},
                oldValuesParsed: null,
                newValuesParsed: null,

                viewDetail(log) {
                    this.selectedLog = log;
                    
                    try {
                        this.oldValuesParsed = log.old_values ? (typeof log.old_values === 'string' ? JSON.parse(log.old_values) : log.old_values) : null;
                    } catch(e) {
                        this.oldValuesParsed = null;
                    }

                    try {
                        this.newValuesParsed = log.new_values ? (typeof log.new_values === 'string' ? JSON.parse(log.new_values) : log.new_values) : null;
                    } catch(e) {
                        this.newValuesParsed = null;
                    }

                    this.modalOpen = true;

                    this.$nextTick(() => {
                        if (window.lucide) {
                            window.lucide.createIcons();
                        }
                    });
                }
            };
        }

        document.addEventListener('DOMContentLoaded', function () {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    </script>
</x-app-layout>
