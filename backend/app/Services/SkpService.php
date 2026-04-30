<?php

namespace App\Services;

use App\Models\Skp;
use App\Models\User;
use App\Jobs\ScanUploadedFile;
use App\Jobs\UploadSkpToGoogleDrive;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class SkpService
{
    public function getStats(int $selectedTahun, int $selectedTahun2, string $selectedBulan): array
    {
        $karyawan = User::where('id', '!=', 1)
            ->where('email', 'like', "%@bps.go.id")
            ->count();

        $statsVersion = Cache::get('skp_stats_version', 'v1');
        $cacheKey = "skp_index_stats_{$statsVersion}_{$selectedTahun}_{$selectedTahun2}_{$selectedBulan}";

        return Cache::remember($cacheKey, 3600, function () use ($selectedTahun, $selectedTahun2, $selectedBulan, $karyawan) {
            $userSkpBlnIni = Skp::where('bulan', $selectedBulan)
                ->where('tahun', $selectedTahun)
                ->pluck('user_id')
                ->toArray();

            $userNotSkpBlnIni = User::where('id', '!=', 1)
                ->where('email', 'like', "%@bps.go.id")
                ->whereNotIn('id', $userSkpBlnIni)
                ->pluck('name')
                ->toArray();

            $userNotSkpBlnIniCount = count($userNotSkpBlnIni);

            $thnSkpNilai = Carbon::now()->subYear()->format('Y');
            $userSkpNilaiThnIni = Skp::where('jenis', 'SKP Tahunan (Penilaian)')
                ->whereNull('bulan')
                ->where('tahun', $thnSkpNilai)
                ->pluck('user_id')
                ->toArray();

            $userNotSkpNilaiThnIni = User::where('id', '!=', 1)
                ->where('email', 'like', "%@bps.go.id")
                ->whereNotIn('id', $userSkpNilaiThnIni)
                ->pluck('name')
                ->toArray();

            $userSkpTetapThnIni = Skp::where('jenis', 'SKP Tahunan (Penetapan)')
                ->whereNull('bulan')
                ->where('tahun', $selectedTahun2)
                ->pluck('user_id')
                ->toArray();

            $userNotSkpTetapThnIni = User::where('id', '!=', 1)
                ->where('email', 'like', "%@bps.go.id")
                ->whereNotIn('id', $userSkpTetapThnIni)
                ->pluck('name')
                ->toArray();

            $thnSkpEvaluasi = Carbon::now()->subYear()->format('Y');
            $userSkpEvaluasiThnIni = Skp::where('jenis', 'SKP Evaluasi Tahunan')
                ->whereNull('bulan')
                ->where('tahun', $thnSkpEvaluasi)
                ->pluck('user_id')
                ->toArray();

            $userNotSkpEvaluasiThnIni = User::where('id', '!=', 1)
                ->where('email', 'like', "%@bps.go.id")
                ->whereNotIn('id', $userSkpEvaluasiThnIni)
                ->pluck('name')
                ->toArray();

            return [
                'skp_monthly_stats' => [
                    'not_uploaded_count' => $userNotSkpBlnIniCount,
                    'uploaded_count'     => $karyawan - $userNotSkpBlnIniCount,
                    'not_uploaded_users' => implode(', ', $userNotSkpBlnIni),
                    'period'             => $selectedBulan . '/' . $selectedTahun,
                ],
                'skp_annual_setting_stats' => [
                    'not_uploaded_users' => implode(', ', $userNotSkpTetapThnIni),
                    'period'             => $selectedTahun2,
                ],
                'skp_annual_determine_stats' => [
                    'not_uploaded_users' => implode(', ', $userNotSkpNilaiThnIni),
                    'period'             => $thnSkpNilai,
                ],
                'skp_annual_evaluation_stats' => [
                    'not_uploaded_users' => implode(', ', $userNotSkpEvaluasiThnIni),
                    'period'             => $thnSkpEvaluasi,
                ],
                'total_employees' => $karyawan,
            ];
        });
    }

    public function listSkps(array $params): array
    {
        $perPage = $params['per_page'] ?? 10;
        $sortBy  = $params['sort_by'] ?? 'nama';
        $sortDir = $params['sort_dir'] ?? 'ASC';

        $query = Skp::with(['user:id,name,email']);

        if (isset($params['user_id'])) {
            $query->where('user_id', $params['user_id']);
        }

        if (isset($params['tahun'])) {
            $query->where('tahun', $params['tahun']);
        }

        $skps = QueryBuilder::for($query)
            ->allowedFilters([
                AllowedFilter::partial('nama'),
                AllowedFilter::exact('user_id'),
                AllowedFilter::exact('tahun'),
            ])
            ->defaultSort($sortBy)
            ->allowedSorts(['nama', 'jenis', 'bulan', 'tahun', 'created_at'])
            ->fastPaginate($perPage);

        $totalRecords = $query->count();
        $totalPages   = $perPage > 0 ? ceil($totalRecords / $perPage) : 1;

        $users = User::where('email', 'like', '%@bps.go.id')
            ->orderBy('name')
            ->pluck('name', 'id');

        $listTahun = Skp::distinct()->orderBy('tahun', 'desc')->pluck('tahun');

        return [
            'data' => $skps,
            'meta' => [
                'current_page' => $skps->currentPage(),
                'from' => $skps->firstItem(),
                'last_page' => $skps->lastPage(),
                'path' => $skps->path(),
                'per_page' => $skps->perPage(),
                'to' => $skps->lastItem(),
                'total' => $skps->total(),
                'count' => count($skps->items()),
            ],
            'links' => [
                'first' => $skps->url(1),
                'last' => $skps->url($skps->lastPage()),
                'prev' => $skps->previousPageUrl(),
                'next' => $skps->nextPageUrl(),
                'path' => $skps->path(),
                'next_cursor' => null,
                'next_page_url' => $skps->nextPageUrl(),
                'prev_cursor' => null,
                'prev_page_url' => $skps->previousPageUrl(),
            ],
            'pagination_info' => [
                'total_page'    => $skps->lastPage(),
                'total_records' => $totalRecords,
            ],
            'users'     => $users,
            'listTahun' => $listTahun,
        ];
    }

    public function createSkp(User $user, array $data, $file): Skp
    {
        $jenis = $data['jenis'];
        $bulan = $data['bulan'] ?? null;
        $tahun = $data['tahun'];

        $nama = $this->generateSkpName($user->name, $jenis, $bulan, $tahun);
        $filename = $this->generateSkpFilename($user->name, $jenis, $bulan, $tahun);

        $pdfPath = $file->storeAs('skp_files', $filename, 'direct');
        ScanUploadedFile::dispatch($pdfPath, 'direct');
        $konten = $this->processPdfContent($pdfPath);

        $skp = Skp::create([
            'user_id' => $user->id,
            'jenis'   => $jenis,
            'nama'    => $nama,
            'bulan'   => $bulan,
            'tahun'   => $tahun,
            'link'    => $pdfPath,
            'konten'  => $konten,
        ]);

        UploadSkpToGoogleDrive::dispatch($skp->id, $pdfPath, $filename, $jenis, $tahun, $bulan);
        $this->invalidateSkpCaches();

        return $skp;
    }

    public function deleteSkp(Skp $skp): void
    {
        if ($skp->link && Storage::disk('direct')->exists($skp->link)) {
            Storage::disk('direct')->delete($skp->link);
        }
        $skp->delete();
        $this->invalidateSkpCaches();
    }

    public function updateSkp(Skp $skp, User $user, array $data, $file = null): Skp
    {
        $jenis = $data['jenis'] ?? $skp->jenis;
        $bulan = array_key_exists('bulan', $data) ? ($data['bulan'] ?: null) : $skp->bulan;
        $tahun = $data['tahun'] ?? $skp->tahun;

        $nama = $this->generateSkpName($user->name, $jenis, $bulan, $tahun);
        $filename = $this->generateSkpFilename($user->name, $jenis, $bulan, $tahun);

        $updateData = [
            'user_id' => $user->id,
            'jenis'   => $jenis,
            'nama'    => $nama,
            'bulan'   => $bulan,
            'tahun'   => $tahun,
        ];

        if ($file) {
            if ($skp->link && Storage::disk('direct')->exists($skp->link)) {
                Storage::disk('direct')->delete($skp->link);
            }

            $pdfPath = $file->storeAs('skp_files', $filename, 'direct');
            ScanUploadedFile::dispatch($pdfPath, 'direct');
            $konten = $this->processPdfContent($pdfPath);

            $updateData['link']   = $pdfPath;
            $updateData['konten'] = $konten;

            UploadSkpToGoogleDrive::dispatch($skp->id, $pdfPath, $filename, $jenis, $tahun, $bulan);
        }

        $skp->update($updateData);
        $this->invalidateSkpCaches();

        return $skp;
    }

    public function getAlternativeStats(): array
    {
        $now = now();
        $actualCurrentYear = $now->year;
        
        $lastMonth = $now->copy()->subMonthNoOverflow();
        $currentYear = $lastMonth->year;
        $currentMonth = $lastMonth->format('m');

        $skpBulanan = Skp::where('jenis', 'SKP Bulanan')
            ->where('bulan', $currentMonth)
            ->where('tahun', $currentYear)
            ->count();

        $skpPenetapan = Skp::where('jenis', 'SKP Tahunan (Penetapan)')
            ->whereNull('bulan')
            ->where('tahun', $actualCurrentYear)
            ->count();

        $lastYear = $actualCurrentYear - 1;
        $skpPenilaian = Skp::where('jenis', 'SKP Tahunan (Penilaian)')
            ->whereNull('bulan')
            ->where('tahun', $lastYear)
            ->count();

        $skpEvaluasi = Skp::where('jenis', 'SKP Evaluasi Tahunan')
            ->whereNull('bulan')
            ->where('tahun', $lastYear)
            ->count();

        $chartBulanan = [];
        $date = now()->subMonth();

        for ($i = 0; $i < 12; $i++) {
            $month = $date->format('m');
            $year = $date->year;

            $count = Skp::where('jenis', 'SKP Bulanan')
                ->where('bulan', $month)
                ->where('tahun', $year)
                ->count();
            
            $chartBulanan[] = [
                'bulan' => $month,
                'jumlah' => $count,
                'tahun' => (string) $year,
            ];

            $date->subMonth();
        }

        $lastUploads = Skp::with('user:id,name')
            ->orderBy('created_at', 'DESC')
            ->take(5)
            ->get()
            ->map(fn($skp) => [
                'user_name'  => $skp->user->name ?? 'Unknown',
                'nama'       => $skp->nama,
                'created_at' => $skp->created_at->toDateTimeString(),
            ]);

        return [
            'skp_bulanan'   => ['count' => $skpBulanan, 'bulan' => $currentMonth, 'tahun' => (string) $currentYear],
            'skp_penetapan' => ['count' => $skpPenetapan, 'tahun' => (string) $actualCurrentYear],
            'skp_penilaian' => ['count' => $skpPenilaian, 'tahun' => (string) $lastYear],
            'skp_evaluasi'  => ['count' => $skpEvaluasi, 'tahun' => (string) $lastYear],
            'chart_bulanan' => $chartBulanan,
            'last_upload'   => $lastUploads,
        ];
    }

    public function processPdfContent(string $pdfPath): string
    {
        try {
            $parser   = new \Smalot\PdfParser\Parser();
            $fullPath = Storage::disk('direct')->path($pdfPath);
            $pdf      = $parser->parseFile($fullPath);
            $metaData = $pdf->getDetails();
            $pages    = $pdf->getPages();

            if (empty($pages)) {
                return $this->formatPdfInfo([
                    'producer'   => $metaData['Producer'] ?? '',
                    'page_count' => 0,
                    'konten'     => 'Tidak ada halaman',
                ]);
            }

            $firstPageContent = $pages[0]->getText();
            
            if (empty(trim($firstPageContent))) {
                 return $this->formatPdfInfo([
                    'producer'   => $metaData['Producer'] ?? '',
                    'page_count' => count($pages),
                    'konten'     => 'Tidak bisa dibaca',
                ]);
            }

            $firstPageContent = preg_replace('/1\s*\n\s*Nama/', '1 Nama', $firstPageContent);
            $firstPageContent = preg_replace('/\n\s*\n+/', "\n", $firstPageContent);

            $nama = '';
            if (preg_match('/1 Nama\s*(.*?)\s*1 Nama/s', $firstPageContent, $matches)) {
                $nama = trim(str_replace("\n", ' ', $matches[1]));
            }
            $nama = str_replace('1 Nama ', '', $nama);

            $lines = explode("\n", $firstPageContent);
            $periode = isset($lines[2]) ? trim($lines[2]) : '';
            $periode = str_replace('Periode: ', '', $periode);

            return $this->formatPdfInfo([
                'producer'   => $metaData['Producer'] ?? '',
                'page_count' => count($pages),
                'nama'       => $nama,
                'periode'    => $periode,
            ]);

        } catch (\Exception $e) {
            try {
                $parser   = new \Smalot\PdfParser\Parser();
                $fullPath = Storage::disk('direct')->path($pdfPath);
                $pdf      = $parser->parseFile($fullPath);
                $metaData = $pdf->getDetails();
                $pages    = $pdf->getPages();

                return $this->formatPdfInfo([
                    'producer'   => $metaData['Producer'] ?? '',
                    'page_count' => count($pages),
                    'konten'     => 'Tidak bisa dibaca',
                ]);
            } catch (\Exception $e2) {
                return '<div><p>Error ketika proses file PDF</p></div>';
            }
        }
    }

    protected function formatPdfInfo(array $info): string
    {
        $htmlContent = '<div>';
        foreach ($info as $key => $value) {
            $htmlContent .= '<p><strong>' . ucfirst($key) . ':</strong> ' . htmlspecialchars($value) . '</p>';
        }
        $htmlContent .= '</div>';
        return $htmlContent;
    }

    protected function generateSkpName(string $userName, string $jenis, ?string $bulan, string $tahun): string
    {
        if ($jenis == 'SKP Bulanan' || $jenis == 'SKP Triwulanan') {
            return "{$jenis} {$bulan}/{$tahun} {$userName}";
        }
        return "{$jenis} {$tahun} {$userName}";
    }

    protected function generateSkpFilename(string $userName, string $jenis, ?string $bulan, string $tahun): string
    {
        $safeName = str_replace(' ', '_', $userName);
        if ($jenis == 'SKP Bulanan' || $jenis == 'SKP Triwulanan') {
            return "{$jenis}_{$bulan}_{$tahun}_{$safeName}.pdf";
        }
        return "{$jenis}_{$tahun}_{$safeName}.pdf";
    }

    public function invalidateSkpCaches(): void
    {
        Cache::put('skp_stats_version', now()->timestamp);
    }
}
