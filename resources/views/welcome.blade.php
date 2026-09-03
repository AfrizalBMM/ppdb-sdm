@extends('layouts.public')

@section('content')
<div class="max-w-4xl mx-auto px-6 py-10">

    <div class="card text-center">

        <h1 class="text-2xl md:text-3xl font-bold text-slate-800 mb-4">
            Penerimaan Peserta Didik Baru (PPDB)
        </h1>

        <h2 class="text-lg font-semibold text-primary mb-2">
            SD Muhammadiyah Wonorejo
        </h2>

        <p class="text-sm text-slate-600 leading-relaxed mb-6">
            Sistem Penerimaan Peserta Didik Baru (PPDB) ini digunakan untuk
            proses pendaftaran calon peserta didik baru secara resmi dan terintegrasi.
            <br class="hidden md:block">
            Silakan melanjutkan ke formulir pendaftaran dengan menekan tombol di bawah ini.
        </p>

        <div class="flex justify-center">
            <a
                href="{{ route('pendaftaran.public.fresh') }}"
                class="btn-primary px-8 py-3 text-base">
                Mulai Pendaftaran
            </a>
        </div>

        <p class="text-xs text-slate-500 mt-6">
            Pastikan data yang diisikan sesuai dengan dokumen resmi
            (Kartu Keluarga, Akta Kelahiran, dan identitas orang tua).
        </p>

    </div>

</div>
@endsection
