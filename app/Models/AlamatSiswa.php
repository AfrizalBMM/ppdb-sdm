<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlamatSiswa extends Model
{
    protected $table = 'alamat_siswa';

    protected $fillable = [
        'siswa_id',
        'alamat',
        'provinsi',
        'kabupaten',
        'kecamatan',
        'kelurahan',
        'rt',
        'rw',
        'kode_pos',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
