@extends('layouts.admin')

@section('title', 'Ganti Password')
@section('page-title', 'Ganti Password')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-border p-6">
        <div class="mb-6">
            <h2 class="text-xl font-bold text-textPrimary">Keamanan Akun</h2>
            <p class="text-sm text-textSecondary mt-1">Perbarui password Anda secara berkala untuk menjaga keamanan akun.</p>
        </div>

        <form action="{{ route('admin.profile.password.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div>
                    <label for="current_password" class="block text-sm font-medium text-textPrimary mb-1">Password Saat Ini</label>
                    <input type="password" name="current_password" id="current_password" class="input-form w-full" required autocomplete="current-password">
                    @error('current_password')
                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <hr class="border-border">

                <div>
                    <label for="password" class="block text-sm font-medium text-textPrimary mb-1">Password Baru</label>
                    <input type="password" name="password" id="password" class="input-form w-full" required autocomplete="new-password">
                    @error('password')
                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-textPrimary mb-1">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="input-form w-full" required autocomplete="new-password">
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-border flex justify-end gap-3">
                <button type="submit" class="btn-primary px-8">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
