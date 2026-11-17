@props([
    'name',
    'label' => 'Unggah Berkas',
    'accept' => '.pdf,image/*',
    'maxSizeMb' => 10,
    'required' => false,
    'helpText' => 'Format file: PDF atau Gambar (JPG, PNG). Maksimal 10 MB.',
    'existingFileUrl' => null,
])

@php
    $uploadId = 'upload_' . $name . '_' . uniqid();
@endphp

<div x-data="{
        fileName: '',
        fileSize: '',
        fileType: '',
        previewUrl: null,
        errorMessage: '',
        hasExisting: {{ !empty($existingFileUrl) ? 'true' : 'false' }},
        existingUrl: '{{ $existingFileUrl ?? '' }}',

        onFileSelected(e) {
            this.errorMessage = '';
            const file = e.target.files[0];
            if (!file) return;

            // Validasi Ukuran File
            const maxBytes = {{ $maxSizeMb }} * 1024 * 1024;
            if (file.size > maxBytes) {
                this.errorMessage = `Ukuran file melebihi batas maksimal {{ $maxSizeMb }} MB.`;
                e.target.value = '';
                this.fileName = '';
                this.previewUrl = null;
                return;
            }

            this.fileName = file.name;
            this.fileSize = (file.size / 1024 / 1024).toFixed(2) + ' MB';
            this.fileType = file.type;

            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    this.previewUrl = event.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                this.previewUrl = URL.createObjectURL(file);
            }
        },

        removeFile() {
            this.$refs.fileInput.value = '';
            this.fileName = '';
            this.fileSize = '';
            this.previewUrl = null;
            this.errorMessage = '';
        }
     }" 
     class="w-full">
    
    @if($label)
        <label class="block text-xs font-bold text-slate-700 mb-1.5">
            {{ $label }}
            @if($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif

    <!-- Dropzone Box -->
    <div class="relative rounded-2xl border-2 border-dashed transition-all p-4 text-center cursor-pointer"
         :class="errorMessage ? 'border-rose-300 bg-rose-50/20' : (fileName || hasExisting ? 'border-emerald-300 bg-emerald-50/10' : 'border-slate-200 bg-slate-50/50 hover:bg-slate-50 hover:border-slate-300')">
        
        <input type="file" 
               name="{{ $name }}" 
               id="{{ $uploadId }}"
               x-ref="fileInput"
               @change="onFileSelected($event)"
               accept="{{ $accept }}"
               {{ $required ? 'required' : '' }}
               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">

        <!-- Tampilan Belum Ada File Dipilih -->
        <div x-show="!fileName && !hasExisting" class="space-y-2 pointer-events-none">
            <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 shadow-2xs flex items-center justify-center text-slate-400 mx-auto">
                <i data-lucide="upload-cloud" class="w-5 h-5 text-rose-500"></i>
            </div>
            <div>
                <p class="text-xs font-extrabold text-slate-800">
                    Klik untuk memilih berkas <span class="text-slate-400 font-normal">atau tarik ke sini</span>
                </p>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ $helpText }}</p>
            </div>
        </div>

        <!-- Tampilan File Baru Dipilih -->
        <div x-show="fileName" x-cloak class="flex items-center justify-between gap-3 text-left">
            <div class="flex items-center gap-3 overflow-hidden">
                <!-- Preview Thumbnail / PDF Icon -->
                <template x-if="previewUrl && fileType.startsWith('image/')">
                    <img :src="previewUrl" class="w-12 h-12 object-cover rounded-xl border border-slate-200 shadow-2xs shrink-0">
                </template>
                <template x-if="!fileType.startsWith('image/')">
                    <div class="w-12 h-12 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center shrink-0 border border-rose-200">
                        <i data-lucide="file-text" class="w-6 h-6"></i>
                    </div>
                </template>

                <div class="overflow-hidden">
                    <span class="font-bold text-xs text-slate-900 block truncate" x-text="fileName"></span>
                    <span class="text-[10px] text-emerald-700 font-mono font-bold" x-text="fileSize"></span>
                    <span class="text-[10px] text-slate-400 block font-medium">Klik kotak untuk mengganti berkas</span>
                </div>
            </div>

            <div class="flex items-center gap-1.5 z-20">
                <template x-if="previewUrl">
                    <a :href="previewUrl" target="_blank" class="p-2 rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 shadow-2xs" title="Buka berkas di tab baru">
                        <i data-lucide="external-link" class="w-4 h-4"></i>
                    </a>
                </template>
                <button type="button" @click.prevent="removeFile()" class="p-2 rounded-xl bg-white hover:bg-rose-50 text-rose-600 border border-slate-200 shadow-2xs" title="Hapus berkas ini">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- Tampilan File Lama (Existing File) -->
        <div x-show="!fileName && hasExisting" x-cloak class="flex items-center justify-between gap-3 text-left">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0 border border-slate-200">
                    <i data-lucide="file-check" class="w-6 h-6 text-emerald-600"></i>
                </div>
                <div>
                    <span class="font-bold text-xs text-slate-900 block">Berkas Lampiran Tersimpan</span>
                    <span class="text-[10px] text-slate-400 block font-medium">Klik kotak untuk mengunggah berkas baru (menggantikan)</span>
                </div>
            </div>

            <a :href="existingUrl" target="_blank" class="px-3 py-1.5 rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 shadow-2xs text-xs font-bold flex items-center gap-1 z-20">
                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                <span>Lihat Berkas</span>
            </a>
        </div>
    </div>

    <!-- Error Validation Message -->
    <div x-show="errorMessage" x-cloak class="mt-1.5 text-[11px] font-bold text-rose-600 flex items-center gap-1">
        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
        <span x-text="errorMessage"></span>
    </div>
</div>
