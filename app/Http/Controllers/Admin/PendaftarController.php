<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PendaftarExport;
use App\Http\Controllers\Controller;
use App\Models\Biaya;
use App\Models\LogAktivitas;
use App\Models\Pembayaran;
use App\Models\Registration;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;

class PendaftarController extends Controller
{
    private function validateAcuanPaymentForPromotion(Siswa $siswa): ?string
    {
        $registration = $this->ensureRegistration($siswa);
        $tahunAjaranId = (int) ($registration->tahun_ajaran_id ?? 0);

        $acuanBiayaIds = Biaya::query()
            ->where('is_acuan_status_ppdb', true)
            ->when($tahunAjaranId > 0, function ($q) use ($tahunAjaranId) {
                $q->where('tahun_ajaran_id', $tahunAjaranId);
            })
            ->pluck('id');

        if ($acuanBiayaIds->isEmpty()) {
            return 'Belum ada biaya acuan status PPDB untuk tahun ajaran siswa ini.';
        }

        $hasRiwayatBayarAcuan = Pembayaran::query()
            ->whereHas('tagihan', function ($q) use ($siswa, $acuanBiayaIds) {
                $q->where('siswa_id', $siswa->id)
                    ->where('total', '>', 0)
                    ->whereIn('biaya_id', $acuanBiayaIds);
            })
            ->exists();

        if (!$hasRiwayatBayarAcuan) {
            return 'Siswa belum memiliki riwayat pembayaran pada biaya acuan status PPDB.';
        }

        return null;
    }

    private function ensureRegistration(Siswa $siswa)
    {
        if (!$siswa->registration) {
            abort(422, 'Data registrasi siswa tidak ditemukan.');
        }

        if (empty($siswa->registration->status)) {
            $siswa->registration->status = Registration::STATUS_BAKAL_CALON;
            $siswa->registration->save();
        }

        return $siswa->registration;
    }

