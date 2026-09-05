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

    <div class="grid gap-6">

        {{-- TOMBOL TAMBAH (form tambah dipindah ke modal) --}}
        <div class="card">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-base font-semibold text-slate-800">Data Tahun Ajaran</h2>
                    <p class="mt-1 text-xs text-slate-500">Buat periode baru, lalu tentukan apakah langsung dijadikan aktif.</p>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row md:flex-row md:items-center">
                    <button onclick="openModal('modalTambahTahun')"
                        class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                        + Tambah Tahun Ajaran
                    </button>
                </div>
            </div>
        </div>

        <div class="card p-0 overflow-hidden">
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
                            <th class="px-4 py-3 text-center">Batas Maksimal Lahir</th>
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
                                    @if($item->batas_maksimal_lahir)
                                        <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                                            {{ $item->batas_maksimal_lahir->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                                            Belum diatur
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <div class="flex flex-wrap justify-center gap-2">
                                        <button type="button" onclick="openBatasLahirModal('{{ $item->id }}', '{{ $item->nama }}', '{{ $item->batas_maksimal_lahir?->format('Y-m-d') }}')"
                                            class="rounded-lg border border-indigo-200 bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">
                                            Atur Batas
                                        </button>

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
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">
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

    </div>

    {{-- MODAL TAMBAH TAHUN AJARAN --}}
    <div id="modalTambahTahun" class="fixed inset-0 z-[300] hidden items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm transition-all duration-300">
        <div class="w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl transform transition-all duration-300 sm:p-8">
            <div class="mb-6 flex items-start justify-between gap-3">
                <div>
                    <h3 class="text-xl font-bold text-slate-800">Tambah Tahun Ajaran</h3>
                    <p class="mt-1 text-xs text-slate-500">Buat periode baru, lalu tentukan apakah langsung dijadikan aktif.</p>
                </div>
                <button type="button" onclick="closeModal('modalTambahTahun')"
                    class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('tahun-ajaran.store') }}" class="flex flex-col gap-4">
                @csrf

                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nama Tahun Ajaran <span class="text-red-500">*</span></label>
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

                <div class="rounded-xl border border-blue-200 bg-blue-50 p-3 text-xs text-blue-700">
                    Tips: hanya satu tahun ajaran yang boleh aktif pada satu waktu. Saat memilih aktif, sistem akan menonaktifkan periode aktif sebelumnya.
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-200 pt-4">
                    <button type="button" onclick="closeModal('modalTambahTahun')"
                        class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-500/30 hover:bg-blue-700 transition-all">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL ATUR BATAS LAHIR --}}
    <div id="modalBatasLahir" class="fixed inset-0 z-[300] hidden items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm transition-all duration-300">
        <div class="w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl transform transition-all duration-300 sm:p-8">
            <div class="mb-6 flex items-start justify-between gap-3">
                <div>
                    <h3 class="text-xl font-bold text-slate-800">Atur Batas Maksimal Lahir</h3>
                    <p class="mt-1 text-xs text-slate-500">
                        Tentukan tanggal batas maksimal lahir calon siswa untuk tahun ajaran
                        <span id="batasLahirTahunNama" class="font-semibold text-slate-700"></span>.
                    </p>
                </div>
                <button type="button" onclick="closeBatasLahirModal()"
                    class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="formBatasLahir" method="POST" action="" class="flex flex-col gap-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Tanggal Batas Maksimal Lahir <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="batas_maksimal_lahir" id="batasLahirInput"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100"
                        required>
                    <p class="text-xs text-slate-500 mt-1">
                        Calon siswa yang lahir <span class="font-semibold">setelah</span> tanggal ini
                        dianggap belum memenuhi syarat usia dan tidak dapat mendaftar pada tahun ajaran ini.
                    </p>
                </div>

                <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-700">
                    Tips: umumnya batas lahir PPDB SD adalah <span class="font-semibold">30 Juni</span>
                    pada tahun dimana calon siswa minimal berusia 6 tahun. Contoh: untuk pendaftar
                    minimal 6 tahun per 30 Juni 2026, isi dengan <span class="font-semibold">30/06/2020</span>.
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-200 pt-4">
                    <button type="button" onclick="closeBatasLahirModal()"
                        class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openBatasLahirModal(id, nama, current) {
            const form = document.getElementById('formBatasLahir');
            if (!form) return;
            const baseUrl = "{{ route('tahun-ajaran.batas-lahir', '__ID__') }}";
            form.action = baseUrl.replace('__ID__', id);

            document.getElementById('batasLahirTahunNama').textContent = nama || '';
            document.getElementById('batasLahirInput').value = current || '';

            const modal = document.getElementById('modalBatasLahir');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeBatasLahirModal() {
            const modal = document.getElementById('modalBatasLahir');
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }
    </script>

@endsection
