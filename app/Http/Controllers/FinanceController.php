<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function index()
    {
        // 1. Daftar Piutang Usaha (AR)
        $receivablesRaw = DB::table('accounts_receivable')
            ->join('customers', 'accounts_receivable.customer_id', '=', 'customers.id')
            ->leftJoin('sales_orders', 'accounts_receivable.sales_order_id', '=', 'sales_orders.id')
            ->leftJoin('sales', 'sales_orders.sales_id', '=', 'sales.id')
            ->select(
                'accounts_receivable.*',
                'customers.name as customer_name',
                'customers.code as customer_code',
                'customers.phone as customer_phone',
                'sales.name as sales_name',
                'sales_orders.doc_number as so_number',
                'sales_orders.channel as so_channel'
            )
            ->orderBy('accounts_receivable.id', 'desc')
            ->get();

        // 2. Daftar Tanda Terima Kasir (TTK) dengan alokasi Multi-AR
        $paymentsRaw = DB::table('payments')
            ->join('customers', 'payments.customer_id', '=', 'customers.id')
            ->leftJoin('bank_receipts', 'payments.id', '=', 'bank_receipts.payment_id')
            ->select(
                'payments.*',
                'customers.name as customer_name',
                'customers.code as customer_code',
                'bank_receipts.doc_number as bbm_number',
                'bank_receipts.receipt_type as bbm_type'
            )
            ->orderBy('payments.id', 'desc')
            ->get();

        // Ambil alokasi AR untuk setiap TTK
        $paymentIds = $paymentsRaw->pluck('id')->toArray();
        $allocationsByPayment = [];
        if (!empty($paymentIds)) {
            $allocations = DB::table('payment_allocations')
                ->join('accounts_receivable', 'payment_allocations.accounts_receivable_id', '=', 'accounts_receivable.id')
                ->leftJoin('sales_orders', 'accounts_receivable.sales_order_id', '=', 'sales_orders.id')
                ->leftJoin('sales', 'sales_orders.sales_id', '=', 'sales.id')
                ->whereIn('payment_allocations.payment_id', $paymentIds)
                ->select(
                    'payment_allocations.*',
                    'accounts_receivable.doc_number as ar_number',
                    'accounts_receivable.total_amount as invoice_total',
                    'accounts_receivable.status as ar_status',
                    'sales_orders.doc_number as so_number',
                    'sales_orders.channel as so_channel',
                    'sales.name as sales_name'
                )
                ->get();

            foreach ($allocations as $alloc) {
                $allocationsByPayment[$alloc->payment_id][] = $alloc;
            }
        }

        foreach ($paymentsRaw as $payment) {
            $payment->allocations = $allocationsByPayment[$payment->id] ?? [];
            $payment->ar_count = count($payment->allocations);
            // Kumpulkan nama sales penanggung jawab dari AR terkait
            $salesNames = collect($payment->allocations)->pluck('sales_name')->filter()->unique()->values()->all();
            $payment->sales_summary = !empty($salesNames) ? implode(', ', $salesNames) : '-';
        }

        // 3. Daftar Bukti Bank Masuk (BBM)
        $bankReceiptsRaw = DB::table('bank_receipts')
            ->leftJoin('payments', 'bank_receipts.payment_id', '=', 'payments.id')
            ->leftJoin('customers', 'payments.customer_id', '=', 'customers.id')
            ->select(
                'bank_receipts.*',
                'payments.doc_number as ttk_number',
                'payments.payment_method',
                'payments.cashier_name',
                'payments.notes as payment_notes',
                'customers.name as customer_name'
            )
            ->orderBy('bank_receipts.id', 'desc')
            ->get();

        foreach ($bankReceiptsRaw as $bbm) {
            $bbm->allocations = $allocationsByPayment[$bbm->payment_id] ?? [];
            $bbm->ar_count = count($bbm->allocations);
            $salesNames = collect($bbm->allocations)->pluck('sales_name')->filter()->unique()->values()->all();
            $bbm->sales_summary = !empty($salesNames) ? implode(', ', $salesNames) : '-';
        }

        // 4. Daftar Customer yang memiliki Outstanding AR (Belum Lunas)
        $unpaidReceivables = DB::table('accounts_receivable')
            ->join('customers', 'accounts_receivable.customer_id', '=', 'customers.id')
            ->leftJoin('sales_orders', 'accounts_receivable.sales_order_id', '=', 'sales_orders.id')
            ->leftJoin('sales', 'sales_orders.sales_id', '=', 'sales.id')
            ->where('accounts_receivable.status', '!=', 'PAID')
            ->where('accounts_receivable.balance_amount', '>', 0)
            ->select(
                'accounts_receivable.*',
                'customers.name as customer_name',
                'customers.code as customer_code',
                'sales_orders.doc_number as so_number',
                'sales_orders.channel as so_channel',
                'sales.name as sales_name'
            )
            ->orderBy('accounts_receivable.id', 'asc')
            ->get();

        $customerIdsWithUnpaid = $unpaidReceivables->pluck('customer_id')->unique();
        $customersWithUnpaidAr = DB::table('customers')
            ->whereIn('id', $customerIdsWithUnpaid)
            ->orderBy('name')
            ->get();

        return view('finance.index', [
            'receivables' => $receivablesRaw,
            'payments' => $paymentsRaw,
            'bankReceipts' => $bankReceiptsRaw,
            'unpaidReceivables' => $unpaidReceivables,
            'customersWithUnpaidAr' => $customersWithUnpaidAr
        ]);
    }

    public function storeTtk(Request $request)
    {
        $customerId = (int)$request->input('customer_id');
        $paymentDate = $request->input('payment_date', date('Y-m-d'));
        $paymentMethod = $request->input('payment_method', 'CASH');
        $cashierName = $request->input('cashier_name', 'Kasir Finance TAP');
        $bankName = $request->input('bank_name', 'BCA Operasional TAP');
        $accountNumber = $request->input('account_number', '711-0988-234');
        $notes = $request->input('notes', 'Pelunasan Faktur Piutang via TTK');

        $allocationsInput = $request->input('allocations', []);

        if (!$customerId) {
            return redirect()->back()->with('error', 'Silakan pilih Pelanggan / Toko terlebih dahulu!');
        }

        // Filter alokasi yang memiliki nominal bayar > 0
        $validAllocations = [];
        $totalPayAmount = 0;

        foreach ($allocationsInput as $arId => $row) {
            $payAmt = isset($row['amount']) ? (float)$row['amount'] : 0;
            if ($payAmt > 0) {
                $validAllocations[$arId] = $payAmt;
                $totalPayAmount += $payAmt;
            }
        }

        if (empty($validAllocations)) {
            return redirect()->back()->with('error', 'Pilih minimal 1 faktur AR dan masukkan nominal pembayaran yang valid!');
        }

        DB::beginTransaction();
        try {
            // Ambil data AR terpilih dengan lock for update
            $arRecords = DB::table('accounts_receivable')
                ->whereIn('id', array_keys($validAllocations))
                ->where('customer_id', $customerId)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Validasi saldo masing-masing AR
            foreach ($validAllocations as $arId => $payAmt) {
                if (!isset($arRecords[$arId])) {
                    throw new \Exception("Faktur AR ID #{$arId} tidak valid atau bukan milik pelanggan terpilih.");
                }

                $ar = $arRecords[$arId];
                if ($ar->status === 'PAID' || (float)$ar->balance_amount <= 0) {
                    throw new \Exception("Faktur [{$ar->doc_number}] sudah lunas sebelumnya.");
                }

                if ($payAmt > (float)$ar->balance_amount) {
                    throw new \Exception("Nominal bayar untuk [{$ar->doc_number}] sebesar Rp " . number_format($payAmt, 0, ',', '.') . " melebihi sisa piutang (Sisa: Rp " . number_format($ar->balance_amount, 0, ',', '.') . ").");
                }
            }

            // 1. Terbitkan Dokumen Tanda Terima Kasir (TTK)
            $nextTtk = DB::table('payments')->count() + 1;
            $ttkNum = 'TTK/' . date('Ym') . '/' . str_pad($nextTtk, 4, '0', STR_PAD_LEFT);

            $primaryArId = array_key_first($validAllocations);
            $primaryAr = $arRecords[$primaryArId];

            $paymentId = DB::table('payments')->insertGetId([
                'doc_type' => 'TTK',
                'doc_number' => $ttkNum,
                'ref_doc_type' => 'AR',
                'ref_doc_number' => count($validAllocations) === 1 ? $primaryAr->doc_number : 'MULTI-AR (' . count($validAllocations) . ' Faktur)',
                'accounts_receivable_id' => count($validAllocations) === 1 ? $primaryAr->id : null,
                'customer_id' => $customerId,
                'payment_date' => $paymentDate,
                'payment_method' => $paymentMethod,
                'amount' => $totalPayAmount,
                'cashier_name' => $cashierName,
                'notes' => $notes,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Terbitkan Dokumen Bukti Bank Masuk (BBM)
            $nextBbm = DB::table('bank_receipts')->count() + 1;
            $bbmNum = 'BBM/' . date('Ym') . '/' . str_pad($nextBbm, 4, '0', STR_PAD_LEFT);

            $receiptType = ($paymentMethod === 'CASH') ? 'TUNAI' : 'NON_TUNAI';
            $finalBankName = $bankName;
            $finalAccountNum = $accountNumber;

            if ($paymentMethod === 'CASH') {
                $finalBankName = 'KAS TUNAI TAP (BBM TUNAI)';
                $finalAccountNum = 'KAS-OPERASIONAL-01';
            } elseif ($paymentMethod === 'DEPOSIT') {
                $finalBankName = 'SALDO TITIPAN PELANGGAN';
                $finalAccountNum = 'DEPOSIT-SALDO';
            } elseif ($paymentMethod === 'RETURN_DEDUCTION') {
                $finalBankName = 'POTONGAN NOTA RETUR';
                $finalAccountNum = 'DEDUCTION-RETUR';
            }

            DB::table('bank_receipts')->insert([
                'doc_type' => 'BBM',
                'doc_number' => $bbmNum,
                'receipt_type' => $receiptType,
                'ref_doc_type' => 'TTK',
                'ref_doc_number' => $ttkNum,
                'payment_id' => $paymentId,
                'bank_name' => $finalBankName,
                'account_number' => $finalAccountNum,
                'receipt_date' => $paymentDate,
                'amount' => $totalPayAmount,
                'status' => 'VERIFIED',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. Simpan Alokasi Pembayaran Multi-AR dan Update Saldo AR
            $fullyPaidCount = 0;
            $partialPaidCount = 0;

            foreach ($validAllocations as $arId => $payAmt) {
                $ar = $arRecords[$arId];
                $balanceBefore = (float)$ar->balance_amount;
                $newPaid = (float)$ar->paid_amount + $payAmt;
                $newBalance = max(0, $balanceBefore - $payAmt);
                $newStatus = $newBalance <= 0 ? 'PAID' : 'PARTIAL';

                if ($newStatus === 'PAID') {
                    $fullyPaidCount++;
                } else {
                    $partialPaidCount++;
                }

                // Catat detail alokasi
                DB::table('payment_allocations')->insert([
                    'payment_id' => $paymentId,
                    'accounts_receivable_id' => $ar->id,
                    'amount' => $payAmt,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $newBalance,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Update saldo AR
                DB::table('accounts_receivable')->where('id', $ar->id)->update([
                    'paid_amount' => $newPaid,
                    'balance_amount' => $newBalance,
                    'status' => $newStatus,
                    'updated_at' => now(),
                ]);

                // Jika AR sudah lunas dan terhubung ke SO, periksa apakah SO bisa di-complete
                if ($newStatus === 'PAID' && $ar->sales_order_id) {
                    $hasUnpaidSibling = DB::table('accounts_receivable')
                        ->where('sales_order_id', $ar->sales_order_id)
                        ->where('status', '!=', 'PAID')
                        ->exists();

                    if (!$hasUnpaidSibling) {
                        DB::table('sales_orders')->where('id', $ar->sales_order_id)->update([
                            'status' => 'COMPLETED',
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            DB::commit();

            $summaryMsg = "Tanda Terima Kasir [{$ttkNum}] dan Bukti Bank Masuk [{$bbmNum} - {$receiptType}] senilai Rp " . number_format($totalPayAmount, 0, ',', '.') . " berhasil diterbitkan! (" . count($validAllocations) . " Faktur AR diproses: {$fullyPaidCount} Lunas, {$partialPaidCount} Partial).";
            return redirect()->route('finance.index')->with('success', $summaryMsg);

        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }

    public function printKwitansi($id)
    {
        $payment = DB::table('payments')
            ->join('customers', 'payments.customer_id', '=', 'customers.id')
            ->where('payments.id', $id)
            ->select(
                'payments.*',
                'customers.name as customer_name',
                'customers.code as customer_code',
                'customers.phone as customer_phone',
                'customers.address as customer_address'
            )
            ->first();

        if (!$payment) abort(404);

        $bbm = DB::table('bank_receipts')->where('payment_id', $payment->id)->first();

        $allocations = DB::table('payment_allocations')
            ->join('accounts_receivable', 'payment_allocations.accounts_receivable_id', '=', 'accounts_receivable.id')
            ->leftJoin('sales_orders', 'accounts_receivable.sales_order_id', '=', 'sales_orders.id')
            ->leftJoin('sales', 'sales_orders.sales_id', '=', 'sales.id')
            ->where('payment_allocations.payment_id', $payment->id)
            ->select(
                'payment_allocations.*',
                'accounts_receivable.doc_number as ar_number',
                'accounts_receivable.due_date as ar_due_date',
                'accounts_receivable.total_amount as invoice_total',
                'accounts_receivable.status as ar_status',
                'sales_orders.doc_number as so_number',
                'sales_orders.channel as so_channel',
                'sales.name as sales_name'
            )
            ->get();

        return view('finance.print-kwitansi', compact('payment', 'bbm', 'allocations'));
    }
}
