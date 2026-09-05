<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PasswordPanitia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PasswordPanitiaController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100|unique:password_panitia,nama',
            'password' => 'required|string|max:50',
        ]);

        $data = PasswordPanitia::create([
            'nama' => trim((string) $request->nama),
            'password' => Hash::make($request->password),
        ]);

        logAktivitas(
            'Password Panitia',
            'Menambahkan panitia baru: ' . $data->nama . ' (ID: ' . $data->id . ').'
        );

        return back()->with('success', 'Panitia berhasil ditambahkan.');
    }

    public function update(Request $request, PasswordPanitia $passwordPanitia)
    {
        $request->validate([
            'nama' => 'required|string|max:100|unique:password_panitia,nama,' . $passwordPanitia->id,
            'password' => 'nullable|string|max:50',
        ]);

        $oldNama = $passwordPanitia->nama;

        $payload = [
            'nama' => trim((string) $request->nama),
        ];

        if ($request->filled('password')) {
            $payload['password'] = Hash::make($request->password);
        }

        $passwordPanitia->update($payload);

        logAktivitas(
            'Password Panitia',
            'Memperbarui data panitia ID ' . $passwordPanitia->id
            . ' (nama: ' . $oldNama . ' -> ' . $passwordPanitia->nama
            . ', password diubah: ' . ($request->filled('password') ? 'ya' : 'tidak') . ').'
        );

        return back()->with('success', 'Data panitia berhasil diperbarui.');
    }

    public function destroy(PasswordPanitia $passwordPanitia)
    {
        $nama = $passwordPanitia->nama;
        $id = $passwordPanitia->id;

        $passwordPanitia->delete();

        logAktivitas(
            'Password Panitia',
            'Menghapus panitia: ' . $nama . ' (ID: ' . $id . ').'
        );

        return back()->with('success', 'Panitia berhasil dihapus.');
    }
}
