<?php

namespace App\Http\Controllers\Api\Kantor\NomorSurat;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\BaseApiController;
use App\Models\SuratKeluar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SuratKeluarApiController extends BaseApiController
{
    /**
     * Menampilkan daftar surat keluar dengan pagination dan filtering.
     *
     * Endpoint ini mengembalikan daftar surat keluar dengan kemampuan filtering
     * berdasarkan tahun, tanggal, dan pencarian. Mendukung pagination dan sorting.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Nomor Surat Keluar
     *
     * @authenticated
     *
     * @queryParam filter[thn] string Filter berdasarkan tahun (default: tahun saat ini). Contoh: 2024
     * @queryParam filter[tanggal] date Filter berdasarkan tanggal (format: YYYY-MM-DD). Contoh: 2024-01-15
     * @queryParam filter[bulan] integer Filter berdasarkan bulan (1-12). Contoh: 1
     * @queryParam filter[tujuan] string Pencarian berdasarkan tujuan. Contoh: Dinas
     * @queryParam filter[perihal] string Pencarian berdasarkan perihal. Contoh: Survei
     * @queryParam filter[no_surat] string Pencarian berdasarkan nomor surat. Contoh: 0001/SK
     * @queryParam filter[search] string Pencarian global di tujuan, perihal, dan isi_surat. Contoh: pelaksanaan
     * @queryParam sort string Kolom untuk sorting (default: -id). Contoh: -tanggal,nomor
     * @queryParam per_page int Jumlah item per halaman (default: 15). Contoh: 10
     * @queryParam cursor string Cursor untuk pagination. Contoh: eyJpZCI6M...
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Surat keluar retrieved successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "thn": "2024",
     *       "bln": "01",
     *       "tanggal": "2024-01-15",
     *       "nomor": "0001",
     *       "no_sisip": null,
     *       "tanggal_indo": "15 Januari 2024",
     *       "no_surat": "0001/SK/2024",
     *       "dari": "Kepala BPS Kota ABC",
     *       "tujuan": "Dinas Pendidikan",
     *       "perihal": "Pemberitahuan Pelaksanaan Survei",
     *       "isi_surat": "Dengan hormat...",
     *       "lampiran": 2,
     *       "file": null,
     *       "tembusan": ["Kabid Statistik Sosial", "Kabid Statistik Produksi"],
     *       "sifat": "Biasa",
     *       "created_by": 1,
     *       "created_at": "2024-01-15T00:00:00.000000Z",
     *       "updated_at": "2024-01-15T00:00:00.000000Z"
     *     }
     *   ],
     *   "meta": {
     *     "per_page": 15,
     *     "has_more": false,
     *     "count": 1
     *   },
     *   "links": {
     *     "next_cursor": null,
     *     "next_page_url": null,
     *     "prev_cursor": null,
     *     "prev_page_url": null,
     *     "path": "http://localhost:8000/api/surat-keluar"
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
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        if (!$authUser) {
            return $this->error('Unauthorized', null, 401);
        }

        // Get tahun from either 'tahun' query parameter or 'filter[thn]'
        $tahun = $request->get('tahun') ??
                 ($request->get('filter')['thn'] ?? null);

        // Prepare base query (year filter is applied only when explicitly provided)
        $suratKeluarQuery = SuratKeluar::query();
        if (!empty($tahun)) {
            $suratKeluarQuery->where('thn', $tahun);
        }

        // Get total count for pagination info (separate query for performance)
        $totalRecords = $suratKeluarQuery->count();

        // Use Spatie QueryBuilder for filtering and sorting
        $suratKeluar = QueryBuilder::for($suratKeluarQuery)
            ->allowedFilters([
                AllowedFilter::exact('thn'),
                AllowedFilter::exact('tanggal'),
                AllowedFilter::callback('bulan', function ($query, $value) {
                    if (is_numeric($value)) {
                        $query->whereMonth('tanggal', $value);
                    }
                }),
                'tujuan',
                'perihal',
                'no_surat',
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($q) use ($value) {
                        $q->where('tujuan', 'LIKE', "%{$value}%")
                            ->orWhere('perihal', 'LIKE', "%{$value}%")
                            ->orWhere('isi_surat', 'LIKE', "%{$value}%");
                    });
                }),
            ])
            ->allowedSorts(['id', 'thn', 'tanggal', 'nomor'])
            ->defaultSort('-thn', '-nomor')
            ->fastPaginate($request->get('per_page', 15));

        $perPage = $request->get('per_page', 15);
        $totalPages = $perPage > 0 ? ceil($totalRecords / $perPage) : 1;
        $extras = [
            'pagination_info' => [
                'total_page' => $totalPages,
                'total_records' => $totalRecords,
            ]
        ];

        return $this->success($suratKeluar, 'Surat keluar retrieved successfully', 200, $extras);
    }

    /**
     * Membuat surat keluar baru dengan nomor otomatis.
     *
     * Endpoint ini membuat surat keluar baru dengan sistem penomoran otomatis.
     * Jika tahun (thn) tidak disediakan, sistem akan menggunakan tahun saat ini.
     * Jika nomor tidak disediakan, sistem akan secara otomatis menentukan nomor
     * berikutnya berdasarkan tahun yang sama. Nomor akan diformat sesuai dengan
     * template yang ada di settings.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Nomor Surat Keluar
     *
     * @authenticated
     *
     * @bodyParam thn string Tahun surat (akan menggunakan tahun saat ini jika tidak disediakan). Contoh: 2024
     * @bodyParam tanggal date required Tanggal surat (format: YYYY-MM-DD). Contoh: 2024-01-15
     * @bodyParam nomor string Nomor urut surat (akan diisi otomatis jika kosong). Contoh: 1
     * @bodyParam dari string required Pengirim surat. Contoh: Kepala BPS Kota ABC
     * @bodyParam tujuan string required Tujuan surat. Contoh: Dinas Pendidikan
     * @bodyParam perihal string required Perihal surat. Contoh: Pemberitahuan Pelaksanaan Survei
     * @bodyParam isi_surat string required Isi surat. Contoh: Dengan hormat...
     * @bodyParam lampiran integer Jumlah lampiran (opsional). Contoh: 2
     * @bodyParam file string File attachment (opsional). Contoh: document.pdf
     * @bodyParam tembusan array Tembusan surat dalam bentuk array. Contoh: ["Kabid Statistik Sosial", "Kabid Statistik Produksi"]
     * @bodyParam sifat string Sifat surat. Contoh: Biasa
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Nomor Surat 0001/SK/2024 berhasil disimpan",
     *   "data": {
     *     "id": 1,
     *     "thn": "2024",
     *     "bln": "01",
     *     "tanggal": "2024-01-15",
     *     "nomor": "0001",
     *     "no_sisip": null,
     *     "tanggal_indo": "15 Januari 2024",
     *     "no_surat": "0001/SK/2024",
     *     "dari": "Kepala BPS Kota ABC",
     *     "tujuan": "Dinas Pendidikan",
     *     "perihal": "Pemberitahuan Pelaksanaan Survei",
     *     "isi_surat": "Dengan hormat...",
     *     "lampiran": 2,
     *     "file": null,
     *     "tembusan": ["Kabid Statistik Sosial", "Kabid Statistik Produksi"],
     *     "sifat": "Biasa",
     *     "created_by": 1,
     *     "created_at": "2024-01-15T00:00:00.000000Z",
     *     "updated_at": "2024-01-15T00:00:00.000000Z"
     *   }
     * }
     * @response 201 {
     *   "success": true,
     *   "message": "Nomor Surat 0002/SK/2024 berhasil disimpan",
     *   "data": {
     *     "id": 2,
     *     "thn": "2024",
     *     "bln": "01",
     *     "tanggal": "2024-01-16",
     *     "nomor": "0002",
     *     "no_sisip": null,
     *     "tanggal_indo": "16 Januari 2024",
     *     "no_surat": "0002/SK/2024",
     *     "dari": "Kepala BPS Kota ABC",
     *     "tujuan": "Dinas Kesehatan",
     *     "perihal": "Undangan Rapat Koordinasi",
     *     "isi_surat": "Dengan hormat...",
     *     "lampiran": 1,
     *     "file": null,
     *     "tembusan": ["Kabid Statistik Distribusi", "Kasubag Umum"],
     *     "sifat": "Segera",
     *     "created_by": 1,
     *     "created_at": "2024-01-16T00:00:00.000000Z",
     *     "updated_at": "2024-01-16T00:00:00.000000Z"
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
     *     "tanggal": ["The tanggal field is required."]
     *   }
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Failed to create surat keluar",
     *   "errors": "Error message details"
     * }
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        if (!$authUser) {
            return $this->error('Unauthorized', null, 401);
        }

        $validator = Validator::make($request->all(), [
            'thn' => 'nullable|string',
            'tanggal' => 'required|date',
            'nomor' => 'nullable|string',
            'dari' => 'required|string',
            'tujuan' => 'required|string',
            'perihal' => 'required|string',
            'isi_surat' => 'required|string',
            'lampiran' => 'nullable|integer',
            'tembusan' => 'nullable|array',
            'sifat' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', $validator->errors(), 422);
        }

        try {
            $data = $request->all();

            // Set default value for thn as current year if not provided
            if (empty($data['thn'])) {
                $data['thn'] = Carbon::now()->format('Y');
            }

            // Generate nomor otomatis jika tidak disediakan
            if (empty($data['nomor'])) {
                // Cari nomor maksimum untuk tahun yang sama
                $maxNomor = SuratKeluar::where('thn', $data['thn'])
                    ->max('nomor');

                // Tambahkan 1 ke nomor maksimum, default ke 1 jika tidak ada
                $nextNomor = $maxNomor ? (int) $maxNomor + 1 : 1;
                $data['nomor'] = str_pad($nextNomor, 4, '0', STR_PAD_LEFT);
            } else {
                // Format nomor yang diberikan
                $data['nomor'] = str_pad($data['nomor'], 4, '0', STR_PAD_LEFT);
            }

            $formatSetting = DB::table('settings')
                             ->where('key', 'FORMAT_SURAT_KELUAR')
                             ->latest()
                             ->first();

            if (!$formatSetting) {
                return $this->error('Format setting not found', 'FORMAT_SURAT_KELUAR setting is missing in the database', 500);
            }

            $format = $formatSetting->value;

            // Generate nomor surat
            if (! empty($data['no_sisip'])) {
                $no = $data['nomor'].'.'.$data['no_sisip'];
                $data['no_surat'] = str_replace('{nomor}', $no, $format);
            } else {
                $data['no_surat'] = str_replace('{nomor}', $data['nomor'], $format);
            }

            $data['no_surat'] = str_replace('{tahun}', $data['thn'], $data['no_surat']);
            $data['tanggal_indo'] = Carbon::parse($data['tanggal'])
                ->locale('id')
                ->settings(['formatFunction' => 'translatedFormat'])
                ->format('j F Y');
            $data['bln'] = Carbon::parse($data['tanggal'])->format('m');
            $data['created_by'] = Auth::id();

            $suratKeluar = SuratKeluar::create($data);

            return $this->success($suratKeluar, 'Nomor Surat '.$data['no_surat'].' berhasil disimpan', 201);

        } catch (\Exception $e) {
            return $this->error('Failed to create surat keluar', $e->getMessage(), 500);
        }
    }

    /**
     * Menampilkan detail surat keluar berdasarkan ID.
     *
     * Endpoint ini mengembalikan detail lengkap dari surat keluar tertentu.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Nomor Surat Keluar
     *
     * @authenticated
     *
     * @urlParam id integer required ID surat keluar. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data retrieved successfully",
     *   "data": {
     *     "id": 1,
     *     "thn": "2024",
     *     "bln": "01",
     *     "tanggal": "2024-01-15",
     *     "nomor": "0001",
     *     "no_sisip": null,
     *     "tanggal_indo": "15 Januari 2024",
     *     "no_surat": "0001/SK/2024",
     *     "dari": "Kepala BPS Kota ABC",
     *     "tujuan": "Dinas Pendidikan",
     *     "perihal": "Pemberitahuan Pelaksanaan Survei",
     *     "isi_surat": "Dengan hormat...",
     *     "lampiran": 2,
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
     *   "message": "Surat keluar not found"
     * }
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $suratKeluar = SuratKeluar::findOrFail($id);

            return $this->success($suratKeluar, 'Data retrieved successfully');

        } catch (\Exception $e) {
            return $this->error('Surat keluar not found', null, 404);
        }
    }

    /**
     * Mengupdate surat keluar berdasarkan ID.
     *
     * Endpoint ini mengupdate data surat keluar yang sudah ada.
     * Nomor surat akan diformat ulang sesuai dengan data yang diupdate.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Nomor Surat Keluar
     *
     * @authenticated
     *
     * @urlParam id integer required ID surat keluar yang akan diupdate. Contoh: 1
     *
     * @bodyParam thn string required Tahun surat. Contoh: 2024
     * @bodyParam tanggal date required Tanggal surat (format: YYYY-MM-DD). Contoh: 2024-01-15
     * @bodyParam nomor string required Nomor urut surat. Contoh: 1
     * @bodyParam dari string required Pengirim surat. Contoh: Kepala BPS Kota ABC
     * @bodyParam tujuan string required Tujuan surat. Contoh: Dinas Pendidikan
     * @bodyParam perihal string required Perihal surat. Contoh: Pemberitahuan Pelaksanaan Survei
     * @bodyParam isi_surat string Isi surat (opsional). Contoh: Dengan hormat...
     * @bodyParam lampiran integer Jumlah lampiran (opsional). Contoh: 2
     * @bodyParam no_sisip string Nomor sisip (opsional). Contoh: 1
     * @bodyParam file string File attachment (opsional). Contoh: document.pdf
     * @bodyParam tembusan array Tembusan surat dalam bentuk array. Contoh: ["Kabid Statistik Sosial", "Kabid Statistik Produksi"]
     * @bodyParam sifat string Sifat surat. Contoh: Biasa
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Nomor Surat 0001/SK/2024 berhasil diupdate",
     *   "data": {
     *     "id": 1,
     *     "thn": "2024",
     *     "bln": "01",
     *     "tanggal": "2024-01-15",
     *     "nomor": "0001",
     *     "no_sisip": null,
     *     "tanggal_indo": "15 Januari 2024",
     *     "no_surat": "0001/SK/2024",
     *     "dari": "Kepala BPS Kota ABC",
     *     "tujuan": "Dinas Pendidikan",
     *     "perihal": "Pemberitahuan Pelaksanaan Survei",
     *     "isi_surat": "Dengan hormat...",
     *     "lampiran": 2,
     *     "file": null,
     *     "tembusan": ["Kabid Statistik Sosial", "Kabid Statistik Produksi"],
     *     "sifat": "Biasa",
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
     *   "message": "Surat keluar not found"
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
     *   "message": "Failed to update surat keluar",
     *   "errors": "Error message details"
     * }
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'thn' => 'required',
            'tanggal' => 'required|date',
            'nomor' => 'required|string',
            'dari' => 'required|string',
            'tujuan' => 'required|string',
            'perihal' => 'required|string',
            'isi_surat' => 'nullable|string',
            'lampiran' => 'nullable|integer',
            'no_sisip' => 'nullable|string',
            'file' => 'nullable|string',
            'tembusan' => 'nullable|array',
            'sifat' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', $validator->errors(), 422);
        }

        try {
            $suratKeluar = SuratKeluar::findOrFail($id);
            $data = $request->all();

            // Format nomor
            if (empty($data['nomor']) || $data['nomor'] == 0) {
                $data['nomor'] = null;
            }

            $formatSetting = DB::table('settings')
                             ->where('key', 'FORMAT_SURAT_KELUAR')
                             ->latest()
                             ->first();

            if (!$formatSetting) {
                return $this->error('Format setting not found', 'FORMAT_SURAT_KELUAR setting is missing in the database', 500);
            }

            $format = $formatSetting->value;

            $data['nomor'] = str_pad($data['nomor'], 4, '0', STR_PAD_LEFT);

            // Generate nomor surat
            if (! empty($data['no_sisip'])) {
                $no = $data['nomor'].'.'.$data['no_sisip'];
                $data['no_surat'] = str_replace('{nomor}', $no, $format);
            } else {
                $data['no_surat'] = str_replace('{nomor}', $data['nomor'], $format);
            }

            $data['no_surat'] = str_replace('{tahun}', $data['thn'], $data['no_surat']);
            $data['tanggal_indo'] = Carbon::parse($data['tanggal'])
                ->locale('id')
                ->settings(['formatFunction' => 'translatedFormat'])
                ->format('j F Y');
            $data['bln'] = Carbon::parse($data['tanggal'])->format('m');

            $suratKeluar->update($data);
            $suratKeluar->refresh();

            return $this->success($suratKeluar, 'Nomor Surat '.$data['no_surat'].' berhasil diupdate');

        } catch (\Exception $e) {
            return $this->error('Failed to update surat keluar', $e->getMessage(), 500);
        }
    }

    /**
     * Menghapus surat keluar berdasarkan ID.
     *
     * Endpoint ini menghapus data surat keluar yang dipilih dari database.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Nomor Surat Keluar
     *
     * @authenticated
     *
     * @urlParam id integer required ID surat keluar yang akan dihapus. Contoh: 1
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
     *   "message": "Surat keluar not found"
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Failed to delete surat keluar",
     *   "errors": "Error message details"
     * }
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $suratKeluar = SuratKeluar::findOrFail($id);
            $suratKeluar->delete();

            return $this->success(null, 'Nomor Surat berhasil dihapus');

        } catch (\Exception $e) {
            return $this->error('Failed to delete surat keluar', $e->getMessage(), 500);
        }
    }

    /**
     * Menyisipkan surat keluar baru dengan penomoran otomatis.
     *
     * Endpoint ini menyisipkan surat keluar baru dengan sistem penomoran otomatis.
     * Fungsi ini mirip dengan update tetapi membuat data baru. Nomor surat akan
     * diambil dari surat referensi, dan nomor sisip akan secara otomatis ditentukan
     * berdasarkan nilai maksimum no_sisip yang ada untuk nomor tersebut ditambah 1.
     * Jika tidak ada nomor_sisip sebelumnya, maka akan dimulai dari 1.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Nomor Surat Keluar
     *
     * @authenticated
     *
     * @bodyParam id integer required ID surat keluar sebagai referensi untuk tahun dan nomor. Contoh: 1
     * @bodyParam thn string Tahun surat (akan menggunakan tahun dari referensi jika tidak disediakan). Contoh: 2024
     * @bodyParam tanggal date required Tanggal surat (format: YYYY-MM-DD). Contoh: 2024-01-15
     * @bodyParam dari string required Pengirim surat. Contoh: Kepala BPS Kota ABC
     * @bodyParam tujuan string required Tujuan surat. Contoh: Dinas Pendidikan
     * @bodyParam perihal string required Perihal surat. Contoh: Pemberitahuan Pelaksanaan Survei
     * @bodyParam isi_surat string Isi surat (opsional). Contoh: Dengan hormat...
     * @bodyParam lampiran integer Jumlah lampiran (opsional). Contoh: 2
     * @bodyParam file string File attachment (opsional). Contoh: document.pdf
     * @bodyParam tembusan array Tembusan surat dalam bentuk array. Contoh: ["Kabid Statistik Sosial", "Kabid Statistik Produksi"]
     * @bodyParam sifat string Sifat surat. Contoh: Biasa
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Nomor Surat 0001/SK/2024 berhasil disisipkan",
     *   "data": {
     *     "id": 3,
     *     "thn": "2024",
     *     "bln": "01",
     *     "tanggal": "2024-01-17",
     *     "nomor": "0001",
     *     "no_sisip": "1",
     *     "tanggal_indo": "17 Januari 2024",
     *     "no_surat": "0001.1/SK/2024",
     *     "dari": "Kepala BPS Kota ABC",
     *     "tujuan": "Dinas Pendidikan",
     *     "perihal": "Pemberitahuan Pelaksanaan Survei",
     *     "isi_surat": "Dengan hormat...",
     *     "lampiran": 2,
     *     "file": null,
     *     "tembusan": ["Kabid Statistik Sosial", "Kabid Statistik Produksi"],
     *     "sifat": "Biasa",
     *     "created_by": 1,
     *     "created_at": "2024-01-17T00:00:00.000000Z",
     *     "updated_at": "2024-01-17T00:00:00.000000Z"
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Surat keluar not found"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation error",
     *   "errors": {
     *     "tanggal": ["The tanggal field is required."]
     *   }
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Failed to insert surat keluar",
     *   "errors": "Error message details"
     * }
     *
     * @return \Illuminate\Http\Response
     */
    public function insert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:surat_keluar,id',
            'thn' => 'nullable|string',
            'tanggal' => 'required|date',
            'dari' => 'required|string',
            'tujuan' => 'required|string',
            'perihal' => 'required|string',
            'isi_surat' => 'nullable|string',
            'lampiran' => 'nullable|integer',
            'file' => 'nullable|string',
            'tembusan' => 'nullable|array',
            'sifat' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', $validator->errors(), 422);
        }

        try {
            // Get the reference surat keluar to determine the year if not provided
            $referenceSurat = SuratKeluar::findOrFail($request->id);

            $data = $request->all();

            // Set default value for thn as current year or from reference if not provided
            if (empty($data['thn'])) {
                $data['thn'] = $referenceSurat->thn;
            }

            // Use the reference surat's nomor
            $data['nomor'] = $referenceSurat->nomor;

            // Generate nomor sisip otomatis
            // Cari nomor sisip maksimum untuk kombinasi nomor dan tahun yang sama
            $maxNomorSisip = SuratKeluar::where('thn', $data['thn'])
                ->where('nomor', $data['nomor'])
                ->max('no_sisip');

            // Tambahkan 1 ke nomor sisip maksimum, default ke 1 jika tidak ada
            $nextNomorSisip = $maxNomorSisip ? (int) $maxNomorSisip + 1 : 1;
            $data['no_sisip'] = (string) $nextNomorSisip;

            $formatSetting = DB::table('settings')
                             ->where('key', 'FORMAT_SURAT_KELUAR')
                             ->latest()
                             ->first();

            if (!$formatSetting) {
                return $this->error('Format setting not found', 'FORMAT_SURAT_KELUAR setting is missing in the database', 500);
            }

            $format = $formatSetting->value;

            // Generate nomor surat dengan nomor sisip
            $no = $data['nomor'].'.'.$data['no_sisip'];
            $data['no_surat'] = str_replace('{nomor}', $no, $format);
            $data['no_surat'] = str_replace('{tahun}', $data['thn'], $data['no_surat']);

            $data['tanggal_indo'] = Carbon::parse($data['tanggal'])
                ->locale('id')
                ->settings(['formatFunction' => 'translatedFormat'])
                ->format('j F Y');
            $data['bln'] = Carbon::parse($data['tanggal'])->format('m');
            $data['created_by'] = Auth::id();

            $suratKeluar = SuratKeluar::create($data);

            return $this->success($suratKeluar, 'Nomor Surat '.$data['no_surat'].' berhasil disisipkan', 201);

        } catch (\Exception $e) {
            return $this->error('Failed to insert surat keluar', $e->getMessage(), 500);
        }
    }

    /**
     * Mendapatkan daftar tanggal yang tersedia untuk filter.
     *
     * Endpoint ini mengembalikan daftar tanggal unik dari data surat keluar
     * yang dapat digunakan untuk filtering. Hanya pengguna yang sudah login
     * yang dapat mengakses endpoint ini.
     *
     * @group Nomor Surat Keluar
     *
     * @authenticated
     *
     * @queryParam tahun string Filter berdasarkan tahun. Contoh: 2024
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
     * @return \Illuminate\Http\Response
     */
    public function getDates(Request $request)
    {
        $tahun = $request->get('tahun', Carbon::now()->format('Y'));

        $dates = SuratKeluar::where('thn', $tahun)
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
     * Endpoint ini mengembalikan daftar tahun unik dari data surat keluar
     * yang dapat digunakan untuk filtering. Hanya pengguna yang sudah login
     * yang dapat mengakses endpoint ini.
     *
     * @group Nomor Surat Keluar
     *
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
        $years = SuratKeluar::select('thn')
            ->groupBy('thn')
            ->orderBy('thn', 'desc')
            ->pluck('thn');

        return response()->json($years);
    }

    /**
     * Mendapatkan opsi formulir untuk pengaturan.
     *
     * Endpoint ini mengembalikan pengaturan yang memiliki grup 1 atau id 15,
     * serta nomor baru yang merupakan nomor maksimum + 1 untuk tahun saat ini.
     * Digunakan untuk mengisi opsi pada formulir surat keluar.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Nomor Surat Keluar
     *
     * @authenticated
     *
     * @queryParam tahun string Tahun untuk menghitung nomor baru (opsional, default: tahun saat ini). Contoh: 2024
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Form options retrieved successfully",
     *   "data": {
     *     "settings": [
     *       {
     *         "id": 1,
     *         "tahun": "2024",
     *         "key": "FORMAT_SURAT_KELUAR",
     *         "value": "{nomor}/SK/{tahun}",
     *         "grup": 1,
     *         "created_at": "2024-01-15T00:00:00.000000Z",
     *         "updated_at": "2024-01-15T00:00:00.000000Z"
     *       },
     *       {
     *         "id": 15,
     *         "tahun": "2024",
     *         "key": "SETTING_LAIN",
     *         "value": "nilai_setting",
     *         "grup": 2,
     *         "created_at": "2024-01-15T00:00:00.000000Z",
     *         "updated_at": "2024-01-15T00:00:00.000000Z"
     *       }
     *     ],
     *     "nomor_baru": 5
     *   }
     * }
     * @response 401 {
     *   "message": "Unauthorized"
     * }
     *
     * @return \Illuminate\Http\Response
     */
    public function formOptions(Request $request)
    {
        try {
            $settings = \App\Models\Setting::where('grup', 1)
                ->orWhere('grup', 2)
                ->orWhere('id', 15)
                ->orWhere('key', 'TEMA_KEGIATAN')
                ->get();

            // Get tahun from query parameter or default to current year
            $tahun = $request->get('tahun', Carbon::now()->format('Y'));

            // Get the maximum nomor for the specified year and add 1
            $maxNomor = SuratKeluar::where('thn', $tahun)
                ->max('nomor');

            // Calculate the next nomor, default to 1 if no records exist
            $nomorBaru = $maxNomor ? (int) $maxNomor + 1 : 1;

            return $this->success([
                'settings' => $settings,
                'nomor_baru' => $nomorBaru,
            ], 'Form options retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve form options', $e->getMessage(), 500);
        }
    }
}
