@extends('layouts.admin')

@section('title','Master Voucher')

@section('content')

<div class="mx-auto max-w-7xl space-y-6">
  @php
  $totalVoucher = $vouchers->total();
  $aktifVoucher = $vouchers->getCollection()->filter(fn($item) => $item->masihBerlaku())->count();
  $nonAktifVoucher = $vouchers->getCollection()->filter(fn($item) => !$item->masihBerlaku())->count();
  @endphp

  <div class="rounded-2xl border border-slate-200 bg-gradient-to-r from-slate-50 via-white to-emerald-50 p-5 shadow-sm">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
      <div>
        <h2 class="text-xl font-semibold text-slate-800">Master Voucher</h2>
        <p class="mt-1 text-sm text-slate-600">Kelola voucher potongan biaya PPDB secara terpusat.</p>
        <p class="mt-1 text-xs text-slate-500">
          Voucher tidak dipilih saat pendaftaran. Voucher diklaim admin/panitia pada rincian biaya
          saat seluruh biaya lain sudah lunas, dan hanya bisa diklaim sampai tanggal selesai
          (setelah itu dianggap hangus).
        </p>
      </div>
      <div class="flex flex-wrap items-center gap-2">
        <span
          class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700">Total:
          {{ $totalVoucher }}</span>
        <span
          class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Aktif:
          {{ $aktifVoucher }}</span>
        <span
          class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">Tidak
          Aktif: {{ $nonAktifVoucher }}</span>
      </div>
    </div>
  </div>

  {{-- Flash success/error ditampilkan sebagai toast global oleh layouts/admin --}}


  {{-- Data voucher utk modal edit --}}
  @php
    $voucherJson = $vouchers->getCollection()->map(fn($v) => [
      'id' => $v->id,
      'kode' => $v->kode,
      'nama' => $v->nama,
      'jenis_biaya' => $v->jenis_biaya,
      'diskon_nominal' => (int) $v->diskon_nominal,
      'maks_penggunaan' => (int) $v->maks_penggunaan,
      'digunakan' => (int) $v->digunakan,
      'tanggal_mulai' => optional($v->tanggal_mulai)->format('Y-m-d'),
      'tanggal_selesai' => optional($v->tanggal_selesai)->format('Y-m-d'),
    ])->values()->toJson();
  @endphp
  <script>
    window.__voucherData = {!! $voucherJson !!};
  </script>

  {{-- TOMBOL TAMBAH (form tambah dipindah ke modal) --}}
  <div class="card">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
      <div>
        <h2 class="text-base font-semibold text-slate-800">Data Voucher</h2>
        <p class="mt-1 text-xs text-slate-500">Buat voucher potongan biaya yang dapat diklaim pada rincian biaya siswa.</p>
      </div>

      <div class="flex flex-col gap-2 sm:flex-row md:flex-row md:items-center">
        <button onclick="openModal('modalTambahVoucher')"
          class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
          + Tambah Voucher
        </button>
      </div>
    </div>
  </div>

  {{-- TABEL DATA --}}
  <div class="card p-0 overflow-hidden">
    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
      <!-- Judul -->
      <h2 class="text-base font-semibold text-slate-800">
        Daftar Voucher
      </h2>

      <!-- Hapus Semua -->
      <button onclick="openModal('modalDeleteAll')"
        class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100">
        Hapus Semua Voucher
      </button>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-sm border-collapse">
        <thead class="bg-slate-100 text-slate-700">
          <tr>
            <th class="px-4 py-3 text-left">Kode</th>
            <th class="px-4 py-3 text-left">Nama</th>
            <th class="px-4 py-3 text-left">Jenis</th>
            <th class="px-4 py-3 text-right">Diskon</th>
            <th class="px-4 py-3 text-center">Diklaim</th>
            <th class="px-4 py-3 text-center">Status</th>
            <th class="px-4 py-3 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          @forelse($vouchers as $v)
          <tr class="hover:bg-slate-50 transition">
            <!-- Kode -->
            <td class="px-4 py-3 font-mono">
              {{ $v->kode }}
            </td>

            <!-- Nama + Tanggal Mulai & Selesai -->
            <td class="px-4 py-3 font-medium">
              {{ $v->nama }}
              <div class="mt-1 text-xs text-slate-500 flex flex-wrap gap-1">
                <span class="badge-info">{{ date('d M Y', strtotime($v->tanggal_mulai)) }}</span>
                -
                <span class="badge-danger">{{ date('d M Y', strtotime($v->tanggal_selesai)) }}</span>
              </div>
            </td>

            <!-- Jenis Biaya -->
            <td class="px-4 py-3">
              {{ ui_label($v->jenis_biaya) }}
            </td>

            <!-- Diskon -->
            <td class="px-4 py-3 text-right whitespace-nowrap">
              Rp {{ number_format($v->diskon_nominal,0,',','.') }}
            </td>

            <!-- Maks Penggunaan -->
            <td class="px-4 py-3 text-center">
              {{ $v->digunakan }} / {{ $v->maks_penggunaan }}
            </td>

            <!-- Status -->
            <td class="px-4 py-3 text-center">
              @if($v->masihBerlaku())
              <span class="badge-success">Aktif</span>
              @elseif($v->isHangus())
              <span class="badge-danger">Hangus</span>
              @else
              <span class="badge-warning">Tidak Aktif</span>
              @endif
            </td>

            <!-- Aksi -->
            <td class="px-4 py-3 text-center">
              <div x-data="{
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
                                        const menuWidth = this.$refs.actionMenu ? this.$refs.actionMenu.offsetWidth : 180;
                                        const menuHeight = this.$refs.actionMenu ? this.$refs.actionMenu.offsetHeight : 140;
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
                            }" @scroll.window="open = false" @resize.window="open = false"
                class="inline-block text-left">
                <button type="button" @click="toggleMenu($event)"
                  class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white p-2 text-slate-500 shadow-sm transition hover:bg-slate-50 hover:text-blue-600 focus:outline-none">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                  </svg>
                </button>

                <template x-teleport="body">
                  <div x-show="open" x-cloak @click.away="open = false" @keydown.escape.window="open = false"
                    x-ref="actionMenu"
                                        class="fixed z-dropdown w-44 rounded-lg border border-slate-200 bg-white p-2 shadow-lg"
                    :style="`top: ${menuTop}px; left: ${menuLeft}px;`">
                    <button type="button" onclick="openEditVoucherModal({{ $v->id }})"
                      class="flex w-full items-center gap-2 rounded-md px-2.5 py-2 text-left text-xs font-semibold text-amber-700 hover:bg-amber-50">
                      <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 20h4l10-10-4-4L4 16v4Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l4 4" />
                      </svg>
                      Edit
                    </button>

                    <form method="POST" action="{{ route('voucher.toggle',$v) }}">
                      @csrf
                      @method('PATCH')
                      <button type="submit"
                        class="flex w-full items-center gap-2 rounded-md px-2.5 py-2 text-left text-xs font-semibold text-blue-700 hover:bg-blue-50">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ $v->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                      </button>
                    </form>

                    <form method="POST" action="{{ route('voucher.destroy',$v) }}">
                      @csrf
                      @method('DELETE')
                      <button type="submit"
                        class="flex w-full items-center gap-2 rounded-md px-2.5 py-2 text-left text-xs font-semibold text-red-700 hover:bg-red-50">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 7h12m-9 0V5a1 1 0 011-1h4a1 1 0 011 1v2m-7 4v6m4-6v6M5 7l1 12a2 2 0 002 2h8a2 2 0 002-2l1-12" />
                        </svg>
                        Hapus
                      </button>
                    </form>
                  </div>
                </template>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="px-4 py-6 text-center text-slate-500">
              Data voucher belum tersedia
            </td>
          </tr>
          @endforelse
        </tbody>

      </table>
    </div>

    <div class="flex flex-col gap-3 border-t border-slate-200 px-4 py-3 md:flex-row md:items-center md:justify-between">
      <form method="GET" class="flex items-center gap-2 text-xs text-slate-600">
        <label for="perPageVoucher">Tampilkan</label>
        <select id="perPageVoucher" name="per_page" onchange="this.form.submit()"
          class="rounded border border-slate-300 px-2 py-1 text-xs">
          @foreach([10,20,50,100] as $size)
          <option value="{{ $size }}" {{ (int) request('per_page', $perPage ?? 20) === $size ? 'selected' : '' }}>
            {{ $size }}</option>
          @endforeach
        </select>
        <span>data</span>
      </form>

      <div>
        {{ $vouchers->links() }}
      </div>
    </div>
  </div>

