@extends('layouts.admin')

@section('title', 'Detail Keuangan Siswa')

@section('content')
<div class="max-w-3xl mx-auto py-8">
    <div class="mb-4">
        <a href="{{ route('keuangan.index') }}" class="inline-block px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow transition">&larr; Kembali</a>
    </div>
    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-xl font-bold mb-4">Detail Keuangan Siswa</h2>
        <div class="mb-6">
            <table class="w-full text-sm border border-slate-200 rounded-lg">
                <tbody>
                    <tr class="bg-slate-50">
                        <td class="p-3 w-48 font-medium">Nama Peserta Didik</td>
                        <td class="p-3">{{ $siswa->nama }}</td>
                    </tr>
                    <tr>
                        <td class="p-3 font-medium">No Registrasi</td>
                        <td class="p-3">{{ optional($siswa->registration)->nomor_registrasi ?? '-' }}</td>
                    </tr>
                    <tr class="bg-slate-50">
                        <td class="p-3 font-medium">Nama Ibu</td>
                        <td class="p-3">{{ optional($siswa->ibu)->nama ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="p-3 font-medium">No HP Ibu</td>
                        <td class="p-3">{{ optional($siswa->ibu)->no_hp ?? '-' }}</td>
                    </tr>
                    <tr class="bg-slate-50">
                        <td class="p-3 font-medium">Voucher</td>
                        <td class="p-3">
                            @php $voucher = optional($siswa->registration)->voucher; @endphp
                            @if($voucher)
                                <span class="badge-success">{{ $voucher->kode }}</span>
                                dengan potongan <strong>Rp {{ number_format($voucher->diskon_nominal, 0, ',', '.') }}</strong>
                                berlaku untuk biaya <strong>{{ ui_label($voucher->jenis_biaya) }}</strong>
                            @else
                                <i class="text-slate-400">Tidak dapat Voucher</i>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border">
                <thead>
                    <tr class="bg-slate-100">
                        <th class="px-4 py-2 border">Jenis Biaya</th>
                        <th class="px-4 py-2 border">Total Tagihan</th>
                        <th class="px-4 py-2 border">Sudah Dibayar</th>
                        <th class="px-4 py-2 border">Sisa</th>
                        <th class="px-4 py-2 border">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($siswa->tagihan as $tagihan)
                    <tr>
                        <td class="px-4 py-2 border">{{ $tagihan->biaya->nama_biaya ?? '-' }}</td>
                        <td class="px-4 py-2 border">Rp {{ number_format($tagihan->total) }}</td>
                        <td class="px-4 py-2 border text-emerald-700">Rp {{ number_format($tagihan->total_dibayar) }}</td>
                        <td class="px-4 py-2 border text-rose-700">Rp {{ number_format($tagihan->sisa) }}</td>
                        <td class="px-4 py-2 border">
                            @if($tagihan->is_lunas)
                                <span class="inline-block px-2 py-1 text-xs rounded bg-emerald-100 text-emerald-700">Lunas</span>
                            @else
                                <span class="inline-block px-2 py-1 text-xs rounded bg-rose-100 text-rose-700">Belum Lunas</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-8">
            <div class="flex items-center justify-between mb-2">
                <h3 class="font-semibold mb-2">Riwayat Pembayaran</h3>
                <form method="get" class="flex items-center gap-2">
                    <label for="filter_jenis_biaya" class="text-xs text-slate-600">Filter</label>
                    <select name="jenis_biaya" id="filter_jenis_biaya" class="input input-sm" onchange="this.form.submit()">
                        <option value="">Semua</option>
                        @php
                            $jenisList = $semuaPembayaran->pluck('tagihan.biaya.jenis_biaya')->unique()->filter()->values();
                        @endphp
                        @foreach($jenisList as $jenis)
                            <option value="{{ $jenis }}" @if(request('jenis_biaya') == $jenis) selected @endif>{{ ui_label($jenis) }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border">
                    <thead>
                        <tr class="bg-slate-100">
                            <th class="px-4 py-2 border">Tanggal</th>
                            <th class="px-4 py-2 border">Jenis Biaya</th>
                            <th class="px-4 py-2 border">Nominal</th>
                            <th class="px-4 py-2 border">Penerima</th>
                            <th class="px-4 py-2 border">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($semuaPembayaran as $pembayaran)
                            @if(!request('jenis_biaya') || (string)($pembayaran->tagihan->biaya->jenis_biaya ?? '') === request('jenis_biaya'))
                            <tr>
                                <td class="px-4 py-2 border">{{ $pembayaran->tanggal_bayar?->translatedFormat('d F Y') ?? '-' }}</td>
                                <td class="px-4 py-2 border">{{ $pembayaran->tagihan->biaya->nama_biaya ?? '-' }}</td>
                                <td class="px-4 py-2 border">Rp {{ number_format($pembayaran->nominal_bayar) }}</td>
                                <td class="px-4 py-2 border">{{ $pembayaran->admin_penerima ?? '-' }}</td>
                                <td class="px-4 py-2 border">{{ $pembayaran->keterangan ?? '-' }}</td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
