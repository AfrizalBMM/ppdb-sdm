<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KelasSiswa extends Model
{
    protected $table = 'kelas_siswa';

    protected $fillable = [
        'nama_kelas',
    ];

    public function siswa()
    {
        return $this->hasMany(Siswa::class, 'kelas_siswa_id');
    }
}
