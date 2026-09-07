<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\CetakController;
use App\Http\Controllers\Admin\PembayaranController;
use App\Http\Controllers\Public\PendaftaranController;
use App\Http\Controllers\Public\PasswordPanitiaController;
use App\Http\Controllers\Public\PasswordPetugasKeuanganController;
use App\Http\Controllers\Public\StatistikKeuanganController;
use App\Http\Controllers\Public\NisnPublicController;
use App\Livewire\PendaftaranWizard;
use App\Models\Siswa;
use App\Http\Controllers\Public\PembayaranController as PublicPembayaranController;
use App\Http\Controllers\VoucherKlaimController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (TANPA LOGIN)
|--------------------------------------------------------------------------
*/

// landing page
Route::view('/', 'public.landing')->name('public.landing');
Route::post('/download-brochure', [\App\Http\Controllers\BrosurController::class, 'download'])->name('brosur.download');

// nisn public input page
Route::get('/nisn', [NisnPublicController::class, 'index'])->name('nisn.public.index');
Route::post('/nisn/cari', [NisnPublicController::class, 'cari'])->name('nisn.public.cari');
Route::get('/nisn/cek', [NisnPublicController::class, 'cekNisn'])->name('nisn.public.cek');
Route::post('/nisn/simpan', [NisnPublicController::class, 'simpan'])->name('nisn.public.simpan');

// program detail page
Route::get('/program/{landingProgram}', function (\App\Models\LandingProgram $landingProgram) {
    return view('public.program-detail', compact('landingProgram'));
})->name('public.program.detail');

// pendaftaran calon siswa (Livewire)
Route::get('/pendaftaran/fresh', function () {
    session(['pendaftaran_wizard_force_fresh' => true]);

    return redirect()->route('pendaftaran.public');
})->name('pendaftaran.public.fresh');

Route::get('/pendaftaran', PendaftaranWizard::class)
    ->name('pendaftaran.public');

// sukses pendaftaran + review data
Route::get('/pendaftaran/sukses/{siswa}', function (Siswa $siswa) {
    $siswa->load([
        'registration',
        'ibu',
        'ayah',
        'wali',
        'alamat',
        'dataPendukung.paudTk',
        'tagihan.biaya',
    ]);

    return view('pendaftaran.sukses', compact('siswa'));
})->name('pendaftaran.sukses');

Route::get('/pendaftaran/list', [PendaftaranController::class, 'list'])
    ->name('pendaftaran.list');

Route::get('/pendaftaran/{siswa}/biaya', [PendaftaranController::class, 'showBiaya'])
    ->name('pendaftaran.biaya');

Route::get('/pendaftaran/{id}', [PendaftaranController::class, 'show'])
    ->whereNumber('id')
    ->name('pendaftaran.detail');

Route::post('/pendaftaran/{siswa}/terima-peserta', [PendaftaranController::class, 'terimaPeserta'])
    ->name('pendaftaran.terima-peserta');

Route::post('/pendaftaran/{siswa}/kelola-tes', [PendaftaranController::class, 'kelolaTes'])
    ->name('pendaftaran.kelola-tes');

Route::middleware('akses_pembayaran')->group(function () {

Route::get('/pendaftaran/{id}/lihat-nik', [PendaftaranController::class, 'showNik'])
    ->whereNumber('id')
    ->name('pendaftaran.show-nik');

Route::get('/pendaftaran/{siswa}/edit', PendaftaranWizard::class)
    ->name('pendaftaran.edit');

    Route::post('/pembayaran/store',
        [PublicPembayaranController::class,'store'])
        ->name('pembayaran.public.store');

    Route::delete('/pembayaran/{id}',
        [PublicPembayaranController::class,'destroy'])
        ->name('pembayaran.public.destroy');

    Route::put('/pembayaran/{id}',
        [PublicPembayaranController::class,'update'])
        ->name('pembayaran.public.update');

    Route::get('/pembayaran/{id}/nota',
        [\App\Http\Controllers\Public\PembayaranController::class,'nota']
    )->name('pembayaran.public.nota');

    Route::post('/pembayaran/{id}/nota',
        [\App\Http\Controllers\Public\PembayaranController::class,'nota']
    )->name('pembayaran.public.nota.post');

    Route::get('/pendaftaran/{siswa}/biaya/nota-rincian',
        [\App\Http\Controllers\Public\PembayaranController::class, 'notaRincianBiaya']
    )->name('pendaftaran.biaya.nota');

    Route::post('/pendaftaran/{siswa}/biaya/nota-rincian',
        [\App\Http\Controllers\Public\PembayaranController::class, 'notaRincianBiaya']
    )->name('pendaftaran.biaya.nota.post');

    // Klaim / batalkan voucher pada rincian biaya (oleh panitia)
    Route::post('/pendaftaran/{siswa}/voucher/klaim', [VoucherKlaimController::class, 'klaim'])
        ->name('pendaftaran.voucher.klaim');

    Route::post('/pendaftaran/{siswa}/voucher/batal', [VoucherKlaimController::class, 'batal'])
        ->name('pendaftaran.voucher.batal');

});

// cetak formulir pendaftaran (public access setelah daftar)
Route::get('/cetak/formulir/{siswa}', [CetakController::class, 'formulir'])
    ->name('cetak.formulir');

Route::get('/cetak/formulir/{siswa}/preview', [CetakController::class, 'formulirPreview'])
    ->name('cetak.formulir.preview');
    
Route::post('/cetak/formulir', [CetakController::class, 'cetakFormulir'])
    ->name('cetak.formulir.post');

Route::post('/verifikasi-password-panitia',
    [PasswordPanitiaController::class,'verifikasi'])
    ->name('verifikasi.password.panitia');

Route::post('/verifikasi-password-petugas-keuangan',
    [PasswordPetugasKeuanganController::class,'verifikasi'])
    ->name('verifikasi.password.petugas.keuangan');

Route::middleware('akses_keuangan_public')->group(function () {
    Route::get('/pendaftaran/statistik-keuangan', [StatistikKeuanganController::class, 'index'])
        ->name('pendaftaran.statistik.keuangan');

    Route::get('/pendaftaran/statistik-keuangan/export/excel', [StatistikKeuanganController::class, 'exportExcel'])
        ->name('pendaftaran.statistik.keuangan.export.excel');

    Route::get('/pendaftaran/statistik-keuangan/export/pdf', [StatistikKeuanganController::class, 'exportPdf'])
        ->name('pendaftaran.statistik.keuangan.export.pdf');

    Route::post('/pendaftaran/statistik-keuangan/logout', [StatistikKeuanganController::class, 'logout'])
        ->name('pendaftaran.statistik.keuangan.logout');
});

/*
|--------------------------------------------------------------------------
| AUTH (LOGIN / LOGOUT)
|--------------------------------------------------------------------------
*/

Route::get('/login', [LoginController::class, 'form'])->name('login');
Route::post('/login', [LoginController::class, 'proses'])->name('login.proses');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
