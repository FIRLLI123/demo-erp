<x-app-layout>
    <x-slot name="title">Klasifikasi Segmen Outlet</x-slot>
    <x-slot name="headerTitle">Master Klasifikasi & Segmen Outlet</x-slot>
    <x-slot name="badge">Sales & Channeling</x-slot>

    <div class="space-y-6">
        <!-- Header Ringkas -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                        Segmentasi Channeling
                    </span>
                    <span class="text-xs text-slate-400">Pengelompokan Jenis Toko Mitra</span>
                </div>
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Klasifikasi & Segmen Outlet (RS / BOS / SD)</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Definisi klasifikasi channel distribusi mitra: Retail Seller (RS), Bakul Kuota/Grosir (BOS), dan Sub-Distributor (SD).
                </p>
            </div>
            <span class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                Standar Telco Channel Matrix
            </span>
        </div>

        <!-- 3 Segment Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <!-- RS -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs space-y-3">
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-1 rounded-md text-xs font-extrabold bg-blue-50 text-blue-700 border border-blue-200">
                        RETAIL SELLER (RS)
                    </span>
                    <span class="text-xs font-bold text-slate-400">Tier Retail</span>
                </div>
                <h4 class="text-base font-bold text-slate-900">Konter Pulsa & Toko Kelontong</h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Outlet fisik berskala retail yang menjual langsung kartu perdana dan voucher fisik ke konsumen akhir (*end-user*).
                </p>
                <div class="pt-3 border-t border-slate-100 text-xs space-y-1.5 text-slate-600">
                    <div class="flex justify-between"><span>Minimum Order:</span><strong class="text-slate-900">1 Pcs</strong></div>
                    <div class="flex justify-between"><span>Plafon Kredit Maks:</span><strong class="text-slate-900">Rp 10.000.000</strong></div>
                    <div class="flex justify-between"><span>Tenor Tempo:</span><strong class="text-slate-900">7 Hari</strong></div>
                </div>
            </div>

            <!-- BOS -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs space-y-3">
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-1 rounded-md text-xs font-extrabold bg-purple-50 text-purple-700 border border-purple-200">
                        BAKUL / GROSIR (BOS)
                    </span>
                    <span class="text-xs font-bold text-slate-400">Tier Grosir</span>
                </div>
                <h4 class="text-base font-bold text-slate-900">Bakul Kuota & Grosir Wilayah</h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Pusat grosir atau bakul voucher yang mensuplai puluhan konter kecil di sekitarnya dengan perputaran stok sangat cepat.
                </p>
                <div class="pt-3 border-t border-slate-100 text-xs space-y-1.5 text-slate-600">
                    <div class="flex justify-between"><span>Minimum Order:</span><strong class="text-slate-900">50 Pcs</strong></div>
                    <div class="flex justify-between"><span>Plafon Kredit Maks:</span><strong class="text-slate-900">Rp 50.000.000</strong></div>
                    <div class="flex justify-between"><span>Tenor Tempo:</span><strong class="text-slate-900">14 Hari</strong></div>
                </div>
            </div>

            <!-- SD -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs space-y-3">
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-1 rounded-md text-xs font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        SUB-DISTRIBUTOR (SD)
                    </span>
                    <span class="text-xs font-bold text-slate-400">Tier Distributor</span>
                </div>
                <h4 class="text-base font-bold text-slate-900">Mitra Sub-Distribusi Cluster</h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Mitra strategis perpanjangan tangan distributor resmi yang memegang buffer stok dan pengantaran di sub-area terpencil.
                </p>
                <div class="pt-3 border-t border-slate-100 text-xs space-y-1.5 text-slate-600">
                    <div class="flex justify-between"><span>Minimum Order:</span><strong class="text-slate-900">250 Pcs</strong></div>
                    <div class="flex justify-between"><span>Plafon Kredit Maks:</span><strong class="text-slate-900">Rp 150.000.000</strong></div>
                    <div class="flex justify-between"><span>Tenor Tempo:</span><strong class="text-slate-900">21 Hari</strong></div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

