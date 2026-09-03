@extends('layouts.admin')

@section('title', 'Kelola Fasilitas')
@section('page-title', 'Fasilitas Sekolah')

@section('content')
<div x-data="{ 
    showCreate: false, 
    showEdit: false,
    editData: { id: '', title: '', order: 0 }
}" class="mx-auto max-w-7xl space-y-6">
    @php
        $totalFacility = $facilities->total();
        $withImage = $facilities->getCollection()->whereNotNull('image')->count();
        $withoutImage = $facilities->getCollection()->whereNull('image')->count();
    @endphp

    <div class="rounded-2xl border border-slate-200 bg-gradient-to-r from-slate-50 via-white to-cyan-50 p-5 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-800">Fasilitas Sekolah</h2>
                <p class="mt-1 text-sm text-slate-600">Kelola galeri fasilitas utama yang ditampilkan di landing page.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700">Total: {{ $totalFacility }}</span>
                <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Dengan Foto: {{ $withImage }}</span>
                <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">Tanpa Foto: {{ $withoutImage }}</span>
            </div>
        </div>
    </div>

    <div class="card p-0 overflow-hidden">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h3 class="text-base font-semibold text-slate-800">Daftar Fasilitas</h3>
                <p class="mt-1 text-xs text-slate-500">Atur urutan tampilan fasilitas untuk kebutuhan promosi landing page.</p>
            </div>

            <button @click="showCreate = true" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Fasilitas
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-100 text-left text-sm font-semibold text-slate-700">
                    <tr>
                        <th class="w-16 px-4 py-3 text-center">Urutan</th>
                        <th class="px-4 py-3">Fasilitas</th>
                        <th class="w-32 px-4 py-3">Preview</th>
                        <th class="w-32 px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($facilities as $f)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3 text-center font-semibold text-slate-700">{{ $f->order }}</td>
                        <td class="px-4 py-3">
                            <div class="font-semibold text-slate-800">{{ $f->title }}</div>
                        </td>
                        <td class="px-4 py-3">
                            @if($f->image)
                                <img src="{{ Storage::url($f->image) }}" class="h-12 w-20 rounded-lg border border-slate-200 object-cover shadow-sm">
                            @else
                                <div class="flex h-12 w-20 items-center justify-center rounded-lg border border-slate-200 bg-slate-50">
                                    <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button @click="
                                    editData = { 
                                        id: '{{ $f->id }}', 
                                        title: '{{ addslashes($f->title) }}', 
                                        order: '{{ $f->order }}' 
                                    };
                                    showEdit = true;
                                " class="rounded-lg border border-blue-200 bg-blue-50 p-2 text-blue-700 hover:bg-blue-100" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <form action="{{ route('landing-facilities.destroy', $f->id) }}" method="POST" onsubmit="return window.globalConfirmSubmit(this, 'Yakin hapus fasilitas ini?', { title: 'Konfirmasi Hapus' });" class="inline">
                                    @csrf @method('DELETE')
                                    <button class="rounded-lg border border-red-200 bg-red-50 p-2 text-red-700 hover:bg-red-100" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex flex-col gap-3 border-t border-slate-200 px-4 py-3 md:flex-row md:items-center md:justify-between">
            <form method="GET" class="flex items-center gap-2 text-xs text-slate-600">
                <label for="perPageFacilities">Tampilkan</label>
                <select id="perPageFacilities" name="per_page" onchange="this.form.submit()" class="rounded border border-slate-300 px-2 py-1 text-xs">
                    @foreach([10,20,50,100] as $size)
                        <option value="{{ $size }}" {{ (int) request('per_page', $perPage ?? 20) === $size ? 'selected' : '' }}>{{ $size }}</option>
                    @endforeach
                </select>
                <span>data</span>
            </form>

            <div>
                {{ $facilities->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Create -->
    <div x-show="showCreate" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showCreate" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-black/50" @click="showCreate = false"></div>

            <div x-show="showCreate" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block w-full max-w-lg my-8 overflow-hidden rounded-2xl bg-white p-8 text-left align-middle shadow-xl transition-all transform">
                
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-slate-800">Tambah Fasilitas Baru</h3>
                    <button @click="showCreate = false" class="text-slate-500 hover:text-slate-800">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('landing-facilities.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div class="space-y-1.5">
                        <label class="label">Nama Fasilitas</label>
                        <input type="text" name="title" class="input" required placeholder="Cth: Laboratorium Komputer">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="label">Urutan</label>
                            <input type="number" name="order" value="0" class="input">
                        </div>
                        <div class="space-y-1.5">
                            <label class="label">Foto Fasilitas</label>
                            <input type="file" name="image" class="text-xs text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-blue-100 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-blue-700 hover:file:bg-blue-200">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-8">
                        <button type="button" @click="showCreate = false" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Simpan Fasilitas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit -->
    <div x-show="showEdit" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showEdit" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-black/50" @click="showEdit = false"></div>

            <div x-show="showEdit" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block w-full max-w-lg my-8 overflow-hidden rounded-2xl bg-white p-8 text-left align-middle shadow-xl transition-all transform">
                
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-slate-800">Edit Fasilitas</h3>
                    <button @click="showEdit = false" class="text-slate-500 hover:text-slate-800">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="'{{ url('admin/landing-facilities') }}/' + editData.id" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf @method('PUT')
                    <div class="space-y-1.5">
                        <label class="label">Nama Fasilitas</label>
                        <input type="text" name="title" x-model="editData.title" class="input" required>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="label">Urutan</label>
                            <input type="number" name="order" x-model="editData.order" class="input">
                        </div>
                        <div class="space-y-1.5">
                            <label class="label">Ganti Foto (Opsional)</label>
                            <input type="file" name="image" class="text-xs text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-blue-100 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-blue-700 hover:file:bg-blue-200">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-8">
                        <button type="button" @click="showEdit = false" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Perbarui Fasilitas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

