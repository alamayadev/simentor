<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Smalot\PdfParser\Parser as PdfParser;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class TestDomPdfController extends Controller
{
    /**
     * Read PDF file and return metadata and content of page 1
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function readPdf(Request $request)
    {
        try {
            // Define the PDF file path
            $filePath = public_path('Nurul Nubuwwati Mukarromah SKP Penilaian Agustus 2025.pdf');

            // Check if file exists
            if (!File::exists($filePath)) {
                return response()->json([
                    'error' => 'PDF file not found',
                    'message' => 'The requested PDF file does not exist'
                ], 404);
            }

            // Parse the PDF file
            $parser = new PdfParser();
            $pdf = $parser->parseFile($filePath);

            // Get metadata
            $metaData = $pdf->getDetails();

            // Get all pages
            $pages = $pdf->getPages();

            // Get content of the first page
            $firstPageContent = $pages[0]->getText();

            // Check if content is readable
            if (empty(trim($firstPageContent))) {
                // Return producer and indication that content is not readable
                $info = [
                    'producer' => isset($metaData['Producer']) ? $metaData['Producer'] : '',
                    'page_count' => count($pages),
                    'konten' => 'Tidak bisa dibaca'
                ];

                // Convert info to HTML format
                $htmlContent = '<div>';
                foreach ($info as $key => $value) {
                    $htmlContent .= '<p><strong>' . ucfirst($key) . ':</strong> ' . htmlspecialchars($value) . '</p>';
                }
                $htmlContent .= '</div>';

                return response()->json([
                    'info' => $info,
                    'konten' => $htmlContent
                ]);
            }

            // Replace '1 \nNama' with '1 Nama'
            $firstPageContent = preg_replace('/1\s*\n\s*Nama/', '1 Nama', $firstPageContent);

            // Replace multiple consecutive newlines (with only spaces in between) with single newline
            $firstPageContent = preg_replace('/\n\s*\n+/', "\n", $firstPageContent);

            // Split page_1_content by \n or \t to create an array
            $contentLines = preg_split('/[\n\t]+/', $firstPageContent);

            // Extract text between first '1 Nama' and 2nd '1 Nama' and remove \n
            $nama = '';
            $pattern = '/1 Nama\s*(.*?)\s*1 Nama/s';
            if (preg_match($pattern, $firstPageContent, $matches)) {
                $nama = trim($matches[1]);
                // Remove newlines from $nama
                $nama = str_replace("\n", ' ', $nama);
            }

            // Remove "1 Nama " from $nama
            $nama = str_replace('1 Nama ', '', $nama);

            // Extract periode from between 2nd and 3rd \n
            $lines = explode("\n", $firstPageContent);
            $periode = '';
            if (isset($lines[2])) {
                $periode = trim($lines[2]);
            }

            // Remove "Periode: " from $periode
            $periode = str_replace('Periode: ', '', $periode);

            // Create info object
            $info = [
                'producer' => isset($metaData['Producer']) ? $metaData['Producer'] : '',
                'page_count' => count($pages),
                'nama' => $nama,
                'periode' => $periode
            ];

            // Convert info to HTML format
            $htmlContent = '<div>';
            foreach ($info as $key => $value) {
                $htmlContent .= '<p><strong>' . ucfirst($key) . ':</strong> ' . htmlspecialchars($value) . '</p>';
            }
            $htmlContent .= '</div>';

            return response()->json([
                'info' => $info,
                'konten' => $htmlContent
            ]);
        } catch (\Exception $e) {
            // If parsing fails, try to at least return metadata
            try {
                $parser = new PdfParser();
                $pdf = $parser->parseFile($filePath);
                $metaData = $pdf->getDetails();
                $pages = $pdf->getPages();

                $info = [
                    'producer' => isset($metaData['Producer']) ? $metaData['Producer'] : '',
                    'page_count' => count($pages),
                    'konten' => 'Tidak bisa dibaca'
                ];

                // Convert info to HTML format
                $htmlContent = '<div>';
                foreach ($info as $key => $value) {
                    $htmlContent .= '<p><strong>' . ucfirst($key) . ':</strong> ' . htmlspecialchars($value) . '</p>';
                }
                $htmlContent .= '</div>';

                return response()->json([
                    'info' => $info,
                    'konten' => $htmlContent
                ]);
            } catch (\Exception $e2) {
                return response()->json([
                    'error' => 'Failed to parse PDF',
                    'message' => $e->getMessage()
                ], 500);
            }
        }
    }

    /**
     * Read all PDF files in the Juli folder and return their content
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function readAllJuliPdfs(Request $request)
    {
        try {
            // Define the PDF folder path
            $folderPath = public_path('Juli');

            // Check if folder exists
            if (!File::exists($folderPath)) {
                return response()->json([
                    'error' => 'Folder not found',
                    'message' => 'The requested folder does not exist'
                ], 404);
            }

            // Get all PDF files in the folder
            $pdfFiles = File::glob($folderPath . '/*.pdf');

            $results = [];

            foreach ($pdfFiles as $filePath) {
                try {
                    // Parse the PDF file
                    $parser = new PdfParser();
                    $pdf = $parser->parseFile($filePath);

                    // Get metadata
                    $metaData = $pdf->getDetails();

                    // Get all pages
                    $pages = $pdf->getPages();

                    // Get content of the first page
                    $firstPageContent = $pages[0]->getText();

                    // Check if content is readable
                    if (empty(trim($firstPageContent))) {
                        // Return producer and indication that content is not readable
                        $info = [
                            'file_name' => basename($filePath),
                            'producer' => isset($metaData['Producer']) ? $metaData['Producer'] : '',
                            'page_count' => count($pages),
                            'konten' => 'Tidak bisa dibaca'
                        ];

                        // Convert info to HTML format
                        $htmlContent = '<div>';
                        foreach ($info as $key => $value) {
                            $htmlContent .= '<p><strong>' . ucfirst($key) . ':</strong> ' . htmlspecialchars($value) . '</p>';
                        }
                        $htmlContent .= '</div>';

                        $results[] = [
                            'info' => $info,
                            'konten' => $htmlContent
                        ];
                        continue;
                    }

                    // Replace '1 \nNama' with '1 Nama'
                    $firstPageContent = preg_replace('/1\s*\n\s*Nama/', '1 Nama', $firstPageContent);

                    // Replace multiple consecutive newlines (with only spaces in between) with single newline
                    $firstPageContent = preg_replace('/\n\s*\n+/', "\n", $firstPageContent);

                    // Split page_1_content by \n or \t to create an array
                    $contentLines = preg_split('/[\n\t]+/', $firstPageContent);

                    // Extract text between first '1 Nama' and 2nd '1 Nama' and remove \n
                    $nama = '';
                    $pattern = '/1 Nama\s*(.*?)\s*1 Nama/s';
                    if (preg_match($pattern, $firstPageContent, $matches)) {
                        $nama = trim($matches[1]);
                        // Remove newlines from $nama
                        $nama = str_replace("\n", ' ', $nama);
                    }

                    // Remove "1 Nama " from $nama
                    $nama = str_replace('1 Nama ', '', $nama);

                    // Extract periode from between 2nd and 3rd \n
                    $lines = explode("\n", $firstPageContent);
                    $periode = '';
                    if (isset($lines[2])) {
                        $periode = trim($lines[2]);
                    }

                    // Remove "Periode: " from $periode
                    $periode = str_replace('Periode: ', '', $periode);

                    // Create info object
                    $info = [
                        'file_name' => basename($filePath),
                        'producer' => isset($metaData['Producer']) ? $metaData['Producer'] : '',
                        'page_count' => count($pages),
                        'nama' => $nama,
                        'periode' => $periode
                    ];

                    // Convert info to HTML format
                    $htmlContent = '<div>';
                    foreach ($info as $key => $value) {
                        $htmlContent .= '<p><strong>' . ucfirst($key) . ':</strong> ' . htmlspecialchars($value) . '</p>';
                    }
                    $htmlContent .= '</div>';

                    $results[] = [
                        'info' => $info,
                        'konten' => $htmlContent
                    ];
                } catch (\Exception $e) {
                    // If there's an error with one file, try to at least return metadata
                    try {
                        $parser = new PdfParser();
                        $pdf = $parser->parseFile($filePath);
                        $metaData = $pdf->getDetails();
                        $pages = $pdf->getPages();

                        $info = [
                            'file_name' => basename($filePath),
                            'producer' => isset($metaData['Producer']) ? $metaData['Producer'] : '',
                            'page_count' => count($pages),
                            'konten' => 'Tidak bisa dibaca'
                        ];

                        // Convert info to HTML format
                        $htmlContent = '<div>';
                        foreach ($info as $key => $value) {
                            $htmlContent .= '<p><strong>' . ucfirst($key) . ':</strong> ' . htmlspecialchars($value) . '</p>';
                        }
                        $htmlContent .= '</div>';

                        $results[] = [
                            'info' => $info,
                            'konten' => $htmlContent
                        ];
                    } catch (\Exception $e2) {
                        // If there's an error with one file, continue with the others
                        $results[] = [
                            'file_name' => basename($filePath),
                            'error' => 'Failed to parse PDF',
                            'message' => $e->getMessage()
                        ];
                    }
                }
            }

            return response()->json($results);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to read folder',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
