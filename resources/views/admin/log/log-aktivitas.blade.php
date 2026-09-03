@extends('layouts.admin')

@section('title', 'Log Aktivitas')
@section('page-title', 'Log Aktivitas')

@section('content')
<div x-data="{ 
    showDeleteAll: false,
    search: '{{ request('search') }}',
    role: '{{ request('role') }}',
    kategori: '{{ request('kategori') }}',
    date: '{{ request('tanggal') }}'
}" class="mx-auto max-w-7xl space-y-6">

    <div class="rounded-2xl border border-slate-200 bg-gradient-to-r from-slate-50 via-white to-cyan-50 p-5 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-800">Log Aktivitas Sistem</h2>
                <p class="mt-1 text-sm text-slate-600">Pantau jejak aktivitas user internal dan public secara terpusat.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700">Total: {{ number_format($stats['total'] ?? 0, 0, ',', '.') }}</span>
                <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">Hari Ini: {{ number_format($stats['today'] ?? 0, 0, ',', '.') }}</span>
                <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">Public: {{ number_format($stats['public'] ?? 0, 0, ',', '.') }}</span>
                <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">Internal: {{ number_format($stats['staff'] ?? 0, 0, ',', '.') }}</span>
            </div>
        </div>

        @if(auth()->user()->role === 'superadmin')
        <div class="mt-4 flex justify-end">
            <button @click="showDeleteAll = true" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100">
                Bersihkan Log
            </button>
        </div>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">Total Log</p>
            <p class="mt-1 text-2xl font-extrabold text-slate-800">{{ number_format($stats['total'] ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">Log Hari Ini</p>
            <p class="mt-1 text-2xl font-extrabold text-blue-700">{{ number_format($stats['today'] ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">Aktivitas Public</p>
            <p class="mt-1 text-2xl font-extrabold text-slate-700">{{ number_format($stats['public'] ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">Aktivitas Internal</p>
            <p class="mt-1 text-2xl font-extrabold text-amber-700">{{ number_format($stats['staff'] ?? 0, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card">
        <form action="{{ route('log.aktivitas') }}" method="GET" class="grid gap-4 md:grid-cols-12 md:items-end">
            <div class="space-y-1.5 md:col-span-4">
                <label class="label">Pencarian</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" name="search" x-model="search" placeholder="Cari user, aksi, atau keterangan..." class="input pl-10" value="{{ request('search') }}">
                </div>
            </div>

            <div class="space-y-1.5 md:col-span-2">
                <label class="label">Role</label>
                <select name="role" x-model="role" class="input">
                    <option value="">Semua Role</option>
                    <option value="superadmin">Superadmin</option>
                    <option value="admin">Admin</option>
                    <option value="keuangan">Keuangan</option>
                    <option value="public">Public</option>
                </select>
            </div>

            <div class="space-y-1.5 md:col-span-3">
                <label class="label">Kategori Aksi</label>
                <select name="kategori" x-model="kategori" class="input">
                    <option value="">Semua Kategori</option>
                    <option value="pendaftaran">Pendaftaran</option>
                    <option value="pembayaran">Pembayaran</option>
                    <option value="verifikasi">Verifikasi Password</option>
                    <option value="manajemen-log">Manajemen Log</option>
                    <option value="lainnya">Lainnya</option>
                </select>
            </div>

            <div class="space-y-1.5 md:col-span-2">
                <label class="label">Tanggal</label>
                <input type="date" name="tanggal" x-model="date" class="input" value="{{ request('tanggal') }}">
            </div>

            <div class="flex gap-2 md:col-span-1 md:justify-end">
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">Filter</button>
                <a href="{{ route('log.aktivitas') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50" title="Reset">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden !p-0">
        <div class="border-b border-slate-200 px-5 py-4">
            <h3 class="text-base font-semibold text-slate-800">Daftar Log Aktivitas</h3>
            <p class="mt-1 text-xs text-slate-500">Riwayat aktivitas user berdasarkan waktu, kategori aksi, dan keterangan.</p>
        </div>

        <div class="overflow-x-auto w-full">
            <table class="w-full text-sm">
                <thead class="bg-slate-100 text-left text-sm font-semibold text-slate-700">
                    <tr>
                        <th class="w-32 px-4 py-3">Waktu</th>
                        <th class="w-48 px-4 py-3">Pengguna</th>
                        <th class="w-52 px-4 py-3">Aksi</th>
                        <th class="px-4 py-3">Keterangan</th>
                        <th class="w-36 px-4 py-3 text-right">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3">
                            <div class="whitespace-nowrap text-[13px] text-slate-600">
                                {{ $log->created_at->timezone('Asia/Jakarta')->format('d M Y') }}
                                <div class="text-[11px] opacity-60">{{ $log->created_at->timezone('Asia/Jakarta')->format('H:i:s') }}</div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-bold uppercase text-blue-700">
                                    {{ substr(optional($log->user)->name ?? 'P', 0, 1) }}
                                </div>
                                <span class="truncate font-medium text-slate-800">{{ optional($log->user)->name ?? 'Public / Guest' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $aksi = (string) $log->aksi;
                                $aksiLower = strtolower($aksi);

                                $kategoriLabel = 'Lainnya';
                                $kategoriClass = 'bg-slate-100 text-slate-700 border-slate-200';

                                if (str_contains($aksiLower, 'pendaftaran') || str_contains($aksiLower, 'form edit') || str_contains($aksiLower, 'nik')) {
                                    $kategoriLabel = 'Pendaftaran';
                                    $kategoriClass = 'bg-cyan-100 text-cyan-700 border-cyan-200';
                                } elseif (str_contains($aksiLower, 'pembayaran') || str_contains($aksiLower, 'cicilan') || str_contains($aksiLower, 'nota') || str_contains($aksiLower, 'pembiayaan')) {
                                    $kategoriLabel = 'Pembayaran';
                                    $kategoriClass = 'bg-emerald-100 text-emerald-700 border-emerald-200';
                                } elseif (str_contains($aksiLower, 'verifikasi password')) {
                                    $kategoriLabel = 'Verifikasi';
                                    $kategoriClass = 'bg-violet-100 text-violet-700 border-violet-200';
                                } elseif (str_contains($aksiLower, 'kelola log') || str_contains($aksiLower, 'monitoring aktivitas')) {
                                    $kategoriLabel = 'Manajemen Log';
                                    $kategoriClass = 'bg-amber-100 text-amber-700 border-amber-200';
                                }
                            @endphp

                            <div class="space-y-1">
                                <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-md border {{ $kategoriClass }}">
                                    {{ $kategoriLabel }}
                                </span>
                                <div class="font-bold leading-snug text-blue-700">{{ $aksi }}</div>
                            </div>
                        </td>
                        <td class="max-w-xs px-4 py-3 md:max-w-md lg:max-w-lg">
                            @php
                                $keterangan = trim((string) $log->keterangan);
                                $keterangan = $keterangan !== '' ? $keterangan : '-';
                                $keteranganRows = preg_split('/\s*\|\s*/', $keterangan) ?: [];
                            @endphp

                            @if(count($keteranganRows) > 1)
                                <ul class="space-y-1 text-xs leading-relaxed text-slate-600">
                                    @foreach($keteranganRows as $row)
                                        @if(trim($row) !== '')
                                            <li>• {{ trim($row) }}</li>
                                        @endif
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-sm leading-relaxed text-slate-600" title="{{ $keterangan }}">
                                    {{ $keterangan }}
                                </p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-xs font-mono tracking-tighter text-slate-500">
                            {{ $log->ip_address }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center justify-center space-y-3 opacity-40">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="text-sm font-medium">Belum ada jejak aktivitas yang tercatat</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="border-t border-slate-200 px-4 py-3">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <form method="GET" class="flex items-center gap-2 text-xs text-slate-600">
                    @foreach(request()->except(['page', 'per_page']) as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <label for="perPageLogs">Tampilkan</label>
                    <select id="perPageLogs" name="per_page" onchange="this.form.submit()" class="rounded border border-slate-300 px-2 py-1 text-xs">
                        @foreach([10,20,50,100] as $size)
                            <option value="{{ $size }}" {{ (int) request('per_page', $perPage ?? 10) === $size ? 'selected' : '' }}>{{ $size }}</option>
                        @endforeach
                    </select>
                    <span>data</span>
                </form>
                <div>
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Delete All --}}
    <div x-show="showDeleteAll" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showDeleteAll" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-black/50" @click="showDeleteAll = false"></div>

            <div x-show="showDeleteAll" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block w-full max-w-md p-8 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl">
                
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-6">
                        <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-textPrimary mb-2">Konfirmasi Hapus Semua Log</h3>
                    <p class="text-textSecondary text-sm mb-8">Apakah Anda yakin ingin menghapus seluruh jejak aktivitas sistem? Tindakan ini bersifat permanen dan tidak dapat dibatalkan.</p>
                </div>

                <div class="flex justify-center gap-3">
                    <button type="button" @click="showDeleteAll = false" class="btn-secondary">Batalkan</button>
                    <form method="POST" action="{{ route('logs.destroyAll') }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-danger">Hapus Permanen</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
