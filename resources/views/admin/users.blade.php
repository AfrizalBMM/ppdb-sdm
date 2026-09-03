@extends('layouts.admin')

@section('page-title','Manajemen Akun')

@section('content')
<div class="max-w-4xl">

    {{-- CARD FORM --}}
    <x-card>

        <h2 class="font-semibold text-lg text-slate-800 mb-4">
            Registrasi Akun
        </h2>

        <form method="POST" class="grid md:grid-cols-2 gap-4">
            @csrf

            <div>
                <x-label>Nama</x-label>
                <x-input name="name" value="{{ old('name') }}" placeholder="Nama Lengkap"/>
            </div>

            <div>
                <x-label>Email</x-label>
                <x-input name="email" type="email" value="{{ old('email') }}" placeholder="Email"/>
            </div>

            <div>
                <x-label>Role</x-label>
                <x-select name="role">
                    <option value="">Pilih Role</option>
                    <option value="superadmin" @selected(old('role')=='superadmin')>Superadmin</option>
                    <option value="admin" @selected(old('role')=='admin')>Admin</option>
                    <option value="keuangan" @selected(old('role')=='keuangan')>Keuangan</option>
                </x-select>
            </div>

            <div>
                <x-label>Password</x-label>
                <x-input name="password" type="password" placeholder="Password"/>
            </div>

            <div class="md:col-span-2 flex justify-end">
                <button type="button" onclick="openConfirmModal()" class="btn-primary">
                    Simpan
                </button>
            </div>

            @if(session('success'))
            <div class="alert-success bg-green-100 text-green-800 px-4 py-2 rounded mb-4">
                {{ session('success') }}
            </div>
            @endif

        </form>

    </x-card>


    {{-- TABLE USERS --}}
    <x-card class="mt-6">

        <h3 class="font-semibold mb-4">Daftar Akun</h3>

        <div class="overflow-x-auto">
            <table class="table-base">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($users as $u)
                    <tr>
                        <td class="font-medium">{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td>
                            <x-badge :type="$u->role">
                                {{ ui_label($u->role) }}
                            </x-badge>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center py-6 text-slate-500">
                            Belum ada akun terdaftar
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </x-card>

</div>

{{-- MODAL CONFIRM --}}
<x-modal id="confirmModal" title="Konfirmasi">
    <p>Anda yakin menambahkan akun baru?</p>

    <div class="flex justify-end gap-2 mt-4">
        <button onclick="closeConfirmModal()" class="btn-secondary">Batal</button>
        <button onclick="document.querySelector('form').submit()" class="btn-primary">Ya, Simpan</button>
    </div>
</x-modal>

@endsection
