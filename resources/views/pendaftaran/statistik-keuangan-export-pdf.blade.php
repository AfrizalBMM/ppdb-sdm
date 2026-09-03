<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #0f172a; }
        .title { font-size: 16px; font-weight: 700; margin-bottom: 2px; }
        .subtitle { font-size: 11px; color: #475569; margin-bottom: 10px; }
        .meta { margin-bottom: 12px; padding: 8px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; }
        .section-title { margin: 12px 0 6px; font-size: 12px; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 6px; vertical-align: top; }
        th { background: #f1f5f9; text-align: left; font-weight: 700; }
        .text-right { text-align: right; }
        .small { color: #64748b; font-size: 10px; }
    </style>
</head>
<body>
    <div class="title">Laporan Statistik Keuangan</div>
    <div class="subtitle">PPDB SD Muhammadiyah</div>

    <div class="meta">
        <div><strong>Periode:</strong> {{ $periodeLabel }}</div>
        <div><strong>Diperbarui:</strong> {{ $updatedAt->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</div>
        <div><strong>Petugas:</strong> {{ $namaPetugas }}</div>
    </div>

    <div class="section-title">Ringkasan Statistik</div>
    <table>
        <tbody>
            <tr><th>Jumlah Biaya Keseluruhan</th><td class="text-right">Rp {{ number_format($jumlahBiayaKeseluruhan, 0, ',', '.') }}</td></tr>
            <tr><th>Jumlah yang Belum Lunas</th><td class="text-right">Rp {{ number_format($jumlahSisaPiutang, 0, ',', '.') }}</td></tr>
            <tr><th>Jumlah Yang Sudah Lunas</th><td class="text-right">Rp {{ number_format($jumlahLunasNominal, 0, ',', '.') }}</td></tr>
            <tr><th>Persentase Pelunasan</th><td class="text-right">{{ number_format($persentasePelunasan, 1, ',', '.') }}%</td></tr>
            <tr><th>Jumlah Uang Masuk Periode</th><td class="text-right">Rp {{ number_format($jumlahUangMasukPeriode, 0, ',', '.') }}</td></tr>
            <tr><th>Jumlah Pendaftar</th><td class="text-right">{{ number_format($jumlahPendaftar, 0, ',', '.') }}</td></tr>
        </tbody>
    </table>

    <div class="section-title">Jumlah Biaya per Jenis Pembayaran</div>
    <table>
        <thead>
            <tr>
                <th>Jenis Pembayaran</th>
                <th class="text-right">Total Biaya</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jumlahBiayaPerJenis as $row)
                <tr>
                    <td>{{ ui_label($row->jenis_biaya) }}</td>
                    <td class="text-right">Rp {{ number_format((int) $row->total_jenis, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="small">Belum ada data biaya.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Riwayat Pembayaran Hari Ini</div>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Waktu</th>
                <th>Nama</th>
                <th>No Registrasi</th>
                <th>Jenis Biaya</th>
                <th class="text-right">Nominal</th>
                <th>Metode</th>
            </tr>
        </thead>
        <tbody>
            @forelse($riwayatPembayaranHariIni as $pembayaran)
                <tr>
                    <td>{{ optional($pembayaran->tanggal_bayar)->format('d-m-Y') ?? '-' }}</td>
                    <td>{{ optional($pembayaran->created_at)->format('H:i') ?? '-' }}</td>
                    <td>{{ optional(optional($pembayaran->tagihan)->siswa)->nama ?? '-' }}</td>
                    <td>{{ optional(optional(optional($pembayaran->tagihan)->siswa)->registration)->nomor_registrasi ?? '-' }}</td>
                    <td>{{ ui_label(optional(optional($pembayaran->tagihan)->biaya)->jenis_biaya ?? '-') }}</td>
                    <td class="text-right">Rp {{ number_format((int) $pembayaran->nominal_bayar, 0, ',', '.') }}</td>
                    <td>{{ $pembayaran->metode ? ucfirst($pembayaran->metode) : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="small">Belum ada pembayaran hari ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
