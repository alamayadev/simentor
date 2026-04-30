<?php
namespace App\Http\Controllers\Api\Kantor\NomorSurat;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\SuratPermintaan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PermintaanApiController extends BaseApiController
{
    /**
     * Menampilkan daftar surat permintaan dengan pagination dan filtering.
     *
     * Endpoint ini mengembalikan daftar surat permintaan dengan kemampuan filtering
     * berdasarkan tahun, tanggal, dan pencarian. Mendukung pagination dan sorting.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Nomor Surat Permintaan
     * @authenticated
     *
     * @queryParam per_page int Jumlah item per halaman (default: 10). Contoh: 10
     * @queryParam cursor string Cursor untuk pagination. Contoh: eyJpZCI6M...
     * @queryParam search string Pencarian berdasarkan 'dari' dan 'perihal'. Contoh: BPS
     * @queryParam tanggal date Filter berdasarkan tanggal (format: YYYY-MM-DD). Contoh: 2024-01-15
     * @queryParam tahun string Filter berdasarkan tahun (default: tahun saat ini). Contoh: 2024
     * @queryParam sort_by string Kolom untuk sorting (default: tanggal). Contoh: tanggal
     * @queryParam sort_dir string Arah sorting ASC/DESC (default: DESC). Contoh: DESC
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data retrieved successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "thn": "2024",
     *       "tanggal": "2024-01-15",
     *       "nomor": "0001",
     *       "no_sisip": null,
     *       "tanggal_indo": "15 Januari 2024",
     *       "kode_klas": "100",
     *       "no_surat": "0001/BPS-100/2024",
     *       "dari": "Kepala BPS Kota ABC",
     *       "perihal": "Permohonan Data Statistik",
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
     *     "path": "http://localhost:8000/api/kantor/nomor-surat/permintaan"
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
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        if (! $authUser) {
            if (! $authUser) {
                return $this->error('Unauthorized', null, 401);
            }
        }

        $perPage     = $request->get('per_page', 10);
        $search      = $request->get('search', '');
        $selectedTgl = $request->get('tanggal', '');
        $selectedThn = $request->get('tahun', Carbon::now()->format('Y'));
        $sortBy      = $request->get('sort_by', 'tanggal');
        $sortDir     = $request->get('sort_dir', 'DESC');

        // PERFORMANCE OPTIMIZATION: Use selective field selection for list views
        // Limit response to essential fields only to reduce data transfer and improve response times
        // Exclude large text fields (perihal, dari) from list views when not needed
        $baseQuery = SuratPermintaan::where('thn', $selectedThn)
            ->select([
                'id', 'thn', 'tanggal', 'nomor', 'no_sisip',
                'no_surat', 'tanggal_indo', 'kode_klas',
                'dari', 'perihal', 'created_by', 'created_at', 'updated_at',
            ]);

        // Filter by date if provided
        if ($selectedTgl !== '') {
            $baseQuery->whereDate('tanggal', $selectedTgl);
        }

        // Search functionality
        if ($search !== '') {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('dari', 'like', '%' . $search . '%')
                    ->orWhere('perihal', 'like', '%' . $search . '%');
            });
        }

        // Get total count for pagination info (separate query for performance)
        $totalRecords = $baseQuery->count();

        $data = $baseQuery->orderBy($sortBy, $sortDir)
            ->fastPaginate($perPage);

        // Add pagination extras including total_page
        $totalPages = $perPage > 0 ? ceil($totalRecords / $perPage) : 1;
        $extras     = [
            'pagination_info' => [
                'total_page'    => $totalPages,
                'total_records' => $totalRecords,
            ],
        ];

        return $this->success($data, 'Data retrieved successfully', 200, $extras);
    }

    /**
     * Membuat surat permintaan baru dengan nomor otomatis.
     *
     * Endpoint ini membuat surat permintaan baru dengan sistem penomoran otomatis.
     * Nomor akan diformat sesuai dengan template yang ada di settings.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Nomor Surat Permintaan
     * @authenticated
     *
     * @bodyParam thn string required Tahun surat. Contoh: 2024
     * @bodyParam tanggal date required Tanggal surat (format: YYYY-MM-DD). Contoh: 2024-01-15
     * @bodyParam nomor string required Nomor urut surat. Contoh: 1
     * @bodyParam kode_klas string required Kode klasifikasi surat. Contoh: 100
     * @bodyParam dari string required Pengirim surat. Contoh: Kepala BPS Kota ABC
     * @bodyParam perihal string required Perihal surat. Contoh: Permohonan Data Statistik
     * @bodyParam no_sisip string Nomor sisip (opsional). Contoh: 1
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Nomor Surat 0001/BPS-100/2024 berhasil disimpan",
     *   "data": {
     *     "id": 1,
     *     "thn": "2024",
     *     "tanggal": "2024-01-15",
     *     "nomor": "0001",
     *     "no_sisip": null,
     *     "tanggal_indo": "15 Januari 2024",
     *     "kode_klas": "100",
     *     "no_surat": "0001/BPS-100/2024",
     *     "dari": "Kepala BPS Kota ABC",
     *     "perihal": "Permohonan Data Statistik",
     *     "created_by": 1,
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
     *     "tanggal": ["Field tanggal wajib diisi."]
     *   }
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Failed to create surat permintaan",
     *   "errors": "Error message details"
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        if (! $authUser) {
            return $this->error('Unauthorized', null, 401);
        }

        $validator = Validator::make($request->all(), [
            'thn'       => 'required',
            'tanggal'   => 'required|date',
            'nomor'     => 'required|string',
            'kode_klas' => 'required|string',
            'dari'      => 'required|string',
            'perihal'   => 'required|string',
            'no_sisip'  => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', $validator->errors(), 422);
        }

        try {
            $data = $request->all();

            // Format nomor
            if (empty($data['nomor']) || $data['nomor'] == 0) {
                $data['nomor'] = null;
            }

            $formatSetting = DB::table('settings')
                ->where('key', 'FORMAT_FORM_PERMINTAAN')
                ->latest()
                ->first();

            if (! $formatSetting) {
                return $this->error('Format setting not found', 'FORMAT_FORM_PERMINTAAN setting is missing in the database', 500);
            }

            $format = $formatSetting->value;

            $data['nomor'] = str_pad($data['nomor'], 4, '0', STR_PAD_LEFT);

            // Generate nomor surat
            if (! empty($data['no_sisip'])) {
                $no               = $data['nomor'] . '.' . $data['no_sisip'];
                $data['no_surat'] = str_replace("{nomor}", $no, $format);
            } else {
                $data['no_surat'] = str_replace("{nomor}", $data['nomor'], $format);
            }

            $data['no_surat']     = str_replace("{tahun}", $data['thn'], $data['no_surat']);
            $data['no_surat']     = str_replace("{klas}", $data['kode_klas'], $data['no_surat']);
            $data['tanggal_indo'] = Carbon::parse($data['tanggal'])
                ->locale('id')
                ->settings(['formatFunction' => 'translatedFormat'])
                ->format('j F Y');
            $data['bln']        = Carbon::parse($data['tanggal'])->format('m');
            $data['created_by'] = Auth::id();

            $suratPermintaan = SuratPermintaan::create($data);

            return $this->success($suratPermintaan, 'Nomor Surat ' . $data['no_surat'] . ' berhasil disimpan', 201);

        } catch (\Exception $e) {
            return $this->error('Failed to create surat permintaan', $e->getMessage(), 500);
        }
    }

    /**
     * Menampilkan detail surat permintaan berdasarkan ID.
     *
     * Endpoint ini mengembalikan detail lengkap surat permintaan berdasarkan ID yang diberikan.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Nomor Surat Permintaan
     * @authenticated
     *
     * @urlParam id int required ID surat permintaan. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data retrieved successfully",
     *   "data": {
     *     "id": 1,
     *     "thn": "2024",
     *     "tanggal": "2024-01-15",
     *     "nomor": "0001",
     *     "no_sisip": null,
     *     "tanggal_indo": "15 Januari 2024",
     *     "kode_klas": "100",
     *     "no_surat": "0001/BPS-100/2024",
     *     "dari": "Kepala BPS Kota ABC",
     *     "perihal": "Permohonan Data Statistik",
     *     "created_by": 1,
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
     *   "message": "Surat permintaan not found",
     *   "errors": "No query results for model [App\\Models\\SuratPermintaan] 999"
     * }
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $suratPermintaan = SuratPermintaan::find($id);

            if (! $suratPermintaan) {
                return $this->error('Surat permintaan not found', null, 404);
            }

            return $this->success($suratPermintaan, 'Data retrieved successfully');

        } catch (\Exception $e) {
            return $this->error('Surat permintaan not found', $e->getMessage(), 404);
        }
    }

    /**
     * Memperbarui data surat permintaan berdasarkan ID.
     *
     * Endpoint ini memperbarui data surat permintaan dengan ID yang diberikan.
     * Nomor surat akan di-generate ulang sesuai dengan data yang diubah.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Nomor Surat Permintaan
     * @authenticated
     *
     * @urlParam id int required ID surat permintaan. Contoh: 1
     *
     * @bodyParam thn string required Tahun surat. Contoh: 2024
     * @bodyParam tanggal date required Tanggal surat (format: YYYY-MM-DD). Contoh: 2024-01-15
     * @bodyParam nomor string required Nomor urut surat. Contoh: 1
     * @bodyParam kode_klas string required Kode klasifikasi surat. Contoh: 100
     * @bodyParam dari string required Pengirim surat. Contoh: Kepala BPS Kota ABC
     * @bodyParam perihal string required Perihal surat. Contoh: Permohonan Data Statistik
     * @bodyParam no_sisip string Nomor sisip (opsional). Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Nomor Surat 0001/BPS-100/2024 berhasil diubah",
     *   "data": {
     *     "id": 1,
     *     "thn": "2024",
     *     "tanggal": "2024-01-15",
     *     "nomor": "0001",
     *     "no_sisip": null,
     *     "tanggal_indo": "15 Januari 2024",
     *     "kode_klas": "100",
     *     "no_surat": "0001/BPS-100/2024",
     *     "dari": "Kepala BPS Kota ABC Updated",
     *     "perihal": "Permohonan Data Statistik Updated",
     *     "created_by": 1,
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
     *   "message": "Failed to update surat permintaan",
     *   "errors": "No query results for model [App\\Models\\SuratPermintaan] 999"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation error",
     *   "errors": {
     *     "thn": ["The thn field is required."],
     *     "tanggal": ["The tanggal field is required."]
     *   }
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Failed to update surat permintaan",
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
            'tanggal'   => 'required|date',
            'kode_klas' => 'required|string',
            'dari'      => 'required|string',
            'perihal'   => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', $validator->errors(), 422);
        }

        try {
            $suratPermintaan = SuratPermintaan::find($id);
            if (! $suratPermintaan) {
                return $this->error('Failed to update surat permintaan', 'Surat permintaan not found', 404);
            }

            // Exclude immutable fields
            $data = $request->except(['thn', 'nomor', 'no_sisip']);

            // Get existing immutable values
            $thn     = $suratPermintaan->thn;
            $nomor   = $suratPermintaan->nomor;
            $noSisip = $suratPermintaan->no_sisip;

            $formatSetting = DB::table('settings')
                ->where('key', 'FORMAT_FORM_PERMINTAAN')
                ->latest()
                ->first();

            if (! $formatSetting) {
                return $this->error('Format setting not found', 'FORMAT_FORM_PERMINTAAN setting is missing in the database', 500);
            }

            $format = $formatSetting->value;

            // Ensure nomor is padded (just in case, though it should be from DB)
            $nomor = str_pad($nomor, 4, '0', STR_PAD_LEFT);

            // Generate nomor surat using existing numbers and potentially new kode_klas
            if (! empty($noSisip)) {
                $no               = $nomor . '.' . $noSisip;
                $data['no_surat'] = str_replace("{nomor}", $no, $format);
            } else {
                $data['no_surat'] = str_replace("{nomor}", $nomor, $format);
            }

            $data['no_surat']     = str_replace("{tahun}", $thn, $data['no_surat']);
            $data['no_surat']     = str_replace("{klas}", $request->kode_klas, $data['no_surat']); // Use new kode_klas
            $data['tanggal_indo'] = Carbon::parse($data['tanggal'])
                ->locale('id')
                ->settings(['formatFunction' => 'translatedFormat'])
                ->format('j F Y');
            $data['bln'] = Carbon::parse($data['tanggal'])->format('m');

            $suratPermintaan->update($data);

            return $this->success($suratPermintaan->fresh(), 'Nomor Surat ' . $data['no_surat'] . ' berhasil diubah');

        } catch (\Exception $e) {
            return $this->error('Failed to update surat permintaan', $e->getMessage(), 500);
        }
    }

    /**
     * Menghapus surat permintaan berdasarkan ID.
     *
     * Endpoint ini menghapus surat permintaan dengan ID yang diberikan secara permanen.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Nomor Surat Permintaan
     * @authenticated
     *
     * @urlParam id int required ID surat permintaan yang akan dihapus. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Nomor Surat 0001/BPS-100/2024 berhasil dihapus",
     *   "data": null
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Failed to delete surat permintaan",
     *   "errors": "No query results for model [App\\Models\\SuratPermintaan] 999"
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Failed to delete surat permintaan",
     *   "errors": "Error message details"
     * }
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $suratPermintaan = SuratPermintaan::find($id);

            if (! $suratPermintaan) {
                return $this->error('Failed to delete surat permintaan', 'Surat permintaan not found', 404);
            }

            $noSurat = $suratPermintaan->no_surat;

            $suratPermintaan->delete();

            return $this->success(null, 'Nomor Surat ' . $noSurat . ' berhasil dihapus');

        } catch (\Exception $e) {
            return $this->error('Failed to delete surat permintaan', $e->getMessage(), 500);
        }
    }

    /**
     * Membuat surat permintaan sisip (insert) dengan nomor di antara nomor yang sudah ada.
     *
     * Endpoint ini membuat surat permintaan dengan nomor sisip yang disisipkan di antara
     * nomor yang sudah ada. Nomor sisip akan ditambahkan setelah nomor utama (contoh: 0001.1).
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Nomor Surat Permintaan
     * @authenticated
     *
     * @bodyParam thn string required Tahun surat. Contoh: 2024
     * @bodyParam tanggal date required Tanggal surat (format: YYYY-MM-DD). Contoh: 2024-01-15
     * @bodyParam nomor string required Nomor urut surat. Contoh: 1
     * @bodyParam no_sisip string required Nomor sisip yang akan disisipkan. Contoh: 1
     * @bodyParam kode_klas string required Kode klasifikasi surat. Contoh: 100
     * @bodyParam dari string required Pengirim surat. Contoh: Kepala BPS Kota ABC
     * @bodyParam perihal string required Perihal surat. Contoh: Permohonan Data Statistik Sisip
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Nomor Surat 0001.1/BPS-100/2024 berhasil disisipkan",
     *   "data": {
     *     "id": 2,
     *     "thn": "2024",
     *     "tanggal": "2024-01-15",
     *     "nomor": "0001",
     *     "no_sisip": "1",
     *     "tanggal_indo": "15 Januari 2024",
     *     "kode_klas": "100",
     *     "no_surat": "0001.1/BPS-100/2024",
     *     "dari": "Kepala BPS Kota ABC",
     *     "perihal": "Permohonan Data Statistik Sisip",
     *     "created_by": 1,
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
     *     "no_sisip": ["The no sisip field is required."]
     *   }
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Failed to create sisip surat permintaan",
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
            'id'        => 'required|exists:surat_permintaan,id',
            'tanggal'   => 'required|date',
            'kode_klas' => 'required|string',
            'dari'      => 'required|string',
            'perihal'   => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', $validator->errors(), 422);
        }

        try {
            // Get reference surat
            $referenceSurat = SuratPermintaan::findOrFail($request->id);

            $thn   = $referenceSurat->thn;
            $nomor = $referenceSurat->nomor;

            // Generate nomor sisip otomatis
            $maxNomorSisip = SuratPermintaan::where('thn', $thn)
                ->where('nomor', $nomor)
                ->max('no_sisip');

            $nomorSisip = $maxNomorSisip ? (int) $maxNomorSisip + 1 : 1;

            $data             = $request->except(['id']); // Exclude ID to prevent primary key collision
            $data['thn']      = $thn;
            $data['nomor']    = $nomor;
            $data['no_sisip'] = (string) $nomorSisip;

            // Format nomor
            $data['nomor'] = str_pad($data['nomor'], 4, '0', STR_PAD_LEFT);

            $formatSetting = DB::table('settings')
                ->where('key', 'FORMAT_FORM_PERMINTAAN')
                ->latest()
                ->first();

            if (! $formatSetting) {
                return $this->error('Format setting not found', 'FORMAT_FORM_PERMINTAAN setting is missing in the database', 500);
            }

            $format = $formatSetting->value;

            // Generate nomor surat with sisip
            $no                   = $data['nomor'] . '.' . $data['no_sisip'];
            $data['no_surat']     = str_replace("{nomor}", $no, $format);
            $data['no_surat']     = str_replace("{tahun}", $data['thn'], $data['no_surat']);
            $data['no_surat']     = str_replace("{klas}", $data['kode_klas'], $data['no_surat']);
            $data['tanggal_indo'] = Carbon::parse($data['tanggal'])
                ->locale('id')
                ->settings(['formatFunction' => 'translatedFormat'])
                ->format('j F Y');
            $data['bln']        = Carbon::parse($data['tanggal'])->format('m');
            $data['created_by'] = Auth::id();

            $suratPermintaan = SuratPermintaan::create($data);

            return $this->success($suratPermintaan, 'Nomor Surat ' . $data['no_surat'] . ' berhasil disisipkan', 201);

        } catch (\Exception $e) {
            return $this->error('Failed to create sisip surat permintaan', $e->getMessage(), 500);
        }
    }

    /**
     * Menampilkan daftar tanggal yang tersedia untuk filtering.
     *
     * Endpoint ini mengembalikan daftar tanggal yang tersedia dalam database
     * untuk filtering data surat permintaan berdasarkan tahun.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Nomor Surat Permintaan
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
     *     "2024-02-01"
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
        $year = $request->get('tahun', Carbon::now()->format('Y'));

        $dates = SuratPermintaan::where('thn', $year)
            ->where('tanggal', '!=', null)
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'DESC')
            ->pluck('tanggal');

        return $this->success($dates, 'Dates retrieved successfully');
    }

    /**
     * Menampilkan daftar tahun yang tersedia untuk filtering.
     *
     * Endpoint ini mengembalikan daftar tahun yang tersedia dalam database
     * untuk filtering data surat permintaan.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Nomor Surat Permintaan
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Years retrieved successfully",
     *   "data": [
     *     "2022",
     *     "2023",
     *     "2024"
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
        $years = SuratPermintaan::groupBy('thn')
            ->pluck('thn')
            ->toArray();

        return $this->success($years, 'Years retrieved successfully');
    }

    /**
     * Menampilkan options untuk form (klasifikasi dan nomor baru).
     *
     * endpoint ini mengembalikan data klasifikasi dan nomor surat baru (max + 1)
     * untuk tahun saat ini.
     *
     * @group Nomor Surat Permintaan
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Form options retrieved successfully",
     *   "data": {
     *     "klasifikasi": [
     *       { "kode": "100", "keterangan": "Organisasi" }
     *     ],
     *     "nomor_baru": 1
     *   }
     * }
     *
     * @return \Illuminate\Http\Response
     */
    public function formOptions()
    {
        $klasifikasi = DB::table('klasifikasi_surat')
            ->where('parent_id', 32)
            ->select('kode', 'keterangan')
            ->get();

        $year     = Carbon::now()->format('Y');
        $maxNomor = SuratPermintaan::where('thn', $year)
            ->max(DB::raw('CAST(nomor as UNSIGNED)'));

        $nomorBaru = $maxNomor ? $maxNomor + 1 : 1;
        $nomorBaru = str_pad($nomorBaru, 4, '0', STR_PAD_LEFT);

        return $this->success([
            'klasifikasi' => $klasifikasi,
            'nomor_baru'  => $nomorBaru,
        ], 'Form options retrieved successfully');
    }

