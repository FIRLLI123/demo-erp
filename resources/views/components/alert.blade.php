@if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-transition
         class="mb-5 p-4 rounded-2xl bg-emerald-50/90 border border-emerald-200 text-emerald-900 text-xs flex items-start justify-between gap-3 shadow-xs">
        <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-2xs">
                <i data-lucide="check-circle-2" class="w-4 h-4"></i>
            </div>
            <div>
                <span class="font-extrabold text-emerald-950 block text-xs">Berhasil Diproses</span>
                <p class="font-semibold text-emerald-800 mt-0.5 leading-relaxed">{{ session('success') }}</p>
            </div>
        </div>
        <button type="button" @click="show = false" class="text-emerald-500 hover:text-emerald-800 p-1 rounded-lg cursor-pointer shrink-0">&times;</button>
    </div>
@endif

@if(session('error'))
    <div x-data="{ show: true }" x-show="show" x-transition
         class="mb-5 p-4 rounded-2xl bg-rose-50/90 border border-rose-200 text-rose-900 text-xs flex items-start justify-between gap-3 shadow-xs">
        <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0 shadow-2xs">
                <i data-lucide="alert-octagon" class="w-4 h-4"></i>
            </div>
            <div>
                <span class="font-extrabold text-rose-950 block text-xs">Proses Gagal / Ditolak</span>
                <p class="font-semibold text-rose-800 mt-0.5 leading-relaxed">{{ session('error') }}</p>
            </div>
        </div>
        <button type="button" @click="show = false" class="text-rose-500 hover:text-rose-800 p-1 rounded-lg cursor-pointer shrink-0">&times;</button>
    </div>
@endif

@if(isset($errors) && $errors->any())
    <div x-data="{ show: true }" x-show="show" x-transition
         class="mb-5 p-4 rounded-2xl bg-rose-50/90 border border-rose-200 text-rose-900 text-xs flex items-start justify-between gap-3 shadow-xs">
        <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0 shadow-2xs">
                <i data-lucide="alert-triangle" class="w-4 h-4"></i>
            </div>
            <div>
                <span class="font-extrabold text-rose-950 block text-xs">Validasi Data Belum Lengkap</span>
                <ul class="list-disc pl-4 mt-1 space-y-0.5 font-semibold text-rose-800">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <button type="button" @click="show = false" class="text-rose-500 hover:text-rose-800 p-1 rounded-lg cursor-pointer shrink-0">&times;</button>
    </div>
@endif
