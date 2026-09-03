<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        *{ font-family:'Helvetica', 'Inter', Arial, sans-serif; }

        @page{
            size:215mm 330mm; /* F4 */
            margin:6mm;
        }

        body{
            margin:0;
            padding:0;
            padding-bottom:12mm;
            font-size:11px;
            color:#000;
        }

        .page{ position:relative; width:100%; page-break-after:always; }
        .page:last-child{ page-break-after:auto; }

        .header{
            border:1px solid #000;
            border-radius:10px;
            padding:0;
            margin-bottom:10px;
            background:#fff;
            position:relative;
        }

        .header-body{ padding:8px 12px 10px; }

        .meta{ width:100%; border-collapse:collapse; }
        .meta td{ padding:4px 6px; border:1px solid #000; vertical-align:top; }
        .meta .label{ width:22%; color:#000; font-weight:600; background:#f2f2f2; }

        .section{ margin-top:10px; border:1px solid #000; border-radius:10px; overflow:hidden; position:relative; }
        .section-title{ margin:0; padding:7px 10px; font-size:11px; font-weight:700; letter-spacing:.5px; color:#000; background:#e8e8e8; text-transform:uppercase; }

        table{ width:100%; border-collapse:collapse; }
        .grid td{ border:1px solid #000; padding:6px 8px; vertical-align:top; }
        .grid .k{ width:24%; background:#f2f2f2; color:#000; font-weight:600; }
        .grid .v{ width:26%; color:#000; }

        .muted{ color:#000; }
    </style>
</head>
<body>
@php
    $dash = '-';

    $display = function ($value) use ($dash) {
        if (is_null($value)) {
            return $dash;
        }

        $text = trim((string) $value);

        return $text !== '' ? $text : $dash;
    };

    $formatDate = function ($value, string $format = 'Y-m-d') use ($dash) {
        if (is_null($value) || trim((string) $value) === '') {
            return $dash;
        }

        return \Carbon\Carbon::parse($value)->format($format);
    };

    $withUnit = function ($value, string $unit) use ($display, $dash) {
        $formatted = $display($value);
        if ($formatted === $dash) {
            return $dash;
        }
        return $formatted . ' ' . $unit;
    };

    $toLabel = function ($value) use ($dash) {
        if (is_null($value) || trim((string) $value) === '') {
            return $dash;
        }

        return ucwords(str_replace('_', ' ', (string) $value));
    };
@endphp

@foreach($rows as $index => $item)
    @php
        $registration = optional($item->registration);
        $alamat = optional($item->alamat);
        $ayah = optional($item->ayah);
        $ibu = optional($item->ibu);
        $wali = optional($item->wali);
        $dataPendukung = optional($item->dataPendukung);
        $kelasLabel = $display(optional($item->kelasSiswa)->nama_kelas ?? 'Belum Masuk Kelas');

        $tanggalDaftar = $registration->tanggal_daftar
            ? $formatDate($registration->tanggal_daftar, 'd-m-Y')
            : $dash;

        $tanggalLahir = $item->tanggal_lahir
            ? $formatDate($item->tanggal_lahir, 'd-m-Y')
            : $dash;

        $tinggalBersama = $toLabel($item->tinggal_bersama ?? null);
        $transportasi = $toLabel($item->transportasi ?? null);

        $berkebutuhanKhususDisplay = $dash;
        if (!is_null($item->berkebutuhan_khusus) && trim((string) $item->berkebutuhan_khusus) !== '' && trim((string) $item->berkebutuhan_khusus) !== 'Tidak') {
            $berkebutuhanKhususDisplay = 'Ya, ' . trim((string) $item->berkebutuhan_khusus);
        } else {
            $berkebutuhanKhususDisplay = 'Tidak';
        }

        $kontakUtama = ($item->tinggal_bersama === 'wali')
            ? ($wali->no_hp ?? $dash)
            : ($ibu->no_hp ?? $dash);

        $namaTk = $dash;
        if ($dataPendukung->is_tk_manual) {
            $namaTk = $display($dataPendukung->nama_tk_manual ?? null);
        } elseif ($dataPendukung->paudTk) {
            $namaTk = $display(optional($dataPendukung->paudTk)->nama ?? null);
        }

        $alamatTk = $display($dataPendukung->alamat_tk ?? null);
        $asalTkPaud = $namaTk;
        if ($namaTk !== $dash && $alamatTk !== $dash) {
            $asalTkPaud = $namaTk . ' - ' . $alamatTk;
        } elseif ($alamatTk !== $dash) {
            $asalTkPaud = $alamatTk;
        }

        $tahunAjaran = $display(optional($tahunAktif)->nama ?? null);
        $tanggalCetak = now()->locale('id')->translatedFormat('l d F Y \j\a\m H.i');
    @endphp

    <div class="page">
        <div class="header">
            <div class="header-body">
                <table class="meta">
                    <tr>
                        <td class="label">Nomor Registrasi</td>
                        <td>{{ $display($registration->nomor_registrasi ?? null) }}</td>
                        <td class="label">Tanggal Daftar</td>
                        <td>{{ $tanggalDaftar }}</td>
                    </tr>
                    <tr>
                        <td class="label">Tahun Ajaran</td>
                        <td>{{ $tahunAjaran }}</td>
                        <td class="label">Hasil Tes</td>
                        <td>{{ $display($item->hasil_tes ?? null) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Kelas</td>
                        <td>{{ $kelasLabel }}</td>
                        <td class="label">Status Registrasi</td>
                        <td>{{ $display(\App\Models\Registration::statusLabel($registration->status ?? null) ?? null) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Input By</td>
                        <td>{{ $display($registration->input_by ?? null) }}</td>
                        <td class="label">Tanggal Cetak</td>
                        <td>{{ $tanggalCetak }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="section">
            <h3 class="section-title">IDENTITAS PESERTA DIDIK</h3>
            <table class="grid">
                <tr>
                    <td class="k">Nama Lengkap</td><td class="v">{{ $display($item->nama ?? null) }}</td>
                    <td class="k">Agama</td><td class="v">{{ $display($item->agama ?? null) }}</td>
                </tr>
                <tr>
                    <td class="k">NISN</td><td class="v" colspan="3">{{ $display($item->nisn ?? null) }}</td>
                </tr>
                <tr>
                    <td class="k">NIK</td><td class="v">{{ $display($item->nik ?? null) }}</td>
                    <td class="k">Berkebutuhan Khusus</td><td class="v">{{ $berkebutuhanKhususDisplay }}</td>
                </tr>
                <tr>
                    <td class="k">Tempat Lahir</td><td class="v">{{ $display($item->tempat_lahir ?? null) }}</td>
                    <td class="k">Tinggal Bersama</td><td class="v">{{ $tinggalBersama }}</td>
                </tr>
                <tr>
                    <td class="k">Tanggal Lahir</td><td class="v">{{ $tanggalLahir }}</td>
                    <td class="k">Moda Transportasi</td><td class="v">{{ $transportasi }}</td>
                </tr>
                <tr>
                    <td class="k">No Akta Lahir</td><td class="v">{{ $display($item->akta_no ?? null) }}</td>
                    <td class="k">No KKS</td><td class="v">{{ $display($item->no_kks ?? null) }}</td>
                </tr>
                <tr>
                    <td class="k">Kewarganegaraan</td><td class="v">{{ $display($item->kewarganegaraan ?? null) }}</td>
                    <td class="k">KPS / PKH</td><td class="v">{{ $display($item->kps ?? null) }}</td>
                </tr>
                <tr>
                    <td class="k">No KK</td><td class="v">{{ $display($item->no_kk ?? null) }}</td>
                    <td class="k">Peserta KIP</td><td class="v">{{ $display($item->kip ?? null) }}</td>
                </tr>
                <tr>
                    <td class="k">Jenis Kelamin</td><td class="v">{{ $toLabel($item->jenis_kelamin ?? null) }}</td>
                    <td class="k">Layak PIP</td><td class="v">{{ $display($item->layak_pip ?? null) }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h3 class="section-title">ALAMAT PESERTA DIDIK</h3>
            <table class="grid">
                <tr>
                    <td class="k">Alamat Lengkap</td><td class="v" colspan="3">{{ $display($alamat->alamat ?? null) }}</td>
                </tr>
                <tr>
                    <td class="k">Kelurahan</td><td class="v">{{ $display($alamat->kelurahan ?? null) }}</td>
                    <td class="k">Kecamatan</td><td class="v">{{ $display($alamat->kecamatan ?? null) }}</td>
                </tr>
                <tr>
                    <td class="k">Kabupaten</td><td class="v">{{ $display($alamat->kabupaten ?? null) }}</td>
                    <td class="k">Provinsi</td><td class="v">{{ $display($alamat->provinsi ?? null) }}</td>
                </tr>
                <tr>
                    <td class="k">RT / RW</td><td class="v">{{ $display($alamat->rt ?? null) }} / {{ $display($alamat->rw ?? null) }}</td>
                    <td class="k">Kode Pos</td><td class="v">{{ $display($alamat->kode_pos ?? null) }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h3 class="section-title">DATA ORANG TUA / WALI</h3>
            <table class="grid">
                <tr>
                    <td class="k">Nama Ayah</td><td class="v">{{ $display($ayah->nama ?? null) }}</td>
                    <td class="k">Nama Ibu</td><td class="v">{{ $display($ibu->nama ?? null) }}</td>
                </tr>
                <tr>
                    <td class="k">No HP Ayah</td><td class="v">{{ $display($ayah->no_hp ?? null) }}</td>
                    <td class="k">No HP Ibu</td><td class="v">{{ $display($ibu->no_hp ?? null) }}</td>
                </tr>
                <tr>
                    <td class="k">NIK Ayah</td><td class="v">{{ $display($ayah->nik ?? null) }}</td>
                    <td class="k">NIK Ibu</td><td class="v">{{ $display($ibu->nik ?? null) }}</td>
                </tr>
                <tr>
                    <td class="k">Tahun Lahir Ayah</td><td class="v">{{ $display($ayah->tahun_lahir ?? null) }}</td>
                    <td class="k">Tahun Lahir Ibu</td><td class="v">{{ $display($ibu->tahun_lahir ?? null) }}</td>
                </tr>
                <tr>
                    <td class="k">Pendidikan Ayah</td><td class="v">{{ $display($ayah->pendidikan ?? null) }}</td>
                    <td class="k">Pendidikan Ibu</td><td class="v">{{ $display($ibu->pendidikan ?? null) }}</td>
                </tr>
                <tr>
                    <td class="k">Pekerjaan Ayah</td><td class="v">{{ $display($ayah->pekerjaan ?? null) }}</td>
                    <td class="k">Pekerjaan Ibu</td><td class="v">{{ $display($ibu->pekerjaan ?? null) }}</td>
                </tr>
                <tr>
                    <td class="k">Penghasilan Ayah</td><td class="v">{{ $display($ayah->penghasilan ?? null) }}</td>
                    <td class="k">Penghasilan Ibu</td><td class="v">{{ $display($ibu->penghasilan ?? null) }}</td>
                </tr>
                <tr>
                    <td class="k">Nama Wali</td><td class="v">{{ $display($wali->nama ?? null) }}</td>
                    <td class="k">Hubungan Wali</td><td class="v">{{ $display($wali->hubungan ?? null) }}</td>
                </tr>
                <tr>
                    <td class="k">No HP Wali</td><td class="v">{{ $display($wali->no_hp ?? null) }}</td>
                    <td class="k">Kontak Utama</td><td class="v">{{ $display($kontakUtama) }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h3 class="section-title">DATA PENDUKUNG PESERTA DIDIK</h3>
            <table class="grid">
                <tr>
                    <td class="k">Tinggi - Berat Badan</td><td class="v">{{ $withUnit($dataPendukung->tinggi ?? null, 'cm') }} - {{ $withUnit($dataPendukung->berat ?? null, 'kg') }}</td>
                    <td class="k">Anak Ke (Berdasarkan KK)</td><td class="v">{{ $display($dataPendukung->anak_ke ?? null) }}</td>
                </tr>
                <tr>
                    <td class="k">Jarak ke Sekolah</td><td class="v">{{ $withUnit($dataPendukung->jarak ?? null, 'km') }}</td>
                    <td class="k">Jumlah Saudara</td><td class="v">{{ $display($dataPendukung->jumlah_saudara ?? null) }}</td>
                </tr>
                <tr>
                    <td class="k">Hobi</td><td class="v">{{ $display($dataPendukung->hobi ?? null) }}</td>
                    <td class="k">Cita-cita</td><td class="v">{{ $display($dataPendukung->cita_cita ?? null) }}</td>
                </tr>
                <tr>
                    <td class="k">Asal TK/PAUD (Nama & Alamat)</td><td class="v" colspan="3">{{ $asalTkPaud }}</td>
                </tr>
            </table>
        </div>
    </div>
@endforeach
</body>
</html>