</div>


{{-- MODAL TAMBAH VOUCHER --}}
<div id="modalTambahVoucher"
  class="fixed inset-0 z-[300] hidden items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm transition-all duration-300">
  <div class="w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl transform transition-all duration-300 sm:p-8">
    <div class="mb-6 flex items-start justify-between gap-3">
      <div>
        <h3 class="text-xl font-bold text-slate-800">Tambah Voucher</h3>
        <p class="mt-1 text-xs text-slate-500">Susun detail voucher potongan biaya.</p>
      </div>
      <button type="button" onclick="closeModal('modalTambahVoucher')"
        class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <form method="POST" action="{{ route('voucher.store') }}" class="grid gap-4" id="voucherForm">
      @csrf

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div>
          <label class="label">Nama Voucher <span class="text-red-500">*</span></label>
          <input name="nama" class="input" placeholder="Nama Voucher" value="{{ old('nama') }}" required>
        </div>

        <div>
          <label class="label">Jenis Biaya (Target Potongan) <span class="text-red-500">*</span></label>
          <select name="jenis_biaya" class="input" required>
            <option value="">Pilih</option>
            <option value="pendaftaran" {{ old('jenis_biaya') === 'pendaftaran' ? 'selected' : '' }}>Pendaftaran
            </option>
            <option value="daftar_ulang" {{ old('jenis_biaya') === 'daftar_ulang' ? 'selected' : '' }}>Daftar Ulang
            </option>
            <option value="udp" {{ old('jenis_biaya') === 'udp' ? 'selected' : '' }}>UDP</option>
          </select>
          <p class="text-xs text-slate-500 mt-1">Potongan diterapkan pada biaya jenis ini setelah biaya lain lunas.</p>
        </div>

        <div x-data="{
                    diskonRaw: '{{ old('diskon_nominal') }}',
                    diskonFormatted: '',
                    init() {
                        const raw = (this.diskonRaw || '').toString().replace(/\D/g, '');
                        this.diskonRaw = raw;
                        this.diskonFormatted = this.formatDiskon(raw);
                    },
                    formatDiskon(value) {
                        if (!value) return '';
                        return new Intl.NumberFormat('id-ID').format(Number(value));
                    },
                    onDiskonInput(event) {
                        const raw = event.target.value.replace(/\D/g, '');
                        this.diskonRaw = raw;
                        this.diskonFormatted = this.formatDiskon(raw);
                    }
                }">
          <label class="label">Diskon Nominal <span class="text-red-500">*</span></label>
          <div class="relative">
            <span
              class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm font-medium text-slate-600">Rp.</span>
            <input type="text" inputmode="numeric" :value="diskonFormatted" @input="onDiskonInput($event)"
              class="w-full rounded-lg border border-gray-300 py-2 pl-12 pr-3 text-sm shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
              placeholder="Contoh: 50.000" required>
            <input type="hidden" name="diskon_nominal" :value="diskonRaw">
          </div>
          @error('diskon_nominal')
          <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
          @enderror
        </div>
      </div>

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div>
          <label class="label">Maksimal Penggunaan <span class="text-red-500">*</span></label>
          <input type="number" name="maks_penggunaan" class="input" placeholder="Contoh: 10"
            value="{{ old('maks_penggunaan') }}" min="1" required>
        </div>

        <div>
          <label class="label">Tanggal Mulai <span class="text-red-500">*</span></label>
          <input type="date" name="tanggal_mulai" class="input" value="{{ old('tanggal_mulai') }}" required>
        </div>

        <div>
          <label class="label">Tanggal Selesai <span class="text-red-500">*</span></label>
          <input type="date" name="tanggal_selesai" class="input" value="{{ old('tanggal_selesai') }}" required>
        </div>
      </div>

      <div class="flex justify-end gap-3 border-t border-slate-200 pt-4">
        <button type="button" onclick="closeModal('modalTambahVoucher')"
          class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors">
          Batal
        </button>
        <button type="submit" id="submitVoucherBtn"
          class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-500/30 hover:bg-blue-700 transition-all">
          Simpan Voucher
        </button>
      </div>
    </form>
  </div>
