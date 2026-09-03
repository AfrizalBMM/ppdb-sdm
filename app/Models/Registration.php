<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    public const STATUS_BAKAL_CALON = 1;
    public const STATUS_CALON = 2;
    public const STATUS_PESERTA_DIDIK = 3;

    protected $fillable = [
        'nomor_registrasi',
        'tanggal_daftar',
        'tahun_ajaran_id',
        'voucher_id',
        'status',
        'input_by',
    ];

    protected $casts = [
        'tanggal_daftar' => 'date',
        'status' => 'integer',
    ];

    public static function statusLabel(?int $status): string
    {
        return match ((int) $status) {
            self::STATUS_BAKAL_CALON => 'Bakal Calon',
            self::STATUS_CALON => 'Calon',
            self::STATUS_PESERTA_DIDIK => 'Peserta Didik',
            default => 'Bakal Calon',
        };
    }

    // ================= RELATIONS =================

    public function siswa()
    {
        return $this->hasOne(Siswa::class);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
}