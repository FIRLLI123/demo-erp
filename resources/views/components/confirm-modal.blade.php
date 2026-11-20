{{-- ================================================================= --}}
{{-- MASTER UI COMPONENT: GLOBAL CONFIRMATION MODAL                   --}}
{{-- Dapat dipanggil otomatis lewat atribut form `data-confirm="..."` --}}
{{-- atau via JS `window.confirmAction({ title, message, ... })`      --}}
{{-- ================================================================= --}}
<div x-data="erpConfirmModal()"
     x-init="initConfirmInterceptor()"
     @open-confirm-modal.window="openModal($event.detail)"
     @keydown.escape.window="if (isOpen) cancel()"
     x-show="isOpen"
     x-cloak
     class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-slate-950/65 backdrop-blur-xs"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click.self="cancel()">

    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200/90 overflow-hidden transform transition-all"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">

        <!-- Header Icon & Title -->
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 shadow-xs"
                 :class="{
                     'bg-rose-100 text-rose-600 border border-rose-200': type === 'danger',
                     'bg-emerald-100 text-emerald-600 border border-emerald-200': type === 'emerald',
                     'bg-amber-100 text-amber-600 border border-amber-200': type === 'warning',
                     'bg-indigo-100 text-indigo-600 border border-indigo-200': type === 'primary'
                 }">
                <template x-if="type === 'danger'">
                    <i data-lucide="alert-octagon" class="w-6 h-6"></i>
                </template>
                <template x-if="type === 'emerald'">
                    <i data-lucide="check-circle-2" class="w-6 h-6"></i>
                </template>
                <template x-if="type === 'warning'">
                    <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                </template>
                <template x-if="type === 'primary'">
                    <i data-lucide="help-circle" class="w-6 h-6"></i>
                </template>
            </div>

            <div class="flex-1 min-w-0">
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-extrabold uppercase tracking-wider"
                      :class="{
                          'bg-rose-50 text-rose-700': type === 'danger',
                          'bg-emerald-50 text-emerald-700': type === 'emerald',
                          'bg-amber-50 text-amber-800': type === 'warning',
                          'bg-indigo-50 text-indigo-700': type === 'primary'
                      }"
                      x-text="badgeText"></span>
                <h3 class="text-sm font-extrabold text-slate-900 mt-1 leading-snug" x-text="title"></h3>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed" x-text="message"></p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
            <button type="button"
                    @click="cancel()"
                    class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-all cursor-pointer active:scale-95">
                <span x-text="cancelText"></span>
            </button>

            <button type="button"
                    @click="proceed()"
                    :class="{
                        'bg-rose-600 hover:bg-rose-700 shadow-rose-600/20': type === 'danger',
                        'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/20': type === 'emerald',
                        'bg-amber-600 hover:bg-amber-700 shadow-amber-600/20': type === 'warning',
                        'bg-slate-900 hover:bg-slate-800 shadow-slate-900/20': type === 'primary'
                    }"
                    class="px-5 py-2.5 rounded-xl text-white font-extrabold text-xs shadow-md transition-all flex items-center gap-1.5 cursor-pointer active:scale-95">
                <span x-text="confirmText"></span>
            </button>
        </div>
    </div>
</div>

<script>
    function erpConfirmModal() {
        return {
            isOpen: false,
            title: 'Konfirmasi Tindakan',
            message: 'Apakah Anda yakin ingin melanjutkan proses ini?',
            type: 'primary', // primary | emerald | danger | warning
            badgeText: 'KONFIRMASI SISTEM',
            confirmText: 'Ya, Lanjutkan',
            cancelText: 'Tidak / Batal',
            pendingForm: null,
            onConfirmCallback: null,

            initConfirmInterceptor() {
                // Tangkap event submit pada fase CAPTURE (sebelum global loading listener)
                document.addEventListener('submit', (e) => {
                    const form = e.target;
                    if (!form || !form.tagName || form.tagName.toLowerCase() !== 'form') return;
                    if (!form.hasAttribute('data-confirm')) return;

                    // Jika sudah dikonfirmasi lewat modal ini, izinkan lanjut submit
                    if (form.dataset.confirmed === 'true') {
                        delete form.dataset.confirmed;
                        return;
                    }

                    // Hentikan submit & cegah loading modal muncul sebelum user klik "Ya"
                    e.preventDefault();
                    e.stopImmediatePropagation();

                    this.pendingForm = form;
                    this.onConfirmCallback = null;
                    this.title = form.getAttribute('data-confirm') || 'Konfirmasi Tindakan';
                    this.message = form.getAttribute('data-confirm-text') || 'Apakah Anda yakin ingin melanjutkan proses ini?';
                    this.type = form.getAttribute('data-confirm-type') || 'primary';
                    this.badgeText = form.getAttribute('data-confirm-badge') || (this.type === 'danger' ? 'PERINGATAN PEMBATALAN' : 'KONFIRMASI TRANSAKSI');
                    this.confirmText = form.getAttribute('data-confirm-btn') || 'Ya, Lanjutkan';
                    this.cancelText = form.getAttribute('data-cancel-btn') || 'Tidak, Batal';
                    this.isOpen = true;

                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                }, true);
            },

            openModal(opts = {}) {
                this.pendingForm = opts.form || null;
                this.onConfirmCallback = typeof opts.onConfirm === 'function' ? opts.onConfirm : null;
                this.title = opts.title || 'Konfirmasi Tindakan';
                this.message = opts.message || 'Apakah Anda yakin ingin melanjutkan?';
                this.type = opts.type || 'primary';
                this.badgeText = opts.badge || (this.type === 'danger' ? 'PERINGATAN' : 'KONFIRMASI');
                this.confirmText = opts.confirmText || 'Ya, Lanjutkan';
                this.cancelText = opts.cancelText || 'Tidak, Batal';
                this.isOpen = true;

                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                });
            },

            cancel() {
                this.isOpen = false;
                this.pendingForm = null;
                this.onConfirmCallback = null;
                if (typeof window.hideLoading === 'function') {
                    window.hideLoading();
                }
            },

            proceed() {
                this.isOpen = false;
                const form = this.pendingForm;
                const cb = this.onConfirmCallback;
                this.pendingForm = null;
                this.onConfirmCallback = null;

                if (cb) {
                    cb();
                    return;
                }

                if (form) {
                    form.dataset.confirmed = 'true';
                    if (!form.hasAttribute('data-no-loading') && typeof window.showLoading === 'function') {
                        const customTitle = form.getAttribute('data-loading-title') || 'Memproses Transaksi...';
                        const customSub = form.getAttribute('data-loading-sub') || 'Menyimpan perubahan ke database ERP...';
                        const docType = form.getAttribute('data-doctype') || '';
                        window.showLoading(customTitle, customSub, docType);
                    }
                    form.submit();
                }
            }
        };
    }

    window.confirmAction = function(options) {
        window.dispatchEvent(new CustomEvent('open-confirm-modal', { detail: options }));
    };
</script>
