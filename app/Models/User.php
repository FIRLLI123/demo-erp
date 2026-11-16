<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

#[Fillable(['name', 'email', 'password', 'role_id', 'has_full_access', 'cluster_id', 'tap_id', 'sales_id', 'phone', 'is_active', 'auto_include_sales_wh', 'is_ho_readonly'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'has_full_access' => 'boolean',
            'auto_include_sales_wh' => 'boolean',
            'is_ho_readonly' => 'boolean',
        ];
    }

    /**
     * Mengambil daftar TAP yang dapat diakses / dimonitor oleh user
     */
    public function getAllowedTaps()
    {
        // 1. Akses Penuh: Seluruh TAP
        if ($this->has_full_access) {
            return DB::table('taps')->where('status', 'ACTIVE')->orderBy('name')->get();
        }

        $role = DB::table('roles')->where('id', $this->role_id)->first();
        if ($role && in_array($role->slug, ['it_dev', 'super_admin'])) {
            return DB::table('taps')->where('status', 'ACTIVE')->orderBy('name')->get();
        }

        // 2. Ambil dari tabel tunggal user_has_areas_clusters_taps_warehouses
        $tapIds = DB::table('user_has_areas_clusters_taps_warehouses')
            ->where('user_id', $this->id)
            ->whereNotNull('tap_id')
            ->pluck('tap_id')
            ->unique()
            ->toArray();

        if (!empty($tapIds)) {
            return DB::table('taps')->whereIn('id', $tapIds)->where('status', 'ACTIVE')->orderBy('name')->get();
        }

        // Cek jika ada penugasan cluster di mapping
        $clusterIds = DB::table('user_has_areas_clusters_taps_warehouses')
            ->where('user_id', $this->id)
            ->whereNotNull('cluster_id')
            ->pluck('cluster_id')
            ->unique()
            ->toArray();

        if (!empty($clusterIds)) {
            return DB::table('taps')->whereIn('cluster_id', $clusterIds)->where('status', 'ACTIVE')->orderBy('name')->get();
        }

        // Fallback tap_id profil
        if ($this->tap_id) {
            return DB::table('taps')->where('id', $this->tap_id)->where('status', 'ACTIVE')->get();
        }

        return DB::table('taps')->where('status', 'ACTIVE')->orderBy('name')->get();
    }

    /**
     * Mengambil daftar gudang yang memiliki hak transaksi bagi user
     */
    public function getAllowedWarehouses()
    {
        // 1. Mode HO / Monitoring: Mutlak 0 Gudang (Hanya Lihat Laporan)
        if ($this->is_ho_readonly) {
            return collect([]);
        }

        // 2. Akun Sales Kanvas: Otomatis terkunci ke tas motornya sendiri
        if ($this->sales_id) {
            return DB::table('warehouses')
                ->where('sales_id', $this->sales_id)
                ->where('status', 'ACTIVE')
                ->get();
        }

        // 3. Akses Penuh: Seluruh Gudang
        if ($this->has_full_access) {
            return DB::table('warehouses')->where('status', 'ACTIVE')->orderBy('type')->orderBy('name')->get();
        }

        // 4. Ambil dari tabel tunggal user_has_areas_clusters_taps_warehouses
        $mappings = DB::table('user_has_areas_clusters_taps_warehouses')
            ->where('user_id', $this->id)
            ->get();

        if ($mappings->isEmpty()) {
            return collect([]);
        }

        $allWhIds = collect([]);

        // A. TAP dengan mode is_all_warehouses = 1 (Semua gudang di TAP termasuk sales baru)
        $autoTapIds = $mappings->where('is_all_warehouses', 1)->pluck('tap_id')->unique()->filter()->toArray();
        if (!empty($autoTapIds)) {
            $autoWhs = DB::table('warehouses')
                ->whereIn('tap_id', $autoTapIds)
                ->where('status', 'ACTIVE')
                ->pluck('id');
            $allWhIds = $allWhIds->merge($autoWhs);
        }

        // B. Gudang spesifik (Custom checklist)
        $specificWhIds = $mappings->where('is_all_warehouses', 0)->pluck('warehouse_id')->unique()->filter()->toArray();
        if (!empty($specificWhIds)) {
            $allWhIds = $allWhIds->merge($specificWhIds);
        }

        $uniqueIds = $allWhIds->unique()->toArray();
        if (empty($uniqueIds)) {
            return collect([]);
        }

        return DB::table('warehouses')
            ->whereIn('id', $uniqueIds)
            ->where('status', 'ACTIVE')
            ->orderBy('type')
            ->orderBy('name')
            ->get();
    }

    /**
     * Cek apakah user berhak melakukan transaksi pada suatu gudang tertentu
     */
    public function canTransactWarehouse($warehouseId): bool
    {
        if ($this->is_ho_readonly) return false;
        return $this->getAllowedWarehouses()->contains('id', (int)$warehouseId);
    }
}
