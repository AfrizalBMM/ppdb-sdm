@extends('errors.layout')

@section('title', '500 - Kesalahan Server')

@section('image')
    <div class="flex justify-center flex-shrink-0">
        <img src="{{ asset('images/errors/500.png') }}" alt="500 Error" class="w-full max-w-sm h-auto drop-shadow-2xl">
    </div>
@endsection

@section('code', '500')

@section('message', 'Oops! Kesalahan Server Internal')

@section('description')
    Sistem sedang mengalami gangguan teknis. Kami mohon maaf atas ketidaknyamanan ini. Silakan coba segarkan halaman atau hubungi admin.
@endsection
