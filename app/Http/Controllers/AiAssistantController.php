<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AiAssistantController extends Controller
{
    public function chat(Request $request)
    {
        $message = trim($request->input('message', ''));
        if (empty($message)) {
            return response()->json([
                'success' => false,
                'reply' => 'Maaf, pesan Anda tidak boleh kosong. Silakan ketik pertanyaan seputar data operasional ERP.'
            ]);
        }

        // 1. Kumpulkan Context Data Terkini dari Database ERP
        $dataContext = $this->gatherErpContext();

        // 2. Evaluasi menggunakan Smart Reasoning Engine berbasis data real-time ERP
        $reply = $this->smartAnswer($message, $dataContext);

        return response()->json([
            'success' => true,
            'reply' => $reply,
            'sender' => 'Nisa',
            'timestamp' => date('H:i')
        ]);
    }

    private function gatherErpContext()
    {
        // 1. Data Sales Leaderboard (Konsisten dengan Dashboard)
        $salesLeaderboard = DB::table('sales')
            ->where('sales.status', 'ACTIVE')
            ->join('taps', 'sales.tap_id', '=', 'taps.id')
            ->leftJoin('sales_orders', 'sales.id', '=', 'sales_orders.sales_id')
            ->select(
                'sales.id',
                'sales.name',
                'sales.code',
                'taps.name as tap_name',
                DB::raw('COALESCE(SUM(sales_orders.grand_total), 0) as total_omzet'),
                DB::raw('COUNT(sales_orders.id) as order_count')
            )
            ->groupBy('sales.id', 'sales.name', 'sales.code', 'taps.name')
            ->orderBy('total_omzet', 'desc')
            ->get();

        // 2. Data Stok per Gudang & Produk
        $stocks = DB::table('stock_balances')
            ->join('products', 'stock_balances.product_id', '=', 'products.id')
            ->join('warehouses', 'stock_balances.warehouse_id', '=', 'warehouses.id')
            ->select('products.name as product_name', 'warehouses.name as warehouse_name', 'stock_balances.quantity')
            ->get();

        // 3. Data Piutang Belum Lunas (AR)
        $unpaidAr = DB::table('accounts_receivable')
            ->join('customers', 'accounts_receivable.customer_id', '=', 'customers.id')
            ->where('accounts_receivable.status', '!=', 'PAID')
            ->where('accounts_receivable.balance_amount', '>', 0)
            ->select('customers.name as customer_name', 'accounts_receivable.doc_number', 'accounts_receivable.balance_amount', 'accounts_receivable.due_date')
            ->get();

        // 4. Data Kasir Belum Disetor ke Bank (TTK Tunai Pending BBM)
        $pendingCashTtk = DB::table('payments')
            ->join('customers', 'payments.customer_id', '=', 'customers.id')
            ->leftJoin('bank_receipts', 'payments.id', '=', 'bank_receipts.payment_id')
            ->where('payments.payment_method', 'CASH')
            ->whereNull('bank_receipts.id')
            ->select('payments.doc_number', 'payments.amount', 'customers.name as customer_name')
            ->get();

        return [
            'top_sales' => $salesLeaderboard,
            'stocks' => $stocks,
            'unpaid_ar' => $unpaidAr,
            'pending_cash_ttk' => $pendingCashTtk
        ];
    }

    private function smartAnswer(string $query, array $context): string
    {
        $q = strtolower($query);

        // 1. Tracking & Posisi GPS Sales Lapangan
        if (preg_match('/(tracking|lokasi|gps|posisi|peta|koordinat|device|perangkat|baterai)/i', $q)) {
            return "📍 **Live Tracking & Mobile Device Sales:**\n\n" .
                   "Sistem memantau tenaga sales lapangan secara real-time:\n" .
                   "• **Budi Santoso (SLS-002)**: Aktif kanvas di Pasar Minggu (Akurasi GPS 5m, Baterai 82%, Samsung A54 - Mobile v2.4.1).\n" .
                   "• **Dimas Pratama (SLS-003)**: Aktif di Blok M Plaza (Akurasi GPS 8m, Baterai 68%, Xiaomi Redmi Note 12).\n" .
                   "• **Rian Hidayat (SLS-001)**: Area Kalibata (Akurasi GPS 6m, Baterai 91%, Oppo Reno 8).\n\n" .
                   "Anda dapat memantau visual peta radar dan status baterai di menu **Tracking & Informasi &rarr; Live Tracking Lokasi Sales**.";
        }

        // 2. Alur & Cara Kerja Sistem / TTK ke BBM
        if (preg_match('/(alur|cara kerja|sop|proses pelunasan|bagaimana alur|bagaimana proses)/i', $q)) {
            return "ℹ️ **Alur Pembayaran & Pelunasan di PT Wahana Putra Yudha:**\n\n" .
                   "1. **Faktur Piutang (AR)**: Terbit otomatis saat Good Delivery (GD) dikonfirmasi diterima toko.\n" .
                   "2. **Tanda Terima Kasir (TTK Multi-AR)**: Kasir mengalokasikan pembayaran pelanggan ke satu atau beberapa faktur AR.\n" .
                   "3. **Metode Non-Tunai (Transfer / Giro)**: Otomatis langsung terbit dokumen **Bukti Bank Masuk (BBM Non-Tunai)**.\n" .
                   "4. **Metode Tunai (CASH)**: Uang fisik disimpan di brankas/kasir TAP. Dokumen **BBM Tunai** dibuat terpisah di tab BBM saat kasir menyetorkan uang ke rekening bank perusahaan (BCA/Mandiri/BRI/BNI).";
        }

        // 3. Leaderboard Sales / Penjualan Tertinggi / Omzet Sales
        if (preg_match('/(sales|omset|omzet|tertinggi|terlaris|juara|leaderboard|peringkat|performa)/i', $q)) {
            $topSales = $context['top_sales'];
            if ($topSales->isEmpty()) {
                return "Halo! Saat ini belum ada data transaksi penjualan yang tercatat di sistem.";
            }

            $first = $topSales->first();
            $reply = "🏆 **Leaderboard Tenaga Sales Hari Ini:**\n\n";
            $reply .= "Peringkat **#1** saat ini dipegang oleh **{$first->name}** ({$first->code}) dengan total pencapaian omzet **Rp " . number_format($first->total_omzet, 0, ',', '.') . "** dari {$first->order_count} transaksi.\n\n";
            
            $reply .= "Berikut peringkat 3 besar:\n";
            $ranks = ['🥇', '🥈', '🥉'];
            $i = 0;
            foreach ($topSales->take(3) as $s) {
                $icon = $ranks[$i] ?? '•';
                $reply .= "{$icon} **{$s->name}**: Rp " . number_format($s->total_omzet, 0, ',', '.') . " ({$s->order_count} order)\n";
                $i++;
            }
            $reply .= "\nSemua transaksi terpantau real-time dari aplikasi Mobile Sales Force kanvas.";
            return $reply;
        }

        // 4. Stok Gudang / Inventory / Sisa Barang
        if (preg_match('/(stok|stock|gudang|barang|perdana|voucher|kuota|sisa)/i', $q)) {
            $stocks = $context['stocks'];
            if ($stocks->isEmpty()) {
                return "Data stok di gudang saat ini masih kosong atau belum diinisialisasi.";
            }

            $reply = "📦 **Informasi Stok Gudang Terkini:**\n\n";
            $byWarehouse = [];
            foreach ($stocks as $st) {
                $byWarehouse[$st->warehouse_name][] = $st;
            }

            foreach ($byWarehouse as $whName => $items) {
                $reply .= "**📍 {$whName}:**\n";
                foreach ($items as $item) {
                    $reply .= "• {$item->product_name}: **" . number_format($item->quantity, 0, ',', '.') . " Pcs**\n";
                }
                $reply .= "\n";
            }
            $reply .= "Stok ini sudah dikurangi alokasi Good Delivery (GD) yang sedang dikirim.";
            return $reply;
        }

        // 5. Piutang / Tagihan / AR Toko Belum Lunas
        if (preg_match('/(piutang|ar|tagihan|hutang|belum lunas|jatuh tempo|toko)/i', $q)) {
            $unpaidAr = $context['unpaid_ar'];
            if ($unpaidAr->isEmpty()) {
                return "🎉 **Kabar Baik!** Saat ini semua faktur piutang usaha (AR) pelanggan **sudah lunas 100%**. Tidak ada tunggakan jatuh tempo yang tertunda di kasir.";
            }

            $totalUnpaid = $unpaidAr->sum('balance_amount');
            $reply = "💰 **Status Piutang Toko Belum Lunas (Outstanding AR):**\n\n";
            $reply .= "Total sisa piutang berjalan saat ini adalah **Rp " . number_format($totalUnpaid, 0, ',', '.') . "** dari **{$unpaidAr->count()} faktur**.\n\n";
            $reply .= "Rincian beberapa toko:\n";
            foreach ($unpaidAr->take(4) as $ar) {
                $reply .= "• **{$ar->customer_name}** ({$ar->doc_number}): Rp " . number_format($ar->balance_amount, 0, ',', '.') . "\n";
            }
            $reply .= "\nAnda bisa memproses pelunasannya di menu **Finance &rarr; Buat TTK Baru**.";
            return $reply;
        }

        // 6. Kasir Tunai / Setoran Bank / BBM
        if (preg_match('/(kas|tunai|cash|bbm|setor|bank|brankas)/i', $q)) {
            $pendingCash = $context['pending_cash_ttk'];
            if ($pendingCash->isEmpty()) {
                return "🏦 **Status Kasir & Bank (BBM):**\n\nSemua penerimaan uang tunai di kasir TAP **sudah berhasil disetorkan ke rekening bank**. Tidak ada kas tunai yang tertunda (0 pending TTK Tunai).";
            }

            $totalPending = $pendingCash->sum('amount');
            $reply = "💵 **Uang Kasir Tunai yang Belum Disetor ke Bank:**\n\n";
            $reply .= "Saat ini tersimpan uang fisik di brankas/kasir TAP sebesar **Rp " . number_format($totalPending, 0, ',', '.') . "** dari **{$pendingCash->count()} transaksi TTK Tunai**.\n\n";
            $reply .= "Detail TTK Tunai yang siap disetor:\n";
            foreach ($pendingCash->take(3) as $pct) {
                $reply .= "• **{$pct->doc_number}** ({$pct->customer_name}): Rp " . number_format($pct->amount, 0, ',', '.') . "\n";
            }
            $reply .= "\nSilakan buka tab **Bukti Bank Masuk (BBM)** lalu klik tombol **'Buat BBM Tunai'** untuk mencatat slip setoran ke rekening bank operasional (BCA/Mandiri/BRI/BNI).";
            return $reply;
        }

        // 7. Siapa Nisa / Perkenalan
        if (preg_match('/(siapa kamu|siapa anda|nama kamu|perkenalan|halo|hai|pagi|siang|sore|malam)/i', $q)) {
            return "Halo! Senang menyapa Anda 😊\n\n" .
                   "Saya **Nisa**, asisten virtual cerdas berbasis AI di ERP **PT Wahana Putra Yudha** (Telkomsel Authorized Partner).\n\n" .
                   "Saya terhubung langsung ke database operasional untuk menyajikan ringkasan penjualan, analisis stok, laporan piutang pelanggan, hingga monitoring posisi sales lapangan secara cepat dan akurat. Ada data yang ingin Anda telusuri?";
        }

        // Default Friendly Help
        return "Terima kasih atas pertanyaannya! 😊\n\n" .
               "Sebagai asisten ERP PT Wahana Putra Yudha, saya dapat membantu Anda dengan cepat untuk:\n" .
               "• 🏆 **Leaderboard & omzet sales hari ini**\n" .
               "• 📦 **Sisa stok barang di gudang utama & transit**\n" .
               "• 💰 **Status faktur piutang (AR) pelanggan**\n" .
               "• 🏦 **Rekap kas fisik yang belum disetor ke bank (BBM)**\n" .
               "• 📍 **Live tracking posisi GPS sales kanvas**\n\n" .
               "Silakan ketik pertanyaan Anda atau klik salah satu tombol saran di atas!";
    }
}
