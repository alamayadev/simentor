<?php

namespace App\Http\Controllers\Api\Ipds;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\Request;
use App\Models\Tiket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use App\Http\Requests\StoreTiketRequest;
use App\Http\Requests\UpdateTiketRequest;
use App\Enums\JenisKeluhanType;

class TiketApiController extends BaseApiController
{
    /**
    * Daftar tiket dengan pagination dan opsi filter.
     *
     * Mengembalikan daftar tiket yang dipaginasi. Mendukung filter berdasarkan jenis_keluhan
     * dan parameter per_page. Hanya pengguna yang terautentikasi yang dapat mengakses endpoint ini.
     *
    * @group IPDS - Tiket
    * @authenticated
     *
     * @queryParam per_page int Jumlah item per halaman (default: 10). Contoh: 10
     * @queryParam cursor string Cursor untuk pagination. Contoh: eyJpZCI6M...
     * @queryParam filter[jenis_keluhan] string Filter berdasarkan jenis_keluhan. Contoh: "AC"
     * @queryParam sort string Sort field. Default: -created_at. Example: status
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data retrieved successfully",
     *   "data": [
     *     {
     *       "id": 123,
     *       "jenis_keluhan": "AC not cooling",
     *       "deskripsi": "...",
     *       "status": "open",
     *       "keterangan": null,
     *       "user_id": 5,
     *       "ditangani_oleh": null,
     *       "created_at": "2025-01-01T18:55:35.000000Z",
     *       "updated_at": "2025-01-01T18:55:35.000000Z",
     *       "user": {
     *         "id": 5,
     *         "name": "John"
     *       }
     *     }
     *   ],
     *   "meta": {
     *     "per_page": 10,
     *     "has_more": true,
     *     "count": 10
     *   },
     *   "links": {
     *     "next_cursor": "...",
     *     "next_page_url": "http://127.0.0.1:8000/api/ipds/tiket?cursor=...",
     *     "prev_cursor": null,
     *     "prev_page_url": null,
     *     "path": "http://127.0.0.1:8000/api/ipds/tiket"
     *   },
     *   "pagination_info": {
     *     "total_page": 2,
     *     "total_records": 12
     *   }
     * }
     * @response 401 {
     *   "message": "Unauthorized"
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        
        // Prepare query for tickets
        $tiketQuery = Tiket::with('user');
        
        // Get total count for pagination info (separate query for performance)
        $totalRecords = $tiketQuery->count();

        $tikets = \Spatie\QueryBuilder\QueryBuilder::for($tiketQuery)
            ->allowedFilters([
                \Spatie\QueryBuilder\AllowedFilter::partial('jenis_keluhan'),
            ])
            ->defaultSort('-created_at')
            ->allowedSorts(['created_at', 'status', 'jenis_keluhan'])
            ->fastPaginate($perPage);

        // Add pagination extras including total_page
        $totalPages = $perPage > 0 ? ceil($totalRecords / $perPage) : 1;
        $extras = [
            'pagination_info' => [
                'total_page' => $totalPages,
                'total_records' => $totalRecords,
            ]
        ];

        return $this->success($tikets, 'Data retrieved successfully', 200, $extras);
    }

    /**
    * Buat tiket baru.
     *
     * Membuat record tiket baru. Tiket baru akan diberi status default "open".
     * Hanya pengguna yang terautentikasi yang dapat membuat tiket.
     *
    * @group IPDS - Tiket
    * @authenticated
     *
     * @bodyParam jenis_keluhan string required Jenis keluhan. Contoh: "AC not cooling"
     * @bodyParam deskripsi string required Deskripsi detil. Contoh: "AC in room 101 not cooling"
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Tiket created successfully",
     *   "data": {
     *     "id": 124,
     *     "jenis_keluhan": "AC not cooling",
     *     "deskripsi": "Room 101",
     *     "status": "open",
     *     "keterangan": null,
     *     "user_id": 5,
     *     "ditangani_oleh": null,
     *     "created_at": "2025-01-01T18:55:35.000000Z",
     *     "updated_at": "2025-01-01T18:55:35.000000Z"
     *   }
     * }
     * @response 401 {
     *   "message": "Unauthorized"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "jenis_keluhan": [
     *       "The selected jenis keluhan is invalid."
     *     ]
     *   }
     * }
     *
     * @param  \App\Http\Requests\StoreTiketRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTiketRequest $request)
    {
        $data = $request->only(['jenis_keluhan', 'deskripsi']);
        // default status for new tiket
        $data['status'] = 'open';
        $data['user_id'] = Auth::id();

        $tiket = Tiket::create($data);

        // Send notification via Fonnte API for new tiket
        $this->sendFonnteNotification($tiket, 'new');

        return $this->success($tiket, 'Tiket created successfully', 201);
    }

    /**
    * Tampilkan detail tiket.
     *
     * Mengembalikan detail lengkap tiket termasuk relasi user dan responder.
     * Hanya pengguna yang terautentikasi yang dapat mengakses endpoint ini.
     *
    * @group IPDS - Tiket
    * @authenticated
     *
     * @urlParam id int required ID tiket. Contoh: 124
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data retrieved successfully",
     *   "data": {
     *     "id": 124,
     *     "jenis_keluhan": "AC not cooling",
     *     "deskripsi": "Room 101",
     *     "status": "open",
     *     "user": {"id":5, "name":"John"},
     *     "responder": null
     *   }
     * }
     * @response 401 {
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Tiket not found"
     * }
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $tiket = Tiket::with(['user','responder'])->find($id);

        if (!$tiket) {
            return $this->error('Tiket not found', null, 404);
        }

        return $this->success($tiket, 'Data retrieved successfully');
    }

    /**
    * Perbarui status atau keterangan tiket.
     *
     * Memperbarui field tiket seperti status dan keterangan. Pengguna yang saat ini
     * terautentikasi akan dicatat sebagai ditangani_oleh saat memperbarui.
     *
    * @group IPDS - Tiket
    * @authenticated
     *
     * @urlParam id int required ID tiket. Contoh: 124
     * @bodyParam status string Status tiket. Contoh: "in_progress"
     * @bodyParam keterangan string Catatan tambahan (opsional). Contoh: "Assigned to technician"
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Tiket updated successfully",
     *   "data": {
     *     "id": 124,
     *     "jenis_keluhan": "AC not cooling",
     *     "deskripsi": "Room 101",
     *     "status": "in_progress",
     *     "keterangan": "Assigned to technician",
     *     "user_id": 5,
     *     "ditangani_oleh": 6,
     *     "created_at": "2025-01-01T18:55:35.000000Z",
     *     "updated_at": "2025-01-01T19:00:00.000000Z"
     *   }
     * }
     * @response 401 {
     *   "message": "Unauthorized"
     * }
     * @response 403 {
     *   "message": "This action is unauthorized."
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Tiket not found"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "status": [
     *       "The status must be a string."
     *     ]
     *   }
     * }
     *
     * @param  \App\Http\Requests\UpdateTiketRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTiketRequest $request, $id)
    {
        $tiket = Tiket::find($id);

        if (!$tiket) {
            return $this->error('Tiket not found', null, 404);
        }

        $this->authorize('update', $tiket);

        $data = $request->only(['status', 'keterangan']);

        // set responder
        $data['ditangani_oleh'] = Auth::id();

        $tiket->update($data);

        // Send notification via Fonnte API for updated tiket
        $this->sendFonnteNotification($tiket, 'update');

        return $this->success($tiket, 'Tiket updated successfully');
    }

    /**
    * Hapus tiket.
     *
     * Menghapus tiket dengan ID yang diberikan. Hanya role yang berwenang yang dapat menghapus.
     *
    * @group IPDS - Tiket
     * @authenticated
     *
     * @urlParam id int required ID tiket. Contoh: 124
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Tiket deleted successfully",
     *   "data": null
     * }
     * @response 401 {
     *   "message": "Unauthorized"
     * }
     * @response 403 {
     *   "message": "This action is unauthorized."
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Tiket not found"
     * }
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $tiket = Tiket::find($id);

        if (!$tiket) {
            return $this->error('Tiket not found', null, 404);
        }

        $this->authorize('delete', $tiket);
        $tiket->delete();
        return $this->success(null, 'Tiket deleted successfully');
    }

    /**
     * Get available jenis keluhan options
     *
     * @group IPDS Tiket
     * @authenticated false
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Options retrieved successfully",
     *   "data": [
     *     "Sistem",
     *     "Software",
     *     "Printer",
     *     "Hardware PC/Laptop",
     *     "Jaringan",
     *     "Akun BPS"
     *   ]
     * }
     * @return \Illuminate\Http\Response
     */
    public function keluhanOptions()
    {
        return $this->success(
            JenisKeluhanType::all(),
            'Options retrieved successfully'
        );
    }

