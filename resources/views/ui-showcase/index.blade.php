<x-app-layout>
    <x-slot name="title">Master UI Components - Testing Lab</x-slot>
    <x-slot name="headerTitle">Master UI Components & Testing Lab</x-slot>
    <x-slot name="badge">SHOWCASE & QA</x-slot>

    <div x-data="uiShowcaseLab()" class="pb-16 max-w-7xl mx-auto space-y-6 w-full overflow-x-hidden">

        <!-- Top Header & Banner -->
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-6 sm:p-7 text-white shadow-xl relative overflow-hidden border border-indigo-900/50">
            <div class="relative z-10">
                <div class="flex items-center gap-2 mb-2 flex-wrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-extrabold uppercase tracking-wider bg-indigo-500/30 text-indigo-300 border border-indigo-400/30">
                        Design System & Component Library
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-extrabold uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                        Production Ready
                    </span>
                </div>
                <h1 class="text-xl font-black tracking-tight text-white sm:text-2xl">
                    Master UI Components Lab
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 mt-1.5 max-w-3xl leading-relaxed">
                    Pusat pengujian interaktif untuk seluruh komponen master UI di direktori <code class="text-indigo-300 bg-indigo-950/80 px-1.5 py-0.5 rounded font-mono">resources/views/components</code>. Silakan uji coba tombol konfirmasi, alert berhasil/gagal, filter wilayah, format mata uang, dan status badge di bawah ini.
                </p>
            </div>
            <div class="absolute -right-8 -bottom-8 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
        </div>

        <!-- ========================================================================= -->
        <!-- 1. ALERT BANNER COMPONENT (<x-alert />) & GLOBAL TOAST                    -->
        <!-- ========================================================================= -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-slate-200/80">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-5 border-b border-slate-100 gap-2">
                <div>
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-extrabold text-xs shrink-0">
                            01
                        </span>
                        <h2 class="text-sm sm:text-base font-extrabold text-slate-900">Alert Component & Toast Notifier</h2>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Menguji tampilan banner inline <code class="text-indigo-600 font-mono font-bold">&lt;x-alert /&gt;</code> dan notifikasi popup Toast.</p>
                </div>
                <span class="text-[11px] font-mono font-bold text-slate-400 bg-slate-50 px-2 py-1 rounded-lg border border-slate-200/60 self-start sm:self-auto">components/alert.blade.php</span>
            </div>

            <!-- Banner Alert Realtime Render Area -->
            <div class="mb-5">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Area Tampilan Banner Inline (&lt;x-alert /&gt;):</span>
                <x-alert />
            </div>

            <!-- Action Buttons to Trigger Alerts -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Trigger Success Alert -->
                <form method="POST" action="{{ route('ui.showcase.trigger-alert') }}" class="m-0">
                    @csrf
                    <input type="hidden" name="type" value="success">
                    <button type="submit"
                            class="w-full px-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-2xl shadow-xs hover:shadow transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-95">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                        <span>Test Alert Berhasil</span>
                    </button>
                </form>

                <!-- Trigger Error Alert -->
                <form method="POST" action="{{ route('ui.showcase.trigger-alert') }}" class="m-0">
                    @csrf
                    <input type="hidden" name="type" value="error">
                    <button type="submit"
                            class="w-full px-4 py-3 bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs rounded-2xl shadow-xs hover:shadow transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-95">
                        <i data-lucide="alert-octagon" class="w-4 h-4"></i>
                        <span>Test Alert Gagal</span>
                    </button>
                </form>

                <!-- Trigger Validation Error Alert -->
                <form method="POST" action="{{ route('ui.showcase.trigger-alert') }}" class="m-0">
                    @csrf
                    <input type="hidden" name="type" value="validation">
                    <input type="hidden" name="sample_email" value="invalid-email">
                    <input type="hidden" name="sample_nominal" value="100">
                    <input type="hidden" name="sample_notes" value="pendek">
                    <button type="submit"
                            class="w-full px-4 py-3 bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-xs rounded-2xl shadow-xs hover:shadow transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-95">
                        <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                        <span>Test Validasi Form Gagal</span>
                    </button>
                </form>

                <!-- Trigger Toast Client-side -->
                <button type="button"
                        @click="window.showToast('Data PO-2026-0099 telah disimpan ke sistem tanpa refresh!', 'success', 'Operasi Berhasil')"
                        class="w-full px-4 py-3 bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-xs rounded-2xl shadow-xs hover:shadow transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-95">
                    <i data-lucide="bell-ring" class="w-4 h-4 text-emerald-400"></i>
                    <span>Test Toast Popup (JS)</span>
                </button>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- 2. GLOBAL CONFIRMATION MODAL (<x-confirm-modal />)                        -->
        <!-- ========================================================================= -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-slate-200/80">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-5 border-b border-slate-100 gap-2">
                <div>
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-extrabold text-xs shrink-0">
                            02
                        </span>
                        <h2 class="text-sm sm:text-base font-extrabold text-slate-900">Modal Konfirmasi Tindakan (<x-confirm-modal />)</h2>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Modal dialog konfirmasi universal. Jika user klik <b>"Tidak / Batal"</b>, proses dihentikan seketika <b>tanpa loading screen</b>.
                    </p>
                </div>
                <span class="text-[11px] font-mono font-bold text-slate-400 bg-slate-50 px-2 py-1 rounded-lg border border-slate-200/60 self-start sm:self-auto">components/confirm-modal.blade.php</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- Test 1: Konfirmasi Simpan PO (Primary) -->
                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/50 flex flex-col justify-between space-y-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                            <h4 class="text-xs font-extrabold text-slate-800 uppercase">Tipe Primary (Default)</h4>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            Untuk simpan data baru, buat PO, update master data.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('ui.showcase.trigger-alert') }}" class="m-0"
                          data-confirm="Simpan & Terbitkan Purchase Order?"
                          data-confirm-text="Pastikan daftar item, kuantiti, dan vendor penyedia sudah sesuai sebelum disimpan."
                          data-confirm-type="primary"
                          data-confirm-badge="KONFIRMASI PENERBITAN PO"
                          data-confirm-btn="Ya, Terbitkan PO"
                          data-cancel-btn="Periksa Kembali">
                        @csrf
                        <input type="hidden" name="type" value="success">
                        <button type="submit"
                                class="w-full py-2.5 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-xs transition-all cursor-pointer">
                            Test Konfirmasi Simpan PO
                        </button>
                    </form>
                </div>

                <!-- Test 2: Konfirmasi Terima Barang / Approval (Emerald) -->
                <div class="p-4 rounded-2xl border border-emerald-200 bg-emerald-50/20 flex flex-col justify-between space-y-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <h4 class="text-xs font-extrabold text-emerald-950 uppercase">Tipe Emerald (Goods Received)</h4>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            Untuk Goods Received (GR), approval transaksi, penambahan stok.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('ui.showcase.trigger-alert') }}" class="m-0"
                          data-confirm="Konfirmasi Penerimaan Barang (GR)?"
                          data-confirm-text="Stok fisik sejumlah 100 pcs akan langsung ditambahkan ke Gudang Utama TAP!"
                          data-confirm-type="emerald"
                          data-confirm-badge="GOODS RECEIVED (GR)"
                          data-confirm-btn="Ya, Terima Barang Sekarang"
                          data-cancel-btn="Tunda Penerimaan">
                        @csrf
                        <input type="hidden" name="type" value="success">
                        <button type="submit"
                                class="w-full py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition-all cursor-pointer">
                            Test Konfirmasi GR (Emerald)
                        </button>
                    </form>
                </div>

                <!-- Test 3: Konfirmasi VOID / Pembatalan (Danger) -->
                <div class="p-4 rounded-2xl border border-rose-200 bg-rose-50/20 flex flex-col justify-between space-y-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                            <h4 class="text-xs font-extrabold text-rose-950 uppercase">Tipe Danger (Void / Batal)</h4>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            Untuk Void Dokumen PO, pembatalan invoice, dan penghapusan data.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('ui.showcase.trigger-alert') }}" class="m-0"
                          data-confirm="Batalkan & Void Dokumen PO-2026-0044?"
                          data-confirm-text="PERINGATAN: Tindakan ini permanen. Dokumen yang dibatalkan tidak dapat dikembalikan lagi."
                          data-confirm-type="danger"
                          data-confirm-badge="PERINGATAN PEMBATALAN PERMANEN"
                          data-confirm-btn="Ya, Batalkan & VOID Sekarang"
                          data-cancel-btn="Jangan Batalkan (Kembali)">
                        @csrf
                        <input type="hidden" name="type" value="void">
                        <button type="submit"
                                class="w-full py-2.5 px-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-xs transition-all cursor-pointer">
                            Test Konfirmasi VOID (Danger)
                        </button>
                    </form>
                </div>

                <!-- Test 4: Konfirmasi Via JavaScript Callback -->
                <div class="p-4 rounded-2xl border border-amber-200 bg-amber-50/20 flex flex-col justify-between space-y-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            <h4 class="text-xs font-extrabold text-amber-950 uppercase">Tipe Warning / JS Action</h4>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            Dipanggil lewat JS <code class="text-amber-800 font-mono">window.confirmAction(...)</code> untuk aksi khusus tanpa form submit.
                        </p>
                    </div>

                    <button type="button"
                            @click="window.confirmAction({
                                title: 'Verifikasi Selisih Stok Fisik?',
                                message: 'Terdapat catatan selisih 2 unit fisik saat proses audit. Apakah ingin tetap melanjutkan penyimpanan mutasi?',
                                type: 'warning',
                                badge: 'PERINGATAN AUDIT STOK',
                                confirmText: 'Ya, Tetap Lanjutkan',
                                cancelText: 'Batal & Hitung Ulang',
                                onConfirm: () => {
                                    window.showToast('Aksi audit dikonfirmasi via JavaScript Callback!', 'success', 'Audit Terverifikasi');
                                }
                            })"
                            class="w-full py-2.5 px-3 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs transition-all cursor-pointer">
                        Test Konfirmasi via JS API
                    </button>
                </div>

            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- 3. TERRITORY FILTER COMPONENT (<x-filter-territory />)                    -->
        <!-- ========================================================================= -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-slate-200/80">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-5 border-b border-slate-100 gap-2">
                <div>
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center font-extrabold text-xs shrink-0">
                            03
                        </span>
                        <h2 class="text-sm sm:text-base font-extrabold text-slate-900">Territory Filter Spinner (<x-filter-territory />)</h2>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Dropdown pembatasan hierarki Area &rarr; Cluster &rarr; TAP & Gudang sesuai hak akses user.</p>
                </div>
                <span class="text-[11px] font-mono font-bold text-slate-400 bg-slate-50 px-2 py-1 rounded-lg border border-slate-200/60 self-start sm:self-auto">components/filter-territory.blade.php</span>
            </div>

            <!-- Territory Filter Wrapper with Auto-Wrap Responsive Layout -->
            <div class="w-full overflow-hidden">
                <x-filter-territory 
                    :showArea="true"
                    :showCluster="true"
                    :showTap="true"
                    :areas="$areas"
                    :clusters="$clusters"
                    :taps="$taps"
                    searchPlaceholder="Cari data tester..."
                    modelSearch="dummySearch"
                />
            </div>

            <!-- Reactive Status Display -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 text-xs text-slate-600 flex flex-wrap items-center gap-y-2 gap-x-4">
                <span class="font-bold text-slate-800 flex items-center gap-1.5">
                    <i data-lucide="info" class="w-3.5 h-3.5 text-indigo-600"></i>
                    Status Seleksi Reaktif:
                </span>
                <span>Area: <strong class="text-indigo-600 font-mono" x-text="selectedAreaText()"></strong></span>
                <span>&bull;</span>
                <span>Cluster: <strong class="text-indigo-600 font-mono" x-text="selectedClusterText()"></strong></span>
                <span>&bull;</span>
                <span>TAP: <strong class="text-indigo-600 font-mono" x-text="selectedTapText()"></strong></span>
                <span>&bull;</span>
                <span>Teks Pencarian: <strong class="text-indigo-600 font-mono" x-text="dummySearch || '(Kosong)'"></strong></span>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- 4. DATE RANGE FILTER COMPONENT (<x-filter-date-range />)                  -->
        <!-- ========================================================================= -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-slate-200/80">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-5 border-b border-slate-100 gap-2">
                <div>
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-extrabold text-xs shrink-0">
                            04
                        </span>
                        <h2 class="text-sm sm:text-base font-extrabold text-slate-900">Rentang Tanggal Transaksi (<x-filter-date-range />)</h2>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Preset filter cepat: Hari ini, 7 hari terakhir, 30 hari terakhir, bulan ini.</p>
                </div>
                <span class="text-[11px] font-mono font-bold text-slate-400 bg-slate-50 px-2 py-1 rounded-lg border border-slate-200/60 self-start sm:self-auto">components/filter-date-range.blade.php</span>
            </div>

            <div class="w-full overflow-hidden">
                <x-filter-date-range 
                    :startDate="request('start_date', now()->subDays(7)->format('Y-m-d'))"
                    :endDate="request('end_date', now()->format('Y-m-d'))"
                    :presets="['today', 'last_7_days', 'last_30_days', 'this_month']"
                />
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- 5. STATUS BADGE COMPONENT (<x-status-badge />)                            -->
        <!-- ========================================================================= -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-slate-200/80">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-5 border-b border-slate-100 gap-2">
                <div>
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-extrabold text-xs shrink-0">
                            05
                        </span>
                        <h2 class="text-sm sm:text-base font-extrabold text-slate-900">Status Badge System (<x-status-badge />)</h2>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Badge visual konsisten dengan warna dan ikon otomatis berdasarkan status transaksi ERP.</p>
                </div>
                <span class="text-[11px] font-mono font-bold text-slate-400 bg-slate-50 px-2 py-1 rounded-lg border border-slate-200/60 self-start sm:self-auto">components/status-badge.blade.php</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Master Status -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2.5">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">1. Master & Keaktifan</span>
                    <div class="flex flex-wrap gap-2">
                        <x-status-badge status="ACTIVE" />
                        <x-status-badge status="INACTIVE" />
                    </div>
                </div>

                <!-- Procurement Status -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2.5">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">2. Pengadaan (PO & GR)</span>
                    <div class="flex flex-wrap gap-2">
                        <x-status-badge status="DRAFT" />
                        <x-status-badge status="ORDERED" />
                        <x-status-badge status="RECEIVED" />
                        <x-status-badge status="CONFIRMED" />
                        <x-status-badge status="VOID" />
                    </div>
                </div>

                <!-- Sales & Approval Status -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2.5">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">3. Sales & Approval</span>
                    <div class="flex flex-wrap gap-2">
                        <x-status-badge status="PENDING_APPROVAL" />
                        <x-status-badge status="APPROVED" />
                        <x-status-badge status="REJECTED" />
                        <x-status-badge status="DELIVERED" />
                    </div>
                </div>

                <!-- Finance Status -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2.5">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">4. Finance & Bayar</span>
                    <div class="flex flex-wrap gap-2">
                        <x-status-badge status="PAID" />
                        <x-status-badge status="UNPAID" />
                        <x-status-badge status="PARTIAL" />
                        <x-status-badge status="OVERDUE" />
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- 6. CURRENCY INPUT & DISPLAY (<x-money /> & <x-input-currency />)          -->
        <!-- ========================================================================= -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-slate-200/80">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-5 border-b border-slate-100 gap-2">
                <div>
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-extrabold text-xs shrink-0">
                            06
                        </span>
                        <h2 class="text-sm sm:text-base font-extrabold text-slate-900">Format Rupiah & Input Mata Uang</h2>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Format uang terstandarisasi <code class="text-purple-600 font-mono">&lt;x-money /&gt;</code> dan input dinamis <code class="text-purple-600 font-mono">&lt;x-input-currency /&gt;</code>.</p>
                </div>
                <span class="text-[11px] font-mono font-bold text-slate-400 bg-slate-50 px-2 py-1 rounded-lg border border-slate-200/60 self-start sm:self-auto">components/money.blade.php & input-currency.blade.php</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Display Nominal -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                    <span class="text-xs font-bold text-slate-700 block">Contoh Tampilan &lt;x-money /&gt;:</span>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">Nominal Kecil:</span>
                            <span class="font-extrabold text-slate-900"><x-money :amount="15000" /></span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">Total Purchase Order:</span>
                            <span class="font-extrabold text-emerald-600"><x-money :amount="24500000" /></span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">Piutang Dagang (AR):</span>
                            <span class="font-extrabold text-rose-600"><x-money :amount="182450000" /></span>
                        </div>
                    </div>
                </div>

                <!-- Input Currency Realtime -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                    <x-input-currency 
                        name="demo_nominal"
                        label="Test Input Nominal Rupiah Otomatis"
                        placeholder="Ketik angka di sini..."
                        :value="1500000"
                    />
                    <p class="text-[11px] text-slate-400 mt-2">
                        Format titik ribuan akan terisi otomatis saat Anda mengetik nominal angka di atas.
                    </p>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- 7. EMPTY STATE COMPONENT (<x-empty-state />)                              -->
        <!-- ========================================================================= -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-slate-200/80">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-4 border-b border-slate-100 gap-2">
                <div>
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center font-extrabold text-xs shrink-0">
                            07
                        </span>
                        <h2 class="text-sm sm:text-base font-extrabold text-slate-900">Tampilan Data Kosong (<x-empty-state />)</h2>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Placeholder elegan ketika hasil filter atau database belum memiliki data.</p>
                </div>
                <span class="text-[11px] font-mono font-bold text-slate-400 bg-slate-50 px-2 py-1 rounded-lg border border-slate-200/60 self-start sm:self-auto">components/empty-state.blade.php</span>
            </div>

            <x-empty-state 
                icon="package-open"
                title="Tidak Ada Dokumen Transaksi"
                subtitle="Tidak ditemukan catatan transaksi untuk rentang filter wilayah dan tanggal yang dipilih."
                actionClick="window.showToast('Tombol aksi pada empty state berhasil ditekan!', 'success', 'Interaksi Berhasil')"
                actionLabel="Muat Ulang Filter"
                actionIcon="rotate-cw"
            />
        </div>

        <!-- ========================================================================= -->
        <!-- 8. GLOBAL LOADING OVERLAY                                                 -->
        <!-- ========================================================================= -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-slate-200/80">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-4 border-b border-slate-100 gap-2">
                <div>
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-extrabold text-xs shrink-0">
                            08
                        </span>
                        <h2 class="text-sm sm:text-base font-extrabold text-slate-900">Global Loading Overlay</h2>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Overlay transparan dengan spinner modern saat sistem sedang memproses transaksi ke database.</p>
                </div>
                <span class="text-[11px] font-mono font-bold text-slate-400 bg-slate-50 px-2 py-1 rounded-lg border border-slate-200/60 self-start sm:self-auto">layouts/app.blade.php</span>
            </div>

            <div class="flex items-center gap-3">
                <button type="button"
                        @click="testLoadingOverlay()"
                        class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-xs shadow-md transition-all flex items-center gap-2 cursor-pointer active:scale-95">
                    <i data-lucide="loader" class="w-4 h-4 text-cyan-400 animate-spin"></i>
                    <span>Test Loading Screen (2 Detik Auto-Close)</span>
                </button>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function uiShowcaseLab() {
            return {
                allAreas: @json($areas),
                allClusters: @json($clusters),
                allTaps: @json($taps),

                selectedArea: '',
                selectedCluster: '',
                selectedTap: '',
                dummySearch: '',

                get clusterOptions() {
                    if (!this.selectedArea) return this.allClusters;
                    return this.allClusters.filter(c => String(c.area_id) === String(this.selectedArea));
                },

                get tapOptions() {
                    if (this.selectedCluster) {
                        return this.allTaps.filter(t => String(t.cluster_id) === String(this.selectedCluster));
                    }
                    if (this.selectedArea) {
                        const clsIds = this.clusterOptions.map(c => String(c.id));
                        return this.allTaps.filter(t => clsIds.includes(String(t.cluster_id)));
                    }
                    return this.allTaps;
                },

                onAreaChange() {
                    this.selectedCluster = '';
                    this.selectedTap = '';
                    this.refreshIcons();
                },

                onClusterChange() {
                    this.selectedTap = '';
                    this.refreshIcons();
                },

                onTapChange() {
                    this.refreshIcons();
                },

                onSearchClear() {
                    this.dummySearch = '';
                },

                selectedAreaText() {
                    if (!this.selectedArea) return 'Semua Area';
                    const a = this.allAreas.find(x => String(x.id) === String(this.selectedArea));
                    return a ? `${a.name} (${a.code})` : this.selectedArea;
                },

                selectedClusterText() {
                    if (!this.selectedCluster) return 'Semua Cluster';
                    const c = this.allClusters.find(x => String(x.id) === String(this.selectedCluster));
                    return c ? `${c.name} (${c.code})` : this.selectedCluster;
                },

                selectedTapText() {
                    if (!this.selectedTap) return 'Semua TAP';
                    const t = this.allTaps.find(x => String(x.id) === String(this.selectedTap));
                    return t ? `${t.name} (${t.code})` : this.selectedTap;
                },

                refreshIcons() {
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                testLoadingOverlay() {
                    if (typeof window.showLoading === 'function') {
                        window.showLoading('Memproses Validasi Transaksi...', 'Menghubungkan ke database ERP...', 'TEST-DOC');
                        setTimeout(() => {
                            if (typeof window.hideLoading === 'function') {
                                window.hideLoading();
                            }
                            window.showToast('Proses pengujian loading berhasil ditutup otomatis!', 'success', 'Selesai');
                        }, 2000);
                    }
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
