<?php

namespace App\Http\Controllers\Api;

use App\Models\DirektoriUsaha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

/**
 * @group Kantor - Direktori Usaha
 *
 * APIs for managing business directory (Direktori Usaha).
 */
class DirektoriUsahaController extends BaseApiController
{
    /**
     * List businesses ready for geocoding
     * 
     * Get paginated list of businesses from region 3215 that have no gcs_result and missing coordinates.
     * 
     * @queryParam per_page integer Number of records per page. Default: 15. Example: 20
     * @queryParam filter[kdkec] string Filter by district code (exact match). Example: 051
     * @queryParam filter[kddesa] string Filter by village code (exact match). Example: 008
     * @queryParam filter[nama_usaha] string Search by business name (partial). Example: CELL
     * @queryParam filter[search] string Alias for nama_usaha filter. Example: TOKO
     * @queryParam sort string Sort field. Only 'nama_usaha' is allowed. Example: -nama_usaha
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [{
     *     "id": 1,
     *     "kode_wilayah": "3215",
     *     "nama_usaha": "KARTIKA CELL",
     *     "alamat_usaha": "JL. RAYA CENGKONG",
     *     "kdkec": "051",
     *     "kddesa": "008", 
     *     "nmkec": "PURWASARI",
     *     "nmdesa": "CENGKONG",
     *     "latitude": null,
     *     "longitude": null,
     *     "latlong_status": "invalid",
     *     "latitude_gc": null,
     *     "longitude_gc": null,
     *     "gcs_result": "1",
     *     "hasilgc": "1",
     *     "name_similarity": "100.00",
     *   }],
     *   "meta": {
     *     "current_page": 1,
     *     "from": 1,
     *     "last_page": 100,
     *     "per_page": 15,
     *     "to": 15,
     *     "total": 1500
     *   },
     *   "links": {
     *     "first": "http://localhost:9001/api/direktori-usaha?page=1",
     *     "last": "http://localhost:9001/api/direktori-usaha?page=100",
     *     "prev": null,
     *     "next": "http://localhost:9001/api/direktori-usaha?page=2"
     *   }
     * }
     */
    public function index(Request $request)
    {
        $data = QueryBuilder::for(DirektoriUsaha::class)
            // ->where('kode_wilayah', '3215')
            // ->where(function ($query) {
            //     $query->whereNull('gcs_result')
            //         ->orWhere('gcs_result', '');
            // })
            // ->where(function ($query) {
            //     $query->whereNull('latitude')
            //         ->orWhereNull('longitude');
            // })
            // ->where('name_similarity', '>=', 85.00)
            ->allowedFilters([
                AllowedFilter::callback('kdkec', function ($query, $value) {
                    if ($value === '' || $value === null || strtolower($value) === 'null') {
                        $query->where(function ($q) {
                            $q->whereNull('kdkec')->orWhere('kdkec', '');
                        });
                    } else {
                        $query->where('kdkec', $value);
                    }
                }),
                AllowedFilter::callback('kddesa', function ($query, $value) {
                    if ($value === '' || $value === null || strtolower($value) === 'null') {
                        $query->where(function ($q) {
                            $q->whereNull('kddesa')->orWhere('kddesa', '');
                        });
                    } else {
                        $query->where('kddesa', $value);
                    }
                }),
                AllowedFilter::callback('gc_username', function ($query, $value) {
                    $query->where('gc_username', $value);
                }),
                AllowedFilter::callback('gcs_result', function ($query, $value) {
                    if ($value === '' || $value === null || strtolower($value) === 'null') {
                         $query->where(function ($q) {
                             $q->whereNull('gcs_result')->orWhere('gcs_result', '');
                         });
                    } else {
                        $query->where('gcs_result', $value);
                    }
                }),
                AllowedFilter::callback('latlong_status_gc', function ($query, $value) {
                    $query->where('latlong_status_gc', $value);
                }),
                AllowedFilter::callback('search', function ($query, $value) {
                    if (is_array($value)) {
                        $value = implode(',', $value);
                    }
                    $query->where(function ($q) use ($value) {
                        $q->where('nama_usaha', 'like', "%{$value}%")
                          ->orWhere('alamat_usaha', 'like', "%{$value}%");
                    });
                }),
            ])
            ->allowedSorts([
                'nama_usaha',
            ])
            ->defaultSort('nama_usaha')
            ->select([
                'id',
                'kode_wilayah',
                'nama_usaha',
                'alamat_usaha',
                'kdkec',
                'kddesa',
                'nmkec',
                'nmdesa',
                'latitude',
                'longitude',
                'latlong_status',
                'gcs_result',
                'latitude_gc',
                'longitude_gc',
                'latlong_status_gc',
                'hasilgc',
                'name_similarity',
            ])
            ->fastPaginate($request->get('per_page', 15));

        return $this->success($data);
    }

