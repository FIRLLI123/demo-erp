<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Tampilkan Halaman Login Wahana POS
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        // Ambil daftar akun demo untuk tombol jalan pintas presentasi / testing
        $demoUsers = DB::table('users')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->leftJoin('taps', 'users.tap_id', '=', 'taps.id')
            ->select('users.*', 'roles.name as role_name', 'roles.slug as role_slug', 'taps.name as tap_name')
            ->where('users.is_active', true)
            ->whereIn('users.email', [
                'itdev@wahanapos.com',
                'admin.pasarminggu@wahanapos.com',
                'admin.blokm@wahanapos.com',
                'rian@wahanaputrayudha.co.id'
            ])
            ->get();

        return view('auth.login', compact('demoUsers'));
    }

    /**
     * Proses Login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $remember = $request->boolean('remember');

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withInput($request->only('email', 'remember'))->withErrors([
                'email' => 'Email atau kata sandi yang Anda masukkan tidak sesuai.',
            ]);
        }

        // Cek status keaktifan user (00 vs 99)
        if (!$user->is_active) {
            return back()->withInput($request->only('email', 'remember'))->withErrors([
                'email' => 'Akun pengguna ini berstatus NON-AKTIF (99). Akses ke Wahana POS ditutup.',
            ]);
        }

        Auth::login($user, $remember);
        $request->session()->regenerate();

        // Load TAP context
        $tap = $user->tap_id ? DB::table('taps')->where('id', $user->tap_id)->first() : null;
        $tapName = $tap ? $tap->name : 'SEMUA AREA (IT DEV & SUPER ADMIN)';

        session([
            'active_tap_id' => $user->tap_id,
            'active_tap_name' => $tapName,
            'is_all_areas' => is_null($user->tap_id),
        ]);

        // Catat Audit Trail
        if (\Illuminate\Support\Facades\Schema::hasTable('activity_logs')) {
            DB::table('activity_logs')->insert([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role_id ? (DB::table('roles')->where('id', $user->role_id)->value('name') ?? 'Staff') : 'Staff',
                'action' => 'LOGIN',
                'module' => 'AUTHENTICATION',
                'doc_number' => "SESSION-" . session()->getId(),
                'severity' => 'INFO',
                'description' => "Pengguna '{$user->name}' berhasil masuk ke Wahana POS [Scope: {$tapName}]",
                'ip_address' => $request->ip() ?? '127.0.0.1',
                'user_agent' => $request->userAgent() ?? 'System',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Switch Active Cluster (Global Scope Filter)
     * Mengatur cluster aktif di Session yang otomatis menyaring seluruh menu & data ERP.
     */
    public function switchCluster(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $clusterId = $request->input('cluster_id');

        if ($clusterId === 'all' || empty($clusterId)) {
            session()->forget(['active_cluster_id', 'active_cluster_name']);
            session([
                'active_cluster_id' => 'all',
                'active_cluster_name' => 'Semua Cluster',
            ]);
        } else {
            $accessible = \App\Services\TerritoryScope::getUserAccessibleClusters($user);
            $cluster = $accessible->firstWhere('id', (int) $clusterId);

            if (!$cluster) {
                return back()->with('error', 'Akses ditolak: Anda tidak memiliki hak akses untuk cluster tersebut.');
            }

            session([
                'active_cluster_id' => (int) $cluster->id,
                'active_cluster_name' => $cluster->name,
            ]);
        }

        \App\Services\TerritoryScope::flush();

        return back();
    }

    /**
     * Switch TAP (Khusus IT DEV & Super Admin yang memiliki hak akses Semua Area)
     */
    public function switchTap(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Cek apakah user berhak akses semua area (tap_id null atau role it_dev/super_admin)
        $roleSlug = DB::table('roles')->where('id', $user->role_id)->value('slug');
        $isOmni = is_null($user->tap_id) || in_array($roleSlug, ['it_dev', 'super_admin']);

        if (!$isOmni) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk berpindah area TAP lain.');
        }

        $targetTapId = $request->tap_id;

        if ($targetTapId === 'all' || empty($targetTapId)) {
            session([
                'active_tap_id' => null,
                'active_tap_name' => 'SEMUA AREA (KONSOLIDASI)',
                'is_all_areas' => true,
            ]);
            $msg = 'Area aktif berhasil dialihkan ke: SEMUA AREA (KONSOLIDASI)';
        } else {
            $tap = DB::table('taps')->where('id', $targetTapId)->first();
            if ($tap) {
                session([
                    'active_tap_id' => $tap->id,
                    'active_tap_name' => $tap->name,
                    'is_all_areas' => false,
                ]);
                $msg = "Area aktif berhasil dialihkan ke: {$tap->name}";
            } else {
                $msg = 'TAP tidak ditemukan.';
            }
        }

        return back()->with('success', $msg);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user && \Illuminate\Support\Facades\Schema::hasTable('activity_logs')) {
            DB::table('activity_logs')->insert([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role_id ? (DB::table('roles')->where('id', $user->role_id)->value('name') ?? 'Staff') : 'Staff',
                'action' => 'LOGOUT',
                'module' => 'AUTHENTICATION',
                'doc_number' => "SESSION-" . session()->getId(),
                'severity' => 'INFO',
                'description' => "Pengguna '{$user->name}' keluar dari sistem (Logout).",
                'ip_address' => $request->ip() ?? '127.0.0.1',
                'user_agent' => $request->userAgent() ?? 'System',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem Wahana POS.');
    }
}

