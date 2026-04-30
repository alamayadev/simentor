<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CekScan;
use App\Models\CekGeoref;
use App\Models\SlsSipw;

class Alokasi extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'alokasis';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'idsls',
        'kdprov',
        'kdkab',
        'kdkec',
        'kddesa',
        'kdsls',
        'nmprov',
        'nmkab',
        'nmkec',
        'nmdesa',
        'nmsls',
        'periode',
        'idsubsls',
        'iddesa',
        'alokasi_scan',
        'alokasi_georef',
        'alokasi_geojson',
        'alokasi_muatan',
    ];

    /**
     * Expose parsed operator from alokasi_geojson when present
     */
    protected $appends = ['operator'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * One-to-one relation to CekScan (Alokasi.idsls -> CekScan.kode)
     */
    public function cekScan()
    {
        return $this->hasOne(CekScan::class, 'kode', 'idsls');
    }

    /**
     * One-to-one relation to CekGeoref (Alokasi.idsls -> CekGeoref.kode)
     */
    public function cekGeoref()
    {
        return $this->hasOne(CekGeoref::class, 'kode', 'idsls');
    }

    /**
     * One-to-one relation to SlsSipw (Alokasi.idsls -> SlsSipw.idsls)
     */
    public function slsSipw()
    {
        return $this->hasOne(SlsSipw::class, 'idsls', 'idsls');
    }

    /**
     * Return operator parsed from alokasi_geojson payload.
     * Handles JSON objects, scalar JSON, and plain-text payloads.
     */
    public function getOperatorAttribute()
    {
        if (empty($this->alokasi_geojson)) {
            return null;
        }

        $json = $this->alokasi_geojson;
        $data = @json_decode($json, true);

        if (is_array($data)) {
            $paths = [
                ['operator'], ['name'], ['nama'],
                ['properties', 'operator'], ['properties', 'name'], ['properties', 'nama'],
                ['features', 0, 'properties', 'operator'], ['features', 0, 'properties', 'name'], ['features', 0, 'properties', 'nama'],
            ];
            foreach ($paths as $path) {
                $value = $data;
                foreach ($path as $key) {
                    if (is_array($value) && array_key_exists($key, $value)) {
                        $value = $value[$key];
                    } else {
                        $value = null;
                        break;
                    }
                }
                if ($value !== null && $value !== '') {
                    return $value;
                }
            }
            $candidates = ['operator', 'name', 'nama'];
            foreach ($candidates as $c) {
                if (array_key_exists($c, $data) && $data[$c]) {
                    return $data[$c];
                }
            }
        }

        if (is_scalar($data) && $data !== '') {
            return (string) $data;
        }

        $trim = trim($json, " \t\n\r\0\x0B\"'");
        if ($trim !== '') {
            return $trim;
        }

        return null;
    }
}
