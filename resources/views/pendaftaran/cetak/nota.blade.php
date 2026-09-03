<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        @page {
            size: 215mm 330mm;
            margin: 6mm 10mm;
        }

        body {
            font-family: Inter, DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        .kwitansi {
            border: 1.5px solid #222;
            padding: 12px 16px;
            box-sizing: border-box;
            position: relative;
            overflow: hidden;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 340px;
            height: auto;
            display: block;
            opacity: 0.1;
            transform: translate(-50%, -50%);
            z-index: 0;
            pointer-events: none;
        }

        .kwitansi > *:not(.watermark) {
            position: relative;
            z-index: 1;
        }

        .potong {
            border-top: 2px dashed #888;
            margin: 22px 0 12px;
            text-align: center;
            font-size: 12px;
            color: #aaa;
            padding-top: 4px;
        }

        .kop img {
            width: 100%;
            height: auto;
            margin-bottom: 4px;
        }

        .judul {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            text-decoration: none;
            margin: 0;
            line-height: 1.4;
        }

        .judul-baris {
            position: relative;
            width: 100%;
            margin: 8px 0 6px;
            min-height: 34px;
        }

        .judul-baris .judul {
            width: 100%;
        }

        .header-date {
            position: absolute;
            right: 0;
            top: 0;
            text-align: right;
            font-size: 12px;
            white-space: nowrap;
            padding-left: 8px;
        }

        .section-label {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 2px;
        }

        /* === Tabel utama — semua field sejajar === */
        table.fields {
            width: 100%;
            border-collapse: collapse;
        }

        table.fields td {
            padding: 2px 0;
            vertical-align: top;
            font-size: 12px;
            white-space: nowrap;
        }

        table.fields td.lbl {
            width: 130px;
            font-weight: bold;
        }

        table.fields td.sep {
            width: 12px;
            text-align: center;
        }

        table.fields td.val {
            /* field values auto-width */
        }

        table.fields tr.nama-field td.val {
            white-space: normal;
            word-break: break-word;
            word-wrap: break-word;
            max-width: 200px;
        }

        table.info-dua-kolom {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
        }

        table.info-dua-kolom td {
            vertical-align: top;
            width: 50%;
        }

        .kolom-kiri {
            padding-right: 8px;
        }

        .kolom-kanan {
            padding-left: 8px;
            border-left: 1px solid #ddd;
        }

        .coret {
            text-decoration: line-through;
            color: #999;
        }

        .note-coret {
            font-style: italic;
            font-size: 12px;
            color: #c00;
            margin-top: 1px;
            margin-bottom: 2px;
        }

        .kekurangan-val {
            font-style: italic;
        }

        /* === Tanda tangan === */
        .ttd-tempat {
            text-align: right;
            margin-top: 10px;
        }

        table.ttd {
            width: 100%;
            border-collapse: collapse;
            margin-top: 24px;
        }

        table.ttd td {
            text-align: center;
            vertical-align: top;
            font-size: 10px;
        }

        .ttd-garis {
            padding-top: 55px;
        }

        .catatan {
            font-style: italic;
            color: #c00;
            font-size: 8px;
            margin-top: 0;
            padding-top: 3px;
        }

        .footer-info {
            display: table;
            width: 100%;
            margin-top: 6px;
            border-top: 1px solid #ccc;
        }

        .footer-info-left,
        .footer-info-right {
            display: table-cell;
            vertical-align: top;
            padding-top: 3px;
        }

        .footer-info-right {
            text-align: right;
            font-size: 8px;
            color: #666;
        }
    </style>
</head>

<body>

    @php
        $siswa = $pembayaran->tagihan->siswa;
        $tagihan = $pembayaran->tagihan;
        $biaya = $tagihan->biaya;
        $reg = optional($siswa->registration);
        $jk = $siswa->jenis_kelamin == 'laki-laki' ? 'L' : 'P';
        $jkLabel = match (strtolower((string) ($siswa->jenis_kelamin ?? ''))) {
            'laki-laki' => 'Laki-laki',
            'perempuan' => 'Perempuan',
            default => $siswa->jenis_kelamin ?? '-',
        };
        $isLunas = $tagihan->is_lunas;
        $sisa = $tagihan->sisa;
        $tglBayar = \Carbon\Carbon::parse($pembayaran->tanggal_bayar)->translatedFormat('d F Y');
        $tglDaftar = $reg->created_at ? \Carbon\Carbon::parse($reg->created_at)->translatedFormat('d F Y') : '.......................................';
        $jenisBiayaRaw = strtolower((string) ($biaya->jenis_biaya ?? 'pendaftaran'));
        $jenisBiayaLabel = match ($jenisBiayaRaw) {
            'daftar_ulang' => 'Daftar Ulang',
            'udp' => 'UDP - Pengembangan',
            'pendaftaran' => 'Pendaftaran',
            default => ucwords(str_replace('_', ' ', $jenisBiayaRaw)),
        };
    @endphp

    {{-- ============================
    KWITANSI 1 (ATAS)
    ============================ --}}
    <div class="kwitansi">

        <img src="{{ public_path('images/logo.png') }}" class="watermark">

        <div class="kop">
            <img src="{{ public_path('images/kopsdm.png') }}">
        </div>

        <div class="judul-baris">
            <div class="judul">
                Kuitansi Pembayaran<br>{{ $jenisBiayaLabel }}
            </div>
            <div class="header-date">
                Polokarto, {{ $tglBayar }}
            </div>
        </div>

        <div class="section-label">Informasi Pendaftar</div>

        <table class="info-dua-kolom">
            <tr>
                <td class="kolom-kiri">
                    <table class="fields">
                        <tr>
                            <td class="lbl">Tanggal Pendaftaran</td>
                            <td class="sep">:</td>
                            <td class="val">{{ $tglDaftar }}</td>
                        </tr>
                        <tr class="nama-field">
                            <td class="lbl">Nama Pendaftar</td>
                            <td class="sep">:</td>
                            <td class="val">{{ $siswa->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">Jenis Kelamin</td>
                            <td class="sep">:</td>
                            <td class="val">{{ $jkLabel }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">Nomor Pendaftaran</td>
                            <td class="sep">:</td>
                            <td class="val">{{ $reg->nomor_registrasi ?? '-' }}</td>
                        </tr>
                    </table>
                </td>
                <td class="kolom-kanan">
                    <table class="fields">
                        <tr>
                            <td class="lbl">Biaya {{ $jenisBiayaLabel }} ({{ $jk }})</td>
                            <td class="sep">:</td>
                            <td class="val"><b>Rp. {{ number_format($tagihan->total, 0, ',', '.') }},00</b></td>
                        </tr>
                        <tr>
                            <td class="lbl">Nominal Pembayaran</td>
                            <td class="sep">:</td>
                            <td class="val"><b>Rp. {{ number_format($pembayaran->nominal_bayar, 0, ',', '.') }},00</b></td>
                        </tr>
                        <tr>
                            <td class="lbl">Status Pembayaran</td>
                            <td class="sep">:</td>
                            <td class="val">
                                <b>
                                    @if($isLunas)
                                        LUNAS / <span class="coret">BELUM LUNAS</span>
                                    @else
                                        <span class="coret">LUNAS</span> / BELUM LUNAS
                                    @endif
                                </b>
                            </td>
                        </tr>
                        <tr>
                            <td class="lbl">Kekurangan</td>
                            <td class="sep">:</td>
                            <td class="val"><span class="kekurangan-val">Rp. {{ number_format($sisa, 0, ',', '.') }},00</span></td>
                        </tr>
                        <tr>
                            <td class="lbl">Keterangan</td>
                            <td class="sep">:</td>
                            <td class="val"><span style="text-decoration: underline;">{{ $pembayaran->keterangan ?? '-' }}</span></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="ttd">
            <tr>
                <td width="50%">Pendaftar,</td>
                <td width="50%">Panitia PPDB</td>
            </tr>
            <tr>
                <td class="ttd-garis">(........................................)</td>
                <td class="ttd-garis">( {{ $panitia }} )</td>
            </tr>
        </table>

        <div class="footer-info">
            <div class="footer-info-left">
                <div class="catatan">
                    Catatan : Kuitansi ini mohon disimpan, sebagai bukti pembayaran yang sah.
                </div>
            </div>
            <div class="footer-info-right">
                dibuat otomatis melalui website https://ppdb.sdmuhwonorejo.com/
            </div>
        </div>

    </div>

    {{-- GARIS POTONG --}}
    <div class="potong">✂ potong di sini</div>

    {{-- ============================
    KWITANSI 2 (BAWAH) — SALINAN
    ============================ --}}
    <div class="kwitansi">

        <img src="{{ public_path('images/logo.png') }}" class="watermark">

        <div class="kop">
            <img src="{{ public_path('images/kopsdm.png') }}">
        </div>

        <div class="judul-baris">
            <div class="judul">
                Kuitansi Pembayaran<br>{{ $jenisBiayaLabel }}
            </div>
            <div class="header-date">
                Polokarto, {{ $tglBayar }}
            </div>
        </div>

        <div class="section-label">Informasi Pendaftar</div>

        <table class="info-dua-kolom">
            <tr>
                <td class="kolom-kiri">
                    <table class="fields">
                        <tr>
                            <td class="lbl">Tanggal Pendaftaran</td>
                            <td class="sep">:</td>
                            <td class="val">{{ $tglDaftar }}</td>
                        </tr>
                        <tr class="nama-field">
                            <td class="lbl">Nama Pendaftar</td>
                            <td class="sep">:</td>
                            <td class="val">{{ $siswa->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">Jenis Kelamin</td>
                            <td class="sep">:</td>
                            <td class="val">{{ $jkLabel }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">Nomor Pendaftaran</td>
                            <td class="sep">:</td>
                            <td class="val">{{ $reg->nomor_registrasi ?? '-' }}</td>
                        </tr>
                    </table>
                </td>
                <td class="kolom-kanan">
                    <table class="fields">
                        <tr>
                            <td class="lbl">Biaya {{ $jenisBiayaLabel }} ({{ $jk }})</td>
                            <td class="sep">:</td>
                            <td class="val"><b>Rp. {{ number_format($tagihan->total, 0, ',', '.') }},00</b></td>
                        </tr>
                        <tr>
                            <td class="lbl">Nominal Pembayaran</td>
                            <td class="sep">:</td>
                            <td class="val"><b>Rp. {{ number_format($pembayaran->nominal_bayar, 0, ',', '.') }},00</b></td>
                        </tr>
                        <tr>
                            <td class="lbl">Status Pembayaran</td>
                            <td class="sep">:</td>
                            <td class="val">
                                <b>
                                    @if($isLunas)
                                        LUNAS / <span class="coret">BELUM LUNAS</span>
                                    @else
                                        <span class="coret">LUNAS</span> / BELUM LUNAS
                                    @endif
                                </b>
                            </td>
                        </tr>
                        <tr>
                            <td class="lbl">Kekurangan</td>
                            <td class="sep">:</td>
                            <td class="val"><span class="kekurangan-val">Rp. {{ number_format($sisa, 0, ',', '.') }},00</span></td>
                        </tr>
                        <tr>
                            <td class="lbl">Keterangan</td>
                            <td class="sep">:</td>
                            <td class="val"><span style="text-decoration: underline;">{{ $pembayaran->keterangan ?? '-' }}</span></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="ttd">
            <tr>
                <td width="50%">Pendaftar,</td>
                <td width="50%">Panitia PPDB</td>
            </tr>
            <tr>
                <td class="ttd-garis">(........................................)</td>
                <td class="ttd-garis">( {{ $panitia }} )</td>
            </tr>
        </table>

        <div class="footer-info">
            <div class="footer-info-left">
                <div class="catatan">
                    Catatan : Kuitansi ini mohon disimpan, sebagai bukti pembayaran yang sah.
                </div>
            </div>
            <div class="footer-info-right">
                dibuat otomatis melalui website https://ppdb.sdmuhwonorejo.com/
            </div>
        </div>

    </div>

</body>

</html>