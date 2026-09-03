
@extends('layouts.public')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">

    @if(session('error'))
        <div class="mb-4 p-3 rounded-lg border border-red-200 bg-red-50 text-red-700 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <!-- PAGE CARD -->
    <div class="bg-white shadow-lg rounded-xl border border-slate-200 min-h-screen flex flex-col">

        <!-- HEADER -->
        <div class="p-6 border-b border-slate-200 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">📋 Pendaftar PPDB</h1>
                <p class="text-sm text-slate-500 mt-1">Manajemen data calon peserta didik baru</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <button type="button"
                    onclick="bukaPasswordKeuanganModal('{{ route('pendaftaran.statistik.keuangan') }}')"
                    class="px-4 py-2 bg-emerald-600 text-white text-sm rounded-lg shadow hover:bg-emerald-700">
                    📊 Statistik Keuangan
                </button>

                <a href="{{ route('pendaftaran.public.fresh') }}"
                    class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg shadow hover:bg-blue-700">
                    + Daftar Peserta Didik Baru
                </a>
            </div>
        </div>

        <!-- TOOLBAR -->
        <div class="p-4 pb-2 bg-slate-50">
            <form id="filterForm" method="GET" class="flex flex-row flex-wrap items-end gap-2">
                <div class="relative w-[220px] shrink-0">
                    <input id="searchInput" type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari nama, no registrasi, atau data ibu"
                        class="w-full rounded-lg border border-slate-300 pl-3 pr-9 py-2 text-sm text-slate-700 focus:ring-2 focus:ring-blue-100 focus:border-blue-400">
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-4.65a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                @php
                    $selectedPaymentStatuses = $paymentStatuses ?? (array) request('payment_statuses', []);
                    $selectedBiayaIds = array_map('intval', $biayaIds ?? (array) request('biaya_ids', []));
                    $selectedJenisKelamins = $jenisKelamins ?? (array) request('jenis_kelamins', []);
                    $selectedDateFrom = $dateFrom ?? request('date_from');
                    $selectedDateTo = $dateTo ?? request('date_to');
                    $selectedOrder = $order ?? request('order', 'terbaru');
                    $selectedStatusPpdb = (int) ($statusPpdb ?? request('status_ppdb', 0));
                    if (!in_array($selectedStatusPpdb, [1, 2, 3], true)) {
                        $selectedStatusPpdb = 0;
                    }

                    $selectedHasilTes = (string) ($hasilTes ?? request('hasil_tes', ''));
                    $hasilTesLabelMap = [
                        'SB' => 'Sangat Baik',
                        'B' => 'Baik',
                        'PB' => 'Perlu Bantuan',
                        'BT' => 'Belum Test',
                    ];
                    if (!in_array($selectedHasilTes, array_keys($hasilTesLabelMap), true)) {
                        $selectedHasilTes = '';
                    }

                    $selectedVoucherActive = (bool) ($voucherActive ?? request()->boolean('voucher_active'));
                    $selectedVoucherCode = (string) ($voucherCode ?? request('voucher_code', ''));
                    $voucherCodeOptions = $voucherCodeOptions ?? [];
                    if ($selectedVoucherCode !== '' && $selectedVoucherCode !== 'none' && !in_array($selectedVoucherCode, $voucherCodeOptions, true)) {
                        $selectedVoucherCode = '';
                    }

                    $activeFilterCount = count($selectedPaymentStatuses)
                        + count($selectedBiayaIds)
                        + count($selectedJenisKelamins)
                        + (!empty($selectedHasilTes) ? 1 : 0)
                        + ($selectedVoucherActive ? 1 : 0)
                        + (!empty($selectedVoucherCode) ? 1 : 0)
                        + (!empty($selectedDateFrom) ? 1 : 0)
                        + (!empty($selectedDateTo) ? 1 : 0)
                        + ($selectedOrder === 'terlama' ? 1 : 0)
                        + ($selectedStatusPpdb > 0 ? 1 : 0);

                    $paymentLabelMap = [
                        'lunas' => 'Lunas',
                        'belum_lunas' => 'Belum Lunas',
                    ];

                    $selectedPaymentMap = collect($selectedPaymentStatuses)
                        ->mapWithKeys(fn ($status) => [$status => $paymentLabelMap[$status] ?? ui_label($status)])
                        ->all();

                    $selectedBiayaMap = collect($biayaOptions)
                        ->whereIn('id', $selectedBiayaIds)
                        ->pluck('nama_biaya', 'id')
                        ->toArray();

                    $jenisKelaminLabelMap = [
                        'laki-laki' => 'Laki-laki',
                        'perempuan' => 'Perempuan',
                    ];

                    $statusPpdbLabelMap = [
                        1 => 'Bakal Calon',
                        2 => 'Calon Peserta Didik',
                        3 => 'Peserta Didik',
                    ];

                    $selectedJenisKelaminMap = collect($selectedJenisKelamins)
                        ->mapWithKeys(fn ($jenisKelamin) => [$jenisKelamin => $jenisKelaminLabelMap[$jenisKelamin] ?? ui_label($jenisKelamin)])
                        ->all();

                    $currentQuery = request()->query();
                @endphp

                <div class="flex shrink-0 items-end gap-2">
                    <button
                        type="button"
                        onclick="location.reload()"
                        class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-100 transition-colors"
                        title="Refresh halaman"
                    >
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>

                    <div x-data="{ open: false }" class="relative w-full md:w-auto md:shrink-0">
                        <button
                            type="button"
                            @click="open = !open"
                            class="w-full md:w-auto inline-flex items-center justify-between gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-100 transition-colors"
                        >
                            <span>Filter</span>
                            @if($activeFilterCount > 0)
                                <span class="inline-flex items-center justify-center rounded-full bg-blue-600 px-1.5 py-0.5 text-[10px] text-white">{{ $activeFilterCount }}</span>
                            @endif
                            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                    <div
                        x-show="open"
                        x-cloak
                        x-transition
                        @click.away="open = false"
                        class="absolute right-0 mt-2 w-full md:w-[360px] rounded-xl border border-slate-200 bg-white p-3 shadow-lg z-30"
                    >
                        <div class="space-y-3">
                            <div>
                                <div class="mb-2 flex items-center justify-between">
                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Status Bayar</p>
                                    <button type="button" onclick="document.querySelectorAll('input[name=\'payment_statuses[]\']').forEach(el => el.checked = false)" class="text-[10px] font-medium text-slate-500 hover:text-slate-700">Reset</button>
                                </div>
                                <div class="space-y-1.5 text-xs text-slate-700">
                                    <label class="flex items-center gap-2"><input type="checkbox" name="payment_statuses[]" value="lunas" class="rounded border-slate-300" {{ in_array('lunas', $selectedPaymentStatuses, true) ? 'checked' : '' }}>Lunas</label>
                                    <label class="flex items-center gap-2"><input type="checkbox" name="payment_statuses[]" value="belum_lunas" class="rounded border-slate-300" {{ in_array('belum_lunas', $selectedPaymentStatuses, true) ? 'checked' : '' }}>Belum Lunas</label>
                                </div>
                            </div>

                            <div class="border-t border-slate-100 pt-3">
                                <div class="mb-2 flex items-center justify-between">
                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Jenis Biaya</p>
                                    <div class="flex items-center gap-2">
                                        <button type="button" onclick="document.querySelectorAll('input[name=\'biaya_ids[]\']').forEach(el => el.checked = true)" class="text-[10px] font-medium text-blue-600 hover:text-blue-700">Pilih Semua</button>
                                        <button type="button" onclick="document.querySelectorAll('input[name=\'biaya_ids[]\']').forEach(el => el.checked = false)" class="text-[10px] font-medium text-slate-500 hover:text-slate-700">Reset</button>
                                    </div>
                                </div>
                                <div class="max-h-32 overflow-y-auto space-y-1.5 text-xs text-slate-700 pr-1">
                                    @foreach($biayaOptions as $biaya)
                                        <label class="flex items-center gap-2">
                                            <input type="checkbox" name="biaya_ids[]" value="{{ $biaya->id }}" class="rounded border-slate-300" {{ in_array((int) $biaya->id, $selectedBiayaIds, true) ? 'checked' : '' }}>
                                            <span>{{ $biaya->nama_biaya }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="border-t border-slate-100 pt-3">
                                <div class="mb-2 flex items-center justify-between">
                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Jenis Kelamin</p>
                                    <button type="button" onclick="document.querySelectorAll('input[name=\'jenis_kelamins[]\']').forEach(el => el.checked = false)" class="text-[10px] font-medium text-slate-500 hover:text-slate-700">Reset</button>
                                </div>
                                <div class="space-y-1.5 text-xs text-slate-700">
                                    <label class="flex items-center gap-2"><input type="checkbox" name="jenis_kelamins[]" value="laki-laki" class="rounded border-slate-300" {{ in_array('laki-laki', $selectedJenisKelamins, true) ? 'checked' : '' }}>Laki-laki</label>
                                    <label class="flex items-center gap-2"><input type="checkbox" name="jenis_kelamins[]" value="perempuan" class="rounded border-slate-300" {{ in_array('perempuan', $selectedJenisKelamins, true) ? 'checked' : '' }}>Perempuan</label>
                                </div>
                            </div>

                            <div class="border-t border-slate-100 pt-3">
                                <div class="mb-2 flex items-center justify-between">
                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Voucher</p>
                                    <button type="button" onclick="document.querySelectorAll('input[name=\'voucher_active\']').forEach(el => el.checked = false)" class="text-[10px] font-medium text-slate-500 hover:text-slate-700">Reset</button>
                                </div>
                                <div class="space-y-1.5 text-xs text-slate-700">
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" name="voucher_active" value="1" class="rounded border-slate-300" {{ $selectedVoucherActive ? 'checked' : '' }}>
                                        <span>Voucher Aktif</span>
                                    </label>

                                    <div class="pt-2">
                                        <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">Jenis Voucher</label>
                                        <select name="voucher_code" class="w-full rounded-lg border border-slate-300 px-2.5 py-2 text-xs text-slate-700 focus:ring-2 focus:ring-blue-100 focus:border-blue-400">
                                            <option value="">Semua</option>
                                            <option value="none" {{ $selectedVoucherCode === 'none' ? 'selected' : '' }}>Tidak dapat voucher</option>
                                            @foreach($voucherCodeOptions as $kode)
                                                <option value="{{ $kode }}" {{ $selectedVoucherCode === $kode ? 'selected' : '' }}>{{ $kode }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 border-t border-slate-100 pt-3">
                                <a href="{{ route('pendaftaran.list') }}" class="w-1/2 text-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100">Reset</a>
                                <button type="submit" class="w-1/2 rounded-lg bg-blue-600 px-3 py-2 text-xs font-medium text-white hover:bg-blue-700">Terapkan</button>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>

                <div class="flex shrink-0 items-end gap-2">
                    <div class="flex flex-col">
                        <label class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 mb-1">Dari</label>
                        <input type="date" id="dateFromInput" name="date_from" value="{{ $selectedDateFrom }}" class="rounded-lg border border-slate-300 px-2.5 py-2 text-xs text-slate-700 focus:ring-2 focus:ring-blue-100 focus:border-blue-400">
                    </div>
                    <div class="flex flex-col">
                        <label class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 mb-1">Sampai</label>
                        <input type="date" id="dateToInput" name="date_to" value="{{ $selectedDateTo }}" class="rounded-lg border border-slate-300 px-2.5 py-2 text-xs text-slate-700 focus:ring-2 focus:ring-blue-100 focus:border-blue-400">
                    </div>
                    <div class="flex flex-col sm:w-36">
                        <label class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 mb-1">Urutan</label>
                        <select id="orderSelect" name="order" class="rounded-lg border border-slate-300 px-2.5 py-2 text-xs text-slate-700 focus:ring-2 focus:ring-blue-100 focus:border-blue-400">
                            <option value="terlama" {{ $selectedOrder === 'terlama' ? 'selected' : '' }}>Data Terlama</option>
                            <option value="terbaru" {{ $selectedOrder === 'terbaru' ? 'selected' : '' }}>Data Terbaru</option>
                        </select>
                    </div>
                    <div class="flex flex-col sm:w-44">
                        <label class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 mb-1">Status Peserta Didik</label>
                        <select id="statusPpdbSelect" name="status_ppdb" class="rounded-lg border border-slate-300 px-2.5 py-2 text-xs text-slate-700 focus:ring-2 focus:ring-blue-100 focus:border-blue-400">
                            <option value="">Semua Status</option>
                            <option value="1" {{ $selectedStatusPpdb === 1 ? 'selected' : '' }}>Bakal Calon</option>
                            <option value="2" {{ $selectedStatusPpdb === 2 ? 'selected' : '' }}>Calon Peserta Didik</option>
                            <option value="3" {{ $selectedStatusPpdb === 3 ? 'selected' : '' }}>Peserta Didik</option>
                        </select>
                    </div>
                    <div class="flex flex-col sm:w-44">
                        <label class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 mb-1">Hasil Test</label>
                        <select id="hasilTesSelect" name="hasil_tes" class="rounded-lg border border-slate-300 px-2.5 py-2 text-xs text-slate-700 focus:ring-2 focus:ring-blue-100 focus:border-blue-400">
                            <option value="">Semua Hasil</option>
                            <option value="SB" {{ $selectedHasilTes === 'SB' ? 'selected' : '' }}>Sangat Baik</option>
                            <option value="B" {{ $selectedHasilTes === 'B' ? 'selected' : '' }}>Baik</option>
                            <option value="PB" {{ $selectedHasilTes === 'PB' ? 'selected' : '' }}>Perlu Bantuan</option>
                            <option value="BT" {{ $selectedHasilTes === 'BT' ? 'selected' : '' }}>Belum Test</option>
                        </select>
                    </div>
                </div>

            </form>

            @if($activeFilterCount > 0)
                <div class="mt-1 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Filter Aktif:</span>

                    @foreach($selectedPaymentMap as $statusValue => $label)
                        @php
                            $queryWithoutStatus = $currentQuery;
                            $remainingStatuses = array_values(array_filter(
                                $selectedPaymentStatuses,
                                fn ($status) => $status !== $statusValue
                            ));

                            if (empty($remainingStatuses)) {
                                unset($queryWithoutStatus['payment_statuses']);
                            } else {
                                $queryWithoutStatus['payment_statuses'] = $remainingStatuses;
                            }
                        @endphp
                        <a href="{{ route('pendaftaran.list', $queryWithoutStatus) }}" class="inline-flex items-center gap-1 rounded-full border border-blue-200 bg-blue-50 px-2 py-0.5 text-[10px] font-medium text-blue-700 hover:bg-blue-100" title="Hapus filter {{ $label }}">
                            <span>{{ $label }}</span>
                            <span class="text-blue-500">x</span>
                        </a>
                    @endforeach

                    @foreach($selectedBiayaMap as $biayaId => $label)
                        @php
                            $queryWithoutBiaya = $currentQuery;
                            $remainingBiayaIds = array_values(array_filter(
                                $selectedBiayaIds,
                                fn ($id) => (int) $id !== (int) $biayaId
                            ));

                            if (empty($remainingBiayaIds)) {
                                unset($queryWithoutBiaya['biaya_ids']);
                            } else {
                                $queryWithoutBiaya['biaya_ids'] = $remainingBiayaIds;
                            }
                        @endphp
                        <a href="{{ route('pendaftaran.list', $queryWithoutBiaya) }}" class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700 hover:bg-emerald-100" title="Hapus filter {{ $label }}">
                            <span>{{ $label }}</span>
                            <span class="text-emerald-500">x</span>
                        </a>
                    @endforeach

                    @foreach($selectedJenisKelaminMap as $jenisKelaminValue => $label)
                        @php
                            $queryWithoutJenisKelamin = $currentQuery;
                            $remainingJenisKelamins = array_values(array_filter(
                                $selectedJenisKelamins,
                                fn ($jenisKelamin) => $jenisKelamin !== $jenisKelaminValue
                            ));

                            if (empty($remainingJenisKelamins)) {
                                unset($queryWithoutJenisKelamin['jenis_kelamins']);
                            } else {
                                $queryWithoutJenisKelamin['jenis_kelamins'] = $remainingJenisKelamins;
                            }
                        @endphp
                        <a href="{{ route('pendaftaran.list', $queryWithoutJenisKelamin) }}" class="inline-flex items-center gap-1 rounded-full border border-fuchsia-200 bg-fuchsia-50 px-2 py-0.5 text-[10px] font-medium text-fuchsia-700 hover:bg-fuchsia-100" title="Hapus filter {{ $label }}">
                            <span>{{ $label }}</span>
                            <span class="text-fuchsia-500">x</span>
                        </a>
                    @endforeach

                    @if(!empty($selectedHasilTes))
                        @php
                            $queryWithoutHasilTes = $currentQuery;
                            unset($queryWithoutHasilTes['hasil_tes']);
                        @endphp
                        <a href="{{ route('pendaftaran.list', $queryWithoutHasilTes) }}" class="inline-flex items-center gap-1 rounded-full border border-fuchsia-200 bg-fuchsia-50 px-2 py-0.5 text-[10px] font-medium text-fuchsia-700 hover:bg-fuchsia-100" title="Hapus filter hasil test">
                            <span>{{ $hasilTesLabelMap[$selectedHasilTes] ?? 'Hasil Test' }}</span>
                            <span class="text-fuchsia-500">x</span>
                        </a>
                    @endif

                    @if($selectedVoucherActive)
                        @php
                            $queryWithoutVoucher = $currentQuery;
                            unset($queryWithoutVoucher['voucher_active']);
                        @endphp
                        <a href="{{ route('pendaftaran.list', $queryWithoutVoucher) }}" class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700 hover:bg-emerald-100" title="Hapus filter voucher aktif">
                            <span>Voucher Aktif</span>
                            <span class="text-emerald-500">x</span>
                        </a>
                    @endif

                    @if(!empty($selectedVoucherCode))
                        @php
                            $queryWithoutVoucherCode = $currentQuery;
                            unset($queryWithoutVoucherCode['voucher_code']);
                        @endphp
                        <a href="{{ route('pendaftaran.list', $queryWithoutVoucherCode) }}" class="inline-flex items-center gap-1 rounded-full border border-indigo-200 bg-indigo-50 px-2 py-0.5 text-[10px] font-medium text-indigo-700 hover:bg-indigo-100" title="Hapus filter jenis voucher">
                            <span>{{ $selectedVoucherCode === 'none' ? 'Tidak dapat voucher' : ('Voucher: ' . $selectedVoucherCode) }}</span>
                            <span class="text-indigo-500">x</span>
                        </a>
                    @endif

                    @if($selectedOrder === 'terlama')
                        @php
                            $queryWithoutOrder = $currentQuery;
                            unset($queryWithoutOrder['order']);
                        @endphp
                        <a href="{{ route('pendaftaran.list', $queryWithoutOrder) }}" class="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 text-[10px] font-medium text-amber-700 hover:bg-amber-100" title="Kembali ke urutan terbaru (default)">
                            <span>Terlama</span>
                            <span class="text-amber-500">x</span>
                        </a>
                    @endif

                    @if($selectedStatusPpdb > 0)
                        @php
                            $queryWithoutStatusPpdb = $currentQuery;
                            unset($queryWithoutStatusPpdb['status_ppdb']);
                        @endphp
                        <a href="{{ route('pendaftaran.list', $queryWithoutStatusPpdb) }}" class="inline-flex items-center gap-1 rounded-full border border-rose-200 bg-rose-50 px-2 py-0.5 text-[10px] font-medium text-rose-700 hover:bg-rose-100" title="Hapus filter status peserta didik">
                            <span>{{ $statusPpdbLabelMap[$selectedStatusPpdb] ?? 'Status Peserta Didik' }}</span>
                            <span class="text-rose-500">x</span>
                        </a>
                    @endif

                    @if(!empty($selectedDateFrom))
                        @php
                            $queryWithoutDateFrom = $currentQuery;
                            unset($queryWithoutDateFrom['date_from']);
                        @endphp
                        <a href="{{ route('pendaftaran.list', $queryWithoutDateFrom) }}" class="inline-flex items-center gap-1 rounded-full border border-cyan-200 bg-cyan-50 px-2 py-0.5 text-[10px] font-medium text-cyan-700 hover:bg-cyan-100" title="Hapus tanggal dari">
                            <span>Dari: {{ \Illuminate\Support\Carbon::parse($selectedDateFrom)->format('d/m/Y') }}</span>
                            <span class="text-cyan-500">x</span>
                        </a>
                    @endif

                    @if(!empty($selectedDateTo))
                        @php
                            $queryWithoutDateTo = $currentQuery;
                            unset($queryWithoutDateTo['date_to']);
                        @endphp
                        <a href="{{ route('pendaftaran.list', $queryWithoutDateTo) }}" class="inline-flex items-center gap-1 rounded-full border border-cyan-200 bg-cyan-50 px-2 py-0.5 text-[10px] font-medium text-cyan-700 hover:bg-cyan-100" title="Hapus tanggal sampai">
                            <span>Sampai: {{ \Illuminate\Support\Carbon::parse($selectedDateTo)->format('d/m/Y') }}</span>
                            <span class="text-cyan-500">x</span>
                        </a>
                    @endif

                        <a href="{{ route('pendaftaran.list') }}" class="ml-1 text-[10px] font-medium text-slate-500 hover:text-slate-700">Hapus semua</a>
                    </div>

                    <span class="shrink-0 inline-flex items-center rounded-full border border-green-600 bg-green-600 px-3 py-1 text-[10px] font-semibold text-white">
                        {{ $siswa->total() }} data ditemukan
                    </span>
                </div>
            @endif
        </div>

        <!-- TABLE WRAPPER -->
        <div class="flex-1 overflow-x-auto overflow-y-visible">
            <table class="table">
                <thead>
                    <tr>
                        <th rowspan="2" class="text-center align-middle">No</th>
                        <th rowspan="2" class="text-center align-middle">No Registrasi</th>
                        <th rowspan="2" class="text-center align-middle">Tanggal Daftar</th>
                        <th rowspan="2" class="text-center align-middle">Data Peserta</th>
                        <th rowspan="2" class="text-center align-middle">Data Ibu</th>
                        <th colspan="3" class="align-middle" style="text-align: center;">Biaya</th>
                        <th rowspan="2" class="text-center align-middle">Aksi</th>
                    </tr>
                    <tr>
                        <th class="text-center w-16 min-w-[72px]">P</th>
                        <th class="text-center w-16 min-w-[72px]">DU</th>
                        <th class="text-center w-16 min-w-[72px]">UDP</th>
                    </tr>
                </thead>

                <tbody class="bg-white">
                    @forelse($siswa as $item)
                    <tr>
                        
                        <!-- NO -->
                        <td class="text-center">
                            {{ $loop->iteration }}
                        </td>

                        <!-- NO REG + VOUCHER -->
                        <td class="text-left text-xs text-slate-600 whitespace-nowrap">
                            <div class="font-medium text-slate-700">{{ optional($item->registration)->nomor_registrasi ?? '-' }}</div>
                            @php
                                $voucherWithBiaya = $item->tagihan
                                    ->firstWhere(fn($t) => !empty($t->kode_voucher));
                            @endphp
                            @if($voucherWithBiaya && $voucherWithBiaya->biaya)
                                @php
                                    $colorClass = match($voucherWithBiaya->biaya->jenis_biaya) {
                                        'pendaftaran' => 'border-blue-200 bg-blue-50 text-blue-700',
                                        'daftar_ulang' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                        'udp' => 'border-violet-200 bg-violet-50 text-violet-700',
                                        default => 'border-slate-200 bg-slate-50 text-slate-700',
                                    };
                                @endphp
                                <div class="mt-1 inline-block rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $colorClass }}">
                                    {{ $voucherWithBiaya->kode_voucher }}
                                </div>
                            @endif
                        </td>

                        <!-- TANGGAL DAFTAR -->
                        <td class="text-center text-xs text-slate-600 whitespace-nowrap">
                            {{ optional($item->registration)->tanggal_daftar ? \Illuminate\Support\Carbon::parse(optional($item->registration)->tanggal_daftar)->format('d M Y') : '-' }}
                        </td>

                        <!-- DATA SISWA -->
                        <td>
                            <div class="space-y-0.5">
                                <div class="font-semibold text-slate-800">
                                    {{ $item->nama }}
                                </div>

                                <div class="text-[11px] text-slate-500 capitalize flex items-center gap-1">
                                    {{ $item->jenis_kelamin }}
                                    @php
                                        $hasilTesValue = strtoupper(trim((string) ($item->hasil_tes ?? '')));
                                        if ($hasilTesValue === 'BELUM TEST') {
                                            $hasilTesValue = 'BT';
                                        }

                                        $hasilTesColorClass = match ($hasilTesValue) {
                                            'SB' => 'border-green-200 bg-green-50 text-green-700',
                                            'B' => 'border-blue-200 bg-blue-50 text-blue-700',
                                            'PB' => 'border-amber-200 bg-amber-50 text-amber-700',
                                            'BT' => 'border-red-200 bg-red-50 text-red-700',
                                            default => null,
                                        };
                                    @endphp

                                    @if(!empty($hasilTesColorClass))
                                        <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $hasilTesColorClass }}">
                                            @if($hasilTesValue === 'BT')
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            @else
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            @endif
                                            {{ $hasilTesValue }}
                                        </span>
                                    @endif

                                    @php
                                        $layakPipValue = strtolower(trim((string) ($item->layak_pip ?? '')));
                                        $isLayakPip = in_array($layakPipValue, ['ya', 'y', '1', 'true'], true);
                                        $noKks = trim((string) ($item->no_kks ?? ''));
                                        $kpsValue = trim((string) ($item->kps ?? ''));
                                        $kipValue = trim((string) ($item->kip ?? ''));
                                    @endphp

                                    @if($isLayakPip)
                                        <span x-data="{ open: false }" class="relative inline-flex">
                                            <button
                                                type="button"
                                                @click.stop="open = !open"
                                                @keydown.escape.window="open = false"
                                                class="inline-flex items-center gap-1.5 rounded-full border border-green-600 bg-green-600 px-2 py-0.5 text-[10px] font-semibold text-white"
                                                title="Layak menerima PIP"
                                            >
                                                <span>PIP</span>
                                                <span class="inline-flex h-3.5 w-3.5 items-center justify-center rounded-full border border-white/80 text-[9px] leading-none text-white/95">i</span>
                                            </button>

                                            <div
                                                x-show="open"
                                                x-cloak
                                                @click.away="open = false"
                                                class="absolute z-50 w-64 rounded-xl border border-slate-200 bg-white p-3 shadow-lg top-full mt-1 sm:top-1/2 sm:-translate-y-1/2 sm:left-full sm:ml-2"
                                            >
                                                <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 mb-2">Detail PIP</div>
                                                <div class="space-y-1 text-xs text-slate-700">
                                                    <div class="flex items-start justify-between gap-3">
                                                        <span class="text-slate-500">No KKS</span>
                                                        <span class="font-medium text-slate-800 text-right">{{ $noKks !== '' ? $noKks : '-' }}</span>
                                                    </div>
                                                    <div class="flex items-start justify-between gap-3">
                                                        <span class="text-slate-500">Penerima KPS/PKH</span>
                                                        <span class="font-medium text-slate-800 text-right">{{ $kpsValue !== '' ? $kpsValue : '-' }}</span>
                                                    </div>
                                                    <div class="flex items-start justify-between gap-3">
                                                        <span class="text-slate-500">Punya KIP</span>
                                                        <span class="font-medium text-slate-800 text-right">{{ $kipValue !== '' ? $kipValue : '-' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <!-- NAMA IBU + NO HP -->
                        <td>
                            <div class="font-medium">
                                {{ optional($item->ibu)->nama ?? '-' }}
                            </div>

                            @if(optional($item->ibu)->no_hp)
                                <button
                                    type="button"
                                    onclick="salinNoTelp(@js(optional($item->ibu)->no_hp))"
                                    class="text-xs text-slate-500 hover:text-blue-600 hover:underline"
                                    title="Klik untuk salin nomor telepon"
                                >
                                    {{ optional($item->ibu)->no_hp }}
                                </button>
                            @else
                                <div class="text-xs text-slate-500">-</div>
                            @endif
                        </td>

                        @php
                            $tagihanPendaftaran = $item->tagihan
                                ->filter(fn ($tagihan) => optional($tagihan->biaya)->jenis_biaya === 'pendaftaran');
                            $tagihanDaftarUlang = $item->tagihan
                                ->filter(fn ($tagihan) => optional($tagihan->biaya)->jenis_biaya === 'daftar_ulang');
                            $tagihanUdp = $item->tagihan
                                ->filter(fn ($tagihan) => optional($tagihan->biaya)->jenis_biaya === 'udp');

                            // Helper to get status for a tagihan collection
                            // Calculate based on actual pembayaran, not just status field
                            $getPaymentStatus = function($collection) {
                                if ($collection->isEmpty()) {
                                    return 'lunas'; // Tidak ada tagihan = dianggap lunas
                                }
                                
                                $totalTagihan = $collection->sum('total');
                                if ($totalTagihan === 0) {
                                    return 'lunas'; // Total 0 = lunas
                                }
                                
                                // Calculate total dibayar from pembayaran
                                $totalDibayar = 0;
                                foreach ($collection as $tagihan) {
                                    if ($tagihan->relationLoaded('pembayaran') && $tagihan->pembayaran->isNotEmpty()) {
                                        $totalDibayar += $tagihan->pembayaran->sum('nominal_bayar');
                                    }
                                }
                                
                                if ($totalDibayar >= $totalTagihan) {
                                    return 'lunas';
                                } elseif ($totalDibayar > 0) {
                                    return 'cicil';
                                } else {
                                    return 'belum';
                                }
                            };

                            $pendaftaranStatus = $getPaymentStatus($tagihanPendaftaran);
                            $daftarUlangStatus = $getPaymentStatus($tagihanDaftarUlang);
                            $udpStatus = $getPaymentStatus($tagihanUdp);
                            $statusPpdbSaatIni = (int) (optional($item->registration)->status ?? 0);
                            $isSudahSiswa = $statusPpdbSaatIni === \App\Models\Registration::STATUS_PESERTA_DIDIK;
                        @endphp

                        <td class="text-center w-16 min-w-[72px]">
                            @if($pendaftaranStatus === 'lunas')
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-green-500/70" title="Lunas">
                                    <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                                    </svg>
                                </span>
                            @elseif($pendaftaranStatus === 'cicil')
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-amber-500/70" title="Cicil Sebagian">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                </span>
                            @else
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-red-500/70" title="Belum Cicil">
                                    <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
                                    </svg>
                                </span>
                            @endif
                        </td>
                        <td class="text-center w-16 min-w-[72px]">
                            @if($daftarUlangStatus === 'lunas')
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-green-500/70" title="Lunas">
                                    <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                                    </svg>
                                </span>
                            @elseif($daftarUlangStatus === 'cicil')
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-amber-500/70" title="Cicil Sebagian">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                </span>
                            @else
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-red-500/70" title="Belum Cicil">
                                    <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
                                    </svg>
                                </span>
                            @endif
                        </td>
                        <td class="text-center w-16 min-w-[72px]">
                            @if($udpStatus === 'lunas')
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-green-500/70" title="Lunas">
                                    <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                                    </svg>
                                </span>
                            @elseif($udpStatus === 'cicil')
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-amber-500/70" title="Cicil Sebagian">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                </span>
                            @else
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-red-500/70" title="Belum Cicil">
                                    <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
                                    </svg>
                                </span>
                            @endif
                        </td>

                        <!-- AKSI -->
                        <td class="text-center">
                            <div
                                x-data="{
                                    open: false,
                                    triggerEl: null,
                                    x: 0,
                                    y: 0,
                                    placement: 'bottom',
                                    menuHeight: 132,
                                    menuWidth: 176,
                                    toggle($el) {
                                        this.triggerEl = $el;
                                        this.updatePosition();
                                        this.open = !this.open;
                                    },
                                    updatePosition() {
                                        if (!this.triggerEl) {
                                            return;
                                        }

                                        const rect = this.triggerEl.getBoundingClientRect();
                                        const spaceBelow = window.innerHeight - rect.bottom;

                                        this.placement = spaceBelow < this.menuHeight ? 'top' : 'bottom';
                                        this.x = rect.right - this.menuWidth;
                                        this.y = this.placement === 'top' ? rect.top - 8 : rect.bottom + 8;
                                    },
                                    menuStyle() {
                                        const left = Math.max(8, Math.min(this.x, window.innerWidth - this.menuWidth - 8));
                                        const top = this.placement === 'top'
                                            ? Math.max(8, this.y - this.menuHeight)
                                            : Math.min(this.y, window.innerHeight - this.menuHeight - 8);

                                        return `left:${left}px; top:${top}px;`;
                                    }
                                }"
                                @scroll.window="open && updatePosition()"
                                @resize.window="open && updatePosition()"
                                class="relative inline-block text-left"
                            >

                                <!-- BUTTON -->
                                <button @click="toggle($el)"
                                    class="btn-secondary px-3 py-1.5 text-[11px] flex items-center gap-1.5 mx-auto">
                                    <span>Aksi</span>
                                    <svg class="w-3 h-3 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <!-- DROPDOWN -->
                                <template x-teleport="body">
                                    <div x-show="open"
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="transform opacity-0 scale-95"
                                        x-transition:enter-end="transform opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="transform opacity-100 scale-100"
                                        x-transition:leave-end="transform opacity-0 scale-95"
                                        @click.outside="open = false"
                                        @keydown.escape.window="open = false"
                                        class="fixed w-44 bg-white border border-border rounded-lg shadow-hover z-[120] py-1.5 overflow-hidden"
                                        :style="menuStyle()">

                                        <!-- CETAK -->
                                        <button 
                                            @click="open = false; openModalPetugas({{ $item->id }})"
                                            class="flex w-full items-center gap-2.5 px-4 py-2 hover:bg-primary/5 hover:text-primary text-[11px] text-textPrimary transition-colors">
                                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                            </svg>
                                            Cetak Formulir
                                        </button>

                                        <!-- DETAIL -->
                                        <a href="#"
                                            onclick="event.preventDefault(); bukaPasswordModal('{{ route('pendaftaran.detail', $item->id) }}')"
                                            class="flex items-center gap-2.5 px-4 py-2 hover:bg-primary/5 hover:text-primary text-[11px] text-textPrimary transition-colors border-t border-gray-50">
                                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Detail
                                        </a>

                                        <!-- BIAYA -->
                                        <a href="#"
                                            onclick="event.preventDefault(); bukaPasswordModal('{{ route('pendaftaran.biaya', $item) }}')"
                                            class="flex items-center gap-2.5 px-4 py-2 hover:bg-primary/5 hover:text-primary text-[11px] text-textPrimary transition-colors border-t border-gray-50">
                                            <svg class="w-4 h-4 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                            Rincian Biaya
                                        </a>

                                        <button
                                            type="button"
                                            @click="open = false; openTerimaPesertaModal({
                                                url: @js(route('pendaftaran.terima-peserta', $item->id)),
                                                nama: @js($item->nama),
                                                nomorRegistrasi: @js(optional($item->registration)->nomor_registrasi ?? '-'),
                                                isSudahSiswa: @js($isSudahSiswa)
                                            })"
                                            class="flex w-full items-center gap-2.5 px-4 py-2 text-[11px] text-textPrimary transition-colors border-t border-gray-50 {{ $isSudahSiswa ? 'hover:bg-slate-50 hover:text-slate-700' : 'hover:bg-emerald-50 hover:text-emerald-700' }}"
                                        >
                                            <svg class="w-4 h-4 {{ $isSudahSiswa ? 'text-slate-500' : 'text-emerald-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ $isSudahSiswa ? 'Sudah Menjadi Peserta Didik' : 'Jadikan Peserta Didik' }}
                                        </button>

                                    </div>
                                </template>
                            </div>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="p-6 text-center text-slate-500">
                            Belum ada pendaftar
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- FOOTER PAGINATION -->
        <div class="p-4 border-t border-slate-200 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="text-sm text-slate-600 flex flex-col md:flex-row md:items-center gap-1 md:gap-3">
                <span>Menampilkan {{ $siswa->firstItem() }} - {{ $siswa->lastItem() }} dari {{ $siswa->total() }} data</span>
                <span class="inline-flex items-center rounded-full bg-green-50/80 text-green-700 border border-green-200 px-2.5 py-0.5 text-xs">P : Pendaftaran, DU : Daftar Ulang, UDP : Pengembangan</span>
                <span class="inline-flex items-center gap-3 rounded-full bg-slate-50 text-slate-700 border border-slate-200 px-2.5 py-0.5 text-xs">
                    <span class="inline-flex items-center gap-1">
                        <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-green-500/70">
                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                            </svg>
                        </span>
                        <span> = Lunas</span>
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-amber-500/70">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </span>
                        <span> = Tahap Cicil</span>
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-red-500/70">
                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
                            </svg>
                        </span>
                        <span> = Belum Cicil</span>
                    </span>
                </span>
            </div>

            <div class="flex w-full flex-col items-center gap-2 md:w-auto md:items-end">
                <form method="GET" class="flex items-center gap-2 text-xs text-slate-600">
                    @foreach(request()->except(['page', 'per_page']) as $key => $value)
                        @if(is_array($value))
                            @foreach($value as $item)
                                <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <label for="perPagePublicList">Tampilkan</label>
                    <select id="perPagePublicList" name="per_page" onchange="this.form.submit()" class="rounded border border-slate-300 px-2 py-1 text-xs">
                        @foreach([10,20,50,100] as $size)
                            <option value="{{ $size }}" {{ (int) request('per_page', $perPage ?? 10) === $size ? 'selected' : '' }}>{{ $size }}</option>
                        @endforeach
                    </select>
                    <span>data</span>
                </form>
                {{ $siswa->links() }}
            </div>
        </div>

    </div>

