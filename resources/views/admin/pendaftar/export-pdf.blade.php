<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #0f172a;
        }
        .title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 2px;
        }
        .subtitle {
            font-size: 11px;
            color: #475569;
            margin-bottom: 10px;
        }
        .meta {
            margin-bottom: 12px;
            padding: 8px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 6px;
            vertical-align: top;
        }
        th {
            background: #f1f5f9;
            text-align: left;
            font-weight: 700;
        }
        .small {
            color: #64748b;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="title">Laporan Data Pendaftar</div>
    <div class="subtitle">PPDB SD Muhammadiyah</div>

    <div class="meta">
        <div><strong>Tanggal cetak:</strong> {{ now()->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</div>
        <div><strong>Total data:</strong> {{ number_format($rows->count(), 0, ',', '.') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:4%">No</th>
                <th style="width:16%">No Registrasi</th>
                <th style="width:24%">Nama</th>
                <th style="width:12%">Jenis Kelamin</th>
                <th style="width:12%">Tanggal Daftar</th>
                <th style="width:14%">Status PPDB</th>
                <th style="width:14%">Status Pembayaran</th>
                <th style="width:14%">NIK</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $index => $siswa)
                @php
                    $statusPpdb = \App\Models\Registration::statusLabel((int) optional($siswa->registration)->status);
                    $tagihanAktif = $siswa->tagihan->filter(fn ($t) => (float) $t->total > 0);

                    if ($tagihanAktif->isEmpty()) {
                        $statusPembayaran = 'Belum Ada Tagihan';
                    } elseif ($tagihanAktif->every(fn ($t) => $t->status === 'lunas')) {
                        $statusPembayaran = 'Lunas';
                    } else {
                        $statusPembayaran = 'Belum Lunas';
                    }
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $siswa->registration->nomor_registrasi ?? '-' }}</td>
                    <td>{{ $siswa->nama }}</td>
                    <td>{{ ui_label($siswa->jenis_kelamin ?? '-') }}</td>
                    <td>{{ optional($siswa->registration?->tanggal_daftar)->format('d-m-Y') ?? '-' }}</td>
                    <td>{{ $statusPpdb }}</td>
                    <td>{{ $statusPembayaran }}</td>
                    <td>{{ $siswa->nik ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="small">Data tidak ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
