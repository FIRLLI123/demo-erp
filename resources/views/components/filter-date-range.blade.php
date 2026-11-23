@props([
    'action' => null,
    'standalone' => false,
    'autoSubmit' => false,
    'startDateName' => 'start_date',
    'endDateName' => 'end_date',
    'startDate' => null,
    'endDate' => null,
    'label' => 'Periode Tanggal',
    'placeholder' => 'Pilih Rentang Tanggal',
    'presets' => [],
])

@php
    $start = $startDate ?? request($startDateName, '');
    $end = $endDate ?? request($endDateName, '');
    $targetUrl = $action ?? request()->url();
    $isStandalone = $standalone || (!empty($action));
@endphp

<div x-data="smartDateRangePicker({
        initialStart: '{{ $start }}',
        initialEnd: '{{ $end }}',
        startDateName: '{{ $startDateName }}',
        endDateName: '{{ $endDateName }}',
        autoSubmit: {{ $autoSubmit ? 'true' : 'false' }},
        isStandalone: {{ $isStandalone ? 'true' : 'false' }}
     })"
     class="relative">

    @if($isStandalone)
        <form x-ref="standaloneForm" method="GET" action="{{ $targetUrl }}" data-no-loading>
            @foreach(request()->query() as $key => $val)
                @if($key !== $startDateName && $key !== $endDateName && !is_array($val))
                    <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                @endif
            @endforeach
    @endif

    <!-- Hidden Inputs yang Dikirim ke Server / Form -->
    <input type="hidden" name="{{ $startDateName }}" :value="startDate">
    <input type="hidden" name="{{ $endDateName }}" :value="endDate">

    <!-- Tombol Input Tunggal (Single-Field) -->
    <div class="flex items-center gap-1.5">
        <button type="button"
                @click="togglePopover()"
                @click.outside="open = false"
                class="w-full text-xs font-bold bg-white text-slate-800 border rounded-xl px-3 py-2 flex items-center justify-between gap-2 shadow-2xs hover:border-slate-300 transition-all cursor-pointer text-left min-w-[200px]"
                :class="hasActiveRange ? 'border-rose-400 ring-2 ring-rose-500/10 text-rose-950 bg-rose-50/20' : 'border-slate-200'">
            <div class="flex items-center gap-2 min-w-0">
                <i data-lucide="calendar" class="w-4 h-4 shrink-0" :class="hasActiveRange ? 'text-rose-600' : 'text-slate-400'"></i>
                <div class="truncate">
                    <span class="text-[10px] uppercase tracking-wider text-slate-400 block font-extrabold leading-none mb-0.5" x-text="'{{ $label }}'"></span>
                    <span class="text-xs font-bold block truncate" x-text="displayLabel"></span>
                </div>
            </div>
            <div class="flex items-center gap-1 text-slate-400 shrink-0">
                <span x-show="hasActiveRange"
                      @click.stop="clearDateRange()"
                      title="Hapus / Reset Tanggal"
                      class="p-0.5 hover:text-rose-600 rounded cursor-pointer">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                </span>
                <i data-lucide="chevron-down" class="w-3.5 h-3.5 transition-transform" :class="open ? 'rotate-180' : ''"></i>
            </div>
        </button>
    </div>

    <!-- Dropdown Popover Menu (Presets & Kustom) -->
    <div x-show="open"
         x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute left-0 mt-2 z-50 w-72 sm:w-80 bg-white rounded-2xl shadow-xl border border-slate-200/90 p-4 text-xs font-medium">

        <div class="flex items-center justify-between pb-2.5 mb-3 border-b border-slate-100">
            <span class="font-extrabold text-slate-900 text-xs flex items-center gap-1.5">
                <i data-lucide="clock" class="w-3.5 h-3.5 text-rose-600"></i>
                <span>Pilih Periode Cepat</span>
            </span>
            <span class="text-[10px] font-mono text-slate-400 font-bold" x-text="activePresetName"></span>
        </div>

        <!-- Pilihan Presets Cepat (Grid) -->
        <div class="grid grid-cols-2 gap-1.5 mb-3">
            <button type="button" @click="applyPreset('today')"
                    :class="isPresetActive('today') ? 'bg-rose-50 text-rose-700 border-rose-300 font-extrabold' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200 font-semibold'"
                    class="px-2.5 py-1.5 rounded-xl border text-left transition-all flex items-center justify-between cursor-pointer">
                <span>Hari Ini</span>
                <i data-lucide="check" class="w-3.5 h-3.5 text-rose-600" x-show="isPresetActive('today')"></i>
            </button>

            <button type="button" @click="applyPreset('yesterday')"
                    :class="isPresetActive('yesterday') ? 'bg-rose-50 text-rose-700 border-rose-300 font-extrabold' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200 font-semibold'"
                    class="px-2.5 py-1.5 rounded-xl border text-left transition-all flex items-center justify-between cursor-pointer">
                <span>Kemarin</span>
                <i data-lucide="check" class="w-3.5 h-3.5 text-rose-600" x-show="isPresetActive('yesterday')"></i>
            </button>

            <button type="button" @click="applyPreset('7days')"
                    :class="isPresetActive('7days') ? 'bg-rose-50 text-rose-700 border-rose-300 font-extrabold' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200 font-semibold'"
                    class="px-2.5 py-1.5 rounded-xl border text-left transition-all flex items-center justify-between cursor-pointer">
                <span>7 Hari Terakhir</span>
                <i data-lucide="check" class="w-3.5 h-3.5 text-rose-600" x-show="isPresetActive('7days')"></i>
            </button>

            <button type="button" @click="applyPreset('30days')"
                    :class="isPresetActive('30days') ? 'bg-rose-50 text-rose-700 border-rose-300 font-extrabold' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200 font-semibold'"
                    class="px-2.5 py-1.5 rounded-xl border text-left transition-all flex items-center justify-between cursor-pointer">
                <span>30 Hari Terakhir</span>
                <i data-lucide="check" class="w-3.5 h-3.5 text-rose-600" x-show="isPresetActive('30days')"></i>
            </button>

            <button type="button" @click="applyPreset('this_month')"
                    :class="isPresetActive('this_month') ? 'bg-rose-50 text-rose-700 border-rose-300 font-extrabold' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200 font-semibold'"
                    class="px-2.5 py-1.5 rounded-xl border text-left transition-all flex items-center justify-between cursor-pointer">
                <span>Bulan Ini</span>
                <i data-lucide="check" class="w-3.5 h-3.5 text-rose-600" x-show="isPresetActive('this_month')"></i>
            </button>

            <button type="button" @click="applyPreset('last_month')"
                    :class="isPresetActive('last_month') ? 'bg-rose-50 text-rose-700 border-rose-300 font-extrabold' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200 font-semibold'"
                    class="px-2.5 py-1.5 rounded-xl border text-left transition-all flex items-center justify-between cursor-pointer">
                <span>Bulan Kemarin</span>
                <i data-lucide="check" class="w-3.5 h-3.5 text-rose-600" x-show="isPresetActive('last_month')"></i>
            </button>
        </div>

        <!-- Opsi Kustom Tanggal (Manual) -->
        <div class="pt-2.5 border-t border-slate-100">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-700">Kustom Tanggal Manual:</span>
                <button type="button"
                        x-show="!showCustomInputs"
                        @click="showCustomInputs = true"
                        class="text-[11px] text-rose-600 hover:text-rose-800 font-bold flex items-center gap-1 cursor-pointer">
                    <span>Atur Tanggal &rarr;</span>
                </button>
            </div>

            <div x-show="showCustomInputs" class="space-y-2.5">
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">Dari:</label>
                        <input type="date"
                               x-model="customStart"
                               class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 focus:outline-none focus:border-rose-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">Sampai:</label>
                        <input type="date"
                               x-model="customEnd"
                               class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 focus:outline-none focus:border-rose-500">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-1.5 pt-1">
                    <button type="button" @click="showCustomInputs = false"
                            class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-xs font-bold cursor-pointer">
                        Batal
                    </button>
                    <button type="button" @click="applyCustomDate()"
                            class="px-3 py-1 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-bold flex items-center gap-1 cursor-pointer shadow-xs">
                        <i data-lucide="check" class="w-3 h-3 text-emerald-400"></i>
                        <span>Terapkan</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if($isStandalone)
        </form>
    @endif
