
@php
    use App\Models\TagihanSiswa;
@endphp
@extends('layouts.admin')

@section('page-title','Detail Pendaftar')

@section('content')

<div class="grid md:grid-cols-3 gap-8">

    {{-- LEFT --}}

    <div class="md:col-span-2 space-y-8">

        {{-- REGISTRATION --}}
        <div class="card p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                <div>
                    <h3 class="text-xl font-bold text-blue-700 mb-1">Data Registrasi</h3>
                    <div class="text-xs text-slate-500">Detail pendaftaran peserta didik</div>
                </div>
                <div class="flex gap-2 flex-wrap">
                    @php
                        $regStatus = (int) ($siswa->registration->status ?? \App\Models\Registration::STATUS_BAKAL_CALON);
                        $regBadge = $regStatus === \App\Models\Registration::STATUS_PESERTA_DIDIK
                            ? 'bg-emerald-100 text-emerald-700 border-emerald-300'
                            : ($regStatus === \App\Models\Registration::STATUS_CALON ? 'bg-blue-100 text-blue-700 border-blue-300' : 'bg-yellow-100 text-yellow-700 border-yellow-300');
                    @endphp
                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $regBadge }}">
                        {{ \App\Models\Registration::statusLabel($regStatus) }}
                    </span>
                    <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700">
                        No. Reg: {{ $siswa->registration->nomor_registrasi ?? '-' }}
                    </span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <div class="text-slate-500 text-xs mb-1">Tanggal Daftar</div>
                    <div class="font-medium">
                        {{ optional($siswa->registration->tanggal_daftar)->translatedFormat('d F Y') ?? '-' }}
                    </div>
                </div>
                <div>
                    <div class="text-slate-500 text-xs mb-1">Tahun Ajaran</div>
                    <div class="font-medium">{{ $siswa->registration->tahunAjaran->nama ?? '-' }}</div>
                </div>
            </div>
        </div>

        {{-- DATA PESERTA DIDIK --}}
        <div class="card p-6">
            <h3 class="text-xl font-bold text-blue-700 mb-4">Data Peserta Didik</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <div class="text-slate-500 text-xs mb-1">Nama</div>
                    <div class="font-medium">{{ $siswa->nama }}</div>
                </div>
                <div>
                    <div class="text-slate-500 text-xs mb-1">NISN</div>
                    <div class="font-medium">{{ $siswa->nisn ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-slate-500 text-xs mb-1">NIK</div>
                    <div class="font-medium">{{ $siswa->nik }}</div>
                </div>
                <div>
                    <div class="text-slate-500 text-xs mb-1">No KK</div>
                    <div class="font-medium">{{ $siswa->no_kk }}</div>
                </div>
                <div>
                    <div class="text-slate-500 text-xs mb-1">Jenis Kelamin</div>
                    <div class="font-medium">{{ ui_label($siswa->jenis_kelamin) }}</div>
                </div>
                <div>
                    <div class="text-slate-500 text-xs mb-1">Tempat, Tanggal Lahir</div>
                    <div class="font-medium">
                        {{ $siswa->tempat_lahir }},
                        {{ optional($siswa->tanggal_lahir)->translatedFormat('d F Y') ?? '-' }}
                    </div>
                </div>
                <div>
                    <div class="text-slate-500 text-xs mb-1">Transportasi</div>
                    <div class="font-medium">{{ $siswa->transportasi ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-slate-500 text-xs mb-1">Tinggal Bersama</div>
                    <div class="font-medium">{{ ui_label($siswa->tinggal_bersama ?? '-') }}</div>
                </div>
                <div>
                    <div class="text-slate-500 text-xs mb-1">Hasil Tes</div>
                    <div class="font-medium">{{ $siswa->hasil_tes }}</div>
                </div>
            </div>
        </div>

        {{-- ALAMAT --}}
        <div class="card p-6">
            <h3 class="text-xl font-bold text-blue-700 mb-4">Alamat</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <div class="text-slate-500 text-xs mb-1">Alamat Lengkap</div>
                    <div class="font-medium">{{ optional($siswa->alamat)->alamat ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-slate-500 text-xs mb-1">Wilayah</div>
                    <div class="font-medium">
                        {{ optional($siswa->alamat)->kelurahan ?? '-' }},
                        {{ optional($siswa->alamat)->kecamatan ?? '-' }},
                        {{ optional($siswa->alamat)->kabupaten ?? '-' }},
                        {{ optional($siswa->alamat)->provinsi ?? '-' }}
                    </div>
                </div>
                <div>
                    <div class="text-slate-500 text-xs mb-1">RT / RW</div>
                    <div class="font-medium">RT {{ optional($siswa->alamat)->rt ?? '-' }} / RW {{ optional($siswa->alamat)->rw ?? '-' }}</div>
                </div>
            </div>
        </div>

        {{-- ORANG TUA --}}
        <div class="card p-6">
            <h3 class="text-xl font-bold text-blue-700 mb-4">Data Orang Tua</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <div class="text-slate-500 text-xs mb-1">Ibu</div>
                    <div class="font-medium">{{ optional($siswa->ibu)->nama ?? '-' }} <span class="text-slate-400">|</span> {{ optional($siswa->ibu)->no_hp ?? '-' }}</div>
                </div>
                @if($siswa->ayah)
                <div>
                    <div class="text-slate-500 text-xs mb-1">Ayah</div>
                    <div class="font-medium">{{ $siswa->ayah->nama }} <span class="text-slate-400">|</span> {{ $siswa->ayah->no_hp ?? '-' }}</div>
                </div>
                @endif
                @if($siswa->wali)
                <div>
                    <div class="text-slate-500 text-xs mb-1">Wali</div>
                    <div class="font-medium">{{ $siswa->wali->nama }} ({{ $siswa->wali->hubungan }}) <span class="text-slate-400">|</span> {{ $siswa->wali->no_hp }}</div>
                </div>
                @endif
            </div>
        </div>

        {{-- DATA PENDUKUNG --}}
        <div class="card p-6">
            <h3 class="text-xl font-bold text-blue-700 mb-4">Data Pendukung</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <div class="text-slate-500 text-xs mb-1">Tinggi</div>
                    <div class="font-medium">{{ optional($siswa->dataPendukung)->tinggi ?? '-' }} cm</div>
                </div>
                <div>
                    <div class="text-slate-500 text-xs mb-1">Berat</div>
                    <div class="font-medium">{{ optional($siswa->dataPendukung)->berat ?? '-' }} kg</div>
                </div>
                <div>
                    <div class="text-slate-500 text-xs mb-1">Jarak</div>
                    <div class="font-medium">{{ optional($siswa->dataPendukung)->jarak ?? '-' }} km</div>
                </div>
                <div>
                    <div class="text-slate-500 text-xs mb-1">Jumlah Saudara</div>
                    <div class="font-medium">{{ optional($siswa->dataPendukung)->jumlah_saudara ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-slate-500 text-xs mb-1">Anak Ke (KK)</div>
                    <div class="font-medium">{{ optional($siswa->dataPendukung)->anak_ke ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-slate-500 text-xs mb-1">Hobi</div>
                    <div class="font-medium">{{ optional($siswa->dataPendukung)->hobi ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-slate-500 text-xs mb-1">Cita-cita</div>
                    <div class="font-medium">{{ optional($siswa->dataPendukung)->cita_cita ?? '-' }}</div>
                </div>
            </div>
        </div>

    </div>

    {{-- RIGHT --}}

    <div class="space-y-6">

        <div class="card p-4 flex flex-col gap-3">
            <a
                href="{{ route('cetak.formulir.preview',$siswa) }}"
                target="_blank"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-700 w-full">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Cetak Formulir
            </a>
            <a
                href="{{ route('keuangan.detail', $siswa->id) }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700 w-full">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a5 5 0 00-10 0v2m-2 0h14a2 2 0 012 2v7a2 2 0 01-2 2H5a2 2 0 01-2-2v-7a2 2 0 012-2z" /></svg>
                Keuangan
            </a>
        </div>

        {{-- RINGKASAN TAGIHAN --}}
        <div class="card p-6">
            <h3 class="text-xl font-bold text-blue-700 mb-4">Ringkasan Tagihan</h3>
            <div class="space-y-2">
                @foreach($siswa->tagihan as $tagihan)
                    <div class="border-b py-2 text-sm">
                        <div class="flex flex-col">
                            <span class="font-semibold">{{ $tagihan->biaya->nama_biaya }}</span>
                            <span class="ml-0 text-xs text-slate-500">Tagihan: Rp {{ number_format($tagihan->total) }}</span>
                            <span class="ml-0 text-xs text-emerald-700">Sudah dibayar: Rp {{ number_format($tagihan->total_dibayar) }}</span>
                            <span class="ml-0 text-xs text-rose-700">Sisa: Rp {{ number_format($tagihan->sisa) }}</span>
                            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-semibold mt-1 w-max {{ $tagihan->is_lunas ? 'bg-emerald-100 text-emerald-700 border-emerald-300' : 'bg-yellow-100 text-yellow-700 border-yellow-300' }}">
                                {{ $tagihan->is_lunas ? 'Lunas' : 'Belum Lunas' }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div id="riwayat-aktivitas" class="card p-6">
            <h3 class="text-xl font-bold text-blue-700 mb-4">Riwayat Aktivitas</h3>
            @if(!empty($aktivitas) && $aktivitas->count())
                <div class="space-y-3 max-h-72 overflow-y-auto pr-2">
                    @foreach($aktivitas as $log)
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                            <div class="text-xs font-semibold text-slate-700">{{ $log->aksi }}</div>
                            <div class="mt-1 text-[11px] text-slate-500">
                                {{ optional($log->created_at)->translatedFormat('d F Y') }}
                                • {{ $log->nama ?? '-' }}
                                • {{ $log->role ?? '-' }}
                            </div>
                            <div class="mt-1 text-xs text-slate-600 break-words">{{ $log->keterangan ?? '-' }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-slate-500">Belum ada aktivitas tercatat untuk data ini.</p>
            @endif
        </div>

    </div>

</div>

@if(session('scroll_to_activity'))
    <script>
        window.addEventListener('load', function () {
            var el = document.getElementById('riwayat-aktivitas');
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    </script>
@endif
@endsection