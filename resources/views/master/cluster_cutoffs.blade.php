@extends('layouts.app')

@section('title', 'Pengaturan Jam Close Sales Order (Cut-Off)')

@section('content')
<div class="space-y-6">

    <!-- Header & Quick Info -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Pengaturan Jam Close SO</h1>
            <p class="text-xs text-slate-500 mt-1">Konfigurasi batas waktu pembuatan pesanan penjualan (Sales Order) per cluster wilayah.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-600">
                <i data-lucide="clock" class="w-4 h-4 text-rose-500"></i>
                <span>Waktu Server: <strong class="font-mono text-slate-900" id="serverClock">{{ date('H:i:s') }}</strong></span>
            </div>
        </div>
    </div>

    <!-- Alert / Flash Message -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden" x-data="clusterCutoffManager()">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/80 text-slate-700 uppercase tracking-wider text-[11px] font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">No</th>
                        <th class="py-3.5 px-4">Area / Wilayah</th>
                        <th class="py-3.5 px-4">Cluster</th>
                        <th class="py-3.5 px-4 text-center">Batas Jam Close</th>
                        <th class="py-3.5 px-4 text-center">Status Hari Ini</th>
                        <th class="py-3.5 px-4 text-center">Fitur Cut-off</th>
                        <th class="py-3.5 px-4">Catatan</th>
                        <th class="py-3.5 px-4 text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($clusters as $idx => $cluster)
                        @php
                            $cutoff = $cluster->cutoff;
                            $st = $cutoffStatuses[$cluster->id] ?? ['is_open' => true, 'status' => 'OPEN', 'reason' => 'Default', 'close_time' => '17:00'];
                            $closeTimeVal = $cutoff ? substr($cutoff->close_time, 0, 5) : '17:00';
                            $isActiveVal = $cutoff ? (bool)$cutoff->is_active : true;
                            $isForceClosedToday = $cutoff && $cutoff->is_force_closed && $cutoff->force_closed_date && $cutoff->force_closed_date->format('Y-m-d') === date('Y-m-d');
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 text-center text-slate-400 font-mono">{{ $idx + 1 }}</td>
                            <td class="py-3.5 px-4 font-semibold text-slate-900">
                                {{ $cluster->area->name ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-800">{{ $cluster->name }}</div>
                                <div class="text-[10px] font-mono text-slate-400">{{ $cluster->code }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-800">
                                <span class="px-2.5 py-1 bg-slate-100 rounded-lg border border-slate-200">
                                    {{ $closeTimeVal }} WIB
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($st['is_open'])
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        OPEN (Buka)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        CLOSED (Tutup)
                                    </span>
                                @endif
                                <div class="text-[10px] text-slate-400 mt-0.5 max-w-[160px] mx-auto truncate" title="{{ $st['reason'] }}">
                                    {{ $st['reason'] }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($isActiveVal)
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        AKTIF
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                        NON-AKTIF
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 text-[11px]">
                                {{ $cutoff->notes ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Tombol Edit Jam & Status -->
                                    <button type="button" 
                                            @click="openEditModal({{ $cluster->id }}, '{{ $cluster->name }}', '{{ $closeTimeVal }}', {{ $isActiveVal ? 'true' : 'false' }}, '{{ addslashes($cutoff->notes ?? '') }}')"
                                            class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors flex items-center gap-1">
                                        <i data-lucide="sliders" class="w-3.5 h-3.5"></i>
                                        <span>Atur</span>
                                    </button>

                                    <!-- Tombol Force Close / Buka Hari Ini -->
                                    <form action="{{ route('master.cluster-cutoffs.toggle-force', $cluster->id) }}" method="POST" class="inline">
                                        @csrf
                                        @if($isForceClosedToday)
                                            <button type="submit" 
                                                    title="Buka kembali order hari ini"
                                                    onclick="return confirm('Buka kembali pesanan untuk {{ $cluster->name }} hari ini?')"
                                                    class="px-2.5 py-1 rounded-lg bg-emerald-100 hover:bg-emerald-200 text-emerald-800 text-xs font-semibold transition-colors flex items-center gap-1">
                                                <i data-lucide="lock-open" class="w-3.5 h-3.5"></i>
                                                <span>Buka</span>
                                            </button>
                                        @else
                                            <button type="submit" 
                                                    title="Tutup order hari ini (otomatis buka besok)"
                                                    onclick="return confirm('Tutup paksa pesanan {{ $cluster->name }} hari ini? Order otomatis dibuka kembali besok.')"
                                                    class="px-2.5 py-1 rounded-lg bg-rose-100 hover:bg-rose-200 text-rose-800 text-xs font-semibold transition-colors flex items-center gap-1">
                                                <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                                                <span>Tutup</span>
                                            </button>
                                        @endif
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">Tidak ada data cluster.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Edit Modal (Tanpa Placeholder, Bersih) -->
        <div x-show="showModal" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="showModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200 transform transition-all">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Atur Jam Close SO</h3>
                        <p class="text-xs text-slate-500 font-medium" x-text="clusterName"></p>
                    </div>
                    <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form :action="formAction" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jam Close (WIB)</label>
                        <input type="time" name="close_time" x-model="closeTime" required 
                               class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan / Alasan</label>
                        <input type="text" name="notes" x-model="notes" 
                               class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>

                    <div class="pt-2">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" x-model="isActive" class="w-4 h-4 text-rose-600 rounded border-slate-300 focus:ring-rose-500">
                            <span class="text-xs font-semibold text-slate-700">Aktifkan Pembatasan Jam Close</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                        <button type="button" @click="showModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-colors shadow-xs">
                            Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function clusterCutoffManager() {
        return {
            showModal: false,
            clusterId: null,
            clusterName: '',
            closeTime: '17:00',
            isActive: true,
            notes: '',
            formAction: '',

            openEditModal(id, name, time, active, notes) {
                this.clusterId = id;
                this.clusterName = name;
                this.closeTime = time;
                this.isActive = active;
                this.notes = notes;
                this.formAction = "{{ url('master/cluster-cutoffs') }}/" + id;
                this.showModal = true;
            }
        };
    }

    // Live Server Clock incrementer
    setInterval(function() {
        const el = document.getElementById('serverClock');
        if (el) {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            el.textContent = h + ':' + m + ':' + s;
        }
    }, 1000);
</script>
@endpush
@endsection