    /**
     * List businesses that are companies (PT/CV)
     * 
     * Get paginated list of businesses where nama_usaha contains 'PT' or 'CV' with filtering and sorting.
     * 
     * @queryParam per_page integer Number of records per page. Default: 15. Example: 20
     * @queryParam filter[kdkec] string Filter by district code (exact match). Example: 051
     * @queryParam filter[kddesa] string Filter by village code (exact match). Example: 008
     * @queryParam filter[gcs_result] integer Filter by GCS result code. Example: 99
     * @queryParam filter[latlong_status_gc] string Filter by geocoding status. Example: valid
     * @queryParam filter[nama_usaha] string Search by business name (partial). Example: PT
     * @queryParam filter[search] string Alias for nama_usaha filter. Example: CV
     * @queryParam sort string Sort field (prefix with - for desc). Example: -nama_usaha
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [{
     *     "id": 1,
     *     "kode_wilayah": "3215",
     *     "nama_usaha": "PT KARYA MANDIRI",
     *     "alamat_usaha": "JL. RAYA CENGKONG",
     *     "kdkec": "051",
     *     "kddesa": "008",
     *     "nmkec": "PURWASARI",
     *     "nmdesa": "CENGKONG",
     *     "gcs_result": 99,
     *     "latitude_gc": "-6.366053",
     *     "longitude_gc": "107.378948",
     *     "latlong_status_gc": "invalid",
     *     "gc_username": "ekasyamsi-pppk"
     *   }],
     *   "meta": {
     *     "current_page": 1,
     *     "total": 1500
     *   }
     * }
     */
    public function perusahaan(Request $request)
    {
        $data = QueryBuilder::for(DirektoriUsaha::class)
            ->where(function ($query) {
                $query->where('nama_usaha', 'like', '%pt%')
                      ->orWhere('nama_usaha', 'like', '%cv%');
            })
            ->allowedFilters([
                AllowedFilter::callback('kdkec', function ($query, $value) {
                    if ($value === '' || $value === null || strtolower($value) === 'null') {
                        $query->where(function ($q) {
                            $q->whereNull('kdkec')->orWhere('kdkec', '');
                        });
                    } else {
                        $query->where('kdkec', $value);
                    }
                }),
                AllowedFilter::callback('kddesa', function ($query, $value) {
                    if ($value === '' || $value === null || strtolower($value) === 'null') {
                        $query->where(function ($q) {
                            $q->whereNull('kddesa')->orWhere('kddesa', '');
                        });
                    } else {
                        $query->where('kddesa', $value);
                    }
                }),
                AllowedFilter::callback('gcs_result', function ($query, $value) {
                    if ($value === '' || $value === null || strtolower($value) === 'null') {
                         $query->where(function ($q) {
                             $q->whereNull('gcs_result')->orWhere('gcs_result', '');
                         });
                    } else {
                        $query->where('gcs_result', $value);
                    }
                }),
                AllowedFilter::callback('latlong_status_gc', function ($query, $value) {
                    $query->where('latlong_status_gc', $value);
                }),
                AllowedFilter::partial('nama_usaha'),
                AllowedFilter::callback('search', function ($query, $value) {
                    if (is_array($value)) {
                        $value = implode(',', $value);
                    }
                    $query->where(function ($q) use ($value) {
                        $q->where('nama_usaha', 'like', "%{$value}%");
                    });
                }),
            ])
            ->allowedSorts([
                'nama_usaha',
                'kode_wilayah',
                'kdkec',
                'kddesa',
                'gcs_result',
                'latlong_status_gc',
            ])
            ->defaultSort('nama_usaha')
            ->select([
                'id',
                'kode_wilayah',
                'nama_usaha',
                'alamat_usaha',
                'kdkec',
                'kddesa',
                'nmkec',
                'nmdesa',
                'gcs_result',
                'latitude_gc',
                'longitude_gc',
                'latlong_status_gc',
                'gc_username'
            ])
            ->fastPaginate($request->get('per_page', 15));

        return $this->success($data);
    }

