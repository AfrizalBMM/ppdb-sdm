<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wali extends Model
{
    protected $table = 'wali';

    protected $fillable = [
        'siswa_id',
        'nama',
        'hubungan',
        'hubungan_lainnya',
        'no_hp',
        'nik',
        'tahun_lahir',
        'pendidikan',
        'pekerjaan',
        'pekerjaan_lainnya',
        'penghasilan',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
