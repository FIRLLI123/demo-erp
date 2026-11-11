<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditLogController extends Controller
{
    /**
     * Tampilkan Audit Trail & Log Aktivitas Sistem.
     */
    public function index(Request $request)
    {
        // Pastikan ada data awal jika tabel masih kosong
        $this->ensureInitialLogs();

        $module = $request->get('module', 'ALL');       // 'ALL' | 'SALES' | 'INVENTORY' | 'FINANCE' | 'PROCUREMENT' | 'MASTER' | 'SECURITY'
        $severity = $request->get('severity', 'ALL');   // 'ALL' | 'INFO' | 'WARNING' | 'CRITICAL'
        $search = $request->get('q');
        $dateFilter = $request->get('date', 'ALL');     // 'ALL' | 'TODAY' | 'WEEK'

        $query = DB::table('activity_logs');

        if ($module !== 'ALL') {
            $query->where('module', $module);
        }

        if ($severity !== 'ALL') {
            $query->where('severity', $severity);
        }

        if ($dateFilter === 'TODAY') {
            $query->whereDate('created_at', now()->toDateString());
        } elseif ($dateFilter === 'WEEK') {
            $query->where('created_at', '>=', now()->subDays(7));
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('doc_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%");
            });
        }

        $logs = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        // Metrik KPI Ringkas
        $kpi = [
            'total_today' => DB::table('activity_logs')->whereDate('created_at', now()->toDateString())->count(),
            'critical_count' => DB::table('activity_logs')->where('severity', 'CRITICAL')->count(),
            'financial_stock' => DB::table('activity_logs')->whereIn('module', ['FINANCE', 'INVENTORY', 'SALES'])->count(),
            'active_users' => DB::table('activity_logs')->distinct('user_name')->count('user_name'),
        ];

        return view('reports.audit', [
            'headerTitle' => 'Audit Trail & Log Aktivitas',
            'badge' => 'Keamanan & Kepatuhan',
            'logs' => $logs,
            'kpi' => $kpi,
            'currentModule' => $module,
            'currentSeverity' => $severity,
            'currentDate' => $dateFilter,
            'search' => $search,
        ]);
    }

    /**
     * Pastikan log awal tersedia untuk demonstrasi kepada klien.
     */
    private function ensureInitialLogs(): void
    {
        if (DB::table('activity_logs')->count() > 0) {
            return;
        }

        $now = now();

        $logs = [
            [
                'user_id' => 1,
                'user_name' => 'Admin Operasional',
                'user_role' => 'Finance & Commercial Head',
                'module' => 'FINANCE',
                'action' => 'APPROVE_CREDIT_LIMIT',
                'severity' => 'CRITICAL',
                'doc_number' => 'REQ/CR/202611/0001',
                'description' => 'Menyetujui kenaikan plafon kredit Toko Berkah Cellindo dari Rp 5.000.000 menjadi Rp 20.000.000.',
                'ip_address' => '192.168.1.10 (LAN Kantor Pusat)',
                'user_agent' => 'Chrome 122.0 / Windows 11 Desktop',
                'old_values' => json_encode([
                    'toko' => 'Toko Berkah Cellindo',
                    'credit_limit_lama' => 'Rp 5.000.000',
                    'status_plafon' => 'NORMAL',
                ]),
                'new_values' => json_encode([
                    'toko' => 'Toko Berkah Cellindo',
                    'credit_limit_baru' => 'Rp 20.000.000',
                    'otorisator' => 'Admin Operasional (Finance Head)',
                    'alasan' => 'Track record pembayaran tepat waktu 3 bulan & pembukaan cabang baru',
                ]),
                'created_at' => $now->copy()->subMinutes(12),
                'updated_at' => $now->copy()->subMinutes(12),
            ],
            [
                'user_id' => 2,
                'user_name' => 'Rian Hidayat',
                'user_role' => 'Sales Force Canvas',
                'module' => 'SALES',
                'action' => 'CREATE_SALES_ORDER',
                'severity' => 'INFO',
                'doc_number' => 'SO/202611/0009',
                'description' => 'Menerbitkan Sales Order tunai senilai Rp 25.000 di Toko Berkah Cellindo melalui aplikasi mobile lapangan.',
                'ip_address' => '182.253.11.45 (Telkomsel 4G)',
                'user_agent' => 'Mobile App Android v2.4 (Xiaomi POCO X5)',
                'old_values' => null,
                'new_values' => json_encode([
                    'customer' => 'Toko Berkah Cellindo',
                    'total_amount' => 'Rp 25.000',
                    'payment_method' => 'CASH',
                    'gps_lat_long' => '-6.175392, 106.632145',
                ]),
                'created_at' => $now->copy()->subMinutes(45),
                'updated_at' => $now->copy()->subMinutes(45),
            ],
            [
                'user_id' => 1,
                'user_name' => 'Admin Operasional',
                'user_role' => 'Finance & Commercial Head',
                'module' => 'FINANCE',
                'action' => 'APPROVE_SALES_ORDER',
                'severity' => 'CRITICAL',
                'doc_number' => 'SO/202611/0002',
                'description' => 'Otorisasi transaksi Sales Order grosir tempo 14 hari senilai Rp 6.400.000 untuk Mitra Reload Grosir.',
                'ip_address' => '192.168.1.10 (LAN Kantor Pusat)',
                'user_agent' => 'Chrome 122.0 / Windows 11 Desktop',
                'old_values' => json_encode(['status' => 'PENDING_APPROVAL']),
                'new_values' => json_encode(['status' => 'APPROVED', 'tenor' => '14 Hari', 'limit_check' => 'PASSED']),
                'created_at' => $now->copy()->subHours(2),
                'updated_at' => $now->copy()->subHours(2),
            ],
            [
                'user_id' => 4,
                'user_name' => 'Hendro Prasetyo',
                'user_role' => 'Warehouse Supervisor',
                'module' => 'PROCUREMENT',
                'action' => 'PROCESS_GOODS_RECEIPT',
                'severity' => 'INFO',
                'doc_number' => 'GR/202611/0001',
                'description' => 'Penerimaan fisik 5.000 pcs Serial ICCID Perdana Telkomsel dari PT Telekomunikasi Selular ke Gudang Utama TAP Tangerang.',
                'ip_address' => '192.168.1.25 (PC Gudang)',
                'user_agent' => 'Chrome 121.0 / Windows 10',
                'old_values' => json_encode(['status_po' => 'ORDERED']),
                'new_values' => json_encode([
                    'status_po' => 'RECEIVED',
                    'total_iccid_scanned' => 5000,
                    'range_serial' => '8962012400000000001 s/d 8962012400000005000',
                ]),
                'created_at' => $now->copy()->subHours(3),
                'updated_at' => $now->copy()->subHours(3),
            ],
            [
                'user_id' => 4,
                'user_name' => 'Hendro Prasetyo',
                'user_role' => 'Warehouse Supervisor',
                'module' => 'INVENTORY',
                'action' => 'STOCK_MOVEMENT',
                'severity' => 'INFO',
                'doc_number' => 'SM/202611/0003',
                'description' => 'Transfer 1.000 pcs Voucher Fisik 3GB dari Gudang Utama ke Gudang Mobil Van Sales (Rian Hidayat).',
                'ip_address' => '192.168.1.25 (PC Gudang)',
                'user_agent' => 'Chrome 121.0 / Windows 10',
                'old_values' => json_encode(['gudang_asal_stok' => 8500]),
                'new_values' => json_encode(['transfer_qty' => 1000, 'gudang_tujuan' => 'Van Rian Hidayat']),
                'created_at' => $now->copy()->subHours(4),
                'updated_at' => $now->copy()->subHours(4),
            ],
            [
                'user_id' => 1,
                'user_name' => 'Admin Operasional',
                'user_role' => 'Finance & Commercial Head',
                'module' => 'FINANCE',
                'action' => 'REJECT_CREDIT_LIMIT',
                'severity' => 'WARNING',
                'doc_number' => 'REQ/CR/202611/0000',
                'description' => 'Menolak permohonan kenaikan limit kredit toko Mitra Reload Grosir (Permintaan: Rp 50.000.000) karena melampaui limit rasio risiko piutang.',
                'ip_address' => '192.168.1.10 (LAN Kantor Pusat)',
                'user_agent' => 'Chrome 122.0 / Windows 11 Desktop',
                'old_values' => json_encode(['status' => 'PENDING', 'diminta' => 'Rp 50.000.000']),
                'new_values' => json_encode(['status' => 'REJECTED', 'alasan' => 'Rasio perputaran piutang maksimum toko adalah Rp 30.000.000']),
                'created_at' => $now->copy()->subHours(6),
                'updated_at' => $now->copy()->subHours(6),
            ],
            [
                'user_id' => 3,
                'user_name' => 'Budi Santoso',
                'user_role' => 'Supervisor Sales Force',
                'module' => 'SALES',
                'action' => 'PRINT_NOTA',
                'severity' => 'INFO',
                'doc_number' => 'SO/202611/0001',
                'description' => 'Mencetak ulang rangkap nota penjualan resmi untuk verifikasi tanda terima toko.',
                'ip_address' => '192.168.1.18 (Laptop Supervisor)',
                'user_agent' => 'Edge 122.0 / Windows 11',
                'old_values' => null,
                'new_values' => json_encode(['cetak_ke' => 2, 'alasan' => 'Arsip Faktur Pajak Outlet']),
                'created_at' => $now->copy()->subHours(7),
                'updated_at' => $now->copy()->subHours(7),
            ],
            [
                'user_id' => 5,
                'user_name' => 'Siti Aminah',
                'user_role' => 'Kasir & Finance AR',
                'module' => 'FINANCE',
                'action' => 'STORE_BBM_TUNAI',
                'severity' => 'INFO',
                'doc_number' => 'BBM/202611/0001',
                'description' => 'Menerbitkan Bukti Bank Masuk (BBM) pelunasan piutang tunai dari Toko Sentral Phone Plaza senilai Rp 2.500.000.',
                'ip_address' => '192.168.1.12 (PC Kasir)',
                'user_agent' => 'Chrome 122.0 / Windows 10',
                'old_values' => json_encode(['sisa_piutang_lama' => 'Rp 4.500.000']),
                'new_values' => json_encode(['jumlah_setor' => 'Rp 2.500.000', 'sisa_piutang_baru' => 'Rp 2.000.000', 'metode' => 'KAS TUNAI']),
                'created_at' => $now->copy()->subHours(8),
                'updated_at' => $now->copy()->subHours(8),
            ],
            [
                'user_id' => 6,
                'user_name' => 'Dimas Pratama',
                'user_role' => 'Direct Sales Executive',
                'module' => 'SECURITY',
                'action' => 'USER_LOGIN',
                'severity' => 'INFO',
                'doc_number' => null,
                'description' => 'Autentikasi login berhasil ke aplikasi mobile sales canvas.',
                'ip_address' => '182.253.14.88 (Telkomsel Selular)',
                'user_agent' => 'Mobile App Android v2.4 (Samsung Galaxy A54)',
                'old_values' => null,
                'new_values' => json_encode(['status' => 'SUCCESS', 'device_id' => 'IMEI-8631240599182']),
                'created_at' => $now->copy()->subHours(9),
                'updated_at' => $now->copy()->subHours(9),
            ],
            [
                'user_id' => null,
                'user_name' => 'Tamu / Anonim',
                'user_role' => 'Unauthenticated',
                'module' => 'SECURITY',
                'action' => 'LOGIN_FAILED',
                'severity' => 'WARNING',
                'doc_number' => null,
                'description' => 'Gagal autentikasi 2x berturut-turut pada akun "kasir_serpong" (Kata sandi tidak sesuai).',
                'ip_address' => '192.168.1.155 (Subnet Wi-Fi Kantor)',
                'user_agent' => 'Firefox 123.0 / Linux',
                'old_values' => null,
                'new_values' => json_encode(['username_target' => 'kasir_serpong', 'failure_reason' => 'Invalid credentials']),
                'created_at' => $now->copy()->subHours(10),
                'updated_at' => $now->copy()->subHours(10),
            ],
            [
                'user_id' => 1,
                'user_name' => 'Admin Operasional',
                'user_role' => 'Master Data Controller',
                'module' => 'MASTER',
                'action' => 'UPDATE_CUSTOMER',
                'severity' => 'INFO',
                'doc_number' => 'CST-001',
                'description' => 'Memperbarui data PIC kontak & koordinat GPS Toko Berkah Cellindo.',
                'ip_address' => '192.168.1.10 (LAN Kantor Pusat)',
                'user_agent' => 'Chrome 122.0 / Windows 11 Desktop',
                'old_values' => json_encode(['phone' => '081298765430', 'pic' => 'Ahmad']),
                'new_values' => json_encode(['phone' => '081298765432', 'pic' => 'Haji Ahmad Fauzi']),
                'created_at' => $now->copy()->subDay(),
                'updated_at' => $now->copy()->subDay(),
            ],
            [
                'user_id' => 1,
                'user_name' => 'Admin Operasional',
                'user_role' => 'Finance & Commercial Head',
                'module' => 'SECURITY',
                'action' => 'EXPORT_EXCEL',
                'severity' => 'INFO',
                'doc_number' => 'EXP/XLS/202611/001',
                'description' => 'Mengunduh rekapitulasi data omset, inventory, dan piutang periode berjalan ke format Excel (.xlsx).',
                'ip_address' => '192.168.1.10 (LAN Kantor Pusat)',
                'user_agent' => 'Chrome 122.0 / Windows 11 Desktop',
                'old_values' => null,
                'new_values' => json_encode(['export_type' => 'FULL_MONTHLY_REPORT', 'total_rows' => 450]),
                'created_at' => $now->copy()->subDays(2),
                'updated_at' => $now->copy()->subDays(2),
            ],
        ];

        DB::table('activity_logs')->insert($logs);
    }
}
