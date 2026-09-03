@extends('layouts.admin')

@section('page-title','Dashboard PPDB')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">

    <div class="rounded-2xl border border-slate-200 bg-gradient-to-r from-slate-50 via-white to-blue-50 p-5 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-800">Ringkasan Dashboard PPDB</h2>
                <p class="mt-1 text-sm text-slate-600">Pantau statistik pendaftar, peserta didik, dan pembayaran terbaru dalam satu halaman.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700">Total Pembayaran: Rp {{ number_format($totalPembayaran ?? 0,0,',','.') }}</span>
                <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Hari Ini: Rp {{ number_format($pembayaranHariIni ?? 0,0,',','.') }}</span>
            </div>
        </div>
    </div>

    {{-- STATISTICS --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex justify-between items-start">
                <div>
                    <p class="mb-1 text-xs font-bold uppercase tracking-wider text-slate-500">Total Pendaftar</p>
                    <h2 class="text-3xl font-bold text-slate-800">
                        {{ $totalPendaftar }}
                    </h2>
                </div>
                <div class="rounded-xl bg-blue-100 p-3 text-blue-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-xs font-medium text-slate-500">Data seluruh pendaftar tersimpan</span>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex justify-between items-start">
                <div>
                    <p class="mb-1 text-xs font-bold uppercase tracking-wider text-slate-500">Total Peserta Didik</p>
                    <h2 class="text-3xl font-bold text-slate-800">
                        {{ $totalSiswa }}
                    </h2>
                </div>
                <div class="rounded-xl bg-emerald-100 p-3 text-emerald-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-xs font-medium text-slate-500">Status peserta didik aktif</span>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex justify-between items-start">
                <div>
                    <p class="mb-1 text-xs font-bold uppercase tracking-wider text-slate-500">Daftar Hari Ini</p>
                    <h2 class="text-3xl font-bold text-slate-800">
                        {{ $pendaftarHariIni }}
                    </h2>
                </div>
                <div class="rounded-xl bg-amber-100 p-3 text-amber-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-xs font-medium text-slate-500">Pendaftaran baru tanggal {{ now()->format('d M Y') }}</span>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex justify-between items-start">
                <div>
                    <p class="mb-1 text-xs font-bold uppercase tracking-wider text-slate-500">Pembayaran (Hari Ini)</p>
                    <h2 class="mt-1 text-2xl font-bold text-slate-800">
                        Rp {{ number_format($pembayaranHariIni ?? 0,0,',','.') }}
                    </h2>
                </div>
                <div class="rounded-xl bg-indigo-100 p-3 text-indigo-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-xs font-medium text-slate-500">Total pemasukan pada hari berjalan</span>
            </div>
        </div>

    </div>

    {{-- TABLE PENDAFTAR TERBARU --}}
    <div class="card p-0 overflow-hidden">
        <div class="flex flex-col items-center justify-between gap-3 border-b border-slate-200 p-6 sm:flex-row">
            <h3 class="text-lg font-semibold text-slate-800">
                Pendaftar Terbaru
            </h3>
            <a href="{{ route('pendaftar.index') }}" class="mt-2 inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 sm:mt-0">
                Lihat Semua
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-100 text-left text-sm font-semibold text-slate-700">
                    <tr>
                        <th class="px-4 py-3">Nama Peserta</th>
                        <th class="px-4 py-3">No Registrasi</th>
                        <th class="px-4 py-3">Tanggal Daftar</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($pendaftarTerbaru as $p)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-800">{{ $p->nama }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full border border-sky-200 bg-sky-50 px-2.5 py-0.5 font-mono text-xs text-sky-700">
                                {{ optional($p->registration)->nomor_registrasi ?? '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $p->created_at->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('pendaftar.show', $p->id) }}" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-8 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="mb-3 w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                <span>Belum ada pendaftar terbaru</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 px-4 py-3">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <form method="GET" class="flex items-center gap-2 text-xs text-slate-600">
                    @foreach(request()->except(['page', 'per_page']) as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <label for="perPageDashboard">Tampilkan</label>
                    <select id="perPageDashboard" name="per_page" onchange="this.form.submit()" class="rounded border border-slate-300 px-2 py-1 text-xs">
                        @foreach([10,20,50,100] as $size)
                            <option value="{{ $size }}" {{ (int) request('per_page', $perPage ?? 10) === $size ? 'selected' : '' }}>{{ $size }}</option>
                        @endforeach
                    </select>
                    <span>data</span>
                </form>
                <div>
                    {{ $pendaftarTerbaru->links() }}
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
