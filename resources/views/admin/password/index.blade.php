@extends('layouts.admin')

@section('title', 'Password & Batasan')
@section('page-title', 'Password & Batasan')

@section('content')

<div class="mx-auto max-w-6xl space-y-6" x-data="{ tab: '{{ $activeTab }}' }">

    {{-- HEADER --}}
    <div class="rounded-2xl border border-slate-200 bg-gradient-to-r from-slate-50 via-white to-blue-50 p-5 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-800">Manajemen Password</h2>
                <p class="mt-1 text-sm text-slate-600">
                    Kelola kredensial akses panitia & petugas keuangan yang dipakai di halaman publik.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                    Panitia: {{ $panitia->total() }}
                </span>
                <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                    Petugas Keuangan: {{ $petugas->total() }}
                </span>
            </div>
        </div>
    </div>

    {{-- TAB NAV --}}
    <div class="card p-0 overflow-hidden">
        <div class="flex flex-col gap-2 border-b border-slate-200 px-4 pt-3 md:flex-row md:items-end md:gap-1">
            <button type="button" @click="tab='panitia'"
                :class="tab==='panitia' ? 'border-indigo-600 text-indigo-700 bg-indigo-50/60' : 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-50'"
                class="rounded-t-lg border-b-2 px-4 py-2.5 text-sm font-semibold transition-colors">
                🔑 Password Panitia
            </button>
            <button type="button" @click="tab='petugas'"
                :class="tab==='petugas' ? 'border-indigo-600 text-indigo-700 bg-indigo-50/60' : 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-50'"
                class="rounded-t-lg border-b-2 px-4 py-2.5 text-sm font-semibold transition-colors">
                💼 Password Petugas Keuangan
            </button>
        </div>
    </div>

    {{-- ===================== TAB PANITIA ===================== --}}
    <div x-show="tab==='panitia'" x-cloak class="space-y-6">

        <div class="grid gap-6 md:grid-cols-2">
            <div class="card">
                <h3 class="text-base font-semibold text-slate-800">Tambah Password Panitia</h3>
                <p class="mt-1 text-xs text-slate-500">
                    Setiap panitia yang dibuat bisa memverifikasi akses pembayaran pada halaman publik.
                </p>

                <form method="POST" action="{{ route('admin.password.panitia.store') }}" class="mt-5 space-y-4">
                    @csrf

                    <div>
                        <label class="label">Nama Panitia</label>
                        <input type="text" name="nama" class="input" placeholder="Contoh: Panitia 1" required maxlength="100">
                    </div>

                    <div>
                        <label class="label">Password</label>
                        <input type="password" name="password" class="input" placeholder="Masukkan password" required maxlength="50">
                    </div>

                    <button type="submit" class="btn-primary">Simpan</button>
                </form>
            </div>

            <div class="card">
                <h3 class="font-semibold text-slate-800 mb-3">Keterangan</h3>
                <ul class="text-sm text-slate-600 space-y-2 list-disc pl-5">
                    <li>Password panitia dipakai untuk membuka akses pembayaran / cetak nota di halaman publik.</li>
                    <li>Verifikasi publik memeriksa <span class="font-semibold">semua</span> panitia yang ada. Salah satu match → akses dibuka.</li>
                    <li>Gunakan password yang kuat dan hanya dibagikan ke panitia terkait.</li>
                </ul>
            </div>
        </div>

        <div class="card p-0 overflow-hidden">
            <div class="border-b border-slate-200 px-5 py-4">
                <h3 class="text-base font-semibold text-slate-800">Daftar Panitia</h3>
                <p class="mt-1 text-xs text-slate-500">Kelola kredensial panitia untuk akses publik.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr class="border-b">
                            <th class="px-4 py-3 text-left">No</th>
                            <th class="px-4 py-3 text-left">Nama Panitia</th>
                            <th class="px-4 py-3 text-left">Dibuat</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($panitia as $item)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3">{{ $panitia->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3 font-medium">{{ $item->nama }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $item->created_at?->format('d M Y H:i') ?? '-' }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <button type="button"
                                    onclick="openEditPanitiaModal('{{ route('admin.password.panitia.update', $item->id) }}', @json($item->nama))"
                                    class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 mr-2">
                                    Edit
                                </button>

                                <form method="POST" action="{{ route('admin.password.panitia.destroy', $item->id) }}"
                                    onsubmit="return window.globalConfirmSubmit(this, 'Yakin ingin menghapus panitia ini?', { title: 'Konfirmasi Hapus' })"
                                    class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button class="px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-slate-500">Belum ada data panitia.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-200 px-4 py-3 md:flex-row md:items-center md:justify-between">
                <form method="GET" class="flex items-center gap-2 text-xs text-slate-600" data-disable-auto-loading="true">
                    <input type="hidden" name="tab" value="panitia">
                    <label for="perPagePanitia">Tampilkan</label>
                    <select id="perPagePanitia" name="per_page_panitia" onchange="this.form.submit()"
                        class="rounded border border-slate-300 px-2 py-1 text-xs">
                        @foreach([10,20,50,100] as $size)
                        <option value="{{ $size }}" {{ (int) request('per_page_panitia', $perPagePanitia ?? 20) === $size ? 'selected' : '' }}>
                            {{ $size }}
                        </option>
                        @endforeach
                    </select>
                    <span>data</span>
                </form>

                <div>
                    {{ $panitia->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== TAB PETUGAS KEUANGAN ===================== --}}
    <div x-show="tab==='petugas'" x-cloak class="space-y-6">

        <div class="grid gap-6 md:grid-cols-2">
            <div class="card">
                <h3 class="text-base font-semibold text-slate-800">Tambah Password Petugas Keuangan</h3>
                <p class="mt-1 text-xs text-slate-500">
                    Petugas keuangan dipakai untuk membuka halaman statistik keuangan publik.
                </p>

                <form method="POST" action="{{ route('admin.password.petugas-keuangan.store') }}" class="mt-5 space-y-4">
                    @csrf

                    <div>
                        <label class="label">Nama Petugas</label>
                        <input type="text" name="nama" class="input" placeholder="Masukkan nama petugas" required maxlength="100">
                    </div>

                    <div>
                        <label class="label">Password</label>
                        <input type="password" name="password" class="input" placeholder="Masukkan password" required maxlength="50">
                    </div>

                    <button type="submit" class="btn-primary">Simpan</button>
                </form>
            </div>

            <div class="card">
                <h3 class="font-semibold text-slate-800 mb-3">Keterangan</h3>
                <ul class="text-sm text-slate-600 space-y-2 list-disc pl-5">
                    <li>Data petugas ini digunakan untuk akses halaman Statistik Keuangan publik.</li>
                    <li>Akses hanya diberikan jika nama dan password valid.</li>
                    <li>Gunakan password yang kuat dan hanya dibagikan ke petugas terkait.</li>
                </ul>
            </div>
        </div>

        <div class="card p-0 overflow-hidden">
            <div class="border-b border-slate-200 px-5 py-4">
                <h3 class="text-base font-semibold text-slate-800">Daftar Petugas Keuangan</h3>
                <p class="mt-1 text-xs text-slate-500">Kelola kredensial petugas keuangan untuk akses publik.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr class="border-b">
                            <th class="px-4 py-3 text-left">No</th>
                            <th class="px-4 py-3 text-left">Nama Petugas</th>
                            <th class="px-4 py-3 text-left">Dibuat</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($petugas as $item)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3">{{ $petugas->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3 font-medium">{{ $item->nama }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $item->created_at?->format('d M Y H:i') ?? '-' }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <button type="button"
                                    onclick="openEditPetugasModal('{{ route('admin.password.petugas-keuangan.update', $item->id) }}', @json($item->nama))"
                                    class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 mr-2">
                                    Edit
                                </button>

                                <form method="POST" action="{{ route('admin.password.petugas-keuangan.destroy', $item->id) }}"
                                    onsubmit="return window.globalConfirmSubmit(this, 'Yakin ingin menghapus petugas ini?', { title: 'Konfirmasi Hapus' })"
                                    class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button class="px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-slate-500">Belum ada data petugas keuangan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-200 px-4 py-3 md:flex-row md:items-center md:justify-between">
                <form method="GET" class="flex items-center gap-2 text-xs text-slate-600" data-disable-auto-loading="true">
                    <input type="hidden" name="tab" value="petugas">
                    <label for="perPagePetugas">Tampilkan</label>
                    <select id="perPagePetugas" name="per_page_petugas" onchange="this.form.submit()"
                        class="rounded border border-slate-300 px-2 py-1 text-xs">
                        @foreach([10,20,50,100] as $size)
                        <option value="{{ $size }}" {{ (int) request('per_page_petugas', $perPagePetugas ?? 20) === $size ? 'selected' : '' }}>
                            {{ $size }}
                        </option>
                        @endforeach
                    </select>
                    <span>data</span>
                </form>

                <div>
                    {{ $petugas->links() }}
                </div>
            </div>
        </div>
    </div>

