<x-app-layout>
    <x-slot name="title">Sales Order & Goods Delivery</x-slot>
    <x-slot name="headerTitle">Proses Penjualan & Pengiriman</x-slot>
    <x-slot name="badge">DocType: SO & GD</x-slot>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between">
            <span class="flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                {{ session('success') }}
            </span>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800">&times;</button>
        </div>
    @endif

    <!-- Process Workflow Banner -->
    <div class="mb-8 p-5 bg-white rounded-2xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 uppercase font-mono">
                Alur Penjualan 2: SO &rarr; Approval Finance &rarr; GD &rarr; AR
            </span>
            <h2 class="text-base font-bold text-slate-900 mt-1">Pesanan Penjualan dari Web Admin & Mobile Sales</h2>
            <p class="text-xs text-slate-500 mt-0.5">Setelah SO disetujui, Gudang menerbitkan Surat Jalan (GD) dan sistem otomatis membentuk Piutang (AR).</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-mono font-bold">SO</span>
            <i data-lucide="arrow-right" class="w-4 h-4 text-slate-400"></i>
            <span class="px-3 py-1.5 rounded-xl bg-cyan-700 text-white text-xs font-mono font-bold">GD</span>
            <i data-lucide="arrow-right" class="w-4 h-4 text-slate-400"></i>
            <span class="px-3 py-1.5 rounded-xl bg-rose-600 text-white text-xs font-mono font-bold">AR</span>
        </div>
    </div>

    <!-- Tabbed Sales -->
    <div x-data="{ activeTab: 'so' }" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="border-b border-slate-200/80 px-6 pt-4 flex gap-6 text-xs font-bold">
            <button @click="activeTab = 'so'" :class="activeTab === 'so' ? 'text-rose-600 border-b-2 border-rose-600 pb-3 font-extrabold' : 'text-slate-500 hover:text-slate-800 pb-3'">
                1. Daftar Sales Order (SO) ({{ count($orders) }})
            </button>
            <button @click="activeTab = 'gd'" :class="activeTab === 'gd' ? 'text-rose-600 border-b-2 border-rose-600 pb-3 font-extrabold' : 'text-slate-500 hover:text-slate-800 pb-3'">
                2. Daftar Surat Jalan (GD) ({{ count($deliveries) }})
            </button>
        </div>

        <!-- TAB: Sales Orders -->
        <div x-show="activeTab === 'so'" class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3.5 px-4">DocType & No. SO</th>
                            <th class="py-3.5 px-4">Channel Input</th>
                            <th class="py-3.5 px-4">Outlet / Customer</th>
                            <th class="py-3.5 px-4">Sales & Gudang</th>
                            <th class="py-3.5 px-4 text-right">Nilai Tagihan</th>
                            <th class="py-3.5 px-4">Status Transaksi</th>
                            <th class="py-3.5 px-4 text-center">Aksi Workflow Demo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($orders as $so)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-4 px-4 font-mono font-bold text-slate-900">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] bg-slate-900 text-white mr-1">{{ $so->doc_type }}</span>
                                    {{ $so->doc_number }}
                                    <span class="block text-[10px] text-slate-400 font-sans mt-0.5">{{ date('d M Y', strtotime($so->order_date)) }}</span>
                                </td>
                                <td class="py-4 px-4">
                                    @if($so->channel === 'MOBILE_SALES')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i data-lucide="smartphone" class="w-3 h-3 text-emerald-600"></i> Mobile Sales
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            <i data-lucide="monitor" class="w-3 h-3 text-indigo-600"></i> Web Admin
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 font-bold text-slate-900">{{ $so->customer_name }}</td>
                                <td class="py-4 px-4">
                                    <span class="font-semibold text-slate-800 block">{{ $so->sales_name }}</span>
                                    <span class="text-[10px] text-slate-500 font-mono">{{ $so->warehouse_name }}</span>
                                </td>
                                <td class="py-4 px-4 text-right font-mono font-bold text-slate-900">
                                    Rp {{ number_format($so->grand_total, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-4">
                                    @if($so->status === 'PENDING_APPROVAL')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 animate-pulse">
                                            PENDING APPROVAL
                                        </span>
                                    @elseif($so->status === 'APPROVED')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">
                                            APPROVED
                                        </span>
                                    @elseif($so->status === 'DELIVERED')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-cyan-100 text-cyan-800">
                                            DELIVERED (GD Aktif)
                                        </span>
                                    @elseif($so->status === 'COMPLETED')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            COMPLETED (Lunas)
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-center">
                                    @if($so->status === 'PENDING_APPROVAL')
                                        <form method="POST" action="{{ route('sales.approve', $so->id) }}">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm flex items-center gap-1 mx-auto">
                                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                                <span>Setujui (Finance)</span>
                                            </button>
                                        </form>
                                    @elseif($so->status === 'APPROVED')
                                        <form method="POST" action="{{ route('sales.deliver', $so->id) }}">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm flex items-center gap-1 mx-auto">
                                                <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                                                <span>Kirim Barang (GD)</span>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-400 font-semibold flex items-center justify-center gap-1">
                                            <i data-lucide="check-check" class="w-3.5 h-3.5 text-emerald-600"></i> GD & AR Terbit
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB: Goods Deliveries -->
        <div x-show="activeTab === 'gd'" class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3.5 px-4">DocType & Nomor GD</th>
                            <th class="py-3.5 px-4">Ref. Nomor SO</th>
                            <th class="py-3.5 px-4">Toko Tujuan</th>
                            <th class="py-3.5 px-4">Gudang Pengirim</th>
                            <th class="py-3.5 px-4">Tanggal Kirim</th>
                            <th class="py-3.5 px-4">Penerima Barang</th>
                            <th class="py-3.5 px-4">Status Pengiriman</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($deliveries as $gd)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-4 px-4 font-mono font-bold text-slate-900">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] bg-cyan-700 text-white mr-1">{{ $gd->doc_type }}</span>
                                    {{ $gd->doc_number }}
                                </td>
                                <td class="py-4 px-4 font-mono font-bold text-slate-600">{{ $gd->ref_doc_number }}</td>
                                <td class="py-4 px-4 font-bold text-slate-800">{{ $gd->customer_name }}</td>
                                <td class="py-4 px-4 text-slate-600">{{ $gd->warehouse_name }}</td>
                                <td class="py-4 px-4 text-slate-500">{{ date('d M Y', strtotime($gd->delivery_date)) }}</td>
                                <td class="py-4 px-4 text-slate-800 font-semibold">{{ $gd->recipient_name }}</td>
                                <td class="py-4 px-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        {{ $gd->status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

