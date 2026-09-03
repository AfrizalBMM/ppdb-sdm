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

    <div class="grid gap-6 xl:grid-cols-5">

        {{-- Form Tambah User --}}
        <div class="card xl:col-span-2">
            <h3 class="text-base font-semibold text-slate-800">Tambah User</h3>
            <p class="mt-1 text-xs text-slate-500">Akun baru dibuat dengan password default sistem.</p>

            <form id="createUserForm" method="POST" action="{{ route('users.store') }}" class="mt-5 grid gap-4">
                @csrf

                <div>
                    <label class="label">Nama</label>
                    <input
                        name="name"
                        value="{{ old('name') }}"
                        class="input"
                        placeholder="Nama user">
                </div>

                <div>
                    <label class="label">Email</label>
                    <input
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        class="input"
                        placeholder="Email user">
                </div>

                <div>
                    <label class="label">Role</label>
                    <select name="role" class="input">
                        <option value="">Pilih Role</option>
                        <option value="admin" @selected(old('role') === 'admin')>
                            Admin
                        </option>
                        <option value="keuangan" @selected(old('role') === 'keuangan')>
                            Keuangan
                        </option>
                    </select>
                </div>

                <div class="flex justify-end pt-1">
                    <button 
                        type="button"
                        onclick="openModal('confirmUserModal')"
                        class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                        Simpan
                    </button>
                </div>
            </form>
        </div>

        {{-- Daftar User --}}
        <div class="card p-0 overflow-hidden xl:col-span-3">
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

{{-- MODAL KONFIRMASI --}}
<x-modal id="confirmUserModal" title="Konfirmasi Simpan">
    <p>Yakin ingin menambahkan user baru?</p>

    <div class="flex justify-end gap-2 mt-4">
        <button 
            type="button"
            onclick="closeModal('confirmUserModal')" 
            class="btn-secondary">
            Batal
        </button>

        <button 
            type="button"
            onclick="document.getElementById('createUserForm').submit()" 
            class="btn-primary">
            Ya, Simpan
        </button>
    </div>
</x-modal>


@endsection
