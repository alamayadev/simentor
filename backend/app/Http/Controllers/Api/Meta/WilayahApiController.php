<?php

namespace App\Http\Controllers\Api\Meta;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group Meta - Wilayah
 * 
 * APIs for finding geographical areas based on specific criteria.
 * 
 * @unauthenticated
 */
class WilayahApiController extends Controller
{
    public function kecamatan(Request $request)
    {
        $kdprov = trim($request->query('kdprov'), "\"'");
        $kdkab = trim($request->query('kdkab'), "\"'");

        if (!$kdprov || !$kdkab) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter kdprov dan kdkab wajib diisi'
            ], 400);
        }

        $kecamatan = DB::table('sls_sipw')
            ->select('kdkec as value', 'nmkec as text')
            ->where('kdprov', $kdprov)
            ->where('kdkab', $kdkab)
            ->distinct()
            ->orderBy('kdkec')
            ->get()
            ->map(function ($item) {
                $item->text = "[$item->value] $item->text";
                return $item;
            });

        return response()->json($kecamatan);
    }
}
