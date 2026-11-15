@props([
    'entity',
    'id',
    'name' => '',
    'active' => true
])

@php
    $isActive = (bool) $active;
@endphp

<div class="inline-flex items-center gap-1.5 select-none" data-no-loading>
    <button type="button" 
            onclick="window.toggleMasterStatus('{{ $entity }}', {{ $id }}, '{{ addslashes($name) }}', this)"
            data-no-loading
            class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-emerald-500/20 {{ $isActive ? 'bg-emerald-500' : 'bg-slate-300' }}"
            role="switch" 
            aria-checked="{{ $isActive ? 'true' : 'false' }}"
            title="Klik untuk {{ $isActive ? 'Menonaktifkan (Jadikan 99/INACTIVE)' : 'Mengaktifkan (Jadikan 00/ACTIVE)' }}">
        <span class="sr-only">Toggle status {{ $name }}</span>
        <span data-toggle-knob 
              class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-xs ring-0 transition duration-200 ease-in-out {{ $isActive ? 'translate-x-4' : 'translate-x-0' }}"></span>
    </button>
    <span data-toggle-label class="text-[10px] font-mono font-bold tracking-tight {{ $isActive ? 'text-emerald-700' : 'text-slate-400' }}">
        {{ $isActive ? 'AKTIF' : 'NONAKTIF' }}
    </span>
</div>

