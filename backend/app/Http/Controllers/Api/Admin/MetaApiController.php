<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Meta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MetaApiController extends BaseApiController
{
    /**
     * Menampilkan daftar meta.
     *
     * Endpoint ini mengembalikan daftar metadata. Secara default mengembalikan top-level parents dengan children mereka.
     * Jika `parent_id` disediakan, akan mengembalikan parent tersebut dengan children-nya.
     *
     * @group Admin Meta
     * @authenticated
     *
     * @queryParam parent_id int ID parent untuk mengambil parent tertentu dengan children-nya. Contoh: 1
     * @queryParam search string Pencarian berdasarkan nama meta. Contoh: Fungsi
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Metas retrieved successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "parent_id": null,
     *       "name": "Fungsi",
     *       "name2": null,
     *       "created_at": "2026-04-28T12:06:46.000000Z",
     *       "updated_at": "2026-04-28T12:06:46.000000Z",
     *       "children": [
     *         {
     *           "id": 2,
     *           "parent_id": 1,
     *           "name": "Umum",
     *           "name2": null,
     *           "created_at": "2026-04-28T12:06:46.000000Z",
     *           "updated_at": "2026-04-28T12:06:46.000000Z"
     *         }
     *       ]
     *     }
     *   ]
     * }
     */
    public function index(Request $request)
    {
        $query = Meta::with('children');

        // If a parent_id is provided, get that specific parent with its children
        if ($request->has('parent_id')) {
            $query->where('id', $request->parent_id);
        } else {
            // Otherwise, get all top-level parents with their children
            $query->whereNull('parent_id');
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $metas = $query->get();

        return $this->success($metas, 'Metas retrieved successfully');
    }

    /**
     * Menampilkan pohon meta (hierarkis).
     *
     * Endpoint ini mengembalikan seluruh struktur pohon metadata mulai dari top-level.
     *
     * @group Admin Meta
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Meta tree retrieved successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "parent_id": null,
     *       "name": "Fungsi",
     *       "children": [...]
     *     }
     *   ]
     * }
     */
    public function tree()
    {
        $metas = Meta::whereNull('parent_id')->with('children')->get();
        return $this->success($metas, 'Meta tree retrieved successfully');
    }

    /**
     * Simpan meta baru.
     *
     * Endpoint ini digunakan untuk membuat metadata baru.
     *
     * @group Admin Meta
     * @authenticated
     *
     * @bodyParam parent_id int ID parent (opsional). Contoh: 1
     * @bodyParam name string required Nama meta. Contoh: Sub-Fungsi Baru
     * @bodyParam name2 string Nama alternatif/tambahan (opsional).
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Meta created successfully",
     *   "data": {
     *     "id": 116,
     *     "parent_id": 1,
     *     "name": "Sub-Fungsi Baru",
     *     "name2": null,
     *     "updated_at": "2026-04-28T12:10:00.000000Z",
     *     "created_at": "2026-04-28T12:10:00.000000Z"
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation Error",
     *   "errors": {
     *     "name": ["The name field is required."]
     *   }
     * }
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'parent_id' => 'nullable|exists:metas,id',
            'name' => 'required|string|max:255',
            'name2' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation Error', $validator->errors(), 422);
        }

        $meta = Meta::create($request->all());

        return $this->success($meta, 'Meta created successfully', 201);
    }

    /**
     * Detail meta.
     *
     * Endpoint ini mengembalikan detail dari sebuah item metadata beserta children-nya.
     *
     * @group Admin Meta
     * @authenticated
     *
     * @urlParam id int required ID meta. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Meta retrieved successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "Fungsi",
     *     "children": [...]
     *   }
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Meta not found"
     * }
     */
    public function show($id)
    {
        $meta = Meta::with('children')->find($id);

        if (!$meta) {
            return $this->error('Meta not found', null, 404);
        }

        return $this->success($meta, 'Meta retrieved successfully');
    }

    /**
     * Update data meta.
     *
     * Endpoint ini digunakan untuk memperbarui data metadata yang sudah ada.
     *
     * @group Admin Meta
     * @authenticated
     *
     * @urlParam id int required ID meta yang akan diupdate. Contoh: 116
     *
     * @bodyParam parent_id int ID parent (opsional).
     * @bodyParam name string Nama meta.
     * @bodyParam name2 string Nama alternatif/tambahan (opsional).
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Meta updated successfully",
     *   "data": {...}
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Meta not found"
     * }
     */
    public function update(Request $request, $id)
    {
        $meta = Meta::find($id);

        if (!$meta) {
            return $this->error('Meta not found', null, 404);
        }

        $validator = Validator::make($request->all(), [
            'parent_id' => 'nullable|exists:metas,id',
            'name' => 'sometimes|required|string|max:255',
            'name2' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation Error', $validator->errors(), 422);
        }

        $meta->update($request->all());

        return $this->success($meta, 'Meta updated successfully');
    }

    /**
     * Hapus meta.
     *
     * Endpoint ini digunakan untuk menghapus metadata. Menghapus parent akan menghapus children-nya secara cascade.
     *
     * @group Admin Meta
     * @authenticated
     *
     * @urlParam id int required ID meta yang akan dihapus. Contoh: 116
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Meta deleted successfully",
     *   "data": null
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Meta not found"
     * }
     */
    public function destroy($id)
    {
        $meta = Meta::find($id);

        if (!$meta) {
            return $this->error('Meta not found', null, 404);
        }

        $meta->delete();

        return $this->success(null, 'Meta deleted successfully');
    }
}
