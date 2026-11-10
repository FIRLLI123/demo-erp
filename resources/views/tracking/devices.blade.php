<x-app-layout>
    <x-slot name="title">Informasi Device Mobile Sales Force</x-slot>
    <x-slot name="headerTitle">Telemetri & Device Mobile Sales</x-slot>
    <x-slot name="badge">Mobile Device Management (MDM)</x-slot>

    <div>
        <!-- Top Action & Navigation Banner -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <i data-lucide="smartphone" class="w-5 h-5 text-indigo-600"></i>
                    <span>Informasi & Audit Perangkat Mobile Sales (MDM)</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-purple-100 text-purple-800 border border-purple-200">PT WAHANA PUTRA YUDHA</span>
                </h2>
                <p class="text-xs text-slate-500">Telemetri spesifikasi smartphone kanvaser, versi sistem operasi Android, versi build APK ERP Mobile, integrasi DigiPOS, serta audit kepatuhan anti-fake GPS</p>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 text-indigo-700 border border-indigo-200 text-xs font-bold shadow-2xs">
                    <i data-lucide="shield-check" class="w-4 h-4 text-indigo-600"></i>
                    <span>MDM Security Audit Active</span>
                </span>
            </div>
        </div>

        <!-- KPI Metrics Summary -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 block mb-1">Total Smartphone Terdaftar</span>
                    <span class="text-2xl font-extrabold text-slate-900">{{ count($devices) }} Unit</span>
                    <span class="text-[11px] text-slate-500 block mt-1">Dedicated Sales Mobile</span>
                </div>
                <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
                    <i data-lucide="smartphone" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 block mb-1">Versi APK ERP Terbaru</span>
                    <span class="text-2xl font-extrabold text-indigo-600 font-mono">v2.4.1</span>
                    <span class="text-[11px] text-emerald-600 font-semibold block mt-1">Semua Device Up to Date</span>
                </div>
                <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <i data-lucide="layers" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 block mb-1">Audit Keamanan Device</span>
                    <span class="text-2xl font-extrabold text-emerald-600">100%</span>
                    <span class="text-[11px] text-emerald-600/90 font-semibold block mt-1">Non-Root & No Mock GPS</span>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="shield-alert" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 block mb-1">Sistem Operasi Rata-rata</span>
                    <span class="text-2xl font-extrabold text-slate-900 font-mono">Android 14</span>
                    <span class="text-[11px] text-slate-500 block mt-1">Google Play Protect Aktif</span>
                </div>
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="cpu" class="w-5 h-5"></i>
                </div>
            </div>
        </div>

        <!-- Detail Table: Mobile Device Inventory -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <span class="text-xs font-extrabold text-slate-800">Daftar Spesifikasi Smartphone & Versi Aplikasi Sales Force</span>
                <span class="text-xs text-slate-500 font-mono">{{ count($devices) }} Device Terhubung</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Sales Force & TAP</th>
                            <th class="py-3 px-4">Model Smartphone</th>
                            <th class="py-3 px-4">Sistem Operasi (OS)</th>
                            <th class="py-3 px-4">Versi App ERP & DigiPOS</th>
                            <th class="py-3 px-4">Instalasi & Sync Terakhir</th>
                            <th class="py-3 px-4">Hardware (RAM/Storage)</th>
                            <th class="py-3 px-4 text-center">Status Keamanan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($devices as $d)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <!-- Sales Info -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-slate-800 to-slate-700 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                            {{ strtoupper(substr($d->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <span class="font-extrabold text-slate-900 block text-xs">{{ $d->name }}</span>
                                            <span class="text-[10px] text-slate-500 font-mono">{{ $d->code }} &bull; TAP {{ $d->tap_name }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Device Model -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2">
                                        <div class="p-1 rounded-md bg-slate-100 text-slate-600">
                                            <i data-lucide="smartphone" class="w-4 h-4"></i>
                                        </div>
                                        <div>
                                            <span class="font-bold text-slate-800 block text-xs">{{ $d->device_model }}</span>
                                            <span class="text-[10px] text-slate-400 font-mono">IMEI: {{ $d->imei }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- OS & Android -->
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200 block max-w-fit mb-0.5">
                                        Android {{ $d->android_version }}
                                    </span>
                                    <span class="text-[11px] text-slate-500">{{ $d->os_name }}</span>
                                </td>

                                <!-- App Versions -->
                                <td class="py-3.5 px-4">
                                    <div class="space-y-0.5">
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span class="font-mono font-bold text-slate-800 text-[11px]">ERP: {{ $d->app_version }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-slate-500 text-[10px]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            <span class="font-mono">{{ $d->digipos_version }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Install & Sync Date -->
                                <td class="py-3.5 px-4">
                                    <span class="text-xs font-semibold text-slate-800 block">{{ $d->last_sync }}</span>
                                    <span class="text-[10px] text-slate-400 block">Tgl Install: {{ $d->installed_at }}</span>
                                </td>

                                <!-- Hardware Specs -->
                                <td class="py-3.5 px-4">
                                    <div class="text-[11px]">
                                        <span class="font-semibold text-slate-700">RAM: {{ $d->ram }}</span>
                                        <span class="text-slate-400 block text-[10px]">Storage: {{ $d->storage_used }}</span>
                                        <span class="flex items-center gap-1 text-[10px] text-emerald-600 font-bold mt-0.5">
                                            <i data-lucide="battery" class="w-3 h-3"></i> Baterai {{ $d->battery_level }}%
                                        </span>
                                    </div>
                                </td>

                                <!-- Security Compliance -->
                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200 inline-flex items-center gap-1">
                                        <i data-lucide="shield-check" class="w-3 h-3 text-emerald-600"></i>
                                        <span>SECURE</span>
                                    </span>
                                    <span class="text-[9px] text-slate-400 block mt-1">Anti-Fake GPS OK</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>

