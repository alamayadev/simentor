<?php

namespace App\Http\Controllers\Api\Kantor\Dipa;

use App\Http\Controllers\Api\BaseApiController;
use App\Exports\BudgetPlansExport;
use App\Models\Dipa\BudgetPlan;
use App\Models\Dipa\BudgetUsage;
use App\Models\Dipa\ImportedFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * DIPA Budget API Controller
 *
 * Mengelola modul DIPA (Daftar Isian Pelaksanaan Anggaran) meliputi monitoring pagu vs realisasi,
 * pencatatan transaksi pengeluaran, rencana pencairan bulanan, upload file DIPA Excel,
 * pencarian item anggaran, dan pemetaan item antar revisi.
 *
 * @group Kantor - DIPA Budgeting
 */
class DipaBudgetApiController extends BaseApiController
{
    private const INDONESIAN_REVISION_MONTHS = ['JAN', 'PEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AGU', 'SEP', 'OKT', 'NOV', 'DES'];

    private const INDONESIAN_SHORT_MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    private const DEFAULT_PICKER_LIMIT = 50;

    private const VIEW_DEPENDENCIES = [
        ['view_name' => 'v_budget_monitoring', 'check_sql' => 'SELECT 1 FROM v_budget_monitoring LIMIT 1', 'purpose' => 'dasbor monitoring pagu versus realisasi'],
        ['view_name' => 'v_item_picker', 'check_sql' => 'SELECT 1 FROM v_item_picker LIMIT 1', 'purpose' => 'pencarian item anggaran untuk pencatatan, perencanaan, dan mapping'],
        ['view_name' => 'v_usage_history', 'check_sql' => 'SELECT 1 FROM v_usage_history LIMIT 1', 'purpose' => 'riwayat transaksi realisasi anggaran'],
        ['view_name' => 'v_latest_budget', 'check_sql' => 'SELECT 1 FROM v_latest_budget LIMIT 1', 'purpose' => 'referensi item anggaran revisi terbaru dan cek orphan'],
    ];

