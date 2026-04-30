<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\Kegiatan;
use App\Models\Penugasan;
use App\Models\Skp;
use App\Models\Pegawai;
use App\Models\SuratKeluar;
use App\Models\Mitra;

class LandingApiController extends Controller
{
    /**
     * Get landing page data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $jsonPath = public_path('data/landing-page.json');
            
            if (!File::exists($jsonPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Landing page data not found'
                ], 404);
            }

            $jsonData = File::get($jsonPath);
            $data = json_decode($jsonData, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid JSON format: ' . json_last_error_msg()
                ], 500);
            }

            // Validate and normalize the data structure
            $normalizedData = $this->normalizeLandingData($data);

            return response()->json([
                'success' => true,
                'data' => $normalizedData,
                'meta' => [
                    'version' => '1.0',
                    'last_updated' => date('Y-m-d H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Normalize and validate landing page data structure
     *
     * @param array $data
     * @return array
     */
    private function normalizeLandingData(array $data): array
    {
        return [
            'metrics' => $this->validateMetrics($data['metrics'] ?? []),
            'features' => $this->validateFeatures($data['features'] ?? []),
            'integrations' => $this->validateIntegrations($data['integrations'] ?? []),
            'statistics' => $this->validateStatistics($data['statistics'] ?? [])
        ];
    }

    /**
     * Validate metrics data - returns real data from models
     */
    private function validateMetrics(array $metrics): array
    {
        $currentMonth = date('m');
        $currentYear = date('Y');
        $lastMonth = date('m', strtotime('-1 month'));
        $lastMonthYear = date('Y', strtotime('-1 month'));
        $twoMonthsAgo = date('m', strtotime('-2 months'));
        $twoMonthsAgoYear = date('Y', strtotime('-2 months'));
        
        // 1. Surat Keluar - current month vs last month
        $suratKeluarCurrent = SuratKeluar::where('bln', $currentMonth)
            ->where('thn', $currentYear)
            ->count();
        $suratKeluarLast = SuratKeluar::where('bln', $lastMonth)
            ->where('thn', $lastMonthYear)
            ->count();
        $suratKeluarPercent = $this->calculatePercentChange($suratKeluarLast, $suratKeluarCurrent);
        
        // 2. Mitra Aktif - count distinct mitra in Penugasan this month vs last month
        $mitraCurrentCount = Penugasan::whereYear('bln_bayar', $currentYear)
            ->whereMonth('bln_bayar', $currentMonth)
            ->distinct('mitra_id')
            ->count('mitra_id');
        $mitraLastCount = Penugasan::whereYear('bln_bayar', $lastMonthYear)
            ->whereMonth('bln_bayar', $lastMonth)
            ->distinct('mitra_id')
            ->count('mitra_id');
        $mitraPercent = $this->calculatePercentChange($mitraLastCount, $mitraCurrentCount);
        
        // 3. Serapan Honor - sum nilai this month vs last month
        $honorCurrent = Penugasan::whereYear('bln_bayar', $currentYear)
            ->whereMonth('bln_bayar', $currentMonth)
            ->sum('nilai');
        $honorLast = Penugasan::whereYear('bln_bayar', $lastMonthYear)
            ->whereMonth('bln_bayar', $lastMonth)
            ->sum('nilai');
        $honorPercent = $this->calculatePercentChange($honorLast, $honorCurrent);
        
        // 4. Laporan SKP - count last month vs two months ago
        $skpLastMonth = Skp::where('bulan', $lastMonth)
            ->where('tahun', $lastMonthYear)
            ->count();
        $skpTwoMonthsAgo = Skp::where('bulan', $twoMonthsAgo)
            ->where('tahun', $twoMonthsAgoYear)
            ->count();
        $skpPercent = $this->calculatePercentChange($skpTwoMonthsAgo, $skpLastMonth);
        
        return [
            [
                'label' => 'Surat Keluar',
                'value' => (string) $suratKeluarCurrent,
                'note' => $suratKeluarPercent.' dari bulan lalu'
            ],
            [
                'label' => 'Mitra Aktif',
                'value' => (string) $mitraCurrentCount,
                'note' => $mitraPercent.' dari bulan lalu'
            ],
            [
                'label' => 'Serapan Honor',
                'value' => 'Rp.' . number_format($honorCurrent, 0, ',', '.'),
                'note' => $honorPercent.' dari bulan lalu'
            ],
            [
                'label' => 'Laporan SKP',
                'value' => (string) $skpLastMonth,
                'note' => $skpPercent.' dari bulan lalu'
            ]
        ];
    }
    
    /**
     * Calculate percentage change between two values
     */
    private function calculatePercentChange($oldValue, $newValue): string
    {
        if ($oldValue == 0) {
            return $newValue > 0 ? '+100% naik' : '0%';
        }
        
        $percentChange = (($newValue - $oldValue) / $oldValue) * 100;
        $sign = $percentChange >= 0 ? '+' : '';
        $word = $percentChange >= 0 ? ' naik' : ' turun';
        
        return $sign . number_format($percentChange, 1) . '%' . $word;
    }

    /**
     * Validate features data
     */
    private function validateFeatures(array $features): array
    {
        return collect($features)->map(function ($feature) {
            return [
                'title' => $feature['title'] ?? '',
                'desc' => $feature['desc'] ?? ''
            ];
        })->toArray();
    }

    /**
     * Validate integrations data (handles both old string format and new object format)
     */
    private function validateIntegrations(array $integrations): array
    {
        return collect($integrations)->map(function ($integration) {
            if (is_string($integration)) {
                // Legacy format - convert to object
                return [
                    'title' => $integration,
                    'status' => 'Terhubung.'
                ];
            } elseif (is_array($integration)) {
                // New format
                return [
                    'title' => $integration['title'] ?? '',
                    'status' => $integration['status'] ?? 'Tidak diketahui.'
                ];
            }
            return [
                'title' => '',
                'status' => 'Tidak diketahui.'
            ];
        })->toArray();
    }

    /**
     * Validate statistics data - returns real data from models
     */
    private function validateStatistics(array $statistics): array
    {
        $currentYear = date('Y');
        
        // Count Kegiatan for current year
        $jumlahKegiatan = Kegiatan::where('tahun', $currentYear)->count();
        
        // Sum nilai from Penugasan where year of bln_bayar = current year
        $penyerapanHonor = Penugasan::whereYear('bln_bayar', $currentYear)
            ->sum('nilai');
        
        // Count SKP where jenis='SKP Bulanan' and tahun=current year
        $countSkp = Skp::where('jenis', 'SKP Bulanan')
            ->where('tahun', $currentYear)
            ->count();
        
        // Count Pegawai where status is NULL
        $countPegawai = Pegawai::whereNull('status')->count();
        
        // Calculate average (protect against division by zero)
        $rataRataSkp = $countPegawai > 0 ? round($countSkp / $countPegawai, 2) : 0;
        
        return [
            [
                (string) $jumlahKegiatan,
                "Jumlah Kegiatan Tahun ini"
            ],
            [
                'Rp.' . number_format($penyerapanHonor, 0, ',', '.'),
                "Penyerapan Honor Tahun ini"
            ],
            [
                (string) round($rataRataSkp, 0).'/'.$countPegawai,
                "Rata-rata Laporan SKP Bulanan Tahun ini"
            ]
        ];
    }
}