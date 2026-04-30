<?php

namespace App\Http\Controllers\Api\Kantor;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Kantor\Skp\IndexSkpRequest;
use App\Http\Requests\Kantor\Skp\ListSkpRequest;
use App\Http\Requests\Kantor\Skp\StoreSkpRequest;
use App\Http\Requests\Kantor\Skp\UpdateSkpRequest;
use App\Http\Resources\SkpResource;
use App\Models\Pegawai;
use App\Models\Skp;
use App\Models\User;
use App\Services\SkpService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * @group Kantor - SKP
 *
 * APIs for managing Employee Performance Targets (SKP).
 */
class SkpApiController extends BaseApiController
{
    protected SkpService $skpService;

    public function __construct(SkpService $skpService)
    {
        $this->skpService = $skpService;
    }

    /**
     * SKP Dashboard
     *
     * Retrieve SKP dashboard summary.
     *
     * @authenticated
     *
     * @response {
     *  "success": true,
     *  "message": "Data retrieved successfully",
     *  "data": {
     *      "total_pegawai": 50,
     *      "skp_bulanan_by_role": {
     *          "Kepala": 1,
     *          "Kasub Umum": 1,
     *          "Katim": 3
     *      },
     *      "activities": [
     *          {
     *              "id": 1,
     *              "nama": "SKP Name",
     *              "user_name": "John Doe",
     *              "created_at": "2024-01-01T00:00:00.000000Z"
     *          }
     *      ],
     *      "skp_bulan_by_month": {
     *          "tahun": 2025,
     *          "data": {
     *              "01": 30,
     *              "02": 31,
     *              "03": 29,
     *              "04": 29,
     *              "05": 28,
     *              "06": 20,
     *              "07": 19,
     *              "08": 20,
     *              "09": 21,
     *              "10": 20,
     *              "11": 19,
     *              "12": 30
     *          }
     *      },
     *      "skp_penilaian": {
     *          "tahun": 2025,
     *          "data": {
     *              "2024-01": 10,
     *              "2024-02": 15,
     *              "2024-03": 12
     *          }
     *      },
     *      "skp_evaluasi": {
     *          "tahun": 2025,
     *          "data": {
     *              "2024-01": 8,
     *              "2024-02": 12,
     *              "2024-03": 10
     *          }
     *      },
     *      "skp_penetapan": {
     *          "tahun": 2025,
     *          "data": {
     *              "2024-01": 5,
     *              "2024-02": 8,
     *              "2024-03": 6
     *          }
     *      },
     *      "skp_nilai_by_role": {
     *          "Kepala": 1,
     *          "Kasub Umum": 1,
     *          "Katim": 3
     *      }
     *  }
     * }
     */
    public function dashboard()
    {
        $totalPegawai = Pegawai::whereNull('status')->count();

        $skpBulanan = [];

        $maxPeriod = Skp::whereNotNull('bulan')
            ->selectRaw('MAX(CAST(CONCAT(tahun, LPAD(bulan, 2, \'0\')) AS UNSIGNED)) as max_period')
            ->value('max_period');

        if ($maxPeriod) {
            $maxTahun = (string) substr((string) $maxPeriod, 0, 4);
            $maxBulan = (string) ltrim(substr((string) $maxPeriod, 4, 2), '0');

            $skpBulanan = Skp::whereNotNull('bulan')
                ->where('tahun', $maxTahun)
                ->where('bulan', $maxBulan)
                ->join('model_has_roles', 'skps.user_id', '=', 'model_has_roles.model_id')
                ->where('model_has_roles.model_type', User::class)
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->groupBy('roles.name')
                ->pluck(DB::raw('COUNT(*)'), 'roles.name')
                ->toArray();
        }

        $lastSkpPeriode = $maxPeriod
            ? substr((string) $maxPeriod, 0, 4).'-'.substr((string) $maxPeriod, 4, 2)
            : null;

        $maxTahunBulanan = Skp::whereNotNull('bulan')->max('tahun');

        $maxTahunPenetapan = Skp::where('jenis', 'SKP Tahunan (Penetapan)')->max('tahun');
        $skpTetapByRoleData = Skp::when($maxTahunPenetapan, function ($query, $maxTahunPenetapan) {
            $query->where('tahun', $maxTahunPenetapan);
        })
            ->where('jenis', 'SKP Tahunan (Penetapan)')
            ->join('model_has_roles', 'skps.user_id', '=', 'model_has_roles.model_id')
            ->where('model_has_roles.model_type', User::class)
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->groupBy('roles.name')
            ->pluck(DB::raw('COUNT(*)'), 'roles.name')
            ->toArray();

        $skpTetapByRole = [
            'tahun' => $maxTahunPenetapan,
            'data' => $skpTetapByRoleData,
        ];

        // Get last 4 SKP activities
        $activities = Skp::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get()
            ->map(function ($skp) {
                return [
                    'id' => $skp->id,
                    'nama' => $skp->nama,
                    'user_name' => $skp->user ? $skp->user->name : null,
                    'created_at' => $skp->created_at,
                ];
            });

        // Get SKP grouped by bulan for max tahun
        $maxTahunForBulan = Skp::whereNotNull('bulan')->max('tahun');
        $skpBulanByMonthRaw = Skp::whereNotNull('bulan')
            ->when($maxTahunForBulan, function ($query) use ($maxTahunForBulan) {
                $query->where('tahun', $maxTahunForBulan);
            })
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck(DB::raw('COUNT(*)'), 'bulan')
            ->toArray();

        // Format with zero-padded month keys
        $skpBulanByMonthData = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthKey = str_pad($month, 2, '0', STR_PAD_LEFT);
            // Check both string and integer keys since database returns string keys
            $skpBulanByMonthData[$monthKey] = $skpBulanByMonthRaw[$monthKey] ?? $skpBulanByMonthRaw[$month] ?? 0;
        }

