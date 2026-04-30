<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DomPDFController;
use App\Models\Penugasan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use ZipArchive;

class PdfApiController extends BaseApiController
{
    /**
     * Menghasilkan PDF SPK untuk penugasan mitra.
     *
     * Endpoint ini menghasilkan dokumen SPK (Surat Perintah Kerja) dalam format PDF untuk penugasan mitra tertentu.
     * Hanya pengguna yang sudah terotentikasi yang dapat mengakses endpoint ini.
     *
     * @group PDF
     *
     * @authenticated
     *
     * @urlParam id int required ID penugasan. Contoh: 1
     *
     * @response 200 {
     *   "message": "SPK PDF berhasil dibuat"
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Penugasan tidak ditemukan"
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Kesalahan internal server"
     * }
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function generateSPK($id)
    {
        try {
            $user = Auth::user();
            if (! $user) {
                return $this->error('Unauthorized', null, 401);
            }

            // Check if penugasan exists
            $penugasan = Penugasan::find($id);
            if (! $penugasan) {
                return $this->error('Penugasan not found', null, 404);
            }

            // Check if required fields for SPK are present
            if (empty($penugasan->no_sk) || empty($penugasan->tgl_sk) || empty($penugasan->jangka_waktu_mulai) || empty($penugasan->jangka_waktu_selesai)) {
                return $this->error('Penugasan missing required fields for SPK generation', null, 422);
            }

            // Create a new request with the penugasan ID
            $pdfRequest = new Request(['id' => $id]);

            // Instantiate the domPDFController
            $pdfController = new domPDFController($pdfRequest);

            // Generate the SPK PDF
            $response = $pdfController->generateSPK($pdfRequest);

            // Return the PDF response
            return $response;
        } catch (\Exception $e) {
            return $this->error('Internal server error: '.$e->getMessage(), null, 500);
        }
    }

    /**
     * Menghasilkan PDF BAST untuk penugasan mitra.
     *
     * Endpoint ini menghasilkan dokumen BAST (Berita Acara Serah Terima) dalam format PDF untuk penugasan mitra tertentu.
     * Hanya pengguna yang sudah terotentikasi yang dapat mengakses endpoint ini.
     *
     * @group PDF
     *
     * @authenticated
     *
     * @urlParam id int required ID penugasan. Contoh: 1
     *
     * @response 200 {
     *   "message": "BAST PDF berhasil dibuat"
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Penugasan tidak ditemukan"
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Kesalahan internal server"
     * }
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function generateBAST($id)
    {
        try {
            $user = Auth::user();
            if (! $user) {
                return $this->error('Unauthorized', null, 401);
            }

            // Check if penugasan exists
            $penugasan = Penugasan::find($id);
            if (! $penugasan) {
                return $this->error('Penugasan not found', null, 404);
            }

            // Check if required fields for BAST are present
            if (empty($penugasan->no_bast) || empty($penugasan->tgl_bast)) {
                return $this->error('Penugasan missing required fields for BAST generation', null, 422);
            }

            // Create a new request with the penugasan ID
            $pdfRequest = new Request(['id' => $id]);

            // Instantiate the domPDFController
            $pdfController = new domPDFController($pdfRequest);

            // Generate the BAST PDF
            $response = $pdfController->generateBAST($pdfRequest);

            // Return the PDF response
            return $response;
        } catch (\Exception $e) {
            return $this->error('Internal server error: '.$e->getMessage(), null, 500);
        }
    }

    /**
     * Bulk generate SPK PDFs and return as ZIP file.
     *
     * Endpoint ini menghasilkan dokumen SPK (Surat Perintah Kerja) dalam format PDF untuk beberapa penugasan sekaligus
     * dan mengembalikannya sebagai file ZIP. Hanya pengguna yang sudah terotentikasi yang dapat mengakses endpoint ini.
     *
     * @group PDF
     *
     * @authenticated
     *
     * @bodyParam ids array required Array of penugasan IDs. Contoh: [1, 2, 3]
     * @bodyParam ids.* int ID penugasan. Contoh: 1
     *
     * @response 200 {
     *   "message": "SPK PDFs generated successfully"
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation error",
     *   "errors": {
     *     "ids": ["The ids field is required."]
     *   }
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Internal server error"
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function bulkGenerateSPK(Request $request)
    {
        try {
            $user = Auth::user();
            if (! $user) {
                return $this->error('Unauthorized', null, 401);
            }

            // Validate input
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:penugasan,id',
            ]);

            if ($validator->fails()) {
                return $this->error('Validation error', $validator->errors(), 422);
            }

            $ids = $request->input('ids');

            // Create temporary ZIP file
            $zipFileName = 'SPK_Bulk_' . time() . '.zip';
            $zipFilePath = storage_path('app/temp/' . $zipFileName);

            // Ensure temp directory exists
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            $zip = new ZipArchive();
            if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
                return $this->error('Could not create ZIP file', null, 500);
            }

            $successful = 0;
            $errors = [];

            // Generate PDF for each ID
            foreach ($ids as $id) {
                try {
                    // Check if penugasan exists
                    $penugasan = Penugasan::find($id);
                    if (! $penugasan) {
                        $errors[] = "Penugasan with ID {$id} not found";
                        continue;
                    }

                    // Check if required fields for SPK are present
                    if (empty($penugasan->no_sk) || empty($penugasan->tgl_sk) || empty($penugasan->jangka_waktu_mulai) || empty($penugasan->jangka_waktu_selesai)) {
                        $errors[] = "Penugasan with ID {$id} missing required fields for SPK generation";
                        continue;
                    }

                    // Create a new request with the penugasan ID
                    $pdfRequest = new Request(['id' => $id]);

                    // Instantiate the domPDFController
                    $pdfController = new domPDFController($pdfRequest);

                    // Generate the SPK PDF
                    $pdfResponse = $pdfController->generateSPK($pdfRequest);

                    // Get PDF content
                    $pdfContent = $pdfResponse->getContent();

                    // Generate file name
                    $fileName = sprintf('SPK_%04d_%s.pdf', $penugasan->no_sk, $penugasan->mitra->nik ?? 'unknown');

                    // Add PDF to ZIP
                    $zip->addFromString($fileName, $pdfContent);
                    $successful++;
                } catch (\Exception $e) {
                    $errors[] = "Error generating SPK for ID {$id}: " . $e->getMessage();
                }
            }

            $zip->close();

            // Check if any PDFs were generated successfully
            if ($successful === 0) {
                // Clean up
                if (file_exists($zipFilePath)) {
                    unlink($zipFilePath);
                }

                return $this->error('No PDFs could be generated', $errors, 422);
            }

            // Return ZIP file
            return response()->download($zipFilePath, $zipFileName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return $this->error('Internal server error: '.$e->getMessage(), null, 500);
        }
    }

    /**
     * Bulk generate BAST PDFs and return as ZIP file.
     *
     * Endpoint ini menghasilkan dokumen BAST (Berita Acara Serah Terima) dalam format PDF untuk beberapa penugasan sekaligus
     * dan mengembalikannya sebagai file ZIP. Hanya pengguna yang sudah terotentikasi yang dapat mengakses endpoint ini.
     *
     * @group PDF
     *
     * @authenticated
     *
     * @bodyParam ids array required Array of penugasan IDs. Contoh: [1, 2, 3]
     * @bodyParam ids.* int ID penugasan. Contoh: 1
     *
     * @response 200 {
     *   "message": "BAST PDFs generated successfully"
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation error",
     *   "errors": {
     *     "ids": ["The ids field is required."]
     *   }
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Internal server error"
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function bulkGenerateBAST(Request $request)
    {
        try {
            $user = Auth::user();
            if (! $user) {
                return $this->error('Unauthorized', null, 401);
            }

            // Validate input
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:penugasan,id',
            ]);

            if ($validator->fails()) {
                return $this->error('Validation error', $validator->errors(), 422);
            }

            $ids = $request->input('ids');

            // Create temporary ZIP file
            $zipFileName = 'BAST_Bulk_' . time() . '.zip';
            $zipFilePath = storage_path('app/temp/' . $zipFileName);

            // Ensure temp directory exists
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            $zip = new ZipArchive();
            if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
                return $this->error('Could not create ZIP file', null, 500);
            }

            $successful = 0;
            $errors = [];

            // Generate PDF for each ID
            foreach ($ids as $id) {
                try {
                    // Check if penugasan exists
                    $penugasan = Penugasan::find($id);
                    if (! $penugasan) {
                        $errors[] = "Penugasan with ID {$id} not found";
                        continue;
                    }

                    // Check if required fields for BAST are present
                    if (empty($penugasan->no_bast) || empty($penugasan->tgl_bast)) {
                        $errors[] = "Penugasan with ID {$id} missing required fields for BAST generation";
                        continue;
                    }

                    // Create a new request with the penugasan ID
                    $pdfRequest = new Request(['id' => $id]);

                    // Instantiate the domPDFController
                    $pdfController = new domPDFController($pdfRequest);

                    // Generate the BAST PDF
                    $pdfResponse = $pdfController->generateBAST($pdfRequest);

                    // Get PDF content
                    $pdfContent = $pdfResponse->getContent();

                    // Generate file name
                    $fileName = sprintf('BAST_%04d_%s.pdf', $penugasan->no_bast, $penugasan->mitra->nik ?? 'unknown');

                    // Add PDF to ZIP
                    $zip->addFromString($fileName, $pdfContent);
                    $successful++;
                } catch (\Exception $e) {
                    $errors[] = "Error generating BAST for ID {$id}: " . $e->getMessage();
                }
            }

            $zip->close();

            // Check if any PDFs were generated successfully
            if ($successful === 0) {
                // Clean up
                if (file_exists($zipFilePath)) {
                    unlink($zipFilePath);
                }

                return $this->error('No PDFs could be generated', $errors, 422);
            }

            // Return ZIP file
            return response()->download($zipFilePath, $zipFileName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return $this->error('Internal server error: '.$e->getMessage(), null, 500);
        }
    }
}
