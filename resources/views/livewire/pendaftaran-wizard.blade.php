<div class="max-w-6xl mx-auto px-6 pb-10">

    @if ($showValidationModal && $errors->any())
        <div x-data="{ open: @entangle('showValidationModal') }" x-show="open" x-transition x-cloak
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-lg max-w-md w-full p-6">
                <h2 class="text-lg font-semibold text-red-600 mb-3">Data Pendaftaran Belum Lengkap</h2>

                <p class="text-sm text-slate-600 mb-4">
                    Silakan periksa kembali form pendaftaran. Beberapa data wajib belum diisi dengan benar.
                </p>

                <ul class="list-disc list-inside text-sm text-red-600 space-y-1 mb-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

                <div class="text-right">
                    <button type="button" @click="open = false; $wire.showValidationModal = false"
                        class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                        OK
                    </button>
                </div>
            </div>
        </div>
    @endif


    <form wire:submit.prevent="prepareSubmit" class="space-y-8">

        {{-- HEADER --}}
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-slate-800">
                {{ $isEditMode ? 'Formulir Edit Data Pendaftar' : 'Formulir Pendaftaran Calon Peserta Didik' }}
            </h1>
            <p class="text-sm text-slate-500">
                {{ $isEditMode ? 'Perbarui data menggunakan form yang sama seperti pendaftaran.' : 'Diisi dengan data yang benar dan valid.' }}
            </p>
        </div>

        {{-- ================= A. DATA UMUM ================= --}}
        <div class="card relative z-40">
            <h2 class="font-heading font-bold text-lg text-primary mb-5 border-b border-border pb-2">A. Data Umum</h2>

            <div class="grid md:grid-cols-3 gap-5">

                {{-- TANGGAL DAFTAR --}}
                <div>
                    <label class="label">
                        Tanggal Daftar <span class="text-red-500">*</span>
                    </label>
                    {{-- Tidak perlu request tiap ketikan, pakai defer --}}
                    <input type="date" wire:model.blur="tanggal_daftar"
                        class="input @error('tanggal_daftar') border-red-500 @enderror">
                    @error('tanggal_daftar')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- TAHUN AJARAN --}}
                <div>
                    <label class="label">Tahun Ajaran</label>
                    {{-- Readonly, tidak perlu update ke server --}}
                    <input type="text" value="{{ $tahun_ajaran_nama ?? 'belum ditentukan admin' }}" readonly
                        class="input bg-slate-100 cursor-not-allowed">
                </div>

                {{-- VOUCHER --}}
                <div>
                    <label class="label">Voucher</label>
                    @php
                        $selectedVoucher = $vouchers->firstWhere('id', (int) $voucher_id);
                        $selectedLabel = $selectedVoucher
                            ? trim(($selectedVoucher->kode ?? '') . ' — ' . ($selectedVoucher->nama ?? ''))
                            : 'Pilih Voucher (Opsional)';
                        $voucherCards = [
                            ['accent' => 'border-emerald-200', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700'],
                            ['accent' => 'border-blue-200', 'bg' => 'bg-blue-50', 'text' => 'text-blue-700'],
                            ['accent' => 'border-amber-200', 'bg' => 'bg-amber-50', 'text' => 'text-amber-700'],
                            ['accent' => 'border-rose-200', 'bg' => 'bg-rose-50', 'text' => 'text-rose-700'],
                            ['accent' => 'border-slate-200', 'bg' => 'bg-slate-50', 'text' => 'text-slate-700'],
                        ];
                    @endphp

                    <div x-data="{ open: false }" class="relative z-50" @click.outside="open = false">
                        <button type="button" @click="open = !open" @keydown.escape.window="open = false"
                            class="input flex items-center justify-between gap-2 text-left @error('voucher_id') border-red-500 @enderror {{ $voucher_expired ? 'bg-slate-100 cursor-not-allowed' : '' }}"
                            {{ $voucher_expired ? 'disabled' : '' }}>
                            <span class="truncate">{{ $selectedLabel }}</span>
                            <svg class="h-4 w-4 text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''"
                                viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.512a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06Z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>

                        <div x-show="open" x-cloak x-transition
                            class="absolute z-[999] mt-2 w-full rounded-2xl border border-slate-200 bg-white p-3 shadow-xl">
                            @if($vouchers->count() === 0)
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-500">
                                    Voucher tidak tersedia.
                                </div>
                            @else
                                <div class="max-h-64 overflow-y-auto space-y-2 pr-1">
                                    <button type="button" @click="$wire.set('voucher_id', null); open = false"
                                        class="w-full rounded-xl border border-dashed border-slate-200 bg-white px-4 py-3 text-left text-sm text-slate-500 hover:bg-slate-50">
                                        Tanpa voucher
                                    </button>

                                    @foreach($vouchers as $index => $v)
                                        @php
                                            $cardStyle = $voucherCards[$index % count($voucherCards)];
                                            $now = \Carbon\Carbon::now();
                                            $startDate = $v->tanggal_mulai ? \Carbon\Carbon::parse($v->tanggal_mulai)->startOfDay() : null;
                                            $endDate = $v->tanggal_selesai ? \Carbon\Carbon::parse($v->tanggal_selesai)->endOfDay() : null;
                                            $quotaRemaining = is_null($v->maks_penggunaan)
                                                ? null
                                                : max(0, (int) $v->maks_penggunaan - (int) $v->digunakan);
                                            $isPrelaunch = $startDate && $now->lt($startDate);
                                            $isExpired = $endDate && $now->gt($endDate);
                                            $isQuotaEmpty = !is_null($quotaRemaining) && $quotaRemaining <= 0;
                                            $isActive = $v->aktif &&
                                                !$isPrelaunch && !$isExpired && !$isQuotaEmpty;
                                            $daysToStart = $isPrelaunch ? $now->startOfDay()->diffInDays($startDate) : null;
                                            $statusBadgeText = $isPrelaunch
                                                ? 'Berlaku ' . $daysToStart . ' hari lagi'
                                                : ($isActive
                                                    ? 'Voucher aktif'
                                                    : ($isQuotaEmpty
                                                        ? 'Kuota habis'
                                                        : ($isExpired ? 'Periode habis' : 'Tidak aktif')));
                                            $statusBadgeClass = $isQuotaEmpty
                                                ? 'bg-red-100 text-red-700 border-red-200'
                                                : ($isActive
                                                    ? 'bg-emerald-100 text-emerald-700 border-emerald-200'
                                                    : 'bg-slate-100 text-slate-600 border-slate-200');
                                            $cardBgClass = $isQuotaEmpty
                                                ? 'bg-red-50'
                                                : ($isActive
                                                    ? 'bg-emerald-50'
                                                    : 'bg-slate-50');
                                        @endphp
                                        <button type="button" @click="$wire.set('voucher_id', {{ $v->id }}); open = false"
                                            class="w-full rounded-2xl border {{ $cardStyle['accent'] }} {{ $cardBgClass }} px-4 py-3 text-left transition hover:shadow-md"
                                            {{ $isActive ? '' : 'disabled' }}>
                                            <div class="flex items-start justify-between gap-3">
                                                <div>
                                                    <p class="text-sm font-semibold text-slate-800">{{ $v->kode ?? '-' }}</p>
                                                </div>
                                                <span
                                                    class="rounded-full border border-white/60 bg-white/70 px-2 py-0.5 text-[10px] font-semibold {{ $cardStyle['text'] }}">
                                                    {{ ui_label($v->jenis_biaya ?? '-') }}
                                                </span>
                                            </div>
                                            <div class="mt-2 flex items-center justify-between text-[11px]">
                                                <span
                                                    class="inline-flex items-center rounded-full border px-2 py-0.5 {{ $statusBadgeClass }}">
                                                    {{ $statusBadgeText }}
                                                </span>
                                                @if($isActive)
                                                    <span class="text-slate-500">
                                                        Kuota: {{ is_null($quotaRemaining) ? 'Unlimited' : $quotaRemaining }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="mt-2 flex items-center justify-between text-xs">
                                                <span class="font-semibold text-slate-700">
                                                    Potongan Rp
                                                    {{ number_format((int) ($v->diskon_nominal ?? 0), 0, ',', '.') }}
                                                </span>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <input type="hidden" wire:model.blur="voucher_id">

                    {{-- Info Diskon --}}
                    @if($voucher_label)
                        <div class="mt-2 p-3 rounded-lg
                                    {{ $voucher_expired ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700' }}">
                            <p class="text-sm font-medium">{{ $voucher_label }}</p>
                        </div>
                    @endif

                    @error('voucher_id')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        </div>

        {{-- ================= B. IDENTITAS SISWA ================= --}}
        <div class="card">
            <h2 class="font-heading font-bold text-lg text-primary mb-5 border-b border-border pb-2">B. Identitas</h2>

            <div class="grid md:grid-cols-2 gap-5">

                {{-- NAMA LENGKAP --}}
                <div class="md:col-span-2">
                    <label class="label">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    {{-- Tidak perlu update tiap ketikan, pakai defer --}}
                    <input wire:model.blur="nama_siswa" class="input @error('nama_siswa') border-red-500 @enderror">
                    @error('nama_siswa')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- JENIS KELAMIN --}}
                <div>
                    <label class="label">
                        Jenis Kelamin <span class="text-red-500">*</span>
                    </label>
                    <select wire:model.blur="jenis_kelamin"
                        class="input @error('jenis_kelamin') border-red-500 @enderror">
                        <option value="">Pilih</option>
                        <option value="laki-laki">Laki-laki</option>
                        <option value="perempuan">Perempuan</option>
                    </select>
                    @error('jenis_kelamin')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- NISN --}}
                <div>
                    <label class="label">NISN</label>
                    <input wire:model.blur="nisn" type="text" inputmode="numeric" maxlength="10" placeholder="10 digit angka (Opsional)"
                        class="input @error('nisn') border-red-500 @enderror">

                    @error('nisn')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- NIK --}}
                <div>
                    <label class="label">NIK <span class="text-red-500">*</span></label>
                    <input wire:model.blur="nik" type="text" inputmode="numeric" maxlength="16" placeholder="16 digit"
                        class="input @error('nik') border-red-500 @enderror">

                    @if(!$errors->has('nik'))
                        @if(strlen((string) $nik) > 0 && strlen((string) $nik) < 16)
                            <p class="text-amber-600 text-xs mt-1">NIK
                                harus 16 digit.</p>
                        @endif
                    @endif

                    @error('nik')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror

                    @if($nikTersedia)
                        <p class="text-green-600 text-xs mt-1">NIK tersedia</p>
                    @endif
                </div>

                {{-- NO KK --}}
                <div>
                    <label class="label">No KK <span class="text-red-500">*</span></label>
                    <input wire:model.blur="no_kk" type="text" inputmode="numeric" maxlength="16" placeholder="16 digit"
                        class="input @error('no_kk') border-red-500 @enderror">

                    @if(!$errors->has('no_kk'))
                        @if(strlen((string) $no_kk) > 0 && strlen((string) $no_kk) < 16)
                            <p class="text-amber-600 text-xs mt-1">No
                                KK harus 16 digit.</p>
                        @endif
                    @endif

                    @error('no_kk')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- TEMPAT LAHIR --}}
                <div>
                    <label class="label">Tempat Lahir <span class="text-red-500">*</span></label>
                    <input wire:model.blur="tempat_lahir" class="input @error('tempat_lahir') border-red-500 @enderror">
                    @error('tempat_lahir')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- TANGGAL LAHIR --}}
                <div>
                    <label class="label">Tanggal Lahir <span class="text-red-500">*</span></label>
                    <input type="date" wire:model.blur="tanggal_lahir" max="{{ $this->getMinBirthDate()->format('Y-m-d') }}"
                        class="input @error('tanggal_lahir') border-red-500 @enderror">

                    {{-- Hint batas umur --}}
                    <p class="text-slate-400 text-xs mt-1">
                        Maks. lahir: {{ $this->getMinBirthDate()->format('d/m/Y') }}
                        (usia minimal 6 tahun per 30 Juni {{ $this->getCutoffYear() }})
                    </p>

                    {{-- Live feedback usia kurang --}}
                    @if($umurKurang)
                        <p class="text-red-600 text-xs mt-1 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            Usia belum mencukupi
                        </p>
                    @endif

                    {{-- Live feedback usia cukup --}}
                    @if($umurCukup && !$errors->has('tanggal_lahir'))
                        <p class="text-green-600 text-xs mt-1 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            Usia mencukupi
                        </p>
                    @endif

                    {{-- Error dari validasi Livewire (saat submit) --}}
                    @if(!$umurKurang)
                        @error('tanggal_lahir')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    @endif
                </div>

                {{-- AKTA --}}
                <div x-data="{ showAktaModal: false }">
                    <label class="label flex items-center gap-2">
                        No Akta Lahir
                        <button type="button" @click="showAktaModal = true"
                            class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-100 text-blue-600 hover:bg-blue-200 transition"
                            title="Lihat contoh Akta Lahir">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </label>
                    <input wire:model.blur="akta_no" class="input">

                    {{-- Modal Contoh Akta Lahir --}}
                    <div x-show="showAktaModal" x-transition.opacity x-cloak @click.self="showAktaModal = false"
                        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                        <div x-transition x-show="showAktaModal"
                            class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 overflow-hidden">
                            {{-- Header --}}
                            <div
                                class="flex items-center justify-between bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                                <h3 class="text-lg font-semibold text-white">Contoh Akta Lahir</h3>
                                <button type="button" @click="showAktaModal = false"
                                    class="text-white hover:text-gray-200 transition">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            {{-- Content --}}
                            <div class="p-6">
                                <img src="{{ asset('images/contoh-aktalahir.jpg') }}" alt="Contoh Akta Lahir"
                                    class="w-full rounded-lg border border-gray-200">
                            </div>

                            {{-- Footer --}}
                            <div class="bg-gray-50 px-6 py-3 border-t border-gray-200 flex justify-end">
                                <button type="button" @click="showAktaModal = false"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- AGAMA --}}
                <div>
                    <label class="label">Agama</label>
                    {{-- Static, readonly --}}
                    <input value="Islam" disabled class="input bg-slate-100 cursor-not-allowed">
                </div>

                {{-- KEWARGANEGARAAN --}}
                <div>
                    <label class="label">Kewarganegaraan</label>
                    <input value="Indonesia" disabled class="input bg-slate-100 cursor-not-allowed">
                </div>

                {{-- BERKEBUTUHAN KHUSUS --}}
                <div>
                    <label class="label">Berkebutuhan Khusus</label>
                    <select wire:model.blur="berkebutuhan_khusus" class="input">
                        <option value="Tidak">Tidak</option>
                        <option value="Ya">Ya</option>
                    </select>
                </div>

                @if($berkebutuhan_khusus === 'Ya')
                    <div>
                        <label class="label">Jenis Kebutuhan Khusus <span class="text-red-500">*</span></label>
                        <select wire:model.blur="jenis_kebutuhan_khusus"
                            class="input @error('jenis_kebutuhan_khusus') border-red-500 @enderror">
                            <option value="">Pilih</option>
                            <option value="netra">Netra</option>
                            <option value="rungu">Rungu</option>
                            <option value="grahita ringan">Grahita Ringan</option>
                            <option value="grahita sedang">Grahita Sedang</option>
                            <option value="daksa ringan">Daksa Ringan</option>
                            <option value="daksa sedang">Daksa Sedang</option>
                            <option value="laras">Laras</option>
                            <option value="wicara">Wicara</option>
                            <option value="hyperaktif">Hyperaktif</option>
                            <option value="cerdas istimewa">Cerdas Istimewa</option>
                            <option value="bakat istimewa">Bakat Istimewa</option>
                            <option value="kesulitan belajar">Kesulitan Belajar</option>
                            <option value="indigo">Indigo</option>
                            <option value="down syndrome">Down Syndrome</option>
                            <option value="autis">Autis</option>
                            <option value="Lain-lain">Lain-lain</option>
                        </select>
                        @error('jenis_kebutuhan_khusus')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    @if($jenis_kebutuhan_khusus === 'Lain-lain')
                        <div>
                            <label class="label">Kebutuhan Khusus Lainnya <span class="text-red-500">*</span></label>
                            <input wire:model.blur="kebutuhan_khusus_lainnya"
                                class="input @error('kebutuhan_khusus_lainnya') border-red-500 @enderror">
                            @error('kebutuhan_khusus_lainnya')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                @endif



                {{-- TINGGAL BERSAMA --}}
                <div>
                    <label class="label">Tinggal Bersama <span class="text-red-500">*</span></label>
                    {{-- Live karena mempengaruhi rendering field wali --}}
                    <select wire:model.blur="tinggal_bersama"
                        class="input @error('tinggal_bersama') border-red-500 @enderror">
                        <option value="">Pilih</option>
                        <option value="orang_tua">Orang Tua</option>
                        <option value="wali">Wali</option>
                    </select>
                    @error('tinggal_bersama')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- WALI --}}
                @if($tinggal_bersama === 'wali')
                    <div>
                        <label class="label">No HP Wali <span class="text-red-500">*</span></label>
                        <input wire:model.blur="hp_wali" type="text" inputmode="numeric" maxlength="15"
                            class="input @error('hp_wali') border-red-500 @enderror">

                        @if(!$errors->has('hp_wali'))
                            @if(strlen((string) $hp_wali) > 0 && (strlen((string) $hp_wali) < 6 || strlen((string) $hp_wali) > 15))
                                <p class="text-amber-600 text-xs mt-1">No HP wali harus 6-15 digit.</p>
                            @endif
                        @endif

                        @error('hp_wali')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label">Nama Wali <span class="text-red-500">*</span></label>
                        <input wire:model.blur="wali_nama" class="input @error('wali_nama') border-red-500 @enderror">
                        @error('wali_nama')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label">Hubungan dengan Peserta Didik <span class="text-red-500">*</span></label>
                        <select wire:model.blur="wali_hubungan"
                            class="input @error('wali_hubungan') border-red-500 @enderror">
                            <option value="">Pilih</option>
                            <option value="Kakek">Kakek</option>
                            <option value="Nenek">Nenek</option>
                            <option value="Paman">Paman</option>
                            <option value="Bibi">Bibi</option>
                            <option value="Saudara">Saudara</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                        @error('wali_hubungan')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    @if($wali_hubungan === 'Lainnya')
                        <div>
                            <label class="label">Hubungan Wali (Lainnya) <span class="text-red-500">*</span></label>
                            <input wire:model.blur="wali_hubungan_lainnya"
                                class="input @error('wali_hubungan_lainnya') border-red-500 @enderror">
                            @error('wali_hubungan_lainnya')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <div>
                        <label class="label">Pendidikan Terakhir Wali</label>
                        <select wire:model.blur="wali_pendidikan"
                            class="input @error('wali_pendidikan') border-red-500 @enderror">
                            <option value="">Pilih</option>
                            <option>Tidak Sekolah</option>
                            <option>SD</option>
                            <option>SMP</option>
                            <option>SMA/K</option>
                            <option>D3</option>
                            <option>S1</option>
                            <option>S2</option>
                            <option>S3</option>
                        </select>
                        @error('wali_pendidikan')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label">Pekerjaan Wali</label>
                        <select wire:model.blur="wali_pekerjaan"
                            class="input @error('wali_pekerjaan') border-red-500 @enderror">
                            <option value="">Pilih</option>
                            <option>Tidak Bekerja</option>
                            <option>Nelayan</option>
                            <option>Petani</option>
                            <option>Peternak</option>
                            <option>PNS/TNI/Polri</option>
                            <option>Karyawan Swasta</option>
                            <option>Pedagang Kecil</option>
                            <option>Pedagang Besar</option>
                            <option>Wiraswasta</option>
                            <option>Wirausaha</option>
                            <option>Buruh</option>
                            <option>Pensiunan</option>
                            <option>Tenaga Kerja Indonesia</option>
                            <option>Karyawan BUMN</option>
                            <option>Tidak dapat diterapkan</option>
                            <option>Sudah Meninggal</option>
                            <option>Lainnya</option>
                        </select>
                        @error('wali_pekerjaan')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    @if($wali_pekerjaan === 'Lainnya')
                        <div>
                            <label class="label">Pekerjaan Wali (Lainnya) <span class="text-red-500">*</span></label>
                            <input wire:model.blur="wali_pekerjaan_lainnya"
                                class="input @error('wali_pekerjaan_lainnya') border-red-500 @enderror">
                            @error('wali_pekerjaan_lainnya')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <div>
                        <label class="label">Penghasilan Wali</label>
                        <select wire:model.blur="wali_penghasilan"
                            class="input @error('wali_penghasilan') border-red-500 @enderror">
                            <option value="">Pilih</option>
                            <option>Kurang dari Rp. 500.000</option>
                            <option>Rp. 500.000 - Rp. 999.000</option>
                            <option>Rp. 1.000.000 - Rp. 1.999.999</option>
                            <option>Rp. 2.000.000 - Rp. 4.999.999</option>
                            <option>Rp. 5.000.000 - Rp. 20.000.000</option>
                            <option>Lebih dari Rp. 20.000.000</option>
                            <option>Tidak Berpenghasilan</option>
                        </select>
                        @error('wali_penghasilan')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label">NIK Wali <span class="text-red-500">*</span></label>
                        <input wire:model.blur="wali_nik" type="text" inputmode="numeric" maxlength="16"
                            placeholder="16 digit" class="input @error('wali_nik') border-red-500 @enderror">

                        @if(!$errors->has('wali_nik'))
                            @if(strlen((string) $wali_nik) > 0 && strlen((string) $wali_nik) < 16)
                                <p class="text-amber-600 text-xs mt-1">NIK wali harus 16 digit.</p>
                            @endif
                        @endif

                        @error('wali_nik')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label">Tahun Lahir Wali <span class="text-red-500">*</span></label>
                        <input wire:model.blur="wali_tahun_lahir" type="text" inputmode="numeric" maxlength="4"
                            pattern="[0-9]*" min="1945" placeholder="Contoh: 1979"
                            class="input @error('wali_tahun_lahir') border-red-500 @enderror">
                        @if($wali_tahun_lahir !== null && $wali_tahun_lahir !== '' && strlen((string) $wali_tahun_lahir) > 4)
                            <p class="text-amber-600 text-xs mt-1">Tahun lahir wali maksimal 4 digit.</p>
                        @endif
                        @error('wali_tahun_lahir')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                {{-- TRANSPORTASI --}}
                <div>
                    <label class="label">Moda Transportasi <span class="text-red-500">*</span></label>
                    <select wire:model.blur="transportasi"
                        class="input @error('transportasi') border-red-500 @enderror">
                        <option value="">Pilih</option>
                        <option value="jalan_kaki">Jalan Kaki</option>
                        <option value="sepeda">Sepeda</option>
                        <option value="antar_jemput">Antar Jemput</option>
                    </select>
                    @error('transportasi')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>



            </div>
        </div>

        {{-- ================= C. ALAMAT ================= --}}
        <div class="card">
            <h2 class="font-heading font-bold text-lg text-primary mb-5 border-b border-border pb-2">C. Alamat</h2>

            {{-- ALAMAT LENGKAP --}}
            <div class="mb-4">
                <label class="label">Alamat Lengkap <span class="text-red-500">*</span></label>
                {{-- Pakai defer, tidak perlu update server tiap ketikan --}}
                <textarea wire:model.blur="alamat" class="input @error('alamat') border-red-500 @enderror"></textarea>
                @error('alamat')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div wire:key="wilayah-picker-{{ $wilayahPickerKey }}" x-data="emsifaWilayahPicker({
                provinsi: $wire.entangle('provinsi'),
                kabupaten: $wire.entangle('kabupaten'),
                kecamatan: $wire.entangle('kecamatan'),
                kelurahan: $wire.entangle('kelurahan'),
                kodePos: $wire.entangle('kode_pos')
            })" x-init="init()" class="grid md:grid-cols-4 gap-5">
                <div class="md:col-span-4 rounded-lg border border-amber-200 bg-amber-50 p-3" x-show="useManualInput"
                    x-cloak>
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <p class="text-xs font-semibold text-amber-800">Mode Input Manual Wilayah Aktif</p>
                            <p class="text-xs text-amber-700">API referensi wilayah sedang bermasalah. Anda tetap bisa
                                melanjutkan
                                pengisian dengan input manual.</p>
                        </div>
                        <button type="button" class="text-xs font-medium text-amber-800 hover:text-amber-900"
                            @click="disableManualInput()">
                            Coba pakai dropdown API lagi
                        </button>
                    </div>
                </div>

                <div class="md:col-span-4 grid md:grid-cols-4 gap-5" x-show="useManualInput" x-cloak>
                    <div>
                        <label class="label">Provinsi <span class="text-red-500">*</span></label>
                        <input type="text" x-model="provinsiName"
                            @input="selectedProvinceId = null; kabupatenName = ''; kecamatanName = ''; kelurahanName = ''; selectedRegencyId = null; selectedDistrictId = null; regencies = []; districts = []; villages = [];"
                            placeholder="Isi provinsi manual" class="input @error('provinsi') border-red-500 @enderror">
                        @error('provinsi')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label">Kabupaten <span class="text-red-500">*</span></label>
                        <input type="text" x-model="kabupatenName"
                            @input="selectedRegencyId = null; kecamatanName = ''; kelurahanName = ''; selectedDistrictId = null; districts = []; villages = [];"
                            placeholder="Isi kabupaten manual"
                            class="input @error('kabupaten') border-red-500 @enderror">
                        @error('kabupaten')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label">Kecamatan <span class="text-red-500">*</span></label>
                        <input type="text" x-model="kecamatanName"
                            @input="selectedDistrictId = null; kelurahanName = ''; villages = [];"
                            placeholder="Isi kecamatan manual"
                            class="input @error('kecamatan') border-red-500 @enderror">
                        @error('kecamatan')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label">Desa/Kelurahan <span class="text-red-500">*</span></label>
                        <input type="text" x-model="kelurahanName" @blur="onKelurahanChange()"
                            placeholder="Isi desa/kelurahan manual"
                            class="input @error('kelurahan') border-red-500 @enderror">
                        @error('kelurahan')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="md:col-span-4" x-show="!useManualInput" x-cloak>
                    <button type="button" class="text-xs text-primary font-medium hover:text-primary/80"
                        @click="enableManualInput('Input manual diaktifkan oleh pengguna.')">
                        Wilayah bermasalah? Klik untuk isi manual
                    </button>
                </div>

                {{-- PROVINSI --}}
                <div x-show="!useManualInput" x-cloak>
                    <label class="label flex items-center gap-2">
                        Provinsi <span class="text-red-500">*</span>
                        <svg x-show="isLoadingProvinces" class="animate-spin h-3.5 w-3.5 text-slate-500"
                            viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                    </label>
                    <div class="relative" @keydown.escape="openProvinsi = false">
                        <button type="button" @click="toggleDropdown('provinsi')" :disabled="isLoadingProvinces"
                            class="input w-full text-left flex items-center justify-between @error('provinsi') border-red-500 @enderror"
                            :class="isLoadingProvinces ? 'bg-slate-100 cursor-not-allowed' : ''">
                            <span class="truncate" :class="provinsiName ? 'text-slate-900' : 'text-slate-400'"
                                x-text="provinsiName || 'Pilih Provinsi'"></span>
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="openProvinsi" x-transition @click.outside="openProvinsi = false"
                            class="absolute z-30 mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-lg">
                            <div class="p-2 border-b border-slate-100">
                                <input type="text" x-ref="searchProvinsiInput" x-model.debounce.200ms="searchProvinsi"
                                    @keydown.enter.prevent placeholder="Cari provinsi" class="input w-full">
                            </div>
                            <ul class="max-h-56 overflow-y-auto py-1">
                                <template x-for="item in filteredProvinces()" :key="item.id">
                                    <li>
                                        <button type="button" @click="selectProvinsi(item)"
                                            class="w-full px-3 py-2 text-left text-sm hover:bg-slate-100"
                                            :class="provinsiName === item.name ? 'bg-slate-100 font-medium' : ''"
                                            x-text="item.name"></button>
                                    </li>
                                </template>
                                <li x-show="filteredProvinces().length === 0" class="px-3 py-2 text-sm text-slate-500">
                                    Data tidak
                                    ditemukan.</li>
                            </ul>
                        </div>
                    </div>
                    @error('provinsi')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- KABUPATEN --}}
                <div x-show="!useManualInput" x-cloak>
                    <label class="label flex items-center gap-2">
                        Kabupaten <span class="text-red-500">*</span>
                        <svg x-show="isLoadingRegencies" class="animate-spin h-3.5 w-3.5 text-slate-500"
                            viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                    </label>
                    <div class="relative" @keydown.escape="openKabupaten = false">
                        <button type="button" @click="toggleDropdown('kabupaten')" :disabled="!provinsiName"
                            class="input w-full text-left flex items-center justify-between @error('kabupaten') border-red-500 @enderror"
                            :class="!provinsiName ? 'bg-slate-100 cursor-not-allowed' : ''">
                            <span class="truncate" :class="kabupatenName ? 'text-slate-900' : 'text-slate-400'"
                                x-text="isLoadingRegencies ? 'Memuat kabupaten...' : (kabupatenName || 'Pilih Kabupaten')"></span>
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="openKabupaten" x-transition @click.outside="openKabupaten = false"
                            class="absolute z-30 mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-lg">
                            <div class="p-2 border-b border-slate-100">
                                <input type="text" x-ref="searchKabupatenInput" x-model.debounce.200ms="searchKabupaten"
                                    @keydown.enter.prevent placeholder="Cari kabupaten" class="input w-full">
                            </div>
                            <ul class="max-h-56 overflow-y-auto py-1">
                                <li x-show="isLoadingRegencies" class="px-3 py-2 text-sm text-slate-500">Memuat data...
                                </li>
                                <template x-for="item in filteredRegencies()" :key="item.id">
                                    <li>
                                        <button type="button" @click="selectKabupaten(item)"
                                            class="w-full px-3 py-2 text-left text-sm hover:bg-slate-100"
                                            :class="kabupatenName === item.name ? 'bg-slate-100 font-medium' : ''"
                                            x-text="item.name"></button>
                                    </li>
                                </template>
                                <li x-show="filteredRegencies().length === 0" class="px-3 py-2 text-sm text-slate-500">
                                    Data tidak
                                    ditemukan.</li>
                            </ul>
                        </div>
                    </div>
                    @error('kabupaten')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- KECAMATAN --}}
                <div x-show="!useManualInput" x-cloak>
                    <label class="label flex items-center gap-2">
                        Kecamatan <span class="text-red-500">*</span>
                        <svg x-show="isLoadingDistricts" class="animate-spin h-3.5 w-3.5 text-slate-500"
                            viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                    </label>
                    <div class="relative" @keydown.escape="openKecamatan = false">
                        <button type="button" @click="toggleDropdown('kecamatan')" :disabled="!kabupatenName"
                            class="input w-full text-left flex items-center justify-between @error('kecamatan') border-red-500 @enderror"
                            :class="!kabupatenName ? 'bg-slate-100 cursor-not-allowed' : ''">
                            <span class="truncate" :class="kecamatanName ? 'text-slate-900' : 'text-slate-400'"
                                x-text="isLoadingDistricts ? 'Memuat kecamatan...' : (kecamatanName || 'Pilih Kecamatan')"></span>
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="openKecamatan" x-transition @click.outside="openKecamatan = false"
                            class="absolute z-30 mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-lg">
                            <div class="p-2 border-b border-slate-100">
                                <input type="text" x-ref="searchKecamatanInput" x-model.debounce.200ms="searchKecamatan"
                                    @keydown.enter.prevent placeholder="Cari kecamatan" class="input w-full">
                            </div>
                            <ul class="max-h-56 overflow-y-auto py-1">
                                <li x-show="isLoadingDistricts" class="px-3 py-2 text-sm text-slate-500">Memuat data...
                                </li>
                                <template x-for="item in filteredDistricts()" :key="item.id">
                                    <li>
                                        <button type="button" @click="selectKecamatan(item)"
                                            class="w-full px-3 py-2 text-left text-sm hover:bg-slate-100"
                                            :class="kecamatanName === item.name ? 'bg-slate-100 font-medium' : ''"
                                            x-text="item.name"></button>
                                    </li>
                                </template>
                                <li x-show="filteredDistricts().length === 0" class="px-3 py-2 text-sm text-slate-500">
                                    Data tidak
                                    ditemukan.</li>
                            </ul>
                        </div>
                    </div>
                    @error('kecamatan')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- DESA/KELURAHAN --}}
                <div x-show="!useManualInput" x-cloak>
                    <label class="label flex items-center gap-2">
                        Desa/Kelurahan <span class="text-red-500">*</span>
                        <svg x-show="isLoadingVillages" class="animate-spin h-3.5 w-3.5 text-slate-500"
                            viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                    </label>
                    <div class="relative" @keydown.escape="openKelurahan = false">
                        <button type="button" @click="toggleDropdown('kelurahan')" :disabled="!kecamatanName"
                            class="input w-full text-left flex items-center justify-between @error('kelurahan') border-red-500 @enderror"
                            :class="!kecamatanName ? 'bg-slate-100 cursor-not-allowed' : ''">
                            <span class="truncate" :class="kelurahanName ? 'text-slate-900' : 'text-slate-400'"
                                x-text="isLoadingVillages ? 'Memuat desa/kelurahan...' : (kelurahanName || 'Pilih Desa/Kelurahan')"></span>
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="openKelurahan" x-transition @click.outside="openKelurahan = false"
                            class="absolute z-30 mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-lg">
                            <div class="p-2 border-b border-slate-100">
                                <input type="text" x-ref="searchKelurahanInput" x-model.debounce.200ms="searchKelurahan"
                                    @keydown.enter.prevent placeholder="Cari desa/kelurahan" class="input w-full">
                            </div>
                            <ul class="max-h-56 overflow-y-auto py-1">
                                <li x-show="isLoadingVillages" class="px-3 py-2 text-sm text-slate-500">Memuat data...
                                </li>
                                <template x-for="item in filteredVillages()" :key="item.id">
                                    <li>
                                        <button type="button" @click="selectKelurahan(item)"
                                            class="w-full px-3 py-2 text-left text-sm hover:bg-slate-100"
                                            :class="kelurahanName === item.name ? 'bg-slate-100 font-medium' : ''"
                                            x-text="item.name"></button>
                                    </li>
                                </template>
                                <li x-show="filteredVillages().length === 0" class="px-3 py-2 text-sm text-slate-500">
                                    Data tidak
                                    ditemukan.</li>
                            </ul>
                        </div>
                    </div>
                    @error('kelurahan')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- RT --}}
                <div>
                    <label class="label">RT</label>
                    <input wire:model.blur="rt" type="text" inputmode="numeric" maxlength="4" pattern="[0-9]*"
                        placeholder="Maks 4 digit" class="input @error('rt') border-red-500 @enderror">
                    @if($rt !== null && $rt !== '' && strlen((string) $rt) > 4)
                        <p class="text-amber-600 text-xs mt-1">RT maksimal 4 digit.</p>
                    @elseif($rt !== null && $rt !== '' && !ctype_digit((string) $rt))
                        <p class="text-amber-600 text-xs mt-1">RT harus angka.</p>
                    @endif
                    @error('rt')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- RW --}}
                <div>
                    <label class="label">RW</label>
                    <input wire:model.blur="rw" type="text" inputmode="numeric" maxlength="4" pattern="[0-9]*"
                        placeholder="Maks 4 digit" class="input @error('rw') border-red-500 @enderror">
                    @if($rw !== null && $rw !== '' && strlen((string) $rw) > 4)
                        <p class="text-amber-600 text-xs mt-1">RW maksimal 4 digit.</p>
                    @elseif($rw !== null && $rw !== '' && !ctype_digit((string) $rw))
                        <p class="text-amber-600 text-xs mt-1">RW harus angka.</p>
                    @endif
                    @error('rw')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- KODE POS --}}
                <div>
                    <label class="label flex items-center gap-2">
                        Kode Pos <span class="text-red-500">*</span>
                        <svg wire:loading wire:target="fetchKodePosByGroq"
                            class="animate-spin h-3.5 w-3.5 text-slate-500" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <span wire:loading wire:target="fetchKodePosByGroq"
                            class="text-xs text-slate-500 italic font-normal">Tanya Groq AI...</span>
                    </label>
                    <input x-model="kodePos" type="text" inputmode="numeric" maxlength="6" placeholder="Isi kode pos"
                        class="input @error('kode_pos') border-red-500 @enderror">

                    @if ($kode_pos_is_ai)
                        <div
                            class="mt-2 flex items-center gap-2 p-2 bg-emerald-50 border border-emerald-100 rounded-lg animate-pulse-subtle">
                            <span class="flex h-2 w-2">
                                <span
                                    class="animate-ping absolute inline-flex h-2 w-2 rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            <p class="text-[11px] text-emerald-700 font-medium">
                                ✨ Ditebak otomatis AI. Mohon pastikan kembali kesesuaiannya.
                            </p>
                        </div>
                    @else
                        <p class="text-[11px] text-slate-500 mt-1">Ditebak otomatis jika wilayah lengkap. Boleh ubah manual.
                        </p>
                    @endif

                    @if (session()->has('ai_error'))
                        <span class="text-[10px] text-amber-600 italic block mt-1">{{ session('ai_error') }}</span>
                    @endif

                    @error('kode_pos')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-4" x-show="fetchError" x-cloak>
                    <p class="text-red-600 text-xs" x-text="fetchError"></p>
                </div>

            </div>
        </div>

        {{-- ================= D. DATA ORANG TUA ================= --}}
        <div class="card">
            <h2 class="font-heading font-bold text-lg text-primary mb-5 border-b border-border pb-2">D. Data Orang Tua /
                Wali</h2>

            {{-- ================= DATA IBU ================= --}}
            <div class="grid md:grid-cols-2 gap-5 mb-4">

                {{-- Nama Ibu --}}
                <div>
                    <label class="label">Nama Ibu <span class="text-red-500">*</span></label>
                    <input wire:model.blur="ibu_nama" class="input @error('ibu_nama') border-red-500 @enderror">
                    @error('ibu_nama')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- No HP Ibu --}}
                <div>
                    <label class="label">No HP Ibu <span class="text-red-500">*</span></label>
                    <input wire:model.blur="ibu_hp" type="text" inputmode="numeric" maxlength="15"
                        class="input @error('ibu_hp') border-red-500 @enderror">

                    @if(!$errors->has('ibu_hp'))
                        @if(strlen((string) $ibu_hp) > 0 && (strlen((string) $ibu_hp) < 6 || strlen((string) $ibu_hp) > 15))
                            <p class="text-amber-600 text-xs mt-1">No HP ibu harus 6-15 digit.</p>
                        @endif
                    @endif

                    @error('ibu_hp')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <div class="grid md:grid-cols-2 gap-5 mt-4">

                {{-- NIK Ibu --}}
                <div>
                    <label class="label">NIK Ibu</label>
                    <input wire:model.blur="ibu_nik" type="text" inputmode="numeric" maxlength="16"
                        placeholder="16 digit" class="input @error('ibu_nik') border-red-500 @enderror">

                    @if(!$errors->has('ibu_nik'))
                        @if(strlen((string) $ibu_nik) > 0 && strlen((string) $ibu_nik) < 16)
                            <p class="text-amber-600 text-xs mt-1">NIK ibu harus 16 digit jika diisi.</p>
                        @endif
                    @endif

                    @error('ibu_nik')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tahun Lahir Ibu --}}
                <div>
                    <label class="label">Tahun Lahir Ibu</label>
                    <input wire:model.blur="ibu_tahun_lahir" type="text" inputmode="numeric" maxlength="4"
                        pattern="[0-9]*" min="1945" placeholder="Contoh: 1988"
                        class="input @error('ibu_tahun_lahir') border-red-500 @enderror">
                    @if($ibu_tahun_lahir !== null && $ibu_tahun_lahir !== '' && strlen((string) $ibu_tahun_lahir) > 4)
                        <p class="text-amber-600 text-xs mt-1">Tahun lahir ibu maksimal 4 digit.</p>
                    @endif
                    @error('ibu_tahun_lahir')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Pendidikan Ibu --}}
                <div>
                    <label class="label">Pendidikan Terakhir Ibu</label>
                    <select wire:model.blur="ibu_pendidikan"
                        class="input @error('ibu_pendidikan') border-red-500 @enderror">
                        <option value="">Pilih</option>
                        <option>Tidak Sekolah</option>
                        <option>SD</option>
                        <option>SMP</option>
                        <option>SMA/K</option>
                        <option>D3</option>
                        <option>S1</option>
                        <option>S2</option>
                        <option>S3</option>
                    </select>
                    @error('ibu_pendidikan')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Pekerjaan Ibu --}}
                <div>
                    <label class="label">Pekerjaan Ibu</label>
                    <select wire:model.blur="ibu_pekerjaan"
                        class="input @error('ibu_pekerjaan') border-red-500 @enderror">
                        <option value="">Pilih</option>
                        <option>Tidak Bekerja</option>
                        <option>Nelayan</option>
                        <option>Petani</option>
                        <option>Peternak</option>
                        <option>PNS/TNI/Polri</option>
                        <option>Karyawan Swasta</option>
                        <option>Pedagang Kecil</option>
                        <option>Pedagang Besar</option>
                        <option>Wiraswasta</option>
                        <option>Wirausaha</option>
                        <option>Buruh</option>
                        <option>Pensiunan</option>
                        <option>Tenaga Kerja Indonesia</option>
                        <option>Karyawan BUMN</option>
                        <option>Tidak dapat diterapkan</option>
                        <option>Sudah Meninggal</option>
                        <option>Lainnya</option>
                    </select>
                    @error('ibu_pekerjaan')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Pekerjaan Ibu Lainnya --}}
                @if($ibu_pekerjaan === 'Lainnya')
                    <div>
                        <label class="label">Pekerjaan Ibu (Lainnya) <span class="text-red-500">*</span></label>
                        <input wire:model.blur="ibu_pekerjaan_lainnya"
                            class="input @error('ibu_pekerjaan_lainnya') border-red-500 @enderror">
                        @error('ibu_pekerjaan_lainnya')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                {{-- Penghasilan Ibu --}}
                <div>
                    <label class="label">Penghasilan Ibu <span class="text-red-500">*</span></label>
                    <select wire:model.blur="ibu_penghasilan"
                        class="input @error('ibu_penghasilan') border-red-500 @enderror">
                        <option value="">Pilih</option>
                        <option>Kurang dari Rp. 500.000</option>
                        <option>Rp. 500.000 - Rp. 999.000</option>
                        <option>Rp. 1.000.000 - Rp. 1.999.999</option>
                        <option>Rp. 2.000.000 - Rp. 4.999.999</option>
                        <option>Rp. 5.000.000 - Rp. 20.000.000</option>
                        <option>Lebih dari Rp. 20.000.000</option>
                        <option>Tidak Berpenghasilan</option>
                    </select>
                    @error('ibu_penghasilan')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <hr class="my-6">

            {{-- ================= DATA AYAH ================= --}}
            <h3 class="font-semibold text-slate-700 mb-3">Data Ayah Kandung</h3>

            <div class="grid md:grid-cols-2 gap-5">

                {{-- Nama Ayah --}}
                <div>
                    <label class="label">Nama Ayah</label>
                    <input wire:model.blur="ayah_nama" class="input @error('ayah_nama') border-red-500 @enderror">
                    @error('ayah_nama')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- No HP Ayah --}}
                <div>
                    <label class="label">No HP Ayah</label>
                    <input wire:model.blur="ayah_hp" type="text" inputmode="numeric" maxlength="15"
                        class="input @error('ayah_hp') border-red-500 @enderror">

                    @if(!$errors->has('ayah_hp'))
                        @if(strlen((string) $ayah_hp) > 0 && (strlen((string) $ayah_hp) < 6 || strlen((string) $ayah_hp) > 15))
                            <p class="text-amber-600 text-xs mt-1">No HP ayah harus 6-15 digit jika diisi.</p>
                        @endif
                    @endif

                    @error('ayah_hp')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- NIK Ayah --}}
                <div>
                    <label class="label">NIK Ayah</label>
                    <input wire:model.blur="ayah_nik" type="text" inputmode="numeric" maxlength="16"
                        placeholder="16 digit" class="input @error('ayah_nik') border-red-500 @enderror">

                    @if(!$errors->has('ayah_nik'))
                        @if(strlen((string) $ayah_nik) > 0 && strlen((string) $ayah_nik) < 16)
                            <p class="text-amber-600 text-xs mt-1">NIK ayah harus 16 digit jika diisi.</p>
                        @endif
                    @endif

                    @error('ayah_nik')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tahun Lahir Ayah --}}
                <div>
                    <label class="label">Tahun Lahir Ayah</label>
                    <input wire:model.blur="ayah_tahun_lahir" type="text" inputmode="numeric" maxlength="4"
                        pattern="[0-9]*" min="1945" placeholder="Contoh: 1985"
                        class="input @error('ayah_tahun_lahir') border-red-500 @enderror">
                    @if($ayah_tahun_lahir !== null && $ayah_tahun_lahir !== '' && strlen((string) $ayah_tahun_lahir) > 4)
                        <p class="text-amber-600 text-xs mt-1">Tahun lahir ayah maksimal 4 digit.</p>
                    @endif
                    @error('ayah_tahun_lahir')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Pendidikan Ayah --}}
                <div>
                    <label class="label">Pendidikan Terakhir Ayah</label>
                    <select wire:model.blur="ayah_pendidikan"
                        class="input @error('ayah_pendidikan') border-red-500 @enderror">
                        <option value="">Pilih</option>
                        <option>Tidak Sekolah</option>
                        <option>SD</option>
                        <option>SMP</option>
                        <option>SMA/K</option>
                        <option>D3</option>
                        <option>S1</option>
                        <option>S2</option>
                        <option>S3</option>
                    </select>
                    @error('ayah_pendidikan')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Pekerjaan Ayah --}}
                <div>
                    <label class="label">Pekerjaan Ayah</label>
                    <select wire:model.blur="ayah_pekerjaan"
                        class="input @error('ayah_pekerjaan') border-red-500 @enderror">
                        <option value="">Pilih</option>
                        <option>Tidak Bekerja</option>
                        <option>Nelayan</option>
                        <option>Petani</option>
                        <option>Peternak</option>
                        <option>PNS/TNI/Polri</option>
                        <option>Karyawan Swasta</option>
                        <option>Pedagang Kecil</option>
                        <option>Pedagang Besar</option>
                        <option>Wiraswasta</option>
                        <option>Wirausaha</option>
                        <option>Buruh</option>
                        <option>Pensiunan</option>
                        <option>Tenaga Kerja Indonesia</option>
                        <option>Karyawan BUMN</option>
                        <option>Tidak dapat diterapkan</option>
                        <option>Sudah Meninggal</option>
                        <option>Lainnya</option>
                    </select>
                    @error('ayah_pekerjaan')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Pekerjaan Ayah Lainnya --}}
                @if($ayah_pekerjaan === 'Lainnya')
                    <div>
                        <label class="label">Pekerjaan Ayah (Lainnya) <span class="text-red-500">*</span></label>
                        <input wire:model.blur="ayah_pekerjaan_lainnya"
                            class="input @error('ayah_pekerjaan_lainnya') border-red-500 @enderror">
                        @error('ayah_pekerjaan_lainnya')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                {{-- Penghasilan Ayah --}}
                <div>
                    <label class="label">Penghasilan Ayah</label>
                    <select wire:model.blur="ayah_penghasilan"
                        class="input @error('ayah_penghasilan') border-red-500 @enderror">
                        <option value="">Pilih</option>
                        <option>Kurang dari Rp. 500.000</option>
                        <option>Rp. 500.000 - Rp. 999.000</option>
                        <option>Rp. 1.000.000 - Rp. 1.999.999</option>
                        <option>Rp. 2.000.000 - Rp. 4.999.999</option>
                        <option>Rp. 5.000.000 - Rp. 20.000.000</option>
                        <option>Lebih dari Rp. 20.000.000</option>
                        <option>Tidak Berpenghasilan</option>
                    </select>
                    @error('ayah_penghasilan')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- No KKS --}}
                <div>
                    <label class="label">No KKS</label>
                    <input wire:model.blur="no_kks" class="input">
                </div>

                {{-- Penerima KPS/PKH --}}
                <div>
                    <label class="label">Penerima KPS/PKH</label>
                    <select wire:model.blur="kps" class="input">
                        <option value="Tidak">Tidak</option>
                        <option value="Ya">Ya</option>
                    </select>
                </div>

                {{-- Apakah punya KIP --}}
                <div>
                    <label class="label">Apakah Punya KIP</label>
                    <select wire:model.blur="kip" class="input">
                        <option value="Tidak">Tidak</option>
                        <option value="Ya">Ya</option>
                    </select>
                </div>

                {{-- Apakah Layak mendapatkan PIP --}}
                <div>
                    <label class="label">Apakah Peserta Layak Mendapatkan PIP</label>
                    <select wire:model.blur="layak_pip" class="input">
                        <option value="Tidak">Tidak</option>
                        <option value="Ya">Ya</option>
                    </select>
                    @if($layak_pip === 'Ya')
                        <div class="mt-3">
                            <span class="badge-danger">
                                Pastikan memahami peraturan yang berlaku.
                                <a href="https://puslapdik.kemendikdasmen.go.id/inilah-peserta-didik-yang-layak-menerima-dana-bantuan-pip/"
                                    target="_blank" rel="noopener noreferrer"
                                    class="text-black visited:text-black hover:text-black underline font-semibold hover:no-underline">
                                    Cek kelayakan di sini.
                                </a>
                            </span>
                        </div>
                    @endif
                </div>

            </div>
        </div>

        {{-- ================= E. DATA PENDUKUNG ================= --}}
        <div class="card">
            <h2 class="font-heading font-bold text-lg text-primary mb-5 border-b border-border pb-2">E. Data Pendukung
            </h2>

            <div class="grid md:grid-cols-3 gap-5">

                {{-- Tinggi Badan --}}
                <div>
                    <label class="label">Tinggi Badan (cm)</label>
                    <input wire:model.blur="tinggi" type="text" inputmode="numeric" maxlength="3" pattern="[0-9]*"
                        placeholder="Maks 3 digit" class="input @error('tinggi') border-red-500 @enderror">
                    @if($tinggi !== null && $tinggi !== '' && strlen((string) $tinggi) > 3)
                        <p class="text-amber-600 text-xs mt-1">Tinggi badan maksimal 3 digit.</p>
                    @elseif($tinggi !== null && $tinggi !== '' && !ctype_digit((string) $tinggi))
                        <p class="text-amber-600 text-xs mt-1">Tinggi badan harus angka.</p>
                    @endif
                    @error('tinggi')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Berat Badan --}}
                <div>
                    <label class="label">Berat Badan (kg)</label>
                    <input wire:model.blur="berat" type="text" inputmode="numeric" maxlength="3" pattern="[0-9]*"
                        placeholder="Maks 3 digit" class="input @error('berat') border-red-500 @enderror">
                    @if($berat !== null && $berat !== '' && strlen((string) $berat) > 3)
                        <p class="text-amber-600 text-xs mt-1">Berat badan maksimal 3 digit.</p>
                    @elseif($berat !== null && $berat !== '' && !ctype_digit((string) $berat))
                        <p class="text-amber-600 text-xs mt-1">Berat badan harus angka.</p>
                    @endif
                    @error('berat')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Jarak ke Sekolah --}}
                <div>
                    <label class="label">Jarak ke Sekolah (km)</label>
                    <input wire:model.blur="jarak" type="text" inputmode="decimal" maxlength="7"
                        placeholder="Contoh: 4,5" class="input @error('jarak') border-red-500 @enderror">
                    @if($jarak !== null && $jarak !== '' && !preg_match('/^\d{1,4}([.,]\d{1,2})?$/', (string) $jarak))
                        <p class="text-amber-600 text-xs mt-1">Jarak harus angka maksimal 4 digit, boleh desimal (contoh:
                            4,5).
                        </p>
                    @endif
                    @error('jarak')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Jumlah Saudara --}}
                <div>
                    <label class="label">Jumlah Saudara</label>
                    <input wire:model.blur="jumlah_saudara" type="number" min="0" max="99" placeholder="Maks 2 digit"
                        class="input @error('jumlah_saudara') border-red-500 @enderror">
                    @if($jumlah_saudara !== null && $jumlah_saudara !== '' && $jumlah_saudara > 99)
                        <p class="text-amber-600 text-xs mt-1">Jumlah saudara maksimal 2 digit.</p>
                    @endif
                    @error('jumlah_saudara')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Anak Ke (berdasarkan KK) --}}
                <div>
                    <label class="label">Anak ke berapa (berdasarkan KK)</label>
                    <input wire:model.blur="anak_ke" type="text" inputmode="numeric" maxlength="3" pattern="[0-9]*"
                        min="1" max="999" placeholder=" 2" class="input @error('anak_ke') border-red-500 @enderror">
                    @if($anak_ke !== null && $anak_ke !== '' && strlen((string) $anak_ke) > 3)
                        <p class="text-amber-600 text-xs mt-1">Anak ke berapa maksimal 3 digit.</p>
                    @elseif($anak_ke !== null && $anak_ke !== '' && !ctype_digit((string) $anak_ke))
                        <p class="text-amber-600 text-xs mt-1">Anak ke berapa harus angka.</p>
                    @endif
                    @error('anak_ke')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Asal PAUD / TK --}}
                <div class="md:col-span-3" x-data="tkPicker({
                    selectedId: $wire.entangle('paud_tk_id'),
                    selectedName: @js(optional($paud->firstWhere('id', $paud_tk_id))->nama),
                    manualNama: $wire.entangle('nama_tk_manual'),
                    manualAlamat: $wire.entangle('alamat_tk'),
                    isManual: $wire.entangle('is_manual_tk'),
                    options: @js($paud->map(fn($item) => [
                        'id' => $item->id,
                        'nama' => $item->nama,
                        'alamat' => trim(($item->kelurahan ?? '') . (($item->kecamatan ?? '') ? ' - ' . $item->kecamatan : '')),
                    ])->values())
                })" x-init="init()">

                    <div class="grid md:grid-cols-2 gap-4 md:gap-5 mt-3 items-start">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <label class="label mb-0">Asal TK / BA / RA</label>
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium"
                                    :class="isManual ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700'"
                                    x-text="isManual ? 'Mode manual aktif' : 'Dipilih dari daftar TK'"></span>
                            </div>
                            <div class="mb-2" x-show="isManual" x-cloak>
                                <button type="button" @click="switchToListMode()"
                                    class="text-xs font-medium text-primary hover:text-primary/80">
                                    Kembali ke daftar TK
                                </button>
                                <p class="text-xs text-slate-500 mt-1">
                                    Gunakan mode manual jika nama TK belum tersedia di daftar.
                                </p>
                            </div>

                            <div class="relative" @keydown.escape="open = false">
                                <button type="button" @click="toggle()"
                                    class="input w-full text-left flex items-center justify-between @error('paud_tk_id') border-red-500 @enderror">
                                    <span class="truncate" :class="displayName() ? 'text-slate-900' : 'text-slate-400'"
                                        x-text="displayName() || 'Pilih Nama TK'"></span>
                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div x-show="open" x-transition @click.outside="open = false"
                                    class="absolute z-30 mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-lg">
                                    <div class="p-2 border-b border-slate-100">
                                        <input type="text" x-ref="searchInput" x-model.debounce.200ms="search"
                                            @keydown.enter.prevent placeholder="Cari nama TK" class="input w-full">
                                    </div>
                                    <ul class="max-h-56 overflow-y-auto py-1">
                                        <template x-for="item in filteredOptions()" :key="item.id">
                                            <li>
                                                <button type="button" @click="selectOption(item)"
                                                    class="w-full px-3 py-2 text-left text-sm hover:bg-slate-100"
                                                    :class="selectedId == item.id && !isManual ? 'bg-slate-100 font-medium' : ''">
                                                    <span x-text="item.nama"></span>
                                                </button>
                                            </li>
                                        </template>

                                        <li x-show="filteredOptions().length === 0"
                                            class="px-3 py-2 text-sm text-slate-500">
                                            Tidak ditemukan.
                                        </li>

                                        <li class="border-t border-slate-100 mt-1 pt-1">
                                            <button type="button" @click="selectManualMode()"
                                                class="w-full px-3 py-2 text-left text-sm text-primary hover:bg-primary/5">
                                                Nama TK tidak ada? Isi manual
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            @error('paud_tk_id')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            @error('is_manual_tk')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror

                            <div class="mt-4" x-show="isManual" x-cloak>
                                <label class="label">Nama TK (Manual) <span class="text-red-500">*</span></label>
                                <input type="text" x-ref="manualNamaInput" x-model="manualNama"
                                    class="input @error('nama_tk_manual') border-red-500 @enderror"
                                    placeholder="Contoh: TK Harapan Bunda">
                                @error('nama_tk_manual')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="label">Alamat TK <span x-show="isManual" x-cloak><span
                                        class="text-red-500">*</span></span></label>
                            <textarea x-model="manualAlamat" :readonly="!isManual" rows="3"
                                class="input @error('alamat_tk') border-red-500 @enderror"
                                :class="!isManual ? 'bg-slate-100 cursor-not-allowed' : ''"
                                :placeholder="isManual ? 'Tulis alamat TK' : 'Terisi otomatis saat pilih TK'"></textarea>
                            @error('alamat_tk')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Hobi --}}
                <div>
                    <label class="label">Hobi</label>
                    <textarea wire:model.blur="hobi" class="input @error('hobi') border-red-500 @enderror"></textarea>
                    @error('hobi')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Cita-cita --}}
                <div>
                    <label class="label">Cita-cita</label>
                    <input wire:model.blur="cita_cita" class="input @error('cita_cita') border-red-500 @enderror">
                    @error('cita_cita')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Hasil Tes --}}
                <div>
                    <label class="label">Hasil Tes <span class="text-red-500">*</span></label>
                    <select wire:model.blur="hasil_tes" class="input @error('hasil_tes') border-red-500 @enderror">
                        <option value="">Pilih</option>
                        <option value="SB">Sangat Baik</option>
                        <option value="B">Baik</option>
                        <option value="PB">Perlu Bantuan</option>
                        <option value="belum test">Belum Test</option>
                    </select>
                    @error('hasil_tes')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        </div>


        {{-- Tombol Submit Pendaftaran --}}
        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" wire:loading.attr="disabled"
                class="btn-primary px-10 py-3 text-base flex items-center gap-2">

                {{-- Normal --}}
                <span wire:loading.remove wire:target="prepareSubmit">
                    {{ $isEditMode ? 'Simpan Perubahan' : 'Simpan Pendaftaran' }}
                </span>

                {{-- Loading --}}
                <span wire:loading wire:target="prepareSubmit" class="flex items-center gap-2">
                    <svg class="animate-spin h-5 w-5 text-white" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"
                            fill="none">
                        </circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    Menyimpan...
                </span>

            </button>

            @unless($isEditMode)
                <button type="button" wire:click="confirmResetDraft" wire:loading.attr="disabled"
                    class="px-6 py-3 rounded-lg border border-red-300 text-red-700 hover:bg-red-50 transition">
                    Reset Data
                </button>
            @endunless
        </div>


        {{-- Modal Konfirmasi Reset Draft --}}
        @if ($showResetDraftModal)
            <div x-data="{ open: @entangle('showResetDraftModal') }" x-show="open" x-transition.opacity x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                <div x-transition x-show="open" class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md mx-4">
                    <h3 class="text-lg font-semibold text-slate-800 mb-3">Reset Data</h3>
                    <p class="text-sm text-slate-600 mb-6">
                        Data belum dikirim, apakah ingin reset data?
                    </p>

                    <div class="flex justify-end gap-3">
                        <button type="button" @click="open = false" wire:click="cancelResetDraft"
                            class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 transition">
                            Batal
                        </button>

                        <button type="button" wire:click="resetDraftForm" wire:loading.attr="disabled"
                            class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 transition">
                            Ya, Reset
                        </button>
                    </div>
                </div>
            </div>
        @endif


        {{-- Modal Konfirmasi Pendaftaran --}}
        @if ($showConfirm)
            <div x-data="{ open: @entangle('showConfirm') }" x-show="open" x-transition.opacity x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                <div x-transition x-show="open" class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md mx-4">
                    <h3 class="text-lg font-semibold text-slate-800 mb-4">Konfirmasi Pendaftaran</h3>
                    <p class="text-sm text-slate-600 mb-6">
                        {{ $isEditMode ? 'Apakah Anda yakin ingin menyimpan perubahan data ini?' : 'Apakah Anda yakin ingin mengirimkan pendaftaran ini?' }}
                    </p>

                    <div class="flex justify-end gap-3">
                        <button type="button" @click="open = false"
                            class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 transition">
                            Batal
                        </button>

                        <button type="button" wire:click.prevent="submitForm" wire:loading.attr="disabled"
                            class="px-4 py-2 rounded-lg bg-primary text-white hover:bg-primary/90 transition flex items-center gap-2">
                            <span wire:loading.remove>{{ $isEditMode ? 'Ya, Simpan' : 'Ya, Kirim' }}</span>
                            <span wire:loading class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5 text-white" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"
                                        fill="none"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                    </path>
                                </svg>
                                Mengirim...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Modal Error (Server + Client) -->
        <div x-data="{
                serverOpen: @entangle('errorsTriggered'),
                clientOpen: false,
                clientMessage: '',
                openClient(message) {
                    this.clientMessage = (message && String(message).trim() !== '')
                        ? String(message)
                        : 'Terjadi kesalahan. Silakan coba lagi.';
                    this.clientOpen = true;
                },
                close() {
                    this.clientOpen = false;
                    this.clientMessage = '';
                    if (this.serverOpen) this.serverOpen = false;
                },
            }" x-cloak @ppdb-client-error.window="openClient($event.detail?.message)"
            class="relative">
            <div x-show="serverOpen || clientOpen" x-transition.opacity
                class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="close()">
                <div class="bg-white p-6 rounded shadow-lg max-w-md w-full">
                    <h2 class="text-lg font-semibold mb-4 text-red-600">Terjadi Kesalahan</h2>

                    <p x-show="clientOpen" x-text="clientMessage" class="mb-4 text-sm text-gray-700"></p>

                    <p x-show="!clientOpen" class="mb-4 text-sm text-gray-700">
                        {{ $feedbackMessage }}
                    </p>

                    <button type="button"
                        @click="close(); if ($wire) { $wire.errorsTriggered = false }"
                        class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        {{-- Script Alpine untuk halaman publik --}}
        <script>
            const __ppdbRegisterWizardAlpineComponents = () => {
                if (window.__ppdbWizardAlpineRegistered) return;
                window.__ppdbWizardAlpineRegistered = true;

                if (!window.Alpine || typeof window.Alpine.data !== 'function') return;

                Alpine.data('tkPicker', (initial) => ({
                    open: false,
                    search: '',
                    options: initial.options || [],
                    selectedId: initial.selectedId,
                    selectedName: initial.selectedName || '',
                    manualNama: initial.manualNama,
                    manualAlamat: initial.manualAlamat,
                    isManual: initial.isManual,

                    syncWire() {
                        // Semua binding telah menggunakan $wire.entangle()
                    },

                    init() {
                        if (this.selectedId && !this.selectedName) {
                            const found = this.options.find((item) => String(item.id) === String(this.selectedId));
                            if (found) {
                                this.selectedName = found.nama;
                                this.manualAlamat = found.alamat || '';
                            }
                        }
                    },

                    toggle() {
                        this.open = !this.open;
                        if (this.open) {
                            this.$nextTick(() => this.$refs.searchInput?.focus());
                        }
                    },

                    filteredOptions() {
                        if (!this.search) {
                            return this.options;
                        }

                        const keyword = this.search.toLowerCase();
                        return this.options.filter((item) => item.nama.toLowerCase().includes(keyword));
                    },

                    selectOption(item) {
                        this.isManual = false;
                        this.selectedId = item.id;
                        this.selectedName = item.nama;
                        this.manualNama = '';
                        this.manualAlamat = item.alamat || '';
                        this.open = false;
                        this.search = '';
                        this.syncWire();
                    },

                    selectManualMode() {
                        this.isManual = true;
                        this.selectedId = null;
                        this.selectedName = '';
                        this.manualAlamat = '';
                        this.open = false;
                        this.search = '';
                        this.syncWire();
                        this.$nextTick(() => {
                            this.$refs.manualNamaInput?.focus();
                        });
                    },

                    async switchToListMode() {
                        const hasManualDraft = (this.manualNama && this.manualNama.trim() !== '') ||
                            (this.manualAlamat && this.manualAlamat.trim() !== '');

                        if (hasManualDraft) {
                            let proceed = true;
                            if (typeof window.showGlobalConfirm === 'function') {
                                proceed = await window.showGlobalConfirm(
                                    'Input manual akan dihapus jika kembali ke daftar TK. Lanjutkan?', {
                                    title: 'Konfirmasi Perubahan',
                                    okText: 'Ya, Lanjutkan',
                                    cancelText: 'Batal'
                                });
                            }
                            if (!proceed) {
                                return;
                            }
                        }

                        this.isManual = false;
                        this.manualNama = '';
                        this.manualAlamat = '';
                        this.selectedId = null;
                        this.selectedName = '';
                        this.syncWire();
                        this.open = true;
                        this.$nextTick(() => this.$refs.searchInput?.focus());
                    },

                    displayName() {
                        if (this.isManual) {
                            return this.manualNama ? `Manual: ${this.manualNama}` : 'Manual (isi nama TK)';
                        }

                        return this.selectedName;
                    },
                }));

                Alpine.data('emsifaWilayahPicker', (initial) => ({
                    baseUrl: 'https://www.emsifa.com/api-wilayah-indonesia/api',
                    fetchError: '',
                    provinces: [],
                    regencies: [],
                    districts: [],
                    villages: [],
                    provinsiName: initial.provinsi,
                    kabupatenName: initial.kabupaten,
                    kecamatanName: initial.kecamatan,
                    kelurahanName: initial.kelurahan,
                    kodePos: initial.kodePos,
                    selectedProvinceId: null,
                    selectedRegencyId: null,
                    selectedDistrictId: null,
                    useManualInput: false,
                    openProvinsi: false,
                    openKabupaten: false,
                    openKecamatan: false,
                    openKelurahan: false,
                    searchProvinsi: '',
                    searchKabupaten: '',
                    searchKecamatan: '',
                    searchKelurahan: '',
                    isLoadingRegencies: false,
                    isLoadingDistricts: false,
                    isLoadingVillages: false,
                    isLoadingProvinces: false,
                    reqRegenciesId: 0,
                    reqDistrictsId: 0,
                    reqVillagesId: 0,
                    abortControllers: {
                        regencies: null,
                        districts: null,
                        villages: null,
                    },
                    responseCache: {
                        provinces: null,
                        regencies: {},
                        districts: {},
                        villages: {},
                    },

                    syncHiddenInputs() {
                        // Ensure list fields are always arrays to avoid Alpine errors.
                        this.provinces = Array.isArray(this.provinces) ? this.provinces : [];
                        this.regencies = Array.isArray(this.regencies) ? this.regencies : [];
                        this.districts = Array.isArray(this.districts) ? this.districts : [];
                        this.villages = Array.isArray(this.villages) ? this.villages : [];
                    },

                    async init() {
                        await this.loadProvinces();
                        this.syncHiddenInputs();

                        if (this.useManualInput) {
                            return;
                        }

                        if (!this.provinsiName) {
                            return;
                        }

                        const currentProvince = this.provinces.find((item) => item.name === this.provinsiName);
                        if (!currentProvince) {
                            return;
                        }

                        this.selectedProvinceId = currentProvince.id;
                        await this.loadRegencies(currentProvince.id);

                        if (!this.kabupatenName) {
                            return;
                        }

                        const currentRegency = this.regencies.find((item) => item.name === this.kabupatenName);
                        if (!currentRegency) {
                            return;
                        }

                        this.selectedRegencyId = currentRegency.id;
                        await this.loadDistricts(currentRegency.id);

                        if (!this.kecamatanName) {
                            return;
                        }

                        const currentDistrict = this.districts.find((item) => item.name === this.kecamatanName);
                        if (!currentDistrict) {
                            return;
                        }

                        this.selectedDistrictId = currentDistrict.id;
                        await this.loadVillages(currentDistrict.id);
                    },

                    async loadProvinces() {
                        this.fetchError = '';
                        this.isLoadingProvinces = true;
                        try {
                            this.provinces = await this.fetchJson('/provinces.json', 'provinces');
                        } finally {
                            this.isLoadingProvinces = false;
                        }
                    },

                    async loadRegencies(provinceId) {
                        this.fetchError = '';
                        const requestId = ++this.reqRegenciesId;
                        this.isLoadingRegencies = true;
                        const result = await this.fetchJson(`/regencies/${provinceId}.json`, 'regencies');

                        if (requestId === this.reqRegenciesId && result !== null) {
                            this.regencies = result;
                        }

                        if (requestId === this.reqRegenciesId) {
                            this.isLoadingRegencies = false;
                        }
                    },

                    async loadDistricts(regencyId) {
                        this.fetchError = '';
                        const requestId = ++this.reqDistrictsId;
                        this.isLoadingDistricts = true;
                        const result = await this.fetchJson(`/districts/${regencyId}.json`, 'districts');

                        if (requestId === this.reqDistrictsId && result !== null) {
                            this.districts = result;
                        }

                        if (requestId === this.reqDistrictsId) {
                            this.isLoadingDistricts = false;
                        }
                    },

                    async loadVillages(districtId) {
                        this.fetchError = '';
                        const requestId = ++this.reqVillagesId;
                        this.isLoadingVillages = true;
                        const result = await this.fetchJson(`/villages/${districtId}.json`, 'villages');

                        if (requestId === this.reqVillagesId && result !== null) {
                            this.villages = result;
                        }

                        if (requestId === this.reqVillagesId) {
                            this.isLoadingVillages = false;
                        }
                    },

                    async fetchJson(path, scope = null) {
                        if (scope && this.abortControllers[scope]) {
                            this.abortControllers[scope].abort();
                        }

                        const controller = new AbortController();
                        if (scope) {
                            this.abortControllers[scope] = controller;
                        }

                        const readCache = () => {
                            if (scope === 'provinces') {
                                return Array.isArray(this.responseCache.provinces) ? this.responseCache.provinces :
                                    null;
                            }

                            const key = path;
                            if (scope === 'regencies') {
                                return Array.isArray(this.responseCache.regencies[key]) ? this.responseCache.regencies[
                                    key] : null;
                            }
                            if (scope === 'districts') {
                                return Array.isArray(this.responseCache.districts[key]) ? this.responseCache.districts[
                                    key] : null;
                            }
                            if (scope === 'villages') {
                                return Array.isArray(this.responseCache.villages[key]) ? this.responseCache.villages[
                                    key] : null;
                            }

                            return null;
                        };

                        const writeCache = (list) => {
                            if (!Array.isArray(list)) {
                                return;
                            }

                            if (scope === 'provinces') {
                                this.responseCache.provinces = list;
                                return;
                            }

                            const key = path;
                            if (scope === 'regencies') {
                                this.responseCache.regencies[key] = list;
                            }
                            if (scope === 'districts') {
                                this.responseCache.districts[key] = list;
                            }
                            if (scope === 'villages') {
                                this.responseCache.villages[key] = list;
                            }
                        };

                        const fetchWithTimeout = async (url, options, timeout = 5000) => {
                            return new Promise((resolve, reject) => {
                                const timer = setTimeout(() => {
                                    reject(new Error('Timeout'));
                                }, timeout);
                                fetch(url, options).then(
                                    response => {
                                        clearTimeout(timer);
                                        resolve(response);
                                    },
                                    err => {
                                        clearTimeout(timer);
                                        reject(err);
                                    }
                                );
                            });
                        };

                        try {
                            for (let attempt = 0; attempt < 2; attempt++) {
                                try {
                                    const response = await fetchWithTimeout(`${this.baseUrl}${path}`, {
                                        signal: controller.signal
                                    }, 5000);
                                    if (!response.ok) {
                                        throw new Error('Gagal mengambil data wilayah.');
                                    }

                                    const result = await response.json();
                                    const list = Array.isArray(result) ? result : [];
                                    writeCache(list);
                                    return list;
                                } catch (error) {
                                    if (error.name === 'AbortError') {
                                        return null;
                                    }

                                    if (attempt === 0) {
                                        await new Promise((resolve) => setTimeout(resolve, 300));
                                        continue;
                                    }

                                    throw error;
                                }
                            }
                        } catch (error) {
                            if (error.name === 'AbortError') {
                                return null;
                            }

                            const cached = readCache();
                            if (cached) {
                                this.fetchError = 'Koneksi API wilayah sedang lambat. Menampilkan data terakhir.';
                                return cached;
                            }

                            this.fetchError =
                                'API referensi wilayah tidak merespon/lambat. Silakan periksa koneksi lalu coba klik dropdown lagi.';
                            this.enableManualInput(this.fetchError + ' Mode input manual diaktifkan otomatis.');
                            return [];
                        }
                    },

                    async onProvinsiChange() {
                        if (!this.selectedProvinceId && this.provinsiName) {
                            const selected = this.provinces.find((item) => item.name === this.provinsiName);
                            this.selectedProvinceId = selected ? selected.id : null;
                        }
                        this.selectedRegencyId = null;
                        this.selectedDistrictId = null;

                        this.kabupatenName = '';
                        this.kecamatanName = '';
                        this.kelurahanName = '';
                        this.regencies = [];
                        this.districts = [];
                        this.villages = [];
                        this.searchKabupaten = '';
                        this.searchKecamatan = '';
                        this.searchKelurahan = '';
                        this.isLoadingDistricts = false;
                        this.isLoadingVillages = false;

                        if (this.selectedProvinceId) {
                            await this.loadRegencies(this.selectedProvinceId);
                        }
                    },

                    async onKabupatenChange() {
                        if (!this.selectedRegencyId && this.kabupatenName) {
                            const selected = this.regencies.find((item) => item.name === this.kabupatenName);
                            this.selectedRegencyId = selected ? selected.id : null;
                        }

                        this.kecamatanName = '';
                        this.kelurahanName = '';
                        this.districts = [];
                        this.villages = [];
                        this.searchKecamatan = '';
                        this.searchKelurahan = '';
                        this.isLoadingVillages = false;

                        if (this.selectedRegencyId) {
                            await this.loadDistricts(this.selectedRegencyId);
                        }
                    },

                    async onKecamatanChange() {
                        if (!this.selectedDistrictId && this.kecamatanName) {
                            const selected = this.districts.find((item) => item.name === this.kecamatanName);
                            this.selectedDistrictId = selected ? selected.id : null;
                        }

                        this.kelurahanName = '';
                        this.villages = [];
                        this.searchKelurahan = '';

                        if (this.selectedDistrictId) {
                            await this.loadVillages(this.selectedDistrictId);
                        }
                    },

                    async onKelurahanChange() {
                        // Hanya reset kodePos tepat sebelum memanggil AI agar tidak terjadi balapan data (race condition)
                        this.kodePos = '';

                        if (this.provinsiName && this.kabupatenName && this.kecamatanName && this.kelurahanName) {
                            if (this.$wire) {
                                this.$wire.fetchKodePosByGroq(
                                    this.provinsiName,
                                    this.kabupatenName,
                                    this.kecamatanName,
                                    this.kelurahanName
                                );
                            }
                        }
                    },

                    closeAllDropdowns() {
                        this.openProvinsi = false;
                        this.openKabupaten = false;
                        this.openKecamatan = false;
                        this.openKelurahan = false;
                    },

                    enableManualInput(message = '') {
                        this.useManualInput = true;
                        this.closeAllDropdowns();
                        if (message) {
                            this.fetchError = message;
                        }
                    },

                    async disableManualInput() {
                        this.useManualInput = false;
                        this.fetchError = '';
                        if (this.provinces.length === 0 && !this.isLoadingProvinces) {
                            await this.loadProvinces();
                        }
                    },

                    async toggleDropdown(level) {
                        if (this.useManualInput) {
                            return;
                        }

                        if (level === 'provinsi' && this.isLoadingProvinces) {
                            return;
                        }

                        if (level === 'kabupaten' && !this.selectedProvinceId && this.provinsiName) {
                            if (this.provinces.length === 0 && !this.isLoadingProvinces) {
                                await this.loadProvinces();
                            }
                            const selectedProvince = this.provinces.find((item) => item.name === this.provinsiName);
                            this.selectedProvinceId = selectedProvince ? selectedProvince.id : null;
                        }

                        if (level === 'kecamatan' && !this.selectedRegencyId && this.kabupatenName) {
                            const selectedRegency = this.regencies.find((item) => item.name === this.kabupatenName);
                            this.selectedRegencyId = selectedRegency ? selectedRegency.id : null;
                        }

                        if (level === 'kelurahan' && !this.selectedDistrictId && this.kecamatanName) {
                            const selectedDistrict = this.districts.find((item) => item.name === this.kecamatanName);
                            this.selectedDistrictId = selectedDistrict ? selectedDistrict.id : null;
                        }

                        const keyMap = {
                            provinsi: 'openProvinsi',
                            kabupaten: 'openKabupaten',
                            kecamatan: 'openKecamatan',
                            kelurahan: 'openKelurahan',
                        };

                        const targetKey = keyMap[level];
                        const shouldOpen = !this[targetKey];
                        this.closeAllDropdowns();
                        this[targetKey] = shouldOpen;

                        if (shouldOpen) {
                            if (level === 'kabupaten' && this.selectedProvinceId && this.regencies.length === 0 && !this
                                .isLoadingRegencies) {
                                this.loadRegencies(this.selectedProvinceId);
                            }

                            if (level === 'kecamatan' && this.selectedRegencyId && this.districts.length === 0 && !this
                                .isLoadingDistricts) {
                                this.loadDistricts(this.selectedRegencyId);
                            }

                            if (level === 'kelurahan' && this.selectedDistrictId && this.villages.length === 0 && !this
                                .isLoadingVillages) {
                                this.loadVillages(this.selectedDistrictId);
                            }
                        }

                        if (shouldOpen) {
                            this.$nextTick(() => {
                                if (level === 'provinsi' && this.$refs.searchProvinsiInput) {
                                    this.$refs.searchProvinsiInput.focus();
                                }
                                if (level === 'kabupaten' && this.$refs.searchKabupatenInput) {
                                    this.$refs.searchKabupatenInput.focus();
                                }
                                if (level === 'kecamatan' && this.$refs.searchKecamatanInput) {
                                    this.$refs.searchKecamatanInput.focus();
                                }
                                if (level === 'kelurahan' && this.$refs.searchKelurahanInput) {
                                    this.$refs.searchKelurahanInput.focus();
                                }
                            });
                        }
                    },

                    async selectProvinsi(item) {
                        this.selectedProvinceId = item.id;
                        this.selectedRegencyId = null;
                        this.selectedDistrictId = null;
                        this.provinsiName = item.name;
                        this.searchProvinsi = '';
                        this.openProvinsi = false;
                        await this.onProvinsiChange();
                    },

                    async selectKabupaten(item) {
                        this.selectedRegencyId = item.id;
                        this.selectedDistrictId = null;
                        this.kabupatenName = item.name;
                        this.searchKabupaten = '';
                        this.openKabupaten = false;
                        await this.onKabupatenChange();
                    },

                    async selectKecamatan(item) {
                        this.selectedDistrictId = item.id;
                        this.kecamatanName = item.name;
                        this.searchKecamatan = '';
                        this.openKecamatan = false;
                        await this.onKecamatanChange();
                    },

                    async selectKelurahan(item) {
                        this.kelurahanName = item.name;
                        this.searchKelurahan = '';
                        this.openKelurahan = false;
                        await this.onKelurahanChange();
                    },

                    filteredProvinces() {
                        return this.filterBySearch(this.provinces, this.searchProvinsi);
                    },

                    filteredRegencies() {
                        return this.filterBySearch(this.regencies, this.searchKabupaten);
                    },

                    filteredDistricts() {
                        return this.filterBySearch(this.districts, this.searchKecamatan);
                    },

                    filteredVillages() {
                        return this.filterBySearch(this.villages, this.searchKelurahan);
                    },

                    filterBySearch(list, keyword) {
                        const safeList = Array.isArray(list) ? list : [];
                        if (!keyword) {
                            return safeList;
                        }

                        const normalizedKeyword = keyword.toLowerCase();
                        return safeList.filter((item) => item.name.toLowerCase().includes(normalizedKeyword));
                    },

                }));

                Alpine.data('confirmModal', () => ({
                    open: false,
                    init() {
                        window.addEventListener('open-confirm-modal', () => {
                            this.open = true;
                        });
                    }
                }));
            };

            if (window.Alpine && typeof window.Alpine.data === 'function') {
                __ppdbRegisterWizardAlpineComponents();
            } else {
                document.addEventListener('alpine:init', __ppdbRegisterWizardAlpineComponents);
            }

            document.addEventListener('livewire:init', () => {
                if (window.__ppdbLivewireFailureHookInstalled) return;
                window.__ppdbLivewireFailureHookInstalled = true;

                if (!window.Livewire || typeof window.Livewire.hook !== 'function') return;

                window.Livewire.hook('request', ({ fail }) => {
                    fail(({ status, preventDefault }) => {
                        if (typeof preventDefault === 'function') preventDefault();

                        let message = 'Gagal memproses permintaan. Silakan coba lagi.';
                        if (status === 419) {
                            message = 'Sesi Anda sudah berakhir (419). Silakan refresh halaman lalu coba lagi.';
                        } else if (typeof status === 'number' && status >= 500) {
                            message = `Server sedang bermasalah (HTTP ${status}). Silakan coba beberapa saat lagi.`;
                        } else if (typeof status === 'number' && status > 0) {
                            message = `Gagal memproses permintaan (HTTP ${status}). Silakan coba lagi.`;
                        }

                        window.dispatchEvent(
                            new CustomEvent('ppdb-client-error', {
                                detail: { message, status }
                            })
                        );
                    });
                });

                window.addEventListener('unhandledrejection', (event) => {
                    const reason = event?.reason;
                    const reasonMessage = (reason && reason.message) ? String(reason.message) : '';

                    const looksLikeNetworkError = !navigator.onLine ||
                        reasonMessage.toLowerCase().includes('failed to fetch') ||
                        reasonMessage.toLowerCase().includes('networkerror');

                    if (!looksLikeNetworkError) return;

                    event.preventDefault();

                    window.dispatchEvent(
                        new CustomEvent('ppdb-client-error', {
                            detail: {
                                message: 'Koneksi bermasalah. Silakan cek internet Anda, lalu coba lagi.'
                            }
                        })
                    );
                });
            });
        </script>



    </form>
</div>
