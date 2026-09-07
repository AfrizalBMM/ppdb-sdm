<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Formulir Pendaftaran</title>

<style>

*{
    font-family:'Helvetica', 'Inter', Arial, sans-serif;
}

@page{
    size:215mm 330mm;
    margin:10mm;
}

body{
    margin:0;
    padding:0;
    font-size:11px;
    color:#000;
    line-height:1.3;
}

.page{
    position:relative;
    width:100%;
}

.watermark{
    position:fixed;
    top:50%;
    left:50%;
    width:120mm;
    margin-left:-60mm;
    margin-top:-60mm;
    opacity:0.045;
    z-index:0;
}

.header{
    border:1.2px solid #000;
    border-radius:8px;
    padding:0;
    margin-bottom:8px;
    background:#fff;
    position:relative;
    z-index:2;
}

.header-title{
    font-size:13px;
    font-weight:700;
    font-family:'Helvetica', 'Inter', Arial, sans-serif;
    color:#1e3a8a;
    letter-spacing:.3px;
    margin:0;
    padding-bottom:5px;
    text-align:center;
}

.header-subtitle{
    margin:0;
    font-size:10.5px;
    color:#000;
}

.header-body{
    padding:7px 10px 9px;
}

.meta{
    width:100%;
    border-collapse:collapse;
    margin-top:7px;
}

.meta td{
    padding:4px 7px;
    border:1px solid #000;
    vertical-align:top;
    font-size:11px;
}

.meta .label{
    width:22%;
    color:#000;
    font-weight:600;
    background:#f2f2f2;
}

.section{
    margin-top:8px;
    border:1.2px solid #000;
    border-radius:8px;
    overflow:hidden;
    position:relative;
    z-index:2;
}

.section-title{
    margin:0;
    padding:6px 9px;
    font-size:11px;
    font-weight:700;
    font-family:'Helvetica', 'Inter', Arial, sans-serif;
    letter-spacing:.5px;
    color:#000;
    background:#e8e8e8;
    text-transform:uppercase;
}

.kop-full{
    width:100%;
    height:auto;
    display:block;
    border-bottom:1px solid #000;
}

.grid{
    width:100%;
    border-collapse:collapse;
}

.grid td{
    border:1px solid #000;
    padding:5px 8px;
    vertical-align:top;
    font-size:11px;
}

.grid .k{
    width:24%;
    background:#f2f2f2;
    color:#000;
    font-weight:600;
}

.grid .v{
    width:26%;
    color:#000;
}

.footer{
    margin-top:12px;
    width:100%;
    border-collapse:collapse;
    position:relative;
    z-index:2;
}

.footer td{
    vertical-align:top;
    width:50%;
    text-align:center;
    padding-top:4px;
    font-size:11px;
}

.muted{
    color:#000;
}