    /**
     * Ambil data monitoring DIPA
     *
     * Mengembalikan data monitoring pagu versus realisasi seluruh item anggaran dari revisi terbaru.
     * Data diambil dari view `v_budget_monitoring` yang diagregasi per composite_key.
     *
     * @group Kantor - DIPA Budgeting
     *
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data monitoring DIPA berhasil diambil",
     *   "data": [
     *     {
     *       "composite_key": "a1b2c3d4e5f6...",
     *       "budget_item_key": "a1b2c3d4e5f6...",
     *       "formatted_description": "5241 > 5241.SAK > 001 > Belanja Barang/Jasa > ...",
     *       "total_pagu": 150000000,
     *       "total_realisasi": 50000000,
     *       "persentase_realisasi": 33.33,
     *       "output_name": "Peningkatan kapasitas sumber daya statistik",
     *       "component_name": "Belanja Barang/Jasa"
     *     }
     *   ]
     * }
     * @response 400 {
     *   "success": false,
     *   "message": "Gagal mengambil data monitoring",
     *   "errors": "SQLSTATE[42S02]: Base table or view not found..."
     * }
     */
    public function getMonitoring(Request $request): JsonResponse
    {
        try {
            $year = $request->query('year');
            $now = now();
            $selectedYear = $year ? (int) $year : $now->year;

            $data = DB::select('
                SELECT
                    v.*,
                    b.output_name,
                    b.component_name
                FROM v_budget_monitoring v
                LEFT JOIN budget_items b
                    ON b.composite_key = v.composite_key
                    AND b.revision_date = (
                        SELECT MAX(revision_date)
                        FROM budget_items
                        WHERE tahun_anggaran = ?
                    )
                WHERE v.tahun_anggaran = ?
                ORDER BY v.total_realisasi DESC, v.composite_key ASC
            ', [$selectedYear, $selectedYear]);

            return $this->success($data, 'Data monitoring DIPA berhasil diambil');
        } catch (\Throwable $e) {
            return $this->error('Gagal mengambil data monitoring', $e->getMessage());
        }
    }

    /**
     * Ambil ringkasan monitoring DIPA
     *
     * Mengembalikan ringkasan statistik monitoring bulan berjalan beserta chart data realisasi
     * vs rencana per bulan untuk tahun tertentu.
     *
     * @group Kantor - DIPA Budgeting
     *
     * @authenticated
     *
     * @queryParam year int Tahun yang ingin dilihat. Jika tidak diisi, menggunakan tahun berjalan. Contoh: 2026
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Ringkasan monitoring DIPA berhasil diambil",
     *   "data": {
     *     "year": 2026,
     *     "current_month": "2026-04",
     *     "current_month_realisasi": 50000000,
     *     "current_month_rencana": 75000000,
     *     "chart": [
     *       {
     *         "month_number": 1,
     *         "month_label": "Jan",
     *         "realisasi": 30000000,
     *         "rencana": 40000000
     *       },
     *       {
     *         "month_number": 2,
     *         "month_label": "Feb",
     *         "realisasi": 25000000,
     *         "rencana": 35000000
     *       },
     *       {
     *         "month_number": 3,
     *         "month_label": "Mar",
     *         "realisasi": 0,
     *         "rencana": 0
     *       },
     *       {
     *         "month_number": 12,
     *         "month_label": "Des",
     *         "realisasi": 0,
     *         "rencana": 0
     *       }
     *     ]
     *   }
     * }
     * @response 400 {
     *   "success": false,
     *   "message": "Gagal mengambil ringkasan monitoring DIPA",
     *   "errors": "..."
     * }
     */
    public function getMonitoringSummary(Request $request): JsonResponse
    {
        try {
            $year = $request->query('year');
            $now = now();
            $selectedYear = $year ? (int) $year : $now->year;
            $currentMonthNumber = $now->month;
            $currentMonth = $selectedYear.'-'.str_pad($currentMonthNumber, 2, '0', STR_PAD_LEFT);

            $currentRealisasi = DB::selectOne('
                SELECT COALESCE(SUM(amount_spent), 0) AS total
                FROM budget_usage
                WHERE YEAR(usage_date) = ? AND MONTH(usage_date) = ?
            ', [$selectedYear, $currentMonthNumber]);
            $currentMonthRealisasi = (float) ($currentRealisasi->total ?? 0);

            $currentRencana = DB::selectOne('
                SELECT COALESCE(SUM(planned_amount), 0) AS total
                FROM budget_plans
                WHERE YEAR(target_month) = ? AND MONTH(target_month) = ?
            ', [$selectedYear, $currentMonthNumber]);
            $currentMonthRencana = (float) ($currentRencana->total ?? 0);

            $totalBudgetCeilingRow = DB::selectOne('
                SELECT COALESCE(SUM(total_amount), 0) AS total
                FROM v_latest_budget
                WHERE tahun_anggaran = ?
            ', [$selectedYear]);
            $totalBudgetCeiling = (float) ($totalBudgetCeilingRow->total ?? 0);

            $totalBudgetRealizationRow = DB::selectOne('
                SELECT COALESCE(SUM(amount_spent), 0) AS total
                FROM budget_usage
                WHERE YEAR(usage_date) = ?
            ', [$selectedYear]);
            $totalBudgetRealization = (float) ($totalBudgetRealizationRow->total ?? 0);

            $remainingBudget = $totalBudgetCeiling - $totalBudgetRealization;

            $realisasiRows = DB::select('
                SELECT
                    MONTH(usage_date) AS month_number,
                    COALESCE(SUM(amount_spent), 0) AS total_realisasi
                FROM budget_usage
                WHERE YEAR(usage_date) = ?
                GROUP BY MONTH(usage_date)
            ', [$selectedYear]);

            $rencanaRows = DB::select('
                SELECT
                    MONTH(target_month) AS month_number,
                    COALESCE(SUM(planned_amount), 0) AS total_rencana
                FROM budget_plans
                WHERE YEAR(target_month) = ?
                GROUP BY MONTH(target_month)
            ', [$selectedYear]);

            $realisasiMap = collect($realisasiRows)->mapWithKeys(fn ($r) => [(int) $r->month_number => (float) $r->total_realisasi]);
            $rencanaMap = collect($rencanaRows)->mapWithKeys(fn ($r) => [(int) $r->month_number => (float) $r->total_rencana]);

            $chart = [];
            for ($i = 0; $i < 12; $i++) {
                $monthNumber = $i + 1;
                $chart[] = [
                    'month_number' => $monthNumber,
                    'month_label' => self::INDONESIAN_SHORT_MONTHS[$i],
                    'realisasi' => $realisasiMap->get($monthNumber, 0),
                    'rencana' => $rencanaMap->get($monthNumber, 0),
                ];
            }

            return $this->success([
                'year' => $selectedYear,
                'current_month' => $currentMonth,
                'total_budget_ceiling' => $totalBudgetCeiling,
                'total_budget_realization' => $totalBudgetRealization,
                'remaining_budget' => $remainingBudget,
                'current_month_realisasi' => $currentMonthRealisasi,
                'current_month_rencana' => $currentMonthRencana,
                'chart' => $chart,
            ], 'Ringkasan monitoring DIPA berhasil diambil');
        } catch (\Throwable $e) {
            return $this->error('Gagal mengambil ringkasan monitoring DIPA', $e->getMessage());
        }
    }

    /**
     * Cari item anggaran (Picker)
     *
     * Mengembalikan daftar item anggaran untuk keperluan pencatatan realisasi, rencana, dan mapping.
     * Mendukung pencarian multi-keyword yang dipisahkan dengan koma atau spasi.
     * Hasil diurutkan berdasarkan relevance score.
     *
     * @group Kantor - DIPA Budgeting
     *
     * @authenticated
     *
     * @queryParam q string Kata kunci pencarian, pisahkan beberapa keyword dengan koma atau spasi. Contoh: belanja,barang
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data picker DIPA berhasil diambil",
     *   "data": [
     *     {
     *       "short_code": "5241",
     *       "formatted_description": "5241 > 5241.SAK > 001 > Belanja Barang/Jasa > ...",
     *       "funding_source": "RM",
     *       "composite_key": "a1b2c3d4e5f6...",
     *       "budget_item_key": "a1b2c3d4e5f6...",
     *       "total_pagu": 150000000,
     *       "relevance_score": 18
     *     }
     *   ]
     * }
     * @response 400 {
     *   "success": false,
     *   "message": "Gagal mengambil data picker",
     *   "errors": "..."
     * }
     */
    public function getPicker(Request $request): JsonResponse
    {
        try {
            $search = $request->query('q');

            if (! $search || ! trim($search)) {
                $data = DB::select('
                    SELECT v.*, COALESCE(m.total_pagu, 0) AS total_pagu 
                    FROM v_item_picker v
                    LEFT JOIN v_budget_monitoring m ON v.composite_key = m.composite_key
                    LIMIT ?
                ', [self::DEFAULT_PICKER_LIMIT]);

                return $this->success($data, 'Data picker DIPA berhasil diambil');
            }

            $terms = array_filter(array_map('trim', preg_split('/[\s,]+/', $search)), fn ($t) => strlen($t) > 0);

            if (empty($terms)) {
                $data = DB::select('SELECT * FROM v_item_picker LIMIT ?', [self::DEFAULT_PICKER_LIMIT]);

                return $this->success($data, 'Data picker DIPA berhasil diambil');
            }

            $conditions = [];
            $bindings = [];
            $scoreParts = [];

            foreach ($terms as $term) {
                $keyword = "%{$term}%";

                $conditions[] = '(
                    b.program_name LIKE ? OR
                    b.activity_name LIKE ? OR
                    b.output_name LIKE ? OR
                    b.component_name LIKE ? OR
                    b.sub_component_name LIKE ? OR
                    b.account_name LIKE ? OR
                    b.description LIKE ? OR
                    b.composite_key LIKE ?
                )';

                $bindings = array_merge($bindings, array_fill(0, 8, $keyword));

                $scoreParts[] = '(
                    (CASE WHEN b.description LIKE ? THEN 10 ELSE 0 END) +
                    (CASE WHEN b.account_name LIKE ? THEN 8 ELSE 0 END) +
                    (CASE WHEN b.sub_component_name LIKE ? THEN 5 ELSE 0 END) +
                    (CASE WHEN b.component_name LIKE ? THEN 5 ELSE 0 END) +
                    (CASE WHEN b.output_name LIKE ? THEN 1 ELSE 0 END) +
                    (CASE WHEN b.activity_name LIKE ? THEN 1 ELSE 0 END) +
                    (CASE WHEN b.program_name LIKE ? THEN 1 ELSE 0 END) +
                    (CASE WHEN b.composite_key LIKE ? THEN 2 ELSE 0 END)
                )';

                $bindings = array_merge($bindings, array_fill(0, 8, $keyword));
            }

            $whereClause = implode(' AND ', $conditions);
            $scoreSql = implode(' + ', $scoreParts);

            $data = DB::select("
                SELECT
                    v.short_code,
                    v.formatted_description,
                    v.funding_source,
                    v.composite_key,
                    v.composite_key AS budget_item_key,
                    COALESCE(m.total_pagu, 0) AS total_pagu,
                    MAX({$scoreSql}) AS relevance_score
                FROM v_item_picker v
                LEFT JOIN v_budget_monitoring m ON v.composite_key = m.composite_key
                JOIN budget_items b ON v.composite_key = b.composite_key
                WHERE {$whereClause}
                GROUP BY v.short_code, v.formatted_description, v.funding_source, v.composite_key, v.composite_key, m.total_pagu
                ORDER BY relevance_score DESC, v.composite_key ASC
                LIMIT ?
            ", array_merge($bindings, [self::DEFAULT_PICKER_LIMIT]));

            return $this->success($data, 'Data picker DIPA berhasil diambil');
        } catch (\Throwable $e) {
            return $this->error('Gagal mengambil data picker', $e->getMessage());
        }
    }

    /**
     * Catat realisasi anggaran
     *
     * Mencatat transaksi pengeluaran (realisasi) untuk item anggaran tertentu.
     * Jika `usage_date` tidak diisi, akan menggunakan tanggal hari ini.
     *
     * @group Kantor - DIPA Budgeting
     *
     * @authenticated
     *
     * @bodyParam budget_item_key string required Composite key item anggaran (dari picker). Contoh: a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d
     * @bodyParam amount_spent number required Jumlah pengeluaran, harus lebih besar dari 0. Contoh: 5000000
     * @bodyParam description string Deskripsi transaksi pengeluaran. Contoh: Pembelian ATK untuk kegiatan sensus
     * @bodyParam usage_date string Tanggal transaksi (format: YYYY-MM-DD). Jika tidak diisi menggunakan tanggal hari ini. Contoh: 2026-04-18
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Realisasi anggaran berhasil dicatat",
     *   "data": null
     * }
     * @response 400 {
     *   "success": false,
     *   "message": "Gagal mencatat realisasi",
     *   "errors": "amount_spent harus berupa angka lebih besar dari 0"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "budget_item_key": ["The budget item key field is required."],
     *     "amount_spent": ["The amount spent must be greater than 0."]
     *   }
     * }
     */
    public function recordUsage(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'budget_item_key' => 'required|string',
                'description' => 'nullable|string',
                'amount_spent' => 'required|numeric|gt:0',
                'usage_date' => 'nullable|date',
            ]);

            $usageDate = isset($validated['usage_date'])
                ? \Carbon\Carbon::parse($validated['usage_date'])->endOfMonth()->toDateString()
                : now()->endOfMonth()->toDateString();

            BudgetUsage::create([
                'budget_item_key' => $validated['budget_item_key'],
                'usage_date' => $usageDate,
                'description' => $validated['description'] ?? '',
                'amount_spent' => (float) $validated['amount_spent'],
                'data_source' => 'manual',
            ]);

            return $this->success(null, 'Realisasi anggaran berhasil dicatat');
        } catch (\Throwable $e) {
            return $this->error('Gagal mencatat realisasi', $e->getMessage());
        }
    }

    /**
     * Sinkronisasi realisasi FA SAKTI ke budget usage
     *
     * Menyalin data dari `budget_sakti_realizations` ke `budget_usage` menggunakan
     * `usage_date` yang dikirim pada payload. Data yang disalin hanya untuk
     * batch `budget_sakti_realizations` dengan `usage_date` yang sama.
     *
     * Jika endpoint dijalankan ulang untuk tanggal yang sama, baris `budget_usage`
     * dengan `budget_item_key` yang sama akan diganti ulang agar tidak terjadi duplikasi,
     * tanpa memperhatikan tanggal transaksi sebelumnya.
     *
     * @group Kantor - DIPA Budgeting
     *
     * @authenticated
     *
     * @bodyParam usage_date string required Tanggal realisasi yang akan dipakai saat sinkronisasi (format: YYYY-MM-DD). Contoh: 2026-04-22
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Sinkronisasi FA usage berhasil dijalankan",
     *   "data": {
     *     "usage_date": "2026-04-22",
     *     "tahun_anggaran": 2026,
     *     "deleted_rows": 12,
     *     "inserted_rows": 120
     *   }
     * }
     * @response 400 {
     *   "success": false,
     *   "message": "Gagal menjalankan sinkronisasi FA usage",
     *   "errors": "..."
     * }
     */
    public function syncFaUsage(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'usage_date' => 'required|date',
            ]);

            $usageDate = \Carbon\Carbon::parse($validated['usage_date'])->toDateString();
            $tahunAnggaran = (int) \Carbon\Carbon::parse($usageDate)->year;

            $result = DB::transaction(function () use ($usageDate) {
                $deletedRows = DB::table('budget_usage')
                    ->whereDate('usage_date', $usageDate)
                    ->where('data_source', 'sakti')
                    ->whereIn('budget_item_key', function ($query) use ($usageDate) {
                        $query->select('composite_key')
                            ->from('budget_sakti_realizations')
                            ->whereDate('usage_date', $usageDate);
                    })
                    ->delete();

                $insertedRows = DB::affectingStatement('
                    INSERT INTO budget_usage (budget_item_key, usage_date, description, amount_spent, data_source)
                    SELECT
                        sr.composite_key AS budget_item_key,
                        ? AS usage_date,
                        COALESCE(
                            CONCAT(
                                \'<small>\', COALESCE(sr.program_name, \'\'), \'</small><br/>\',
                                \'<small>\', COALESCE(sr.component_name, \'\'), \'</small><br/>\',
                                \'<b>\', COALESCE(sr.description, \'\'), \'</b><br/>\',
                                \'<small>\', COALESCE(sr.account_name, \'\'), \'</small>\'
                            ),
                            sr.description,
                            \'\' 
                        ) AS description,
                        sr.periode_ini AS amount_spent,
                        \'sakti\' AS data_source
                    FROM budget_sakti_realizations sr
                    WHERE sr.usage_date = ?
                        AND sr.periode_ini <> 0
                ', [$usageDate, $usageDate]);

                return [
                    'deleted_rows' => $deletedRows,
                    'inserted_rows' => $insertedRows,
                ];
            });

            return $this->success([
                'usage_date' => $usageDate,
                'tahun_anggaran' => $tahunAnggaran,
                'deleted_rows' => $result['deleted_rows'],
                'inserted_rows' => $result['inserted_rows'],
            ], 'Sinkronisasi FA usage berhasil dijalankan');
        } catch (\Throwable $e) {
            return $this->error('Gagal menjalankan sinkronisasi FA usage', $e->getMessage());
        }
    }

    /**
     * Ambil ringkasan data FA SAKTI untuk sinkronisasi
     *
     * Mengembalikan jumlah baris dan total nominal pada `budget_sakti_realizations`
     * untuk bulan `usage_date` tertentu, agar client dapat mengetahui apakah data siap
     * untuk disinkronkan ke `budget_usage`.
     *
     * @group Kantor - DIPA Budgeting
     *
     * @authenticated
     *
     * @queryParam usage_date string required Bulan usage batch SAKTI (format: YYYY-MM). Contoh: 2026-05
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Ringkasan data FA SAKTI berhasil diambil",
     *   "data": {
     *     "usage_date": "2026-05-01",
     *     "row_count": 120,
     *     "total_amount": 350000000.00
     *   }
     * }
     * @response 400 {
     *   "success": false,
     *   "message": "Gagal mengambil ringkasan data FA SAKTI",
     *   "errors": "..."
     * }
     */
    public function getSyncFaSummary(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'usage_date' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            ]);

            [$year, $month] = array_map('intval', explode('-', $validated['usage_date']));
            $summary = DB::table('budget_sakti_realizations')
                ->selectRaw('COUNT(*) AS row_count, COALESCE(SUM(total_amount), 0) AS total_amount, MAX(usage_date) AS usage_date')
                ->whereYear('usage_date', $year)
                ->whereMonth('usage_date', $month)
                ->first();

            return $this->success([
                'usage_date' => $summary->usage_date,
                'row_count' => (int) ($summary->row_count ?? 0),
                'total_amount' => (float) ($summary->total_amount ?? 0),
            ], 'Ringkasan data FA SAKTI berhasil diambil');
        } catch (\Throwable $e) {
            return $this->error('Gagal mengambil ringkasan data FA SAKTI', $e->getMessage());
        }
    }

    /**
     * Upload dan import file DIPA Excel
     *
     * Mengupload file Excel DIPA baru, menyimpannya ke direktori `public/dipa_excel/`,
     * lalu menjalankan script Python `auto_import.py` untuk memproses ingestion otomatis.
     * Script akan memparse konten Excel ke dalam tabel `budget_items` dan mencatat log di `imported_files`.
     * File yang sudah pernah diimport (berdasarkan hash) akan dilewati.
     *
     * @group Kantor - DIPA Budgeting
     *
     * @authenticated
     *
     * @bodyParam revisi number required Nomor revisi DIPA. Contoh: 3
     * @bodyParam tgl_revisi string required Tanggal revisi (format: YYYY-MM-DD). Contoh: 2026-04-18
     * @bodyParam excel_file file required File Excel DIPA (.xlsx atau .xls). Contoh: RKK_DIPA_REVISI_3_2026_APR_18.xlsx
     *
     * @response 200 {
     *   "success": true,
     *   "message": "File berhasil diupload dan proses import DIPA selesai dikerjakan",
     *   "data": {
     *     "filename": "RKK_DIPA_REVISI_3_2026_APR_18.xlsx",
     *     "logs": "Starting Automated Budget Ingestion (MySQL)...\nNew file detected: RKK_DIPA_REVISI_3_2026_APR_18.xlsx\nExtracted Revision: REVISI_3_2026APR18\nSuccessfully imported 245 items.\nScan Complete. Processed: 1, Skipped: 0"
     *   }
     * }
     * @response 400 {
     *   "success": false,
     *   "message": "Gagal memproses upload/import",
     *   "errors": "Proses ingestion gagal dijalankan"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "revisi": ["The revisi field is required."],
     *     "tgl_revisi": ["The tgl revisi field is required."],
     *     "excel_file": ["The excel file must be a file of type: xlsx, xls."]
     *   }
     * }
     */
    public function importDipaFile(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'revisi' => 'required|numeric',
                'tgl_revisi' => 'required|date',
                'tahun_anggaran' => 'required|numeric',
                'excel_file' => 'required|file|mimes:xlsx,xls',
            ]);

            $revisi = $validated['revisi'];
            $tahunAnggaran = $validated['tahun_anggaran'];
            $tglRevisi = \Carbon\Carbon::parse($validated['tgl_revisi']);
            $standardizedName = sprintf(
                'RKK_DIPA_REVISI_%d_%d_%s_%s.xlsx',
                $revisi,
                $tglRevisi->year,
                self::INDONESIAN_REVISION_MONTHS[$tglRevisi->month - 1],
                str_pad($tglRevisi->day, 2, '0', STR_PAD_LEFT)
            );

            $dipaExcelDir = public_path('dipa_excel');
            if (! is_dir($dipaExcelDir)) {
                mkdir($dipaExcelDir, 0755, true);
            }

            $request->file('excel_file')->move($dipaExcelDir, $standardizedName);

            $pythonScript = base_path('scripts/python/auto_import.py');
            $pythonBinary = env('PYTHON_BINARY', 'python');
            $result = Process::path(base_path())
                ->env([
                    'PATH' => getenv('PATH'),
                    'SystemRoot' => getenv('SystemRoot'),
                ])
                ->run(escapeshellarg($pythonBinary).' '.escapeshellarg($pythonScript).' --year '.escapeshellarg($tahunAnggaran));

            $logs = $result->successful()
                ? $result->output()
                : $result->errorOutput();

            if ($result->failed()) {
                return $this->error('Gagal memproses upload/import', $logs ?: 'Proses ingestion gagal dijalankan');
            }

            return $this->success([
                'filename' => $standardizedName,
                'logs' => $logs ?: 'Proses import selesai tanpa log tambahan',
            ], 'File berhasil diupload dan proses import DIPA selesai dikerjakan');
        } catch (\Throwable $e) {
            return $this->error('Gagal memproses upload/import', $e->getMessage());
        }
    }

    /**
     * Ambil riwayat transaksi pengeluaran
     *
     * Mengembalikan seluruh riwayat transaksi realisasi anggaran dari view `v_usage_history`,
     * diurutkan berdasarkan tanggal transaksi terbaru. Setiap row sudah dinormalisasi
     * agar field `budget_item_key`, `budget_item_at_time`, `description`, dan `usage_description`
     * selalu terisi (fallback ke field alternatif jika null).
     *
     * @group Kantor - DIPA Budgeting
     *
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Riwayat transaksi berhasil diambil",
     *   "data": [
     *     {
     *       "id": 15,
     *       "usage_date": "2026-04-15",
     *       "created_at": "2026-04-15T10:30:00.000000Z",
     *       "budget_item_key": "a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d",
     *       "budget_item_at_time": "a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d",
     *       "description": "Pembelian ATK untuk kegiatan sensus",
     *       "usage_description": "Pembelian ATK untuk kegiatan sensus",
     *       "amount_spent": 5000000
     *     },
     *     {
     *       "id": 14,
     *       "usage_date": "2026-04-10",
     *       "created_at": "2026-04-10T08:00:00.000000Z",
     *       "budget_item_key": "f6e5d4c3b2a1f0e9d8c7b6a5f4e3d2c1",
     *       "budget_item_at_time": "f6e5d4c3b2a1f0e9d8c7b6a5f4e3d2c1",
     *       "description": "Pembayaran honor kegiatan pendaftaran",
     *       "usage_description": "Pembayaran honor kegiatan pendaftaran",
     *       "amount_spent": 12000000
     *     }
     *   ]
     * }
     * @response 400 {
     *   "success": false,
     *   "message": "Gagal mengambil riwayat transaksi",
     *   "errors": "..."
     * }
     */
    public function getHistory(Request $request): JsonResponse
    {
        try {
            $perPage = max(1, min((int) $request->query('per_page', 20), 100));
            $page = max((int) $request->query('page', 1), 1);
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');
            $programName = trim((string) $request->query('program_name', ''));
            $outputName = trim((string) $request->query('output_name', ''));

            $rawQuery = DB::table('v_usage_history as h')
                ->leftJoin('v_latest_budget as lb', function ($join) {
                    $join->on('lb.composite_key', '=', 'h.budget_item_key')
                        ->whereRaw('lb.tahun_anggaran = YEAR(h.usage_date)');
                });

            $baseQuery = clone $rawQuery;
            if ($startDate) {
                $baseQuery->whereDate('h.usage_date', '>=', $startDate);
            }

            if ($endDate) {
                $baseQuery->whereDate('h.usage_date', '<=', $endDate);
            }

            $programOptions = (clone $baseQuery)
                ->whereNotNull('lb.program_name')
                ->distinct()
                ->orderBy('lb.program_name')
                ->pluck('lb.program_name')
                ->values()
                ->all();

            $outputOptions = [];
            if ($programName !== '') {
                $outputOptions = (clone $baseQuery)
                    ->where('lb.program_name', $programName)
                    ->whereNotNull('lb.output_name')
                    ->distinct()
                    ->orderBy('lb.output_name')
                    ->pluck('lb.output_name')
                    ->values()
                    ->all();
            }

            $filteredQuery = clone $baseQuery;
            if ($programName !== '') {
                $filteredQuery->where('lb.program_name', $programName);
            }
            if ($programName !== '' && $outputName !== '') {
                $filteredQuery->where('lb.output_name', $outputName);
            }

            $allSummary = (clone $rawQuery)
                ->selectRaw('COUNT(*) AS total_records, COALESCE(SUM(h.amount_spent), 0) AS total_amount')
                ->first();

            $filteredSummary = (clone $filteredQuery)
                ->selectRaw('COUNT(*) AS total_records, COALESCE(SUM(h.amount_spent), 0) AS total_amount')
                ->first();

            $paginator = (clone $filteredQuery)
                ->select('h.*', 'lb.program_name', 'lb.output_name')
                ->orderBy('h.usage_date', 'desc')
                ->orderBy('h.id', 'desc')
                ->fastPaginate($perPage, ['*'], 'page', $page)
                ->withQueryString();

            $paginator->getCollection()->transform(function ($row) {
                $rowArray = (array) $row;
                $rowArray['budget_item_key'] = $rowArray['budget_item_key'] ?? $rowArray['budget_item_at_time'] ?? $rowArray['composite_key'] ?? null;
                $rowArray['budget_item_at_time'] = $rowArray['budget_item_at_time'] ?? $rowArray['budget_item_key'] ?? $rowArray['composite_key'] ?? null;
                $rowArray['description'] = $rowArray['description'] ?? $rowArray['usage_description'] ?? null;
                $rowArray['usage_description'] = $rowArray['usage_description'] ?? $rowArray['description'] ?? null;

                return $rowArray;
            });

            return $this->success($paginator, 'Riwayat transaksi berhasil diambil', 200, [
                'filter_options' => [
                    'programs' => $programOptions,
                    'outputs' => $outputOptions,
                ],
                'summary' => [
                    'total_records_all' => (int) ($allSummary->total_records ?? 0),
                    'total_amount_all' => (float) ($allSummary->total_amount ?? 0),
                    'total_records_filtered' => (int) ($filteredSummary->total_records ?? 0),
                    'total_amount_filtered' => (float) ($filteredSummary->total_amount ?? 0),
                ],
            ]);
        } catch (\Throwable $e) {
            return $this->error('Gagal mengambil riwayat transaksi', $e->getMessage());
        }
    }

    /**
     * Ambil data pengeluaran orphan
     *
     * Mengembalikan daftar pengeluaran yang terputus link-nya karena revisi DIPA.
     * Item dianggap orphan jika `budget_item_key` tidak ditemukan di view `v_latest_budget`
     * dan tidak ada pemetaan di tabel `item_mapping`. Data dikelompokkan per `budget_item_key`.
     *
     * @group Kantor - DIPA Budgeting
     *
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data orphans berhasil diambil",
     *   "data": [
     *     {
     *       "budget_item_key": "x1y2z3a4b5c6d7e8f9g0h1i2j3k4l5m6",
     *       "last_description": "Pembelian toner printer",
     *       "total_orphan_amount": 3500000,
     *       "transaction_count": 3
     *     },
     *     {
     *       "budget_item_key": "m6l5k4j3i2h1g0f9e8d7c6b5a4z3y2x1",
     *       "last_description": "Sewa kendaraan operasional",
     *       "total_orphan_amount": 2000000,
     *       "transaction_count": 1
     *     }
     *   ]
     * }
     * @response 400 {
     *   "success": false,
     *   "message": "Gagal mengambil data orphans",
     *   "errors": "..."
     * }
     */
    public function getOrphans(): JsonResponse
    {
        try {
            $data = DB::select('
                SELECT
                    u.budget_item_key,
                    MAX(u.description) AS last_description,
                    SUM(u.amount_spent) AS total_orphan_amount,
                    COUNT(*) AS transaction_count
                FROM budget_usage u
                WHERE u.budget_item_key NOT IN (SELECT composite_key FROM v_latest_budget)
                    AND u.budget_item_key NOT IN (SELECT old_composite_key FROM item_mapping)
                GROUP BY u.budget_item_key
                ORDER BY total_orphan_amount DESC, u.budget_item_key ASC
            ');

            return $this->success($data, 'Data orphans berhasil diambil');
        } catch (\Throwable $e) {
            return $this->error('Gagal mengambil data orphans', $e->getMessage());
        }
    }

    /**
     * Simpan pemetaan item anggaran
     *
     * Memetakan kode anggaran lama ke kode anggaran baru (merging) setelah terjadi revisi DIPA.
     * Digunakan untuk menghubungkan kembali transaksi orphan ke item anggaran yang baru.
     * Jika pemetaan sudah ada, akan di-update.
     *
     * @group Kantor - DIPA Budgeting
     *
     * @authenticated
     *
     * @bodyParam old_composite_key string required Composite key item anggaran lama (yang orphan). Contoh: x1y2z3a4b5c6d7e8f9g0h1i2j3k4l5m6
     * @bodyParam new_composite_key string required Composite key item anggaran baru (dari revisi terbaru). Contoh: a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Pemetaan item anggaran berhasil disimpan",
     *   "data": null
     * }
     * @response 400 {
     *   "success": false,
     *   "message": "Gagal menyimpan pemetaan",
     *   "errors": "..."
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "old_composite_key": ["The old composite key field is required."],
     *     "new_composite_key": ["The new composite key field is required."]
     *   }
     * }
     */
    public function saveMapping(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'old_composite_key' => 'required|string',
                'new_composite_key' => 'required|string',
            ]);

            DB::table('item_mapping')->upsert(
                [
                    'old_composite_key' => $validated['old_composite_key'],
                    'new_composite_key' => $validated['new_composite_key'],
                ],
                ['old_composite_key', 'new_composite_key'],
                ['new_composite_key']
            );

            return $this->success(null, 'Pemetaan item anggaran berhasil disimpan');
        } catch (\Throwable $e) {
            return $this->error('Gagal menyimpan pemetaan', $e->getMessage());
        }
    }

    /**
     * Ambil daftar file DIPA yang sudah diimport
     *
     * Mengembalikan log file DIPA Excel yang sudah pernah diimport ke dalam sistem.
     * Data diambil dari tabel `imported_files` dan diurutkan berdasarkan waktu import terbaru.
     *
     * @group Kantor - DIPA Budgeting
     *
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Log file import berhasil diambil",
     *   "data": [
     *     {
     *       "id": 3,
     *       "file_path": "RKK_DIPA_REVISI_2_2026_JAN_21.xlsx",
     *       "file_hash": "e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855",
     *       "revision_name": "REVISI_2_21JAN2026",
     *       "imported_at": "2026-01-21 14:30:00",
     *       "created_at": "2026-01-21 14:30:00",
     *       "filename": "RKK_DIPA_REVISI_2_2026_JAN_21.xlsx",
     *       "status": "success"
     *     },
     *     {
     *       "id": 1,
     *       "file_path": "RKK_DIPA_REVISI_1_2025_DES_26.xlsx",
     *       "file_hash": "a7ffc6f8bf1ed76651c14756a061d662f580ff4de43b49fa82d80a4b80f8434a",
     *       "revision_name": "REVISI_1_26DES2025",
     *       "imported_at": "2025-12-26 09:15:00",
     *       "created_at": "2025-12-26 09:15:00",
     *       "filename": "RKK_DIPA_REVISI_1_2025_DES_26.xlsx",
     *       "status": "success"
     *     }
     *   ]
     * }
     * @response 400 {
     *   "success": false,
     *   "message": "Gagal mengambil log file",
     *   "errors": "..."
     * }
     */
    public function getFiles(): JsonResponse
    {
        try {
            $data = DB::select("
                SELECT
                    id,
                    file_path,
                    file_hash,
                    revision_name,
                    tahun_anggaran,
                    imported_at,
                    imported_at AS created_at,
                    SUBSTRING_INDEX(file_path, '/', -1) AS filename,
                    'success' AS status
                FROM imported_files
                ORDER BY imported_at DESC, id DESC
            ");

            return $this->success($data, 'Log file import berhasil diambil');
        } catch (\Throwable $e) {
            return $this->error('Gagal mengambil log file', $e->getMessage());
        }
    }

    /**
     * Audit integritas import file DIPA
     *
     * Menjalankan audit dengan mem-parse ulang file Excel DIPA yang sudah diimport,
     * lalu membandingkan hasil parse dengan data `budget_items` untuk `revision_name`
     * dan `tahun_anggaran` file tersebut. Digunakan untuk mendeteksi row sumber
     * yang belum masuk ke database, row ekstra di database, serta duplikasi key
     * di file sumber.
     *
     * @group Kantor - DIPA Budgeting
     *
     * @authenticated
     *
     * @urlParam id int required ID log file import pada tabel `imported_files`. Contoh: 3
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Audit integritas import berhasil dijalankan",
     *   "data": {
     *     "file_id": 3,
     *     "filename": "RKK_DIPA_REVISI_3_2026_APR_18.xlsx",
     *     "revision_name": "REVISI_3_2026APR18",
     *     "tahun_anggaran": 2026,
     *     "source_row_count": 245,
     *     "source_unique_key_count": 245,
     *     "database_row_count": 245,
     *     "missing_row_count": 0,
     *     "extra_db_row_count": 0,
     *     "duplicate_source_key_count": 0,
     *     "is_complete": true,
     *     "missing_rows": [],
     *     "extra_db_rows": [],
     *     "duplicate_source_rows": []
     *   }
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "File import tidak ditemukan"
     * }
     */
    public function auditImportedFileIntegrity(int $id): JsonResponse
    {
        try {
            $file = ImportedFile::find($id);

            if (! $file) {
                return $this->error('File import tidak ditemukan', null, 404);
            }

            $filename = basename((string) $file->file_path);
            $fullPath = public_path('dipa_excel'.DIRECTORY_SEPARATOR.$filename);

            if (! is_file($fullPath)) {
                return $this->error('File Excel sumber tidak ditemukan di server', $filename, 404);
            }

            $pythonScript = base_path('scripts/python/audit_rkk_import.py');
            $pythonBinary = env('PYTHON_BINARY', 'python');
            $result = Process::path(base_path())
                ->env([
                    'PATH' => getenv('PATH'),
                    'SystemRoot' => getenv('SystemRoot'),
                ])
                ->run(
                    escapeshellarg($pythonBinary).' '.
                    escapeshellarg($pythonScript).' '.
                    '--file '.escapeshellarg($fullPath).' '.
                    '--revision '.escapeshellarg((string) $file->revision_name).' '.
                    '--year '.escapeshellarg((string) $file->tahun_anggaran)
                );

            $logs = $result->successful()
                ? $result->output()
                : $result->errorOutput();

            if ($result->failed()) {
                return $this->error('Gagal menjalankan audit integritas import', $logs ?: 'Audit script gagal dijalankan');
            }

            $auditData = json_decode($logs, true);
            if (! is_array($auditData)) {
                return $this->error('Output audit integritas tidak valid', $logs ?: 'Audit script tidak mengembalikan JSON yang valid');
            }

            return $this->success([
                'file_id' => $file->id,
                'filename' => $filename,
                'revision_name' => $file->revision_name,
                'tahun_anggaran' => (int) $file->tahun_anggaran,
                'source_row_count' => (int) ($auditData['source_row_count'] ?? 0),
                'source_unique_key_count' => (int) ($auditData['source_unique_key_count'] ?? 0),
                'database_row_count' => (int) ($auditData['database_row_count'] ?? 0),
                'missing_row_count' => (int) ($auditData['missing_row_count'] ?? 0),
                'extra_db_row_count' => (int) ($auditData['extra_db_row_count'] ?? 0),
                'duplicate_source_key_count' => (int) ($auditData['duplicate_source_key_count'] ?? 0),
                'is_complete' => (bool) ($auditData['is_complete'] ?? false),
                'missing_rows' => $auditData['missing_rows'] ?? [],
                'extra_db_rows' => $auditData['extra_db_rows'] ?? [],
                'duplicate_source_rows' => $auditData['duplicate_source_rows'] ?? [],
            ], 'Audit integritas import berhasil dijalankan');
        } catch (\Throwable $e) {
            return $this->error('Gagal menjalankan audit integritas import', $e->getMessage());
        }
    }

    /**
     * Re-import file DIPA yang sudah ada
     *
     * Menjalankan ulang parser `converter.py` untuk satu file DIPA yang sudah tercatat
     * di `imported_files`, agar row yang sebelumnya terlewat dapat masuk ke `budget_items`
     * tanpa terganjal mekanisme skip berbasis hash pada `auto_import.py`.
     *
     * @group Kantor - DIPA Budgeting
     *
     * @authenticated
     *
     * @urlParam id int required ID log file import pada tabel `imported_files`. Contoh: 18
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Re-import file DIPA berhasil dijalankan",
     *   "data": {
     *     "file_id": 18,
     *     "filename": "RKK_DIPA_REVISI_8_2025_APR_16.xlsx",
     *     "revision_name": "REVISI_8_2025APR16",
     *     "tahun_anggaran": 2025,
     *     "logs": "Loading ...\nImported 128 items ..."
     *   }
     * }
     */
    public function reimportFile(int $id): JsonResponse
    {
        try {
            $file = ImportedFile::find($id);

            if (! $file) {
                return $this->error('File import tidak ditemukan', null, 404);
            }

            $filename = basename((string) $file->file_path);
            $fullPath = public_path('dipa_excel'.DIRECTORY_SEPARATOR.$filename);

            if (! is_file($fullPath)) {
                return $this->error('File Excel sumber tidak ditemukan di server', $filename, 404);
            }

            $pythonScript = base_path('scripts/python/converter.py');
            $pythonBinary = env('PYTHON_BINARY', 'python');
            $result = Process::path(base_path())
                ->env([
                    'PATH' => getenv('PATH'),
                    'SystemRoot' => getenv('SystemRoot'),
                ])
                ->run(
                    escapeshellarg($pythonBinary).' '.
                    escapeshellarg($pythonScript).' '.
                    '--file '.escapeshellarg($fullPath).' '.
                    '--revision '.escapeshellarg((string) $file->revision_name).' '.
                    '--year '.escapeshellarg((string) $file->tahun_anggaran)
                );

            $logs = $result->successful()
                ? $result->output()
                : $result->errorOutput();

            if ($result->failed()) {
                return $this->error('Gagal menjalankan re-import file DIPA', $logs ?: 'Proses re-import gagal dijalankan');
            }

            return $this->success([
                'file_id' => $file->id,
                'filename' => $filename,
                'revision_name' => $file->revision_name,
                'tahun_anggaran' => (int) $file->tahun_anggaran,
                'logs' => $logs ?: 'Proses re-import selesai tanpa log tambahan',
            ], 'Re-import file DIPA berhasil dijalankan');
        } catch (\Throwable $e) {
            return $this->error('Gagal menjalankan re-import file DIPA', $e->getMessage());
        }
    }

    /**
     * Update nominal transaksi realisasi
     *
     * Memperbarui jumlah pengeluaran pada transaksi realisasi yang sudah dicatat.
     * Hanya field `amount_spent` yang dapat diperbarui.
     *
     * @group Kantor - DIPA Budgeting
     *
     * @authenticated
     *
     * @urlParam id int required ID transaksi realisasi. Contoh: 15
     *
     * @bodyParam amount_spent number required Nominal pengeluaran baru, harus lebih besar dari 0. Contoh: 7500000
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Nominal transaksi berhasil diperbarui",
     *   "data": null
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Gagal memperbarui transaksi",
     *   "errors": "Transaksi tidak ditemukan"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "amount_spent": ["The amount spent must be greater than 0."]
     *   }
     * }
     */
    public function updateUsageAmount(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'amount_spent' => 'required|numeric|gt:0',
            ]);

            $existing = BudgetUsage::find($id);
            if (! $existing) {
                return $this->error('Gagal memperbarui transaksi', 'Transaksi tidak ditemukan', 404);
            }

            $existing->update(['amount_spent' => (float) $validated['amount_spent']]);

            return $this->success(null, 'Nominal transaksi berhasil diperbarui');
        } catch (\Throwable $e) {
            return $this->error('Gagal memperbarui transaksi', $e->getMessage());
        }
    }

    /**
     * Hapus transaksi realisasi
     *
     * Menghapus transaksi pengeluaran dari riwayat berdasarkan ID.
     *
     * @group Kantor - DIPA Budgeting
     *
     * @authenticated
     *
     * @urlParam id int required ID transaksi realisasi yang akan dihapus. Contoh: 15
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Transaksi berhasil dihapus",
     *   "data": null
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Gagal menghapus transaksi",
     *   "errors": "Transaksi tidak ditemukan"
     * }
     */
    public function deleteUsage(int $id): JsonResponse
    {
        try {
            $existing = BudgetUsage::find($id);
            if (! $existing) {
                return $this->error('Gagal menghapus transaksi', 'Transaksi tidak ditemukan', 404);
            }

            $existing->delete();

            return $this->success(null, 'Transaksi berhasil dihapus');
        } catch (\Throwable $e) {
            return $this->error('Gagal menghapus transaksi', $e->getMessage());
        }
    }

    /**
     * Catat rencana pencairan bulanan
     *
     * Mencatat rencana anggaran (plan) untuk item anggaran tertentu pada bulan tertentu.
     * Tanggal target akan otomatis dinormalisasi ke awal bulan (tanggal 1).
     *
     * @group Kantor - DIPA Budgeting
     *
     * @authenticated
     *
     * @bodyParam budget_item_key string required Composite key item anggaran (dari picker). Contoh: a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d
     * @bodyParam planned_amount number required Jumlah rencana pencairan, harus lebih besar dari 0. Contoh: 10000000
     * @bodyParam target_month string required Bulan target pencairan (format: YYYY-MM). Contoh: 2026-05
     * @bodyParam description string Deskripsi rencana pencairan. Contoh: Rencana pembelian komputer untuk IPDS
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Rencana anggaran berhasil dicatat",
     *   "data": null
     * }
     * @response 400 {
     *   "success": false,
     *   "message": "Gagal mencatat rencana",
     *   "errors": "planned_amount harus berupa angka lebih besar dari 0"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "budget_item_key": ["The budget item key field is required."],
     *     "planned_amount": ["The planned amount must be greater than 0."],
     *     "target_month": ["The target month is not a valid date."]
     *   }
     * }
     */
    public function recordPlan(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'budget_item_key' => 'required|string',
                'description' => 'nullable|string',
                'planned_amount' => 'required|numeric|gt:0',
                'target_month' => 'required|date',
            ]);

            $targetMonth = \Carbon\Carbon::parse($validated['target_month'])->startOfMonth();

            BudgetPlan::create([
                'budget_item_key' => $validated['budget_item_key'],
                'target_month' => $targetMonth,
                'description' => $validated['description'] ?? '',
                'planned_amount' => (float) $validated['planned_amount'],
            ]);

            return $this->success(null, 'Rencana anggaran berhasil dicatat');
        } catch (\Throwable $e) {
            return $this->error('Gagal mencatat rencana', $e->getMessage());
        }
    }

    /**
     * Ambil daftar rencana pencairan bulanan
     *
     * Mengembalikan daftar rencana pencairan anggaran (plan) yang sudah dicatat.
     * Dapat difilter berdasarkan bulan tertentu. Jika tidak ada filter, mengembalikan semua data.
     * Setiap plan dilengkapi informasi nama program, kegiatan, dan akun dari revisi terbaru.
     *
     * @group Kantor - DIPA Budgeting
     *
     * @authenticated
     *
     * @queryParam month string Filter berdasarkan bulan target (format: YYYY-MM). Jika tidak diisi, mengembalikan semua data. Contoh: 2026-05
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Daftar rencana berhasil diambil",
     *   "data": [
     *     {
     *       "id": 1,
     *       "budget_item_key": "a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d",
     *       "target_month": "2026-05-01",
     *       "description": "Rencana pembelian komputer untuk IPDS",
     *       "planned_amount": 10000000,
     *       "created_at": "2026-04-18T10:00:00.000000Z",
     *       "program_code": "5241",
     *       "activity_code": "5241.SAK",
     *       "account_code": "524111",
     *       "budget_name": "Program Statistik > Kegiatan SAK > Pembelian Peralatan Komputer"
     *     }
     *   ]
     * }
     * @response 400 {
     *   "success": false,
     *   "message": "Gagal mengambil daftar rencana",
     *   "errors": "..."
     * }
     */
    public function getPlans(Request $request): JsonResponse
    {
        try {
            $month = $request->query('month');
            $perPage = max(1, min((int) $request->query('per_page', 20), 100));
            $page = max((int) $request->query('page', 1), 1);

            $query = DB::table('budget_plans as p')
                ->join('budget_items as b', function ($join) {
                    $join->on('p.budget_item_key', '=', 'b.composite_key')
                        ->whereRaw('b.revision_date = (SELECT MAX(revision_date) FROM budget_items)');
                })
                ->selectRaw("
                    p.id,
                    p.budget_item_key,
                    p.target_month,
                    p.description,
                    p.planned_amount,
                    p.created_at,
                    b.program_code,
                    b.activity_code,
                    b.account_code,
                    CONCAT(b.program_name, ' > ', b.activity_name, ' > ', b.description) AS budget_name
                ");

            if ($month) {
                $normalizedMonth = \Carbon\Carbon::parse($month)->startOfMonth()->toDateString();
                $query->whereDate('p.target_month', '=', $normalizedMonth);
            }

            $query->orderBy('p.target_month', 'asc')
                ->orderBy('p.id', 'desc');

            if ($request->has('page') || $request->has('per_page')) {
                $data = $query
                    ->paginate($perPage, ['*'], 'page', $page)
                    ->withQueryString();
            } else {
                $data = $query->get();
            }

            return $this->success($data, 'Daftar rencana berhasil diambil');
        } catch (\Throwable $e) {
            return $this->error('Gagal mengambil daftar rencana', $e->getMessage());
        }
    }

    /**
     * Ubah data rencana pencairan bulanan
     */
    public function updatePlan(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'planned_amount' => 'required|numeric|gt:0',
                'target_month' => 'required|date',
                'description' => 'nullable|string',
            ]);

            $plan = BudgetPlan::find($id);

            if (!$plan) {
                return $this->error('Rencana tidak ditemukan', null, 404);
            }

            $plan->update([
                'target_month' => \Carbon\Carbon::parse($validated['target_month'])->startOfMonth(),
                'description' => $validated['description'] ?? '',
                'planned_amount' => (float) $validated['planned_amount'],
            ]);

            return $this->success(null, 'Rencana anggaran berhasil diperbarui');
        } catch (\Throwable $e) {
            return $this->error('Gagal memperbarui rencana', $e->getMessage());
        }
    }

    /**
     * Hapus data rencana pencairan bulanan
     */
    public function deletePlan(int $id): JsonResponse
    {
        try {
            $plan = BudgetPlan::find($id);

            if (!$plan) {
                return $this->error('Rencana tidak ditemukan', null, 404);
            }

            $plan->delete();

            return $this->success(null, 'Rencana anggaran berhasil dihapus');
        } catch (\Throwable $e) {
            return $this->error('Gagal menghapus rencana', $e->getMessage());
        }
    }

    /**
     * Ambil data rekonsiliasi SAKTI
     *
     * Membandingkan realisasi dari SAKTI dengan pencatatan internal budget_usage.
     *
     * @group Kantor - DIPA Budgeting
     * @authenticated
     * @queryParam year int Tahun anggaran. Default tahun berjalan.
     */
    public function getReconciliation(Request $request): JsonResponse
    {
        try {
            $year = $request->query('year', now()->year);
            $data = DB::select('
                SELECT * 
                FROM v_sakti_reconciliation 
                WHERE tahun_anggaran = ?
                ORDER BY ABS(selisih) DESC, composite_key ASC
            ', [$year]);

            return $this->success($data, 'Data rekonsiliasi SAKTI berhasil diambil');
        } catch (\Throwable $e) {
            return $this->error('Gagal mengambil data rekonsiliasi', $e->getMessage());
        }
    }

    /**
     * Import file Realisasi SAKTI
     *
     * Menjalankan script Python sakti_import.py untuk memproses file FA Detail SAKTI.
     *
     * @group Kantor - DIPA Budgeting
     * @authenticated
     * @bodyParam tahun_anggaran number required
     * @bodyParam usage_date string required Tanggal usage batch SAKTI (format: YYYY-MM-DD). Contoh: 2025-01-31
     * @bodyParam excel_file file required
     */
    public function importSakti(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'tahun_anggaran' => 'required|numeric',
                'usage_date' => 'required|date',
                'excel_file' => 'required|file|mimes:xlsx,xls,bin',
            ]);

            $tahunAnggaran = $validated['tahun_anggaran'];
            $usageDate = \Carbon\Carbon::parse($validated['usage_date'])->toDateString();
            
            // Extract creation date from Excel metadata
            $fileDate = now();
            try {
                $tempPath = $request->file('excel_file')->getRealPath();
                $reader = IOFactory::createReaderForFile($tempPath);
                $spreadsheet = $reader->load($tempPath);
                $createdTimestamp = $spreadsheet->getProperties()->getCreated();
                if ($createdTimestamp) {
                    $fileDate = \Illuminate\Support\Carbon::createFromTimestamp($createdTimestamp);
                }
                $spreadsheet->disconnectWorksheets(); // Free memory
            } catch (\Throwable $e) {
                // Fallback to current time if meta cannot be read
            }

            $filename = 'LAP_FA_SAKTI_' . $fileDate->format('Ymd_His') . '.xlsx';

            $saktiDir = public_path('sakti_excel');
            if (! is_dir($saktiDir)) {
                mkdir($saktiDir, 0755, true);
            }

            $path = $request->file('excel_file')->move($saktiDir, $filename);

            $pythonScript = base_path('scripts/python/sakti_import.py');
            $pythonBinary = env('PYTHON_BINARY', 'python');
            
            $result = Process::path(base_path())
                ->env([
                    'PATH' => getenv('PATH'),
                    'SystemRoot' => getenv('SystemRoot'),
                ])
                ->run(
                    escapeshellarg($pythonBinary)
                    .' '.escapeshellarg($pythonScript)
                    .' --file '.escapeshellarg($path->getRealPath())
                    .' --year '.escapeshellarg($tahunAnggaran)
                    .' --usage-date '.escapeshellarg($usageDate)
                );

            $logs = $result->successful() ? $result->output() : $result->errorOutput();

            if ($result->failed()) {
                return $this->error('Gagal memproses import SAKTI', $logs ?: 'Proses ingestion gagal dijalankan');
            }

            $fileHash = hash_file('sha256', $path->getRealPath());
            ImportedFile::updateOrCreate(
                ['file_hash' => $fileHash],
                [
                    'file_path' => $filename,
                    'revision_name' => pathinfo($filename, PATHINFO_FILENAME),
                    'tahun_anggaran' => $tahunAnggaran,
                    'imported_at' => now(),
                ]
            );

            return $this->success([
                'filename' => $filename,
                'usage_date' => $usageDate,
                'logs' => $logs ?: 'Proses import selesai',
            ], 'Data SAKTI berhasil diimport');
        } catch (\Throwable $e) {
            return $this->error('Gagal memproses import SAKTI', $e->getMessage());
        }
    }

    /**
     * Verifikasi dependency view database DIPA
     *
     * Menjalankan smoke check untuk memastikan semua view database yang dibutuhkan
     * oleh modul DIPA sudah ada dan dapat di-query. Berguna untuk debugging masalah
     * migrasi atau setup database.
     *
     * View yang dicek:
     * - `v_budget_monitoring` — dasbor monitoring pagu versus realisasi
     * - `v_item_picker` — pencarian item anggaran
     * - `v_usage_history` — riwayat transaksi realisasi
     * - `v_latest_budget` — referensi item revisi terbaru
     *
     * @group Kantor - DIPA Budgeting
     *
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Verifikasi dependency view DIPA berhasil dijalankan",
     *   "data": [
     *     {
     *       "view_name": "v_budget_monitoring",
     *       "ok": true,
     *       "detail": "query berhasil"
     *     },
     *     {
     *       "view_name": "v_item_picker",
     *       "ok": true,
     *       "detail": "query berhasil"
     *     },
     *     {
     *       "view_name": "v_usage_history",
     *       "ok": true,
     *       "detail": "query berhasil"
     *     },
     *     {
     *       "view_name": "v_latest_budget",
     *       "ok": true,
     *       "detail": "query berhasil"
     *     }
     *   ]
     * }
     * @response 200 {
     *   "success": true,
     *   "message": "Verifikasi dependency view DIPA berhasil dijalankan",
     *   "data": [
     *     {
     *       "view_name": "v_budget_monitoring",
     *       "ok": true,
     *       "detail": "query berhasil"
     *     },
     *     {
     *       "view_name": "v_item_picker",
     *       "ok": false,
     *       "detail": "SQLSTATE[42S02]: Base table or view not found: 1146 Table 'simentordb.v_item_picker' doesn't exist"
     *     }
     *   ]
     * }
     * @response 400 {
     *   "success": false,
     *   "message": "Gagal memverifikasi dependency view DIPA",
     *   "errors": "..."
     * }
     */
    public function verifyDependencies(): JsonResponse
    {
        try {
            $results = [];

            foreach (self::VIEW_DEPENDENCIES as $view) {
                try {
                    DB::selectOne($view['check_sql']);
                    $results[] = [
                        'view_name' => $view['view_name'],
                        'ok' => true,
                        'detail' => 'query berhasil',
                    ];
                } catch (\Throwable $e) {
                    $results[] = [
                        'view_name' => $view['view_name'],
                        'ok' => false,
                        'detail' => $e->getMessage() ?? 'gagal query view',
                    ];
                }
            }

            return $this->success($results, 'Verifikasi dependency view DIPA berhasil dijalankan');
        } catch (\Throwable $e) {
            return $this->error('Gagal memverifikasi dependency view DIPA', $e->getMessage());
        }
    }

    /**
     * Export data perencanaan DIPA ke Excel
     *
     * Mengexport data rencana pencairan anggaran dengan struktur:
     * - Header template FA: Uraian, Pagu Revisi, Rencana Realisasi TA {year}
     * - Kolom rencana: JAN, FEB, ..., DES, SISA PAGU
     * - Baris: total keseluruhan, subtotal program/activity/output, dan item dari v_latest_budget
     *
     * @group Kantor - DIPA Budgeting
     * @authenticated
     * @queryParam year int Tahun anggaran. Default tahun berjalan.
     * @response 200 binary File Excel Rencana_Realisasi_TA_{year}.xlsx
     */
    public function exportPlans(Request $request)
    {
        try {
            $year = (int) $request->query('year', now()->year);

            $budgetItems = DB::select('
                SELECT 
                    TRIM(composite_key) AS composite_key,
                    account_code,
                    account_name,
                    program_code,
                    program_name,
                    activity_code,
                    activity_name,
                    output_code,
                    output_name,
                    total_amount,
                    description,
                    CONCAT(
                        \'<small>\', COALESCE(program_name, \'\'), \'</small><br/>\',
                        \'<small>\', COALESCE(activity_name, \'\'), \'</small><br/>\',
                        \'<b>\', COALESCE(description, \'\'), \'</b><br/>\',
                        \'<small>\', COALESCE(account_name, \'\'), \'</small>\'
                    ) AS formatted_description
                FROM v_latest_budget
                WHERE tahun_anggaran = ?
                ORDER BY program_code ASC, activity_code ASC, output_code ASC, account_code ASC, composite_key ASC
            ', [$year]);

            $directPlans = DB::select('
                SELECT 
                    TRIM(p.budget_item_key) AS budget_item_key,
                    SUM(p.planned_amount) AS planned_amount,
                    MONTH(p.target_month) as month_number
                FROM budget_plans p
                WHERE YEAR(p.target_month) = ?
                GROUP BY TRIM(p.budget_item_key), MONTH(p.target_month)
                ORDER BY budget_item_key ASC, month_number ASC
            ', [$year]);

            $mappedPlans = DB::select('
                SELECT 
                    TRIM(p.budget_item_key) AS original_budget_item_key,
                    TRIM(m.new_composite_key) AS budget_item_key,
                    SUM(p.planned_amount) AS planned_amount,
                    MONTH(p.target_month) as month_number
                FROM budget_plans p
                LEFT JOIN (
                    SELECT TRIM(old_composite_key) AS old_composite_key, MAX(TRIM(new_composite_key)) AS new_composite_key
                    FROM item_mapping
                    GROUP BY TRIM(old_composite_key)
                ) m ON TRIM(p.budget_item_key) = m.old_composite_key
                WHERE YEAR(p.target_month) = ?
                    AND m.new_composite_key IS NOT NULL
                    AND NOT EXISTS (
                        SELECT 1
                        FROM v_latest_budget lb
                        WHERE lb.tahun_anggaran = ?
                            AND TRIM(lb.composite_key) = TRIM(p.budget_item_key)
                    )
                GROUP BY TRIM(p.budget_item_key), TRIM(m.new_composite_key), MONTH(p.target_month)
                ORDER BY budget_item_key ASC, month_number ASC
            ', [$year, $year]);

            $directPlansByItemAndMonth = [];
            foreach ($directPlans as $plan) {
                $key = $plan->budget_item_key;
                if (!isset($directPlansByItemAndMonth[$key])) {
                    $directPlansByItemAndMonth[$key] = array_fill(0, 13, 0);
                }
                $monthIndex = (int) $plan->month_number;
                $directPlansByItemAndMonth[$key][$monthIndex] += $plan->planned_amount;
            }

            $mappedPlansByItemAndMonth = [];
            $planSourceKeys = [];
            $mappedSourceKeys = [];
            foreach ($mappedPlans as $plan) {
                $key = $plan->budget_item_key;
                if (!isset($mappedPlansByItemAndMonth[$key])) {
                    $mappedPlansByItemAndMonth[$key] = array_fill(0, 13, 0);
                }
                if (!isset($planSourceKeys[$key])) {
                    $planSourceKeys[$key] = [];
                }
                $monthIndex = (int) $plan->month_number;
                $mappedPlansByItemAndMonth[$key][$monthIndex] += $plan->planned_amount;
                $planSourceKeys[$key][] = $plan->original_budget_item_key;
                $mappedSourceKeys[$plan->original_budget_item_key] = true;
            }

            $exportData = [];
            $matchedPlanKeys = [];
            foreach ($budgetItems as $item) {
                $key = $item->composite_key;
                $monthlyValues = array_fill(0, 13, 0);
                foreach ([$directPlansByItemAndMonth, $mappedPlansByItemAndMonth] as $planBuckets) {
                    foreach (($planBuckets[$key] ?? []) as $monthIndex => $plannedAmount) {
                        $monthlyValues[$monthIndex] += $plannedAmount;
                    }
                }
                $matchedPlanKeys[$key] = true;

                $exportData[] = (object) [
                    'budget_item_key' => $key,
                    'formatted_description' => $item->formatted_description,
                    'program_code' => $item->program_code,
                    'program_name' => $item->program_name,
                    'activity_code' => $item->activity_code,
                    'activity_name' => $item->activity_name,
                    'output_code' => $item->output_code,
                    'output_name' => $item->output_name,
                    'account_code' => $item->account_code,
                    'account_name' => $item->account_name,
                    'description' => $item->description,
                    'pagu_revisi' => (float) $item->total_amount,
                    'months' => array_slice($monthlyValues, 1, 12),
                ];
            }

            $unmatchedPlansByItemAndMonth = $directPlansByItemAndMonth;
            foreach (array_keys($mappedSourceKeys) as $mappedSourceKey) {
                unset($unmatchedPlansByItemAndMonth[$mappedSourceKey]);
            }
            foreach ($mappedPlansByItemAndMonth as $key => $mappedMonthlyValues) {
                if (!isset($unmatchedPlansByItemAndMonth[$key])) {
                    $unmatchedPlansByItemAndMonth[$key] = array_fill(0, 13, 0);
                }
                foreach ($mappedMonthlyValues as $monthIndex => $plannedAmount) {
                    $unmatchedPlansByItemAndMonth[$key][$monthIndex] += $plannedAmount;
                }
            }

            foreach ($unmatchedPlansByItemAndMonth as $key => $monthlyValues) {
                if (isset($matchedPlanKeys[$key])) {
                    continue;
                }

                $sourceKeys = array_values(array_unique($planSourceKeys[$key] ?? [$key]));
                $sourceLabel = implode(', ', $sourceKeys);
                $description = $sourceLabel === $key
                    ? "Rencana orphan belum terpetakan: {$key}"
                    : "Rencana orphan dari {$sourceLabel} dipetakan ke {$key}, tetapi key tujuan tidak ada di v_latest_budget";

                $exportData[] = (object) [
                    'budget_item_key' => $key,
                    'formatted_description' => $description,
                    'program_code' => 'ORPHAN',
                    'program_name' => 'Rencana belum terhubung ke v_latest_budget',
                    'activity_code' => 'ORPHAN',
                    'activity_name' => 'Periksa hasil integritas data dan item_mapping',
                    'output_code' => 'ORPHAN',
                    'output_name' => 'Budget plan orphan',
                    'account_code' => $key,
                    'account_name' => 'Composite key tidak ditemukan pada revisi terbaru',
                    'description' => $description,
                    'pagu_revisi' => 0,
                    'months' => array_slice($monthlyValues, 1, 12),
                ];
            }

            $spreadsheet = (new BudgetPlansExport($exportData, $year))->toSpreadsheet();
            $filename = "Rencana_Realisasi_TA_{$year}.xlsx";

            return response()->streamDownload(function () use ($spreadsheet) {
                $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                $writer->save('php://output');
                $spreadsheet->disconnectWorksheets();
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        } catch (\Throwable $e) {
            return $this->error('Gagal export data perencanaan', $e->getMessage());
        }
    }
}
