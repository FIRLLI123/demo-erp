<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} | DISTRI-ERP - Telco Distribution Management</title>

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

    <!-- Alpine.js -->
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
                            <span class="font-extrabold text-lg text-white tracking-tight">DISTRI<span class="text-rose-500">POS</span></span>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30">ERP v1.0</span>
                        </div>
                        <p class="text-[11px] text-slate-400 font-medium">Telco Distribution System</p>
                    </div>
                </div>
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Organization Context Badge -->
            <div class="px-5 py-3 border-b border-slate-800/60 bg-slate-900/40">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-400 flex items-center gap-1.5">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-rose-400"></i> Area Aktif
                    </span>
                    <span class="font-semibold text-white bg-slate-800 px-2 py-0.5 rounded text-[11px] border border-slate-700/60">
                        Jabodetabek
                    </span>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-6">
                <!-- Group: CORE -->
                <div>
                    <div class="px-3 mb-2 text-[11px] font-bold tracking-wider text-slate-400 uppercase">Core System</div>
                    <div class="space-y-1">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-rose-600 to-red-600 text-white shadow-lg shadow-red-600/30' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <i data-lucide="layout-dashboard" class="w-4 h-4 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-400' }}"></i>
                            <span>Dashboard Visual</span>
                        </a>
                    </div>
                </div>

                <!-- Group: MASTER HIERARKI -->
                <div>
                    <div class="px-3 mb-2 text-[11px] font-bold tracking-wider text-slate-400 uppercase">Master & Organisasi</div>
                    <div class="space-y-1">
                        <a href="{{ route('master.organization') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('master.organization*') ? 'bg-slate-800 text-white border-l-4 border-rose-500' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <div class="flex items-center gap-3">
                                <i data-lucide="network" class="w-4 h-4 text-slate-400"></i>
                                <span>Area, Cluster, TAP</span>
                            </div>
                            <span class="text-[10px] bg-slate-800 px-1.5 py-0.5 rounded text-slate-400 border border-slate-700">4 Level</span>
                        </a>
                        <a href="{{ route('master.products') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('master.products*') ? 'bg-slate-800 text-white border-l-4 border-rose-500' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <i data-lucide="package" class="w-4 h-4 text-slate-400"></i>
                            <span>Katalog Produk (SA/Voucher)</span>
                        </a>
                        <a href="{{ route('master.warehouses') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('master.warehouses*') ? 'bg-slate-800 text-white border-l-4 border-rose-500' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <i data-lucide="warehouse" class="w-4 h-4 text-slate-400"></i>
                            <span>Gudang (Main, Inv, Sales)</span>
                        </a>
                    </div>
                </div>

                <!-- Group: PENGADAAN & STOK -->
                <div>
                    <div class="px-3 mb-2 text-[11px] font-bold tracking-wider text-slate-400 uppercase flex items-center justify-between">
                        <span>Pengadaan & Stok</span>
                        <span class="text-[9px] font-mono text-slate-400">PO • GR • SM</span>
                    </div>
                    <div class="space-y-1">
                        <a href="{{ route('procurement.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('procurement*') ? 'bg-slate-800 text-white border-l-4 border-rose-500' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <div class="flex items-center gap-3">
                                <i data-lucide="shopping-cart" class="w-4 h-4 text-emerald-400"></i>
                                <span>PO & Goods Received (GR)</span>
                            </div>
                            <span class="text-[10px] bg-emerald-500/20 text-emerald-400 px-1.5 py-0.5 rounded font-mono font-bold">PO-GR</span>
                        </a>
                        <a href="{{ route('inventory.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('inventory*') ? 'bg-slate-800 text-white border-l-4 border-rose-500' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <div class="flex items-center gap-3">
                                <i data-lucide="boxes" class="w-4 h-4 text-cyan-400"></i>
                                <span>Stok Gudang & Mutasi (SM)</span>
                            </div>
                            <span class="text-[10px] bg-cyan-500/20 text-cyan-400 px-1.5 py-0.5 rounded font-mono font-bold">SM</span>
                        </a>
                        <a href="{{ route('inventory.repack') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('inventory.repack*') ? 'bg-slate-800 text-white border-l-4 border-rose-500' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <div class="flex items-center gap-3">
                                <i data-lucide="refresh-cw" class="w-4 h-4 text-indigo-400"></i>
                                <span>Repack In & Out</span>
                            </div>
                            <span class="text-[10px] bg-indigo-500/20 text-indigo-400 px-1.5 py-0.5 rounded font-mono font-bold">RO-RI</span>
                        </a>
                    </div>
                </div>

                <!-- Group: PENJUALAN & KEUANGAN -->
                <div>
                    <div class="px-3 mb-2 text-[11px] font-bold tracking-wider text-slate-400 uppercase flex items-center justify-between">
                        <span>Penjualan & Piutang</span>
                        <span class="text-[9px] font-mono text-slate-400">SO • GD • AR • FP</span>
                    </div>
                    <div class="space-y-1">
                        <a href="{{ route('sales.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('sales*') ? 'bg-slate-800 text-white border-l-4 border-rose-500' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <div class="flex items-center gap-3">
                                <i data-lucide="badge-dollar-sign" class="w-4 h-4 text-amber-400"></i>
                                <span>Sales Order & GD</span>
                            </div>
                            <span class="text-[10px] bg-amber-500/20 text-amber-400 px-1.5 py-0.5 rounded font-mono font-bold">SO-GD</span>
                        </a>
                        <a href="{{ route('finance.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('finance*') ? 'bg-slate-800 text-white border-l-4 border-rose-500' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <div class="flex items-center gap-3">
                                <i data-lucide="receipt" class="w-4 h-4 text-rose-400"></i>
                                <span>Piutang (AR) & Pelunasan</span>
                            </div>
                            <span class="text-[10px] bg-rose-500/20 text-rose-400 px-1.5 py-0.5 rounded font-mono font-bold">AR-FP</span>
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
                        <h1 class="text-lg font-bold text-slate-900 tracking-tight flex items-center gap-2">
                            {{ $headerTitle ?? 'Dashboard Operasional' }}
                            @if(isset($badge))
                                <span class="px-2 py-0.5 rounded-md text-xs font-semibold bg-rose-50 text-rose-600 border border-rose-200">
                                    {{ $badge }}
                                </span>
                            @endif
                        </h1>
                        <p class="text-xs text-slate-500 hidden sm:block">Sistem Pengelolaan Stok & Penjualan Distributor Telkomsel</p>
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
                {{ $slot }}
            </main>
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

                    // Otomatis intercept semua form submit untuk menampilkan loading modern
                    document.addEventListener('submit', (e) => {
                        const form = e.target;
                        if (form.tagName.toLowerCase() === 'form') {
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
    </script>
    @stack('scripts')
</body>
</html>
