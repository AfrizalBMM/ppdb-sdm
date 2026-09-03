<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\TahunAjaran;
use App\Models\PaudTk;

use App\Models\Registration;
use App\Models\Siswa;

use App\Models\DataPendukung;
use App\Models\Ayah;
use App\Models\Ibu;
use App\Models\Wali;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use App\Models\AlamatSiswa;

use App\Services\GenerateTagihanService;
use App\Services\VoucherAdjustmentService;

class PendaftaranWizard extends Component
{
    private const DRAFT_SESSION_KEY = 'pendaftaran_wizard_draft';
    private ?array $dataPendukungColumns = null;
    public bool $isEditMode = false;
    public ?int $editSiswaId = null;

    // STEP MODAL
    public $errorsTriggered = false;
    public $showConfirm = false;
    public $showValidationModal = false;
    public $showResetDraftModal = false;
    public $wilayahPickerKey = 0;
    public $feedbackMessage;
    public $nikSudahTerdaftar = false;
    public $nikTersedia = false;
    public $umurKurang = false;
    public $umurCukup = false;

    // STEP A – UMUM
    public $tanggal_daftar;
    public $kelas = 1;
    public $tahun_ajaran_id;
    public $tahun_ajaran_nama;
    public $voucher_id;
    public $voucher_diskon = 0;
    public $voucher_label;
    public $voucher_expired = false;

    // STEP B – SISWA
    public $nama_siswa;
    public $jenis_kelamin;
    public $nisn;
    public $nik;
    public $no_kk;
    public $tempat_lahir;
    public $tanggal_lahir;
    public $akta_no;
    public $agama = 'Islam';
    public $kewarganegaraan = 'Indonesia';
    public $berkebutuhan_khusus = 'Tidak';
    public $jenis_kebutuhan_khusus;
    public $kebutuhan_khusus_lainnya;
    public $tinggal_bersama;
    public $hp_wali;
    public $transportasi;
    public $no_kks;
    public $kps = 'Tidak';
    public $kip = 'Tidak';
    public $layak_pip = 'Tidak';

    // STEP B – WALI
    public $wali_nama;
    public $wali_hubungan;
    public $wali_hubungan_lainnya;
    public $wali_nik;
    public $wali_tahun_lahir;
    public $wali_pendidikan;
    public $wali_pekerjaan;
    public $wali_pekerjaan_lainnya;
    public $wali_penghasilan;

    // STEP C – ALAMAT
    public $alamat;
    public $provinsi;
    public $kabupaten;
    public $kecamatan;
    public $kelurahan;
    public $rt;
    public $rw;
    public $kode_pos;
    public $kode_pos_is_ai = false;

    // STEP D – AYAH
    public $ayah_nama;
    public $ayah_nik;
    public $ayah_hp;
    public $ayah_tahun_lahir;
    public $ayah_pendidikan;
    public $ayah_pekerjaan;
    public $ayah_pekerjaan_lainnya;
    public $ayah_penghasilan;

    // STEP E – IBU
    public $ibu_nama;
    public $ibu_nik;
    public $ibu_tahun_lahir;
    public $ibu_pendidikan;
    public $ibu_pekerjaan;
    public $ibu_hp;
    public $ibu_pekerjaan_lainnya;
    public $ibu_penghasilan;

    // STEP F – PENDUKUNG
    public $tinggi;
    public $berat;
    public $jarak;
    public $jumlah_saudara;
    public $anak_ke;
    public $paud_tk_id;
    public $hasil_tes;
    public $alamat_tk;
    public $nama_tk_manual;
    public $is_manual_tk = false;
    public $hobi;
    public $cita_cita;

    public function mount($siswa = null)
    {
        // Reset modal states
        $this->errorsTriggered = false;
        $this->showConfirm = false;
        $this->showValidationModal = false;
        $this->showResetDraftModal = false;
        $this->feedbackMessage = null;

        if ($siswa) {
            $siswaModel = Siswa::with([
                'registration.tahunAjaran',
                'alamat',
                'ibu',
                'ayah',
                'wali',
                'dataPendukung.paudTk',
            ])->findOrFail((int) $siswa);

            $this->isEditMode = true;
            $this->editSiswaId = $siswaModel->id;
            $this->loadSiswaForEdit($siswaModel);
            return;
        }

        $this->applyInitialFormState();

        // One-time fresh intent (set by route pendaftaran.public.fresh).
        // Using session pull prevents draft from being cleared repeatedly on page reload.
        if (session()->pull('pendaftaran_wizard_force_fresh', false)) {
            $this->clearDraft();
        }

        $this->loadDraft();
        $this->syncVoucherState();
    }

    private function loadSiswaForEdit(Siswa $siswa): void
    {
        $reg = $siswa->registration;
        $alamat = $siswa->alamat;
        $ibu = $siswa->ibu;
        $ayah = $siswa->ayah;
        $wali = $siswa->wali;
        $pendukung = $siswa->dataPendukung;

        $this->tanggal_daftar = optional($reg?->tanggal_daftar)->format('Y-m-d')
            ?? optional($siswa->created_at)->format('Y-m-d')
            ?? now()->format('Y-m-d');
        $this->tahun_ajaran_id = $reg?->tahun_ajaran_id;
        $this->tahun_ajaran_nama = $reg?->tahunAjaran?->nama;
        $this->voucher_id = $reg?->voucher_id;

        $this->nama_siswa = $siswa->nama;
        $this->jenis_kelamin = $siswa->jenis_kelamin;
        $this->nisn = $siswa->nisn;
        $this->nik = $siswa->nik;
        $this->no_kk = $siswa->no_kk;
        $this->tempat_lahir = $siswa->tempat_lahir;
        $this->tanggal_lahir = optional($siswa->tanggal_lahir)->format('Y-m-d');
        $this->akta_no = $siswa->akta_no;
        $this->agama = $siswa->agama;
        $this->kewarganegaraan = $siswa->kewarganegaraan;
        $this->berkebutuhan_khusus = ($siswa->berkebutuhan_khusus && $siswa->berkebutuhan_khusus !== 'Tidak') ? 'Ya' : 'Tidak';
        $this->tinggal_bersama = $siswa->tinggal_bersama;
        $this->transportasi = $siswa->transportasi;
        $this->no_kks = $siswa->no_kks;
        $this->kps = $this->normalizeYesNo($siswa->kps);
        $this->kip = $this->normalizeYesNo($siswa->kip);
        $this->layak_pip = $this->normalizeYesNo($siswa->layak_pip);
        $this->hasil_tes = $siswa->hasil_tes;

        $this->alamat = $alamat?->alamat;
        $this->provinsi = $alamat?->provinsi;
        $this->kabupaten = $alamat?->kabupaten;
        $this->kecamatan = $alamat?->kecamatan;
        $this->kelurahan = $alamat?->kelurahan;
        $this->rt = $alamat?->rt;
        $this->rw = $alamat?->rw;
        $this->kode_pos = $alamat?->kode_pos;

        $this->ibu_nama = $ibu?->nama;
        $this->ibu_nik = $ibu?->nik;
        $this->ibu_tahun_lahir = $ibu?->tahun_lahir;
        $this->ibu_pendidikan = $ibu?->pendidikan;
        $this->ibu_pekerjaan = $ibu?->pekerjaan;
        $this->ibu_pekerjaan_lainnya = $ibu?->pekerjaan_lainnya;
        $this->ibu_penghasilan = $this->normalizePenghasilanOption($ibu?->penghasilan);
        $this->ibu_hp = $ibu?->no_hp;

        $this->ayah_nama = $ayah?->nama;
        $this->ayah_nik = $ayah?->nik;
        $this->ayah_hp = $ayah?->no_hp;
        $this->ayah_tahun_lahir = $ayah?->tahun_lahir;
        $this->ayah_pendidikan = $ayah?->pendidikan;
        $normalizedAyahPekerjaan = $this->normalizePekerjaanAyahOption($ayah?->pekerjaan);
        if ($normalizedAyahPekerjaan === '__LAINNYA__') {
            $this->ayah_pekerjaan = 'Lainnya';
            $this->ayah_pekerjaan_lainnya = $ayah?->pekerjaan_lainnya ?: $ayah?->pekerjaan;
        } else {
            $this->ayah_pekerjaan = $normalizedAyahPekerjaan;
            $this->ayah_pekerjaan_lainnya = $ayah?->pekerjaan_lainnya;
        }
        $this->ayah_penghasilan = $this->normalizePenghasilanOption($ayah?->penghasilan);

        $this->wali_nama = $wali?->nama;
        $this->wali_hubungan = $wali?->hubungan;
        $this->wali_hubungan_lainnya = $wali?->hubungan_lainnya;
        $this->hp_wali = $wali?->no_hp;
        $this->wali_nik = $wali?->nik;
        $this->wali_tahun_lahir = $wali?->tahun_lahir;
        $this->wali_pendidikan = $wali?->pendidikan;
        $this->wali_pekerjaan = $wali?->pekerjaan;
        $this->wali_pekerjaan_lainnya = $wali?->pekerjaan_lainnya;
        $this->wali_penghasilan = $wali?->penghasilan;

        $this->tinggi = $pendukung?->tinggi;
        $this->berat = $pendukung?->berat;
        $this->jarak = $pendukung?->jarak;
        $this->jumlah_saudara = $pendukung?->jumlah_saudara;
        $this->anak_ke = $pendukung?->anak_ke;
        $this->paud_tk_id = $pendukung?->paud_tk_id;
        $this->alamat_tk = $pendukung?->alamat_tk;
        $this->hobi = $pendukung?->hobi;
        $this->cita_cita = $pendukung?->cita_cita;

        $isManual = false;
        if ($this->hasDataPendukungColumn('is_tk_manual')) {
            $isManual = (bool) ($pendukung?->is_tk_manual ?? false);
        }
        $this->is_manual_tk = $isManual;

        if ($this->hasDataPendukungColumn('nama_tk_manual')) {
            $this->nama_tk_manual = $pendukung?->nama_tk_manual;
        }

        $this->nikTersedia = false;
        $this->nikSudahTerdaftar = false;
        $this->feedbackMessage = null;
        $this->showConfirm = false;
        $this->showValidationModal = false;
        $this->errorsTriggered = false;
        $this->syncVoucherState();
    }

