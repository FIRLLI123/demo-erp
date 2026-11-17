@props([
    'action' => null,
    'startDateName' => 'start_date',
    'endDateName' => 'end_date',
    'startDate' => null,
    'endDate' => null,
    'label' => 'Periode Transaksi',
])

@php
    $start = $startDate ?? request($startDateName, '');
    $end = $endDate ?? request($endDateName, '');
    $targetUrl = $action ?? request()->url();
    $hasDateFilter = !empty($start) || !empty($end);
@endphp

<div x-data="{
        startDate: '{{ $start }}',
        endDate: '{{ $end }}',
        formatDate(d) {
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        setPreset(type) {
            const now = new Date();
            if (type === 'today') {
                const todayStr = this.formatDate(now);
                this.startDate = todayStr;
                this.endDate = todayStr;
            } else if (type === 'yesterday') {
                const y = new Date();
                y.setDate(y.getDate() - 1);
                const yStr = this.formatDate(y);
                this.startDate = yStr;
                this.endDate = yStr;
            } else if (type === '7days') {
                const past = new Date();
                past.setDate(past.getDate() - 6);
                this.startDate = this.formatDate(past);
                this.endDate = this.formatDate(now);
            } else if (type === 'this_month') {
                const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
                const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                this.startDate = this.formatDate(firstDay);
                this.endDate = this.formatDate(lastDay);
            }
            this.$nextTick(() => {
                this.$refs.dateForm.submit();
            });
        }
     }" 
     class="bg-slate-50/80 border border-slate-200/90 rounded-2xl p-3 shadow-2xs">
    
    <form x-ref="dateForm" method="GET" action="{{ $targetUrl }}" class="flex flex-wrap items-center justify-between gap-3" data-no-loading>
        <!-- Preserve Query String Lain yang Sedang Aktif (seperti tab, area_id, status dll) -->
        @foreach(request()->query() as $key => $val)
            @if($key !== $startDateName && $key !== $endDateName && !is_array($val))
                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
            @endif
        @endforeach

        <div class="flex flex-wrap items-center gap-2">
            <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 flex items-center gap-1.5 mr-1">
                <i data-lucide="calendar" class="w-3.5 h-3.5 text-rose-600"></i>
                <span>{{ $label }}:</span>
            </span>

            <!-- Input Tanggal Dari -->
            <div class="relative">
                <input type="date" 
                       name="{{ $startDateName }}" 
                       x-model="startDate" 
                       class="text-xs font-bold bg-white text-slate-800 border border-slate-200 rounded-xl px-2.5 py-1.5 focus:outline-none focus:border-rose-500 cursor-pointer shadow-2xs">
            </div>

            <span class="text-xs text-slate-400 font-bold">s/d</span>

            <!-- Input Tanggal Sampai -->
            <div class="relative">
                <input type="date" 
                       name="{{ $endDateName }}" 
                       x-model="endDate" 
                       class="text-xs font-bold bg-white text-slate-800 border border-slate-200 rounded-xl px-2.5 py-1.5 focus:outline-none focus:border-rose-500 cursor-pointer shadow-2xs">
            </div>

            <!-- Tombol Terapkan Filter Tanggal -->
            <button type="submit" 
                    class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition-all shadow-xs flex items-center gap-1 cursor-pointer">
                <i data-lucide="search" class="w-3 h-3"></i>
                <span>Filter</span>
            </button>

            <!-- Tombol Reset Tanggal -->
            @if($hasDateFilter)
                <a href="{{ request()->fullUrlWithQuery([$startDateName => null, $endDateName => null]) }}" 
                   class="px-2.5 py-1.5 rounded-xl bg-slate-200 hover:bg-rose-100 hover:text-rose-700 text-slate-600 font-bold text-xs transition-all shadow-2xs cursor-pointer flex items-center gap-1"
                   title="Hapus filter tanggal">
                    <i data-lucide="x" class="w-3 h-3"></i>
                    <span>Reset</span>
                </a>
            @endif
        </div>

        <!-- Tombol Preset Cepat (Pill Presets) -->
        <div class="flex items-center gap-1 bg-white p-1 rounded-xl border border-slate-200 shadow-2xs text-[11px] font-bold">
            <button type="button" @click="setPreset('today')" class="px-2 py-1 rounded-lg text-slate-600 hover:text-rose-600 hover:bg-slate-50 transition cursor-pointer">Hari Ini</button>
            <button type="button" @click="setPreset('yesterday')" class="px-2 py-1 rounded-lg text-slate-600 hover:text-rose-600 hover:bg-slate-50 transition cursor-pointer">Kemarin</button>
            <button type="button" @click="setPreset('7days')" class="px-2 py-1 rounded-lg text-slate-600 hover:text-rose-600 hover:bg-slate-50 transition cursor-pointer">7 Hari</button>
            <button type="button" @click="setPreset('this_month')" class="px-2 py-1 rounded-lg text-slate-600 hover:text-rose-600 hover:bg-slate-50 transition cursor-pointer">Bulan Ini</button>
        </div>
    </form>
</div>