</div>

{{-- MODAL CETAK FORMULIR --}}
<div id="modalPetugas" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4 transition-all duration-300">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm p-8 relative transform transition-all duration-300">
        <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
            🖨️
        </div>
        <div class="text-center mb-6">
            <h3 class="text-xl font-bold text-slate-800">Cetak Formulir</h3>
            <p class="text-sm text-slate-500 mt-1">Masukkan nama panitia untuk mengunduh berkas pendaftaran.</p>
        </div>

        <form id="formCetakFormulirList" method="POST" action="{{ route('cetak.formulir.post') }}" target="cetakFormulirFrameList" onsubmit="submitCetakFormulirList()">
            @csrf
            <input type="hidden" name="siswa_id" id="modalSiswaId">

            <div class="mb-5">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nama Panitia</label>
                <input type="text" name="nama_panitia" required
                        class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500 transition-all"
                        placeholder="Contoh: Ahmad Fauzi">
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="closeModalPetugas()"
                    class="flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                    Batal
                </button>

                <button id="btnCetakFormulirList" type="submit"
                    class="flex-1 rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-blue-500/30 hover:bg-blue-700 transition-all">
                    Download PDF
                </button>
            </div>
        </form>
    </div>
</div>

<iframe name="cetakFormulirFrameList" id="cetakFormulirFrameList" class="hidden"></iframe>

