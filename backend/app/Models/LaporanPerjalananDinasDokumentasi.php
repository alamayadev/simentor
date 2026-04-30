<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LaporanPerjalananDinasDokumentasi extends Model
{
    use HasFactory;

    protected $table = 'laporan_perjalanan_dinas_dokumentasi';

    protected $guarded = ['id'];

    public function getWidthAttribute()
    {
        // Default width if file doesn't exist
        $defaultWidth = 800;
        
        try {
            $path = public_path($this->file_path);
            if (!file_exists($path)) {
                $path = storage_path('app/public/' . $this->file_path);
            }
            
            if (file_exists($path)) {
                $size = getimagesize($path);
                return $size[0] ?? $defaultWidth;
            }
        } catch (\Exception $e) {
            // ignore
        }
        
        return $defaultWidth;
    }

    public function getHeightAttribute()
    {
        // Default height if file doesn't exist
        $defaultHeight = 600;

        try {
            $path = public_path($this->file_path);
            if (!file_exists($path)) {
                $path = storage_path('app/public/' . $this->file_path);
            }

            if (file_exists($path)) {
                $size = getimagesize($path);
                return $size[1] ?? $defaultHeight;
            }
        } catch (\Exception $e) {
            // ignore
        }

        return $defaultHeight;
    }
}
