<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ayah extends Model
{
    protected $table = 'ayah';

    protected $fillable = [
        'siswa_id',
        'nama',
        'nik',
        'no_hp',
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
