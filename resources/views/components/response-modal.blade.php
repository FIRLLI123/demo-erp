{{-- ================================================================= --}}
{{-- MASTER UI COMPONENT: GLOBAL RESPONSE ALERT MODAL (SUCCESS & ERROR)--}}
{{-- Muncul otomatis di tengah layar saat session('success') / error   --}}
{{-- atau dipanggil via JS: window.showSuccessAlert(title, message)    --}}
{{-- ================================================================= --}}
@php
    $hasSessionSuccess = session()->has('success');
    $hasSessionError = session()->has('error');
    $initialOpen = $hasSessionSuccess || $hasSessionError;
    $initialType = $hasSessionSuccess ? 'success' : ($hasSessionError ? 'error' : 'success');
    $initialTitle = $hasSessionSuccess ? 'Operasi Berhasil!' : 'Terjadi Kesalahan!';
    $initialMessage = $hasSessionSuccess ? session('success') : session('error', '');
@endphp

<div x-data="erpResponseModal({{ \Illuminate\Support\Js::from(['isOpen' => $initialOpen, 'type' => $initialType, 'title' => $initialTitle, 'message' => (string) $initialMessage]) }})"
     @open-response-modal.window="openModal($event.detail)"
     @keydown.escape.window="if (isOpen) close()"
     x-show="isOpen"
     x-cloak
     class="fixed inset-0 z-[10001] flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm"
     x-transition:enter="transition ease-out duration-250"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click.self="close()">

    <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-7 shadow-2xl border border-slate-100 overflow-hidden transform transition-all text-center relative"
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0 scale-95 translate-y-3"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">

        <!-- Top Decorative Bar -->
        <div class="absolute top-0 left-0 right-0 h-1.5"
             :class="type === 'success' ? 'bg-gradient-to-r from-emerald-500 via-teal-400 to-emerald-600' : 'bg-gradient-to-r from-rose-500 via-red-500 to-amber-500'">
        </div>

        <!-- Big Center Icon with Ripple Rings -->
        <div class="relative w-20 h-20 mx-auto mb-4 flex items-center justify-center">
            <!-- Pulsing outer ring -->
            <div class="absolute inset-0 rounded-full animate-ping opacity-25"
                 :class="type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'"></div>

            <!-- Soft background ring -->
            <div class="w-16 h-16 rounded-full flex items-center justify-center shadow-lg transition-all"
                 :class="type === 'success' 
                     ? 'bg-emerald-100 text-emerald-600 ring-8 ring-emerald-50 shadow-emerald-600/20' 
                     : 'bg-rose-100 text-rose-600 ring-8 ring-rose-50 shadow-rose-600/20'">
                <template x-if="type === 'success'">
                    <i data-lucide="check-circle-2" class="w-9 h-9 stroke-[2.2]"></i>
                </template>
                <template x-if="type !== 'success'">
                    <i data-lucide="alert-octagon" class="w-9 h-9 stroke-[2.2]"></i>
                </template>
            </div>
        </div>

        <!-- Badge Category -->
        <div class="mb-2">
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-extrabold uppercase tracking-wider inline-block"
                  :class="type === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'"
                  x-text="type === 'success' ? 'TRANSAKSI BERHASIL DIPROSES' : 'TRANSAKSI GAGAL / DITOLAK'"></span>
        </div>

        <!-- Title & Detailed Message -->
        <h3 class="text-lg font-black text-slate-900 tracking-tight" x-text="title"></h3>
        <p class="text-xs sm:text-sm text-slate-600 mt-2 leading-relaxed px-2 font-medium" x-text="message"></p>

        <!-- Close / Action Button -->
        <div class="mt-6 pt-4 border-t border-slate-100">
            <button type="button"
                    @click="close()"
                    :class="type === 'success' 
                        ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/25' 
                        : 'bg-rose-600 hover:bg-rose-700 shadow-rose-600/25'"
                    class="w-full py-3 px-5 rounded-2xl text-white font-extrabold text-xs shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-95">
                <template x-if="type === 'success'">
                    <i data-lucide="check" class="w-4 h-4"></i>
                </template>
                <template x-if="type !== 'success'">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </template>
                <span x-text="type === 'success' ? 'Oke, Selesai' : 'Tutup Pesan'"></span>
            </button>
        </div>
    </div>
</div>

<script>
    function erpResponseModal(initOpts = {}) {
        return {
            isOpen: initOpts.isOpen || false,
            type: initOpts.type || 'success',
            title: initOpts.title || 'Pemberitahuan',
            message: initOpts.message || '',

            init() {
                if (this.isOpen) {
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                }
            },

            openModal(opts = {}) {
                this.type = opts.type || 'success';
                this.title = opts.title || (this.type === 'success' ? 'Berhasil Diproses!' : 'Terjadi Kesalahan!');
                this.message = opts.message || '';
                this.reloadOnClose = !!opts.reload;
                this.isOpen = true;
                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                });
            },

            close() {
                this.isOpen = false;
                if (this.reloadOnClose) {
                    window.location.reload();
                }
            }
        };
    }

    // JS Global Helpers untuk memanggil modal alert di mana saja
    window.showSuccessAlert = function(message, title = 'Operasi Berhasil!', opts = {}) {
        window.dispatchEvent(new CustomEvent('open-response-modal', {
            detail: { type: 'success', title: title, message: message, reload: !!opts.reload }
        }));
    };

    window.showErrorAlert = function(message, title = 'Terjadi Kesalahan!', opts = {}) {
        window.dispatchEvent(new CustomEvent('open-response-modal', {
            detail: { type: 'error', title: title, message: message, reload: !!opts.reload }
        }));
    };
</script>
