<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        return view('admin.settings.index', compact('settings'));
    }

    public function store(Request $request)
    {
        $data = $request->except('_token', 'logo', 'brochure');
        
        // Handle text settings
        foreach($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        // Handle file uploads
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('settings', 'public');
            Setting::updateOrCreate(['key' => 'logo'], ['value' => $logoPath]);
        }
        
        if ($request->hasFile('brochure')) {
            $brochurePath = $request->file('brochure')->store('settings', 'public');
            Setting::updateOrCreate(['key' => 'brochure'], ['value' => $brochurePath]);
        }

        logAktivitas('Pengaturan', 'Memperbarui pengaturan website');

        return back()->with('success', 'Pengaturan website berhasil disimpan!');
    }
}
