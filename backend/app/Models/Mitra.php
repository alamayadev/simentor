<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

class Mitra extends Model
{
    use HasFactory;

    protected $table = 'mitra_kepka';
    public $timestamps = false;

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::addGlobalScope('exclude_rejected', function ($query) {
            $query->where('status_seleksi', '!=', 'Tidak Diterima');
        });
    }

    protected $fillable = [
        'email',
        'sobat_id',
        'posisi',
        'status_seleksi',
        'posisi_daftar',
        'nama_lengkap',
        'alamat_detail',
        'alamat_prov',
        'alamat_kab',
        'alamat_kec',
        'alamat_desa',
        'tgl_lahir',
        'jenis_kelamin',
        'agama',
        'status_kawin',
        'pendidikan',
        'pekerjaan',
        'deskripsi_pekerjaan_lain',
        'no_telp',
        'npwp',
        'kepemilikan_motor',
        'kemampuan_berkendara_motor',
        'pernah_capi',
        'kepemilikan_hp_android',
        'merk_hp',
        'tipe_hp',
        'ram_hp',
        'kepemilikan_laptop',
        'kemampuan_komputer',
        'mitra_eksternal',
        'nama_k_l_lain',
        'catatan',
        'nilai_ujian',
        'waktu_mulai',
        'waktu_submit',
        'durasi_menit',
        'remedial',
        'kabid',
        'kab',
        'kecid',
        'keca',
        'desaid',
        'desa',
        'nik',
        'foto',
        'foto_ktp',
        'ijazah',
        'cek_kepka'
    ];

    protected $appends = ['umur'];

    protected $casts = [
        'tgl_lahir' => 'date:Y-m-d',
    ];

    public function getUmurAttribute(): ?int
    {
        if (! $this->tgl_lahir) {
            return null;
        }

        return Carbon::parse($this->tgl_lahir)->age;
    }

    public function penugasan(): HasMany
    {
        return $this->hasMany(Penugasan::class);
    }
    public function pcl(): HasMany
    {
        return $this->hasMany(Sls2024::class,'pcl_id');
    }
    public function pml(): HasMany
    {
        return $this->hasMany(Sls2024::class,'pml_id');
    }
    public function peta(): HasMany
    {
        return $this->hasMany(Sls2024::class,'pcl_id');
    }
    public function scopeSearch($query, $value){
        $query->where('nama_lengkap','like',"%{$value}%")->orWhere('nik','like',"%{$value}%");
    }
}