    /**
     * Get tiket statistics
     *
     * @group IPDS - Tiket
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Statistics retrieved successfully",
     *   "data": {
     *     "total_tiket": 25,
     *     "status_counts": {
     *       "open": 10,
     *       "in_progress": 8,
     *       "closed": 7
     *     }
     *   }
     * }
     * @response 401 {
     *   "message": "Unauthorized"
     * }
     * @return \Illuminate\Http\Response
     */
    public function statistics()
    {
        $totalTiket = Tiket::count();

        $statusCounts = Tiket::select('status')
            ->selectRaw('count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $statistics = [
            'total_tiket' => $totalTiket,
            'status_counts' => $statusCounts,
        ];

        return $this->success($statistics, 'Statistics retrieved successfully');
    }

    /**
     * Send notification via Fonnte API
     *
     * @param Tiket $tiket
     * @param string $type Type of notification ('new' or 'update')
     * @return void
     */
    private function sendFonnteNotification(Tiket $tiket, string $type = 'new')
    {
        try {
            $fonnte_token = Config::get('fonnte.token');
            if (!$fonnte_token) {
                Log::warning('Fonnte token not configured. Skipping notification.');
                return;
            }

            if ($type === 'new') {
                $user = Auth::user()->pegawai->nama ?? Auth::user()->name ?? 'Unknown User';
                $jenis_keluhan = $tiket->jenis_keluhan;
                $detil = $tiket->deskripsi;
                $message = 'Ada Tiket Baru dari '.$user.' keluhan: '.$jenis_keluhan.' Isi keluhan: '.$detil;
            } else {
                // Get user who created the ticket
                $user = $tiket->user->pegawai->nama ?? $tiket->user->name ?? 'Unknown User';
                $jenis_keluhan = $tiket->jenis_keluhan;
                $handleBy = Auth::user()->pegawai->nama ?? Auth::user()->name ?? 'Unknown Handler';
                $keterangan = $tiket->keterangan ?? 'No description';
                $message = 'Tiket Keluhan '.$jenis_keluhan.' dari '.$user.' sudah ditangani oleh '.$handleBy.' dengan keterangan '.$keterangan;
            }

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.fonnte.com/send',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array(
                    'target' => '6285781781754-1459328832@g.us',
                    'message' => $message,
                    'schedule' => 0,
                    'typing' => false,
                    'delay' => '2',
                    'countryCode' => '62',
                ),
                CURLOPT_HTTPHEADER => array(
                    'Authorization: '.$fonnte_token
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
        } catch (\Exception $e) {
            // Log the error but don't fail the request
            Log::error('Fonnte notification failed: ' . $e->getMessage());
        }
    }
}
