@extends('errors.layout')

@section('title', '404 - Halaman Tidak Ditemukan')

@section('image')
    <div class="flex justify-center flex-shrink-0">
        <img src="{{ asset('images/errors/404.png') }}" alt="404 Error" class="w-full max-w-sm h-auto drop-shadow-2xl">
    </div>
@endsection

@section('code', '404')

@section('message', 'Oops! Halaman Tidak Ditemukan')

@section('description')
    Maaf, halaman yang Anda cari tidak ada atau sudah dipindahkan. Silakan periksa kembali URL atau kembali ke halaman utama.
@endsection
