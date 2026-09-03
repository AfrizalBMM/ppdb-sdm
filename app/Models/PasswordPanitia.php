<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordPanitia extends Model
{
    protected $table = 'password_panitia';

    protected $fillable = [
        'tahun_ajaran_id',
        'password'
    ];

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }
}