    /**
     * Display businesses without geocoding data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function noGcNoLoc(Request $request)
    {
        $data = QueryBuilder::for(DirektoriUsaha::class)
            ->where(function ($query) {
                $query->whereNull('gcs_result')
                    ->orWhere('gcs_result', '');
            })
            ->where(function ($query) {
                $query->whereNull('latitude_gc')
                    ->orWhere('latitude_gc', '');
            })
            ->where(function ($query) {
                $query->where('alamat_usaha', 'like', '%rt%')
                      ->orWhere('alamat_usaha', 'like', '%rw%');
            })
            ->where('alamat_usaha', 'not like', '%rt 0/rw 0%')
            ->allowedFilters([
                AllowedFilter::callback('kdkec', function ($query, $value) {
                    $query->where('kdkec', $value);
                }),
                AllowedFilter::callback('kddesa', function ($query, $value) {
                    $query->where('kddesa', $value);
                }),
                AllowedFilter::partial('nama_usaha'),
                AllowedFilter::callback('search', function ($query, $value) {
                    if (is_array($value)) {
                        $value = implode(',', $value);
                    }
                    $query->where(function ($q) use ($value) {
                        $q->where('nama_usaha', 'like', "%{$value}%")
                          ->orWhere('alamat_usaha', 'like', "%{$value}%");
                    });
                }),
            ])
            ->allowedSorts([
                'nama_usaha',
            ])
            ->defaultSort('nama_usaha')
            ->select([
                'id',
                'kode_wilayah',
                'nama_usaha',
                'alamat_usaha',
                'kdkec',
                'kddesa',
                'nmkec',
                'nmdesa',
                'latitude',
                'longitude',
                'latlong_status',
                'gcs_result',
                'hasilgc',
                'name_similarity',
            ])
            ->fastPaginate($request->get('per_page', 15));

        return $this->success($data);

    }

    /**
     * Display businesses with invalid geocoding status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function invalid(Request $request)
    {
        $data = QueryBuilder::for(DirektoriUsaha::class)
            ->where('latlong_status_gc', 'invalid')
            ->whereNotNull('latitude_gc')
            ->where('latitude_gc', '!=', '')
            ->allowedFilters([
                AllowedFilter::exact('kdprov'),
                AllowedFilter::exact('kdkab'),
                AllowedFilter::exact('kdkec'),
                AllowedFilter::exact('kddesa'),
                AllowedFilter::exact('gcs_result'),
                AllowedFilter::partial('nama_usaha'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('nama_usaha', 'like', "%{$value}%");
                }),
            ])
            ->allowedSorts([
                'nama_usaha',
                'kode_wilayah',
                'kdkec',
                'kddesa',
                'gcs_result',
            ])
            ->defaultSort('nama_usaha')
            ->select([
                'id',
                'kode_wilayah',
                'nama_usaha',
                'alamat_usaha',
                'kdkec',
                'kddesa',
                'nmkec',
                'nmdesa',
                'gcs_result',
                'latitude_gc',
                'longitude_gc',
                'latlong_status_gc',
                'gc_username'
            ])
            ->fastPaginate($request->get('per_page', 15));

        return $this->success($data);
    }

    /**
     * Display businesses ready to send for geocoding.
     * (No RT/RW in address or placeholder RT 0/RW 0, and no gcs_result)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function siapKirim(Request $request)
    {
        $data = QueryBuilder::for(DirektoriUsaha::class)
            ->where(function ($query) {
                // Address doesn't contain RT or RW
                $query->where(function ($q) {
                    $q->where('alamat_usaha', 'not like', '%rt%')
                      ->where('alamat_usaha', 'not like', '%rw%');
                })
                // OR contains the placeholder pattern 'rt 0/rw 0'
                ->orWhere('alamat_usaha', 'like', '%rt 0/rw 0%')
                ->orWhere('alamat_usaha', 'like', '%rt 00/rw 00%')
                ->orWhere('alamat_usaha', 'like', '%rt 000/rw 000%');
            })
            ->where(function ($query) {
                // No gcs_result
                $query->whereNull('gcs_result')
                    ->orWhere('gcs_result', '');
            })
            ->where('latlong_status', 'valid')
            // Exclude chain stores and business entities
            ->where('nama_usaha', 'not like', '%indomart%')
            ->where('nama_usaha', 'not like', '%indomaret%')
            ->where('nama_usaha', 'not like', '%alfamart%')
            ->where('nama_usaha', 'not like', '%alfamaret%')
            ->where('nama_usaha', 'not like', '%indomarco%')
            ->where('nama_usaha', 'not like', '%cv%')
            ->where('nama_usaha', 'not like', '%pt%')
            ->allowedFilters([
                AllowedFilter::exact('kdprov'),
                AllowedFilter::exact('kdkab'),
                AllowedFilter::exact('kdkec'),
                AllowedFilter::exact('kddesa'),
                AllowedFilter::partial('nama_usaha'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('nama_usaha', 'like', "%{$value}%");
                }),
            ])
            ->allowedSorts([
                'nama_usaha',
                'kode_wilayah',
                'kdkec',
                'kddesa',
            ])
            ->defaultSort('nama_usaha')
            ->select([
                'id',
                'kode_wilayah',
                'nama_usaha',
                'alamat_usaha',
                'kdkec',
                'kddesa',
                'nmkec',
                'nmdesa',
                'gcs_result',
                'latitude_gc',
                'longitude_gc',
                'latlong_status_gc',
                'gc_username'
            ])
            ->fastPaginate($request->get('per_page', 15));

        return $this->success($data);
    }



    /**
     * Export businesses data as CSV based on flag.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function csvExport(Request $request)
    {
        $flag = $request->input('flag');
        
        if (!in_array($flag, ['NO_GC', 'INVALID'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid flag. Must be NO_GC or INVALID.'
            ], 400);
        }

        // Validate gc_username is required for INVALID flag
        if ($flag === 'INVALID' && !$request->has('gc_username')) {
            return response()->json([
                'success' => false,
                'message' => 'gc_username parameter is required when flag=INVALID.'
            ], 400);
        }

        // Build query based on flag
        if ($flag === 'NO_GC') {
            // Same logic as noGcNoLoc
            $data = QueryBuilder::for(DirektoriUsaha::class)
                ->where(function ($query) {
                    $query->whereNull('gcs_result')
                        ->orWhere('gcs_result', '');
                })
                ->where(function ($query) {
                    $query->whereNull('latitude_gc')
                        ->orWhere('latitude_gc', '');
                })
                ->allowedFilters([
                    AllowedFilter::exact('kdkec'),
                    AllowedFilter::exact('kddesa'),
                ])
                ->get();
        } else {
            // Same logic as invalid + gc_username filter
            $data = QueryBuilder::for(DirektoriUsaha::class)
                ->where('latlong_status_gc', 'invalid')
                ->whereNotNull('latitude_gc')
                ->where('latitude_gc', '!=', '')
                ->where('gc_username', $request->gc_username)
                ->allowedFilters([
                    AllowedFilter::exact('kdkec'),
                    AllowedFilter::exact('kddesa'),
                    AllowedFilter::exact('gc_username'),
                ])
                ->get();
        }

        // Define all columns
        $columns = [
            'idsbr', 'nama_usaha', 'alamat_usaha', 'kode_wilayah',
            'kdprov', 'kdkab', 'kdkec', 'kddesa',
            'nmprov', 'nmkab', 'nmkec', 'nmdesa',
            'perusahaan_id', 'status_perusahaan', 'skor_kalo', 'kegiatan_usaha',
            'rank_nama', 'rank_alamat', 'history_ref_profiling_id', 'skala_usaha', 'sumber_data',
            'latitude', 'longitude', 'latlong_status', 'gcid', 'gcs_result',
            'allow_cancel', 'allow_edit', 'allow_flagging',
            'latitude_gc', 'longitude_gc', 'latlong_status_gc', 'gc_username',
            'nama_usaha_gc', 'alamat_usaha_gc'
        ];

        // Generate CSV header
        $csvContent = implode(',', $columns) . "\n";
        
        // Generate CSV rows
        foreach ($data as $row) {
            $rowData = [];
            foreach ($columns as $column) {
                $value = $row->$column ?? '';
                // Escape quotes and wrap in quotes if contains comma or quote
                if (strpos($value, ',') !== false || strpos($value, '"') !== false) {
                    $value = '"' . str_replace('"', '""', $value) . '"';
                }
                $rowData[] = $value;
            }
            $csvContent .= implode(',', $rowData) . "\n";
        }

        // Return CSV download
        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="direktori_usaha_full_all_columns_2026.csv"');
    }

    /**
     * Display businesses with potential duplicates.
     * (RT/RW in address but not placeholder, no gcs_result, name_similarity >= 85)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ganda(Request $request)
    {
        $data = QueryBuilder::for(DirektoriUsaha::class)
            ->where(function ($query) {
                // Address contains RT or RW
                $query->where(function ($q) {
                    $q->where('alamat_usaha', 'like', '%rt%')
                      ->orWhere('alamat_usaha', 'like', '%rw%');
                })
                // AND NOT placeholder patterns
                ->where('alamat_usaha', 'not like', '%rt 0/rw 0%')
                ->where('alamat_usaha', 'not like', '%rt 00/rw 00%')
                ->where('alamat_usaha', 'not like', '%rt 000/rw 000%');
            })
            ->where(function ($query) {
                // No gcs_result
                $query->whereNull('gcs_result')
                    ->orWhere('gcs_result', '');
            })
            ->where('name_similarity', '>=', 85)
            ->allowedFilters([
                AllowedFilter::exact('kdprov'),
                AllowedFilter::exact('kdkab'),
                AllowedFilter::exact('kdkec'),
                AllowedFilter::exact('kddesa'),
                AllowedFilter::partial('nama_usaha'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('nama_usaha', 'like', "%{$value}%");
                }),
            ])
            ->allowedSorts([
                'nama_usaha',
                'kode_wilayah',
                'kdkec',
                'kddesa',
                'name_similarity',
            ])
            ->defaultSort('-name_similarity')
            ->select([
                'id',
                'kode_wilayah',
                'nama_usaha',
                'alamat_usaha',
                'kdkec',
                'kddesa',
                'nmkec',
                'nmdesa',
                'gcs_result',
                'latitude_gc',
                'longitude_gc',
                'latlong_status_gc',
                'gc_username',
                'name_similarity'
            ])
            ->fastPaginate($request->get('per_page', 15));

        return $this->success($data);
    }

    /**
     * Display the specified direktori usaha.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $usaha = DirektoriUsaha::select([
            'id',
            'kode_wilayah',
            'nama_usaha',
            'alamat_usaha',
            'kdkec',
            'kddesa',
            'nmkec',
            'nmdesa',
            'gcs_result',
            'latitude_gc',
            'longitude_gc',
            'latlong_status_gc',
            'gc_username'
        ])->find($id);

        if (!$usaha) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $usaha
        ]);
    }

    /**
     * Update geocoding fields of the specified direktori usaha.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $usaha = DirektoriUsaha::find($id);

        if (!$usaha) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        // Validate only allowed fields
        $validator = Validator::make($request->all(), [
            'hasilgc' => 'required|integer|in:1,3,4,99',
            'latitude' => 'required_if:hasilgc,1|nullable|string|regex:/^-6\.\d+$/',
            'longitude' => 'required_if:hasilgc,1|nullable|string|regex:/^107\.\d+$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update only geocoding fields
        $updateData = [];
        
        // Set hasilgc value
        if ($request->has('hasilgc')) {
            $updateData['hasilgc'] = $request->hasilgc;
        }
        
        // Conditionally set latitude and longitude based on hasilgc
        if ($request->hasilgc == 1 || $request->hasilgc == 4) {
            // If hasilgc is 1 or 4, latitude and longitude are required and saved
            $updateData['latitude'] = $request->latitude;
            $updateData['longitude'] = $request->longitude;
            $updateData['latitude_gc'] = $request->latitude;
            $updateData['longitude_gc'] = $request->longitude;
        } else {
            // If hasilgc is 3 or 99, set latitude and longitude to null
            $updateData['latitude'] = null;
            $updateData['longitude'] = null;
            $updateData['latitude_gc'] = null;
            $updateData['longitude_gc'] = null;
        }
        
        // Automatically set latlong_status to 'valid' when update passes validation
        $updateData['latlong_status'] = 'valid';
        
        // Set update_by to current user id
        $updateData['update_by'] = Auth::id();

        $usaha->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diperbarui',
            'data' => $usaha->only([
                'id',
                'kode_wilayah',
                'nama_usaha',
                'alamat_usaha',
                'kdkec',
                'kddesa',
                'nmkec',
                'nmdesa',
                'gcs_result',
                'latitude',
                'longitude',
                'latlong_status',
                'gc_username'
            ])
        ]);
    }

    /**
     * Get unique desa options based on kecamatan code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function desaOptions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kdkec' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $desas = DirektoriUsaha::select('kddesa', 'nmdesa')
            ->where('kdkec', $request->kdkec)
            ->whereNotNull('kddesa')
            ->whereNotNull('nmdesa')
            ->where('kddesa', '!=', '')
            ->where('nmdesa', '!=', '')
            ->distinct()
            ->orderBy('kddesa')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $desas
        ]);
    }

    /**
     * Get recap of users who updated businesses.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function rekapUser()
    {
        $data = DirektoriUsaha::select('update_by', \DB::raw('count(*) as total'))
            ->whereNotNull('hasilgc')
            ->whereNotNull('update_by')
            ->groupBy('update_by')
            ->with('updater:id,name') // Eager load user name
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get progress of geocoding by kecamatan.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function progres()
    {
        $data = DirektoriUsaha::select(
            'kdkec', 
            'nmkec',
            \DB::raw("COUNT(CASE WHEN (hasilgc IS NOT NULL AND hasilgc != '') OR (gcs_result IS NOT NULL AND gcs_result != '') THEN 1 END) as Sudah"),
            \DB::raw("COUNT(CASE WHEN (hasilgc IS NULL OR hasilgc = '') AND (gcs_result IS NULL OR gcs_result = '') THEN 1 END) as Belum")
        )
        ->groupBy('kdkec', 'nmkec')
        ->orderBy('kdkec')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get filter options for dropdown selectors.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function filters()
    {
        // Get unique kdkec with nmkec
        $keca = DirektoriUsaha::select('kdkec', 'nmkec')
            ->whereNotNull('kdkec')
            ->whereNotNull('nmkec')
            ->where('kdkec', '!=', '')
            ->where('nmkec', '!=', '')
            ->groupBy('kdkec', 'nmkec')
            ->orderBy('kdkec')
            ->get()
            ->map(function ($item) {
                return [
                    'kdkec' => $item->kdkec,
                    'nmkec' => $item->nmkec,
                ];
            })
            ->values();

        // Get unique gc_username
        $username = DirektoriUsaha::select('gc_username')
            ->whereNotNull('gc_username')
            ->where('gc_username', '!=', '')
            ->distinct()
            ->orderBy('gc_username')
            ->pluck('gc_username');

        return response()->json([
            'success' => true,
            'data' => [
                'keca' => $keca,
                'gc_username' => $username,
            ]
        ]);
    }

    /**
     * Export siap kirim data to CSV.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function csvSiapKirim()
    {
        $fileName = 'data_gc_profiling_bahan_kirim.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, ['perusahaan_id', 'latitude', 'longitude', 'hasilgc']);

            // Query data
            $query = DirektoriUsaha::select('perusahaan_id', 'latitude', 'longitude', 'hasilgc')
                ->where(function($q) {
                    $q->whereNull('gcs_result')
                      ->orWhere('gcs_result', '');
                })
                ->where(function($q) {
                    $q->whereNotNull('hasilgc')
                      ->where('hasilgc', '!=', '');
                })
                ->where('update_by', auth()->id());

            // Chunking for performance
            $query->chunk(100, function($rows) use ($file) {
                foreach ($rows as $row) {
                    fputcsv($file, [
                        $row->perusahaan_id,
                        $row->latitude,
                        $row->longitude,
                        $row->hasilgc
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    /**
     * Get unique desa mapping
     * 
     * Returns a list of unique 10-digit kode_wilayah with concatenated nama_wilayah (KECAMATAN - DESA).
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "kode_wilayah": "3215010001",
     *       "nama_wilayah": "KARAWANG BARAT - TANJUNGPURA"
     *     }
     *   ]
     * }
     */
    public function mapDesa()
    {
        $data = DirektoriUsaha::select('kode_wilayah', 'nmkec', 'nmdesa')
            ->whereRaw('CHAR_LENGTH(kode_wilayah) = 10')
            ->distinct()
            ->orderBy('nmkec')
            ->orderBy('nmdesa')
            ->get()
            ->map(function ($item) {
                return [
                    'kode_wilayah' => $item->kode_wilayah,
                    'nama_wilayah' => trim($item->nmkec) . ' - ' . trim($item->nmdesa)
                ];
            })
            ->values(); // Reset keys after map just in case

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
    /**
     * Export updated data to CSV
     * 
     * Exports records where hasilgc is set, gcs_result is set, and gc_username matches the logged-in user's email prefix.
     * 
     * @response 200 CSV file download
     */
    public function csvUpdate(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $username = explode('@', $user->email)[0];
        $fileName = 'data_update_' . $username . '.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['perusahaan_id', 'latitude', 'longitude', 'hasilgc'];

        $callback = function() use ($columns, $username) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, $columns);

            // Query data
            $query = DirektoriUsaha::select($columns)
                ->whereNotNull('hasilgc')
                ->where('hasilgc', '!=', '')
                ->whereNotNull('gcs_result')
                ->where('gcs_result', '!=', '') 
                ->where('gc_username', $username);

            // Chunking for performance
            $query->chunk(100, function($rows) use ($file) {
                foreach ($rows as $row) {
                    fputcsv($file, [
                        $row->perusahaan_id,
                        $row->latitude,
                        $row->longitude,
                        $row->hasilgc
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