</div>

{{-- MODAL EDIT VOUCHER --}}
<div id="modalEditVoucher"
  class="fixed inset-0 z-[300] hidden items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm transition-all duration-300">
  <div class="w-full max-w-2xl rounded-3xl border border-slate-200 bg-white p-8 shadow-2xl transform transition-all duration-300 max-h-[90vh] overflow-y-auto">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h3 class="text-xl font-bold text-slate-800">Edit Voucher</h3>
        <p class="text-xs text-slate-500 mt-1">Kode: <span id="editVoucherKode" class="font-mono font-semibold"></span></p>
      </div>
      <button type="button" onclick="closeModal('modalEditVoucher')"
        class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <form id="formEditVoucher" method="POST" class="grid gap-4">
      @csrf
      @method('PUT')

      <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
          <label class="label">Nama Voucher</label>
          <input name="nama" id="editNamaVoucher" class="input" placeholder="Nama Voucher" required>
        </div>

        <div>
          <label class="label">Jenis Biaya (Target Potongan)</label>
          <select name="jenis_biaya" id="editJenisBiaya" class="input" required>
            <option value="pendaftaran">Pendaftaran</option>
            <option value="daftar_ulang">Daftar Ulang</option>
            <option value="udp">UDP</option>
          </select>
          {{-- Saat select dikunci (voucher sudah diklaim), nilai dikirim lewat hidden input ini --}}
          <input type="hidden" name="jenis_biaya" id="editJenisBiayaHidden" disabled>
          <p class="text-xs text-slate-500 mt-1">Tidak dapat diubah jika voucher sudah pernah diklaim.</p>
        </div>

        <div>
          <label class="label">Diskon Nominal</label>
          <div class="relative">
            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm font-medium text-slate-600">Rp.</span>
            <input type="text" inputmode="numeric" id="editDiskonDisplay"
              class="w-full rounded-lg border border-gray-300 py-2 pl-12 pr-3 text-sm shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
              placeholder="Contoh: 50.000" required>
          </div>
          <input type="hidden" name="diskon_nominal" id="editDiskonNominal">
          <p class="text-xs text-slate-500 mt-1">Tidak dapat diubah jika voucher sudah pernah diklaim.</p>
        </div>

        <div>
          <label class="label">Maksimal Penggunaan</label>
          <input type="number" name="maks_penggunaan" id="editMaksPenggunaan" class="input" min="1" required>
          <p class="text-xs text-slate-500 mt-1">Diklaim saat ini: <span id="editDigunakan" class="font-semibold"></span></p>
        </div>

        <div>
          <label class="label">Tanggal Mulai</label>
          <input type="date" name="tanggal_mulai" id="editTanggalMulai" class="input" required>
        </div>

        <div>
          <label class="label">Tanggal Selesai</label>
          <input type="date" name="tanggal_selesai" id="editTanggalSelesai" class="input" required>
        </div>
      </div>

      <div class="flex justify-end gap-3 pt-2">
        <button type="button" onclick="closeModal('modalEditVoucher')"
          class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors">
          Batal
        </button>
        <button type="submit" id="submitEditVoucherBtn"
          class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-500/30 hover:bg-blue-700 transition-all">
          Simpan Perubahan
        </button>
      </div>
    </form>
  </div>
