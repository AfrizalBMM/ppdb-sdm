<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>@yield('title','Admin PPDB')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css','resources/js/app.js'])

    <!-- AlpineJS with Collapse Plugin -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        body.sidebar-collapsed #sidebar {
            display: none !important;
        }

        body.sidebar-collapsed #adminContent {
            margin-left: 0 !important;
        }
    </style>
</head>

<body class="bg-background text-textPrimary font-sans antialiased">

    <div class="flex min-h-screen">

        {{-- OVERLAY MOBILE --}}
        <div id="sidebarOverlay"
            onclick="toggleSidebar()"
            class="fixed inset-0 bg-black/40 z-30 hidden md:hidden">
        </div>

        {{-- SIDEBAR --}}
        <aside
            id="sidebar"
            class="fixed inset-y-0 left-0 z-40 w-64 bg-white text-textPrimary border-r border-border shadow-sm
               transform -translate-x-full md:translate-x-0
               transition-transform duration-200">

            @include('layouts.partials.sidebar')
        </aside>

        {{-- CONTENT --}}
        <div id="adminContent" class="flex-1 flex flex-col md:ml-64 transition-[margin] duration-200">

            {{-- HEADER --}}
            <header class="bg-white shadow-sm border-b border-border px-6 py-4 flex items-center justify-between sticky top-0 z-50">
                <div class="flex items-center gap-3 min-w-0">
                    <button
                        type="button"
                        id="sidebarCollapseBtn"
                        onclick="toggleSidebarCollapse()"
                        class="hidden md:inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white p-2 text-slate-700 hover:bg-slate-100 transition-colors"
                        aria-label="Minimize sidebar"
                        title="Minimize sidebar">
                        <svg id="sidebarCollapseBtnIcon" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <!-- Tombol sidebar mobile -->
                    <button onclick="toggleSidebar()" class="md:hidden text-xl text-textSecondary hover:text-primary transition-colors">☰</button>
                    <!-- Judul halaman dihapus sesuai permintaan -->
                </div>

                <!-- Waktu & user -->
                <div class="flex items-center space-x-3 text-sm text-slate-600">
                    <!-- Label + tanggal & waktu live dengan badge -->
                    <span class="hidden lg:flex items-center space-x-1">
                        <span id="live-clock" class="bg-slate-100 text-slate-600 px-3 py-1 rounded-full font-medium border border-slate-200"></span>
                    </span>

                    <!-- Nama user Dropdown -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @click.away="open = false" class="flex items-center space-x-2 hover:bg-slate-50 px-3 py-2 rounded-lg transition-colors group">
                            <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <div class="flex flex-col items-start">
                                <span class="font-bold text-textPrimary leading-none">{{ auth()->user()->name }}</span>
                                <span class="text-[10px] uppercase tracking-wider text-textSecondary mt-1">{{ auth()->user()->role }}</span>
                            </div>
                            <svg class="w-4 h-4 text-textSecondary transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Dropdown menu -->
                        <div x-show="open"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-border py-2 z-50"
                            x-cloak>

                            <div class="px-4 py-2 border-b border-border mb-1">
                                <p class="text-[10px] uppercase font-bold text-textSecondary tracking-widest">Akun Saya</p>
                            </div>

                            <a href="{{ route('admin.profile.password.edit') }}" class="flex items-center space-x-2 px-4 py-2 text-sm text-textPrimary hover:bg-slate-50 transition-colors">
                                <svg class="w-4 h-4 text-textSecondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                </svg>
                                <span>Ganti Password</span>
                            </a>

                            <button @click="open = false; openModal('logoutModal')" class="w-full flex items-center space-x-2 px-4 py-2 text-sm text-danger hover:bg-red-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                <span>Logout</span>
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <main class="p-4 md:p-6 flex-1 relative">

                {{-- ALERTS --}}
                <div class="absolute top-0 left-0 w-full px-4 md:px-0">
                    {{-- Validation Errors --}}
                    @if($errors->any())
                    <div class="alert-error bg-red-100 text-red-800 px-4 py-2 rounded mb-4 shadow-md max-w-2xl mx-auto">
                        <ul class="list-disc pl-5">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>

                {{-- KONTEN HALAMAN --}}
                @yield('content')
            </main>

            <!-- Tambahkan ini tepat sebelum </body> -->
            <footer class="bg-white border-t border-slate-200 text-center text-sm text-slate-500 py-3 mt-auto">
                Sistem Pendaftaran Peserta Didik Baru - SD Muhammadiyah Wonorejo
            </footer>

        </div>
    </div>

    <div id="globalToast"
        class="fixed bottom-4 right-4 z-toast hidden min-w-[260px] max-w-sm overflow-hidden rounded-xl border px-4 py-3 shadow-2xl transition-all duration-200 translate-y-3 opacity-0"
        role="status"
        aria-live="polite">
        <div class="flex items-start gap-3">
            <div id="globalToastIcon" class="mt-0.5"></div>
            <div class="flex-1">
                <p id="globalToastTitle" class="text-sm font-semibold"></p>
                <p id="globalToastMessage" class="mt-0.5 text-xs"></p>
            </div>
            <button type="button" id="globalToastClose" class="rounded-md p-1 text-current/70 hover:bg-black/5 hover:text-current" aria-label="Tutup notifikasi">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <div id="globalConfirmModal" class="fixed inset-0 z-[300] hidden items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm transition-all duration-300">
        <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-8 shadow-2xl transform transition-all">
            <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
                ❓
            </div>
            <div class="text-center mb-6">
                <h3 id="globalConfirmTitle" class="text-xl font-bold text-slate-800">Konfirmasi</h3>
                <p id="globalConfirmMessage" class="mt-2 text-sm text-slate-500 leading-relaxed">Apakah Anda yakin?</p>
            </div>

            <div class="flex justify-center gap-3">
                <button type="button" id="globalConfirmCancel" class="flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors">Batal</button>
                <button type="button" id="globalConfirmOk" class="flex-1 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-500/30 hover:bg-emerald-700 transition-all">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>

    {{-- MODAL LOGOUT --}}
    <div id="logoutModal" class="fixed inset-0 bg-slate-900/60 hidden items-center justify-center z-[300] p-4 backdrop-blur-sm transition-all duration-300">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm p-8 relative transform transition-all duration-300">
            <div class="w-16 h-16 bg-red-50 text-red-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
            </div>

            <div class="text-center mb-8">
                <h3 class="text-xl font-bold text-slate-800">Konfirmasi Logout</h3>
                <p class="text-sm text-slate-500 mt-2 leading-relaxed">Apakah Anda yakin ingin keluar dari sistem? Sesi Anda akan berakhir.</p>
            </div>

            <form id="logoutForm" method="POST" action="{{ route('logout') }}">
                @csrf
                <div class="flex gap-3">
                    <button type="button" onclick="closeModal('logoutModal')" class="flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 rounded-xl bg-red-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-red-500/30 hover:bg-red-700 transition-all">
                        Ya, Logout
                    </button>
                </div>
            </form>
        </div>
    </div>




    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        function openDeleteModal(action) {
            const modal = document.getElementById('deleteModal');
            const form = document.getElementById('deleteForm');
            if (!modal || !form) return;
            form.action = action;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function openConfirmModal() {
            const el = document.getElementById('confirmModal');
            if (!el) return;
            el.classList.remove('hidden');
            el.classList.add('flex');
        }

        function closeConfirmModal() {
            const el = document.getElementById('confirmModal');
            if (!el) return;
            el.classList.add('hidden');
            el.classList.remove('flex');
        }

        function updateSidebarCollapseButton(isCollapsed) {
            const btn = document.getElementById('sidebarCollapseBtn');
            const icon = document.getElementById('sidebarCollapseBtnIcon');

            if (!btn || !icon) {
                return;
            }

            if (isCollapsed) {
                btn.setAttribute('aria-label', 'Expand sidebar');
                btn.setAttribute('title', 'Expand sidebar');
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />';
            } else {
                btn.setAttribute('aria-label', 'Minimize sidebar');
                btn.setAttribute('title', 'Minimize sidebar');
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />';
            }
        }

        function setSidebarCollapsed(collapsed) {
            document.body.classList.toggle('sidebar-collapsed', collapsed);
            localStorage.setItem('adminSidebarCollapsed', collapsed ? '1' : '0');
            updateSidebarCollapseButton(collapsed);
        }

        function toggleSidebarCollapse() {
            setSidebarCollapsed(!document.body.classList.contains('sidebar-collapsed'));
        }

        document.querySelectorAll('input[name="nominal"]').forEach(el => {
            el.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '');
            });
        });

        // openModal/closeModal global didefinisikan SATU KALI di resources/js/app.js.
        // Jangan definisikan ulang di sini (pernah menyebabkan modal tampil di pojok).

        document.addEventListener("DOMContentLoaded", function() {

            const savedSidebarState = localStorage.getItem('adminSidebarCollapsed');
            setSidebarCollapsed(savedSidebarState === '1');

            // ===== ALERT SUKSES OTOMATIS HILANG =====
            document.querySelectorAll('.alert-success').forEach(alert => {
                setTimeout(() => alert.remove(), 4000);
            });

            // ===== ALERT ERROR OTOMATIS HILANG =====
            document.querySelectorAll('.alert-error').forEach(alert => {
                setTimeout(() => alert.remove(), 4000);
            });

            // ===== LOADING BUTTON OTOMATIS UNTUK SEMUA FORM =====
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function() {
                    if (form.dataset.disableAutoLoading === 'true') {
                        return;
                    }

                    const btn = form.querySelector('button[type="submit"]');
                    if (btn) {
                        btn.disabled = true;
                        btn.textContent = 'Loading...';
                        btn.classList.add('opacity-50', 'cursor-not-allowed');
                    }
                });
            });

        });

        (() => {
            const box = document.getElementById('globalToast');
            const icon = document.getElementById('globalToastIcon');
            const title = document.getElementById('globalToastTitle');
            const message = document.getElementById('globalToastMessage');
            const close = document.getElementById('globalToastClose');

            if (!box || !icon || !title || !message || !close) {
                return;
            }

            let timer = null;

            const themes = {
                success: {
                    title: 'Sukses',
                    className: 'border-emerald-200 bg-emerald-50 text-emerald-800',
                    icon: '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0" /></svg>'
                },
                warning: {
                    title: 'Peringatan',
                    className: 'border-amber-200 bg-amber-50 text-amber-800',
                    icon: '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86l-7.4 12.81A1 1 0 003.76 18h16.48a1 1 0 00.87-1.33l-7.4-12.81a1 1 0 00-1.74 0z" /></svg>'
                },
                info: {
                    title: 'Info',
                    className: 'border-sky-200 bg-sky-50 text-sky-800',
                    icon: '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
                },
                danger: {
                    title: 'Gagal',
                    className: 'border-red-200 bg-red-50 text-red-800',
                    icon: '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
                }
            };

            const hideToast = () => {
                box.classList.add('opacity-0', 'translate-y-3');
                setTimeout(() => {
                    box.classList.add('hidden');
                }, 180);
            };

            close.addEventListener('click', hideToast);

            const showToast = (type = 'info', text = '', options = {}) => {
                const theme = themes[type] || themes.info;

                box.className = 'fixed bottom-4 right-4 z-toast min-w-[260px] max-w-sm overflow-hidden rounded-xl border px-4 py-3 shadow-2xl transition-all duration-200 ' + theme.className;
                icon.innerHTML = theme.icon;
                title.textContent = options.title || theme.title;
                message.textContent = text || '';
                box.classList.remove('hidden');
                requestAnimationFrame(() => {
                    box.classList.remove('opacity-0', 'translate-y-3');
                });

                clearTimeout(timer);
                const duration = Number(options.duration || 2800);
                if (duration > 0) {
                    timer = setTimeout(hideToast, duration);
                }
            };

            window.showGlobalToast = showToast;
            window.showGlobalNotice = showToast;
        })();

        (() => {
            const modal = document.getElementById('globalConfirmModal');
            const title = document.getElementById('globalConfirmTitle');
            const message = document.getElementById('globalConfirmMessage');
            const btnCancel = document.getElementById('globalConfirmCancel');
            const btnOk = document.getElementById('globalConfirmOk');

            if (!modal || !title || !message || !btnCancel || !btnOk) {
                return;
            }

            let resolver = null;

            const close = (result) => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                if (resolver) {
                    resolver(result);
                    resolver = null;
                }
            };

            btnCancel.addEventListener('click', () => close(false));
            btnOk.addEventListener('click', () => close(true));
            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    close(false);
                }
            });

            window.showGlobalConfirm = (text = 'Apakah Anda yakin?', options = {}) => {
                title.textContent = options.title || 'Konfirmasi';
                message.textContent = text;
                btnOk.textContent = options.okText || 'Ya, Lanjutkan';
                btnCancel.textContent = options.cancelText || 'Batal';
                modal.classList.remove('hidden');
                modal.classList.add('flex');

                return new Promise((resolve) => {
                    resolver = resolve;
                });
            };

            window.globalConfirmSubmit = (form, text, options = {}) => {
                window.showGlobalConfirm(text, options).then((ok) => {
                    if (ok) {
                        form.submit();
                    }
                });
                return false;
            };
        })();

        document.addEventListener('DOMContentLoaded', function() {
            const successMessage = @json(session('success'));
            const warningMessage = @json(session('warning'));
            const errorMessage = @json(session('error'));

            if (successMessage && typeof window.showGlobalToast === 'function') {
                window.showGlobalToast('success', successMessage, {
                    title: 'Berhasil',
                    duration: 3200,
                });
            }

            if (warningMessage && typeof window.showGlobalToast === 'function') {
                window.showGlobalToast('warning', warningMessage, {
                    title: 'Peringatan',
                    duration: 3600,
                });
            }

            if (errorMessage && typeof window.showGlobalToast === 'function') {
                window.showGlobalToast('danger', errorMessage, {
                    title: 'Gagal',
                    duration: 3800,
                });
            }
        });

        function updateClock() {
            const clock = document.getElementById('live-clock');
            const now = new Date();

            // Array nama bulan
            const months = [
                "Januari", "Februari", "Maret", "April", "Mei", "Juni",
                "Juli", "Agustus", "September", "Oktober", "November", "Desember"
            ];

            // Array nama hari
            const days = [
                "Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"
            ];

            const dayName = days[now.getDay()]; // nama hari
            const day = now.getDate();
            const monthName = months[now.getMonth()];
            const year = now.getFullYear();

            // Format waktu
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');

            clock.textContent = `${dayName}, ${day} ${monthName} ${year} ${hours}:${minutes}:${seconds}`;
        }

        setInterval(updateClock, 1000); // update tiap detik
        updateClock(); // langsung tampil saat page load
    </script>

</body>

</html>