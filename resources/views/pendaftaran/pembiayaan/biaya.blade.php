@extends('layouts.public')

@section('content')

<div class="max-w-5xl mx-auto px-6 py-8">

    @if(session('success'))
    <div id="alertSuccess" class="mb-4 p-3 bg-green-100 border border-green-300 text-green-800 rounded">
        ✅ {{ session('success') }}
    </div>

    <script>
        setTimeout(function() {
            document.getElementById('alertSuccess').style.display = 'none';
        }, 3000);
    </script>
    @endif
    <div class="md:col-span-2 card">

        <h2 class="font-semibold text-lg text-slate-800 mb-4">
            Rincian Pembiayaan Calon Peserta Didik
        </h2>

        <div class="bg-yellow-50 border border-yellow-200 p-3 text-xs rounded mb-6">
            ⚠️ Informasi pembiayaan ini bersifat RAHASIA, hanya untuk panitia dan orang tua/wali peserta didik.
        </div>


        {{-- ===============================
            DATA PESERTA DIDIK
            =============================== --}}
        <div class="overflow-x-auto mb-8">

            <h3 class="font-semibold mb-3">
                Informasi Peserta Didik
            </h3>

            <table class="w-full text-sm border border-slate-200 rounded-lg">

                <tbody>

                    <tr class="bg-slate-50">
                        <td class="p-3 w-48 font-medium">
                            Nama Peserta Didik
                        </td>

                        <td class="p-3">
                            {{ $siswa->nama }}
                        </td>
                    </tr>

                    <tr>
                        <td class="p-3 font-medium">
                            No Registrasi
                        </td>

                        <td class="p-3">
                            {{ optional($siswa->registration)->nomor_registrasi ?? '-' }}
                        </td>
                    </tr>

                    <tr class="bg-slate-50">
                        <td class="p-3 font-medium">
                            Nama Ibu
                        </td>

                        <td class="p-3">
                            {{ optional($siswa->ibu)->nama ?? '-' }}
                        </td>
                    </tr>

                    <tr>
                        <td class="p-3 font-medium">
                            No HP Ibu
                        </td>

                        <td class="p-3">
                            {{ optional($siswa->ibu)->no_hp ?? '-' }}
                        </td>
                    </tr>

                    <tr class="bg-slate-50">
                        <td class="p-3 font-medium">
                            Voucher
                        </td>

                        <td class="p-3">
                            @php
                            $voucher = optional($siswa->registration)->voucher;
                            @endphp
                            @if($voucher)
                            <span class="badge-success">{{ $voucher->kode }}</span>
                            dengan potongan <strong>Rp
                                {{ number_format($voucher->diskon_nominal, 0, ',', '.') }}</strong>
                            berlaku untuk biaya <strong>{{ ui_label($voucher->jenis_biaya) }}</strong>
                            @else
                            <i class="text-slate-400">Tidak dapat Voucher</i>
                            @endif
                        </td>
                    </tr>

                </tbody>

            </table>

        </div>



        {{-- ===============================
            RINCIAN BIAYA
            =============================== --}}
        <div class="overflow-x-auto">

            <h3 class="font-semibold mb-3">
                Rincian Biaya
            </h3>

            <table class="w-full text-sm border border-slate-200 rounded-lg">

                <tbody>

                    @php
                    $totalBiaya = 0;
                    $totalKekurangan = 0;
                    @endphp

                    @foreach($siswa->tagihan as $tagihan)

                    @php
                    $totalBiaya += $tagihan->total;
                    $totalKekurangan += $tagihan->sisa;
                    @endphp


                    <tr class="bg-slate-100">
                        <th class="p-3 border text-left">
                            Jenis Biaya
                        </th>

                        <th class="p-3 border text-right">
                            Nominal
                        </th>

                        <th class="p-3 border text-right">
                            Diskon
                        </th>

                        <th class="p-3 border text-right">
                            Total
                        </th>

                        <th class="p-3 border text-right">
                            Kekurangan
                        </th>

                        <th class="p-3 border text-center">
                            Status
                        </th>

                        <th class="p-3 border text-center">
                            Aksi
                        </th>
                    </tr>

                    {{-- ROW TAGIHAN --}}
                    <tr class="{{ $loop->even ? 'bg-slate-50' : '' }}">

                        <td class="p-3 border">
                            {{ ui_label($tagihan->biaya->jenis_biaya) }}
                        </td>

                        <td class="p-3 border text-right">
                            Rp {{ number_format($tagihan->nominal, 0, ',', '.') }}
                        </td>

                        <td class="p-3 border text-right text-green-600">
                            Rp {{ number_format($tagihan->diskon, 0, ',', '.') }}
                        </td>

                        <td class="p-3 border text-right font-medium">
                            Rp {{ number_format($tagihan->total, 0, ',', '.') }}
                        </td>

                        <td class="p-3 border text-right">
                            Rp {{ number_format($tagihan->sisa, 0, ',', '.') }}
                        </td>

                        <td class="p-3 border text-center">

                            @if($tagihan->is_lunas)

                            <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full">
                                Lunas
                            </span>

                            @else

                            <span class="px-2 py-1 bg-yellow-100 text-yellow-700 text-xs rounded-full">
                                Belum Lunas
                            </span>

                            @endif

                        </td>

                        <td class="p-3 border text-center">

                            @if(!$tagihan->is_lunas)

                            <button onclick="openBayarModal({{ $tagihan->id }}, {{ $tagihan->sisa }})"
                                class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-1 rounded">
                                Bayar
                            </button>

                            @else

                            <button class="bg-gray-300 text-gray-600 text-xs px-3 py-1 rounded cursor-not-allowed"
                                disabled>
                                Lunas
                            </button>

                            @endif

                        </td>

                    </tr>

                    {{-- BREAKDOWN CICILAN --}}
                    @if($tagihan->pembayaran->count())

                    <tr>

                        <td colspan="7" class="border bg-slate-50 p-3">

                            <div class="flex items-center justify-between mb-2">
                                <div class="text-xs font-semibold">
                                    Riwayat Cicilan
                                </div>
                                <button type="button"
                                    onclick="toggleRiwayatCicilan('{{ $tagihan->id }}')"
                                    id="riwayatToggleBtn{{ $tagihan->id }}"
                                    aria-label="Toggle riwayat cicilan"
                                    class="text-slate-600 hover:text-slate-900 p-1.5 border border-slate-300 rounded-md bg-white transition-colors">
                                    <svg class="w-4 h-4 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.512a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>

                            <div id="riwayatContent{{ $tagihan->id }}" class="hidden">
                                <table class="w-full text-xs">

                                    <thead>
                                        <tr class="text-slate-600">

                                            <th class="text-left py-2">
                                                Tanggal Bayar
                                            </th>

                                            <th class="text-left py-1">
                                                Nominal
                                            </th>

                                            <th class="text-left py-1">
                                                Metode
                                            </th>

                                            <th class="text-left py-1">
                                                Penerima
                                            </th>

                                            <th class="text-left py-1">
                                                Keterangan
                                            </th>

                                            <th class="text-center py-1">
                                                Aksi
                                            </th>

                                        </tr>
                                    </thead>


                                    <tbody>

                                        @foreach($tagihan->pembayaran as $bayar)

                                        <tr class="border-t">

                                            <td class="py-1">
                                                {{ $bayar->tanggal_bayar->format('d M Y') }}
                                            </td>

                                            <td class="py-1 text-left">
                                                Rp {{ number_format($bayar->nominal_bayar, 0, ',', '.') }}
                                            </td>

                                            <td class="py-1">
                                                {{ $bayar->metode ? ucfirst($bayar->metode) : '-' }}
                                            </td>

                                            <td class="py-1">
                                                {{ $bayar->admin_penerima ?? '-' }}
                                            </td>

                                            <td class="py-1">
                                                {{ $bayar->keterangan ?? '-' }}
                                            </td>

                                            <td class="py-1 text-center">

                                                <button type="button"
                                                    onclick="openNotaModal('{{ route('pembayaran.public.nota.post', $bayar->id) }}')"
                                                    title="Cetak Nota"
                                                    aria-label="Cetak Nota"
                                                    class="text-blue-600 hover:text-blue-800 inline-flex items-center justify-center p-1 rounded">
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                        <path d="M7 9V4h10v5" stroke-linecap="round" stroke-linejoin="round" />
                                                        <rect x="6" y="14" width="12" height="6" rx="1" stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M5 10h14a2 2 0 0 1 2 2v4h-3" stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M6 17H5a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h1" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </button>

                                                <button type="button"
                                                    onclick='openEditModal(@json(route("pembayaran.public.update", $bayar->id)), @json($bayar->tanggal_bayar->format("Y-m-d")), {{ (int) $bayar->nominal_bayar }}, @json($bayar->metode), @json($bayar->keterangan), @json($bayar->admin_penerima), {{ (int) $tagihan->sisa + (int) $bayar->nominal_bayar }})'
                                                    title="Edit"
                                                    aria-label="Edit"
                                                    class="text-amber-500 hover:text-amber-700 inline-flex items-center justify-center p-1 rounded ml-2">
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                        <path d="M4 20h4l10-10-4-4L4 16v4Z" stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M13 7l4 4" stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M15 5l2-2a1.5 1.5 0 0 1 2 2l-2 2" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </button>

                                                <button type="button"
                                                    onclick="openHapusModal('{{ route('pembayaran.public.destroy', $bayar->id) }}', '{{ $bayar->tanggal_bayar->format('d M Y') }}', '{{ number_format($bayar->nominal_bayar, 0, ',', '.') }}')"
                                                    title="Hapus"
                                                    aria-label="Hapus"
                                                    class="text-red-500 hover:text-red-700 inline-flex items-center justify-center p-1 rounded ml-2">
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                        <path d="M4 7h16" stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M10 3h4" stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M8 7l1 13h6l1-13" stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M10 11v6M14 11v6" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </button>

                                            </td>

                                        </tr>

                                        @endforeach

                                    </tbody>

                                </table>
                            </div>

                        </td>

                    </tr>

                    @endif

                    <tr>
                        <td colspan="7" style="border: none; height: 10px; padding: 0;"></td>
                    </tr>


                    @endforeach

                </tbody>



                <tfoot>

                    <tr class="bg-slate-100 font-semibold">

                        <td class="p-3 border text-right" colspan="3">
                            Total Biaya
                        </td>

                        <td class="p-3 border text-right">
                            Rp {{ number_format($totalBiaya, 0, ',', '.') }}
                        </td>

                        <td colspan="3" class="border"></td>

                    </tr>

                    <tr class="bg-red-50 font-semibold">

                        <td class="p-3 border text-right" colspan="4">
                            Total Kekurangan
                        </td>

                        <td class="p-3 border text-right text-red-600">
                            Rp {{ number_format($totalKekurangan, 0, ',', '.') }}
                        </td>

                        <td colspan="2" class="border p-2 text-right">
                            <button type="button"
                                onclick="openNotaModal('{{ route('pendaftaran.biaya.nota.post', $siswa->id) }}')"
                                class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs px-3 py-1.5 rounded-lg font-semibold shadow-sm">
                                Cetak Rincian Biaya
                            </button>
                        </td>

                    </tr>

                </tfoot>

            </table>

        </div>



        <div class="mt-6">

            <a href="{{ route('pendaftaran.list') }}" class="btn-primary inline-block">
                Kembali
            </a>

        </div>

    </div>

</div>

{{-- ===============================
    MODAL PEMBAYARAN
    =============================== --}}
{{-- ===============================
    MODAL PEMBAYARAN
    =============================== --}}
<div id="modalBayar" class="fixed inset-0 z-[300] hidden items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm transition-all duration-300">
    <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-8 shadow-2xl transform transition-all duration-300">

        <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
            💳
        </div>

        <div class="text-center mb-6">
            <h3 class="text-xl font-bold text-slate-800">Pembayaran Cicilan</h3>
            <p class="text-sm text-slate-500 mt-1">Gunakan form ini untuk mencatat pembayaran baru siswa.</p>
        </div>

        <form id="formBayar" onsubmit="handleFormPaymentSubmit(event)" class="space-y-4">
            @csrf
            <input type="hidden" name="tagihan_siswa_id" id="tagihan_id">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Sisa Tagihan</label>
                    <input type="text" id="sisa_tagihan" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-bold text-slate-700" readonly>
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Tanggal Bayar</label>
                    <input type="date" name="tanggal_bayar" value="{{ date('Y-m-d') }}" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all" required>
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Nominal Bayar</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">Rp.</span>
                    <input type="text" id="nominal_display"
                        class="w-full rounded-xl border-slate-200 bg-slate-50 pl-12 pr-4 py-3 text-sm font-bold text-slate-800 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all"
                        placeholder="0" onkeyup="formatRupiah(this)" required>
                </div>
                <input type="hidden" name="nominal_bayar" id="nominal_bayar">
                <p id="nominalErrorText" class="mt-1.5 text-[11px] font-medium text-red-500 hidden bg-red-50 p-2 rounded-lg border border-red-100 italic">
                    ⚠️ Nominal melebihi sisa tagihan!
                </p>

                <div class="mt-3 flex flex-wrap gap-1.5">
                    @foreach([50, 100, 150, 200, 500] as $n)
                    <button type="button" onclick="applyQuickNominal({{ $n * 1000 }})"
                        class="rounded-lg border border-slate-100 bg-slate-50 px-2 py-1 text-[10px] font-bold text-slate-500 transition hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200">
                        {{ $n }}.000
                    </button>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Metode</label>
                    <select name="metode" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all">
                        <option value="cash">Cash</option>
                        <option value="transfer">Transfer</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Petugas Penerima</label>
                    <input type="text" name="admin_penerima" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all" placeholder="Nama..." required>
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Keterangan (Opsional)</label>
                <input type="text" name="keterangan" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all" placeholder="...">
            </div>

            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeBayarModal()" class="flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors">Batal</button>
                <button type="submit" id="btnSimpanPembayaran" class="flex-1 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-500/30 hover:bg-emerald-700 transition-all">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- ===============================
    MODAL KONFIRMASI HAPUS CICILAN
    =============================== --}}
{{-- ===============================
    MODAL KONFIRMASI HAPUS CICILAN
    =============================== --}}
<div id="modalHapus" class="fixed inset-0 z-[300] hidden items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm transition-all duration-300">
    <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-8 shadow-2xl transform transition-all duration-300">
        <div class="w-20 h-20 bg-red-50 text-red-600 rounded-3xl flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
        </div>

        <div class="text-center mb-6">
            <h3 class="text-xl font-bold text-slate-800">Hapus Riwayat Cicilan?</h3>
            <p class="text-sm text-slate-500 mt-2 italic">Aksi ini tidak dapat dibatalkan.</p>
        </div>

        <div class="bg-slate-50 border border-slate-100 rounded-2xl p-5 mb-6">
            <div class="flex justify-between items-center mb-2">
                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Tanggal</span>
                <span id="hapusTanggal" class="text-sm font-bold text-slate-700"></span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Nominal</span>
                <span id="hapusNominal" class="text-sm font-bold text-slate-700"></span>
            </div>
        </div>

        <form id="formHapus" method="POST" class="space-y-5">
            @csrf
            @method('DELETE')

            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Nama Petugas Konfirmasi</label>
                <input type="text" name="admin_penghapus" id="adminPenghapus"
                    class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-red-500 focus:ring-4 focus:ring-red-500/10 transition-all font-semibold"
                    placeholder="Masukkan nama petugas..." required>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="closeHapusModal()" class="flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors">Batal</button>
                <button type="submit" class="flex-1 rounded-xl bg-red-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-red-500/30 hover:bg-red-700 transition-all">Ya, Hapus</button>
            </div>
        </form>
    </div>
</div>

{{-- ===============================
    MODAL EDIT CICILAN
    =============================== --}}
{{-- ===============================
    MODAL EDIT CICILAN
    =============================== --}}
<div id="modalEdit" class="fixed inset-0 z-[300] hidden items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm transition-all duration-300">
    <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-8 shadow-2xl transform transition-all duration-300">

        <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
            📝
        </div>

        <div class="text-center mb-6">
            <h3 class="text-xl font-bold text-slate-800">Edit Riwayat Cicilan</h3>
            <p class="text-sm text-slate-500 mt-1">Perbarui data pembayaran yang salah input.</p>
        </div>

        <form id="formEdit" method="POST" onsubmit="return confirmEditCicilan(event)" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Tanggal Bayar</label>
                    <input type="date" name="tanggal_bayar" id="editTanggalBayar"
                        class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-semibold" required>
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Nominal</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">Rp.</span>
                        <input type="text" id="editNominalDisplay"
                            class="w-full rounded-xl border-slate-200 bg-slate-50 pl-12 pr-4 py-2.5 text-sm font-bold text-slate-800 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all"
                            placeholder="0" oninput="formatRupiahEdit(this)" required>
                    </div>
                    <input type="hidden" name="nominal_bayar" id="editNominalBayar">
                    <p id="editNominalErrorText" class="mt-1.5 text-[11px] font-medium text-red-500 hidden bg-red-50 p-2 rounded-lg border border-red-100 italic">
                        ⚠️ Nominal melebihi sisa tagihan!
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Metode</label>
                    <select name="metode" id="editMetode" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all">
                        <option value="cash">Cash</option>
                        <option value="transfer">Transfer</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Petugas Penerima</label>
                    <input type="text" name="admin_penerima" id="editAdminPenerima"
                        class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all" required>
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Keterangan</label>
                <input type="text" name="keterangan" id="editKeterangan"
                    class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all" placeholder="opsional">
            </div>

            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Nama Petugas Pengubah</label>
                <input type="text" name="admin_pengubah" id="editAdminPengubah"
                    class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-semibold"
                    placeholder="Masukkan nama petugas..." required>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeEditModal()" class="flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors">Batal</button>
                <button type="submit" class="flex-1 rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-blue-500/30 hover:bg-blue-700 transition-all">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- ===============================
    MODAL CETAK NOTA (INPUT PANITIA)
    =============================== --}}
{{-- ===============================
    MODAL CETAK NOTA (INPUT PANITIA)
    =============================== --}}
