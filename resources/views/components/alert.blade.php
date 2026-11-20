@if(session('success'))
    <div x-data="{ show: true }" 
         x-show="show" 
         x-init="$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); $el.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); })"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-2 scale-98"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="mb-6 p-4 sm:p-5 rounded-2xl bg-gradient-to-r from-emerald-50 via-emerald-50/80 to-white border-2 border-emerald-500/40 text-emerald-950 text-xs flex items-start justify-between gap-4 shadow-md shadow-emerald-500/5">
        <div class="flex items-start gap-3.5">
            <div class="w-10 h-10 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-emerald-600/20 ring-4 ring-emerald-500/10">
                <i data-lucide="check-circle-2" class="w-5 h-5"></i>
            </div>
            <div class="flex-1 min-w-0 pt-0.5">
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-extrabold uppercase tracking-wider bg-emerald-200/60 text-emerald-900 border border-emerald-300/60">
                        TRANSAKSI BERHASIL DIPROSES
                    </span>
                </div>
                <p class="font-bold text-slate-800 text-xs leading-relaxed">{{ session('success') }}</p>
            </div>
        </div>
        <button type="button" 
                @click="show = false" 
                class="text-emerald-600 hover:text-emerald-900 hover:bg-emerald-100/60 p-1.5 rounded-xl transition cursor-pointer shrink-0" 
                title="Tutup Pesan">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
@endif

@if(session('error'))
    <div x-data="{ show: true }" 
         x-show="show" 
         x-init="$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); $el.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); })"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-2 scale-98"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="mb-6 p-4 sm:p-5 rounded-2xl bg-gradient-to-r from-rose-50 via-rose-50/80 to-white border-2 border-rose-500/40 text-rose-950 text-xs flex items-start justify-between gap-4 shadow-md shadow-rose-500/5">
        <div class="flex items-start gap-3.5">
            <div class="w-10 h-10 rounded-2xl bg-rose-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-rose-600/20 ring-4 ring-rose-500/10">
                <i data-lucide="alert-octagon" class="w-5 h-5"></i>
            </div>
            <div class="flex-1 min-w-0 pt-0.5">
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-extrabold uppercase tracking-wider bg-rose-200/60 text-rose-900 border border-rose-300/60">
                        TRANSAKSI DITOLAK / GAGAL
                    </span>
                </div>
                <p class="font-bold text-slate-800 text-xs leading-relaxed">{{ session('error') }}</p>
            </div>
        </div>
        <button type="button" 
                @click="show = false" 
                class="text-rose-600 hover:text-rose-900 hover:bg-rose-100/60 p-1.5 rounded-xl transition cursor-pointer shrink-0" 
                title="Tutup Pesan">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
@endif

@if(isset($errors) && $errors->any())
    <div x-data="{ show: true }" 
         x-show="show" 
         x-init="$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); $el.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); })"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-2 scale-98"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="mb-6 p-4 sm:p-5 rounded-2xl bg-gradient-to-r from-amber-50 via-amber-50/80 to-white border-2 border-amber-500/40 text-amber-950 text-xs flex items-start justify-between gap-4 shadow-md shadow-amber-500/5">
        <div class="flex items-start gap-3.5">
            <div class="w-10 h-10 rounded-2xl bg-amber-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-amber-600/20 ring-4 ring-amber-500/10">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
            </div>
            <div class="flex-1 min-w-0 pt-0.5">
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-extrabold uppercase tracking-wider bg-amber-200/60 text-amber-900 border border-amber-300/60">
                        PERIKSA KELENGKAPAN FORM
                    </span>
                </div>
                <ul class="list-disc pl-4 space-y-1 font-bold text-slate-800">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <button type="button" 
                @click="show = false" 
                class="text-amber-600 hover:text-amber-900 hover:bg-amber-100/60 p-1.5 rounded-xl transition cursor-pointer shrink-0" 
                title="Tutup Pesan">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
@endif