    private function normalizeYesNo($value): string
    {
        if ($value === 'Ya' || $value === 'Tidak') {
            return $value;
        }

        if ($value === null) {
            return 'Tidak';
        }

        $normalized = mb_strtolower(trim((string) $value));

        if ($normalized === '' || $normalized === 'tidak' || $normalized === '0' || $normalized === 'false' || $normalized === 'no') {
            return 'Tidak';
        }

        return 'Ya';
    }

    private function normalizePenghasilanOption($value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $raw = trim((string) $value);
        $normalized = mb_strtolower($raw);

        $allowed = [
            'Kurang dari Rp. 500.000',
            'Rp. 500.000 - Rp. 999.000',
            'Rp. 1.000.000 - Rp. 1.999.999',
            'Rp. 2.000.000 - Rp. 4.999.999',
            'Rp. 5.000.000 - Rp. 20.000.000',
            'Lebih dari Rp. 20.000.000',
            'Tidak Berpenghasilan',
        ];

        if (in_array($raw, $allowed, true)) {
            return $raw;
        }

        if (str_contains($normalized, 'tidak') && str_contains($normalized, 'penghasilan')) {
            return 'Tidak Berpenghasilan';
        }

        if (preg_match('/(^|\D)(0|500)\s*(rb|ribu)/i', $raw) || str_contains($normalized, 'kurang')) {
            return 'Kurang dari Rp. 500.000';
        }

        if (str_contains($normalized, '500') && str_contains($normalized, '999')) {
            return 'Rp. 500.000 - Rp. 999.000';
        }

        if ((str_contains($normalized, '1') && str_contains($normalized, '2')) || str_contains($normalized, '1-2') || str_contains($normalized, '1 s/d 2')) {
            return 'Rp. 1.000.000 - Rp. 1.999.999';
        }

        if (str_contains($normalized, '2-4') || str_contains($normalized, '2 - 4') || str_contains($normalized, '2 sampai 4') || str_contains($normalized, '2 s/d 4')) {
            return 'Rp. 2.000.000 - Rp. 4.999.999';
        }

        if (str_contains($normalized, '4-5') || str_contains($normalized, '4 - 5') || str_contains($normalized, '4 sampai 5') || str_contains($normalized, '4 s/d 5')) {
            return 'Rp. 2.000.000 - Rp. 4.999.999';
        }

        if (str_contains($normalized, '2.000.000') && str_contains($normalized, '4.999.999')) {
            return 'Rp. 2.000.000 - Rp. 4.999.999';
        }

        if (str_contains($normalized, '5') && str_contains($normalized, '20')) {
            return 'Rp. 5.000.000 - Rp. 20.000.000';
        }

        if (str_contains($normalized, 'lebih') || str_contains($normalized, '>20') || str_contains($normalized, '20.000.000')) {
            return 'Lebih dari Rp. 20.000.000';
        }

        return $raw;
    }

    private function normalizePekerjaanAyahOption($value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $raw = trim((string) $value);
        $normalized = mb_strtolower($raw);

        $allowed = [
            'Tidak Bekerja',
            'Nelayan',
            'Petani',
            'Peternak',
            'PNS/TNI/Polri',
            'Karyawan Swasta',
            'Pedagang Kecil',
            'Pedagang Besar',
            'Wiraswasta',
            'Wirausaha',
            'Buruh',
            'Pensiunan',
            'Tenaga Kerja Indonesia',
            'Karyawan BUMN',
            'Tidak dapat diterapkan',
            'Sudah Meninggal',
            'Lainnya',
        ];

        if (in_array($raw, $allowed, true)) {
            return $raw;
        }

        if (str_contains($normalized, 'wirausaha')) {
            return 'Wirausaha';
        }

        if (str_contains($normalized, 'wiraswasta')) {
            return 'Wiraswasta';
        }

        if (str_contains($normalized, 'swasta')) {
            return 'Karyawan Swasta';
        }

        if (str_contains($normalized, 'pns') || str_contains($normalized, 'tni') || str_contains($normalized, 'polri')) {
            return 'PNS/TNI/Polri';
        }

        if (str_contains($normalized, 'bumn')) {
            return 'Karyawan BUMN';
        }

        if (str_contains($normalized, 'meninggal')) {
            return 'Sudah Meninggal';
        }

        return '__LAINNYA__';
    }

    public function updatedNisn($value)
    {
        $this->resetErrorBag('nisn');
        
        if (empty($value)) {
            return;
        }

        if (strlen($value) > 10) {
            $this->addError('nisn', 'NISN maksimal 10 digit.');
            return;
        }

        $exists = Siswa::where('nisn', $value)
            ->when($this->isEditMode && $this->editSiswaId, function ($q) {
                $q->where('id', '!=', $this->editSiswaId);
            })
            ->exists();

        if ($exists) {
            $this->addError('nisn', 'NISN sudah terdaftar.');
        }
    }

    public function updatedNik($value)
    {
        $this->nikSudahTerdaftar = false;
        $this->nikTersedia = false;

        if (strlen($value) !== 16) {
            return;
        }

        // Live check langsung ke database siswa (global, lintas tahun ajaran)
        $exists = Siswa::where('nik', $value)
            ->when($this->isEditMode && $this->editSiswaId, function ($q) {
                $q->where('id', '!=', $this->editSiswaId);
            })
            ->exists();

        if ($exists) {
            $this->nikSudahTerdaftar = true;
            $this->addError('nik', 'NIK sudah terdaftar.');
        } else {
            $this->resetErrorBag('nik');
            $this->nikTersedia = true;
        }
    }

    /**
     * Dapatkan tahun cutoff: tahun kalender saat ini (2026, 2027, dst.)
     */
    private function getCutoffYear(): int
    {
        return now()->year;
    }