        $skpBulanByMonth = [
            'tahun' => $maxTahunForBulan,
            'data' => $skpBulanByMonthData,
        ];

        // Get SKP Penilaian stats
        $maxTahunPenilaian = Skp::where('jenis', 'SKP Tahunan (Penilaian)')->max('tahun');
        $skpPenilaianRaw = Skp::where('jenis', 'SKP Tahunan (Penilaian)')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month_year, COUNT(*) as count")
            ->groupBy('month_year')
            ->orderBy('month_year')
            ->pluck('count', 'month_year')
            ->toArray();

        $skpPenilaian = [
            'tahun' => $maxTahunPenilaian,
            'data' => $skpPenilaianRaw,
        ];

        // Get SKP Evaluasi stats
        $maxTahunEvaluasi = Skp::where('jenis', 'SKP Evaluasi Tahunan')->max('tahun');
        $skpEvaluasiRaw = Skp::where('jenis', 'SKP Evaluasi Tahunan')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month_year, COUNT(*) as count")
            ->groupBy('month_year')
            ->orderBy('month_year')
            ->pluck('count', 'month_year')
            ->toArray();

        $skpEvaluasi = [
            'tahun' => $maxTahunEvaluasi,
            'data' => $skpEvaluasiRaw,
        ];

        // Get SKP Penetapan stats
        $maxTahunPenetapan = Skp::where('jenis', 'SKP Tahunan (Penetapan)')->max('tahun');
        $skpPenetapanRaw = Skp::where('jenis', 'SKP Tahunan (Penetapan)')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month_year, COUNT(*) as count")
            ->groupBy('month_year')
            ->orderBy('month_year')
            ->pluck('count', 'month_year')
            ->toArray();

        $skpPenetapan = [
            'tahun' => $maxTahunPenetapan,
            'data' => $skpPenetapanRaw,
        ];

        // Get SKP Penilaian by role
        $maxTahunPenilaian = Skp::where('jenis', 'SKP Tahunan (Penilaian)')->max('tahun');
        $skpNilaiByRoleData = Skp::when($maxTahunPenilaian, function ($query, $maxTahunPenilaian) {
            $query->where('tahun', $maxTahunPenilaian);
        })
            ->where('jenis', 'SKP Tahunan (Penilaian)')
            ->join('model_has_roles', 'skps.user_id', '=', 'model_has_roles.model_id')
            ->where('model_has_roles.model_type', User::class)
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->groupBy('roles.name')
            ->pluck(DB::raw('COUNT(*)'), 'roles.name')
            ->toArray();

