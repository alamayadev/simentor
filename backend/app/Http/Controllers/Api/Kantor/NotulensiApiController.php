<?php

namespace App\Http\Controllers\Api\Kantor;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Notulensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use PhpOffice\PhpWord\TemplateProcessor;

/**
 * @group Kantor - Notulensi
 *
 * APIs for managing meeting minutes (Notulensi) including CRUD operations and document generation.
 */
class NotulensiApiController extends BaseApiController
{
    /**
     * List all meeting minutes
     *
     * Retrieve a list of all meeting minutes with related pimpinan and notulis data.
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Notulensi list retrieved successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "judul": "Rapat Pleno Bulanan",
     *       "instansi": "BPS Kabupaten Karawang",
     *       "kegiatan": "Pembangunan Dashboard Simentor",
     *       "topik": "Pembahasan Progres Simentor v1.5",
     *       "tanggal_rapat": "2026-01-20",
     *       "waktu_mulai": "08:30:00",
     *       "waktu_selesai": "11:15:00",
     *       "tempat": "Ruang Rapat Utama",
     *       "pimpinan_id": 1,
     *       "notulis_id": 2,
     *       "nip_pimpinan": "198001012005011001",
     *       "nip_notulis": "199001012015011002",
     *       "jabatan_pimpinan_rapat": "Kepala BPS Kabupaten Karawang",
     *       "peserta": ["Ahmad Rizal", "Siti Nurhaliza", "Budi Santoso"],
     *       "agenda": "<p>Pembahasan progres pengembangan</p>",
     *       "resume": "<p>Progres mencapai 80%</p>",
     *       "tanya_jawab": "<p>Q: Kapan selesai? A: Akhir Januari</p>",
     *       "kategori": 3,
     *       "pimpinan": {
     *         "id": 1,
     *         "nama": "Dr. Ahmad Wijaya",
     *         "nip": "198001012005011001"
     *       },
     *       "notulis": {
     *         "id": 2,
     *         "nama": "Siti Aminah",
     *         "nip": "199001012015011002"
     *       }
     *     }
     *   ]
     * }
     */
    public function index()
    {
        $notulensis = Notulensi::with(['pimpinan:id,nama,nip', 'notulis:id,nama,nip'])
            ->orderBy('created_at', 'desc')
            ->select([
                'id', 'judul', 'instansi', 'kegiatan', 'topik',
                'tanggal_rapat', 'waktu_mulai', 'waktu_selesai', 'tempat',
                'pimpinan_id', 'nip_pimpinan', 'notulis_id', 'nip_notulis',
                'kategori', 'peserta', 'created_at', 'updated_at'
            ]);

        $perPage = request()->get('per_page', 20);

        // PERFORMANCE OPTIMIZATION: Use cursor pagination for large datasets
        // fastPaginate() is more efficient than get() for large tables (>1000 records)
        // It uses database cursors instead of OFFSET, which scans all previous records
        $notulensis = $notulensis->fastPaginate($perPage);

        return $this->success($notulensis,'Notulensi list retrieved successfully');
    }