<div id="modalPassword"
     onclick="closePasswordModal()"
     class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4 transition-all duration-300">

    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm p-8 relative transform transition-all duration-300" onclick="event.stopPropagation()">

        <button
            type="button"
            onclick="closePasswordModal()"
            class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 p-1"
            aria-label="Tutup modal"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
                🔐
            </div>
            <h3 class="text-xl font-bold text-slate-800">Verifikasi Panitia</h3>
            <p class="text-sm text-slate-500 mt-1">Masukkan password panitia untuk melanjutkan.</p>
        </div>

        <form method="POST" action="{{ route('verifikasi.password.panitia') }}">
            @csrf
            <input type="hidden" name="redirect_url" id="redirectUrl">

            <div class="mb-5">
                <input
                    type="password"
                    name="password"
                    class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500 transition-all"
                    placeholder="Masukkan password"
                    required
                >
            </div>

            <div class="flex gap-3">
                <button
                    type="button"
                    onclick="closePasswordModal()"
                    class="flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors"
                >
                    Batal
                </button>
                <button class="flex-1 rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-blue-500/30 hover:bg-blue-700 transition-all">
                    Verifikasi
                </button>
            </div>
        </form>
    </div>
</div>

<div id="modalPasswordKeuangan"
     onclick="closePasswordKeuanganModal()"
     class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4 transition-all duration-300">

    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm p-8 relative transform transition-all duration-300" onclick="event.stopPropagation()">

        <button
            type="button"
            onclick="closePasswordKeuanganModal()"
            class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 p-1"
            aria-label="Tutup modal"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
                📊
            </div>
            <h3 class="text-xl font-bold text-slate-800">Keuangan</h3>
            <p class="text-sm text-slate-500 mt-1">Verifikasi diperlukan untuk mengakses data keuangan.</p>
        </div>

        <form method="POST" action="{{ route('verifikasi.password.petugas.keuangan') }}">
            @csrf
            <input type="hidden" name="redirect_url" id="redirectUrlKeuangan">

            <div class="space-y-4 mb-6">
                <div>
                    <input
                        type="text"
                        name="nama"
                        class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500 transition-all"
                        placeholder="Nama petugas"
                        required
                    >
                </div>

                <div>
                    <input
                        type="password"
                        name="password"
                        class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500 transition-all"
                        placeholder="Password"
                        required
                    >
                </div>
            </div>

            <div class="flex gap-3">
                <button
                    type="button"
                    onclick="closePasswordKeuanganModal()"
                    class="flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors"
                >
                    Batal
                </button>
                <button class="flex-1 rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-blue-500/30 hover:bg-blue-700 transition-all">
                    Verifikasi
                </button>
            </div>
        </form>
    </div>
