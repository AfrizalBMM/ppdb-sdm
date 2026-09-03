@extends('layouts.admin')

@section('page-title','Dashboard Keuangan')

@section('content')

@php
$stat_total_tagihan = $siswa_list->sum(fn($s)=>$s->tagihan->sum('total'));
$stat_total_dibayar = $siswa_list->sum(fn($s)=>$s->tagihan->sum(fn($t)=>$t->pembayaran->sum('nominal_bayar')));
$stat_total_sisa = $stat_total_tagihan - $stat_total_dibayar;
$stat_lunas = $siswa_list->filter(function($s){
$total = $s->tagihan->sum('total');
$dibayar = $s->tagihan->sum(fn($t)=>$t->pembayaran->sum('nominal_bayar'));
return $total > 0 && $dibayar >= $total;
})->count();
$stat_belum = $siswa_list->count() - $stat_lunas;
@endphp

<div class="mb-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-center gap-4 shadow-sm">
        <div class="bg-blue-100 text-blue-700 rounded-full p-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                </path>
            </svg>
        </div>
        <div>
            <div class="text-xs text-blue-700 font-semibold">Total Tagihan</div>
            <div class="text-lg font-bold">Rp {{ number_format($stat_total_tagihan,0,',','.') }}</div>
        </div>
    </div>
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-center gap-4 shadow-sm">
        <div class="bg-emerald-100 text-emerald-700 rounded-full p-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                </path>
            </svg>
        </div>
        <div>
            <div class="text-xs text-emerald-700 font-semibold">Uang Masuk</div>
            <div class="text-lg font-bold">Rp {{ number_format($stat_total_dibayar,0,',','.') }}</div>
        </div>
    </div>
    <div class="bg-rose-50 border border-rose-200 rounded-xl p-4 flex items-center gap-4 shadow-sm">
        <div class="bg-rose-100 text-rose-700 rounded-full p-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                </path>
            </svg>
        </div>
        <div>
            <div class="text-xs text-rose-700 font-semibold">Sisa Tagihan</div>
            <div class="text-lg font-bold">Rp {{ number_format($stat_total_sisa,0,',','.') }}</div>
        </div>
    </div>
    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex flex-col gap-2 items-start shadow-sm">
        <div class="flex items-center gap-2">
            <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
            <span class="text-xs text-slate-700 font-semibold">Lunas</span>
            <span class="font-bold text-lg text-emerald-700">{{ $stat_lunas }}</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-block w-2 h-2 rounded-full bg-rose-500"></span>
            <span class="text-xs text-slate-700 font-semibold">Belum Lunas</span>
            <span class="font-bold text-lg text-rose-700">{{ $stat_belum }}</span>
        </div>
    </div>
</div>

