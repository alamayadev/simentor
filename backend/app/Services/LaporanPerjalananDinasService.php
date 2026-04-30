<?php

namespace App\Services;

use App\Models\LaporanPerjalananDinas;
use App\Models\LaporanPerjalananDinasDetail;
use App\Models\LaporanPerjalananDinasDokumentasi;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class LaporanPerjalananDinasService
{
    public function listLaporan(int $perPage = 20): LengthAwarePaginator
    {
        $version = Cache::get('laperdin_last_modified', 'init');
        $page = request('page', 1);
        $cacheKey = "laperdin_v{$version}_page_{$page}_per_{$perPage}";

        return Cache::remember($cacheKey, 600, function () use ($perPage) {
            return LaporanPerjalananDinas::with(['details', 'dokumentasi'])
                ->orderBy('created_at', 'desc')
                ->select([
                    'id', 'nama_traveler', 'tujuan', 'lama_tanggal', 'dalam_rangka',
                    'pembebanan', 'kode_keg', 'status', 'user_id',
                    'created_at', 'updated_at'
                ])
                ->fastPaginate($perPage);
        });
    }

    public function createLaporan(array $data, ?int $userId): LaporanPerjalananDinas
    {
        $laporan = LaporanPerjalananDinas::create(array_merge($data, [
            'user_id' => $userId ?? 1,
            'status' => $data['status'] ?? 'draft',
        ]));

        $this->invalidateLaperdinCaches();
        return $laporan;
    }

    public function updateLaporan(LaporanPerjalananDinas $laporan, array $data): LaporanPerjalananDinas
    {
        $laporan->update($data);
        $this->invalidateLaperdinCaches($laporan->id);
        return $laporan;
    }

    public function deleteLaporan(LaporanPerjalananDinas $laporan): void
    {
        DB::transaction(function () use ($laporan) {
            foreach ($laporan->dokumentasi as $doc) {
                $this->deleteFile($doc->file_path);
            }
            $laporan->delete();
            $this->invalidateLaperdinCaches($laporan->id);
        });
    }

    public function createDetail(LaporanPerjalananDinas $laporan, array $data): LaporanPerjalananDinasDetail
    {
        $detail = $laporan->details()->create($data);
        Cache::forget("laperdin_detail_{$laporan->id}");
        return $detail;
    }

    public function updateDetail(LaporanPerjalananDinasDetail $detail, array $data): LaporanPerjalananDinasDetail
    {
        $detail->update($data);
        Cache::forget("laperdin_detail_{$detail->laporan_perjalanan_dinas_id}");
        return $detail;
    }

    public function deleteDetail(LaporanPerjalananDinasDetail $detail): void
    {
        $laporanId = $detail->laporan_perjalanan_dinas_id;
        $detail->delete();
        Cache::forget("laperdin_detail_{$laporanId}");
    }

    public function uploadDokumentasi(LaporanPerjalananDinas $laporan, $file, ?string $deskripsi): LaporanPerjalananDinasDokumentasi
    {
        $extension = $file->getClientOriginalExtension();
        $filename = 'laperdin_' . $laporan->id . '_' . time() . '_' . uniqid() . '.' . $extension;
        $path = 'laperdin/' . $filename;

        $this->processAndSaveImage($file, $filename);

        $maxUrutan = $laporan->dokumentasi()->max('urutan') ?? 0;

        $dokumentasi = $laporan->dokumentasi()->create([
            'file_path' => $path,
            'deskripsi' => $deskripsi,
            'urutan' => $maxUrutan + 1,
        ]);

        Cache::forget("laperdin_detail_{$laporan->id}");
        return $dokumentasi;
    }

    public function updateDokumentasi(LaporanPerjalananDinasDokumentasi $dokumentasi, $file = null, ?string $deskripsi = null): LaporanPerjalananDinasDokumentasi
    {
        if ($file) {
            $this->deleteFile($dokumentasi->file_path);
            
            $extension = $file->getClientOriginalExtension();
            $filename = 'laperdin_' . $dokumentasi->laporan_perjalanan_dinas_id . '_' . time() . '_' . uniqid() . '.' . $extension;
            $path = 'laperdin/' . $filename;

            $this->processAndSaveImage($file, $filename);
            $dokumentasi->file_path = $path;
        }

        if ($deskripsi !== null) {
            $dokumentasi->deskripsi = $deskripsi;
        }

        $dokumentasi->save();
        Cache::forget("laperdin_detail_{$dokumentasi->laporan_perjalanan_dinas_id}");
        return $dokumentasi;
    }

    public function deleteDokumentasi(LaporanPerjalananDinasDokumentasi $dokumentasi): void
    {
        $laporanId = $dokumentasi->laporan_perjalanan_dinas_id;
        $this->deleteFile($dokumentasi->file_path);
        $dokumentasi->delete();
        Cache::forget("laperdin_detail_{$laporanId}");
    }

    protected function processAndSaveImage($file, string $filename): void
    {
        $maxWidth = 1200;
        $maxHeight = 1200;
        $quality = 80;

        $sourcePath = $file->getRealPath();
        $mime = $file->getMimeType();

        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $image = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($sourcePath);
                break;
            case 'image/webp':
                $image = imagecreatefromwebp($sourcePath);
                break;
            default:
                Storage::disk('direct')->putFileAs('laperdin', $file, $filename);
                return;
        }

        if (!$image) {
            Storage::disk('direct')->putFileAs('laperdin', $file, $filename);
            return;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        if ($width > $maxWidth || $height > $maxHeight) {
            $ratio = $width / $height;
            if ($width > $height) {
                $newWidth = $maxWidth;
                $newHeight = $maxWidth / $ratio;
            } else {
                $newHeight = $maxHeight;
                $newWidth = $maxHeight * $ratio;
            }
        } else {
            $newWidth = $width;
            $newHeight = $height;
        }

        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        if ($mime == 'image/png' || $mime == 'image/webp') {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
        
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                imagejpeg($newImage, $tempPath, $quality);
                break;
            case 'image/png':
                imagepng($newImage, $tempPath, 6); 
                break;
            case 'image/webp':
                imagewebp($newImage, $tempPath, $quality);
                break;
        }

        imagedestroy($image);
        imagedestroy($newImage);

        Storage::disk('direct')->put('laperdin/' . $filename, file_get_contents($tempPath));
        @unlink($tempPath);
    }

    protected function deleteFile(string $filePath): void
    {
        if (Storage::disk('direct')->exists($filePath)) {
            Storage::disk('direct')->delete($filePath);
        } elseif (Storage::exists($filePath)) {
            Storage::delete($filePath);
        }
    }

    public function invalidateLaperdinCaches(?int $id = null): void
    {
        Cache::forever('laperdin_last_modified', time());
        if ($id) {
            Cache::forget("laperdin_detail_{$id}");
        }
    }
}
