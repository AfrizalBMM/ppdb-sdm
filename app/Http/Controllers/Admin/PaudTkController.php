<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaudTk;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PaudTkController extends Controller
{
    private const ALLOWED_JENIS = ['PAUD', 'TK', 'BA', 'RA'];

    /**
     * Parse nilai EMIS/DAPODIK dari sel Excel: angka 1/0, teks ya/tidak/true/false.
     */
    private static function parseEmisDapodik($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $value = strtolower(trim((string) $value));

        return in_array($value, ['1', 'ya', 'yes', 'true', 'v', 'x'], true);
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 30);
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 30;
        }

        return view('admin.paud-tk.index', [
            'data' => PaudTk::orderBy('nama')->paginate($perPage)->withQueryString(),
            'perPage' => $perPage,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'  => 'required|string|max:255',
            'jenis' => 'required|in:PAUD,TK,BA,RA',
            'npsn'  => 'nullable|string|max:20',
            'telp'  => 'nullable|string|max:20',
        ]);

        $data = $request->only([
            'npsn','nama','jenis','alamat',
            'kelurahan','kecamatan','telp','akreditasi'
        ]);

        $data['aktif'] = $request->boolean('aktif');
        $data['is_emis_dapodik'] = $request->boolean('is_emis_dapodik');

        $paud = PaudTk::create($data);

        logAktivitas(
            'Kelola PAUD/TK',
            'Menambahkan PAUD/TK baru: "'.$paud->nama.'" ('.$paud->jenis.')'
            .($paud->is_emis_dapodik ? ' [EMIS/DAPODIK]' : '')
        );

        return back()->with('success', 'Data PAUD/TK berhasil disimpan!');
    }

    public function toggle(PaudTk $paudTk)
    {
        $paudTk->update([
            'aktif' => !$paudTk->aktif
        ]);

        logAktivitas(
            'Kelola PAUD/TK',
            ($paudTk->aktif ? 'Mengaktifkan' : 'Menonaktifkan').
            ' PAUD/TK #'.$paudTk->id.' "'.$paudTk->nama.'"'
        );

        return back();
    }

    public function toggleEmisDapodik(PaudTk $paudTk)
    {
        $paudTk->update([
            'is_emis_dapodik' => !$paudTk->is_emis_dapodik
        ]);

        logAktivitas(
            'Kelola PAUD/TK',
            'Mengubah status EMIS/DAPODIK PAUD/TK #'.$paudTk->id.' "'.$paudTk->nama.'" menjadi '
            .($paudTk->is_emis_dapodik ? 'terdaftar' : 'tidak terdaftar')
        );

        return back()->with('success', 'Status EMIS/DAPODIK "'.$paudTk->nama.'" berhasil diperbarui.');
    }

    public function destroy(PaudTk $paudTk)
    {
        // ❗ Jangan hapus jika sudah dipakai siswa
        if ($paudTk->dataPendukung()->exists()) {
            return back()->with('error','PAUD/TK sudah digunakan oleh siswa dan tidak dapat dihapus.');
        }

        $nama = $paudTk->nama;
        $id   = $paudTk->id;

        $paudTk->delete();

        logAktivitas(
            'Kelola PAUD/TK',
            'Menghapus PAUD/TK #'.$id.' "'.$nama.'"'
        );

        return back();
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        $rows = Excel::toArray([], $request->file('file'))[0] ?? [];

        $countNew = 0;
        $countUpdated = 0;
        $countInvalidJenis = 0;
        $invalidJenisSamples = [];

        // Deteksi format file dari header:
        // - Format baru (10 kolom): npsn..akreditasi, emis_dapodik (I), aktif (J)
        // - Format lama  (9 kolom): npsn..akreditasi, aktif (I)
        $header = array_map(fn ($h) => strtolower(trim((string) ($h ?? ''))), $rows[0] ?? []);
        $hasEmisColumn = in_array('emis_dapodik', $header, true)
            || in_array('emis/dapodik', $header, true)
            || in_array('emis', $header, true);

        foreach ($rows as $index => $row) {

            // skip header
            if ($index == 0) continue;

            if (empty($row[1])) continue; // kolom nama (B)

            $jenisRaw = strtoupper(trim((string) ($row[2] ?? '')));
            if ($jenisRaw === '' || !in_array($jenisRaw, self::ALLOWED_JENIS, true)) {
                $countInvalidJenis++;
                if (count($invalidJenisSamples) < 5) {
                    // +1 to show human-friendly row number (Excel row)
                    $invalidJenisSamples[] = 'baris ' . ($index + 1) . ' (jenis: ' . (($row[2] ?? '') === '' ? '-' : $row[2]) . ')';
                }
                continue;
            }

            // Posisi kolom sesuai format file.
            if ($hasEmisColumn) {
                $emisValue = $row[8] ?? null;
                $aktifValue = $row[9] ?? null;
            } else {
                // Format lama: kolom I = aktif, EMIS tidak ada (default false).
                $emisValue = null;
                $aktifValue = $row[8] ?? null;
            }

            $paud = PaudTk::firstOrNew([
                'nama'       => $row[1],
                'kelurahan'  => $row[4] ?? null,
                'kecamatan'  => $row[5] ?? null
            ]);

            $isNew = !$paud->exists;

            $paud->npsn       = $row[0] ?? null;
            $paud->jenis      = $jenisRaw;
            $paud->alamat     = $row[3] ?? null;
            $paud->kelurahan  = $row[4] ?? null;
            $paud->kecamatan  = $row[5] ?? null;
            $paud->telp       = $row[6] ?? null;
            $paud->akreditasi = $row[7] ?? null;
            $paud->is_emis_dapodik = $hasEmisColumn
                ? self::parseEmisDapodik($emisValue)
                : ($isNew ? false : $paud->is_emis_dapodik); // file lama: pertahankan nilai existing
            $paud->aktif      = $aktifValue === null ? true : (bool) $aktifValue;

            $paud->save();

            $isNew ? $countNew++ : $countUpdated++;
        }

        logAktivitas(
            'Import PAUD/TK',
            "Import file Excel: {$request->file('file')->getClientOriginalName()} | ".
            "$countNew data baru, $countUpdated data diperbarui".
            ($countInvalidJenis > 0 ? " | $countInvalidJenis baris dilewati (jenis tidak valid)" : "")
        );

        $message = "Import selesai! $countNew data baru, $countUpdated data diperbarui.";
        if ($countInvalidJenis > 0) {
            $message .= " $countInvalidJenis baris dilewati karena jenis tidak valid (boleh: ".implode(',', self::ALLOWED_JENIS).').';
            if (!empty($invalidJenisSamples)) {
                $message .= ' Contoh: ' . implode(', ', $invalidJenisSamples) . '.';
            }
        }

        return back()->with('success', $message);
    }


    public function template(): BinaryFileResponse
    {
        // Generate template on-the-fly agar selalu sesuai format import terbaru.
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('PAUD_TK');

        $headers = ['npsn', 'nama', 'jenis', 'alamat', 'kelurahan', 'kecamatan', 'telp', 'akreditasi', 'emis_dapodik', 'aktif'];

        $contoh = [
            ['12345678', 'TK Harapan Bangsa', 'TK', 'Jl. Merdeka No 10', 'Banjarsari', 'Laweyan', '0271-123456', 'A', 1, 1],
            ['87654321', 'PAUD Ceria Anak', 'PAUD', 'Jl. Mawar No 5', 'Manahan', 'Banjarsari', '0271-654321', 'B', 0, 1],
            ['55667788', 'RA Pelita Hati', 'RA', 'Jl. Anggrek No 15', 'Pajang', 'Laweyan', '0271-777888', 'Belum', 1, 1],
        ];

        // Header styling
        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray($contoh, null, 'A2');

        $lastCol = $sheet->getHighestColumn();
        $sheet->getStyle('A1:'.$lastCol.'1')->getFont()->setBold(true);
        $sheet->getStyle('A1:'.$lastCol.'1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE2E8F0');

        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Catatan format di bawah contoh
        $sheet->setCellValue('A6', 'Catatan: jenis = PAUD/TK/BA/RA; emis_dapodik & aktif = 1 (ya) / 0 (tidak); baris contoh boleh dihapus.');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $filename = 'template-paud-tk-'.now()->format('Ymd-His').'.xlsx';
        $path = storage_path('app/'.$filename);
        $writer->save($path);

        return response()->download($path, 'template-paud-tk.xlsx')->deleteFileAfterSend();
    }

    public function destroyAll()
    {
        // ❗ Hanya hapus yang belum pernah dipakai
        $count = PaudTk::doesntHave('dataPendukung')->count();

        PaudTk::doesntHave('dataPendukung')->delete();

        logAktivitas(
            'Kelola PAUD/TK',
            "Menghapus $count data PAUD/TK yang belum digunakan"
        );

        return back()->with('success','Data yang belum digunakan berhasil dihapus.');
    }
}