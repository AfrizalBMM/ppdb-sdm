{{-- HEADER --}}
<div class="px-6 py-5 border-b border-border flex items-center space-x-3">
    <img src="{{ asset('images/logo.png') }}" alt="Logo PPDB" class="h-10 w-10 object-contain drop-shadow-sm">
    <div class="flex flex-col">
        <div class="text-lg font-heading font-bold tracking-wide text-primary">
            PPDB SDM
        </div>
        <div class="text-xs text-textSecondary mt-0.5 font-medium">
            Islami, Mandiri, Berprestasi
        </div>
    </div>
</div>


{{-- MENU --}}
<nav class="px-3 py-4 space-y-1.5 text-sm h-[calc(100vh-140px)] overflow-y-auto w-full">

    {{-- MAIN --}}
    <h3 class="sidebar-section">
        Menu Utama
    </h3>

    <a href="{{ route('dashboard') }}"
       class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        📊 Dashboard
    </a>

    @role('superadmin|admin')
        <a href="{{ route('admin.brochure.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.brochure.*') ? 'active' : '' }}">
            📖 Kelola Brosur
        </a>

        <a href="{{ route('pendaftar.index') }}"
           class="sidebar-link {{ request()->routeIs('pendaftar.*') ? 'active' : '' }}">
            📝 Pendaftar
        </a>

        @php
            $siswaSubmenus = collect();
            if (\Illuminate\Support\Facades\Schema::hasTable('kelas_siswa')) {
                $siswaSubmenus = \App\Models\KelasSiswa::query()
                    ->get(['id', 'nama_kelas'])
                    ->sortBy(function ($item) {
                        return strtolower(trim((string) $item->nama_kelas));
                    }, SORT_NATURAL)
                    ->values();
            }
        @endphp

        <div x-data="{ open: {{ request()->routeIs('siswa.*') ? 'true' : 'false' }} }" class="space-y-1">
            <button
                type="button"
                @click="open = !open"
                class="sidebar-link w-full !flex !items-center !justify-between {{ request()->routeIs('siswa.*') ? 'active' : '' }}"
            >
                <span>🎓 Siswa</span>
                <svg class="h-4 w-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-show="open" x-transition class="ml-4 mt-1 space-y-1 rounded-lg border border-slate-200 bg-slate-50/80 p-2">
                <a href="{{ route('siswa.management-kelas') }}"
                   class="flex items-center gap-2 rounded-md px-2.5 py-1.5 text-xs font-medium transition {{ request()->routeIs('siswa.management-kelas') ? 'bg-blue-100 text-blue-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-800' }}">
                    <span class="text-[11px]">🗂️</span>
                    <span>Management Kelas</span>
                </a>

                <a href="{{ route('siswa.index', ['from_menu' => 1]) }}"
                   class="flex items-center gap-2 rounded-md px-2.5 py-1.5 text-xs font-medium transition {{ request()->routeIs('siswa.index') && !request()->has('kelas_id') ? 'bg-blue-100 text-blue-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-800' }}">
                    <span class="text-[11px]">👥</span>
                    <span>Semua Siswa</span>
                </a>

                <a href="{{ route('siswa.index', ['kelas_id' => 'belum', 'from_menu' => 1]) }}"
                   class="flex items-center gap-2 rounded-md px-2.5 py-1.5 text-xs font-medium transition {{ request()->routeIs('siswa.index') && request('kelas_id') === 'belum' ? 'bg-blue-100 text-blue-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-800' }}">
                    <span class="text-[11px]">🏫</span>
                    <span>Belum Dapat Kelas</span>
                </a>

                <div class="mt-1 border-t border-slate-200 pt-1.5 text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                    Data per Kelas
                </div>

                @foreach($siswaSubmenus as $submenuKelas)
                    @php
                        $rawNamaKelas = trim((string) $submenuKelas->nama_kelas);
                        $labelKelas = \Illuminate\Support\Str::startsWith(strtolower($rawNamaKelas), 'kelas ')
                            ? $rawNamaKelas
                            : 'Kelas ' . $rawNamaKelas;
                    @endphp
                    <a href="{{ route('siswa.index', ['kelas_id' => $submenuKelas->id, 'from_menu' => 1]) }}"
                       class="flex items-center gap-2 rounded-md px-2.5 py-1.5 text-xs font-medium transition {{ request()->routeIs('siswa.index') && (int) request('kelas_id') === (int) $submenuKelas->id ? 'bg-blue-100 text-blue-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-800' }}">
                        <span class="text-[11px]">🏫</span>
                        <span>{{ $labelKelas }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endrole

    @role('superadmin|keuangan')
        <a href="{{ route('keuangan.index') }}"
           class="sidebar-link {{ request()->routeIs('keuangan.*') ? 'active' : '' }}">
            💰 Keuangan
        </a>
    @endrole

    {{-- MASTER --}}
    @role('superadmin')
        <h3 class="sidebar-section mt-6">
            Master Data
        </h3>

        <a href="{{ route('admin.password.panitia') }}"
            class="sidebar-link {{ request()->routeIs('admin.password.panitia') ? 'active' : '' }}">
            🔑 Password Panitia
        </a>

        <a href="{{ route('admin.password.petugas-keuangan') }}"
            class="sidebar-link {{ request()->routeIs('admin.password.petugas-keuangan*') ? 'active' : '' }}">
            💼 Password Petugas Keuangan
        </a>

        <a href="{{ route('tahun-ajaran.index') }}"
           class="sidebar-link {{ request()->routeIs('tahun-ajaran.*') ? 'active' : '' }}">
            📅 Tahun Ajaran
        </a>

        <a href="{{ route('biaya.index') }}"
           class="sidebar-link {{ request()->routeIs('biaya.*') ? 'active' : '' }}">
            💳 Set Biaya
        </a>

        <a href="{{ route('voucher.index') }}"
           class="sidebar-link {{ request()->routeIs('voucher.*') ? 'active' : '' }}">
            🎟 Voucher
        </a>

        <a href="{{ route('paud-tk.index') }}"
           class="sidebar-link {{ request()->routeIs('paud-tk.*') ? 'active' : '' }}">
            🏫 PAUD / TK
        </a>

        <a href="{{ route('users.index') }}"
           class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
            👤 Akun User
        </a>

        <a href="{{ route('log.aktivitas') }}"
           class="sidebar-link {{ request()->routeIs('log.aktivitas') ? 'active' : '' }}">
            🧾 Monitoring Aktivitas
        </a>
        <a href="{{ route('admin.settings.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
            ⚙️ Setting Website Base
        </a>
        <a href="{{ route('landing-programs.index') }}"
           class="sidebar-link {{ request()->routeIs('landing-programs.*') ? 'active' : '' }}">
            🎯 Program Unggulan
        </a>
        <a href="{{ route('landing-facilities.index') }}"
           class="sidebar-link {{ request()->routeIs('landing-facilities.*') ? 'active' : '' }}">
            🏢 Fasilitas Sekolah
        </a>
        <a href="{{ route('landing-testimonials.index') }}"
           class="sidebar-link {{ request()->routeIs('landing-testimonials.*') ? 'active' : '' }}">
            💬 Testimonial
        </a>
        <a href="{{ route('landing-galleries.index') }}"
           class="sidebar-link {{ request()->routeIs('landing-galleries.*') ? 'active' : '' }}">
            🖼️ Galeri Sekolah
        </a>
        <a href="{{ route('landing-faqs.index') }}"
           class="sidebar-link {{ request()->routeIs('landing-faqs.*') ? 'active' : '' }}">
            ❓ FAQ Landing
        </a>
    @endrole

</nav>






