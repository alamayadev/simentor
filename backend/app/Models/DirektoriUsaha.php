<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DirektoriUsaha extends Model
{
    protected $table = 'direktori_usaha';

    protected $fillable = [
        'idsbr',
        'nama_usaha',
        'alamat_usaha',
        'kode_wilayah',
        'kdprov',
        'kdkab',
        'kdkec',
        'kddesa',
        'nmprov',
        'nmkab',
        'nmkec',
        'nmdesa',
        'perusahaan_id',
        'status_perusahaan',
        'skor_kalo',
        'kegiatan_usaha',
        'rank_nama',
        'rank_alamat',
        'history_ref_profiling_id',
        'skala_usaha',
        'sumber_data',
        'latitude',
        'longitude',
        'latlong_status',
        'hasilgc',
        'gcid',
        'gcs_result',
        'allow_cancel',
        'allow_edit',
        'allow_flagging',
        'latitude_gc',
        'longitude_gc',
        'latlong_status_gc',
        'gc_username',
        'nama_usaha_gc',
        'alamat_usaha_gc',
        'name_similarity',
        'update_by',
    ];

    protected $casts = [
        'allow_cancel' => 'boolean',
        'allow_edit' => 'boolean',
        'allow_flagging' => 'boolean',
        'name_similarity' => 'decimal:2',
        'history_ref_profiling_id' => 'date',
    ];

    /**
     * Scope untuk filter by wilayah
     */
    public function scopeByWilayah($query, $kdprov = null, $kdkab = null, $kdkec = null, $kddesa = null)
    {
        return $query
            ->when($kdprov, fn($q) => $q->where('kdprov', $kdprov))
            ->when($kdkab, fn($q) => $q->where('kdkab', $kdkab))
            ->when($kdkec, fn($q) => $q->where('kdkec', $kdkec))
            ->when($kddesa, fn($q) => $q->where('kddesa', $kddesa));
    }

    /**
     * Scope untuk filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status_perusahaan', $status);
    }

    /**
     * Scope untuk filter yang memiliki koordinat valid
     */
    /**
     * Scope untuk filter yang memiliki koordinat valid
     */
    public function scopeHasValidCoordinates($query)
    {
        return $query->where('latlong_status', 'valid')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');
    }

    /**
     * Relationship with User model (updater)
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'update_by');
    }
}
