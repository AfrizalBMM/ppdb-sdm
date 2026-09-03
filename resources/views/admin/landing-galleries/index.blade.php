@extends('layouts.admin')

@section('title', 'Kelola Galeri')
@section('page-title', 'Galeri Sekolah')

@section('content')
<div x-data="{ 
    showCreate: false, 
    showEdit: false,
    editData: { id: '', title: '', caption: '', order: 0 }
}" class="mx-auto max-w-7xl space-y-6">
    @php
        $totalGallery = $galleries->total();
        $captionedGallery = $galleries->getCollection()->filter(fn($item) => !empty($item->caption))->count();
        $uncaptionedGallery = $galleries->getCollection()->filter(fn($item) => empty($item->caption))->count();
    @endphp

    <div class="rounded-2xl border border-slate-200 bg-gradient-to-r from-slate-50 via-white to-cyan-50 p-5 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-800">Galeri Foto Sekolah</h2>
                <p class="mt-1 text-sm text-slate-600">Kelola foto dokumentasi sekolah untuk ditampilkan di landing page.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700">Total: {{ $totalGallery }}</span>
                <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Dengan Caption: {{ $captionedGallery }}</span>
                <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">Tanpa Caption: {{ $uncaptionedGallery }}</span>
            </div>
        </div>
    </div>

    <div class="card p-0 overflow-hidden">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h3 class="text-base font-semibold text-slate-800">Daftar Foto Galeri</h3>
                <p class="mt-1 text-xs text-slate-500">Atur urutan foto agar tampilan landing page lebih menarik.</p>
            </div>

            <button @click="showCreate = true" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Foto
            </button>
        </div>

        @if($galleries->isEmpty())
            <div class="rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 py-20 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-white shadow-sm">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-800">Belum Ada Foto</h3>
                <p class="mx-auto mt-1 max-w-xs text-sm text-slate-600">Mulai isi galeri sekolah untuk mempercantik landing page Anda.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-6 p-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($galleries as $g)
                <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white transition-all duration-300 hover:shadow-md">
                    <div class="aspect-square relative overflow-hidden">
                        <img src="{{ Storage::url($g->image) }}" alt="{{ $g->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 flex items-center justify-center gap-2 bg-black/40 opacity-0 transition-opacity group-hover:opacity-100">
                            <button @click="
                                editData = { 
                                    id: '{{ $g->id }}', 
                                    title: '{{ addslashes($g->title) }}', 
                                    caption: '{{ addslashes($g->caption ?? '') }}', 
                                    order: '{{ $g->order }}' 
                                };
                                showEdit = true;
                            " class="rounded-lg bg-white p-2 text-blue-700 shadow-lg transition-transform hover:scale-110">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <form action="{{ route('landing-galleries.destroy', $g->id) }}" method="POST" onsubmit="return window.globalConfirmSubmit(this, 'Yakin hapus foto ini?', { title: 'Konfirmasi Hapus' });" class="inline">
                                @csrf @method('DELETE')
                                <button class="rounded-lg bg-white p-2 text-red-700 shadow-lg transition-transform hover:scale-110" title="Hapus">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="flex justify-between items-start gap-2">
                            <div>
                                <h4 class="truncate font-semibold text-slate-800" title="{{ $g->title }}">{{ $g->title }}</h4>
                                @if($g->caption)
                                    <p class="mt-1 truncate text-xs text-slate-500">{{ $g->caption }}</p>
                                @endif
                            </div>
                            <span class="rounded-md bg-slate-100 px-2 py-1 text-[10px] font-bold text-slate-600">#{{ $g->order }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-200 px-4 py-3 md:flex-row md:items-center md:justify-between">
                <form method="GET" class="flex items-center gap-2 text-xs text-slate-600">
                    <label for="perPageGalleries">Tampilkan</label>
                    <select id="perPageGalleries" name="per_page" onchange="this.form.submit()" class="rounded border border-slate-300 px-2 py-1 text-xs">
                        @foreach([10,20,50,100] as $size)
                            <option value="{{ $size }}" {{ (int) request('per_page', $perPage ?? 20) === $size ? 'selected' : '' }}>{{ $size }}</option>
                        @endforeach
                    </select>
                    <span>data</span>
                </form>

                <div>
                    {{ $galleries->links() }}
                </div>
            </div>
        @endif
    </div>

    <!-- Modal Create -->
    <div x-show="showCreate" 
         class="fixed inset-0 z-[300] flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm transition-all duration-300" 
         x-cloak>
        
        <div x-show="showCreate" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0 scale-95" 
             x-transition:enter-end="opacity-100 scale-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100 scale-100" 
             x-transition:leave-end="opacity-0 scale-95" 
             class="w-full max-w-lg overflow-hidden rounded-3xl bg-white shadow-2xl"
             @click.away="showCreate = false">
            
            <div class="relative border-b border-slate-100 bg-slate-50/50 px-8 py-6">
                <h3 class="text-xl font-bold text-slate-800">Tambah Foto Galeri</h3>
                <p class="text-xs text-slate-500 mt-1">Unggah dokumentasi baru ke galeri sekolah.</p>
                <button @click="showCreate = false" class="absolute right-6 top-6 rounded-xl p-2 text-slate-400 transition hover:bg-white hover:text-slate-700 hover:shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-8">
                <form action="{{ route('landing-galleries.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Judul Foto</label>
                        <input type="text" name="title" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all" required placeholder="Cth: Ruang Perpustakaan">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Keterangan (Caption)</label>
                        <input type="text" name="caption" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all" placeholder="Cth: Suasana tenang di perpustakaan">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Urutan</label>
                            <input type="number" name="order" value="0" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">File Foto</label>
                            <input type="file" name="image" required class="w-full text-xs text-slate-600 file:mr-3 file:rounded-xl file:border-0 file:bg-blue-100 file:px-4 file:py-2.5 file:text-[10px] file:font-bold file:uppercase file:tracking-widest file:text-blue-700 hover:file:bg-blue-200 transition-all">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="showCreate = false" class="rounded-xl border border-slate-300 bg-white px-6 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Batal</button>
                        <button type="submit" class="rounded-xl bg-blue-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-blue-500/30 transition hover:bg-blue-700">Simpan Foto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit -->
    <div x-show="showEdit" 
         class="fixed inset-0 z-[300] flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm transition-all duration-300" 
         x-cloak>
        
        <div x-show="showEdit" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0 scale-95" 
             x-transition:enter-end="opacity-100 scale-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100 scale-100" 
             x-transition:leave-end="opacity-0 scale-95" 
             class="w-full max-w-lg overflow-hidden rounded-3xl bg-white shadow-2xl"
             @click.away="showEdit = false">
            
            <div class="relative border-b border-slate-100 bg-slate-50/50 px-8 py-6">
                <h3 class="text-xl font-bold text-slate-800">Edit Foto Galeri</h3>
                <p class="text-xs text-slate-500 mt-1">Perbarui informasi foto yang sudah ada.</p>
                <button @click="showEdit = false" class="absolute right-6 top-6 rounded-xl p-2 text-slate-400 transition hover:bg-white hover:text-slate-700 hover:shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-8">
                <form :action="'{{ url('admin/landing-galleries') }}/' + editData.id" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf @method('PUT')
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Judul Foto</label>
                        <input type="text" name="title" x-model="editData.title" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all" required>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Keterangan (Caption)</label>
                        <input type="text" name="caption" x-model="editData.caption" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Urutan</label>
                            <input type="number" name="order" x-model="editData.order" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Ganti Foto (Opsional)</label>
                            <input type="file" name="image" class="w-full text-xs text-slate-600 file:mr-3 file:rounded-xl file:border-0 file:bg-blue-100 file:px-4 file:py-2.5 file:text-[10px] file:font-bold file:uppercase file:tracking-widest file:text-blue-700 hover:file:bg-blue-200 transition-all">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="showEdit = false" class="rounded-xl border border-slate-300 bg-white px-6 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Batal</button>
                        <button type="submit" class="rounded-xl bg-blue-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-blue-500/30 transition hover:bg-blue-700">Perbarui Foto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