</div>

<div id="modalTerimaPeserta"
     onclick="closeTerimaPesertaModal()"
     class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4 transition-all duration-300">

    <div class="w-full max-w-lg overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl transform transition-all" onclick="event.stopPropagation()">
        <div class="relative overflow-hidden border-b border-slate-100 bg-gradient-to-r from-emerald-50 via-white to-sky-50 px-8 py-6">
            <div class="absolute -right-10 -top-10 h-24 w-24 rounded-full bg-emerald-100/60 blur-2xl"></div>
            <div class="flex items-start gap-4">
                <div class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-100 text-emerald-700 text-xl shadow-sm">
                    ✨
                </div>
                <div class="pr-8">
                    <h3 class="text-lg font-bold text-slate-800">
                        Jadikan Peserta Didik
                    </h3>
                    <p class="mt-0.5 text-xs text-slate-500 italic">Konfirmasi akhir untuk pendaftaran siswa baru.</p>
                </div>
            </div>
            
            <button
                type="button"
                onclick="closeTerimaPesertaModal()"
                class="absolute right-4 top-4 rounded-xl p-2 text-slate-400 transition hover:bg-white hover:text-slate-700 hover:shadow-sm"
                aria-label="Tutup modal"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="px-8 py-7">
            <div class="mb-6 rounded-2xl border border-emerald-100 bg-emerald-50/50 px-4 py-3.5">
                <p id="terimaPesertaConfirmText" class="text-sm leading-relaxed text-slate-700">
                    Apakah Anda yakin ingin menjadikan peserta ini sebagai peserta didik SD Muhammadiyah Wonorejo?
                </p>
            </div>

            <form id="formTerimaPeserta" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="terimaNamaPanitia" class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Nama Panitia</label>
                        <input
                            id="terimaNamaPanitia"
                            type="text"
                            name="nama_panitia"
                            class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                            placeholder="Ahmad Fauzi"
                            required
                        >
                    </div>

                    <div>
                        <label for="terimaPasswordPanitia" class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Password Panitia</label>
                        <input
                            id="terimaPasswordPanitia"
                            type="password"
                            name="password"
                            class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                            placeholder="••••••••"
                            required
                        >
                    </div>
                </div>

                <div class="flex gap-3 pt-4">
                    <button
                        type="button"
                        onclick="closeTerimaPesertaModal()"
                        class="flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                    >
                        Batal
                    </button>
                    <button id="btnSubmitTerimaPeserta" class="flex-1 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-500/30 transition hover:bg-emerald-700">
                        Ya, Konfirmasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="modalTerimaPesertaSuccess"
     onclick="closeTerimaPesertaSuccessModal()"
     class="fixed inset-0 bg-slate-900/55 backdrop-blur-[2px] hidden items-center justify-center z-50 p-4">

    <div class="w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl" onclick="event.stopPropagation()">
        <div class="px-6 py-5">
            <div class="mx-auto mb-3 inline-flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0" />
                </svg>
            </div>

            <h3 class="mb-1 text-center text-base font-semibold text-emerald-700">Perubahan Status Berhasil</h3>
            <p id="terimaPesertaSuccessText" class="mb-5 text-center text-sm leading-relaxed text-slate-700">Status peserta berhasil diperbarui menjadi Peserta Didik.</p>

            <div class="flex justify-end">
            <button
                type="button"
                onclick="closeTerimaPesertaSuccessModal(true)"
                class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700"
            >
                Tutup
            </button>
            </div>
        </div>
    </div>

