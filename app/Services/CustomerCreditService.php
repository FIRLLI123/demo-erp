<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class CustomerCreditService
{
    /**
     * Hitung ringkasan limit dan pemakaian kredit customer secara real-time.
     *
     * @param int $customerId
     * @return array
     */
    public static function getCreditSummary(int $customerId): array
    {
        $customer = DB::table('customers')->where('id', $customerId)->first();
        if (!$customer) {
            return [
                'exists' => false,
                'customer_id' => $customerId,
                'customer_name' => '-',
                'credit_limit' => 0,
                'outstanding_ar' => 0,
                'active_so' => 0,
                'used_limit' => 0,
                'remaining_limit' => 0,
                'is_eligible' => false,
                'reason' => 'Customer tidak ditemukan.',
            ];
        }

        $creditLimit = (float)($customer->credit_limit ?? 0);

        // 1. Hitung total piutang faktur yang belum lunas (Accounts Receivable UNPAID / PARTIAL)
        $outstandingAr = (float) DB::table('accounts_receivable')
            ->where('customer_id', $customerId)
            ->whereIn('status', ['UNPAID', 'PARTIAL'])
            ->sum('balance_amount');

        // 2. Hitung total nilai SO aktif yang sedang berjalan tapi BELUM diterbitkan faktur AR
        // SO yang belum diterbitkan faktur AR memiliki status PENDING_APPROVAL atau APPROVED
        // (SO yang sudah DELIVERED/COMPLETED sudah otomatis terbentuk AR, jadi tidak dihitung dua kali)
        $activeSo = (float) DB::table('sales_orders')
            ->where('customer_id', $customerId)
            ->whereIn('status', ['PENDING_APPROVAL', 'APPROVED'])
            ->sum('grand_total');

        // Total Limit yang Sedang Terpakai
        $usedLimit = $outstandingAr + $activeSo;

        // Sisa Limit Kredit
        $remainingLimit = max(0, $creditLimit - $usedLimit);

        // Aturan: Jika credit_limit = 0, tidak bisa order
        if ($creditLimit <= 0) {
            return [
                'exists' => true,
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'credit_limit' => 0,
                'outstanding_ar' => $outstandingAr,
                'active_so' => $activeSo,
                'used_limit' => $usedLimit,
                'remaining_limit' => 0,
                'is_eligible' => false,
                'reason' => "Customer {$customer->name} memiliki Limit Kredit Rp 0 (Tidak diizinkan order kredit).",
            ];
        }

        return [
            'exists' => true,
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'credit_limit' => $creditLimit,
            'outstanding_ar' => $outstandingAr,
            'active_so' => $activeSo,
            'used_limit' => $usedLimit,
            'remaining_limit' => $remainingLimit,
            'is_eligible' => $remainingLimit > 0,
            'reason' => $remainingLimit > 0 ? 'Limit kredit mencukupi.' : "Limit kredit Customer {$customer->name} telah habis.",
        ];
    }

    /**
     * Validasi apakah transaksi SO baru diperbolehkan berdasarkan sisa limit.
     *
     * @param int $customerId
     * @param float $soAmount
     * @return array ['allowed' => bool, 'message' => string, 'summary' => array]
     */
    public static function validateOrder(int $customerId, float $soAmount): array
    {
        $summary = self::getCreditSummary($customerId);

        if (!$summary['exists']) {
            return [
                'allowed' => false,
                'message' => 'Customer tidak ditemukan!',
                'summary' => $summary,
            ];
        }

        if ($summary['credit_limit'] <= 0) {
            return [
                'allowed' => false,
                'message' => "Customer [{$summary['customer_name']}] memiliki Limit Kredit Rp 0. Tidak dapat membuat Sales Order!",
                'summary' => $summary,
            ];
        }

        if ($soAmount > $summary['remaining_limit']) {
            $formattedSo = number_format($soAmount, 0, ',', '.');
            $formattedRemaining = number_format($summary['remaining_limit'], 0, ',', '.');
            $formattedLimit = number_format($summary['credit_limit'], 0, ',', '.');
            $formattedUsed = number_format($summary['used_limit'], 0, ',', '.');

            return [
                'allowed' => false,
                'message' => "Order ditolak: Total SO (Rp {$formattedSo}) melebihi Sisa Limit Customer (Rp {$formattedRemaining}). [Plafon: Rp {$formattedLimit}, Terpakai: Rp {$formattedUsed}]",
                'summary' => $summary,
            ];
        }

        return [
            'allowed' => true,
            'message' => 'Limit kredit valid dan mencukupi.',
            'summary' => $summary,
        ];
    }
}
