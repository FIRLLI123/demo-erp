<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login Masuk &bull; DistriPOS ERP</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
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
        
        /* Subtle entrance animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fade-in {
            animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* Subtle ambient grid pattern */
        .bg-subtle-pattern {
            background-size: 28px 28px;
            background-image: 
                linear-gradient(to right, rgba(148, 163, 184, 0.08) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(148, 163, 184, 0.08) 1px, transparent 1px);
        }
    </style>
</head>
<body class="h-full font-sans text-slate-800 antialiased bg-slate-50 bg-subtle-pattern flex items-center justify-center p-4 relative selection:bg-rose-500 selection:text-white"
      x-data="cleanLoginApp()">

    <!-- Subtle Ambient Glow Orbs -->
    <div class="fixed top-[-10%] left-[-5%] w-[450px] h-[450px] rounded-full bg-rose-500/8 blur-[100px] pointer-events-none"></div>
    <div class="fixed bottom-[-10%] right-[-5%] w-[450px] h-[450px] rounded-full bg-amber-500/6 blur-[110px] pointer-events-none"></div>

    <!-- Login Card Container -->
    <div class="w-full max-w-md mx-auto relative z-10 animate-fade-in">
        
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xl shadow-slate-200/60 p-7 sm:p-9 relative overflow-hidden text-center">

            <!-- Logo & Brand Header -->
            <div class="mb-6">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-rose-600 via-rose-500 to-amber-500 flex items-center justify-center text-white shadow-lg shadow-rose-500/25 mx-auto mb-3.5 transition-transform hover:scale-105 duration-300">
                    <i data-lucide="layers" class="w-7 h-7"></i>
                </div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">DistriPOS ERP</h1>
                <p class="text-xs text-slate-400 font-medium mt-1">
                    Telkomsel Authorized Partner &bull; Enterprise ERP
                </p>
            </div>

            <!-- Flash Alert Messages -->
            @if(session('success'))
                <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs text-left flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4 shrink-0 text-emerald-600"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(isset($errors) && $errors->any())
                <div class="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs text-left flex items-start gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 mt-0.5 text-rose-600"></i>
                    <div class="leading-relaxed">
                        {{ $errors->first() }}
                    </div>
                </div>
            @endif

            <!-- Form Login -->
            <form method="POST" action="{{ route('login.post') }}" class="space-y-4 text-left" @submit="isSubmitting = true">
                @csrf

                <!-- Input Email -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Email Pengguna</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </div>
                        <input type="email" 
                               name="email" 
                               x-model="email" 
                               required 
                               autofocus
                               placeholder="nama@distripos.com" 
                               class="w-full text-xs font-semibold bg-slate-50/80 border border-slate-200 rounded-xl pl-10 pr-3.5 py-3 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-rose-500 focus:bg-white focus:ring-2 focus:ring-rose-500/20 transition">
                    </div>
                </div>

                <!-- Input Password -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-700">Kata Sandi</label>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </div>
                        <input :type="showPassword ? 'text' : 'password'" 
                               name="password" 
                               x-model="password" 
                               required 
                               placeholder="Masukkan kata sandi" 
                               class="w-full text-xs font-semibold bg-slate-50/80 border border-slate-200 rounded-xl pl-10 pr-10 py-3 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-rose-500 focus:bg-white focus:ring-2 focus:ring-rose-500/20 transition">
                        <button type="button" 
                                @click="showPassword = !showPassword" 
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition cursor-pointer">
                            <template x-if="!showPassword">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </template>
                            <template x-if="showPassword">
                                <i data-lucide="eye-off" class="w-4 h-4"></i>
                            </template>
                        </button>
                    </div>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between text-xs pt-0.5">
                    <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-rose-600 focus:ring-rose-500/20 cursor-pointer">
                        <span class="text-slate-600 font-medium">Ingat saya</span>
                    </label>
                    <span class="text-slate-400 font-mono text-[11px]">Pass: password123</span>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        :disabled="isSubmitting"
                        class="w-full mt-2 py-3 px-4 rounded-xl font-bold text-xs bg-rose-600 hover:bg-rose-700 active:scale-[0.99] text-white shadow-md shadow-rose-600/25 transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-60">
                    <template x-if="isSubmitting">
                        <span class="flex items-center gap-2">
                            <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                            <span>Memeriksa Akses...</span>
                        </span>
                    </template>
                    <template x-if="!isSubmitting">
                        <span class="flex items-center gap-2">
                            <span>Masuk ke Sistem</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </span>
                    </template>
                </button>
            </form>

            <!-- Quick Preset Buttons (Minimalis & Rapi) -->
            <div class="mt-6 pt-5 border-t border-slate-100 text-left">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-2">
                    Coba Akun Demo:
                </span>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" 
                            @click="setPreset('it@distripos.com', 'password')"
                            :class="email === 'it@distripos.com' ? 'border-rose-500 bg-rose-50 text-rose-700 font-bold' : 'border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100 font-semibold'"
                            class="px-2.5 py-1.5 rounded-lg border text-[11px] transition text-center cursor-pointer">
                        ⚡ IT Dev (Semua Akses)
                    </button>

                    <button type="button" 
                            @click="setPreset('admin1@distripos.com', 'password')"
                            :class="email === 'admin1@distripos.com' ? 'border-rose-500 bg-rose-50 text-rose-700 font-bold' : 'border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100 font-semibold'"
                            class="px-2.5 py-1.5 rounded-lg border text-[11px] transition text-center cursor-pointer">
                        🏢 Admin 1 (Operasional)
                    </button>

                    <button type="button" 
                            @click="setPreset('admin2@distripos.com', 'password')"
                            :class="email === 'admin2@distripos.com' ? 'border-rose-500 bg-rose-50 text-rose-700 font-bold' : 'border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100 font-semibold'"
                            class="px-2.5 py-1.5 rounded-lg border text-[11px] transition text-center cursor-pointer">
                        🏢 Admin 2 (Operasional)
                    </button>

                    <button type="button" 
                            @click="setPreset('it@distripos.com', 'password')"
                            class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100 font-semibold text-[11px] transition text-center cursor-pointer">
                        🔑 Akun Default
                    </button>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-5 text-center text-[11px] text-slate-400">
                &copy; 2026 Enterprise Distribution System
            </div>

        </div>

    </div>

    <script>
        function cleanLoginApp() {
            return {
                email: '{{ old('email', 'it@distripos.com') }}',
                password: 'password',
                showPassword: false,
                isSubmitting: false,

                setPreset(email, password) {
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
