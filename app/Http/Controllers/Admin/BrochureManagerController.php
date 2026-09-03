<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrochureDownload;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;

class BrochureManagerController extends Controller
{
    public function index(Request $request)
    {
        $query = BrochureDownload::query();

        if ($request->filled('q')) {
            $search = trim($request->q);
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nomor_wa', 'like', "%{$search}%");
            });
        }

        $downloads = $query->latest()->paginate(10)->withQueryString();

        return view('admin.brochure.index', compact('downloads'));
    }

    public function destroy(BrochureDownload $brochure)
    {
        $name = $brochure->name;
        $brochure->delete();

        LogAktivitas::create([
            'user_id' => auth()->id(),
            'aktivitas' => 'Hapus Data Riwayat Brosur',
            'deskripsi' => 'Menghapus riwayat unduh brosur atas nama: ' . $name,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Data riwayat unduh brosur berhasil dihapus.');
    }
}
