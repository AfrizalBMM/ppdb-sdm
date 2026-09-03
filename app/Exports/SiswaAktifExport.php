<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SiswaAktifExport implements FromCollection, WithHeadings, ShouldAutoSize, WithColumnFormatting, WithStyles
{
    private bool $includeWali;

    // Map: column letter => index (0-based) for columns that must be TEXT
    // NIK (siswa) = J, No KK = K, Ayah-NIK = AG, Ibu-NIK = AO, Wali-NIK = AW (approx)
    // We apply text format by letter ranges dynamically in styles().

    public function __construct(private readonly Collection $rows)
    {
        $this->includeWali = $this->rows->contains(function ($item) {
            if (method_exists($item, 'relationLoaded') && $item->relationLoaded('wali')) {
                return (bool) $item->getRelation('wali');
            }

            return !empty($item->wali);
        });
    }

    public function headings(): array
    {
        $headings = [
            'No',
            'No Registrasi',
            'Tanggal Daftar',
            'Tahun Ajaran',
            'Status',
            'Input By',
            'Kelas',

            'Nama Peserta Didik',
            'Jenis Kelamin',
            'NIK',
            'No KK',
            'Tempat Lahir',
            'Tanggal Lahir',
            'No Akta',
            'Agama',
            'Kewarganegaraan',
            'Berkebutuhan Khusus',
            'Tinggal Bersama',
            'Transportasi',
            'No KKS',
            'KPS',
            'KIP',
            'Layak PIP',

            'Alamat',
            'Provinsi',
            'Kabupaten',
            'Kecamatan',
            'Kelurahan',
            'RT',
            'RW',
            'Kode Pos',

            'Ayah - Nama',
            'Ayah - NIK',
            'Ayah - No HP',
            'Ayah - Tahun Lahir',
            'Ayah - Pendidikan',
            'Ayah - Pekerjaan',
            'Ayah - Pekerjaan Lainnya',
            'Ayah - Penghasilan',

            'Ibu - Nama',
            'Ibu - NIK',
            'Ibu - No HP',
            'Ibu - Tahun Lahir',
            'Ibu - Pendidikan',
            'Ibu - Pekerjaan',
            'Ibu - Pekerjaan Lainnya',
            'Ibu - Penghasilan',

            'Tinggi',
            'Berat',
            'Jarak',
            'Jumlah Saudara',
            'Anak Ke',
            'PAUD/TK (Referensi)',
            'PAUD/TK Manual?',
            'Nama TK Manual',
            'Alamat TK',
            'Hobi',
            'Cita-cita',

            'Hasil Tes',
        ];

        if ($this->includeWali) {
            $headings = array_merge($headings, [
                'Wali - Nama',
                'Wali - Hubungan',
                'Wali - Hubungan Lainnya',
                'Wali - No HP',
                'Wali - NIK',
                'Wali - Tahun Lahir',
                'Wali - Pendidikan',
                'Wali - Pekerjaan',
                'Wali - Pekerjaan Lainnya',
                'Wali - Penghasilan',
            ]);
        }

        return $headings;
    }

    /**
     * Columns (0-based index) whose values should be exported as plain text strings.
     * This prevents Excel from mangling long numeric strings (NIK, No KK, No HP, etc.).
     */
    private function textColumnIndexes(): array
    {
        // 0-based column positions matching the headings() order:
        // J=9  NIK siswa
        // K=10 No KK
        // U=20 KPS
        // V=21 KIP
        // AE=31 + offset? Let's count carefully:
        // 0  No
        // 1  No Registrasi
        // 2  Tanggal Daftar
        // 3  Tahun Ajaran
        // 4  Status
        // 5  Input By
        // 6  Kelas
        // 7  Nama Peserta Didik
        // 8  Jenis Kelamin
        // 9  NIK  <-- TEXT
        // 10 No KK  <-- TEXT
        // 11 Tempat Lahir
        // 12 Tanggal Lahir
        // 13 No Akta
        // 14 Agama
        // 15 Kewarganegaraan
        // 16 Berkebutuhan Khusus
        // 17 Tinggal Bersama
        // 18 Transportasi
        // 19 No KKS  <-- TEXT
        // 20 KPS
        // 21 KIP
        // 22 Layak PIP
        // 23 Alamat
        // 24 Provinsi
        // 25 Kabupaten
        // 26 Kecamatan
        // 27 Kelurahan
        // 28 RT
        // 29 RW
        // 30 Kode Pos  <-- TEXT
        // 31 Ayah - Nama
        // 32 Ayah - NIK  <-- TEXT
        // 33 Ayah - No HP  <-- TEXT
        // 34 Ayah - Tahun Lahir
        // 35 Ayah - Pendidikan
        // 36 Ayah - Pekerjaan
        // 37 Ayah - Pekerjaan Lainnya
        // 38 Ayah - Penghasilan
        // 39 Ibu - Nama
        // 40 Ibu - NIK  <-- TEXT
        // 41 Ibu - No HP  <-- TEXT
        // 42 Ibu - Tahun Lahir
        // ... rest non-critical
        // Wali (if included):
        // base+3  Wali - No HP  <-- TEXT
        // base+4  Wali - NIK  <-- TEXT
        $base = 57; // index of 'Hasil Tes' + 1 = index of first Wali col
        $indexes = [9, 10, 19, 30, 32, 33, 40, 41];

        if ($this->includeWali) {
            $indexes[] = $base + 3; // Wali - No HP
            $indexes[] = $base + 4; // Wali - NIK
        }

        return $indexes;
    }

    /**
     * Convert a 0-based column index to Excel column letter (A, B, ... Z, AA, AB ...)
     */
    private function colLetter(int $index): string
    {
        $letter = '';
        $index++;
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $index = (int)(($index - $mod) / 26);
        }
        return $letter;
    }

    public function columnFormats(): array
    {
        $formats = [];
        foreach ($this->textColumnIndexes() as $idx) {
            $formats[$this->colLetter($idx)] = NumberFormat::FORMAT_TEXT;
        }
        return $formats;
    }

    public function styles(Worksheet $sheet): void
    {
        // Extra safety: set cell data type to string explicitly for text columns
        // PhpSpreadsheet will respect FORMAT_TEXT from columnFormats()
        // so styles() here is mainly for header row bolding.
        $sheet->getStyle('1')->getFont()->setBold(true);
    }

    public function collection(): Collection
    {
        return $this->rows->values()->map(function ($item, $index) {
            $registration  = $item->registration;
            $alamat        = $item->alamat;
            $ayah          = $item->ayah;
            $ibu           = $item->ibu;
            $wali          = $item->wali;
            $dataPendukung = $item->dataPendukung;

            // Helper: force a value to string so PhpSpreadsheet writes it as text
            $str = fn($val, $fallback = '-') => (string) ($val ?? $fallback);

            // Helper for NIK/numeric strings: prefix with apostrophe to force Excel text mode
            $nik = fn($val) => $val ? "'" . $val : '-';

            $row = [
                $index + 1,
                $str(optional($registration)->nomor_registrasi),
                optional($registration?->tanggal_daftar)->format('Y-m-d') ?? '-',
                $str(optional($registration?->tahunAjaran)->nama),
                \App\Models\Registration::statusLabel(optional($registration)->status),
                $str(optional($registration)->input_by),
                $str(optional($item->kelasSiswa)->nama_kelas, 'Belum Masuk Kelas'),

                $str($item->nama),
                ui_label($item->jenis_kelamin),
                $nik($item->nik),          // NIK → text
                $nik($item->no_kk),        // No KK → text
                $str($item->tempat_lahir),
                optional($item->tanggal_lahir)->format('Y-m-d') ?? '-',
                $str($item->akta_no),
                $str($item->agama),
                $str($item->kewarganegaraan),
                $str($item->berkebutuhan_khusus),
                $str($item->tinggal_bersama),
                $str($item->transportasi),
                $nik($item->no_kks),       // No KKS → text
                $nik($item->kps),
                $nik($item->kip),
                $str($item->layak_pip),

                $str(optional($alamat)->alamat),
                $str(optional($alamat)->provinsi),
                $str(optional($alamat)->kabupaten),
                $str(optional($alamat)->kecamatan),
                $str(optional($alamat)->kelurahan),
                $str(optional($alamat)->rt),
                $str(optional($alamat)->rw),
                $str(optional($alamat)->kode_pos), // Kode Pos → text

                $str(optional($ayah)->nama),
                $nik(optional($ayah)->nik),         // Ayah NIK → text
                $str(optional($ayah)->no_hp),       // Ayah No HP → text
                $str(optional($ayah)->tahun_lahir),
                $str(optional($ayah)->pendidikan),
                $str(optional($ayah)->pekerjaan),
                $str(optional($ayah)->pekerjaan_lainnya),
                $str(optional($ayah)->penghasilan),

                $str(optional($ibu)->nama),
                $nik(optional($ibu)->nik),          // Ibu NIK → text
                $str(optional($ibu)->no_hp),        // Ibu No HP → text
                $str(optional($ibu)->tahun_lahir),
                $str(optional($ibu)->pendidikan),
                $str(optional($ibu)->pekerjaan),
                $str(optional($ibu)->pekerjaan_lainnya),
                $str(optional($ibu)->penghasilan),

                $str(optional($dataPendukung)->tinggi),
                $str(optional($dataPendukung)->berat),
                $str(optional($dataPendukung)->jarak),
                $str(optional($dataPendukung)->jumlah_saudara),
                $str(optional($dataPendukung)->anak_ke),
                $str(optional($dataPendukung?->paudTk)->nama),
                $str(optional($dataPendukung)->is_tk_manual),
                $str(optional($dataPendukung)->nama_tk_manual),
                $str(optional($dataPendukung)->alamat_tk),
                $str(optional($dataPendukung)->hobi),
                $str(optional($dataPendukung)->cita_cita),

                $str($item->hasil_tes),
            ];

            if ($this->includeWali) {
                $row = array_merge($row, [
                    $str(optional($wali)->nama, ''),
                    $str(optional($wali)->hubungan, ''),
                    $str(optional($wali)->hubungan_lainnya, ''),
                    $str(optional($wali)->no_hp, ''),       // Wali No HP → text
                    $nik(optional($wali)->nik),         // Wali NIK → text
                    $str(optional($wali)->tahun_lahir, ''),
                    $str(optional($wali)->pendidikan, ''),
                    $str(optional($wali)->pekerjaan, ''),
                    $str(optional($wali)->pekerjaan_lainnya, ''),
                    $str(optional($wali)->penghasilan, ''),
                ]);
            }

            return $row;
        });
    }
}
