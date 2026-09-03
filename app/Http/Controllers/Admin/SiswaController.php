<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KelasSiswa;
use App\Models\Registration;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Exports\SiswaAktifExport;
use App\Exports\SiswaKeuanganExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SiswaController extends Controller
{
    private const MAX_EXPORT_ROWS = 2000;

    public function normalizeNama(Request $request)
    {
        $validated = $request->validate([
            'kelas_id' => ['nullable', 'string'],
        ]);

        $tahunAktif = TahunAjaran::where('aktif', true)->first();

        if (!$tahunAktif) {
            return back()->with('error', 'Tidak ada Tahun Ajaran aktif.');
        }

        $kelasIdRaw = $validated['kelas_id'] ?? null;
        $scopeLabel = 'semua peserta didik aktif';

        $query = Siswa::query()
            ->whereHas('registration', function ($q) use ($tahunAktif) {
                $q->where('status', Registration::STATUS_PESERTA_DIDIK)
                    ->where('tahun_ajaran_id', $tahunAktif->id);
            });

        if ($kelasIdRaw === 'belum') {
            $query->whereNull('kelas_siswa_id');
            $scopeLabel = 'peserta didik belum kelas';
        } elseif (is_numeric($kelasIdRaw) && (int) $kelasIdRaw > 0) {
            $kelasId = (int) $kelasIdRaw;
            $query->where('kelas_siswa_id', $kelasId);

            $kelas = KelasSiswa::query()->find($kelasId);
            $scopeLabel = 'kelas ' . ($kelas?->nama_kelas ?? ('ID ' . $kelasId));
        }

        $total = (clone $query)->count();

        if ($total === 0) {
            return back()->with('info', 'Tidak ada data yang cocok untuk dinormalisasi.');
        }

        $updated = 0;

        DB::transaction(function () use ($query, &$updated) {
            $query->chunkById(100, function ($rows) use (&$updated) {
                foreach ($rows as $row) {
                    $namaLama = $row->nama;
                    $namaBaru = ui_title_case_name($namaLama, $namaLama ?? '');

                    if ($namaBaru !== '' && $namaBaru !== $namaLama) {
                        $row->nama = $namaBaru;
                        $row->save();
                        $updated++;
                    }
                }
            });
        });

        logAktivitas(
            'Admin - Normalisasi Nama Siswa',
            'Scope: ' . $scopeLabel . ' | Total diperiksa: ' . $total . ' | Berubah: ' . $updated . '.'
        );

        if ($updated === 0) {
            return back()->with('warning', 'Data sudah di normalisasi sesuai Tittle case.');
        }

        return back()->with('success', 'berahasil normalisasi');
    }

    private function countNamaPerluNormalisasi(TahunAjaran $tahunAktif, ?int $kelasId = null, bool $belumKelas = false): int
    {
        $query = Siswa::query()
            ->select(['id', 'nama'])
            ->whereHas('registration', function ($q) use ($tahunAktif) {
                $q->where('status', Registration::STATUS_PESERTA_DIDIK)
                    ->where('tahun_ajaran_id', $tahunAktif->id);
            });

        if ($belumKelas) {
            $query->whereNull('kelas_siswa_id');
        } elseif (!empty($kelasId)) {
            $query->where('kelas_siswa_id', $kelasId);
        }

        $count = 0;

        $query->chunkById(200, function ($rows) use (&$count) {
            foreach ($rows as $row) {
                $namaLama = $row->nama;
                $namaBaru = ui_title_case_name($namaLama, $namaLama ?? '');

                if ($namaBaru !== '' && $namaBaru !== $namaLama) {
                    $count++;
                }
            }
        });

        return $count;
    }

    public function exportExcelKeuangan(Request $request)
    {
        $this->cleanupOldExportFiles(7);
        if (!$this->ensureExportDirectories()) {
            return redirect()->back()->with('error', 'Folder export tidak dapat diakses atau ditulis. Pastikan public/file/excel dan public/file/pdf dapat dibaca/ditulis oleh server.');
        }

        $rows = $this->getFilteredSiswaKeuangan($request);
        if ($exportError = $this->guardExportRowLimit($rows, 'Export Excel keuangan')) {
            return redirect()->back()->with('error', $exportError);
        }
        $kelasIdRaw = $request->input('kelas_id');
        $namaKelas = 'Semua';

        if ($kelasIdRaw === 'belum') {
            $namaKelas = 'Belum Dapat Kelas';
        } elseif (is_numeric($kelasIdRaw) && (int) $kelasIdRaw > 0) {
            $kelas = KelasSiswa::query()->find((int) $kelasIdRaw);
            $namaKelas = $kelas?->nama_kelas ?: ('ID ' . (int) $kelasIdRaw);
        }

        $tanggal = now()->format('Y-m-d');
        $namaKelasForFile = preg_replace('~[\\/:*?"<>|]+~', ' ', (string) $namaKelas);
        $namaKelasForFile = trim(preg_replace('~\s+~', ' ', $namaKelasForFile));
        if ($namaKelasForFile === '') {
            $namaKelasForFile = 'Semua';
        }

        $fileName = 'keuangan peserta didik kelas (' . $namaKelasForFile . ') ' . $tanggal . '.xlsx';

        $excelDir = public_path('file/excel');
        File::ensureDirectoryExists($excelDir, 0755, true);

        $filePath = $excelDir . DIRECTORY_SEPARATOR . $fileName;
        $excelData = Excel::raw(new SiswaKeuanganExport($rows), \Maatwebsite\Excel\Excel::XLSX);
        File::put($filePath, $excelData);

        return response()->download($filePath, $fileName);
    }

    public function exportPdfKeuangan(Request $request)
    {
        $this->cleanupOldExportFiles(7);
        if (!$this->ensureExportDirectories()) {
            return redirect()->back()->with('error', 'Folder export tidak dapat diakses atau ditulis. Pastikan public/file/excel dan public/file/pdf dapat dibaca/ditulis oleh server.');
        }

        $tahunAktif = TahunAjaran::where('aktif', true)->first();
        $rows = $this->getFilteredSiswaKeuangan($request);
        if ($exportError = $this->guardExportRowLimit($rows, 'Export PDF keuangan')) {
            return redirect()->back()->with('error', $exportError);
        }

        $tableRows = $rows->values()->map(function ($item) {
            $registration = $item->registration;
            $tagihan = $item->tagihan ?? collect();
            $totalBiaya = (int) $tagihan->sum('total');
            $totalTerbayar = (int) $tagihan->sum('total_dibayar');
            $totalKekurangan = (int) $tagihan->sum('sisa');

            // Sertakan tagihan dan pembayaran lengkap untuk kebutuhan PDF
            return [
                'nama' => $item->nama ?? '-',
                'jenis_kelamin' => $item->jenis_kelamin === 'laki-laki' ? 'Laki-laki' : ($item->jenis_kelamin === 'perempuan' ? 'Perempuan' : ($item->jenis_kelamin ?? '-')),
                'no_registrasi' => optional($registration)->nomor_registrasi ?? '-',
                'total_biaya' => $totalBiaya,
                'total_terbayar' => $totalTerbayar,
                'total_kekurangan' => $totalKekurangan,
                'tagihan' => $tagihan,
            ];
        });

        $kelasIdRaw = $request->input('kelas_id');
        $namaKelas = 'Semua';
        if ($kelasIdRaw === 'belum') {
            $namaKelas = 'Belum Dapat Kelas';
        } elseif (is_numeric($kelasIdRaw) && (int) $kelasIdRaw > 0) {
            $kelas = KelasSiswa::query()->find((int) $kelasIdRaw);
            $namaKelas = $kelas?->nama_kelas ?: ('ID ' . (int) $kelasIdRaw);
        }

        $tanggal = now()->format('Y-m-d');
        $namaKelasForFile = preg_replace('~[\\/:*?"<>|]+~', ' ', (string) $namaKelas);
        $namaKelasForFile = trim(preg_replace('~\s+~', ' ', $namaKelasForFile));
        if ($namaKelasForFile === '') {
            $namaKelasForFile = 'Semua';
        }

        $pdf = Pdf::loadView('pdf.siswa_keuangan', [
            'rows' => $tableRows,
            'tahunAktif' => $tahunAktif,
            'title' => 'Export Keuangan Peserta Didik',
            'namaKelas' => $namaKelas,
        ]);

        $fileName = 'keuangan peserta didik kelas (' . $namaKelasForFile . ') ' . $tanggal . '.pdf';
        $pdfDir = public_path('file/pdf');
        File::ensureDirectoryExists($pdfDir, 0755, true);

        $filePath = $pdfDir . DIRECTORY_SEPARATOR . $fileName;
        $pdf->setPaper('a4', 'landscape')->save($filePath);

        return response()->download($filePath, $fileName);
    }

    public function exportExcel(Request $request)
    {
        $this->cleanupOldExportFiles(7);
        if (!$this->ensureExportDirectories()) {
            return redirect()->back()->with('error', 'Folder export tidak dapat diakses atau ditulis. Pastikan public/file/excel dan public/file/pdf dapat dibaca/ditulis oleh server.');
        }

        $rows = $this->getFilteredSiswa($request);
        if ($exportError = $this->guardExportRowLimit($rows, 'Export Excel peserta didik')) {
            return redirect()->back()->with('error', $exportError);
        }
        $kelasIdRaw = $request->input('kelas_id');
        $namaKelas = 'Semua';

        if ($kelasIdRaw === 'belum') {
            $namaKelas = 'Belum Dapat Kelas';
        } elseif (is_numeric($kelasIdRaw) && (int) $kelasIdRaw > 0) {
            $kelas = KelasSiswa::query()->find((int) $kelasIdRaw);
            $namaKelas = $kelas?->nama_kelas ?: ('ID ' . (int) $kelasIdRaw);
        }

        $tanggal = now()->format('Y-m-d');
        $namaKelasForFile = preg_replace('~[\\/:*?"<>|]+~', ' ', (string) $namaKelas);
        $namaKelasForFile = trim(preg_replace('~\s+~', ' ', $namaKelasForFile));
        if ($namaKelasForFile === '') {
            $namaKelasForFile = 'Semua';
        }

        $fileName = 'daftar peserta didik kelas (' . $namaKelasForFile . ') ' . $tanggal . '.xlsx';

        $excelDir = public_path('file/excel');
        File::ensureDirectoryExists($excelDir, 0755, true);

        $filePath = $excelDir . DIRECTORY_SEPARATOR . $fileName;
        $excelData = Excel::raw(new SiswaAktifExport($rows), \Maatwebsite\Excel\Excel::XLSX);
        File::put($filePath, $excelData);

        return response()->download($filePath, $fileName);
    }

    public function exportPdf(Request $request)
    {
        $this->cleanupOldExportFiles(7);
        if (!$this->ensureExportDirectories()) {
            return redirect()->back()->with('error', 'Folder export tidak dapat diakses atau ditulis. Pastikan public/file/excel dan public/file/pdf dapat dibaca/ditulis oleh server.');
        }

        $tahunAktif = TahunAjaran::where('aktif', true)->first();
        $rows = $this->getFilteredSiswa($request);
        if ($exportError = $this->guardExportRowLimit($rows, 'Export PDF peserta didik')) {
            return redirect()->back()->with('error', $exportError);
        }
        $scopeLabel = $this->getScopeLabel($request);

        $kelasIdRaw = $request->input('kelas_id');
        $namaKelas = 'Semua';
        if ($kelasIdRaw === 'belum') {
            $namaKelas = 'Belum Dapat Kelas';
        } elseif (is_numeric($kelasIdRaw) && (int) $kelasIdRaw > 0) {
            $kelas = KelasSiswa::query()->find((int) $kelasIdRaw);
            $namaKelas = $kelas?->nama_kelas ?: ('ID ' . (int) $kelasIdRaw);
        }

        $tanggal = now()->format('Y-m-d');
        $namaKelasForFile = preg_replace('~[\\/:*?"<>|]+~', ' ', (string) $namaKelas);
        $namaKelasForFile = trim(preg_replace('~\s+~', ' ', $namaKelasForFile));
        if ($namaKelasForFile === '') {
            $namaKelasForFile = 'Semua';
        }

        $pdf = Pdf::loadView('pdf.siswa_aktif_detail', [
            'rows' => $rows,
            'tahunAktif' => $tahunAktif,
            'scopeLabel' => $scopeLabel,
            'title' => 'Detail Peserta Didik Aktif'
        ]);

        $fileName = 'daftar peserta didik kelas (' . $namaKelasForFile . ') ' . $tanggal . '.pdf';

        $pdfDir = public_path('file/pdf');
        File::ensureDirectoryExists($pdfDir, 0755, true);

        $filePath = $pdfDir . DIRECTORY_SEPARATOR . $fileName;
        $pdf->setPaper('f4', 'portrait')->save($filePath);

        return response()->download($filePath, $fileName);
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

    private function getFilteredSiswa(Request $request)
    {
        $tahunAktif = TahunAjaran::where('aktif', true)->first();
        if (!$tahunAktif) return collect();

        $search = trim((string) $request->input('q', ''));
        $jenisKelaminRaw = $request->input('jenis_kelamin');
        $jenisKelamin = in_array($jenisKelaminRaw, ['laki-laki', 'perempuan'], true) ? $jenisKelaminRaw : null;
        $kelasIdRaw = $request->input('kelas_id');
        $hasilTes = strtoupper(trim((string) $request->input('hasil_tes')));
        if (!in_array($hasilTes, ['SB', 'B', 'PB', 'BT'], true)) {
            $hasilTes = null;
        }
        
        $siswaQuery = Siswa::with([
                'registration.tahunAjaran',
                'kelasSiswa',
                'alamat',
                'ayah',
                'ibu',
                'wali',
                'dataPendukung.paudTk',
            ])
            ->whereHas('registration', function ($q) use ($tahunAktif) {
                $q->where('status', Registration::STATUS_PESERTA_DIDIK)
                    ->where('tahun_ajaran_id', $tahunAktif->id);
            });

        if ($search !== '') {
            $siswaQuery->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhereHas('registration', function ($q2) use ($search) {
                        $q2->where('nomor_registrasi', 'like', "%{$search}%");
                    })
                    ->orWhereHas('ibu', function ($q3) use ($search) {
                        $q3->where('nama', 'like', "%{$search}%")
                            ->orWhere('no_hp', 'like', "%{$search}%");
                    });
            });
        }

        if ($jenisKelamin) $siswaQuery->where('jenis_kelamin', $jenisKelamin);

        if ($kelasIdRaw === 'belum') {
            $siswaQuery->whereNull('kelas_siswa_id');
        } elseif (is_numeric($kelasIdRaw) && (int) $kelasIdRaw > 0) {
            $siswaQuery->where('kelas_siswa_id', (int) $kelasIdRaw);
        }

        if (!empty($hasilTes)) {
            if ($hasilTes === 'BT') {
                $siswaQuery->where(function ($q) {
                    $q->whereNull('hasil_tes')
                        ->orWhere('hasil_tes', '')
                        ->orWhereRaw("LOWER(hasil_tes) IN ('bt', 'belum test', 'belum_test')");
                });
            } else {
                $siswaQuery->whereRaw('UPPER(hasil_tes) = ?', [$hasilTes]);
            }
        }

        $order = $request->input('order', 'nama_asc');
        if ($order === 'nama_desc') $siswaQuery->orderByDesc('nama');
        elseif ($order === 'terbaru') $siswaQuery->orderByDesc('created_at')->orderByDesc('id');
        elseif ($order === 'terlama') $siswaQuery->orderBy('created_at')->orderBy('id');
        else $siswaQuery->orderBy('nama');

        return $siswaQuery->get();
    }

    private function getFilteredSiswaKeuangan(Request $request)
    {
        $tahunAktif = TahunAjaran::where('aktif', true)->first();
        if (!$tahunAktif) return collect();

        $search = trim((string) $request->input('q', ''));
        $jenisKelaminRaw = $request->input('jenis_kelamin');
        $jenisKelamin = in_array($jenisKelaminRaw, ['laki-laki', 'perempuan'], true) ? $jenisKelaminRaw : null;
        $kelasIdRaw = $request->input('kelas_id');
        $hasilTes = strtoupper(trim((string) $request->input('hasil_tes')));
        if (!in_array($hasilTes, ['SB', 'B', 'PB', 'BT'], true)) {
            $hasilTes = null;
        }

        $siswaQuery = Siswa::with([
                'registration.tahunAjaran',
                'kelasSiswa',
                'tagihan' => function ($q) use ($tahunAktif) {
                    $q->whereHas('biaya', function ($b) use ($tahunAktif) {
                        $b->where('tahun_ajaran_id', $tahunAktif->id);
                    })->with(['pembayaran', 'biaya']);
                },
            ])
            ->whereHas('registration', function ($q) use ($tahunAktif) {
                $q->where('status', Registration::STATUS_PESERTA_DIDIK)
                    ->where('tahun_ajaran_id', $tahunAktif->id);
            });

        if ($search !== '') {
            $siswaQuery->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhereHas('registration', function ($q2) use ($search) {
                        $q2->where('nomor_registrasi', 'like', "%{$search}%");
                    });
            });
        }

        if ($jenisKelamin) $siswaQuery->where('jenis_kelamin', $jenisKelamin);

        if ($kelasIdRaw === 'belum') {
            $siswaQuery->whereNull('kelas_siswa_id');
        } elseif (is_numeric($kelasIdRaw) && (int) $kelasIdRaw > 0) {
            $siswaQuery->where('kelas_siswa_id', (int) $kelasIdRaw);
        }

        if (!empty($hasilTes)) {
            if ($hasilTes === 'BT') {
                $siswaQuery->where(function ($q) {
                    $q->whereNull('hasil_tes')
                        ->orWhere('hasil_tes', '')
                        ->orWhereRaw("LOWER(hasil_tes) IN ('bt', 'belum test', 'belum_test')");
                });
            } else {
                $siswaQuery->whereRaw('UPPER(hasil_tes) = ?', [$hasilTes]);
            }
        }

        $order = $request->input('order', 'nama_asc');
        if ($order === 'nama_desc') $siswaQuery->orderByDesc('nama');
        elseif ($order === 'terbaru') $siswaQuery->orderByDesc('created_at')->orderByDesc('id');
        elseif ($order === 'terlama') $siswaQuery->orderBy('created_at')->orderBy('id');
        else $siswaQuery->orderBy('nama');

        return $siswaQuery->get();
    }

    private function guardExportRowLimit($rows, string $label): ?string
    {
        $count = $rows->count();

        if ($count <= self::MAX_EXPORT_ROWS) {
            return null;
        }

        logAktivitas(
            'Admin - Export Dibatalkan',
            $label . ' dibatalkan karena jumlah data ' . $count
            . ' melebihi batas aman export ' . self::MAX_EXPORT_ROWS . ' baris.'
        );

        return 'Jumlah data export (' . $count . ' baris) melebihi batas aman ' . self::MAX_EXPORT_ROWS . ' baris. Persempit filter lalu coba lagi.';
    }

    private function getScopeLabel(Request $request)
    {
        $kelasIdRaw = $request->input('kelas_id');
        if ($kelasIdRaw === 'belum') return 'Peserta Didik Belum Dapat Kelas';
        if (is_numeric($kelasIdRaw) && (int) $kelasIdRaw > 0) {
            $kelas = KelasSiswa::find($kelasIdRaw);
            return $kelas ? "Peserta Didik Kelas {$kelas->nama_kelas}" : 'Per Kelas';
        }
        return 'Semua Peserta Didik Aktif';
    }

    public function kelas1(Request $request)
    {
        $tahunAktif = TahunAjaran::where('aktif', true)->first();
        $fromMenu = $request->boolean('from_menu');

        if ($fromMenu) {
            $kelasIdRaw = $request->input('kelas_id');
            $query = [];

            if ($kelasIdRaw === 'belum') {
                $query['kelas_id'] = 'belum';
            } elseif (is_numeric($kelasIdRaw) && (int) $kelasIdRaw > 0) {
                $query['kelas_id'] = (int) $kelasIdRaw;
            }

            return redirect()->route('siswa.index', $query);
        }

        if (!$tahunAktif) {
            return back()->with('error', 'Tidak ada Tahun Ajaran aktif.');
        }

        $perPage = $fromMenu ? 20 : (int) $request->input('per_page', 20);
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $search = $fromMenu ? '' : trim((string) $request->input('q', ''));
        $jenisKelaminRaw = $fromMenu ? null : $request->input('jenis_kelamin');
        $jenisKelamin = in_array($jenisKelaminRaw, ['laki-laki', 'perempuan'], true)
            ? $request->input('jenis_kelamin')
            : null;
        $hasilTesRaw = $fromMenu ? null : $request->input('hasil_tes');
        $hasilTes = strtoupper(trim((string) $hasilTesRaw));
        if (!in_array($hasilTes, ['SB', 'B', 'PB', 'BT'], true)) {
            $hasilTes = null;
        }
        $kelasIdRaw = $request->input('kelas_id');
        $kelasId = null;
        $filterBelumKelas = false;

        if ($kelasIdRaw === 'belum') {
            $filterBelumKelas = true;
        } elseif (is_numeric($kelasIdRaw) && (int) $kelasIdRaw > 0) {
            $kelasId = (int) $kelasIdRaw;
        }

        $order = $fromMenu ? 'nama_asc' : $request->input('order');
        if (!in_array($order, ['nama_asc', 'nama_desc', 'terbaru', 'terlama'], true)) {
            $order = 'nama_asc';
        }

        $siswaAktifQuery = Siswa::query()
            ->whereHas('registration', function ($q) use ($tahunAktif) {
                $q->where('status', Registration::STATUS_PESERTA_DIDIK)
                    ->where('tahun_ajaran_id', $tahunAktif->id);
            });

        $siswaQuery = Siswa::with([
                'registration.tahunAjaran',
                'ibu',
                'kelasSiswa',
            ])
            ->whereHas('registration', function ($q) use ($tahunAktif) {
                $q->where('status', Registration::STATUS_PESERTA_DIDIK)
                    ->where('tahun_ajaran_id', $tahunAktif->id);
            });

        if ($search !== '') {
            $siswaQuery->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhereHas('registration', function ($q2) use ($search) {
                        $q2->where('nomor_registrasi', 'like', "%{$search}%");
                    })
                    ->orWhereHas('ibu', function ($q3) use ($search) {
                        $q3->where('nama', 'like', "%{$search}%")
                            ->orWhere('no_hp', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($jenisKelamin)) {
            $siswaQuery->where('jenis_kelamin', $jenisKelamin);
        }

        if ($filterBelumKelas) {
            $siswaQuery->whereNull('kelas_siswa_id');
        } elseif (!empty($kelasId)) {
            $siswaQuery->where('kelas_siswa_id', $kelasId);
        }

        if (!empty($hasilTes)) {
            if ($hasilTes === 'BT') {
                $siswaQuery->where(function ($q) {
                    $q->whereNull('hasil_tes')
                        ->orWhere('hasil_tes', '')
                        ->orWhereRaw("LOWER(hasil_tes) IN ('bt', 'belum test', 'belum_test')");
                });
            } else {
                $siswaQuery->whereRaw('UPPER(hasil_tes) = ?', [$hasilTes]);
            }
        }

        if ($order === 'nama_desc') {
            $siswaQuery->orderByDesc('nama');
        } elseif ($order === 'terbaru') {
            $siswaQuery->orderByDesc('created_at')->orderByDesc('id');
        } elseif ($order === 'terlama') {
            $siswaQuery->orderBy('created_at')->orderBy('id');
        } else {
            $siswaQuery->orderBy('nama');
        }

        $siswa = $siswaQuery
            ->paginate($perPage)
            ->withQueryString();

        $scopeLabel = 'Semua Peserta Didik Aktif';
        if ($filterBelumKelas) {
            $scopeLabel = 'Peserta Didik Belum Dapat Kelas';
        } elseif (!empty($kelasId)) {
            $kelasAktif = KelasSiswa::query()->find($kelasId);
            $scopeLabel = 'Peserta Didik Kelas ' . ($kelasAktif->nama_kelas ?? ('ID ' . $kelasId));
        }

        $scopeCount = $siswa->total();
        $scopeNormalizeCount = $this->countNamaPerluNormalisasi(
            $tahunAktif,
            !empty($kelasId) ? (int) $kelasId : null,
            (bool) $filterBelumKelas
        );

        $kelasList = KelasSiswa::query()
            ->withCount([
                'siswa',
                'siswa as siswa_aktif_count' => function ($q) use ($tahunAktif) {
                    $q->whereHas('registration', function ($q2) use ($tahunAktif) {
                        $q2->where('status', Registration::STATUS_PESERTA_DIDIK)
                            ->where('tahun_ajaran_id', $tahunAktif->id);
                    });
                },
            ])
            ->orderBy('nama_kelas')
            ->get(['id', 'nama_kelas']);

        $totalSiswaAktif = (clone $siswaAktifQuery)->count();
        $totalBelumMasukKelas = (clone $siswaAktifQuery)
            ->whereNull('kelas_siswa_id')
            ->count();

        $siswaBelumKelas = Siswa::query()
            ->with('registration')
            ->whereNull('kelas_siswa_id')
            ->whereHas('registration', function ($q) use ($tahunAktif) {
                $q->where('status', Registration::STATUS_PESERTA_DIDIK)
                    ->where('tahun_ajaran_id', $tahunAktif->id);
            })
            ->orderBy('nama')
            ->get(['id', 'registration_id', 'nama']);

        $isScopedKelas = !empty($kelasId) || $filterBelumKelas;

        $filterAktif = 0;
        if (!$isScopedKelas) {
            $filterAktif += (!empty($kelasId) || $filterBelumKelas) ? 1 : 0;
        }
        $filterAktif += !empty($jenisKelamin) ? 1 : 0;
        $filterAktif += !empty($hasilTes) ? 1 : 0;
        $filterAktif += ($order !== 'nama_asc') ? 1 : 0;

        $resetQuery = [];
        if ($filterBelumKelas) {
            $resetQuery['kelas_id'] = 'belum';
        } elseif (!empty($kelasId)) {
            $resetQuery['kelas_id'] = (int) $kelasId;
        }

        return view('admin.siswa.kelas1', compact(
            'siswa',
            'tahunAktif',
            'perPage',
            'kelasList',
            'search',
            'jenisKelamin',
            'hasilTes',
            'kelasId',
            'filterBelumKelas',
            'order',
            'totalSiswaAktif',
            'totalBelumMasukKelas',
            'siswaBelumKelas',
            'scopeLabel',
            'scopeCount',
            'scopeNormalizeCount',
            'isScopedKelas',
            'filterAktif',
            'resetQuery'
        ));
    }

    public function managementKelas(Request $request)
    {
        $tahunAktif = TahunAjaran::where('aktif', true)->first();
        if (!$tahunAktif) {
            return back()->with('error', 'Tidak ada Tahun Ajaran aktif.');
        }

        $kelasList = KelasSiswa::query()
            ->withCount([
                'siswa',
                'siswa as siswa_aktif_count' => function ($q) use ($tahunAktif) {
                    $q->whereHas('registration', function ($q2) use ($tahunAktif) {
                        $q2->where('status', Registration::STATUS_PESERTA_DIDIK)
                            ->where('tahun_ajaran_id', $tahunAktif->id);
                    });
                },
            ])
            ->orderBy('nama_kelas')
            ->get(['id', 'nama_kelas']);

        $siswaAktifQuery = Siswa::query()
            ->whereHas('registration', function ($q) use ($tahunAktif) {
                $q->where('status', Registration::STATUS_PESERTA_DIDIK)
                    ->where('tahun_ajaran_id', $tahunAktif->id);
            });

        $totalSiswaAktif = (clone $siswaAktifQuery)->count();
        $totalBelumMasukKelas = (clone $siswaAktifQuery)
            ->whereNull('kelas_siswa_id')
            ->count();

        $siswaBelumKelas = Siswa::query()
            ->with('registration')
            ->whereNull('kelas_siswa_id')
            ->whereHas('registration', function ($q) use ($tahunAktif) {
                $q->where('status', Registration::STATUS_PESERTA_DIDIK)
                    ->where('tahun_ajaran_id', $tahunAktif->id);
            })
            ->orderBy('nama')
            ->get(['id', 'registration_id', 'nama']);

        return view('admin.siswa.management-kelas', compact(
            'tahunAktif',
            'kelasList',
            'totalSiswaAktif',
            'totalBelumMasukKelas',
            'siswaBelumKelas'
        ));
    }

    public function storeKelas(Request $request)
    {
        $validated = $request->validate([
            'nama_kelas' => ['required', 'string', 'max:100', 'unique:kelas_siswa,nama_kelas'],
        ]);

        $kelas = KelasSiswa::create([
            'nama_kelas' => trim($validated['nama_kelas']),
        ]);

        logAktivitas(
            'Admin - Tambah Kelas',
            'Menambahkan kelas baru: ' . ($kelas->nama_kelas ?? '-') . ' (ID: ' . $kelas->id . ').'
        );

        return back()->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function assignKelas(Request $request, Siswa $siswa)
    {
        $validated = $request->validate([
            'kelas_siswa_id' => ['required', 'integer', Rule::exists('kelas_siswa', 'id')],
        ]);

        $tahunAktif = TahunAjaran::where('aktif', true)->first();

        if (!$tahunAktif) {
            return back()->with('error', 'Tidak ada Tahun Ajaran aktif.');
        }

        $siswa->load('registration');

        if (!$siswa->registration || (int) $siswa->registration->status !== Registration::STATUS_PESERTA_DIDIK) {
            return back()->with('error', 'Hanya data dengan status Peserta Didik yang dapat dimasukkan ke kelas.');
        }

        if ((int) $siswa->registration->tahun_ajaran_id !== (int) $tahunAktif->id) {
            return back()->with('error', 'Data siswa tidak berada di tahun ajaran aktif.');
        }

        $kelas = KelasSiswa::find($validated['kelas_siswa_id']);
        if (!$kelas) {
            return back()->with('error', 'Kelas tidak ditemukan.');
        }

        $kelasLama = optional($siswa->kelasSiswa)->nama_kelas ?? '-';
        $siswa->kelas_siswa_id = $kelas->id;
        $siswa->save();

        logAktivitas(
            'Admin - Assign Kelas Siswa',
            'Memasukkan siswa ' . ($siswa->nama ?? '-')
            . ' (ID: ' . $siswa->id
            . ', No Registrasi: ' . (optional($siswa->registration)->nomor_registrasi ?? '-')
            . ') dari kelas ' . $kelasLama
            . ' ke kelas ' . ($kelas->nama_kelas ?? '-') . '.'
        );

        return back()->with('success', 'Siswa berhasil dimasukkan ke kelas ' . ($kelas->nama_kelas ?? '-') . '.');
    }

    public function removeKelas(Siswa $siswa)
    {
        $tahunAktif = TahunAjaran::where('aktif', true)->first();

        if (!$tahunAktif) {
            return back()->with('error', 'Tidak ada Tahun Ajaran aktif.');
        }

        $siswa->load(['registration', 'kelasSiswa']);

        if (!$siswa->registration || (int) $siswa->registration->status !== Registration::STATUS_PESERTA_DIDIK) {
            return back()->with('error', 'Hanya data dengan status Peserta Didik yang dapat diubah kelasnya.');
        }

        if ((int) $siswa->registration->tahun_ajaran_id !== (int) $tahunAktif->id) {
            return back()->with('error', 'Data siswa tidak berada di tahun ajaran aktif.');
        }

        if (empty($siswa->kelas_siswa_id)) {
            return back()->with('info', 'Siswa ini belum memiliki kelas.');
        }

        $kelasLama = optional($siswa->kelasSiswa)->nama_kelas ?? '-';
        $siswa->kelas_siswa_id = null;
        $siswa->save();

        logAktivitas(
            'Admin - Keluarkan Siswa dari Kelas',
            'Mengeluarkan siswa ' . ($siswa->nama ?? '-')
            . ' (ID: ' . $siswa->id
            . ', No Registrasi: ' . (optional($siswa->registration)->nomor_registrasi ?? '-')
            . ') dari kelas ' . $kelasLama . '.'
        );

        return back()->with('success', 'Siswa berhasil dikeluarkan dari kelas ' . $kelasLama . '.');
    }

    public function updateKelas(Request $request, KelasSiswa $kelas)
    {
        $validated = $request->validate([
            'nama_kelas' => ['required', 'string', 'max:100', Rule::unique('kelas_siswa', 'nama_kelas')->ignore($kelas->id)],
        ]);

        $namaLama = $kelas->nama_kelas;
        $kelas->nama_kelas = trim($validated['nama_kelas']);
        $kelas->save();

        logAktivitas(
            'Admin - Ubah Nama Kelas',
            'Mengubah nama kelas ID ' . $kelas->id . ' dari ' . $namaLama . ' menjadi ' . $kelas->nama_kelas . '.'
        );

        return back()->with('success', 'Nama kelas berhasil diperbarui.');
    }

    public function destroyKelas(KelasSiswa $kelas)
    {
        $jumlahTerpakai = Siswa::query()
            ->where('kelas_siswa_id', $kelas->id)
            ->count();

        if ($jumlahTerpakai > 0) {
            return back()->with('error', 'Kelas tidak dapat dihapus karena masih dipakai oleh ' . $jumlahTerpakai . ' siswa.');
        }

        $namaKelas = $kelas->nama_kelas;
        $kelas->delete();

        logAktivitas(
            'Admin - Hapus Kelas',
            'Menghapus kelas ' . $namaKelas . ' (ID: ' . $kelas->id . ').'
        );

        return back()->with('success', 'Kelas ' . $namaKelas . ' berhasil dihapus.');
    }
}
