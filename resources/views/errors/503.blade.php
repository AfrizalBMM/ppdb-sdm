@extends('errors.layout')

@section('title', '503 - Sedang Maintenance')

@section('image')
    <div class="flex justify-center flex-shrink-0">
        <img src="{{ asset('images/errors/503.png') }}" alt="503 Maintenance" class="w-full max-w-sm h-auto drop-shadow-2xl">
    </div>
@endsection

@section('code', '503')

@section('message', 'Sistem Sedang Dalam Pemeliharaan')

@section('description')
    Maaf, kami sedang melakukan pembaruan rutin untuk meningkatkan kualitas layanan. Kami akan segera kembali online dalam waktu dekat.
@endsection

@section('footer')
    @php
        $wa = \App\Models\Setting::where('key', 'wa_number')->value('value');
        $waClean = preg_replace('/\D/', '', $wa ?? '');
    @endphp
    
    @if($wa)
    <div class="mt-8 border-t border-slate-200 pt-6">
        <p class="text-xs text-slate-400 mb-3 uppercase tracking-widest font-bold">Butuh Bantuan?</p>
        <div class="flex justify-center">
            <a href="https://wa.me/{{ $waClean }}" target="_blank"
               class="inline-flex items-center gap-2 bg-green-50 text-green-700 px-4 py-2 rounded-full border border-green-200 font-semibold hover:bg-green-100 transition shadow-sm">
                <span class="text-xl">📲</span>
                WhatsApp Admin: {{ $wa }}
            </a>
        </div>
    </div>
    @endif
@endsection
