@extends('layouts.admin')

@section('title','Management Kelas')

@section('content')
<div class="mx-auto max-w-7xl space-y-4">
    <div class="rounded-2xl border border-slate-200 bg-gradient-to-r from-slate-50 via-white to-blue-50 p-4 md:p-5">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-lg font-semibold text-slate-800">Management Kelas</h1>
                <p class="mt-1 text-xs text-slate-600">
                    Tahun ajaran: <span class="font-semibold text-slate-700">{{ $tahunAktif->nama }}</span>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    onclick="openTambahKelasModal()"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Kelas</span>
                </button>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-2.5">
            <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-700">
                Total Kelas: {{ $kelasList?->count() ?? 0 }}
            </span>
            <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                Total Peserta Didik Aktif: {{ $totalSiswaAktif ?? 0 }}
            </span>
            <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                Belum Masuk Kelas: {{ $totalBelumMasukKelas ?? 0 }}
            </span>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="p-4 md:p-5">
            <div class="mb-4 rounded-xl border border-blue-200 bg-blue-50 p-3">
                <h4 class="text-xs font-semibold uppercase tracking-wide text-blue-700">Aksi Cepat</h4>
                <p class="mt-1 text-xs text-blue-700/90">Masukkan peserta didik yang belum memiliki kelas langsung dari panel ini.</p>

                <form id="quickAssignForm" method="POST" onsubmit="return prepareQuickAssignForm(event)" data-action-template="{{ route('siswa.assign-kelas', '__SID__') }}" class="mt-3 grid grid-cols-1 gap-2 md:grid-cols-12 md:items-end">
                    @csrf
                    <div class="md:col-span-6">
                        <label for="quickAssignSiswa" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-blue-700">Peserta Didik Belum Kelas</label>
                        <select id="quickAssignSiswa" class="w-full rounded-lg border border-blue-200 bg-white px-2.5 py-2 text-xs text-slate-700 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                            <option value="">Pilih peserta didik...</option>
                            @foreach($siswaBelumKelas as $siswaItem)
                            <option value="{{ $siswaItem->id }}">
                                {{ $siswaItem->nama }} ({{ optional($siswaItem->registration)->nomor_registrasi ?? '-' }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-4">
                        <label for="quickAssignKelas" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-blue-700">Kelas Tujuan</label>
                        <select id="quickAssignKelas" name="kelas_siswa_id" class="w-full rounded-lg border border-blue-200 bg-white px-2.5 py-2 text-xs text-slate-700 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" required>
                            <option value="">Pilih kelas...</option>
                            @foreach($kelasList as $kelas)
                            <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="w-full rounded-lg bg-blue-600 px-2.5 py-2 text-xs font-semibold text-white transition hover:bg-blue-700">Assign</button>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-700">
                        <tr>
                            <th class="px-3 py-2 text-left">Nama Kelas</th>
                            <th class="px-3 py-2 text-left">Peserta Didik Aktif</th>
                            <th class="px-3 py-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($kelasList as $kelas)
                        <tr>
                            <td class="px-3 py-2">
                                <form method="POST" action="{{ route('siswa.kelas.update', $kelas->id) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <input
                                        type="text"
                                        name="nama_kelas"
                                        value="{{ $kelas->nama_kelas }}"
                                        class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs text-slate-700 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                        required>
                                    <button type="submit" class="rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                                        Simpan
                                    </button>
                                </form>
                            </td>
                            <td class="px-3 py-2">
                                <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ (int) ($kelas->siswa_aktif_count ?? 0) > 0 ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-50 text-slate-600' }}">
                                    {{ (int) ($kelas->siswa_aktif_count ?? 0) }} peserta didik
                                </span>
                            </td>
                            <td class="px-3 py-2 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('siswa.index', ['kelas_id' => $kelas->id, 'from_menu' => 1]) }}" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-blue-600">
                                        Lihat
                                    </a>
                                    <button type="button" onclick="setQuickAssignClass('{{ $kelas->id }}')" class="rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                                        Pilih untuk Assign
                                    </button>

                                    @if((int) ($kelas->siswa_count ?? 0) === 0)
                                    <form method="POST" action="{{ route('siswa.kelas.destroy', $kelas->id) }}" onsubmit="return window.globalConfirmSubmit(this, 'Hapus kelas {{ $kelas->nama_kelas }}?', { title: 'Konfirmasi Hapus Kelas' })" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-rose-200 bg-rose-50 px-2.5 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100">
                                            Hapus
                                        </button>
                                    </form>
                                    @else
                                    <button type="button" disabled class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs font-semibold text-slate-400 cursor-not-allowed" title="Kelas masih terisi">
                                        Hapus
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-3 py-6 text-center text-xs text-slate-500">Belum ada kelas dibuat.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="modalTambahKelas" class="fixed inset-0 z-[300] hidden items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm transition-all duration-300" onclick="if(event.target===this)closeTambahKelasModal()">
    <div class="w-full max-w-sm rounded-3xl border border-slate-200 bg-white p-8 shadow-2xl transform transition-all duration-300" onclick="event.stopPropagation()">

        <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
            🏫
        </div>

        <div class="text-center mb-6">
            <h3 class="text-xl font-bold text-slate-800">Tambah Kelas</h3>
            <p class="text-sm text-slate-500 mt-1">Buat ruang kelas baru untuk tahun ajaran ini.</p>
        </div>

        <form action="{{ route('siswa.kelas.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="nama_kelas" class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Nama Kelas</label>
                <input
                    id="nama_kelas"
                    type="text"
                    name="nama_kelas"
                    class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all"
                    placeholder="Contoh: 1A / 1B / Tahfidz"
                    required>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeTambahKelasModal()" class="flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors">Batal</button>
                <button type="submit" class="flex-1 rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-blue-500/30 hover:bg-blue-700 transition-all">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function setQuickAssignClass(kelasId) {
        const selectKelas = document.getElementById('quickAssignKelas');
        if (!selectKelas) {
            return;
        }

        selectKelas.value = String(kelasId || '');
        if (typeof window.showGlobalToast === 'function') {
            window.showGlobalToast('info', 'Kelas tujuan quick assign dipilih.');
        }
    }

    function prepareQuickAssignForm(event) {
        event.preventDefault();

        const form = document.getElementById('quickAssignForm');
        const siswaSelect = document.getElementById('quickAssignSiswa');
        const kelasSelect = document.getElementById('quickAssignKelas');

        if (!form || !siswaSelect || !kelasSelect) {
            return false;
        }

        const siswaId = (siswaSelect.value || '').trim();
        const kelasId = (kelasSelect.value || '').trim();

        if (!siswaId) {
            if (typeof window.showGlobalToast === 'function') {
                window.showGlobalToast('warning', 'Pilih peserta didik yang belum memiliki kelas.');
            }
            return false;
        }

        if (!kelasId) {
            if (typeof window.showGlobalToast === 'function') {
                window.showGlobalToast('warning', 'Pilih kelas tujuan terlebih dahulu.');
            }
            return false;
        }

        const template = form.getAttribute('data-action-template') || '';
        if (!template.includes('__SID__')) {
            if (typeof window.showGlobalToast === 'function') {
                window.showGlobalToast('danger', 'Template quick assign tidak valid.');
            }
            return false;
        }

        form.action = template.replace('__SID__', encodeURIComponent(siswaId));
        form.submit();
        return false;
    }

    function openTambahKelasModal() {
        const modal = document.getElementById('modalTambahKelas');
        if (!modal) {
            return;
        }
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeTambahKelasModal() {
        const modal = document.getElementById('modalTambahKelas');
        if (!modal) {
            return;
        }
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }
</script>
@endsection