@props([
    'icon' => 'inbox',
    'title' => 'Belum Ada Data',
    'subtitle' => 'Data untuk kriteria ini belum tersedia atau belum diinput.',
    'actionUrl' => null,
    'actionLabel' => null,
    'actionIcon' => 'plus',
])

<div class="py-12 px-6 text-center bg-slate-50/60 rounded-3xl border border-dashed border-slate-300/80 my-4">
    <!-- Icon Container -->
    <div class="w-14 h-14 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center justify-center text-slate-400 mx-auto mb-3.5">
        <i data-lucide="{{ $icon }}" class="w-6 h-6 stroke-[1.5]"></i>
    </div>

    <!-- Title & Description -->
    <h4 class="text-sm font-extrabold text-slate-800 tracking-tight">{{ $title }}</h4>
    @if($subtitle)
        <p class="text-xs text-slate-500 max-w-md mx-auto mt-1 leading-relaxed">{{ $subtitle }}</p>
    @endif

    <!-- Optional Action Button -->
    @if($actionUrl && $actionLabel)
        <div class="mt-4">
            <a href="{{ $actionUrl }}" 
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-white border border-slate-300 hover:border-rose-400 text-slate-700 hover:text-rose-600 font-bold text-xs shadow-xs hover:shadow-sm transition-all active:scale-95">
                @if($actionIcon)
                    <i data-lucide="{{ $actionIcon }}" class="w-3.5 h-3.5"></i>
                @endif
                <span>{{ $actionLabel }}</span>
            </a>
        </div>
    @endif
</div>