</div>


{{-- MODAL HAPUS SEMUA VOUCHER --}}
<div id="modalDeleteAll"
  class="fixed inset-0 z-[300] hidden items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm transition-all duration-300">
  <div
    class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-8 shadow-2xl transform transition-all duration-300">
    <div class="w-20 h-20 bg-red-50 text-red-600 rounded-3xl flex items-center justify-center mx-auto mb-6">
      <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
      </svg>
    </div>

    <div class="text-center mb-8">
      <h3 class="text-xl font-bold text-slate-800">Clear All Vouchers?</h3>
      <p class="text-sm text-slate-500 mt-2 leading-relaxed">Apakah Anda yakin ingin menghapus <strong>semua data
          voucher</strong>? Aksi ini akan menghapus semua catatan voucher secara permanen.</p>
    </div>

    <div class="flex gap-3">
      <button type="button" onclick="closeModal('modalDeleteAll')"
        class="flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors">
        Batal
      </button>

      <form method="POST" action="{{ route('voucher.destroyAll') }}" class="flex-1">
        @csrf
        @method('DELETE')
        <button type="submit"
          class="w-full rounded-xl bg-red-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-red-500/30 hover:bg-red-700 transition-all">
          Ya, Bersihkan
        </button>
      </form>
    </div>
  </div>
</div>


