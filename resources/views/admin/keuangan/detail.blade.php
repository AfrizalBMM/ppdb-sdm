@extends('layouts.admin')

@section('title', 'Detail Keuangan Siswa')

@section('content')
<div class="max-w-3xl mx-auto py-8">
    <div class="mb-4">
        <a href="{{ route('keuangan.index') }}" class="inline-block px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow transition">&larr; Kembali</a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-3 bg-red-50 border border-red-300 text-red-800 rounded">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-xl font-bold mb-4">Detail Keuangan Siswa</h2>
        <div class="mb-6">
            <table class="w-full text-sm border border-slate-200 rounded-lg">
                <tbody>
                    <tr class="bg-slate-50">
                        <td class="p-3 w-48 font-medium">Nama Peserta Didik</td>
                        <td class="p-3">{{ $siswa->nama }}</td>
                    </tr>
                    <tr>
                        <td class="p-3 font-medium">No Registrasi</td>
                        <td class="p-3">{{ optional($siswa->registration)->nomor_registrasi ?? '-' }}</td>
                    </tr>
                    <tr class="bg-slate-50">
                        <td class="p-3 font-medium">Nama Ibu</td>
                        <td class="p-3">{{ optional($siswa->ibu)->nama ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="p-3 font-medium">No HP Ibu</td>
                        <td class="p-3">{{ optional($siswa->ibu)->no_hp ?? '-' }}</td>
                    </tr>
                    <tr class="bg-slate-50">
                        <td class="p-3 font-medium">Voucher</td>
                        <td class="p-3">
                            @if($claimedTagihan)
                                <p>
                                    <span class="badge-success">{{ $claimedTagihan->kode_voucher }}</span>
                                    dengan potongan <strong>Rp {{ number_format($claimedTagihan->diskon, 0, ',', '.') }}</strong>
                                    untuk biaya <strong>{{ ui_label($claimedTagihan->biaya->jenis_biaya ?? '-') }}</strong>
                                </p>
                                <form id="formBatalVoucher" method="POST" action="{{ route('keuangan.voucher.batal', $siswa) }}" class="mt-2">
                                    @csrf
                                    <button type="button" onclick="openModal('modalBatalVoucher')"
                                        class="px-3 py-1.5 rounded bg-red-600 hover:bg-red-700 text-white text-xs font-semibold">
                                        Batalkan Klaim
                                    </button>
                                </form>
                            @elseif($eligibleVouchers->isNotEmpty())
                                <form id="formKlaimVoucher" method="POST" action="{{ route('keuangan.voucher.klaim', $siswa) }}" class="flex flex-wrap items-center gap-2">
                                    @csrf
                                    <select name="voucher_id" id="selectVoucherKlaim" class="input max-w-xs" required>
                                        @foreach($eligibleVouchers as $v)
                                            <option value="{{ $v->id }}"
                                                    data-kode="{{ $v->kode }}"
                                                    data-diskon="{{ $v->diskon_nominal }}"
                                                    data-jenis="{{ ui_label($v->jenis_biaya) }}">
                                                {{ $v->kode }} — potongan Rp {{ number_format($v->diskon_nominal, 0, ',', '.') }} ({{ ui_label($v->jenis_biaya) }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="button" onclick="openKlaimVoucherModal()"
                                        class="px-3 py-2 rounded bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold">
                                        Klaim Voucher
                                    </button>
                                </form>
                                <p class="text-xs text-slate-500 mt-2">
                                    Voucher hanya bisa diklaim jika seluruh biaya lain sudah lunas dan biaya target belum lunas.
                                </p>
                            @else
                                <i class="text-slate-400">Belum ada voucher yang diklaim / belum ada voucher yang bisa diklaim</i>
                            @endif

                            @error('voucher_id')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border">
                <thead>
                    <tr class="bg-slate-100">
                        <th class="px-4 py-2 border">Jenis Biaya</th>
                        <th class="px-4 py-2 border">Total Tagihan</th>
                        <th class="px-4 py-2 border">Sudah Dibayar</th>
                        <th class="px-4 py-2 border">Sisa</th>
                        <th class="px-4 py-2 border">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($siswa->tagihan as $tagihan)
                    <tr>
                        <td class="px-4 py-2 border">{{ $tagihan->biaya->nama_biaya ?? '-' }}</td>
                        <td class="px-4 py-2 border">Rp {{ number_format($tagihan->total) }}</td>
                        <td class="px-4 py-2 border text-emerald-700">Rp {{ number_format($tagihan->total_dibayar) }}</td>
                        <td class="px-4 py-2 border text-rose-700">Rp {{ number_format($tagihan->sisa) }}</td>
                        <td class="px-4 py-2 border">
                            @if($tagihan->is_lunas)
                                <span class="inline-block px-2 py-1 text-xs rounded bg-emerald-100 text-emerald-700">Lunas</span>
                            @else
                                <span class="inline-block px-2 py-1 text-xs rounded bg-rose-100 text-rose-700">Belum Lunas</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-8">
            <div class="flex items-center justify-between mb-2">
                <h3 class="font-semibold mb-2">Riwayat Pembayaran</h3>
                <form method="get" class="flex items-center gap-2">
                    <label for="filter_jenis_biaya" class="text-xs text-slate-600">Filter</label>
                    <select name="jenis_biaya" id="filter_jenis_biaya" class="input input-sm" onchange="this.form.submit()">
                        <option value="">Semua</option>
                        @php
                            $jenisList = $semuaPembayaran->pluck('tagihan.biaya.jenis_biaya')->unique()->filter()->values();
                        @endphp
                        @foreach($jenisList as $jenis)
                            <option value="{{ $jenis }}" @if(request('jenis_biaya') == $jenis) selected @endif>{{ ui_label($jenis) }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border">
                    <thead>
                        <tr class="bg-slate-100">
                            <th class="px-4 py-2 border">Tanggal</th>
                            <th class="px-4 py-2 border">Jenis Biaya</th>
                            <th class="px-4 py-2 border">Nominal</th>
                            <th class="px-4 py-2 border">Penerima</th>
                            <th class="px-4 py-2 border">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($semuaPembayaran as $pembayaran)
                            @if(!request('jenis_biaya') || (string)($pembayaran->tagihan->biaya->jenis_biaya ?? '') === request('jenis_biaya'))
                            <tr>
                                <td class="px-4 py-2 border">{{ $pembayaran->tanggal_bayar?->translatedFormat('d F Y') ?? '-' }}</td>
                                <td class="px-4 py-2 border">{{ $pembayaran->tagihan->biaya->nama_biaya ?? '-' }}</td>
                                <td class="px-4 py-2 border">Rp {{ number_format($pembayaran->nominal_bayar) }}</td>
                                <td class="px-4 py-2 border">{{ $pembayaran->admin_penerima ?? '-' }}</td>
                                <td class="px-4 py-2 border">{{ $pembayaran->keterangan ?? '-' }}</td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
{{-- MODAL KONFIRMASI KLAIM VOUCHER --}}
<div id="modalKlaimVoucher" class="fixed inset-0 z-[300] hidden items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm transition-all duration-300">
    <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-8 shadow-2xl transform transition-all duration-300">
        <div class="w-20 h-20 bg-emerald-50 text-emerald-600 rounded-3xl flex items-center justify-center mx-auto mb-6 text-3xl">
            🎟️
        </div>

        <div class="text-center mb-6">
            <h3 class="text-xl font-bold text-slate-800">Klaim Voucher?</h3>
            <p class="text-sm text-slate-500 mt-2">Potongan akan diterapkan pada biaya target.</p>
        </div>

        <div class="bg-slate-50 border border-slate-100 rounded-2xl p-5 mb-6">
            <div class="flex justify-between items-center mb-2">
                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Kode Voucher</span>
                <span id="klaimVoucherKode" class="text-sm font-bold text-slate-700"></span>
            </div>
            <div class="flex justify-between items-center mb-2">
                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Potongan</span>
                <span id="klaimVoucherDiskon" class="text-sm font-bold text-emerald-700"></span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Biaya Target</span>
                <span id="klaimVoucherJenis" class="text-sm font-bold text-slate-700"></span>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="button" onclick="closeModal('modalKlaimVoucher')" class="flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors">Batal</button>
            <button type="button" onclick="document.getElementById('formKlaimVoucher').submit()" class="flex-1 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-500/30 hover:bg-emerald-700 transition-all">Ya, Klaim</button>
        </div>
    </div>
</div>

{{-- MODAL KONFIRMASI BATAL VOUCHER --}}
<div id="modalBatalVoucher" class="fixed inset-0 z-[300] hidden items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm transition-all duration-300">
    <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-8 shadow-2xl transform transition-all duration-300">
        <div class="w-20 h-20 bg-red-50 text-red-600 rounded-3xl flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
        </div>

        <div class="text-center mb-6">
            <h3 class="text-xl font-bold text-slate-800">Batalkan Klaim Voucher?</h3>
            <p class="text-sm text-slate-500 mt-2 leading-relaxed">Total tagihan akan dikembalikan ke nominal penuh dan kuota voucher dikembalikan.</p>
        </div>

        <div class="flex gap-3">
            <button type="button" onclick="closeModal('modalBatalVoucher')" class="flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors">Batal</button>
            <button type="button" onclick="document.getElementById('formBatalVoucher').submit()" class="flex-1 rounded-xl bg-red-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-red-500/30 hover:bg-red-700 transition-all">Ya, Batalkan</button>
        </div>
    </div>
</div>

<script>
    function openKlaimVoucherModal() {
        const select = document.getElementById('selectVoucherKlaim');
        const form = document.getElementById('formKlaimVoucher');
        if (!select || !form) return;

        const opt = select.options[select.selectedIndex];
        if (!opt) return;

        document.getElementById('klaimVoucherKode').textContent = opt.dataset.kode || '-';
        document.getElementById('klaimVoucherDiskon').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(Number(opt.dataset.diskon || 0));
        document.getElementById('klaimVoucherJenis').textContent = opt.dataset.jenis || '-';

        openModal('modalKlaimVoucher');
    }
</script>

@endsection
