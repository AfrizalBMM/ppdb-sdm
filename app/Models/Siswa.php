<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $table = 'siswa';

    protected $fillable = [
        'registration_id',
        'kelas_siswa_id',
        'nama',
        'jenis_kelamin',
        'nisn',
        'nik',
        'no_kk',
        'tempat_lahir',
        'tanggal_lahir',
        'akta_no',

        'agama',
        'kewarganegaraan',
        'berkebutuhan_khusus',
        'tinggal_bersama',
        'transportasi',

        'no_kks',
        'kps',
        'kip',
        'layak_pip',

        'hasil_tes',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    // ================= RELATIONS =================

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }


        // Pembayaran diakses melalui tagihan
        public function semuaPembayaran()
        {
            return $this->hasManyThrough(
                \App\Models\Pembayaran::class,
                \App\Models\TagihanSiswa::class,
                'siswa_id', // Foreign key di tagihan_siswa
                'tagihan_siswa_id', // Foreign key di pembayaran
                'id', // Local key di siswa
                'id' // Local key di tagihan_siswa
            );
        }

    public function kelasSiswa()
    {
        return $this->belongsTo(KelasSiswa::class, 'kelas_siswa_id');
    }

    public function alamat()
    {
        return $this->hasOne(AlamatSiswa::class);
    }

    public function ibu()
    {
        return $this->hasOne(Ibu::class);
    }

    public function ayah()
    {
        return $this->hasOne(Ayah::class);
    }

    public function wali()
    {
        return $this->hasOne(Wali::class);
    }

    public function dataPendukung()
    {
        return $this->hasOne(DataPendukung::class);
    }

    public function tagihan()
    {
        return $this->hasMany(TagihanSiswa::class);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function logCetak()
    {
        return $this->hasMany(LogCetak::class);
    }

}