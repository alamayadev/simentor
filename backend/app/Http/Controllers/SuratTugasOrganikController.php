<?php
namespace App\Http\Controllers;

use App\Http\Traits\Terbilang;
use App\Models\Mitra;
use App\Models\Pegawai;
use App\Models\SurtugDetil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\TemplateProcessor;

class SuratTugasOrganikController extends Controller
{
    use Terbilang;

    /**
     * Generate DOCX for Surat Tugas Organik
     *
     * Menghasilkan dokumen DOCX untuk surat tugas organik berdasarkan ID.
     * Endpoint ini menyediakan paritas dengan rute web untuk menghasilkan dokumen DOCX.
     *
     * @group Docx Generator
     *
     * @authenticated
     *
     * @urlParam id int required ID dari surat tugas. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "DOCX generated successfully"
     * }
     * @response 401 {
     *   "message": "Unauthorized"
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Failed to generate DOCX",
     *   "error": "Error message details"
     * }
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        try {
            $surtug = SurtugDetil::where('surtug_id', request()->id)->first();
            if (! $surtug) {
                return response()->json(['error' => 'Surat Tugas Detail not found'], 404);
            }

            $jml_petugas = SurtugDetil::where('surtug_id', request()->id)->count();
            $no_surtug   = DB::table('surat_tugas')->where('id', $surtug->surtug_id)->first();

            // Get settings
            $setting_kepala     = DB::table('settings')->where('key', 'KEPALA_KANTOR')->first();
            $setting_ppk        = DB::table('settings')->where('key', 'PPK')->first();
            $setting_nip_kepala = DB::table('settings')->where('key', 'like', 'NIP_KEPALA%')->first() ?? DB::table('settings')->where('key', 'like', 'NIP%')->first();
            $setting_nip_ppk    = DB::table('settings')->where('key', 'like', 'NIP_PPK%')->first() ?? DB::table('settings')->where('key', 'like', 'NIP%')->skip(1)->first();

            $templatePath = '';

            if ($surtug->isOrganik == true && $jml_petugas == 1 && $surtug->sppd == true) {
                $templatePath = 'templates/surat_tugas_organik.docx';
            } elseif ($surtug->isOrganik == true && $jml_petugas == 1 && $surtug->sppd == false) {
                $templatePath = 'templates/format_surat_tugas_organik_lokal.docx';
            } elseif ($surtug->isOrganik == false && $jml_petugas == 1 && $surtug->dasar == null) {
                $templatePath = 'templates/surat_tugas_mitra.docx';
            } elseif ($surtug->isOrganik == false && $jml_petugas > 1 && $surtug->dasar != null) {
                $templatePath = 'templates/surat_tugas_mitra_with_table_and_menimbang.docx';
            } elseif ($surtug->isOrganik == true && $jml_petugas > 1 && $surtug->sppd == true) {
                $templatePath = 'templates/surat_tugas_organik_with_table.docx';
            } elseif ($surtug->isOrganik == true && $jml_petugas > 1 && $surtug->sppd == false) {
                $templatePath = 'templates/format_surat_tugas_organik_lokal_with_table.docx';
            }

            if (empty($templatePath) || ! file_exists(public_path($templatePath))) {
                return response()->json(['error' => 'Template not found or condition not met', 'path' => $templatePath, 'public_path' => public_path($templatePath)], 404);
            }

            $templateProcessor = new TemplateProcessor(public_path($templatePath));

            $petugas          = null;
            $nama_petugas     = '';
            $nip_petugas      = '';
            $sobatID          = '';
            $jabatan_petugas  = '';
            $pangkat_petugas  = '';
            $golongan_petugas = '';

            if ($surtug->pegawai_id > 0 && $jml_petugas == 1) {
                $petugas          = Pegawai::where('id', $surtug->pegawai_id)->first();
                $nama_petugas     = $petugas?->nama;
                $nip_petugas      = $petugas?->nip;
                $jabatan_petugas  = $petugas?->jabatan;
                $pangkat_petugas  = $petugas?->pangkat;
                $golongan_petugas = $petugas?->gol;
            } elseif ($surtug->mitra_id > 0 && $jml_petugas == 1) {
                $petugas         = Mitra::where('id', $surtug->mitra_id)->first();
                $nama_petugas    = $petugas?->nama_lengkap;
                $sobatID         = $petugas?->sobat_id;
                $jabatan_petugas = 'Mitra Statistik';
            }

            $hari         = $surtug->hari * 1;
            $tgl_laksana2 = '';
            $tgl_laksana3 = '';

            if ($hari <= 1) {
                $tgl_laksana3 = \Carbon\Carbon::parse($surtug->tgl_mulai)->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y');
                $tgl_laksana2 = \Carbon\Carbon::parse($surtug->tgl_mulai)->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y');
            } else {
                $tgl_laksana2 = \Carbon\Carbon::parse($surtug->tgl_mulai)->addDays($hari - 1)->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y');
                $tgl_laksana3 = \Carbon\Carbon::parse($surtug->tgl_mulai)->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y') . " - " . $tgl_laksana2;
            }

            $no         = str_replace("/", "_", $no_surtug->no_surat);
            $dataSurtug = [];

            if ($surtug->pegawai_id > 0 && $jml_petugas == 1) {
                $dataSurtug = [
                    'no_surtug'                    => $no_surtug->no_surat,
                    'dasar_pelaksanaan_perjalanan' => $surtug->dasar,
                    'nama_petugas'                 => $nama_petugas,
                    'nip'                          => $nip_petugas,
                    'jabatan'                      => $jabatan_petugas,
                    'nama_kegiatan'                => $surtug->nama_kegiatan,
                    'sebagai'                      => $surtug->tugas_sebagai,
                    'wilayah_kerja'                => $surtug->wilayah_kerja,
                    'tanggal_surat'                => \Carbon\Carbon::parse($no_surtug->tanggal)->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y'),
                    'kepala_kantor'                => $setting_kepala->value ?? '',
                    'nip_kepala'                   => 'NIP. ' . ($setting_nip_kepala->value ?? ''),
                    'ppk'                          => $setting_ppk->value ?? '',
                    'nip_ppk'                      => 'NIP. ' . ($setting_nip_ppk->value ?? ''),
                    'pangkat_petugas'              => $pangkat_petugas,
                    'jabatan_petugas'              => $jabatan_petugas,
                    'golongan_petugas'             => $golongan_petugas,
                    'jenis_kendaraan'              => $surtug->jenis_kendaraan,
                    'tanggal_pelaksanaan1'         => \Carbon\Carbon::parse($surtug->tgl_mulai)->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y'),
                    'tanggal_pelaksanaan2'         => $tgl_laksana2,
                    'tanggal_pelaksanaan3'         => $tgl_laksana3,
                    'maksud_perjalanan'            => 'Melaksanakan ' . $surtug->nama_kegiatan . ' di ' . $surtug->wilayah_kerja,
                    'no_dipa'                      => $surtug->no_dipa,
                    'terbilang_hari'               => $this->pembilang($hari),
                ];
            } elseif ($surtug->mitra_id > 0 && $jml_petugas == 1) {
                $dataSurtug = [
                    'no_surtug'                    => $no_surtug->no_surat,
                    'dasar_pelaksanaan_perjalanan' => $surtug->dasar,
                    'nama_petugas'                 => $nama_petugas,
                    'sobat_id'                     => $sobatID,
                    'nama_kegiatan'                => $surtug->nama_kegiatan,
                    'dasar'                        => $surtug->dasar,
                    'wilayah_kerja'                => $surtug->wilayah_kerja,
                    'tanggal_surat'                => \Carbon\Carbon::parse($no_surtug->tanggal)->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y'),
                    'kepala_kantor'                => $setting_kepala->value ?? '',
                    'nip_kepala'                   => ($setting_nip_kepala->value ?? ''),
                    'ppk'                          => $setting_ppk->value ?? '',
                    'pangkat_petugas'              => '',
                    'jabatan_petugas'              => 'Mitra Statistik',
                    'golongan_petugas'             => '',
                    'jenis_kendaraan'              => $surtug->jenis_kendaraan,
                    'tanggal_pelaksanaan1'         => \Carbon\Carbon::parse($surtug->tgl_mulai)->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y'),
                    'tanggal_pelaksanaan2'         => $tgl_laksana2,
                    'interval_tanggal'             => $tgl_laksana3,
                    'maksud_perjalanan'            => 'Melaksanakan ' . $surtug->nama_kegiatan . ' di ' . $surtug->wilayah_kerja,
                    'no_dipa'                      => $surtug->no_dipa,
                    'terbilang_hari'               => $this->pembilang($hari),
                ];
            } elseif ($jml_petugas > 1) {
                $dataSurtug = [
                    'no_surtug'                    => $no_surtug->no_surat,
                    'dasar_pelaksanaan_perjalanan' => $surtug->dasar,
                    'nama_petugas'                 => 'Sesuai dengan isi Lampiran 1',
                    'sobat_id'                     => 'Sesuai dengan isi Lampiran 1',
                    'nama_kegiatan'                => $surtug->nama_kegiatan,
                    'dasar'                        => $surtug->dasar,
                    'wilayah_kerja'                => $surtug->wilayah_kerja,
                    'tanggal_surat'                => \Carbon\Carbon::parse($no_surtug->tanggal)->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y'),
                    'kepala_kantor'                => $setting_kepala->value ?? '',
                    'nip_kepala'                   => ($setting_nip_kepala->value ?? ''),
                    'ppk'                          => $setting_ppk->value ?? '',
                    'nip_ppk'                      => 'NIP. ' . ($setting_nip_ppk->value ?? ''),
                    'tanggal_pelaksanaan1'         => \Carbon\Carbon::parse($surtug->tgl_mulai)->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y'),
                    'tanggal_pelaksanaan2'         => $tgl_laksana2,
                    'interval_tanggal'             => $tgl_laksana3,
                    'tanggal_pelaksanaan3'         => $tgl_laksana3,
                    'maksud_perjalanan'            => 'Melaksanakan ' . $surtug->nama_kegiatan . ' di ' . $surtug->wilayah_kerja,
                    'no_dipa'                      => $surtug->no_dipa,
                    'sebagai'                      => $surtug->tugas_sebagai,
                    'nip'                          => 'Sesuai dengan isi Lampiran 1',
                    'jabatan'                      => 'Sesuai dengan isi Lampiran 1',
                    'pangkat_petugas'              => 'Sesuai dengan isi Lampiran 1',
                    'jabatan_petugas'              => 'Sesuai dengan isi Lampiran 1',
                    'golongan_petugas'             => 'Sesuai dengan isi Lampiran 1',
                    'jenis_kendaraan'              => $surtug->jenis_kendaraan,
                    'terbilang_hari'               => $this->pembilang($hari),
                ];
            }

            $templateProcessor->setValues($dataSurtug);

            if ($jml_petugas >= 2) {
                $document_with_table = new PhpWord();
                $section             = $document_with_table->addSection();
                // ... (Logic for creating dynamic table in a separate document if needed for extraction, but simplified here for updating template)

                $data = SurtugDetil::with(['mitra', 'pegawai'])->where('surtug_id', request()->id)->get();

                // NOTE: PhpWord table creation directly into template using setComplexBlock is tricky.
                // Assuming the 'table' logic was working, we keep it but ensure values are set.

                $table = new Table(['borderSize' => 12, 'borderColor' => 'black', 'width' => 6000]);
                $table->addRow(900);
                $styleCell     = ['valign' => 'center'];
                $styleFirstRow = ['borderBottomSize' => 18, 'borderBottomColor' => '000000', 'align' => 'center', 'bold' => true];

                $table->addCell(1000, $styleCell)->addText('No', $styleFirstRow);
                $table->addCell(4500, $styleCell)->addText('Nama', $styleFirstRow);
                if ($surtug->pegawai_id > 0) {
                    $table->addCell(4500, $styleCell)->addText('NIP', $styleFirstRow);
                    $table->addCell(3500, $styleCell)->addText('Jabatan / Gol', $styleFirstRow);
                } elseif ($surtug->mitra_id > 0) {
                    $table->addCell(2500, $styleCell)->addText('Sobat ID', $styleFirstRow);
                    $table->addCell(3500, $styleCell)->addText('Kecamatan', $styleFirstRow);
                }
                foreach ($data as $index => $item) {
                    $table->addRow();
                    $table->addCell(1000)->addText($index + 1);
                    if ($surtug->pegawai_id > 0) {
                        $table->addCell(4500)->addText(ucwords(strtolower($item->pegawai->nama ?? '-')));
                        $table->addCell(3500)->addText($item->pegawai->nip ?? '-');
                        $table->addCell(3500)->addText(($item->pegawai->jabatan ?? '-') . ' / ' . ($item->pegawai->gol ?? '-'));
                    } elseif ($surtug->mitra_id > 0) {
                        $table->addCell(4500)->addText(ucwords(strtolower($item->mitra->nama_lengkap ?? '-')));
                        $table->addCell(2500)->addText($item->mitra->sobat_id ?? '-');
                        $table->addCell(3500)->addText($item->mitra->keca ?? '-');
                    }
                }

                $templateProcessor->setComplexBlock('table', $table);

                if ($surtug->pegawai_id > 0) {
                    $templateProcessor->setValues([
                        'baris_satu' => 'Lampiran 1',
                        'baris_dua'  => 'Surat Tugas No. ' . $no_surtug->no_surat,
                        'judul'      => 'Pegawai Dalam Kegiatan ' . $surtug->nama_kegiatan,
                    ]);
                } elseif ($surtug->mitra_id > 0) {
                    $templateProcessor->setValues([
                        'baris_satu' => 'Lampiran 1',
                        'baris_dua'  => 'Surat Tugas No. ' . $no_surtug->no_surat,
                        'judul'      => 'Petugas ' . $surtug->nama_kegiatan,
                    ]);
                }
            }

            // Create temp file
            $tempDir = storage_path('app/temp');
            if (! file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            $tempFile = $tempDir . '/' . uniqid('doc_') . '.docx';
            $templateProcessor->saveAs($tempFile);

            if (ob_get_length()) {
                ob_end_clean();
            }
            // Clean output buffer to remove any stray whitespace
            return response()->download($tempFile, $no . '.docx')->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
        }
    }

    public function satu($id)
    {
        try {
            $surtug = SurtugDetil::where('id', request()->id)->first();
            if (! $surtug) {
                return response()->json(['error' => 'Detail Surat Tugas not found'], 404);
            }

            $jml_petugas = SurtugDetil::where('id', request()->id)->count();
            $no_surtug   = DB::table('surat_tugas')->where('id', $surtug->surtug_id)->first();

            // Get settings (safer approach)
            $setting_kepala = DB::table('settings')->where('key', 'KEPALA_KANTOR')->first();
            $setting_ppk    = DB::table('settings')->where('key', 'PPK')->first();
            // Look for NIP_KEPALA, fallback to NIP (first)
            $setting_nip_kepala = DB::table('settings')->where('key', 'like', 'NIP_KEPALA%')->first() ?? DB::table('settings')->where('key', 'like', 'NIP%')->first();
            // Look for NIP_PPK, fallback to NIP (second)
            $setting_nip_ppk = DB::table('settings')->where('key', 'like', 'NIP_PPK%')->first() ?? DB::table('settings')->where('key', 'like', 'NIP%')->skip(1)->first();

            $templatePath = '';

            if ($surtug->isOrganik == true && $jml_petugas == 1 && $surtug->sppd == true) {
                $templatePath = 'templates/surat_tugas_organik.docx';
            } elseif ($surtug->isOrganik == true && $jml_petugas == 1 && $surtug->sppd == false) {
                $templatePath = 'templates/format_surat_tugas_organik_lokal.docx';
            } elseif ($surtug->isOrganik == false && $jml_petugas == 1 && $surtug->dasar == null) {
                $templatePath = 'templates/surat_tugas_mitra.docx';
            } elseif ($surtug->isOrganik == false && $jml_petugas == 1 && $surtug->dasar != null) {
                $templatePath = 'templates/surat_tugas_mitra_with_menimbang.docx';
            } elseif ($surtug->isOrganik == false && $jml_petugas > 1 && $surtug->dasar != null) {
                $templatePath = 'templates/surat_tugas_mitra_with_table_and_menimbang.docx';
            }

            if (empty($templatePath) || ! file_exists(public_path($templatePath))) {
                // Fallback for debugging, trying without public_path in case it's not set correctly in CLI context, but standard Laravel uses public_path
                if (! file_exists($templatePath)) {
                    return response()->json(['error' => 'Template not found', 'path' => $templatePath], 404);
                }
            } else {
                $templatePath = public_path($templatePath);
            }

            $templateProcessor = new TemplateProcessor($templatePath);

            $petugas          = null;
            $nama_petugas     = '';
            $nip_petugas      = '';
            $sobatID          = '';
            $jabatan_petugas  = '';
            $pangkat_petugas  = '';
            $golongan_petugas = '';

            if ($surtug->pegawai_id > 0 && $jml_petugas == 1) {
                $petugas          = Pegawai::where('id', $surtug->pegawai_id)->first();
                $nama_petugas     = $petugas?->nama;
                $nip_petugas      = $petugas?->nip;
                $jabatan_petugas  = $petugas?->jabatan;
                $pangkat_petugas  = $petugas?->pangkat;
                $golongan_petugas = $petugas?->gol;
            } elseif ($surtug->mitra_id > 0 && $jml_petugas == 1) {
                $petugas         = Mitra::where('id', $surtug->mitra_id)->first();
                $nama_petugas    = $petugas?->nama_lengkap;
                $sobatID         = $petugas?->sobat_id;
                $jabatan_petugas = 'Mitra Statistik';
            }

            $hari         = $surtug->hari * 1;
            $tgl_laksana2 = '';
            $tgl_laksana3 = '';

            if ($hari <= 1) {
                $tgl_laksana3 = \Carbon\Carbon::parse($surtug->tgl_mulai)->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y');
                $tgl_laksana2 = \Carbon\Carbon::parse($surtug->tgl_mulai)->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y');
            } else {
                // Fix days calculation: add $hari - 1 usually defines the range
                $tgl_laksana2 = \Carbon\Carbon::parse($surtug->tgl_mulai)->addDays($hari - 1)->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y');
                $tgl_laksana3 = \Carbon\Carbon::parse($surtug->tgl_mulai)->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y') . " - " . $tgl_laksana2;
            }

            $no = str_replace("/", "_", $no_surtug->no_surat);

            $dataSurtug = []; // Initialize to empty array

            if ($surtug->pegawai_id > 0 && $jml_petugas == 1) {
                $dataSurtug = [
                    'no_surtug'                    => $no_surtug->no_surat,
                    'dasar_pelaksanaan_perjalanan' => $surtug->dasar,
                    'nama_petugas'                 => $nama_petugas,
                    'nip'                          => $nip_petugas,
                    'jabatan'                      => $jabatan_petugas,
                    'nama_kegiatan'                => $surtug->nama_kegiatan,
                    'sebagai'                      => $surtug->tugas_sebagai,
                    'dasar'                        => $surtug->dasar,
                    'wilayah_kerja'                => $surtug->wilayah_kerja,
                    'tanggal_surat'                => \Carbon\Carbon::parse($no_surtug->tanggal)->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y'),
                    'kepala_kantor'                => $setting_kepala->value ?? '',
                    'nip_kepala'                   => 'NIP. ' . ($setting_nip_kepala->value ?? ''),
                    'ppk'                          => $setting_ppk->value ?? '',
                    'nip_ppk'                      => 'NIP. ' . ($setting_nip_ppk->value ?? ''),
                    'pangkat_petugas'              => $pangkat_petugas,
                    'jabatan_petugas'              => $jabatan_petugas,
                    'golongan_petugas'             => $golongan_petugas,
                    'jenis_kendaraan'              => $surtug->jenis_kendaraan,
                    'tanggal_pelaksanaan1'         => \Carbon\Carbon::parse($surtug->tgl_mulai)->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y'),
                    'tanggal_pelaksanaan2'         => $tgl_laksana2,
                    'tanggal_pelaksanaan3'         => $tgl_laksana3,
                    'maksud_perjalanan'            => 'Melaksanakan ' . $surtug->nama_kegiatan . ' di ' . $surtug->wilayah_kerja,
                    'no_dipa'                      => $surtug->no_dipa,
                    'hari'                         => $hari,
                    'terbilang_hari'               => $this->pembilang($hari),
                ];
            } elseif ($surtug->mitra_id > 0 && $jml_petugas == 1) {
                $dataSurtug = [
                    'no_surtug'                    => $no_surtug->no_surat,
                    'dasar_pelaksanaan_perjalanan' => $surtug->dasar,
                    'nama_petugas'                 => $nama_petugas,
                    'sobat_id'                     => $sobatID,
                    'nama_kegiatan'                => $surtug->nama_kegiatan,
                    'dasar'                        => $surtug->dasar,
                    'wilayah_kerja'                => $surtug->wilayah_kerja,
                    'tanggal_surat'                => \Carbon\Carbon::parse($no_surtug->tanggal)->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y'),
                    'kepala_kantor'                => $setting_kepala->value ?? '',
                    'nip_kepala'                   => $setting_nip_kepala->value ?? '',
                    'ppk'                          => $setting_ppk->value ?? '',
                    'pangkat_petugas'              => '',
                    'jabatan_petugas'              => 'Mitra Statistik',
                    'golongan_petugas'             => '',
                    'jenis_kendaraan'              => $surtug->jenis_kendaraan,
                    'tanggal_pelaksanaan1'         => \Carbon\Carbon::parse($surtug->tgl_mulai)->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y'),
                    'tanggal_pelaksanaan2'         => $tgl_laksana2,
                    'interval_tanggal'             => $tgl_laksana3,
                    'maksud_perjalanan'            => 'Melaksanakan ' . $surtug->nama_kegiatan . ' di ' . $surtug->wilayah_kerja,
                    'no_dipa'                      => $surtug->no_dipa,
                    'hari'                         => $hari,
                    'terbilang_hari'               => $this->pembilang($hari),
                ];
            }

            $templateProcessor->setValues($dataSurtug);

            // Create temp file
            $tempDir = storage_path('app/temp');
            if (! file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            $tempFile = $tempDir . '/' . uniqid('doc_') . '.docx';
            $templateProcessor->saveAs($tempFile);

            if (ob_get_length()) {
                ob_end_clean();
            }

            // Clean output buffer to remove any stray whitespace
            return response()->download($tempFile, $no . '.docx')->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
        }
    }
}