        $skpNilaiByRole = [
            'tahun' => $maxTahunPenilaian,
            'data' => $skpNilaiByRoleData,
        ];

        return $this->success([
            'total_pegawai' => $totalPegawai,
            'max_tahun_bulanan' => $maxTahunBulanan,
            'last_skp_periode' => $lastSkpPeriode,
            'skp_bulanan_by_role' => $skpBulanan,
            'skp_tetap_by_role' => $skpTetapByRole,
            'activities' => $activities,
            'skp_bulan_by_month' => $skpBulanByMonth,
            'skp_penilaian' => $skpPenilaian,
            'skp_evaluasi' => $skpEvaluasi,
            'skp_penetapan' => $skpPenetapan,
            'skp_nilai_by_role' => $skpNilaiByRole,
        ], 'Data retrieved successfully');
    }

    /**
     * Get SKP Stats
     *
     * Retrieve statistics for SKP based on year and month.
     *
     * @queryParam tahun int The year to filter. Example: 2023
     * @queryParam tahun2 int The second year to filter. Example: 2023
     * @queryParam bulan string The month to filter. Example: 01
     *
     * @response {
     *  "success": true,
     *  "message": "Data retrieved successfully",
     *  "data": {
     *      "selected_tahun": "2023",
     *      "selected_tahun2": "2023",
     *      "selected_bulan": "01",
     *      "tahun_options": ["2022", "2023", "2024"],
     *      "bulan_options": ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"],
     *      "total_target": 100,
     *      "total_realisasi": 80
     *  }
     * }
     */
    public function index(IndexSkpRequest $request)
    {
        $lastMonth = Carbon::now()->subMonthNoOverflow()->startOfMonth();
        $selectedTahun = (int) $request->get('tahun', $lastMonth->format('Y'));
        $selectedTahun2 = (int) $request->get('tahun2', now()->year);
        $selectedBulan = $request->get('bulan', $lastMonth->format('m'));

        $stats = $this->skpService->getStats($selectedTahun, $selectedTahun2, (string) $selectedBulan);

        $yearOptions = [
            $lastMonth->copy()->subYearsNoOverflow()->format('Y'),
            $lastMonth->copy()->format('Y'),
            $lastMonth->copy()->addYearsNoOverflow()->format('Y'),
        ];

        $monthOptions = array_map(fn ($m) => Carbon::create(null, $m)->format('m'), range(1, 12));

        return $this->success(array_merge([
            'tahun_options' => $yearOptions,
            'bulan_options' => $monthOptions,
            'selected_tahun' => (string) $selectedTahun,
            'selected_tahun2' => (string) $selectedTahun2,
            'selected_bulan' => $selectedBulan,
        ], $stats), 'Data retrieved successfully');
    }

    /**
     * List SKP
     *
     * Retrieve a list of SKP records with pagination and filtering.
     *
     * @queryParam page int The page number. Example: 1
     * @queryParam per_page int The number of items per page. Example: 20
     * @queryParam tahun int Filter by year. Example: 2023
     * @queryParam user_id int Filter by user ID. Example: 1
     *
     * @response {
     *  "success": true,
     *  "message": "Data retrieved successfully",
     *  "data": [
     *      {
     *          "id": 1,
     *          "user_id": 1,
     *          "tahun": "2023",
     *          "target": 100,
     *          "realisasi": 90,
     *          "kualitas": 95,
     *          "file_url": "http://example.com/file.pdf"
     *      }
     *  ],
     *  "meta": {
     *       "pagination_info": {
     *           "total": 50,
     *           "per_page": 20,
     *           "current_page": 1,
     *           "last_page": 3
     *       }
     *  }
     * }
     */
    public function list(ListSkpRequest $request)
    {
        $result = $this->skpService->listSkps($request->validated());

        return $this->success(
            SkpResource::collection($result['data']),
            'Data retrieved successfully',
            200,
            [
                'meta' => $result['meta'],
                'links' => $result['links'],
                'pagination_info' => $result['pagination_info'],
                'users' => $result['users'],
                'listTahun' => $result['listTahun'],
            ]
        );
    }

    /**
     * Create SKP
     *
     * Create a new SKP record.
     *
     * @response 201 {
     *  "success": true,
     *  "message": "SKP record created successfully",
     *  "data": {
     *      "id": 1,
     *      "user_id": 1,
     *      "tahun": "2023",
     *      "target": 100,
     *      "realisasi": 90,
     *      "kualitas": 95
     *  }
     * }
     * @response 500 {
     *  "success": false,
     *  "message": "Failed to create SKP record",
     *  "data": "Error message details"
     * }
     */
    public function store(StoreSkpRequest $request)
    {
        try {
            $skp = $this->skpService->createSkp(
                Auth::user(),
                $request->validated(),
                $request->file('file')
            );

            return $this->success(new SkpResource($skp), 'SKP record created successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create SKP record', $e->getMessage(), 500);
        }
    }

    /**
     * Show SKP
     *
     * Retrieve details of a specific SKP record.
     *
     * @urlParam id string required The ID of the SKP record. Example: 1
     *
     * @response {
     *  "success": true,
     *  "message": "Data retrieved successfully",
     *  "data": {
     *      "id": 1,
     *      "user_id": 1,
     *      "tahun": "2023",
     *      "target": 100,
     *      "realisasi": 90,
     *      "kualitas": 95,
     *      "user": {
     *          "id": 1,
     *          "name": "John Doe"
     *      }
     *  }
     * }
     * @response 404 {
     *  "success": false,
     *  "message": "SKP record not found",
     *  "data": "Error message details"
     * }
     */
    public function show($id)
    {
        try {
            $skp = Skp::with(['user'])->findOrFail($id);

            return $this->success(new SkpResource($skp), 'Data retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('SKP record not found', $e->getMessage(), 404);
        }
    }

    /**
     * Update SKP
     *
     * Update an existing SKP record.
     *
     * @urlParam id string required The ID of the SKP record. Example: 1
     *
     * @response {
     *  "success": true,
     *  "message": "SKP record updated successfully",
     *  "data": {
     *      "id": 1,
     *      "tahun": "2023",
     *      "target": 110,
     *      "realisasi": 95
     *  }
     * }
     * @response 500 {
     *  "success": false,
     *  "message": "Failed to update SKP record",
     *  "data": "Error message details"
     * }
     */
    public function update(UpdateSkpRequest $request, $id)
    {
        try {
            $skp = Skp::findOrFail($id);
            $updatedSkp = $this->skpService->updateSkp(
                $skp,
                Auth::user(),
                $request->validated(),
                $request->file('file')
            );

            return $this->success(new SkpResource($updatedSkp), 'SKP record updated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to update SKP record', $e->getMessage(), 500);
        }
    }

    /**
     * Get Alternative Stats
     *
     * Retrieve alternative statistics for SKP.
     *
     * @response {
     *  "success": true,
     *  "message": "Statistics retrieved successfully",
     *  "data": {
     *      "total_active_users": 50,
     *      "average_performance": 85.5
     *  }
     * }
     * @response 500 {
     *  "success": false,
     *  "message": "Failed to retrieve statistics",
     *  "data": "Error message details"
     * }
     */
    public function stat2()
    {
        try {
            $stats = $this->skpService->getAlternativeStats();

            return $this->success($stats, 'Statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve statistics', $e->getMessage(), 500);
        }
    }

    /**
     * Delete SKP
     *
     * Delete an SKP record.
     *
     * @urlParam id string required The ID of the SKP record. Example: 1
     *
     * @response {
     *  "success": true,
     *  "message": "SKP record deleted successfully",
     *  "data": null
     * }
     * @response 500 {
     *  "success": false,
     *  "message": "Failed to delete SKP record",
     *  "data": "Error message details"
     * }
     */
    public function destroy($id)
    {
        try {
            $skp = Skp::findOrFail($id);
            $this->skpService->deleteSkp($skp);

            return $this->success(null, 'SKP record deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to delete SKP record', $e->getMessage(), 500);
        }
    }
}
