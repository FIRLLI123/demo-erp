<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login Masuk &bull; WAHANA POS - PT Wahana Putra Yudha</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#fff1f2',
                            100: '#ffe4e6',
                            500: '#f43f5e',
                            600: '#e11d48',
                            700: '#be123c',
                            800: '#9f1239',
                            900: '#881337',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js & Lucide Icons -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        [x-cloak] { display: none !important; }
        .bg-grid-pattern {
            background-size: 32px 32px;
            background-image: 
                linear-gradient(to right, rgba(255, 255, 255, 0.04) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
        }
    </style>
</head>
<body class="h-full font-sans text-slate-100 antialiased bg-slate-950 flex items-center justify-center p-4 relative overflow-x-hidden selection:bg-rose-500 selection:text-white"
      x-data="loginPageApp()">

    <!-- Ambient Glow Orbs -->
    <div class="fixed top-[-10%] left-[-10%] w-[500px] h-[500px] rounded-full bg-rose-600/15 blur-[120px] pointer-events-none"></div>
    <div class="fixed bottom-[-10%] right-[-10%] w-[550px] h-[550px] rounded-full bg-amber-500/10 blur-[140px] pointer-events-none"></div>
    <div class="fixed inset-0 bg-grid-pattern pointer-events-none"></div>

    <div class="w-full max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-8 items-center relative z-10 py-6">

        <!-- ============================================== -->
        <!-- KOLOM KIRI: BRANDING & HIGHLIGHT DISTRIBUTOR  -->
        <!-- ============================================== -->
        <div class="lg:col-span-6 space-y-6 text-left">
            <!-- Brand Badge -->
            <div class="inline-flex items-center gap-2.5 px-3.5 py-1.5 rounded-full bg-slate-900/90 border border-slate-800 shadow-xl">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-[11px] font-bold text-slate-300 uppercase tracking-wider">Enterprise Distribution Engine v2.6</span>
            </div>

            <!-- Title & Pitch -->
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-rose-600 via-red-500 to-amber-500 flex items-center justify-center text-white shadow-xl shadow-rose-600/30">
                        <i data-lucide="layers" class="w-7 h-7"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-extrabold tracking-tight text-white">
                            WAHANA <span class="text-transparent bg-clip-text bg-gradient-to-r from-rose-500 via-red-400 to-amber-400">POS</span>
                        </h1>
                        <p class="text-xs font-mono font-bold text-rose-400 tracking-wider">TELKOMSEL AUTHORIZED PARTNER</p>
                    </div>
                </div>

                <h2 class="text-xl sm:text-2xl font-bold text-slate-200 leading-snug">
                    PT Wahana Putra Yudha
                </h2>
                <p class="text-xs text-slate-400 leading-relaxed max-w-lg">
                    Sistem operasional terintegrasi untuk distribusi produk Telkomsel: manajemen per TAP, tracking serial ICCID kartu perdana, voucher fisik, distribusi sales canvas, dan mutasi kasir.
                </p>
            </div>

            <!-- Features Highlights Grid -->
            <div class="grid grid-cols-2 gap-3.5 pt-2">
                <div class="p-3.5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm">
                    <div class="w-7 h-7 rounded-lg bg-rose-500/10 text-rose-400 flex items-center justify-center mb-2">
                        <i data-lucide="building-2" class="w-4 h-4"></i>
                    </div>
                    <h4 class="text-xs font-bold text-white">Multi-TAP Isolation</h4>
                    <p class="text-[11px] text-slate-400 mt-0.5">Operasional dibatasi per kantor cabang TAP masing-masing.</p>
                </div>

                <div class="p-3.5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm">
                    <div class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center mb-2">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                    </div>
                    <h4 class="text-xs font-bold text-white">IT DEV Omni Access</h4>
                    <p class="text-[11px] text-slate-400 mt-0.5">Akses pengawasan penuh konsolidasi ke seluruh wilayah TAP.</p>
                </div>

                <div class="p-3.5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm">
                    <div class="w-7 h-7 rounded-lg bg-cyan-500/10 text-cyan-400 flex items-center justify-center mb-2">
                        <i data-lucide="scan-barcode" class="w-4 h-4"></i>
                    </div>
                    <h4 class="text-xs font-bold text-white">ICCID vs Qty Tracking</h4>
                    <p class="text-[11px] text-slate-400 mt-0.5">Pencatatan nomor seri barcode SA dan stok quantity voucher.</p>
                </div>

                <div class="p-3.5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm">
                    <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-2">
                        <i data-lucide="file-check-2" class="w-4 h-4"></i>
                    </div>
                    <h4 class="text-xs font-bold text-white">Real-Time Audit Trail</h4>
                    <p class="text-[11px] text-slate-400 mt-0.5">Seluruh aktivitas login dan mutasi tercatat aman di log audit.</p>
                </div>
            </div>

            <!-- Status Indicator -->
            <div class="flex items-center gap-2 text-xs text-slate-500 pt-2 font-mono">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                <span>Server Database: Laragon MySQL [demo_erp] Connected</span>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- KOLOM KANAN: CARD FORM LOGIN WAHANA POS       -->
        <!-- ============================================== -->
        <div class="lg:col-span-6">
            <div class="bg-slate-900/90 backdrop-blur-xl border border-slate-800 rounded-3xl p-7 sm:p-9 shadow-2xl shadow-black/60 relative overflow-hidden">
                
                <!-- Shimmer Top Line -->
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-rose-600 via-amber-400 to-rose-600"></div>

                <!-- Form Header -->
                <div class="mb-6 text-left">
                    <h3 class="text-xl font-bold text-white">Masuk ke Sistem</h3>
                    <p class="text-xs text-slate-400 mt-1">Silakan masukkan kredensial akun terdaftar untuk memulai sesi.</p>
                </div>

                <!-- Alert Session Messages -->
                @if(session('success'))
                    <div class="mb-5 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs flex items-center gap-2">
                        <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(isset($errors) && $errors->any())
                    <div class="mb-5 p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs flex items-start gap-2.5">
                        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 mt-0.5"></i>
                        <div>
                            <strong class="font-bold block">Autentikasi Gagal:</strong>
                            <span class="text-[11px] leading-relaxed">{{ $errors->first() }}</span>
                        </div>
                    </div>
                @endif

                <!-- Form Login -->
                <form method="POST" action="{{ route('login.post') }}" class="space-y-4 text-left" @submit="isSubmitting = true">
                    @csrf

                    <!-- Email Field -->
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1.5">Alamat Email Pengguna</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                <i data-lucide="mail" class="w-4 h-4"></i>
                            </div>
                            <input type="email" 
                                   name="email" 
                                   x-model="email" 
                                   required 
                                   autofocus
                                   placeholder="nama@wahanapos.com" 
                                   class="w-full text-xs font-semibold bg-slate-950/80 border border-slate-700/80 rounded-xl pl-10 pr-3.5 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 transition">
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-slate-300">Kata Sandi</label>
                            <span class="text-[11px] text-slate-500">Default: password123</span>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                            </div>
                            <input :type="showPassword ? 'text' : 'password'" 
                                   name="password" 
                                   x-model="password" 
                                   required 
                                   placeholder="Masukkan kata sandi" 
                                   class="w-full text-xs font-semibold bg-slate-950/80 border border-slate-700/80 rounded-xl pl-10 pr-10 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 transition">
                            <button type="button" 
                                    @click="showPassword = !showPassword" 
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300 transition">
                                <template x-if="!showPassword">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </template>
                                <template x-if="showPassword">
                                    <i data-lucide="eye-off" class="w-4 h-4"></i>
                                </template>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between pt-1">
                        <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-700 bg-slate-950 text-rose-600 focus:ring-rose-500/30">
                            <span class="text-xs text-slate-400 font-medium">Ingat saya di perangkat ini</span>
                        </label>
                        <span class="text-[11px] text-rose-400 font-medium hover:underline cursor-pointer">Bantuan IT?</span>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            :disabled="isSubmitting"
                            class="w-full mt-2 py-3 px-4 rounded-xl font-bold text-xs bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white shadow-lg shadow-rose-600/30 hover:shadow-rose-600/40 transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50">
                        <template x-if="isSubmitting">
                            <span class="flex items-center gap-2">
                                <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                                <span>Memverifikasi Hak Akses...</span>
                            </span>
                        </template>
                        <template x-if="!isSubmitting">
                            <span class="flex items-center gap-2">
                                <span>Masuk ke Wahana POS</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </span>
                        </template>
                    </button>
                </form>

                <!-- ============================================== -->
                <!-- SHORTCUT DEMO SELECTOR (UNTUK KUNJUNGAN KLIEN) -->
                <!-- ============================================== -->
                <div class="mt-6 pt-5 border-t border-slate-800/80 text-left">
                    <div class="flex items-center justify-between mb-2.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1">
                            <i data-lucide="zap" class="w-3.5 h-3.5 text-amber-400"></i>
                            <span>Pilih Cepat Akun Demo (Preset Hak Akses)</span>
                        </span>
                        <span class="text-[10px] text-slate-500 font-mono">Password: password123</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <!-- IT DEV Omni -->
                        <button type="button" 
                                @click="fillPreset('itdev@wahanapos.com', 'password123')"
                                class="p-2.5 rounded-xl border border-rose-500/30 bg-rose-500/10 hover:bg-rose-500/20 text-left transition cursor-pointer group">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-white group-hover:text-rose-300">IT Developer</span>
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-rose-600 text-white font-mono">ALL AREA</span>
                            </div>
                            <span class="text-[10px] text-slate-400 block truncate mt-0.5">itdev@wahanapos.com</span>
                        </button>

                        <!-- TAP Pasar Minggu -->
                        <button type="button" 
                                @click="fillPreset('admin.pasarminggu@wahanapos.com', 'password123')"
                                class="p-2.5 rounded-xl border border-slate-800 bg-slate-950/60 hover:bg-slate-800/60 text-left transition cursor-pointer group">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-200 group-hover:text-white">Admin Cabang</span>
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-500/20 text-amber-300 font-mono">TAP PS. MINGGU</span>
                            </div>
                            <span class="text-[10px] text-slate-400 block truncate mt-0.5">admin.pasarminggu@wahanapos.com</span>
                        </button>

                        <!-- TAP Blok M -->
                        <button type="button" 
                                @click="fillPreset('admin.blokm@wahanapos.com', 'password123')"
                                class="p-2.5 rounded-xl border border-slate-800 bg-slate-950/60 hover:bg-slate-800/60 text-left transition cursor-pointer group">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-200 group-hover:text-white">Admin Cabang</span>
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-cyan-500/20 text-cyan-300 font-mono">TAP BLOK M</span>
                            </div>
                            <span class="text-[10px] text-slate-400 block truncate mt-0.5">admin.blokm@wahanapos.com</span>
                        </button>

                        <!-- Sales Force -->
                        <button type="button" 
                                @click="fillPreset('rian@wahanaputrayudha.co.id', 'password123')"
                                class="p-2.5 rounded-xl border border-slate-800 bg-slate-950/60 hover:bg-slate-800/60 text-left transition cursor-pointer group">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-200 group-hover:text-white">Sales Canvasing</span>
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-500/20 text-emerald-300 font-mono">SLS-001</span>
                            </div>
                            <span class="text-[10px] text-slate-400 block truncate mt-0.5">rian@wahanaputrayudha.co.id</span>
                        </button>
                    </div>
                </div>

                <!-- Footer Copyright -->
                <div class="mt-5 text-center text-[10px] text-slate-500">
                    &copy; 2026 PT Wahana Putra Yudha. Hak Cipta Dilindungi Undang-Undang.
                </div>
            </div>
        </div>

    </div>

    <script>
        function loginPageApp() {
            return {
                email: '{{ old('email', 'itdev@wahanapos.com') }}',
                password: 'password123',
                showPassword: false,
                isSubmitting: false,

                fillPreset(email, password) {
                    this.email = email;
                    this.password = password;
                }
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            lucide.createIcons();
        });
    </script>
</body>
</html>
