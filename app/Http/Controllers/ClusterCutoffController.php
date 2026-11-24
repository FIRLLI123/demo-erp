<?php

namespace App\Http\Controllers;

use App\Models\Cluster;
use App\Models\ClusterSoCutoff;
use App\Services\OrderCutoffService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ClusterCutoffController extends Controller
{
    /**
     * Halaman master pengaturan jam close SO per cluster.
     */
    public function index()
    {
        // Ambil semua cluster dengan relasi area dan cutoff
        $clusters = Cluster::with(['area', 'cutoff'])->orderBy('name', 'asc')->get();

        // Siapkan status cutoff saat ini untuk tiap cluster
        $now = Carbon::now();
        $cutoffStatuses = [];

        foreach ($clusters as $cluster) {
            $cutoff = $cluster->cutoff;
            if ($cutoff) {
                $status = $cutoff->checkStatus($now);
            } else {
                // Default jika belum pernah disetting
                $status = [
                    'is_open' => true,
                    'status' => 'OPEN',
                    'reason' => 'Default (Belum disetting)',
                    'close_time' => '17:00',
                ];
            }
            $cutoffStatuses[$cluster->id] = $status;
        }

        return view('master.cluster_cutoffs', compact('clusters', 'cutoffStatuses'));
    }

    /**
     * Simpan / Perbarui pengaturan jam cutoff per cluster.
     */
    public function update(Request $request, $clusterId)
    {
        $cluster = Cluster::findOrFail($clusterId);

        $request->validate([
            'close_time' => 'required',
            'notes' => 'nullable|string|max:255',
        ]);

        $isActive = $request->boolean('is_active');
        $closeTime = $request->input('close_time');
        if (strlen($closeTime) === 5) {
            $closeTime .= ':00';
        }

        ClusterSoCutoff::updateOrCreate(
            ['cluster_id' => $cluster->id],
            [
                'close_time' => $closeTime,
                'is_active' => $isActive,
                'notes' => $request->input('notes'),
            ]
        );

        return redirect()->route('master.cluster-cutoffs.index')
            ->with('success', "Pengaturan jam close untuk {$cluster->name} berhasil disimpan.");
    }

    /**
     * Toggle Force Close hari ini (Darurat Tutup Hari Ini).
     * Jika diaktifkan, otomatis reset besok harinya.
     */
    public function toggleForceClose(Request $request, $clusterId)
    {
        $cluster = Cluster::findOrFail($clusterId);
        $today = Carbon::today()->toDateString();

        $cutoff = ClusterSoCutoff::firstOrCreate(
            ['cluster_id' => $cluster->id],
            [
                'close_time' => '17:00:00',
                'is_active' => true,
            ]
        );

        // Jika saat ini force closed pada hari ini, maka buka kembali
        if ($cutoff->is_force_closed && $cutoff->force_closed_date && $cutoff->force_closed_date->format('Y-m-d') === $today) {
            $cutoff->update([
                'is_force_closed' => false,
                'force_closed_date' => null,
            ]);
            $msg = "Order untuk {$cluster->name} dibuka kembali hari ini.";
        } else {
            // Tutup paksa hari ini
            $cutoff->update([
                'is_force_closed' => true,
                'force_closed_date' => $today,
                'notes' => $request->input('notes', 'Pemesanan hari ini telah ditutup oleh Administrator.'),
            ]);
            $msg = "Order untuk {$cluster->name} berhasil ditutup untuk hari ini (akan otomatis buka besok).";
        }

        return redirect()->route('master.cluster-cutoffs.index')->with('success', $msg);
    }

    /**
     * API Endpoint untuk APK Sales / Frontend mengecek status cut-off cluster.
     * URL: GET /api/cluster-cutoffs/{clusterId}/status
     */
    public function getStatusApi($clusterId)
    {
        $cluster = Cluster::find($clusterId);
        if (!$cluster) {
            return response()->json([
                'success' => false,
                'message' => 'Cluster tidak ditemukan.',
            ], 404);
        }

        $now = Carbon::now();
        $status = OrderCutoffService::checkByClusterId((int)$clusterId, $now);

        return response()->json([
            'success' => true,
            'cluster_id' => $cluster->id,
            'cluster_code' => $cluster->code,
            'cluster_name' => $cluster->name,
            'server_time' => $now->format('H:i:s'),
            'server_date' => $now->format('Y-m-d'),
            'is_open' => $status['is_open'],
            'status' => $status['status'],
            'close_time' => $status['close_time'],
            'message' => $status['reason'],
        ]);
    }
}
