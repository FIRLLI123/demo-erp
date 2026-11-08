<x-app-layout>
    <x-slot name="title">Piutang (AR) & Pelunasan (FP / BBM)</x-slot>
    <x-slot name="headerTitle">Keuangan, Piutang & Kasir</x-slot>
    <x-slot name="badge">DocType: AR • FP • BBM</x-slot>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between">
            <span class="flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                {{ session('success') }}
            </span>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800">&times;</button>
        </div>
    @endif

    <!-- Process Workflow Banner -->
    <div class="mb-8 p-5 bg-white rounded-2xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 uppercase font-mono">
                Alur Keuangan 3: AR &rarr; FP (TTK) &rarr; BBM
            </span>
            <h2 class="text-base font-bold text-slate-900 mt-1">Penerimaan Pembayaran Tanda Terima Kasir & Bukti Bank Masuk</h2>
            <p class="text-xs text-slate-500 mt-0.5">Mendukung metode Tunai, Transfer (BBM), Titipan Uang, dan Pengurangan melalui Retur.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-mono font-bold">AR</span>
            <i data-lucide="arrow-right" class="w-4 h-4 text-slate-400"></i>
            <span class="px-3 py-1.5 rounded-xl bg-emerald-700 text-white text-xs font-mono font-bold">FP</span>
            <i data-lucide="arrow-right" class="w-4 h-4 text-slate-400"></i>
            <span class="px-3 py-1.5 rounded-xl bg-indigo-700 text-white text-xs font-mono font-bold">BBM</span>
        </div>
    </div>

    <!-- Tabbed Finance -->
    <div x-data="{ activeTab: 'ar' }" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="border-b border-slate-200/80 px-6 pt-4 flex gap-6 text-xs font-bold">
            <button @click="activeTab = 'ar'" :class="activeTab === 'ar' ? 'text-rose-600 border-b-2 border-rose-600 pb-3 font-extrabold' : 'text-slate-500 hover:text-slate-800 pb-3'">
                1. Piutang Usaha (AR) ({{ count($receivables) }})
            </button>
            <button @click="activeTab = 'fp'" :class="activeTab === 'fp' ? 'text-rose-600 border-b-2 border-rose-600 pb-3 font-extrabold' : 'text-slate-500 hover:text-slate-800 pb-3'">
                2. Tanda Terima Kasir / Pelunasan (FP) ({{ count($payments) }})
            </button>
            <button @click="activeTab = 'bbm'" :class="activeTab === 'bbm' ? 'text-rose-600 border-b-2 border-rose-600 pb-3 font-extrabold' : 'text-slate-500 hover:text-slate-800 pb-3'">
                3. Bukti Bank Masuk (BBM) ({{ count($bankReceipts) }})
            </button>
        </div>

        <!-- TAB 1: Accounts Receivable -->
        <div x-show="activeTab === 'ar'" class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3.5 px-4">DocType & Nomor AR</th>
                            <th class="py-3.5 px-4">Ref. Nomor SO</th>
                            <th class="py-3.5 px-4">Nama Outlet / Customer</th>
                            <th class="py-3.5 px-4">Jatuh Tempo</th>
                            <th class="py-3.5 px-4 text-right">Total Tagihan</th>
                            <th class="py-3.5 px-4 text-right">Sisa Piutang</th>
                            <th class="py-3.5 px-4">Status AR</th>
                            <th class="py-3.5 px-4 text-center">Aksi Pelunasan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($receivables as $ar)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-4 px-4 font-mono font-bold text-slate-900">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] bg-amber-600 text-white mr-1">{{ $ar->doc_type }}</span>
                                    {{ $ar->doc_number }}
                                </td>
                                <td class="py-4 px-4 font-mono font-semibold text-slate-600">{{ $ar->ref_doc_number }}</td>
                                <td class="py-4 px-4 font-bold text-slate-900">{{ $ar->customer_name }}</td>
                                <td class="py-4 px-4 text-slate-500">{{ date('d M Y', strtotime($ar->due_date)) }}</td>
                                <td class="py-4 px-4 text-right font-mono font-bold text-slate-900">
                                    Rp {{ number_format($ar->total_amount, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-4 text-right font-mono font-bold text-rose-600">
                                    Rp {{ number_format($ar->balance_amount, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-4">
                                    @if($ar->status === 'PAID')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            LUNAS (PAID)
                                        </span>
                                    @elseif($ar->status === 'PARTIAL')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                            SEBAGIAN (PARTIAL)
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                            BELUM LUNAS
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-center">
                                    @if($ar->status !== 'PAID')
                                        <form method="POST" action="{{ route('finance.pay', $ar->id) }}"
                                              data-loading-title="Pencatatan Pelunasan Piutang"
                                              data-loading-sub="Menerbitkan Tanda Terima Kasir (FP) & Bukti Bank Masuk (BBM)..."
                                              data-doctype="FP-BBM">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm flex items-center gap-1 mx-auto active:scale-95">
                                                <i data-lucide="banknote" class="w-3.5 h-3.5"></i>
                                                <span>Bayar Lunas (TTK)</span>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-emerald-600 font-bold flex items-center justify-center gap-1">
                                            <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Lunas Selesai
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 2: Payments / FP -->
        <div x-show="activeTab === 'fp'" class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3.5 px-4">DocType & Nomor FP</th>
                            <th class="py-3.5 px-4">Ref. Nomor AR</th>
                            <th class="py-3.5 px-4">Nama Customer</th>
                            <th class="py-3.5 px-4">Metode Bayar</th>
                            <th class="py-3.5 px-4 text-right">Nominal Pembayaran</th>
                            <th class="py-3.5 px-4">Petugas Kasir</th>
                            <th class="py-3.5 px-4">Tanggal Setor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($payments as $fp)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-4 px-4 font-mono font-bold text-slate-900">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] bg-emerald-700 text-white mr-1">{{ $fp->doc_type }}</span>
                                    {{ $fp->doc_number }}
                                </td>
                                <td class="py-4 px-4 font-mono font-semibold text-slate-600">{{ $fp->ref_doc_number }}</td>
                                <td class="py-4 px-4 font-bold text-slate-800">{{ $fp->customer_name }}</td>
                                <td class="py-4 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-800">
                                        {{ $fp->payment_method }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-right font-mono font-extrabold text-emerald-600">
                                    Rp {{ number_format($fp->amount, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-4 text-slate-600">{{ $fp->cashier_name }}</td>
                                <td class="py-4 px-4 text-slate-500">{{ date('d M Y', strtotime($fp->payment_date)) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 3: Bank Receipts / BBM -->
        <div x-show="activeTab === 'bbm'" class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3.5 px-4">DocType & Nomor BBM</th>
                            <th class="py-3.5 px-4">Ref. Nomor FP (TTK)</th>
                            <th class="py-3.5 px-4">Bank Tujuan</th>
                            <th class="py-3.5 px-4">Nomor Rekening</th>
                            <th class="py-3.5 px-4 text-right">Nominal Masuk</th>
                            <th class="py-3.5 px-4">Status Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($bankReceipts as $bbm)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-4 px-4 font-mono font-bold text-slate-900">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] bg-indigo-700 text-white mr-1">{{ $bbm->doc_type }}</span>
                                    {{ $bbm->doc_number }}
                                </td>
                                <td class="py-4 px-4 font-mono font-semibold text-slate-600">{{ $bbm->ref_doc_number }}</td>
                                <td class="py-4 px-4 font-bold text-slate-800">{{ $bbm->bank_name }}</td>
                                <td class="py-4 px-4 font-mono text-slate-500">{{ $bbm->account_number }}</td>
                                <td class="py-4 px-4 text-right font-mono font-extrabold text-slate-900">
                                    Rp {{ number_format($bbm->amount, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        {{ $bbm->status }} (KAS BERTAMBAH)
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

