<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class LogAktivitas extends Model
{
    use HasFactory;

    protected $table = 'log_aktivitas';

    protected $fillable = [
        'user_id',
        'role',
        'aksi',
        'keterangan',
        'ip_address',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeByUser(Builder $query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByRole(Builder $query, $role)
    {
        return $query->where('role', $role);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER STATIC
    |--------------------------------------------------------------------------
    */

    public static function catat($aksi, $keterangan = null)
    {
        self::create([
            'user_id'    => auth()->id(),
            'role'       => auth()->user()?->role,
            'aksi'       => $aksi,
            'keterangan' => $keterangan,
            'ip_address' => request()->ip(),
        ]);
    }
}