<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property int $siswa_id
 * @property int $biaya_id
 * @property int $nominal
 * @property int $diskon
 * @property int $total
 * @property string $status
 * @property int|null $voucher_id
 * @property string|null $kode_voucher
 * @property int $total_dibayar
 * @property int $sisa
 * @property bool $is_lunas
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Pembayaran[] $pembayaran
 * @method static \Illuminate\Database\Eloquent\Builder|TagihanSiswa belumLunas()
 * @method static \Illuminate\Database\Eloquent\Builder|TagihanSiswa lunas()
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class TagihanSiswa extends Model
{
    protected $table = 'tagihan_siswa';

    protected $fillable = [
        'siswa_id',
        'biaya_id',
        'nominal',
        'diskon',
        'total',
        'sisa',
        'status',
        'voucher_id',
        'kode_voucher',
        'is_lunas',
    ];

    protected $casts = [
        'nominal' => 'integer',
        'diskon'  => 'integer',
        'total'   => 'integer',
        'sisa'    => 'integer',
        'is_lunas' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function biaya()
    {
        return $this->belongsTo(Biaya::class);
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeBelumLunas(Builder $query)
    {
        return $query->where('status', 'belum_lunas');
    }

    public function scopeLunas(Builder $query)
    {
        return $query->where('status', 'lunas');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getTotalDibayarAttribute()
    {
        if ($this->relationLoaded('pembayaran')) {
            return $this->pembayaran->sum('nominal_bayar');
        }

        return $this->pembayaran()->sum('nominal_bayar');
    }

    public function getSisaAttribute()
    {
        return max(0, $this->total - $this->total_dibayar);
    }

    public function getIsLunasAttribute()
    {
        return $this->sisa <= 0;
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER
    |--------------------------------------------------------------------------
    */

    public function refreshStatus()
    {
        $sisa = max(0, (int) $this->total - (int) $this->total_dibayar);
        $isLunas = $sisa <= 0;

        // Persist computed fields for reporting queries that use DB columns.
        $this->sisa = $sisa;
        $this->is_lunas = $isLunas;
        $this->status = $isLunas ? 'lunas' : 'belum_lunas';

        $this->save();
    }
}