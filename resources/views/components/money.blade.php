@props([
    'value' => 0,
    'prefix' => 'Rp ',
    'decimals' => 0,
    'nullText' => 'Rp 0',
    'class' => '',
])

@php
    $num = is_numeric($value) ? (float) $value : 0;
    $formatted = number_format($num, $decimals, ',', '.');
@endphp

<span class="font-mono font-semibold {{ $class }}">
    <span class="text-[10px] font-normal opacity-70">{{ $prefix }}</span><span>{{ $formatted }}</span>
</span>
