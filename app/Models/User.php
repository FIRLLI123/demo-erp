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

#[Fillable(['name', 'email', 'password', 'role_id', 'cluster_id', 'tap_id', 'sales_id', 'phone', 'is_active', 'auto_include_sales_wh', 'is_ho_readonly'])]
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
            'auto_include_sales_wh' => 'boolean',
            'is_ho_readonly' => 'boolean',
        ];
    }

    /**
     * Mengambil daftar TAP yang dapat diakses / dimonitor oleh user
     */
    public function getAllowedTaps()
    {
        $role = DB::table('roles')->where('id', $this->role_id)->first();
        if ($role && in_array($role->slug, ['it_dev', 'super_admin'])) {
            return DB::table('taps')->where('status', 'ACTIVE')->orderBy('name')->get();
        }

        // Cek multi-TAP di tabel user_taps
        $multiTapIds = DB::table('user_taps')->where('user_id', $this->id)->pluck('tap_id')->toArray();
        if (!empty($multiTapIds)) {
            return DB::table('taps')->whereIn('id', $multiTapIds)->where('status', 'ACTIVE')->orderBy('name')->get();
        }

        // Cek jika penugasan per Cluster
        if ($this->cluster_id) {
            return DB::table('taps')->where('cluster_id', $this->cluster_id)->where('status', 'ACTIVE')->orderBy('name')->get();
        }

        // Cek single TAP
        if ($this->tap_id) {
            return DB::table('taps')->where('id', $this->tap_id)->where('status', 'ACTIVE')->get();
        }

        // Default IT fallback
        return DB::table('taps')->where('status', 'ACTIVE')->orderBy('name')->get();
    }

    /**
     * Mengambil daftar gudang yang memiliki hak transaksi bagi user
     */
    public function getAllowedWarehouses()
    {
        // 1. Akun HO / Monitoring: Mutlak 0 Gudang (Hanya Lihat Laporan)
        if ($this->is_ho_readonly) {
            return collect([]);
        }

        // 2. Akun Sales Kanvas: Otomatis terkunci hanya ke gudang motor miliknya sendiri
        if ($this->sales_id) {
            return DB::table('warehouses')
                ->where('sales_id', $this->sales_id)
                ->where('status', 'ACTIVE')
                ->get();
        }

        // 3. Mode Custom Checklist Gudang (auto_include_sales_wh = false)
        if (!$this->auto_include_sales_wh) {
            $mappedWhIds = DB::table('user_warehouses')
                ->where('user_id', $this->id)
                ->pluck('warehouse_id')
                ->toArray();

            return DB::table('warehouses')
                ->whereIn('id', $mappedWhIds)
                ->where('status', 'ACTIVE')
                ->orderBy('name')
                ->get();
        }

        // 4. Mode Otomatis Ikut TAP (Termasuk Otomatis Seluruh Sales Baru)
        $allowedTapIds = $this->getAllowedTaps()->pluck('id')->toArray();
        if (empty($allowedTapIds)) {
            return collect([]);
        }

        return DB::table('warehouses')
            ->whereIn('tap_id', $allowedTapIds)
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
