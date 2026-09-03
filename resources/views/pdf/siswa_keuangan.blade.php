<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Export Keuangan' }}</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #111827; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th, td { border: 1px solid #d1d5db; padding: 6px 6px; vertical-align: top; }
        .row-white { background: #fff; }
        .row-gray { background: #f3f4f6; }
        .bold { font-weight: bold; }
        th { background: #f3f4f6; font-size: 10px; text-transform: uppercase; letter-spacing: .04em; text-align: left; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .wrap { white-space: normal; }
    </style>
</head>
<body>
    <div style="text-align:center; margin-bottom: 8px;">
        <div style="font-size:18px; font-weight:bold;">
            Laporan Rekapitulasi Keuangan Peserta Didik - Kelas {{ $namaKelas ?? '-' }}
        </div>
        <div style="font-size:12px; margin-top:2px; margin-bottom:2px; display:flex; justify-content:center; align-items:center; gap:16px;">
            <span>Dicetak: {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y H:i') }},</span>
            <span>Dibuat otomatis melalui website www.ppdb.sdmuhwonorejo.com</span>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 4%;" class="text-center">no</th>
                <th rowspan="2" style="width: 18%;">nama</th>
                <th rowspan="2" style="width: 8%;">Jenis Kelamin</th>
                <th rowspan="2" style="width: 16%;">No Registrasi</th>
                <th rowspan="2" style="width: 10%;" class="text-right">total biaya</th>
                <th colspan="3" style="width: 24%; text-align:center;">Jenis pembayaran</th>
                <th rowspan="2" style="width: 10%;" class="text-right">total Sudah bayar</th>
                <th rowspan="2" style="width: 10%;" class="text-right">kekurangan</th>
            </tr>
            <tr>
                <th style="width: 8%; text-align:center;">P</th>
                <th style="width: 8%; text-align:center;">DU</th>
                <th style="width: 8%; text-align:center;">UDP</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $grandTotalKekurangan = 0; 
            @endphp
            @foreach($rows as $index => $row)
                @php
                    $bayarP = 0;
                    $bayarDU = 0;
                    $bayarUDP = 0;
                    if (isset($row['tagihan']) && is_iterable($row['tagihan'])) {
                        foreach ($row['tagihan'] as $t) {
                            $jenis = strtolower(str_replace(' ', '_', optional($t->biaya)->jenis_biaya ?? ''));
                            $totalDibayar = isset($t->pembayaran) && is_iterable($t->pembayaran)
                                ? collect($t->pembayaran)->sum('nominal_bayar')
                                : (isset($t->total_dibayar) ? (int) $t->total_dibayar : 0);
                            if ($jenis === 'pendaftaran') {
                                $bayarP += $totalDibayar;
                            } elseif ($jenis === 'daftar_ulang') {
                                $bayarDU += $totalDibayar;
                            } elseif ($jenis === 'udp') {
                                $bayarUDP += $totalDibayar;
                            }
                        }
                    }
                    $rowClass = $index % 2 === 0 ? 'row-white' : 'row-gray';
                    // Tambahkan total_kekurangan ke grandTotalKekurangan
                    $grandTotalKekurangan += isset($row['total_kekurangan']) ? (int) $row['total_kekurangan'] : 0;
                @endphp
                <tr class="{{ $rowClass }}">
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="wrap">{{ $row['nama'] }}</td>
                    <td>{{ $row['jenis_kelamin'] ?? '-' }}</td>
                    <td>{{ $row['no_registrasi'] }}</td>
                    <td class="text-right">{{ number_format($row['total_biaya'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($bayarP, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($bayarDU, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($bayarUDP, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['total_terbayar'], 0, ',', '.') }}</td>
                    <td class="text-right bold">{{ number_format($row['total_kekurangan'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="9" class="text-right bold">Total Kekurangan semua peserta didik</td>
                <td class="text-right bold">{{ number_format($grandTotalKekurangan, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
</body>
</html>
</body>
</html>
