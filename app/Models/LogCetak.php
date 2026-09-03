<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogCetak extends Model
{
    protected $table = 'log_cetak';

    protected $fillable = [
        'siswa_id',
        'jenis_dokumen',
        'nama_petugas'
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
