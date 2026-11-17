@props([
    'name',
    'id' => null,
    'value' => 0,
    'label' => null,
    'placeholder' => '0',
    'prefix' => 'Rp',
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'helpText' => null,
])

@php
    $inputId = $id ?? $name . '_' . uniqid();
    $initialValue = is_numeric($value) ? (float)$value : 0;
@endphp

<div x-data="{
        rawVal: {{ $initialValue }},
        displayVal: '',
        formatNumber(n) {
            if (n === null || n === undefined || isNaN(n)) return '';
            const parts = n.toString().split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            return parts.join(',');
        },
        init() {
            if (this.rawVal > 0) {
                this.displayVal = this.formatNumber(this.rawVal);
            }
        },
        onInput(e) {
            // Ambil hanya digit
            let clean = e.target.value.replace(/[^0-9]/g, '');
            if (!clean) {
                this.rawVal = 0;
                this.displayVal = '';
                return;
            }
            this.rawVal = parseInt(clean, 10);
            this.displayVal = this.formatNumber(this.rawVal);
        }
     }" 
     class="w-full">
    
    @if($label)
        <label for="{{ $inputId }}_display" class="block text-xs font-bold text-slate-700 mb-1.5">
            {{ $label }}
            @if($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative rounded-xl shadow-2xs">
        @if($prefix)
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 font-bold text-xs">
                {{ $prefix }}
            </div>
        @endif

        <!-- Input Tampilan (Formatted dengan Pemisah Ribuan Titik) -->
        <input type="text"
               id="{{ $inputId }}_display"
               x-model="displayVal"
               @input="onInput($event)"
               placeholder="{{ $placeholder }}"
               {{ $disabled ? 'disabled' : '' }}
               {{ $readonly ? 'readonly' : '' }}
               {{ $required ? 'required' : '' }}
               class="w-full text-xs font-semibold bg-slate-50/70 border border-slate-200 rounded-xl {{ $prefix ? 'pl-9' : 'pl-3.5' }} pr-3.5 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-rose-500 focus:bg-white focus:ring-2 focus:ring-rose-500/10 transition-all font-mono">
        
        <!-- Input Tersembunyi (Nilai Murni Angka untuk Dikirim ke Server/Database) -->
        <input type="hidden" 
               name="{{ $name }}" 
               id="{{ $inputId }}"
               :value="rawVal">
    </div>

    @if($helpText)
        <p class="text-[10px] text-slate-400 mt-1 leading-normal">{{ $helpText }}</p>
    @endif
</div>
