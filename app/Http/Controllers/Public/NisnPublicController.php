<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use Illuminate\Http\Request;

class NisnPublicController extends Controller
{
    /**
     * Show the NISN lookup form.
     */
    public function index()
    {
        return view('nisn.index');
    }

    /**
     * Search a student by name + date of birth (AJAX/fetch).
     * Returns JSON.
     */
    public function cari(Request $request)
    {
        $validated = $request->validate([
            'nama'      => 'required|string|min:2|max:255',
            'nama_ibu'  => 'required|string|min:2|max:255',
        ]);

        $nama     = trim($validated['nama']);
        $namaIbu  = trim($validated['nama_ibu']);

        $siswa = Siswa::whereRaw('LOWER(nama) = ?', [strtolower($nama)])
            ->whereHas('ibu', function ($q) use ($namaIbu) {
                $q->whereRaw('LOWER(nama) = ?', [strtolower($namaIbu)]);
            })
            ->first(['id', 'nama', 'tanggal_lahir', 'nisn']);

        if (!$siswa) {
            return response()->json([
                'found' => false,
                'message' => 'Data siswa tidak ditemukan. Pastikan nama lengkap dan tanggal lahir sudah benar.',
            ], 404);
        }

        return response()->json([
            'found'    => true,
            'id'       => $siswa->id,
            'nama'     => $siswa->nama,
            'has_nisn' => !empty($siswa->nisn),
            'nisn'     => $siswa->nisn,
        ]);
    }

    /**
     * Check if a NISN value is already used by another student (live-check via AJAX).
     */
    public function cekNisn(Request $request)
    {
        $nisn    = trim((string) $request->input('nisn', ''));
        $siswaId = (int) $request->input('siswa_id', 0);

        if ($nisn === '' || !preg_match('/^\d{1,10}$/', $nisn)) {
            return response()->json(['valid' => false, 'message' => 'NISN harus berupa angka maksimal 10 digit.']);
        }

        $exists = Siswa::where('nisn', $nisn)
            ->when($siswaId > 0, fn ($q) => $q->where('id', '!=', $siswaId))
            ->exists();

        if ($exists) {
            return response()->json(['valid' => false, 'message' => 'NISN sudah digunakan oleh siswa lain.']);
        }

        return response()->json(['valid' => true, 'message' => 'NISN tersedia.']);
    }

    /**
     * Save/update the NISN for a student.
     */
    public function simpan(Request $request)
    {
        $validated = $request->validate([
            'siswa_id' => 'required|integer|exists:siswa,id',
            'nisn'     => 'required|digits_between:1,10|unique:siswa,nisn,' . $request->input('siswa_id'),
        ], [
            'nisn.required'       => 'NISN wajib diisi.',
            'nisn.digits_between' => 'NISN maksimal 10 digit angka.',
            'nisn.unique'         => 'NISN sudah digunakan oleh siswa lain.',
        ]);

        $siswa = Siswa::findOrFail($validated['siswa_id']);

        // Guard: jangan timpa NISN yang sudah ada
        if (!empty($siswa->nisn)) {
            return response()->json([
                'success' => false,
                'message' => 'NISN untuk siswa ini sudah tercatat sebelumnya.',
            ], 422);
        }

        $siswa->nisn = $validated['nisn'];
        $siswa->save();

        return response()->json([
            'success' => true,
            'message' => 'NISN berhasil disimpan untuk ' . $siswa->nama . '.',
        ]);
    }
}
