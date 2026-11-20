<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\TerritoryScope;

class UiShowcaseController extends Controller
{
    public function index(Request $request)
    {
        $territory = TerritoryScope::for();

        // Data wilayah untuk demonstrasi filter territory
        $areas = DB::table('areas')->where('status', 'ACTIVE')->orderBy('name')->get();
        $clusters = DB::table('clusters')->where('status', 'ACTIVE')->orderBy('name')->get();
        $taps = DB::table('taps')->where('status', 'ACTIVE')->orderBy('name')->get();

        return view('ui-showcase.index', compact('territory', 'areas', 'clusters', 'taps'));
    }

    public function triggerAlert(Request $request)
    {
        $type = $request->input('type', 'success');

        if ($type === 'success') {
            return back()->with('success', 'Transaksi Purchase Order #PO-2026-0089 berhasil diterbitkan dan siap diverifikasi oleh Gudang!');
        }

        if ($type === 'error') {
            return back()->with('error', 'Transaksi Ditolak: Plafon kredit limit pelanggan telah terlampaui atau stok di gudang tidak mencukupi!');
        }

        if ($type === 'void') {
            return back()->with('success', 'Dokumen PO-2026-0044 berhasil di-VOID dan dibatalkan secara permanen dari sistem.');
        }

        if ($type === 'validation') {
            $request->validate([
                'sample_email' => 'required|email',
                'sample_nominal' => 'required|numeric|min:500000',
                'sample_notes' => 'required|min:10',
            ], [
                'sample_email.required' => 'Email PIC wajib diisi untuk verifikasi!',
                'sample_email.email' => 'Format email PIC tidak valid!',
                'sample_nominal.required' => 'Nominal transaksi wajib dicantumkan!',
                'sample_nominal.min' => 'Nominal minimal transaksi adalah Rp 500.000!',
                'sample_notes.required' => 'Catatan alasan transaksi wajib diisi!',
                'sample_notes.min' => 'Catatan minimal 10 karakter.',
            ]);

            return back()->with('success', 'Validasi form lolos tanpa kendala!');
        }

        return back()->with('success', 'Aksi berhasil dieksekusi.');
    }
}
