<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Ambil harga produk efektif berdasarkan prioritas wilayah:
     * TAP -> Cluster -> Area -> Default Master Produk
     */
    public static function resolveProductPrice(int $productId, ?int $tapId = null, ?int $clusterId = null, ?int $areaId = null): array
    {
        $prod = DB::table('products')->where('id', $productId)->first();
        if (!$prod) {
            return [
                'cost_price' => 0.0,
                'selling_price' => 0.0,
                'special_price' => 0.0,
                'source' => 'NONE',
            ];
        }

        // Lengkapi parent wilayah dari TAP / Cluster jika belum diberikan
        if ($tapId && !$clusterId) {
            $tap = DB::table('taps')->where('id', $tapId)->first();
            if ($tap) $clusterId = (int) $tap->cluster_id;
        }
        if ($clusterId && !$areaId) {
            $cls = DB::table('clusters')->where('id', $clusterId)->first();
            if ($cls) $areaId = (int) $cls->area_id;
        }

        $override = null;
        $source = 'DEFAULT';

        if ($tapId) {
            $override = DB::table('product_prices')
                ->where('product_id', $productId)
                ->where('tap_id', $tapId)
                ->where('status', 'ACTIVE')
                ->first();
            if ($override) $source = 'TAP';
        }

        if (!$override && $clusterId) {
            $override = DB::table('product_prices')
                ->where('product_id', $productId)
                ->where('cluster_id', $clusterId)
                ->whereNull('tap_id')
                ->where('status', 'ACTIVE')
                ->first();
            if ($override) $source = 'CLUSTER';
        }

        if (!$override && $areaId) {
            $override = DB::table('product_prices')
                ->where('product_id', $productId)
                ->where('area_id', $areaId)
                ->whereNull('cluster_id')
                ->whereNull('tap_id')
                ->where('status', 'ACTIVE')
                ->first();
            if ($override) $source = 'AREA';
        }

        return [
            'cost_price' => (float) ($override ? $override->cost_price : $prod->cost_price),
            'selling_price' => (float) ($override ? $override->selling_price : $prod->selling_price),
            'special_price' => (float) ($override ? $override->special_price : ($prod->special_price ?? 0)),
            'source' => $source,
        ];
    }

    /**
     * Catat stok MASUK (IN) secara atomik:
     * 1. Lock & update stock_balances
     * 2. Catat buku besar ke stock_mutations (dengan balance_after)
     * 3. Jika ERP_ENABLE_ICCID=true dan ada daftar ICCID, catat ke iccid_units + iccid_histories
     *
     * @param string $docType Standar: OPENING, GR, SM_IN, RI, SR, ADJ_IN
     */
    public static function recordIn(
        int $warehouseId,
        int $productId,
        int $quantity,
        string $docType,
        string $docNumber,
        string $date,
        ?string $notes = null,
        array $iccids = [],
        ?float $unitCostPrice = null
    ): int {
        if ($quantity <= 0) {
            return 0;
        }

        $balance = DB::table('stock_balances')
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->lockForUpdate()
            ->first();

        if ($balance) {
            $newBalance = (int) $balance->quantity + $quantity;
            DB::table('stock_balances')->where('id', $balance->id)->update([
                'quantity' => $newBalance,
                'updated_at' => now(),
            ]);
        } else {
            $newBalance = $quantity;
            DB::table('stock_balances')->insert([
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'quantity' => $newBalance,
                'reserved_quantity' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Catat ke buku besar stock_mutations
        DB::table('stock_mutations')->insert([
            'date' => $date,
            'warehouse_id' => $warehouseId,
            'product_id' => $productId,
            'doc_type' => strtoupper($docType),
            'doc_number' => $docNumber,
            'mutation_type' => 'IN',
            'quantity' => $quantity,
            'balance_after' => $newBalance,
            'notes' => $notes,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Jika feature flag ICCID aktif & daftar ICCID dikirim, simpan ke iccid_units + iccid_histories
        if (config('erp.enable_iccid', false) && !empty($iccids)) {
            $prod = DB::table('products')->where('id', $productId)->first();
            $userId = Auth::id();
            $eventType = strtoupper($docType) === 'GR' ? 'GR_IN' : (strtoupper($docType) . '_IN');

            foreach ($iccids as $sn) {
                $sn = trim((string) $sn);
                if ($sn === '') continue;

                $existing = DB::table('iccid_units')->where('iccid_number', $sn)->first();
                if ($existing) {
                    $oldStatus = $existing->status;
                    $unitId = $existing->id;
                    DB::table('iccid_units')->where('id', $unitId)->update([
                        'warehouse_id' => $warehouseId,
                        'status' => 'AVAILABLE',
                        'last_doc_type' => strtoupper($docType),
                        'last_doc_number' => $docNumber,
                        'updated_at' => now(),
                    ]);
                } else {
                    $oldStatus = null;
                    $unitId = DB::table('iccid_units')->insertGetId([
                        'iccid_number' => $sn,
                        'product_id' => $productId,
                        'warehouse_id' => $warehouseId,
                        'status' => 'AVAILABLE',
                        'customer_id' => null,
                        'last_doc_type' => strtoupper($docType),
                        'last_doc_number' => $docNumber,
                        'activation_date' => $prod->activation_date ?? null,
                        'expired_date' => $prod->expired_date ?? null,
                        'cost_price' => $unitCostPrice ?? ($prod->cost_price ?? 0),
                        'is_consignment' => (bool) ($prod->is_consignment ?? false),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('iccid_histories')->insert([
                    'iccid_unit_id' => $unitId,
                    'iccid_number' => $sn,
                    'event_type' => $eventType,
                    'doc_type' => strtoupper($docType),
                    'doc_number' => $docNumber,
                    'from_warehouse_id' => null,
                    'to_warehouse_id' => $warehouseId,
                    'customer_id' => null,
                    'sales_id' => null,
                    'old_status' => $oldStatus,
                    'new_status' => 'AVAILABLE',
                    'notes' => $notes,
                    'created_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return $newBalance;
    }

    /**
     * Catat stok KELUAR (OUT) secara atomik:
     * 1. Lock & validasi kecukupan saldo di stock_balances
     * 2. Kurangi stock_balances & catat stock_mutations (dengan balance_after)
     *
     * @param string $docType Standar: GD, SM_OUT, RO, PR, ADJ_OUT
     */
    public static function recordOut(
        int $warehouseId,
        int $productId,
        int $quantity,
        string $docType,
        string $docNumber,
        string $date,
        ?string $notes = null,
        bool $allowNegative = false
    ): int {
        if ($quantity <= 0) {
            return 0;
        }

        $balance = DB::table('stock_balances')
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->lockForUpdate()
            ->first();

        $currentQty = $balance ? (int) $balance->quantity : 0;
        if (!$allowNegative && $currentQty < $quantity) {
            throw new \RuntimeException("Stok tidak mencukupi (tersedia: {$currentQty}, dibutuhkan: {$quantity}).");
        }

        $newBalance = $currentQty - $quantity;

        if ($balance) {
            DB::table('stock_balances')->where('id', $balance->id)->update([
                'quantity' => $newBalance,
                'updated_at' => now(),
            ]);
        } else {
            DB::table('stock_balances')->insert([
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'quantity' => $newBalance,
                'reserved_quantity' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('stock_mutations')->insert([
            'date' => $date,
            'warehouse_id' => $warehouseId,
            'product_id' => $productId,
            'doc_type' => strtoupper($docType),
            'doc_number' => $docNumber,
            'mutation_type' => 'OUT',
            'quantity' => $quantity,
            'balance_after' => $newBalance,
            'notes' => $notes,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $newBalance;
    }
}