</div>

<script>
    if (typeof window.smartDateRangePicker === 'undefined') {
        window.smartDateRangePicker = function(config) {
            return {
                open: false,
                startDate: config.initialStart || '',
                endDate: config.initialEnd || '',
                customStart: config.initialStart || '',
                customEnd: config.initialEnd || '',
                showCustomInputs: false,
                autoSubmit: config.autoSubmit || false,
                isStandalone: config.isStandalone || false,

                init() {
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                togglePopover() {
                    this.open = !this.open;
                    if (this.open) {
                        this.customStart = this.startDate;
                        this.customEnd = this.endDate;
                        this.$nextTick(() => {
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                        });
                    }
                },

                get hasActiveRange() {
                    return Boolean(this.startDate || this.endDate);
                },

                formatDisplayDate(dtStr) {
                    if (!dtStr) return '';
                    const parts = dtStr.split('-');
                    if (parts.length !== 3) return dtStr;
                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                    const day = parts[2];
                    const month = months[parseInt(parts[1], 10) - 1] || parts[1];
                    const year = parts[0];
                    return `${day} ${month} ${year}`;
                },

                get displayLabel() {
                    if (!this.startDate && !this.endDate) return 'Semua Periode';
                    
                    if (this.isPresetActive('today')) {
                        return `Hari Ini (${this.formatDisplayDate(this.startDate)})`;
                    }
                    if (this.isPresetActive('yesterday')) {
                        return `Kemarin (${this.formatDisplayDate(this.startDate)})`;
                    }
                    if (this.isPresetActive('7days')) {
                        return `7 Hari Terakhir (${this.formatDisplayDate(this.startDate)} - ${this.formatDisplayDate(this.endDate)})`;
                    }
                    if (this.isPresetActive('this_month')) {
                        return `Bulan Ini (${this.formatDisplayDate(this.startDate)} - ${this.formatDisplayDate(this.endDate)})`;
                    }
                    if (this.isPresetActive('last_month')) {
                        return `Bulan Kemarin (${this.formatDisplayDate(this.startDate)} - ${this.formatDisplayDate(this.endDate)})`;
                    }
                    
                    if (this.startDate === this.endDate) {
                        return this.formatDisplayDate(this.startDate);
                    }
                    return `${this.formatDisplayDate(this.startDate)} s/d ${this.formatDisplayDate(this.endDate)}`;
                },

                get activePresetName() {
                    if (this.isPresetActive('today')) return 'Hari Ini';
                    if (this.isPresetActive('yesterday')) return 'Kemarin';
                    if (this.isPresetActive('7days')) return '7 Hari';
                    if (this.isPresetActive('30days')) return '30 Hari';
                    if (this.isPresetActive('this_month')) return 'Bulan Ini';
                    if (this.isPresetActive('last_month')) return 'Bulan Lalu';
                    if (this.hasActiveRange) return 'Kustom';
                    return 'Semua';
                },

                isPresetActive(type) {
                    const now = new Date();
                    const pad = n => String(n).padStart(2, '0');
                    const fmt = d => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

                    if (type === 'today') {
                        const t = fmt(now);
                        return this.startDate === t && this.endDate === t;
                    }
                    if (type === 'yesterday') {
                        const y = new Date();
                        y.setDate(y.getDate() - 1);
                        const yStr = fmt(y);
                        return this.startDate === yStr && this.endDate === yStr;
                    }
                    if (type === '7days') {
                        const past = new Date();
                        past.setDate(past.getDate() - 6);
                        return this.startDate === fmt(past) && this.endDate === fmt(now);
                    }
                    if (type === '30days') {
                        const past = new Date();
                        past.setDate(past.getDate() - 29);
                        return this.startDate === fmt(past) && this.endDate === fmt(now);
                    }
                    if (type === 'this_month') {
                        const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
                        const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                        return this.startDate === fmt(firstDay) && this.endDate === fmt(lastDay);
                    }
                    if (type === 'last_month') {
                        const firstDay = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                        const lastDay = new Date(now.getFullYear(), now.getMonth(), 0);
                        return this.startDate === fmt(firstDay) && this.endDate === fmt(lastDay);
                    }
                    return false;
                },

                applyPreset(type) {
                    const now = new Date();
                    const pad = n => String(n).padStart(2, '0');
                    const fmt = d => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

                    if (type === 'today') {
                        const t = fmt(now);
                        this.startDate = t;
                        this.endDate = t;
                    } else if (type === 'yesterday') {
                        const y = new Date();
                        y.setDate(y.getDate() - 1);
                        const yStr = fmt(y);
                        this.startDate = yStr;
                        this.endDate = yStr;
                    } else if (type === '7days') {
                        const past = new Date();
                        past.setDate(past.getDate() - 6);
                        this.startDate = fmt(past);
                        this.endDate = fmt(now);
                    } else if (type === '30days') {
                        const past = new Date();
                        past.setDate(past.getDate() - 29);
                        this.startDate = fmt(past);
                        this.endDate = fmt(now);
                    } else if (type === 'this_month') {
                        const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
                        const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                        this.startDate = fmt(firstDay);
                        this.endDate = fmt(lastDay);
                    } else if (type === 'last_month') {
                        const firstDay = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                        const lastDay = new Date(now.getFullYear(), now.getMonth(), 0);
                        this.startDate = fmt(firstDay);
                        this.endDate = fmt(lastDay);
                    }

                    this.showCustomInputs = false;
                    this.open = false;
                    this.triggerChange();
                },

                applyCustomDate() {
                    if (this.customStart) this.startDate = this.customStart;
                    if (this.customEnd) this.endDate = this.customEnd;
                    this.open = false;
                    this.triggerChange();
                },

                clearDateRange() {
                    this.startDate = '';
                    this.endDate = '';
                    this.customStart = '';
                    this.customEnd = '';
                    this.open = false;
                    this.triggerChange();
                },

                triggerChange() {
                    this.$dispatch('date-range-changed', {
                        start: this.startDate,
                        end: this.endDate
                    });

                    if (this.autoSubmit) {
                        this.$nextTick(() => {
                            if (this.isStandalone && this.$refs.standaloneForm) {
                                this.$refs.standaloneForm.submit();
                            } else {
                                const parentForm = this.$el.closest('form');
                                if (parentForm) parentForm.submit();
                            }
                        });
                    }
                }
            };
        };
    }
</script>
