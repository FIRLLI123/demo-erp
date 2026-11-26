<x-app-layout>
    <x-slot name="title">Master Pengguna & Otorisasi Wilayah</x-slot>
    <x-slot name="headerTitle">Master Akun & Pengguna</x-slot>
    <x-slot name="badge">User Management & Signature</x-slot>

    <div x-data="userMasterApp()" class="pb-12">
        
        <!-- Top Action & Navigation Banner -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Master Pengguna & Tanda Tangan Digital</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">ERP USERS</span>
                </h2>
                <p class="text-xs text-slate-500">Kelola akun pengguna, tanda tangan digital (signature), cakupan wilayah, dan hak akses.</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('master.roles.matrix') }}" 
                   class="px-3.5 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-2xs transition-all cursor-pointer">
                    <i data-lucide="shield-check" class="w-4 h-4 text-indigo-600"></i>
                    <span>Atur Hak Akses Role</span>
                </a>
                <button type="button" 
                        @click="toggleCreateForm()" 
                        class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-xs transition-all active:scale-95 shrink-0 cursor-pointer">
                    <i data-lucide="user-plus" class="w-4 h-4 text-emerald-400"></i>
                    <span x-text="createOpen ? 'Tutup Formulir' : '+ Tambah Pengguna Baru'"></span>
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between shadow-2xs">
                <span class="flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                    {{ session('success') }}
                </span>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 cursor-pointer">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center justify-between shadow-2xs">
                <span class="flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                    {{ session('error') }}
                </span>
                <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800 cursor-pointer">&times;</button>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- ============================================== -->
        <!-- FORM TAMBAH USER BARU (INLINE CARD)            -->
        <!-- ============================================== -->
        <div x-show="createOpen" x-cloak x-transition class="mb-6 bg-white rounded-2xl border-2 border-indigo-500/30 p-6 shadow-xl">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-indigo-100 text-indigo-700">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                    </span>
                    <h3 class="font-extrabold text-slate-900 text-sm">Registrasi Akun Pengguna Baru</h3>
                </div>
                <button @click="createOpen = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('master.users.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap Pengguna <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required placeholder="Contoh: Rian Hidayat" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Email Login <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" required placeholder="nama@distripos.com" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kata Sandi (Password) <span class="text-rose-500">*</span></label>
                        <input type="password" name="password" required minlength="6" placeholder="Minimal 6 karakter" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Peran / Otorisasi Role <span class="text-rose-500">*</span></label>
                        <select name="role_id" required class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:bg-white focus:outline-none">
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }} ({{ $role->description }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pemetaan ke Tenaga Sales (Opsional)</label>
                        <select name="sales_id" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:bg-white focus:outline-none">
                            <option value="">-- Bukan Akun Sales / Non-Kanvas --</option>
                            @foreach($salesList as $s)
                                <option value="{{ $s->id }}">[{{ $s->code }}] {{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Telepon / WhatsApp</label>
                        <input type="text" name="phone" placeholder="0812xxxxxxxx" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:bg-white focus:outline-none">
                    </div>
                </div>

                <!-- SECTION TANDA TANGAN DIGITAL (OPSI C: UPLOAD ATAU CANVAS) -->
                <div class="mb-5 pt-3 border-t border-slate-100">
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                            <i data-lucide="pen-tool" class="w-3.5 h-3.5 text-indigo-600"></i>
                            <span>Tanda Tangan Digital (Signature) - Opsional</span>
                        </label>
                        
                        <!-- Tab Selector Mode Signature -->
                        <div class="inline-flex rounded-lg border border-slate-200 bg-slate-50 p-0.5 text-[11px] font-bold">
                            <button type="button" 
                                    @click="createSignMode = 'upload'" 
                                    :class="createSignMode === 'upload' ? 'bg-white text-indigo-700 shadow-2xs' : 'text-slate-500 hover:text-slate-800'"
                                    class="px-2.5 py-1 rounded-md transition-all cursor-pointer">
                                Upload Gambar
                            </button>
                            <button type="button" 
                                    @click="createSignMode = 'draw'; $nextTick(() => initCreateCanvas())" 
                                    :class="createSignMode === 'draw' ? 'bg-white text-indigo-700 shadow-2xs' : 'text-slate-500 hover:text-slate-800'"
                                    class="px-2.5 py-1 rounded-md transition-all cursor-pointer">
                                Gores di Layar
                            </button>
                        </div>
                    </div>

                    <!-- Mode 1: Upload File Gambar -->
                    <div x-show="createSignMode === 'upload'" class="p-4 rounded-xl border border-dashed border-slate-300 bg-slate-50/60">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                            <input type="file" 
                                   name="signature_file" 
                                   accept="image/png,image/jpeg,image/webp" 
                                   @change="handleCreateFilePreview($event)" 
                                   class="text-xs file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                            
                            <template x-if="createFilePreview">
                                <div class="flex items-center gap-2 p-1.5 rounded-lg border border-slate-200 bg-white">
                                    <img :src="createFilePreview" class="h-10 max-w-[120px] object-contain" alt="Preview TTD">
                                    <button type="button" @click="createFilePreview = null" class="text-rose-500 hover:text-rose-700 text-xs px-1">&times;</button>
                                </div>
                            </template>
                        </div>
                        <p class="text-[10px] text-slate-400 mt-2">Disarankan menggunakan gambar transparan (PNG) atau foto tanda tangan di atas kertas putih.</p>
                    </div>

                    <!-- Mode 2: Gores di Canvas Digital Pad -->
                    <div x-show="createSignMode === 'draw'" x-cloak class="p-4 rounded-xl border border-slate-200 bg-slate-50/60">
                        <div class="flex flex-col sm:flex-row gap-4 items-start">
                            <div>
                                <canvas id="createCanvas" 
                                        width="340" 
                                        height="120" 
                                        class="bg-white border-2 border-slate-300 rounded-xl cursor-crosshair shadow-2xs touch-none">
                                </canvas>
                                <div class="flex items-center justify-between mt-1.5">
                                    <span class="text-[10px] text-slate-400">Tanda tangani di dalam kotak di atas</span>
                                    <button type="button" 
                                            @click="clearCreateCanvas()" 
                                            class="text-[11px] font-bold text-rose-600 hover:underline cursor-pointer">
                                        Bersihkan Canvas
                                    </button>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="signature_canvas" :value="createCanvasData">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="createOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-xs transition-all cursor-pointer">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Akun Pengguna</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- ============================================== -->
        <!-- MODAL EDIT PENGGUNA (PROFIL, PASSWORD & TTD)   -->
        <!-- ============================================== -->
        <template x-if="editModalOpen && editData">
            <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                <div class="relative bg-white rounded-2xl border border-slate-200 max-w-lg w-full p-6 shadow-2xl z-10 max-h-[90vh] flex flex-col" @click.outside="editModalOpen = false">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 shrink-0">
                        <h3 class="font-bold text-slate-900 text-sm">Edit Profil Pengguna [<span x-text="editData.email"></span>]</h3>
                        <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">&times;</button>
                    </div>

                    <form :action="'/master/users/update/' + editData.id" method="POST" enctype="multipart/form-data" class="overflow-y-auto pr-1 flex-1 space-y-3.5">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap</label>
                            <input type="text" name="name" x-model="editData.name" required class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Email</label>
                                <input type="email" name="email" x-model="editData.email" required class="w-full text-xs font-medium bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">No. HP / WA</label>
                                <input type="text" name="phone" x-model="editData.phone" class="w-full text-xs font-medium bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Peran (Role)</label>
                                <select name="role_id" x-model="editData.role_id" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Status Keaktifan</label>
                                <select name="is_active" x-model="editData.is_active" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                    <option value="1">Aktif (Dapat Login)</option>
                                    <option value="0">Nonaktif (Diblokir)</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Pemetaan ke Sales Force</label>
                            <select name="sales_id" x-model="editData.sales_id" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                <option value="">-- Bukan Akun Sales / Non-Kanvas --</option>
                                @foreach($salesList as $s)
                                    <option value="{{ $s->id }}">[{{ $s->code }}] {{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Ganti Kata Sandi (Kosongkan bila tidak diubah)</label>
                            <input type="password" name="password" minlength="6" placeholder="Masukkan password baru..." class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                        </div>

                        <!-- EDIT TANDA TANGAN DIGITAL -->
                        <div class="pt-3 border-t border-slate-100">
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                    <i data-lucide="pen-tool" class="w-3.5 h-3.5 text-indigo-600"></i>
                                    <span>Tanda Tangan Digital (Signature)</span>
                                </label>
                                
                                <div class="inline-flex rounded-lg border border-slate-200 bg-slate-50 p-0.5 text-[11px] font-bold">
                                    <button type="button" 
                                            @click="editSignMode = 'upload'" 
                                            :class="editSignMode === 'upload' ? 'bg-white text-indigo-700 shadow-2xs' : 'text-slate-500 hover:text-slate-800'"
                                            class="px-2 py-0.5 rounded transition-all cursor-pointer">
                                        Upload
                                    </button>
                                    <button type="button" 
                                            @click="editSignMode = 'draw'; $nextTick(() => initEditCanvas())" 
                                            :class="editSignMode === 'draw' ? 'bg-white text-indigo-700 shadow-2xs' : 'text-slate-500 hover:text-slate-800'"
                                            class="px-2 py-0.5 rounded transition-all cursor-pointer">
                                        Gores Canvas
                                    </button>
                                </div>
                            </div>

                            <!-- TTD Saat Ini (Jika Ada) -->
                            <template x-if="editData.signature && !editRemoveSignature">
                                <div class="mb-3 p-3 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="h-12 w-28 bg-white border border-slate-200 rounded-lg p-1 flex items-center justify-center">
                                            <img :src="'/' + editData.signature" class="max-h-full max-w-full object-contain" alt="TTD Saat Ini">
                                        </div>
                                        <div>
                                            <span class="text-[11px] font-bold text-slate-700 block">Tanda Tangan Aktif</span>
                                            <span class="text-[10px] text-slate-400">Digunakan pada dokumen PDF</span>
                                        </div>
                                    </div>
                                    <button type="button" 
                                            @click="editRemoveSignature = true" 
                                            class="px-2.5 py-1 text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg border border-rose-200 transition-all cursor-pointer">
                                        Hapus TTD
                                    </button>
                                </div>
                            </template>

                            <input type="hidden" name="signature_remove" :value="editRemoveSignature ? '1' : '0'">

                            <!-- Mode 1: Upload Baru -->
                            <div x-show="editSignMode === 'upload'" class="p-3 rounded-xl border border-dashed border-slate-300 bg-slate-50/60">
                                <input type="file" 
                                       name="signature_file" 
                                       accept="image/png,image/jpeg,image/webp" 
                                       class="text-xs file:mr-3 file:py-1.5 file:px-2.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 cursor-pointer">
                                <p class="text-[10px] text-slate-400 mt-1">Pilih gambar baru untuk mengganti tanda tangan saat ini.</p>
                            </div>

                            <!-- Mode 2: Gores Canvas Baru -->
                            <div x-show="editSignMode === 'draw'" x-cloak class="p-3 rounded-xl border border-slate-200 bg-slate-50/60">
                                <canvas id="editCanvas" 
                                        width="320" 
                                        height="110" 
                                        class="bg-white border-2 border-slate-300 rounded-xl cursor-crosshair shadow-2xs touch-none">
                                </canvas>
                                <div class="flex items-center justify-between mt-1">
                                    <span class="text-[10px] text-slate-400">Goreskan tanda tangan baru</span>
                                    <button type="button" 
                                            @click="clearEditCanvas()" 
                                            class="text-[11px] font-bold text-rose-600 hover:underline cursor-pointer">
                                        Bersihkan
                                    </button>
                                </div>
                                <input type="hidden" name="signature_canvas" :value="editCanvasData">
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100 shrink-0">
                            <button type="button" @click="editModalOpen = false" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl text-xs cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs cursor-pointer transition-all">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- ============================================== -->
        <!-- TABEL MASTER USER                              -->
        <!-- ============================================== -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-extrabold text-slate-800">Daftar Pengguna Sistem</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-slate-200 text-slate-700">
                        <span x-text="filteredUsers.length"></span> / {{ count($users) }}
                    </span>
                </div>

                <!-- Input Pencarian Universal -->
                <x-search-input model="searchUser" placeholder="Cari nama, email, role, sales..." width="w-full sm:w-64" accent="indigo" />
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Pengguna</th>
                            <th class="py-3 px-4 text-center">Tanda Tangan</th>
                            <th class="py-3 px-4">Peran (Role)</th>
                            <th class="py-3 px-4">Cakupan Wilayah</th>
                            <th class="py-3 px-4">Otorisasi Transaksi</th>
                            <th class="py-3 px-4">Sales Force</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center w-36">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($users as $u)
                            <tr x-show="paginatedUserIds.includes({{ $u->id }})"
                                x-transition
                                class="hover:bg-slate-50 transition-colors">
                                
                                <!-- Pengguna Name & Email -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-slate-700 to-slate-900 flex items-center justify-center text-white font-bold text-xs shadow-xs shrink-0">
                                            {{ strtoupper(substr($u->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <span class="font-extrabold text-slate-900 block text-xs">{{ $u->name }}</span>
                                            <span class="text-[11px] text-slate-500 font-mono">{{ $u->email }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Tanda Tangan Digital (Signature Preview) -->
                                <td class="py-3.5 px-4 text-center">
                                    @if($u->signature)
                                        <div class="inline-flex flex-col items-center gap-1 group relative cursor-pointer" @click="previewSignatureModal('{{ asset($u->signature) }}', '{{ $u->name }}')">
                                            <div class="h-8 w-16 bg-white border border-slate-200 rounded p-0.5 flex items-center justify-center shadow-2xs group-hover:border-indigo-400 transition-all">
                                                <img src="{{ asset($u->signature) }}" class="max-h-full max-w-full object-contain" alt="TTD {{ $u->name }}">
                                            </div>
                                            <span class="text-[9px] font-bold text-emerald-600 font-mono">Ada TTD</span>
                                        </div>
                                    @else
                                        <span class="text-[10px] text-slate-400 font-mono italic">Kosong</span>
                                    @endif
                                </td>

                                <!-- Role -->
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold 
                                        {{ in_array($u->role_slug, ['it_dev', 'super_admin']) ? 'bg-rose-100 text-rose-800 border border-rose-200' : ($u->role_slug === 'sales' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : ($u->role_slug === 'finance' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-slate-100 text-slate-800 border border-slate-200')) }}">
                                        {{ $u->role_name ?? 'Staf' }}
                                    </span>
                                </td>

                                <!-- Cakupan Wilayah -->
                                <td class="py-3.5 px-4">
                                    @if($u->has_full_access || in_array($u->role_slug, ['it_dev', 'super_admin']))
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200 font-bold text-[10px]">
                                            <i data-lucide="globe" class="w-3.5 h-3.5 text-indigo-600"></i>
                                            <span>Akses Penuh</span>
                                        </span>
                                    @elseif($u->taps_count > 0)
                                        <span class="inline-flex items-center gap-1 font-bold text-slate-800 text-xs bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
                                            <i data-lucide="building-2" class="w-3.5 h-3.5 text-indigo-600"></i>
                                            <span>{{ $u->taps_count }} TAP Wilayah</span>
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">Belum Diatur</span>
                                    @endif
                                </td>

                                <!-- Otorisasi Gudang -->
                                <td class="py-3.5 px-4">
                                    @if($u->is_ho_readonly)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-300 font-bold text-[10px]">
                                            <i data-lucide="eye" class="w-3 h-3 text-amber-700"></i>
                                            <span>Monitoring (Read-Only)</span>
                                        </span>
                                    @elseif($u->sales_id)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-purple-100 text-purple-800 border border-purple-200 font-bold text-[10px]">
                                            <i data-lucide="bike" class="w-3 h-3 text-purple-600"></i>
                                            <span>Gudang Sales Sendiri</span>
                                        </span>
                                    @elseif($u->has_full_access)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 font-bold text-[10px]">
                                            <i data-lucide="check-check" class="w-3 h-3 text-emerald-600"></i>
                                            <span>Semua Gudang</span>
                                        </span>
                                    @elseif(!empty($u->auto_tap_ids) && count($u->auto_tap_ids) > 0)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 font-bold text-[10px]">
                                            <i data-lucide="zap" class="w-3 h-3 text-emerald-600"></i>
                                            <span>Otomatis TAP (+Sales Baru)</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 border border-blue-200 font-bold text-[10px]">
                                            <i data-lucide="check-square" class="w-3 h-3 text-blue-600"></i>
                                            <span>Custom {{ $u->warehouses_count }} Gudang</span>
                                        </span>
                                    @endif
                                </td>

                                <!-- Sales Link -->
                                <td class="py-3.5 px-4">
                                    @if($u->sales_code)
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 border border-indigo-200 font-mono font-bold text-[10px]">
                                                {{ $u->sales_code }}
                                            </span>
                                            <span class="font-bold text-slate-800">{{ $u->sales_name }}</span>
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">-</span>
                                    @endif
                                </td>

                                <!-- Status Toggle -->
                                <td class="py-3.5 px-4 text-center">
                                    <x-master-status-toggle entity="users" :id="$u->id" :name="$u->name" :active="(bool)$u->is_active" />
                                </td>

                                <!-- Aksi -->
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="{{ route('master.users.mapping', $u->id) }}" 
                                           target="_blank"
                                           class="px-2.5 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-[11px] font-bold transition-all inline-flex items-center gap-1.5 shadow-2xs active:scale-95"
                                           title="Atur Otorisasi Wilayah & Gudang (Buka Tab Baru)">
                                            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                                            <span>Mapping</span>
                                            <i data-lucide="external-link" class="w-3 h-3 text-indigo-400"></i>
                                        </a>

                                        <button type="button" 
                                                @click.stop="openEditModal({{ $u->id }})" 
                                                class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold transition-all cursor-pointer active:scale-95">
                                            Edit
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                        <tr x-show="filteredUsers.length === 0" x-cloak>
                            <td colspan="8" class="py-8 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-1.5">
                                    <i data-lucide="user-x" class="w-6 h-6 text-slate-300"></i>
                                    <span class="text-xs font-semibold">Tidak ada data pengguna yang sesuai dengan pencarian.</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar Master Pengguna -->
            <div class="p-4 border-t border-slate-200 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-500">
                <div class="flex items-center gap-3">
                    <div>
                        Menampilkan <strong class="text-slate-800" x-text="userPaginationStart"></strong> - <strong class="text-slate-800" x-text="userPaginationEnd"></strong> dari <strong class="text-slate-800" x-text="filteredUsers.length"></strong> data Pengguna
                    </div>
                    <span class="text-slate-300">|</span>
                    <label class="flex items-center gap-1.5 text-xs">
                        <span>Baris:</span>
                        <select x-model="userPerPage" @change="userPage = 1; refreshIcons()" class="border border-slate-200 rounded-lg px-2 py-1 bg-white text-xs font-semibold text-slate-700 focus:outline-none focus:ring-1 focus:ring-indigo-500 cursor-pointer">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="ALL">Semua</option>
                        </select>
                    </label>
                </div>

                <!-- Page Navigation Buttons -->
                <div class="flex items-center gap-1" x-show="totalUserPages > 1">
                    <button type="button" 
                            @click="userPage = Math.max(1, userPage - 1); refreshIcons()" 
                            :disabled="userPage === 1"
                            class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed text-xs font-bold transition-all flex items-center gap-1 cursor-pointer">
                        <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                        <span>Prev</span>
                    </button>

                    <template x-for="(p, idx) in displayedUserPages" :key="idx">
                        <button type="button"
                                @click="if (p !== '...') { userPage = p; refreshIcons(); }"
                                :disabled="p === '...'"
                                :class="userPage === p ? 'bg-indigo-600 text-white font-extrabold border-indigo-600 shadow-xs' : (p === '...' ? 'cursor-default border-transparent text-slate-400' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100 font-semibold')"
                                class="min-w-[30px] h-7 px-1.5 rounded-lg border text-xs flex items-center justify-center transition-all cursor-pointer"
                                x-text="p">
                        </button>
                    </template>

                    <button type="button" 
                            @click="userPage = Math.min(totalUserPages, userPage + 1); refreshIcons()" 
                            :disabled="userPage === totalUserPages"
                            class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed text-xs font-bold transition-all flex items-center gap-1 cursor-pointer">
                        <span>Next</span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- POPUP PREVIEW BESAR TANDA TANGAN -->
        <div x-show="showSignatureZoom" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 max-w-sm w-full text-center shadow-2xl" @click.outside="showSignatureZoom = false">
                <h4 class="font-bold text-slate-900 text-sm mb-1">Tanda Tangan Digital</h4>
                <p class="text-xs text-slate-500 mb-4" x-text="signatureZoomName"></p>
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 mb-4 flex items-center justify-center min-h-[140px]">
                    <img :src="signatureZoomUrl" class="max-h-32 max-w-full object-contain" alt="Tanda Tangan">
                </div>
                <button type="button" @click="showSignatureZoom = false" class="px-4 py-2 bg-slate-900 text-white font-bold rounded-xl text-xs cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function userMasterApp() {
            return {
                createOpen: false,
                editModalOpen: false,
                editData: null,
                usersList: @json($users),
                searchUser: '',

                // Pagination Master Pengguna
                userPage: 1,
                userPerPage: '10',

                init() {
                    this.$watch('searchUser', () => {
                        this.userPage = 1;
                        this.refreshIcons();
                    });
                },

                // Signature Tambah User
                createSignMode: 'upload',
                createFilePreview: null,
                createCanvasData: '',
                createCanvasCtx: null,
                createDrawing: false,

                // Signature Edit User
                editSignMode: 'upload',
                editRemoveSignature: false,
                editCanvasData: '',
                editCanvasCtx: null,
                editDrawing: false,

                // Zoom Modal
                showSignatureZoom: false,
                signatureZoomUrl: '',
                signatureZoomName: '',

                toggleCreateForm() {
                    this.createOpen = !this.createOpen;
                    if (this.createOpen && this.createSignMode === 'draw') {
                        this.$nextTick(() => this.initCreateCanvas());
                    }
                },

                handleCreateFilePreview(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.createFilePreview = URL.createObjectURL(file);
                    } else {
                        this.createFilePreview = null;
                    }
                },

                // Canvas Tambah User
                initCreateCanvas() {
                    const canvas = document.getElementById('createCanvas');
                    if (!canvas) return;
                    this.createCanvasCtx = canvas.getContext('2d');
                    this.setupCanvasListeners(canvas, this.createCanvasCtx, (data) => {
                        this.createCanvasData = data;
                    });
                },

                clearCreateCanvas() {
                    const canvas = document.getElementById('createCanvas');
                    if (canvas && this.createCanvasCtx) {
                        this.createCanvasCtx.clearRect(0, 0, canvas.width, canvas.height);
                        this.createCanvasData = '';
                    }
                },

                // Canvas Edit User
                initEditCanvas() {
                    const canvas = document.getElementById('editCanvas');
                    if (!canvas) return;
                    this.editCanvasCtx = canvas.getContext('2d');
                    this.setupCanvasListeners(canvas, this.editCanvasCtx, (data) => {
                        this.editCanvasData = data;
                    });
                },

                clearEditCanvas() {
                    const canvas = document.getElementById('editCanvas');
                    if (canvas && this.editCanvasCtx) {
                        this.editCanvasCtx.clearRect(0, 0, canvas.width, canvas.height);
                        this.editCanvasData = '';
                    }
                },

                setupCanvasListeners(canvas, ctx, onSave) {
                    let drawing = false;
                    ctx.strokeStyle = '#0f172a';
                    ctx.lineWidth = 2.5;
                    ctx.lineCap = 'round';
                    ctx.lineJoin = 'round';

                    const getPos = (e) => {
                        const rect = canvas.getBoundingClientRect();
                        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                        const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                        return {
                            x: clientX - rect.left,
                            y: clientY - rect.top
                        };
                    };

                    const start = (e) => {
                        e.preventDefault();
                        drawing = true;
                        const pos = getPos(e);
                        ctx.beginPath();
                        ctx.moveTo(pos.x, pos.y);
                    };

                    const move = (e) => {
                        if (!drawing) return;
                        e.preventDefault();
                        const pos = getPos(e);
                        ctx.lineTo(pos.x, pos.y);
                        ctx.stroke();
                    };

                    const stop = () => {
                        if (!drawing) return;
                        drawing = false;
                        ctx.closePath();
                        onSave(canvas.toDataURL('image/png'));
                    };

                    canvas.onmousedown = start;
                    canvas.onmousemove = move;
                    window.addEventListener('mouseup', stop);

                    canvas.ontouchstart = start;
                    canvas.ontouchmove = move;
                    canvas.ontouchend = stop;
                },

                previewSignatureModal(url, name) {
                    this.signatureZoomUrl = url;
                    this.signatureZoomName = name;
                    this.showSignatureZoom = true;
                },

                isUserVisible(u) {
                    if (!this.searchUser) return true;
                    const q = this.searchUser.toLowerCase();
                    return (u.name && u.name.toLowerCase().includes(q)) ||
                           (u.email && u.email.toLowerCase().includes(q)) ||
                           (u.role_name && u.role_name.toLowerCase().includes(q)) ||
                           (u.sales_name && u.sales_name.toLowerCase().includes(q)) ||
                           (u.sales_code && u.sales_code.toLowerCase().includes(q)) ||
                           (u.phone && u.phone.toLowerCase().includes(q));
                },

                get filteredUsers() {
                    return this.usersList.filter(u => this.isUserVisible(u));
                },

                // Logika Paginasi Master Pengguna
                get totalUserPages() {
                    if (this.userPerPage === 'ALL') return 1;
                    const perPage = parseInt(this.userPerPage, 10) || 10;
                    return Math.max(1, Math.ceil(this.filteredUsers.length / perPage));
                },

                get paginatedUserIds() {
                    if (this.userPerPage === 'ALL') {
                        return this.filteredUsers.map(u => u.id);
                    }
                    const perPage = parseInt(this.userPerPage, 10) || 10;
                    const start = (this.userPage - 1) * perPage;
                    return this.filteredUsers.slice(start, start + perPage).map(u => u.id);
                },

                get userPaginationStart() {
                    if (this.filteredUsers.length === 0) return 0;
                    if (this.userPerPage === 'ALL') return 1;
                    const perPage = parseInt(this.userPerPage, 10) || 10;
                    return (this.userPage - 1) * perPage + 1;
                },

                get userPaginationEnd() {
                    if (this.userPerPage === 'ALL') return this.filteredUsers.length;
                    const perPage = parseInt(this.userPerPage, 10) || 10;
                    return Math.min(this.userPage * perPage, this.filteredUsers.length);
                },

                get displayedUserPages() {
                    const current = this.userPage;
                    const total = this.totalUserPages;
                    if (total <= 7) {
                        return Array.from({ length: total }, (_, i) => i + 1);
                    }
                    if (current <= 4) {
                        return [1, 2, 3, 4, 5, '...', total];
                    }
                    if (current >= total - 3) {
                        return [1, '...', total - 4, total - 3, total - 2, total - 1, total];
                    }
                    return [1, '...', current - 1, current, current + 1, '...', total];
                },

                refreshIcons() {
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                openEditModal(userId) {
                    const u = this.usersList.find(x => x.id === userId);
                    if (!u) return;
                    this.editData = { ...u };
                    this.editSignMode = 'upload';
                    this.editRemoveSignature = false;
                    this.editCanvasData = '';
                    this.editModalOpen = true;
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
