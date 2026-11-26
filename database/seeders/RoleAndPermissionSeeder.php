<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. DAFTAR PERMISSION MODULAR & GRANULAR (VIEW, CREATE, EDIT, VOID/DELETE)
        $permissionsByModule = [
            'Pengadaan (PO & GR)' => [
                'po.view' => 'Melihat Daftar & Detail PO',
                'po.create' => 'Membuat & Menerbitkan PO',
                'po.void' => 'Melakukan VOID / Pembatalan PO',
                'gr.view' => 'Melihat Penerimaan Barang (GR)',
                'gr.create' => 'Menerima Fisik Barang (GR)',
                'gr.void' => 'Melakukan VOID Dokumen GR',
            ],
            'Gudang & Mutasi Stok' => [
                'inventory.view' => 'Melihat Posisi Stok & Kartu Stok',
                'stock_movement.view' => 'Melihat Riwayat Mutasi Stok (SM)',
                'stock_movement.create' => 'Membuat Mutasi Antar Gudang (SM)',
                'stock_movement.multi' => 'Upload SM Multi (Massal via Excel)',
                'stock_movement.void' => 'Melakukan VOID / Pembatalan SM',
                'stock_opname.view' => 'Melihat Data Stock Opname',
                'stock_opname.create' => 'Melakukan Stock Opname & Penyesuaian',
            ],
            'Penjualan (Sales Order & Delivery)' => [
                'so.view' => 'Melihat Daftar & Detail Sales Order',
                'so.create' => 'Membuat Sales Order (SO)',
                'so.void' => 'Melakukan VOID / Pembatalan SO',
                'delivery.view' => 'Melihat Pengiriman Barang (GD)',
                'delivery.create' => 'Memproses Delivery & Cetak Surat Jalan',
                'delivery.void' => 'Melakukan VOID Dokumen Pengiriman',
            ],
            'Keuangan & Kasir' => [
                'finance.view' => 'Melihat Kartu Piutang & Buku Kas/Bank',
                'finance.create_bbm' => 'Membuat Bukti Bank Masuk (BBM)',
                'finance.create_ttk' => 'Penerimaan Pembayaran Kasir (TTK)',
                'finance.void_payment' => 'Melakukan VOID Pembayaran / BBM / TTK',
            ],
            'Pusat Laporan & Approval' => [
                'reports.view' => 'Melihat & Unduh Laporan (Excel/PDF)',
                'audit.view' => 'Melihat Audit Trail & Log Aktivitas',
                'approvals.manage' => 'Melakukan Persetujuan (Approval Center)',
            ],
            'Master Data & Konfigurasi' => [
                'master.view' => 'Melihat Master Data (Produk, Customer, dll)',
                'master.create' => 'Menambah Master Data',
                'master.edit' => 'Mengubah / Nonaktifkan Master Data',
                'roles.manage' => 'Mengatur Role & Hak Akses Pengguna',
            ],
        ];

        // Buat semua permissions
        foreach ($permissionsByModule as $module => $perms) {
            foreach ($perms as $permName => $label) {
                Permission::firstOrCreate(
                    ['name' => $permName, 'guard_name' => 'web']
                );
            }
        }

        // 2. DAFTAR 6 ROLE RESMI
        $rolesData = [
            'Super User' => [
                'description' => 'Akses penuh ke seluruh sistem, konfigurasi, otorisasi wilayah, dan persetujuan tingkat tertinggi.',
                'permissions' => 'ALL',
            ],
            'Manager support' => [
                'description' => 'Monitoring seluruh operasional, persetujuan (approval), audit log, laporan terpadu, dan hak VOID jika diperlukan.',
                'permissions' => [
                    'po.view', 'po.create', 'po.void',
                    'gr.view', 'gr.create', 'gr.void',
                    'inventory.view', 'stock_movement.view', 'stock_movement.create', 'stock_movement.multi', 'stock_movement.void', 'stock_opname.view',
                    'so.view', 'so.create', 'so.void',
                    'delivery.view', 'delivery.create', 'delivery.void',
                    'finance.view', 'finance.create_bbm', 'finance.create_ttk', 'finance.void_payment',
                    'reports.view', 'audit.view', 'approvals.manage',
                    'master.view', 'master.create', 'master.edit'
                ],
            ],
            'SPV' => [
                'description' => 'Supervisor operasional: review & verifikasi transaksi harian, approval lapangan, monitoring penjualan & stok, hak VOID terbatas.',
                'permissions' => [
                    'po.view', 'po.create',
                    'gr.view', 'gr.create',
                    'inventory.view', 'stock_movement.view', 'stock_movement.create', 'stock_movement.multi', 'stock_opname.view',
                    'so.view', 'so.create', 'so.void',
                    'delivery.view', 'delivery.create',
                    'finance.view', 'finance.create_ttk',
                    'reports.view', 'approvals.manage',
                    'master.view'
                ],
            ],
            'Admin sales' => [
                'description' => 'Operasional penjualan: input Sales Order (SO), penerbitan Delivery (GD), monitoring piutang toko, dan input tanda terima kasir (TTK).',
                'permissions' => [
                    'inventory.view',
                    'so.view', 'so.create',
                    'delivery.view', 'delivery.create',
                    'finance.view', 'finance.create_ttk',
                    'reports.view',
                    'master.view'
                ],
            ],
            'Inventory' => [
                'description' => 'Operasional gudang & logistik: penerimaan barang (GR), mutasi stok (SM Biasa & Multi), opname fisik, dan delivery order keluar.',
                'permissions' => [
                    'po.view',
                    'gr.view', 'gr.create',
                    'inventory.view', 'stock_movement.view', 'stock_movement.create', 'stock_movement.multi', 'stock_opname.view', 'stock_opname.create',
                    'delivery.view', 'delivery.create',
                    'reports.view',
                    'master.view'
                ],
            ],
            'Finance' => [
                'description' => 'Operasional kas & bank: penerimaan pembayaran (TTK/BBM), rekonsiliasi piutang, buku besar kas/bank, dan laporan keuangan.',
                'permissions' => [
                    'so.view',
                    'finance.view', 'finance.create_bbm', 'finance.create_ttk',
                    'reports.view',
                    'master.view'
                ],
            ],
        ];

        $allPermissionNames = Permission::pluck('name')->toArray();

        foreach ($rolesData as $roleName => $data) {
            $slug = \Illuminate\Support\Str::slug($roleName);
            
            // Cek apakah role sudah ada berdasarkan name atau slug
            $role = Role::where('name', $roleName)
                ->orWhere('slug', $slug)
                ->first();

            if (!$role) {
                $role = Role::create([
                    'name' => $roleName,
                    'slug' => $slug,
                    'guard_name' => 'web',
                    'description' => $data['description']
                ]);
            } else {
                $role->name = $roleName;
                $role->slug = $slug;
                $role->guard_name = 'web';
                $role->description = $data['description'];
                $role->save();
            }

            if ($data['permissions'] === 'ALL') {
                $role->syncPermissions($allPermissionNames);
            } else {
                $role->syncPermissions($data['permissions']);
            }
        }

        // 3. Pastikan user superadmin/admin yang ada tersambung ke role 'Super User'
        $superUsers = \App\Models\User::where('email', 'admin@distripos.com')
            ->orWhere('email', 'like', '%superadmin%')
            ->orWhere('role_id', 1)
            ->get();

        $superRole = Role::where('name', 'Super User')->first();
        if ($superRole) {
            foreach ($superUsers as $u) {
                if (!$u->hasRole('Super User')) {
                    $u->assignRole($superRole);
                }
                // sinkronisasi role_id di users table
                $u->role_id = $superRole->id;
                $u->save();
            }
        }
    }
}
