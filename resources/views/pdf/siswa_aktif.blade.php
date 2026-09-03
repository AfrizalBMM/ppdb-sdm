<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #444; padding-bottom: 10px; }
        .header h2 { margin: 0; text-transform: uppercase; color: #1e40af; }
        .header p { margin: 5px 0 0; color: #64748b; font-size: 10px; }
        .info { margin-bottom: 15px; }
        .info span { font-weight: bold; color: #475569; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f1f5f9; color: #475569; text-transform: uppercase; font-size: 9px; letter-spacing: 0.05em; }
        th, td { border: 1px solid #e2e8f0; padding: 8px 6px; text-align: left; }
        tr:nth-child(even) { background-color: #f8fafc; }
        .text-center { text-align: center; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-weight: bold; font-size: 8px; }
        .badge-blue { background-color: #dbeafe; color: #1e40af; }
        .badge-emerald { background-color: #d1fae5; color: #065f46; }
        .footer { margin-top: 30px; text-align: right; font-size: 9px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Daftar Peserta Didik Aktif</h2>
        <p>SD MUHAMMADIYAH - PPDB Online</p>
    </div>

    <div class="info">
        <p>Tahun Ajaran: <span>{{ $tahunAktif->nama }}</span></p>
        <p>Kategori: <span>{{ $scopeLabel }}</span></p>
        <p>Total Data: <span>{{ count($rows) }} Peserta Didik</span></p>
        <p>Dicetak pada: <span>{{ now()->translatedFormat('d F Y H:i') }}</span></p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="15%">No. Reg</th>
                <th>Nama Lengkap</th>
                <th width="10%">JK</th>
                <th width="15%">Kelas</th>
                <th width="10%">Hasil Tes</th>
                <th width="15%">Data Ibu</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ optional($item->registration)->nomor_registrasi ?? '-' }}</td>
                    <td style="font-weight: 500;">{{ $item->nama }}</td>
                    <td>{{ $item->jenis_kelamin === 'laki-laki' ? 'L' : 'P' }}</td>
                    <td>
                        <span class="badge {{ $item->kelasSiswa ? 'badge-emerald' : 'badge-blue' }}">
                            {{ optional($item->kelasSiswa)->nama_kelas ?? 'BELUM KELAS' }}
                        </span>
                    </td>
                    <td>{{ $item->hasil_tes ?? '-' }}</td>
                    <td>
                        <div style="font-weight: 500;">{{ optional($item->ibu)->nama ?? '-' }}</div>
                        <div style="font-size: 8px; color: #64748b;">{{ optional($item->ibu)->no_hp ?? '-' }}</div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak otomatis oleh Sistem PPDB SD Muhammadiyah
    </div>
</body>
</html>
