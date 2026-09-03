<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TagihanSiswa;
use App\Models\Pembayaran;
use App\Models\Siswa;
use App\Services\StatusPpdbService;
use Barryvdh\DomPDF\Facade\Pdf;

class PembayaranController extends Controller
{
    public function __construct(private readonly StatusPpdbService $statusPpdbService)
    {
    }

    private function actorLabel(?string $namaPetugas): string
    {
        $nama = trim((string) $namaPetugas);
        return $nama !== '' ? $nama : 'Panitia Public';
    }

    public function store(Request $request)
    {
        $request->validate([
            'tagihan_siswa_id' => 'required|exists:tagihan_siswa,id',
            'tanggal_bayar' => 'required|date',
            'nominal_bayar' => 'required|numeric|min:1',
            'admin_penerima' => 'required|string|max:100',
            'metode' => 'nullable|in:cash,transfer',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $aktor = $this->actorLabel($request->admin_penerima);

        logAktivitas(
            $aktor . ' - Submit Modal Pembayaran',
            'Mengirim form pembayaran dengan penerima ' . $request->admin_penerima
            . ' (Tagihan ID: ' . $request->tagihan_siswa_id
            . ', nominal Rp ' . number_format($request->nominal_bayar, 0, ',', '.') . ').'
        );

        $tagihan = TagihanSiswa::findOrFail($request->tagihan_siswa_id);

        if ($request->nominal_bayar > $tagihan->sisa) {
            logAktivitas(
                $aktor . ' - Gagal Simpan Pembayaran',
                'Gagal menyimpan pembayaran untuk siswa ID ' . $tagihan->siswa_id
                . ' (Tagihan ID: ' . $tagihan->id
                . ', nominal bayar Rp ' . number_format($request->nominal_bayar, 0, ',', '.')
                . ', sisa tagihan Rp ' . number_format($tagihan->sisa, 0, ',', '.') . ').'
            );
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Nominal melebihi sisa tagihan'
                ], 400);
            }
            
            return back()->with('error','Nominal melebihi sisa tagihan');
        }

        $pembayaran = Pembayaran::create([
            'tagihan_siswa_id' => $tagihan->id,
            'tanggal_bayar' => $request->tanggal_bayar,
            'nominal_bayar' => $request->nominal_bayar,
            'metode' => $request->input('metode', 'cash'),
            'keterangan' => $request->keterangan,
            'admin_penerima' => $request->admin_penerima,
        ]);

        // Recalculate from payments to keep computed and stored fields consistent.
        $tagihan->unsetRelation('pembayaran');
        $tagihan->refreshStatus();
        $this->statusPpdbService->syncBySiswa($tagihan->siswa);