<script>
  // ---- Modal Edit Voucher ----
  function openEditVoucherModal(id) {
    const v = (window.__voucherData || []).find((item) => Number(item.id) === Number(id));
    if (!v) return;

    document.getElementById('formEditVoucher').action = '{{ url("admin/voucher") }}/' + v.id;
    document.getElementById('editVoucherKode').textContent = v.kode;
    document.getElementById('editNamaVoucher').value = v.nama;
    document.getElementById('editJenisBiaya').value = v.jenis_biaya;
    document.getElementById('editJenisBiayaHidden').value = v.jenis_biaya;
    document.getElementById('editDiskonNominal').value = v.diskon_nominal;
    document.getElementById('editDiskonDisplay').value = new Intl.NumberFormat('id-ID').format(v.diskon_nominal);
    document.getElementById('editMaksPenggunaan').value = v.maks_penggunaan;
    document.getElementById('editDigunakan').textContent = v.digunakan;
    document.getElementById('editTanggalMulai').value = v.tanggal_mulai;
    document.getElementById('editTanggalSelesai').value = v.tanggal_selesai;

    // Voucher yang sudah diklaim: kunci jenis biaya & diskon.
    // Catatan: elemen disabled tidak ikut terkirim saat submit, jadi kita pakai
    // hidden input + tampilan visual terkunci (bukan disabled) agar nilai tetap terkirim.
    const locked = Number(v.digunakan) > 0;
    const jenisSelect = document.getElementById('editJenisBiaya');
    const hiddenJenis = document.getElementById('editJenisBiayaHidden');
    const diskonDisplay = document.getElementById('editDiskonDisplay');

    if (locked) {
      jenisSelect.classList.add('bg-slate-100', 'cursor-not-allowed', 'text-slate-500');
      jenisSelect.disabled = true;
      hiddenJenis.disabled = false;

      diskonDisplay.classList.add('bg-slate-100', 'cursor-not-allowed', 'text-slate-500');
      diskonDisplay.readOnly = true;
    } else {
      jenisSelect.classList.remove('bg-slate-100', 'cursor-not-allowed', 'text-slate-500');
      jenisSelect.disabled = false;
      hiddenJenis.disabled = true;

      diskonDisplay.classList.remove('bg-slate-100', 'cursor-not-allowed', 'text-slate-500');
      diskonDisplay.readOnly = false;
    }

    openModal('modalEditVoucher');
  }

  document.addEventListener('DOMContentLoaded', function() {
    // Format input diskon edit (rupiah)
    const editDiskonDisplay = document.getElementById('editDiskonDisplay');
    const editDiskonNominal = document.getElementById('editDiskonNominal');
    if (editDiskonDisplay && editDiskonNominal) {
      editDiskonDisplay.addEventListener('input', function() {
        const raw = this.value.replace(/\D/g, '');
        editDiskonNominal.value = raw;
        this.value = raw ? new Intl.NumberFormat('id-ID').format(Number(raw)) : '';
      });
    }

    // Sinkronkan hidden input jenis biaya saat select diubah (mode unlocked)
    const editJenisSelect = document.getElementById('editJenisBiaya');
    const editJenisHidden = document.getElementById('editJenisBiayaHidden');
    if (editJenisSelect && editJenisHidden) {
      editJenisSelect.addEventListener('change', function() {
        editJenisHidden.value = this.value;
      });
    }

    // Form edit voucher: disable tombol saat submit
    const formEditVoucher = document.getElementById('formEditVoucher');
    const submitEditBtn = document.getElementById('submitEditVoucherBtn');
    if (formEditVoucher && submitEditBtn) {
      formEditVoucher.addEventListener('submit', function() {
        submitEditBtn.disabled = true;
        submitEditBtn.textContent = 'Loading...';
        submitEditBtn.classList.add('opacity-50', 'cursor-not-allowed');
      });
    }

    // FORM SIMPAN VOUCHER
  const voucherForm = document.getElementById('voucherForm');
  const submitBtn = document.getElementById('submitVoucherBtn');

  voucherForm.addEventListener('submit', function() {
    submitBtn.disabled = true;
    submitBtn.textContent = 'Loading...';
    submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
  });

  // SEMUA FORM HAPUS VOUCHER
  const deleteForms = document.querySelectorAll('form[action*="voucher.destroy"]');
  deleteForms.forEach(form => {
    form.addEventListener('submit', function(e) {
      const btn = form.querySelector('button[type="submit"]');
      if (btn) {
        btn.disabled = true;
        btn.textContent = 'Menghapus...';
        btn.classList.add('opacity-50', 'cursor-not-allowed');
      }
    });
  });

  // FORM HAPUS SEMUA VOUCHER
  const destroyAllUrl = '{{ route("voucher.destroyAll") }}';
  const deleteAllForm = document.querySelector(`form[action="${destroyAllUrl}"]`);
  if (deleteAllForm) {
    deleteAllForm.addEventListener('submit', function() {
      const btn = deleteAllForm.querySelector('button[type="submit"]');
      if (btn) {
        btn.disabled = true;
        btn.textContent = 'Menghapus semua...';
        btn.classList.add('opacity-50', 'cursor-not-allowed');
      }
    });
  }
});
</script>

@endsection