@extends('layouts.public')

@section('title', $landingProgram->title . ' - Program & Ekskul')

@section('content')
    <div class="max-w-5xl mx-auto px-6 py-12">
        <div class="mb-8">
            <a href="{{ route('public.landing') }}#program" class="inline-flex items-center gap-2 text-primary font-semibold hover:underline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Kembali ke Program & Ekskul
            </a>
        </div>

        <div class="bg-white rounded-3xl overflow-hidden shadow-lg">
            @if($landingProgram->image)
                <img src="{{ Storage::url($landingProgram->image) }}" alt="{{ $landingProgram->title }}" class="w-full h-72 object-cover">
            @else
                <div class="w-full h-72 bg-slate-100 flex items-center justify-center text-slate-500">(Tidak ada gambar)</div>
            @endif

            <div class="p-8">
                <h1 class="text-3xl md:text-4xl font-bold text-slate-800 mb-4">{{ $landingProgram->title }}</h1>
                <div class="text-sm text-slate-500 mb-6">Dipublikasikan: {{ $landingProgram->created_at ? $landingProgram->created_at->format('d M Y') : '-' }}</div>
                <div class="prose prose-slate max-w-none text-slate-700">
                    {!! nl2br(e($landingProgram->description)) !!}
                </div>

                <div class="mt-8">
                    <a href="{{ route('pendaftaran.public.fresh') }}" class="inline-block bg-primary text-white px-6 py-3 rounded-full font-semibold shadow-lg hover:shadow-xl transition-all">
                        Daftar Sekarang
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection