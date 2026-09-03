@extends('layouts.public')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8 grid md:grid-cols-2 gap-6 lg:gap-8">

    {{-- DATA PENDAFTAR --}}
    <div class="space-y-4"
        x-data="successPager({ hasWali: @js($siswa->tinggal_bersama === 'wali') })"
        x-init="init()">
        <div class="card">
            <h2 class="font-semibold text-xl text-slate-800 mb-4">
                ✅ Pendaftaran Berhasil
            </h2>

            <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-4 text-sm text-green-800 leading-relaxed">
                Data calon peserta didik berhasil disimpan.
                Silakan lakukan pembayaran biaya pendaftaran.
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 text-sm text-slate-600">
                <p>
                    Data <span class="font-semibold text-slate-800" x-text="currentIndex + 1"></span>
                    dari <span class="font-semibold text-slate-800" x-text="steps.length"></span>
                </p>
                <div class="flex items-center gap-1">
                    <template x-for="(step, idx) in steps" :key="step.key">
                        <button
                            type="button"
                            @click="goTo(idx)"
                            class="h-2.5 w-2.5 rounded-full transition"
                            :class="idx === currentIndex ? 'bg-blue-600 scale-110' : 'bg-slate-300 hover:bg-slate-400'"
                            :title="step.title"
                        ></button>
                    </template>
                </div>
            </div>
        </div>

        {{-- Data Siswa --}}
        <div x-show="isCurrent('siswa')" x-cloak
            class="card overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm text-slate-700">
            <h3 class="font-semibold px-4 py-3 border-b border-slate-200 bg-slate-50">Data Peserta Didik</h3>
            <div class="p-4 text-sm text-slate-900">
                <div class="[&>div]:grid [&>div]:grid-cols-1 sm:[&>div]:grid-cols-[180px_1fr] [&>div]:gap-1 sm:[&>div]:gap-4 [&>div]:py-3 [&>div]:border-b [&>div]:border-dashed [&>div]:border-slate-200 [&>div:last-child]:border-b-0">
                    <div><p class="text-slate-500">Nama Peserta Didik</p><p>{{ $siswa->nama }}</p></div>
                    <div><p class="text-slate-500">No Registrasi</p><p>{{ optional($siswa->registration)->nomor_registrasi ?? '-' }}</p></div>
                    <div><p class="text-slate-500">Jenis Kelamin</p><p>{{ ui_label($siswa->jenis_kelamin) }}</p></div>
                    <div><p class="text-slate-500">NIK</p><p>{{ $siswa->nik ?? '-' }}</p></div>
                    <div><p class="text-slate-500">No KK</p><p>{{ $siswa->no_kk ?? '-' }}</p></div>
                    <div><p class="text-slate-500">Tempat / Tgl Lahir</p><p>{{ $siswa->tempat_lahir }}, {{ \Carbon\Carbon::parse($siswa->tanggal_lahir)->format('d-m-Y') }}</p></div>
                    <div><p class="text-slate-500">No Akta Lahir</p><p>{{ $siswa->akta_no ?? '-' }}</p></div>
                    <div><p class="text-slate-500">Agama</p><p>{{ $siswa->agama ?? 'Islam' }}</p></div>
                    <div><p class="text-slate-500">Kewarganegaraan</p><p>{{ $siswa->kewarganegaraan ?? 'Indonesia' }}</p></div>
                    <div><p class="text-slate-500">Berkebutuhan Khusus</p><p>{{ $siswa->berkebutuhan_khusus ?? '-' }}</p></div>
                    <div><p class="text-slate-500">Tinggal Bersama</p><p>{{ ui_label($siswa->tinggal_bersama ?? '-') }}</p></div>
                    <div><p class="text-slate-500">No KKS</p><p>{{ $siswa->no_kks ?? '-' }}</p></div>
                    <div><p class="text-slate-500">Penerima KPS/PKH</p><p>{{ $siswa->kps ?? '-' }}</p></div>
                    <div><p class="text-slate-500">Apakah Punya KIP</p><p>{{ $siswa->kip ?? '-' }}</p></div>
                    <div><p class="text-slate-500">Apakah Peserta Layak Mendapatkan PIP</p><p>{{ $siswa->layak_pip ?? '-' }}</p></div>
                    <div><p class="text-slate-500">Transportasi</p><p>{{ ui_label($siswa->transportasi ?? '-') }}</p></div>
                    <div><p class="text-slate-500">Hasil Tes</p><p><span class="badge-success">{{ $siswa->hasil_tes }}</span></p></div>
                </div>
            </div>
        </div>

        {{-- Data Alamat --}}
        <div x-show="isCurrent('alamat')" x-cloak
            class="card overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm text-slate-700">
            <h3 class="font-semibold px-4 py-3 border-b border-slate-200 bg-slate-50">Data Alamat</h3>
            <div class="p-4 text-sm text-slate-900">
                <div class="[&>div]:grid [&>div]:grid-cols-1 sm:[&>div]:grid-cols-[180px_1fr] [&>div]:gap-1 sm:[&>div]:gap-4 [&>div]:py-3 [&>div]:border-b [&>div]:border-dashed [&>div]:border-slate-200 [&>div:last-child]:border-b-0">
                    <div><p class="text-slate-500">Alamat Lengkap</p><p>{{ optional($siswa->alamat)->alamat ?? '-' }}</p></div>
                    <div><p class="text-slate-500">Provinsi</p><p>{{ optional($siswa->alamat)->provinsi ?? '-' }}</p></div>
                    <div><p class="text-slate-500">Kabupaten</p><p>{{ optional($siswa->alamat)->kabupaten ?? '-' }}</p></div>
                    <div><p class="text-slate-500">Kecamatan</p><p>{{ optional($siswa->alamat)->kecamatan ?? '-' }}</p></div>
                    <div><p class="text-slate-500">Kelurahan</p><p>{{ optional($siswa->alamat)->kelurahan ?? '-' }}</p></div>
                    <div><p class="text-slate-500">RT / RW</p><p>{{ optional($siswa->alamat)->rt ?? '-' }} / {{ optional($siswa->alamat)->rw ?? '-' }}</p></div>
                    <div><p class="text-slate-500">Kode Pos</p><p>{{ optional($siswa->alamat)->kode_pos ?? '-' }}</p></div>
                </div>
            </div>
        </div>

        {{-- Data Ibu --}}
        <div x-show="isCurrent('ibu')" x-cloak
            class="card overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm text-slate-700">
            <h3 class="font-semibold px-4 py-3 border-b border-slate-200 bg-slate-50">Data Ibu</h3>
            <div class="p-4 text-sm text-slate-900">
                <div class="[&>div]:grid [&>div]:grid-cols-1 sm:[&>div]:grid-cols-[180px_1fr] [&>div]:gap-1 sm:[&>div]:gap-4 [&>div]:py-3 [&>div]:border-b [&>div]:border-dashed [&>div]:border-slate-200 [&>div:last-child]:border-b-0">
                    <div><p class="text-slate-500">Nama Ibu</p><p>{{ optional($siswa->ibu)->nama ?? '-' }}</p></div>
                    <div><p class="text-slate-500">No HP</p><p>{{ optional($siswa->ibu)->no_hp ?? '-' }}</p></div>
                    <div><p class="text-slate-500">NIK</p><p>{{ optional($siswa->ibu)->nik ?? '-' }}</p></div>
                    <div><p class="text-slate-500">Tahun Lahir</p><p>{{ optional($siswa->ibu)->tahun_lahir ?? '-' }}</p></div>
                    <div><p class="text-slate-500">Pendidikan</p><p>{{ optional($siswa->ibu)->pendidikan ?? '-' }}</p></div>
                    <div><p class="text-slate-500">Pekerjaan</p><p>{{ optional($siswa->ibu)->pekerjaan ?? '-' }}</p></div>
                    <div><p class="text-slate-500">Penghasilan</p><p>{{ optional($siswa->ibu)->penghasilan ?? '-' }}</p></div>
                </div>
            </div>
        </div>

        {{-- Data Ayah --}}
        <div x-show="isCurrent('ayah')" x-cloak
            class="card overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm text-slate-700">
            <h3 class="font-semibold px-4 py-3 border-b border-slate-200 bg-slate-50">Data Ayah</h3>
            <div class="p-4 text-sm text-slate-900">
                <div class="[&>div]:grid [&>div]:grid-cols-1 sm:[&>div]:grid-cols-[180px_1fr] [&>div]:gap-1 sm:[&>div]:gap-4 [&>div]:py-3 [&>div]:border-b [&>div]:border-dashed [&>div]:border-slate-200 [&>div:last-child]:border-b-0">
                    <div><p class="text-slate-500">Nama Ayah</p><p>{{ optional($siswa->ayah)->nama ?? '-' }}</p></div>
                    <div><p class="text-slate-500">NIK</p><p>{{ optional($siswa->ayah)->nik ?? '-' }}</p></div>
                    <div><p class="text-slate-500">Tahun Lahir</p><p>{{ optional($siswa->ayah)->tahun_lahir ?? '-' }}</p></div>
                    <div><p class="text-slate-500">Pendidikan</p><p>{{ optional($siswa->ayah)->pendidikan ?? '-' }}</p></div>
                    <div><p class="text-slate-500">Pekerjaan</p><p>{{ optional($siswa->ayah)->pekerjaan ?? '-' }}</p></div>
                    <div><p class="text-slate-500">Penghasilan</p><p>{{ optional($siswa->ayah)->penghasilan ?? '-' }}</p></div>
                </div>
            </div>
        </div>

        {{-- Data Wali --}}
        <div x-show="isCurrent('wali')" x-cloak
            class="card overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm text-slate-700">
            <h3 class="font-semibold px-4 py-3 border-b border-slate-200 bg-slate-50">Data Wali</h3>
            <div class="p-4 text-sm text-slate-900">
                <div class="[&>div]:grid [&>div]:grid-cols-1 sm:[&>div]:grid-cols-[180px_1fr] [&>div]:gap-1 sm:[&>div]:gap-4 [&>div]:py-3 [&>div]:border-b [&>div]:border-dashed [&>div]:border-slate-200 [&>div:last-child]:border-b-0">
                    <div><p class="text-slate-500">Nama Wali</p><p>{{ $siswa->wali_nama ?? '-' }}</p></div>
                    <div><p class="text-slate-500">No HP Wali</p><p>{{ $siswa->hp_wali ?? '-' }}</p></div>
                    <div><p class="text-slate-500">Hubungan</p><p>{{ $siswa->wali_hubungan ?? '-' }}</p></div>
                </div>
            </div>
        </div>

        {{-- Data Pendukung --}}
        <div x-show="isCurrent('pendukung')" x-cloak
            class="card overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm text-slate-700">
            <h3 class="font-semibold px-4 py-3 border-b border-slate-200 bg-slate-50">Data Pendukung</h3>
            <div class="p-4 text-sm text-slate-900">
                <div class="[&>div]:grid [&>div]:grid-cols-1 sm:[&>div]:grid-cols-[180px_1fr] [&>div]:gap-1 sm:[&>div]:gap-4 [&>div]:py-3 [&>div]:border-b [&>div]:border-dashed [&>div]:border-slate-200 [&>div:last-child]:border-b-0">
                    <div><p class="text-slate-500">Tinggi / Berat</p><p>{{ optional($siswa->dataPendukung)->tinggi ?? '-' }} cm / {{ optional($siswa->dataPendukung)->berat ?? '-' }} kg</p></div>
                    <div><p class="text-slate-500">Jarak Rumah</p><p>{{ optional($siswa->dataPendukung)->jarak ?? '-' }} km</p></div>
                    <div><p class="text-slate-500">Jumlah Saudara</p><p>{{ optional($siswa->dataPendukung)->jumlah_saudara ?? '-' }}</p></div>
                    <div><p class="text-slate-500">Anak Ke (berdasarkan KK)</p><p>{{ optional($siswa->dataPendukung)->anak_ke ?? '-' }}</p></div>
                    @php
                        $dp = $siswa->dataPendukung;
                        $isManual = (bool) (optional($dp)->is_tk_manual ?? false);

                        $tkNama = $isManual
                            ? ($dp->nama_tk_manual ?? null)
                            : (optional(optional($dp)->paudTk)->nama ?? null);
                        $tkAlamat = $isManual
                            ? ($dp->alamat_tk ?? null)
                            : (optional(optional($dp)->paudTk)->alamat ?? null);

                        // Fallback: jika flag manual belum tersimpan tapi field manual sudah ada.
                        if (($tkNama === null || $tkNama === '') && (optional($dp)->nama_tk_manual ?? null)) {
                            $tkNama = $dp->nama_tk_manual;
                        }
                        if (($tkAlamat === null || $tkAlamat === '') && (optional($dp)->alamat_tk ?? null)) {
                            $tkAlamat = $dp->alamat_tk;
                        }
                    @endphp
                    <div><p class="text-slate-500">Asal PAUD / TK</p><p>{{ $tkNama ?? '-' }}</p></div>
                    <div><p class="text-slate-500">Alamat TK</p><p>{{ $tkAlamat ?? '-' }}</p></div>
                    <div><p class="text-slate-500">Hobi / Cita-cita</p><p>{{ optional($siswa->dataPendukung)->hobi ?? '-' }} / {{ optional($siswa->dataPendukung)->cita_cita ?? '-' }}</p></div>
                </div>
            </div>
        </div>

        <div class="card flex flex-wrap items-center justify-between gap-3">
            <button
                type="button"
                @click="prev()"
                :disabled="currentIndex === 0"
                class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 disabled:opacity-50 disabled:cursor-not-allowed transition focus:outline-none focus:ring-2 focus:ring-blue-300"
            >
                ← Sebelumnya
            </button>
            <p class="text-sm text-slate-600" x-text="currentTitle()"></p>
            <button
                type="button"
                @click="next()"
                :disabled="currentIndex === steps.length - 1"
                class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition focus:outline-none focus:ring-2 focus:ring-blue-300"
            >
                Berikutnya →
            </button>
        </div>
    </div>

    {{-- PANEL AKSI --}}
    <div class="card space-y-4 h-fit md:sticky md:top-4">

        {{-- Rincian Biaya Pendaftaran --}}
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <h3 class="font-semibold px-4 py-3 border-b border-slate-200 bg-slate-50">Rincian Biaya Pendaftaran</h3>
            <div class="p-3 space-y-3 text-sm text-slate-700">
                @php $grandTotal = 0; @endphp
                @foreach($siswa->tagihan as $t)
                    <div class="rounded-lg border border-slate-200 bg-white p-3">
                        <div class="flex items-start justify-between gap-3">
                            <p class="font-semibold text-slate-800">{{ $t->biaya->nama_biaya ?? '-' }}</p>
                            <span class="badge-{{ $t->status === 'lunas' ? 'success' : 'warning' }}">{{ ui_label($t->status) }}</span>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-x-4 gap-y-2 text-xs sm:text-sm">
                            <p class="text-slate-500">Nominal</p>
                            <p class="text-slate-900 text-right">{{ number_format($t->nominal,0,',','.') }}</p>
                            <p class="text-slate-500">Diskon</p>
                            <p class="text-slate-900 text-right">{{ number_format($t->diskon,0,',','.') }}</p>
                            <p class="text-slate-500">Sub Total</p>
                            <p class="font-semibold text-slate-900 text-right">{{ number_format($t->total,0,',','.') }}</p>
                        </div>
                    </div>
                    @php $grandTotal += $t->total; @endphp
                @endforeach

                <div class="rounded-lg border border-green-200 bg-green-50/70 p-3">
                    <div class="flex items-center justify-between gap-3">
                        <p class="font-semibold text-slate-700">Total Bayar</p>
                        <p class="text-base font-bold text-slate-900">{{ number_format($grandTotal,0,',','.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tombol Aksi --}}
        <div class="flex flex-row flex-nowrap gap-2 w-full">
            <button onclick="openModalPetugas({{ $siswa->id }})"
                class="basis-1/3 min-w-0 inline-flex items-center justify-center gap-1 rounded-lg bg-blue-600 px-2 py-2 text-white text-[11px] leading-tight font-semibold hover:bg-blue-700 transition focus:outline-none focus:ring-2 focus:ring-blue-300 text-center overflow-hidden">
                <span>🖨️</span>
                <span class="truncate">Cetak Formulir</span>
            </button>

            <button onclick="bukaPasswordModal('{{ route('pendaftaran.biaya', $siswa) }}')"
                class="basis-1/3 min-w-0 inline-flex items-center justify-center gap-1 rounded-lg bg-green-600 border border-green-700 px-2 py-2 text-white text-[11px] leading-tight font-semibold shadow-sm hover:bg-green-700 transition focus:outline-none focus:ring-2 focus:ring-green-300 text-center overflow-hidden">
                <span>💰</span>
                <span class="truncate">Input & Cetak Nota</span>
            </button>

            <button onclick="window.location='{{ route('pendaftaran.public.fresh') }}'"
                class="basis-1/3 min-w-0 inline-flex items-center justify-center gap-1 rounded-lg bg-slate-700 px-2 py-2 text-white text-[11px] leading-tight font-semibold hover:bg-slate-800 transition focus:outline-none focus:ring-2 focus:ring-slate-300 text-center overflow-hidden">
                <span>➕</span>
                <span class="truncate">Daftarkan Lagi</span>
            </button>
        </div>

    </div>

</div>

{{-- Modal wajib di luar .card: .card punya hover:-translate-y-1 (transform)
     yang membuat position:fixed di dalamnya terjebak di kartu (modal muncul di pojok). --}}
@include('pendaftaran.modal-cetak-formulir')

{{-- MODAL PASSWORD PANITIA --}}
<div id="modalPassword"
    onclick="closePasswordModal()"
    class="fixed inset-0 z-[300] hidden items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm transition-all duration-300">
    <div class="w-full max-w-sm rounded-3xl border border-slate-200 bg-white p-8 shadow-2xl transform transition-all duration-300" onclick="event.stopPropagation()">
        
        <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
            🔒
        </div>
        
        <div class="text-center mb-6">
            <h3 class="text-xl font-bold text-slate-800">Password Panitia</h3>
            <p class="text-sm text-slate-500 mt-1">Sesi ini memerlukan verifikasi keamanan panitia.</p>
        </div>

        <form method="POST" action="{{ route('verifikasi.password.panitia') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="redirect_url" id="redirectUrl">

            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Masukkan Password</label>
                <input type="password" name="password" 
                    class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-semibold" 
                    placeholder="••••••••" required autofocus>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closePasswordModal()" class="flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors">Batal</button>
                <button type="submit" class="flex-1 rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-blue-500/30 hover:bg-blue-700 transition-all">Verifikasi</button>
            </div>
        </form>
    </div>
</div>

<script>
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

    document.addEventListener('alpine:init', () => {
        Alpine.data('successPager', ({ hasWali }) => ({
            currentIndex: 0,
            steps: [],
            init() {
                this.steps = [
                    { key: 'siswa', title: 'Data Siswa' },
                    { key: 'alamat', title: 'Data Alamat' },
                    { key: 'ibu', title: 'Data Ibu' },
                    { key: 'ayah', title: 'Data Ayah' },
                    ...(hasWali ? [{ key: 'wali', title: 'Data Wali' }] : []),
                    { key: 'pendukung', title: 'Data Pendukung' },
                ];
            },
            isCurrent(key) {
                return this.steps[this.currentIndex]?.key === key;
            },
            currentTitle() {
                return this.steps[this.currentIndex]?.title || '';
            },
            goTo(index) {
                if (index < 0 || index >= this.steps.length) {
                    return;
                }
                this.currentIndex = index;
            },
            next() {
                if (this.currentIndex < this.steps.length - 1) {
                    this.currentIndex += 1;
                }
            },
            prev() {
                if (this.currentIndex > 0) {
                    this.currentIndex -= 1;
                }
            }
        }));
    });
</script>
@endsection


