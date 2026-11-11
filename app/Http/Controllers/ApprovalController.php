<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    /**
     * Tampilkan Pusat Persetujuan Terpadu (Approval Center).
     */
    public function index(Request $request)
    {
        // Pastikan ada data awal jika tabel masih kosong
        $this->ensureInitialData();

        $tab = $request->get('tab', 'pending'); // 'pending' | 'history' | 'all'
        $type = $request->get('type', 'ALL');   // 'ALL' | 'SALES_ORDER' | 'CREDIT_LIMIT' | 'PURCHASE_ORDER'
        $search = $request->get('q');

        // Query dasar
        $query = DB::table('approvals');

        if ($tab === 'pending') {
            $query->where('status', 'PENDING');
        } elseif ($tab === 'history') {
            $query->whereIn('status', ['APPROVED', 'REJECTED']);
        }

        if ($type !== 'ALL') {
            $query->where('approval_type', $type);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('doc_number', 'like', "%{$search}%")
                  ->orWhere('requestor_name', 'like', "%{$search}%")
                  ->orWhere('party_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $approvals = $query->orderByDesc('created_at')->get();

        // Metrik KPI
        $kpi = [
            'pending_count' => DB::table('approvals')->where('status', 'PENDING')->count(),
            'pending_amount' => DB::table('approvals')->where('status', 'PENDING')->sum('requested_amount'),
            'approved_count' => DB::table('approvals')->where('status', 'APPROVED')->count(),
            'approved_amount' => DB::table('approvals')->where('status', 'APPROVED')->sum('requested_amount'),
            'rejected_count' => DB::table('approvals')->where('status', 'REJECTED')->count(),
            'so_pending' => DB::table('approvals')->where('status', 'PENDING')->where('approval_type', 'SALES_ORDER')->count(),
            'credit_pending' => DB::table('approvals')->where('status', 'PENDING')->where('approval_type', 'CREDIT_LIMIT')->count(),
            'po_pending' => DB::table('approvals')->where('status', 'PENDING')->where('approval_type', 'PURCHASE_ORDER')->count(),
        ];

        return view('approvals.index', [
            'headerTitle' => 'Pusat Persetujuan Terpadu',
            'badge' => 'Otorisasi & Kontrol',
            'approvals' => $approvals,
            'kpi' => $kpi,
            'currentTab' => $tab,
            'currentType' => $type,
            'search' => $search,
        ]);
    }

    /**
     * Proses Persetujuan / Penolakan Pengajuan Dokumen.
     */
    public function process(Request $request)
    {
        $request->validate([
            'approval_id' => 'required|integer',
            'action' => 'required|in:APPROVE,REJECT',
            'notes' => 'nullable|string|max:500',
        ]);

        $approval = DB::table('approvals')->where('id', $request->approval_id)->first();
        if (!$approval) {
            return redirect()->back()->with('error', 'Dokumen pengajuan tidak ditemukan!');
        }

        if ($approval->status !== 'PENDING') {
            return redirect()->back()->with('error', 'Dokumen ini sudah diproses sebelumnya!');
        }

        $action = $request->action;
        $notes = $request->notes ?? ($action === 'APPROVE' ? 'Disetujui sesuai kebijakan limit otorisasi operasional.' : 'Ditolak.');
        $approverName = 'Admin Operasional (Finance Head)';

        DB::beginTransaction();
        try {
            // 1. Update status tabel approvals
            DB::table('approvals')->where('id', $approval->id)->update([
                'status' => $action === 'APPROVE' ? 'APPROVED' : 'REJECTED',
                'approver_name' => $approverName,
                'approver_notes' => $notes,
                'action_date' => now(),
                'updated_at' => now(),
            ]);

            // 2. Cascade update ke entitas terkait
            if ($approval->approval_type === 'SALES_ORDER' && $approval->reference_id) {
                if ($action === 'APPROVE') {
                    DB::table('sales_orders')->where('id', $approval->reference_id)->update([
                        'status' => 'APPROVED',
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('sales_orders')->where('id', $approval->reference_id)->update([
                        'status' => 'REJECTED',
                        'updated_at' => now(),
                    ]);

                    // Lepas reservasi serial ICCID
                    $so = DB::table('sales_orders')->where('id', $approval->reference_id)->first();
                    if ($so) {
                        DB::table('iccid_units')
                            ->where('last_doc_type', 'SO')
                            ->where('last_doc_number', $so->doc_number)
                            ->where('status', 'RESERVED')
                            ->update([
                                'status' => 'AVAILABLE',
                                'warehouse_id' => $so->warehouse_id,
                                'last_doc_type' => null,
                                'last_doc_number' => null,
                                'updated_at' => now(),
                            ]);
                    }
                }
            } elseif ($approval->approval_type === 'CREDIT_LIMIT' && $approval->reference_id) {
                if ($action === 'APPROVE') {
                    // Update credit_limit pelanggan
                    DB::table('customers')->where('id', $approval->reference_id)->update([
                        'credit_limit' => $approval->requested_amount,
                        'updated_at' => now(),
                    ]);
                }
            } elseif ($approval->approval_type === 'PURCHASE_ORDER' && $approval->reference_id) {
                if ($action === 'APPROVE') {
                    DB::table('purchase_orders')->where('id', $approval->reference_id)->update([
                        'status' => 'APPROVED',
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('purchase_orders')->where('id', $approval->reference_id)->update([
                        'status' => 'CANCELLED',
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            $statusText = $action === 'APPROVE' ? 'disetujui' : 'ditolak';
            return redirect()->back()->with('success', "Pengajuan [{$approval->doc_number}] ({$approval->party_name}) berhasil {$statusText}.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses persetujuan: ' . $e->getMessage());
        }
    }

    /**
     * Pastikan tersedianya data realistis untuk demo.
     */
    private function ensureInitialData(): void
    {
        if (DB::table('approvals')->count() > 0) {
            return;
        }

        // Cari SO yang statusnya PENDING_APPROVAL jika ada
        $pendingSo = DB::table('sales_orders')->where('status', 'PENDING_APPROVAL')->first();
        $soRefId = $pendingSo ? $pendingSo->id : 3;
        $soDocNum = $pendingSo ? $pendingSo->doc_number : 'SO/202611/0003';
        $soAmount = $pendingSo ? $pendingSo->grand_total : 4875000;

        $now = now();

        $records = [
            // 1. Pending: Sales Order tempo melebihi plafon
            [
                'approval_type' => 'SALES_ORDER',
                'doc_number' => $soDocNum,
                'reference_id' => $soRefId,
                'requestor_name' => 'Rian Hidayat (Sales Canvas)',
                'party_name' => 'Toko Berkah Cellindo',
                'requested_amount' => $soAmount,
                'description' => 'Order Kartu Perdana & Voucher Fisik Telkomsel dengan pembayaran tempo 14 hari. Melampaui sisa plafon kredit toko (Sisa plafon: Rp 2.500.000).',
                'status' => 'PENDING',
                'approver_name' => null,
                'approver_notes' => null,
                'action_date' => null,
                'created_at' => $now->copy()->subHours(2),
                'updated_at' => $now->copy()->subHours(2),
            ],
            // 2. Pending: Kenaikan Plafon Kredit Toko
            [
                'approval_type' => 'CREDIT_LIMIT',
                'doc_number' => 'REQ/CR/202611/0001',
                'reference_id' => 1, // Toko Berkah Cellindo
                'requestor_name' => 'Budi Santoso (Supervisor Sales)',
                'party_name' => 'Toko Berkah Cellindo',
                'requested_amount' => 20000000,
                'description' => 'Pengajuan kenaikan limit kredit toko dari Rp 5.000.000 menjadi Rp 20.000.000. Alasan: Toko membuka cabang kedua dan rekam jejak pembayaran tepat waktu selama 3 bulan berturut-turut.',
                'status' => 'PENDING',
                'approver_name' => null,
                'approver_notes' => null,
                'action_date' => null,
                'created_at' => $now->copy()->subHours(5),
                'updated_at' => $now->copy()->subHours(5),
            ],
            // 3. Pending: Pengadaan Purchase Order Besar ke Principal
            [
                'approval_type' => 'PURCHASE_ORDER',
                'doc_number' => 'PO/202611/0019',
                'reference_id' => null,
                'requestor_name' => 'Divisi Logistik & Purchasing',
                'party_name' => 'PT Telekomunikasi Selular (Principal)',
                'requested_amount' => 85000000,
                'description' => 'Pengadaan buffer stok Kartu Perdana 4G/5G Special Edition 10.000 pcs antisipasi peak season libur akhir tahun. Membutuhkan persetujuan otorisasi Direktur.',
                'status' => 'PENDING',
                'approver_name' => null,
                'approver_notes' => null,
                'action_date' => null,
                'created_at' => $now->copy()->subHours(8),
                'updated_at' => $now->copy()->subHours(8),
            ],
            // 4. Pending: Kenaikan Plafon Kredit Toko lain
            [
                'approval_type' => 'CREDIT_LIMIT',
                'doc_number' => 'REQ/CR/202611/0002',
                'reference_id' => 3, // Sentral Phone Plaza
                'requestor_name' => 'Dimas Pratama (Direct Sales)',
                'party_name' => 'Sentral Phone Plaza',
                'requested_amount' => 35000000,
                'description' => 'Pengajuan plafon kredit dari Rp 10.000.000 menjadi Rp 35.000.000 untuk pengadaan paket perdana kuota besar bulk order.',
                'status' => 'PENDING',
                'approver_name' => null,
                'approver_notes' => null,
                'action_date' => null,
                'created_at' => $now->copy()->subDay(),
                'updated_at' => $now->copy()->subDay(),
            ],
            // 5. Approved: Riwayat Sales Order disetujui
            [
                'approval_type' => 'SALES_ORDER',
                'doc_number' => 'SO/202611/0002',
                'reference_id' => 2,
                'requestor_name' => 'Budi Santoso (Supervisor Sales)',
                'party_name' => 'Mitra Reload Grosir',
                'requested_amount' => 6400000,
                'description' => 'Order kuota voucher fisik grosir tempo 7 hari.',
                'status' => 'APPROVED',
                'approver_name' => 'Admin Operasional (Finance Head)',
                'approver_notes' => 'Disetujui. Toko memiliki histori piutang lancar.',
                'action_date' => $now->copy()->subDays(2),
                'created_at' => $now->copy()->subDays(3),
                'updated_at' => $now->copy()->subDays(2),
            ],
            // 6. Rejected: Riwayat Pengajuan ditolak
            [
                'approval_type' => 'CREDIT_LIMIT',
                'doc_number' => 'REQ/CR/202611/0000',
                'reference_id' => 2,
                'requestor_name' => 'Rian Hidayat (Sales Canvas)',
                'party_name' => 'Mitra Reload Grosir',
                'requested_amount' => 50000000,
                'description' => 'Pengajuan plafon ekstrem menjadi Rp 50.000.000.',
                'status' => 'REJECTED',
                'approver_name' => 'Admin Operasional (Finance Head)',
                'approver_notes' => 'Ditolak. Nilai limit melampaui rasio perputaran piutang maksimum (maksimal Rp 30.000.000).',
                'action_date' => $now->copy()->subDays(4),
                'created_at' => $now->copy()->subDays(5),
                'updated_at' => $now->copy()->subDays(4),
            ],
        ];

        DB::table('approvals')->insert($records);
    }
}
