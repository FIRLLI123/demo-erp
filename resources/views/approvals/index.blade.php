<x-app-layout>
    <x-slot name="title">Pusat Persetujuan Terpadu</x-slot>
    <x-slot name="headerTitle">Pusat Persetujuan Terpadu</x-slot>
    <x-slot name="badge">Otorisasi & Kontrol</x-slot>

    <div class="space-y-6" x-data="approvalCenter()">

        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                        Otorisasi Terpadu
                    </span>
                    <span class="text-xs text-slate-400">Multi-Divisi (Sales, Finance, Purchasing)</span>
                </div>
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Pusat Persetujuan (Approval Center)</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Verifikasi otorisasi Sales Order kredit/tempo, permintaan limit plafon toko, dan Purchase Order bernilai besar.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                    <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i>
                    <span>Role: Finance & Otorisator</span>
                </span>
            </div>
        </div>

        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Pending Action Card -->
            <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Menunggu Persetujuan</p>
                    <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ $kpi['pending_count'] }} <span class="text-xs font-normal text-slate-400">dokumen</span></h3>
                    <p class="text-[11px] text-amber-600 font-semibold mt-1">Total Rp {{ number_format($kpi['pending_amount'], 0, ',', '.') }}</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-100">
                    <i data-lucide="clock" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- Sales Order Pending -->
            <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Pending Sales Order</p>
                    <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ $kpi['so_pending'] }} <span class="text-xs font-normal text-slate-400">order</span></h3>
                    <p class="text-[11px] text-slate-400 mt-1">Melampaui limit / tempo kredit</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100">
                    <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- Credit Limit Request Pending -->
            <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Pengajuan Plafon Kredit</p>
                    <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ $kpi['credit_pending'] }} <span class="text-xs font-normal text-slate-400">toko</span></h3>
                    <p class="text-[11px] text-slate-400 mt-1">Permintaan naik limit outlet</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 border border-purple-100">
                    <i data-lucide="credit-card" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- PO Principal Pending -->
            <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Pengadaan PO Principal</p>
                    <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ $kpi['po_pending'] }} <span class="text-xs font-normal text-slate-400">pengadaan</span></h3>
                    <p class="text-[11px] text-slate-400 mt-1">Buffer stok bernilai besar</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                </div>
            </div>
        </div>

        <!-- Main Content Box -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            
            <!-- Filter Toolbar -->
            <div class="p-4 border-b border-slate-200 bg-slate-50/50 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                
                <!-- Tabs Status -->
                <div class="flex items-center gap-1 bg-slate-200/60 p-1 rounded-xl">
                    <a href="{{ route('approvals.index', ['tab' => 'pending', 'type' => $currentType, 'q' => $search]) }}"
                       class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $currentTab === 'pending' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        Menunggu Tindakan
                        @if($kpi['pending_count'] > 0)
                            <span class="ml-1.5 px-1.5 py-0.2 bg-amber-500 text-white text-[10px] rounded-full font-bold">
                                {{ $kpi['pending_count'] }}
                            </span>
                        @endif
                    </a>
                    <a href="{{ route('approvals.index', ['tab' => 'history', 'type' => $currentType, 'q' => $search]) }}"
                       class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $currentTab === 'history' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        Riwayat Selesai
                        <span class="ml-1 text-[10px] text-slate-400 font-mono">({{ $kpi['approved_count'] + $kpi['rejected_count'] }})</span>
                    </a>
                    <a href="{{ route('approvals.index', ['tab' => 'all', 'type' => $currentType, 'q' => $search]) }}"
                       class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $currentTab === 'all' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        Semua
                    </a>
                </div>

                <!-- Type Filter & Search -->
                <form action="{{ route('approvals.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                    <input type="hidden" name="tab" value="{{ $currentTab }}">
                    
                    <!-- Filter Type -->
                    <select name="type" onchange="this.form.submit()" class="text-xs bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-slate-700 font-medium focus:outline-none focus:ring-1 focus:ring-slate-400">
                        <option value="ALL" {{ $currentType === 'ALL' ? 'selected' : '' }}>Semua Jenis</option>
                        <option value="SALES_ORDER" {{ $currentType === 'SALES_ORDER' ? 'selected' : '' }}>Sales Order (SO)</option>
                        <option value="CREDIT_LIMIT" {{ $currentType === 'CREDIT_LIMIT' ? 'selected' : '' }}>Plafon Kredit Toko</option>
                        <option value="PURCHASE_ORDER" {{ $currentType === 'PURCHASE_ORDER' ? 'selected' : '' }}>Purchase Order (PO)</option>
                    </select>

                    <!-- Search Input -->
                    <div class="relative">
                        <input type="text" name="q" value="{{ $search }}" placeholder="Cari dokumen, toko, pengaju..." 
                               class="text-xs bg-white border border-slate-200 rounded-lg pl-8 pr-3 py-1.5 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-400 w-52 sm:w-64">
                        <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5"></i>
                    </div>

                    @if(!empty($search) || $currentType !== 'ALL')
                        <a href="{{ route('approvals.index', ['tab' => $currentTab]) }}" class="text-xs text-rose-600 hover:text-rose-700 px-2 py-1 font-medium">Reset</a>
                    @endif
                </form>
            </div>

            <!-- Table Listing -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100/70 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider text-[10px]">
                            <th class="py-3 px-4">Dokumen</th>
                            <th class="py-3 px-4">Jenis Pengajuan</th>
                            <th class="py-3 px-4">Pihak Terkait & Pemohon</th>
                            <th class="py-3 px-4 text-right">Nilai Nominal</th>
                            <th class="py-3 px-4">Alasan & Keterangan</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Aksi Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($approvals as $item)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                
                                <!-- Nomor Dokumen & Tanggal -->
                                <td class="py-3.5 px-4">
                                    <span class="font-mono font-bold text-slate-900 block">{{ $item->doc_number }}</span>
                                    <span class="text-[10px] text-slate-400 flex items-center gap-1 mt-0.5">
                                        <i data-lucide="calendar" class="w-3 h-3"></i>
                                        {{ \Carbon\Carbon::parse($item->created_at)->format('d M Y, H:i') }}
                                    </span>
                                </td>

                                <!-- Jenis Pengajuan -->
                                <td class="py-3.5 px-4">
                                    @if($item->approval_type === 'SALES_ORDER')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                            <i data-lucide="shopping-bag" class="w-3 h-3"></i> Sales Order
                                        </span>
                                    @elseif($item->approval_type === 'CREDIT_LIMIT')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                            <i data-lucide="credit-card" class="w-3 h-3"></i> Limit Kredit Toko
                                        </span>
                                    @elseif($item->approval_type === 'PURCHASE_ORDER')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i data-lucide="file-text" class="w-3 h-3"></i> PO Principal
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                                            {{ $item->approval_type }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Pihak Terkait & Pemohon -->
                                <td class="py-3.5 px-4">
                                    <span class="font-semibold text-slate-900 block">{{ $item->party_name }}</span>
                                    <span class="text-[10px] text-slate-500 flex items-center gap-1 mt-0.5">
                                        <i data-lucide="user" class="w-3 h-3 text-slate-400"></i>
                                        Diajukan oleh: <strong class="font-medium text-slate-600">{{ $item->requestor_name }}</strong>
                                    </span>
                                </td>

                                <!-- Nilai Nominal -->
                                <td class="py-3.5 px-4 text-right">
                                    <span class="font-bold text-slate-900 block font-mono">
                                        Rp {{ number_format($item->requested_amount, 0, ',', '.') }}
                                    </span>
                                    @if($item->approval_type === 'CREDIT_LIMIT')
                                        <span class="text-[10px] text-purple-600 font-medium">Pengajuan Plafon Baru</span>
                                    @elseif($item->approval_type === 'SALES_ORDER')
                                        <span class="text-[10px] text-blue-600 font-medium">Nilai Transaksi</span>
                                    @endif
                                </td>

                                <!-- Alasan & Keterangan -->
                                <td class="py-3.5 px-4 max-w-xs">
                                    <p class="text-slate-600 text-[11px] leading-relaxed line-clamp-2" title="{{ $item->description }}">
                                        {{ $item->description ?: '-' }}
                                    </p>
                                    @if($item->approver_notes && $item->status !== 'PENDING')
                                        <div class="mt-1.5 p-1.5 bg-slate-50 border border-slate-200 rounded text-[10px] text-slate-700">
                                            <span class="font-semibold text-slate-900">Catatan Otorisator:</span> {{ $item->approver_notes }}
                                        </div>
                                    @endif
                                </td>

                                <!-- Status Badge -->
                                <td class="py-3.5 px-4 text-center">
                                    @if($item->status === 'PENDING')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            Menunggu Otorisasi
                                        </span>
                                    @elseif($item->status === 'APPROVED')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i data-lucide="check" class="w-3 h-3"></i>
                                            Disetujui
                                        </span>
                                        <span class="block text-[9px] text-slate-400 mt-0.5">
                                            {{ \Carbon\Carbon::parse($item->action_date)->format('d/m/y H:i') }}
                                        </span>
                                    @elseif($item->status === 'REJECTED')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <i data-lucide="x" class="w-3 h-3"></i>
                                            Ditolak
                                        </span>
                                        <span class="block text-[9px] text-slate-400 mt-0.5">
                                            {{ \Carbon\Carbon::parse($item->action_date)->format('d/m/y H:i') }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Aksi Tindakan -->
                                <td class="py-3.5 px-4 text-right">
                                    @if($item->status === 'PENDING')
                                        <div class="flex items-center justify-end gap-1.5">
                                            <!-- Tombol Setujui -->
                                            <button type="button" 
                                                    @click="openConfirmModal({{ $item->id }}, '{{ $item->doc_number }}', '{{ $item->party_name }}', 'APPROVE', '{{ number_format($item->requested_amount, 0, ',', '.') }}', '{{ $item->approval_type }}')"
                                                    class="px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-700 transition shadow-xs flex items-center gap-1">
                                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                                <span>Setujui</span>
                                            </button>

                                            <!-- Tombol Tolak -->
                                            <button type="button" 
                                                    @click="openConfirmModal({{ $item->id }}, '{{ $item->doc_number }}', '{{ $item->party_name }}', 'REJECT', '{{ number_format($item->requested_amount, 0, ',', '.') }}', '{{ $item->approval_type }}')"
                                                    class="px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-white text-rose-600 border border-rose-200 hover:bg-rose-50 transition shadow-xs flex items-center gap-1">
                                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                                <span>Tolak</span>
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-[11px] text-slate-400 italic">Selesai dievaluasi</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                        <i data-lucide="inbox" class="w-6 h-6"></i>
                                    </div>
                                    <p class="text-xs font-medium text-slate-600">Tidak ada pengajuan dalam kriteria ini</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Semua permohonan telah selesai diproses atau filter tidak cocok.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Konfirmasi Otorisasi (Approve / Reject) -->
        <div x-show="modalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-md w-full p-6 text-left"
                 @click.away="modalOpen = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                
                <form action="{{ route('approvals.process') }}" method="POST" data-no-loading>
                    @csrf
                    <input type="hidden" name="approval_id" :value="selectedId">
                    <input type="hidden" name="action" :value="selectedAction">

                    <!-- Modal Header -->
                    <div class="flex items-start gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                             :class="selectedAction === 'APPROVE' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-rose-50 text-rose-600 border border-rose-100'">
                            <template x-if="selectedAction === 'APPROVE'">
                                <i data-lucide="check-circle" class="w-5 h-5"></i>
                            </template>
                            <template x-if="selectedAction === 'REJECT'">
                                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                            </template>
                        </div>
                        <div>
                            <h4 class="text-base font-bold text-slate-900" 
                                x-text="selectedAction === 'APPROVE' ? 'Konfirmasi Persetujuan Dokumen' : 'Konfirmasi Penolakan Dokumen'"></h4>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Otorisasi keputusan operasional untuk dokumen berikut:
                            </p>
                        </div>
                    </div>

                    <!-- Document Brief -->
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 mb-4 text-xs space-y-1.5">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Nomor Dokumen:</span>
                            <span class="font-mono font-bold text-slate-900" x-text="selectedDoc"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Pihak / Outlet:</span>
                            <span class="font-semibold text-slate-900" x-text="selectedParty"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Nilai Transaksi:</span>
                            <span class="font-mono font-bold text-slate-900" x-text="'Rp ' + selectedAmount"></span>
                        </div>
                    </div>

                    <!-- Notes Input -->
                    <div class="mb-5">
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Catatan / Catatan Evaluasi Otorisator
                            <span class="text-slate-400 font-normal" x-show="selectedAction === 'APPROVE'">(Opsional)</span>
                            <span class="text-rose-500 font-bold" x-show="selectedAction === 'REJECT'">* Alasan penolakan</span>
                        </label>
                        <textarea name="notes" rows="3" 
                                  class="w-full text-xs border border-slate-200 rounded-lg p-2.5 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-400"
                                  :placeholder="selectedAction === 'APPROVE' ? 'Masukkan catatan bila diperlukan (misal: Disetujui dengan syarat tempo maks 14 hari)...' : 'Masukkan alasan penolakan dokumen pengajuan...'"></textarea>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="modalOpen = false" 
                                class="px-4 py-2 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 rounded-lg text-xs font-semibold text-white transition shadow-xs flex items-center gap-1.5"
                                :class="selectedAction === 'APPROVE' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700'">
                            <span x-text="selectedAction === 'APPROVE' ? 'Setujui Dokumen' : 'Tolak Dokumen'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
        function approvalCenter() {
            return {
                modalOpen: false,
                selectedId: null,
                selectedDoc: '',
                selectedParty: '',
                selectedAction: 'APPROVE',
                selectedAmount: '',
                selectedType: '',

                openConfirmModal(id, doc, party, action, amount, type) {
                    this.selectedId = id;
                    this.selectedDoc = doc;
                    this.selectedParty = party;
                    this.selectedAction = action;
                    this.selectedAmount = amount;
                    this.selectedType = type;
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
