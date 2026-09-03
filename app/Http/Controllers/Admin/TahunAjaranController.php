<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class TahunAjaranController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 20);
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $data = TahunAjaran::orderByDesc('aktif') 
                            ->orderByDesc('id') 
                            ->paginate($perPage)
                            ->withQueryString();

        return view('admin.tahun-ajaran.index', [
            'data' => $data,
            'perPage' => $perPage,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:20|unique:tahun_ajaran,nama',
            'aktif' => 'boolean',
        ], [
            'nama.unique' => 'Nama tahun ajaran sudah ada.',
        ]);

        if ($request->aktif) {
            TahunAjaran::where('aktif',true)->update(['aktif'=>false]);
        }

        $tahun = TahunAjaran::create([
            'nama' => $request->nama,
            'aktif' => $request->aktif ?? false
        ]);

        logAktivitas(
            'Tahun Ajaran',
            'Menambahkan tahun ajaran #'.$tahun->id.' "'.$tahun->nama.'"' .
            ($tahun->aktif ? ' (aktif)' : '')
        );


        return back()->with('success', 'Tahun ajaran berhasil di tambahkan.');
    }

    public function aktifkan(TahunAjaran $tahunAjaran)
    {
        TahunAjaran::where('aktif',true)->update(['aktif'=>false]);
        $tahunAjaran->update(['aktif'=>true]);

        logAktivitas(
            'Tahun Ajaran',
            'Mengaktifkan tahun ajaran #'.$tahunAjaran->id.' "'.$tahunAjaran->nama.'"'
        );

        return back()->with('success', 'Tahun ajaran berhasil di ganti.');
    }

    public function destroy(TahunAjaran $tahunAjaran)
    {
        if ($tahunAjaran->aktif) {
            return back()->with('error', 'Tahun ajaran aktif tidak boleh dihapus.');
        }

        $nama = $tahunAjaran->nama;
        $id = $tahunAjaran->id;

        $tahunAjaran->delete();

        logAktivitas(
            'Tahun Ajaran',
            'Menghapus tahun ajaran #'.$id.' "'.$nama.'"'
        );

        return back()->with('success', 'Tahun ajaran berhasil dihapus.');
    }

}
