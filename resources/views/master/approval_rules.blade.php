<x-app-layout>
    <x-slot name="title">Matriks Otorisasi Persetujuan</x-slot>
    <x-slot name="headerTitle">Master Matriks & Aturan Otorisasi Persetujuan</x-slot>
    <x-slot name="badge">Governance & Approval</x-slot>

    <div class="space-y-6">
        <!-- Header Ringkas -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                        Governance & Kontrol Internal
                    </span>
                    <span class="text-xs text-slate-400">Delegasi Wewenang & Batas Otorisasi Transaksi</span>
                </div>
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Matriks Otorisasi Persetujuan (Approval Matrix)</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Konfigurasi jenjang kewenangan persetujuan Sales Order kredit, kenaikan limit plafon toko, dan Purchase Order.
                </p>
            </div>
            <span class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                SOP Otorisasi ERP Aktif
            </span>
        </div>

        <!-- Tabel Matriks -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="shield" class="w-4 h-4 text-slate-500"></i>
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Jenjang Batas Nilai Otorisasi (Limit Matrix)</h3>
                </div>
                <span class="text-xs text-slate-400 font-mono">4 Level Otoritas</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100/70 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider text-[10px]">
                            <th class="py-3 px-4">Jenis Transaksi / Dokumen</th>
                            <th class="py-3 px-4">Batas Nilai Nominal (Rp)</th>
                            <th class="py-3 px-4">Kondisi / Pemicu Persetujuan</th>
                            <th class="py-3 px-4">Pejabat Berwenang (Otorisator)</th>
                            <th class="py-3 px-4 text-center">SLA Waktu</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-slate-900">Sales Order (SO Kredit)</td>
                            <td class="py-3.5 px-4 font-mono font-semibold text-slate-800">s/d Rp 5.000.000</td>
                            <td class="py-3.5 px-4 text-slate-600">Tempo pembayaran 7 hari standar</td>
                            <td class="py-3.5 px-4 font-semibold text-blue-700">Supervisor Sales Force</td>
                            <td class="py-3.5 px-4 text-center font-mono">Max. 2 Jam</td>
                            <td class="py-3.5 px-4 text-center"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">AKTIF</span></td>
                        </tr>
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-slate-900">Sales Order (SO Kredit Besar)</td>
                            <td class="py-3.5 px-4 font-mono font-semibold text-slate-800">&gt; Rp 5.000.000 s/d Rp 25.000.000</td>
                            <td class="py-3.5 px-4 text-slate-600">Tempo 14 hari atau melampaui sisa plafon</td>
                            <td class="py-3.5 px-4 font-semibold text-purple-700">Finance & Commercial Manager</td>
                            <td class="py-3.5 px-4 text-center font-mono">Max. 4 Jam</td>
                            <td class="py-3.5 px-4 text-center"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">AKTIF</span></td>
                        </tr>
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-slate-900">Kenaikan Plafon Kredit Toko</td>
                            <td class="py-3.5 px-4 font-mono font-semibold text-slate-800">&gt; Rp 20.000.000</td>
                            <td class="py-3.5 px-4 text-slate-600">Permohonan kenaikan limit kredit outlet</td>
                            <td class="py-3.5 px-4 font-semibold text-rose-700">Finance Head & Branch Manager</td>
                            <td class="py-3.5 px-4 text-center font-mono">Max. 1 Hari Kerja</td>
                            <td class="py-3.5 px-4 text-center"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">AKTIF</span></td>
                        </tr>
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-slate-900">Purchase Order (PO Principal)</td>
                            <td class="py-3.5 px-4 font-mono font-semibold text-slate-800">&gt; Rp 50.000.000</td>
                            <td class="py-3.5 px-4 text-slate-600">Pengadaan buffer stok serial perdana Telkomsel</td>
                            <td class="py-3.5 px-4 font-semibold text-rose-700">Direktur Operasional / Direksi</td>
                            <td class="py-3.5 px-4 text-center font-mono">Max. 1 Hari Kerja</td>
                            <td class="py-3.5 px-4 text-center"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">AKTIF</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
