<x-app-layout>
    <x-slot name="title">Master Data Toko & Outlet Pelanggan</x-slot>
    <x-slot name="headerTitle">Toko & Outlet Pelanggan</x-slot>
    <x-slot name="badge">Master Customer</x-slot>

    <div x-data="customerMasterApp()">
        
        <!-- Top Action & Navigation Banner -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Master Toko, Outlet & Sub Dealer</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">PT WAHANA PUTRA YUDHA</span>
                </h2>
                <p class="text-xs text-slate-500">Database seluruh mitra toko konter, modern outlet, dan sub dealer resmi yang dilayani oleh armada penjualan</p>
            </div>

            <button type="button" 
                    @click="createOpen = !createOpen" 
                    class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-sm transition-all active:scale-95 shrink-0">
                <i data-lucide="store" class="w-4 h-4 text-amber-400"></i>
                <span x-text="createOpen ? 'Tutup Formulir' : '+ Tambah Toko Baru'"></span>
            </button>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between">
                <span class="flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                    {{ session('success') }}
                </span>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800">&times;</button>
            </div>
        @endif

        <!-- ============================================== -->
        <!-- FORM TAMBAH TOKO BARU (INLINE CARD)            -->
        <!-- ============================================== -->
        <div x-show="createOpen" x-cloak x-transition class="mb-6 bg-white rounded-2xl border-2 border-amber-500/30 p-5 shadow-lg">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-amber-100 text-amber-700">
                        <i data-lucide="store" class="w-4 h-4"></i>
                    </span>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Registrasi Outlet / Toko Baru</h3>
                        <p class="text-[11px] text-slate-500">Lengkapi profil toko, status DigiPOS, data pajak (NPWP), dan limit kredit</p>
                    </div>
                </div>
                <button @click="createOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('master.customers.store') }}">
                @csrf
                
                <!-- Section 1: Profil Toko, Tipe & Limit Plafon -->
                <div class="mb-4 p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                    <h4 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <i data-lucide="info" class="w-3.5 h-3.5 text-amber-600"></i>
                        <span>1. Profil Toko</span>
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Toko / Outlet <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" required class="w-full text-xs font-semibold bg-white border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Outlet <span class="text-rose-500">*</span></label>
                            <select name="type" required class="w-full text-xs font-bold bg-white border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                                <option value="RETAIL">Retail (Outlet / Konter Reguler)</option>
                                <option value="GROSIR">Grosir (Sub-Dealer / Toko Besar)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nomor RS / Chip Mkios Utama</label>
                            <input type="text" name="nomor_rs" class="w-full text-xs font-mono font-bold bg-white border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Limit Plafon Piutang (Rp) <span class="text-rose-500">*</span></label>
                            <input type="number" name="credit_limit" value="10000000" min="0" step="500000" required class="w-full text-xs font-mono font-bold bg-white border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                            <span class="text-[10px] text-slate-500 block mt-0.5">Batas maksimal piutang penjualan toko</span>
                        </div>
                    </div>

                    <!-- Hidden Default Konsinyasi -->
                    <input type="hidden" name="is_consignment" value="1">

                    <!-- Toggle Checkbox DigiPOS -->
                    <div class="mt-4 pt-3.5 border-t border-slate-200/90">
                        <div class="p-3.5 rounded-xl border transition-all" :class="isDigipos ? 'bg-rose-50/50 border-rose-200' : 'bg-white border-slate-200'">
                            <label class="flex items-start gap-2.5 cursor-pointer">
                                <input type="checkbox" name="is_digipos" value="1" x-model="isDigipos" class="mt-0.5 rounded border-slate-300 text-rose-600 focus:ring-rose-500 w-4 h-4 cursor-pointer">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-slate-900">Outlet DigiPOS Telkomsel</span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold" :class="isDigipos ? 'bg-rose-100 text-rose-800 border border-rose-200' : 'bg-slate-200 text-slate-600'" x-text="isDigipos ? 'DigiPOS Aktif' : 'Non-DigiPOS'"></span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 mt-0.5">Centang jika outlet memiliki akun DigiPOS resmi dari Telkomsel</p>
                                    
                                    <div x-show="isDigipos" x-cloak class="mt-2.5">
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">ID DigiPOS Toko</label>
                                        <input type="text" name="digipos_id" class="w-full text-xs font-mono font-bold bg-white border border-rose-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Data Pemilik & Identitas Pajak (KTP & NPWP) -->
                <div class="mb-4 p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                    <h4 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-indigo-600"></i>
                        <span>2. Identitas Pemilik & Pajak (KTP & NPWP)</span>
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama PIC / Pemilik Toko</label>
                            <input type="text" name="customer_pic" placeholder="" class="w-full text-xs font-semibold bg-white border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nomor KTP Pemilik</label>
                            <input type="tesxt" name="customer_ktp" placeholder="" class="w-full text-xs font-mono bg-white border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nomor NPWP Toko / PIC</label>
                            <input type="text" name="npwp" placeholder="" class="w-full text-xs font-mono bg-white border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Sesuai NPWP</label>
                            <input type="text" name="alamat_npwp" placeholder=" class="w-full text-xs bg-white border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none">
                        </div>
                    </div>
                </div>

                <!-- Section 3: Wilayah Distribusi, Kontak & Alamat (Kelurahan di-hide) -->
                <div class="mb-4 p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                    <h4 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-emerald-600"></i>
                        <span>3. Wilayah Operasional, Jadwal PJP & Alamat</span>
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Wilayah TAP <span class="text-rose-500">*</span></label>
                            <select name="tap_id" required class="w-full text-xs font-bold bg-white border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none">
                                @foreach($taps as $tap)
                                    <option value="{{ $tap->id }}">{{ $tap->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Salesman Pembina (PJP)</label>
                            <select name="sales_id" class="w-full text-xs font-bold bg-white border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none">
                                <option value="">-- Pilih Salesman --</option>
                                @foreach($salesList as $sl)
                                    <option value="{{ $sl->id }}">[{{ $sl->code }}] {{ $sl->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Hari Kunjungan PJP</label>
                            <select name="hari_pjp" class="w-full text-xs font-bold bg-white border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none">
                                <option value="">-- Pilih Hari PJP --</option>
                                <option value="SENIN">SENIN</option>
                                <option value="SELASA">SELASA</option>
                                <option value="RABU">RABU</option>
                                <option value="KAMIS">KAMIS</option>
                                <option value="JUMAT">JUMAT</option>
                                <option value="SABTU">SABTU</option>
                                <option value="MINGGU">MINGGU</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">No. Telepon / WA Toko</label>
                            <input type="text" name="phone" placeholder="" class="w-full text-xs font-semibold bg-white border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kecamatan</label>
                            <input type="text" name="kecamatan_name" placeholder="" class="w-full text-xs bg-white border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kota / Kabupaten</label>
                            <input type="text" name="kota_name" placeholder="" class="w-full text-xs bg-white border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Provinsi</label>
                            <input type="text" name="provinsi_name" placeholder="" class="w-full text-xs bg-white border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Lengkap Toko</label>
                            <input type="text" name="address" placeholder="" class="w-full text-xs bg-white border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Latitude</label>
                            <input type="number" step="any" name="latitude" placeholder="" class="w-full text-xs font-mono bg-white border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Longitude</label>
                            <input type="number" step="any" name="longitude" placeholder="" class="w-full text-xs font-mono bg-white border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="createOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-sm transition-all">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Data Toko</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- ============================================== -->
        <!-- MODAL EDIT CUSTOMER                            -->
        <!-- ============================================== -->
        <template x-if="editModalOpen && editData">
            <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                <div class="bg-white rounded-2xl border border-slate-200 max-w-3xl w-full p-6 shadow-2xl max-h-[90vh] overflow-y-auto" @click.outside="editModalOpen = false">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 sticky top-0 bg-white z-10">
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">Edit Data Toko [<span x-text="editData.code"></span>]</h3>
                            <p class="text-[11px] text-slate-400">Perbarui rincian profil outlet, status DigiPOS, NPWP, dan plafon kredit</p>
                        </div>
                        <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
                    </div>

                    <form :action="'/master/customers/update/' + editData.id" method="POST">
                        @csrf
                        <div class="space-y-4 mb-5">
                            <!-- Section Profil & Plafon -->
                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                                <h4 class="text-xs font-bold text-slate-800 mb-3 flex items-center gap-1.5">
                                    <i data-lucide="store" class="w-3.5 h-3.5 text-amber-600"></i>
                                    <span>Profil Toko & Status Kerjasama</span>
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Toko / Outlet <span class="text-rose-500">*</span></label>
                                        <input type="text" name="name" x-model="editData.name" required class="w-full text-xs font-bold bg-white border border-slate-200 rounded-xl px-3 py-2 focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Outlet</label>
                                        <select name="type" x-model="editData.type" class="w-full text-xs font-bold bg-white border border-slate-200 rounded-xl px-3 py-2 focus:outline-none">
                                            <option value="RETAIL">RETAIL (Outlet Konter)</option>
                                            <option value="GROSIR">GROSIR (Sub Dealer)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor RS / Chip Mkios Utama</label>
                                        <input type="text" name="nomor_rs" x-model="editData.nomor_rs" class="w-full text-xs font-mono font-bold bg-white border border-slate-200 rounded-xl px-3 py-2 focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Limit Plafon Piutang (Rp)</label>
                                        <input type="number" name="credit_limit" x-model.number="editData.credit_limit" min="0" step="500000" class="w-full text-xs font-mono font-bold bg-white border border-slate-200 rounded-xl px-3 py-2 focus:outline-none">
                                    </div>
                                </div>

                                <!-- Hidden Default Konsinyasi -->
                                <input type="hidden" name="is_consignment" value="1">

                                <!-- Toggle Checkbox DigiPOS Edit -->
                                <div class="pt-3 border-t border-slate-200">
                                    <div class="p-3 rounded-xl border transition-all" :class="editData.is_digipos ? 'bg-rose-50/50 border-rose-200' : 'bg-white border-slate-200'">
                                        <label class="flex items-start gap-2.5 cursor-pointer">
                                            <input type="checkbox" name="is_digipos" value="1" x-model="editData.is_digipos" class="mt-0.5 rounded border-slate-300 text-rose-600 focus:ring-rose-500 w-4 h-4 cursor-pointer">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-xs font-bold text-slate-900">Outlet DigiPOS</span>
                                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold" :class="editData.is_digipos ? 'bg-rose-100 text-rose-800' : 'bg-slate-200 text-slate-600'" x-text="editData.is_digipos ? 'DigiPOS Aktif' : 'Non-DigiPOS'"></span>
                                                </div>
                                                <div x-show="editData.is_digipos" class="mt-2">
                                                    <label class="block text-[10px] font-bold text-slate-600 mb-0.5">ID DigiPOS Toko</label>
                                                    <input type="text" name="digipos_id" x-model="editData.digipos_id" class="w-full text-xs font-mono font-bold bg-white border border-rose-300 rounded-lg px-2.5 py-1.5 focus:outline-none">
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Section Pajak (NPWP) & Pemilik -->
                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                                <h4 class="text-xs font-bold text-slate-800 mb-3 flex items-center gap-1.5">
                                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-indigo-600"></i>
                                    <span>Identitas Pemilik & Pajak (KTP & NPWP)</span>
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama PIC / Pemilik Toko</label>
                                        <input type="text" name="customer_pic" x-model="editData.customer_pic" placeholder="" class="w-full text-xs font-semibold bg-white border border-slate-200 rounded-xl px-3 py-2">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">No. KTP Pemilik</label>
                                        <input type="text" name="customer_ktp" x-model="editData.customer_ktp" placeholder="" class="w-full text-xs font-mono bg-white border border-slate-200 rounded-xl px-3 py-2">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor NPWP Toko / PIC</label>
                                        <input type="text" name="npwp" x-model="editData.npwp" placeholder="" class="w-full text-xs font-mono bg-white border border-slate-200 rounded-xl px-3 py-2">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Sesuai NPWP</label>
                                        <input type="text" name="alamat_npwp" x-model="editData.alamat_npwp" placeholder="" class="w-full text-xs bg-white border border-slate-200 rounded-xl px-3 py-2">
                                    </div>
                                </div>
                            </div>

                            <!-- Section Operasional & Wilayah (Kelurahan di-hide) -->
                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                                <h4 class="text-xs font-bold text-slate-800 mb-3 flex items-center gap-1.5">
                                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-emerald-600"></i>
                                    <span>Operasional, Wilayah & Alamat</span>
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-3">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Wilayah TAP <span class="text-rose-500">*</span></label>
                                        <select name="tap_id" x-model="editData.tap_id" class="w-full text-xs font-bold bg-white border border-slate-200 rounded-xl px-3 py-2">
                                            @foreach($taps as $tap)
                                                <option value="{{ $tap->id }}">{{ $tap->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Salesman PJP</label>
                                        <select name="sales_id" x-model="editData.sales_id" class="w-full text-xs font-bold bg-white border border-slate-200 rounded-xl px-3 py-2">
                                            <option value="">-- Tanpa Salesman --</option>
                                            @foreach($salesList as $sl)
                                                <option value="{{ $sl->id }}">[{{ $sl->code }}] {{ $sl->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Hari Kunjungan PJP</label>
                                        <select name="hari_pjp" x-model="editData.hari_pjp" class="w-full text-xs font-bold bg-white border border-slate-200 rounded-xl px-3 py-2">
                                            <option value="">-- Pilih Hari PJP --</option>
                                            <option value="SENIN">SENIN</option>
                                            <option value="SELASA">SELASA</option>
                                            <option value="RABU">RABU</option>
                                            <option value="KAMIS">KAMIS</option>
                                            <option value="JUMAT">JUMAT</option>
                                            <option value="SABTU">SABTU</option>
                                            <option value="MINGGU">MINGGU</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-3">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">No. Telepon / WA</label>
                                        <input type="text" name="phone" x-model="editData.phone" class="w-full text-xs font-medium bg-white border border-slate-200 rounded-xl px-3 py-2">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Kecamatan</label>
                                        <input type="text" name="kecamatan_name" x-model="editData.kecamatan_name" class="w-full text-xs bg-white border border-slate-200 rounded-xl px-3 py-2">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Kota / Kabupaten</label>
                                        <input type="text" name="kota_name" x-model="editData.kota_name" class="w-full text-xs bg-white border border-slate-200 rounded-xl px-3 py-2">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-3">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Provinsi</label>
                                        <input type="text" name="provinsi_name" x-model="editData.provinsi_name" class="w-full text-xs bg-white border border-slate-200 rounded-xl px-3 py-2">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Lengkap</label>
                                        <input type="text" name="address" x-model="editData.address" class="w-full text-xs bg-white border border-slate-200 rounded-xl px-3 py-2">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Keaktifan</label>
                                        <select name="status" x-model="editData.status" class="w-full text-xs font-bold bg-white border border-slate-200 rounded-xl px-3 py-2">
                                            <option value="ACTIVE">ACTIVE (Aktif)</option>
                                            <option value="INACTIVE">INACTIVE (Nonaktif)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Latitude</label>
                                        <input type="number" step="any" name="latitude" x-model="editData.latitude" placeholder="" class="w-full text-xs font-mono bg-white border border-slate-200 rounded-xl px-3 py-2">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Longitude</label>
                                        <input type="number" step="any" name="longitude" x-model="editData.longitude" placeholder="" class="w-full text-xs font-mono bg-white border border-slate-200 rounded-xl px-3 py-2">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                            <button type="button" @click="editModalOpen = false" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl text-xs">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 bg-slate-900 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-sm">
                                <i data-lucide="check" class="w-4 h-4"></i>
                                <span>Perbarui Toko</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- ============================================== -->
        <!-- FILTER WILAYAH, TIPE & PENCARIAN (REUSABLE UI) -->
        <!-- ============================================== -->
        <x-filter-territory
            tab="customers"
            :server-side="true"
            :action="route('master.customers')"
            :show-area="true"
            :show-cluster="true"
            :show-tap="true"
            :show-type="true"
            type-mode="customer"
            :show-search="true"
            search-placeholder=""
            :areas="$areas"
            :clusters="$clusters"
            :taps="$taps"
            :customers-count="$customers->count()"
            :total-count="$customers->total()" />

        <!-- ============================================== -->
        <!-- TABEL MASTER TOKO                              -->
        <!-- ============================================== -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <span class="text-xs font-extrabold text-slate-800">Daftar Toko & Outlet Pelanggan</span>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] bg-slate-200 text-slate-700 font-mono font-bold">{{ number_format($customers->total()) }} Toko</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Kode Toko</th>
                            <th class="py-3 px-4">Nama Toko & PIC</th>
                            <th class="py-3 px-4">ID DigiPOS & No. RS</th>
                            <th class="py-3 px-4">Sales & Jadwal PJP</th>
                            <th class="py-3 px-4">Wilayah TAP / Area</th>
                            <th class="py-3 px-4 text-center">Tipe Outlet</th>
                            <th class="py-3 px-4 text-right">Limit Kredit</th>
                            <th class="py-3 px-4 text-right">Sisa Piutang (AR)</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($customers as $c)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-3.5 px-4 font-mono font-bold text-amber-900">
                                    {{ $c->code }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-extrabold text-slate-900 block text-xs">{{ $c->name }}</span>
                                    @if($c->customer_pic)
                                        <span class="text-[11px] text-slate-600 block">
                                            <strong class="text-slate-700">PIC:</strong> {{ $c->customer_pic }}
                                            @if($c->customer_ktp)
                                                <span class="text-slate-400 font-mono text-[10px]">({{ $c->customer_ktp }})</span>
                                            @endif
                                        </span>
                                    @endif
                                    @if($c->npwp)
                                        <span class="text-[10px] text-slate-500 font-mono block mt-0.5">
                                            <strong class="text-slate-600">NPWP:</strong> {{ $c->npwp }}
                                        </span>
                                    @endif
                                    <span class="text-[11px] text-slate-400 block truncate max-w-xs mt-0.5">{{ $c->address }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-xs">
                                    @if($c->is_digipos)
                                        <div class="flex items-center gap-1.5 text-slate-900 font-bold font-mono">
                                            <i data-lucide="smartphone" class="w-3.5 h-3.5 text-rose-500 shrink-0"></i>
                                            <span>{{ $c->digipos_id ?? '-' }}</span>
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-rose-50 text-rose-700 border border-rose-200">DigiPOS</span>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">Non-DigiPOS</span>
                                        </div>
                                    @endif
                                    <div class="mt-1 flex items-center gap-1.5 flex-wrap">
                                        <span class="font-mono text-[11px] font-bold text-slate-800 bg-slate-100 px-2 py-0.5 rounded border border-slate-200" title="Nomor RS Utama Toko">
                                            RS: {{ $c->primary_rs_number }}
                                        </span>
                                        @if(($c->total_rs_count ?? 0) > 1)
                                            <button type="button" 
                                                    @click="openRsModal({{ json_encode($c) }})"
                                                    class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 transition-all cursor-pointer"
                                                    title="Toko ini memiliki {{ $c->total_rs_count }} chip RS. Klik untuk kelola multi chip.">
                                                <i data-lucide="layers" class="w-3 h-3"></i>
                                                <span>+{{ $c->total_rs_count - 1 }} RS</span>
                                            </button>
                                        @else
                                            <button type="button" 
                                                    @click="openRsModal({{ json_encode($c) }})"
                                                    class="text-slate-400 hover:text-indigo-600 p-0.5 transition cursor-pointer"
                                                    title="Tambah / Kelola Nomor RS Toko">
                                                <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($c->sales_name)
                                        <span class="font-bold text-slate-900 block text-xs">{{ $c->sales_name }}</span>
                                    @else
                                        <span class="text-slate-400 italic text-[11px] block">Non-Sales PJP</span>
                                    @endif
                                    @if($c->hari_pjp)
                                        <span class="mt-0.5 inline-block px-2 py-0.5 rounded font-mono font-bold text-[10px] bg-amber-50 text-amber-700 border border-amber-200">
                                            PJP: {{ $c->hari_pjp }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-[10px]">Tanpa jadwal</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-semibold text-slate-800 block">{{ $c->tap_name }}</span>
                                    @if($c->kecamatan_name || $c->kota_name)
                                        <span class="text-[11px] text-slate-400 block truncate max-w-xs">
                                            {{ $c->kecamatan_name ? 'Kec. ' . $c->kecamatan_name : '' }}{{ $c->kecamatan_name && $c->kota_name ? ', ' : '' }}{{ $c->kota_name }}
                                        </span>
                                    @endif
                                    @if($c->latitude && $c->longitude)
                                        <a href="https://maps.google.com/?q={{ $c->latitude }},{{ $c->longitude }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-mono text-indigo-600 hover:underline mt-0.5">
                                             <i data-lucide="map-pin" class="w-3 h-3"></i>
                                            <span>{{ number_format($c->latitude, 5) }}, {{ number_format($c->longitude, 5) }}</span>
                                        </a>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold 
                                        {{ $c->type === 'GROSIR' ? 'bg-purple-100 text-purple-700 border border-purple-200' : 'bg-blue-100 text-blue-700 border border-blue-200' }}">
                                        {{ $c->type }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-800">
                                    Rp {{ number_format($c->credit_limit, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    @if($c->unpaid_ar_amount > 0)
                                        <span class="font-mono font-extrabold text-rose-600 block">
                                            Rp {{ number_format($c->unpaid_ar_amount, 0, ',', '.') }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 block">{{ $c->unpaid_ar_count }} Faktur</span>
                                    @else
                                        <span class="text-emerald-700 font-bold text-[11px]">Lunas (Rp 0)</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <x-master-status-toggle entity="customers" :id="$c->id" :name="$c->name" :active="$c->status === 'ACTIVE'" />
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <button type="button" 
                                            @click="openEditModal({{ json_encode($c) }})" 
                                            class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold transition-all">
                                        Edit
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-10 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-1.5">
                                        <i data-lucide="store" class="w-7 h-7 text-slate-300"></i>
                                        <span class="text-xs font-semibold">Tidak ada data outlet yang sesuai dengan filter atau pencarian.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Server-Side Pagination Bar -->
            <div class="p-4 border-t border-slate-200 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-500">
                <div class="flex items-center gap-3">
                    <div>
                        Menampilkan <strong class="text-slate-800">{{ number_format($customers->firstItem() ?? 0) }}</strong> - <strong class="text-slate-800">{{ number_format($customers->lastItem() ?? 0) }}</strong> dari <strong class="text-slate-800">{{ number_format($customers->total()) }}</strong> data Toko
                    </div>
                    <span class="text-slate-300">|</span>
                    <label class="flex items-center gap-1.5 text-xs">
                        <span>Baris:</span>
                        <select x-model="perPage" @change="$nextTick(() => $refs.filterForm.submit())" class="border border-slate-200 rounded-lg px-2 py-1 bg-white text-xs font-semibold text-slate-700 focus:outline-none focus:ring-1 focus:ring-amber-500 cursor-pointer">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </label>
                </div>

                @if($customers->hasPages())
                    @php
                        $current = $customers->currentPage();
                        $last = $customers->lastPage();
                        $pages = [];
                        if ($last <= 7) {
                            $pages = range(1, $last);
                        } elseif ($current <= 4) {
                            $pages = [1, 2, 3, 4, 5, '...', $last];
                        } elseif ($current >= $last - 3) {
                            $pages = [1, '...', $last - 4, $last - 3, $last - 2, $last - 1, $last];
                        } else {
                            $pages = [1, '...', $current - 1, $current, $current + 1, '...', $last];
                        }
                    @endphp
                    <div class="flex items-center gap-1">
                        @if($customers->onFirstPage())
                            <span class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-400 opacity-50 cursor-not-allowed text-xs font-bold flex items-center gap-1">
                                <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                                <span>Prev</span>
                            </span>
                        @else
                            <a href="{{ $customers->previousPageUrl() }}" class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 text-xs font-bold transition-all flex items-center gap-1">
                                <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                                <span>Prev</span>
                            </a>
                        @endif

                        @foreach($pages as $p)
                            @if($p === '...')
                                <span class="min-w-[30px] h-7 px-1.5 flex items-center justify-center text-xs text-slate-400 cursor-default">...</span>
                            @elseif($p === $current)
                                <span class="min-w-[30px] h-7 px-1.5 rounded-lg border border-amber-600 bg-amber-600 text-white font-extrabold text-xs flex items-center justify-center shadow-xs">{{ $p }}</span>
                            @else
                                <a href="{{ $customers->url($p) }}" class="min-w-[30px] h-7 px-1.5 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-100 font-semibold text-xs flex items-center justify-center transition-all">{{ $p }}</a>
                            @endif
                        @endforeach

                        @if($customers->hasMorePages())
                            <a href="{{ $customers->nextPageUrl() }}" class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 text-xs font-bold transition-all flex items-center gap-1">
                                <span>Next</span>
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </a>
                        @else
                            <span class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-400 opacity-50 cursor-not-allowed text-xs font-bold flex items-center gap-1">
                                <span>Next</span>
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </span>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- ============================================== -->
        <!-- MODAL KELOLA MULTI NOMOR RS CUSTOMER           -->
        <!-- ============================================== -->
        <template x-if="rsModalOpen && rsCustomer">
            <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                <div class="bg-white rounded-2xl border border-slate-200 max-w-2xl w-full p-6 shadow-2xl max-h-[90vh] overflow-y-auto" @click.outside="rsModalOpen = false">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-rose-500 to-red-600 text-white flex items-center justify-center font-bold shadow-sm">
                                <i data-lucide="smartphone" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-extrabold text-slate-900 text-sm" x-text="rsCustomer.name"></h3>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-100 text-amber-800" x-text="rsCustomer.code"></span>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    DigiPOS ID: <span class="font-mono font-bold text-slate-700" x-text="rsCustomer.digipos_id || '-'"></span> • Manajemen Multi Nomor Chip RS
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="rsModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold p-1">
                            &times;
                        </button>
                    </div>

                    <!-- List Nomor RS Saat Ini -->
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-2.5">
                            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                                <i data-lucide="list" class="w-3.5 h-3.5 text-indigo-500"></i>
                                <span>Daftar Nomor Chip RS Terdaftar</span>
                            </h4>
                            <span class="text-[11px] font-mono text-slate-400" x-text="rsList.length + ' Chip RS Terdata'"></span>
                        </div>

                        <!-- Loading State -->
                        <div x-show="rsLoading" class="py-6 text-center text-xs text-slate-400 flex items-center justify-center gap-2">
                            <i data-lucide="loader-2" class="w-4 h-4 animate-spin text-indigo-500"></i>
                            <span>Memuat data chip RS...</span>
                        </div>

                        <!-- Table Chip RS -->
                        <div x-show="!rsLoading" class="border border-slate-200 rounded-xl overflow-hidden bg-slate-50/30">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-100 text-slate-600 font-bold border-b border-slate-200 text-[10px] uppercase">
                                    <tr>
                                        <th class="py-2.5 px-3">Nomor RS / Chip</th>
                                        <th class="py-2.5 px-3">Nama / Label Chip</th>
                                        <th class="py-2.5 px-3 text-center">Tipe Transaksi</th>
                                        <th class="py-2.5 px-3 text-center">Utama (Primary)</th>
                                        <th class="py-2.5 px-3 text-center">Status</th>
                                        <th class="py-2.5 px-3 text-center w-16">Hapus</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200/70 font-medium">
                                    <template x-for="(rs, idx) in rsList" :key="rs.id">
                                        <tr class="hover:bg-white transition-colors">
                                            <td class="py-3 px-3 font-mono font-extrabold text-slate-900">
                                                <span x-text="rs.rs_number"></span>
                                            </td>
                                            <td class="py-3 px-3 text-slate-700">
                                                <span class="font-bold block" x-text="rs.rs_name || 'Chip #' + (idx+1)"></span>
                                                <span class="text-[10px] text-slate-400" x-text="rs.notes || '-'"></span>
                                            </td>
                                            <td class="py-3 px-3 text-center">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-200/70 text-slate-700" x-text="rs.chip_type"></span>
                                            </td>
                                            <td class="py-3 px-3 text-center">
                                                <template x-if="rs.is_primary">
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 inline-flex items-center gap-1">
                                                        <i data-lucide="star" class="w-3 h-3 text-amber-600 fill-amber-500"></i>
                                                        <span>RS UTAMA</span>
                                                    </span>
                                                </template>
                                                <template x-if="!rs.is_primary">
                                                    <button type="button" 
                                                            @click="setPrimaryRs(rs.id)"
                                                            class="px-2 py-0.5 rounded text-[10px] font-bold text-slate-600 hover:text-amber-700 hover:bg-amber-50 border border-slate-200 hover:border-amber-300 transition cursor-pointer">
                                                        Jadikan Utama
                                                    </button>
                                                </template>
                                            </td>
                                            <td class="py-3 px-3 text-center">
                                                <button type="button" 
                                                        @click="window.toggleMasterStatus('customer_rs', rs.id, rs.rs_number, $el)"
                                                        data-no-loading
                                                        class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                                        :class="rs.status === 'ACTIVE' ? 'bg-emerald-500' : 'bg-slate-300'"
                                                        role="switch" 
                                                        :aria-checked="rs.status === 'ACTIVE' ? 'true' : 'false'"
                                                        :title="rs.status === 'ACTIVE' ? 'Klik untuk nonaktifkan' : 'Klik untuk aktifkan'">
                                                    <span data-toggle-knob 
                                                          class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-xs ring-0 transition duration-200 ease-in-out"
                                                          :class="rs.status === 'ACTIVE' ? 'translate-x-4' : 'translate-x-0'"></span>
                                                </button>
                                            </td>
                                            <td class="py-3 px-3 text-center">
                                                <button type="button" 
                                                        @click="deleteRs(rs.id)"
                                                        :disabled="rsList.length <= 1"
                                                        class="p-1 rounded text-rose-500 hover:text-rose-700 hover:bg-rose-50 disabled:opacity-20 disabled:hover:bg-transparent transition cursor-pointer"
                                                        title="Hapus nomor RS ini">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-if="rsList.length === 0">
                                        <tr>
                                            <td colspan="6" class="py-5 text-center text-slate-400 italic">
                                                Belum ada nomor RS terdaftar untuk outlet ini.
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Form Tambah Nomor RS Baru -->
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80">
                        <h4 class="text-xs font-bold text-slate-900 mb-3 flex items-center gap-1.5">
                            <i data-lucide="plus-circle" class="w-4 h-4 text-emerald-600"></i>
                            <span>Tambah Nomor Chip RS Baru ke Outlet Ini</span>
                        </h4>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Nomor Chip RS (MSISDN) <span class="text-rose-500">*</span></label>
                                <input type="text" x-model="newRs.rs_number" placeholder="" class="w-full text-xs font-mono font-bold bg-white border border-slate-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500/20">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Label / Nama Chip</label>
                                <input type="text" x-model="newRs.rs_name" placeholder="" class="w-full text-xs font-semibold bg-white border border-slate-200 rounded-xl px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Peruntukan Transaksi</label>
                                <select x-model="newRs.chip_type" class="w-full text-xs font-bold bg-white border border-slate-200 rounded-xl px-3 py-2">
                                    <option value="ALL">ALL (Semua Transaksi)</option>
                                    <option value="DATA">DATA (Paket Data)</option>
                                    <option value="PERDANA">PERDANA (Starter Pack SA)</option>
                                    <option value="REGULER">REGULER (Pulsa Biasa)</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-2">
                            <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" x-model="newRs.is_primary" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-xs text-slate-700 font-semibold">Jadikan sebagai <strong>Nomor RS Utama</strong> toko</span>
                            </label>

                            <button type="button" 
                                    @click="submitNewRs()" 
                                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs flex items-center justify-center gap-1.5 shadow-sm transition-all cursor-pointer">
                                <i data-lucide="check" class="w-4 h-4"></i>
                                <span>Tambahkan Chip RS</span>
                            </button>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                        <span class="flex items-center gap-1">
                            <i data-lucide="info" class="w-3.5 h-3.5 text-slate-400"></i>
                            Perubahan nomor RS utama akan otomatis tersinkron ke profil outlet & DigiPOS.
                        </span>
                        <button type="button" @click="rsModalOpen = false; window.location.reload();" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">
                            Selesai & Tutup
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    @push('scripts')
    <script>
        function customerMasterApp() {
            return {
                createOpen: false,
                isDigipos: true,
                editModalOpen: false,
                editData: null,

                // Cascading Filter & Pagination Server-Side (Sinkron dengan x-filter-territory)
                allClusters: @json($clusters),
                allTaps: @json($taps),
                selectedArea: '{{ request('area_id', '') }}',
                selectedCluster: '{{ request('cluster_id', '') }}',
                selectedTap: '{{ request('tap_id', '') }}',
                selectedType: '{{ request('type', 'ALL') ?: 'ALL' }}',
                searchQuery: @json(request('search', '')),
                perPage: '{{ $perPage }}',

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
                    this.$nextTick(() => this.$refs.filterForm.submit());
                },

                onClusterChange() {
                    this.selectedTap = '';
                    this.$nextTick(() => this.$refs.filterForm.submit());
                },

                onTapChange() {
                    this.$nextTick(() => this.$refs.filterForm.submit());
                },

                onTypeChange() {
                    this.$nextTick(() => this.$refs.filterForm.submit());
                },

                onSearchClear() {
                    this.$nextTick(() => this.$refs.filterForm.submit());
                },

                hasActiveFilter() {
                    return Boolean(this.selectedArea || this.selectedCluster || this.selectedTap || (this.selectedType && this.selectedType !== 'ALL') || this.searchQuery);
                },

                resetFilter() {
                    window.location.href = '{{ route('master.customers') }}' + '?per_page=' + encodeURIComponent(this.perPage);
                },

                // Modal Multi Nomor RS
                rsModalOpen: false,
                rsCustomer: null,
                rsList: [],
                rsLoading: false,
                newRs: {
                    rs_number: '',
                    rs_name: '',
                    chip_type: 'ALL',
                    is_primary: false,
                    notes: ''
                },

                openEditModal(customer) {
                    this.editData = Object.assign({}, customer);
                    this.editData.is_digipos = customer.is_digipos === 1 || customer.is_digipos === '1' || customer.is_digipos === true;
                    this.editData.is_consignment = customer.is_consignment === 1 || customer.is_consignment === '1' || customer.is_consignment === true;
                    this.editModalOpen = true;
                },

                async openRsModal(customer) {
                    this.rsCustomer = customer;
                    this.rsModalOpen = true;
                    this.resetNewRs();
                    await this.loadRsList();
                },

                resetNewRs() {
                    this.newRs = {
                        rs_number: '',
                        rs_name: '',
                        chip_type: 'ALL',
                        is_primary: false,
                        notes: ''
                    };
                },

                async loadRsList() {
                    if (!this.rsCustomer) return;
                    this.rsLoading = true;
                    try {
                        const res = await fetch(`/master/customers/${this.rsCustomer.id}/rs-numbers`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.rsList = data.rs_numbers;
                        }
                    } catch (e) {
                        console.error('Failed to load RS numbers:', e);
                    } finally {
                        this.rsLoading = false;
                        this.$nextTick(() => { lucide.createIcons(); });
                    }
                },

                async submitNewRs() {
                    if (!this.newRs.rs_number) {
                        alert('Nomor RS wajib diisi!');
                        return;
                    }
                    try {
                        const res = await fetch('{{ route('master.customers.rs-numbers.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                customer_id: this.rsCustomer.id,
                                ...this.newRs
                            })
                        });
                        const data = await res.json();
                        if (data.success) {
                            window.showToast(data.message, 'success', 'Chip RS Ditambahkan');
                            this.resetNewRs();
                            await this.loadRsList();
                        } else {
                            window.showToast(data.message || 'Gagal menyimpan chip RS', 'error', 'Perhatian');
                        }
                    } catch (e) {
                        window.showToast(e.message || 'Terjadi kesalahan sistem', 'error', 'Error');
                    }
                },

                async setPrimaryRs(id) {
                    try {
                        const res = await fetch(`/master/customers/rs-numbers/set-primary/${id}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            window.showToast(data.message, 'success', 'RS Utama Diperbarui');
                            await this.loadRsList();
                        }
                    } catch (e) {
                        window.showToast('Gagal mengubah nomor RS utama', 'error', 'Error');
                    }
                },

                async deleteRs(id) {
                    if (!confirm('Hapus nomor RS ini dari outlet?')) return;
                    try {
                        const res = await fetch(`/master/customers/rs-numbers/delete/${id}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            window.showToast(data.message, 'success', 'Nomor RS Dihapus');
                            await this.loadRsList();
                        } else {
                            window.showToast(data.message, 'error', 'Perhatian');
                        }
                    } catch (e) {
                        window.showToast('Gagal menghapus nomor RS', 'error', 'Error');
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
