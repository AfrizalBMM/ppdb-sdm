@extends('layouts.admin')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <div class="grid md:grid-cols-2 gap-6">
        <div class="card">
            <h2 class="text-lg font-semibold mb-4">💼 Password Petugas Keuangan</h2>

            <form method="POST" action="{{ route('admin.password.petugas-keuangan.store') }}" class="space-y-3">
                @csrf

                <div>
                    <label class="label">Nama Petugas</label>
                    <input type="text" name="nama" class="input" placeholder="Masukkan nama petugas" required>
                </div>

                <div>
                    <label class="label">Password</label>
                    <input type="password" name="password" class="input" placeholder="Masukkan password" required>
                </div>

                <button class="btn-primary">Simpan</button>
            </form>
        </div>

        <div class="card">
            <h3 class="font-semibold text-slate-800 mb-4">Keterangan</h3>
            <ul class="text-sm text-slate-600 space-y-2 list-disc pl-5">
                <li>Data petugas ini digunakan untuk akses halaman Statistik Keuangan public.</li>
                <li>Akses hanya diberikan jika nama dan password valid.</li>
                <li>Gunakan password yang kuat dan hanya dibagikan ke petugas terkait.</li>
            </ul>
        </div>
    </div>

    <div class="card">
        <h3 class="font-semibold text-slate-800 mb-4">Daftar Petugas Keuangan</h3>

        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
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
                        <td class="px-4 py-3">{{ $loop->iteration }}</td>
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

        <div class="mt-4 flex flex-col gap-3 border-t border-slate-200 pt-3 md:flex-row md:items-center md:justify-between">
            <form method="GET" class="flex items-center gap-2 text-xs text-slate-600">
                <label for="perPagePetugas">Tampilkan</label>
                <select id="perPagePetugas" name="per_page" onchange="this.form.submit()"
                    class="rounded border border-slate-300 px-2 py-1 text-xs">
                    @foreach([10,20,50,100] as $size)
                    <option value="{{ $size }}" {{ (int) request('per_page', $perPage ?? 20) === $size ? 'selected' : '' }}>
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

<div id="modalEditPetugas"
    class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4 transition-all duration-300"
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
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Nama
                        Petugas</label>
                    <input type="text" name="nama" id="editPetugasNama"
                        class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500"
                        required>
                </div>

                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Password Baru
                        (opsional)</label>
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

<script>
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