    /**
     * Menampilkan daftar klasifikasi surat untuk dropdown.
     *
     * Endpoint ini mengembalikan daftar klasifikasi surat yang tersedia
     * untuk digunakan pada dropdown pilihan klasifikasi surat.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Nomor Surat Permintaan
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Klasifikasi retrieved successfully",
     *   "data": [
     *     {
     *       "kode": "100",
     *       "keterangan": "Organisasi"
     *     },
     *     {
     *       "kode": "200",
     *       "keterangan": "Tata Usaha"
     *     },
     *     {
     *       "kode": "300",
     *       "keterangan": "Kepegawaian"
     *     }
     *   ]
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     *
     * @return \Illuminate\Http\Response
     */
    public function getKlasifikasi()
    {
        $klasifikasi = DB::table('klasifikasi_surat')
            ->where('parent_id', 32)
            ->select('kode', 'keterangan')
            ->get();

        return $this->success($klasifikasi, 'Klasifikasi retrieved successfully');
    }

    /**
     * Bulk update status for multiple surat permintaan.
     *
     * Endpoint ini memungkinkan pembaruan status massal untuk beberapa surat permintaan sekaligus.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Nomor Surat Permintaan
     * @authenticated
     *
     * @bodyParam ids array required Array of surat permintaan IDs to update. Contoh: [1, 2, 3]
     * @bodyParam status string required New status for the surat permintaan. Contoh: approved
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Status updated successfully",
     *   "data": {
     *     "updated_count": 3
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation error",
     *   "errors": {}
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function bulkUpdateStatus(Request $request)
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        if (! $authUser) {
            return $this->error('Unauthorized', null, 401);
        }

        $validator = Validator::make($request->all(), [
            'ids'    => 'required|array',
            'ids.*'  => 'integer|exists:surat_permintaan,id',
            'status' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', $validator->errors(), 422);
        }

        try {
            $ids    = $request->input('ids');
            $status = $request->input('status');

            $updatedCount = SuratPermintaan::whereIn('id', $ids)->update(['status' => $status]);

            return $this->success(['updated_count' => $updatedCount], 'Status updated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to update status', $e->getMessage(), 500);
        }
    }
}
