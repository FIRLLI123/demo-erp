<?php

namespace App\Services;

use App\Models\Cluster;
use App\Models\ClusterSoCutoff;
use App\Models\Customer;
use App\Models\User;
use Carbon\Carbon;

class OrderCutoffService
{
    /**
     * Dapatkan status cutoff berdasarkan cluster_id.
     * Jika cluster belum punya settingan, default jam 17:00 dan aktif.
     */
    public static function checkByClusterId(int $clusterId, ?Carbon $now = null): array
    {
        $cutoff = ClusterSoCutoff::where('cluster_id', $clusterId)->first();

        if (!$cutoff) {
            // Default jika belum disetting di database
            $defaultCutoff = new ClusterSoCutoff([
                'cluster_id' => $clusterId,
                'close_time' => '17:00:00',
                'is_active' => true,
                'is_force_closed' => false,
            ]);
            return $defaultCutoff->checkStatus($now);
        }

        return $cutoff->checkStatus($now);
    }

    /**
     * Cek status cutoff berdasarkan customer_id.
     * Alur relasi: Customer -> Tap -> Cluster
     */
    public static function checkByCustomerId(int $customerId, ?Carbon $now = null): array
    {
        $customer = Customer::with('tap.cluster')->find($customerId);

        if (!$customer || !$customer->tap || !$customer->tap->cluster_id) {
            return [
                'is_open' => true,
                'status' => 'OPEN',
                'reason' => 'Cluster tidak ditemukan, order diizinkan.',
                'close_time' => '17:00',
            ];
        }

        return self::checkByClusterId($customer->tap->cluster_id, $now);
    }

    /**
     * Cek status cutoff berdasarkan user sales (jika order dibuat oleh sales).
     */
    public static function checkBySalesUser(?User $user = null, ?Carbon $now = null): array
    {
        if (!$user) {
            return [
                'is_open' => true,
                'status' => 'OPEN',
                'reason' => 'User tidak terdefinisi.',
                'close_time' => '17:00',
            ];
        }

        if ($user->tap_id) {
            $user->loadMissing('tap');
            if ($user->tap && $user->tap->cluster_id) {
                return self::checkByClusterId($user->tap->cluster_id, $now);
            }
        }

        return [
            'is_open' => true,
            'status' => 'OPEN',
            'reason' => 'User tidak terhubung ke cluster tertentu.',
            'close_time' => '17:00',
        ];
    }
}
