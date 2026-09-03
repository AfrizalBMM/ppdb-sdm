<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AksesKeuanganPublic
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('akses_keuangan_public')) {
            logAktivitas(
                'Petugas Keuangan Public - Akses Ditolak',
                'Akses statistik keuangan ditolak karena belum verifikasi password petugas.'
            );

            return redirect()
                ->route('pendaftaran.list')
                ->with('error', 'Masukkan nama petugas dan password keuangan terlebih dahulu.');
        }

        return $next($request);
    }
}
