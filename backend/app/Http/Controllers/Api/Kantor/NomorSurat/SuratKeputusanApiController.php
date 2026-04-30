<?php
namespace App\Http\Controllers\Api\Kantor\NomorSurat;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\SkBast;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SuratKeputusanApiController extends BaseApiController
{
    /**
     * Menampilkan daftar surat keputusan dengan pagination dan filtering.
     *
     * Endpoint ini mengembalikan daftar surat keputusan dengan kemampuan filtering
     * berdasarkan tahun, tanggal, dan pencarian. Mendukung pagination dan sorting.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Nomor Surat Keputusan
     * @authenticated
     *
     * @queryParam filter[thn] string Filter berdasarkan tahun (default: tahun saat ini). Contoh: 2024
     * @queryParam filter[tanggal] date Filter berdasarkan tanggal (format: YYYY-MM-DD). Contoh: 2024-01-15
     * @queryParam filter[kepada] string Pencarian berdasarkan kepada. Contoh: Tim
     * @queryParam filter[perihal] string Pencarian berdasarkan perihal. Contoh: Survei
     * @queryParam filter[search] string Pencarian global di kepada dan perihal. Contoh: Tim
     * @queryParam sort string Kolom untuk sorting (default: -tanggal). Contoh: -tanggal,nomor
     * @queryParam per_page int Jumlah item per halaman (default: 10). Contoh: 10
     * @queryParam cursor string Cursor untuk pagination. Contoh: eyJpZCI6M...
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data berhasil diambil",
     *   "data": [
     *     {
     *       "id": 1,
     *       "thn": "2024",
     *       "bln": "I",
     *       "tanggal": "2024-01-15",
     *       "nomor": "0001",
     *       "no_sisip": null,
     *       "no_surat": "0001/SK/I/2024",
     *       "oleh": "Kepala BPS",
     *       "kegiatan": "Survei Sosial Ekonomi",
     *       "kepada": "Tim Survei",
     *       "perihal": "Penunjukan Tim Survei Survei Sosial Ekonomi",
     *       "type": "SK",
     *       "kol_lampiran": "Surat Tugas;Daftar Nama",
     *       "created_by": 1,
     *       "created_at": "2024-01-15T00:00:00.000000Z",
     *       "updated_at": "2024-01-15T00:00:00.000000Z"
     *     }
     *   ],
     *   "meta": {
     *     "per_page": 10,
     *     "has_more": false,
     *     "count": 1
     *   },
     *   "links": {
     *     "next_cursor": null,
     *     "next_page_url": null,
     *     "prev_cursor": null,
     *     "prev_page_url": null,
     *     "path": "http://localhost:8000/api/kantor/nomor-surat/surat-keputusan"
     *   },
     *   "pagination_info": {
     *     "total_page": 1,
     *     "total_records": 1
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Get tahun from either 'tahun' query parameter or 'filter[thn]'
        $tahun = $request->get('tahun') ??
                 ($request->get('filter')['thn'] ?? null);

        // Prepare base query (year filter is applied only when explicitly provided)
        $baseQuery = SkBast::where('type', 'SK');
        if (!empty($tahun)) {
            $baseQuery->where('thn', $tahun);
        }

        // Get total count for pagination info (separate query for performance)
        $totalRecords = $baseQuery->count();

        // Use Spatie QueryBuilder for filtering and sorting
        $data = QueryBuilder::for($baseQuery)
            ->allowedFilters([
                AllowedFilter::exact('thn'),
                AllowedFilter::exact('tahun', 'thn'),
                AllowedFilter::exact('tanggal'),
                'kepada',
                'perihal',
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($q) use ($value) {
                        $q->where('kepada', 'LIKE', "%{$value}%")
                            ->orWhere('perihal', 'LIKE', "%{$value}%");
                    });
                }),
            ])
            ->allowedSorts(['id', 'nomor', 'tanggal', 'thn'])
            ->defaultSort('-nomor', '-tanggal')
            ->fastPaginate($request->get('per_page', 10));

        $perPage = $request->get('per_page', 10);
        $totalPages = $perPage > 0 ? ceil($totalRecords / $perPage) : 1;
        $extras = [
            'pagination_info' => [
                'total_page' => $totalPages,
                'total_records' => $totalRecords,
            ]
        ];

        return $this->success($data, 'Data berhasil diambil', 200, $extras);
    }

    /**
     * Membuat surat keputusan baru dengan nomor otomatis.
     *
     * Endpoint ini membuat surat keputusan baru dengan sistem penomoran otomatis
     * dan konversi bulan ke format Roman. Nomor akan diformat sesuai dengan
     * template FORMAT_SK yang ada di settings. Hanya pengguna yang sudah login
     * yang dapat mengakses endpoint ini.
     *
     * @group Nomor Surat Keputusan
     * @authenticated
     *
     * @bodyParam thn string required Tahun surat. Contoh: 2024
     * @bodyParam tanggal date required Tanggal surat (format: YYYY-MM-DD). Contoh: 2024-01-15
     * @bodyParam nomor string required Nomor urut surat. Contoh: 1
     * @bodyParam oleh string Pembuat surat (opsional). Contoh: Kepala BPS
     * @bodyParam kegiatan string Nama kegiatan (opsional). Contoh: Survei Sosial Ekonomi
     * @bodyParam kepada string required Penerima surat. Contoh: Tim Survei
     * @bodyParam perihal string required Perihal surat. Contoh: Penunjukan Tim Survei
     * @bodyParam kode_klas string Kode klasifikasi (opsional). Contoh: 100
     * @bodyParam no_sisip string Nomor sisip (opsional). Contoh: 1
     * @bodyParam kol_lampiran string Daftar lampiran (opsional). Contoh: Surat Tugas;Daftar Nama
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Nomor Surat 0001/SK/I/2024 berhasil disimpan",
     *   "data": {
     *     "id": 1,
     *     "thn": "2024",
     *     "bln": "I",
     *     "tanggal": "2024-01-15",
     *     "nomor": "0001",
     *     "no_sisip": null,
     *     "no_surat": "0001/SK/I/2024",
     *     "oleh": "Kepala BPS",
     *     "kegiatan": "Survei Sosial Ekonomi",
     *     "kepada": "Tim Survei",
     *     "perihal": "Penunjukan Tim Survei",
     *     "type": "SK",
     *     "kol_lampiran": "Surat Tugas;Daftar Nama",
     *     "create_by": 1,
     *     "created_at": "2024-01-15T00:00:00.000000Z",
     *     "updated_at": "2024-01-15T00:00:00.000000Z"
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation error",
     *   "errors": {
     *     "thn": ["Field thn wajib diisi."],
     *     "tanggal": ["Field tanggal wajib diisi."],
     *     "nomor": ["Field nomor wajib diisi."],
     *     "kepada": ["Field kepada wajib diisi."],
     *     "perihal": ["Field perihal wajib diisi."]
     *   }
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Failed to create surat keputusan",
     *   "errors": "Error message details"
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    /**
     * Menampilkan options untuk form (pejabat dan nomor baru).
     *
     * endpoint ini mengembalikan daftar pejabat dan nomor surat baru (max + 1)
     * untuk tahun saat ini, dipisahkan berdasarkan type (SK dan BAST).
     *
     * @group Nomor Surat Keputusan
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Form options retrieved successfully",
     *   "data": {
     *     "pejabat": ["KPA", "Kepala Kantor"],
     *     "nomor_baru_sk": 1,
     *     "nomor_baru_bast": 1
     *   }
     * }
     *
     * @return \Illuminate\Http\Response
     */
    public function formOptions()
    {
        $pejabat = ['KPA', 'Kepala Kantor'];
        $type    = ['SK', 'BAST'];
        $year    = Carbon::now()->format('Y');

        $maxNomorSk = SkBast::where('thn', $year)
            ->where('type', 'SK')
            ->max(DB::raw('CAST(nomor as UNSIGNED)'));
        $nomorBaruSk = $maxNomorSk ? $maxNomorSk + 1 : 1;
        $nomorBaruSk = str_pad($nomorBaruSk, 4, '0', STR_PAD_LEFT);

        $maxNomorBast = SkBast::where('thn', $year)
            ->where('type', 'BAST')
            ->max(DB::raw('CAST(nomor as UNSIGNED)'));
        $nomorBaruBast = $maxNomorBast ? $maxNomorBast + 1 : 1;
        $nomorBaruBast = str_pad($nomorBaruBast, 4, '0', STR_PAD_LEFT);

        return $this->success([
            'pejabat'         => $pejabat,
            'type'            => $type,
            'nomor_baru_sk'   => $nomorBaruSk,
            'nomor_baru_bast' => $nomorBaruBast,
        ], 'Form options retrieved successfully');
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        if (! $authUser) {
            return $this->error('Unauthorized', null, 401);
        }

        $validator = Validator::make($request->all(), [
            'thn'          => 'required',
            'tanggal'      => 'required|date',
            'nomor'        => 'required|string',
            'oleh'         => 'nullable|string',
            'kegiatan'     => 'nullable|string',
            'kepada'       => 'required|string',
            'perihal'      => 'required|string',
            'kode_klas'    => 'nullable|string',
            'no_sisip'     => 'nullable|string',
            'kol_lampiran' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', $validator->errors(), 422);
        }

        try {
            $data = $request->all();

            // Set type to SK for Surat Keputusan
            $data['type'] = 'SK';

            // Generate Roman numeral for month
            $data['bln'] = $this->convertToRoman(Carbon::parse($data['tanggal'])->format('m'));

            // Format nomor
            if (empty($data['nomor']) || $data['nomor'] == 0) {
                $data['nomor'] = null;
            }

            $formatSetting = DB::table('settings')
                ->where('key', 'FORMAT_SK')
                ->latest()
                ->first();

            if (! $formatSetting) {
                return $this->error('Format setting not found', 'FORMAT_SK setting is missing in the database', 500);
            }

            $format = $formatSetting->value;

            $data['nomor'] = str_pad($data['nomor'], 4, '0', STR_PAD_LEFT);

            // Generate nomor surat
            if (! empty($data['no_sisip'])) {
                $no = $data['nomor'] . '.' . $data['no_sisip'];
            } else {
                $no = $data['nomor'];
            }

            $data['no_surat'] = str_replace("{nomor}", $no, $format);
            $data['no_surat'] = str_replace("{bln}", $data['bln'], $data['no_surat']);
            $data['no_surat'] = str_replace("{tahun}", $data['thn'], $data['no_surat']);

            $data['create_by'] = Auth::id();

            $suratKeputusan = SkBast::create($data);

            return $this->success($suratKeputusan, 'Nomor Surat ' . $data['no_surat'] . ' berhasil disimpan', 201);

        } catch (\Exception $e) {
            return $this->error('Failed to create surat keputusan', $e->getMessage(), 500);
        }
    }

    /**
     * Menampilkan detail surat keputusan berdasarkan ID.
     *
     * Endpoint ini mengembalikan detail surat keputusan berdasarkan ID.
     * Hanya menampilkan surat dengan type 'SK' (Surat Keputusan).
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Nomor Surat Keputusan
     * @authenticated
     *
     * @urlParam id int required ID surat keputusan. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data retrieved successfully",
     *   "data": {
     *     "id": 1,
     *     "thn": "2024",
     *     "bln": "I",
     *     "tanggal": "2024-01-15",
     *     "nomor": "0001",
     *     "no_sisip": null,
     *     "no_surat": "0001/SK/I/2024",
     *     "oleh": "Kepala BPS",
     *     "kegiatan": "Survei Sosial Ekonomi",
     *     "kepada": "Tim Survei",
     *     "perihal": "Penunjukan Tim Survei",
     *     "type": "SK",
     *     "kol_lampiran": "Surat Tugas;Daftar Nama",
     *     "create_by": 1,
     *     "created_at": "2024-01-15T00:00:00.000000Z",
     *     "updated_at": "2024-01-15T00:00:00.000000Z"
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Surat keputusan not found"
     * }
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $suratKeputusan = SkBast::where('type', 'SK')->find($id);

            if (! $suratKeputusan) {
                return $this->error('Surat keputusan not found', null, 404);
            }

            return $this->success($suratKeputusan, 'Data retrieved successfully');

        } catch (\Exception $e) {
            return $this->error('Surat keputusan not found', $e->getMessage(), 404);
        }
    }

    /**
     * Mengupdate surat keputusan berdasarkan ID.
     *
     * Endpoint ini mengupdate surat keputusan yang sudah ada dengan regenerasi
     * nomor surat otomatis dan konversi bulan ke format Roman. Field tahun,
     * nomor, dan nomor_sisip tidak akan diupdate dan akan menggunakan nilai
     * yang sudah ada. Hanya pengguna yang sudah login yang dapat mengakses
     * endpoint ini.
     *
     * @group Nomor Surat Keputusan
     * @authenticated
     *
     * @urlParam id int required ID surat keputusan. Contoh: 1
     * @bodyParam tanggal date required Tanggal surat (format: YYYY-MM-DD). Contoh: 2024-01-15
     * @bodyParam oleh string Pembuat surat (opsional). Contoh: Kepala BPS
     * @bodyParam kegiatan string Nama kegiatan (opsional). Contoh: Survei Sosial Ekonomi
     * @bodyParam kepada string required Penerima surat. Contoh: Tim Survei
     * @bodyParam perihal string required Perihal surat. Contoh: Penunjukan Tim Survei
     * @bodyParam kode_klas string Kode klasifikasi (opsional). Contoh: 100
     * @bodyParam kol_lampiran string Daftar lampiran (opsional). Contoh: Surat Tugas;Daftar Nama
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Nomor Surat 0001/SK/I/2024 berhasil diupdate",
     *   "data": {
     *     "id": 1,
     *     "thn": "2024",
     *     "bln": "I",
     *     "tanggal": "2024-01-15",
     *     "nomor": "0001",
     *     "no_sisip": null,
     *     "no_surat": "0001/SK/I/2024",
     *     "oleh": "Kepala BPS",
     *     "kegiatan": "Survei Sosial Ekonomi",
     *     "kepada": "Tim Survei",
     *     "perihal": "Penunjukan Tim Survei",
     *     "type": "SK",
     *     "kol_lampiran": "Surat Tugas;Daftar Nama",
     *     "create_by": 1,
     *     "created_at": "2024-01-15T00:00:00.000000Z",
     *     "updated_at": "2024-01-15T00:00:00.000000Z"
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Surat keputusan not found"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation error",
     *   "errors": {
     *     "tanggal": ["The tanggal field is required."],
     *     "kepada": ["The kepada field is required."],
     *     "perihal": ["The perihal field is required."]
     *   }
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Failed to update surat keputusan",
     *   "errors": "Error message details"
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'tanggal'      => 'required|date',
            'oleh'         => 'nullable|string',
            'kegiatan'     => 'nullable|string',
            'kepada'       => 'required|string',
            'perihal'      => 'required|string',
            'kode_klas'    => 'nullable|string',
            'kol_lampiran' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', $validator->errors(), 422);
        }

        try {
            $suratKeputusan = SkBast::where('type', 'SK')->find($id);
            if (! $suratKeputusan) {
                return $this->error('Failed to update surat keputusan', null, 404);
            }

            // Get data from request, excluding tahun, nomor, and no_sisip
            $data = $request->only([
                'tanggal',
                'oleh',
                'kegiatan',
                'kepada',
                'perihal',
                'kode_klas',
                'kol_lampiran'
            ]);

            // Generate Roman numeral for month
            $data['bln'] = $this->convertToRoman(Carbon::parse($data['tanggal'])->format('m'));

            // Get existing values for nomor surat generation
            $existingNomor = $suratKeputusan->nomor;
            $existingThn = $suratKeputusan->thn;
            $existingNoSisip = $suratKeputusan->no_sisip;

            $formatSetting = DB::table('settings')
                ->where('key', 'FORMAT_SK')
                ->latest()
                ->first();

            if (! $formatSetting) {
                return $this->error('Format setting not found', 'FORMAT_SK setting is missing in the database', 500);
            }

            $format = $formatSetting->value;

            // Generate nomor surat using existing values
            if (! empty($existingNoSisip)) {
                $no = $existingNomor . '.' . $existingNoSisip;
            } else {
                $no = $existingNomor;
            }

            $data['no_surat'] = str_replace("{nomor}", $no, $format);
            $data['no_surat'] = str_replace("{bln}", $data['bln'], $data['no_surat']);
            $data['no_surat'] = str_replace("{tahun}", $existingThn, $data['no_surat']);

            $suratKeputusan->update($data);
            $suratKeputusan->refresh();

            return $this->success($suratKeputusan, 'Nomor Surat ' . $data['no_surat'] . ' berhasil diupdate');

        } catch (\Exception $e) {
            return $this->error('Failed to update surat keputusan', $e->getMessage(), 500);
        }
    }

    /**
     * Menghapus surat keputusan berdasarkan ID.
     *
     * Endpoint ini menghapus surat keputusan dari database berdasarkan ID.
     * Hanya menghapus surat dengan type 'SK' (Surat Keputusan).
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Nomor Surat Keputusan
     * @authenticated
     *
     * @urlParam id int required ID surat keputusan. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Nomor Surat berhasil dihapus",
     *   "data": null
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Surat keputusan not found"
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Failed to delete surat keputusan",
     *   "errors": "Error message details"
     * }
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $suratKeputusan = SkBast::where('type', 'SK')->find($id);

            if (! $suratKeputusan) {
                return $this->error('Surat keputusan not found', null, 404);
            }

            $suratKeputusan->delete();

            return $this->success(null, 'Nomor Surat berhasil dihapus');

        } catch (\Exception $e) {
            return $this->error('Failed to delete surat keputusan', $e->getMessage(), 500);
        }
    }

    /**
     * Membuat surat keputusan sisipan baru berdasarkan referensi.
     *
     * Endpoint ini membuat surat keputusan baru dengan menggunakan tahun dan nomor
     * dari surat keputusan referensi berdasarkan ID, sedangkan tanggal diambil
     * dari request body. Data lainnya diambil dari request body. Nomor sisip akan
     * di-generate otomatis berdasarkan nomor sisip maksimum yang ada untuk
     * kombinasi nomor dan tahun yang sama. Hanya pengguna yang sudah login
     * yang dapat mengakses endpoint ini.
     *
     * @group Nomor Surat Keputusan
     * @authenticated
     *
     * @bodyParam id integer required ID surat keputusan sebagai referensi untuk tahun dan nomor. Contoh: 1
     * @bodyParam tanggal date required Tanggal surat (format: YYYY-MM-DD). Contoh: 2024-01-15
     * @bodyParam oleh string Pembuat surat (opsional). Contoh: Kepala BPS
     * @bodyParam kegiatan string Nama kegiatan (opsional). Contoh: Survei Sosial Ekonomi
     * @bodyParam kepada string required Penerima surat. Contoh: Tim Survei
     * @bodyParam perihal string required Perihal surat. Contoh: Penunjukan Tim Survei
     * @bodyParam kode_klas string Kode klasifikasi (opsional). Contoh: 100
     * @bodyParam kol_lampiran string Daftar lampiran (opsional). Contoh: Surat Tugas;Daftar Nama
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Nomor Surat 0001.1/SK/I/2024 berhasil disimpan",
     *   "data": {
     *     "id": 2,
     *     "thn": "2024",
     *     "bln": "I",
     *     "tanggal": "2024-01-15",
     *     "nomor": "0001",
     *     "no_sisip": "1",
     *     "no_surat": "0001.1/SK/I/2024",
     *     "oleh": "Kepala BPS",
     *     "kegiatan": "Survei Sosial Ekonomi",
     *     "kepada": "Tim Survei",
     *     "perihal": "Penunjukan Tim Survei Sisipan",
     *     "type": "SK",
     *     "kol_lampiran": "Surat Tugas;Daftar Nama",
     *     "create_by": 1,
     *     "created_at": "2024-01-15T00:00:00.000000Z",
     *     "updated_at": "2024-01-15T00:00:00.000000Z"
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Surat keputusan not found"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation error",
     *   "errors": {
     *     "id": ["Field id wajib diisi."],
     *     "tanggal": ["Field tanggal wajib diisi."],
     *     "kepada": ["Field kepada wajib diisi."],
     *     "perihal": ["Field perihal wajib diisi."]
     *   }
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Failed to create surat keputusan sisipan",
     *   "errors": "Error message details"
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sisip(Request $request)
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        if (! $authUser) {
            return $this->error('Unauthorized', null, 401);
        }

        $validator = Validator::make($request->all(), [
            'id'           => 'required|integer|exists:surat_sk_bast,id',
            'tanggal'      => 'required|date',
            'type'         => 'required|string|in:SK,BAST',
            'oleh'         => 'nullable|string',
            'kegiatan'     => 'nullable|string',
            'kepada'       => 'required|string',
            'perihal'      => 'required|string',
            'kode_klas'    => 'nullable|string',
            'kol_lampiran' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', $validator->errors(), 422);
        }

        try {
            // Get the reference surat keputusan to get tahun and nomor
            $referenceSurat = SkBast::where('type', 'SK')->findOrFail($request->id);

            // Get data from reference
            $thn = $referenceSurat->thn;
            $nomor = $referenceSurat->nomor;

            // Generate nomor sisip otomatis
            // Cari nomor sisip maksimum untuk kombinasi nomor dan tahun yang sama
            $maxNomorSisip = SkBast::where('thn', $thn)
                ->where('type', 'SK')
                ->where('nomor', $nomor)
                ->max('no_sisip');

            // Tambahkan 1 ke nomor sisip maksimum, default ke 1 jika tidak ada
            $nomorSisip = $maxNomorSisip ? (int) $maxNomorSisip + 1 : 1;

            // Prepare data for new record
            $data = $request->all();
            $data['thn'] = $thn;
            $data['nomor'] = $nomor;
            $data['no_sisip'] = (string) $nomorSisip;

            // Generate Roman numeral for month
            $data['bln'] = $this->convertToRoman(Carbon::parse($data['tanggal'])->format('m'));

            // Format nomor
            $data['nomor'] = str_pad($data['nomor'], 4, '0', STR_PAD_LEFT);

            // Get format setting
            $formatSetting = DB::table('settings')
                ->where('key', 'FORMAT_SK')
                ->latest()
                ->first();

            if (! $formatSetting) {
                return $this->error('Format setting not found', 'FORMAT_SK setting is missing in the database', 500);
            }

            $format = $formatSetting->value;

            // Generate nomor surat
            $no = $data['nomor'] . '.' . $data['no_sisip'];
            $data['no_surat'] = str_replace("{nomor}", $no, $format);
            $data['no_surat'] = str_replace("{bln}", $data['bln'], $data['no_surat']);
            $data['no_surat'] = str_replace("{tahun}", $data['thn'], $data['no_surat']);

            $data['create_by'] = Auth::id();

            // Create new surat keputusan sisipan
            $suratKeputusanSisipan = SkBast::create($data);

            return $this->success($suratKeputusanSisipan, 'Nomor Surat ' . $data['no_surat'] . ' berhasil disimpan', 201);

        } catch (\Exception $e) {
            return $this->error('Failed to create surat keputusan sisipan', $e->getMessage(), 500);
        }
    }

    /**
     * Mendapatkan daftar tanggal yang tersedia untuk filter.
     *
     * Endpoint ini mengembalikan daftar tanggal unik yang tersedia
     * untuk filtering surat keputusan berdasarkan tahun tertentu.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Nomor Surat Keputusan
     * @authenticated
     *
     * @queryParam tahun string Filter berdasarkan tahun (default: tahun saat ini). Contoh: 2024
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Dates retrieved successfully",
     *   "data": [
     *     "2024-01-15",
     *     "2024-01-20",
     *     "2024-02-10"
     *   ]
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getDates(Request $request)
    {
        $tahun = $request->get('tahun', Carbon::now()->format('Y'));

        $dates = SkBast::where('thn', $tahun)
            ->where('type', 'SK')
            ->whereNotNull('tanggal')
            ->distinct()
            ->orderBy('tanggal')
            ->pluck('tanggal')
            ->map(function ($date) {
                return Carbon::parse($date)->format('Y-m-d');
            })
            ->toArray();

        return $this->success($dates, 'Dates retrieved successfully');
    }

    /**
     * Mendapatkan daftar tahun yang tersedia untuk filter.
     *
     * Endpoint ini mengembalikan daftar tahun unik yang tersedia
     * untuk filtering surat keputusan, diurutkan dari yang terbaru.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Nomor Surat Keputusan
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Years retrieved successfully",
     *   "data": [
     *     "2024",
     *     "2023",
     *     "2022"
     *   ]
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     *
     * @return \Illuminate\Http\Response
     */
    public function getYears()
    {
        $years = SkBast::where('type', 'SK')
            ->distinct()
            ->orderBy('thn', 'desc')
            ->pluck('thn')
            ->toArray();

        return $this->success($years, 'Years retrieved successfully');
    }

    /**
     * Helper function to convert number to Roman numeral
     */
    private function convertToRoman($number)
    {
        $mapping = [
            1000 => 'M',
            900  => 'CM',
            500  => 'D',
            400  => 'CD',
            100  => 'C',
            90   => 'XC',
            50   => 'L',
            40   => 'XL',
            10   => 'X',
            9    => 'IX',
            5    => 'V',
            4    => 'IV',
            1    => 'I',
        ];

        $result = '';
        foreach ($mapping as $value => $roman) {
            while ($number >= $value) {
                $result .= $roman;
                $number -= $value;
            }
        }

        return $result;
    }
}
