<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TerritoryScope
{
    protected static array $cache = [];

    /**
     * Ambil cakupan wilayah & gudang user berdasarkan tabel tunggal
     * `user_has_areas_clusters_taps_warehouses`.
     * Di-cache per request untuk menghindari query berulang.
     */
    public static function for(?User $user = null): array
    {
        $user = $user ?? Auth::user();
        if (!$user) {
            return [
                'is_full_access' => false,
                'is_ho_readonly' => false,
                'sales_id' => null,
                'area_ids' => [],
                'cluster_ids' => [],
                'tap_ids' => [],
                'warehouse_ids' => [],
            ];
        }

        if (isset(static::$cache[$user->id])) {
            return static::$cache[$user->id];
        }

        $role = DB::table('roles')->where('id', $user->role_id)->first();
        $isFullAccess = (bool) $user->has_full_access || ($role && in_array($role->slug, ['it_dev', 'super_admin'], true));

        if ($isFullAccess) {
            return static::$cache[$user->id] = [
                'is_full_access' => true,
                'is_ho_readonly' => (bool) $user->is_ho_readonly,
                'sales_id' => $user->sales_id,
                'area_ids' => DB::table('areas')->pluck('id')->map(fn ($v) => (int) $v)->all(),
                'cluster_ids' => DB::table('clusters')->pluck('id')->map(fn ($v) => (int) $v)->all(),
                'tap_ids' => DB::table('taps')->pluck('id')->map(fn ($v) => (int) $v)->all(),
                'warehouse_ids' => DB::table('warehouses')->pluck('id')->map(fn ($v) => (int) $v)->all(),
            ];
        }

        $mappings = DB::table('user_has_areas_clusters_taps_warehouses')
            ->where('user_id', $user->id)
            ->get();

        // Akun Sales Kanvas tanpa mapping eksplisit -> kunci ke TAP & gudang kanvas miliknya sendiri
        if ($user->sales_id && $mappings->isEmpty()) {
            $sales = DB::table('sales')->where('id', $user->sales_id)->first();
            $tapIds = ($sales && $sales->tap_id) ? [(int) $sales->tap_id] : [];
            $clusterIds = !empty($tapIds)
                ? DB::table('taps')->whereIn('id', $tapIds)->pluck('cluster_id')->filter()->unique()->map(fn ($v) => (int) $v)->values()->all()
                : [];
            $areaIds = !empty($clusterIds)
                ? DB::table('clusters')->whereIn('id', $clusterIds)->pluck('area_id')->filter()->unique()->map(fn ($v) => (int) $v)->values()->all()
                : [];
            $whIds = DB::table('warehouses')->where('sales_id', $user->sales_id)->pluck('id')->map(fn ($v) => (int) $v)->all();

            return static::$cache[$user->id] = [
                'is_full_access' => false,
                'is_ho_readonly' => (bool) $user->is_ho_readonly,
                'sales_id' => $user->sales_id,
                'area_ids' => $areaIds,
                'cluster_ids' => $clusterIds,
                'tap_ids' => $tapIds,
                'warehouse_ids' => $whIds,
            ];
        }

        // User non-admin tanpa mapping sama sekali -> 0 data (kosong demi keamanan)
        if ($mappings->isEmpty()) {
            return static::$cache[$user->id] = [
                'is_full_access' => false,
                'is_ho_readonly' => (bool) $user->is_ho_readonly,
                'sales_id' => $user->sales_id,
                'area_ids' => [],
                'cluster_ids' => [],
                'tap_ids' => [],
                'warehouse_ids' => [],
            ];
        }

        // 1. Kumpulkan TAP IDs (langsung dari tap_id, atau turunan dari cluster_id / area_id jika tap_id null)
        $directTapIds = $mappings->pluck('tap_id')->filter()->unique()->map(fn ($v) => (int) $v)->values()->all();
        $clusterOnlyIds = $mappings->whereNull('tap_id')->pluck('cluster_id')->filter()->unique()->map(fn ($v) => (int) $v)->values()->all();
        $areaOnlyIds = $mappings->whereNull('tap_id')->whereNull('cluster_id')->pluck('area_id')->filter()->unique()->map(fn ($v) => (int) $v)->values()->all();

        $tapIds = $directTapIds;
        if (!empty($clusterOnlyIds)) {
            $tapsFromClusters = DB::table('taps')->whereIn('cluster_id', $clusterOnlyIds)->pluck('id')->map(fn ($v) => (int) $v)->all();
            $tapIds = array_values(array_unique(array_merge($tapIds, $tapsFromClusters)));
        }
        if (!empty($areaOnlyIds)) {
            $tapsFromAreas = DB::table('taps')
                ->join('clusters', 'taps.cluster_id', '=', 'clusters.id')
                ->whereIn('clusters.area_id', $areaOnlyIds)
                ->pluck('taps.id')
                ->map(fn ($v) => (int) $v)
                ->all();
            $tapIds = array_values(array_unique(array_merge($tapIds, $tapsFromAreas)));
        }

        // 2. Kumpulkan Cluster IDs & Area IDs
        $clusterIds = $mappings->pluck('cluster_id')->filter()->unique()->map(fn ($v) => (int) $v)->values()->all();
        if (!empty($tapIds)) {
            $clustersFromTaps = DB::table('taps')->whereIn('id', $tapIds)->pluck('cluster_id')->filter()->unique()->map(fn ($v) => (int) $v)->all();
            $clusterIds = array_values(array_unique(array_merge($clusterIds, $clustersFromTaps)));
        }
        if (!empty($areaOnlyIds)) {
            $clustersFromAreas = DB::table('clusters')->whereIn('area_id', $areaOnlyIds)->pluck('id')->map(fn ($v) => (int) $v)->all();
            $clusterIds = array_values(array_unique(array_merge($clusterIds, $clustersFromAreas)));
        }

        $areaIds = $mappings->pluck('area_id')->filter()->unique()->map(fn ($v) => (int) $v)->values()->all();
        if (!empty($clusterIds)) {
            $areasFromClusters = DB::table('clusters')->whereIn('id', $clusterIds)->pluck('area_id')->filter()->unique()->map(fn ($v) => (int) $v)->all();
            $areaIds = array_values(array_unique(array_merge($areaIds, $areasFromClusters)));
        }

        // 3. Kumpulkan Warehouse IDs
        if ($user->sales_id) {
            $whIds = DB::table('warehouses')->where('sales_id', $user->sales_id)->pluck('id')->map(fn ($v) => (int) $v)->all();
        } else {
            $whIds = [];
            // A. TAP dengan is_all_warehouses = 1 -> otomatis semua gudang di TAP tersebut
            $autoTapIds = $mappings->where('is_all_warehouses', 1)->pluck('tap_id')->filter()->unique()->map(fn ($v) => (int) $v)->all();
            if (!empty($autoTapIds)) {
                $autoWhs = DB::table('warehouses')->whereIn('tap_id', $autoTapIds)->pluck('id')->map(fn ($v) => (int) $v)->all();
                $whIds = array_merge($whIds, $autoWhs);
            }
            // B. Broad mapping (cluster/area level dengan tap_id null) -> semua gudang di TAP turunannya
            $broadTapIds = array_diff($tapIds, $directTapIds);
            if (!empty($broadTapIds)) {
                $broadWhs = DB::table('warehouses')->whereIn('tap_id', $broadTapIds)->pluck('id')->map(fn ($v) => (int) $v)->all();
                $whIds = array_merge($whIds, $broadWhs);
            }
            // C. Gudang spesifik (warehouse_id)
            $customWhs = $mappings->pluck('warehouse_id')->filter()->unique()->map(fn ($v) => (int) $v)->all();
            $whIds = array_values(array_unique(array_merge($whIds, $customWhs)));
        }

        return static::$cache[$user->id] = [
            'is_full_access' => false,
            'is_ho_readonly' => (bool) $user->is_ho_readonly,
            'sales_id' => $user->sales_id,
            'area_ids' => $areaIds,
            'cluster_ids' => $clusterIds,
            'tap_ids' => $tapIds,
            'warehouse_ids' => $whIds,
        ];
    }

    public static function flush(): void
    {
        static::$cache = [];
    }

    public static function canAccessArea(int $areaId, ?User $user = null): bool
    {
        $scope = static::for($user);
        return $scope['is_full_access'] || in_array($areaId, $scope['area_ids'], true);
    }

    public static function canAccessCluster(int $clusterId, ?User $user = null): bool
    {
        $scope = static::for($user);
        return $scope['is_full_access'] || in_array($clusterId, $scope['cluster_ids'], true);
    }

    public static function canAccessTap(int $tapId, ?User $user = null): bool
    {
        $scope = static::for($user);
        return $scope['is_full_access'] || in_array($tapId, $scope['tap_ids'], true);
    }

    public static function canAccessWarehouse(int $warehouseId, ?User $user = null): bool
    {
        $scope = static::for($user);
        return $scope['is_full_access'] || in_array($warehouseId, $scope['warehouse_ids'], true);
    }

    public static function canAccessTerritory(?int $areaId, ?int $clusterId, ?int $tapId, ?User $user = null): bool
    {
        $scope = static::for($user);
        if ($scope['is_full_access']) return true;
        if ($tapId) return in_array($tapId, $scope['tap_ids'], true);
        if ($clusterId) return in_array($clusterId, $scope['cluster_ids'], true);
        if ($areaId) return in_array($areaId, $scope['area_ids'], true);
        return false;
    }

    public static function applyAreaScope($query, string $column = 'area_id', ?User $user = null)
    {
        $scope = static::for($user);
        if ($scope['is_full_access']) return $query;
        if (empty($scope['area_ids'])) return $query->whereRaw('1 = 0');
        return $query->whereIn($column, $scope['area_ids']);
    }

    public static function applyClusterScope($query, string $column = 'cluster_id', ?User $user = null)
    {
        $scope = static::for($user);
        if ($scope['is_full_access']) return $query;
        if (empty($scope['cluster_ids'])) return $query->whereRaw('1 = 0');
        return $query->whereIn($column, $scope['cluster_ids']);
    }

    public static function applyTapScope($query, string $column = 'tap_id', ?User $user = null)
    {
        $scope = static::for($user);
        if ($scope['is_full_access']) return $query;
        if (empty($scope['tap_ids'])) return $query->whereRaw('1 = 0');
        return $query->whereIn($column, $scope['tap_ids']);
    }

    public static function applyWarehouseScope($query, string $column = 'warehouse_id', ?User $user = null)
    {
        $scope = static::for($user);
        if ($scope['is_full_access']) return $query;
        if (empty($scope['warehouse_ids'])) return $query->whereRaw('1 = 0');
        return $query->whereIn($column, $scope['warehouse_ids']);
    }
}
