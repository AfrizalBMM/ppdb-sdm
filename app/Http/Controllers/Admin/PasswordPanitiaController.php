<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PasswordPanitia;
use Illuminate\Support\Facades\Hash;
use App\Models\TahunAjaran; 

class PasswordPanitiaController extends Controller
{
    public function index()
    {
        $tahunAjaran = TahunAjaran::where('aktif',1)->first();

        $password = PasswordPanitia::where('tahun_ajaran_id', $tahunAjaran->id)->first();

        return view('admin.password_panitia', compact('password','tahunAjaran'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'password' => 'required|string|max:50'
        ]);

        $tahunAjaran = TahunAjaran::where('aktif',1)->first();

        PasswordPanitia::updateOrCreate(
            ['tahun_ajaran_id' => $tahunAjaran->id],
            ['password' => Hash::make($request->password)]
        );

        logAktivitas('Password Panitia', 'Memperbarui password panitia untuk tahun ajaran ' . $tahunAjaran->nama);

        return back()
            ->with('success','Password panitia berhasil disimpan')
            ->with('password_plain', $request->password);
    }
}