<div id="modalNota" class="fixed inset-0 z-[300] hidden items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm transition-all duration-300">
    <div class="w-full max-w-sm rounded-3xl border border-slate-200 bg-white p-8 shadow-2xl transform transition-all duration-300">

        <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
            🖨️
        </div>

        <div class="text-center mb-6">
            <h3 class="text-xl font-bold text-slate-800">Cetak Kuitansi</h3>
            <p class="text-sm text-slate-500 mt-1">Siapkan salinan fisik untuk wali murid.</p>
        </div>

        <form id="formNota" method="POST" target="_blank" class="space-y-5">
            @csrf

            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Nama Panitia PPDB</label>
                <input type="text" name="panitia" id="inputPanitia"
                    class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-semibold"
                    placeholder="Petugas yang bertanda tangan..." required>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="closeNotaModal()" class="flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors">Batal</button>
                <button type="submit" class="flex-1 rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-blue-500/30 hover:bg-blue-700 transition-all">Cetak</button>
            </div>
        </form>
    </div>
</div>

<script>
    // ---- Session Status Check (OUTPUT BOOLEAN, NOT STRING) ----
    const userHasAksesPembayaran = @if(session('akses_pembayaran')) true @else false @endif;
    console.log('📋 Page loaded - userHasAksesPembayaran =', userHasAksesPembayaran, 'type:', typeof userHasAksesPembayaran);

    // ---- Handle Form Pembayaran Submit via AJAX ----
    function handleFormPaymentSubmit(event) {
        event.preventDefault();

        const nominalBayar = Number(document.getElementById('nominal_bayar').value || 0);
        const nominalValid = validateNominalAgainstSisa(nominalBayar);

        if (!nominalValid) {
            document.getElementById('nominal_display').focus();
            return false;
        }

        console.log('Form pembayaran submit - checking session...', userHasAksesPembayaran);

        // Jika user BELUM punya akses pembayaran
        if (!userHasAksesPembayaran) {
            console.log('❌ No akses_pembayaran session - showing password modal');

            // Set redirect ke halaman biaya saat ini setelah password verified
            const currentUrl = window.location.href;
            bukaPasswordModal(currentUrl);

            // Setelah user verifikasi password dan halaman reload, 
            // user bisa submit form pembayaran lagi dengan session yang sudah ada
            return false;
        }

        // ✅ User sudah punya akses - submit form via AJAX
        console.log('✅ Has akses_pembayaran session - submitting form via AJAX');
        submitFormPembayaranAjax();
    }

    // ---- Submit Pembayaran Form via AJAX ----
    function submitFormPembayaranAjax() {
        const form = document.getElementById('formBayar');
        const formData = new FormData(form);
        const btn = document.getElementById('btnSimpanPembayaran');

        console.log('📤 Submitting form data via AJAX...');
        console.log('Form data entries:', Array.from(formData.entries()));

        // Extract CSRF token dari form atau meta tag
        let csrfToken = form.querySelector('input[name="_token"]')?.value;
        if (!csrfToken) {
            csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        }
        console.log('🔐 CSRF Token found:', !!csrfToken, '(' + (csrfToken ? csrfToken.substring(0, 10) + '...' : 'none') + ')');

        // Ensure CSRF token in FormData
        if (csrfToken) {
            formData.set('_token', csrfToken); // Use set instead of append to avoid duplicates
            console.log('✅ CSRF token added to FormData');
        }

        // Disable button
        btn.disabled = true;
        btn.textContent = 'Menyimpan...';

        console.log('🔐 Sending request with credentials: include');

        fetch('{{ route("pembayaran.public.store") }}', {
                method: 'POST',
                body: formData,
                credentials: 'include', // ← CRITICAL: Send session cookies!
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken || '', // ← Add CSRF to header as well
                }
            })
            .then(response => {
                console.log('📨 Response received');
                console.log('Response status:', response.status);
                console.log('Response ok:', response.ok);
                console.log('Response type:', response.type);
                console.log('Response url:', response.url);

                // Log response headers
                console.log('--- Response Headers ---');
                response.headers.forEach((value, name) => {
                    if (name.toLowerCase().includes('csrf') ||
                        name.toLowerCase().includes('set-cookie') ||
                        name.toLowerCase().includes('cookie')) {
                        console.log(`${name}: ${value}`);
                    }
                });

                // Parse JSON dulu
                return response.json().then(data => {
                    console.log('📦 Response JSON data:', data);
                    console.log('Response keys:', Object.keys(data));
                    console.log('Response full:', JSON.stringify(data, null, 2));

                    // Handle non-2xx status codes
                    if (!response.ok) {
                        console.warn('❌ Response not ok. Status:', response.status);
                        console.warn('Error details:', data);

                        // If 403 Unauthorized (CSRF fail atau akses ditolak)
                        if (response.status === 403) {
                            console.log('❌ Status 403 - Forbidden/Unauthorized');
                            console.warn('This could be CSRF token issue or session expired');
                            if (typeof window.showGlobalToast === 'function') {
                                window.showGlobalToast('warning', 'Akses ditolak (403). Silakan verifikasi password panitia kembali.');
                            }
                            bukaPasswordModal(window.location.href);
                            throw new Error('403 Forbidden - showing password modal');
                        }

                        // Other error responses
                        throw new Error(data.error || data.message || `Server error (${response.status})`);
                    }

                    // Success response (2xx status)
                    if (data.success) {
                        console.log('✅ Success response received');
                        return data;
                    }

                    // No success flag - treat as error
                    console.warn('❌ Response has no success flag or success=false');
                    throw new Error(data.error || data.message || 'Unknown error');
                });
            })
            .then(data => {
                // Only reach here if success = true
                console.log('✅ Payment saved successfully!');
                console.log('Response data:', data);
                window.location.href = data.redirect || redirectUrl || window.location.href;
            })
            .catch(error => {
                console.error('❌ Error submitting form:', error);
                console.error('Error message:', error.message);
                console.error('Error stack:', error.stack);

                // Don't show alert if it's the session expired error (password modal already shown)
                if (!error.message.includes('Session expired')) {
                    if (typeof window.showGlobalToast === 'function') {
                        window.showGlobalToast('danger', 'Terjadi error: ' + error.message);
                    }
                }
            })
            .finally(() => {
                console.log('🏁 AJAX submit finalized');
                // Re-enable button
                btn.disabled = false;
                btn.textContent = 'Simpan Pembayaran';
            });
    }

    // ---- Modal Pembayaran ----
    let currentSisaTagihan = 0;

    function openBayarModal(tagihanId, sisa) {
        document.getElementById('modalBayar').classList.remove('hidden');
        document.getElementById('modalBayar').classList.add('flex');

        currentSisaTagihan = Number(sisa) || 0;

        document.getElementById('tagihan_id').value = tagihanId;

        document.getElementById('sisa_tagihan').value =
            'Rp ' + new Intl.NumberFormat('id-ID').format(sisa);

        document.getElementById('nominal_bayar').max = sisa;
        document.getElementById('nominal_bayar').value = '';
        document.getElementById('nominal_display').value = '';
        validateNominalAgainstSisa(0);
    }

    function closeBayarModal() {
        document.getElementById('modalBayar').classList.add('hidden');
        document.getElementById('modalBayar').classList.remove('flex');
    }

    function formatRupiah(input) {
        let angka = input.value.replace(/\D/g, '');

        document.getElementById('nominal_bayar').value = angka;

        validateNominalAgainstSisa(Number(angka || 0));

        let formatted = new Intl.NumberFormat('id-ID').format(angka);

        input.value = formatted;
    }

    function validateNominalAgainstSisa(nominal) {
        const errorEl = document.getElementById('nominalErrorText');
        const isExceeded = Number(nominal || 0) > Number(currentSisaTagihan || 0);

        if (isExceeded) {
            errorEl.classList.remove('hidden');
            return false;
        }

        errorEl.classList.add('hidden');
        return true;
    }

    function applyQuickNominal(nominal) {
        const selectedNominal = Number(nominal) || 0;
        const finalNominal = currentSisaTagihan > 0 ?
            Math.min(selectedNominal, currentSisaTagihan) :
            selectedNominal;

        const nominalInput = document.getElementById('nominal_bayar');
        const nominalDisplay = document.getElementById('nominal_display');

        nominalInput.value = finalNominal;
        nominalDisplay.value = new Intl.NumberFormat('id-ID').format(finalNominal);
        validateNominalAgainstSisa(finalNominal);
    }

    function toggleRiwayatCicilan(tagihanId) {
        const contentEl = document.getElementById('riwayatContent' + tagihanId);
        const btnEl = document.getElementById('riwayatToggleBtn' + tagihanId);
        const iconEl = btnEl ? btnEl.querySelector('svg') : null;

        if (!contentEl || !btnEl) return;

        const isHidden = contentEl.classList.contains('hidden');
        if (isHidden) {
            contentEl.classList.remove('hidden');
            iconEl?.classList.add('rotate-180');
        } else {
            contentEl.classList.add('hidden');
            iconEl?.classList.remove('rotate-180');
        }
    }

    // ---- Modal Hapus Cicilan ----
    function openHapusModal(actionUrl, tanggal, nominal) {
        document.getElementById('formHapus').action = actionUrl;
        document.getElementById('hapusTanggal').textContent = tanggal;
        document.getElementById('hapusNominal').textContent = 'Rp ' + nominal;

        document.getElementById('modalHapus').classList.remove('hidden');
        document.getElementById('modalHapus').classList.add('flex');
    }

    function closeHapusModal() {
        document.getElementById('modalHapus').classList.add('hidden');
        document.getElementById('modalHapus').classList.remove('flex');
    }

    let currentEditMaxNominal = 0;

    function openEditModal(actionUrl, tanggal, nominal, metode, keterangan, adminPenerima, maxNominal) {
        document.getElementById('formEdit').action = actionUrl;
        document.getElementById('editTanggalBayar').value = tanggal || '';
        document.getElementById('editNominalBayar').value = nominal || '';
        document.getElementById('editNominalDisplay').value = new Intl.NumberFormat('id-ID').format(Number(nominal || 0));
        document.getElementById('editMetode').value = (metode || '').toLowerCase();
        document.getElementById('editKeterangan').value = keterangan || '';
        document.getElementById('editAdminPenerima').value = adminPenerima || '';
        document.getElementById('editAdminPengubah').value = '';
        currentEditMaxNominal = Number(maxNominal || 0);
        validateEditNominal(Number(nominal || 0));

        const modal = document.getElementById('modalEdit');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeEditModal() {
        const modal = document.getElementById('modalEdit');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function formatRupiahEdit(input) {
        const angka = input.value.replace(/\D/g, '');
        document.getElementById('editNominalBayar').value = angka;
        validateEditNominal(Number(angka || 0));
        input.value = new Intl.NumberFormat('id-ID').format(angka || 0);
    }

    function validateEditNominal(nominal) {
        const errorEl = document.getElementById('editNominalErrorText');
        const isExceeded = Number(nominal || 0) > Number(currentEditMaxNominal || 0);

        if (isExceeded) {
            errorEl.classList.remove('hidden');
            return false;
        }

        errorEl.classList.add('hidden');
        return true;
    }

    function confirmEditCicilan(event) {
        event.preventDefault();

        const nominal = Number(document.getElementById('editNominalBayar').value || 0);
        if (nominal < 1) {
            if (typeof window.showGlobalToast === 'function') {
                window.showGlobalToast('warning', 'Nominal bayar harus lebih dari 0');
            }
            document.getElementById('editNominalDisplay').focus();
            return false;
        }

        if (!validateEditNominal(nominal)) {
            document.getElementById('editNominalDisplay').focus();
            return false;
        }

        const form = document.getElementById('formEdit');
        if (!form) {
            return false;
        }

        if (typeof window.globalConfirmSubmit === 'function') {
            return window.globalConfirmSubmit(form, 'Yakin ingin menyimpan perubahan cicilan ini?', {
                title: 'Konfirmasi Simpan'
            });
        }

        form.submit();
        return false;
    }

    // ---- Modal Cetak Nota ----
    function openNotaModal(actionUrl) {
        document.getElementById('formNota').action = actionUrl;
        document.getElementById('inputPanitia').value = '';

        document.getElementById('modalNota').classList.remove('hidden');
        document.getElementById('modalNota').classList.add('flex');
    }

    function closeNotaModal() {
        document.getElementById('modalNota').classList.add('hidden');
        document.getElementById('modalNota').classList.remove('flex');
    }

    // ---- Modal Password Panitia ----
    function bukaPasswordModal(url) {
        document.getElementById('redirectUrl').value = url;
        const modal = document.getElementById('modalPassword');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closePasswordModal() {
        const modal = document.getElementById('modalPassword');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // ---- Handle Password Verification (AJAX) ----
    function handlePasswordSubmit(event) {
        event.preventDefault();

        const pwd = document.getElementById('inputPasswordPanitia').value;
        const redirectUrl = document.getElementById('redirectUrl').value;
        const btn = event.target.querySelector('button[type="submit"]');

        console.log('🔐 Password submit - redirectUrl:', redirectUrl);

        if (!pwd) {
            if (typeof window.showGlobalToast === 'function') {
                window.showGlobalToast('warning', 'Masukkan password terlebih dahulu');
            }
            return false;
        }

        btn.disabled = true;
        btn.textContent = 'Memverifikasi...';

        fetch('{{ route("verifikasi.password.panitia") }}', {
                method: 'POST',
                credentials: 'include', // ← CRITICAL: Send & receive session cookies!
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '',
                },
                body: JSON.stringify({
                    password: pwd,
                    redirect_url: redirectUrl
                })
            })
            .then(response => {
                console.log('🔐 Password response status:', response.status);
                return response.json().then(data => {
                    console.log('🔐 Password response data:', data);

                    if (!response.ok) {
                        throw new Error(data.error || data.message || 'Verifikasi gagal');
                    }
                    return data;
                });
            })
            .then(data => {
                console.log('✅ Password verified! Reloading page...');
                // Password verified, session now set on server
                // Reload page to get fresh session state
                if (typeof window.showGlobalToast === 'function') {
                    window.showGlobalToast('success', 'Password terverifikasi. Memuat ulang halaman...');
                }
                window.location.reload();
            })
            .catch(error => {
                console.error('❌ Password error:', error.message);
                if (typeof window.showGlobalToast === 'function') {
                    window.showGlobalToast('danger', 'Error: ' + error.message);
                }
                btn.disabled = false;
                btn.textContent = 'Verifikasi';
            });

        return false;
    }
</script>

{{-- ===============================
    MODAL PASSWORD VERIFIKASI
    =============================== --}}
<div id="modalPassword"
    onclick="closePasswordModal()"
    class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4 transition-all duration-300">

    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm p-8 relative transform transition-all duration-300" onclick="event.stopPropagation()">

        <button
            type="button"
            onclick="closePasswordModal()"
            class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 p-1"
            aria-label="Tutup modal">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
                🔐
            </div>
            <h3 class="text-xl font-bold text-slate-800">Verifikasi Panitia</h3>
            <p class="text-sm text-slate-500 mt-1">Masukkan password panitia untuk melanjutkan.</p>
        </div>

        <form id="formPasswordVerifikasi" onsubmit="handlePasswordSubmit(event)">
            @csrf
            <input type="hidden" name="redirect_url" id="redirectUrl">

            <div class="mb-5">
                <input
                    type="password"
                    id="inputPasswordPanitia"
                    name="password"
                    class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500 transition-all"
                    placeholder="Masukkan password"
                    required
                    autofocus>
            </div>

            <div class="flex gap-3">
                <button
                    type="button"
                    onclick="closePasswordModal()"
                    class="flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                    Batal
                </button>
                <button type="submit" class="flex-1 rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-blue-500/30 hover:bg-blue-700 transition-all">
                    Verifikasi
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
