@props([
    'model' => 'searchQuery',
    'placeholder' => 'Cari data...',
    'width' => 'w-full sm:w-64',
    'accent' => 'rose', // rose, purple, emerald, blue, etc.
    'class' => '',
])

<div class="relative inline-flex items-center {{ $width }} {{ $class }}">
    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
        <i data-lucide="search" class="w-3.5 h-3.5"></i>
    </div>
    <input type="text" 
           x-model="{{ $model }}" 
           placeholder="{{ $placeholder }}" 
           class="w-full text-xs font-semibold bg-white border border-slate-200 rounded-xl pl-8 pr-7 py-2 text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-{{ $accent }}-500 focus:ring-2 focus:ring-{{ $accent }}-500/10 shadow-2xs transition-all">
    <button type="button" 
            x-show="{{ $model }}" 
            x-cloak
            @click="{{ $model }} = ''" 
            class="absolute inset-y-0 right-0 pr-2 flex items-center text-slate-400 hover:text-slate-600 transition-colors cursor-pointer"
            title="Bersihkan pencarian">
        <i data-lucide="x" class="w-3.5 h-3.5"></i>
    </button>
</div>
