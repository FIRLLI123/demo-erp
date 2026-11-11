<x-app-layout>
    <x-slot name="title">Master Wilayah Administrasi</x-slot>
    <x-slot name="headerTitle">Master Teritorial & Wilayah Administrasi</x-slot>
    <x-slot name="badge">Geo & Teritorial</x-slot>

    <div class="space-y-6">
        <!-- Header Ringkas -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-cyan-50 text-cyan-700 border border-cyan-200">
                        Geo-Coverage Teritorial
                    </span>
                    <span class="text-xs text-slate-400">Provinsi &bull; Kota/Kabupaten &bull; Kecamatan</span>
                </div>
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Master Wilayah Administrasi Teritorial</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Pemetaan teritori wilayah distribusi resmi Telkomsel di tingkat Provinsi, Kota/Kabupaten, dan Kecamatan.
                </p>
            </div>
            <span class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                Coverage Area: Jabodetabek & Banten
            </span>
        </div>

        <!-- Tabel Teritori -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="map" class="w-4 h-4 text-slate-500"></i>
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Pemetaan Teritorial Kecamatan ke TAP Telkomsel</h3>
                </div>
                <span class="text-xs text-slate-400 font-mono">6 Wilayah Terpetakan</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100/70 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider text-[10px]">
                            <th class="py-3 px-4">Provinsi</th>
                            <th class="py-3 px-4">Kota / Kabupaten</th>
                            <th class="py-3 px-4">Kecamatan</th>
                            <th class="py-3 px-4">TAP Pengampu</th>
                            <th class="py-3 px-4">Cluster Distribusi</th>
                            <th class="py-3 px-4 text-center">Status Jangkauan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 font-semibold text-slate-800">Banten</td>
                            <td class="py-3.5 px-4 font-medium text-slate-900">Kota Tangerang</td>
                            <td class="py-3.5 px-4 font-mono text-slate-800">Tangerang (Cikokol)</td>
                            <td class="py-3.5 px-4 font-bold text-rose-600">TAP Tangerang Kota</td>
                            <td class="py-3.5 px-4 text-slate-600">Cluster Tangerang Barat</td>
                            <td class="py-3.5 px-4 text-center"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">100% COVERED</span></td>
                        </tr>
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 font-semibold text-slate-800">Banten</td>
                            <td class="py-3.5 px-4 font-medium text-slate-900">Kota Tangerang</td>
                            <td class="py-3.5 px-4 font-mono text-slate-800">Cipondoh</td>
                            <td class="py-3.5 px-4 font-bold text-rose-600">TAP Tangerang Kota</td>
                            <td class="py-3.5 px-4 text-slate-600">Cluster Tangerang Barat</td>
                            <td class="py-3.5 px-4 text-center"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">100% COVERED</span></td>
                        </tr>
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 font-semibold text-slate-800">Banten</td>
                            <td class="py-3.5 px-4 font-medium text-slate-900">Kota Tangerang</td>
                            <td class="py-3.5 px-4 font-mono text-slate-800">Pinang</td>
                            <td class="py-3.5 px-4 font-bold text-rose-600">TAP Tangerang Kota</td>
                            <td class="py-3.5 px-4 text-slate-600">Cluster Tangerang Barat</td>
                            <td class="py-3.5 px-4 text-center"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">100% COVERED</span></td>
                        </tr>
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 font-semibold text-slate-800">Banten</td>
                            <td class="py-3.5 px-4 font-medium text-slate-900">Kota Tangerang Selatan</td>
                            <td class="py-3.5 px-4 font-mono text-slate-800">Serpong (BSD City)</td>
                            <td class="py-3.5 px-4 font-bold text-rose-600">TAP Serpong</td>
                            <td class="py-3.5 px-4 text-slate-600">Cluster Tangerang Selatan</td>
                            <td class="py-3.5 px-4 text-center"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">100% COVERED</span></td>
                        </tr>
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 font-semibold text-slate-800">Banten</td>
                            <td class="py-3.5 px-4 font-medium text-slate-900">Kota Tangerang Selatan</td>
                            <td class="py-3.5 px-4 font-mono text-slate-800">Pamulang</td>
                            <td class="py-3.5 px-4 font-bold text-rose-600">TAP Serpong</td>
                            <td class="py-3.5 px-4 text-slate-600">Cluster Tangerang Selatan</td>
                            <td class="py-3.5 px-4 text-center"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">100% COVERED</span></td>
                        </tr>
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 font-semibold text-slate-800">Banten</td>
                            <td class="py-3.5 px-4 font-medium text-slate-900">Kota Tangerang Selatan</td>
                            <td class="py-3.5 px-4 font-mono text-slate-800">Ciputat</td>
                            <td class="py-3.5 px-4 font-bold text-rose-600">TAP Serpong</td>
                            <td class="py-3.5 px-4 text-slate-600">Cluster Tangerang Selatan</td>
                            <td class="py-3.5 px-4 text-center"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">100% COVERED</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

