<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class TerritoryScope
{
    protected static array $cache = [];
    protected static array $baseCache = [];

    /**
     * Ambil cakupan wilayah & gudang DASAR user berdasarkan role / tabel
     * `user_has_areas_clusters_taps_warehouses` (sebelum filter active cluster di session).
     */
    public static function getBaseScope(?User $user = null): array
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

        if (isset(static::$baseCache[$user->id])) {
            return static::$baseCache[$user->id];
        }

        $role = DB::table('roles')->where('id', $user->role_id)->first();
        $isFullAccess = (bool) $user->has_full_access || ($role && in_array($role->slug, ['it_dev', 'super_admin'], true));

        if ($isFullAccess) {
            return static::$baseCache[$user->id] = [
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

            return static::$baseCache[$user->id] = [
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
            return static::$baseCache[$user->id] = [
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

        return static::$baseCache[$user->id] = [
            'is_full_access' => false,
            'is_ho_readonly' => (bool) $user->is_ho_readonly,
            'sales_id' => $user->sales_id,
            'area_ids' => $areaIds,
            'cluster_ids' => $clusterIds,
            'tap_ids' => $tapIds,
            'warehouse_ids' => $whIds,
        ];
    }

    /**
     * Ambil daftar Cluster yang berhak diakses user (untuk pilihan di dropdown navbar).
     */
    public static function getUserAccessibleClusters(?User $user = null): Collection
    {
        $base = static::getBaseScope($user);
        $query = DB::table('clusters')
            ->join('areas', 'clusters.area_id', '=', 'areas.id')
            ->select('clusters.*', 'areas.name as area_name', 'areas.code as area_code')
            ->where('clusters.status', 'ACTIVE')
            ->orderBy('areas.name')
            ->orderBy('clusters.name');

        if (!$base['is_full_access']) {
            if (empty($base['cluster_ids'])) {
                return collect();
            }
            $query->whereIn('clusters.id', $base['cluster_ids']);
        }

        return $query->get();
    }

    /**
     * Ambil cakupan wilayah & gudang EFEKTIF user (menggabungkan mapping user + active cluster di session).
     * Di-cache per request untuk performa tinggi.
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
                'active_cluster_id' => null,
                'active_cluster_name' => 'Semua Cluster',
            ];
        }

        if (isset(static::$cache[$user->id])) {
            return static::$cache[$user->id];
        }

        $base = static::getBaseScope($user);

        // Periksa filter cluster aktif di session
        $activeClusterId = session('active_cluster_id');

        if (!empty($activeClusterId) && $activeClusterId !== 'all') {
            $targetId = (int) $activeClusterId;
            // Validasi: hanya boleh jika ada di hak akses user atau user full access
            $canAccess = $base['is_full_access'] || in_array($targetId, $base['cluster_ids'], true);
            if ($canAccess) {
                $cluster = DB::table('clusters')->where('id', $targetId)->first();
                if ($cluster) {
                    $clusterTapIds = DB::table('taps')
                        ->where('cluster_id', $targetId)
                        ->where('status', 'ACTIVE')
                        ->pluck('id')
                        ->map(fn ($v) => (int) $v)
                        ->all();

                    $filteredTapIds = $base['is_full_access']
                        ? $clusterTapIds
                        : array_values(array_intersect($base['tap_ids'], $clusterTapIds));

                    $clusterWhIds = DB::table('warehouses')
                        ->whereIn('tap_id', $filteredTapIds)
                        ->where('status', 'ACTIVE')
                        ->pluck('id')
                        ->map(fn ($v) => (int) $v)
                        ->all();

                    $filteredWhIds = $base['is_full_access']
                        ? $clusterWhIds
                        : array_values(array_intersect($base['warehouse_ids'], $clusterWhIds));

                    return static::$cache[$user->id] = [
                        'is_full_access' => false, // Terkunci ke cluster yang dipilih
                        'is_ho_readonly' => $base['is_ho_readonly'],
                        'sales_id' => $base['sales_id'],
                        'area_ids' => [(int) $cluster->area_id],
                        'cluster_ids' => [$targetId],
                        'tap_ids' => $filteredTapIds,
                        'warehouse_ids' => $filteredWhIds,
                        'active_cluster_id' => $targetId,
                        'active_cluster_name' => $cluster->name,
                    ];
                }
            }
        }

        // Default: Semua Cluster (sesuai hak akses user)
        $base['active_cluster_id'] = null;
        $base['active_cluster_name'] = $base['is_full_access'] ? 'Semua Cluster (Konsolidasi)' : 'Semua Cluster Saya';
        return static::$cache[$user->id] = $base;
    }

    public static function flush(): void
    {
        static::$cache = [];
        static::$baseCache = [];
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

    public static function getActiveClusterId(?User $user = null): ?int
    {
        $scope = static::for($user);
        return $scope['active_cluster_id'] ?? null;
    }

    public static function getActiveClusterName(?User $user = null): string
    {
        $scope = static::for($user);
        return $scope['active_cluster_name'] ?? 'Semua Cluster';
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
