@extends('layouts.admin')
@section('title','Tahun Ajaran')
@section('page-title', 'Tahun Ajaran')

@section('content')

<div class="mx-auto max-w-7xl space-y-6">
    @php
        $totalTahun = $data->total();
        $aktifCount = $data->getCollection()->where('aktif', true)->count();
        $nonAktifCount = $data->getCollection()->where('aktif', false)->count();
    @endphp

    <div class="rounded-2xl border border-slate-200 bg-gradient-to-r from-slate-50 via-white to-blue-50 p-5 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-800">Manajemen Tahun Ajaran</h2>
                <p class="mt-1 text-sm text-slate-600">Kelola periode tahun ajaran aktif untuk sinkronisasi seluruh flow PPDB.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700">
                    Total: {{ $totalTahun }}
                </span>
                <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                    Aktif: {{ $aktifCount }}
                </span>
                <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                    Tidak Aktif: {{ $nonAktifCount }}
                </span>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-5">

        <div class="card p-0 overflow-hidden lg:col-span-3">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-base font-semibold text-slate-800">Daftar Tahun Ajaran</h2>
                <p class="mt-1 text-xs text-slate-500">Aktifkan satu periode tahun ajaran sebagai acuan utama sistem.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th class="px-4 py-3 text-left w-16">No</th>
                            <th class="px-4 py-3 text-left">Nama</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        @forelse($data as $item)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3 text-slate-500">{{ $data->firstItem() + $loop->index }}</td>

                                <td class="px-4 py-3 font-medium">
                                    {{ $item->nama }}
                                </td>

                                <td class="px-4 py-3 text-center">
                                    @if($item->aktif)
                                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-700" title="Aktif" aria-label="Aktif">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </span>
                                    @else
                                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-rose-100 text-rose-700" title="Tidak Aktif" aria-label="Tidak Aktif">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <div class="flex flex-wrap justify-center gap-2">
                                        @if(!$item->aktif)
                                        <form method="POST" action="{{ route('tahun-ajaran.aktifkan',$item) }}">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit" class="rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                                                Aktifkan
                                            </button>
                                        </form>
                                        @endif

                                        <form method="POST" action="{{ route('tahun-ajaran.destroy',$item) }}" onsubmit="return window.globalConfirmSubmit(this, 'Yakin ingin menghapus tahun ajaran ini?', { title: 'Konfirmasi Hapus' });">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-rose-200 bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-100">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-slate-500">
                                    Data tahun ajaran belum tersedia
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-200 px-4 py-3 md:flex-row md:items-center md:justify-between">
                <form method="GET" class="flex items-center gap-2 text-xs text-slate-600">
                    <label for="perPageTahun">Tampilkan</label>
                    <select id="perPageTahun" name="per_page" onchange="this.form.submit()" class="rounded border border-slate-300 px-2 py-1 text-xs">
                        @foreach([10,20,50,100] as $size)
                            <option value="{{ $size }}" {{ (int) request('per_page', $perPage ?? 20) === $size ? 'selected' : '' }}>{{ $size }}</option>
                        @endforeach
                    </select>
                    <span>data</span>
                </form>

                <div>
                    {{ $data->links() }}
                </div>
            </div>
        </div>

        <div class="card lg:col-span-2">
            <h2 class="text-base font-semibold text-slate-800">Tambah Tahun Ajaran</h2>
            <p class="mt-1 text-xs text-slate-500">Buat periode baru, lalu tentukan apakah langsung dijadikan aktif.</p>

            <form method="POST" action="{{ route('tahun-ajaran.store') }}" class="mt-5 flex flex-col gap-4">
                @csrf

                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nama Tahun Ajaran</label>
                    <input type="text" name="nama" placeholder="2025/2026" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" required>
                    <p class="text-xs text-slate-500 mt-1">
                        Contoh: <span class="font-medium">2025/2026</span>
                    </p>
                </div>

                {{-- Default tidak aktif --}}
                <input type="hidden" name="aktif" value="0">

                <div class="flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                    <input type="checkbox" name="aktif" id="aktif" value="1" class="rounded border-slate-300 focus:ring-primary">
                    <label for="aktif" class="text-sm font-medium text-slate-700">
                        Aktifkan sekarang
                    </label>
                </div>

                <div>
                    <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                        Simpan
                    </button>
                </div>
            </form>

            <div class="mt-5 rounded-xl border border-blue-200 bg-blue-50 p-3 text-xs text-blue-700">
                Tips: hanya satu tahun ajaran yang boleh aktif pada satu waktu. Saat memilih aktif, sistem akan menonaktifkan periode aktif sebelumnya.
            </div>
        </div>

    </div>

</div>

@endsection
