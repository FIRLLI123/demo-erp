<x-app-layout>
    <x-slot name="title">Penerimaan Barang Fisik (Goods Received)</x-slot>
    <x-slot name="headerTitle">Goods Received (GR) Gudang</x-slot>
    <x-slot name="badge">Ref: {{ $po->doc_number }}</x-slot>

    <!-- Top Navigation Bar -->
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('procurement.index') }}" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-bold inline-flex items-center gap-1.5 transition-all shadow-sm">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Daftar PO</span>
        </a>

        <div class="flex items-center gap-2">
            <span class="text-xs font-mono font-bold text-slate-500">Target Dokumen:</span>
            <span class="px-2.5 py-1 rounded-lg bg-emerald-600 text-white font-mono text-xs font-extrabold shadow-sm">
                {{ $predictedGrNumber }}
            </span>
        </div>
    </div>

    <!-- Main GR Reception Form -->
    <div class="bg-white rounded-2xl border-2 border-emerald-500/30 shadow-lg overflow-hidden">
        
        <!-- Header Banner -->
        <div class="bg-slate-900 text-white p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-500 text-white">
                        GOODS RECEIVED
                    </span>
                    <span class="font-mono text-sm font-bold text-slate-300">Penerimaan dari {{ $po->doc_number }}</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-emerald-400 border border-slate-700">PT WAHANA PUTRA YUDHA</span>
                </div>
                <h3 class="text-lg font-extrabold text-white mt-1">Penerimaan Fisik Barang ke Gudang Utama TAP</h3>
            </div>

            <div class="flex flex-wrap items-center gap-2 text-xs">
                <div class="bg-slate-800 px-3 py-1.5 rounded-xl border border-slate-700">
                    <span class="text-slate-400 block text-[10px] font-bold">Supplier:</span>
                    <span class="font-bold text-white">{{ $po->supplier_name }}</span>
                </div>
                <div class="bg-slate-800 px-3 py-1.5 rounded-xl border border-slate-700">
                    <span class="text-slate-400 block text-[10px] font-bold">Gudang Alokasi:</span>
                    <span class="font-bold text-emerald-400">{{ $mainWh->name }}</span>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="m-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                <div class="flex items-center gap-2 font-bold mb-1.5">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                    <span>Terdapat data yang belum lengkap:</span>
                </div>
                <ul class="list-disc list-inside space-y-1 text-rose-700 pl-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('procurement.process-gr', $po->id) }}" enctype="multipart/form-data" class="p-6 md:p-8"
              x-data="{
                  doNumber: '{{ old('do_number', '') }}',
                  fileName: '',
                  fileSize: '',
                  hasPdf: false,
                  handleFile(e) {
                      const file = e.target.files[0];
                      if (file) {
                          if (!file.name.toLowerCase().endsWith('.pdf') && file.type !== 'application/pdf') {
                              alert('Mohon pilih berkas dengan format PDF (.pdf)!');
                              e.target.value = '';
                              this.hasPdf = false;
                              this.fileName = '';
                              this.fileSize = '';
                              return;
                          }
                          this.hasPdf = true;
                          this.fileName = file.name;
                          const sizeKb = file.size / 1024;
                          this.fileSize = sizeKb > 1024 ? (sizeKb / 1024).toFixed(2) + ' MB' : Math.round(sizeKb) + ' KB';
                      } else {
                          this.hasPdf = false;
                          this.fileName = '';
                          this.fileSize = '';
                      }
                  },
                  clearFile() {
                      this.$refs.fileInput.value = '';
                      this.hasPdf = false;
                      this.fileName = '';
                      this.fileSize = '';
                  }
              }"
              data-loading-title="Penerimaan Barang Fisik (GR)"
              data-loading-sub="Mendaftarkan dokumen GR, memverifikasi serial ICCID & menambah stok Gudang MAIN..."
              data-doctype="GR">
            @csrf

            <!-- Metadata & Dokumen Telkomsel Box -->
            <div class="mb-8 rounded-2xl border-2 border-emerald-500/20 bg-emerald-50/30 p-5 md:p-6">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-emerald-500/10">
                    <div class="flex items-center gap-2">
                        <span class="p-1.5 rounded-lg bg-emerald-600 text-white shadow-xs">
                            <i data-lucide="file-check-2" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h4 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Validasi Surat Jalan & Berkas Telkomsel</h4>
                            <p class="text-[11px] text-slate-500">Kedua field ini wajib diisi sebelum barang fisik dapat di-input ke gudang.</p>
                        </div>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 border border-rose-200">
                        * Wajib (Mandatory)
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- 1. Input Nomor DO -->
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1.5 flex items-center justify-between">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="hash" class="w-3.5 h-3.5 text-emerald-600"></i>
                                <span>Nomor DO / Surat Jalan Telkomsel</span>
                                <span class="text-rose-600 font-extrabold">*</span>
                            </span>
                            <span class="text-[10px] font-mono text-slate-400">Huruf/Angka</span>
                        </label>
                        <input type="text" 
                               name="do_number" 
                               x-model="doNumber" 
                               required 
                               placeholder="Contoh: DO-TSEL-202611-9812 / SJ/BGR/26/001" 
                               class="w-full text-xs font-mono font-bold bg-white border border-slate-200 rounded-xl px-3.5 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all shadow-xs">
                        <p class="text-[11px] text-slate-500 mt-1.5 flex items-center gap-1">
                            <i data-lucide="info" class="w-3 h-3 text-slate-400"></i>
                            <span>Nomor surat jalan resmi dari pihak Telkomsel saat mengantar barang.</span>
                        </p>
                    </div>

                    <!-- 2. Upload Berkas Fisik (PDF) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1.5 flex items-center justify-between">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="file-up" class="w-3.5 h-3.5 text-emerald-600"></i>
                                <span>Upload Bukti Berkas Penerimaan</span>
                                <span class="text-rose-600 font-extrabold">* (Wajib PDF)</span>
                            </span>
                            <span class="text-[10px] font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded border border-rose-200">Maks 10MB</span>
                        </label>

                        <!-- Hidden File Input -->
                        <input type="file" 
                               name="do_file" 
                               x-ref="fileInput" 
                               @change="handleFile($event)" 
                               accept=".pdf,application/pdf" 
                               required 
                               class="hidden">

                        <!-- Dropzone / Picker State -->
                        <div x-show="!hasPdf" 
                             @click="$refs.fileInput.click()" 
                             class="border-2 border-dashed border-slate-300 hover:border-emerald-500 bg-white hover:bg-emerald-50/40 rounded-xl p-3.5 text-center cursor-pointer transition-all group">
                            <div class="flex items-center justify-center gap-2 text-slate-600 group-hover:text-emerald-700">
                                <i data-lucide="upload-cloud" class="w-5 h-5 text-slate-400 group-hover:text-emerald-600 transition-colors"></i>
                                <span class="text-xs font-bold">Pilih Berkas PDF Surat Jalan</span>
                            </div>
                            <span class="text-[10px] text-slate-400 block mt-0.5">Klik di sini untuk upload dokumen scan/PDF dari Telkomsel</span>
                        </div>

                        <!-- File Selected Preview State -->
                        <div x-show="hasPdf" x-cloak class="bg-white border-2 border-emerald-500/40 rounded-xl p-3 flex items-center justify-between shadow-xs">
                            <div class="flex items-center gap-2.5 overflow-hidden">
                                <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center shrink-0 font-bold text-[10px]">
                                    PDF
                                </div>
                                <div class="truncate">
                                    <span class="text-xs font-bold text-slate-900 block truncate" x-text="fileName"></span>
                                    <span class="text-[10px] text-slate-500 font-mono" x-text="fileSize"></span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <button type="button" @click="$refs.fileInput.click()" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[10px] rounded-lg transition-all">
                                    Ganti
                                </button>
                                <button type="button" @click="clearFile()" class="p-1 text-slate-400 hover:text-rose-600 rounded-lg transition-all" title="Hapus">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Secondary Row: Tanggal & Catatan -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-4 pt-4 border-t border-emerald-500/10">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Penerimaan Fisik di Kantor TAP</label>
                        <input type="date" name="received_date" value="{{ date('Y-m-d') }}" required class="w-full text-xs font-semibold bg-white border border-slate-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Catatan Tambahan / Kondisi Fisik Paket</label>
                        <input type="text" name="notes" placeholder="Contoh: Diterima utuh tanpa cacat segel / box rapi" class="w-full text-xs font-semibold bg-white border border-slate-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    </div>
                </div>
            </div>

            <!-- Items Checklist & Serial Registration -->
            <div class="space-y-6 mb-8">
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="package-check" class="w-4 h-4 text-emerald-600"></i>
                    <span>Verifikasi Item Fisik Barang Diterima</span>
                </h4>

                @foreach($items as $idx => $item)
                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50/60" x-data="{ iccidMode: 'range' }">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 mb-4 border-b border-slate-200/80 gap-2">
                            <div>
                                <span class="font-mono text-[10px] text-slate-400 block">{{ $item->sku }}</span>
                                <h5 class="font-extrabold text-slate-900 text-sm">{{ $item->name ?? $item->product_name }}</h5>
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono
                                    {{ $item->product_type === 'SA' ? 'bg-rose-100 text-rose-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ $item->product_type }} &bull; {{ $item->tracking_method }}
                                </span>
                                <span class="text-xs font-bold bg-white px-2.5 py-1 rounded-lg border border-slate-200 font-mono text-slate-800">
                                    Qty PO: {{ number_format($item->quantity) }} pcs
                                </span>
                            </div>
                        </div>

                        <!-- If Quantity Based (Voucher / Modem) -->
                        @if($item->tracking_method !== 'ICCID')
                            <div class="flex items-center justify-between bg-white p-3.5 rounded-xl border border-slate-200 text-xs">
                                <span class="text-slate-600 font-semibold flex items-center gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                                    Kuantitas otomatis dialokasikan ke saldo persediaan Gudang MAIN:
                                </span>
                                <span class="font-extrabold font-mono text-emerald-700 text-sm">+{{ number_format($item->quantity) }} pcs</span>
                            </div>
                        @else
                            <!-- If ICCID Based (Kartu Perdana SA) -->
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-purple-900 flex items-center gap-1.5">
                                        <i data-lucide="scan-barcode" class="w-4 h-4 text-purple-600"></i>
                                        Registrasi {{ $item->quantity }} Nomor Seri ICCID:
                                    </span>

                                    <!-- Mode Toggle Tabs -->
                                    <div class="flex items-center gap-1 bg-white p-1 rounded-xl border border-slate-200 text-[11px] font-bold">
                                        <button type="button" @click="iccidMode = 'range'" :class="iccidMode === 'range' ? 'bg-purple-100 text-purple-800 shadow-xs' : 'text-slate-500 hover:text-slate-800'" class="px-2.5 py-1 rounded-lg transition-all">Rentang Urut</button>
                                        <button type="button" @click="iccidMode = 'upload'" :class="iccidMode === 'upload' ? 'bg-purple-100 text-purple-800 shadow-xs' : 'text-slate-500 hover:text-slate-800'" class="px-2.5 py-1 rounded-lg transition-all">Upload File CSV</button>
                                        <button type="button" @click="iccidMode = 'paste'" :class="iccidMode === 'paste' ? 'bg-purple-100 text-purple-800 shadow-xs' : 'text-slate-500 hover:text-slate-800'" class="px-2.5 py-1 rounded-lg transition-all">Paste Teks</button>
                                    </div>
                                </div>

                                <!-- Tab 1: Range Generator (Recommended for instant fast demos) -->
                                <div x-show="iccidMode === 'range'" class="bg-white p-4 rounded-xl border border-purple-200">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Nomor Seri Awal (16-20 Digit):</label>
                                    <input type="text" 
                                           name="iccids_{{ $item->product_id }}_range_start" 
                                           value="89621000{{ date('ymd') }}00001" 
                                           class="w-full text-xs font-mono font-bold bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-purple-900 focus:outline-none">
                                    <span class="text-[10px] text-slate-400 mt-1 block">Sistem otomatis generate urut sebanyak {{ $item->quantity }} unit mulai dari nomor di atas.</span>
                                </div>

                                <!-- Tab 2: Upload CSV File -->
                                <div x-show="iccidMode === 'upload'" x-cloak class="bg-white p-4 rounded-xl border border-purple-200">
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block text-[11px] font-bold text-slate-700">Pilih File CSV / Excel:</label>
                                        <a href="{{ route('procurement.download-iccid-template') }}" class="text-[10px] font-bold text-purple-700 hover:underline flex items-center gap-1">
                                            <i data-lucide="download" class="w-3 h-3"></i> Download Template CSV
                                        </a>
                                    </div>
                                    <input type="file" 
                                           name="iccids_{{ $item->product_id }}_file" 
                                           accept=".csv, .txt" 
                                           class="w-full text-xs text-slate-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer">
                                </div>

                                <!-- Tab 3: Paste Multi-line List -->
                                <div x-show="iccidMode === 'paste'" x-cloak class="bg-white p-4 rounded-xl border border-purple-200">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Paste Daftar Serial (1 baris 1 serial):</label>
                                    <textarea name="iccids_{{ $item->product_id }}_paste" 
                                              rows="3" 
                                              placeholder="8962100000000001&#10;8962100000000002..." 
                                              class="w-full text-xs font-mono bg-slate-50 border border-slate-200 rounded-lg p-2.5 focus:outline-none"></textarea>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Submit Action -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pt-4 border-t border-slate-100 gap-3">
                <a href="{{ route('procurement.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs text-center">
                    Batal
                </a>

                <div class="flex flex-wrap items-center justify-end gap-3">
                    <span x-show="!doNumber.trim() || !hasPdf" class="text-[11px] font-bold text-amber-600 flex items-center gap-1.5 bg-amber-50 px-3 py-1.5 rounded-lg border border-amber-200">
                        <i data-lucide="alert-triangle" class="w-3.5 h-3.5 shrink-0"></i>
                        <span>Wajib isi No. DO & upload berkas PDF Surat Jalan</span>
                    </span>

                    <button type="submit" 
                            :class="(!doNumber.trim() || !hasPdf) ? 'opacity-50 cursor-not-allowed bg-slate-400' : 'bg-emerald-600 hover:bg-emerald-700 shadow-md shadow-emerald-600/20 active:scale-95'"
                            :disabled="!doNumber.trim() || !hasPdf"
                            class="px-6 py-3 text-white font-extrabold rounded-xl text-xs flex items-center justify-center gap-2 transition-all">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                        <span>Konfirmasi Penerimaan & Terbitkan GR</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>

