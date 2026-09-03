<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VoucherController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 20);
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $vouchers = Voucher::latest()->paginate($perPage)->withQueryString();
        return view('admin.voucher.index', compact('vouchers', 'perPage'));
    }

    public function store(Request $request)
    {
        $diskonDigits = preg_replace('/\D/', '', (string) $request->input('diskon_nominal'));
        $request->merge([
            'diskon_nominal' => $diskonDigits === '' ? null : (int) $diskonDigits,
        ]);

        $request->validate([
            'nama'            => 'required|string|max:150',
            'jenis_biaya'     => 'required|in:pendaftaran,daftar_ulang,udp',
            'diskon_nominal'  => 'required|integer|min:0',
            'maks_penggunaan' => 'required|integer|min:1',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $baseKode = strtoupper(Str::slug($request->nama)).'-'.$request->diskon_nominal;

        // Pastikan kode unik (tambahkan suffix -02, -03, dst. jika bentrok).
        $kode = $baseKode;
        $urut = 2;
        while (Voucher::where('kode', $kode)->exists()) {
            $kode = $baseKode.'-'.str_pad((string) $urut, 2, '0', STR_PAD_LEFT);
            $urut++;
        }

        $voucher = Voucher::create([
            'kode'            => $kode,
            'nama'            => $request->nama,
            'jenis_biaya'     => $request->jenis_biaya,
            'diskon_nominal'  => $request->diskon_nominal,
            'maks_penggunaan' => $request->maks_penggunaan,
            'tanggal_mulai'   => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'aktif'           => true,
        ]);

        logAktivitas(
            'Kelola Voucher',
            'Menambahkan voucher #'.$voucher->id.' '.$voucher->kode.
            ' ('.$voucher->nama.', jenis biaya: '.$voucher->jenis_biaya.')'
        );

        return back()->with('success','Voucher berhasil dibuat');
    }

    public function update(Request $request, Voucher $voucher)
    {
        $diskonDigits = preg_replace('/\D/', '', (string) $request->input('diskon_nominal'));
        $request->merge([
            'diskon_nominal' => $diskonDigits === '' ? null : (int) $diskonDigits,
        ]);

        $request->validate([
            'nama'            => 'required|string|max:150',
            'jenis_biaya'     => 'required|in:pendaftaran,daftar_ulang,udp',
            'diskon_nominal'  => 'required|integer|min:0',
            'maks_penggunaan' => 'required|integer|min:1',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        // Voucher yang sudah pernah diklaim tidak boleh diubah jenis biaya &
        // diskonnya (mengecil) agar klaim yang sudah berjalan tidak rusak.
        $sudahDipakai = (int) $voucher->digunakan > 0;

        if ($sudahDipakai) {
            if ($request->jenis_biaya !== $voucher->jenis_biaya) {
                return back()->with('error', 'Jenis biaya tidak dapat diubah karena voucher sudah pernah diklaim.');
            }

            if ((int) $request->diskon_nominal !== (int) $voucher->diskon_nominal) {
                return back()->with('error', 'Diskon nominal tidak dapat diubah karena voucher sudah pernah diklaim.');
            }
        }

        // Kuota baru tidak boleh lebih kecil dari jumlah yang sudah diklaim.
        if ($sudahDipakai && (int) $request->maks_penggunaan < (int) $voucher->digunakan) {
            return back()->with('error', 'Maksimal penggunaan tidak boleh lebih kecil dari jumlah klaim saat ini ('.$voucher->digunakan.').');
        }

        $dataLama = $voucher->kode.' ('.$voucher->nama.')';

        $voucher->update([
            'nama'            => $request->nama,
            'jenis_biaya'     => $request->jenis_biaya,
            'diskon_nominal'  => $request->diskon_nominal,
            'maks_penggunaan' => $request->maks_penggunaan,
            'tanggal_mulai'   => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
        ]);

        logAktivitas(
            'Kelola Voucher',
            'Mengubah voucher #'.$voucher->id.' '.$dataLama.' menjadi '.$voucher->nama
            .' (jenis biaya: '.$voucher->jenis_biaya
            . ', diskon Rp '.number_format($voucher->diskon_nominal, 0, ',', '.')
            . ', maks penggunaan: '.$voucher->maks_penggunaan.')'
        );

        return back()->with('success', 'Voucher "'.$voucher->nama.'" berhasil diperbarui.');
    }

    public function toggle(Voucher $voucher)
    {
        // Tidak boleh aktifkan voucher yang sudah habis kuota
        if (!$voucher->aktif && !$voucher->masihAdaKuota()) {
            return back()->with('error', 'Voucher '.$voucher->nama.' sudah mencapai batas penggunaan.');
        }

        // Toggle status
        $voucher->update([
            'aktif' => !$voucher->aktif
        ]);

        $statusText = $voucher->aktif ? 'diaktifkan' : 'dinonaktifkan';

        logAktivitas(
            'Kelola Voucher',
            ucfirst($statusText).' voucher #'.$voucher->id.' '.$voucher->kode
        );

        return back()->with(
            'success',
            'Voucher "'.$voucher->nama.'" berhasil '.$statusText.'.'
        );
    }

    public function destroy(Voucher $voucher)
    {
        // ❗ Jangan hapus jika sudah pernah dipakai
        if ($voucher->digunakan > 0) {
            return back()->with('error','Voucher sudah digunakan dan tidak dapat dihapus.');
        }

        $kode = $voucher->kode;
        $id   = $voucher->id;

        $voucher->delete();

        logAktivitas(
            'Kelola Voucher',
            'Menghapus voucher #'.$id.' '.$kode
        );

        return back()->with('success','Voucher dihapus');
    }

    public function destroyAll()
    {
        // ❗ Hanya hapus voucher yang belum pernah dipakai
        $count = Voucher::where('digunakan', 0)->count();

        Voucher::where('digunakan', 0)->delete();

        logAktivitas(
            'Kelola Voucher',
            "Menghapus semua voucher yang belum digunakan ($count data)"
        );

        return back()->with('success', 'Voucher yang belum digunakan berhasil dihapus');
    }
}