</div>

{{-- MODAL EDIT PANITIA --}}
<div id="modalEditPanitia"
    class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center z-modal p-4 transition-all duration-300"
    onclick="if(event.target===this)closeEditPanitiaModal()">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm p-8 relative transform transition-all duration-300"
        onclick="event.stopPropagation()">

        <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
            🔑
        </div>

        <div class="text-center mb-6">
            <h3 class="text-xl font-bold text-slate-800">Edit Panitia</h3>
            <p class="text-sm text-slate-500 mt-1">Perbarui informasi akses panitia.</p>
        </div>

        <form id="formEditPanitia" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="space-y-4 mb-6 text-left">
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Nama Panitia</label>
                    <input type="text" name="nama" id="editPanitiaNama"
                        class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500"
                        required>
                </div>

                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Password Baru (opsional)</label>
                    <input type="password" name="password"
                        class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Biarkan kosong jika tidak diubah">
                </div>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="closeEditPanitiaModal()"
                    class="flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors">Batal</button>
                <button type="submit"
                    class="flex-1 rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-blue-500/30 hover:bg-blue-700 transition-all">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT PETUGAS --}}
<div id="modalEditPetugas"
    class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center z-modal p-4 transition-all duration-300"
    onclick="if(event.target===this)closeEditPetugasModal()">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm p-8 relative transform transition-all duration-300"
        onclick="event.stopPropagation()">

        <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
            💼
        </div>

        <div class="text-center mb-6">
            <h3 class="text-xl font-bold text-slate-800">Edit Petugas</h3>
            <p class="text-sm text-slate-500 mt-1">Perbarui informasi akses petugas keuangan.</p>
        </div>

        <form id="formEditPetugas" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="space-y-4 mb-6 text-left">
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Nama Petugas</label>
                    <input type="text" name="nama" id="editPetugasNama"
                        class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500"
                        required>
                </div>

                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Password Baru (opsional)</label>
                    <input type="password" name="password"
                        class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Biarkan kosong jika tidak diubah">
                </div>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="closeEditPetugasModal()"
                    class="flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors">Batal</button>
                <button type="submit"
                    class="flex-1 rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-blue-500/30 hover:bg-blue-700 transition-all">Simpan</button>
            </div>
        </form>
    </div>
</div>

<style>[x-cloak]{display:none !important;}</style>

<script>
    function openEditPanitiaModal(actionUrl, nama) {
        document.getElementById('formEditPanitia').action = actionUrl;
        document.getElementById('editPanitiaNama').value = nama || '';

        const modal = document.getElementById('modalEditPanitia');
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeEditPanitiaModal() {
        const modal = document.getElementById('modalEditPanitia');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    function openEditPetugasModal(actionUrl, nama) {
        document.getElementById('formEditPetugas').action = actionUrl;
        document.getElementById('editPetugasNama').value = nama || '';

        const modal = document.getElementById('modalEditPetugas');
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeEditPetugasModal() {
        const modal = document.getElementById('modalEditPetugas');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }
</script>
@endsection
