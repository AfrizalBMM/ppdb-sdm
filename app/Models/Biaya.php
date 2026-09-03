<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property int $tahun_ajaran_id
 * @property string $jenis_biaya
 * @property string $kategori
 * @property string $jenis_kelamin
 * @property string $nama_biaya
 * @property int $nominal
 * @property bool $aktif
 * @method static \Illuminate\Database\Eloquent\Builder|Biaya aktif()
 * @method static \Illuminate\Database\Eloquent\Builder|Biaya untukTahun($tahunAjaranId)
 * @method static \Illuminate\Database\Eloquent\Builder|Biaya untukJenisKelamin($jenisKelamin)
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Biaya extends Model
{
    protected $table = 'biaya';

    protected $fillable = [
        'tahun_ajaran_id',
        'jenis_biaya',
        'kategori',
        'jenis_kelamin',
        'nama_biaya',
        'nominal',
        'aktif',
        'is_acuan_status_ppdb',
    ];

    protected $casts = [
        'nominal' => 'integer',
        'aktif'   => 'boolean',
        'is_acuan_status_ppdb' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function tagihan()
    {
        return $this->hasMany(TagihanSiswa::class, 'biaya_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeAktif(Builder $query)
    {
        return $query->where('aktif', true);
    }

    public function scopeUntukTahun(Builder $query, $tahunAjaranId)
    {
        return $query->where('tahun_ajaran_id', $tahunAjaranId);
    }

    public function scopeUntukJenisKelamin(Builder $query, $jenisKelamin)
    {
        return $query->where(function ($q) use ($jenisKelamin) {
            $q->where('jenis_kelamin', $jenisKelamin)
              ->orWhere('jenis_kelamin', 'semua');
        });
    }
}