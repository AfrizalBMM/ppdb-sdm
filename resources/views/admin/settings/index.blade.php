@extends('layouts.admin')

@section('title', 'Setting Website')
@section('page-title', 'Setting Website')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    @php
        $filledCount = collect($settings ?? [])->filter(fn($value) => trim((string) $value) !== '')->count();
        $totalKey = 16;
    @endphp

    <div class="rounded-2xl border border-slate-200 bg-gradient-to-r from-slate-50 via-white to-blue-50 p-5 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-800">Konfigurasi Website PPDB</h2>
                <p class="mt-1 text-sm text-slate-600">Kelola SEO, konten landing page, dan berkas pendukung dari satu halaman.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700">Total Konfigurasi: {{ $totalKey }}</span>
                <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Terisi: {{ $filledCount }}</span>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="mb-6 flex items-center gap-3 border-b border-slate-200 pb-4">
            <div class="rounded-lg bg-blue-100 p-2 text-blue-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-slate-800">Detail Pengaturan Landing Page</h2>
                <p class="text-sm text-slate-600">Pastikan data sesuai identitas sekolah dan kebutuhan publikasi.</p>
            </div>
        </div>

        <form action="{{ route('admin.settings.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="space-y-8">
                {{-- SEO & KONTAK --}}
                <section>
                    <div class="flex items-center gap-2 mb-4">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-600">01</span>
                        <h3 class="font-bold text-slate-800">SEO & Informasi Kontak</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-1.5">
                            <label class="label">Meta Title (Judul Tab Browser)</label>
                            <input type="text" name="seo_title" value="{{ $settings['seo_title'] ?? 'PPDB SD Muhammadiyah Wonorejo' }}" class="input" placeholder="Cth: PPDB 2026 - SD Muhammadiyah Wonorejo">
                            <p class="text-[10px] italic text-slate-500">Maksimal 60 karakter disarankan.</p>
                        </div>
                        <div class="space-y-1.5">
                            <label class="label">Nomor WhatsApp (Admin)</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-500">+</span>
                                <input type="text" name="wa_number" value="{{ $settings['wa_number'] ?? '628123456789' }}" class="input pl-7" placeholder="62xxxxxxxxxx">
                            </div>
                            <p class="text-[10px] italic text-slate-500">Tanpa tanda + atau spasi.</p>
                        </div>
                        <div class="md:col-span-2 space-y-1.5">
                            <label class="label">Meta Description (Penjelasan Singkat di Google)</label>
                            <textarea name="seo_description" rows="2" class="input h-auto py-3" placeholder="Deskripsikan sekolah Anda...">{{ $settings['seo_description'] ?? '' }}</textarea>
                        </div>
                    </div>
                </section>

                <hr class="border-slate-200">

                {{-- HERO SECTION --}}
                <section>
                    <div class="flex items-center gap-2 mb-4">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-600">02</span>
                        <h3 class="font-bold text-slate-800">Banner Utama (Hero Section)</h3>
                    </div>
                    
                    <div class="space-y-5">
                        <div class="space-y-1.5">
                            <label class="label">Headline Utama</label>
                            <input type="text" name="hero_headline" value="{{ $settings['hero_headline'] ?? '' }}" class="input" placeholder="Kalimat ajakan besar...">
                        </div>
                        <div class="space-y-1.5">
                            <label class="label">Sub Headline</label>
                            <textarea name="hero_subheadline" rows="2" class="input h-auto py-3" placeholder="Penjelasan tambahan...">{{ $settings['hero_subheadline'] ?? '' }}</textarea>
                        </div>
                        <div class="w-full md:w-1/3 space-y-1.5">
                            <label class="label">Kuota Tersisa (Urgency)</label>
                            <input type="number" name="hero_quota" value="{{ $settings['hero_quota'] ?? '' }}" class="input" placeholder="Cth: 15">
                        </div>
                    </div>
                </section>

                <hr class="border-slate-200">

                {{-- VALUE PROP --}}
                <section>
                    <div class="flex items-center gap-2 mb-4">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-600">03</span>
                        <h3 class="font-bold text-slate-800">Keunggulan Utama (Value Prop)</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @for($i=1; $i<=4; $i++)
                            <div class="space-y-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <h4 class="text-xs font-bold uppercase text-blue-700">Keunggulan {{ $i }}</h4>
                                <div class="space-y-2">
                                    <input type="text" name="vp_title_{{$i}}" value="{{ $settings['vp_title_'.$i] ?? '' }}" class="input bg-white" placeholder="Judul Singkat">
                                    <textarea name="vp_desc_{{$i}}" rows="2" class="input bg-white h-auto py-2 text-xs" placeholder="Deskripsi">{{ $settings['vp_desc_'.$i] ?? '' }}</textarea>
                                </div>
                            </div>
                        @endfor
                    </div>
                </section>

                <hr class="border-slate-200">

                {{-- FILES --}}
                <section>
                    <div class="flex items-center gap-2 mb-4">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-600">04</span>
                        <h3 class="font-bold text-slate-800">Media & Dokumen</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="label">Logo Website</label>
                            <div class="flex items-center gap-4">
                                @if(isset($settings['logo']))
                                    <img src="{{ Storage::url($settings['logo']) }}" class="h-12 w-12 object-contain bg-gray-50 rounded p-1 border">
                                @endif
                                <input type="file" name="logo" class="text-xs text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-blue-100 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-blue-700 hover:file:bg-blue-200 transition-all">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="label">Download Brosur (PDF)</label>
                            <div class="flex items-center gap-4">
                                @if(isset($settings['brochure']))
                                    <div class="rounded bg-emerald-100 p-2 text-emerald-700">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                                <input type="file" name="brochure" class="text-xs text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-blue-100 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-blue-700 hover:file:bg-blue-200 transition-all">
                            </div>
                        </div>
                    </div>
                </section>

                <div class="flex justify-end gap-3 border-t border-slate-200 pt-8">
                    <button type="reset" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset</button>
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Simpan Konfigurasi</button>
                </div>
            </div>

        </form>
    </div>
</div>
@endsection
