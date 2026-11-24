<x-app-layout>
    <x-slot name="title">Master Rencana Kunjungan (PJP)</x-slot>
    <x-slot name="headerTitle">Rencana Kunjungan Sales (PJP)</x-slot>
    <x-slot name="badge">Master Data</x-slot>

    <div x-data="pjpMasterApp()">

        <!-- Header Actions & Summary -->
        <div class="mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="px-3 py-1.5 rounded-xl bg-slate-900 text-white flex items-center gap-2 text-xs font-bold shadow-xs">
                    <i data-lucide="calendar" class="w-4 h-4 text-amber-400"></i>
                    <span>{{ number_format($totalSchedules) }} Jadwal</span>
                </div>
                <div class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-700 flex items-center gap-2 text-xs font-bold shadow-xs">
                    <i data-lucide="store" class="w-4 h-4 text-rose-500"></i>
                    <span>{{ number_format($totalCoveredOutlets) }} Outlet</span>
                </div>
                <div class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-700 flex items-center gap-2 text-xs font-bold shadow-xs">
                    <i data-lucide="users" class="w-4 h-4 text-emerald-600"></i>
                    <span>{{ number_format($totalActiveSales) }} Sales</span>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('master.pjp.template') }}" class="px-3 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-xs transition">
                    <i data-lucide="download" class="w-4 h-4 text-slate-500"></i>
                    <span>Unduh Template</span>
                </a>

                <button type="button" @click="openUploadModal = true; resetUploadState()" class="px-3.5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-xs transition">
                    <i data-lucide="file-up" class="w-4 h-4 text-emerald-200"></i>
                    <span>Upload Excel</span>
                </button>

                <button type="button" @click="createOpen = !createOpen" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-xs transition">
                    <i data-lucide="plus" class="w-4 h-4 text-amber-400"></i>
                    <span x-text="createOpen ? 'Tutup Form' : 'Tambah Manual'"></span>
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between">
                <span class="flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                    {{ session('success') }}
                </span>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 font-bold">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center justify-between">
                <span class="flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                    {{ session('error') }}
                </span>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800 font-bold">&times;</button>
            </div>
        @endif

        <!-- Form Tambah Manual Inline -->
        <div x-show="createOpen" x-cloak x-transition class="mb-5 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <span class="font-extrabold text-slate-800 text-xs uppercase tracking-wider">Tambah Jadwal Kunjungan</span>
                <button type="button" @click="createOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('master.pjp.store') }}">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Sales</label>
                        <select name="sales_id" required class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none">
                            <option value="">-- Pilih Sales --</option>
                            @foreach($salesList as $s)
                                <option value="{{ $s->id }}">[{{ $s->code }}] {{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Outlet</label>
                        <select name="customer_id" required class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none">
                            <option value="">-- Pilih Outlet --</option>
                            @foreach($customersList as $cust)
                                <option value="{{ $cust->id }}">[{{ $cust->code }}] {{ $cust->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Hari Kunjungan</label>
                        <select name="day" required class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none">
                            @foreach($days as $day)
                                <option value="{{ $day }}">{{ $day }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan</label>
                    <input type="text" name="notes" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="createOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs">
                        Simpan
                    </button>
                </div>
            </form>
        </div>

        <!-- Filter Bar -->
        <div class="mb-5 bg-white rounded-2xl border border-slate-200 p-4 shadow-2xs">
            <form method="GET" action="{{ route('master.pjp') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Filter Sales</label>
                    <select name="sales_id" onchange="this.form.submit()" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none">
                        <option value="ALL">Semua Sales</option>
                        @foreach($salesList as $s)
                            <option value="{{ $s->id }}" {{ request('sales_id') == $s->id ? 'selected' : '' }}>[{{ $s->code }}] {{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Filter Hari</label>
                    <select name="day" onchange="this.form.submit()" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none">
                        <option value="ALL">Semua Hari</option>
                        @foreach($days as $day)
                            <option value="{{ $day }}" {{ request('day') == $day ? 'selected' : '' }}>{{ $day }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Filter Wilayah TAP</label>
                    <select name="tap_id" onchange="this.form.submit()" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none">
                        <option value="ALL">Semua TAP</option>
                        @foreach($taps as $tap)
                            <option value="{{ $tap->id }}" {{ request('tap_id') == $tap->id ? 'selected' : '' }}>{{ $tap->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Pencarian</label>
                    <div class="flex items-center gap-2">
                        <input type="text" name="search" value="{{ request('search') }}" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none">
                        <button type="submit" class="px-4 py-2 bg-slate-900 text-white font-bold rounded-xl text-xs shrink-0">
                            Cari
                        </button>
                        @if(request()->hasAny(['sales_id', 'day', 'tap_id', 'search']))
                            <a href="{{ route('master.pjp') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-xs shrink-0">
                                Reset
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabel Daftar PJP -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <span class="text-xs font-extrabold text-slate-800">Daftar Jadwal Kunjungan Sales</span>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] bg-slate-200 text-slate-700 font-mono font-bold">{{ number_format($pjps->total()) }} Baris</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Sales Pembina</th>
                            <th class="py-3 px-4">Hari Kunjungan</th>
                            <th class="py-3 px-4">Outlet / Toko</th>
                            <th class="py-3 px-4">Wilayah TAP</th>
                            <th class="py-3 px-4">Catatan</th>
                            <th class="py-3 px-4 text-center w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($pjps as $pjp)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-3 px-4">
                                    <span class="font-extrabold text-slate-900 block text-xs">{{ $pjp->sales_name }}</span>
                                    <span class="text-[10px] font-mono text-slate-500 block">{{ $pjp->sales_code }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    @php
                                        $badgeColor = match($pjp->day) {
                                            'SENIN' => 'bg-blue-100 text-blue-800 border-blue-200',
                                            'SELASA' => 'bg-amber-100 text-amber-800 border-amber-200',
                                            'RABU' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                            'KAMIS' => 'bg-purple-100 text-purple-800 border-purple-200',
                                            'JUMAT' => 'bg-rose-100 text-rose-800 border-rose-200',
                                            'SABTU' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                            default => 'bg-slate-100 text-slate-800 border-slate-200'
                                        };
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-md text-[11px] font-extrabold border {{ $badgeColor }}">
                                        {{ $pjp->day }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-extrabold text-slate-900 text-xs">{{ $pjp->customer_name }}</span>
                                        <span class="text-[10px] font-mono text-amber-800 bg-amber-50 px-1.5 py-0.2 rounded border border-amber-200">{{ $pjp->customer_code }}</span>
                                    </div>
                                    <span class="text-[11px] text-slate-400 block truncate max-w-sm">{{ $pjp->customer_address }}</span>
                                </td>
                                <td class="py-3 px-4 text-slate-700">
                                    {{ $pjp->tap_name ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-slate-500 text-[11px]">
                                    {{ $pjp->notes ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button type="button" @click="openEditModal({{ json_encode($pjp) }})" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold">
                                            Edit
                                        </button>
                                        <form method="POST" action="{{ route('master.pjp.delete', $pjp->id) }}" onsubmit="return confirm('Hapus jadwal ini?');" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 text-[11px] font-bold">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-slate-400">
                                    <span>Tidak ada jadwal kunjungan yang sesuai dengan filter.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-200 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-500">
                <div>
                    Menampilkan <strong class="text-slate-800">{{ number_format($pjps->firstItem() ?? 0) }}</strong> - <strong class="text-slate-800">{{ number_format($pjps->lastItem() ?? 0) }}</strong> dari <strong class="text-slate-800">{{ number_format($pjps->total()) }}</strong> jadwal
                </div>
                <div>
                    {{ $pjps->links() }}
                </div>
            </div>
        </div>

        <!-- Modal Edit Jadwal -->
        <template x-if="editModalOpen && editData">
            <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                <div class="bg-white rounded-2xl border border-slate-200 max-w-md w-full p-6 shadow-2xl" @click.outside="editModalOpen = false">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                        <span class="font-extrabold text-slate-900 text-xs uppercase tracking-wider">Edit Jadwal Kunjungan</span>
                        <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
                    </div>

                    <form :action="'/master/pjp/update/' + editData.id" method="POST">
                        @csrf
                        <div class="space-y-3 mb-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Sales</label>
                                <input type="text" :value="'[' + editData.sales_code + '] ' + editData.sales_name" disabled class="w-full text-xs font-bold bg-slate-100 border border-slate-200 rounded-xl px-3 py-2 text-slate-500">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Outlet</label>
                                <input type="text" :value="'[' + editData.customer_code + '] ' + editData.customer_name" disabled class="w-full text-xs font-bold bg-slate-100 border border-slate-200 rounded-xl px-3 py-2 text-slate-500">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Hari Kunjungan</label>
                                <select name="day" x-model="editData.day" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none">
                                    @foreach($days as $day)
                                        <option value="{{ $day }}">{{ $day }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Catatan</label>
                                <input type="text" name="notes" x-model="editData.notes" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none">
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                            <button type="button" @click="editModalOpen = false" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl text-xs">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 bg-slate-900 text-white font-bold rounded-xl text-xs">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- Modal Upload Excel dengan Preview 2-Stage -->
        <template x-if="openUploadModal">
            <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                <div class="bg-white rounded-2xl border border-slate-200 max-w-4xl w-full p-6 shadow-2xl max-h-[90vh] overflow-y-auto" @click.outside="openUploadModal = false">
                    
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                        <div>
                            <span class="font-extrabold text-slate-900 text-sm">Upload & Pratinjau Mapping PJP</span>
                            <span x-show="uploadStage === 'preview'" class="ml-2 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                Mode Pratinjau
                            </span>
                        </div>
                        <button type="button" @click="openUploadModal = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
                    </div>

                    <!-- STAGE 1: Pilih File -->
                    <div x-show="uploadStage === 'select'">
                        <div class="mb-4">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih File Excel (.xlsx) atau CSV (.csv)</label>
                            <input type="file" x-ref="fileInput" @change="onFileSelected($event)" accept=".xlsx,.csv,.txt" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-slate-800 border border-slate-200 rounded-xl p-2 bg-slate-50">
                        </div>

                        <div x-show="errorMessage" class="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold flex items-center gap-2">
                            <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                            <span x-text="errorMessage"></span>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                            <button type="button" @click="openUploadModal = false" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl text-xs">
                                Batal
                            </button>
                            <button type="button" @click="submitPreview()" :disabled="!selectedFile || isProcessing" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 disabled:opacity-50 text-white font-bold rounded-xl text-xs flex items-center gap-1.5">
                                <span x-show="!isProcessing">Pratinjau Data</span>
                                <span x-show="isProcessing">Membaca File...</span>
                            </button>
                        </div>
                    </div>

                    <!-- STAGE 2: Pratinjau Data Sebelum Masuk DB -->
                    <div x-show="uploadStage === 'preview'">
                        <!-- Summary Cards -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Total Baris File</span>
                                <span class="text-base font-extrabold text-slate-900 font-mono" x-text="previewData ? previewData.total_rows : 0"></span>
                            </div>
                            <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200">
                                <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider block">Baris Valid (Siap Simpan)</span>
                                <span class="text-base font-extrabold text-emerald-800 font-mono" x-text="previewData ? previewData.valid_count : 0"></span>
                            </div>
                            <div class="p-3 rounded-xl bg-rose-50 border border-rose-200">
                                <span class="text-[10px] font-bold text-rose-700 uppercase tracking-wider block">Baris Tidak Valid (Dilewati)</span>
                                <span class="text-base font-extrabold text-rose-800 font-mono" x-text="previewData ? previewData.invalid_count : 0"></span>
                            </div>
                        </div>

                        <!-- Affected Sales List -->
                        <div class="mb-4 p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900">
                            <span class="text-xs font-bold block mb-1">Sales yang Terpengaruh:</span>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="s in (previewData ? previewData.affected_sales : [])" :key="s.id">
                                    <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-white text-amber-900 border border-amber-300">
                                        <span x-text="s.name"></span> (<span x-text="s.code"></span>): <strong x-text="s.total_outlets"></strong> outlet
                                    </span>
                                </template>
                            </div>
                        </div>

                        <!-- Preview Table -->
                        <div class="mb-4 border border-slate-200 rounded-xl overflow-hidden max-h-72 overflow-y-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-100 text-slate-700 font-bold sticky top-0">
                                    <tr>
                                        <th class="py-2 px-3 text-center">Baris</th>
                                        <th class="py-2 px-3">Sales</th>
                                        <th class="py-2 px-3">Outlet</th>
                                        <th class="py-2 px-3 text-center">Hari</th>
                                        <th class="py-2 px-3 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <template x-for="r in (previewData ? previewData.rows : [])" :key="r.row_num">
                                        <tr :class="!r.is_valid ? 'bg-rose-50/60' : 'hover:bg-slate-50'">
                                            <td class="py-2 px-3 text-center font-mono text-slate-500" x-text="r.row_num"></td>
                                            <td class="py-2 px-3">
                                                <span class="font-bold text-slate-800 block" x-text="r.sales_name"></span>
                                                <span class="text-[10px] font-mono text-slate-500" x-text="r.sales_code"></span>
                                            </td>
                                            <td class="py-2 px-3">
                                                <span class="font-bold text-slate-800 block" x-text="r.customer_name"></span>
                                                <span class="text-[10px] font-mono text-slate-500" x-text="r.customer_code"></span>
                                            </td>
                                            <td class="py-2 px-3 text-center font-bold text-slate-700" x-text="r.day"></td>
                                            <td class="py-2 px-3 text-center">
                                                <span x-show="r.is_valid" class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                    Valid
                                                </span>
                                                <span x-show="!r.is_valid" :title="r.error_msg" class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200 cursor-help" x-text="r.error_msg">
                                                </span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                            <button type="button" @click="resetUploadState()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">
                                Ganti File
                            </button>

                            <div class="flex items-center gap-2">
                                <button type="button" @click="openUploadModal = false" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl text-xs">
                                    Batal
                                </button>
                                <button type="button" @click="submitConfirm()" :disabled="!previewData || previewData.valid_count === 0 || isProcessing" class="px-5 py-2 bg-emerald-700 hover:bg-emerald-800 disabled:opacity-50 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-sm">
                                    <span x-show="!isProcessing">Konfirmasi & Simpan ke Database</span>
                                    <span x-show="isProcessing">Menyimpan...</span>
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </template>

    </div>

    @push('scripts')
    <script>
        function pjpMasterApp() {
            return {
                createOpen: false,
                editModalOpen: false,
                editData: null,
                
                openUploadModal: false,
                uploadStage: 'select', // 'select' | 'preview'
                selectedFile: null,
                isProcessing: false,
                errorMessage: '',
                previewData: null,

                openEditModal(data) {
                    this.editData = Object.assign({}, data);
                    this.editModalOpen = true;
                },

                resetUploadState() {
                    this.uploadStage = 'select';
                    this.selectedFile = null;
                    this.isProcessing = false;
                    this.errorMessage = '';
                    this.previewData = null;
                    if (this.$refs.fileInput) {
                        this.$refs.fileInput.value = '';
                    }
                },

                onFileSelected(event) {
                    const files = event.target.files;
                    if (files && files.length > 0) {
                        this.selectedFile = files[0];
                        this.errorMessage = '';
                    }
                },

                async submitPreview() {
                    if (!this.selectedFile) {
                        this.errorMessage = 'Silakan pilih file terlebih dahulu.';
                        return;
                    }

                    this.isProcessing = true;
                    this.errorMessage = '';

                    const formData = new FormData();
                    formData.append('file', this.selectedFile);

                    try {
                        const res = await fetch('{{ route('master.pjp.preview-upload') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        const data = await res.json();
                        if (data.success) {
                            this.previewData = data;
                            this.uploadStage = 'preview';
                        } else {
                            this.errorMessage = data.message || 'Gagal memproses file.';
                        }
                    } catch (e) {
                        this.errorMessage = 'Terjadi kesalahan sistem saat membaca file: ' + e.message;
                    } finally {
                        this.isProcessing = false;
                        this.$nextTick(() => { lucide.createIcons(); });
                    }
                },

                async submitConfirm() {
                    if (!this.previewData || !this.previewData.preview_token) return;

                    this.isProcessing = true;
                    try {
                        const res = await fetch('{{ route('master.pjp.confirm-upload') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                preview_token: this.previewData.preview_token
                            })
                        });

                        const data = await res.json();
                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert(data.message || 'Gagal menyimpan data.');
                            this.isProcessing = false;
                        }
                    } catch (e) {
                        alert('Terjadi kesalahan sistem: ' + e.message);
                        this.isProcessing = false;
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