<div class="card mt-6 w-full max-w-none" x-data="{ search: '' }">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-4">
        <h2 class="font-semibold text-slate-800">
            Daftar Tagihan PPDB
        </h2>
        <input type="text" x-model="search" placeholder="Cari nama / registrasi..."
            class="input input-sm w-full md:w-72 border-slate-300 focus:ring-blue-200" />
    </div>
    <div class="overflow-x-auto w-full">
        <table x-ref="rowsTable" class="table w-full min-w-[900px]">
            @foreach($siswa_list as $siswa)
            @php
            $total_tagihan = $siswa->tagihan->sum('total');
            $total_dibayar = $siswa->tagihan->sum(function($t) {
            return $t->pembayaran->sum('nominal_bayar');
            });
            $sisa_semua = $total_tagihan - $total_dibayar;
            $nama = strtolower($siswa->nama);
            $reg = strtolower(optional($siswa->registration)->nomor_registrasi ?? '');
            @endphp
            <tbody
                data-row
                x-data="{ expanded: false, nama: @js($nama), reg: @js($reg) }"
                x-show="search === '' || nama.includes(search.toLowerCase()) || reg.includes(search.toLowerCase())"
            >
                <tr class="hover:bg-slate-50 transition-colors cursor-pointer group" @click="expanded = !expanded">
                    <td>
                        <div class="font-medium text-slate-800 group-hover:text-blue-600 transition-colors">
                            {{ $siswa->nama }}
                        </div>
                    </td>
                    <td class="text-center whitespace-nowrap">
                        {{ optional($siswa->registration)->nomor_registrasi ?? '-' }}
                    </td>
                    <td class="text-right whitespace-nowrap font-medium">
                        Rp {{ number_format($total_tagihan, 0, ',', '.') }}
                    </td>
                    <td class="text-right whitespace-nowrap text-green-700 font-medium">
                        Rp {{ number_format($total_dibayar, 0, ',', '.') }}
                    </td>
                    <td
                        class="text-right whitespace-nowrap font-medium {{ $sisa_semua > 0 ? 'text-red-500' : 'text-slate-600' }}">
                        Rp {{ number_format($sisa_semua, 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                        @if($sisa_semua <= 0) <span class="badge-success">Lunas</span>
                            @else
                            <span class="badge-warning">Belum Lunas</span>
                            @endif
                    </td>
                    <td class="text-center">
                        <button type="button"
                            class="p-2 text-slate-400 group-hover:text-blue-600 group-hover:bg-blue-50 rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-200">
                            <svg class="w-5 h-5 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </td>
                </tr>
                <tr x-show="expanded" x-transition x-cloak>
                    <td colspan="7" class="p-0 border-b border-slate-200">
                        <div class="bg-indigo-50/40 p-5 shadow-inner">
                            <h4 class="text-xs font-bold text-indigo-800 uppercase tracking-widest mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                    </path>
                                </svg>
                                Rincian Tagihan: {{ $siswa->nama }}
                            </h4>
                            <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm">
                                <table class="w-full text-sm text-left">
                                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600">
                                        <tr>
                                            <th class="py-2 px-4 font-semibold">Jenis Biaya</th>
                                            <th class="py-2 px-4 font-semibold">Voucher</th>
                                            <th class="py-2 px-4 font-semibold text-right">Nominal</th>
                                            <th class="py-2 px-4 font-semibold text-right">Dibayar</th>
                                            <th class="py-2 px-4 font-semibold text-right">Kekurangan</th>
                                            <th class="py-2 px-4 font-semibold text-center">Status</th>
                                            <th class="py-2 px-4 font-semibold text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($siswa->tagihan as $t)
                                        @php
                                        $dibayar = $t->pembayaran->sum('nominal_bayar');
                                        $sisa = ($t->total ?? 0) - $dibayar;
                                        @endphp
                                        <tr class="border-b border-slate-100 mt-0 hover:bg-slate-50">
                                            <td class="py-3 px-4 font-medium text-slate-700">
                                                {{ optional($t->biaya)->jenis_biaya ?? '-' }}
                                            </td>
                                            <td class="py-3 px-4">
                                                @if($t->kode_voucher)
                                                <span
                                                    class="px-2 py-0.5 bg-green-100 text-green-700 text-[10px] rounded border border-green-200 inline-block font-mono">
                                                    {{ $t->kode_voucher }}
                                                </span>
                                                @else
                                                <span class="text-slate-300">—</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4 text-right">Rp {{ number_format($t->total ?? 0,0,',','.') }}</td>
                                            <td class="py-3 px-4 text-right text-green-600 font-medium">Rp
                                                {{ number_format($dibayar,0,',','.') }}
                                            </td>
                                            <td class="py-3 px-4 text-right {{ $sisa > 0 ? 'text-red-500 font-medium' : 'text-slate-500' }}">
                                                Rp {{ number_format($sisa,0,',','.') }}</td>
                                            <td class="py-3 px-4 text-center">
                                                @if($sisa <= 0) <span
                                                    class="px-2 py-1 bg-green-100 text-green-700 text-[10px] rounded-full font-medium">
                                                    Lunas</span>
                                                    @else
                                                    <span
                                                        class="px-2 py-1 bg-yellow-100 text-yellow-700 text-[10px] rounded-full font-medium">Belum</span>
                                                    @endif
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                @if($sisa > 0)
                                                <button onclick="openBayar(
                                                                        '{{ $t->id }}',
                                                                        '{{ addslashes($siswa->nama) }}',
                                                                        '{{ addslashes(optional($t->biaya)->jenis_biaya) }}',
                                                                        '{{ $sisa }}'
                                                                    )"
                                                    class="px-3 py-1.5 bg-blue-600 text-white hover:bg-blue-700 active:bg-blue-800 text-[11px] rounded transition-colors focus:ring-2 focus:ring-blue-200 font-medium shadow-sm">
                                                    Bayar
                                                </button>
                                                @else
                                                <span class="text-slate-300 text-xs italic">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                        {{-- BREAKDOWN CICILAN PEMBAYARAN --}}
                                        @if($t->pembayaran->count())
                                        <tr class="bg-indigo-50/10">
                                            <td colspan="7" class="p-0 border-b border-slate-200">
                                                <div
                                                    class="px-6 py-3 ml-4 border-l-2 border-indigo-300 bg-white shadow-sm mt-1 mb-2 rounded-r-lg">
                                                    <div
                                                        class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                                                        <svg class="w-3 h-3 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                            </path>
                                                        </svg>
                                                        Riwayat Cicilan Pembayaran
                                                    </div>
                                                    <div class="overflow-x-auto rounded border border-slate-100">
                                                        <table class="w-full text-[11px] text-left">
                                                            <thead class="bg-slate-50 text-slate-500">
                                                                <tr>
                                                                    <th class="py-1.5 px-3 font-medium">Tanggal Bayar</th>
                                                                    <th class="py-1.5 px-3 font-medium text-right">Nominal</th>
                                                                    <th class="py-1.5 px-3 font-medium">Penerima</th>
                                                                    <th class="py-1.5 px-3 font-medium">Keterangan</th>
                                                                    <th class="py-1.5 px-3 font-medium text-center">Nota</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($t->pembayaran as $bayar)
                                                                <tr class="border-b border-slate-50 last:border-0 hover:bg-slate-50 transition-colors">
                                                                    <td class="py-1.5 px-3">
                                                                        {{ \Carbon\Carbon::parse($bayar->tanggal_bayar)->format('d M Y') }}
                                                                    </td>
                                                                    <td class="py-1.5 px-3 text-right font-medium text-slate-700">Rp
                                                                        {{ number_format($bayar->nominal_bayar,0,',','.') }}
                                                                    </td>
                                                                    <td class="py-1.5 px-3">{{ $bayar->admin_penerima ?? '-' }}</td>
                                                                    <td class="py-1.5 px-3">{{ $bayar->keterangan ?? '-' }}</td>
                                                                    <td class="py-1.5 px-3 text-center">
                                                                        <a href="{{ route('pembayaran.public.nota', $bayar->id) }}" target="_blank"
                                                                            class="text-blue-600 hover:text-blue-800 hover:underline flex items-center justify-center gap-1 font-medium transition-colors">
                                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                                                                </path>
                                                                            </svg>
                                                                            Cetak
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
            @endforeach

            <template x-if="Array.from($refs.rowsTable.querySelectorAll('tbody[data-row]')).filter(tb => tb.style.display !== 'none').length === 0">
                <tbody>
                    <tr>
                        <td colspan="7" class="text-center py-10 text-slate-500">
                            <span class="text-sm">Tidak ada data</span>
                        </td>
                    </tr>
                </tbody>
            </template>
        </table>
    </div>
    @if($siswa_list->hasPages())
    <div class="p-4 border-t border-slate-200">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <form method="GET" class="flex items-center gap-2 text-xs text-slate-600">
                @foreach(request()->except(['page', 'per_page']) as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <label for="perPageKeuangan">Tampilkan</label>
                <select id="perPageKeuangan" name="per_page" onchange="this.form.submit()"
                    class="rounded border border-slate-300 px-2 py-1 text-xs">
                    @foreach([10,20,50,100] as $size)
                    <option value="{{ $size }}" {{ (int) request('per_page', $perPage ?? 30) === $size ? 'selected' : '' }}>
                        {{ $size }}
                    </option>
                    @endforeach
                </select>
                <span>data</span>
            </form>
            {{ $siswa_list->links() }}
        </div>
    </div>
    @endif

</div>

@include('keuangan.modal-bayar')

@endsection