</div>

<script>
let isCetakFormulirListSubmitting = false;
let terimaPesertaActionUrl = '';
let terimaPesertaNama = '';
let terimaPesertaNoRegistrasi = '-';

function openModalPetugas(id) {
    document.getElementById('modalSiswaId').value = id;
    document.getElementById('modalPetugas').classList.remove('hidden');
}

function closeModalPetugas() {
    document.getElementById('modalPetugas').classList.add('hidden');
}

function submitCetakFormulirList() {
    isCetakFormulirListSubmitting = true;

    const button = document.getElementById('btnCetakFormulirList');
    if (button) {
        button.disabled = true;
        button.classList.add('opacity-70', 'cursor-not-allowed');
    }

    return true;
}

document.addEventListener('DOMContentLoaded', function () {
    const frame = document.getElementById('cetakFormulirFrameList');
    const form = document.getElementById('formCetakFormulirList');
    const button = document.getElementById('btnCetakFormulirList');

    if (!frame) {
        return;
    }

    frame.addEventListener('load', function () {
        if (!isCetakFormulirListSubmitting) {
            return;
        }

        closeModalPetugas();
        showMiniToast('Download formulir dimulai');

        if (form) {
            form.reset();
        }

        if (button) {
            button.disabled = false;
            button.classList.remove('opacity-70', 'cursor-not-allowed');
        }

        isCetakFormulirListSubmitting = false;
    });
});

