<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PaudTk extends Model
{
    protected $table = 'paud_tk';

    protected $fillable = [
        'npsn',
        'nama',
        'jenis',
        'alamat',
        'kelurahan',
        'kecamatan',
        'telp',
        'akreditasi',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function dataPendukung()
    {
        return $this->hasMany(DataPendukung::class, 'paud_tk_id');
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
}