<?php
namespace App\Http\Controllers\Api\Kantor\NomorSurat;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\SuratTugas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SuratTugasApiController extends BaseApiController
{
    /**
     * Menampilkan daftar surat tugas dengan pagination dan filtering.
     *
     * @group Nomor Surat Tugas
     * @authenticated
     *
     * @queryParam per_page int Jumlah item per halaman (default: 10). Contoh: 10
     * @queryParam cursor string Cursor untuk pagination. Contoh: eyJpZCI6M...
     * @queryParam search string Pencarian berdasarkan 'kepada', 'uraian', dan 'no_surat'. Contoh: Tim
     * @queryParam filter[tanggal] date Filter berdasarkan tanggal (format: YYYY-MM-DD). Contoh: 2024-01-15
     * @queryParam filter[tahun] string Filter berdasarkan tahun (default: tahun saat ini). Contoh: 2024
     * @queryParam sort string Sort field. Default: -tahun. Contoh: -nomor
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data berhasil diambil",
     *   "data": [
     *     {
     *       "id": 1,
     *       "tahun": "2024",
     *       "tanggal": "2024-01-15",
     *       "nomor": "0001",
     *       "no_sisip": null,
     *       "no_mix": "0001",
     *       "no_surat": "0001/ST-100/2024",
     *       "tanggal_indo": "15 Januari 2024",
     *       "kode_klas": "100",
     *       "kepada": "Tim Survei",
     *       "menimbang": "Dalam rangka pelaksanaan kegiatan",
     *       "uraian": "Melaksanakan survei lapangan",
     *       "file": null,
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
     *     "path": "http://localhost:8000/api/kantor/nomor-surat/surat-tugas"
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
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        // Default tahun if not provided in filter
        if (! $request->has('filter.tahun')) {
            $request->merge(['filter' => array_merge($request->get('filter', []), ['tahun' => Carbon::now()->format('Y')])]);
        }

        // Build the same query as the main data query to get accurate count
        $countQuery = \Spatie\QueryBuilder\QueryBuilder::for(SuratTugas::class)
            ->allowedFilters([
                'tahun',
                \Spatie\QueryBuilder\AllowedFilter::exact('tanggal'),
                \Spatie\QueryBuilder\AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($q) use ($value) {
                        $q->where('kepada', 'like', '%' . $value . '%')
                            ->orWhere('uraian', 'like', '%' . $value . '%')
                            ->orWhere('no_surat', 'like', '%' . $value . '%');
                    });
                }),
            ]);

        // Get total count for pagination info (separate query for performance)
        $totalRecords = $countQuery->count();

        // Use selective field selection for list views while keeping fields needed by
        // the table's expandable detail row.
        $data = \Spatie\QueryBuilder\QueryBuilder::for(SuratTugas::class)
            ->select([
                'id', 'tahun', 'tanggal', 'nomor', 'no_sisip', 'no_mix',
                'no_surat', 'tanggal_indo', 'kode_klas', 'kepada',
                'menimbang', 'uraian',
                'created_by', 'created_at', 'updated_at'
            ])
            ->allowedFilters([
                'tahun',
                \Spatie\QueryBuilder\AllowedFilter::exact('tanggal'),
                \Spatie\QueryBuilder\AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($q) use ($value) {
                        $q->where('kepada', 'like', '%' . $value . '%')
                            ->orWhere('uraian', 'like', '%' . $value . '%')
                            ->orWhere('no_surat', 'like', '%' . $value . '%');
                    });
                }),
            ])
            ->defaultSort('-nomor', '-tanggal')
            ->allowedSorts(['tahun', 'nomor', 'tanggal', 'created_at', 'id'])
            ->fastPaginate($perPage);

        // Add pagination extras including total_page
        $totalPages = $perPage > 0 ? ceil($totalRecords / $perPage) : 1;
        $extras     = [
            'pagination_info' => [
                'total_page'    => $totalPages,
                'total_records' => $totalRecords,
            ],
        ];

        return $this->success($data, 'Data berhasil diambil', 200, $extras);
    }

    /**
     * Membuat surat tugas baru dengan nomor otomatis.
     *
     * @group Nomor Surat Tugas
     * @authenticated
     *
     * @bodyParam tahun string required Tahun surat. Contoh: 2024
     * @bodyParam tanggal date required Tanggal surat (format: YYYY-MM-DD). Contoh: 2024-01-15
     * @bodyParam kode_klas string required Kode klasifikasi. Contoh: 100
     * @bodyParam kepada string required Penerima surat. Contoh: Tim Survei
     * @bodyParam menimbang string Dasar pertimbangan (opsional). Contoh: Dalam rangka pelaksanaan kegiatan
     * @bodyParam uraian string required Uraian tugas. Contoh: Melaksanakan survei lapangan
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Nomor Surat 0001/ST-100/2024 berhasil disimpan",
     *   "data": {
     *     "id": 1,
     *     "tahun": "2024",
     *     "tanggal": "2024-01-15",
     *     "nomor": "0001",
     *     "no_sisip": null,
     *     "no_mix": "0001",
     *     "no_surat": "0001/ST-100/2024",
     *     "tanggal_indo": "15 Januari 2024",
     *     "kode_klas": "100",
     *     "kepada": "Tim Survei",
     *     "menimbang": "Dalam rangka pelaksanaan kegiatan",
     *     "uraian": "Melaksanakan survei lapangan",
     *     "file": null,
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
     *     "tahun": ["Field tahun wajib diisi."],
     *     "tanggal": ["Field tanggal wajib diisi."],
     *     "kode_klas": ["Field kode_klas wajib diisi."],
     *     "kepada": ["Field kepada wajib diisi."],
     *     "uraian": ["Field uraian wajib diisi."]
     *   }
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Failed to create surat tugas",
     *   "errors": "Error message details"
     * }
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        if (! $authUser) {
            return $this->error('Unauthorized', null, 401);
        }

        $validator = Validator::make($request->all(), [
            'tahun'     => 'required',
            'tanggal'   => 'required|date',
            'kode_klas' => 'required|string',
            'kepada'    => 'required|string',
            'menimbang' => 'nullable|string',
            'uraian'    => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', $validator->errors(), 422);
        }

        try {
            $data = $request->all();

            // Auto-generate nomor based on max existing nomor in current year +1
            $maxNomor = SuratTugas::where('tahun', $data['tahun'])
                ->max(DB::raw('CAST(nomor AS UNSIGNED)'));

            $data['nomor'] = $maxNomor ? $maxNomor + 1 : 1;

            $formatSetting = DB::table('settings')
                ->where('key', 'FORMAT_SURTUG')
                ->latest()
                ->first();

            if (! $formatSetting) {
                return $this->error('Format setting not found', 'FORMAT_SURTUG setting is missing in the database', 500);
            }

            $format = $formatSetting->value;

            $data['nomor'] = str_pad($data['nomor'], 4, '0', STR_PAD_LEFT);

            // For store, no_sisip remains null (do not auto-generate here)
            $data['no_sisip'] = null;

            if (! empty($data['no_sisip'])) {
                $no             = $data['nomor'] . '.' . $data['no_sisip'];
                $data['no_mix'] = $no;
            } else {
                $no             = $data['nomor'];
                $data['no_mix'] = $no;
            }

            $data['no_surat'] = str_replace("{nomor}", $no, $format);
            $data['no_surat'] = str_replace("{tahun}", $data['tahun'], $data['no_surat']);
            $data['no_surat'] = str_replace("{klasifikasi}", $data['kode_klas'], $data['no_surat']);

            $data['tanggal_indo'] = Carbon::parse($data['tanggal'])
                ->locale('id')
                ->settings(['formatFunction' => 'translatedFormat'])
                ->format('j F Y');

            $data['created_by'] = Auth::id();

            $suratTugas = SuratTugas::create($data);

            return $this->success($suratTugas, 'Nomor Surat ' . $data['no_surat'] . ' berhasil disimpan', 201);

        } catch (\Exception $e) {
            return $this->error('Failed to create surat tugas', $e->getMessage(), 500);
        }
    }

    /**
     * Menampilkan detail surat tugas berdasarkan ID.
     *
     * Endpoint ini mengembalikan detail lengkap dari surat tugas tertentu
     * beserta detail surat tugas (SurtugDetil) yang terkait.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Nomor Surat Tugas
     * @authenticated
     *
     * @urlParam id integer required ID surat tugas. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data retrieved successfully",
     *   "data": {
     *     "id": 1,
     *     "tahun": "2024",
     *     "tanggal": "2024-01-15",
     *     "nomor": "0001",
     *     "no_sisip": null,
     *     "no_mix": "0001",
     *     "no_surat": "0001/ST-100/2024",
     *     "tanggal_indo": "15 Januari 2024",
     *     "kode_klas": "100",
     *     "kepada": "Tim Survei",
     *     "menimbang": "Dalam rangka pelaksanaan kegiatan",
     *     "uraian": "Melaksanakan survei lapangan",
     *     "file": null,
     *     "created_by": 1,
     *     "created_at": "2024-01-15T00:00:00.000000Z",
     *     "updated_at": "2024-01-15T00:00:00.000000Z",
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Surat tugas not found"
     * }
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $suratTugas = SuratTugas::find($id);

            if (! $suratTugas) {
                return $this->error('Surat tugas not found', null, 404);
            }

            return $this->success($suratTugas, 'Data retrieved successfully');

        } catch (\Exception $e) {
            return $this->error('Surat tugas not found', $e->getMessage(), 404);
        }
    }

    /**
     * Mengupdate surat tugas berdasarkan ID.
     *
     * Endpoint ini mengupdate data surat tugas yang sudah ada.
     * Nomor surat akan diformat ulang sesuai dengan data yang diupdate.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Nomor Surat Tugas
     * @authenticated
     *
     * @urlParam id integer required ID surat tugas yang akan diupdate. Contoh: 1
     *
     * @bodyParam tahun string required Tahun surat. Contoh: 2024
     * @bodyParam tanggal date required Tanggal surat (format: YYYY-MM-DD). Contoh: 2024-01-15
     * @bodyParam nomor string required Nomor urut surat. Contoh: 1
     * @bodyParam kode_klas string required Kode klasifikasi. Contoh: 100
     * @bodyParam kepada string required Penerima surat. Contoh: Tim Survei
     * @bodyParam menimbang string Dasar pertimbangan (opsional). Contoh: Dalam rangka pelaksanaan kegiatan
     * @bodyParam uraian string required Uraian tugas. Contoh: Melaksanakan survei lapangan
     * @bodyParam no_sisip string Nomor sisip (opsional). Contoh: 1
     * @bodyParam file file Lampiran (hanya PDF, opsional).
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Nomor Surat 0001/ST-100/2024 berhasil diupdate",
     *   "data": {
     *     "id": 1,
     *     "tahun": "2024",
     *     "tanggal": "2024-01-15",
     *     "nomor": "0001",
     *     "no_sisip": null,
     *     "no_mix": "0001",
     *     "no_surat": "0001/ST-100/2024",
     *     "tanggal_indo": "15 Januari 2024",
     *     "kode_klas": "100",
     *     "kepada": "Tim Survei",
     *     "menimbang": "Dalam rangka pelaksanaan kegiatan",
     *     "uraian": "Melaksanakan survei lapangan",
     *     "file": null,
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
     *   "message": "Surat tugas not found"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation error",
     *   "errors": {
     *     "tahun": ["The tahun field is required."],
     *     "tanggal": ["The tanggal field is required."]
     *   }
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Failed to update surat tugas",
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
            'tahun'     => 'required',
            'tanggal'   => 'required|date',
            'nomor'     => 'required|string',
            'kode_klas' => 'required|string',
            'kepada'    => 'required|string',
            'menimbang' => 'nullable|string',
            'uraian'    => 'required|string',
            'no_sisip'  => 'nullable|string',
            'file'      => 'nullable|file|mimes:pdf|max:2048', // 2MB in kilobytes
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', $validator->errors(), 422);
        }

        try {
            $suratTugas = SuratTugas::find($id);
            if (! $suratTugas) {
                return $this->error('Surat tugas not found', null, 404);
            }
            $data = $request->all();

            if (empty($data['nomor']) || $data['nomor'] == 0) {
                $data['nomor'] = null;
            }

            $formatSetting = DB::table('settings')
                ->where('key', 'FORMAT_SURTUG')
                ->latest()
                ->first();

            if (! $formatSetting) {
                return $this->error('Format setting not found', 'FORMAT_SURTUG setting is missing in the database', 500);
            }

            $format = $formatSetting->value;

            $data['nomor'] = str_pad($data['nomor'], 4, '0', STR_PAD_LEFT);

            if (! empty($data['no_sisip'])) {
                $no             = $data['nomor'] . '.' . $data['no_sisip'];
                $data['no_mix'] = $no;
            } else {
                $no             = $data['nomor'];
                $data['no_mix'] = $no;
            }

            $data['no_surat'] = str_replace("{nomor}", $no, $format);
            $data['no_surat'] = str_replace("{tahun}", $data['tahun'], $data['no_surat']);
            $data['no_surat'] = str_replace("{klasifikasi}", $data['kode_klas'], $data['no_surat']);

            $data['tanggal_indo'] = Carbon::parse($data['tanggal'])
                ->locale('id')
                ->settings(['formatFunction' => 'translatedFormat'])
                ->format('j F Y');

            // Handle file upload: if new file provided, delete old and save new on configured disk
            if ($request->hasFile('file')) {
                if (! empty($suratTugas->file)) {
                    Storage::disk('direct')->delete($suratTugas->file);
                }
                $uploaded     = $request->file('file');
                $storedPath   = Storage::disk('direct')->putFile('surat-tugas', $uploaded);
                $data['file'] = $storedPath; // relative to disk root
            } else {
                $data['file'] = $suratTugas->file;
            }

            $suratTugas->update($data);
            $suratTugas->refresh();

            return $this->success($suratTugas, 'Nomor Surat ' . $data['no_surat'] . ' berhasil diupdate');

        } catch (\Exception $e) {
            return $this->error('Failed to update surat tugas', $e->getMessage(), 500);
        }
    }

    /**
     * Menghapus surat tugas berdasarkan ID.
     *
     * Endpoint ini menghapus data surat tugas yang dipilih dari database.
     * Jika ada file attachment yang terkait, file tersebut juga akan dihapus dari disk.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Nomor Surat Tugas
     * @authenticated
     *
     * @urlParam id integer required ID surat tugas yang akan dihapus. Contoh: 1
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
     *   "message": "Surat tugas not found"
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Failed to delete surat tugas",
     *   "errors": "Error message details"
     * }
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $suratTugas = SuratTugas::find($id);

            if (! $suratTugas) {
                return $this->error('Surat tugas not found', null, 404);
            }

            $suratTugas->delete();
            if (! empty($suratTugas->file)) {
                Storage::disk('direct')->delete($suratTugas->file);
            }

            return $this->success(null, 'Nomor Surat berhasil dihapus');

        } catch (\Exception $e) {
            return $this->error('Failed to delete surat tugas', $e->getMessage(), 500);
        }
    }

    /**
     * Menyimpan surat tugas (varian insert) dengan nomor sisip otomatis.
     *
     * @group Nomor Surat Tugas
     * @authenticated
     *
     * @bodyParam tahun string required Tahun surat. Contoh: 2024
     * @bodyParam tanggal date required Tanggal surat (format: YYYY-MM-DD). Contoh: 2024-01-15
     * @bodyParam nomor string required Nomor urut surat. Contoh: 1
     * @bodyParam kode_klas string required Kode klasifikasi. Contoh: 100
     * @bodyParam kepada string required Penerima surat. Contoh: Tim Survei
     * @bodyParam menimbang string Dasar pertimbangan (opsional). Contoh: Dalam rangka pelaksanaan kegiatan
     * @bodyParam uraian string required Uraian tugas. Contoh: Melaksanakan survei lapangan
     * @bodyParam no_sisip integer optional Nomor sisip (auto-generated jika tidak diisi). Contoh: 1
     * @bodyParam file string File attachment (opsional). Contoh: document.pdf
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Nomor Surat 0001/ST-100/2024 berhasil disisipkan",
     *   "data": {
     *     "id": 1,
     *     "tahun": "2024",
     *     "tanggal": "2024-01-15",
     *     "nomor": "0001",
     *     "no_sisip": "1",
     *     "no_mix": "0001.1",
     *     "no_surat": "0001.1/ST-100/2024",
     *     "tanggal_indo": "15 Januari 2024",
     *     "kode_klas": "100",
     *     "kepada": "Tim Survei",
     *     "menimbang": "Dalam rangka pelaksanaan kegiatan",
     *     "uraian": "Melaksanakan survei lapangan",
     *     "file": null,
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
     *     "tahun": ["The tahun field is required."]
     *   }
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Failed to insert nomor surat",
     *   "errors": "Error message details"
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function insert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tahun'     => 'required',
            'tanggal'   => 'required|date',
            'nomor'     => 'required|string',
            'kode_klas' => 'required|string',
            'kepada'    => 'required|string',
            'menimbang' => 'nullable|string',
            'uraian'    => 'required|string',
            'file'      => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', $validator->errors(), 422);
        }

        try {
            $data = $request->all();

            if (empty($data['nomor']) || $data['nomor'] == 0) {
                $data['nomor'] = null;
            }

            $formatSetting = DB::table('settings')
                ->where('key', 'FORMAT_SURTUG')
                ->latest()
                ->first();

            if (! $formatSetting) {
                return $this->error('Format setting not found', 'FORMAT_SURTUG setting is missing in the database', 500);
            }

            $format = $formatSetting->value;

            // Pad nomor with zeros before using it for queries
            $paddedNomor = str_pad($data['nomor'], 4, '0', STR_PAD_LEFT);

            // Auto-generate no_sisip based on max existing no_sisip for given nomor +1
            if (! isset($data['no_sisip']) || $data['no_sisip'] === '') {
                $maxNoSisip = SuratTugas::where('nomor', $paddedNomor)
                    ->max(DB::raw('CAST(no_sisip AS UNSIGNED)'));
                $data['no_sisip'] = $maxNoSisip ? $maxNoSisip + 1 : 1;
            }

            $data['nomor'] = $paddedNomor;

            if (! empty($data['no_sisip'])) {
                $no             = $data['nomor'] . '.' . $data['no_sisip'];
                $data['no_mix'] = $no;
            } else {
                $no             = $data['nomor'];
                $data['no_mix'] = $no;
            }

            $data['no_surat'] = str_replace("{nomor}", $no, $format);
            $data['no_surat'] = str_replace("{tahun}", $data['tahun'], $data['no_surat']);
            $data['no_surat'] = str_replace("{klasifikasi}", $data['kode_klas'], $data['no_surat']);

            $data['tanggal_indo'] = Carbon::parse($data['tanggal'])
                ->locale('id')
                ->settings(['formatFunction' => 'translatedFormat'])
                ->format('j F Y');

            $data['created_by'] = optional(Auth::user())->id;

            $suratTugas = SuratTugas::create($data);

            return $this->success($suratTugas, 'Nomor Surat ' . $data['no_surat'] . ' berhasil disisipkan', 201);

        } catch (\Exception $e) {
            return $this->error('Failed to insert nomor surat', $e->getMessage(), 500);
        }
    }

    /**
     * Mendapatkan daftar tanggal yang tersedia untuk filter.
     *
     * Endpoint ini mengembalikan daftar tanggal unik dari data surat tugas
     * yang dapat digunakan untuk filtering. Tanggal dikembalikan dalam format Y-m-d
     * dan diurutkan secara descending. Hanya pengguna yang sudah login
     * yang dapat mengakses endpoint ini.
     *
     * @group Nomor Surat Tugas
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

        $dates = SuratTugas::where('tahun', $tahun)
            ->whereNotNull('tanggal')
            ->distinct()
            ->orderBy('tanggal', 'DESC')
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
     * Endpoint ini mengembalikan daftar tahun unik dari data surat tugas
     * yang dapat digunakan untuk filtering. Tahun dikembalikan diurutkan
     * secara descending (terbaru ke terlama). Hanya pengguna yang sudah login
     * yang dapat mengakses endpoint ini.
     *
     * @group Nomor Surat Tugas
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
        $years = SuratTugas::distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();

        return $this->success($years, 'Years retrieved successfully');
    }

    /**
     * Mendapatkan daftar klasifikasi surat untuk dropdown.
     *
     * Endpoint ini mengembalikan daftar klasifikasi surat yang dapat digunakan
     * untuk dropdown dalam form surat tugas. Data diambil dari tabel klasifikasi_surat
     * dengan parent_id 31. Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Nomor Surat Tugas
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
            ->where('parent_id', 31)
            ->select('kode', 'keterangan')
            ->get();

        return $this->success($klasifikasi, 'Klasifikasi retrieved successfully');
    }

    /**
     * Mendapatkan opsi form (klasifikasi + nomor terbaru tahun ini).
     *
     * @group Nomor Surat Tugas
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Form options retrieved successfully",
     *   "data": {
     *     "klasifikasi": [ { "kode": "100", "keterangan": "Organisasi" } ],
     *     "nomor_baru": "0025"
     *   }
     * }
     */
    public function formOptions()
    {
        $year = Carbon::now()->format('Y');

        $klasifikasi = DB::table('klasifikasi_surat')
            ->where('parent_id', 31)
            ->select('kode', 'keterangan')
            ->get();

        $maxNomor = SuratTugas::where('tahun', $year)
            ->max(DB::raw('CAST(nomor AS UNSIGNED)'));

        $nomorBaru = $maxNomor ? $maxNomor + 1 : 1;
        $nomorBaru = str_pad($nomorBaru, 4, '0', STR_PAD_LEFT);

        return $this->success([
            'klasifikasi' => $klasifikasi,
            'nomor_baru'  => $nomorBaru,
        ], 'Form options retrieved successfully');
    }
}
