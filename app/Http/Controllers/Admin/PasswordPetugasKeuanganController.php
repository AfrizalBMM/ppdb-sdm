<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PasswordPetugasKeuangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PasswordPetugasKeuanganController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 20);
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $petugas = PasswordPetugasKeuangan::latest()
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.password_petugas_keuangan', compact('petugas', 'perPage'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100|unique:password_petugas_keuangan,nama',
            'password' => 'required|string|max:50',
        ]);

        $data = PasswordPetugasKeuangan::create([
            'nama' => trim((string) $request->nama),
            'password' => Hash::make($request->password),
        ]);

        logAktivitas(
            'Password Petugas Keuangan',
            'Menambahkan petugas keuangan baru: ' . $data->nama . ' (ID: ' . $data->id . ').'
        );

        return back()->with('success', 'Petugas keuangan berhasil ditambahkan.');
    }

    public function update(Request $request, PasswordPetugasKeuangan $passwordPetugasKeuangan)
    {
        $request->validate([
            'nama' => 'required|string|max:100|unique:password_petugas_keuangan,nama,' . $passwordPetugasKeuangan->id,
            'password' => 'nullable|string|max:50',
        ]);

        $oldNama = $passwordPetugasKeuangan->nama;

        $payload = [
            'nama' => trim((string) $request->nama),
        ];

        if ($request->filled('password')) {
            $payload['password'] = Hash::make($request->password);
        }

        $passwordPetugasKeuangan->update($payload);

        logAktivitas(
            'Password Petugas Keuangan',
            'Memperbarui data petugas keuangan ID ' . $passwordPetugasKeuangan->id
            . ' (nama: ' . $oldNama . ' -> ' . $passwordPetugasKeuangan->nama
            . ', password diubah: ' . ($request->filled('password') ? 'ya' : 'tidak') . ').'
        );

        return back()->with('success', 'Data petugas keuangan berhasil diperbarui.');
    }

    public function destroy(PasswordPetugasKeuangan $passwordPetugasKeuangan)
    {
        $nama = $passwordPetugasKeuangan->nama;
        $id = $passwordPetugasKeuangan->id;

        $passwordPetugasKeuangan->delete();

        logAktivitas(
            'Password Petugas Keuangan',
            'Menghapus petugas keuangan: ' . $nama . ' (ID: ' . $id . ').'
        );

        return back()->with('success', 'Petugas keuangan berhasil dihapus.');
    }
}
