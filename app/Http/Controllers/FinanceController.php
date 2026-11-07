<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function index()
    {
        $receivables = DB::table('accounts_receivable')
            ->join('customers', 'accounts_receivable.customer_id', '=', 'customers.id')
            ->select('accounts_receivable.*', 'customers.name as customer_name')
            ->orderBy('accounts_receivable.created_at', 'desc')
            ->get();

        $payments = DB::table('payments')
            ->join('customers', 'payments.customer_id', '=', 'customers.id')
            ->select('payments.*', 'customers.name as customer_name')
            ->orderBy('payments.created_at', 'desc')
            ->get();

        $bankReceipts = DB::table('bank_receipts')
            ->orderBy('bank_receipts.created_at', 'desc')
            ->get();

        return view('finance.index', compact('receivables', 'payments', 'bankReceipts'));
    }

    public function pay(Request $request, $id)
    {
        $ar = DB::table('accounts_receivable')->where('id', $id)->first();
        if (!$ar) return redirect()->back();

        $payAmount = $ar->balance_amount;
        $nextFp = DB::table('payments')->count() + 1;
        $fpNum = 'FP/' . date('Ym') . '/' . str_pad($nextFp, 4, '0', STR_PAD_LEFT);

        // 1. Create Payment / TTK (DocType: FP)
        $fpId = DB::table('payments')->insertGetId([
            'doc_type' => 'FP',
            'doc_number' => $fpNum,
            'ref_doc_type' => 'AR',
            'ref_doc_number' => $ar->doc_number,
            'accounts_receivable_id' => $ar->id,
            'customer_id' => $ar->customer_id,
            'payment_date' => date('Y-m-d'),
            'payment_method' => 'TRANSFER',
            'amount' => $payAmount,
            'cashier_name' => 'Kasir Finance TAP',
            'notes' => 'Pelunasan faktur via Transfer Bank',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Create Bukti Bank Masuk (DocType: BBM)
        $nextBbm = DB::table('bank_receipts')->count() + 1;
        $bbmNum = 'BBM/' . date('Ym') . '/' . str_pad($nextBbm, 4, '0', STR_PAD_LEFT);

        DB::table('bank_receipts')->insert([
            'doc_type' => 'BBM',
            'doc_number' => $bbmNum,
            'ref_doc_type' => 'FP',
            'ref_doc_number' => $fpNum,
            'payment_id' => $fpId,
            'bank_name' => 'BCA Operasional TAP',
            'account_number' => '711-0988-234',
            'receipt_date' => date('Y-m-d'),
            'amount' => $payAmount,
            'status' => 'VERIFIED',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Update AR to PAID
        DB::table('accounts_receivable')->where('id', $id)->update([
            'paid_amount' => $ar->total_amount,
            'balance_amount' => 0,
            'status' => 'PAID',
            'updated_at' => now(),
        ]);

        // 4. Update Sales Order to COMPLETED
        DB::table('sales_orders')->where('id', $ar->sales_order_id)->update([
            'status' => 'COMPLETED',
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', "Pelunasan berhasil! Dokumen [{$fpNum}] dan Bukti Bank Masuk [{$bbmNum}] terbit. Status AR: Lunas.");
    }
}

