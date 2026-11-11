<x-app-layout>
    <x-slot name="title">Rencana Kunjungan Sales (PJP)</x-slot>
    <x-slot name="headerTitle">Rencana Kunjungan & Call Plan</x-slot>
    <x-slot name="badge">Periodic Journey Plan (PJP)</x-slot>

    @push('styles')
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        .custom-pjp-marker {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .pjp-marker-number {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 2.5px solid #ffffff;
            color: #ffffff;
            font-weight: 800;
            font-size: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.35);
        }
        .pjp-pulse {
            position: absolute;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            animation: pjp-pulse-ring 2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
        }
        @keyframes pjp-pulse-ring {
            0% { transform: scale(0.9); opacity: 0.8; }
            70% { transform: scale(1.6); opacity: 0; }
            100% { transform: scale(1.8); opacity: 0; }
        }
    </style>
    @endpush

    <div x-data="pjpApp()">
        
        <!-- Header Banner & Sales Picker -->
        <div class="mb-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <i data-lucide="calendar-check" class="w-5 h-5 text-amber-600"></i>
                    <span>Periodic Journey Plan (PJP) & Rute Kunjungan Harian</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">CALL PLAN</span>
                </h2>
                <p class="text-xs text-slate-500">Standarisasi jadwal rute outlet tetap, kepatuhan kunjungan sales (Call Compliance), Effective Call (EC), dan foto check-in GPS lapangan</p>
            </div>

            <!-- Controls: Filter Sales & Tambah Jadwal -->
            <div class="flex flex-wrap items-center gap-2.5">
                <form method="GET" action="{{ route('tracking.pjp') }}" class="flex items-center gap-2">
                    <input type="hidden" name="day" value="{{ $selectedDay }}">
                    <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-3 py-1.5 shadow-2xs">
                        <i data-lucide="user-check" class="w-4 h-4 text-slate-400"></i>
                        <span class="text-xs font-bold text-slate-600">Kanvaser:</span>
                        <select name="sales_id" onchange="this.form.submit()" class="text-xs font-extrabold text-slate-900 bg-transparent focus:outline-none cursor-pointer">
                            @foreach($salesList as $s)
                                <option value="{{ $s->id }}" {{ $activeSales->id == $s->id ? 'selected' : '' }}>
                                    [{{ $s->code }}] {{ $s->name }} ({{ $s->tap_name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>

                <button type="button" @click="showAddPlanModal = true" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-sm transition-all active:scale-95">
                    <i data-lucide="plus" class="w-4 h-4 text-amber-400"></i>
                    <span>Atur Jadwal Rute</span>
                </button>
            </div>
        </div>

        <!-- TAB HARI SENIN - SABTU (WEEKLY CYCLE) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-2 mb-6">
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-2">
                @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $day)
                    <a href="{{ route('tracking.pjp', ['sales_id' => $activeSales->id, 'day' => $day]) }}"
                       class="px-3 py-2.5 rounded-xl text-center transition-all flex flex-col items-center justify-center {{ $selectedDay === $day ? 'bg-amber-500 text-white shadow-md shadow-amber-500/20 font-extrabold ring-2 ring-amber-400/40' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 font-bold' }}">
                        <span class="text-xs tracking-wider uppercase">{{ $day }}</span>
                        <span class="text-[10px] font-mono mt-0.5 {{ $selectedDay === $day ? 'text-amber-100' : 'text-slate-400' }}">
                            {{ $daysSchedule[$day]['target_outlets'] ?? 10 }} Toko
                        </span>
                    </a>
                @endforeach
            </div>

            <div class="mt-2.5 pt-2.5 px-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between text-xs gap-2">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-100 text-slate-700">RUTE AKTIF</span>
                    <strong class="text-slate-900 font-extrabold">{{ $daysSchedule[$selectedDay]['area_cluster'] ?? 'Rute Operasional' }}</strong>
                </div>
                <span class="text-slate-500 font-mono text-[11px]">Target Omset Harian: <strong class="text-slate-800">Rp {{ number_format($daysSchedule[$selectedDay]['target_omset'] ?? 0, 0, ',', '.') }}</strong></span>
            </div>
        </div>

        <!-- KPI SUMMARY CARDS -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- 1. Total Planned Call -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 block mb-1">Total Target Kunjungan</span>
                    <span class="text-2xl font-extrabold text-slate-900 font-mono">{{ $totalPlanned }} Toko</span>
                    <span class="text-[11px] text-slate-500 font-medium block mt-1">100% Jadwal PJP {{ $selectedDay }}</span>
                </div>
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="store" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- 2. Call Compliance (Visited) -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 block mb-1">Realisasi Dikunjungi</span>
                    <span class="text-2xl font-extrabold text-slate-900 font-mono">{{ $visitedCount }} / {{ $totalPlanned }}</span>
                    <span class="text-[11px] text-emerald-600 font-bold block mt-1 flex items-center gap-1">
                        <i data-lucide="check" class="w-3.5 h-3.5"></i> {{ $callCompliance }}% Kepatuhan Rute
                    </span>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="footprints" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- 3. Effective Call & Strike Rate -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 block mb-1">Effective Call (Order Sukses)</span>
                    <span class="text-2xl font-extrabold text-amber-600 font-mono">{{ $effectiveCalls }} Outlet</span>
                    <span class="text-[11px] text-amber-700 font-bold block mt-1">
                        {{ $strikeRate }}% Strike Rate
                    </span>
                </div>
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- 4. Revenue Terkumpul Hari Ini -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 block mb-1">Omset Terkumpul Hari Ini</span>
                    <span class="text-xl font-extrabold text-emerald-700 font-mono">Rp {{ number_format($totalOmsetAchieved, 0, ',', '.') }}</span>
                    <span class="text-[11px] text-slate-500 font-medium block mt-1">Dari {{ $effectiveCalls }} transaksi SO</span>
                </div>
                <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </div>
            </div>
        </div>

        <!-- MAIN SPLIT: TIMELINE STOPS & ROUTE MAP -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- LEFT COLUMN: DAFTAR STOP KUNJUNGAN TIMELINE (7 COLS) -->
            <div class="lg:col-span-7 space-y-4">
                
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                    <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
                        <div class="flex items-center gap-2">
                            <i data-lucide="milestone" class="w-4 h-4 text-amber-600"></i>
                            <h3 class="font-extrabold text-slate-900 text-xs uppercase tracking-wider">
                                Urutan Kunjungan Kanvaser (Stop 1 &rarr; Stop {{ count($routeStops) }})
                            </h3>
                        </div>
                        <span class="text-xs text-slate-400 font-mono">{{ count($routeStops) }} Outlet Terjadwal</span>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach($routeStops as $stop)
                            <div class="p-4 transition-all hover:bg-slate-50/70 {{ $stop['status'] === 'EFFECTIVE_CALL' ? 'bg-emerald-50/20' : ($stop['status'] === 'CLOSED' ? 'bg-rose-50/20' : '') }}">
                                
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-start gap-3">
                                        <!-- Stop Badge Number -->
                                        <div class="w-8 h-8 rounded-xl font-extrabold font-mono text-xs flex items-center justify-center shrink-0 shadow-xs
                                            {{ $stop['status'] === 'EFFECTIVE_CALL' ? 'bg-emerald-600 text-white' : ($stop['status'] === 'VISIT_ONLY' ? 'bg-amber-500 text-white' : ($stop['status'] === 'CLOSED' ? 'bg-rose-600 text-white' : 'bg-slate-200 text-slate-600')) }}">
                                            #{{ $stop['stop_no'] }}
                                        </div>

                                        <div>
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <h4 class="font-extrabold text-slate-900 text-sm hover:text-amber-600 cursor-pointer" @click="focusMarker({{ $stop['stop_no'] - 1 }})">
                                                    {{ $stop['customer_name'] }}
                                                </h4>
                                                
                                                <!-- Status Badge -->
                                                @if($stop['status'] === 'EFFECTIVE_CALL')
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1">
                                                        <i data-lucide="check-circle" class="w-3 h-3"></i> EFFECTIVE CALL
                                                    </span>
                                                @elseif($stop['status'] === 'VISIT_ONLY')
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-200 flex items-center gap-1">
                                                        <i data-lucide="info" class="w-3 h-3"></i> HANYA VISIT (NO ORDER)
                                                    </span>
                                                @elseif($stop['status'] === 'CLOSED')
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-rose-100 text-rose-800 border border-rose-200 flex items-center gap-1">
                                                        <i data-lucide="x-circle" class="w-3 h-3"></i> TOKO TUTUP
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-slate-100 text-slate-600 border border-slate-200 flex items-center gap-1">
                                                        <i data-lucide="clock" class="w-3 h-3"></i> PENDING (BELUM TIBA)
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5 font-mono">
                                                <span class="font-bold text-slate-700">{{ $stop['digipos_id'] }}</span>
                                                <span>&bull;</span>
                                                <span>RS: {{ $stop['no_rs'] }}</span>
                                                <span>&bull;</span>
                                                <span>PIC: {{ $stop['pic'] }}</span>
                                            </div>

                                            <p class="text-xs text-slate-600 mt-1 flex items-start gap-1">
                                                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400 shrink-0 mt-0.5"></i>
                                                <span>{{ $stop['address'] }}</span>
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Right: Time & Value Info -->
                                    <div class="text-right shrink-0">
                                        <span class="text-xs font-mono font-bold text-slate-500 block">
                                            Rencana: {{ $stop['time_plan'] }}
                                        </span>
                                        @if($stop['omset'] > 0)
                                            <span class="text-sm font-mono font-extrabold text-emerald-700 block mt-1">
                                                Rp {{ number_format($stop['omset'], 0, ',', '.') }}
                                            </span>
                                            <span class="text-[10px] font-mono text-slate-400 block">{{ $stop['so_doc'] }}</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Checkin Details & Note Strip -->
                                <div class="mt-3 pt-2.5 border-t border-slate-100/80 flex flex-col sm:flex-row sm:items-center justify-between text-xs gap-2">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        @if($stop['checkin_time'])
                                            <span class="text-[11px] text-slate-600 bg-slate-100 px-2 py-0.5 rounded font-mono">
                                                In: <strong>{{ $stop['checkin_time'] }}</strong> &bull; Out: <strong>{{ $stop['checkout_time'] }}</strong> ({{ $stop['duration'] }})
                                            </span>
                                        @else
                                            <span class="text-[11px] text-slate-400 italic">Menunggu kunjungan sales di lokasi...</span>
                                        @endif
                                        
                                        @if($stop['notes'])
                                            <span class="text-[11px] text-slate-500 italic max-w-sm truncate" title="{{ $stop['notes'] }}">
                                                "{{ $stop['notes'] }}"
                                            </span>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-2 shrink-0">
                                        @if($stop['photo_verified'])
                                            <button type="button" @click="openPhotoModal({{ json_encode($stop) }})" class="px-2.5 py-1 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 rounded-lg text-xs font-bold flex items-center gap-1 transition-all">
                                                <i data-lucide="camera" class="w-3.5 h-3.5 text-emerald-600"></i>
                                                <span>Foto Outlet</span>
                                            </button>
                                        @endif
                                        <button type="button" @click="focusMarker({{ $stop['stop_no'] - 1 }})" class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-lg text-xs font-bold flex items-center gap-1 transition-all">
                                            <i data-lucide="navigation" class="w-3.5 h-3.5 text-amber-600"></i>
                                            <span>Lihat di Peta</span>
                                        </button>
                                    </div>
                                </div>

                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN: PETA RUTE LEAFLET & VISUAL TRAIL (5 COLS) -->
            <div class="lg:col-span-5 space-y-4 lg:sticky lg:top-24">
                
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                    <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-900 text-white">
                        <div class="flex items-center gap-2">
                            <i data-lucide="map" class="w-4 h-4 text-amber-400"></i>
                            <h3 class="font-extrabold text-xs uppercase tracking-wider text-white">Visual Rute Polyline PJP</h3>
                        </div>
                        <span class="text-[11px] text-amber-300 font-bold font-mono">TAP Pasar Minggu</span>
                    </div>

                    <!-- Map Container -->
                    <div id="pjpMap" class="w-full h-[450px] bg-slate-100 relative z-0"></div>

                    <!-- Legend & Map Controller -->
                    <div class="p-3.5 bg-slate-50 border-t border-slate-200 text-xs">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-slate-700">Keterangan Pin Marker:</span>
                            <button type="button" @click="fitAllMarkers()" class="text-amber-700 font-bold hover:underline flex items-center gap-1">
                                <i data-lucide="scan" class="w-3 h-3"></i> Zoom Semua Rute
                            </button>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-[11px]">
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full bg-emerald-600 shrink-0"></span>
                                <span class="text-slate-600 font-medium">Effective Call (Order Sukses)</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full bg-amber-500 shrink-0"></span>
                                <span class="text-slate-600 font-medium">Hanya Visit (No Order)</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full bg-rose-600 shrink-0"></span>
                                <span class="text-slate-600 font-medium">Toko Tutup / Gagal</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full bg-slate-400 shrink-0"></span>
                                <span class="text-slate-600 font-medium">Pending Jadwal</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Box Tips Call Compliance -->
                <div class="p-4 rounded-2xl bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-transparent border border-amber-300/60 text-xs text-amber-950">
                    <div class="flex items-start gap-2.5">
                        <i data-lucide="shield-alert" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5"></i>
                        <div>
                            <span class="font-extrabold block">Validasi Geofence & Check-In Anti Fake GPS</span>
                            <p class="text-[11px] text-amber-900/80 leading-relaxed mt-0.5">
                                Setiap check-in kanvaser divalidasi dengan radius maksimal <strong>50 meter</strong> dari koordinat master outlet. Sistem secara otomatis mendeteksi bila ada penggunaan Mock Location / GPS palsu.
                            </p>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <!-- ============================================== -->
        <!-- MODAL 1: BUKTI FOTO CHECK-IN KUNJUNGAN TOKO    -->
        <!-- ============================================== -->
        <div x-show="photoModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="photoModalOpen = false" class="bg-white rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-200">
                
                <div class="p-4 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800">
                    <div class="flex items-center gap-2">
                        <i data-lucide="camera" class="w-4 h-4 text-emerald-400"></i>
                        <h4 class="font-bold text-xs uppercase tracking-wider text-white">Bukti Check-In GPS Outlet</h4>
                    </div>
                    <button type="button" @click="photoModalOpen = false" class="text-slate-400 hover:text-white">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="p-5">
                    <!-- Photo Mock with Watermark -->
                    <div class="relative rounded-xl overflow-hidden bg-slate-900 aspect-4/3 flex items-center justify-center border border-slate-200 mb-4 group shadow-inner">
                        <!-- Placeholder Realistic Shopfront SVG / Image -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent z-10"></div>
                        <div class="text-center text-slate-300 p-6 z-0 flex flex-col items-center justify-center">
                            <div class="w-16 h-16 rounded-2xl bg-slate-800/80 border border-slate-700 flex items-center justify-center mb-3">
                                <i data-lucide="store" class="w-8 h-8 text-rose-500"></i>
                            </div>
                            <span class="text-xs font-bold text-white tracking-wide" x-text="selectedStop ? selectedStop.customer_name : ''"></span>
                            <span class="text-[11px] text-slate-400 mt-0.5">Etalase Resmi Telkomsel & Poster Promo Terbaru</span>
                        </div>

                        <!-- Camera Watermark Overlay at bottom -->
                        <div class="absolute bottom-3 left-3 right-3 z-20 text-[10px] font-mono text-emerald-300 bg-slate-950/80 p-2.5 rounded-lg border border-emerald-500/30 backdrop-blur-xs">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-bold text-white" x-text="selectedStop ? selectedStop.customer_name : ''"></span>
                                <span class="px-1.5 py-0.2 rounded text-[9px] bg-emerald-500 text-slate-950 font-extrabold">VERIFIED GPS</span>
                            </div>
                            <div class="grid grid-cols-2 gap-1 text-[9px] text-slate-300">
                                <div>Waktu: <span class="text-white" x-text="selectedStop ? selectedStop.checkin_time : ''"></span></div>
                                <div>Digipos: <span class="text-white" x-text="selectedStop ? selectedStop.digipos_id : ''"></span></div>
                                <div>Lat/Lng: <span class="text-emerald-400" x-text="selectedStop ? (selectedStop.lat + ', ' + selectedStop.lng) : ''"></span></div>
                                <div>Kanvaser: <span class="text-white">{{ $activeSales->name }}</span></div>
                            </div>
                        </div>
                    </div>

                    <!-- Visit Summary Data -->
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs space-y-1.5">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Alamat Outlet:</span>
                            <span class="font-bold text-slate-800 text-right" x-text="selectedStop ? selectedStop.address : ''"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Hasil Kunjungan:</span>
                            <span class="font-extrabold" :class="selectedStop && selectedStop.status === 'EFFECTIVE_CALL' ? 'text-emerald-700' : 'text-slate-800'" x-text="selectedStop ? selectedStop.status : ''"></span>
                        </div>
                        <template x-if="selectedStop && selectedStop.omset > 0">
                            <div class="flex justify-between pt-1 border-t border-slate-200">
                                <span class="text-slate-500">Nilai Transaksi (SO):</span>
                                <span class="font-extrabold font-mono text-emerald-700" x-text="'Rp ' + Number(selectedStop.omset).toLocaleString('id-ID')"></span>
                            </div>
                        </template>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button type="button" @click="photoModalOpen = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl text-xs">
                            Tutup Preview
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- ============================================== -->
        <!-- MODAL 2: FORM TAMBAH / ATUR TOKO KE JADWAL PJP -->
        <!-- ============================================== -->
        <div x-show="showAddPlanModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="showAddPlanModal = false" class="bg-white rounded-2xl max-w-md w-full overflow-hidden shadow-2xl border border-slate-200">
                <div class="p-4 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800">
                    <div class="flex items-center gap-2">
                        <i data-lucide="plus-circle" class="w-4 h-4 text-amber-400"></i>
                        <h4 class="font-bold text-xs uppercase tracking-wider text-white">Tambah Toko ke Rute PJP</h4>
                    </div>
                    <button type="button" @click="showAddPlanModal = false" class="text-slate-400 hover:text-white">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form @submit.prevent="showAddPlanModal = false; alert('Jadwal PJP berhasil disimpan ke master rute!');" class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Sales Kanvaser</label>
                        <input type="text" readonly value="{{ $activeSales->name }} ({{ $activeSales->code }})" class="w-full text-xs font-bold bg-slate-100 border border-slate-200 rounded-xl px-3 py-2 text-slate-700">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Hari Kunjungan</label>
                            <select class="w-full text-xs font-semibold bg-white border border-slate-200 rounded-xl px-3 py-2">
                                @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $d)
                                    <option value="{{ $d }}" {{ $selectedDay === $d ? 'selected' : '' }}>{{ $d }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Urutan Stop (No)</label>
                            <input type="number" value="{{ count($routeStops) + 1 }}" min="1" class="w-full text-xs font-bold bg-white border border-slate-200 rounded-xl px-3 py-2">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Toko / Outlet</label>
                        <select class="w-full text-xs font-bold bg-white border border-slate-200 rounded-xl px-3 py-2">
                            <option value="">-- Pilih Toko dari Master Outlet --</option>
                            <option value="1">[DGP-JKT-10021] Toko Berkah Cellindo</option>
                            <option value="2">[DGP-JKT-10022] Sinar Maju Komunika</option>
                            <option value="3">[DGP-JKT-10023] Jaya Abadi Cell</option>
                            <option value="4">[DGP-JKT-10024] Mitra Rezeki Pulsa</option>
                            <option value="5">[DGP-JKT-10025] Bintang Phone Cell</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Estimasi Rentang Jam Kunjungan</label>
                        <input type="text" placeholder="Contoh: 14:00 - 14:30" class="w-full text-xs font-medium bg-white border border-slate-200 rounded-xl px-3 py-2">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="showAddPlanModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-sm">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Simpan Jadwal PJP</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    @push('scripts')
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        function pjpApp() {
            return {
                stops: @json($routeStops),
                map: null,
                markers: [],
                polyline: null,
                photoModalOpen: false,
                selectedStop: null,
                showAddPlanModal: false,

                init() {
                    this.$nextTick(() => {
                        this.initMap();
                        setTimeout(() => {
                            if (this.map) this.map.invalidateSize();
                        }, 250);
                    });
                },

                initMap() {
                    const startLat = this.stops.length > 0 ? this.stops[0].lat : -6.2841;
                    const startLng = this.stops.length > 0 ? this.stops[0].lng : 106.8436;

                    this.map = L.map('pjpMap').setView([startLat, startLng], 14);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors | Telkomsel PJP Tracker',
                        maxZoom: 19
                    }).addTo(this.map);

                    const latlngs = [];

                    this.stops.forEach((stop, idx) => {
                        latlngs.push([stop.lat, stop.lng]);

                        let markerBg = '#64748b'; // slate
                        if (stop.status === 'EFFECTIVE_CALL') markerBg = '#059669'; // emerald
                        else if (stop.status === 'VISIT_ONLY') markerBg = '#f59e0b'; // amber
                        else if (stop.status === 'CLOSED') markerBg = '#e11d48'; // rose

                        const customIcon = L.divIcon({
                            className: 'custom-pjp-marker',
                            html: `
                                <div style="position: relative; display: flex; align-items: center; justify-content: center;">
                                    ${stop.status === 'EFFECTIVE_CALL' ? '<div class="pjp-pulse" style="background: rgba(5, 150, 105, 0.35);"></div>' : ''}
                                    <div class="pjp-marker-number" style="background-color: ${markerBg};">
                                        ${stop.stop_no}
                                    </div>
                                </div>
                            `,
                            iconSize: [30, 30],
                            iconAnchor: [15, 15],
                            popupAnchor: [0, -18]
                        });

                        const popupHtml = `
                            <div style="font-family: inherit; min-width: 190px;">
                                <div style="font-size: 10px; font-weight: 800; color: #64748b; text-transform: uppercase;">Stop #${stop.stop_no} &bull; ${stop.time_plan}</div>
                                <div style="font-size: 13px; font-weight: 800; color: #0f172a; margin-top: 2px;">${stop.customer_name}</div>
                                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">${stop.address}</div>
                                <div style="margin-top: 6px; padding-top: 6px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; font-size: 11px;">
                                    <span>Status:</span>
                                    <strong style="color: ${markerBg};">${stop.status}</strong>
                                </div>
                                ${stop.omset > 0 ? `
                                    <div style="margin-top: 4px; font-weight: 800; color: #059669; font-size: 12px; text-align: right;">
                                        Rp ${Number(stop.omset).toLocaleString('id-ID')}
                                    </div>
                                ` : ''}
                            </div>
                        `;

                        const marker = L.marker([stop.lat, stop.lng], { icon: customIcon })
                            .addTo(this.map)
                            .bindPopup(popupHtml);

                        this.markers.push(marker);
                    });

                    // Draw Polyline Route connecting the stops
                    if (latlngs.length > 1) {
                        this.polyline = L.polyline(latlngs, {
                            color: '#d97706', // amber-600
                            weight: 3.5,
                            opacity: 0.85,
                            dashArray: '6, 8',
                            lineJoin: 'round'
                        }).addTo(this.map);

                        this.map.fitBounds(this.polyline.getBounds(), { padding: [40, 40] });
                    }
                },

                focusMarker(index) {
                    if (this.markers[index] && this.map) {
                        const m = this.markers[index];
                        this.map.flyTo(m.getLatLng(), 16, { duration: 1.2 });
                        m.openPopup();
                    }
                },

                fitAllMarkers() {
                    if (this.polyline && this.map) {
                        this.map.fitBounds(this.polyline.getBounds(), { padding: [40, 40] });
                    }
                },

                openPhotoModal(stop) {
                    this.selectedStop = stop;
                    this.photoModalOpen = true;
                }
            };
        }
    </script>
    @endpush
</x-app-layout>

