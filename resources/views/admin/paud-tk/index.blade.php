@extends('layouts.admin')
@section('title','PAUD / TK')

@section('content')

<div class="mx-auto max-w-7xl space-y-6">
    @php
    $totalSekolah = $data->total();
    $aktifSekolah = $data->getCollection()->where('aktif', true)->count();
    $nonAktifSekolah = $data->getCollection()->where('aktif', false)->count();
    @endphp

    <div class="rounded-2xl border border-slate-200 bg-gradient-to-r from-slate-50 via-white to-cyan-50 p-5 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-800">Master PAUD / TK</h2>
                <p class="mt-1 text-sm text-slate-600">Kelola referensi satuan pendidikan PAUD dan TK untuk data pendukung siswa.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700">Total: {{ $totalSekolah }}</span>
                <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Aktif: {{ $aktifSekolah }}</span>
                <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">Nonaktif: {{ $nonAktifSekolah }}</span>
            </div>
        </div>
    </div>

    {{-- FORM TAMBAH --}}
    <div class="card">
        <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-base font-semibold text-slate-800">
                    Tambah PAUD / TK
                </h2>
                <p class="mt-1 text-xs text-slate-500">Isi data sekolah asal untuk kebutuhan administrasi PPDB.</p>
            </div>

            <button onclick="openModal('modalImport')"
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-100">
                Import Excel
            </button>
        </div>

        <form method="POST" action="{{ route('paud-tk.store') }}" class="grid gap-4" id="paudTkForm">
            @csrf

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <label class="label">NPSN</label>
                    <input type="text" name="npsn" class="input" placeholder="NPSN" value="{{ old('npsn') }}">
                    @error('npsn') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label">Nama PAUD / TK</label>
                    <input type="text" name="nama" class="input" placeholder="Nama PAUD / TK" value="{{ old('nama') }}" required>
                    @error('nama') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label">Jenis</label>
                    <select name="jenis" class="input" required>
                        <option value="">Pilih</option>
                        <option value="PAUD" {{ old('jenis')=='PAUD'?'selected':'' }}>PAUD</option>
                        <option value="TK" {{ old('jenis')=='TK'?'selected':'' }}>TK</option>
                        <option value="BA" {{ old('jenis')=='BA'?'selected':'' }}>BA</option>
                        <option value="RA" {{ old('jenis')=='RA'?'selected':'' }}>RA</option>
                    </select>
                    @error('jenis') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <label class="label">Akreditasi</label>
                    <select name="akreditasi" class="input">
                        <option value="">-</option>
                        <option value="A" {{ old('akreditasi')=='A'?'selected':'' }}>A</option>
                        <option value="B" {{ old('akreditasi')=='B'?'selected':'' }}>B</option>
                        <option value="C" {{ old('akreditasi')=='C'?'selected':'' }}>C</option>
                        <option value="Belum" {{ old('akreditasi')=='Belum'?'selected':'' }}>Belum</option>
                    </select>
                </div>

                <div>
                    <label class="label">Telepon</label>
                    <input type="text" name="telp" class="input" placeholder="No Telepon" value="{{ old('telp') }}">
                </div>

                <div>
                    <label class="label">Kelurahan</label>
                    <input type="text" name="kelurahan" class="input" placeholder="Kelurahan" value="{{ old('kelurahan') }}">
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="label">Kecamatan</label>
                    <input type="text" name="kecamatan" class="input" placeholder="Kecamatan" value="{{ old('kecamatan') }}">
                </div>

                <div>
                    <label class="label">Alamat Lengkap</label>
                    <input type="text" name="alamat" class="input" placeholder="Alamat lengkap" value="{{ old('alamat') }}">
                </div>
            </div>

            {{-- Status Aktif + Button Simpan --}}
            <div class="mt-2 flex flex-col gap-3 border-t border-slate-200 pt-3 md:flex-row md:items-center md:justify-between">
                {{-- Aktifkan Sekolah --}}
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="aktif" value="1" {{ old('aktif', 1) ? 'checked' : '' }} class="rounded border-gray-300">
                    <span class="text-sm text-slate-700">Aktifkan sekolah ini</span>
                </div>

                {{-- Tombol Simpan --}}
                <div>
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                        Simpan
                    </button>
                </div>
            </div>

        </form>

    </div>

    {{-- TABEL DATA --}}
    <div class="card p-0 overflow-hidden">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 md:flex-row md:items-center md:justify-between">
            <!-- Judul -->
            <h2 class="text-base font-semibold text-slate-800">
                Daftar PAUD / TK
            </h2>

            <div class="flex w-full flex-col gap-2 md:w-auto md:flex-row md:items-center">
                <input type="text" id="searchInput" placeholder="Cari Nama / Kelurahan / Kecamatan"
                    class="input w-full text-sm md:w-72" />

                <select id="statusFilter" class="input w-full text-sm md:w-44">
                    <option value="">Semua Status</option>
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>

                <button onclick="openModal('modalDeleteAll')"
                    class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100">
                    Hapus Semua
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead class="bg-slate-100 text-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-left">Nama</th>
                        <th class="px-4 py-3 text-left">Jenis</th>
                        <th class="px-4 py-3 text-left">Wilayah</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                    @forelse($data as $item)
                    <tr class="hover:bg-slate-50 transition cursor-pointer" data-main-row="1" data-status="{{ $item->aktif ? '1' : '0' }}" onclick="toggleDetail(this)">
                        <td class="px-4 py-3 font-medium">
                            <div class="flex items-center gap-2">
                                <span class="expand-icon text-xs text-slate-500">▶</span>
                                <span>{{ $item->nama }}</span>
                            </div>
                        </td>

                        <td class="px-4 py-3">{{ $item->jenis }}</td>
                        <td class="px-4 py-3">{{ $item->kelurahan ?? '-' }} / {{ $item->kecamatan ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($item->aktif)
                            <span class="badge-success">Aktif</span>
                            @else
                            <span class="badge-danger">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center" onclick="event.stopPropagation()">
                            <div
                                x-data="{
                                    rowId: {{ $item->id }},
                                    open: false,
                                    menuTop: 0,
                                    menuLeft: 0,
                                    toggleMenu(event) {
                                        if (this.open) {
                                            this.open = false;
                                            return;
                                        }

                                        const rect = event.currentTarget.getBoundingClientRect();
                                        this.open = true;

                                        this.$nextTick(() => {
                                            const menuWidth = this.$refs.actionMenu ? this.$refs.actionMenu.offsetWidth : 160;
                                            const menuHeight = this.$refs.actionMenu ? this.$refs.actionMenu.offsetHeight : 140;
                                            let left = rect.left;

                                            if (left + menuWidth > window.innerWidth - 12) {
                                                left = window.innerWidth - menuWidth - 12;
                                            }

                                            if (left < 12) {
                                                left = 12;
                                            }

                                            this.menuLeft = left;

                                            const openUp = (window.innerHeight - rect.bottom) < (menuHeight + 12);
                                            this.menuTop = openUp
                                                ? Math.max(12, rect.top - menuHeight - 8)
                                                : rect.bottom + 8;

                                            window.dispatchEvent(new CustomEvent('paudtk-action-open', {
                                                detail: this.rowId
                                            }));
                                        });
                                    }
                                }"
                                @scroll.window="open = false"
                                @resize.window="open = false"
                                @paudtk-action-open.window="if ($event.detail !== rowId) open = false"
                                class="inline-block text-left">
                                <button
                                    type="button"
                                    @click="toggleMenu($event)"
                                    class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white p-2 text-slate-500 shadow-sm transition hover:bg-slate-50 hover:text-blue-600 focus:outline-none">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                    </svg>
                                </button>

                                <template x-teleport="body">
                                    <div
                                        x-show="open"
                                        x-cloak
                                        @click.outside="open = false"
                                        @keydown.escape.window="open = false"
                                        x-ref="actionMenu"
                                        class="fixed z-[200] w-40 rounded-lg border border-slate-200 bg-white p-2 shadow-lg"
                                        :style="`top: ${menuTop}px; left: ${menuLeft}px;`">
                                        <form method="POST" action="{{ route('paud-tk.toggle', $item->id) }}">
                                            @csrf
                                            <button type="submit" class="flex w-full items-center gap-2 rounded-md px-2.5 py-2 text-left text-xs font-semibold text-blue-700 hover:bg-blue-50">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                </svg>
                                                {{ $item->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('paud-tk.destroy', $item->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                onclick="return window.globalConfirmSubmit(this.form, 'Hapus data ini?', { title: 'Konfirmasi Hapus' })"
                                                class="flex w-full items-center gap-2 rounded-md px-2.5 py-2 text-left text-xs font-semibold text-red-700 hover:bg-red-50">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12m-9 0V5a1 1 0 011-1h4a1 1 0 011 1v2m-7 4v6m4-6v6M5 7l1 12a2 2 0 002 2h8a2 2 0 002-2l1-12" />
                                                </svg>
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </template>
                            </div>
                        </td>

                    </tr>
                    <tr class="detail-row hidden bg-slate-50">
                        <td colspan="5" class="px-4 py-3 text-sm text-slate-600">
                            <strong>NPSN:</strong> {{ $item->npsn ?? '-' }} <br>
                            <strong>Akreditasi:</strong> {{ $item->akreditasi ?? '-' }} <br>
                            <strong>Alamat:</strong> {{ $item->alamat ?? '-' }} <br>
                            <strong>Telepon:</strong> {{ $item->telp ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">
                            Data PAUD / TK belum tersedia
                        </td>
                    </tr>
                    @endforelse

                </tbody>

            </table>
        </div>

        <div class="flex flex-col gap-3 border-t border-slate-200 px-4 py-3 md:flex-row md:items-center md:justify-between">
            <form method="GET" class="flex items-center gap-2 text-xs text-slate-600">
                <label for="perPagePaudTk">Tampilkan</label>
                <select id="perPagePaudTk" name="per_page" onchange="this.form.submit()" class="rounded border border-slate-300 px-2 py-1 text-xs">
                    @foreach([10,20,50,100] as $size)
                    <option value="{{ $size }}" {{ (int) request('per_page', $perPage ?? 30) === $size ? 'selected' : '' }}>{{ $size }}</option>
                    @endforeach
                </select>
                <span>data</span>
            </form>

            <div>
                {{ $data->links() }}
            </div>
        </div>
    </div>

    {{-- MODAL HAPUS SEMUA --}}
    <div id="modalDeleteAll" class="fixed inset-0 z-[300] hidden items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm transition-all duration-300">
        <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-8 shadow-2xl transform transition-all duration-300">
            <div class="w-20 h-20 bg-red-50 text-red-600 rounded-3xl flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>

            <div class="text-center mb-8">
                <h3 class="text-xl font-bold text-slate-800">Hapus Semua Data?</h3>
                <p class="text-sm text-slate-500 mt-2 leading-relaxed">Apakah Anda yakin ingin menghapus <strong>seluruh data PAUD / TK</strong>? Aksi ini akan menghapus semua catatan secara permanen.</p>
            </div>

            <div class="flex gap-3">
                <button type="button"
                    onclick="closeModal('modalDeleteAll')"
                    class="flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                    Batal
                </button>

                <form method="POST" action="{{ route('paud-tk.destroyAll') }}" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full rounded-xl bg-red-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-red-500/30 hover:bg-red-700 transition-all">
                        Ya, Bersihkan
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

{{-- MODAL IMPORT --}}
<div id="modalImport" class="fixed inset-0 z-[300] hidden items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm transition-all duration-300">
    <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-8 shadow-2xl transform transition-all duration-300">

        <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
            📥
        </div>

        <div class="text-center mb-6">
            <h3 class="text-xl font-bold text-slate-800">Import Data PAUD / TK</h3>
            <p class="text-sm text-slate-500 mt-1">Unggah file Excel untuk menambah data sekolah masal.</p>
        </div>

        <form action="{{ route('paud-tk.import') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">File Excel (.xlsx, .xls)</label>
                <input type="file" name="file" class="w-full text-xs text-slate-600 file:mr-3 file:rounded-xl file:border-0 file:bg-blue-100 file:px-4 file:py-2.5 file:text-[10px] file:font-bold file:uppercase file:tracking-widest file:text-blue-700 hover:file:bg-blue-200 transition-all" required accept=".xlsx,.xls">
            </div>

            <div class="rounded-2xl bg-slate-50 p-4 border border-slate-100">
                <p class="text-[11px] text-slate-500 leading-relaxed text-center">
                    Gunakan template excel agar format sesuai sistem. <br>
                    <a href="{{ route('paud-tk.template') }}" class="font-bold text-blue-600 hover:text-blue-700 hover:underline">Download Template Excel</a>
                </p>
            </div>

            <div class="flex gap-3">
                <button type="button"
                    onclick="closeModal('modalImport')"
                    class="flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                    Batal
                </button>

                <button type="submit" class="flex-1 rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-blue-500/30 hover:bg-blue-700 transition-all">
                    Import
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Toggle detail row
    function toggleDetail(row) {
        let next = row.nextElementSibling;
        if (next && next.classList.contains('detail-row')) {
            next.classList.toggle('hidden');
            let icon = row.querySelector('.expand-icon');
            if (icon) {
                icon.textContent = next.classList.contains('hidden') ? '▶' : '▼';
            }
        }
    }

    function applyFilter() {
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const keyword = (searchInput?.value || '').toLowerCase().trim();
        const statusValue = statusFilter?.value || '';

        const mainRows = document.querySelectorAll('tbody tr[data-main-row="1"]');

        mainRows.forEach(row => {
            const detail = row.nextElementSibling;
            const nameText = row.cells[0]?.textContent.toLowerCase() || '';
            const wilayahText = row.cells[2]?.textContent.toLowerCase() || '';
            const rowStatus = row.dataset.status || '';

            const matchKeyword = keyword === '' || nameText.includes(keyword) || wilayahText.includes(keyword);
            const matchStatus = statusValue === '' || rowStatus === statusValue;
            const visible = matchKeyword && matchStatus;

            row.style.display = visible ? '' : 'none';

            if (detail && detail.classList.contains('detail-row')) {
                detail.classList.add('hidden');
                detail.style.display = visible ? '' : 'none';
            }

            const icon = row.querySelector('.expand-icon');
            if (icon) {
                icon.textContent = '▶';
            }
        });
    }

    // Tunggu DOM siap
    document.addEventListener("DOMContentLoaded", function() {

        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', applyFilter);
        }

        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {
            statusFilter.addEventListener('change', applyFilter);
        }

        // ===== LOADING BUTTON SIMPAN PAUD/TK =====
        const paudForm = document.querySelector('form[action*="paud-tk.store"]');
        if (paudForm) {
            const submitBtn = paudForm.querySelector('button[type="submit"]');
            paudForm.addEventListener('submit', function() {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Loading...';
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            });
        }

        // ===== LOADING BUTTON HAPUS INDIVIDUAL =====
        const deleteForms = document.querySelectorAll('form[action*="paud-tk.destroy"]');
        deleteForms.forEach(form => {
            form.addEventListener('submit', function() {
                const btn = form.querySelector('button[type="submit"]');
                if (btn) {
                    btn.disabled = true;
                    btn.textContent = 'Menghapus...';
                    btn.classList.add('opacity-50', 'cursor-not-allowed');
                }
            });
        });

        // ===== LOADING BUTTON HAPUS SEMUA =====
        const deleteAllForm = document.querySelector('form[action*="paud-tk.destroyAll"]');
        if (deleteAllForm) {
            deleteAllForm.addEventListener('submit', function() {
                const btn = deleteAllForm.querySelector('button[type="submit"]');
                if (btn) {
                    btn.disabled = true;
                    btn.textContent = 'Menghapus semua...';
                    btn.classList.add('opacity-50', 'cursor-not-allowed');
                }
            });
        }

    });
</script>

@endsection