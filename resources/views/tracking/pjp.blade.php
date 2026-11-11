<x-app-layout>
    <x-slot name="title">Rencana Kunjungan Sales (PJP)</x-slot>
    <x-slot name="headerTitle">Rencana Kunjungan (PJP)</x-slot>
    <x-slot name="badge">Call Plan</x-slot>

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
            width: 26px;
            height: 26px;
            border-radius: 50%;
            border: 2px solid #ffffff;
            color: #ffffff;
            font-weight: 800;
            font-size: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
        }
    </style>
    @endpush

    <div x-data="pjpApp()">
        
        <!-- Header Bersih & Ringkas -->
        <div class="mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <i data-lucide="calendar" class="w-5 h-5 text-slate-700"></i>
                    <span>Rencana Kunjungan & Call Plan (PJP)</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Jadwal rute harian, kepatuhan kunjungan, dan realisasi transaksi kanvaser</p>
            </div>

            <!-- Filter Sales & Action -->
            <div class="flex items-center gap-2">
                <form method="GET" action="{{ route('tracking.pjp') }}" class="flex items-center gap-2">
                    <input type="hidden" name="day" value="{{ $selectedDay }}">
                    <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-3 py-1.5 shadow-2xs">
                        <span class="text-xs font-bold text-slate-500">Sales:</span>
                        <select name="sales_id" onchange="this.form.submit()" class="text-xs font-bold text-slate-900 bg-transparent focus:outline-none cursor-pointer">
                            @foreach($salesList as $s)
                                <option value="{{ $s->id }}" {{ $activeSales->id == $s->id ? 'selected' : '' }}>
                                    [{{ $s->code }}] {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>

                <button type="button" @click="showAddPlanModal = true" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 transition-all">
                    <i data-lucide="plus" class="w-3.5 h-3.5 text-slate-300"></i>
                    <span>Atur Rute</span>
                </button>
            </div>
        </div>

        <!-- Tab Hari Kunjungan (Senin - Sabtu) Clean -->
        <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs p-2 mb-5">
            <div class="grid grid-cols-3 sm:grid-cols-6 gap-1.5">
                @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $day)
                    <a href="{{ route('tracking.pjp', ['sales_id' => $activeSales->id, 'day' => $day]) }}"
                       class="py-2 px-3 rounded-lg text-center transition-all text-xs {{ $selectedDay === $day ? 'bg-slate-900 text-white font-bold shadow-xs' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 font-medium' }}">
                        <span>{{ $day }}</span>
                        <span class="text-[10px] font-mono block opacity-80">({{ $daysSchedule[$day]['target_outlets'] ?? 10 }} Toko)</span>
                    </a>
                @endforeach
            </div>

            <div class="mt-2 pt-2 px-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Rute: <strong class="text-slate-800">{{ $daysSchedule[$selectedDay]['area_cluster'] ?? '-' }}</strong></span>
                <span class="font-mono">Target: <strong class="text-slate-800">Rp {{ number_format($daysSchedule[$selectedDay]['target_omset'] ?? 0, 0, ',', '.') }}</strong></span>
            </div>
        </div>

        <!-- 4 KPI Card Ringkas -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 mb-5">
            <div class="p-3.5 rounded-xl bg-white border border-slate-200/90 shadow-2xs">
                <span class="text-[11px] font-bold text-slate-500 block">Target Toko</span>
                <span class="text-lg font-extrabold text-slate-900 font-mono mt-0.5 block">
                    <span x-text="stops.length"></span> Outlet
                </span>
                <span class="text-[10px] text-slate-400 mt-0.5 block">Jadwal {{ $selectedDay }}</span>
            </div>

            <div class="p-3.5 rounded-xl bg-white border border-slate-200/90 shadow-2xs">
                <span class="text-[11px] font-bold text-slate-500 block">Dikunjungi (Visited)</span>
                <span class="text-lg font-extrabold text-slate-900 font-mono mt-0.5 block">{{ $visitedCount }} Outlet</span>
                <span class="text-[10px] text-emerald-600 font-bold mt-0.5 block">{{ $callCompliance }}% Kepatuhan Rute</span>
            </div>

            <div class="p-3.5 rounded-xl bg-white border border-slate-200/90 shadow-2xs">
                <span class="text-[11px] font-bold text-slate-500 block">Effective Call (Order)</span>
                <span class="text-lg font-extrabold text-slate-900 font-mono mt-0.5 block">{{ $effectiveCalls }} Outlet</span>
                <span class="text-[10px] text-slate-500 font-mono mt-0.5 block">Strike Rate: <strong class="text-slate-800">{{ $strikeRate }}%</strong></span>
            </div>

            <div class="p-3.5 rounded-xl bg-white border border-slate-200/90 shadow-2xs">
                <span class="text-[11px] font-bold text-slate-500 block">Omset Rute</span>
                <span class="text-lg font-extrabold text-emerald-700 font-mono mt-0.5 block">
                    Rp {{ number_format($totalOmsetAchieved, 0, ',', '.') }}
                </span>
                <span class="text-[10px] text-slate-400 mt-0.5 block">{{ $effectiveCalls }} Transaksi SO</span>
            </div>
        </div>

        <!-- MAIN SPLIT: TABEL RUTE KUNJUNGAN & PETA -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start mb-6">
            
            <!-- LEFT: TABEL DAFTAR KUNJUNGAN TOKO (7 COLS) -->
            <div class="lg:col-span-7 bg-white rounded-xl border border-slate-200/90 shadow-2xs overflow-hidden">
                <div class="p-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <span class="font-extrabold text-xs text-slate-800 uppercase tracking-wider">
                        Daftar Outlet Rute (Urutan Stop)
                    </span>
                    <span class="text-[11px] text-slate-400 font-mono">{{ count($routeStops) }} Toko</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="py-2.5 px-3 text-center w-10">Stop</th>
                                <th class="py-2.5 px-3">Toko / Outlet</th>
                                <th class="py-2.5 px-3 text-center">Status</th>
                                <th class="py-2.5 px-3 text-center">Waktu Visit</th>
                                <th class="py-2.5 px-3 text-right">Nilai SO</th>
                                <th class="py-2.5 px-3 text-center w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            <template x-for="(stop, idx) in stops" :key="idx">
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-slate-700" x-text="'#' + stop.stop_no"></td>

                                    <td class="py-2.5 px-3">
                                        <span class="font-bold text-slate-900 block" x-text="stop.customer_name"></span>
                                        <span class="text-[10px] text-slate-400 font-mono block" x-text="stop.digipos_id + ' • RS: ' + (stop.no_rs || '-')"></span>
                                    </td>

                                    <td class="py-2.5 px-3 text-center">
                                        <template x-if="stop.status === 'EFFECTIVE_CALL'">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                Order Sukses
                                            </span>
                                        </template>
                                        <template x-if="stop.status === 'VISIT_ONLY'">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                                                Visit Saja
                                            </span>
                                        </template>
                                        <template x-if="stop.status === 'CLOSED'">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">
                                                Tutup
                                            </span>
                                        </template>
                                        <template x-if="stop.status === 'PENDING'">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-400">
                                                Pending
                                            </span>
                                        </template>
                                    </td>

                                    <td class="py-2.5 px-3 text-center font-mono text-[11px] text-slate-600">
                                        <template x-if="stop.checkin_time">
                                            <div>
                                                <span x-text="stop.checkin_time"></span>
                                                <span class="text-slate-400 block text-[10px]" x-text="'(' + stop.duration + ')'"></span>
                                            </div>
                                        </template>
                                        <template x-if="!stop.checkin_time">
                                            <span class="text-slate-400 font-mono text-xs">-</span>
                                        </template>
                                    </td>

                                    <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-800">
                                        <template x-if="stop.omset > 0">
                                            <span class="text-emerald-700" x-text="'Rp ' + Number(stop.omset).toLocaleString('id-ID')"></span>
                                        </template>
                                        <template x-if="!stop.omset || stop.omset <= 0">
                                            <span class="text-slate-300">-</span>
                                        </template>
                                    </td>

                                    <td class="py-2.5 px-3 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <template x-if="stop.photo_verified">
                                                <button type="button" @click="openPhotoModal(stop)" class="p-1 hover:bg-slate-100 rounded text-slate-600" title="Foto Checkin">
                                                    <i data-lucide="camera" class="w-3.5 h-3.5"></i>
                                                </button>
                                            </template>
                                            <button type="button" @click="focusMarker(idx)" class="p-1 hover:bg-slate-100 rounded text-slate-600" title="Fokus Peta">
                                                <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- RIGHT: PETA RUTE LEAFLET (5 COLS) -->
            <div class="lg:col-span-5 bg-white rounded-xl border border-slate-200/90 shadow-2xs overflow-hidden lg:sticky lg:top-24">
                <div class="p-3 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <span class="font-extrabold text-xs text-slate-800">Peta Rute Kunjungan</span>
                    <button type="button" @click="fitAllMarkers()" class="text-xs text-slate-500 font-bold hover:text-slate-800">
                        Reset Zoom
                    </button>
                </div>

                <!-- Leaflet Container -->
                <div id="pjpMap" class="w-full h-[420px] bg-slate-100"></div>

                <div class="p-2.5 bg-slate-50 border-t border-slate-100 text-[11px] flex items-center justify-between text-slate-500">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span> Order Sukses
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-slate-500"></span> Visit Saja
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-600"></span> Tutup
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span> Pending
                    </span>
                </div>
            </div>

        </div>

        <!-- ============================================== -->
        <!-- MODAL: BUKTI FOTO CHECK-IN                     -->
        <!-- ============================================== -->
        <div x-show="photoModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="photoModalOpen = false" class="bg-white rounded-xl max-w-sm w-full overflow-hidden shadow-xl border border-slate-200">
                <div class="px-4 py-3 bg-slate-900 text-white flex items-center justify-between">
                    <span class="font-bold text-xs uppercase tracking-wider">Bukti Kunjungan Outlet</span>
                    <button type="button" @click="photoModalOpen = false" class="text-slate-400 hover:text-white">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <div class="p-4 text-xs">
                    <div class="rounded-lg bg-slate-100 aspect-4/3 flex flex-col items-center justify-center border border-slate-200 mb-3 text-slate-500">
                        <i data-lucide="store" class="w-8 h-8 text-slate-400 mb-1"></i>
                        <span class="font-bold text-slate-700" x-text="selectedStop ? selectedStop.customer_name : ''"></span>
                        <span class="text-[10px] text-slate-400" x-text="selectedStop ? selectedStop.address : ''"></span>
                    </div>

                    <div class="space-y-1 text-slate-600 text-[11px] mb-3">
                        <div class="flex justify-between">
                            <span>Check-in:</span>
                            <strong class="text-slate-800" x-text="selectedStop ? selectedStop.checkin_time : ''"></strong>
                        </div>
                        <div class="flex justify-between">
                            <span>Status:</span>
                            <strong class="text-slate-800" x-text="selectedStop ? selectedStop.status : ''"></strong>
                        </div>
                        <div class="flex justify-between">
                            <span>Nilai SO:</span>
                            <strong class="text-emerald-700 font-mono" x-text="selectedStop && selectedStop.omset > 0 ? ('Rp ' + Number(selectedStop.omset).toLocaleString('id-ID')) : '-'"></strong>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2 border-t border-slate-100">
                        <button type="button" @click="photoModalOpen = false" class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-lg text-xs">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- MODAL: ATUR RUTE KUNJUNGAN                     -->
        <!-- ============================================== -->
        <div x-show="showAddPlanModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="showAddPlanModal = false" class="bg-white rounded-xl max-w-sm w-full overflow-hidden shadow-xl border border-slate-200">
                <div class="px-4 py-3 bg-slate-900 text-white flex items-center justify-between">
                    <span class="font-bold text-xs uppercase tracking-wider">Tambah Outlet ke Rute</span>
                    <button type="button" @click="showAddPlanModal = false" class="text-slate-400 hover:text-white">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form data-no-loading @submit.prevent="submitNewStop()" class="p-4 text-xs space-y-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Pilih Toko / Outlet</label>
                        <select x-model="selectedCustomerToAdd" required class="w-full text-xs font-bold bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 focus:outline-none">
                            <option value="">-- Pilih Toko dari Master --</option>
                            <option value="DGP-JKT-10029|Konter Barokah Cell|Jl. Warung Jati Barat No. 12|-6.2780|106.8310">DGP-JKT-10029 - Konter Barokah Cell</option>
                            <option value="DGP-JKT-10030|Sentral Perdana 99|Jl. TB Simatupang No. 44|-6.2920|106.8350">DGP-JKT-10030 - Sentral Perdana 99</option>
                            <option value="DGP-JKT-10031|Sahabat Pulsa Mandiri|Jl. Raya Lenteng Agung No. 7|-6.3150|106.8320">DGP-JKT-10031 - Sahabat Pulsa Mandiri</option>
                            <option value="DGP-JKT-10032|Plaza Cellular Tebet|Jl. Tebet Barat No. 88|-6.2350|106.8520">DGP-JKT-10032 - Plaza Cellular Tebet</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Estimasi Jam Kunjungan</label>
                        <input type="text" x-model="newTimePlan" placeholder="Contoh: 15:15 - 15:45" class="w-full text-xs font-medium bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 focus:outline-none">
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" @click="showAddPlanModal = false" class="px-3 py-1.5 bg-slate-100 text-slate-700 font-bold rounded-lg text-xs">
                            Batal
                        </button>
                        <button type="submit" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-lg text-xs">
                            Simpan ke Rute
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
                selectedCustomerToAdd: '',
                newTimePlan: '15:15 - 15:45',

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
                        attribution: '&copy; OpenStreetMap contributors',
                        maxZoom: 19
                    }).addTo(this.map);

                    this.drawStopsAndRoute();
                },

                drawStopsAndRoute() {
                    // Clear existing markers & polyline if any
                    this.markers.forEach(m => this.map.removeLayer(m));
                    this.markers = [];
                    if (this.polyline) {
                        this.map.removeLayer(this.polyline);
                    }

                    const latlngs = [];

                    this.stops.forEach((stop, idx) => {
                        latlngs.push([stop.lat, stop.lng]);

                        let markerBg = '#64748b'; // slate
                        if (stop.status === 'EFFECTIVE_CALL') markerBg = '#059669'; // emerald
                        else if (stop.status === 'VISIT_ONLY') markerBg = '#64748b'; // slate
                        else if (stop.status === 'CLOSED') markerBg = '#e11d48'; // rose

                        const customIcon = L.divIcon({
                            className: 'custom-pjp-marker',
                            html: `
                                <div class="pjp-marker-number" style="background-color: ${markerBg};">
                                    ${stop.stop_no}
                                </div>
                            `,
                            iconSize: [26, 26],
                            iconAnchor: [13, 13],
                            popupAnchor: [0, -14]
                        });

                        const popupHtml = `
                            <div style="font-family: inherit; min-width: 160px; font-size: 11px;">
                                <div style="font-weight: 800; color: #0f172a;">Stop #${stop.stop_no} - ${stop.customer_name}</div>
                                <div style="color: #64748b; margin-top: 2px;">${stop.address}</div>
                                ${stop.omset > 0 ? `
                                    <div style="margin-top: 4px; font-weight: 800; color: #059669; font-size: 11px;">
                                        SO: Rp ${Number(stop.omset).toLocaleString('id-ID')}
                                    </div>
                                ` : ''}
                            </div>
                        `;

                        const marker = L.marker([stop.lat, stop.lng], { icon: customIcon })
                            .addTo(this.map)
                            .bindPopup(popupHtml);

                        this.markers.push(marker);
                    });

                    if (latlngs.length > 1) {
                        this.polyline = L.polyline(latlngs, {
                            color: '#475569',
                            weight: 2.5,
                            opacity: 0.8,
                            dashArray: '5, 6'
                        }).addTo(this.map);

                        this.map.fitBounds(this.polyline.getBounds(), { padding: [30, 30] });
                    }
                },

                submitNewStop() {
                    if (!this.selectedCustomerToAdd) {
                        alert('Silakan pilih toko terlebih dahulu');
                        return;
                    }

                    const parts = this.selectedCustomerToAdd.split('|');
                    const digiposId = parts[0];
                    const name = parts[1];
                    const address = parts[2];
                    const lat = parseFloat(parts[3]) || -6.2841;
                    const lng = parseFloat(parts[4]) || 106.8436;

                    const newStopNo = this.stops.length + 1;
                    const newStop = {
                        stop_no: newStopNo,
                        time_plan: this.newTimePlan || '15:15 - 15:45',
                        customer_name: name,
                        digipos_id: digiposId,
                        no_rs: '0812' + Math.floor(10000000 + Math.random() * 90000000),
                        address: address,
                        pic: 'PIC Toko',
                        status: 'PENDING',
                        checkin_time: null,
                        duration: null,
                        omset: 0,
                        lat: lat,
                        lng: lng,
                        photo_verified: false
                    };

                    this.stops.push(newStop);
                    this.showAddPlanModal = false;
                    this.selectedCustomerToAdd = '';

                    // Refresh tampilan peta & polyline
                    if (this.map) {
                        this.drawStopsAndRoute();
                        this.map.flyTo([lat, lng], 15, { duration: 1 });
                    }

                    if (window.showToast) {
                        window.showToast('Outlet ' + name + ' berhasil ditambahkan ke rute kunjungan!', 'success', 'Rute Ditambahkan');
                    }
                },

                focusMarker(index) {
                    if (this.markers[index] && this.map) {
                        const m = this.markers[index];
                        this.map.flyTo(m.getLatLng(), 16, { duration: 1 });
                        m.openPopup();
                    }
                },

                fitAllMarkers() {
                    if (this.polyline && this.map) {
                        this.map.fitBounds(this.polyline.getBounds(), { padding: [30, 30] });
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
