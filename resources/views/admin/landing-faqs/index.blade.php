@extends('layouts.admin')

@section('title', 'Kelola FAQ')
@section('page-title', 'FAQ')

@section('content')
<div x-data="{ 
    showCreate: false, 
    showEdit: false,
    editData: { id: '', question: '', answer: '', order: 0 }
}" class="mx-auto max-w-7xl space-y-6">
    @php
        $totalFaq = $faqs->total();
        $shortAnswer = $faqs->getCollection()->filter(fn($item) => strlen((string) $item->answer) <= 120)->count();
        $longAnswer = $faqs->getCollection()->filter(fn($item) => strlen((string) $item->answer) > 120)->count();
    @endphp

    <div class="rounded-2xl border border-slate-200 bg-gradient-to-r from-slate-50 via-white to-cyan-50 p-5 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-800">Frequently Asked Questions</h2>
                <p class="mt-1 text-sm text-slate-600">Kelola pertanyaan umum agar calon wali murid lebih mudah memahami alur PPDB.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700">Total: {{ $totalFaq }}</span>
                <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Jawaban Singkat: {{ $shortAnswer }}</span>
                <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">Jawaban Panjang: {{ $longAnswer }}</span>
            </div>
        </div>
    </div>

    <div class="card p-0 overflow-hidden">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h3 class="text-base font-semibold text-slate-800">Daftar FAQ</h3>
                <p class="mt-1 text-xs text-slate-500">Susun pertanyaan berdasarkan prioritas agar mudah dipahami pengunjung.</p>
            </div>

            <button @click="showCreate = true" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah FAQ
            </button>
        </div>

        @if($faqs->isEmpty())
            <div class="rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 py-20 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-white shadow-sm">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-800">Belum Ada FAQ</h3>
                <p class="mx-auto mt-1 max-w-xs text-sm text-slate-600">Tambahkan pertanyaan umum untuk membantu calon pendaftar.</p>
            </div>
        @else
            <div class="space-y-4 p-5">
                @foreach($faqs as $faq)
                <div class="group rounded-2xl border border-slate-200 bg-white p-5 transition-all hover:shadow-md">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-700">
                                    {{ $faq->order ?: '0' }}
                                </span>
                                <h4 class="text-base font-semibold text-slate-800">{{ $faq->question }}</h4>
                            </div>
                            <div class="pl-9">
                                <p class="text-sm leading-relaxed text-slate-600">{{ $faq->answer }}</p>
                            </div>
                        </div>
                        <div class="flex shrink-0 gap-2 self-center">
                            <button @click="
                                editData = { 
                                    id: '{{ $faq->id }}', 
                                    question: '{{ addslashes($faq->question) }}', 
                                    answer: '{{ addslashes($faq->answer) }}', 
                                    order: '{{ $faq->order }}' 
                                };
                                showEdit = true;
                            " class="rounded-lg border border-blue-200 bg-blue-50 p-2 text-blue-700 hover:bg-blue-100" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <form action="{{ route('landing-faqs.destroy', $faq->id) }}" method="POST" onsubmit="return window.globalConfirmSubmit(this, 'Yakin hapus FAQ ini?', { title: 'Konfirmasi Hapus' });" class="inline">
                                @csrf @method('DELETE')
                                <button class="rounded-lg border border-red-200 bg-red-50 p-2 text-red-700 hover:bg-red-100" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-200 px-4 py-3 md:flex-row md:items-center md:justify-between">
                <form method="GET" class="flex items-center gap-2 text-xs text-slate-600">
                    <label for="perPageFaqs">Tampilkan</label>
                    <select id="perPageFaqs" name="per_page" onchange="this.form.submit()" class="rounded border border-slate-300 px-2 py-1 text-xs">
                        @foreach([10,20,50,100] as $size)
                            <option value="{{ $size }}" {{ (int) request('per_page', $perPage ?? 20) === $size ? 'selected' : '' }}>{{ $size }}</option>
                        @endforeach
                    </select>
                    <span>data</span>
                </form>

                <div>
                    {{ $faqs->links() }}
                </div>
            </div>
        @endif
    </div>

    <!-- Modal Create -->
    <div x-show="showCreate" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showCreate" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/50 transition-opacity" @click="showCreate = false"></div>

            <div x-show="showCreate" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block w-full max-w-xl my-8 overflow-hidden rounded-2xl bg-white p-8 text-left align-middle shadow-xl transition-all transform">
                
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-slate-800">Tambah Pertanyaan (FAQ)</h3>
                    <button @click="showCreate = false" class="text-slate-500 hover:text-slate-800">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('landing-faqs.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="space-y-1.5">
                        <label class="label">Pertanyaan</label>
                        <input type="text" name="question" class="input" required placeholder="Cth: Bagaimana cara daftar ulang?">
                    </div>
                    <div class="space-y-1.5">
                        <label class="label">Jawaban</label>
                        <textarea name="answer" class="input h-auto py-3" rows="5" required placeholder="Tuliskan jawaban yang jelas dan lengkap..."></textarea>
                    </div>
                    <div class="w-1/3 space-y-1.5">
                        <label class="label">Urutan Tampil</label>
                        <input type="number" name="order" value="0" class="input">
                    </div>

                    <div class="flex justify-end gap-3 mt-8">
                        <button type="button" @click="showCreate = false" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Simpan FAQ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit -->
    <div x-show="showEdit" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showEdit" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/50 transition-opacity" @click="showEdit = false"></div>

            <div x-show="showEdit" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block w-full max-w-xl my-8 overflow-hidden rounded-2xl bg-white p-8 text-left align-middle shadow-xl transition-all transform">
                
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-slate-800">Edit FAQ</h3>
                    <button @click="showEdit = false" class="text-slate-500 hover:text-slate-800">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="'{{ url('admin/landing-faqs') }}/' + editData.id" method="POST" class="space-y-4">
                    @csrf @method('PUT')
                    <div class="space-y-1.5">
                        <label class="label">Pertanyaan</label>
                        <input type="text" name="question" x-model="editData.question" class="input" required>
                    </div>
                    <div class="space-y-1.5">
                        <label class="label">Jawaban</label>
                        <textarea name="answer" x-model="editData.answer" class="input h-auto py-3" rows="5" required></textarea>
                    </div>
                    <div class="w-1/3 space-y-1.5">
                        <label class="label">Urutan Tampil</label>
                        <input type="number" name="order" x-model="editData.order" class="input">
                    </div>

                    <div class="flex justify-end gap-3 mt-8">
                        <button type="button" @click="showEdit = false" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Perbarui FAQ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

