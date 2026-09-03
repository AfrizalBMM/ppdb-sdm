<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Illuminate\Http\Request;

class LaporanKeuanganController extends Controller
{
    public function index(Request $request)
    {
        $mulai   = $request->mulai;
        $selesai = $request->selesai;
        $perPage = (int) $request->input('per_page', 50);
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 50;
        }

        $query = Pembayaran::with([
            'tagihan.biaya',
            'tagihan.siswa.registration.tahunAjaran',
            'tagihan.voucher',
        ]);

        // ================= FILTER TANGGAL =================
        if ($mulai && $selesai) {
            $query->whereBetween('tanggal_bayar', [
                $mulai . ' 00:00:00',
                $selesai . ' 23:59:59'
            ]);
        }

        // ================= URUTKAN =================
        $query->orderByDesc('tanggal_bayar');

        $pembayaran = $query->paginate($perPage)->withQueryString();

        $total = $query->clone()->sum('nominal_bayar');

        return view('laporan.keuangan', compact(
            'pembayaran',
            'total',
            'mulai',
            'selesai',
            'perPage'
        ));
    }
}