function bukaPasswordModal(url)
{
    document.getElementById('redirectUrl').value = url;

    const modal = document.getElementById('modalPassword');

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closePasswordModal()
{
    const modal = document.getElementById('modalPassword');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function bukaPasswordKeuanganModal(url)
{
    document.getElementById('redirectUrlKeuangan').value = url;

    const modal = document.getElementById('modalPasswordKeuangan');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closePasswordKeuanganModal()
{
    const modal = document.getElementById('modalPasswordKeuangan');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function openTerimaPesertaModal(payload)
{
    if (payload?.isSudahSiswa === true) {
        if (typeof window.showGlobalToast === 'function') {
            window.showGlobalToast('info', 'Peserta ini sudah menjadi peserta didik.');
        }
        return;
    }

    terimaPesertaActionUrl = payload?.url || '';
    terimaPesertaNama = payload?.nama || '-';
    terimaPesertaNoRegistrasi = payload?.nomorRegistrasi || '-';

    const text = `Apakah Anda yakin ingin menjadikan ${terimaPesertaNama} (No. Registrasi ${terimaPesertaNoRegistrasi}) sebagai peserta didik SD Muhammadiyah Wonorejo?`;
    const modal = document.getElementById('modalTerimaPeserta');
    const confirmText = document.getElementById('terimaPesertaConfirmText');

    if (confirmText) {
        confirmText.textContent = text;
    }

    const form = document.getElementById('formTerimaPeserta');
    if (form) {
        form.reset();
    }

    if (!modal) {
        return;
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeTerimaPesertaModal()
{
    const modal = document.getElementById('modalTerimaPeserta');
    if (!modal) {
        return;
    }

    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function openTerimaPesertaSuccessModal(message)
{
    const modal = document.getElementById('modalTerimaPesertaSuccess');
    const text = document.getElementById('terimaPesertaSuccessText');

    if (text) {
        text.textContent = message;
    }

    if (!modal) {
        return;
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeTerimaPesertaSuccessModal(reloadPage = false)
{
    const modal = document.getElementById('modalTerimaPesertaSuccess');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    if (reloadPage) {
        window.location.reload();
    }
}

function showMiniToast(message, type = 'success')
{
    const toastTypeMap = {
        success: 'success',
        warning: 'warning',
        info: 'info',
        danger: 'danger',
        error: 'danger',
    };

    if (typeof window.showGlobalToast === 'function') {
        window.showGlobalToast(toastTypeMap[type] || 'info', message, { duration: 1800 });
        return;
    }
}

function salinNoTelp(nomor)
{
    if (!nomor) {
        return;
    }

    navigator.clipboard.writeText(nomor)
        .then(function () {
            showMiniToast('Nomor telepon tersalin');
        })
        .catch(function () {
            try {
                const tempInput = document.createElement('input');
                tempInput.value = nomor;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);
                showMiniToast('Nomor telepon tersalin');
            } catch (error) {
                showMiniToast('Gagal menyalin nomor', 'error');
            }
        });
}

const filterForm = document.getElementById('filterForm');
const searchInput = document.getElementById('searchInput');
const orderSelect = document.getElementById('orderSelect');
const statusPpdbSelect = document.getElementById('statusPpdbSelect');
const hasilTesSelect = document.getElementById('hasilTesSelect');
const dateFromInput = document.getElementById('dateFromInput');
const dateToInput = document.getElementById('dateToInput');
let searchDebounceTimer = null;
const SEARCH_FOCUS_KEY = 'ppdb:list:restore-search-focus';

function isDateRangeValid()
{
    if (!dateFromInput || !dateToInput) {
        return true;
    }

    if (!dateFromInput.value || !dateToInput.value) {
        return true;
    }

    return dateFromInput.value <= dateToInput.value;
}

function syncDateBounds()
{
    if (!dateFromInput || !dateToInput) {
        return;
    }

    dateFromInput.max = dateToInput.value || '';
    dateToInput.min = dateFromInput.value || '';
}

function submitIfDateRangeValid()
{
    if (!filterForm) {
        return;
    }

    if (!isDateRangeValid()) {
        showMiniToast('Rentang tanggal tidak valid', 'error');
        return;
    }

    filterForm.submit();
}

if (searchInput && sessionStorage.getItem(SEARCH_FOCUS_KEY) === '1') {
    sessionStorage.removeItem(SEARCH_FOCUS_KEY);

    requestAnimationFrame(function () {
        searchInput.focus();

        const len = searchInput.value.length;
        searchInput.setSelectionRange(len, len);
    });
}

if (filterForm && searchInput) {
    searchInput.addEventListener('input', function () {
        clearTimeout(searchDebounceTimer);

        searchDebounceTimer = setTimeout(function () {
            sessionStorage.setItem(SEARCH_FOCUS_KEY, '1');
            filterForm.submit();
        }, 350);
    });
}

if (filterForm && orderSelect) {
    orderSelect.addEventListener('change', function () {
        submitIfDateRangeValid();
    });
}

if (filterForm && statusPpdbSelect) {
    statusPpdbSelect.addEventListener('change', function () {
        submitIfDateRangeValid();
    });
}

if (filterForm && hasilTesSelect) {
    hasilTesSelect.addEventListener('change', function () {
        submitIfDateRangeValid();
    });
}

if (filterForm && dateFromInput) {
    dateFromInput.addEventListener('change', function () {
        syncDateBounds();
        submitIfDateRangeValid();
    });
}

if (filterForm && dateToInput) {
    dateToInput.addEventListener('change', function () {
        syncDateBounds();
        submitIfDateRangeValid();
    });
}

syncDateBounds();

const formTerimaPeserta = document.getElementById('formTerimaPeserta');
if (formTerimaPeserta) {
    formTerimaPeserta.addEventListener('submit', async function (event) {
        event.preventDefault();

        if (!terimaPesertaActionUrl) {
            showMiniToast('Aksi tidak valid', 'error');
            return;
        }

        const formData = new FormData(formTerimaPeserta);
        const namaPanitia = (formData.get('nama_panitia') || '').toString().trim();
        const password = (formData.get('password') || '').toString();

        if (!namaPanitia || !password) {
            showMiniToast('Nama panitia dan password wajib diisi', 'error');
            return;
        }

        const submitButton = document.getElementById('btnSubmitTerimaPeserta');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.classList.add('opacity-70', 'cursor-not-allowed');
        }

        try {
            const response = await fetch(terimaPesertaActionUrl, {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': formData.get('_token'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    nama_panitia: namaPanitia,
                    password,
                }),
            });

            const data = await response.json().catch(() => ({}));
            if (!response.ok || data.success === false) {
                showMiniToast(data.message || 'Gagal memproses penerimaan peserta', 'error');
                return;
            }

            closeTerimaPesertaModal();
            openTerimaPesertaSuccessModal(
                data.message || `Berhasil menjadikan ${terimaPesertaNama} (No. Registrasi ${terimaPesertaNoRegistrasi}) sebagai peserta didik SD Muhammadiyah Wonorejo.`
            );
        } catch (error) {
            showMiniToast('Terjadi kesalahan saat memproses data', 'error');
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.classList.remove('opacity-70', 'cursor-not-allowed');
            }
        }
    });
}
</script>

@endsection