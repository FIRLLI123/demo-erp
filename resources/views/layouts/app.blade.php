<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} | PT Wahana Putra Yudha - Telco Distribution Management</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN with typography & forms -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#1e3a8a',
                        },
                        telco: {
                            red: '#ED1C24',
                            dark: '#0F172A',
                            accent: '#F59E0B',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js & Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

    <!-- ApexCharts CDN -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        [x-cloak] { display: none !important; }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Modern Loading Bar Shimmer */
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(200%); }
        }
        .animate-shimmer {
            animation: shimmer 1.6s infinite ease-in-out;
        }

        /* Modern Conic Spinner */
        @keyframes spinSlow {
            to { transform: rotate(360deg); }
        }
        .animate-spin-smooth {
            animation: spinSlow 0.9s cubic-bezier(0.4, 0, 0.2, 1) infinite;
        }
    </style>
    @stack('styles')
</head>
<body class="h-full text-slate-800 antialiased font-sans" x-data="globalApp()" x-init="initApp()"
      @show-global-loading.window="showLoading($event.detail.title, $event.detail.subtitle, $event.detail.docType)"
      @hide-global-loading.window="hideLoading()"
      @show-global-toast.window="showToast($event.detail.message, $event.detail.type, $event.detail.title)">

    <!-- ========================================== -->
    <!-- GLOBAL LOADING MODAL (MODERN & ULTRA FAST) -->
    <!-- ========================================== -->
    <div x-show="isLoading" 
         x-cloak 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-md">
        
        <div class="relative bg-white/95 rounded-3xl max-w-sm w-full p-7 shadow-2xl border border-slate-100/80 overflow-hidden transform transition-all text-center"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            
            <!-- Shimmer Top Accent Bar -->
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-slate-100 overflow-hidden">
                <div class="h-full w-1/2 bg-gradient-to-r from-rose-500 via-amber-400 to-rose-600 rounded-full animate-shimmer"></div>
            </div>

            <!-- Double-Ring Modern Spinner with Icon Center -->
            <div class="relative w-20 h-20 mx-auto mb-5 flex items-center justify-center">
                <!-- Outer Pulse Ring -->
                <div class="absolute inset-0 rounded-full bg-rose-500/10 animate-ping opacity-75"></div>
                
                <!-- Rotating Gradient Border Ring 1 -->
                <div class="absolute inset-0 rounded-full border-3 border-transparent border-t-rose-600 border-r-amber-500 animate-spin-smooth"></div>
                
                <!-- Counter-Rotating Border Ring 2 -->
                <div class="absolute inset-2 rounded-full border-2 border-transparent border-b-cyan-500 border-l-slate-900 animate-spin" style="animation-duration: 1.8s; animation-direction: reverse;"></div>
                
                <!-- Center Core Badge -->
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-slate-900 to-slate-800 text-white flex items-center justify-center shadow-lg shadow-slate-900/30">
                    <template x-if="loadingDocType">
                        <span class="text-[10px] font-mono font-extrabold text-rose-400" x-text="loadingDocType"></span>
                    </template>
                    <template x-if="!loadingDocType">
                        <i data-lucide="cpu" class="w-5 h-5 text-rose-500 animate-pulse"></i>
                    </template>
                </div>
            </div>

            <!-- Text Information -->
            <div class="space-y-1.5">
                <div class="flex items-center justify-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <h4 class="text-base font-extrabold text-slate-900 tracking-tight" x-text="loadingTitle"></h4>
                </div>
                <p class="text-xs text-slate-500 font-medium leading-relaxed px-2" x-text="loadingSubtitle"></p>
            </div>

            <!-- Micro Steps Pill -->
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-center gap-2 text-[11px] font-semibold text-slate-400">
                <span class="flex items-center gap-1">
                    <i data-lucide="database" class="w-3.5 h-3.5 text-cyan-500"></i> Mutasi DB
                </span>
                <span>&bull;</span>
                <span class="flex items-center gap-1">
                    <i data-lucide="file-check-2" class="w-3.5 h-3.5 text-emerald-500"></i> Trace Audit
                </span>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- GLOBAL TOAST NOTIFICATION (AUTO DISMISS)   -->
    <!-- ========================================== -->
    <div x-show="toast.show" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-3 sm:translate-y-0 sm:translate-x-4"
         x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 sm:translate-x-0"
         x-transition:leave-end="opacity-0 sm:translate-x-4"
         class="fixed top-6 right-6 z-[9998] max-w-sm w-full bg-white rounded-2xl shadow-2xl border border-slate-100 p-4 flex items-start gap-3.5 overflow-hidden">
        
        <!-- Toast Icon -->
        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 shadow-sm"
             :class="toast.type === 'error' ? 'bg-rose-100 text-rose-600' : 'bg-emerald-100 text-emerald-600'">
            <template x-if="toast.type === 'error'">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
            </template>
            <template x-if="toast.type !== 'error'">
                <i data-lucide="check-circle-2" class="w-5 h-5"></i>
            </template>
        </div>

        <!-- Toast Text Content -->
        <div class="flex-1 min-w-0 pt-0.5">
            <h5 class="text-xs font-bold text-slate-900" x-text="toast.title"></h5>
            <p class="text-xs text-slate-500 mt-0.5 leading-relaxed" x-text="toast.message"></p>
        </div>

        <!-- Close Button -->
        <button @click="toast.show = false" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>

    <div class="min-h-full flex">
        <!-- Sidebar for Desktop & Mobile Overlay -->
        <aside 
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed inset-y-0 left-0 z-50 w-72 bg-slate-900 text-slate-300 transition-transform duration-300 ease-in-out flex flex-col shadow-2xl lg:shadow-none lg:static">
            
            <!-- Brand Header -->
            <div class="h-20 flex items-center justify-between px-6 border-b border-slate-800/80 bg-slate-950/60">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-rose-600 via-red-500 to-amber-500 flex items-center justify-center text-white shadow-lg shadow-red-500/20">
                        <i data-lucide="layers" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-extrabold text-sm text-white tracking-tight truncate max-w-[155px]">PT WAHANA PUTRA YUDHA</span>
                        </div>
                        <p class="text-[10px] text-rose-400 font-bold tracking-wide">TELKOMSEL AUTHORIZED PARTNER</p>
                    </div>
                </div>
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Organization Context Badge -->
            <div class="px-5 py-2.5 border-b border-slate-800/60 bg-slate-900/40">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-400 flex items-center gap-1.5 text-[11px]">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-rose-400"></i> Distributor Resmi
                    </span>
                    <span class="font-extrabold text-white bg-slate-800 px-2 py-0.5 rounded text-[10px] border border-slate-700/60">
                        AREA JABODETABEK
                    </span>
                </div>
            </div>

            <!-- Navigation Links with Smooth Collapsible Dropdowns -->
            <nav x-data="{
                openMenus: {
                    master: {{ request()->routeIs('master.*') ? 'true' : 'false' }},
                    procurement: {{ (request()->routeIs('procurement*') || request()->routeIs('stock-movement*') || request()->routeIs('inventory.*')) ? 'true' : 'false' }},
                    sales: {{ request()->routeIs('sales*') ? 'true' : 'false' }},
                    finance: {{ request()->routeIs('finance*') ? 'true' : 'false' }},
                    reports: {{ request()->routeIs('reports*') ? 'true' : 'false' }},
                    tracking: {{ request()->routeIs('tracking*') ? 'true' : 'false' }}
                },
                toggle(key) {
                    this.openMenus[key] = !this.openMenus[key];
                },
                collapseAll() {
                    Object.keys(this.openMenus).forEach(k => this.openMenus[k] = false);
                },
                expandAll() {
                    Object.keys(this.openMenus).forEach(k => this.openMenus[k] = true);
                },
                isAllCollapsed() {
                    return !Object.values(this.openMenus).some(v => v);
                }
            }" class="flex-1 overflow-y-auto px-4 py-4 space-y-3">
                <!-- Group: CORE (Dashboard) -->
                <div>
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs uppercase tracking-wider font-bold transition-all {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-rose-600 to-red-600 text-white shadow-lg shadow-red-600/30 ring-1 ring-white/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <i data-lucide="layout-dashboard" class="w-4 h-4 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Dashboard Utama</span>
                    </a>
                </div>

                <!-- Group: APPROVAL CENTER -->
                <div>
                    <a href="{{ route('approvals.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs uppercase tracking-wider font-bold transition-all {{ request()->routeIs('approvals*') ? 'bg-gradient-to-r from-rose-600 to-red-600 text-white shadow-lg shadow-red-600/30 ring-1 ring-white/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <i data-lucide="shield-check" class="w-4 h-4 {{ request()->routeIs('approvals*') ? 'text-white' : 'text-amber-400' }}"></i>
                            <span>Pusat Persetujuan</span>
                        </div>
                        <span class="text-[9px] bg-amber-500/20 text-amber-300 border border-amber-500/30 font-mono px-1.5 py-0.5 rounded font-bold">APPROVAL</span>
                    </a>
                </div>

                <!-- Toolbar Toggle Collapse / Expand Semua Sub-Menu -->
                <div class="px-2 pt-2.5 pb-1 flex items-center justify-between text-[11px] text-slate-400 font-semibold uppercase tracking-wider border-t border-slate-800/70">
                    <span class="text-[10px] text-slate-400 font-bold tracking-wider">Modul Operasional</span>
                    <button type="button" 
                            @click="isAllCollapsed() ? expandAll() : collapseAll()" 
                            class="flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-bold text-slate-300 hover:text-white bg-slate-800/80 hover:bg-slate-800 transition border border-slate-700/60 cursor-pointer select-none">
                        <template x-if="!isAllCollapsed()">
                            <span class="flex items-center gap-1 text-rose-400">
                                <i data-lucide="chevrons-up" class="w-3 h-3"></i>
                                <span>Tutup Semua</span>
                            </span>
                        </template>
                        <template x-if="isAllCollapsed()">
                            <span class="flex items-center gap-1 text-emerald-400">
                                <i data-lucide="chevrons-down" class="w-3 h-3"></i>
                                <span>Buka Semua</span>
                            </span>
                        </template>
                    </button>
                </div>

                <!-- Group: MASTER HIERARKI (Collapsible Dropdown) -->
                <div class="space-y-1">
                    <button type="button" @click="toggle('master')" 
                            :class="openMenus.master ? 'bg-slate-800/90 text-white font-bold ring-1 ring-slate-700/60 shadow-xs' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white font-semibold'"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs uppercase tracking-wider transition-all select-none text-left focus:outline-none group">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="network" :class="openMenus.master ? 'text-rose-400' : 'text-slate-400 group-hover:text-slate-200'" class="w-4 h-4 transition-colors"></i>
                            <span class="font-bold">Master & Organisasi</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[9px] bg-slate-900/90 text-slate-400 px-1.5 py-0.5 rounded border border-slate-700/60 font-mono font-normal">6 Menu</span>
                            <span class="inline-flex items-center justify-center transition-transform duration-300 ease-in-out" :class="openMenus.master ? 'rotate-90 text-rose-400' : 'text-slate-500 group-hover:text-slate-300'">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </span>
                        </div>
                    </button>
                    <!-- Sub-Menu (Tree View Indented) -->
                    <div x-show="openMenus.master" x-collapse.duration.300ms class="ml-4 pl-3.5 my-1.5 space-y-1 border-l-2 border-slate-800">
                        <a href="{{ route('master.organization') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs transition-all {{ request()->routeIs('master.organization*') ? 'bg-[#1E293B] text-white font-extrabold border-l-4 border-rose-500 shadow-md ring-1 ring-white/10' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white font-medium' }}">
                            <div class="flex items-center gap-2.5">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('master.organization*') ? 'bg-rose-500 ring-2 ring-rose-400/40' : 'bg-slate-600' }}"></span>
                                <span>Area, Cluster, TAP</span>
                            </div>
                            <span class="text-[9px] bg-slate-800 px-1.5 py-0.5 rounded text-slate-400 border border-slate-700">4 Level</span>
                        </a>
                        <a href="{{ route('master.products') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs transition-all {{ request()->routeIs('master.products*') ? 'bg-[#1E293B] text-white font-extrabold border-l-4 border-rose-500 shadow-md ring-1 ring-white/10' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white font-medium' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('master.products*') ? 'bg-rose-500 ring-2 ring-rose-400/40' : 'bg-slate-600' }}"></span>
                            <span>Katalog Produk (SA/Voucher)</span>
                        </a>
                        <a href="{{ route('master.warehouses') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs transition-all {{ request()->routeIs('master.warehouses*') ? 'bg-[#1E293B] text-white font-extrabold border-l-4 border-rose-500 shadow-md ring-1 ring-white/10' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white font-medium' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('master.warehouses*') ? 'bg-rose-500 ring-2 ring-rose-400/40' : 'bg-slate-600' }}"></span>
                            <span>Gudang (Main, Inv, Sales)</span>
                        </a>
                        <a href="{{ route('master.sales-team') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs transition-all {{ request()->routeIs('master.sales-team*') ? 'bg-[#1E293B] text-white font-extrabold border-l-4 border-rose-500 shadow-md ring-1 ring-white/10' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white font-medium' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('master.sales-team*') ? 'bg-rose-500 ring-2 ring-rose-400/40' : 'bg-slate-600' }}"></span>
                            <span>Tim Sales Force (Kanvas)</span>
                        </a>
                        <a href="{{ route('master.customers') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs transition-all {{ request()->routeIs('master.customers*') ? 'bg-[#1E293B] text-white font-extrabold border-l-4 border-rose-500 shadow-md ring-1 ring-white/10' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white font-medium' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('master.customers*') ? 'bg-rose-500 ring-2 ring-rose-400/40' : 'bg-slate-600' }}"></span>
                            <span>Master Toko / Outlet</span>
                        </a>
                        <a href="{{ route('master.users') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs transition-all {{ request()->routeIs('master.users*') ? 'bg-[#1E293B] text-white font-extrabold border-l-4 border-rose-500 shadow-md ring-1 ring-white/10' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white font-medium' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('master.users*') ? 'bg-rose-500 ring-2 ring-rose-400/40' : 'bg-slate-600' }}"></span>
                            <span>Master Pengguna & Akun</span>
                        </a>
                    </div>
                </div>

                <!-- Group: PENGADAAN & DISTRIBUSI (Collapsible Dropdown) -->
                <div class="space-y-1">
                    <button type="button" @click="toggle('procurement')" 
                            :class="openMenus.procurement ? 'bg-slate-800/90 text-white font-bold ring-1 ring-slate-700/60 shadow-xs' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white font-semibold'"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs uppercase tracking-wider transition-all select-none text-left focus:outline-none group">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="package-check" :class="openMenus.procurement ? 'text-rose-400' : 'text-slate-400 group-hover:text-slate-200'" class="w-4 h-4 transition-colors"></i>
                            <span class="font-bold">Pengadaan & Distribusi</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[9px] font-mono text-slate-400">PO • GR • SM</span>
                            <span class="inline-flex items-center justify-center transition-transform duration-300 ease-in-out" :class="openMenus.procurement ? 'rotate-90 text-rose-400' : 'text-slate-500 group-hover:text-slate-300'">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </span>
                        </div>
                    </button>
                    <!-- Sub-Menu (Tree View Indented) -->
                    <div x-show="openMenus.procurement" x-collapse.duration.300ms class="ml-4 pl-3.5 my-1.5 space-y-1 border-l-2 border-slate-800">
                        <a href="{{ route('procurement.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs transition-all {{ request()->routeIs('procurement*') ? 'bg-[#1E293B] text-white font-extrabold border-l-4 border-rose-500 shadow-md ring-1 ring-white/10' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white font-medium' }}">
                            <div class="flex items-center gap-2.5">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('procurement*') ? 'bg-emerald-400 ring-2 ring-emerald-400/40' : 'bg-slate-600' }}"></span>
                                <span>PO & Penerimaan (GR)</span>
                            </div>
                            <span class="text-[9px] bg-emerald-500/20 text-emerald-400 px-1.5 py-0.5 rounded font-mono font-bold">PO-GR</span>
                        </a>
                        <a href="{{ route('stock-movement.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs transition-all {{ request()->routeIs('stock-movement*') ? 'bg-[#1E293B] text-white font-extrabold border-l-4 border-rose-500 shadow-md ring-1 ring-white/10' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white font-medium' }}">
                            <div class="flex items-center gap-2.5">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('stock-movement*') ? 'bg-cyan-400 ring-2 ring-cyan-400/40' : 'bg-slate-600' }}"></span>
                                <span>Stock Movement (SM)</span>
                            </div>
                            <span class="text-[9px] bg-cyan-500/20 text-cyan-400 px-1.5 py-0.5 rounded font-mono font-bold">SM</span>
                        </a>
                        <a href="{{ route('inventory.repack') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs transition-all {{ request()->routeIs('inventory.repack*') ? 'bg-[#1E293B] text-white font-extrabold border-l-4 border-rose-500 shadow-md ring-1 ring-white/10' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white font-medium' }}">
                            <div class="flex items-center gap-2.5">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('inventory.repack*') ? 'bg-indigo-400 ring-2 ring-indigo-400/40' : 'bg-slate-600' }}"></span>
                                <span>Repack In & Out</span>
                            </div>
                            <span class="text-[9px] bg-indigo-500/20 text-indigo-400 px-1.5 py-0.5 rounded font-mono font-bold">RO-RI</span>
                        </a>
                        <a href="{{ route('inventory.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs transition-all {{ request()->routeIs('inventory.index') ? 'bg-[#1E293B] text-white font-extrabold border-l-4 border-rose-500 shadow-md ring-1 ring-white/10' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white font-medium' }}">
                            <div class="flex items-center gap-2.5">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('inventory.index') ? 'bg-amber-400 ring-2 ring-amber-400/40' : 'bg-slate-600' }}"></span>
                                <span>Laporan Inventory & Stok</span>
                            </div>
                            <span class="text-[9px] bg-amber-500/20 text-amber-400 px-1.5 py-0.5 rounded font-mono font-bold">STOK</span>
                        </a>
                    </div>
                </div>

                <!-- Group: PENJUALAN (Collapsible Dropdown) -->
                <div class="space-y-1">
                    <button type="button" @click="toggle('sales')" 
                            :class="openMenus.sales ? 'bg-slate-800/90 text-white font-bold ring-1 ring-slate-700/60 shadow-xs' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white font-semibold'"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs uppercase tracking-wider transition-all select-none text-left focus:outline-none group">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="shopping-bag" :class="openMenus.sales ? 'text-rose-400' : 'text-slate-400 group-hover:text-slate-200'" class="w-4 h-4 transition-colors"></i>
                            <span class="font-bold">Penjualan Lapangan</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[9px] bg-slate-900/90 text-slate-400 px-1.5 py-0.5 rounded border border-slate-700/60 font-mono font-normal">2 Menu</span>
                            <span class="inline-flex items-center justify-center transition-transform duration-300 ease-in-out" :class="openMenus.sales ? 'rotate-90 text-rose-400' : 'text-slate-500 group-hover:text-slate-300'">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </span>
                        </div>
                    </button>
                    <!-- Sub-Menu (Tree View Indented) -->
                    <div x-show="openMenus.sales" x-collapse.duration.300ms class="ml-4 pl-3.5 my-1.5 space-y-1 border-l-2 border-slate-800">
                        <a href="{{ route('sales.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs transition-all {{ request()->routeIs('sales.index*') ? 'bg-[#1E293B] text-white font-extrabold border-l-4 border-rose-500 shadow-md ring-1 ring-white/10' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white font-medium' }}">
                            <div class="flex items-center gap-2.5">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('sales.index*') ? 'bg-amber-400 ring-2 ring-amber-400/40' : 'bg-slate-600' }}"></span>
                                <span>Sales Order & Delivery</span>
                            </div>
                            <span class="text-[9px] bg-amber-500/20 text-amber-400 px-1.5 py-0.5 rounded font-mono font-bold">SO-GD</span>
                        </a>
                        <a href="{{ route('sales.targets') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs transition-all {{ request()->routeIs('sales.targets*') ? 'bg-[#1E293B] text-white font-extrabold border-l-4 border-rose-500 shadow-md ring-1 ring-white/10' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white font-medium' }}">
                            <div class="flex items-center gap-2.5">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('sales.targets*') ? 'bg-amber-400 ring-2 ring-amber-400/40' : 'bg-slate-600' }}"></span>
                                <span>Target & Komisi Sales</span>
                            </div>
                            <span class="text-[9px] bg-amber-500/20 text-amber-400 px-1.5 py-0.5 rounded font-mono font-bold">LEADERBOARD</span>
                        </a>
                    </div>
                </div>

                <!-- Group: KEUANGAN & KAS/BANK (Collapsible Dropdown) -->
                <div class="space-y-1">
                    <button type="button" @click="toggle('finance')" 
                            :class="openMenus.finance ? 'bg-slate-800/90 text-white font-bold ring-1 ring-slate-700/60 shadow-xs' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white font-semibold'"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs uppercase tracking-wider transition-all select-none text-left focus:outline-none group">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="receipt" :class="openMenus.finance ? 'text-rose-400' : 'text-slate-400 group-hover:text-slate-200'" class="w-4 h-4 transition-colors"></i>
                            <span class="font-bold">Keuangan & Kas/Bank</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[9px] font-mono text-slate-400">AR • BBM</span>
                            <span class="inline-flex items-center justify-center transition-transform duration-300 ease-in-out" :class="openMenus.finance ? 'rotate-90 text-rose-400' : 'text-slate-500 group-hover:text-slate-300'">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </span>
                        </div>
                    </button>
                    <!-- Sub-Menu (Tree View Indented) -->
                    <div x-show="openMenus.finance" x-collapse.duration.300ms class="ml-4 pl-3.5 my-1.5 space-y-1 border-l-2 border-slate-800">
                        <a href="{{ route('finance.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs transition-all {{ request()->routeIs('finance*') ? 'bg-[#1E293B] text-white font-extrabold border-l-4 border-rose-500 shadow-md ring-1 ring-white/10' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white font-medium' }}">
                            <div class="flex items-center gap-2.5">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('finance*') ? 'bg-rose-400 ring-2 ring-rose-400/40' : 'bg-slate-600' }}"></span>
                                <span>Piutang & Kasir (TTK/BBM)</span>
                            </div>
                            <span class="text-[9px] bg-rose-500/20 text-rose-400 px-1.5 py-0.5 rounded font-mono font-bold">FINANCE</span>
                        </a>
                    </div>
                </div>

                <!-- Group: PUSAT LAPORAN & EKSPOR (Collapsible Dropdown) -->
                <div class="space-y-1">
                    <button type="button" @click="toggle('reports')" 
                            :class="openMenus.reports ? 'bg-slate-800/90 text-white font-bold ring-1 ring-slate-700/60 shadow-xs' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white font-semibold'"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs uppercase tracking-wider transition-all select-none text-left focus:outline-none group">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="file-spreadsheet" :class="openMenus.reports ? 'text-rose-400' : 'text-slate-400 group-hover:text-slate-200'" class="w-4 h-4 transition-colors"></i>
                            <span class="font-bold">Pusat Laporan & Ekspor</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[9px] bg-slate-900/90 text-slate-400 px-1.5 py-0.5 rounded border border-slate-700/60 font-mono font-normal">2 Menu</span>
                            <span class="inline-flex items-center justify-center transition-transform duration-300 ease-in-out" :class="openMenus.reports ? 'rotate-90 text-rose-400' : 'text-slate-500 group-hover:text-slate-300'">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </span>
                        </div>
                    </button>
                    <!-- Sub-Menu (Tree View Indented) -->
                    <div x-show="openMenus.reports" x-collapse.duration.300ms class="ml-4 pl-3.5 my-1.5 space-y-1 border-l-2 border-slate-800">
                        <a href="{{ route('reports.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs transition-all {{ request()->routeIs('reports.index*') ? 'bg-[#1E293B] text-white font-extrabold border-l-4 border-rose-500 shadow-md ring-1 ring-white/10' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white font-medium' }}">
                            <div class="flex items-center gap-2.5">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('reports.index*') ? 'bg-emerald-400 ring-2 ring-emerald-400/40' : 'bg-slate-600' }}"></span>
                                <span>Pusat Laporan Terpadu</span>
                            </div>
                            <span class="text-[9px] bg-emerald-500/20 text-emerald-400 px-1.5 py-0.5 rounded font-mono font-bold">REPORT</span>
                        </a>
                        <a href="{{ route('reports.audit') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs transition-all {{ request()->routeIs('reports.audit*') ? 'bg-[#1E293B] text-white font-extrabold border-l-4 border-rose-500 shadow-md ring-1 ring-white/10' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white font-medium' }}">
                            <div class="flex items-center gap-2.5">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('reports.audit*') ? 'bg-indigo-400 ring-2 ring-indigo-400/40' : 'bg-slate-600' }}"></span>
                                <span>Audit Trail & Aktivitas</span>
                            </div>
                            <span class="text-[9px] bg-indigo-500/20 text-indigo-400 px-1.5 py-0.5 rounded font-mono font-bold">AUDIT LOG</span>
                        </a>
                    </div>
                </div>

                <!-- Group: TRACKING & INFORMASI (Collapsible Dropdown) -->
                <div class="space-y-1">
                    <button type="button" @click="toggle('tracking')" 
                            :class="openMenus.tracking ? 'bg-slate-800/90 text-white font-bold ring-1 ring-slate-700/60 shadow-xs' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white font-semibold'"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs uppercase tracking-wider transition-all select-none text-left focus:outline-none group">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="map-pin" :class="openMenus.tracking ? 'text-rose-400' : 'text-slate-400 group-hover:text-slate-200'" class="w-4 h-4 transition-colors"></i>
                            <span class="font-bold">Tracking & Informasi</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[9px] bg-slate-900/90 text-slate-400 px-1.5 py-0.5 rounded border border-slate-700/60 font-mono font-normal">3 Menu</span>
                            <span class="inline-flex items-center justify-center transition-transform duration-300 ease-in-out" :class="openMenus.tracking ? 'rotate-90 text-rose-400' : 'text-slate-500 group-hover:text-slate-300'">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </span>
                        </div>
                    </button>
                    <!-- Sub-Menu (Tree View Indented) -->
                    <div x-show="openMenus.tracking" x-collapse.duration.300ms class="ml-4 pl-3.5 my-1.5 space-y-1 border-l-2 border-slate-800">
                        <a href="{{ route('tracking.locations') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs transition-all {{ request()->routeIs('tracking.locations*') ? 'bg-[#1E293B] text-white font-extrabold border-l-4 border-rose-500 shadow-md ring-1 ring-white/10' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white font-medium' }}">
                            <div class="flex items-center gap-2.5">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('tracking.locations*') ? 'bg-rose-500 ring-2 ring-rose-400/40' : 'bg-slate-600' }}"></span>
                                <span>Tracking Lokasi Sales (GPS)</span>
                            </div>
                            <span class="text-[9px] bg-rose-500/20 text-rose-400 px-1.5 py-0.5 rounded font-mono font-bold">LIVE MAP</span>
                        </a>
                        <a href="{{ route('tracking.pjp') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs transition-all {{ request()->routeIs('tracking.pjp*') ? 'bg-[#1E293B] text-white font-extrabold border-l-4 border-rose-500 shadow-md ring-1 ring-white/10' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white font-medium' }}">
                            <div class="flex items-center gap-2.5">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('tracking.pjp*') ? 'bg-amber-400 ring-2 ring-amber-400/40' : 'bg-slate-600' }}"></span>
                                <span>Rencana Kunjungan (PJP)</span>
                            </div>
                            <span class="text-[9px] bg-amber-500/20 text-amber-400 px-1.5 py-0.5 rounded font-mono font-bold">CALL PLAN</span>
                        </a>
                        <a href="{{ route('tracking.devices') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs transition-all {{ request()->routeIs('tracking.devices*') ? 'bg-[#1E293B] text-white font-extrabold border-l-4 border-rose-500 shadow-md ring-1 ring-white/10' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white font-medium' }}">
                            <div class="flex items-center gap-2.5">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('tracking.devices*') ? 'bg-indigo-400 ring-2 ring-indigo-400/40' : 'bg-slate-600' }}"></span>
                                <span>Informasi Device Mobile</span>
                            </div>
                            <span class="text-[9px] bg-indigo-500/20 text-indigo-400 px-1.5 py-0.5 rounded font-mono font-bold">MDM</span>
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Bottom User & Role Profile -->
            <div class="p-4 border-t border-slate-800/80 bg-slate-950/70">
                <div class="flex items-center gap-3 p-2 rounded-xl bg-slate-900 border border-slate-800">
                    <div class="w-9 h-9 rounded-lg bg-gradient-to-tr from-slate-700 to-slate-600 flex items-center justify-center text-white font-bold text-sm">
                        AD
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-white truncate">Admin Demo</p>
                        <p class="text-[10px] text-emerald-400 flex items-center gap-1 font-medium">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            Role: <span x-text="activeRole"></span>
                        </p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">
            <!-- Topbar Header -->
            <header class="h-20 bg-white border-b border-slate-200/80 px-6 flex items-center justify-between sticky top-0 z-40 shadow-sm">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-slate-500 hover:text-slate-900 p-2 rounded-lg hover:bg-slate-100">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>
                    <div>
                        <h1 class="text-base font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                            {{ $headerTitle ?? 'Dashboard Operasional' }}
                            @if(isset($badge))
                                <span class="px-2 py-0.5 rounded-md text-xs font-semibold bg-rose-50 text-rose-600 border border-rose-200">
                                    {{ $badge }}
                                </span>
                            @endif
                        </h1>
                        <p class="text-[11px] text-slate-500 font-medium hidden sm:block">PT Wahana Putra Yudha &bull; Telkomsel Distribution Management System</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Quick Role Switcher for Demo -->
                    <div class="hidden md:flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl border border-slate-200 text-xs">
                        <span class="text-slate-400 text-[11px] px-2 font-medium">Demo Role:</span>
                        <button @click="activeRole = 'Super Admin'" :class="activeRole === 'Super Admin' ? 'bg-white shadow text-slate-900 font-bold' : 'text-slate-600 hover:text-slate-900'" class="px-2.5 py-1 rounded-lg transition-all">Super Admin</button>
                        <button @click="activeRole = 'Finance'" :class="activeRole === 'Finance' ? 'bg-white shadow text-slate-900 font-bold' : 'text-slate-600 hover:text-slate-900'" class="px-2.5 py-1 rounded-lg transition-all">Finance</button>
                        <button @click="activeRole = 'Warehouse'" :class="activeRole === 'Warehouse' ? 'bg-white shadow text-slate-900 font-bold' : 'text-slate-600 hover:text-slate-900'" class="px-2.5 py-1 rounded-lg transition-all">Gudang</button>
                    </div>

                    <!-- Notification Pill -->
                    <div class="relative">
                        <button class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200/80 flex items-center justify-center text-slate-600 transition-colors relative">
                            <i data-lucide="bell" class="w-5 h-5"></i>
                            <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-rose-500 ring-2 ring-white animate-pulse"></span>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Page Body -->
            <main class="flex-1 overflow-y-auto p-6 md:p-8">
                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- FLOATING AI ASSISTANT (NISA - VIRTUAL ERP OPERATIONS COPILOT) -->
    <!-- ============================================================ -->
    <div x-data="aiAssistantWidget()" class="fixed bottom-6 right-6 z-50 flex flex-col items-end">
        
        <!-- Jendela Chat Popover AI -->
        <div x-show="isOpen" 
             x-cloak 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-8 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-8 scale-95"
             class="w-[370px] sm:w-[410px] h-[540px] max-h-[85vh] bg-white rounded-3xl shadow-2xl border border-slate-200/90 flex flex-col overflow-hidden mb-3.5 backdrop-blur-md">
            
            <!-- Header Chat Widget -->
            <div class="px-4 py-3.5 bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white flex items-center justify-between border-b border-slate-800 shadow-sm shrink-0">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <img src="{{ asset('images/avatar.jpeg') }}" 
                             alt="Nisa AI Assistant" 
                             class="w-11 h-11 rounded-full object-cover border-2 border-rose-400 shadow-md bg-white">
                        <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full bg-emerald-400 border-2 border-slate-900 animate-pulse"></span>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <h4 class="font-extrabold text-sm text-white tracking-tight">Nisa</h4>
                            <span class="px-1.5 py-0.2 rounded text-[9px] font-extrabold bg-gradient-to-r from-rose-500 to-pink-500 text-white uppercase tracking-wider">
                                AI Copilot
                            </span>
                        </div>
                        <p class="text-[10px] text-slate-300 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            <span>Virtual Operations Assistant &bull; PT WPY</span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-1 text-slate-400">
                    <button type="button" @click="clearChat()" title="Bersihkan Percakapan" class="p-1.5 rounded-lg hover:bg-slate-800 hover:text-white transition-colors">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </button>
                    <button type="button" @click="isOpen = false" title="Tutup Chat" class="p-1.5 rounded-lg hover:bg-slate-800 hover:text-white transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- Body Percakapan (Scrollable) -->
            <div id="aiChatMessagesBox" class="flex-1 p-4 overflow-y-auto space-y-3.5 bg-slate-50/80 text-xs">
                
                <!-- Pesan Sambutan Awal dari Nisa -->
                <div class="flex items-start gap-2.5">
                    <img src="{{ asset('images/avatar.jpeg') }}" 
                         alt="Nisa" 
                         class="w-8 h-8 rounded-full object-cover border border-rose-300 shrink-0 mt-0.5 shadow-xs bg-white">
                    <div class="bg-white border border-slate-200 p-3.5 rounded-2xl rounded-tl-xs shadow-xs text-slate-800 max-w-[85%] leading-relaxed">
                        <p class="font-semibold text-slate-900 mb-1">Halo! Saya <span class="text-rose-600 font-extrabold">Nisa</span> 👋</p>
                        <p class="text-[11px] text-slate-600">
                            Asisten pintar ERP PT Wahana Putra Yudha. Butuh info penjualan sales, stok gudang, piutang toko, kas belum setor, atau tracking GPS? Tanyakan saja ya!
                        </p>
                    </div>
                </div>

                <!-- Quick Prompt Pills (Pertanyaan Cepat) -->
                <div class="pt-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-2 px-1">
                        Pertanyaan Cepat Rekomendasi:
                    </span>
                    <div class="flex flex-wrap gap-1.5">
                        <button type="button" 
                                @click="askQuick('Siapa sales dengan omzet tertinggi hari ini?')"
                                class="px-2.5 py-1.5 bg-white hover:bg-rose-50 text-slate-700 hover:text-rose-700 font-bold rounded-xl border border-slate-200 hover:border-rose-300 shadow-xs text-[11px] flex items-center gap-1.5 transition-all text-left">
                            <span>🏆 Top Sales Leaderboard</span>
                        </button>
                        <button type="button" 
                                @click="askQuick('Berapa sisa stok barang di gudang saat ini?')"
                                class="px-2.5 py-1.5 bg-white hover:bg-indigo-50 text-slate-700 hover:text-indigo-700 font-bold rounded-xl border border-slate-200 hover:border-indigo-300 shadow-xs text-[11px] flex items-center gap-1.5 transition-all text-left">
                            <span>📦 Cek Stok Gudang</span>
                        </button>
                        <button type="button" 
                                @click="askQuick('Toko mana saja yang piutangnya belum lunas?')"
                                class="px-2.5 py-1.5 bg-white hover:bg-amber-50 text-slate-700 hover:text-amber-800 font-bold rounded-xl border border-slate-200 hover:border-amber-300 shadow-xs text-[11px] flex items-center gap-1.5 transition-all text-left">
                            <span>💰 Piutang Toko (AR)</span>
                        </button>
                        <button type="button" 
                                @click="askQuick('Ada berapa uang kasir yang belum disetor ke bank (BBM)?')"
                                class="px-2.5 py-1.5 bg-white hover:bg-emerald-50 text-slate-700 hover:text-emerald-800 font-bold rounded-xl border border-slate-200 hover:border-emerald-300 shadow-xs text-[11px] flex items-center gap-1.5 transition-all text-left">
                            <span>🏦 Kasir Belum Setor (BBM)</span>
                        </button>
                        <button type="button" 
                                @click="askQuick('Bagaimana status live tracking sales di lapangan?')"
                                class="px-2.5 py-1.5 bg-white hover:bg-purple-50 text-slate-700 hover:text-purple-800 font-bold rounded-xl border border-slate-200 hover:border-purple-300 shadow-xs text-[11px] flex items-center gap-1.5 transition-all text-left">
                            <span>📍 Tracking GPS Sales</span>
                        </button>
                        <button type="button" 
                                @click="askQuick('Jelaskan alur TTK Tunai ke BBM Tunai')"
                                class="px-2.5 py-1.5 bg-white hover:bg-blue-50 text-slate-700 hover:text-blue-800 font-bold rounded-xl border border-slate-200 hover:border-blue-300 shadow-xs text-[11px] flex items-center gap-1.5 transition-all text-left">
                            <span>ℹ️ Alur TTK ke BBM</span>
                        </button>
                    </div>
                </div>

                <!-- Loop Pesan Percakapan -->
                <template x-for="(msg, index) in messages" :key="index">
                    <div>
                        <!-- Pesan User (Kanan) -->
                        <template x-if="msg.sender === 'user'">
                            <div class="flex justify-end mb-2">
                                <div class="bg-gradient-to-r from-rose-600 to-indigo-600 text-white p-3 rounded-2xl rounded-tr-xs shadow-xs max-w-[85%] leading-relaxed font-medium">
                                    <p x-text="msg.text" class="text-[11.5px]"></p>
                                    <span class="block text-right text-[9px] text-white/70 mt-1" x-text="msg.time"></span>
                                </div>
                            </div>
                        </template>

                        <!-- Pesan Nisa (Kiri) -->
                        <template x-if="msg.sender === 'nisa'">
                            <div class="flex items-start gap-2.5 mb-2">
                                <img src="{{ asset('images/avatar.jpeg') }}" 
                                     alt="Nisa" 
                                     class="w-8 h-8 rounded-full object-cover border border-rose-300 shrink-0 mt-0.5 shadow-xs bg-white">
                                <div class="bg-white border border-slate-200 p-3.5 rounded-2xl rounded-tl-xs shadow-xs text-slate-800 max-w-[85%] leading-relaxed">
                                    <div class="text-[11.5px] space-y-1.5 whitespace-pre-line" x-html="formatMessage(msg.text)"></div>
                                    <span class="block text-right text-[9px] text-slate-400 mt-1" x-text="msg.time"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- Animasi Nisa Sedang Mengetik -->
                <div x-show="isTyping" x-cloak class="flex items-start gap-2.5">
                    <img src="{{ asset('images/avatar.jpeg') }}" 
                         alt="Nisa" 
                         class="w-8 h-8 rounded-full object-cover border border-rose-300 shrink-0 mt-0.5 shadow-xs bg-white">
                    <div class="bg-white border border-slate-200 px-3.5 py-2.5 rounded-2xl rounded-tl-xs shadow-xs flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-rose-500 animate-bounce"></span>
                        <span class="w-2 h-2 rounded-full bg-indigo-500 animate-bounce" style="animation-delay: 0.15s"></span>
                        <span class="w-2 h-2 rounded-full bg-purple-500 animate-bounce" style="animation-delay: 0.3s"></span>
                        <span class="text-[10px] text-slate-400 ml-1 font-semibold">Nisa sedang berpikir...</span>
                    </div>
                </div>

            </div>

            <!-- Footer Input Chat -->
            <div class="p-3 bg-white border-t border-slate-200/90 shrink-0">
                <div class="flex items-center gap-2">
                    <input type="text" 
                           x-model="inputQuery"
                           @keydown.enter.prevent="sendMessage()"
                           placeholder="Ketik pertanyaan untuk Nisa..." 
                           class="flex-1 bg-slate-100 hover:bg-slate-50 focus:bg-white border border-slate-200 focus:border-rose-500 rounded-xl px-3.5 py-2 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-500/20 font-medium transition-all">
                    <button type="button" 
                            @click="sendMessage()"
                            :disabled="!inputQuery.trim() || isTyping"
                            class="w-9 h-9 rounded-xl bg-gradient-to-tr from-rose-600 via-pink-600 to-indigo-600 hover:opacity-90 disabled:opacity-40 disabled:cursor-not-allowed text-white flex items-center justify-center shadow-md transition-all shrink-0">
                        <i data-lucide="send" class="w-4 h-4"></i>
                    </button>
                </div>
                <div class="mt-1.5 flex items-center justify-between text-[10px] text-slate-400 px-1">
                    <span>✨ AI Virtual Copilot</span>
                    <span class="font-mono">PT Wahana Putra Yudha</span>
                </div>
            </div>
        </div>

        <!-- Floating Button (FAB) & Greeting Badge -->
        <div class="flex items-center gap-3">
            <!-- Greeting Tooltip Pill -->
            <div x-show="showGreeting && !isOpen" 
                 x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-x-4"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 class="bg-white/95 border border-slate-200/80 py-1.5 px-3 rounded-2xl shadow-xl flex items-center gap-2 text-xs font-bold text-slate-800 backdrop-blur-sm cursor-pointer hover:bg-slate-50 transition-all"
                 @click="isOpen = true; showGreeting = false;">
                <span class="flex h-2 w-2 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                </span>
                <span>Tanya Nisa (AI Copilot) ✨</span>
                <button type="button" @click.stop="showGreeting = false" class="text-slate-400 hover:text-slate-600 ml-1">
                    <i data-lucide="x" class="w-3 h-3"></i>
                </button>
            </div>

            <!-- Tombol Utama Bulat (FAB) -->
            <button type="button" 
                    @click="isOpen = !isOpen; showGreeting = false; $nextTick(() => lucide.createIcons());"
                    class="w-14 h-14 rounded-full bg-gradient-to-tr from-rose-600 via-purple-600 to-indigo-600 p-0.5 text-white shadow-2xl hover:scale-105 active:scale-95 transition-all relative flex items-center justify-center group focus:outline-none focus:ring-4 focus:ring-rose-500/30">
                <div class="w-full h-full rounded-full overflow-hidden relative border-2 border-white/90 bg-white">
                    <img src="{{ asset('images/avatar.jpeg') }}" 
                         alt="Nisa AI Assistant" 
                         class="w-full h-full object-cover">
                </div>
                <!-- Status Dot Hijau Online -->
                <span class="absolute bottom-0 right-0 w-4 h-4 rounded-full bg-emerald-500 border-2 border-white shadow-sm flex items-center justify-center">
                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                </span>
            </button>
        </div>

    </div>

    <!-- Initialize Lucide Icons & Global App State -->
    <script>
        function globalApp() {
            return {
                sidebarOpen: false,
                activeRole: 'Super Admin',
                isLoading: false,
                loadingTitle: 'Memproses Transaksi...',
                loadingSubtitle: 'Sedang memvalidasi stok & menerbitkan dokumen...',
                loadingDocType: '',
                toast: {
                    show: false,
                    message: '',
                    type: 'success',
                    title: 'Berhasil'
                },

                showLoading(title = 'Memproses Permintaan...', subtitle = 'Sedang sinkronisasi database ERP...', docType = '') {
                    this.loadingTitle = title;
                    this.loadingSubtitle = subtitle;
                    this.loadingDocType = docType;
                    this.isLoading = true;
                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                },

                hideLoading() {
                    this.isLoading = false;
                },

                showToast(message, type = 'success', title = 'Berhasil') {
                    this.toast.message = message;
                    this.toast.type = type;
                    this.toast.title = title;
                    this.toast.show = true;
                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                    setTimeout(() => {
                        this.toast.show = false;
                    }, 4000);
                },

                initApp() {
                    @if(session('success'))
                        this.showToast(@json(session('success')), 'success', 'Operasi Berhasil');
                    @endif
                    @if(session('error'))
                        this.showToast(@json(session('error')), 'error', 'Perhatian');
                    @endif

                    // Otomatis intercept semua form submit untuk menampilkan loading modern (kecuali chat AI & no-loading)
                    document.addEventListener('submit', (e) => {
                        const form = e.target;
                        if (form && form.tagName && form.tagName.toLowerCase() === 'form') {
                            if (form.closest('[x-data*="aiAssistantWidget"]') || form.hasAttribute('data-no-loading')) {
                                return;
                            }
                            const customTitle = form.getAttribute('data-loading-title') || 'Memproses Transaksi...';
                            const customSub = form.getAttribute('data-loading-sub') || 'Menyimpan perubahan ke database ERP & menerbitkan dokumen...';
                            const docType = form.getAttribute('data-doctype') || '';
                            this.showLoading(customTitle, customSub, docType);
                        }
                    });
                }
            }
        }

        // Window Helper Functions
        window.showLoading = function(title, subtitle, docType) {
            window.dispatchEvent(new CustomEvent('show-global-loading', { 
                detail: { title, subtitle, docType } 
            }));
        };

        window.hideLoading = function() {
            window.dispatchEvent(new CustomEvent('hide-global-loading'));
        };

        window.showToast = function(message, type = 'success', title = 'Berhasil') {
            window.dispatchEvent(new CustomEvent('show-global-toast', { 
                detail: { message, type, title } 
            }));
        };

        document.addEventListener("DOMContentLoaded", function() {
            lucide.createIcons();
        });

        // ============================================
        // ALPINE JS COMPONENT: NISA AI ASSISTANT WIDGET
        // ============================================
        function aiAssistantWidget() {
            return {
                isOpen: false,
                showGreeting: true,
                isTyping: false,
                inputQuery: '',
                messages: [],

                init() {
                    setTimeout(() => {
                        this.showGreeting = false;
                    }, 12000);
                },

                askQuick(query) {
                    this.inputQuery = query;
                    this.sendMessage();
                },

                clearChat() {
                    this.messages = [];
                },

                scrollToBottom() {
                    this.$nextTick(() => {
                        const box = document.getElementById('aiChatMessagesBox');
                        if (box) {
                            box.scrollTop = box.scrollHeight;
                        }
                    });
                },

                formatMessage(text) {
                    if (!text) return '';
                    return text
                        .replace(/\*\*(.*?)\*\*/g, '<strong class="font-extrabold text-slate-900">$1</strong>')
                        .replace(/\n/g, '<br>');
                },

                async sendMessage() {
                    const text = this.inputQuery.trim();
                    if (!text || this.isTyping) return;

                    const now = new Date();
                    const timeStr = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');

                    // 1. Tambahkan pesan user
                    this.messages.push({
                        sender: 'user',
                        text: text,
                        time: timeStr
                    });

                    this.inputQuery = '';
                    this.isTyping = true;
                    this.scrollToBottom();

                    try {
                        const response = await fetch('{{ route('ai.chat') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ message: text })
                        });

                        const data = await response.json();
                        
                        setTimeout(() => {
                            this.isTyping = false;
                            this.messages.push({
                                sender: 'nisa',
                                text: data.reply || 'Maaf, saya belum memahami pertanyaan tersebut. Bisa tanyakan hal lain seputar operasional ERP?',
                                time: data.timestamp || timeStr
                            });
                            this.scrollToBottom();
                            this.$nextTick(() => {
                                lucide.createIcons();
                            });
                        }, 400);

                    } catch (error) {
                        this.isTyping = false;
                        this.messages.push({
                            sender: 'nisa',
                            text: 'Maaf, terjadi kendala saat menghubungkan ke asisten AI. Silakan coba lagi sebentar ya.',
                            time: timeStr
                        });
                        this.scrollToBottom();
                    }
                }
            }
        }
    </script>
    @stack('scripts')
</body>
</html>
