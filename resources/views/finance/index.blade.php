<x-app-layout>
    <x-slot name="title">Piutang (AR), Tanda Terima Kasir (TTK) & Bukti Bank Masuk (BBM)</x-slot>
    <x-slot name="headerTitle">Pelunasan & Keuangan (AR &bull; TTK &bull; BBM)</x-slot>
    <x-slot name="badge">AR &bull; TTK &bull; BBM</x-slot>

    <div x-data="financeApp()" x-init="initData()">
        
        <!-- Top Action & Navigation Tabs -->
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight">Pelunasan Piutang & Penerimaan Kas/Bank</h2>
                <p class="text-xs text-slate-500">Alur: Tagihan Faktur (AR) &rarr; Tanda Terima Kasir (TTK) &rarr; Bukti Bank Masuk (BBM Tunai & Non Tunai)</p>
            </div>

            <!-- Tab Switcher with Distinct Colors -->
            <div class="flex items-center gap-2 bg-slate-200/70 p-1 rounded-2xl shrink-0">
                <button type="button" 
                        @click="activeTab = 'AR'"
                        class="px-4 py-2 rounded-xl text-xs font-extrabold flex items-center gap-1.5 transition-all shadow-xs"
                        :class="activeTab === 'AR' ? 'bg-amber-600 text-white shadow-md' : 'text-slate-600 hover:text-slate-900'">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                    <span>1. Piutang (AR)</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-white/20">{{ count($receivables) }}</span>
                </button>

                <button type="button" 
                        @click="activeTab = 'TTK'"
                        class="px-4 py-2 rounded-xl text-xs font-extrabold flex items-center gap-1.5 transition-all shadow-xs"
                        :class="activeTab === 'TTK' ? 'bg-emerald-600 text-white shadow-md' : 'text-slate-600 hover:text-slate-900'">
                    <i data-lucide="badge-check" class="w-4 h-4"></i>
                    <span>2. Tanda Terima Kasir (TTK)</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-white/20">{{ count($payments) }}</span>
                </button>

                <button type="button" 
                        @click="activeTab = 'BBM'"
                        class="px-4 py-2 rounded-xl text-xs font-extrabold flex items-center gap-1.5 transition-all shadow-xs"
                        :class="activeTab === 'BBM' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-600 hover:text-slate-900'">
                    <i data-lucide="landmark" class="w-4 h-4"></i>
                    <span>3. Bukti Bank Masuk (BBM)</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-white/20">{{ count($bankReceipts) }}</span>
                </button>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- FORM PELUNASAN TTK (INLINE CARD)               -->
        <!-- ============================================== -->
        <div x-show="createTtkOpen" x-cloak x-transition class="mb-6 bg-white rounded-2xl border-2 border-emerald-500/40 p-5 shadow-lg">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <i data-lucide="badge-check" class="w-4 h-4 text-emerald-600"></i>
                    <span>Formulir Tanda Terima Kasir (TTK) & Pencatatan BBM</span>
                </h3>
                <button @click="createTtkOpen = false" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('finance.store-ttk') }}" 
                  data-loading-title="Memproses Pembayaran"
                  data-loading-sub="Menerbitkan TTK & mencatat Bukti Bank Masuk (BBM)..."
                  data-doctype="TTK">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    
                    <!-- Pilih Faktur AR -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Faktur Piutang (AR) yang Belum Lunas</label>
                        <select name="accounts_receivable_id" 
                                x-model="selectedArId" 
                                @change="onSelectedArChange()"
                                required 
                                class="w-full text-xs font-bold text-slate-900 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                            <option value="">-- Pilih Faktur Tagihan Pelanggan --</option>
                            @foreach($unpaidReceivables as $uar)
                                <option value="{{ $uar->id }}">
                                    [{{ $uar->doc_number }}] {{ $uar->customer_name }} &bull; Sisa Piutang: Rp {{ number_format($uar->balance_amount, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Pembayaran</label>
                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none">
                    </div>
                </div>

                <!-- PREVIEW INFO PIUTANG JIKA AR DIPILIH -->
                <template x-if="currentArObj">
                    <div class="mb-4 p-4 rounded-xl bg-amber-50/70 border border-amber-200 grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                        <div>
                            <span class="text-slate-500 block">Toko / Customer:</span>
                            <span class="font-extrabold text-slate-900 text-sm" x-text="currentArObj.customer_name"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Total Tagihan Awal:</span>
                            <span class="font-bold text-slate-800 font-mono" x-text="'Rp ' + Number(currentArObj.total_amount).toLocaleString('id-ID')"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Sisa Piutang yang Harus Dibayar:</span>
                            <span class="font-extrabold text-rose-700 font-mono text-sm" x-text="'Rp ' + Number(currentArObj.balance_amount).toLocaleString('id-ID')"></span>
                        </div>
                    </div>
                </template>

                <!-- METODE PEMBAYARAN & NOMINAL -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Metode Pembayaran</label>
                        <select name="payment_method" 
                                x-model="paymentMethod" 
                                required 
                                class="w-full text-xs font-bold text-slate-800 bg-white border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            <option value="TRANSFER">1. Transfer Bank (BBM Non Tunai)</option>
                            <option value="CASH">2. Tunai (BBM Tunai)</option>
                            <option value="DEPOSIT">3. Uang Titipan (Saldo Deposit)</option>
                            <option value="RETURN_DEDUCTION">4. Pengurangan Retur (Potong Nota)</option>
                        </select>
                    </div>

                    <!-- Nominal Pembayaran -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700">Nominal Pembayaran (Rp)</label>
                            <template x-if="currentArObj">
                                <button type="button" @click="payFull()" class="text-[10px] font-bold text-emerald-700 hover:underline">Bayar Lunas Penuh</button>
                            </template>
                        </div>
                        <input type="number" 
                               name="amount" 
                               x-model.number="payAmount" 
                               min="1" 
                               :max="currentArObj ? currentArObj.balance_amount : 999999999" 
                               required 
                               placeholder="Masukkan nominal bayar..."
                               class="w-full text-xs font-mono font-bold text-emerald-800 bg-white border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kasir / Petugas Penerima</label>
                        <input type="text" name="cashier_name" value="Kasir TAP Pasar Minggu" required class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none">
                    </div>
                </div>

                <!-- SUB-INFO SESUAI METODE (BBM TUNAI VS NON TUNAI) -->
                <div class="mb-5 p-3.5 rounded-xl border border-slate-200 bg-slate-50/70">
                    <template x-if="paymentMethod === 'TRANSFER'">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Bank Tujuan (BBM Non Tunai)</label>
                                <select name="bank_name" class="w-full text-xs font-semibold bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 focus:outline-none">
                                    <option value="BCA Operasional TAP">BCA - PT Telkomsel Distribusi</option>
                                    <option value="Mandiri Rekening Pusat">Mandiri - Rekening Operasional</option>
                                    <option value="BRI Setoran Kasir">BRI - Setoran Toko Retail</option>
                                    <option value="BNI Giro Distribusi">BNI - Giro Kliring</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Nomor Rekening / Referensi Transfer</label>
                                <input type="text" name="account_number" value="711-0988-234" class="w-full text-xs font-mono font-semibold bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 focus:outline-none">
                            </div>
                        </div>
                    </template>

                    <template x-if="paymentMethod === 'CASH'">
                        <div class="flex items-center gap-2 text-xs text-slate-700 font-medium">
                            <i data-lucide="wallet" class="w-4 h-4 text-emerald-600"></i>
                            <span>Pembayaran tunai fisik akan otomatis dicatat sebagai <strong>BBM Tunai</strong> ke brankas operasional kasir TAP.</span>
                        </div>
                    </template>

                    <template x-if="paymentMethod === 'DEPOSIT'">
                        <div class="flex items-center gap-2 text-xs text-slate-700 font-medium">
                            <i data-lucide="piggy-bank" class="w-4 h-4 text-cyan-600"></i>
                            <span>Pembayaran dipotong dari saldo uang titipan customer yang tersimpan di sistem.</span>
                        </div>
                    </template>

                    <template x-if="paymentMethod === 'RETURN_DEDUCTION'">
                        <div class="flex items-center gap-2 text-xs text-slate-700 font-medium">
                            <i data-lucide="undo-2" class="w-4 h-4 text-rose-600"></i>
                            <span>Pengurangan tagihan piutang melalui kompensasi nota retur barang (Sales Retur).</span>
                        </div>
                    </template>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="createTtkOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-sm transition-all active:scale-95">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan TTK & Rilis BBM</span>
                    </button>
                </div>

            </form>
        </div>

        <!-- ============================================== -->
        <!-- TAB 1: PIUTANG USAHA (AR)                      -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'AR'" x-cloak>
            <div class="mb-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-100 text-amber-800 border border-amber-300">
                        DocType: AR
                    </span>
                    <span class="text-xs text-slate-500">Daftar invoice tagihan piutang toko pelanggan</span>
                </div>

                <button type="button" 
                        @click="openTtkFromTab()"
                        class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-sm transition-all active:scale-95">
                    <i data-lucide="plus-circle" class="w-4 h-4 text-emerald-400"></i>
                    <span>+ Terima Pembayaran (TTK)</span>
                </button>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                            <tr>
                                <th class="py-3 px-4">No. Faktur AR</th>
                                <th class="py-3 px-4">Ref. Nomor SO</th>
                                <th class="py-3 px-4">Customer / Outlet</th>
                                <th class="py-3 px-4">Jatuh Tempo</th>
                                <th class="py-3 px-4 text-right">Total Tagihan</th>
                                <th class="py-3 px-4 text-right">Sudah Dibayar</th>
                                <th class="py-3 px-4 text-right">Sisa Piutang</th>
                                <th class="py-3 px-4 text-center">Status AR</th>
                                <th class="py-3 px-4 text-center w-28">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($receivables as $ar)
                                <tr @click="selectAr({{ json_encode($ar) }})" 
                                    class="cursor-pointer transition-colors"
                                    :class="selectedAr && selectedAr.id === {{ $ar->id }} ? 'bg-amber-50/80 border-l-4 border-amber-500' : 'hover:bg-slate-50/70'">
                                    
                                    <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-1.5 py-0.5 rounded text-[10px] bg-amber-600 text-white font-mono font-bold">AR</span>
                                            <span>{{ $ar->doc_number }}</span>
                                        </div>
                                    </td>

                                    <td class="py-3.5 px-4 font-mono font-semibold text-slate-600">{{ $ar->ref_doc_number }}</td>

                                    <td class="py-3.5 px-4">
                                        <span class="font-bold text-slate-900 block">{{ $ar->customer_name }}</span>
                                        <span class="text-[10px] text-slate-400 font-mono">{{ $ar->customer_code }}</span>
                                    </td>

                                    <td class="py-3.5 px-4 text-slate-500">{{ date('d M Y', strtotime($ar->due_date)) }}</td>

                                    <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-900">
                                        Rp {{ number_format($ar->total_amount, 0, ',', '.') }}
                                    </td>

                                    <td class="py-3.5 px-4 text-right font-mono font-semibold text-emerald-700">
                                        Rp {{ number_format($ar->paid_amount, 0, ',', '.') }}
                                    </td>

                                    <td class="py-3.5 px-4 text-right font-mono font-extrabold text-rose-700">
                                        Rp {{ number_format($ar->balance_amount, 0, ',', '.') }}
                                    </td>

                                    <td class="py-3.5 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold
                                            {{ $ar->status === 'PAID' ? 'bg-emerald-100 text-emerald-800' : ($ar->status === 'PARTIAL' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                            {{ $ar->status }}
                                        </span>
                                    </td>

                                    <td class="py-3.5 px-4 text-center">
                                        @if($ar->status !== 'PAID')
                                            <button type="button" 
                                                    @click.stop="quickPayAr({{ json_encode($ar) }})"
                                                    class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs">
                                                Bayar TTK
                                            </button>
                                        @else
                                            <span class="text-[11px] font-bold text-emerald-700 flex items-center justify-center gap-1">
                                                <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Lunas
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-8 text-center text-slate-400">
                                        Belum ada tagihan Piutang (AR).
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- DETAIL AR INLINE -->
            <div x-show="selectedAr" x-cloak class="scroll-mt-4" id="arDetailSection">
                <div class="rounded-2xl border-2 border-slate-300 shadow-lg overflow-hidden bg-white mb-8">
                    <div class="bg-slate-900 text-white px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-extrabold bg-amber-500 text-slate-950">
                                DETAIL PIUTANG
                            </span>
                            <h4 class="text-base font-extrabold text-white tracking-tight" x-text="selectedAr ? selectedAr.doc_number : ''"></h4>
                            <span class="text-xs text-slate-400">&bull;</span>
                            <span class="text-xs font-bold text-amber-400" x-text="selectedAr ? selectedAr.customer_name : ''"></span>
                        </div>
                        <div class="flex items-center gap-3 text-xs">
                            <span class="text-slate-400">Sisa Tagihan:</span>
                            <span class="font-mono font-extrabold text-rose-400 text-sm">
                                Rp <span x-text="selectedAr ? Number(selectedAr.balance_amount).toLocaleString('id-ID') : '0'"></span>
                            </span>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs">
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                                <span class="text-slate-500 block">Total Faktur Awal:</span>
                                <span class="font-bold text-slate-900 text-sm font-mono">Rp <span x-text="selectedAr ? Number(selectedAr.total_amount).toLocaleString('id-ID') : '0'"></span></span>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                                <span class="text-slate-500 block">Sudah Dibayar:</span>
                                <span class="font-bold text-emerald-700 text-sm font-mono">Rp <span x-text="selectedAr ? Number(selectedAr.paid_amount).toLocaleString('id-ID') : '0'"></span></span>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                                <span class="text-slate-500 block">Sisa Piutang:</span>
                                <span class="font-bold text-rose-700 text-sm font-mono">Rp <span x-text="selectedAr ? Number(selectedAr.balance_amount).toLocaleString('id-ID') : '0'"></span></span>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                                <div>
                                    <span class="text-slate-500 block">Status Pelunasan:</span>
                                    <span class="font-bold text-sm" x-text="selectedAr ? selectedAr.status : ''"></span>
                                </div>
                                <template x-if="selectedAr && selectedAr.status !== 'PAID'">
                                    <button type="button" @click="quickPayAr(selectedAr)" class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-xs font-bold">
                                        Bayar TTK
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ============================================== -->
        <!-- TAB 2: TANDA TERIMA KASIR (TTK)                -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'TTK'" x-cloak>
            <div class="mb-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        DocType: TTK
                    </span>
                    <span class="text-xs text-slate-500">Bukti resmi penerimaan pembayaran kasir untuk toko pelanggan</span>
                </div>

                <button type="button" 
                        @click="openTtkFromTab()"
                        class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-sm transition-all active:scale-95">
                    <i data-lucide="plus-circle" class="w-4 h-4 text-emerald-400"></i>
                    <span>+ Buat TTK Baru</span>
                </button>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                            <tr>
                                <th class="py-3 px-4">No. Dokumen TTK</th>
                                <th class="py-3 px-4">Ref. Nomor AR</th>
                                <th class="py-3 px-4">Tanggal Bayar</th>
                                <th class="py-3 px-4">Customer</th>
                                <th class="py-3 px-4">Metode Bayar</th>
                                <th class="py-3 px-4 text-right">Nominal Diterima</th>
                                <th class="py-3 px-4">Kasir Penerima</th>
                                <th class="py-3 px-4 text-center w-28">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($payments as $pm)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-1.5 py-0.5 rounded text-[10px] bg-emerald-700 text-white font-mono font-bold">TTK</span>
                                            <span>{{ $pm->doc_number }}</span>
                                        </div>
                                    </td>

                                    <td class="py-3.5 px-4 font-mono font-bold text-amber-700">
                                        {{ $pm->ref_doc_number }}
                                    </td>

                                    <td class="py-3.5 px-4 text-slate-600">
                                        {{ date('d M Y', strtotime($pm->payment_date)) }}
                                    </td>

                                    <td class="py-3.5 px-4">
                                        <span class="font-bold text-slate-900 block">{{ $pm->customer_name }}</span>
                                    </td>

                                    <td class="py-3.5 px-4">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono
                                            {{ $pm->payment_method === 'CASH' ? 'bg-emerald-100 text-emerald-800' : ($pm->payment_method === 'TRANSFER' ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-700') }}">
                                            {{ $pm->payment_method }}
                                        </span>
                                    </td>

                                    <td class="py-3.5 px-4 text-right font-mono font-extrabold text-slate-900">
                                        Rp {{ number_format($pm->amount, 0, ',', '.') }}
                                    </td>

                                    <td class="py-3.5 px-4 text-slate-600">
                                        {{ $pm->cashier_name }}
                                    </td>

                                    <td class="py-3.5 px-4 text-center">
                                        <a href="{{ route('finance.print-kwitansi', $pm->id) }}" target="_blank"
                                           class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-800 hover:bg-slate-700 text-white inline-flex items-center gap-1 shadow-xs">
                                            <i data-lucide="printer" class="w-3.5 h-3.5 text-emerald-400"></i>
                                            <span>Kwitansi</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-slate-400">
                                        Belum ada data Tanda Terima Kasir (TTK).
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- TAB 3: BUKTI BANK MASUK (BBM)                  -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'BBM'" x-cloak>
            <div class="mb-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-indigo-100 text-indigo-800 border border-indigo-300">
                        DocType: BBM
                    </span>
                    <span class="text-xs text-slate-500">Pencatatan mutasi kas masuk perbankan & kasir operasional</span>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                            <tr>
                                <th class="py-3 px-4">No. Dokumen BBM</th>
                                <th class="py-3 px-4">Ref. Dokumen TTK</th>
                                <th class="py-3 px-4">Jenis Penerimaan</th>
                                <th class="py-3 px-4">Tanggal</th>
                                <th class="py-3 px-4">Bank / Akun Kas Masuk</th>
                                <th class="py-3 px-4">No. Rekening</th>
                                <th class="py-3 px-4 text-right">Nominal Masuk</th>
                                <th class="py-3 px-4 text-center">Status Verifikasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($bankReceipts as $bbm)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-1.5 py-0.5 rounded text-[10px] bg-indigo-700 text-white font-mono font-bold">BBM</span>
                                            <span>{{ $bbm->doc_number }}</span>
                                        </div>
                                    </td>

                                    <td class="py-3.5 px-4 font-mono font-bold text-emerald-700">
                                        {{ $bbm->ref_doc_number }}
                                    </td>

                                    <td class="py-3.5 px-4">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono
                                            {{ $bbm->receipt_type === 'TUNAI' ? 'bg-emerald-100 text-emerald-800' : 'bg-indigo-100 text-indigo-800' }}">
                                            {{ $bbm->receipt_type === 'TUNAI' ? 'BBM TUNAI' : 'BBM NON TUNAI' }}
                                        </span>
                                    </td>

                                    <td class="py-3.5 px-4 text-slate-600">
                                        {{ date('d M Y', strtotime($bbm->receipt_date)) }}
                                    </td>

                                    <td class="py-3.5 px-4 font-bold text-slate-800">
                                        {{ $bbm->bank_name }}
                                    </td>

                                    <td class="py-3.5 px-4 font-mono text-slate-500">
                                        {{ $bbm->account_number ?? '-' }}
                                    </td>

                                    <td class="py-3.5 px-4 text-right font-mono font-extrabold text-slate-900">
                                        Rp {{ number_format($bbm->amount, 0, ',', '.') }}
                                    </td>

                                    <td class="py-3.5 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            {{ $bbm->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-slate-400">
                                        Belum ada data Bukti Bank Masuk (BBM).
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function financeApp() {
            return {
                activeTab: 'AR',
                createTtkOpen: false,
                selectedAr: null,

                selectedArId: '',
                currentArObj: null,
                paymentMethod: 'TRANSFER',
                payAmount: 0,

                unpaidReceivables: @json($unpaidReceivables),

                initData() {
                    const firstAr = @json($receivables->first());
                    if (firstAr) this.selectedAr = firstAr;

                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                },

                selectAr(ar) {
                    this.selectedAr = ar;
                    this.$nextTick(() => {
                        lucide.createIcons();
                        const el = document.getElementById('arDetailSection');
                        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    });
                },

                openTtkFromTab() {
                    this.createTtkOpen = true;
                    if (this.unpaidReceivables.length > 0) {
                        this.selectedArId = this.unpaidReceivables[0].id;
                        this.onSelectedArChange();
                    }
                },

                quickPayAr(ar) {
                    this.createTtkOpen = true;
                    this.selectedArId = ar.id;
                    this.currentArObj = ar;
                    this.payAmount = parseFloat(ar.balance_amount) || 0;
                    this.$nextTick(() => {
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    });
                },

                onSelectedArChange() {
                    this.currentArObj = this.unpaidReceivables.find(a => a.id == this.selectedArId) || null;
                    if (this.currentArObj) {
                        this.payAmount = parseFloat(this.currentArObj.balance_amount) || 0;
                    } else {
                        this.payAmount = 0;
                    }
                },

                payFull() {
                    if (this.currentArObj) {
                        this.payAmount = parseFloat(this.currentArObj.balance_amount) || 0;
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
