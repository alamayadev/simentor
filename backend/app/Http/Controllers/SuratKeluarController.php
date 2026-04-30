<?php
namespace App\Http\Controllers;

use App\Models\SuratKeluar;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\TemplateProcessor;

/**
 * Controller for handling outgoing letter (Surat Keluar) operations
 *
 * @package App\Http\Controllers
 */
class SuratKeluarController extends Controller
{
    /**
     * Cache duration for settings in seconds (1 hour)
     */
    private const SETTINGS_CACHE_DURATION = 3600;

    /**
     * Template file path
     */
    private const TEMPLATE_PATH = 'templates/format_surat_keluar.docx';

    /**
     * Font style for document content
     */
    private const FONT_STYLE = [
        'name' => 'Minion Pro',
        'size' => 10,
        'bold' => true,
    ];

    /**
     * Generate and download a Word document for an outgoing letter
     *
     * Menghasilkan dan mengunduh dokumen Word untuk surat keluar berdasarkan ID.
     * Endpoint ini menyediakan paritas dengan rute web untuk menghasilkan dokumen DOCX.
     *
     * @group Docx Generator
     *
     * @authenticated
     *
     * @urlParam id int required ID dari surat keluar. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "DOCX generated successfully"
     * }
     * @response 401 {
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Surat keluar not found"
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Failed to generate DOCX",
     *   "error": "Error message details"
     * }
     *
     * @param int $id The ID of the SuratKeluar record
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Exception
     */
    public function index($id)
    {
        try {
            // Validate input
            if (! is_numeric($id) || $id <= 0) {
                return response()->json(['error' => 'Invalid ID provided'], 400);
            }

            // Find the letter with proper error handling
            $surat = SuratKeluar::findOrFail($id);

            // Generate safe filename
            $filename = $this->generateSafeFilename($surat->no_surat);

            // Get cached settings
            $settings = $this->getCachedSettings();

            // Process template
            $tempFilePath = $this->processTemplate($surat, $settings, $filename);

            if (ob_get_length()) {
                ob_end_clean();
            }
            // Return download response with automatic cleanup
            return response()->download($tempFilePath, $filename . '.docx')->deleteFileAfterSend(true);

        } catch (Exception $e) {
            // Log the error for debugging
            \Log::error('Error generating Surat Keluar document: ' . $e->getMessage(), [
                'id'    => $id ?? 'unknown',
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Failed to generate document'], 500);
        }
    }

    /**
     * Generate a safe filename from the letter number
     *
     * @param string $noSurat The letter number
     * @return string Safe filename
     */
    private function generateSafeFilename(string $noSurat): string
    {
        return Str::slug($noSurat, '_');
    }

    /**
     * Get settings from cache or database
     *
     * @return \Illuminate\Support\Collection
     */
    private function getCachedSettings()
    {
        return Cache::remember('surat_keluar_settings', self::SETTINGS_CACHE_DURATION, function () {
            return DB::table('settings')
                ->whereIn('key', ['KEPALA_KANTOR', 'PPK'])
                ->orWhere('key', 'like', 'NIP%')
                ->get()
                ->keyBy('key');
        });
    }

    /**
     * Process the template with letter data and settings
     *
     * @param SuratKeluar $surat The letter model
     * @param \Illuminate\Support\Collection $settings Settings collection
     * @param string $filename The base filename
     * @return string Path to the generated file
     * @throws \Exception
     */
    private function processTemplate(SuratKeluar $surat, $settings, string $filename): string
    {
        // Check if template exists
        if (! file_exists(self::TEMPLATE_PATH)) {
            throw new Exception('Template file not found: ' . self::TEMPLATE_PATH);
        }

        $templateProcessor = new TemplateProcessor(self::TEMPLATE_PATH);

        // Set basic values
        $templateProcessor->setValue('no_surat', $surat->no_surat);
        $templateProcessor->setValue('tanggal_surat', $this->formatTanggal($surat->tanggal));
        $templateProcessor->setValue('perihal', $surat->perihal);
        $templateProcessor->setValue('lampiran', $surat->lampiran ? $surat->lampiran . ' halaman' : '-');
        $templateProcessor->setValue('tujuan', $surat->tujuan);

        // Process HTML content
        $this->processHtmlContent($templateProcessor, $surat->isi_surat);

        // Set signature values with fallbacks
        $templateProcessor->setValue('kepala_kantor', $settings->get('KEPALA_KANTOR')->value ?? 'Kepala Kantor');
        $templateProcessor->setValue('nip_kepala', $settings->where('key', 'like', 'NIP%')->first()->value ?? 'NIP');

        // Create temporary file
        $tempFilePath = storage_path('app/temp/' . $filename . '_' . time() . '.docx');

        // Ensure temp directory exists
        $tempDir = dirname($tempFilePath);
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $templateProcessor->saveAs($tempFilePath);

        return $tempFilePath;
    }

    /**
     * Format date in Indonesian format
     *
     * @param string $date The date string
     * @return string Formatted date
     */
    private function formatTanggal(string $date): string
    {
        return \Carbon\Carbon::parse($date)
            ->locale('id')
            ->settings(['formatFunction' => 'translatedFormat'])
            ->format('j F Y');
    }

    /**
     * Process HTML content for Word document
     *
     * @param TemplateProcessor $templateProcessor The template processor
     * @param string $htmlContent The HTML content to process
     * @return void
     */
    private function processHtmlContent(TemplateProcessor $templateProcessor, string $htmlContent): void
    {
        // Clean HTML more efficiently with regex
        $cleanHtml = preg_replace([
            '/<br\s*\/?>/i',
            '/<p><\/p>/i',
            '/style=""/i',
        ], ['', '', ''], $htmlContent);

        // Create table for HTML content
        $wordTable = new Table();
        $wordTable->addRow();
        $cell = $wordTable->addCell();

        // Add HTML to cell
        \PhpOffice\PhpWord\Shared\Html::addHtml($cell, $cleanHtml);

        // Set complex block with font style
        $templateProcessor->setComplexBlock('isi_surat', $wordTable, self::FONT_STYLE);
    }
}
