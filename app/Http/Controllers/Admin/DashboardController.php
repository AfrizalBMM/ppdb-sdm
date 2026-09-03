<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Pembayaran;
use App\Models\Registration;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 10;
        }

        // ================= TOTAL PENDAFTAR =================
        $totalPendaftar = Siswa::count();

        // ================= TOTAL PESERTA DIDIK =================
        $totalSiswa = Siswa::whereHas('registration', function ($q) {
            $q->where('status', Registration::STATUS_PESERTA_DIDIK);
        })->count();

        // ================= PENDAFTAR HARI INI =================
        $pendaftarHariIni = Siswa::whereDate('created_at', now())->count();

        // ================= TOTAL PEMASUKAN =================
        $totalPembayaran = Pembayaran::sum('nominal_bayar');

        // ================= PEMASUKAN HARI INI =================
        $pembayaranHariIni = Pembayaran::whereDate(
            'tanggal_bayar',
            now()
        )->sum('nominal_bayar');

        // ================= PENDAFTAR TERBARU =================
        $pendaftarTerbaru = Siswa::with([
                'registration.tahunAjaran'
            ])
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.dashboard', compact(
            'totalPendaftar',
            'totalSiswa',
            'pendaftarHariIni',
            'totalPembayaran',
            'pembayaranHariIni',
            'pendaftarTerbaru',
            'perPage'
        ));
    }
}