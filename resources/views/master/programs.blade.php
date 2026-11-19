<x-app-layout>
    <x-slot name="title">Master Program Harga</x-slot>
    <x-slot name="headerTitle">Master Program Harga</x-slot>
    <x-slot name="badge">Promo & Event per Cluster</x-slot>

    <div x-data="programMasterApp()" class="pb-12">

        <!-- Header & Action Button -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Master Program Harga</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700 border border-indigo-200 uppercase">WEB & MOBILE READY</span>
                </h2>
            </div>

            <div class="flex items-center gap-2">
                <button type="button"
                        x-show="activeTab === 'programs'"
                        @click="openProgramModal()"
                        class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-xs transition-all cursor-pointer">
                    <i data-lucide="plus" class="w-4 h-4 text-emerald-400"></i>
                    <span>Tambah Program Baru</span>
                </button>

                <button type="button"
                        x-show="activeTab === 'items'" x-cloak
                        @click="openItemModal()"
                        :disabled="!selectedProgramId"
                        class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 disabled:opacity-40 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-xs transition-all cursor-pointer">
                    <i data-lucide="plus" class="w-4 h-4 text-emerald-400"></i>
                    <span>Tambah Item Program</span>
                </button>
            </div>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between shadow-2xs">
                <span class="flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                    {{ session('success') }}
                </span>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 cursor-pointer">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center justify-between shadow-2xs">
                <span class="flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                    {{ session('error') }}
                </span>
                <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800 cursor-pointer">&times;</button>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- ============================================== -->
        <!-- TAB NAVIGASI: DAFTAR PROGRAM | HARGA ITEM      -->
        <!-- ============================================== -->
        <div class="mb-5 border-b border-slate-200 flex flex-wrap items-center gap-1">
            <button type="button"
                    @click="activeTab = 'programs'"
                    :class="activeTab === 'programs' ? 'border-rose-600 text-rose-700 bg-rose-50/50' : 'border-transparent text-slate-500 hover:text-slate-800'"
                    class="px-4 py-2.5 text-xs font-extrabold border-b-2 rounded-t-xl transition-all flex items-center gap-2 cursor-pointer">
                <i data-lucide="sparkles" class="w-4 h-4"></i>
                <span>Daftar Program</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-mono bg-slate-200/80 text-slate-700">{{ count($programs) }}</span>
            </button>

            <button type="button"
                    @click="activeTab = 'items'"
                    :class="activeTab === 'items' ? 'border-rose-600 text-rose-700 bg-rose-50/50' : 'border-transparent text-slate-500 hover:text-slate-800'"
                    class="px-4 py-2.5 text-xs font-extrabold border-b-2 rounded-t-xl transition-all flex items-center gap-2 cursor-pointer">
                <i data-lucide="list-checks" class="w-4 h-4"></i>
                <span>Setting Harga Item Program</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-mono bg-slate-200/80 text-slate-700">{{ count($programItems) }}</span>
            </button>
        </div>

        <!-- ============================================== -->
        <!-- TAB 1: DAFTAR PROGRAM                          -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'programs'">
            <!-- Filter Wilayah & Search -->
            <div class="bg-slate-50/80 border border-slate-200/90 rounded-2xl p-3.5 mb-5 shadow-2xs">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-2.5 flex-1">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 flex items-center gap-1.5 mr-1">
                            <i data-lucide="filter" class="w-3.5 h-3.5 text-rose-600"></i>
                            <span>Filter:</span>
                        </span>

                        <!-- Area -->
                        <div class="relative inline-flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-cyan-600">
                                <i data-lucide="map" class="w-3.5 h-3.5"></i>
                            </div>
                            <select x-model="filterArea" @change="filterCluster = ''; programPage = 1" :class="filterArea ? 'border-rose-500 ring-2 ring-rose-500/10 text-rose-900 bg-rose-50/20' : 'border-slate-200 bg-white text-slate-800'" class="min-w-[165px] text-xs font-bold border rounded-xl pl-9 pr-8 py-2 focus:outline-none cursor-pointer appearance-none">
                                <option value="">-- Semua Area --</option>
                                @foreach($areas as $ar)
                                    <option value="{{ $ar->id }}">{{ $ar->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                            </div>
                        </div>

                        <!-- Cluster -->
                        <div class="relative inline-flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-amber-600">
                                <i data-lucide="grid" class="w-3.5 h-3.5"></i>
                            </div>
                            <select x-model="filterCluster" @change="programPage = 1" :class="filterCluster ? 'border-rose-500 ring-2 ring-rose-500/10 text-rose-900 bg-rose-50/20' : 'border-slate-200 bg-white text-slate-800'" class="min-w-[175px] text-xs font-bold border rounded-xl pl-9 pr-8 py-2 focus:outline-none cursor-pointer appearance-none">
                                <option value="">-- Semua Cluster --</option>
                                <template x-for="cls in filterClusterOptions" :key="cls.id">
                                    <option :value="String(cls.id)" x-text="cls.name"></option>
                                </template>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                            </div>
                        </div>

                        <x-search-input model="searchProgram" placeholder="Cari kode atau nama program..." width="w-full sm:w-64" accent="rose" />

                        <button type="button" x-show="filterArea || filterCluster || searchProgram" x-cloak @click="filterArea = ''; filterCluster = ''; searchProgram = ''; programPage = 1" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-200 hover:bg-rose-100 hover:text-rose-700 text-slate-600 text-xs font-bold cursor-pointer">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i> Reset
                        </button>
                    </div>

                    <div class="flex items-center gap-2 text-xs font-medium text-slate-500 shrink-0">
                        <span>Menampilkan:</span>
                        <span class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 font-bold text-slate-800 shadow-2xs">
                            <strong class="text-rose-600" x-text="filteredPrograms.length"></strong>
                            <span class="text-slate-400 font-normal"> / <span x-text="allPrograms.length"></span> data</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Table Daftar Program -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">Kode & Nama Program</th>
                                <th class="py-3 px-4">Cakupan Wilayah</th>
                                <th class="py-3 px-4">Periode Berlaku</th>
                                <th class="py-3 px-4 text-center">Jumlah Item</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-center w-44">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($programs as $prg)
                                <tr x-show="paginatedProgramIds.includes({{ $prg->id }})" class="hover:bg-slate-50/70">
                                    <td class="py-3.5 px-4">
                                        <span class="px-1.5 py-0.5 rounded font-mono font-bold text-[10px] bg-indigo-50 text-indigo-700 border border-indigo-200">{{ $prg->code }}</span>
                                        <span class="font-extrabold text-slate-900 block text-xs mt-1">{{ $prg->name }}</span>
                                        @if($prg->description)
                                            <span class="text-[11px] text-slate-400 block truncate max-w-sm">{{ $prg->description }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if($prg->tap_name)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">TAP: {{ $prg->tap_name }}</span>
                                            <span class="block text-[10px] text-slate-400 mt-0.5">{{ $prg->cluster_name }} &bull; {{ $prg->area_name }}</span>
                                        @elseif($prg->cluster_name)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">Cluster: {{ $prg->cluster_name }}</span>
                                            <span class="block text-[10px] text-slate-400 mt-0.5">{{ $prg->area_name }}</span>
                                        @elseif($prg->area_name)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-cyan-50 text-cyan-800 border border-cyan-200">Area: {{ $prg->area_name }}</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Nasional (Semua Cluster)</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-[11px]">
                                        @if($prg->start_date || $prg->end_date)
                                            <span class="text-slate-700 font-bold">
                                                {{ $prg->start_date ? date('d M Y', strtotime($prg->start_date)) : 'Mulai sekarang' }}
                                                &rarr;
                                                {{ $prg->end_date ? date('d M Y', strtotime($prg->end_date)) : 'Tanpa batas' }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 italic">Tanpa batas tanggal</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ $prg->items_count }} Item
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <x-master-status-toggle entity="programs" :id="$prg->id" :name="$prg->name" :active="$prg->status === 'ACTIVE'" />
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button type="button"
                                                    @click="goToProgramItems({{ $prg->id }})"
                                                    class="px-2.5 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-[11px] font-bold transition-all cursor-pointer">
                                                Atur Item
                                            </button>
                                            <button type="button"
                                                    @click="openProgramModal({{ json_encode($prg) }})"
                                                    class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 text-[11px] font-bold cursor-pointer">
                                                Edit
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-10 text-center text-slate-400">
                                        Belum ada program harga terdaftar. Klik <strong>Tambah Program Baru</strong> untuk membuat program (misal: Event Sekolah, Promo Ramadan, dll.).
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- TAB 2: SETTING HARGA ITEM PROGRAM              -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'items'" x-cloak>
            <!-- Selector Program -->
            <div class="bg-slate-50/80 border border-slate-200/90 rounded-2xl p-4 mb-5 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-3 flex-1">
                    <span class="text-xs font-extrabold text-slate-700">Pilih Program:</span>
                    <div class="relative inline-flex items-center flex-1 max-w-md">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-indigo-600">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                        </div>
                        <select x-model="selectedProgramId" class="w-full text-xs font-bold bg-white border border-slate-200 rounded-xl pl-9 pr-8 py-2 focus:outline-none focus:border-indigo-500 cursor-pointer appearance-none">
                            <option value="">-- Pilih Program untuk Diatur --</option>
                            @foreach($programs as $prg)
                                <option value="{{ $prg->id }}">
                                    [{{ $prg->code }}] {{ $prg->name }} ({{ $prg->tap_name ?? $prg->cluster_name ?? $prg->area_name ?? 'Nasional' }})
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                        </div>
                    </div>
                </div>

                <div class="text-xs text-slate-500 font-medium" x-show="selectedProgramId">
                    Total Item: <strong class="text-slate-900" x-text="selectedProgramItems.length"></strong> produk
                </div>
            </div>

            <!-- Table Item Program -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">Produk</th>
                                <th class="py-3 px-4 text-right">Harga Normal Wilayah</th>
                                <th class="py-3 px-4 text-center">Mode Penyesuaian</th>
                                <th class="py-3 px-4 text-right">Harga Jadi Program</th>
                                <th class="py-3 px-4 text-center">Selisih</th>
                                <th class="py-3 px-4 text-center">Min Qty</th>
                                <th class="py-3 px-4 text-center w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            <template x-if="!selectedProgramId">
                                <tr>
                                    <td colspan="7" class="py-10 text-center text-slate-400">
                                        Pilih salah satu <strong>Program</strong> di atas (atau klik <strong>Atur Item</strong> dari Tab Daftar Program) untuk melihat dan mengatur harga itemnya.
                                    </td>
                                </tr>
                            </template>

                            @foreach($programItems as $it)
                                <tr x-show="String(selectedProgramId) === '{{ $it->program_id }}'" class="hover:bg-slate-50/70">
                                    <td class="py-3.5 px-4">
                                        <span class="font-bold text-slate-900 block">{{ $it->product_name }}</span>
                                        <span class="text-[10px] font-mono text-slate-400">{{ $it->product_sku }}</span>
                                        @if($it->notes)
                                            <span class="text-[10px] text-slate-500 block mt-0.5">{{ $it->notes }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono text-slate-600">
                                        Rp {{ number_format($it->normal_selling_price, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        @if($it->price_mode === 'FIXED')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">Harga Fix</span>
                                        @elseif($it->price_mode === 'DISCOUNT_NOMINAL')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Diskon -Rp {{ number_format($it->price_value, 0, ',', '.') }}</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">Markup +Rp {{ number_format($it->price_value, 0, ',', '.') }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono font-extrabold text-slate-900 text-sm">
                                        Rp {{ number_format($it->final_price, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-mono">
                                        @if($it->price_diff < 0)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                Turun Rp {{ number_format(abs($it->price_diff), 0, ',', '.') }}
                                            </span>
                                        @elseif($it->price_diff > 0)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                                Naik +Rp {{ number_format($it->price_diff, 0, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 text-[10px]">Sama</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-700">
                                        {{ $it->min_qty }} pcs
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button type="button" @click="openItemModal({{ json_encode($it) }})" class="px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-indigo-50 hover:text-indigo-700 text-slate-700 text-xs font-bold cursor-pointer">
                                                Edit
                                            </button>
                                            <form method="POST" action="{{ route('master.program-items.delete', $it->id) }}" onsubmit="return confirm('Hapus item ini dari program?')">
                                                @csrf
                                                <input type="hidden" name="program_id" value="{{ $it->program_id }}">
                                                <button type="submit" class="px-2 py-1 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold cursor-pointer">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach

                            <tr x-show="selectedProgramId && selectedProgramItems.length === 0" x-cloak>
                                <td colspan="7" class="py-10 text-center text-slate-400">
                                    Belum ada item produk pada program ini. Klik <strong>Tambah Item Program</strong> di kanan atas.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- MODAL TAMBAH / EDIT PROGRAM                    -->
        <!-- ============================================== -->
        <template x-if="showProgramModal">
            <div @click.self="showProgramModal = false" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                <div class="bg-white rounded-2xl border border-slate-200 max-w-xl w-full p-6 shadow-2xl">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                        <h3 class="font-extrabold text-slate-900 text-sm" x-text="isProgramEdit ? 'Edit Program Harga' : 'Tambah Program Harga Baru'"></h3>
                        <button type="button" @click="showProgramModal = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold cursor-pointer">&times;</button>
                    </div>
                    <form :action="programFormAction" method="POST" class="space-y-3.5">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Kode Program <span class="text-rose-500">*</span></label>
                                <input type="text" name="code" x-model="programForm.code" required placeholder="PRG-SEKOLAH-01" class="w-full text-xs font-mono font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Program <span class="text-rose-500">*</span></label>
                                <input type="text" name="name" x-model="programForm.name" required placeholder="Program Event Sekolah" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Area (Kosong = Nasional)</label>
                                <select name="area_id" x-model="programForm.area_id" @change="programForm.cluster_id = ''; programForm.tap_id = ''" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                    <option value="">-- Nasional --</option>
                                    @foreach($areas as $ar)
                                        <option value="{{ $ar->id }}">{{ $ar->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Cluster (Utama)</label>
                                <select name="cluster_id" x-model="programForm.cluster_id" @change="programForm.tap_id = ''" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                    <option value="">-- Semua Cluster --</option>
                                    <template x-for="cls in modalClusterOptions" :key="cls.id">
                                        <option :value="String(cls.id)" :selected="String(cls.id) === String(programForm.cluster_id)" x-text="cls.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">TAP (Opsional)</label>
                                <select name="tap_id" x-model="programForm.tap_id" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                    <option value="">-- Semua TAP --</option>
                                    <template x-for="tp in modalTapOptions" :key="tp.id">
                                        <option :value="String(tp.id)" :selected="String(tp.id) === String(programForm.tap_id)" x-text="tp.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Mulai</label>
                                <input type="date" name="start_date" x-model="programForm.start_date" class="w-full text-xs font-mono bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Berakhir</label>
                                <input type="date" name="end_date" x-model="programForm.end_date" class="w-full text-xs font-mono bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan Program</label>
                            <textarea name="description" x-model="programForm.description" rows="2" placeholder="Syarat / catatan program..." class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2"></textarea>
                        </div>

                        <template x-if="isProgramEdit">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Status</label>
                                <select name="status" x-model="programForm.status" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                    <option value="ACTIVE">ACTIVE</option>
                                    <option value="INACTIVE">INACTIVE</option>
                                </select>
                            </div>
                        </template>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                            <button type="button" @click="showProgramModal = false" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl text-xs cursor-pointer">Batal</button>
                            <button type="submit" class="px-5 py-2 bg-slate-900 text-white font-bold rounded-xl text-xs cursor-pointer">Simpan Program</button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- ============================================== -->
        <!-- MODAL TAMBAH / EDIT ITEM PROGRAM               -->
        <!-- ============================================== -->
        <template x-if="showItemModal">
            <div @click.self="showItemModal = false" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                <div class="bg-white rounded-2xl border border-slate-200 max-w-lg w-full p-6 shadow-2xl">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                        <h3 class="font-extrabold text-slate-900 text-sm" x-text="isItemEdit ? 'Edit Harga Item Program' : 'Tambah Item Harga Program'"></h3>
                        <button type="button" @click="showItemModal = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold cursor-pointer">&times;</button>
                    </div>
                    <form :action="itemFormAction" method="POST" class="space-y-3.5">
                        @csrf
                        <input type="hidden" name="program_id" :value="selectedProgramId">

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Produk <span class="text-rose-500">*</span></label>
                            <select name="product_id" x-model="itemForm.product_id" required class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                <option value="">-- Pilih Produk --</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}">[{{ $prod->sku }}] {{ $prod->name }} (Default: Rp {{ number_format($prod->selling_price, 0, ',', '.') }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Mode Harga <span class="text-rose-500">*</span></label>
                                <select name="price_mode" x-model="itemForm.price_mode" required class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                    <option value="FIXED">Harga Fix (Langsung Set Harga Jadi)</option>
                                    <option value="DISCOUNT_NOMINAL">Potongan / Diskon (- Rp)</option>
                                    <option value="MARKUP_NOMINAL">Kenaikan / Markup (+ Rp)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nilai (Rp) <span class="text-rose-500">*</span></label>
                                <input type="number" name="price_value" x-model="itemForm.price_value" required min="0" step="1" placeholder="Contoh: 4500" class="w-full text-xs font-mono font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Minimal Qty Pembelian</label>
                                <input type="number" name="min_qty" x-model="itemForm.min_qty" min="1" class="w-full text-xs font-mono font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Catatan (Opsional)</label>
                                <input type="text" name="notes" x-model="itemForm.notes" placeholder="Keterangan promo..." class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                            <button type="button" @click="showItemModal = false" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl text-xs cursor-pointer">Batal</button>
                            <button type="submit" class="px-5 py-2 bg-slate-900 text-white font-bold rounded-xl text-xs cursor-pointer">Simpan Item</button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>

    @push('scripts')
    <script>
        function programMasterApp() {
            return {
                activeTab: 'programs',
                allPrograms: @json($programs),
                allProgramItems: @json($programItems),
                allClusters: @json($clusters),
                allTaps: @json($taps),

                // Filter Tab 1
                filterArea: '',
                filterCluster: '',
                searchProgram: '',
                programPage: 1,
                programPerPage: 10,

                // Tab 2 Selected Program
                selectedProgramId: '',

                // Modal Program
                showProgramModal: false,
                isProgramEdit: false,
                programFormAction: '',
                programForm: {},

                // Modal Item Program
                showItemModal: false,
                isItemEdit: false,
                itemFormAction: '',
                itemForm: {},

                init() {
                    const params = new URLSearchParams(window.location.search);
                    if (params.has('tab') && ['programs', 'items'].includes(params.get('tab'))) {
                        this.activeTab = params.get('tab');
                    }
                    if (params.has('program_id')) {
                        this.selectedProgramId = params.get('program_id');
                    }
                    this.$watch('activeTab', () => this.refreshIcons());
                    this.refreshIcons();
                },

                get filterClusterOptions() {
                    if (!this.filterArea) return this.allClusters;
                    return this.allClusters.filter(c => String(c.area_id) === String(this.filterArea));
                },

                get modalClusterOptions() {
                    if (!this.programForm.area_id) return this.allClusters;
                    return this.allClusters.filter(c => String(c.area_id) === String(this.programForm.area_id));
                },

                get modalTapOptions() {
                    if (this.programForm.cluster_id) {
                        return this.allTaps.filter(t => String(t.cluster_id) === String(this.programForm.cluster_id));
                    }
                    if (this.programForm.area_id) {
                        return this.allTaps.filter(t => String(t.area_id) === String(this.programForm.area_id));
                    }
                    return this.allTaps;
                },

                get filteredPrograms() {
                    return this.allPrograms.filter(p => {
                        if (this.filterArea && String(p.area_id) !== String(this.filterArea)) return false;
                        if (this.filterCluster && String(p.cluster_id) !== String(this.filterCluster)) return false;
                        if (this.searchProgram) {
                            const q = this.searchProgram.toLowerCase();
                            const match = (p.code && p.code.toLowerCase().includes(q)) ||
                                          (p.name && p.name.toLowerCase().includes(q)) ||
                                          (p.cluster_name && p.cluster_name.toLowerCase().includes(q)) ||
                                          (p.tap_name && p.tap_name.toLowerCase().includes(q));
                            if (!match) return false;
                        }
                        return true;
                    });
                },

                get paginatedProgramIds() {
                    return this.filteredPrograms.map(p => p.id);
                },

                get selectedProgramItems() {
                    if (!this.selectedProgramId) return [];
                    return this.allProgramItems.filter(it => String(it.program_id) === String(this.selectedProgramId));
                },

                goToProgramItems(programId) {
                    this.selectedProgramId = String(programId);
                    this.activeTab = 'items';
                    this.refreshIcons();
                },

                openProgramModal(item = null) {
                    if (item) {
                        this.isProgramEdit = true;
                        this.programFormAction = '/master/programs/update/' + item.id;
                        this.programForm = {
                            code: item.code || '',
                            name: item.name || '',
                            area_id: item.area_id ? String(item.area_id) : '',
                            cluster_id: item.cluster_id ? String(item.cluster_id) : '',
                            tap_id: item.tap_id ? String(item.tap_id) : '',
                            start_date: item.start_date || '',
                            end_date: item.end_date || '',
                            description: item.description || '',
                            status: item.status || 'ACTIVE',
                        };
                    } else {
                        this.isProgramEdit = false;
                        this.programFormAction = '{{ route('master.programs.store') }}';
                        this.programForm = {
                            code: '', name: '', area_id: '', cluster_id: '', tap_id: '',
                            start_date: '', end_date: '', description: '', status: 'ACTIVE',
                        };
                    }
                    this.showProgramModal = true;
                },

                openItemModal(item = null) {
                    if (!this.selectedProgramId) return;
                    if (item) {
                        this.isItemEdit = true;
                        this.itemFormAction = '/master/program-items/update/' + item.id;
                        this.itemForm = {
                            product_id: String(item.product_id),
                            price_mode: item.price_mode || 'FIXED',
                            price_value: item.price_value || 0,
                            min_qty: item.min_qty || 1,
                            notes: item.notes || '',
                        };
                    } else {
                        this.isItemEdit = false;
                        this.itemFormAction = '{{ route('master.program-items.store') }}';
                        this.itemForm = {
                            product_id: '', price_mode: 'FIXED', price_value: 0, min_qty: 1, notes: '',
                        };
                    }
                    this.showItemModal = true;
                },

                refreshIcons() {
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
