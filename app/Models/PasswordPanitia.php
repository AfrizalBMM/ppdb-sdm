<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordPanitia extends Model
{
    protected $table = 'password_panitia';

    protected $fillable = [
        'nama',
        'password',
    ];
}
