<?php

namespace App\Http\Controllers\Api\Kantor;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Link;
use Illuminate\Http\Request;
use App\Http\Requests\LinkRequest;
use Illuminate\Http\JsonResponse;

/**
 * Link API Controller
 *
 * Simple CRUD for Link entities with hierarchical structure support.
 *
 * @group Links
 * @authenticated
 */
class LinkApiController extends BaseApiController
{
    /**
     * List Links
     *
     * Get a hierarchical list of links. Root links (without parent) are returned with their children.
     * Supports pagination and filtering by name.
     *
     * @queryParam per_page integer Number of items per page (max 100). Example: 15
     * @queryParam cursor string Cursor for pagination. Example: eyJpZCI6M...
     * @queryParam filter[nama] string Filter links by name. Example: External
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Links retrieved successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "parent_id": null,
     *       "nama": "External Services",
     *       "link": null,
     *       "created_at": "2024-01-01T00:00:00.000000Z",
     *       "updated_at": "2024-01-01T00:00:00.000000Z",
     *       "children": [
     *         {
     *           "id": 2,
     *           "parent_id": 1,
     *           "nama": "Data Portal",
     *           "link": "https://data.example.com",
     *           "created_at": "2024-01-02T00:00:00.000000Z",
     *           "updated_at": "2024-01-02T00:00:00.000000Z",
     *           "children": []
     *         }
     *       ]
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
     *     "path": "http://localhost:8000/api/kantor/links"
     *   },
     *   "pagination_info": {
     *     "total_page": 1,
     *     "total_records": 1
     *   }
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 9);
        $perPage = min($perPage, 100); // Max 100 items per page

        $query = Link::whereNull('parent_id')
            ->with('childrenRecursive')
            ->orderBy('id', 'asc');

        // Apply name filter if provided
        if ($request->filled('filter.nama')) {
            $query->where('nama', 'LIKE', '%' . $request->input('filter.nama') . '%');
        }

        // Get total count for pagination info (separate query for performance)
        $totalRecords = $query->count();

        $links = $query->fastPaginate($perPage);

        // Add pagination extras including total_page
        $totalPages = $perPage > 0 ? ceil($totalRecords / $perPage) : 1;
        $extras = [
            'pagination_info' => [
                'total_page' => $totalPages,
                'total_records' => $totalRecords,
            ]
        ];

        return $this->success($links, 'Links retrieved successfully', 200, $extras);
    }

    /**
     * Create Link
     *
     * Create a new link. Can be a root link (parent_id=null) or a child link.
     *
     * @bodyParam nama string required The name of the link. Example: Data Portal
     * @bodyParam link string nullable The URL of the link. Example: https://data.example.com
     * @bodyParam parent_id integer nullable The parent link ID for hierarchical organization. Example: 1
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Link created successfully",
     *   "data": {
     *     "id": 3,
     *     "parent_id": 1,
     *     "nama": "Analytics Dashboard",
     *     "link": "https://analytics.example.com",
     *     "created_at": "2024-01-03T00:00:00.000000Z",
     *     "updated_at": "2024-01-03T00:00:00.000000Z"
     *   }
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "nama": [
     *       "The nama field is required."
     *     ]
     *   }
     * }
     */
    public function store(LinkRequest $request): JsonResponse
    {
        $link = Link::create($request->validated());
        return $this->success($link, 'Link created successfully', 201);
    }

    /**
     * Show Link
     *
     * Get details of a specific link with its children.
     *
     * @urlParam id integer required The ID of the link. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Link retrieved successfully",
     *   "data": {
     *     "id": 1,
     *     "parent_id": null,
     *     "nama": "External Services",
     *     "link": null,
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z",
     *     "children": [
     *       {
     *         "id": 2,
     *         "parent_id": 1,
     *         "nama": "Data Portal",
     *         "link": "https://data.example.com",
     *         "created_at": "2024-01-02T00:00:00.000000Z",
     *         "updated_at": "2024-01-02T00:00:00.000000Z",
     *         "children": []
     *       }
     *     ]
     *   }
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "message": "Link not found"
     * }
     */
    public function show($id): JsonResponse
    {
        $link = Link::with('childrenRecursive')->find($id);

        if (!$link) {
            return $this->error('Link not found', null, 404);
        }

        return $this->success($link, 'Link retrieved successfully');
    }

    /**
     * Update Link
     *
     * Update an existing link's information.
     *
     * @urlParam id integer required The ID of the link. Example: 1
     * @bodyParam nama string The name of the link. Example: Updated External Services
     * @bodyParam link string nullable The URL of the link. Example: https://updated.example.com
     * @bodyParam parent_id integer nullable The parent link ID for hierarchical organization. Example: null
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Link updated successfully",
     *   "data": {
     *     "id": 1,
     *     "parent_id": null,
     *     "nama": "Updated External Services",
     *     "link": "https://updated.example.com",
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-03T00:00:00.000000Z"
     *   }
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "message": "Link not found"
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "link": [
     *       "The link must be a valid URL."
     *     ]
     *   }
     * }
     */
    public function update(LinkRequest $request, $id): JsonResponse
    {
        $link = Link::find($id);

        if (!$link) {
            return $this->error('Link not found', null, 404);
        }

        $link->update($request->validated());
        return $this->success($link, 'Link updated successfully');
    }

    /**
     * Delete Link
     *
     * Delete a link and all its children recursively.
     *
     * @urlParam id integer required The ID of the link. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Link deleted successfully",
     *   "data": null
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "message": "Link not found"
     * }
     */
    public function destroy($id): JsonResponse
    {
        // PERFORMANCE FIX: Eager load children to prevent N+1 queries during recursive deletion
        $link = Link::with('childrenRecursive')->find($id);
        
        if (!$link) {
            return $this->error('Link not found', null, 404);
        }
        
        // Delete all children recursively
        $this->deleteChildrenRecursive($link);
        
        $link->delete();
        return $this->success(null, 'Link deleted successfully');
    }

    /**
     * Recursively delete children links
     *
     * @param Link $link
     * @return void
     */
    private function deleteChildrenRecursive(Link $link): void
    {
        foreach ($link->childrenRecursive as $child) {
            $this->deleteChildrenRecursive($child);
            $child->delete();
        }
    }
}
