<x-app-layout>
    <x-slot name="title">Master Skema Harga</x-slot>
    <x-slot name="headerTitle">Master Skema Harga & Tiering Outlet</x-slot>
    <x-slot name="badge">Commercial & Pricing</x-slot>

    <div class="space-y-6" x-data="{ modalOpen: false }">

        <!-- Header Ringkas -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                        Strategi Penjualan & Tiering
                    </span>
                    <span class="text-xs text-slate-400">Price Profile Outlet (RS, BOS, SD)</span>
                </div>
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Master Skema Harga (Price Profile)</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Konfigurasi tingkatan harga jual dan diskon volume per segmen toko: Retail Counter (RS), Grosir/Bakul (BOS), dan Sub-Distributor (SD).
                </p>
            </div>
            <div>
                <button type="button" @click="modalOpen = true" 
                        class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-rose-600 text-white hover:bg-rose-700 transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Tambah Skema Tier Baru</span>
                </button>
            </div>
        </div>

        <!-- 4 KPI Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Total Skema Tier</p>
                    <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ $kpi['total_profiles'] }} <span class="text-xs font-normal text-slate-400">skema</span></h3>
                    <p class="text-[11px] text-slate-400 mt-1">RS, BOS & SD</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-100">
                    <i data-lucide="tags" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Tier Aktif Berjalan</p>
                    <h3 class="text-2xl font-bold text-emerald-600 mt-1">{{ $kpi['active_profiles'] }} <span class="text-xs font-normal text-slate-400">skema</span></h3>
                    <p class="text-[11px] text-emerald-600 font-semibold mt-1">Diterapkan ke Sales Order</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100">
                    <i data-lucide="check-check" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Outlet Terhubung</p>
                    <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ $kpi['rs_count'] }} <span class="text-xs font-normal text-slate-400">outlet</span></h3>
                    <p class="text-[11px] text-slate-400 mt-1">Terpetakan ke master toko</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100">
                    <i data-lucide="store" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Maksimum Diskon Tier</p>
                    <h3 class="text-2xl font-bold text-rose-600 mt-1">4.50%</h3>
                    <p class="text-[11px] text-slate-400 mt-1">Tier Sub-Distributor (SD)</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 border border-rose-100">
                    <i data-lucide="percent" class="w-5 h-5"></i>
                </div>
            </div>
        </div>

        <!-- Tabel Skema Harga -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="list" class="w-4 h-4 text-slate-500"></i>
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Tabel Konfigurasi Tier Harga Jual</h3>
                </div>
                <span class="text-xs text-slate-400 font-mono">{{ $profiles->count() }} Tingkatan Harga</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100/70 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider text-[10px]">
                            <th class="py-3 px-4">Kode Skema</th>
                            <th class="py-3 px-4">Nama Profil Harga</th>
                            <th class="py-3 px-4 text-center">Segmen Toko</th>
                            <th class="py-3 px-4 text-center">Diskon Khusus (%)</th>
                            <th class="py-3 px-4 text-center">Min. Volume Order</th>
                            <th class="py-3 px-4">Keterangan / Target Segmen</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($profiles as $p)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-800">{{ $p->code }}</td>
                                <td class="py-3.5 px-4 font-semibold text-slate-900">{{ $p->name }}</td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($p->segment === 'RS')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                            RETAIL SELLER (RS)
                                        </span>
                                    @elseif($p->segment === 'BOS')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                            GROSIR / BAKUL (BOS)
                                        </span>
                                    @elseif($p->segment === 'SD')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            SUB-DISTRIBUTOR (SD)
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold font-mono text-slate-800">
                                    {{ number_format($p->discount_percentage, 2) }}%
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono font-semibold text-slate-700">
                                    {{ number_format($p->min_order_qty, 0, ',', '.') }} pcs
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 max-w-xs">{{ $p->description }}</td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        AKTIF
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Tambah Tier Harga -->
        <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-md w-full p-6 text-left" @click.away="modalOpen = false">
                <form action="{{ route('master.price-profiles.store') }}" method="POST" data-no-loading>
                    @csrf
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                        <h4 class="text-base font-bold text-slate-900">Tambah Skema Harga (Tier)</h4>
                        <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Kode Tier *</label>
                            <input type="text" name="code" placeholder="Misal: PRC-VIP" required class="w-full border border-slate-200 rounded-lg p-2.5 font-mono uppercase">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Nama Profil Harga *</label>
                            <input type="text" name="name" placeholder="Misal: Tier Grosir Super VIP" required class="w-full border border-slate-200 rounded-lg p-2.5">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Target Segmen Toko *</label>
                            <select name="segment" class="w-full border border-slate-200 rounded-lg p-2.5 focus:outline-none focus:ring-1 focus:ring-slate-400">
                                <option value="RS">Retail Seller (RS) - Konter Reguler</option>
                                <option value="BOS">Grosir / Bakul Kuota (BOS)</option>
                                <option value="SD">Sub-Distributor Wilayah (SD)</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Diskon Tambahan (%) *</label>
                                <input type="number" step="0.01" name="discount_percentage" value="0.00" min="0" max="100" required class="w-full border border-slate-200 rounded-lg p-2.5">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Minimal Order (Pcs) *</label>
                                <input type="number" name="min_order_qty" value="1" min="1" required class="w-full border border-slate-200 rounded-lg p-2.5">
                            </div>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Keterangan Skema</label>
                            <textarea name="description" rows="2" placeholder="Catatan syarat ketentuan tier harga..." class="w-full border border-slate-200 rounded-lg p-2.5"></textarea>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100 mt-4">
                        <button type="button" @click="modalOpen = false" class="px-4 py-2 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-lg text-xs font-semibold bg-rose-600 text-white hover:bg-rose-700">Simpan Tier</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
