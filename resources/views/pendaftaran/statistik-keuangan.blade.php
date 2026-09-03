@extends('layouts.public')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="bg-white shadow-lg rounded-xl border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-200 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">📊 Statistik Keuangan</h1>
                <p class="text-sm text-slate-500 mt-1">Akses petugas: {{ $namaPetugas }}</p>
                <p class="text-xs text-slate-500 mt-1">Jumlah pendaftar terdata: {{ number_format($jumlahPendaftar, 0, ',', '.') }}</p>
                <div class="mt-2 flex flex-wrap gap-2 text-xs">
                    <span class="px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                        Periode: {{ $periodeLabel }}
                    </span>
                    <span class="px-2.5 py-1 rounded-full bg-slate-50 text-slate-700 border border-slate-200">
                        Diperbarui: {{ $updatedAt->translatedFormat('d M Y H:i') }}
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-2">

                <form method="POST" action="{{ route('pendaftaran.statistik.keuangan.logout') }}">
                    @csrf
                    <button class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 w-fit">
                        Logout Akses Keuangan
                    </button>
                </form>
            </div>
        </div>

        <div class="px-6 pt-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Dari</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Sampai</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div class="flex gap-2 md:col-span-2">
                    <button class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700" type="submit">
                        Terapkan Filter
                    </button>
                    <a href="{{ route('pendaftaran.statistik.keuangan') }}"
                        class="px-4 py-2 bg-slate-100 text-slate-700 text-sm rounded-lg hover:bg-slate-200">
                        Reset
                    </a>
                    <details class="relative">
                        <summary class="list-none cursor-pointer px-4 py-2 bg-slate-800 text-white text-sm rounded-lg hover:bg-slate-900">
                            Export
                        </summary>
                        <div class="absolute right-0 mt-2 w-40 rounded-lg border border-slate-200 bg-white shadow-lg overflow-hidden z-20">
                            <a href="{{ route('pendaftaran.statistik.keuangan.export.excel', ['date_from' => $dateFrom, 'date_to' => $dateTo]) }}"
                                class="block px-4 py-2 text-sm text-slate-700 hover:bg-emerald-50 hover:text-emerald-700">
                                Export Excel
                            </a>
                            <a href="{{ route('pendaftaran.statistik.keuangan.export.pdf', ['date_from' => $dateFrom, 'date_to' => $dateTo]) }}"
                                class="block px-4 py-2 text-sm text-slate-700 hover:bg-rose-50 hover:text-rose-700">
                                Export PDF
                            </a>
                        </div>
                    </details>
                </div>
            </form>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
                <p class="text-xs text-blue-700 font-semibold uppercase tracking-wide">Jumlah Biaya Keseluruhan</p>
                <p class="text-2xl font-bold text-blue-900 mt-2">Rp {{ number_format($jumlahBiayaKeseluruhan, 0, ',', '.') }}</p>
            </div>

            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4">
                <p class="text-xs text-rose-700 font-semibold uppercase tracking-wide">Jumlah yang Belum Lunas</p>
                <p class="text-2xl font-bold text-rose-900 mt-2">Rp {{ number_format($jumlahSisaPiutang, 0, ',', '.') }}</p>
            </div>

            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                <p class="text-xs text-emerald-700 font-semibold uppercase tracking-wide">Jumlah Yang Sudah Lunas</p>
                <p class="text-2xl font-bold text-emerald-900 mt-2">Rp {{ number_format($jumlahLunasNominal, 0, ',', '.') }}</p>
                <p class="text-xs text-emerald-700 mt-1">{{ number_format($jumlahLunasCount, 0, ',', '.') }} dari {{ number_format($totalTagihanCount, 0, ',', '.') }} tagihan lunas</p>
            </div>

            <div class="rounded-xl border border-violet-200 bg-violet-50 p-4">
                <p class="text-xs text-violet-700 font-semibold uppercase tracking-wide">Persentase Pelunasan</p>
                <p class="text-2xl font-bold text-violet-900 mt-2">{{ number_format($persentasePelunasan, 1, ',', '.') }}%</p>
                <p class="text-xs text-violet-700 mt-1">berdasarkan jumlah tagihan</p>
            </div>

            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 md:col-span-2 xl:col-span-1">
                <p class="text-xs text-amber-700 font-semibold uppercase tracking-wide">Jumlah Uang Masuk Periode</p>
                <p class="text-2xl font-bold text-amber-900 mt-2">Rp {{ number_format($jumlahUangMasukPeriode, 0, ',', '.') }}</p>
                <p class="text-xs text-amber-700 mt-1">{{ $periodeLabel }}</p>
            </div>
        </div>

        <div class="px-6 pb-6">
            <div class="mb-4 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-600">
                Definisi singkat: Lunas dihitung dari status tagihan. Uang masuk periode dihitung dari total nominal pembayaran pada rentang tanggal aktif.
            </div>

            <div class="rounded-xl border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 px-4 py-3 border-b border-slate-200">
                    <h3 class="font-semibold text-slate-700">Jumlah Biaya per Jenis Pembayaran</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-100 text-slate-700">
                            <tr>
                                <th class="px-4 py-3 text-left">Jenis Pembayaran</th>
                                <th class="px-4 py-3 text-right">Total Biaya</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($jumlahBiayaPerJenis as $row)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3">{{ ui_label($row->jenis_biaya) }}</td>
                                    <td class="px-4 py-3 text-right font-semibold">Rp {{ number_format((int) $row->total_jenis, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-4 py-6 text-center text-slate-500">Belum ada data biaya.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 overflow-hidden mt-4">
                <div class="bg-slate-50 px-4 py-3 border-b border-slate-200 flex items-center justify-between gap-3">
                    <h3 class="font-semibold text-slate-700">Riwayat Pembayaran Hari Ini</h3>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 border border-blue-200">
                        Jumlah Nominal: Rp {{ number_format((int) $riwayatPembayaranHariIni->sum('nominal_bayar'), 0, ',', '.') }}
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-100 text-slate-700">
                            <tr>
                                <th class="px-4 py-3 text-left w-16">No</th>
                                <th class="px-4 py-3 text-left">Tanggal/Waktu</th>
                                <th class="px-4 py-3 text-left">Nama</th>
                                <th class="px-4 py-3 text-left">Jenis Biaya</th>
                                <th class="px-4 py-3 text-right">Nominal</th>
                                <th class="px-4 py-3 text-left">Metode</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($riwayatPembayaranHariIni as $pembayaran)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3 text-slate-500">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        {{ optional($pembayaran->tanggal_bayar)->format('d M Y') ?? '-' }}
                                        <span class="text-xs text-slate-500">{{ optional($pembayaran->created_at)->format('H:i') ?? '-' }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-slate-800">{{ optional(optional($pembayaran->tagihan)->siswa)->nama ?? '-' }}</div>
                                        <div class="mt-1">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                                {{ optional(optional(optional($pembayaran->tagihan)->siswa)->registration)->nomor_registrasi ?? '-' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">{{ ui_label(optional(optional($pembayaran->tagihan)->biaya)->jenis_biaya ?? '-') }}</td>
                                    <td class="px-4 py-3 text-right font-semibold">Rp {{ number_format((int) $pembayaran->nominal_bayar, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3">{{ $pembayaran->metode ? ucfirst($pembayaran->metode) : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-slate-500">Belum ada pembayaran hari ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
