@extends('layouts.admin')

@section('page-title', 'Password Panitia')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">

    <div class="rounded-2xl border border-slate-200 bg-gradient-to-r from-slate-50 via-white to-blue-50 p-5 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-800">Pengaturan Password Panitia</h2>
                <p class="mt-1 text-sm text-slate-600">Kelola password verifikasi panitia untuk akses pembayaran pada jalur publik.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                    Tahun Ajaran Aktif: {{ $tahunAjaran->nama ?? '-' }}
                </span>
                <span class="inline-flex items-center rounded-full border {{ !empty($password?->password) ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700' }} px-3 py-1 text-xs font-semibold">
                    {{ !empty($password?->password) ? 'Password Tersedia' : 'Password Belum Diatur' }}
                </span>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-5">
        <div class="card lg:col-span-3">
            <h3 class="text-base font-semibold text-slate-800">Setel Password Baru</h3>
            <p class="mt-1 text-xs text-slate-500">Password ini digunakan panitia saat membuka fitur pembayaran pada halaman publik.</p>

            <form method="POST" action="{{ route('admin.password.panitia.store') }}" class="mt-5 space-y-4">
                @csrf

                <div>
                    <label for="password" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Password Panitia</label>

                    <div class="relative">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Masukkan password baru"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 pr-11 text-sm text-slate-700 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            required
                        >

                        <button
                            type="button"
                            id="togglePasswordBtn"
                            onclick="togglePassword()"
                            class="absolute right-2 top-1/2 -translate-y-1/2 rounded-md p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-700"
                            aria-label="Tampilkan/Sembunyikan password"
                        >
                            <svg id="passwordIconShow" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="passwordIconHide" class="hidden h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 012.312-3.568m2.087-1.772A9.956 9.956 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.965 9.965 0 01-4.293 5.125M15 12a3 3 0 00-3-3m0 0a2.99 2.99 0 00-2.121.879M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        Simpan Password
                    </button>
                </div>
            </form>

            @if(session('password_plain'))
                <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Password Panitia Aktif</p>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <span id="plainPasswordValue" class="rounded-lg bg-white px-3 py-1.5 font-mono text-sm font-semibold text-emerald-700 border border-emerald-200">
                            {{ session('password_plain') }}
                        </span>
                        <button
                            type="button"
                            onclick="copyPlainPassword()"
                            class="rounded-lg border border-emerald-300 bg-emerald-100 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-200"
                        >
                            Salin
                        </button>
                    </div>
                </div>
            @endif
        </div>

        <div class="card lg:col-span-2">
            <h3 class="text-base font-semibold text-slate-800">Catatan Keamanan</h3>
            <ul class="mt-3 space-y-2 text-sm text-slate-600">
                <li>Gunakan kombinasi huruf, angka, dan simbol agar password kuat.</li>
                <li>Bagikan hanya ke petugas berwenang.</li>
                <li>Lakukan pembaruan berkala untuk mencegah penyalahgunaan akses.</li>
                <li>Perubahan password langsung berlaku untuk verifikasi berikutnya.</li>
            </ul>
        </div>
    </div>

    <div class="card p-0 overflow-hidden">
        <div class="border-b border-slate-200 px-5 py-4">
            <h3 class="text-base font-semibold text-slate-800">Tabel Flow Akses Password Panitia</h3>
            <p class="mt-1 text-xs text-slate-500">Flow operasional ditampilkan untuk referensi, tanpa perubahan logika proses.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-left w-16">No</th>
                        <th class="px-4 py-3 text-left">Tahap</th>
                        <th class="px-4 py-3 text-left">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white text-slate-700">
                    <tr>
                        <td class="px-4 py-3 font-semibold">1</td>
                        <td class="px-4 py-3 font-medium">Admin set password panitia</td>
                        <td class="px-4 py-3">Password disimpan per tahun ajaran aktif melalui halaman ini.</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 font-semibold">2</td>
                        <td class="px-4 py-3 font-medium">Panitia input password di halaman publik</td>
                        <td class="px-4 py-3">Panitia memasukkan password saat membutuhkan akses pembayaran.</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 font-semibold">3</td>
                        <td class="px-4 py-3 font-medium">Sistem verifikasi</td>
                        <td class="px-4 py-3">Sistem mencocokkan hash password terhadap data tahun ajaran aktif.</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 font-semibold">4</td>
                        <td class="px-4 py-3 font-medium">Akses pembayaran dibuka</td>
                        <td class="px-4 py-3">Jika valid, session akses pembayaran aktif dan pengguna diarahkan ke tujuan.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('password');
    const iconShow = document.getElementById('passwordIconShow');
    const iconHide = document.getElementById('passwordIconHide');

    if (!input || !iconShow || !iconHide) {
        return;
    }

    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    iconShow.classList.toggle('hidden', isPassword);
    iconHide.classList.toggle('hidden', !isPassword);
}

function copyPlainPassword() {
    const el = document.getElementById('plainPasswordValue');
    const text = el ? (el.textContent || '').trim() : '';

    if (!text) {
        return;
    }

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
            if (typeof window.showGlobalToast === 'function') {
                window.showGlobalToast('success', 'Password panitia berhasil disalin.', { title: 'Berhasil Menyalin' });
            }
        });
        return;
    }

    const temp = document.createElement('textarea');
    temp.value = text;
    temp.setAttribute('readonly', '');
    temp.style.position = 'absolute';
    temp.style.left = '-9999px';
    document.body.appendChild(temp);
    temp.select();
    document.execCommand('copy');
    document.body.removeChild(temp);

    if (typeof window.showGlobalToast === 'function') {
        window.showGlobalToast('success', 'Password panitia berhasil disalin.', { title: 'Berhasil Menyalin' });
    }
}
</script>

@endsection