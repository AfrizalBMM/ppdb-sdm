<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property string $nama
 * @property bool $aktif
 * @method static \Illuminate\Database\Eloquent\Builder|TahunAjaran aktif()
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class TahunAjaran extends Model
{
    protected $table = 'tahun_ajaran';

    protected $fillable = [
        'nama',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    public function biaya()
    {
        return $this->hasMany(Biaya::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeAktif(Builder $query)
    {
        return $query->where('aktif', true);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER
    |--------------------------------------------------------------------------
    */

    public static function aktifSekarang()
    {
        return self::aktif()->first();
    }
}