    /**
     * Create a new meeting minute
     *
     * Store a newly created meeting minute in the database.
     *
     * @bodyParam judul string required The title of the meeting. Example: Rapat Pleno Bulanan
     * @bodyParam instansi string optional The institution name. Example: BPS Kabupaten Karawang
     * @bodyParam kegiatan string optional The activity name. Example: Pembangunan Dashboard Simentor
     * @bodyParam topik string optional The topic of discussion. Example: Pembahasan Progres Simentor v1.5
     * @bodyParam tanggal_rapat date optional Meeting date. Example: 2026-01-20
     * @bodyParam waktu_mulai time optional Start time (HH:MM:SS). Example: 08:30:00
     * @bodyParam waktu_selesai time optional End time (HH:MM:SS). Example: 11:15:00
     * @bodyParam tempat string optional Meeting location. Example: Ruang Rapat Utama
     * @bodyParam pimpinan_id integer optional ID of the meeting chairman from profil_pegawai. Example: 1
     * @bodyParam notulis_id integer optional ID of the meeting secretary from profil_pegawai. Example: 2
     * @bodyParam nip_pimpinan string optional NIP of the chairman. Example: 198001012005011001
     * @bodyParam nip_notulis string optional NIP of the secretary. Example: 199001012015011002
     * @bodyParam jabatan_pimpinan_rapat string optional Position of the chairman. Example: Kepala BPS Kabupaten Karawang
     * @bodyParam peserta array optional List of participant names. Example: ["Ahmad Rizal", "Siti Nurhaliza"]
     * @bodyParam agenda string optional Meeting agenda (HTML). Example: <p>Pembahasan progres</p>
     * @bodyParam resume string optional Meeting summary (HTML). Example: <p>Progres 80%</p>
     * @bodyParam tanya_jawab string optional Q&A section (HTML). Example: <p>Q: Kapan? A: Januari</p>
     * @bodyParam kategori integer optional Reform category (1-8). Example: 3
     *
     * @response 201 {
     *   "status": true,
     *   "message": "Notulensi created successfully",
     *   "data": {
     *     "id": 1,
     *     "judul": "Rapat Pleno Bulanan",
     *     "instansi": "BPS Kabupaten Karawang",
     *     "kegiatan": "Pembangunan Dashboard Simentor",
     *     "topik": "Pembahasan Progres Simentor v1.5",
     *     "created_at": "2026-01-13T20:00:00.000000Z"
     *   }
     * }
     *
     * @response 422 {
     *   "status": false,
     *   "message": "Validation error",
     *   "data": {
     *     "judul": ["The judul field is required."]
     *   }
     * }
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required|required|string|max:255',
            'instansi' => 'nullable|string|max:255',
            'kegiatan' => 'nullable|string|max:255',
            'tanggal_rapat' => 'required|date',
            'waktu_mulai' => 'required|date_format:H:i:s',
            'waktu_selesai' => 'required|date_format:H:i:s',
            'tempat' => 'required|string|max:255',
            'pimpinan_id' => 'required|exists:profil_pegawai,id',
            'notulis_id' => 'prohibited',
            'nip_pimpinan' => 'required|string|max:255',
            'nip_notulis' => 'prohibited',
            'peserta' => 'required|array',
            'agenda' => 'nullable|string',
            'resume' => 'nullable|string',
            'tanya_jawab' => 'nullable|string',
            'kategori' => 'required|integer|min:1|max:8',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', $validator->errors(), 422);
        }

        $notulensi = Notulensi::create($request->all());
        return $this->success('Notulensi created successfully', $notulensi, 201);
    }

    /**
     * Get a specific meeting minute
     *
     * Retrieve detailed information about a specific meeting minute including related pimpinan and notulis.
     *
     * @urlParam id integer required The ID of the meeting minute. Example: 1
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Notulensi details retrieved successfully",
     *   "data": {
     *     "id": 1,
     *     "judul": "Rapat Pleno Bulanan",
     *     "instansi": "BPS Kabupaten Karawang",
     *     "kegiatan": "Pembangunan Dashboard Simentor",
     *     "topik": "Pembahasan Progres Simentor v1.5",
     *     "tanggal_rapat": "2026-01-20",
     *     "waktu_mulai": "08:30:00",
     *     "waktu_selesai": "11:15:00",
     *     "tempat": "Ruang Rapat Utama",
     *     "pimpinan": {
     *       "id": 1,
     *       "nama": "Dr. Ahmad Wijaya",
     *       "nip": "198001012005011001"
     *     },
     *     "notulis": {
     *       "id": 2,
     *       "nama": "Siti Aminah",
     *       "nip": "199001012015011002"
     *     }
     *   }
     * }
     *
     * @response 404 {
     *   "status": false,
     *   "message": "Notulensi not found",
     *   "data": null
     * }
     */
    public function show($id)
    {
        $notulensi = Notulensi::with(['pimpinan', 'notulis'])->find($id);
        if (!$notulensi) {
            return $this->error('Notulensi not found', null, 404);
        }
        return $this->success($notulensi,'Notulensi details retrieved successfully');
    }

