<x-app-layout>
    <x-slot name="title">Piutang (AR), Tanda Terima Kasir (TTK) & Bukti Bank Masuk (BBM)</x-slot>
    <x-slot name="headerTitle">Pelunasan & Keuangan (AR &bull; TTK &bull; BBM)</x-slot>
    <x-slot name="badge">AR &bull; TTK &bull; BBM</x-slot>

    <div x-data="financeApp()" x-init="initData()">
        
        <!-- Top Action & Navigation Tabs -->
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Pelunasan Piutang & Penerimaan Kas/Bank</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">PT WAHANA PUTRA YUDHA</span>
                </h2>
                <p class="text-xs text-slate-500">Divisi Keuangan PT Wahana Putra Yudha: Tagihan Faktur (AR) &rarr; Tanda Terima Kasir (TTK Multi-AR) &rarr; Bukti Bank Masuk (BBM Tunai & Non Tunai)</p>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" 
                        @click="openTtkModal()"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold flex items-center gap-2 shadow-sm transition-all">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Buat TTK Baru</span>
                </button>

                <!-- Tab Switcher with Distinct Colors -->
                <div class="flex items-center gap-1.5 bg-slate-200/70 p-1 rounded-2xl shrink-0">
                    <button type="button" 
                            @click="activeTab = 'AR'"
                            class="px-3.5 py-2 rounded-xl text-xs font-extrabold flex items-center gap-1.5 transition-all shadow-xs"
                            :class="activeTab === 'AR' ? 'bg-amber-600 text-white shadow-md' : 'text-slate-600 hover:text-slate-900'">
                        <i data-lucide="receipt" class="w-4 h-4"></i>
                        <span>1. Piutang (AR)</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-white/20">{{ count($receivables) }}</span>
                    </button>

                    <button type="button" 
                            @click="activeTab = 'TTK'"
                            class="px-3.5 py-2 rounded-xl text-xs font-extrabold flex items-center gap-1.5 transition-all shadow-xs"
                            :class="activeTab === 'TTK' ? 'bg-emerald-600 text-white shadow-md' : 'text-slate-600 hover:text-slate-900'">
                        <i data-lucide="badge-check" class="w-4 h-4"></i>
                        <span>2. Tanda Terima Kasir (TTK)</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-white/20">{{ count($payments) }}</span>
                    </button>

                    <button type="button" 
                            @click="activeTab = 'BBM'"
                            class="px-3.5 py-2 rounded-xl text-xs font-extrabold flex items-center gap-1.5 transition-all shadow-xs"
                            :class="activeTab === 'BBM' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-600 hover:text-slate-900'">
                        <i data-lucide="landmark" class="w-4 h-4"></i>
                        <span>3. Bukti Bank Masuk (BBM)</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-white/20">{{ count($bankReceipts) }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- FORM PELUNASAN TTK (MULTI-AR SELECTION)        -->
        <!-- ============================================== -->
        <div x-show="createTtkOpen" x-cloak x-transition class="mb-6 bg-white rounded-2xl border-2 border-emerald-500/40 p-5 shadow-xl">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 bg-emerald-100 text-emerald-700 rounded-lg">
                        <i data-lucide="badge-check" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-sm">Formulir Tanda Terima Kasir (TTK Multi-AR) & Penerbitan BBM</h3>
                        <p class="text-[11px] text-slate-500">Pilih Toko/Pelanggan, tentukan faktur-faktur AR yang dilunasi, lalu pilih metode pembayaran.</p>
                    </div>
                </div>
                <button @click="createTtkOpen = false" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('finance.store-ttk') }}" 
                  data-loading-title="Memproses Pembayaran Kasir"
                  data-loading-sub="Menerbitkan TTK, mengalokasikan saldo AR, dan mencatat Bukti Bank Masuk (BBM)..."
                  data-doctype="TTK">
                @csrf

                <!-- Grid Baris 1: Pelanggan & Info Transaksi -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    
                    <!-- Pilih Pelanggan (Hanya yang punya outstanding AR) -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Pilih Pelanggan / Toko <span class="text-rose-500">*</span>
                        </label>
                        <select name="customer_id" 
                                x-model="selectedCustomerId" 
                                @change="onCustomerChange()"
                                required 
                                class="w-full text-xs font-bold text-slate-900 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                            <option value="">-- Pilih Toko yang Memiliki Piutang --</option>
                            @foreach($customersWithUnpaidAr as $c)
                                <option value="{{ $c->id }}">
                                    [{{ $c->code }}] {{ $c->name }} ({{ $c->phone ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Pembayaran</label>
                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kasir / Penerima</label>
                        <input type="text" name="cashier_name" value="Kasir Finance TAP" required class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none">
                    </div>
                </div>

                <!-- DAFTAR FAKTUR AR TOKO TERPILIH (MULTI-AR) -->
                <div x-show="selectedCustomerId" class="mb-5">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-extrabold text-slate-800">Daftar Faktur Piutang (AR) Outstanding Milik Toko:</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800" x-text="customerArList.length + ' Faktur Belum Lunas'"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="selectAllAr()" class="px-2.5 py-1 text-[11px] font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg border border-emerald-200 transition-all">
                                Pilih Semua & Lunasi
                            </button>
                            <button type="button" @click="deselectAllAr()" class="px-2.5 py-1 text-[11px] font-bold bg-slate-50 text-slate-600 hover:bg-slate-100 rounded-lg border border-slate-200 transition-all">
                                Reset Pilihan
                            </button>
                        </div>
                    </div>

                    <div class="border border-slate-200 rounded-xl overflow-hidden shadow-xs">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-100/80 text-slate-600 font-bold border-b border-slate-200">
                                <tr>
                                    <th class="py-2.5 px-3 w-10 text-center">Pilih</th>
                                    <th class="py-2.5 px-3">No. Faktur AR</th>
                                    <th class="py-2.5 px-3">Ref SO & Channel</th>
                                    <th class="py-2.5 px-3">Sales Penanggung Jawab</th>
                                    <th class="py-2.5 px-3 text-right">Total Tagihan</th>
                                    <th class="py-2.5 px-3 text-right">Sisa Piutang</th>
                                    <th class="py-2.5 px-3 text-right w-44">Nominal Bayar (Rp)</th>
                                    <th class="py-2.5 px-3 text-center w-20">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="ar in customerArList" :key="ar.id">
                                    <tr :class="selectedAllocations[ar.id]?.selected ? 'bg-emerald-50/40 font-medium' : 'hover:bg-slate-50'">
                                        <td class="py-2.5 px-3 text-center">
                                            <input type="checkbox" 
                                                   :checked="selectedAllocations[ar.id]?.selected" 
                                                   @change="toggleArSelection(ar)"
                                                   class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4 cursor-pointer">
                                        </td>
                                        <td class="py-2.5 px-3 font-mono font-bold text-amber-900" x-text="ar.doc_number"></td>
                                        <td class="py-2.5 px-3">
                                            <span class="font-mono text-slate-700" x-text="ar.so_number || '-'"></span>
                                            <template x-if="ar.so_channel === 'MOBILE_SALES'">
                                                <span class="ml-1 px-1.5 py-0.2 rounded text-[9px] font-extrabold bg-purple-100 text-purple-700 border border-purple-200">
                                                    MOBILE SALES
                                                </span>
                                            </template>
                                            <template x-if="ar.so_channel === 'WEB_ADMIN'">
                                                <span class="ml-1 px-1.5 py-0.2 rounded text-[9px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                                    OFFICE
                                                </span>
                                            </template>
                                        </td>
                                        <td class="py-2.5 px-3 font-semibold text-slate-800" x-text="ar.sales_name || '-'"></td>
                                        <td class="py-2.5 px-3 text-right font-mono text-slate-600" x-text="'Rp ' + Number(ar.total_amount).toLocaleString('id-ID')"></td>
                                        <td class="py-2.5 px-3 text-right font-mono font-bold text-rose-600" x-text="'Rp ' + Number(ar.balance_amount).toLocaleString('id-ID')"></td>
                                        <td class="py-2.5 px-3 text-right">
                                            <input type="number" 
                                                   :name="'allocations[' + ar.id + '][amount]'" 
                                                   x-model.number="selectedAllocations[ar.id].amount"
                                                   @input="onAmountInput(ar)"
                                                   :disabled="!selectedAllocations[ar.id]?.selected"
                                                   :max="ar.balance_amount"
                                                   min="0"
                                                   class="w-full text-right font-mono text-xs font-bold px-2 py-1.5 rounded-lg border focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                                   :class="selectedAllocations[ar.id]?.selected ? 'bg-white border-emerald-400 text-emerald-900' : 'bg-slate-100 border-slate-200 text-slate-400'">
                                        </td>
                                        <td class="py-2.5 px-3 text-center">
                                            <button type="button" 
                                                    @click="payFullAr(ar)"
                                                    class="px-2 py-1 text-[10px] font-bold rounded bg-slate-200 hover:bg-emerald-600 hover:text-white transition-all">
                                                Lunas
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot class="bg-slate-50 font-bold border-t border-slate-200">
                                <tr>
                                    <td colspan="5" class="py-3 px-3 text-right text-slate-700">
                                        Total Pembayaran yang Diterima Kasir:
                                    </td>
                                    <td colspan="2" class="py-3 px-3 text-right font-mono text-sm font-extrabold text-emerald-700" x-text="'Rp ' + calculatedTotalPay.toLocaleString('id-ID')"></td>
                                    <td class="py-3 px-3 text-center text-[11px] text-slate-500" x-text="selectedCount + ' Faktur'"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Grid Baris 2: Metode Pembayaran & Detail BBM -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Metode Pembayaran <span class="text-rose-500">*</span>
                        </label>
                        <select name="payment_method" 
                                x-model="paymentMethod" 
                                required 
                                class="w-full text-xs font-bold text-slate-900 bg-white border border-slate-300 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="CASH">Uang Tunai (BBM Tunai / Kas)</option>
                            <option value="TRANSFER">Transfer Bank (BBM Non Tunai)</option>
                            <option value="DEPOSIT">Saldo Titipan Pelanggan (BBM Non Tunai)</option>
                            <option value="RETURN_DEDUCTION">Potongan Nota Retur (BBM Non Tunai)</option>
                        </select>
                        <span class="block text-[10px] text-slate-500 mt-1" x-text="paymentMethod === 'CASH' ? 'Otomatis diterbitkan sebagai BBM TUNAI' : 'Otomatis diterbitkan sebagai BBM NON TUNAI'"></span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Kas / Bank Penerima</label>
                        <input type="text" 
                               name="bank_name" 
                               :value="paymentMethod === 'CASH' ? 'KAS TUNAI TAP (BBM TUNAI)' : 'BCA Operasional TAP'" 
                               required 
                               class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-3 py-2 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Rekening / Akun Kas</label>
                        <input type="text" 
                               name="account_number" 
                               :value="paymentMethod === 'CASH' ? 'KAS-OPERASIONAL-01' : '711-0988-234'" 
                               class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-xl px-3 py-2 focus:outline-none font-mono">
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan</label>
                        <input type="text" name="notes" placeholder="e.g. Pembayaran pelunasan gabungan faktur SA & Voucher" class="w-full text-xs font-medium bg-white border border-slate-300 rounded-xl px-3 py-2 focus:outline-none">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="createTtkOpen = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800">
                        Batal
                    </button>
                    <button type="submit" 
                            :disabled="calculatedTotalPay <= 0"
                            class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white rounded-xl text-xs font-extrabold flex items-center gap-2 shadow-md transition-all">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        <span x-text="'Simpan TTK & Terbitkan BBM (' + (paymentMethod === 'CASH' ? 'Tunai' : 'Non Tunai') + ')'"></span>
                    </button>
                </div>
            </form>
        </div>

        <!-- ============================================== -->
        <!-- TAB 1: DAFTAR PIUTANG USAHA (AR)               -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'AR'" x-cloak>
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-3 bg-slate-50/50">
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                            <i data-lucide="receipt" class="w-4 h-4 text-amber-600"></i>
                            <span>Daftar Faktur Piutang Usaha (Account Receivable - AR)</span>
                        </h3>
                        <p class="text-xs text-slate-500">Tagihan penjualan ke toko/outlet hasil pengiriman barang (Sending Good - GD).</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-600 font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">No. Dokumen AR</th>
                                <th class="py-3 px-4">Ref SO & Channel</th>
                                <th class="py-3 px-4">Toko / Pelanggan</th>
                                <th class="py-3 px-4">Sales Penanggung Jawab</th>
                                <th class="py-3 px-4">Jatuh Tempo</th>
                                <th class="py-3 px-4 text-right">Total Tagihan</th>
                                <th class="py-3 px-4 text-right">Terbayar</th>
                                <th class="py-3 px-4 text-right">Sisa Piutang</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-center w-28">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($receivables as $ar)
                                <tr class="hover:bg-amber-50/40 transition-colors cursor-pointer"
                                    :class="selectedAr?.id === {{ $ar->id }} ? 'bg-amber-50/70 border-l-4 border-amber-600' : ''"
                                    @click="selectAr({{ json_encode($ar) }})">
                                    <td class="py-3.5 px-4 font-mono font-bold text-amber-800">
                                        {{ $ar->doc_number }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-mono text-slate-700 font-bold">{{ $ar->so_number ?? '-' }}</div>
                                        @if($ar->so_channel === 'MOBILE_SALES')
                                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-extrabold bg-purple-100 text-purple-700 border border-purple-200">
                                                MOBILE SALES
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                                OFFICE
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 font-bold text-slate-900">
                                        {{ $ar->customer_name }}
                                        <span class="block text-[11px] font-normal text-slate-400">Code: {{ $ar->customer_code }}</span>
                                    </td>
                                    <td class="py-3.5 px-4 font-semibold text-slate-800">
                                        {{ $ar->sales_name ?? 'TAP Admin' }}
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-600">
                                        {{ date('d M Y', strtotime($ar->due_date)) }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-800">
                                        Rp {{ number_format($ar->total_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono text-emerald-600">
                                        Rp {{ number_format($ar->paid_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono font-extrabold text-rose-600">
                                        Rp {{ number_format($ar->balance_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        @if($ar->status === 'PAID')
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                LUNAS (PAID)
                                            </span>
                                        @elseif($ar->status === 'PARTIAL')
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-300">
                                                PARTIAL
                                            </span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800 border border-rose-300">
                                                UNPAID
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-center" @click.stop>
                                        @if($ar->status !== 'PAID' && $ar->balance_amount > 0)
                                            <button type="button" 
                                                    @click="quickPaySingleAr({{ json_encode($ar) }})" 
                                                    class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[11px] font-bold shadow-xs transition-all">
                                                Bayar TTK
                                            </button>
                                        @else
                                            <span class="text-slate-400 text-xs italic">Selesai</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="py-8 text-center text-slate-400">
                                        Belum ada data Piutang (AR).
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- INLINE DETAIL PIUTANG DI BAWAH TABEL -->
            <template x-if="selectedAr">
                <div id="arDetailSection" class="mt-4 bg-white rounded-2xl border border-amber-500/30 p-5 shadow-sm">
                    <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-amber-100 text-amber-800">DETAIL AR</span>
                            <h4 class="font-bold text-slate-900 text-xs font-mono" x-text="selectedAr.doc_number"></h4>
                        </div>
                        <span class="text-xs text-slate-400">Klik baris lain pada tabel untuk mengganti preview</span>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                        <div class="p-3 bg-slate-50 rounded-xl">
                            <span class="text-slate-400 block text-[10px]">Toko / Pelanggan:</span>
                            <span class="font-extrabold text-slate-800" x-text="selectedAr.customer_name"></span>
                            <span class="block text-[11px] text-slate-500" x-text="'Telp: ' + (selectedAr.customer_phone || '-')"></span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl">
                            <span class="text-slate-400 block text-[10px]">Sales Penanggung Jawab:</span>
                            <span class="font-extrabold text-slate-800" x-text="selectedAr.sales_name || 'TAP Admin'"></span>
                            <span class="block text-[11px] font-bold text-purple-600" x-text="selectedAr.so_channel"></span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl">
                            <span class="text-slate-400 block text-[10px]">Ref Sales Order:</span>
                            <span class="font-mono font-bold text-slate-800" x-text="selectedAr.so_number || '-'"></span>
                        </div>
                        <div class="p-3 bg-amber-50/60 rounded-xl border border-amber-200">
                            <span class="text-amber-800 block text-[10px] font-bold">Sisa Piutang Saat Ini:</span>
                            <span class="font-mono text-sm font-extrabold text-rose-600" x-text="'Rp ' + Number(selectedAr.balance_amount).toLocaleString('id-ID')"></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- ============================================== -->
        <!-- TAB 2: DAFTAR TANDA TERIMA KASIR (TTK)         -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'TTK'" x-cloak>
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-3 bg-slate-50/50">
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                            <i data-lucide="badge-check" class="w-4 h-4 text-emerald-600"></i>
                            <span>Daftar Tanda Terima Kasir (TTK Multi-AR)</span>
                        </h3>
                        <p class="text-xs text-slate-500">Bukti pembayaran resmi kasir dari toko/outlet atas pelunasan satu atau beberapa faktur AR.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-600 font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">No. Dokumen TTK</th>
                                <th class="py-3 px-4">Tanggal TTK</th>
                                <th class="py-3 px-4">Toko / Pelanggan</th>
                                <th class="py-3 px-4">Sales Terkait</th>
                                <th class="py-3 px-4">Metode Pembayaran</th>
                                <th class="py-3 px-4 text-center">Faktur Dilunasi</th>
                                <th class="py-3 px-4 text-right">Total Pembayaran</th>
                                <th class="py-3 px-4">Ref Dokumen BBM</th>
                                <th class="py-3 px-4 text-center w-28">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($payments as $p)
                                <tr class="hover:bg-emerald-50/40 transition-colors cursor-pointer"
                                    :class="selectedPayment?.id === {{ $p->id }} ? 'bg-emerald-50/70 border-l-4 border-emerald-600' : ''"
                                    @click="selectPayment({{ json_encode($p) }})">
                                    <td class="py-3.5 px-4 font-mono font-bold text-emerald-800">
                                        {{ $p->doc_number }}
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-600">
                                        {{ date('d M Y', strtotime($p->payment_date)) }}
                                    </td>
                                    <td class="py-3.5 px-4 font-bold text-slate-900">
                                        {{ $p->customer_name }}
                                        <span class="block text-[11px] font-normal text-slate-400">Kasir: {{ $p->cashier_name }}</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-700 font-semibold">
                                        {{ $p->sales_summary }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if($p->payment_method === 'CASH')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                TUNAI (CASH)
                                            </span>
                                        @elseif($p->payment_method === 'TRANSFER')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                                                TRANSFER BANK
                                            </span>
                                        @elseif($p->payment_method === 'DEPOSIT')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                                SALDO TITIPAN
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                                POTONGAN RETUR
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ $p->ar_count }} Faktur
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono font-extrabold text-emerald-700">
                                        Rp {{ number_format($p->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-mono text-indigo-700 font-bold">{{ $p->bbm_number ?? '-' }}</div>
                                        @if($p->bbm_type === 'TUNAI')
                                            <span class="text-[9px] font-extrabold text-emerald-700">BBM TUNAI</span>
                                        @elseif($p->bbm_type === 'NON_TUNAI')
                                            <span class="text-[9px] font-extrabold text-indigo-700">BBM NON TUNAI</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-center" @click.stop>
                                        <a href="{{ route('finance.print-kwitansi', $p->id) }}" 
                                           target="_blank"
                                           class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-[11px] font-bold inline-flex items-center gap-1 transition-all">
                                            <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                            <span>Kwitansi</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-8 text-center text-slate-400">
                                        Belum ada transaksi Tanda Terima Kasir (TTK).
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- INLINE DETAIL TTK DI BAWAH TABEL -->
            <template x-if="selectedPayment">
                <div class="mt-4 bg-white rounded-2xl border border-emerald-500/30 p-5 shadow-sm">
                    <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-emerald-100 text-emerald-800">DETAIL TTK</span>
                            <h4 class="font-bold text-slate-900 text-xs font-mono" x-text="selectedPayment.doc_number"></h4>
                            <span class="text-xs text-slate-500" x-text="'&bull; Toko: ' + selectedPayment.customer_name"></span>
                        </div>
                        <a :href="'/finance/print-kwitansi/' + selectedPayment.id" target="_blank" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold flex items-center gap-1.5 shadow-xs transition-all">
                            <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                            <span>Cetak Kwitansi TTK</span>
                        </a>
                    </div>

                    <div class="mb-3">
                        <span class="text-xs font-bold text-slate-700 block mb-2">Rincian Alokasi Faktur AR yang Dilunasi:</span>
                        <div class="border border-slate-200 rounded-xl overflow-hidden">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                                    <tr>
                                        <th class="py-2 px-3">No. AR</th>
                                        <th class="py-2 px-3">Ref SO & Channel</th>
                                        <th class="py-2 px-3">Sales Penanggung Jawab</th>
                                        <th class="py-2 px-3 text-right">Saldo Sebelum</th>
                                        <th class="py-2 px-3 text-right">Nominal Bayar</th>
                                        <th class="py-2 px-3 text-right">Sisa Saldo</th>
                                        <th class="py-2 px-3 text-center">Status AR</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <template x-for="alloc in selectedPayment.allocations" :key="alloc.id">
                                        <tr class="hover:bg-slate-50">
                                            <td class="py-2 px-3 font-mono font-bold text-amber-900" x-text="alloc.ar_number"></td>
                                            <td class="py-2 px-3">
                                                <span class="font-mono text-slate-700" x-text="alloc.so_number || '-'"></span>
                                                <template x-if="alloc.so_channel === 'MOBILE_SALES'">
                                                    <span class="ml-1 px-1 py-0.2 rounded text-[9px] font-bold bg-purple-100 text-purple-700">MOBILE</span>
                                                </template>
                                            </td>
                                            <td class="py-2 px-3 font-medium text-slate-800" x-text="alloc.sales_name || '-'"></td>
                                            <td class="py-2 px-3 text-right font-mono text-slate-500" x-text="'Rp ' + Number(alloc.balance_before).toLocaleString('id-ID')"></td>
                                            <td class="py-2 px-3 text-right font-mono font-extrabold text-emerald-700" x-text="'Rp ' + Number(alloc.amount).toLocaleString('id-ID')"></td>
                                            <td class="py-2 px-3 text-right font-mono text-slate-700" x-text="'Rp ' + Number(alloc.balance_after).toLocaleString('id-ID')"></td>
                                            <td class="py-2 px-3 text-center">
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                                      :class="alloc.ar_status === 'PAID' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
                                                      x-text="alloc.ar_status"></span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- ============================================== -->
        <!-- TAB 3: BUKTI BANK MASUK (BBM - 1 MENU TERPADU) -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'BBM'" x-cloak>
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-3 bg-slate-50/50">
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                            <i data-lucide="landmark" class="w-4 h-4 text-indigo-600"></i>
                            <span>Bukti Bank Masuk (BBM - Kas & Bank Masuk)</span>
                        </h3>
                        <p class="text-xs text-slate-500">1 Menu Terpadu untuk Bukti Bank Masuk Tunai (Kas) dan Non Tunai (Transfer / Giro).</p>
                    </div>

                    <!-- Filter Toggle BBM Tunai vs Non-Tunai -->
                    <div class="flex items-center gap-1 bg-slate-200/60 p-1 rounded-xl text-xs font-bold">
                        <button type="button" 
                                @click="bbmFilter = 'ALL'"
                                class="px-3 py-1.5 rounded-lg transition-all"
                                :class="bbmFilter === 'ALL' ? 'bg-white text-indigo-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                            Semua BBM ({{ count($bankReceipts) }})
                        </button>
                        <button type="button" 
                                @click="bbmFilter = 'TUNAI'"
                                class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1"
                                :class="bbmFilter === 'TUNAI' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-300"></span>
                            BBM Tunai
                        </button>
                        <button type="button" 
                                @click="bbmFilter = 'NON_TUNAI'"
                                class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1"
                                :class="bbmFilter === 'NON_TUNAI' ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-300"></span>
                            BBM Non Tunai
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-600 font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">No. Dokumen BBM</th>
                                <th class="py-3 px-4 text-center">Tipe BBM</th>
                                <th class="py-3 px-4">Ref Dokumen TTK</th>
                                <th class="py-3 px-4">Toko / Pelanggan</th>
                                <th class="py-3 px-4">Kas / Rekening Penerima</th>
                                <th class="py-3 px-4">Tanggal Masuk</th>
                                <th class="py-3 px-4 text-center">Faktur Terbayar</th>
                                <th class="py-3 px-4 text-right">Total Penerimaan</th>
                                <th class="py-3 px-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            <template x-for="bbm in filteredBbmList" :key="bbm.id">
                                <tr class="hover:bg-indigo-50/40 transition-colors cursor-pointer"
                                    :class="selectedBbm?.id === bbm.id ? 'bg-indigo-50/70 border-l-4 border-indigo-600' : ''"
                                    @click="selectBbm(bbm)">
                                    <td class="py-3.5 px-4 font-mono font-bold text-indigo-900" x-text="bbm.doc_number"></td>
                                    <td class="py-3.5 px-4 text-center">
                                        <template x-if="bbm.receipt_type === 'TUNAI'">
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                BBM TUNAI
                                            </span>
                                        </template>
                                        <template x-if="bbm.receipt_type === 'NON_TUNAI'">
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-indigo-100 text-indigo-800 border border-indigo-200">
                                                BBM NON TUNAI
                                            </span>
                                        </template>
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-emerald-700 font-bold" x-text="bbm.ttk_number || '-'"></td>
                                    <td class="py-3.5 px-4 font-bold text-slate-900">
                                        <span x-text="bbm.customer_name || '-'"></span>
                                        <span class="block text-[11px] font-normal text-slate-400" x-text="'Sales: ' + (bbm.sales_summary || '-')"></span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="font-bold text-slate-800 block" x-text="bbm.bank_name"></span>
                                        <span class="font-mono text-slate-500 text-[11px]" x-text="bbm.account_number || '-'"></span>
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-600" x-text="bbm.receipt_date"></td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700" x-text="(bbm.ar_count || 0) + ' Faktur'"></span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono font-extrabold text-indigo-900" x-text="'Rp ' + Number(bbm.amount).toLocaleString('id-ID')"></td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            VERIFIED
                                        </span>
                                    </td>
                                </tr>
                            </template>
                            <template x-if="filteredBbmList.length === 0">
                                <tr>
                                    <td colspan="9" class="py-8 text-center text-slate-400">
                                        Tidak ada data Bukti Bank Masuk (BBM) untuk filter ini.
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- INLINE DETAIL BBM DI BAWAH TABEL -->
            <template x-if="selectedBbm">
                <div class="mt-4 bg-white rounded-2xl border border-indigo-500/30 p-5 shadow-sm">
                    <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold"
                                  :class="selectedBbm.receipt_type === 'TUNAI' ? 'bg-emerald-100 text-emerald-800' : 'bg-indigo-100 text-indigo-800'"
                                  x-text="'DETAIL ' + (selectedBbm.receipt_type === 'TUNAI' ? 'BBM TUNAI' : 'BBM NON TUNAI')"></span>
                            <h4 class="font-bold text-slate-900 text-xs font-mono" x-text="selectedBbm.doc_number"></h4>
                            <span class="text-xs text-slate-500" x-text="'&bull; Toko: ' + (selectedBbm.customer_name || '-')"></span>
                        </div>
                        <span class="text-xs font-mono font-bold text-indigo-700" x-text="'Total: Rp ' + Number(selectedBbm.amount).toLocaleString('id-ID')"></span>
                    </div>

                    <div>
                        <span class="text-xs font-bold text-slate-700 block mb-2">Faktur AR Terkait Penerimaan Ini:</span>
                        <div class="border border-slate-200 rounded-xl overflow-hidden">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                                    <tr>
                                        <th class="py-2 px-3">No. AR</th>
                                        <th class="py-2 px-3">Ref SO & Channel</th>
                                        <th class="py-2 px-3">Sales Penanggung Jawab</th>
                                        <th class="py-2 px-3 text-right">Nominal Masuk</th>
                                        <th class="py-2 px-3 text-right">Sisa Piutang Setelah Bayar</th>
                                        <th class="py-2 px-3 text-center">Status AR</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <template x-for="alloc in selectedBbm.allocations" :key="alloc.id">
                                        <tr class="hover:bg-slate-50">
                                            <td class="py-2 px-3 font-mono font-bold text-amber-900" x-text="alloc.ar_number"></td>
                                            <td class="py-2 px-3">
                                                <span class="font-mono text-slate-700" x-text="alloc.so_number || '-'"></span>
                                                <template x-if="alloc.so_channel === 'MOBILE_SALES'">
                                                    <span class="ml-1 px-1 py-0.2 rounded text-[9px] font-bold bg-purple-100 text-purple-700">MOBILE</span>
                                                </template>
                                            </td>
                                            <td class="py-2 px-3 font-medium text-slate-800" x-text="alloc.sales_name || '-'"></td>
                                            <td class="py-2 px-3 text-right font-mono font-extrabold text-indigo-700" x-text="'Rp ' + Number(alloc.amount).toLocaleString('id-ID')"></td>
                                            <td class="py-2 px-3 text-right font-mono text-slate-700" x-text="'Rp ' + Number(alloc.balance_after).toLocaleString('id-ID')"></td>
                                            <td class="py-2 px-3 text-center">
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                                      :class="alloc.ar_status === 'PAID' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
                                                      x-text="alloc.ar_status"></span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </template>
        </div>

    </div>

    @push('scripts')
    <script>
        function financeApp() {
            return {
                activeTab: 'AR',
                bbmFilter: 'ALL',
                createTtkOpen: false,

                // Selection Objects for inline detail view
                selectedAr: null,
                selectedPayment: null,
                selectedBbm: null,

                // TTK Multi-AR Form State
                selectedCustomerId: '',
                paymentMethod: 'CASH',
                unpaidReceivables: @json($unpaidReceivables),
                bankReceiptsList: @json($bankReceipts),
                paymentsList: @json($payments),

                customerArList: [],
                selectedAllocations: {}, // format: { arId: { selected: true/false, amount: 100000 } }

                initData() {
                    const firstAr = @json($receivables->first());
                    if (firstAr) this.selectedAr = firstAr;

                    const firstPayment = @json($payments->first());
                    if (firstPayment) this.selectedPayment = firstPayment;

                    const firstBbm = @json($bankReceipts->first());
                    if (firstBbm) this.selectedBbm = firstBbm;

                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                },

                get filteredBbmList() {
                    if (this.bbmFilter === 'ALL') return this.bankReceiptsList;
                    return this.bankReceiptsList.filter(b => b.receipt_type === this.bbmFilter);
                },

                get selectedCount() {
                    return Object.values(this.selectedAllocations).filter(a => a.selected && a.amount > 0).length;
                },

                get calculatedTotalPay() {
                    return Object.values(this.selectedAllocations)
                        .filter(a => a.selected)
                        .reduce((sum, a) => sum + (parseFloat(a.amount) || 0), 0);
                },

                selectAr(ar) {
                    this.selectedAr = ar;
                    this.$nextTick(() => {
                        lucide.createIcons();
                        const el = document.getElementById('arDetailSection');
                        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    });
                },

                selectPayment(payment) {
                    this.selectedPayment = payment;
                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                },

                selectBbm(bbm) {
                    this.selectedBbm = bbm;
                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                },

                openTtkModal() {
                    this.createTtkOpen = true;
                    this.$nextTick(() => {
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    });
                },

                onCustomerChange() {
                    if (!this.selectedCustomerId) {
                        this.customerArList = [];
                        this.selectedAllocations = {};
                        return;
                    }

                    this.customerArList = this.unpaidReceivables.filter(
                        ar => ar.customer_id == this.selectedCustomerId
                    );

                    // Inisialisasi map alokasi
                    this.selectedAllocations = {};
                    this.customerArList.forEach(ar => {
                        this.selectedAllocations[ar.id] = {
                            selected: false,
                            amount: 0
                        };
                    });
                },

                toggleArSelection(ar) {
                    const item = this.selectedAllocations[ar.id];
                    item.selected = !item.selected;
                    if (item.selected) {
                        item.amount = parseFloat(ar.balance_amount) || 0;
                    } else {
                        item.amount = 0;
                    }
                },

                payFullAr(ar) {
                    const item = this.selectedAllocations[ar.id];
                    item.selected = true;
                    item.amount = parseFloat(ar.balance_amount) || 0;
                },

                onAmountInput(ar) {
                    const item = this.selectedAllocations[ar.id];
                    if (item.amount > parseFloat(ar.balance_amount)) {
                        item.amount = parseFloat(ar.balance_amount);
                    }
                    if (item.amount > 0) {
                        item.selected = true;
                    }
                },

                selectAllAr() {
                    this.customerArList.forEach(ar => {
                        this.selectedAllocations[ar.id] = {
                            selected: true,
                            amount: parseFloat(ar.balance_amount) || 0
                        };
                    });
                },

                deselectAllAr() {
                    this.customerArList.forEach(ar => {
                        this.selectedAllocations[ar.id] = {
                            selected: false,
                            amount: 0
                        };
                    });
                },

                quickPaySingleAr(ar) {
                    this.createTtkOpen = true;
                    this.selectedCustomerId = ar.customer_id;
                    this.onCustomerChange();

                    this.$nextTick(() => {
                        this.payFullAr(ar);
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    });
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
