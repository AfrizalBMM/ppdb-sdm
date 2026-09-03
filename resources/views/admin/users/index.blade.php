@extends('layouts.admin')

@section('page-title','Manajemen User')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    @php
        $totalUsers = $users->total();
        $adminCount = $users->getCollection()->where('role', 'admin')->count();
        $keuanganCount = $users->getCollection()->where('role', 'keuangan')->count();
    @endphp

    <div class="rounded-2xl border border-slate-200 bg-gradient-to-r from-slate-50 via-white to-indigo-50 p-5 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-800">Manajemen User</h2>
                <p class="mt-1 text-sm text-slate-600">Kelola akun admin panel untuk operasional PPDB.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700">Total: {{ $totalUsers }}</span>
                <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">Admin: {{ $adminCount }}</span>
                <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Keuangan: {{ $keuanganCount }}</span>
            </div>
        </div>
    </div>

    <div class="grid gap-6">

        {{-- TOMBOL TAMBAH (form tambah dipindah ke modal) --}}
        <div class="card">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="text-base font-semibold text-slate-800">Data User</h3>
                    <p class="mt-1 text-xs text-slate-500">Akun baru dibuat dengan password default sistem.</p>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row md:flex-row md:items-center">
                    <button onclick="openModal('modalTambahUser')"
                        class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                        + Tambah User
                    </button>
                </div>
            </div>
        </div>

        {{-- Daftar User --}}
        <div class="card p-0 overflow-hidden">
            <div class="border-b border-slate-200 px-5 py-4">
                <h3 class="text-base font-semibold text-slate-800">Daftar User</h3>
                <p class="mt-1 text-xs text-slate-500">Daftar akun internal yang dapat mengakses panel admin.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr class="border-b">
                            <th class="px-4 py-3 text-left w-16">No</th>
                            <th class="px-4 py-3 text-left">Nama</th>
                            <th class="px-4 py-3 text-left">Email</th>
                            <th class="px-4 py-3 text-left">Role</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($users as $u)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3 text-slate-500">{{ $users->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3 font-medium text-slate-800">
                                {{ $u->name }}
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                {{ $u->email }}
                            </td>
                            <td class="px-4 py-3">
                                @if($u->role === 'admin')
                                    <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700">Admin</span>
                                @else
                                    <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">Keuangan</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500">
                                Belum ada user
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-200 px-4 py-3 md:flex-row md:items-center md:justify-between">
                <form method="GET" class="flex items-center gap-2 text-xs text-slate-600">
                    <label for="perPageUsers">Tampilkan</label>
                    <select id="perPageUsers" name="per_page" onchange="this.form.submit()" class="rounded border border-slate-300 px-2 py-1 text-xs">
                        @foreach([10,20,50,100] as $size)
                            <option value="{{ $size }}" {{ (int) request('per_page', $perPage ?? 20) === $size ? 'selected' : '' }}>{{ $size }}</option>
                        @endforeach
                    </select>
                    <span>data</span>
                </form>

                <div>
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>


</div>

{{-- MODAL TAMBAH USER --}}
<div id="modalTambahUser" class="fixed inset-0 z-[300] hidden items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm transition-all duration-300">
    <div class="w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl transform transition-all duration-300 sm:p-8">
        <div class="mb-6 flex items-start justify-between gap-3">
            <div>
                <h3 class="text-xl font-bold text-slate-800">Tambah User</h3>
                <p class="mt-1 text-xs text-slate-500">Akun baru dibuat dengan password default sistem.</p>
            </div>
            <button type="button" onclick="closeModal('modalTambahUser')"
                class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="createUserForm" method="POST" action="{{ route('users.store') }}" class="grid gap-4">
            @csrf

            <div>
                <label class="label">Nama <span class="text-red-500">*</span></label>
                <input
                    name="name"
                    value="{{ old('name') }}"
                    class="input"
                    placeholder="Nama user"
                    required>
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label">Email <span class="text-red-500">*</span></label>
                <input
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    class="input"
                    placeholder="Email user"
                    required>
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label">Role <span class="text-red-500">*</span></label>
                <select name="role" class="input" required>
                    <option value="">Pilih Role</option>
                    <option value="admin" @selected(old('role') === 'admin')>
                        Admin
                    </option>
                    <option value="keuangan" @selected(old('role') === 'keuangan')>
                        Keuangan
                    </option>
                </select>
                @error('role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-200 pt-4">
                <button type="button" onclick="closeModal('modalTambahUser')"
                    class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                    Batal
                </button>
                <button type="submit"
                    class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-500/30 hover:bg-blue-700 transition-all">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
