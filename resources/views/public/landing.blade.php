@php
    // Fetch data directly for the landing page
    $settings = \App\Models\Setting::pluck('value', 'key')->toArray();
    $programs = \App\Models\LandingProgram::orderBy('order')->get();
    $facilities = \App\Models\LandingFacility::orderBy('order')->get();
    $testimonials = \App\Models\LandingTestimonial::latest()->get();
    $galleries = \App\Models\LandingGallery::orderBy('order')->get();
    $faqs = \App\Models\LandingFaq::orderBy('order')->get();

    $waNumber = $settings['wa_number'] ?? '628123456789';
    $waLink = "https://wa.me/{$waNumber}";
    $logo = isset($settings['logo']) ? Storage::url($settings['logo']) : asset('images/logo.png');
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings['seo_title'] ?? 'PPDB SD Muhammadiyah Wonorejo' }}</title>
    <meta name="description"
        content="{{ $settings['seo_description'] ?? 'Pendaftaran Peserta Didik Baru SD Muhammadiyah Menerima Peserta Didik Baru.' }}">
    <meta name="keywords" content="PPDB SD, SD Muhammadiyah, Sekolah Islam, Pendaftaran Peserta Didik Baru">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- AlpineJS with Collapse Plugin -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        /* Modern Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Reveal Animation */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease-out;
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        /* Glassmorphism */
        .glass {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .glass-dark {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Gallery lightbox */
        #lightbox {
            display: none;
        }

        #lightbox.active {
            display: flex;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-gray-50 text-slate-800 font-sans antialiased overflow-x-hidden"
    x-data="{ brosurModal: {{ $errors->has('name') || $errors->has('nomor_wa') ? 'true' : 'false' }}, scrolled: false, mobileMenuOpen: false }"
    @scroll.window="scrolled = (window.pageYOffset > 20)">

    {{-- NAVBAR --}}
    <nav :class="scrolled ? 'glass shadow-sm py-3' : 'bg-transparent py-5'"
        class="fixed w-full top-0 z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <img src="{{ $logo }}" alt="Logo" class="h-10 w-10 object-contain">
                <span :class="scrolled ? 'text-slate-800' : 'text-white'"
                    class="font-bold text-xl tracking-tight transition-colors">SD Muhammadiyah</span>
            </div>

            {{-- DESKTOP MENU --}}
            <div class="hidden md:flex items-center space-x-8">
                <a href="#keunggulan"
                    :class="scrolled ? 'text-slate-600 hover:text-primary' : 'text-gray-100 hover:text-white'"
                    class="font-medium text-sm transition-colors">Keunggulan</a>
                <a href="#program"
                    :class="scrolled ? 'text-slate-600 hover:text-primary' : 'text-gray-100 hover:text-white'"
                    class="font-medium text-sm transition-colors">Program</a>
                <a href="#fasilitas"
                    :class="scrolled ? 'text-slate-600 hover:text-primary' : 'text-gray-100 hover:text-white'"
                    class="font-medium text-sm transition-colors">Fasilitas</a>
                <a href="#galeri"
                    :class="scrolled ? 'text-slate-600 hover:text-primary' : 'text-gray-100 hover:text-white'"
                    class="font-medium text-sm transition-colors">Galeri</a>
                <a href="#faq"
                    :class="scrolled ? 'text-slate-600 hover:text-primary' : 'text-gray-100 hover:text-white'"
                    class="font-medium text-sm transition-colors">FAQ</a>
                <a href="{{ route('pendaftaran.public.fresh') }}"
                    class="bg-primary text-white px-5 py-2.5 rounded-full text-sm font-semibold shadow-lg shadow-primary/30 hover:-translate-y-0.5 transition-all">Daftar
                    Sekarang</a>
            </div>

            {{-- MOBILE HAMBURGER MENU BUTTON --}}
            <button @click="mobileMenuOpen = !mobileMenuOpen"
                class="md:hidden flex items-center justify-center p-2 rounded-lg transition-colors"
                :class="scrolled ? 'text-slate-700' : 'text-white'">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                    </path>
                </svg>
            </button>
        </div>

        {{-- MOBILE DROPDOWN MENU --}}
        <div x-show="mobileMenuOpen" x-transition
            class="md:hidden absolute top-full left-0 right-0 mt-0 glass-dark shadow-lg">
            <div class="max-w-7xl mx-auto px-6 py-4 space-y-3">
                <a href="#keunggulan" @click="mobileMenuOpen = false"
                    class="block text-white font-medium text-sm hover:text-primary transition-colors py-2">Keunggulan</a>
                <a href="#program" @click="mobileMenuOpen = false"
                    class="block text-white font-medium text-sm hover:text-primary transition-colors py-2">Program</a>
                <a href="#fasilitas" @click="mobileMenuOpen = false"
                    class="block text-white font-medium text-sm hover:text-primary transition-colors py-2">Fasilitas</a>
                <a href="#galeri" @click="mobileMenuOpen = false"
                    class="block text-white font-medium text-sm hover:text-primary transition-colors py-2">Galeri</a>
                <a href="#faq" @click="mobileMenuOpen = false"
                    class="block text-white font-medium text-sm hover:text-primary transition-colors py-2">FAQ</a>
                <a href="{{ route('pendaftaran.public.fresh') }}" @click="mobileMenuOpen = false"
                    class="block bg-primary text-white px-4 py-2.5 rounded-full text-sm font-semibold text-center hover:-translate-y-0.5 transition-all">Daftar
                    Sekarang</a>
            </div>
        </div>
    </nav>

    {{-- HERO SECTION --}}
    <section class="relative min-h-screen flex items-center justify-center pt-20 overflow-hidden">
        <!-- Background Video/Image -->
        <div class="absolute inset-0 z-0">
            <!-- Background Image -->
            <img src="{{ asset('images/hero.png') }}" class="w-full h-full object-cover" alt="Hero Background">

            <div class="absolute inset-0 bg-slate-900/60 bg-gradient-to-t from-slate-900/90 to-transparent"></div>
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-6 text-center text-white mt-10">
            @if(isset($settings['hero_quota']) && $settings['hero_quota'] > 0)
                <div
                    class="inline-flex items-center gap-2 glass-dark px-4 py-2 rounded-full text-sm font-medium mb-8 animate-bounce">
                    <span class="relative flex h-3 w-3">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                    </span>
                    Tersisa {{ $settings['hero_quota'] }} Kursi lagi.
                </div>
            @endif

            <h1 class="text-4xl md:text-6xl lg:text-7xl font-extrabold tracking-tight mb-6 leading-tight"
                style="text-shadow: 0 4px 20px rgba(0,0,0,0.5);">
                {{ $settings['hero_headline'] ?? 'Membentuk Generasi Qur\'ani & Berkarakter Global.' }}
            </h1>

            <p class="text-lg md:text-xl text-gray-200 mb-10 max-w-3xl mx-auto font-light">
                {{ $settings['hero_subheadline'] ?? 'Selamat datang di gerbang pendaftaran peserta didik baru SD Muhammadiyah. Mari bergabung dan wujudkan masa depan gemilang anak Anda bersama kami.' }}
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('pendaftaran.public.fresh') }}"
                    class="w-full sm:w-auto px-8 py-4 bg-primary text-white text-lg font-semibold rounded-full shadow-[0_0_30px_rgba(37,99,235,0.4)] hover:shadow-[0_0_40px_rgba(37,99,235,0.6)] hover:-translate-y-1 transition-all">
                    Daftarkan Peserta
                </a>
                <button @click="brosurModal = true"
                    class="w-full sm:w-auto px-8 py-4 glass text-slate-900 border border-white text-lg font-semibold rounded-full hover:bg-white transition-all">
                    Dapatkan Brosur Gratis
                </button>
            </div>
        </div>
    </section>

    {{-- VALUE PROPOSITION --}}
    <section id="keunggulan" class="py-24 bg-white relative">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-16 reveal">
                <h2 class="text-sm font-bold tracking-widest text-primary uppercase mb-3">Keunggulan Kami</h2>
                <h3 class="text-3xl md:text-4xl font-bold text-slate-800">Mengapa Memilih Kami?</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                @for($i = 1; $i <= 4; $i++)
                    @if(isset($settings['vp_title_' . $i]) && $settings['vp_title_' . $i] != '')
                        <div
                            class="bg-gray-50 rounded-2xl p-8 hover:-translate-y-2 transition-transform duration-300 reveal shadow-sm hover:shadow-xl border border-gray-100">
                            <div
                                class="w-14 h-14 bg-blue-100 text-primary rounded-xl flex items-center justify-center mb-6 text-2xl font-bold">
                                {{ $i }}
                            </div>
                            <h4 class="text-xl font-bold mb-3 text-slate-800">{{ $settings['vp_title_' . $i] }}</h4>
                            <p class="text-slate-600 leading-relaxed">{{ $settings['vp_desc_' . $i] ?? '' }}</p>
                        </div>
                    @endif
                @endfor
            </div>
        </div>
    </section>

    {{-- PROGRAM UNGGULAN --}}
    @if($programs->count() > 0)
        <section id="program" class="py-24 bg-slate-50 relative overflow-hidden">
            <div
                class="absolute -top-40 -right-40 w-96 h-96 bg-blue-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30">
            </div>
            <div
                class="absolute -bottom-40 -left-40 w-96 h-96 bg-purple-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30">
            </div>

            <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10">
                <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 reveal">
                    <div class="max-w-2xl">
                        <h2 class="text-sm font-bold tracking-widest text-primary uppercase mb-3">Program & Ekskul</h2>
                        <h3 class="text-3xl md:text-4xl font-bold text-slate-800">Membangun Potensi Maksimal</h3>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @foreach($programs as $index => $program)
                        <div class="group bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-300 reveal"
                            style="transition-delay: {{ $index * 100 }}ms">
                            <div class="relative h-56 overflow-hidden">
                                @if($program->image)
                                    <img src="{{ Storage::url($program->image) }}"
                                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                                        alt="{{ $program->title }}">
                                @else
                                    <div
                                        class="w-full h-full bg-slate-200 flex items-center justify-center text-slate-400 group-hover:scale-110 transition-transform duration-700">
                                        No Image</div>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 to-transparent opacity-60">
                                </div>
                                <h4 class="absolute bottom-4 left-6 text-white text-xl font-bold">{{ $program->title }}</h4>
                            </div>
                            <div class="p-6">
                                <p class="text-slate-600 line-clamp-3">{{ $program->description }}</p>
                                <a href="{{ route('public.program.detail', $program) }}"
                                    class="mt-4 inline-flex items-center text-primary font-medium group-hover:gap-2 transition-all">
                                    <span>Pelajari lebih lanjut</span>
                                    <svg class="w-4 h-4 ml-1 transition-transform group-hover:translate-x-1" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                        </path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- FASILITAS --}}
    @if($facilities->count() > 0)
        <section id="fasilitas" class="py-24 bg-white">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="text-center mb-16 reveal">
                    <h2 class="text-sm font-bold tracking-widest text-primary uppercase mb-3">Fasilitas Modern</h2>
                    <h3 class="text-3xl md:text-4xl font-bold text-slate-800">Mendukung Proses Belajar Mengajar</h3>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($facilities as $index => $facility)
                        <div class="relative group rounded-2xl overflow-hidden aspect-square reveal"
                            style="transition-delay: {{ $index * 50 }}ms">
                            @if($facility->image)
                                <img src="{{ Storage::url($facility->image) }}"
                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                    alt="{{ $facility->title }}">
                            @else
                                <div class="w-full h-full bg-slate-200 flex items-center justify-center text-slate-400">No Image
                                </div>
                            @endif
                            <div
                                class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center backdrop-blur-sm">
                                <span
                                    class="text-white font-bold text-lg text-center px-4 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">{{ $facility->title }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ALUR PENDAFTARAN --}}
    <section class="py-24 bg-slate-900 text-white relative">
        <div class="max-w-4xl mx-auto px-6 lg:px-8 reveal">
            <div class="text-center mb-16">
                <h2 class="text-sm font-bold tracking-widest text-blue-400 uppercase mb-3">One day service</h2>
                <h3 class="text-3xl md:text-4xl font-bold">Alur Pendaftaran</h3>
            </div>

            <div
                class="space-y-8 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-slate-600 before:to-transparent">

                <!-- Step 1 -->
                <div
                    class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                    <div
                        class="flex items-center justify-center w-10 h-10 rounded-full border-4 border-slate-900 bg-blue-500 text-white font-bold shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 shadow-[0_0_15px_rgba(59,130,246,0.6)]">
                        1</div>
                    <div
                        class="w-[calc(100%-3rem)] md:w-[calc(50%-2.5rem)] bg-slate-800 p-6 rounded-2xl shadow-lg border border-slate-700 hover:border-blue-500 transition-colors">
                        <h4 class="font-bold text-xl mb-2">Registrasi Online</h4>
                        <p class="text-slate-400 text-sm">Isi biodata anak dan orang tua melalui form pendaftaran online
                            yang telah disediakan.</p>
                    </div>
                </div>

                <!-- Step 2 -->
                <div
                    class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                    <div
                        class="flex items-center justify-center w-10 h-10 rounded-full border-4 border-slate-900 bg-blue-500 text-white font-bold shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 shadow-[0_0_15px_rgba(59,130,246,0.6)]">
                        2</div>
                    <div
                        class="w-[calc(100%-3rem)] md:w-[calc(50%-2.5rem)] bg-slate-800 p-6 rounded-2xl shadow-lg border border-slate-700 hover:border-blue-500 transition-colors">
                        <h4 class="font-bold text-xl mb-2">Observasi Minat Bakat</h4>
                        <p class="text-slate-400 text-sm">Tanpa tes Calistung. Anak akan diajak bermain sambil
                            diobservasi perkembangan dan minatnya.</p>
                    </div>
                </div>

                <!-- Step 3 -->
                <div
                    class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                    <div
                        class="flex items-center justify-center w-10 h-10 rounded-full border-4 border-slate-900 bg-blue-500 text-white font-bold shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 shadow-[0_0_15px_rgba(59,130,246,0.6)]">
                        3</div>
                    <div
                        class="w-[calc(100%-3rem)] md:w-[calc(50%-2.5rem)] bg-slate-800 p-6 rounded-2xl shadow-lg border border-slate-700 hover:border-blue-500 transition-colors">
                        <h4 class="font-bold text-xl mb-2">Wawancara Orang Tua</h4>
                        <p class="text-slate-400 text-sm">Sesi Parent-School Partnership untuk menyelaraskan visi
                            pendidikan anak di rumah dan sekolah.</p>
                    </div>
                </div>

                <!-- Step 4 -->
                <div
                    class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                    <div
                        class="flex items-center justify-center w-10 h-10 rounded-full border-4 border-slate-900 bg-blue-500 text-white font-bold shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 shadow-[0_0_15px_rgba(59,130,246,0.6)]">
                        4</div>
                    <div
                        class="w-[calc(100%-3rem)] md:w-[calc(50%-2.5rem)] bg-slate-800 p-6 rounded-2xl shadow-lg border border-slate-700 hover:border-blue-500 transition-colors">
                        <h4 class="font-bold text-xl mb-2">Daftar Ulang</h4>
                        <p class="text-slate-400 text-sm">Penyelesaian administrasi, pengambilan seragam, dan buku paket
                            anak untuk persiapan mulai sekolah.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- GALERI SEKOLAH --}}
    @if($galleries->count() > 0)
        <section id="galeri" class="py-24 bg-white">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="text-center mb-16 reveal">
                    <h2 class="text-sm font-bold tracking-widest text-primary uppercase mb-3">Dokumentasi Kegiatan</h2>
                    <h3 class="text-3xl md:text-4xl font-bold text-slate-800">Galeri Sekolah</h3>
                    <p class="text-slate-500 mt-3 max-w-xl mx-auto">Sekilas suasana belajar dan kegiatan seru di SD
                        Muhammadiyah.</p>
                </div>

                <div class="columns-2 md:columns-3 lg:columns-4 gap-4 space-y-4">
                    @foreach($galleries as $index => $gallery)
                        <div class="break-inside-avoid group relative overflow-hidden rounded-2xl reveal cursor-zoom-in"
                            style="transition-delay: {{ $index * 60 }}ms"
                            onclick="openLightbox('{{ Storage::url($gallery->image) }}', '{{ addslashes($gallery->title) }}', '{{ addslashes($gallery->caption ?? '') }}')">
                            <img src="{{ Storage::url($gallery->image) }}" alt="{{ $gallery->title }}"
                                class="w-full object-cover group-hover:scale-105 transition-transform duration-500 rounded-2xl">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-slate-900/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl flex flex-col justify-end p-4">
                                <p class="text-white font-bold text-sm leading-tight">{{ $gallery->title }}</p>
                                @if($gallery->caption)
                                    <p class="text-gray-300 text-xs mt-0.5">{{ $gallery->caption }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- TESTIMONIAL --}}
    @if($testimonials->count() > 0)
        <section class="py-24 bg-gray-50 overflow-hidden">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="text-center mb-16 reveal">
                    <h2 class="text-sm font-bold tracking-widest text-primary uppercase mb-3">Apa Kata Mereka?</h2>
                    <h3 class="text-3xl md:text-4xl font-bold text-slate-800">Testimonial Orang Tua</h3>
                </div>

                <!-- CSS Scroll Snap Slider -->
                <div class="flex gap-6 overflow-x-auto pb-8 snap-x snap-mandatory hide-scroll">
                    @foreach($testimonials as $testi)
                        <div
                            class="min-w-[300px] md:min-w-[400px] bg-white p-8 rounded-3xl shadow-sm border border-border snap-center shrink-0">
                            <div class="flex items-center gap-4 mb-6">
                                @if($testi->image_or_video)
                                    <img src="{{ Storage::url($testi->image_or_video) }}"
                                        class="w-14 h-14 rounded-full object-cover">
                                @else
                                    <div
                                        class="w-14 h-14 rounded-full bg-blue-100 flex items-center justify-center text-primary font-bold text-xl uppercase">
                                        {{ substr($testi->name, 0, 1) }}</div>
                                @endif
                                <div>
                                    <h4 class="font-bold text-slate-800 text-lg">{{ $testi->name }}</h4>
                                    <span class="text-sm text-slate-500">{{ $testi->role }}</span>
                                </div>
                            </div>
                            <p class="text-slate-600 italic">"{{ $testi->content }}"</p>
                        </div>
                    @endforeach
                </div>
                <style>
                    .hide-scroll::-webkit-scrollbar {
                        display: none;
                    }

                    .hide-scroll {
                        -ms-overflow-style: none;
                        scrollbar-width: none;
                    }
                </style>
            </div>
        </section>
    @endif

    {{-- FAQ --}}
    @if($faqs->count() > 0)
        <section id="faq" class="py-24 bg-white" x-data="{ openFaq: null }">
            <div class="max-w-3xl mx-auto px-6 lg:px-8">
                <div class="text-center mb-16 reveal">
                    <h2 class="text-sm font-bold tracking-widest text-primary uppercase mb-3">Pertanyaan Umum</h2>
                    <h3 class="text-3xl md:text-4xl font-bold text-slate-800">FAQ</h3>
                    <p class="text-slate-500 mt-3">Temukan jawaban atas pertanyaan yang paling sering ditanyakan.</p>
                </div>

                <div class="space-y-3 reveal">
                    @foreach($faqs as $index => $faq)
                        <div class="border border-gray-200 rounded-2xl overflow-hidden transition-all duration-300"
                            :class="openFaq === {{ $index }} ? 'shadow-md border-blue-200' : 'hover:border-gray-300'">
                            <button @click="openFaq = openFaq === {{ $index }} ? null : {{ $index }}"
                                class="w-full flex items-center justify-between gap-4 p-5 text-left">
                                <span class="font-semibold text-slate-800 text-sm md:text-base">{{ $faq->question }}</span>
                                <span class="shrink-0 w-8 h-8 flex items-center justify-center rounded-full transition-colors"
                                    :class="openFaq === {{ $index }} ? 'bg-primary text-white' : 'bg-gray-100 text-slate-600'">
                                    <svg class="w-4 h-4 transition-transform duration-300"
                                        :class="openFaq === {{ $index }} ? 'rotate-45' : ''" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                </span>
                            </button>
                            <div x-show="openFaq === {{ $index }}" x-collapse x-cloak>
                                <div class="px-5 pb-5">
                                    <p class="text-slate-600 text-sm leading-relaxed">{{ $faq->answer }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif


    <section class="py-24 bg-blue-600 relative overflow-hidden">
        <!-- Abstract Shapes -->
        <div class="absolute inset-0 opacity-10">
            <div
                class="absolute top-0 right-0 w-64 h-64 bg-white rounded-full mix-blend-overlay filter blur-xl transform translate-x-1/2 -translate-y-1/2">
            </div>
            <div
                class="absolute bottom-0 left-0 w-96 h-96 bg-white rounded-full mix-blend-overlay filter blur-2xl transform -translate-x-1/2 translate-y-1/2">
            </div>
        </div>

        <div class="max-w-4xl mx-auto px-6 text-center relative z-10 text-white reveal">
            <h2 class="text-4xl md:text-5xl font-bold mb-6">Amankan Kursi Anak Anda Mengingat Kuota Terbatas.</h2>
            <p class="text-xl text-blue-100 mb-10">Pendaftaran akan segera ditutup. Jadilah bagian dari keluarga besar
                kami.</p>
            <a href="{{ route('pendaftaran.public.fresh') }}"
                class="inline-block px-10 py-5 bg-white text-blue-600 text-lg font-bold rounded-full shadow-xl hover:scale-105 transition-transform">
                Amankan Kursi Sekarang
            </a>
        </div>
    </section>

    {{-- FOOTER --}}
    <footer class="bg-slate-900 text-slate-400 py-12 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 text-center">
            <img src="{{ asset('images/footer.png') }}" class="h-auto w-full max-w-xs mx-auto mb-6" alt="Footer">
            <p>&copy; {{ date('Y') }} SD Muhammadiyah Wonorejo. All rights reserved.</p>
        </div>
    </footer>

    {{-- FLOATING WA BUTTON (FR-03) --}}
    <a href="{{ $waLink }}" target="_blank"
        class="fixed bottom-6 right-6 z-50 bg-[#25D366] text-white p-4 rounded-full shadow-2xl hover:scale-110 transition-transform flex items-center justify-center group">
        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
            <path
                d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm.029 18.88c-1.161 0-2.305-.292-3.318-.844l-3.677.964.984-3.595c-.607-1.052-.927-2.246-.926-3.468.001-3.825 3.113-6.937 6.937-6.937 3.825 0 6.938 3.112 6.938 6.937 0 3.824-3.113 6.938-6.938 6.938z" />
        </svg>
        <span
            class="absolute right-full mr-4 bg-slate-900 text-white text-sm py-1 px-3 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap shadow-lg">Chat
            dengan Admin</span>
    </a>

    {{-- BROSUR MODAL --}}
    <div x-show="brosurModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4" style="display: none;">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="brosurModal = false"
            x-transition.opacity></div>
    
        <!-- Modal Content -->
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-sm p-8 transform transition-all"
            x-transition.scale.origin.center>
            
            <div class="w-20 h-20 bg-blue-50 text-blue-600 rounded-3xl flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
            </div>
    
            <div class="text-center mb-8">
                <h3 class="text-2xl font-bold text-slate-800">Unduh Brosur</h3>
                <p class="text-sm text-slate-500 mt-2">Dapatkan informasi lengkap mengenai program dan fasilitas kami.</p>
            </div>
    
            <form action="{{ route('brosur.download') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Nama Lengkap Orang Tua</label>
                    <input type="text" name="name" value="{{ old('name') }}" required minlength="3"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none text-sm font-semibold"
                        placeholder="Masukkan nama Anda">
                    @error('name')
                        <p class="text-red-500 text-[10px] mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Nomor WhatsApp Aktif</label>
                    <input type="text" name="nomor_wa" value="{{ old('nomor_wa') }}" required pattern="[0-9]{7,14}"
                        title="Nomor WhatsApp harus berupa angka 7-14 digit"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none text-sm font-semibold"
                        placeholder="Contoh: 081234567890">
                    @error('nomor_wa')
                        <p class="text-red-500 text-[10px] mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>
    
                <div class="flex gap-3 pt-4">
                    <button type="button" @click="brosurModal = false"
                        class="flex-1 px-4 py-3 rounded-xl border border-slate-300 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-3 rounded-xl bg-blue-600 text-white text-sm font-bold shadow-lg shadow-blue-500/30 hover:bg-blue-700 transition-all">
                        Unduh
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- GALLERY LIGHTBOX --}}
    <div id="lightbox" class="fixed inset-0 z-[200] bg-black/90 backdrop-blur-sm items-center justify-center p-4"
        onclick="closeLightbox()">
        <button onclick="closeLightbox()"
            class="absolute top-4 right-4 text-white bg-white/10 hover:bg-white/20 rounded-full p-3 transition-colors z-10">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        <div class="max-w-4xl max-h-[90vh] relative" onclick="event.stopPropagation()">
            <img id="lightboxImg" src="" alt=""
                class="max-w-full max-h-[80vh] object-contain rounded-xl shadow-2xl mx-auto block">
            <div class="mt-3 text-center">
                <p id="lightboxTitle" class="text-white font-bold text-lg"></p>
                <p id="lightboxCaption" class="text-gray-400 text-sm mt-1"></p>
            </div>
        </div>
    </div>

    <!-- Script for Scroll Reveal + Lightbox -->
    <script>
        // Lightbox
        function openLightbox(src, title, caption) {
            document.getElementById('lightboxImg').src = src;
            document.getElementById('lightboxTitle').textContent = title;
            document.getElementById('lightboxCaption').textContent = caption;
            document.getElementById('lightbox').classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        function closeLightbox() {
            document.getElementById('lightbox').classList.remove('active');
            document.body.style.overflow = '';
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeLightbox();
        });

        // Scroll Reveal
        document.addEventListener('DOMContentLoaded', () => {
            const reveals = document.querySelectorAll('.reveal');

            const revealOnScroll = () => {
                const windowHeight = window.innerHeight;
                reveals.forEach(reveal => {
                    const revealTop = reveal.getBoundingClientRect().top;
                    const revealPoint = 100;

                    if (revealTop < windowHeight - revealPoint) {
                        reveal.classList.add('active');
                    }
                });
            }

            window.addEventListener('scroll', revealOnScroll);
            revealOnScroll();

            @if(session('error'))
                if (typeof window.showGlobalToast === 'function') {
                    window.showGlobalToast('danger', '{{ session("error") }}');
                }
            @endif
        });
    </script>
</body>

</html>