<x-app-layout>
    <x-slot name="title">Master Vendor & Supplier</x-slot>
    <x-slot name="headerTitle">Master Vendor & Mitra Pengadaan</x-slot>
    <x-slot name="badge">Pengadaan & Logistik</x-slot>

    <div class="space-y-6" x-data="{ modalOpen: false }">

        <!-- Header Ringkas -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Pengadaan & Principal
                    </span>
                    <span class="text-xs text-slate-400">Pemasok Resmi & Rekanan Bisnis</span>
                </div>
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Master Vendor & Supplier</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Data principal telekomunikasi (Telkomsel), percetakan voucher fisik, dan mitra logistik pengadaan.
                </p>
            </div>
            <div>
                <button type="button" @click="modalOpen = true" 
                        class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-rose-600 text-white hover:bg-rose-700 transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Tambah Vendor Baru</span>
                </button>
            </div>
        </div>

        <!-- 4 KPI Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Total Mitra Terdaftar</p>
                    <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ $kpi['total_vendors'] }} <span class="text-xs font-normal text-slate-400">vendor</span></h3>
                    <p class="text-[11px] text-slate-400 mt-1">Principal & Supplier</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0 border border-slate-200">
                    <i data-lucide="building-2" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Status Aktif Berjalan</p>
                    <h3 class="text-2xl font-bold text-emerald-600 mt-1">{{ $kpi['active_vendors'] }} <span class="text-xs font-normal text-slate-400">rekanan</span></h3>
                    <p class="text-[11px] text-emerald-600 font-semibold mt-1">Siap untuk PO</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Principal Operator</p>
                    <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ $kpi['principal_count'] }} <span class="text-xs font-normal text-slate-400">mitra</span></h3>
                    <p class="text-[11px] text-slate-400 mt-1">PT Telekomunikasi Selular</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 border border-rose-100">
                    <i data-lucide="radio" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Vendor Penunjang</p>
                    <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ $kpi['supplies_count'] }} <span class="text-xs font-normal text-slate-400">vendor</span></h3>
                    <p class="text-[11px] text-slate-400 mt-1">Percetakan & Logistik</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 border border-purple-100">
                    <i data-lucide="truck" class="w-5 h-5"></i>
                </div>
            </div>
        </div>

        <!-- Tabel Vendor -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="list" class="w-4 h-4 text-slate-500"></i>
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Daftar Vendor & Rekanan Pengadaan</h3>
                </div>
                <span class="text-xs text-slate-400 font-mono">{{ $vendors->count() }} Pemasok</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100/70 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider text-[10px]">
                            <th class="py-3 px-4">Kode Vendor</th>
                            <th class="py-3 px-4">Nama Perusahaan Vendor</th>
                            <th class="py-3 px-4">Kategori Bidang</th>
                            <th class="py-3 px-4">PIC Kontak & Telepon</th>
                            <th class="py-3 px-4">Alamat Kantor</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($vendors as $v)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-800">{{ $v->code }}</td>
                                <td class="py-3.5 px-4 font-semibold text-slate-900">
                                    {{ $v->name }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($v->category === 'PRINCIPAL_TELCO')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            PRINCIPAL OPERATOR
                                        </span>
                                    @elseif($v->category === 'PRINTING_VOUCHER')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                            PERCETAKAN VOUCHER
                                        </span>
                                    @elseif($v->category === 'MERCHANDISE_POSM')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                            POSM & MERCHANDISE
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                                            {{ $v->category }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="block font-medium text-slate-800">{{ $v->contact_person ?: '-' }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $v->phone ?: '-' }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 max-w-xs truncate" title="{{ $v->address }}">
                                    {{ $v->address ?: '-' }}
                                </td>
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

        <!-- Modal Tambah Vendor -->
        <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-md w-full p-6 text-left" @click.away="modalOpen = false">
                <form action="{{ route('master.vendors.store') }}" method="POST" data-no-loading>
                    @csrf
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                        <h4 class="text-base font-bold text-slate-900">Registrasi Vendor Baru</h4>
                        <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Nama Perusahaan / Supplier *</label>
                            <input type="text" name="name" placeholder="Misal: PT Telkomsel atau CV Percetakan" required class="w-full border border-slate-200 rounded-lg p-2.5">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Kategori Pengadaan *</label>
                            <select name="category" class="w-full border border-slate-200 rounded-lg p-2.5 focus:outline-none focus:ring-1 focus:ring-slate-400">
                                <option value="PRINCIPAL_TELCO">Principal Operator Telekomunikasi</option>
                                <option value="PRINTING_VOUCHER">Percetakan Voucher Fisik</option>
                                <option value="MERCHANDISE_POSM">Merchandise & POSM Branding</option>
                                <option value="LOGISTIK_ARMADA">Logistik & Ekspedisi Transportasi</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Nama PIC Kontak</label>
                            <input type="text" name="contact_person" placeholder="Nama penanggung jawab vendor" class="w-full border border-slate-200 rounded-lg p-2.5">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Telepon / WhatsApp</label>
                                <input type="text" name="phone" placeholder="0812-xxxx-xxxx" class="w-full border border-slate-200 rounded-lg p-2.5">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Email Resmi</label>
                                <input type="email" name="email" placeholder="sales@vendor.com" class="w-full border border-slate-200 rounded-lg p-2.5">
                            </div>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Alamat Kantor / Pabrik</label>
                            <textarea name="address" rows="2" placeholder="Alamat lengkap supplier..." class="w-full border border-slate-200 rounded-lg p-2.5"></textarea>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100 mt-4">
                        <button type="button" @click="modalOpen = false" class="px-4 py-2 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-lg text-xs font-semibold bg-rose-600 text-white hover:bg-rose-700">Simpan Vendor</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>

