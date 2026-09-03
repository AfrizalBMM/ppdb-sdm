<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ibu extends Model
{
    protected $table = 'ibu';

    protected $fillable = [
        'siswa_id',
        'nama',
        'nik',
        'tahun_lahir',
        'pendidikan',
        'pekerjaan',
        'pekerjaan_lainnya',
        'penghasilan',
        'no_hp',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}