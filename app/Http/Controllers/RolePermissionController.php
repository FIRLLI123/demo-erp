<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    /**
     * Tampilan Matriks Role & Permissions
     */
    public function index()
    {
        $roles = Role::where('guard_name', 'web')->orderBy('id')->get();
        
        // Kelompokkan permissions berdasarkan modul
        $permissions = Permission::where('guard_name', 'web')->orderBy('name')->get();

        $modules = [
            'Pengadaan (PO & GR)' => [
                'prefix' => ['po.', 'gr.'],
                'description' => 'Akses pemesanan ke vendor, approval PO, penerimaan fisik barang (GR), dan hak VOID.',
                'icon' => 'truck',
                'color' => 'emerald'
            ],
            'Gudang & Mutasi Stok' => [
                'prefix' => ['inventory.', 'stock_movement.', 'stock_opname.'],
                'description' => 'Akses kartu stok, mutasi antar gudang (SM Biasa & Multi Excel), dan penyesuaian opname.',
                'icon' => 'boxes',
                'color' => 'cyan'
            ],
            'Penjualan (Sales Order & Delivery)' => [
                'prefix' => ['so.', 'delivery.'],
                'description' => 'Akses input Sales Order (SO), cetak pengiriman (GD), dan hak VOID pesanan.',
                'icon' => 'shopping-bag',
                'color' => 'amber'
            ],
            'Keuangan & Kasir' => [
                'prefix' => ['finance.'],
                'description' => 'Akses buku kas/bank, pembuatan BBM, tanda terima kasir (TTK), dan VOID pembayaran.',
                'icon' => 'receipt',
                'color' => 'rose'
            ],
            'Pusat Laporan & Approval' => [
                'prefix' => ['reports.', 'audit.', 'approvals.'],
                'description' => 'Akses unduh laporan eksekutif (Excel/PDF), log aktivitas, dan approval transaksi.',
                'icon' => 'file-spreadsheet',
                'color' => 'indigo'
            ],
            'Master Data & Sistem' => [
                'prefix' => ['master.', 'roles.'],
                'description' => 'Akses konfigurasi master produk, customer, harga, serta pengelolaan role & akses.',
                'icon' => 'shield-check',
                'color' => 'slate'
            ],
        ];

        $matrix = [];
        foreach ($modules as $modName => $modMeta) {
            $modPerms = $permissions->filter(function ($p) use ($modMeta) {
                foreach ($modMeta['prefix'] as $pref) {
                    if (str_starts_with($p->name, $pref)) return true;
                }
                return false;
            })->values();

            $matrix[$modName] = [
                'meta' => $modMeta,
                'permissions' => $modPerms
            ];
        }

        // Ambil data mapping role-permission
        $rolePermissions = [];
        foreach ($roles as $r) {
            $rolePermissions[$r->id] = $r->permissions->pluck('name')->toArray();
        }

        return view('master.roles_matrix', compact('roles', 'matrix', 'rolePermissions'));
    }

    /**
     * Toggle satu permission pada role secara instan (AJAX)
     */
    public function togglePermission(Request $request)
    {
        $roleId = (int)$request->input('role_id');
        $permissionName = $request->input('permission_name');

        $role = Role::find($roleId);
        if (!$role) {
            return response()->json(['success' => false, 'message' => 'Role tidak ditemukan!'], 404);
        }

        $perm = Permission::where('name', $permissionName)->first();
        if (!$perm) {
            return response()->json(['success' => false, 'message' => 'Permission tidak ditemukan!'], 404);
        }

        if ($role->hasPermissionTo($permissionName)) {
            $role->revokePermissionTo($permissionName);
            $hasAccess = false;
            $msg = "Akses [{$permissionName}] dinonaktifkan untuk role [{$role->name}].";
        } else {
            $role->givePermissionTo($permissionName);
            $hasAccess = true;
            $msg = "Akses [{$permissionName}] berhasil diberikan untuk role [{$role->name}].";
        }

        // Reset cache Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json([
            'success' => true,
            'has_access' => $hasAccess,
            'message' => $msg
        ]);
    }

    /**
     * Batch toggle per modul (Pilih Semua / Batal Semua)
     */
    public function toggleModule(Request $request)
    {
        $roleId = (int)$request->input('role_id');
        $permissionNames = (array)$request->input('permission_names', []);
        $action = $request->input('action', 'grant'); // 'grant' | 'revoke'

        $role = Role::find($roleId);
        if (!$role) {
            return response()->json(['success' => false, 'message' => 'Role tidak ditemukan!'], 404);
        }

        if ($action === 'grant') {
            $role->givePermissionTo($permissionNames);
            $msg = "Seluruh hak akses modul berhasil diaktifkan untuk [{$role->name}].";
        } else {
            foreach ($permissionNames as $p) {
                $role->revokePermissionTo($p);
            }
            $msg = "Seluruh hak akses modul berhasil dicabut dari [{$role->name}].";
        }

        // Reset cache Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json([
            'success' => true,
            'active_permissions' => $role->fresh()->permissions->pluck('name')->toArray(),
            'message' => $msg
        ]);
    }

    /**
     * Tambah Role Baru
     */
    public function storeRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:roles,name',
        ], [
            'name.required' => 'Nama Role wajib diisi.',
            'name.unique' => 'Nama Role tersebut sudah ada di sistem.',
        ]);

        $roleName = trim($request->name);
        $slug = \Illuminate\Support\Str::slug($roleName);

        $role = Role::create([
            'name' => $roleName,
            'slug' => $slug,
            'guard_name' => 'web'
        ]);

        // Reset cache Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('master.roles.matrix', ['role_id' => $role->id])
            ->with('success', "Role [{$role->name}] berhasil ditambahkan. Silakan centang hak aksesnya di bawah.");
    }

    /**
     * Edit / Ganti Nama Role
     */
    public function updateRole(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100|unique:roles,name,' . $id,
        ], [
            'name.required' => 'Nama Role wajib diisi.',
            'name.unique' => 'Nama Role tersebut sudah digunakan.',
        ]);

        $oldName = $role->name;
        $role->name = trim($request->name);
        $role->slug = \Illuminate\Support\Str::slug($role->name);
        $role->save();

        // Reset cache Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('master.roles.matrix', ['role_id' => $role->id])
            ->with('success', "Nama Role [{$oldName}] berhasil diubah menjadi [{$role->name}].");
    }

    /**
     * Hapus Role
     */
    public function deleteRole($id)
    {
        $role = Role::findOrFail($id);

        // Proteksi Super User tidak boleh dihapus
        if (in_array(strtolower($role->name), ['super user', 'super_user', 'superadmin'])) {
            return redirect()->back()->with('error', 'Role [Super User] adalah peran sistem utama dan tidak dapat dihapus!');
        }

        // Cek apakah ada user aktif yang menggunakan role ini
        $usersCount = \App\Models\User::where('role_id', $role->id)
            ->orWhereHas('roles', function($q) use ($role) {
                $q->where('id', $role->id);
            })->count();

        if ($usersCount > 0) {
            return redirect()->back()->with('error', "Gagal menghapus! Masih ada {$usersCount} pengguna aktif yang menggunakan role [{$role->name}]. Silakan pindahkan role pengguna terlebih dahulu.");
        }

        $roleName = $role->name;
        $role->syncPermissions([]); // cabut semua permission
        $role->delete();

        // Reset cache Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('master.roles.matrix')
            ->with('success', "Role [{$roleName}] berhasil dihapus dari sistem.");
    }
}
