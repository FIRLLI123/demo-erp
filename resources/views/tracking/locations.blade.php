<x-app-layout>
    <x-slot name="title">Live Tracking Lokasi Tim Sales</x-slot>
    <x-slot name="headerTitle">Tracking & Posisi Sales</x-slot>
    <x-slot name="badge">Live GPS Kanvaser</x-slot>

    @push('styles')
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        .custom-sales-marker {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .marker-pulse {
            position: absolute;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(225, 29, 72, 0.35);
            animation: pulse-ring 2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
        }
        .marker-pin {
            position: relative;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e11d48;
            border: 3px solid #ffffff;
            color: #ffffff;
            font-weight: 800;
            font-size: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        @keyframes pulse-ring {
            0% { transform: scale(0.9); opacity: 0.9; }
            70% { transform: scale(1.6); opacity: 0; }
            100% { transform: scale(1.8); opacity: 0; }
        }
    </style>
    @endpush

    <div x-data="trackingLocationsApp()">
        
        <!-- Header Banner -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <i data-lucide="map-pin" class="w-5 h-5 text-rose-600"></i>
                    <span>Live GPS Tracking Tim Sales Force (Kanvas Lapangan)</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-purple-100 text-purple-800 border border-purple-200">PT WAHANA PUTRA YUDHA</span>
                </h2>
                <p class="text-xs text-slate-500">Pemantauan real-time rute kanvas motor, kunjungan outlet konter, status baterai perangkat, serta omset penjualan sales lapangan</p>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>GPS Telemetri Aktif</span>
                </span>
                <button type="button" @click="resetView()" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs flex items-center gap-1.5 transition-all">
                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                    <span>Pusatkan Peta</span>
                </button>
            </div>
        </div>

        <!-- KPI Metrics Summary -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 block mb-1">Sales Kanvas Aktif</span>
                    <span class="text-2xl font-extrabold text-slate-900">{{ count($trackingData) }} Tenaga</span>
                    <span class="text-[11px] text-emerald-600 font-semibold block mt-1">100% Online di Lapangan</span>
                </div>
                <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 block mb-1">Toko Terkunjungi Hari Ini</span>
                    <span class="text-2xl font-extrabold text-indigo-600">
                        {{ collect($trackingData)->sum('visited_today') }} Outlet
                    </span>
                    <span class="text-[11px] text-slate-500 block mt-1">Target Hari Ini: {{ collect($trackingData)->sum('target_today') }} Toko</span>
                </div>
                <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <i data-lucide="store" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 block mb-1">Total Omset Lapangan</span>
                    <span class="text-2xl font-extrabold text-emerald-600 font-mono">
                        Rp {{ number_format(collect($trackingData)->sum('today_omset'), 0, ',', '.') }}
                    </span>
                    <span class="text-[11px] text-emerald-600/90 font-semibold block mt-1">Akumulasi SO Berjalan</span>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="banknote" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 block mb-1">Rata-rata Baterai Device</span>
                    <span class="text-2xl font-extrabold text-slate-900">
                        {{ round(collect($trackingData)->avg('battery')) }}%
                    </span>
                    <span class="text-[11px] text-emerald-600 font-semibold block mt-1">Sinyal 5G/4G Telkomsel</span>
                </div>
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="battery-charging" class="w-5 h-5"></i>
                </div>
            </div>
        </div>

        <!-- Main Workspace: Split View (Sales List + Visual Interactive Map) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Left Panel: Sales Live Cards (4 Cols) -->
            <div class="lg:col-span-5 space-y-3">
                <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
                    <span class="text-xs font-extrabold text-slate-800">Daftar Sales Force Lapangan</span>
                    <span class="text-[11px] text-slate-500 font-mono">Klik kartu untuk fokus</span>
                </div>

                <div class="space-y-3 max-h-[620px] overflow-y-auto pr-1">
                    @foreach($trackingData as $idx => $s)
                        <div @click="flyToSales({{ $s->lat }}, {{ $s->lng }}, '{{ $s->name }}', {{ $idx }})"
                             class="bg-white p-4 rounded-2xl border transition-all cursor-pointer hover:shadow-md hover:border-rose-400 group relative"
                             :class="selectedSalesIndex === {{ $idx }} ? 'border-rose-500 ring-2 ring-rose-500/20 bg-rose-50/20' : 'border-slate-200'">
                            
                            <div class="flex items-start justify-between gap-2 mb-2.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-rose-600 to-red-500 flex items-center justify-center text-white font-extrabold text-xs shadow-sm">
                                        {{ strtoupper(substr($s->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-extrabold text-slate-900 text-sm group-hover:text-rose-600 transition-colors">{{ $s->name }}</span>
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-100 text-slate-700 border border-slate-200">{{ $s->code }}</span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 flex items-center gap-1">
                                            <span>TAP {{ $s->tap_name }}</span> &bull; 
                                            <span class="text-slate-400">{{ $s->warehouse_name ?? 'Gudang Kanvas' }}</span>
                                        </p>
                                    </div>
                                </div>

                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold 
                                    {{ $s->status_color === 'emerald' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : ($s->status_color === 'amber' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-blue-100 text-blue-800 border border-blue-200') }}">
                                    {{ $s->status_field }}
                                </span>
                            </div>

                            <!-- Live Telemetry Row -->
                            <div class="bg-slate-50 rounded-xl p-2.5 mb-2.5 border border-slate-100 text-xs space-y-1.5">
                                <div class="flex items-center justify-between text-slate-600">
                                    <span class="flex items-center gap-1 text-[11px]">
                                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-rose-500 shrink-0"></i>
                                        <span class="truncate max-w-[210px]">{{ $s->current_location }}</span>
                                    </span>
                                    <span class="font-mono text-[10px] font-bold text-slate-400">{{ $s->last_ping }}</span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] pt-1 border-t border-slate-200/60">
                                    <span class="text-slate-500">Toko Terakhir: <strong class="text-slate-800">{{ $s->last_outlet }}</strong></span>
                                </div>
                            </div>

                            <!-- Bottom Metrics Row -->
                            <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-100">
                                <div class="flex items-center gap-3 text-slate-600 text-[11px]">
                                    <span class="flex items-center gap-1 font-semibold" title="Level Baterai">
                                        <i data-lucide="battery" class="w-3.5 h-3.5 text-emerald-600"></i>
                                        <span>{{ $s->battery }}%</span>
                                    </span>
                                    <span class="flex items-center gap-1 text-slate-500" title="Kecepatan Motor">
                                        <i data-lucide="gauge" class="w-3.5 h-3.5 text-slate-400"></i>
                                        <span>{{ $s->speed }}</span>
                                    </span>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] text-slate-400 block">Kunjungan: {{ $s->visited_today }}/{{ $s->target_today }}</span>
                                    <span class="font-mono font-bold text-emerald-700 text-xs">Rp {{ number_format($s->today_omset, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Right Panel: Interactive Visual Map (7 Cols) -->
            <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden flex flex-col">
                <div class="p-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
                    <div class="flex items-center gap-2">
                        <i data-lucide="navigation" class="w-4 h-4 text-rose-600"></i>
                        <span class="text-xs font-extrabold text-slate-800">Visual Peta Kanvas Jabodetabek Real-Time</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-[11px] text-slate-500">Satelit / Jalan OpenStreetMap</span>
                    </div>
                </div>

                <!-- Leaflet Map Container -->
                <div id="salesMap" class="w-full h-[580px] z-10 bg-slate-100"></div>

                <!-- Map Footer Legend -->
                <div class="p-3 bg-slate-50 border-t border-slate-100 text-[11px] text-slate-600 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-4">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <span>Visit Toko</span>
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            <span>Transaksi SO</span>
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                            <span>Dalam Perjalanan</span>
                        </span>
                    </div>
                    <span class="font-mono text-slate-400 text-[10px]">&bull; Provider Sinyal: Jaringan Kuat Telkomsel Jabodetabek</span>
                </div>
            </div>

        </div>

    </div>

    @push('scripts')
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <script>
        const salesData = @json($trackingData);

        function trackingLocationsApp() {
            return {
                map: null,
                markers: [],
                selectedSalesIndex: null,

                init() {
                    this.$nextTick(() => {
                        this.initMap();
                        setTimeout(() => {
                            if (this.map) this.map.invalidateSize();
                        }, 250);
                    });
                },

                initMap() {
                    // Inisialisasi peta di area Jakarta Selatan / Jabodetabek
                    this.map = L.map('salesMap').setView([-6.2750, 106.8250], 13);

                    // Gunakan OpenStreetMap standar (100% gratis & tanpa perlu API Key)
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors | Telkomsel Sales Tracker',
                        maxZoom: 19
                    }).addTo(this.map);

                    // Tambahkan marker untuk setiap sales
                    salesData.forEach((s, idx) => {
                        const iconHtml = `
                            <div class="custom-sales-marker">
                                <div class="marker-pulse"></div>
                                <div class="marker-pin">${s.code.replace('SLS-', '')}</div>
                            </div>
                        `;

                        const customIcon = L.divIcon({
                            html: iconHtml,
                            className: 'custom-div-icon',
                            iconSize: [38, 38],
                            iconAnchor: [19, 19],
                            popupAnchor: [0, -18]
                        });

                        const popupContent = `
                            <div style="font-family: inherit; font-size: 12px; min-width: 210px; line-height: 1.5;">
                                <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 6px; margin-bottom: 6px;">
                                    <strong style="color: #0f172a; font-size: 13px;">${s.name}</strong>
                                    <span style="background: #ffe4e6; color: #e11d48; font-weight: 800; font-size: 10px; padding: 1px 6px; border-radius: 4px;">${s.code}</span>
                                </div>
                                <div style="color: #64748b; font-size: 11px; margin-bottom: 4px;">TAP: <strong>${s.tap_name}</strong></div>
                                <div style="color: #0f172a; font-size: 11px; margin-bottom: 4px;">Posisi: <strong>${s.current_location}</strong></div>
                                <div style="color: #0f172a; font-size: 11px; margin-bottom: 6px;">Toko: <strong style="color: #059669;">${s.last_outlet}</strong></div>
                                <div style="display: flex; justify-content: space-between; background: #f8fafc; padding: 4px 8px; border-radius: 6px; margin-bottom: 8px; font-size: 11px;">
                                    <span>Baterai: <strong>${s.battery}%</strong></span>
                                    <span>Omset: <strong style="color: #059669;">Rp ${(s.today_omset).toLocaleString('id-ID')}</strong></span>
                                </div>
                                <a href="https://wa.me/${s.phone.replace(/^0/, '62')}" target="_blank" style="display: block; text-align: center; background: #25D366; color: white; padding: 4px 8px; border-radius: 6px; font-weight: bold; text-decoration: none; font-size: 11px;">
                                    Hubungi WhatsApp Sales
                                </a>
                            </div>
                        `;

                        const marker = L.marker([s.lat, s.lng], { icon: customIcon })
                            .addTo(this.map)
                            .bindPopup(popupContent);

                        this.markers.push(marker);
                    });
                },

                flyToSales(lat, lng, name, idx) {
                    this.selectedSalesIndex = idx;
                    if (this.map) {
                        this.map.flyTo([lat, lng], 16, {
                            duration: 1.2
                        });
                        if (this.markers[idx]) {
                            this.markers[idx].openPopup();
                        }
                    }
                },

                resetView() {
                    this.selectedSalesIndex = null;
                    if (this.map) {
                        this.map.flyTo([-6.2750, 106.8250], 13, { duration: 1 });
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout>