        logAktivitas(
            $aktor . ' - Simpan Pembayaran',
            'Berhasil menyimpan pembayaran siswa ID ' . $tagihan->siswa_id
            . ' (Tagihan ID: ' . $tagihan->id
            . ', Pembayaran ID: ' . $pembayaran->id
            . ', nominal Rp ' . number_format($request->nominal_bayar, 0, ',', '.')
            . ', penerima ' . $request->admin_penerima
            . ', metode ' . ($request->metode ?: '-') . ').'
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil disimpan',
                'redirect' => route('pendaftaran.biaya', $tagihan->siswa_id)
            ]);
        }

        return redirect()
        ->route('pendaftaran.biaya', $tagihan->siswa_id)
        ->with('success','Pembayaran berhasil disimpan');
    }

    public function destroy($id)
    {
        $request = request();
        $request->validate([
            'admin_penghapus' => 'required|string|max:100',
        ]);

        $aktor = $this->actorLabel($request->admin_penghapus);

        logAktivitas(
            $aktor . ' - Submit Modal Hapus Cicilan',
            'Mengirim form hapus cicilan untuk pembayaran ID ' . $id
            . ' dengan nama petugas/penerima: ' . $request->admin_penghapus . '.'
        );

        $pembayaran = Pembayaran::findOrFail($id);
        $tagihan = $pembayaran->tagihan;

        // Guard: jangan biarkan biaya lain jadi belum lunas selama voucher diklaim.
        $totalTerbayarSetelah = (int) $tagihan->total_dibayar - (int) $pembayaran->nominal_bayar;
        try {
            app(\App\Services\VoucherClaimService::class)
                ->assertPerubahanPembayaranAman($tagihan, $totalTerbayarSetelah);
        } catch (\Illuminate\Validation\ValidationException $e) {
            logAktivitas(
                $aktor . ' - Gagal Hapus Cicilan',
                'Gagal hapus pembayaran ID ' . $pembayaran->id
                . ' karena membuat biaya lain belum lunas saat voucher masih diklaim (siswa ID ' . $tagihan->siswa_id . ').'
            );

            throw $e;
        }

        // Log aktivitas sebelum hapus
        logAktivitas(
            $aktor . ' - Hapus Riwayat Cicilan',
            'Menghapus riwayat pembayaran oleh petugas ' . $request->admin_penghapus
            . ' untuk siswa ID ' . $tagihan->siswa_id
            . ' (Pembayaran ID: ' . $pembayaran->id
            . ', nominal Rp ' . number_format($pembayaran->nominal_bayar, 0, ',', '.')
            . ', tanggal bayar ' . $pembayaran->tanggal_bayar->format('d M Y') . ').'
        );

        $pembayaran->delete();

        // Buang cache relasi agar accessor menghitung ulang dari DB
        $tagihan->unsetRelation('pembayaran');

        // refreshStatus() memanggil is_lunas → sisa → total_dibayar
        $tagihan->refreshStatus();
        $this->statusPpdbService->syncBySiswa($tagihan->siswa);

        return redirect()
            ->route('pendaftaran.biaya', $tagihan->siswa_id)
            ->with('success', 'Riwayat cicilan berhasil dihapus oleh ' . $request->admin_penghapus);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tanggal_bayar' => 'required|date',
            'nominal_bayar' => 'required|numeric|min:1',
            'metode' => 'nullable|string|max:50',
            'keterangan' => 'nullable|string|max:255',
            'admin_penerima' => 'required|string|max:100',
            'admin_pengubah' => 'required|string|max:100',
        ]);

        $pembayaran = Pembayaran::with('tagihan')->findOrFail($id);
        $tagihan = $pembayaran->tagihan;
        $aktor = $this->actorLabel($request->admin_pengubah);

        logAktivitas(
            $aktor . ' - Submit Modal Edit Cicilan',
            'Mengirim form edit cicilan untuk pembayaran ID ' . $pembayaran->id
            . ' (Tagihan ID: ' . ($tagihan->id ?? '-') . ').'
        );

        $totalTerbayarLain = (int) $tagihan->pembayaran()
            ->where('id', '!=', $pembayaran->id)
            ->sum('nominal_bayar');
        $maksNominal = max(0, (int) $tagihan->total - $totalTerbayarLain);

        if ((int) $request->nominal_bayar > $maksNominal) {
            logAktivitas(
                $aktor . ' - Gagal Edit Cicilan',
                'Gagal edit pembayaran ID ' . $pembayaran->id
                . ' karena nominal Rp ' . number_format($request->nominal_bayar, 0, ',', '.')
                . ' melebihi batas Rp ' . number_format($maksNominal, 0, ',', '.') . '.'
            );

            return back()->with('error', 'Nominal melebihi sisa tagihan yang tersedia.');
        }

        // Guard: jangan biarkan biaya lain jadi belum lunas selama voucher diklaim.
        app(\App\Services\VoucherClaimService::class)
            ->assertPerubahanPembayaranAman($tagihan, $totalTerbayarLain + (int) $request->nominal_bayar);

        $nominalLama = (int) $pembayaran->nominal_bayar;
        $tanggalLama = optional($pembayaran->tanggal_bayar)->format('Y-m-d');
        $metodeLama = $pembayaran->metode;

        $pembayaran->update([
            'tanggal_bayar' => $request->tanggal_bayar,
            'nominal_bayar' => $request->nominal_bayar,
            'metode' => $request->metode,
            'keterangan' => $request->keterangan,
            'admin_penerima' => $request->admin_penerima,
        ]);

        // Buang cache relasi agar status tagihan dihitung ulang dari data terbaru.
        $tagihan->unsetRelation('pembayaran');
        $tagihan->refreshStatus();
        $this->statusPpdbService->syncBySiswa($tagihan->siswa);

        logAktivitas(
            $aktor . ' - Edit Riwayat Cicilan',
            'Berhasil edit pembayaran ID ' . $pembayaran->id
            . ' untuk siswa ID ' . ($tagihan->siswa_id ?? '-')
            . ' (nominal: Rp ' . number_format($nominalLama, 0, ',', '.')
            . ' -> Rp ' . number_format($request->nominal_bayar, 0, ',', '.')
            . ', tanggal: ' . ($tanggalLama ?: '-')
            . ' -> ' . $request->tanggal_bayar
            . ', metode: ' . ($metodeLama ?: '-')
            . ' -> ' . ($request->metode ?: '-') . ').'
        );

        return redirect()
            ->route('pendaftaran.biaya', $tagihan->siswa_id)
            ->with('success', 'Riwayat cicilan berhasil diperbarui oleh ' . $request->admin_pengubah);
    }

    private function terbilang($angka)
    {
        $angka = abs($angka);
        $baca = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];

        if ($angka < 12) {
            return " " . $baca[$angka];
        } elseif ($angka < 20) {
            return $this->terbilang($angka - 10) . " belas";
        } elseif ($angka < 100) {
            return $this->terbilang($angka / 10) . " puluh" . $this->terbilang($angka % 10);
        } elseif ($angka < 200) {
            return " seratus" . $this->terbilang($angka - 100);
        } elseif ($angka < 1000) {
            return $this->terbilang($angka / 100) . " ratus" . $this->terbilang($angka % 100);
        } elseif ($angka < 2000) {
            return " seribu" . $this->terbilang($angka - 1000);
        } elseif ($angka < 1000000) {
            return $this->terbilang($angka / 1000) . " ribu" . $this->terbilang($angka % 1000);
        }

        return "";
    }

    public function nota($id)
    {
        $pembayaran = Pembayaran::with([
            'tagihan.biaya',
            'tagihan.siswa.registration'
        ])->findOrFail($id);

        $namaPanitia = trim((string) request('panitia', $pembayaran->admin_penerima ?? ''));
        $aktor = $this->actorLabel($namaPanitia);

        logAktivitas(
            $aktor . ' - Akses Endpoint Cetak Nota',
            'Mengakses endpoint cetak nota untuk pembayaran ID ' . $pembayaran->id
            . ' (method: ' . request()->method() . ').'
        );

        if (request()->isMethod('post')) {
            $validator = validator(request()->all(), [
                'panitia' => 'required|string|max:100',
            ]);

            if ($validator->fails()) {
                logAktivitas(
                    $aktor . ' - Gagal Submit Modal Cetak Nota',
                    'Gagal submit cetak nota pembayaran ID ' . $pembayaran->id
                    . ' karena nama penerima/panitia tidak valid.'
                );

                return back()->withErrors($validator)->withInput();
            }

            $aktor = $this->actorLabel(request('panitia'));

            logAktivitas(
                $aktor . ' - Submit Modal Cetak Nota',
                'Mengirim form cetak nota untuk pembayaran ID ' . $pembayaran->id
                . ' dengan nama penerima/panitia: ' . request('panitia') . '.'
            );
        }

        logAktivitas(
            $aktor . ' - Cetak Nota Pembayaran',
            'Mencetak nota pembayaran ID ' . $pembayaran->id
            . ' untuk siswa ID ' . ($pembayaran->tagihan->siswa_id ?? '-')
            . ' dengan nominal Rp ' . number_format($pembayaran->nominal_bayar, 0, ',', '.')
            . ' (penerima/panitia dari modal: ' . ($namaPanitia !== '' ? $namaPanitia : '-') . ').'
        );

        $terbilang = ucwords($this->terbilang($pembayaran->nominal_bayar))." Rupiah";
        $panitia = $namaPanitia;

        $pdf = Pdf::loadView(
            'pendaftaran.cetak.nota',
            compact('pembayaran', 'terbilang', 'panitia')
        );

        $pdf->setPaper([0, 0, 609.45, 935.43]); // F4: 215mm x 330mm

        return $pdf->stream('kwitansi.pdf');
    }

    public function notaRincianBiaya(Siswa $siswa)
    {
        $siswa->load([
            'registration',
            'alamat',
            'ibu',
            'tagihan.biaya',
            'tagihan.pembayaran',
            'tagihan.voucher',
        ]);

        $namaPanitia = trim((string) request('panitia', ''));
        $aktor = $this->actorLabel($namaPanitia);

        logAktivitas(
            $aktor . ' - Akses Endpoint Cetak Nota Rincian Biaya',
            'Mengakses endpoint cetak nota rincian biaya untuk siswa ID ' . $siswa->id
            . ' (method: ' . request()->method() . ').'
        );

        if (request()->isMethod('post')) {
            $validator = validator(request()->all(), [
                'panitia' => 'required|string|max:100',
            ]);

            if ($validator->fails()) {
                logAktivitas(
                    $aktor . ' - Gagal Submit Modal Cetak Nota Rincian Biaya',
                    'Gagal submit cetak nota rincian biaya siswa ID ' . $siswa->id
                    . ' karena nama panitia tidak valid.'
                );

                return back()->withErrors($validator)->withInput();
            }

            $namaPanitia = trim((string) request('panitia'));
            $aktor = $this->actorLabel($namaPanitia);

            logAktivitas(
                $aktor . ' - Submit Modal Cetak Nota Rincian Biaya',
                'Mengirim form cetak nota rincian biaya siswa ID ' . $siswa->id
                . ' dengan nama panitia: ' . $namaPanitia . '.'
            );
        }

        $totalBiaya = (int) $siswa->tagihan->sum('total');
        $totalKekurangan = (int) $siswa->tagihan->sum('sisa');
        $totalTerbayar = max(0, $totalBiaya - $totalKekurangan);

        $claimedTagihan = $siswa->tagihan->first(fn ($t) => !empty($t->kode_voucher));
        $eligibleVouchers = app(\App\Services\VoucherClaimService::class)->getEligibleVouchers($siswa);

        logAktivitas(
            $aktor . ' - Cetak Nota Rincian Biaya',
            'Mencetak nota rincian biaya siswa ' . ($siswa->nama ?? '-')
            . ' (ID: ' . $siswa->id
            . ', No Registrasi: ' . (optional($siswa->registration)->nomor_registrasi ?? '-')
            . ', total biaya Rp ' . number_format($totalBiaya, 0, ',', '.')
            . ', total terbayar Rp ' . number_format($totalTerbayar, 0, ',', '.')
            . ', total kekurangan Rp ' . number_format($totalKekurangan, 0, ',', '.')
            . ', panitia: ' . ($namaPanitia !== '' ? $namaPanitia : '-') . ').'
        );

        $panitia = $namaPanitia;

        $pdf = Pdf::loadView(
            'pendaftaran.cetak.nota-rincian-biaya',
            compact('siswa', 'panitia', 'totalBiaya', 'totalTerbayar', 'totalKekurangan', 'claimedTagihan', 'eligibleVouchers')
        );

        $pdf->setPaper([0, 0, 609.45, 935.43]); // F4: 215mm x 330mm

        return $pdf->stream('nota-rincian-biaya.pdf');
    }

}
