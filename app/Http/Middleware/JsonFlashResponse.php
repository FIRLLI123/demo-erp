<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Untuk request AJAX (header Accept: application/json), ubah redirect()->with('success'|'error')
 * menjadi respons JSON {success, message} agar UI bisa menampilkan loading + pop-up respon
 * tanpa reload halaman. Request non-AJAX tidak terpengaruh.
 */
class JsonFlashResponse
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($request->expectsJson() && $response instanceof RedirectResponse) {
            $session = $request->session();
            $error = $session->get('error');
            $success = $session->get('success');
            $session->forget(['error', 'success']);

            $ok = $error === null;
            return response()->json([
                'success' => $ok,
                'message' => $ok ? ($success ?? 'Proses berhasil.') : $error,
            ], $ok ? 200 : 422);
        }

        return $response;
    }
}