    /**
     * Update a meeting minute
     *
     * Update an existing meeting minute with new data.
     *
     * @urlParam id integer required The ID of the meeting minute. Example: 1
     * @bodyParam judul string optional The title of the meeting. Example: Rapat Pleno Bulanan
     * @bodyParam instansi string optional The institution name. Example: BPS Kabupaten Karawang
     * @bodyParam kegiatan string optional The activity name. Example: Pembangunan Dashboard Simentor
     * @bodyParam topik string optional The topic of discussion. Example: Pembahasan Progres Simentor v1.5
     * @bodyParam tanggal_rapat date optional Meeting date. Example: 2026-01-20
     * @bodyParam waktu_mulai time optional Start time (HH:MM:SS). Example: 08:30:00
     * @bodyParam waktu_selesai time optional End time (HH:MM:SS). Example: 11:15:00
     * @bodyParam tempat string optional Meeting location. Example: Ruang Rapat Utama
     * @bodyParam pimpinan_id integer optional ID of the meeting chairman. Example: 1
     * @bodyParam notulis_id integer optional ID of the meeting secretary. Example: 2
     * @bodyParam nip_pimpinan string optional NIP of the chairman. Example: 198001012005011001
     * @bodyParam nip_notulis string optional NIP of the secretary. Example: 199001012015011002
     * @bodyParam jabatan_pimpinan_rapat string optional Position of the chairman. Example: Kepala BPS
     * @bodyParam peserta array optional List of participant names. Example: ["Ahmad", "Siti"]
     * @bodyParam agenda string optional Meeting agenda (HTML). Example: <p>Updated agenda</p>
     * @bodyParam resume string optional Meeting summary (HTML). Example: <p>Updated summary</p>
     * @bodyParam tanya_jawab string optional Q&A section (HTML). Example: <p>Updated Q&A</p>
     * @bodyParam kategori integer optional Reform category (1-8). Example: 3
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Notulensi updated successfully",
     *   "data": {
     *     "id": 1,
     *     "judul": "Rapat Pleno Bulanan (Updated)",
     *     "updated_at": "2026-01-13T21:00:00.000000Z"
     *   }
     * }
     *
     * @response 404 {
     *   "status": false,
     *   "message": "Notulensi not found",
     *   "data": null
     * }
     */
    public function update(Request $request, $id)
    {
        $notulensi = Notulensi::find($id);
        if (!$notulensi) {
            return $this->error('Notulensi not found', null, 404);
        }

        $validator = Validator::make($request->all(), [
            'judul' => 'required|required|string|max:255',
            'instansi' => 'nullable|string|max:255',
            'kegiatan' => 'nullable|string|max:255',
            'tanggal_rapat' => 'required|date',
            'waktu_mulai' => 'required|date_format:H:i:s',
            'waktu_selesai' => 'required|date_format:H:i:s',
            'tempat' => 'required|string|max:255',
            'pimpinan_id' => 'required|exists:profil_pegawai,id',
            'nip_pimpinan' => 'required|string|max:255',
            'peserta' => 'required|array',
            'agenda' => 'nullable|string',
            'resume' => 'nullable|string',
            'tanya_jawab' => 'nullable|string',
            'kategori' => 'required|integer|min:1|max:8',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', $validator->errors(), 422);
        }

        $notulensi->update($request->except(['notulis_id', 'nip_notulis']));
        return $this->success($notulensi,'Notulensi updated successfully');
    }

    /**
     * Delete a meeting minute
     *
     * Permanently remove a meeting minute from the database.
     *
     * @urlParam id integer required The ID of the meeting minute to delete. Example: 1
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Notulensi deleted successfully",
     *   "data": null
     * }
     *
     * @response 404 {
     *   "status": false,
     *   "message": "Notulensi not found",
     *   "data": null
     * }
     */
    public function destroy($id)
    {
        $notulensi = Notulensi::find($id);
        if (!$notulensi) {
            return $this->error('Notulensi not found', null, 404);
        }

        $notulensi->delete();
        return $this->success('Notulensi deleted successfully');
    }

    /**
     * Generate PDF document
     *
     * Generate and download a PDF document for the specified meeting minute.
     * The PDF includes official BPS and RB logos, meeting details, participants, agenda, summary, and signatures.
     *
     * @urlParam id integer required The ID of the meeting minute. Example: 1
     *
     * @response 200 application/pdf Binary PDF file download
     * @response 404 {
     *   "status": false,
     *   "message": "Notulensi not found",
     *   "data": null
     * }
     */
    public function generatePdf($id)
    {
        $notulensi = Notulensi::with(['pimpinan', 'notulis'])->find($id);
        if (!$notulensi) {
            return $this->error('Notulensi not found', null, 404);
        }

        $categories = [
            1 => 'Penataan dan Penguatan Organisasi',
            2 => 'Penataan Peraturan Perundang-Undangan',
            3 => 'Penataan Sumber Daya Manusia',
            4 => 'Penataan Tata Laksana',
            5 => 'Peningkatan Kualitas Pelayanan Publik',
            6 => 'Penguatan Pengawasan',
            7 => 'Penguatan Akuntabilitas Kinerja',
            8 => 'Manajemen Perubahan',
        ];

        $pdf = PDF::loadView('template-notulensi', compact('notulensi', 'categories'))
            ->setPaper('a4', 'portrait');

        $filename = 'Notulensi_' . str_replace(' ', '_', $notulensi->judul) . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Generate DOCX document
     *
     * Generate and download a DOCX (Microsoft Word) document for the specified meeting minute.
     * Uses a template file to maintain consistent formatting.
     *
     * @urlParam id integer required The ID of the meeting minute. Example: 1
     *
     * @response 200 application/vnd.openxmlformats-officedocument.wordprocessingml.document Binary DOCX file download
     * @response 404 {
     *   "status": false,
     *   "message": "Notulensi not found",
     *   "data": null
     * }
     * @response 404 {
     *   "status": false,
     *   "message": "DOCX template not found",
     *   "data": null
     * }
     */
    public function generateDocx($id)
    {
        $notulensi = Notulensi::with(['pimpinan', 'notulis'])->find($id);
        if (!$notulensi) {
            return $this->error('Notulensi not found', null, 404);
        }

        $templatePath = public_path('templates/notulensi_template.docx');
        if (!file_exists($templatePath)) {
            return $this->error('DOCX template not found', null, 404);
        }

        $templateProcessor = new TemplateProcessor($templatePath);

        // Basic Info
        $templateProcessor->setValue('judul', $notulensi->judul);
        $templateProcessor->setValue('instansi', $notulensi->instansi);
        $templateProcessor->setValue('kegiatan', $notulensi->kegiatan);
        $templateProcessor->setValue('waktu', $notulensi->waktu ? $notulensi->waktu->format('d F Y H:i') : '');
        $templateProcessor->setValue('tempat', $notulensi->tempat);
        $templateProcessor->setValue('pimpinan', $notulensi->pimpinan ? $notulensi->pimpinan->nama : '');
        $templateProcessor->setValue('notulis', $notulensi->notulis ? $notulensi->notulis->nama : '');
        $templateProcessor->setValue('nip_pimpinan', $notulensi->nip_pimpinan);
        $templateProcessor->setValue('nip_notulis', $notulensi->nip_notulis);

        // Participants list
        $pesertaText = '';
        if (is_array($notulensi->peserta)) {
            $pesertaText = implode(', ', $notulensi->peserta);
        }
        $templateProcessor->setValue('peserta', $pesertaText);

        // Checkboxes
        for ($i = 1; $i <= 8; $i++) {
            $templateProcessor->setValue('cb' . $i, $notulensi->kategori == $i ? '☑' : '☐');
        }

        // Rich Text fields (simplified injection for now, PHPWord Html helper is better for complex structures)
        // For a true implementation, one might need to use addHtml into specific sections or simplified string replacement if HTML is minimal
        $templateProcessor->setValue('agenda', strip_tags($notulensi->agenda));
        $templateProcessor->setValue('resume', strip_tags($notulensi->resume));
        $templateProcessor->setValue('tanya_jawab', strip_tags($notulensi->tanya_jawab));

        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $tempFile = $tempDir . '/' . uniqid('notulensi_') . '.docx';
        $templateProcessor->saveAs($tempFile);


        $filename = 'Notulensi_' . str_replace(' ', '_', $notulensi->judul) . '.docx';
        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Get form options
     *
     * Retrieve dropdown options for creating or editing meeting minutes.
     * Returns a list of employees (pegawai) and reform categories.
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Form options retrieved successfully",
     *   "data": {
     *     "pegawai": [
     *       {
     *         "id": 1,
     *         "nama": "Dr. Ahmad Wijaya",
     *         "nip": "198001012005011001",
     *         "jabatan": "Kepala BPS Kabupaten Karawang"
     *       },
     *       {
     *         "id": 2,
     *         "nama": "Siti Aminah, S.ST",
     *         "nip": "199001012015011002",
     *         "jabatan": "Kepala Subbagian Tata Usaha"
     *       }
     *     ],
     *     "categories": {
     *       "1": "Penataan dan Penguatan Organisasi",
     *       "2": "Penataan Peraturan Perundang-Undangan",
     *       "3": "Penataan Sumber Daya Manusia",
     *       "4": "Penataan Tata Laksana",
     *       "5": "Peningkatan Kualitas Pelayanan Publik",
     *       "6": "Penguatan Pengawasan",
     *       "7": "Penguatan Akuntabilitas Kinerja",
     *       "8": "Manajemen Perubahan"
     *     }
     *   }
     * }
     */
    public function formOptions()
    {
        $pegawai = \App\Models\Pegawai::select('id', 'nama', 'nip', 'jabatan')
            ->where('status', NULL)
            ->orderBy('kelas', 'desc')
            ->get();

        $categories = [
            1 => 'Penataan dan Penguatan Organisasi',
            2 => 'Penataan Peraturan Perundang-Undangan',
            3 => 'Penataan Sumber Daya Manusia',
            4 => 'Penataan Tata Laksana',
            5 => 'Peningkatan Kualitas Pelayanan Publik',
            6 => 'Penguatan Pengawasan',
            7 => 'Penguatan Akuntabilitas Kinerja',
            8 => 'Manajemen Perubahan',
        ];

        return $this->success([
            'pegawai' => $pegawai,
            'categories' => $categories,
        ], 'Form options retrieved successfully');
    }
}