.signature{
    margin-top:55px;
    font-weight:700;
    font-size:11px;
}

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

    $withUnit = function ($value, $unit) use ($display, $dash) {
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

    $alamat = optional($siswa->alamat);
    $ayah = optional($siswa->ayah);
    $ibu = optional($siswa->ibu);
    $wali = optional($siswa->wali);
    $dataPendukung = optional($siswa->dataPendukung);
    $registration = optional($siswa->registration);

    $tanggalDaftar = $registration->tanggal_daftar
        ? \Carbon\Carbon::parse($registration->tanggal_daftar)->format('d-m-Y')
        : $dash;

    $tanggalLahir = $siswa->tanggal_lahir
        ? \Carbon\Carbon::parse($siswa->tanggal_lahir)->format('d-m-Y')
        : $dash;

    $tinggalBersama = $toLabel($siswa->tinggal_bersama);
    $transportasi = $toLabel($siswa->transportasi);

    $berkebutuhanKhususDisplay = $dash;
    if (!is_null($siswa->berkebutuhan_khusus) && trim((string) $siswa->berkebutuhan_khusus) !== '' && trim((string) $siswa->berkebutuhan_khusus) !== 'Tidak') {
        $berkebutuhanKhususDisplay = 'Ya, ' . trim((string) $siswa->berkebutuhan_khusus);
    } else {
        $berkebutuhanKhususDisplay = 'Tidak';
    }

    $kontakUtama = $siswa->tinggal_bersama === 'wali'
        ? ($wali->no_hp ?? $dash)
        : ($ibu->no_hp ?? $dash);

    $namaTk = $dash;
    if (!empty($dataPendukung->nama_tk_manual)) {
        $namaTk = $display($dataPendukung->nama_tk_manual);
    } elseif ($dataPendukung->paudTk) {
        $namaTk = $display($dataPendukung->paudTk->nama ?? null);
    }

    $alamatTk = $display($dataPendukung->alamat_tk ?? null);
    $asalTkPaud = $namaTk;
    if ($namaTk !== $dash && $alamatTk !== $dash) {
        $asalTkPaud = $namaTk . ' - ' . $alamatTk;
    } elseif ($alamatTk !== $dash) {
        $asalTkPaud = $alamatTk;
    }

    $panitia = $display($petugas ?? null);

    $tahunAjaran = $display(optional($registration->tahunAjaran)->nama ?? null);
    $tahunAjaranAktif = \App\Models\TahunAjaran::where('aktif', true)->value('nama');
    $tahunAjaranJudul = $display($tahunAjaranAktif ?? optional($registration->tahunAjaran)->nama ?? null);

    $tanggalCetak = now()
        ->locale('id')
        ->translatedFormat('l d F Y \\j\\a\\m H.i');
@endphp

<div class="page">
    <img src="{{ public_path('images/logo.png') }}" alt="Watermark Logo" class="watermark">

    <div class="header">
        <img src="{{ public_path('images/kopsdm.png') }}" alt="Kop SD Muhammadiyah Wonorejo" class="kop-full">

        <div class="header-body">
            <h1 class="header-title">FORMULIR PESERTA DIDIK SD MUHAMMADIYAH WONOREJO TAHUN AJARAN {{ $tahunAjaranJudul }}</h1>

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
                    <td>{{ $display($siswa->hasil_tes ?? null) }}</td>
                </tr>
                <tr>
                    <td class="label">Panitia Penerima</td>
                    <td>{{ $panitia }}</td>
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
                <td class="k">Nama Lengkap</td><td class="v" colspan="3">{{ $display($siswa->nama ?? null) }}</td>
            </tr>
            <tr>
                <td class="k">Agama</td><td class="v">{{ $display($siswa->agama ?? 'Islam') }}</td>
                <td class="k">NISN</td><td class="v">{{ $display($siswa->nisn ?? null) }}</td>
            </tr>
            <tr>
                <td class="k">NIK</td><td class="v">{{ $display($siswa->nik ?? null) }}</td>
                <td class="k">Berkebutuhan Khusus</td><td class="v">{{ $berkebutuhanKhususDisplay }}</td>
            </tr>
            <tr>
                <td class="k">Tempat Lahir</td><td class="v">{{ $display($siswa->tempat_lahir ?? null) }}</td>
                <td class="k">Tinggal Bersama</td><td class="v">{{ $tinggalBersama }}</td>
            </tr>
            <tr>
                <td class="k">Tanggal Lahir</td><td class="v">{{ $tanggalLahir }}</td>
                <td class="k">Moda Transportasi</td><td class="v">{{ $transportasi }}</td>
            </tr>
            <tr>
                <td class="k">No Akta Lahir</td><td class="v">{{ $display($siswa->akta_no ?? null) }}</td>
                <td class="k">No KKS</td><td class="v">{{ $display($siswa->no_kks ?? null) }}</td>
            </tr>
            <tr>
                <td class="k">Kewarganegaraan</td><td class="v">{{ $display($siswa->kewarganegaraan ?? 'Indonesia') }}</td>
                <td class="k">KPS / PKH</td><td class="v">{{ $display($siswa->kps ?? null) }}</td>
            </tr>
            <tr>
                <td class="k">No KK</td><td class="v">{{ $display($siswa->no_kk ?? null) }}</td>
                <td class="k">Peserta KIP</td><td class="v">{{ $display($siswa->kip ?? null) }}</td>
            </tr>
            <tr>
                <td class="k">Jenis Kelamin</td><td class="v">{{ $toLabel($siswa->jenis_kelamin ?? null) }}</td>
                <td class="k">Layak PIP</td><td class="v">{{ $display($siswa->layak_pip ?? null) }}</td>
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

    <table class="footer">
        <tr>
            <td>
                <div class="muted">Panitia Penerima</div>
                <div class="signature">{{ $panitia }}</div>
            </td>
            <td>
                <div class="muted">Wali Murid</div>
                <div class="signature">(...............................)</div>
            </td>
        </tr>
    </table>

</div>

</body>
</html>