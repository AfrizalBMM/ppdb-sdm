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
            color: #222;
        }

        .sheet {
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
            opacity: 0.1;
            transform: translate(-50%, -50%);
            z-index: 0;
        }

        .sheet>*:not(.watermark) {
            position: relative;
            z-index: 1;
        }

        .kop img {
            width: 100%;
            height: auto;
            margin-bottom: 4px;
        }

        .judul-baris {
            position: relative;
            width: 100%;
            margin: 8px 0 8px;
            min-height: 34px;
        }

        .judul {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            line-height: 1.4;
            text-decoration: underline;
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
            margin: 10px 0 4px;
        }

        table.fields,
        table.rincian {
            width: 100%;
            border-collapse: collapse;
        }

        table.fields td {
            padding: 2px 0;
            vertical-align: top;
            font-size: 12px;
        }

        table.fields td.lbl {
            width: 150px;
            font-weight: bold;
            white-space: nowrap;
        }

        table.fields td.sep {
            width: 12px;
            text-align: center;
        }

        table.fields td.val {
            white-space: normal;
            word-break: break-word;
        }

        table.rincian th,
        table.rincian td {
            border: 1px solid #999;
            padding: 4px 6px;
            font-size: 11px;
            vertical-align: top;
        }

        table.rincian th {
            background: #f1f5f9;
            text-align: left;
        }

        .spacer-row td {
            border: none !important;
            padding: 0;
            height: 8px;
            background: transparent;
        }

        table.riwayat-table {
            width: 100%;
            border-collapse: collapse;
        }

        table.riwayat-table th,
        table.riwayat-table td {
            border: 1px solid #cbd5e1;
            padding: 3px 5px;
            font-size: 10px;
        }

        table.riwayat-table th {
            background: #f8fafc;
            color: #334155;
            font-weight: bold;
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .status-lunas {
            color: #15803d;
            font-weight: bold;
        }

        .status-belum {
            color: #b45309;
            font-weight: bold;
        }

        .ringkas {
            margin-top: 8px;
            width: 60%;
            margin-left: auto;
            border-collapse: collapse;
        }

        .ringkas td {
            border: 1px solid #999;
            padding: 4px 6px;
            font-size: 11px;
        }

        .ringkas .label {
            font-weight: bold;
            background: #f8fafc;
        }

        .ringkas .value {
            font-weight: bold;
        }

        table.ttd {
            width: 100%;
            border-collapse: collapse;
            margin-top: 28px;
        }

        table.ttd td {
            text-align: center;
            vertical-align: top;
            font-size: 10px;
        }

        .ttd-garis {
            padding-top: 55px;
        }

        .footer-info {
            display: table;
            width: 100%;
            margin-top: 8px;
            border-top: 1px solid #ccc;
        }

        .footer-info-left,
        .footer-info-right {
            display: table-cell;
            vertical-align: top;
            padding-top: 3px;
            font-size: 8px;
        }

        .footer-info-right {
            text-align: right;
            color: #666;
        }
    </style>
</head>

<body>
    @php
        $reg = optional($siswa->registration);
        $alamat = optional($siswa->alamat);
        $voucher = optional($reg->voucher);
        $tglCetak = \Carbon\Carbon::now()->translatedFormat('d F Y');
        $jkLabel = match (strtolower((string) ($siswa->jenis_kelamin ?? ''))) {
            'laki-laki' => 'Laki-laki',
            'perempuan' => 'Perempuan',
            default => $siswa->jenis_kelamin ?? '-',
        };
        $alamatLengkap = collect([
            $alamat->alamat ?? null,
            $alamat->kelurahan ?? null,
            $alamat->kecamatan ?? null,
            $alamat->kabupaten ?? null,
            $alamat->provinsi ?? null,
        ])->filter(fn ($item) => trim((string) $item) !== '')->implode(', ');
        $voucherNama = trim((string) ($voucher->nama ?? $voucher->kode ?? ''));
        $voucherDiskon = (int) ($voucher->diskon_nominal ?? 0);
        $voucherJenis = trim((string) ($voucher->jenis_biaya ?? ''));
        $voucherJenisLabel = match (strtolower($voucherJenis)) {
            'udp' => 'UDP',
            'daftar_ulang' => 'Daftar Ulang',
            'pendaftaran' => 'Pendaftaran',
            default => $voucherJenis !== '' ? strtoupper($voucherJenis) : '-',
        };
    @endphp

    <div class="sheet">
        <img src="{{ public_path('images/logo.png') }}" class="watermark">

        <div class="kop">
            <img src="{{ public_path('images/kopsdm.png') }}">
        </div>

        <div class="judul-baris">
            <div class="judul">
                Nota Rincian Biaya
            </div>
            <div class="header-date">
                Polokarto, {{ $tglCetak }}
            </div>
        </div>

        <div class="section-label">Informasi Siswa</div>
        <table class="fields">
            <tr>
                <td class="lbl">Nama Pendaftar</td>
                <td class="sep">:</td>
                <td class="val">{{ $siswa->nama ?? '-' }}</td>
            </tr>
            <tr>
                <td class="lbl">Nomor Pendaftaran</td>
                <td class="sep">:</td>
                <td class="val">{{ $reg->nomor_registrasi ?? '-' }}</td>
            </tr>
            <tr>
                <td class="lbl">Jenis Kelamin</td>
                <td class="sep">:</td>
                <td class="val">{{ $jkLabel }}</td>
            </tr>
            <tr>
                <td class="lbl">Alamat Lengkap</td>
                <td class="sep">:</td>
                <td class="val">{{ $alamatLengkap !== '' ? $alamatLengkap : '-' }}</td>
            </tr>
            <tr>
                <td class="lbl">Voucher</td>
                <td class="sep">:</td>
                <td class="val">
                    @if($voucherNama !== '')
                        {{ $voucherNama }} dengan potongan Rp {{ number_format($voucherDiskon, 0, ',', '.') }}
                    @else
                        -
                    @endif
                </td>
            </tr>
        </table>

        <div class="section-label">Rincian Biaya dan Pembayaran</div>
        <table class="rincian">
            <tbody>
                @forelse($siswa->tagihan as $tagihan)
                    @php
                        $totalDibayar = (int) $tagihan->pembayaran->sum('nominal_bayar');
                    @endphp

                    <tr>
                        <th style="width: 22%;">Jenis Biaya</th>
                        <th style="width: 13%;" class="text-right">Nominal</th>
                        <th style="width: 12%;" class="text-right">Diskon</th>
                        <th style="width: 13%;" class="text-right">Total</th>
                        <th style="width: 13%;" class="text-right">Terbayar</th>
                        <th style="width: 13%;" class="text-right">Kekurangan</th>
                        <th style="width: 14%;" class="text-center">Status</th>
                    </tr>

                    <tr>
                        <td>{{ ui_label(optional($tagihan->biaya)->jenis_biaya ?? '-') }}</td>
                        <td class="text-right">Rp {{ number_format($tagihan->nominal, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($tagihan->diskon, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($tagihan->total, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($totalDibayar, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($tagihan->sisa, 0, ',', '.') }}</td>
                        <td class="text-center {{ $tagihan->is_lunas ? 'status-lunas' : 'status-belum' }}">
                            {{ $tagihan->is_lunas ? 'LUNAS' : 'BELUM LUNAS' }}
                        </td>
                    </tr>

                    @if($tagihan->pembayaran->count())
                        <tr>
                            <td colspan="7" style="padding: 0;">
                                <table class="riwayat-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 20%;">Tanggal Bayar</th>
                                            <th style="width: 18%;" class="text-right">Nominal</th>
                                            <th style="width: 15%;">Metode</th>
                                            <th style="width: 22%;">Penerima</th>
                                            <th style="width: 25%;">Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($tagihan->pembayaran as $bayar)
                                            <tr>
                                                <td>{{ optional($bayar->tanggal_bayar)->format('d M Y') ?? '-' }}</td>
                                                <td class="text-right">Rp {{ number_format($bayar->nominal_bayar, 0, ',', '.') }}</td>
                                                <td>{{ $bayar->metode ? ucfirst($bayar->metode) : '-' }}</td>
                                                <td>{{ $bayar->admin_penerima ?? '-' }}</td>
                                                <td>{{ $bayar->keterangan ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    @endif

                    <tr class="spacer-row">
                        <td colspan="7"></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Belum ada data tagihan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <table class="ringkas">
            <tr>
                <td class="label">Total Biaya</td>
                <td class="text-right value">Rp {{ number_format($totalBiaya, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Total Terbayar</td>
                <td class="text-right value">Rp {{ number_format($totalTerbayar, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Total Kekurangan</td>
                <td class="text-right value">Rp {{ number_format($totalKekurangan, 0, ',', '.') }}</td>
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
                Catatan: Nota ini mencakup seluruh rincian biaya dan riwayat cicilan siswa.
            </div>
            <div class="footer-info-right">
                dibuat otomatis melalui website https://ppdb.sdmuhwonorejo.com/
            </div>
        </div>
    </div>
</body>

</html>
