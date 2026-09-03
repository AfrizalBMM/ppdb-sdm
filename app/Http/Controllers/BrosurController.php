<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BrochureDownload;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class BrosurController extends Controller
{
    public function download(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:3|max:255',
            'nomor_wa' => 'required|numeric|digits_between:7,14',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.min' => 'Nama minimal 3 karakter.',
            'nomor_wa.required' => 'Nomor WhatsApp wajib diisi.',
            'nomor_wa.numeric' => 'Nomor WhatsApp harus berupa angka.',
            'nomor_wa.digits_between' => 'Nomor WhatsApp harus antara 7 sampai 14 digit.',
        ]);

        BrochureDownload::create([
            'name' => $request->name,
            'nomor_wa' => $request->nomor_wa,
        ]);

        logAktivitas('Download Brosur', 'Mendownload brosur oleh: ' . $request->name . ' (' . $request->nomor_wa . ')');

        $brochurePath = Setting::where('key', 'brochure')->value('value');
        
        if ($brochurePath && Storage::disk('public')->exists($brochurePath)) {
            return Storage::disk('public')->download($brochurePath, 'Brosur-SD-Muhammadiyah.pdf');
        }

        return back()->with('error', 'Maaf, file brosur belum tersedia saat ini.');
    }
}
