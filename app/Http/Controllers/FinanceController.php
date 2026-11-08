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
                'sales_orders.doc_number as so_number'
            )
            ->orderBy('accounts_receivable.id', 'desc')
            ->get();

        // 2. Daftar Tanda Terima Kasir (TTK)
        $paymentsRaw = DB::table('payments')
            ->join('customers', 'payments.customer_id', '=', 'customers.id')
            ->leftJoin('accounts_receivable', 'payments.accounts_receivable_id', '=', 'accounts_receivable.id')
            ->select(
                'payments.*',
                'customers.name as customer_name',
                'customers.code as customer_code',
                'accounts_receivable.doc_number as ar_number',
                'accounts_receivable.total_amount as invoice_total'
            )
            ->orderBy('payments.id', 'desc')
            ->get();

        // 3. Daftar Bukti Bank Masuk (BBM)
        $bankReceiptsRaw = DB::table('bank_receipts')
            ->leftJoin('payments', 'bank_receipts.payment_id', '=', 'payments.id')
            ->leftJoin('customers', 'payments.customer_id', '=', 'customers.id')
            ->select(
                'bank_receipts.*',
                'payments.doc_number as ttk_number',
                'payments.payment_method',
                'customers.name as customer_name'
            )
            ->orderBy('bank_receipts.id', 'desc')
            ->get();

        // 4. Daftar AR yang masih memiliki sisa piutang (Belum Lunas) untuk form bayar TTK
        $unpaidReceivables = DB::table('accounts_receivable')
            ->join('customers', 'accounts_receivable.customer_id', '=', 'customers.id')
            ->where('accounts_receivable.status', '!=', 'PAID')
            ->where('accounts_receivable.balance_amount', '>', 0)
            ->select(
                'accounts_receivable.*',
                'customers.name as customer_name',
                'customers.code as customer_code'
            )
            ->orderBy('accounts_receivable.id', 'desc')
            ->get();

        return view('finance.index', [
            'receivables' => $receivablesRaw,
            'payments' => $paymentsRaw,
            'bankReceipts' => $bankReceiptsRaw,
            'unpaidReceivables' => $unpaidReceivables
        ]);
    }

    public function storeTtk(Request $request)
    {
        $arId = (int)$request->input('accounts_receivable_id');
        $paymentDate = $request->input('payment_date', date('Y-m-d'));
        $paymentMethod = $request->input('payment_method', 'TRANSFER');
        $amount = (float)$request->input('amount');
        $cashierName = $request->input('cashier_name', 'Kasir Finance TAP');
        $bankName = $request->input('bank_name', 'BCA Operasional TAP');
        $accountNumber = $request->input('account_number', '711-0988-234');
        $notes = $request->input('notes', 'Pembayaran Piutang melalui TTK');

        if (!$arId) {
            return redirect()->back()->with('error', 'Pilih Faktur Piutang (AR) yang akan dibayarkan!');
        }

        $ar = DB::table('accounts_receivable')->where('id', $arId)->first();
        if (!$ar) {
            return redirect()->back()->with('error', 'Faktur Piutang (AR) tidak ditemukan!');
        }

        if ($ar->status === 'PAID' || $ar->balance_amount <= 0) {
            return redirect()->back()->with('error', 'Faktur Piutang ini sudah lunas sebelumnya!');
        }

        if ($amount <= 0) {
            return redirect()->back()->with('error', 'Masukkan nominal pembayaran yang valid!');
        }

        if ($amount > (float)$ar->balance_amount) {
            return redirect()->back()->with('error', 'Nominal pembayaran tidak boleh melebihi sisa piutang (Maksimal: Rp ' . number_format($ar->balance_amount, 0, ',', '.') . ')!');
        }

        DB::beginTransaction();
        try {
            // 1. Terbitkan Dokumen Tanda Terima Kasir (TTK)
            $nextTtk = DB::table('payments')->count() + 1;
            $ttkNum = 'TTK/' . date('Ym') . '/' . str_pad($nextTtk, 4, '0', STR_PAD_LEFT);

            $paymentId = DB::table('payments')->insertGetId([
                'doc_type' => 'TTK',
                'doc_number' => $ttkNum,
                'ref_doc_type' => 'AR',
                'ref_doc_number' => $ar->doc_number,
                'accounts_receivable_id' => $ar->id,
                'customer_id' => $ar->customer_id,
                'payment_date' => $paymentDate,
                'payment_method' => $paymentMethod,
                'amount' => $amount,
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
                'amount' => $amount,
                'status' => 'VERIFIED',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. Update Status & Saldo Piutang (AR)
            $newPaid = (float)$ar->paid_amount + $amount;
            $newBalance = max(0, (float)$ar->balance_amount - $amount);
            $newStatus = $newBalance <= 0 ? 'PAID' : 'PARTIAL';

            DB::table('accounts_receivable')->where('id', $ar->id)->update([
                'paid_amount' => $newPaid,
                'balance_amount' => $newBalance,
                'status' => $newStatus,
                'updated_at' => now(),
            ]);

            // 4. Jika AR sudah lunas, update Sales Order menjadi COMPLETED
            if ($newStatus === 'PAID' && $ar->sales_order_id) {
                DB::table('sales_orders')->where('id', $ar->sales_order_id)->update([
                    'status' => 'COMPLETED',
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            $statusText = $newStatus === 'PAID' ? 'LUNAS SEPENUHNYA' : 'LUNAS SEBAGIAN (PARTIAL)';
            return redirect()->route('finance.index')->with('success', "Tanda Terima Kasir [{$ttkNum}] dan Bukti Bank Masuk [{$bbmNum}] senilai Rp " . number_format($amount, 0, ',', '.') . " berhasil diterbitkan! Status AR: {$statusText}.");

        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }

    public function printKwitansi($id)
    {
        $payment = DB::table('payments')
            ->join('customers', 'payments.customer_id', '=', 'customers.id')
            ->leftJoin('accounts_receivable', 'payments.accounts_receivable_id', '=', 'accounts_receivable.id')
            ->leftJoin('sales_orders', 'accounts_receivable.sales_order_id', '=', 'sales_orders.id')
            ->where('payments.id', $id)
            ->select(
                'payments.*',
                'customers.name as customer_name',
                'customers.code as customer_code',
                'customers.phone as customer_phone',
                'customers.address as customer_address',
                'accounts_receivable.doc_number as ar_number',
                'accounts_receivable.total_amount as invoice_total',
                'accounts_receivable.balance_amount as invoice_balance',
                'sales_orders.doc_number as so_number'
            )
            ->first();

        if (!$payment) abort(404);

        $bbm = DB::table('bank_receipts')->where('payment_id', $payment->id)->first();

        return view('finance.print-kwitansi', compact('payment', 'bbm'));
    }
}
