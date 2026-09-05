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

    public function updateBatasLahir(Request $request, TahunAjaran $tahunAjaran)
    {
        $validated = $request->validate([
            'batas_maksimal_lahir' => 'required|date',
        ], [
            'batas_maksimal_lahir.required' => 'Tanggal batas maksimal lahir wajib diisi.',
            'batas_maksimal_lahir.date' => 'Format tanggal tidak valid.',
        ]);

        $tanggalBaru = \Carbon\Carbon::parse($validated['batas_maksimal_lahir'])->format('Y-m-d');
        $tanggalLama = $tahunAjaran->batas_maksimal_lahir
            ? $tahunAjaran->batas_maksimal_lahir->format('Y-m-d')
            : null;

        $tahunAjaran->update([
            'batas_maksimal_lahir' => $tanggalBaru,
        ]);

        logAktivitas(
            'Tahun Ajaran',
            'Mengatur batas maksimal lahir tahun ajaran #'.$tahunAjaran->id.' "'.$tahunAjaran->nama.'"'.
            ($tanggalLama ? ' ('.$tanggalLama.' -> '.$tanggalBaru.')' : ' (sebelumnya belum diatur, sekarang '.$tanggalBaru.')')
        );

        return back()->with('success', 'Batas maksimal lahir untuk tahun ajaran '.$tahunAjaran->nama.' berhasil disimpan.');
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
