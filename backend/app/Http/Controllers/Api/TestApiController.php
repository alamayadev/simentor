<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;

class TestApiController extends BaseApiController
{
    public function pegawai(Request $request)
    {

        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);
        $search = $request->get('search');

        // Build the query with Spatie Query Builder
        // Get base query for counting
        $baseQuery = Pegawai::whereNull('status');

        // Apply same filters for counting
        if ($search) {
            $baseQuery->where(function ($query) use ($search) {
                $query->where('nama', 'LIKE', "%{$search}%")
                      ->orWhere('nip', 'LIKE', "%{$search}%");
            });
        }
        
        if ($request->get('filter.pangkat')) {
            $baseQuery->where('pangkat', $request->get('filter.pangkat'));
        }
        
        if ($request->get('filter.jabatan')) {
            $baseQuery->where('jabatan', $request->get('filter.jabatan'));
        }

        // Get total count for pagination info (separate query for performance)
        $totalRecords = $baseQuery->count();

        $pegawai = QueryBuilder::for(Pegawai::class)
            ->whereNull('status')
            ->with('user')
            ->allowedFilters(['pangkat', 'jabatan']);

        // Add search functionality for nama and nip
        if ($search) {
            $pegawai->where(function ($query) use ($search) {
                $query->where('nama', 'LIKE', "%{$search}%")
                      ->orWhere('nip', 'LIKE', "%{$search}%");
            });
        }

        $pegawai = $pegawai->fastPaginate($perPage);

        // Add pagination extras including total_page
        $totalPages = $perPage > 0 ? ceil($totalRecords / $perPage) : 1;
        $extras = [
            'pagination_info' => [
                'total_page' => $totalPages,
                'total_records' => $totalRecords,
            ]
        ];

        return $this->success($pegawai, 'Data retrieved successfully', 200, $extras);
    }


    public function getJabatanPangkatList()
    {

        $jabatanList = Pegawai::select('jabatan')
            ->whereNotNull('jabatan')
            ->distinct()
            ->orderBy('jabatan')
            ->pluck('jabatan');

        $pangkatList = Pegawai::select('pangkat')
            ->whereNotNull('pangkat')
            ->distinct()
            ->orderBy('pangkat')
            ->pluck('pangkat');

        return $this->success([
            'jabatanList' => $jabatanList,
            'pangkatList' => $pangkatList
        ]);
    }
}