    /**
     * Dapatkan tanggal cutoff: 30 Juni dari tahun cutoff
     */
    private function getCutoffDate(): \Carbon\Carbon
    {
        $year = $this->getCutoffYear();
        return \Carbon\Carbon::createFromDate($year, 6, 30)->startOfDay();
    }

    /**
     * Dapatkan batas tanggal lahir minimum (6 tahun sebelum cutoff date 30 Juni)
     */
    private function getMinBirthDate(): \Carbon\Carbon
    {
        return $this->getCutoffDate()->subYears(6);
    }

    public function updatedTanggalLahir($value)
    {
        $this->umurKurang = false;
        $this->umurCukup  = false;

        if (empty($value)) {
            return;
        }

        // Batas minimum lahir: 30 Juni (cutoff) - 6 tahun
        $minBirthDate = $this->getMinBirthDate();

        try {
            $lahir = \Carbon\Carbon::parse($value)->startOfDay();
        } catch (\Exception) {
            return;
        }

        if ($lahir->gt($minBirthDate)) {
            $this->umurKurang = true;
            $this->addError('tanggal_lahir', 'Calon siswa belum berusia 6 tahun per 30 Juni ' . $this->getCutoffYear() . ' (' . $minBirthDate->format('d/m/Y') . ').');
        } else {
            $this->resetErrorBag('tanggal_lahir');
            $this->umurCukup = true;
        }

        if (!$this->isEditMode) {
            $this->saveDraft();
        }
    }

    public function updated($propertyName)
    {
        if ($this->isEditMode) {
            // Mode edit tidak menggunakan draft session (agar tidak mengotori form pendaftaran baru).
            if ($propertyName === 'nik' || $propertyName === 'tanggal_lahir') {
                return;
            }

            $this->validateOnly($propertyName);
            return;
        }

        // NIK ditangani khusus lewat updatedNik() agar bisa cek ketersediaan realtime ke DB.
        // tanggal_lahir ditangani khusus lewat updatedTanggalLahir() untuk validasi usia.
        if ($propertyName === 'nik' || $propertyName === 'tanggal_lahir') {
            $this->saveDraft();
            return;
        }

        $this->saveDraft();
        $this->validateOnly($propertyName);
    }

    private function draftableFields(): array
    {
        return [
            'tanggal_daftar',
            'kelas',
            'tahun_ajaran_id',
            'tahun_ajaran_nama',
            'voucher_id',
            'voucher_diskon',
            'voucher_label',
            'voucher_expired',
            'nama_siswa',
            'jenis_kelamin',
            'nisn',
            'nik',
            'no_kk',
            'tempat_lahir',
            'tanggal_lahir',
            'akta_no',
            'agama',
            'kewarganegaraan',
            'berkebutuhan_khusus',
            'jenis_kebutuhan_khusus',
            'kebutuhan_khusus_lainnya',
            'tinggal_bersama',
            'hp_wali',
            'transportasi',
            'no_kks',
            'kps',
            'kip',
            'layak_pip',
            'wali_nama',
            'wali_hubungan',
            'wali_hubungan_lainnya',
            'wali_nik',
            'wali_tahun_lahir',
            'wali_pendidikan',
            'wali_pekerjaan',
            'wali_pekerjaan_lainnya',
            'wali_penghasilan',
            'alamat',
            'provinsi',
            'kabupaten',
            'kecamatan',
            'kelurahan',
            'rt',
            'rw',
            'kode_pos',
            'ayah_nama',
            'ayah_nik',
            'ayah_hp',
            'ayah_tahun_lahir',
            'ayah_pendidikan',
            'ayah_pekerjaan',
            'ayah_pekerjaan_lainnya',
            'ayah_penghasilan',
            'ibu_nama',
            'ibu_nik',
            'ibu_tahun_lahir',
            'ibu_pendidikan',
            'ibu_pekerjaan',
            'ibu_hp',
            'ibu_pekerjaan_lainnya',
            'ibu_penghasilan',
            'tinggi',
            'berat',
            'jarak',
            'jumlah_saudara',
            'anak_ke',
            'paud_tk_id',
            'hasil_tes',
            'alamat_tk',
            'nama_tk_manual',
            'is_manual_tk',
            'hobi',
            'cita_cita',
        ];
    }

    private function saveDraft(): void
    {
        $draft = [];

        foreach ($this->draftableFields() as $field) {
            $draft[$field] = $this->{$field};
        }

        session([self::DRAFT_SESSION_KEY => $draft]);
    }

    private function loadDraft(): void
    {
        $draft = session(self::DRAFT_SESSION_KEY);

        if (!is_array($draft) || empty($draft)) {
            return;
        }

        foreach ($this->draftableFields() as $field) {
            if (array_key_exists($field, $draft)) {
                $this->{$field} = $draft[$field];
            }
        }
    }

    private function clearDraft(): void
    {
        session()->forget(self::DRAFT_SESSION_KEY);
    }

    private function applyInitialFormState(): void
    {
        $this->tanggal_daftar = now()->format('Y-m-d');

        $tahun = TahunAjaran::where('aktif', true)->first();

        if ($tahun) {
            $this->tahun_ajaran_id = $tahun->id;
            $this->tahun_ajaran_nama = $tahun->nama;
        }
    }

    public function confirmResetDraft(): void
    {
        $this->showResetDraftModal = true;
    }

    public function cancelResetDraft(): void
    {
        $this->showResetDraftModal = false;
    }

    public function resetDraftForm(): void
    {
        $this->clearDraft();
        $this->reset($this->draftableFields());

        $this->resetErrorBag();
        $this->resetValidation();
        $this->errorsTriggered = false;
        $this->showConfirm = false;
        $this->showValidationModal = false;
        $this->showResetDraftModal = false;
        $this->feedbackMessage = null;
        $this->nikSudahTerdaftar = false;
        $this->nikTersedia = false;
        $this->umurKurang = false;
        $this->umurCukup = false;
        $this->wilayahPickerKey++;

        $this->applyInitialFormState();
    }

    public function updatedVoucherId($id)
    {
        $this->voucher_diskon = 0;
        $this->voucher_label = null;
        $this->voucher_expired = false;

        if (!$id)
            return;

        $voucher = Voucher::find($id);
        if (!$voucher) {
            return;
        }

        $now = Carbon::now();
        $startDate = $voucher->tanggal_mulai ? Carbon::parse($voucher->tanggal_mulai)->startOfDay() : null;
        $endDate = $voucher->tanggal_selesai ? Carbon::parse($voucher->tanggal_selesai)->endOfDay() : null;
        $quotaRemaining = is_null($voucher->maks_penggunaan)
            ? null
            : max(0, (int) $voucher->maks_penggunaan - (int) $voucher->digunakan);

        if (!$voucher->aktif) {
            $this->voucher_expired = true;
            $this->voucher_label = 'Voucher belum aktif';
            return;
        }

        if ($startDate && $now->lt($startDate)) {
            $daysToStart = $now->startOfDay()->diffInDays($startDate);
            $this->voucher_expired = true;
            $this->voucher_label = 'Voucher berlaku ' . $daysToStart . ' hari lagi';
            return;
        }

        if ($endDate && $now->gt($endDate)) {
            $this->voucher_expired = true;
            $this->voucher_label = 'Voucher periode habis';
            return;
        }

        if (!is_null($quotaRemaining) && $quotaRemaining <= 0) {
            $this->voucher_expired = true;
            $this->voucher_label = 'Kuota voucher habis';
            return;
        }

        $this->voucher_diskon = (int) $voucher->diskon_nominal;
        $this->voucher_label = 'Voucher aktif. Diskon Rp ' . number_format($voucher->diskon_nominal, 0, ',', '.');
    }

    private function voucherValidationRules(): array
    {
        return [
            'nullable',
            'exists:vouchers,id',
            function (string $attribute, $value, \Closure $fail): void {
                if (!$value) {
                    return;
                }

                $voucher = Voucher::find($value);
                if (!$voucher || !$voucher->masihBerlaku()) {
                    $fail('Voucher tidak dapat digunakan (tidak aktif, periode habis, atau kuota habis).');
                }
            },
        ];
    }

