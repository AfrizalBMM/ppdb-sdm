<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';

    protected $fillable = [

        'tagihan_siswa_id',
        'tanggal_bayar',
        'nominal_bayar',
        'metode',
        'admin_penerima',
        'keterangan'

    ];

    protected $casts = [
        'tanggal_bayar' => 'date',
        'nominal_bayar' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function tagihan()
    {
        return $this->belongsTo(TagihanSiswa::class, 'tagihan_siswa_id');
    }

}