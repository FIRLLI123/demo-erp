<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\DB;
use App\Services\TerritoryScope;

#[Fillable(['name', 'email', 'password', 'role_id', 'has_full_access', 'cluster_id', 'tap_id', 'sales_id', 'phone', 'is_active', 'auto_include_sales_wh', 'is_ho_readonly'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

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
     * Ambil seluruh cakupan wilayah & gudang user dari TerritoryScope (terpusat)
     */
    public function territoryScope(): array
    {
        return TerritoryScope::for($this);
    }

    /**
     * Mengambil daftar TAP yang dapat diakses / dimonitor oleh user
     */
    public function getAllowedTaps()
    {
        $scope = TerritoryScope::for($this);
        if ($scope['is_full_access']) {
            return DB::table('taps')->where('status', 'ACTIVE')->orderBy('name')->get();
        }
        if (empty($scope['tap_ids'])) {
            return collect([]);
        }
        return DB::table('taps')
            ->whereIn('id', $scope['tap_ids'])
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get();
    }

    /**
     * Mengambil daftar gudang yang memiliki hak transaksi bagi user
     */
    public function getAllowedWarehouses()
    {
        // Mode HO / Monitoring: Mutlak 0 Gudang untuk transaksi tulis
        if ($this->is_ho_readonly) {
            return collect([]);
        }

        $scope = TerritoryScope::for($this);
        if ($scope['is_full_access']) {
            return DB::table('warehouses')->where('status', 'ACTIVE')->orderBy('type')->orderBy('name')->get();
        }
        if (empty($scope['warehouse_ids'])) {
            return collect([]);
        }

        return DB::table('warehouses')
            ->whereIn('id', $scope['warehouse_ids'])
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
