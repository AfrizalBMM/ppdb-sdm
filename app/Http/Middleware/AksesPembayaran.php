<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AksesPembayaran
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('akses_pembayaran')) {
            $isAsyncRequest = $request->expectsJson()
                || $request->ajax()
                || $request->header('X-Livewire');

            try {
                \Illuminate\Support\Facades\Log::warning('AksesPembayaran denied', [
                    'path' => $request->path(),
                    'method' => $request->method(),
                    'is_livewire' => (bool) $request->header('X-Livewire'),
                    'is_ajax' => $request->ajax(),
                    'expects_json' => $request->expectsJson(),
                    'has_session' => $request->hasSession(),
                    'session_id' => $request->hasSession() ? $request->session()->getId() : null,
                    'referer' => $request->headers->get('referer'),
                ]);
            } catch (\Throwable $logError) {
                // ignore logging failures
            }

            // Untuk AJAX / Livewire request, return JSON error
            if ($isAsyncRequest) {
                return response()->json([
                    'message' => 'Akses ditolak. Silakan verifikasi password panitia terlebih dahulu.',
                    'status' => 'unauthorized'
                ], 403);
            }

            // Untuk regular request, redirect ke halaman yang diminta
            return redirect()
                ->back()
                ->with('error', 'Masukkan password panitia terlebih dahulu');

        }

        return $next($request);
    }
}
