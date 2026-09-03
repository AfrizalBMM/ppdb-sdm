<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login • PPDB SD Muhammadiyah Wonorejo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/css/app.css','resources/js/app.js'])
    <style>
        @keyframes float-soft {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
            100% { transform: translateY(0px); }
        }

        .animate-float-soft {
            animation: float-soft 4s ease-in-out infinite;
        }
    </style>
</head>

<body class="min-h-screen bg-slate-100 text-slate-800">
<div class="min-h-screen p-4 md:p-8">
    <div class="mx-auto max-w-7xl min-h-[calc(100vh-2rem)] md:min-h-[calc(100vh-4rem)] rounded-3xl overflow-hidden border border-slate-200 bg-white shadow-2xl grid lg:grid-cols-2">

        <section class="relative hidden lg:flex flex-col justify-between bg-gradient-to-br from-sky-700 via-blue-700 to-indigo-800 text-white p-10 xl:p-14 overflow-hidden">
            <div class="absolute -top-16 -left-16 w-64 h-64 bg-white/10 rounded-full blur-2xl"></div>
            <div class="absolute -bottom-20 -right-20 w-72 h-72 bg-cyan-300/20 rounded-full blur-2xl"></div>

            <div class="relative z-10">
                <div class="inline-flex items-center gap-3 rounded-xl bg-white/15 border border-white/20 px-4 py-2 backdrop-blur">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo SD Muhammadiyah Wonorejo" class="h-9 w-9 object-contain">
                    <p class="text-sm font-semibold tracking-wide">PPDB SD Muhammadiyah Wonorejo</p>
                </div>

                <h1 class="mt-8 text-4xl font-black leading-tight">
                    Portal Admin Penerimaan Peserta Didik Baru
                </h1>
            </div>

            <div class="relative z-10">
                <div class="rounded-2xl bg-white/10 border border-white/20 p-5 backdrop-blur-sm">
                    <img src="{{ asset('images/hero.png') }}" alt="Ilustrasi portal PPDB" class="w-full max-w-md mx-auto drop-shadow-2xl animate-float-soft">
                    <div class="mt-4 grid grid-cols-2 gap-3 text-xs">
                        <div class="rounded-lg border border-white/20 bg-white/10 p-3">
                            <p class="text-sky-100 uppercase tracking-wide">Status</p>
                            <p class="font-bold mt-1">Sistem Aktif</p>
                        </div>
                        <div class="rounded-lg border border-white/20 bg-white/10 p-3">
                            <p class="text-sky-100 uppercase tracking-wide">Akses</p>
                            <p class="font-bold mt-1">Tervalidasi</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="flex items-center justify-center p-6 sm:p-10 lg:p-14 bg-white">
            <div class="w-full max-w-md">
                <div class="lg:hidden text-center mb-8">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo SD Muhammadiyah Wonorejo" class="h-16 mx-auto mb-3">
                    <h1 class="text-xl font-bold text-slate-800">PPDB SD Muhammadiyah Wonorejo</h1>
                    <p class="text-sm text-slate-500 mt-1">Portal login panitia</p>
                </div>

                <div class="mb-6">
                    <p class="text-xs uppercase tracking-[0.2em] font-bold text-blue-600">Welcome Back</p>
                    <h2 class="mt-2 text-3xl font-extrabold text-slate-900">Masuk ke Dashboard</h2>
                    <p class="mt-2 text-sm text-slate-500">Silakan login menggunakan akun yang telah terdaftar.</p>
                </div>

                @if($errors->any())
                    <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.proses') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label class="label">Email</label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="email@contoh.com"
                            class="input"
                            required>
                        @error('email')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label">Password</label>
                        <div class="relative">
                            <input
                                id="login_password"
                                type="password"
                                name="password"
                                placeholder="••••••••"
                                class="input pr-12"
                                required>

                            <button
                                type="button"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600"
                                aria-label="Tampilkan password"
                                aria-pressed="false"
                                data-password-toggle="login_password">
                                <svg data-password-icon="eye" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.5 12s3.5-7 9.5-7 9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z" />
                                </svg>
                                <svg data-password-icon="eye-off" class="h-5 w-5 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4l16 16" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.2 10.3a2.5 2.5 0 0 0 3.5 3.5" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.8 6.9C4.2 8.6 2.5 12 2.5 12s3.5 7 9.5 7c2.1 0 4-.6 5.5-1.5" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.1 5.3C10 5.1 11 5 12 5c6 0 9.5 7 9.5 7s-1.2 2.4-3.6 4.3" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <button class="w-full rounded-xl bg-blue-700 py-3 text-sm font-bold text-white hover:bg-blue-800 transition shadow-lg shadow-blue-700/20">
                        Login
                    </button>
                </form>

                <div class="mt-7 text-center text-xs text-slate-400">
                    © {{ date('Y') }} SD Muhammadiyah Wonorejo
                </div>

                <div class="mt-3 text-center">
                    <a href="{{ route('public.landing') }}" class="text-xs font-semibold text-blue-700 hover:text-blue-800">
                        Kembali ke halaman utama
                    </a>
                </div>
            </div>
        </section>
    </div>
</div>

</body>
</html>