    private function resolveFilterContext(Request $request): array
    {
        $validatedDateRange = validator($request->only(['date_from', 'date_to']), [
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ])->validate();

        $validStatuses = [
            Registration::STATUS_BAKAL_CALON,
            Registration::STATUS_CALON,
            Registration::STATUS_PESERTA_DIDIK,
        ];
        $validJenisKelamin = ['laki-laki', 'perempuan'];
        $validPaymentStatus = ['lunas', 'belum_lunas', 'belum_ada_tagihan'];

        $status = in_array((int) $request->input('status'), $validStatuses, true)
            ? (int) $request->input('status')
            : null;
        $jenisKelamin = in_array($request->input('jenis_kelamin'), $validJenisKelamin, true)
            ? $request->input('jenis_kelamin')
            : null;
        $paymentStatus = in_array($request->input('payment_status'), $validPaymentStatus, true)
            ? $request->input('payment_status')
            : null;
        $tahunAjaranId = $request->filled('tahun_ajaran_id') && is_numeric($request->tahun_ajaran_id)
            ? (int) $request->tahun_ajaran_id
            : null;
        $order = $request->input('order') === 'terlama' ? 'terlama' : 'terbaru';

        $query = Siswa::with([
            'registration.tahunAjaran',
            'ibu',
            'tagihan.biaya',
            'tagihan.pembayaran',
        ]);

        if ($request->filled('q')) {
            $search = trim((string) $request->q);

            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhereHas('registration', function ($q2) use ($search) {
                      $q2->where('nomor_registrasi', 'like', "%{$search}%");
                  });
            });
        }

        if (!is_null($status)) {
            $query->whereHas('registration', function ($q) use ($status) {
                $q->where('status', $status);
            });
        }

        if (!empty($jenisKelamin)) {
            $query->where('jenis_kelamin', $jenisKelamin);
        }

        if (!empty($tahunAjaranId)) {
            $query->whereHas('registration', function ($q) use ($tahunAjaranId) {
                $q->where('tahun_ajaran_id', $tahunAjaranId);
            });
        }

        $dateFrom = $validatedDateRange['date_from'] ?? null;
        $dateTo = $validatedDateRange['date_to'] ?? null;

        if ($dateFrom || $dateTo) {
            $query->whereHas('registration', function ($q) use ($dateFrom, $dateTo) {
                $q->when($dateFrom, fn ($q2) => $q2->whereDate('tanggal_daftar', '>=', $dateFrom))
                    ->when($dateTo, fn ($q2) => $q2->whereDate('tanggal_daftar', '<=', $dateTo));
            });
        }

        if ($paymentStatus === 'lunas') {
            $query->whereHas('tagihan', function ($q) {
                $q->where('total', '>', 0);
            })->whereDoesntHave('tagihan', function ($q) {
                $q->where('total', '>', 0)
                    ->where('status', '!=', 'lunas');
            });
        } elseif ($paymentStatus === 'belum_lunas') {
            $query->whereHas('tagihan', function ($q) {
                $q->where('total', '>', 0)
                    ->where('status', '!=', 'lunas');
            });
        } elseif ($paymentStatus === 'belum_ada_tagihan') {
            $query->whereDoesntHave('tagihan', function ($q) {
                $q->where('total', '>', 0);
            });
        }

        if ($order === 'terbaru') {
            $query->orderByDesc('created_at')->orderByDesc('id');
        } else {
            $query->orderBy('created_at')->orderBy('id');
        }

        return [
            'query' => $query,
            'filters' => [
                'q' => (string) $request->input('q', ''),
                'status' => $status,
                'jenis_kelamin' => $jenisKelamin,
                'payment_status' => $paymentStatus,
                'tahun_ajaran_id' => $tahunAjaranId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'order' => $order,
            ],
            'tahunAjaranOptions' => TahunAjaran::query()
                ->orderByDesc('aktif')
                ->orderByDesc('id')
                ->get(['id', 'nama', 'aktif']),
        ];
    }

    public function index(Request $request)
    {
        $context = $this->resolveFilterContext($request);
        $query = $context['query'];
        $perPage = (int) $request->input('per_page', 20);
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        return view('admin.pendaftar.index', [
            'siswa' => $query
                ->paginate($perPage)
                ->withQueryString(),
            'filters' => $context['filters'],
            'tahunAjaranOptions' => $context['tahunAjaranOptions'],
            'isArsipPage' => false,
            'perPage' => $perPage,
        ]);
    }

    public function arsip(Request $request)
    {
        return redirect()
            ->route('pendaftar.index')
            ->with('error', 'Mode arsip sudah tidak digunakan pada flow status PPDB baru.');
    }

    private function isExportDirectoryAccessible(string $dir): bool
    {
        return File::exists($dir)
            && File::isDirectory($dir)
            && File::isReadable($dir)
            && File::isWritable($dir);
    }

    private function ensureExportDirectories(): bool
    {
        $excelDir = public_path('file' . DIRECTORY_SEPARATOR . 'excel');
        $pdfDir = public_path('file' . DIRECTORY_SEPARATOR . 'pdf');

        File::ensureDirectoryExists($excelDir, 0755, true);
        File::ensureDirectoryExists($pdfDir, 0755, true);

        return $this->isExportDirectoryAccessible($excelDir)
            && $this->isExportDirectoryAccessible($pdfDir);
    }

    private function cleanupOldExportFiles(int $days = 7): void
    {
        $expiryTimestamp = now()->subDays($days)->timestamp;
        foreach (['excel', 'pdf'] as $subdir) {
            $dir = public_path('file' . DIRECTORY_SEPARATOR . $subdir);
            if (!File::exists($dir) || !File::isDirectory($dir)) {
                continue;
            }

            foreach (File::files($dir) as $file) {
                if ($file->getMTime() <= $expiryTimestamp) {
                    File::delete($file->getPathname());
                }
            }
        }
    }

    public function export(Request $request)
    {
        $format = $request->input('format');
        if (!in_array($format, ['excel', 'pdf'], true)) {
            return redirect()->route('pendaftar.index')->with('error', 'Format export tidak valid.');
        }

        $this->cleanupOldExportFiles(7);

        if (!$this->ensureExportDirectories()) {
            return redirect()->route('pendaftar.index')
                ->with('error', 'Folder export tidak dapat diakses atau ditulis. Pastikan public/file/excel dan public/file/pdf dapat dibaca/ditulis oleh server.');
        }

        $context = $this->resolveFilterContext($request);
        $rows = $context['query']->get();

        $exportRoot = public_path('file');
        $excelDir = $exportRoot . DIRECTORY_SEPARATOR . 'excel';
        $pdfDir = $exportRoot . DIRECTORY_SEPARATOR . 'pdf';

        if ($format === 'excel') {
            $fileName = 'pendaftar-' . now()->format('Ymd-His') . '.xlsx';
            $filePath = $excelDir . DIRECTORY_SEPARATOR . $fileName;

            $excelData = Excel::raw(new PendaftarExport($rows), \Maatwebsite\Excel\Excel::XLSX);
            File::put($filePath, $excelData);

            logAktivitas('Export Pendaftar', 'Export data pendaftar ke Excel (' . $rows->count() . ' baris). Disimpan ke public/file/excel/.');
            return response()->download($filePath, $fileName);
        }

        $fileName = 'pendaftar-' . now()->format('Ymd-His') . '.pdf';
        $filePath = $pdfDir . DIRECTORY_SEPARATOR . $fileName;
        $pdf = Pdf::loadView('admin.pendaftar.export-pdf', ['rows' => $rows]);
        $pdf->save($filePath);

        logAktivitas('Export Pendaftar', 'Export data pendaftar ke PDF (' . $rows->count() . ' baris). Disimpan ke public/file/pdf/.');
        return response()->download($filePath, $fileName);
    }

    public function show(Siswa $siswa)
    {
        $siswa->load([
            // ================= REGISTRATION =================
            'registration.tahunAjaran',

            // ================= IDENTITAS TAMBAHAN =================
            'alamat',

            // ================= ORANG TUA =================
            'ibu',
            'ayah',
            'wali',

            // ================= DATA PENDUKUNG =================
            'dataPendukung.paudTk',

            // ================= KEUANGAN =================
            'tagihan.biaya',
            'tagihan.pembayaran',
            'tagihan.voucher',
        ]);

        $aktivitas = LogAktivitas::query()
            ->where(function ($q) use ($siswa) {
                $q->where('keterangan', 'like', '%Siswa ID: ' . $siswa->id . '%')
                    ->orWhere('keterangan', 'like', '%siswa ID ' . $siswa->id . '%')
                    ->orWhere('keterangan', 'like', '%(ID: ' . $siswa->id . '%')
                    ->orWhere('keterangan', 'like', '%ID ' . $siswa->id . '%')
                    ->orWhere('aksi', 'like', '%' . $siswa->nama . '%');
            })
            ->latest()
            ->limit(30)
            ->get();

        return view('admin.pendaftar.show', compact('siswa', 'aktivitas'));
    }

    public function quickUpdate(Request $request, Siswa $siswa)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nik' => 'required|digits:16|unique:siswa,nik,' . $siswa->id,
            'nisn' => 'nullable|digits_between:1,10|unique:siswa,nisn,' . $siswa->id,
            'no_kk' => 'required|digits:16',
            'jenis_kelamin' => 'required|in:laki-laki,perempuan',
        ]);

        $before = [
            'nama' => $siswa->nama,
            'nik' => $siswa->nik,
            'nisn' => $siswa->nisn,
            'no_kk' => $siswa->no_kk,
            'jenis_kelamin' => $siswa->jenis_kelamin,
        ];

        try {
            $siswa->update([
                'nama' => $validated['nama'],
                'nik' => $validated['nik'],
                'nisn' => $validated['nisn'] ?? null,
                'no_kk' => $validated['no_kk'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
            ]);
        } catch (QueryException $e) {
            $message = strtolower($e->getMessage());

            if (str_contains($message, 'nik')) {
                return back()->withErrors(['nik' => 'NIK sudah terdaftar.'])->withInput();
            }

            return back()->with('error', 'Gagal memperbarui data pendaftar. Silakan coba lagi.')->withInput();
        }

        logAktivitas(
            'Admin - Quick Edit Pendaftar',
            'Siswa ID: ' . $siswa->id
            . ' | Nama: ' . $before['nama'] . ' -> ' . $validated['nama']
            . ' | NIK: ' . ($before['nik'] ?: '-') . ' -> ' . $validated['nik']
            . ' | NISN: ' . ($before['nisn'] ?: '-') . ' -> ' . ($validated['nisn'] ?? '-')
            . ' | No KK: ' . ($before['no_kk'] ?: '-') . ' -> ' . $validated['no_kk']
            . ' | JK: ' . ($before['jenis_kelamin'] ?: '-') . ' -> ' . $validated['jenis_kelamin']
        );

        return back()->with('success', 'Data pendaftar berhasil diperbarui.');
    }

    public function updateStatus(Request $request, Siswa $siswa)
    {
        return $this->jadikanPesertaDidik($siswa);
    }

    public function jadikanPesertaDidik(Siswa $siswa)
    {
        $registration = $this->ensureRegistration($siswa);
        $beforeStatus = (int) ($registration->status ?: Registration::STATUS_BAKAL_CALON);

        if ($beforeStatus === Registration::STATUS_PESERTA_DIDIK) {
            return back()->with('success', 'Data sudah berstatus Peserta Didik.');
        }

        $validationError = $this->validateAcuanPaymentForPromotion($siswa);
        if (!is_null($validationError)) {
            return back()->with('error', $validationError);
        }

        $registration->status = Registration::STATUS_PESERTA_DIDIK;
        $registration->save();

        logAktivitas(
            'Admin - Jadikan Peserta Didik',
            'Siswa ID: ' . $siswa->id
            . ' | No Registrasi: ' . ($registration->nomor_registrasi ?? '-')
            . ' | Status PPDB: ' . Registration::statusLabel($beforeStatus)
            . ' -> ' . Registration::statusLabel(Registration::STATUS_PESERTA_DIDIK)
        );

        return back()->with('success', 'Status berhasil diperbarui menjadi Peserta Didik.');
    }

    public function toggleArsip(Siswa $siswa)
    {
        return back()->with('error', 'Fitur arsip dinonaktifkan pada flow status PPDB baru.');
    }

    public function activity(Siswa $siswa)
    {
        return redirect()
            ->route('pendaftar.show', $siswa)
            ->with('scroll_to_activity', true);
    }
}
