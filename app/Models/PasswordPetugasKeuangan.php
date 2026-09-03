<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordPetugasKeuangan extends Model
{
    protected $table = 'password_petugas_keuangan';

    protected $fillable = [
        'nama',
        'password',
    ];
}
