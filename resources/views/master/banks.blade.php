<x-app-layout>
    <x-slot name="title">Master Rekening Bank & Kas</x-slot>
    <x-slot name="headerTitle">Master Rekening Bank & Kas PT</x-slot>
    <x-slot name="badge">Finance & Perbankan</x-slot>

    <div class="space-y-6" x-data="{ modalOpen: false }">

        <!-- Header Ringkas -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                        Keuangan & Kas/Bank
                    </span>
                    <span class="text-xs text-slate-400">Rekening Penampung Transaksi & Kasir</span>
                </div>
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Master Rekening Bank & Kas PT</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Daftar rekening giro/tabungan perusahaan dan kas kecil kasir untuk penerimaan pembayaran outlet & BBM.
                </p>
            </div>
            <div>
                <button type="button" @click="modalOpen = true" 
                        class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-rose-600 text-white hover:bg-rose-700 transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Tambah Rekening / Kas</span>
                </button>
            </div>
        </div>

        <!-- 4 KPI Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Total Akun Finansial</p>
                    <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ $kpi['total_accounts'] }} <span class="text-xs font-normal text-slate-400">akun</span></h3>
                    <p class="text-[11px] text-slate-400 mt-1">Giro, tabungan & kasir</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100">
                    <i data-lucide="landmark" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Total Saldo Terbuku</p>
                    <h3 class="text-xl font-bold text-slate-900 mt-1 font-mono">Rp {{ number_format($kpi['total_balance'], 0, ',', '.') }}</h3>
                    <p class="text-[11px] text-emerald-600 font-semibold mt-1">Likuiditas Kas & Bank</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Rekening Bank (Giro)</p>
                    <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ $kpi['bank_count'] }} <span class="text-xs font-normal text-slate-400">rekening</span></h3>
                    <p class="text-[11px] text-slate-400 mt-1">BCA, Mandiri, BSI</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 border border-indigo-100">
                    <i data-lucide="credit-card" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Pos Kas Kecil Kasir</p>
                    <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ $kpi['cash_count'] }} <span class="text-xs font-normal text-slate-400">pos TAP</span></h3>
                    <p class="text-[11px] text-slate-400 mt-1">Kas fisik kasir kantor</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-100">
                    <i data-lucide="banknote" class="w-5 h-5"></i>
                </div>
            </div>
        </div>

        <!-- Tabel Rekening -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="list" class="w-4 h-4 text-slate-500"></i>
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Daftar Rekening Bank & Kasir</h3>
                </div>
                <span class="text-xs text-slate-400 font-mono">{{ $banks->count() }} Akun Aktif</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100/70 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider text-[10px]">
                            <th class="py-3 px-4">Nama Bank / Kas</th>
                            <th class="py-3 px-4">Nomor Rekening / Kode</th>
                            <th class="py-3 px-4">Atas Nama</th>
                            <th class="py-3 px-4">Kantor Cabang / Penempatan</th>
                            <th class="py-3 px-4 text-center">Tipe Akun</th>
                            <th class="py-3 px-4 text-right">Saldo Terbuku</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($banks as $b)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4 font-semibold text-slate-900">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-lg {{ $b->account_type === 'BANK' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700' }} flex items-center justify-center font-bold text-xs shrink-0">
                                            <i data-lucide="{{ $b->account_type === 'BANK' ? 'landmark' : 'banknote' }}" class="w-4 h-4"></i>
                                        </div>
                                        <span>{{ $b->bank_name }}</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-800">{{ $b->account_number }}</td>
                                <td class="py-3.5 px-4 text-slate-700">{{ $b->account_holder }}</td>
                                <td class="py-3.5 px-4 text-slate-500">{{ $b->branch_name ?: '-' }}</td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($b->account_type === 'BANK')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                            BANK / GIRO
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            KAS TUNAI
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-900">
                                    Rp {{ number_format($b->balance, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        AKTIF
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Tambah Rekening / Kas -->
        <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-md w-full p-6 text-left" @click.away="modalOpen = false">
                <form action="{{ route('master.banks.store') }}" method="POST" data-no-loading>
                    @csrf
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                        <h4 class="text-base font-bold text-slate-900">Tambah Rekening Bank / Kas</h4>
                        <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tipe Akun *</label>
                            <select name="account_type" class="w-full border border-slate-200 rounded-lg p-2.5 focus:outline-none focus:ring-1 focus:ring-slate-400">
                                <option value="BANK">Rekening Bank / Giro</option>
                                <option value="CASH">Kas Tunai Kasir</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Nama Bank / Pos Kas *</label>
                            <input type="text" name="bank_name" placeholder="Misal: Bank Mandiri atau Kas Kasir Cikokol" required class="w-full border border-slate-200 rounded-lg p-2.5">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Nomor Rekening</label>
                            <input type="text" name="account_number" placeholder="Nomor rekening bank (kosongkan jika kas tunai)" class="w-full border border-slate-200 rounded-lg p-2.5">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Atas Nama Pemilik Akun *</label>
                            <input type="text" name="account_holder" placeholder="PT WAHANA PUTRA YUDHA" required class="w-full border border-slate-200 rounded-lg p-2.5">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Kantor Cabang / Lokasi TAP</label>
                            <input type="text" name="branch_name" placeholder="Misal: KCU Tangerang atau Kantor TAP Serpong" class="w-full border border-slate-200 rounded-lg p-2.5">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Saldo Awal (Rp) *</label>
                            <input type="number" name="balance" value="0" min="0" required class="w-full border border-slate-200 rounded-lg p-2.5">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100 mt-4">
                        <button type="button" @click="modalOpen = false" class="px-4 py-2 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-lg text-xs font-semibold bg-rose-600 text-white hover:bg-rose-700">Simpan Akun</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
