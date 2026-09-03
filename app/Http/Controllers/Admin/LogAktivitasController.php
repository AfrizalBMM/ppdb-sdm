<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LogAktivitas;

class LogAktivitasController extends Controller
{
    private function applyKategoriFilter($query, string $kategori): void
    {
        if ($kategori === 'pendaftaran') {
            $query->where(function ($q) {
                $q->where('aksi', 'like', '%Pendaftaran%')
                    ->orWhere('aksi', 'like', '%Form Edit%')
                    ->orWhere('aksi', 'like', '%Tampilkan NIK%');
            });
            return;
        }

        if ($kategori === 'pembayaran') {
            $query->where(function ($q) {
                $q->where('aksi', 'like', '%Pembayaran%')
                    ->orWhere('aksi', 'like', '%Cicilan%')
                    ->orWhere('aksi', 'like', '%Pembiayaan%')
                    ->orWhere('aksi', 'like', '%Nota%');
            });
            return;
        }

        if ($kategori === 'verifikasi') {
            $query->where('aksi', 'like', '%Verifikasi Password%');
            return;
        }

        if ($kategori === 'manajemen-log') {
            $query->where(function ($q) {
                $q->where('aksi', 'like', '%Kelola Log%')
                    ->orWhere('aksi', 'like', '%Monitoring Aktivitas%');
            });
            return;
        }

        if ($kategori === 'lainnya') {
            $query->where(function ($q) {
                $q->where('aksi', 'not like', '%Pendaftaran%')
                    ->where('aksi', 'not like', '%Form Edit%')
                    ->where('aksi', 'not like', '%Tampilkan NIK%')
                    ->where('aksi', 'not like', '%Pembayaran%')
                    ->where('aksi', 'not like', '%Cicilan%')
                    ->where('aksi', 'not like', '%Pembiayaan%')
                    ->where('aksi', 'not like', '%Nota%')
                    ->where('aksi', 'not like', '%Verifikasi Password%')
                    ->where('aksi', 'not like', '%Kelola Log%')
                    ->where('aksi', 'not like', '%Monitoring Aktivitas%');
            });
        }
    }

    public function index(Request $request)
    {
        $allowedRoles = ['superadmin', 'admin', 'keuangan', 'public'];
        $allowedKategori = ['pendaftaran', 'pembayaran', 'verifikasi', 'manajemen-log', 'lainnya'];
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 10;
        }
        $query = LogAktivitas::with('user');

        // ================= SEARCH =================
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function($q) use ($search) {
                $q->where('aksi', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%")
                  ->orWhereHas('user', function($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // ================= FILTER ROLE =================
        if ($request->filled('role') && in_array($request->role, $allowedRoles, true)) {
            $query->where('role', $request->role);
        }

        // ================= FILTER TANGGAL =================
        if ($request->filled('tanggal')) {
            $query->whereDate('created_at', $request->tanggal);
        }

        if ($request->filled('kategori') && in_array($request->kategori, $allowedKategori, true)) {
            $this->applyKategoriFilter($query, $request->kategori);
        }

        $logs = $query
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        $today = now()->toDateString();
        $stats = [
            'total' => LogAktivitas::count(),
            'today' => LogAktivitas::whereDate('created_at', $today)->count(),
            'public' => LogAktivitas::where('role', 'public')->count(),
            'staff' => LogAktivitas::whereIn('role', ['superadmin', 'admin', 'keuangan'])->count(),
        ];

        if ($request->ajax()) {
            return view('admin.log.logs-table', compact('logs'))->render();
        }

        return view('admin.log.log-aktivitas', compact('logs', 'stats', 'perPage'));
    }

    public function destroyAll()
    {
        // ❗ Hanya boleh admin tertentu (opsional)
        if (auth()->user()->role !== 'superadmin') {
            abort(403);
        }

        $count = LogAktivitas::count();

        LogAktivitas::truncate();

        logAktivitas(
            'Kelola Log',
            "Menghapus semua log aktivitas ($count data)"
        );

        return redirect()
            ->route('log.aktivitas')
            ->with('success', 'Semua log berhasil dihapus!');
    }
}