    private function syncVoucherState(): void
    {
        $this->voucher_diskon = 0;
        $this->voucher_label = null;
        $this->voucher_expired = false;

        if ($this->voucher_id) {
            $this->updatedVoucherId($this->voucher_id);
        }
    }

    public function updatedKodePos()
    {
        $this->kode_pos_is_ai = false;
    }

    public function fetchKodePosByGroq($prov = null, $kab = null, $kec = null, $kel = null)
    {
        $this->kode_pos_is_ai = false; // Reset status saat mulai mencari baru
        $prov = $prov ?: $this->provinsi;
        $kab = $kab ?: $this->kabupaten;
        $kec = $kec ?: $this->kecamatan;
        $kel = $kel ?: $this->kelurahan;

        if (empty($prov) || empty($kab) || empty($kec) || empty($kel)) {
            return;
        }

        $apiKey = config('services.groq.api_key');
        if (empty($apiKey)) {
            return;
        }

        // LOG INPUT UNTUK DIAGNOSA
        \Illuminate\Support\Facades\Log::info("Groq AI Request Input: Prov=$prov, Kab=$kab, Kec=$kec, Kel=$kel");

        $prompt = "Tugas: Temukan Kode Pos Indonesia yang paling AKURAT.
        
        PENTING: Jangan tertukar dengan wilayah yang namanya mirip di kabupaten tetangga.
        
        Contoh 1:
        Wilayah: Kelurahan Wonorejo, Kecamatan Gondangrejo, Kabupaten Karanganyar, Jawa Tengah
        Hasil: 57183

        Contoh 2:
        Wilayah: Kelurahan Wonorejo, Kecamatan Polokarto, Kabupaten Sukoharjo, Jawa Tengah
        Hasil: 57555

        Contoh 3:
        Wilayah: Kelurahan Cangkol, Kecamatan Mojolaban, Kabupaten Sukoharjo, Jawa Tengah
        Hasil: 57554

        Sekarang tentukan untuk wilayah berikut:
        Provinsi: {$prov}
        Kabupaten/Kota: {$kab}
        Kecamatan: {$kec}
        Kelurahan/Desa: {$kel}

        Berikan HANYA 5 digit kode pos yang paling tepat berdasarkan data lokasi di atas tanpa teks apapun.";

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(20)->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => 'llama-3.3-70b-versatile',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => 0,
                'max_tokens' => 32,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                $text = $result['choices'][0]['message']['content'] ?? '';
                $text = trim($text);

                // LOG HASIL UNTUK DEBUGGING
                \Illuminate\Support\Facades\Log::info("Groq AI Success Response: " . $text);

                $found = false;
                // Ekstrak persis 5 digit angka dengan regex toleransi jika AI memberikan narasi sedikit
                if (preg_match('/^\d{5}$/', $text)) {
                    $this->kode_pos = $text;
                    $found = true;
                } elseif (preg_match('/(\d{5})/', $text, $matches)) {
                    $this->kode_pos = $matches[1];
                    $found = true;
                }

                if ($found) {
                    $this->kode_pos_is_ai = true; // Set flag sukses AI
                } else {
                    \Illuminate\Support\Facades\Log::warning("Groq AI gagal menemukan 5 digit angka dalam respon: " . $text);
                    session()->flash('ai_error', 'AI memberikan jawaban tapi format kode pos tidak ditemukan.');
                }
            } elseif ($response->status() === 429) {
                \Illuminate\Support\Facades\Log::warning("Groq AI Rate Limit Hit!");
                session()->flash('ai_error', 'Groq AI sedang istirahat sejenak (kuota penuh). Silakan isi manual atau tunggu 1 menit.');
            } else {
                \Illuminate\Support\Facades\Log::error("Groq API Error: " . $response->status() . " - " . $response->body());
                session()->flash('ai_error', 'Gagal menebak kode pos (API Error). Silakan isi manual.');
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Groq API Exception: ' . $e->getMessage());
        }
    }

    public function prepareSubmit()
    {
        $this->resetErrorBag();
        $this->errorsTriggered = false;
        $this->showValidationModal = false;

        try {
            \Illuminate\Support\Facades\Log::info('PendaftaranWizard prepareSubmit', [
                'is_edit_mode' => (bool) $this->isEditMode,
                'edit_siswa_id' => $this->editSiswaId,
                'has_access_session' => session()->has('akses_pembayaran'),
            ]);
        } catch (\Throwable $logError) {
            // ignore logging failures
        }

        $this->provinsi = trim((string) $this->provinsi) ?: null;
        $this->kabupaten = trim((string) $this->kabupaten) ?: null;
        $this->kecamatan = trim((string) $this->kecamatan) ?: null;
        $this->kelurahan = trim((string) $this->kelurahan) ?: null;

        // Jika tidak tinggal bersama wali, kosongkan data wali
        if ($this->tinggal_bersama !== 'wali') {
            $this->hp_wali = null;
            $this->wali_nama = null;
            $this->wali_hubungan = null;
            $this->wali_hubungan_lainnya = null;
            $this->wali_nik = null;
            $this->wali_tahun_lahir = null;
            $this->wali_pendidikan = null;
            $this->wali_pekerjaan = null;
            $this->wali_pekerjaan_lainnya = null;
            $this->wali_penghasilan = null;
        }

        if ($this->wali_hubungan !== 'Lainnya') {
            $this->wali_hubungan_lainnya = null;
        }

        if ($this->wali_pekerjaan !== 'Lainnya') {
            $this->wali_pekerjaan_lainnya = null;
        }

        try {
            // Validasi form saja, sebelum buka modal konfirmasi
            $this->validate([
                'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
                'tanggal_daftar' => 'required|date',
                'voucher_id' => $this->voucherValidationRules(),
                'nama_siswa' => 'required|string',
                'jenis_kelamin' => 'required|string',
                'nisn' => 'nullable|digits_between:1,10|unique:siswa,nisn,' . ($this->editSiswaId ?? 'NULL') . ',id',
                'nik' => 'required|digits:16|unique:siswa,nik,' . ($this->editSiswaId ?? 'NULL') . ',id',
                'no_kk' => 'required|digits:16',
                'tempat_lahir' => 'required|string',
                'tanggal_lahir' => 'required|date|before_or_equal:' . $this->getMinBirthDate()->format('Y-m-d'),
                'alamat' => 'required|string',
                'provinsi' => 'required|string',
                'kabupaten' => 'required|string',
                'kecamatan' => 'required|string',
                'kelurahan' => 'required|string',
                'kode_pos' => 'nullable|digits_between:3,6',
                'transportasi' => 'required|string',
                'hasil_tes' => 'required|in:SB,B,PB,belum test',
                'ibu_penghasilan' => 'required|string',
                'ibu_tahun_lahir' => 'nullable|digits:4|integer|min:1945|max:' . date('Y'),
                'ayah_tahun_lahir' => 'nullable|digits:4|integer|min:1945|max:' . date('Y'),
                'anak_ke' => 'nullable|integer|min:1|max:999',
                // Validasi wali jika tinggal bersama
                'wali_nama' => $this->tinggal_bersama === 'wali' ? 'required|string' : 'nullable',
                'wali_hubungan' => $this->tinggal_bersama === 'wali' ? 'required|string' : 'nullable',
                'wali_hubungan_lainnya' => $this->tinggal_bersama === 'wali' ? 'required_if:wali_hubungan,Lainnya|nullable|string|max:100' : 'nullable',
                'hp_wali' => $this->tinggal_bersama === 'wali' ? 'required|digits_between:6,15' : 'nullable|digits_between:6,15',
                'wali_nik' => $this->tinggal_bersama === 'wali' ? 'required|digits:16' : 'nullable|digits:16',
                'wali_tahun_lahir' => $this->tinggal_bersama === 'wali' ? 'required|digits:4|integer|min:1945|max:' . date('Y') : 'nullable|digits:4|integer|min:1945|max:' . date('Y'),
                'wali_pendidikan' => $this->tinggal_bersama === 'wali' ? 'nullable|string' : 'nullable',
                'wali_pekerjaan' => $this->tinggal_bersama === 'wali' ? 'nullable|string' : 'nullable',
                'wali_pekerjaan_lainnya' => $this->tinggal_bersama === 'wali' ? 'required_if:wali_pekerjaan,Lainnya|nullable|string|max:100' : 'nullable',
                'wali_penghasilan' => $this->tinggal_bersama === 'wali' ? 'nullable|string' : 'nullable',
                'rt' => 'nullable|digits_between:1,4',
                'rw' => 'nullable|digits_between:1,4',
                'tinggi' => 'nullable|integer|max:999',
                'berat' => 'nullable|integer|max:999',
                'jarak' => ['nullable', 'regex:/^\d{1,4}([.,]\d{1,2})?$/'],
            ]);

            $this->showConfirm = true;
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->showValidationModal = true;
            throw $e; // tetap lempar supaya error bag terisi di Livewire
        }
    }

    public function submitForm()
    {
        $this->showConfirm = false; // tutup modal konfirmasi

        try {
            \Illuminate\Support\Facades\Log::info('PendaftaranWizard submitForm start', [
                'is_edit_mode' => (bool) $this->isEditMode,
                'edit_siswa_id' => $this->editSiswaId,
                'has_access_session' => session()->has('akses_pembayaran'),
            ]);
        } catch (\Throwable $logError) {
            // ignore logging failures
        }

        try {
            $siswaId = $this->simpan(); // simpan data transaksi murni

            if ($this->isEditMode) {
                try {
                    \Illuminate\Support\Facades\Log::info('PendaftaranWizard submitForm success', [
                        'is_edit_mode' => true,
                        'siswa_id' => $siswaId,
                    ]);
                } catch (\Throwable $logError) {
                    // ignore logging failures
                }

                session()->flash('success', 'Data pendaftar berhasil diperbarui.');
                return $this->redirect(route('pendaftaran.detail', ['id' => $siswaId]));
            }

            $this->clearDraft();

            // Deteksi voucher yg dipilih tetapi tidak diterapkan (kuota habis/dipakai
            // pendaftar lain secara bersamaan). Data tetap aman (tidak over-use),
            // namun user perlu tahu agar diskon tidak menghilang diam-diam.
            if ($this->voucher_id) {
                $voucherApplied = Siswa::find($siswaId)
                    ->tagihan()
                    ->whereNotNull('voucher_id')
                    ->exists();

                if (!$voucherApplied) {
                    session()->flash('voucher_warning', 'Voucher tidak dapat diterapkan (kuota habis/digunakan pendaftar lain secara bersamaan). Tagihan dibuat tanpa diskon voucher.');
                }
            }

            // Jika berhasil, redirect ke halaman sukses
            return $this->redirect(route('pendaftaran.sukses', ['siswa' => $siswaId]));
        } catch (ValidationException $e) {
            // Untuk data belum lengkap, tampilkan modal validasi.
            $this->showValidationModal = true;
            throw $e;
        } catch (QueryException $e) {
            // Minimal logging to help production debugging (avoid dumping full SQL/bindings).
            try {
                \Illuminate\Support\Facades\Log::error('PendaftaranWizard QueryException', [
                    'is_edit_mode' => (bool) $this->isEditMode,
                    'edit_siswa_id' => $this->editSiswaId,
                    'sql_state' => $e->errorInfo[0] ?? null,
                    'driver_code' => $e->errorInfo[1] ?? null,
                ]);
            } catch (\Throwable $logError) {
                // ignore logging failures
            }

            $this->errorsTriggered = true;
            $this->feedbackMessage = $this->friendlyDatabaseErrorMessage($e);
        } catch (\Exception $e) {
            // Jika error (misal duplicate NIK di DB), tampilkan modal error
            $this->errorsTriggered = true;
            $this->feedbackMessage = $e->getMessage();
        }
    }

    public function simpan()
    {
        // Defensive validation in case this method is triggered directly.
        $this->validate();

        $siswa = null;

        DB::transaction(function () use (&$siswa) {

            if ($this->isEditMode && $this->editSiswaId) {
                $siswa = Siswa::with(['registration', 'alamat', 'ibu', 'ayah', 'wali', 'dataPendukung'])
                    ->lockForUpdate()
                    ->findOrFail($this->editSiswaId);

                $reg = $siswa->registration;
                if ($reg) {
                    $reg->update([
                        'tanggal_daftar' => $this->tanggal_daftar,
                        'tahun_ajaran_id' => $this->tahun_ajaran_id,
                        'voucher_id' => $this->voucher_id,
                    ]);
                }

                $siswa->update([
                    'nama' => $this->nama_siswa,
                    'jenis_kelamin' => $this->jenis_kelamin,
                    'nisn' => $this->nisn,
                    'nik' => $this->nik,
                    'no_kk' => $this->no_kk,
                    'tempat_lahir' => $this->tempat_lahir,
                    'tanggal_lahir' => $this->tanggal_lahir,
                    'akta_no' => $this->akta_no,
                    'agama' => $this->agama,
                    'kewarganegaraan' => $this->kewarganegaraan,
                    'berkebutuhan_khusus' => $this->berkebutuhan_khusus === 'Ya'
                        ? ($this->jenis_kebutuhan_khusus === 'Lain-lain' ? $this->kebutuhan_khusus_lainnya : $this->jenis_kebutuhan_khusus)
                        : 'Tidak',
                    'tinggal_bersama' => $this->tinggal_bersama,
                    'transportasi' => $this->transportasi,
                    'no_kks' => $this->no_kks,
                    'kps' => $this->kps,
                    'kip' => $this->kip,
                    'layak_pip' => $this->layak_pip,
                    'hasil_tes' => $this->hasil_tes,
                ]);

                AlamatSiswa::updateOrCreate(
                    ['siswa_id' => $siswa->id],
                    [
                        'alamat' => $this->alamat,
                        'provinsi' => $this->provinsi,
                        'kabupaten' => $this->kabupaten,
                        'kecamatan' => $this->kecamatan,
                        'kelurahan' => $this->kelurahan,
                        'rt' => $this->rt !== null && $this->rt !== '' ? trim((string) $this->rt) : null,
                        'rw' => $this->rw !== null && $this->rw !== '' ? trim((string) $this->rw) : null,
                        'kode_pos' => $this->kode_pos,
                    ]
                );

                Ibu::updateOrCreate(
                    ['siswa_id' => $siswa->id],
                    [
                        'nama' => $this->ibu_nama,
                        'nik' => $this->ibu_nik,
                        'tahun_lahir' => $this->ibu_tahun_lahir,
                        'pendidikan' => $this->ibu_pendidikan,
                        'pekerjaan' => $this->ibu_pekerjaan,
                        'pekerjaan_lainnya' => $this->ibu_pekerjaan_lainnya,
                        'penghasilan' => $this->ibu_penghasilan,
                        'no_hp' => $this->ibu_hp,
                    ]
                );

                if ($this->ayah_nama || $this->ayah_nik || $this->ayah_tahun_lahir || $this->ayah_pendidikan || $this->ayah_pekerjaan || $this->ayah_penghasilan) {
                    Ayah::updateOrCreate(
                        ['siswa_id' => $siswa->id],
                        [
                            'nama' => $this->ayah_nama,
                            'nik' => $this->ayah_nik,
                            'no_hp' => $this->ayah_hp,
                            'tahun_lahir' => $this->ayah_tahun_lahir,
                            'pendidikan' => $this->ayah_pendidikan,
                            'pekerjaan' => $this->ayah_pekerjaan,
                            'pekerjaan_lainnya' => $this->ayah_pekerjaan_lainnya,
                            'penghasilan' => $this->ayah_penghasilan,
                        ]
                    );
                } else {
                    $siswa->ayah()?->delete();
                }

                if ($this->tinggal_bersama === 'wali') {
                    Wali::updateOrCreate(
                        ['siswa_id' => $siswa->id],
                        [
                            'nama' => $this->wali_nama,
                            'hubungan' => $this->wali_hubungan,
                            'hubungan_lainnya' => $this->wali_hubungan === 'Lainnya' ? $this->wali_hubungan_lainnya : null,
                            'no_hp' => $this->hp_wali,
                            'nik' => $this->wali_nik,
                            'tahun_lahir' => $this->wali_tahun_lahir,
                            'pendidikan' => $this->wali_pendidikan,
                            'pekerjaan' => $this->wali_pekerjaan,
                            'pekerjaan_lainnya' => $this->wali_pekerjaan_lainnya,
                            'penghasilan' => $this->wali_penghasilan,
                        ]
                    );
                } else {
                    $siswa->wali()?->delete();
                }

                $dataPendukungPayload = [
                    'tinggi' => $this->tinggi ? (int) $this->tinggi : null,
                    'berat' => $this->berat ? (int) $this->berat : null,
                    'jarak' => $this->normalizeJarakValue($this->jarak),
                    'jumlah_saudara' => $this->jumlah_saudara ? (int) $this->jumlah_saudara : null,
                    'anak_ke' => $this->anak_ke ? (int) $this->anak_ke : null,
                    'paud_tk_id' => $this->is_manual_tk ? null : $this->paud_tk_id,
                    'alamat_tk' => $this->alamat_tk,
                    'hobi' => $this->hobi,
                    'cita_cita' => $this->cita_cita,
                ];

                if ($this->hasDataPendukungColumn('is_tk_manual')) {
                    $dataPendukungPayload['is_tk_manual'] = (bool) $this->is_manual_tk;
                }

                if ($this->hasDataPendukungColumn('nama_tk_manual')) {
                    $dataPendukungPayload['nama_tk_manual'] = $this->is_manual_tk ? $this->nama_tk_manual : null;
                }

                DataPendukung::updateOrCreate(
                    ['siswa_id' => $siswa->id],
                    $dataPendukungPayload
                );

                // ================= SYNC VOUCHER TO TAGIHAN + KEUANGAN =================
                // Voucher selection on Registration does not automatically affect existing tagihan.
                // This call adjusts tagihan totals, voucher usage count, and reallocates payments if needed.
                app(VoucherAdjustmentService::class)->adjustForSiswa(
                    $siswa,
                    $this->voucher_id ? (int) $this->voucher_id : null,
                    'Panitia Public'
                );

                logAktivitas(
                    'Edit Pendaftaran Siswa',
                    'Mengubah data siswa ' . $siswa->nama . ' (' . optional($reg)->nomor_registrasi . ')'
                );

                return;
            }

            // ================= CEK DUPLIKASI =================
            $existingSiswa = Siswa::where('nik', $this->nik)->first();

            if ($existingSiswa) {
                $siswa = $existingSiswa;
                return;
            }

            // ================= BUAT REGISTRATION =================
            $reg = $this->createRegistrationWithRetry();

            // ================= BUAT SISWA =================
            $siswa = Siswa::create([
                'registration_id' => $reg->id,
                'nama' => $this->nama_siswa,
                'jenis_kelamin' => $this->jenis_kelamin,
                'nisn' => $this->nisn,
                'nik' => $this->nik,
                'no_kk' => $this->no_kk,
                'tempat_lahir' => $this->tempat_lahir,
                'tanggal_lahir' => $this->tanggal_lahir,
                'akta_no' => $this->akta_no,

                // Field tambahan sesuai DB final
                'agama' => $this->agama,
                'kewarganegaraan' => $this->kewarganegaraan,
                'berkebutuhan_khusus' => $this->berkebutuhan_khusus === 'Ya' ? ($this->jenis_kebutuhan_khusus === 'Lain-lain' ? $this->kebutuhan_khusus_lainnya : $this->jenis_kebutuhan_khusus) : 'Tidak',
                'tinggal_bersama' => $this->tinggal_bersama,
                'transportasi' => $this->transportasi,
                'no_kks' => $this->no_kks,
                'kps' => $this->kps,
                'kip' => $this->kip,
                'layak_pip' => $this->layak_pip,

                'hasil_tes' => $this->hasil_tes,
            ]);

            // ================= ALAMAT =================
            AlamatSiswa::create([
                'siswa_id' => $siswa->id,
                'alamat' => $this->alamat,
                'provinsi' => $this->provinsi,
                'kabupaten' => $this->kabupaten,
                'kecamatan' => $this->kecamatan,
                'kelurahan' => $this->kelurahan,
                'rt' => $this->rt !== null && $this->rt !== '' ? trim((string) $this->rt) : null,
                'rw' => $this->rw !== null && $this->rw !== '' ? trim((string) $this->rw) : null,
                'kode_pos' => $this->kode_pos,
            ]);

            // ================= IBU =================
            if ($this->ibu_nama || $this->ibu_hp) {
                Ibu::create([
                    'siswa_id' => $siswa->id,
                    'nama' => $this->ibu_nama,
                    'nik' => $this->ibu_nik,
                    'tahun_lahir' => $this->ibu_tahun_lahir,
                    'pendidikan' => $this->ibu_pendidikan,
                    'pekerjaan' => $this->ibu_pekerjaan,
                    'pekerjaan_lainnya' => $this->ibu_pekerjaan_lainnya,
                    'penghasilan' => $this->ibu_penghasilan,
                    'no_hp' => $this->ibu_hp,
                ]);
            }
            // ================= AYAH (optional) =================
            if ($this->ayah_nama) {
                Ayah::create([
                    'siswa_id' => $siswa->id,
                    'nama' => $this->ayah_nama,
                    'nik' => $this->ayah_nik,
                    'no_hp' => $this->ayah_hp,
                    'tahun_lahir' => $this->ayah_tahun_lahir,
                    'pendidikan' => $this->ayah_pendidikan,
                    'pekerjaan' => $this->ayah_pekerjaan,
                    'pekerjaan_lainnya' => $this->ayah_pekerjaan_lainnya,
                    'penghasilan' => $this->ayah_penghasilan,
                    // ❌ no_hp sudah dihapus sesuai DB final
                ]);
            }

            // ================= WALI (conditional) =================
            if ($this->tinggal_bersama === 'wali') {
                Wali::create([
                    'siswa_id' => $siswa->id,
                    'nama' => $this->wali_nama,
                    'hubungan' => $this->wali_hubungan,
                    'hubungan_lainnya' => $this->wali_hubungan === 'Lainnya' ? $this->wali_hubungan_lainnya : null,
                    'no_hp' => $this->hp_wali,
                    'nik' => $this->wali_nik,
                    'tahun_lahir' => $this->wali_tahun_lahir,
                    'pendidikan' => $this->wali_pendidikan,
                    'pekerjaan' => $this->wali_pekerjaan,
                    'pekerjaan_lainnya' => $this->wali_pekerjaan_lainnya,
                    'penghasilan' => $this->wali_penghasilan,
                ]);
            }

            // ================= DATA PENDUKUNG =================
            $dataPendukungPayload = [
                'siswa_id' => $siswa->id,
                'tinggi' => $this->tinggi ? (int) $this->tinggi : null,
                'berat' => $this->berat ? (int) $this->berat : null,
                'jarak' => $this->normalizeJarakValue($this->jarak),
                'jumlah_saudara' => $this->jumlah_saudara ? (int) $this->jumlah_saudara : null,
                'anak_ke' => $this->anak_ke ? (int) $this->anak_ke : null,
                'paud_tk_id' => $this->is_manual_tk ? null : $this->paud_tk_id,
                'alamat_tk' => $this->alamat_tk, // ✅ ditambahkan
                'hobi' => $this->hobi,
                'cita_cita' => $this->cita_cita,
            ];

            if ($this->hasDataPendukungColumn('is_tk_manual')) {
                $dataPendukungPayload['is_tk_manual'] = (bool) $this->is_manual_tk;
            }

            if ($this->hasDataPendukungColumn('nama_tk_manual')) {
                $dataPendukungPayload['nama_tk_manual'] = $this->is_manual_tk ? $this->nama_tk_manual : null;
            }

            DataPendukung::create($dataPendukungPayload);

            // ================= GENERATE TAGIHAN =================
            GenerateTagihanService::generate($siswa, $this->voucher_id);

            // ================= LOG AKTIVITAS =================
            logAktivitas(
                'Pendaftaran Siswa',
                'Pendaftaran siswa ' . $siswa->nama . ' (' . $reg->nomor_registrasi . ')'
            );
        }, 3);

        return $siswa ? $siswa->id : null;
    }

    private function generateNomorRegistrasi(): string
    {
        $tahunAjaran = $this->tahun_ajaran_nama;

        if (!$tahunAjaran) {
            $tahunAjaran = TahunAjaran::whereKey($this->tahun_ajaran_id)->value('nama');
        }

        $kodeGender = $this->jenis_kelamin === 'laki-laki' ? 'L' : 'P';
        $prefix = 'PPDB-TA' . $tahunAjaran . '-' . $kodeGender;

        // Locking read to reduce duplicate risk when concurrent inserts happen.
        $lastNomor = Registration::where('tahun_ajaran_id', $this->tahun_ajaran_id)
            ->where('nomor_registrasi', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('nomor_registrasi')
            ->value('nomor_registrasi');

        $nextUrut = $lastNomor ? ((int) substr($lastNomor, -4)) + 1 : 1;

        return $prefix . str_pad((string) $nextUrut, 4, '0', STR_PAD_LEFT);
    }

    private function createRegistrationWithRetry(): Registration
    {
        $maxAttempts = 5;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                return Registration::create([
                    'nomor_registrasi' => $this->generateNomorRegistrasi(),
                    'tanggal_daftar' => $this->tanggal_daftar,
                    'tahun_ajaran_id' => $this->tahun_ajaran_id,
                    'voucher_id' => $this->voucher_id,
                    'status' => Registration::STATUS_BAKAL_CALON,
                    'input_by' => auth()->id(),
                ]);
            } catch (QueryException $e) {
                $driverCode = (int) ($e->errorInfo[1] ?? 0);

                // MySQL 1062 = duplicate key (nomor_registrasi bentrok saat insert bersamaan).
                // MySQL hanya me-rollback statement yg gagal, transaksi tetap aktif -> aman retry.
                if ($driverCode !== 1062 || $attempt === $maxAttempts) {
                    throw $e;
                }

                usleep(50000); // 50ms
            }
        }
    }

    protected function messages()
    {
        return [

            // ================= DATA UMUM =================
            'tanggal_daftar.required' => 'Tanggal daftar wajib diisi.',
            'tanggal_daftar.date' => 'Format tanggal daftar tidak valid.',
            'tahun_ajaran_id.required' => 'Tahun ajaran belum ditentukan oleh admin.',
            'tahun_ajaran_id.exists' => 'Tahun ajaran tidak valid.',
            'voucher_id.exists' => 'Voucher tidak valid.',


            // ================= SISWA =================
            'nama_siswa.required' => 'Nama siswa wajib diisi.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in' => 'Jenis kelamin tidak valid.',

            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus terdiri dari 16 digit.',
            'nik.unique' => 'NIK sudah terdaftar.',

            'no_kk.required' => 'Nomor KK wajib diisi.',
            'no_kk.digits' => 'Nomor KK harus terdiri dari 16 digit.',

            'tempat_lahir.required' => 'Tempat lahir wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid.',

            'tinggal_bersama.required' => 'Status tinggal bersama wajib dipilih.',
            'tinggal_bersama.in' => 'Status tinggal bersama tidak valid.',

            'transportasi.required' => 'Moda transportasi wajib dipilih.',

            'jenis_kebutuhan_khusus.required_if' => 'Jenis kebutuhan khusus wajib dipilih jika berkebutuhan khusus.',
            'kebutuhan_khusus_lainnya.required_if' => 'Kebutuhan khusus lainnya wajib diisi.',

            // ================= ALAMAT =================
            'alamat.required' => 'Alamat lengkap wajib diisi.',
            'provinsi.required' => 'Provinsi wajib diisi.',
            'kabupaten.required' => 'Kabupaten wajib diisi.',
            'kecamatan.required' => 'Kecamatan wajib diisi.',
            'kelurahan.required' => 'Kelurahan wajib diisi.',

            'rt.digits_between' => 'RT harus 1 sampai 4 digit.',
            'rw.digits_between' => 'RW harus 1 sampai 4 digit.',
            'kode_pos.digits_between' => 'Kode pos tidak valid.',

            // ================= IBU =================
            'ibu_nama.required' => 'Nama ibu wajib diisi.',

            'ibu_nik.digits' => 'NIK ibu harus 16 digit.',

            'ibu_hp.required' => 'Nomor HP ibu wajib diisi.',
            'ibu_hp.digits_between' => 'Nomor HP ibu harus 6 sampai 15 digit.',
            'ibu_penghasilan.required' => 'Penghasilan ibu wajib dipilih.',

            'ibu_pekerjaan_lainnya.required_if' =>
            'Pekerjaan ibu wajib diisi jika memilih "Lainnya".',

            'ibu_tahun_lahir.integer' => 'Tahun lahir ibu harus berupa angka.',
            'ibu_tahun_lahir.digits' => 'Tahun lahir ibu harus 4 digit.',
            'ibu_tahun_lahir.min' => 'Tahun lahir ibu minimal 1945.',
            'ibu_tahun_lahir.max' => 'Tahun lahir ibu tidak boleh melebihi tahun saat ini.',

            // ================= AYAH =================
            'ayah_nik.digits' => 'NIK ayah harus 16 digit.',
            'ayah_hp.digits_between' => 'Nomor HP ayah harus 6 sampai 15 digit jika diisi.',

            'ayah_tahun_lahir.integer' => 'Tahun lahir ayah harus berupa angka.',
            'ayah_tahun_lahir.digits' => 'Tahun lahir ayah harus 4 digit.',
            'ayah_tahun_lahir.min' => 'Tahun lahir ayah minimal 1945.',
            'ayah_tahun_lahir.max' => 'Tahun lahir ayah tidak boleh melebihi tahun saat ini.',

            'ayah_pekerjaan_lainnya.required_if' =>
            'Pekerjaan ayah wajib diisi jika memilih "Lainnya".',

            // ================= WALI =================
            'wali_nama.required_if' =>
            'Nama wali wajib diisi jika siswa tinggal bersama wali.',

            'wali_hubungan.required_if' =>
            'Hubungan dengan wali wajib diisi.',

            'wali_hubungan_lainnya.required_if' =>
            'Hubungan wali lainnya wajib diisi jika memilih "Lainnya".',

            'hp_wali.required' =>
            'Nomor HP wali wajib diisi.',

            'hp_wali.digits_between' =>
            'Nomor HP wali harus 6 sampai 15 digit.',

            'wali_nik.required' =>
            'NIK wali wajib diisi jika siswa tinggal bersama wali.',

            'wali_nik.digits' =>
            'NIK wali harus 16 digit.',

            'wali_tahun_lahir.required' =>
            'Tahun lahir wali wajib diisi jika siswa tinggal bersama wali.',

            'wali_tahun_lahir.integer' =>
            'Tahun lahir wali harus berupa angka.',

            'wali_tahun_lahir.digits' =>
            'Tahun lahir wali harus 4 digit.',

            'wali_tahun_lahir.min' =>
            'Tahun lahir wali minimal 1945.',

            'wali_tahun_lahir.max' =>
            'Tahun lahir wali tidak boleh melebihi tahun saat ini.',

            'wali_pekerjaan_lainnya.required_if' =>
            'Pekerjaan wali wajib diisi jika memilih "Lainnya".',

            // ================= DATA PENDUKUNG =================
            'paud_tk_id.exists' => 'Asal PAUD / TK tidak valid.',
            'anak_ke.integer' => 'Anak ke berapa harus berupa angka.',
            'anak_ke.min' => 'Anak ke berapa minimal 1.',
            'anak_ke.max' => 'Anak ke berapa maksimal 3 digit.',

            'tinggi.integer' => 'Tinggi badan harus berupa angka.',
            'tinggi.max' => 'Tinggi badan maksimal 3 digit.',

            'berat.integer' => 'Berat badan harus berupa angka.',
            'berat.max' => 'Berat badan maksimal 3 digit.',

            'jarak.regex' => 'Jarak ke sekolah harus angka maksimal 4 digit dan boleh desimal (contoh: 4,5).',

            'hasil_tes.required' => 'Hasil tes wajib dipilih.',
            'hasil_tes.in' => 'Hasil tes tidak valid.',
        ];
    }

    protected function rules()
    {
        return [

            // DATA UMUM
            'tanggal_daftar' => 'required|date',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'voucher_id' => $this->voucherValidationRules(),

            // SISWA
            'nama_siswa' => 'required|string|max:100',
            'jenis_kelamin' => 'required|in:laki-laki,perempuan',
            'nik' => 'required|digits:16|unique:siswa,nik,' . ($this->editSiswaId ?? 'NULL') . ',id',
            'no_kk' => 'required|digits:16',
            'tanggal_lahir' => 'required|date',
            'tempat_lahir' => 'required|string|max:50',

            'berkebutuhan_khusus' => 'required|in:Ya,Tidak',
            'jenis_kebutuhan_khusus' => 'required_if:berkebutuhan_khusus,Ya',
            'kebutuhan_khusus_lainnya' => 'required_if:jenis_kebutuhan_khusus,Lain-lain',

            'kps' => 'required|in:Ya,Tidak',
            'kip' => 'required|in:Ya,Tidak',
            'layak_pip' => 'required|in:Ya,Tidak',

            'tinggal_bersama' => 'required|in:orang_tua,wali',
            'transportasi' => 'required',

            // ALAMAT
            'alamat' => 'required|string',
            'provinsi' => 'required|string|max:50',
            'kabupaten' => 'required|string|max:50',
            'kecamatan' => 'required|string|max:50',
            'kelurahan' => 'required|string|max:50',
            'rt' => 'nullable|digits_between:1,4',
            'rw' => 'nullable|digits_between:1,4',
            'kode_pos' => 'nullable|digits_between:3,6',

            // IBU
            'ibu_nama' => $this->tinggal_bersama === 'wali'
                ? 'nullable|string|max:100'
                : 'required|string|max:100',
            'ibu_nik' => $this->tinggal_bersama === 'wali'
                ? 'nullable|digits:16'
                : 'nullable|digits:16',

            'ibu_hp' => $this->tinggal_bersama === 'wali'
                ? 'nullable|digits_between:6,15'
                : 'required|digits_between:6,15',
            'ibu_tahun_lahir' => 'nullable|digits:4|integer|min:1945|max:' . date('Y'),
            'ibu_pendidikan' => 'nullable|string',
            'ibu_pekerjaan' => 'nullable|string',
            'ibu_pekerjaan_lainnya' => 'required_if:ibu_pekerjaan,Lainnya',
            'ibu_penghasilan' => 'required|string',

            // AYAH
            'ayah_nik' => 'nullable|digits:16',
            'ayah_hp' => 'nullable|digits_between:6,15',
            'ayah_pekerjaan_lainnya' => 'required_if:ayah_pekerjaan,Lainnya',
            'ayah_tahun_lahir' => 'nullable|digits:4|integer|min:1945|max:' . date('Y'),

            // WALI
            'wali_nama' => 'required_if:tinggal_bersama,wali',
            'wali_hubungan' => 'required_if:tinggal_bersama,wali',
            'wali_hubungan_lainnya' => 'required_if:wali_hubungan,Lainnya|nullable|string|max:100',
            'hp_wali' => $this->tinggal_bersama === 'wali'
                ? 'required|digits_between:6,15'
                : 'nullable|digits_between:6,15',
            'wali_nik' => $this->tinggal_bersama === 'wali'
                ? 'required|digits:16'
                : 'nullable|digits:16',
            'wali_tahun_lahir' => $this->tinggal_bersama === 'wali'
                ? 'required|digits:4|integer|min:1945|max:' . date('Y')
                : 'nullable|digits:4|integer|min:1945|max:' . date('Y'),
            'wali_pendidikan' => 'nullable|string',
            'wali_pekerjaan' => 'nullable|string',
            'wali_pekerjaan_lainnya' => 'required_if:wali_pekerjaan,Lainnya|nullable|string|max:100',
            'wali_penghasilan' => 'nullable|string',

            // DATA PENDUKUNG
            'tinggi' => 'nullable|integer|max:999',
            'berat' => 'nullable|integer|max:999',
            'jarak' => ['nullable', 'regex:/^\d{1,4}([.,]\d{1,2})?$/'],
            'jumlah_saudara' => 'nullable|integer|max:99',
            'anak_ke' => 'nullable|integer|min:1|max:999',

            'paud_tk_id' => 'nullable|exists:paud_tk,id',
            'is_manual_tk' => 'nullable|boolean',
            'nama_tk_manual' => $this->is_manual_tk ? 'required|string|max:150' : 'nullable|string|max:150',
            'alamat_tk' => $this->is_manual_tk ? 'required|string|max:255' : 'nullable|string|max:255',
            'hasil_tes' => 'required|in:SB,B,PB,belum test',
        ];
    }

    private function normalizeJarakValue($value): ?float
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $normalized = str_replace(',', '.', trim((string) $value));

        if (!is_numeric($normalized)) {
            return null;
        }

        return round((float) $normalized, 2);
    }

    public function updatedPaudTkId($id)
    {
        if (!$id) {
            if (!$this->is_manual_tk) {
                $this->alamat_tk = null;
            }
            return;
        }

        $paud = PaudTk::find($id);

        if ($paud) {
            $this->is_manual_tk = false;
            $this->nama_tk_manual = null;
            $this->alamat_tk = "{$paud->kelurahan} - {$paud->kecamatan}";
        }
    }

    public function render()
    {
        return view('livewire.pendaftaran-wizard', [
            'paud' => PaudTk::where('aktif', true)->get(),
            'vouchers' => Voucher::orderBy('nama')->get(),
        ])->layout('layouts.public');
    }

    private function hasDataPendukungColumn(string $column): bool
    {
        if ($this->dataPendukungColumns === null) {
            if (!Schema::hasTable('data_pendukung')) {
                $this->dataPendukungColumns = [];
            } else {
                $this->dataPendukungColumns = Schema::getColumnListing('data_pendukung');
            }
        }

        return in_array($column, $this->dataPendukungColumns, true);
    }

    private function friendlyDatabaseErrorMessage(QueryException $e): string
    {
        $sqlState = $e->errorInfo[0] ?? null;
        $driverCode = (int) ($e->errorInfo[1] ?? 0);
        $message = strtolower($e->getMessage());

        // Common schema issues on hosting: migrations not applied.
        // MySQL: 42S02 = table not found, 42S22 = column not found.
        if ($sqlState === '42S02') {
            return 'Tabel database belum tersedia (migrasi belum dijalankan). Jalankan: php artisan migrate --force';
        }

        if ($sqlState === '42S22') {
            return 'Kolom database belum tersedia (skema DB tidak sama dengan source). Jalankan: php artisan migrate --force';
        }

        // Data too long / invalid data.
        if ($sqlState === '22001' || str_contains($message, 'data too long')) {
            return 'Ada input yang terlalu panjang untuk kolom database. Periksa kembali teks yang diisi lalu simpan ulang.';
        }

        if ($sqlState === '23000' || $driverCode === 1062) {
            if (str_contains($message, 'siswa') && str_contains($message, 'nik')) {
                return 'NIK sudah terdaftar. Silakan periksa kembali data siswa.';
            }

            if (str_contains($message, 'nomor_registrasi')) {
                return 'Nomor registrasi bentrok saat penyimpanan. Silakan coba simpan kembali.';
            }

            return 'Data yang sama sudah tersimpan. Silakan periksa kembali lalu coba lagi.';
        }

        return 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.';
    }
}
