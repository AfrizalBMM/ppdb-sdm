@extends('layouts.admin')

@section('page-title','Master Biaya PPDB')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    @php
        $totalBiaya = $biaya->total();
        $aktifCount = $biaya->getCollection()->where('aktif', true)->count();
        $nonAktifCount = $biaya->getCollection()->where('aktif', false)->count();
        $acuanCount = $biaya->getCollection()->where('is_acuan_status_ppdb', true)->count();
    @endphp

    <div class="rounded-2xl border border-slate-200 bg-gradient-to-r from-slate-50 via-white to-blue-50 p-5 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-800">Master Biaya PPDB</h2>
                <p class="mt-1 text-sm text-slate-600">Kelola komponen biaya untuk tahun ajaran aktif <span class="font-semibold text-slate-700">{{ $tahunAktif->nama }}</span>.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700">Total: {{ $totalBiaya }}</span>
                <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Aktif: {{ $aktifCount }}</span>
                <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">Nonaktif: {{ $nonAktifCount }}</span>
                <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">Acuan: {{ $acuanCount }}</span>
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-5">
        <div class="card xl:col-span-2">
            <h3 class="text-base font-semibold text-slate-800">Tambah Biaya Baru</h3>
            <p class="mt-1 text-xs text-slate-500">Form ini otomatis tersimpan ke tahun ajaran aktif.</p>

            <form method="POST" action="{{ route('biaya.store') }}" class="mt-5 grid gap-4">
                @csrf

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <x-select 
                        name="jenis_biaya" 
                        label="Jenis Biaya"
                        :options="[
                            'pendaftaran' => 'Pendaftaran',
                            'daftar_ulang' => 'Daftar Ulang',
                            'udp' => 'UDP'
                        ]"
                    />

                    <x-select 
                        name="kategori" 
                        label="Kategori"
                        :options="[
                            'wajib' => 'Wajib',
                            'opsional' => 'Opsional'
                        ]"
                    />

                    <x-select 
                        name="jenis_kelamin" 
                        label="Jenis Kelamin"
                        :options="[
                            'semua' => 'Semua',
                            'laki-laki' => 'Laki-laki',
                            'perempuan' => 'Perempuan'
                        ]"
                    />
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <x-input name="nama_biaya" label="Nama Biaya" />
                    <div class="space-y-1" x-data="{
                        nominalRaw: '{{ old('nominal') }}',
                        nominalFormatted: '',
                        init() {
                            const raw = (this.nominalRaw || '').toString().replace(/\D/g, '');
                            this.nominalRaw = raw;
                            this.nominalFormatted = this.formatNominal(raw);
                        },
                        formatNominal(value) {
                            if (!value) return '';
                            return new Intl.NumberFormat('id-ID').format(Number(value));
                        },
                        onNominalInput(event) {
                            const raw = event.target.value.replace(/\D/g, '');
                            this.nominalRaw = raw;
                            this.nominalFormatted = this.formatNominal(raw);
                        }
                    }">
                        <label class="text-sm font-medium text-gray-700">Nominal</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm font-medium text-slate-600">Rp.</span>
                            <input
                                type="text"
                                inputmode="numeric"
                                :value="nominalFormatted"
                                @input="onNominalInput($event)"
                                class="w-full rounded-lg border border-gray-300 py-2 pl-12 pr-3 text-sm shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                placeholder="0"
                            >
                            <input type="hidden" name="nominal" :value="nominalRaw">
                        </div>
                        @error('nominal')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2.5">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="is_acuan_status_ppdb" value="1" class="rounded border-slate-300">
                        Jadikan acuan perpindahan status PPDB
                    </label>
                    <p class="mt-1 text-xs text-blue-700/90">Acuan digunakan untuk menentukan kelayakan perpindahan status dari calon ke peserta didik.</p>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">Simpan Biaya</button>
                </div>
            </form>
        </div>

        <div class="card p-0 overflow-hidden xl:col-span-3">
            <div class="border-b border-slate-200 px-5 py-4">
                <h3 class="text-base font-semibold text-slate-800">Daftar Biaya</h3>
                <p class="mt-1 text-xs text-slate-500">Aktif/nonaktifkan, set acuan, atau hapus komponen biaya sesuai kebutuhan.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-100 text-left text-sm font-semibold text-slate-700">
                            <th class="px-4 py-3 w-16">No</th>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Jenis</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3">JK</th>
                            <th class="px-4 py-3">Nominal</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($biaya as $b)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3 text-slate-500">{{ $biaya->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-slate-800">{{ $b->nama_biaya }}</p>
                                <p class="mt-1 flex items-center gap-1.5 text-xs font-medium text-slate-600">
                                    {{ $b->aktif ? 'Aktif' : 'Nonaktif' }}
                                    @if($b->is_acuan_status_ppdb)
                                        <span>-</span>
                                        <span class="inline-flex items-center rounded-full border border-red-200 bg-red-50 px-2 py-0.5 text-[11px] font-semibold text-red-700">Acuan</span>
                                    @endif
                                </p>
                            </td>
                            <td class="px-4 py-3">{{ ui_label($b->jenis_biaya) }}</td>
                            <td class="px-4 py-3">{{ ui_label($b->kategori) }}</td>
                            <td class="px-4 py-3">{{ ui_label($b->jenis_kelamin) }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-800 whitespace-nowrap">
                                Rp {{ number_format($b->nominal,0,',','.') }}
                            </td>
                            <td class="px-4 py-3">
                                <div
                                    x-data="{
                                        open: false,
                                        menuTop: 0,
                                        menuLeft: 0,
                                        toggleMenu(event) {
                                            if (this.open) {
                                                this.open = false;
                                                return;
                                            }

                                            const rect = event.currentTarget.getBoundingClientRect();
                                            this.open = true;

                                            this.$nextTick(() => {
                                                const menuWidth = this.$refs.actionMenu ? this.$refs.actionMenu.offsetWidth : 176;
                                                const menuHeight = this.$refs.actionMenu ? this.$refs.actionMenu.offsetHeight : 180;
                                                let left = rect.left;

                                                if (left + menuWidth > window.innerWidth - 12) {
                                                    left = window.innerWidth - menuWidth - 12;
                                                }

                                                if (left < 12) {
                                                    left = 12;
                                                }

                                                this.menuLeft = left;

                                                const openUp = (window.innerHeight - rect.bottom) < (menuHeight + 12);
                                                this.menuTop = openUp
                                                    ? Math.max(12, rect.top - menuHeight - 8)
                                                    : rect.bottom + 8;
                                            });
                                        }
                                    }"
                                    @scroll.window="open = false"
                                    @resize.window="open = false"
                                    class="inline-block text-left"
                                >
                                    <button
                                        type="button"
                                        @click="toggleMenu($event)"
                                        class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white p-2 text-slate-500 shadow-sm transition hover:bg-slate-50 hover:text-blue-600 focus:outline-none"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                        </svg>
                                    </button>

                                    <template x-teleport="body">
                                        <div
                                            x-show="open"
                                            x-cloak
                                            @click.away="open = false"
                                            @keydown.escape.window="open = false"
                                            x-ref="actionMenu"
                                            class="fixed z-[200] w-44 rounded-lg border border-slate-200 bg-white p-2 shadow-lg"
                                            :style="`top: ${menuTop}px; left: ${menuLeft}px;`"
                                        >
                                            <form method="POST" action="{{ route('biaya.toggle',$b) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="flex w-full items-center gap-2 rounded-md px-2.5 py-2 text-left text-xs font-semibold text-amber-700 hover:bg-amber-50">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                    </svg>
                                                    {{ $b->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('biaya.toggle-acuan-status',$b) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="flex w-full items-center gap-2 rounded-md px-2.5 py-2 text-left text-xs font-semibold text-emerald-700 hover:bg-emerald-50">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    {{ $b->is_acuan_status_ppdb ? 'Batalkan Acuan' : 'Pilih Acuan' }}
                                                </button>
                                            </form>

                                            <button
                                                type="button"
                                                @click="open = false; openDeleteModal('{{ route('biaya.destroy',$b) }}')"
                                                class="flex w-full items-center gap-2 rounded-md px-2.5 py-2 text-left text-xs font-semibold text-red-700 hover:bg-red-50"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12m-9 0V5a1 1 0 011-1h4a1 1 0 011 1v2m-7 4v6m4-6v6M5 7l1 12a2 2 0 002 2h8a2 2 0 002-2l1-12" />
                                                </svg>
                                                Hapus
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-500">
                                Data biaya belum tersedia
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-200 px-4 py-3 md:flex-row md:items-center md:justify-between">
                <form method="GET" class="flex items-center gap-2 text-xs text-slate-600">
                    <label for="perPageBiaya">Tampilkan</label>
                    <select id="perPageBiaya" name="per_page" onchange="this.form.submit()" class="rounded border border-slate-300 px-2 py-1 text-xs">
                        @foreach([10,20,50,100] as $size)
                            <option value="{{ $size }}" {{ (int) request('per_page', $perPage ?? 20) === $size ? 'selected' : '' }}>{{ $size }}</option>
                        @endforeach
                    </select>
                    <span>data</span>
                </form>

                <div>
                    {{ $biaya->links() }}
                </div>
            </div>
        </div>
    </div>

</div>

{{-- MODAL DELETE --}}
<x-modal id="deleteModal" title="Konfirmasi Hapus">
    <p>Anda yakin ingin menghapus data ini?</p>
    <form id="deleteForm" method="POST" class="mt-4 flex justify-end gap-2">
        @csrf
        @method('DELETE')
        <button type="button" onclick="closeDeleteModal()" class="btn-secondary">Batal</button>
        <button type="submit" class="btn-danger">Ya, Hapus</button>
    </form>
</x-modal>

@endsection
