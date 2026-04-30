<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Models\LaporanPerjalananDinas;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\Request;

class LaporanPerjalananDinasPdfController extends Controller
{
    private const A4_WIDTH = 595;
    private const A4_HEIGHT = 842;
    private const MARGIN = 56;
    private const HEADER_HEIGHT = 60;
    private const GAP = 10;
    private const CAPTION_HEIGHT = 20;

    /**
     * Generate PDF document for travel report
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function generate($id)
    {
        $laporan = LaporanPerjalananDinas::with(['details', 'dokumentasi'])->findOrFail($id);
        
        // Calculate signature date (last date of travel)
        $lastDate = $laporan->details->max('tanggal');
        $formattedDate = '';
        
        if ($lastDate) {
            $date = \Carbon\Carbon::parse($lastDate);
            $months = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            $formattedDate = $date->day . ' ' . $months[$date->month] . ' ' . $date->year;
        } else {
            $formattedDate = '....................';
        }

        $imageLayout = $this->processDocumentationImages($laporan->dokumentasi);

        $pdf = PDF::loadView('pdf.laperdin', compact('laporan', 'imageLayout', 'formattedDate'))
            ->setPaper('a4', 'portrait');

        $filename = 'Laporan_Perjalanan_Dinas_' . $id . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Process documentation images with mosaic auto-sizing algorithm
     *
     * This algorithm automatically calculates optimal image dimensions to fit
     * all images on one page without changing their aspect ratios.
     */
    public function processDocumentationImages($images)
    {
        $availableWidth = self::A4_WIDTH - (2 * self::MARGIN);
        $availableHeight = self::A4_HEIGHT - self::MARGIN - self::HEADER_HEIGHT - self::MARGIN;

        $imageCount = $images->count();

        switch ($imageCount) {
            case 1:
                return $this->layoutSingle($images, $availableWidth, $availableHeight);
            case 2:
                return $this->layoutMosaic($images, $availableWidth, $availableHeight, [2]);
            case 3:
                return $this->layoutMosaic($images, $availableWidth, $availableHeight, [2, 1]);
            case 4:
                return $this->layoutMosaic($images, $availableWidth, $availableHeight, [2, 2]);
            case 5:
                return $this->layoutMosaic($images, $availableWidth, $availableHeight, [2, 3]);
            case 6:
                return $this->layoutMosaic($images, $availableWidth, $availableHeight, [3, 3]);
            default:
                // For 7+ images, use a generic grid of 4 columns
                $rows = ceil($imageCount / 4);
                $structure = array_fill(0, intval($rows), 4);
                return $this->layoutMosaic($images, $availableWidth, $availableHeight, $structure);
        }
    }

    /**
     * Mosaic Layout: Flexible rows with variable item counts.
     * e.g. [2, 3] means Row 1 has 2 items, Row 2 has 3 items.
     */
    private function layoutMosaic($images, $availableWidth, $availableHeight, $rowStructure)
    {
        $imageList = $images->values();
        $layouts = [];
        $rowConfigs = [];
        
        $imageIndex = 0;
        $totalHeightRaw = 0;

        // Pass 1: Calculate dimensions based on full width availability
        foreach ($rowStructure as $cols) {
            if ($imageIndex >= count($imageList)) break;

            $gapTotal = ($cols - 1) * self::GAP;
            $cellWidth = ($availableWidth - $gapTotal) / $cols;
            
            $rowMaxHeight = 0;
            $rowImages = [];

            for ($i = 0; $i < $cols; $i++) {
                if ($imageIndex >= count($imageList)) break;

                $image = $imageList[$imageIndex];
                // Prevent division by zero if width/height missing
                $w = $image->width ?: 800;
                $h = $image->height ?: 600;
                $ratio = $w / $h;
                
                $calcHeight = $cellWidth / $ratio;
                $rowMaxHeight = max($rowMaxHeight, $calcHeight);
                
                $rowImages[] = [
                    'image' => $image,
                    'width' => $cellWidth,
                    'height' => $calcHeight, // Natural height for this width
                    'ratio' => $ratio
                ];
                $imageIndex++;
            }

            $rowConfigs[] = [
                'height' => $rowMaxHeight,
                'items' => $rowImages,
                'cols' => $cols,
                'cellWidth' => $cellWidth
            ];
            
            $totalHeightRaw += $rowMaxHeight;
        }

        // Add vertical gaps (GAP + CAPTION_HEIGHT) to prevent overlap
        $verticalGap = self::GAP + self::CAPTION_HEIGHT;
        $totalVerticalGaps = (count($rowConfigs) - 1) * $verticalGap;
        $totalContentHeight = $totalHeightRaw + $totalVerticalGaps;

        // Pass 2: Calculate Scale Factor if content exceeds height
        $scale = 1.0;
        if ($totalContentHeight > $availableHeight) {
            $scale = $availableHeight / $totalContentHeight;
        }

        // Pass 3: specific positioning
        $finalTotalHeight = $totalContentHeight * $scale;
        $startY = self::MARGIN + self::HEADER_HEIGHT + ($availableHeight - $finalTotalHeight) / 2;
        $currentY = $startY;

        foreach ($rowConfigs as $row) {
            $rowHeight = $row['height'] * $scale;
            $colWidth = $row['cellWidth'] * $scale;
            
            $currentX = self::MARGIN;
            
            foreach ($row['items'] as $index => $item) {
                $finalWidth = $colWidth;
                $finalHeight = $item['height'] * $scale;
                
                // Center vertically in the row
                $yOffset = ($rowHeight - $finalHeight) / 2;
                
                $layouts[] = [
                    'path' => $item['image']->file_path,
                    'x' => $currentX,
                    'y' => $currentY + $yOffset,
                    'width' => $finalWidth,
                    'height' => $finalHeight
                ];
                
                $currentX += $finalWidth + (self::GAP * $scale);
            }
            
            // Add vertical gap including caption space
            $currentY += $rowHeight + ($verticalGap * $scale);
        }

        return $layouts;
    }

    /**
     * Single Image (Full Size, Centered)
     */
    private function layoutSingle($images, $availableWidth, $availableHeight)
    {
        $image = $images->first();
        // Prevent zero division
        $w = $image->width ?: 800;
        $h = $image->height ?: 600;
        $ratio = $w / $h;

        $width = $availableWidth * 0.9;
        $height = $width / $ratio;

        if ($height > $availableHeight) {
            $height = $availableHeight * 0.9;
            $width = $height * $ratio;
        }

        $x = self::MARGIN + ($availableWidth - $width) / 2;
        $y = self::MARGIN + self::HEADER_HEIGHT + ($availableHeight - $height) / 2;

        return [[
            'path' => $image->file_path,
            'x' => $x,
            'y' => $y,
            'width' => $width,
            'height' => $height,
        ]];
    }
}
