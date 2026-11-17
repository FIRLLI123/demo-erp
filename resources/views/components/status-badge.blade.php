@props([
    'status' => 'ACTIVE',
    'label' => null,
    'icon' => true,
    'size' => 'xs', // 'xs' | 'sm' | 'md'
])

@php
    $normalized = strtoupper(trim((string)$status));

    // Konfigurasi Status: Teks, Warna Tailwind, dan Ikon Lucide
    $statusMap = [
        // --- Status Master & Keaktifan ---
        'ACTIVE' => [
            'label' => 'Aktif',
            'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
            'icon' => 'check-circle-2',
        ],
        'INACTIVE' => [
            'label' => 'Nonaktif',
            'bg' => 'bg-slate-100 text-slate-500 border-slate-200',
            'icon' => 'minus-circle',
        ],

        // --- Status PO, GR, Pengadaan ---
        'DRAFT' => [
            'label' => 'Draft',
            'bg' => 'bg-slate-100 text-slate-700 border-slate-300',
            'icon' => 'file-edit',
        ],
        'ORDERED' => [
            'label' => 'Dipesan',
            'bg' => 'bg-blue-50 text-blue-700 border-blue-200',
            'icon' => 'send',
        ],
        'RECEIVED' => [
            'label' => 'Diterima',
            'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'icon' => 'check-check',
        ],
        'CONFIRMED' => [
            'label' => 'Terkonfirmasi',
            'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'icon' => 'check',
        ],

        // --- Status Sales, Order & Approval ---
        'PENDING_APPROVAL' => [
            'label' => 'Menunggu Approval',
            'bg' => 'bg-amber-50 text-amber-800 border-amber-300',
            'icon' => 'clock',
        ],
        'APPROVED' => [
            'label' => 'Disetujui',
            'bg' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'icon' => 'thumbs-up',
        ],
        'REJECTED' => [
            'label' => 'Ditolak',
            'bg' => 'bg-rose-50 text-rose-700 border-rose-200',
            'icon' => 'x-circle',
        ],
        'PROCESSING' => [
            'label' => 'Diproses',
            'bg' => 'bg-sky-50 text-sky-700 border-sky-200',
            'icon' => 'loader-2',
        ],
        'DELIVERING' => [
            'label' => 'Dikirimkan',
            'bg' => 'bg-purple-50 text-purple-700 border-purple-200',
            'icon' => 'truck',
        ],
        'DELIVERED' => [
            'label' => 'Terkirim',
            'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'icon' => 'package-check',
        ],
        'COMPLETED' => [
            'label' => 'Selesai',
            'bg' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            'icon' => 'check-circle-2',
        ],
        'CANCELLED' => [
            'label' => 'Dibatalkan',
            'bg' => 'bg-rose-50 text-rose-700 border-rose-200',
            'icon' => 'ban',
        ],
        'VOID' => [
            'label' => 'Void (Batal)',
            'bg' => 'bg-rose-50 text-rose-800 border-rose-200',
            'icon' => 'ban',
        ],

        // --- Status Keuangan & Piutang (AR / TTK / BBM) ---
        'UNPAID' => [
            'label' => 'Belum Lunas',
            'bg' => 'bg-rose-50 text-rose-700 border-rose-200',
            'icon' => 'alert-circle',
        ],
        'PARTIAL' => [
            'label' => 'Sebagian',
            'bg' => 'bg-amber-50 text-amber-800 border-amber-300',
            'icon' => 'pie-chart',
        ],
        'PAID' => [
            'label' => 'Lunas',
            'bg' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            'icon' => 'badge-check',
        ],
        'VERIFIED' => [
            'label' => 'Terverifikasi',
            'bg' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            'icon' => 'shield-check',
        ],
    ];

    $cfg = $statusMap[$normalized] ?? [
        'label' => ucwords(strtolower(str_replace('_', ' ', $normalized))),
        'bg' => 'bg-slate-100 text-slate-700 border-slate-200',
        'icon' => 'info',
    ];

    $displayLabel = $label ?? $cfg['label'];
    $iconName = $cfg['icon'];

    // Ukuran Font & Padding
    $sizeClasses = [
        'xs' => 'text-[10px] px-2 py-0.5 gap-1',
        'sm' => 'text-xs px-2.5 py-1 gap-1.5',
        'md' => 'text-sm px-3 py-1.5 gap-2',
    ];
    $iconSizes = [
        'xs' => 'w-3 h-3',
        'sm' => 'w-3.5 h-3.5',
        'md' => 'w-4 h-4',
    ];
@endphp

<span class="inline-flex items-center font-bold font-sans rounded-full border {{ $cfg['bg'] }} {{ $sizeClasses[$size] ?? $sizeClasses['xs'] }} shadow-2xs select-none">
    @if($icon && !empty($iconName))
        <i data-lucide="{{ $iconName }}" class="{{ $iconSizes[$size] ?? $iconSizes['xs'] }} shrink-0"></i>
    @endif
    <span>{{ $displayLabel }}</span>
</span>
