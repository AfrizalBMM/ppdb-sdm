<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PasswordPanitia;
use App\Models\PasswordPetugasKeuangan;
use Illuminate\Http\Request;

class PasswordController extends Controller
{
    public function index(Request $request)
    {
        $perPagePanitia = $this->resolvePerPage($request->input('per_page_panitia'));
        $perPagePetugas = $this->resolvePerPage($request->input('per_page_petugas'));

        $panitia = PasswordPanitia::latest()
            ->paginate($perPagePanitia, ['*'], 'panitia_page')
            ->withQueryString();

        $petugas = PasswordPetugasKeuangan::latest()
            ->paginate($perPagePetugas, ['*'], 'petugas_page')
            ->withQueryString();

        $activeTab = in_array($request->input('tab'), ['panitia', 'petugas'], true)
            ? $request->input('tab')
            : 'panitia';

        return view('admin.password.index', [
            'panitia' => $panitia,
            'petugas' => $petugas,
            'perPagePanitia' => $perPagePanitia,
            'perPagePetugas' => $perPagePetugas,
            'activeTab' => $activeTab,
        ]);
    }

    private function resolvePerPage(mixed $value): int
    {
        $value = (int) $value;
        if (!in_array($value, [10, 20, 50, 100], true)) {
            return 20;
        }
        return $value;
    }
}
