<?php
namespace App\Http\Controllers;

use App\Http\Traits\Terbilang;
use App\Models\Kegiatan;
use App\Models\Mitra;
use App\Models\Pegawai;
use App\Models\Setting;
use App\Models\SkBast;
use App\Models\SkDetil;
use App\Models\SurtugDetil;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\TblWidth;
use PhpOffice\PhpWord\Style\Paper;
use PhpOffice\PhpWord\TemplateProcessor;

class SuratKeputusanController extends Controller
{
    use Terbilang;

    private function getCachedSettings($keys)
    {
        return Cache::remember('settings_' . md5(serialize($keys)), 3600, function () use ($keys) {
            $query = Setting::query();
            foreach ($keys as $key) {
                $query->orWhere('key', $key);
            }
            return $query->get();
        });
    }

    private function getCachedKegiatanWithSatuan($kegiatanIds)
    {
        return Cache::remember('kegiatan_with_satuan_' . md5(serialize($kegiatanIds)), 3600, function () use ($kegiatanIds) {
            return Kegiatan::with('satuan')->whereIn('id', $kegiatanIds)->get()->keyBy('id');
        });
    }

    private function processUuData($jenis, $skId)
    {
        $dataUu = Cache::remember("uu_{$jenis}", 3600, function () use ($jenis) {
            return DB::table('uu')->where('jenis', $jenis)->get();
        });

        $uu = [];
        foreach ($dataUu as $index => $value) {
            $uu[] = (object) [
                'first' => $index == 0 ? 'Mengingat' : '',
                'z'     => $index == 0 ? ':' : '',
                'i'     => $index + 1,
                'item'  => $value->detil,
            ];
        }

        $dataUuTambahan = DB::table('uu_tambahan')
            ->where('jenis_surat', 'SK')
            ->where('surat_id', $skId)
            ->get();

        foreach ($dataUuTambahan as $index => $value) {
            $uu[] = (object) [
                'first' => '',
                'z'     => '',
                'i'     => count($uu) + 1,
                'item'  => $value->detil,
            ];
        }

        return $uu;
    }

    private function buildLampiranTable($skDetil, $kegiatanData)
    {
        $tabelLampiran = [
            [
                'n'    => 'NO',
                'kol2' => 'NAMA',
                'kol3' => 'JABATAN TUGAS',
                'kol4' => 'BEBAN TUGAS',
                'kol5' => 'RATE SATUAN',
            ],
            [
                'n'    => '(1)',
                'kol2' => '(2)',
                'kol3' => '(3)',
                'kol4' => '(4)',
                'kol5' => '(5)',
            ],
        ];

        foreach ($skDetil as $index => $value) {
            $kegiatan     = $kegiatanData[$value->penugasan->kegiatan_id] ?? null;
            $jabatanTugas = $value->penugasan->jabatan_tugas;

            $jabatanMap = [
                'PCL'     => 'Pencacah',
                'PML'     => 'Pengawas',
                'default' => 'Operator',
            ];

            $rateMap = [
                'PCL'     => 'rate_pcl',
                'PML'     => 'rate_pml',
                'default' => 'rate_entri',
            ];

            $jabatan   = $jabatanMap[$jabatanTugas] ?? $jabatanMap['default'];
            $rateField = $rateMap[$jabatanTugas] ?? $rateMap['default'];

            $rate = $kegiatan ? number_format($kegiatan->$rateField, 0, ",", ".") . '/' . $kegiatan->satuan->value : '';

            $tabelLampiran[] = [
                'n'    => ($index + 1) . ". ",
                'kol2' => $value->mitra->nama_lengkap,
                'kol3' => $jabatan,
                'kol4' => $value->penugasan->volume,
                'kol5' => $rate,
            ];
        }

        return $tabelLampiran;
    }

    /**
     * Generate DOCX for Surat Tugas (Unused)
     *
     * Menghasilkan dokumen DOCX untuk surat tugas berdasarkan ID.
     * Catatan: Metode ini tampaknya tidak digunakan dalam rute API saat ini.
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
    public function generateDocx($id)
    {
        // Cache settings query
        $setting = $this->getCachedSettings(['KEPALA_KANTOR', 'PPK', 'NIP%']);

        // Optimize queries: get surtug data with eager loading
        $surtugId = request()->id;
        $surtug   = SurtugDetil::with(['nomor', 'pegawai', 'mitra'])
            ->where('surtug_id', $surtugId)
            ->orderBy('id', 'asc')
            ->get();

        $no_surtug = DB::table('surat_tugas')->where('id', $surtugId)->first();
        $no        = str_replace("/", "_", $no_surtug->no_surat);

        // Get last record ID more efficiently
        $last_data = $surtug->last()->id;

        $kantor = "BADAN PUSAT STATISTIK KABUPATEN KARAWANG";

        $paper = new Paper();
        $paper->setSize('A4');
        $phpWord = new PhpWord();
        $section = $phpWord->addSection([
            'marginLeft'   => 800,
            'marginRight'  => 800,
            'marginTop'    => 600,
            'marginBottom' => 600,
            'pageSizeW'    => $paper->getWidth(),
            'pageSizeH'    => $paper->getHeight(),
        ]);
        $phpWord->setDefaultFontName('Cambria');
        $phpWord->setDefaultFontSize(12);

        // Pre-calculate title styles
        $phpWord->addTitleStyle(1, ['bold' => true, 'size' => 12, 'allCaps' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $phpWord->addTitleStyle(2, ['size' => 12, 'allCaps' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

        foreach ($surtug as $item) {
            $hari = (int) $item->hari;

            // Optimize date calculations
            $tglMulai = \Carbon\Carbon::parse($item->tgl_mulai);
            if ($hari <= 1) {
                $tgl_laksana3 = $tglMulai->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y');
                $tgl_laksana2 = $tgl_laksana3;
            } else {
                $tglAkhir     = $tglMulai->copy()->addDays($hari - 1); // Subtract 1 since addDays adds to the start date
                $tgl_laksana2 = $tglMulai->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y');
                $tgl_laksana3 = $tgl_laksana2 . " - " . $tglAkhir->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y');
            }

            $tgl_surat = \Carbon\Carbon::parse($item->nomor->tanggal)->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y');
            $section->addImage(public_path('images\bps-logo.png'),
                [
                    // 'width'         => 100,
                    'height'        => 40,
                    'marginTop'     => -1,
                    'marginLeft'    => -1,
                    'wrappingStyle' => 'behind',
                    'alignment'     => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
                ]);
            $phpWord->addTitleStyle(1, ['bold' => true, 'size' => 12, 'allCaps' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
            $phpWord->addTitleStyle(2, ['size' => 12, 'allCaps' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
            $section->addTitle($kantor, 1);
            $section->addTextBreak(1);
            $section->addTitle('Surat Tugas', 1);
            $section->addTitle($item->nomor->no_surat, 2);
            $section->addTextBreak(1);

            $table = $section->addTable(['cellMargin' => 5, 'unit' => TblWidth::TWIP]);
            $table->addRow();
            $table->addCell(2500)->addText('Menimbang', null, ['cellMargin' => 1000]);
            $table->addCell(200)->addText(':');
            $table->addCell(300)->addText('a.');
            $table->addCell(7500)->addText('Bahwa ' . $item->nama_kegiatan . ' merupakan salah satu survei rutin BPS.');
            $table->addRow();
            $table->addCell(2500)->addText('');
            $table->addCell(200)->addText('');
            $table->addCell(300)->addText('b.');
            $table->addCell(7500)->addText('Bahwa ' . $item->nama_kegiatan . ' harus dilaksanakan sesuai dengan waktu yang telah ditentukan.');

            $section->addTextBreak(1);
            $table = $section->addTable(['cellMargin' => 5, 'unit' => TblWidth::TWIP]);
            $table->addRow();
            $table->addCell(2500)->addText('Mengingat');
            $table->addCell(200)->addText(':');
            $table->addCell(300)->addText('1.');
            $table->addCell(7500)->addText('Undang-undang Nomor 16 Tahun 1997 tentang Statistik;');
            $table->addRow();
            $table->addCell(2500)->addText('');
            $table->addCell(200)->addText('');
            $table->addCell(300)->addText('2.');
            $table->addCell(7500)->addText('Undang-undang Nomor 47 Tahun 2009 tentang Anggaran Pendapatan dan Belanja Negara Tahun Anggaran 2010;');
            $table->addRow();
            $table->addCell(2500)->addText('');
            $table->addCell(200)->addText('');
            $table->addCell(300)->addText('3.');
            $table->addCell(7500)->addText('Peraturan Presiden Republik Indonesia Nomor 86 Tahun 2007 tentang Badan Pusat Statistik;');
            $table->addRow();
            $table->addCell(2500)->addText('');
            $table->addCell(200)->addText('');
            $table->addCell(300)->addText('4.');
            $table->addCell(7500)->addText('Peraturan Badan Pusat Statistik Nomor 8 Tahun 2020 tentang Organisasi dan Tata Kerja Badan Pusat Statistik Provinsi dan Badan Pusat Statistik Kabupaten/Kota;');

            // $section->addTextBreak(1);
            $section->addText('Memberi Perintah', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

            $table = $section->addTable(['cellMargin' => 5, 'unit' => TblWidth::TWIP]);
            $table->addRow();
            $table->addCell(2500)->addText('Kepada');
            $table->addCell(200)->addText(':');
            $table->addCell(7500)->addText($item->mitra->nama_lengkap);
            $table->addRow();
            $table->addCell(2500)->addText('Sobat ID');
            $table->addCell(200)->addText(':');
            $table->addCell(7500)->addText($item->mitra->sobat_id);
            $table->addRow();
            $table->addCell(2500)->addText('Jabatan');
            $table->addCell(200)->addText(':');
            $table->addCell(7500)->addText('Mitra Statistik');
            $table->addRow();
            $table->addCell(2500)->addText('Untuk');
            $table->addCell(200)->addText(':');
            $table->addCell(7500)->addText('Melaksanakan ' . $item->nama_kegiatan . ' di ' . $item->wilayah_kerja . ' pada tanggal ' . $tgl_laksana3 . ' dengan pembebanan dibebankan pada DIPA BPS Karawang ' . $item->no_dipa);

            $section->addTextBreak(1);
            $table = $section->addTable(['cellMargin' => 5, 'unit' => TblWidth::TWIP]);
            $table->addRow();
            $table->addCell(3500)->addText('');
            $table->addCell(200)->addText('');
            $table->addCell(6500)->addText('Karawang, ' . $tgl_surat . '<w:br/>Kepala Badan Pusat Statistik<w:br/>Kabupaten Karawang', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
            $section->addTextBreak(2);
            $table = $section->addTable(['cellMargin' => 5, 'unit' => TblWidth::TWIP]);
            $table->addRow();
            $table->addCell(3500)->addText('');
            $table->addCell(200)->addText('');
            $table->addCell(6500)->addText($setting[1]->value . '<w:br/>NIP. ' . $setting[3]->value, null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

            if ($item->id != $last_data) {
                $section->addPageBreak();
            }
        }

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        try {
            $objWriter->save(public_path($no . '_gabungan.docx'));
        } catch (Exception $e) {
        }

        return response()->download(public_path($no . '_gabungan.docx'))->deleteFileAfterSend(true);

    }
    /**
     * Generate DOCX for SK KPA Mitra
     *
     * Menghasilkan dokumen DOCX untuk Surat Keputusan KPA untuk mitra berdasarkan ID.
     * Endpoint ini menyediakan paritas dengan rute web untuk menghasilkan dokumen DOCX.
     *
     * @group Docx Generator
     *
     * @authenticated
     *
     * @urlParam sk int required ID dari surat keputusan. Contoh: 1
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
     * @param int $sk
     * @return \Illuminate\Http\Response
     */
    public function generateSkKpaMitra($sk)
    {
        // Optimize queries with eager loading and caching
        $skDetil = SkDetil::select('surat_sk_detil.*')
            ->with(['pegawai', 'mitra:id,nama_lengkap,sobat_id,alamat_kec,keca,desaid', 'nomor:id,no_surat,tanggal', 'penugasan:id,kegiatan_id,jabatan_tugas,volume'])
            ->where('sk_id', $sk)
            ->join('mitra_kepka', 'mitra_kepka.id', '=', 'surat_sk_detil.mitra_id')
            ->orderBy('mitra_kepka.desaid')
            ->get();

        $sk = SkBast::where('id', $sk)->first();
        $no = 'SK_' . $sk->oleh . '_' . str_replace("/", "_", $sk->no_surat);

        // Use cached settings
        $setting = $this->getCachedSettings(['grup' => 2]);
        $dipa    = $this->getCachedSettings(['%_DIPA']);

        // Get unique kegiatan IDs and cache kegiatan data
        $kegiatanIds  = $skDetil->pluck('penugasan.kegiatan_id')->unique()->values()->all();
        $kegiatanData = $this->getCachedKegiatanWithSatuan($kegiatanIds);

        // Get first kegiatan for template variables
        $firstKegiatan = $kegiatanData->first();

        $templateProcessor = new TemplateProcessor(public_path('templates/format_sk_kpa_mitra.docx'));

        // Set template values more efficiently
        $templateValues = [
            'nomor_sk'            => $sk->no_surat,
            'perihal'             => $sk->perihal,
            'kepada'              => $sk->kepada,
            'kepada_judul'        => strtoupper($sk->kepada),
            'nama_kegiatan'       => $sk->kegiatan,
            'nama_kegiatan_judul' => strtoupper($sk->kegiatan),
            'tahun_anggaran'      => $firstKegiatan->tahun ?? $sk->thn,
            'tahun'               => $firstKegiatan->tahun ?? $sk->thn,
            'tgl_sk'              => \Carbon\Carbon::parse($sk->tanggal)->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y'),
            'kepala_kantor'       => $setting->where('key', 'KEPALA_KANTOR')->first()->value ?? '',
            'nip_kepala'          => 'NIP.' . ($setting->where('key', 'like', 'NIP%')->first()->value ?? ''),
            'satu'                => 'Menunjuk Petugas Lapangan ' . $sk->kegiatan,
            'nomor_dipa'          => $dipa->first()->value ?? '',
            'tanggal_dipa'        => $dipa->skip(1)->first()->value ?? '',
        ];

        foreach ($templateValues as $key => $value) {
            $templateProcessor->setValue($key, $value);
        }

        // Use optimized UU processing
        $uu = $this->processUuData($sk->oleh, $sk->id);
        $templateProcessor->cloneRowAndSetValues('i', $uu);

        // Use optimized lampiran table building
        $lampiran = $this->buildLampiranTable($skDetil, $kegiatanData);
        $templateProcessor->cloneRowAndSetValues('n', $lampiran);

        $templateProcessor->saveAs(public_path($no . '.docx'));

        return response()->download(public_path($no . '.docx'))->deleteFileAfterSend(true);
    }
    /**
     * Generate DOCX for SK KPA Mitra Manual
     *
     * Menghasilkan dokumen DOCX untuk Surat Keputusan KPA untuk mitra manual berdasarkan ID.
     * Endpoint ini menyediakan paritas dengan rute web untuk menghasilkan dokumen DOCX.
     *
     * @group Docx Generator
     *
     * @authenticated
     *
     * @urlParam sk int required ID dari surat keputusan. Contoh: 1
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
     * @param int $sk
     * @return \Illuminate\Http\Response
     */
    public function generateSkKpaMitraManual($sk)
    {
        // Optimize queries with eager loading
        $skDetil = SkDetil::select('surat_sk_detil.*')
            ->with(['mitra:id,nama_lengkap'])
            ->where('sk_id', $sk)
            ->join('mitra_kepka', 'mitra_kepka.id', '=', 'surat_sk_detil.mitra_id')
            ->orderBy('mitra_kepka.desaid')
            ->get();

        $sk = SkBast::where('id', $sk)->first();
        $no = 'SK_' . $sk->oleh . '_' . str_replace("/", "_", $sk->no_surat);

        // Use cached settings
        $setting = $this->getCachedSettings(['grup' => 2]);
        $dipa    = $this->getCachedSettings(['%_DIPA']);

        // Select template based on oleh
        $templatePath = $sk->oleh == 'KPA'
            ? public_path('templates/format_sk_kpa_mitra.docx')
            : public_path('templates/format_sk_kepala_mitra.docx');

        $templateProcessor = new TemplateProcessor($templatePath);

        // Set template values more efficiently
        $templateValues = [
            'nomor_sk'            => $sk->no_surat,
            'perihal'             => $sk->perihal,
            'kepada'              => $sk->kepada,
            'nama_kegiatan'       => $sk->kegiatan,
            'nama_kegiatan_judul' => strtoupper($sk->kegiatan),
            'tahun_anggaran'      => $sk->thn,
            'tahun'               => $sk->thn,
            'tgl_sk'              => \Carbon\Carbon::parse($sk->tanggal)->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y'),
            'kepala_kantor'       => $setting->where('key', 'KEPALA_KANTOR')->first()->value ?? '',
            'nip_kepala'          => 'NIP.' . ($setting->where('key', 'like', 'NIP%')->first()->value ?? ''),
            'satu'                => 'Menunjuk Petugas Lapangan ' . $sk->kegiatan,
            'nomor_dipa'          => $dipa->first()->value ?? '',
            'tanggal_dipa'        => $dipa->skip(1)->first()->value ?? '',
        ];

        foreach ($templateValues as $key => $value) {
            $templateProcessor->setValue($key, $value);
        }

        // Use optimized UU processing
        $uu = $this->processUuData($sk->oleh, $sk->id);
        $templateProcessor->cloneRowAndSetValues('i', $uu);

        // Build simplified lampiran table for manual version
        $tabelLampiran = [
            [
                'n'    => 'NO',
                'kol2' => 'NAMA',
                'kol3' => 'JABATAN TUGAS',
                'kol4' => 'BEBAN TUGAS',
                'kol5' => 'RATE SATUAN',
            ],
            [
                'n'    => '(1)',
                'kol2' => '(2)',
                'kol3' => '(3)',
                'kol4' => '(4)',
                'kol5' => '(5)',
            ],
        ];

        foreach ($skDetil as $index => $value) {
            $tabelLampiran[] = [
                'n'    => ($index + 1) . ". ",
                'kol2' => $value->mitra->nama_lengkap,
                'kol3' => '',
                'kol4' => '',
                'kol5' => '',
            ];
        }

        $templateProcessor->cloneRowAndSetValues('n', $tabelLampiran);

        $templateProcessor->saveAs(public_path($no . '.docx'));

        return response()->download(public_path($no . '.docx'))->deleteFileAfterSend(true);
    }
    /**
     * Generate DOCX for SK Kepala Organik
     *
     * Menghasilkan dokumen DOCX untuk Surat Keputusan Kepala untuk organik berdasarkan ID.
     * Endpoint ini menyediakan paritas dengan rute web untuk menghasilkan dokumen DOCX.
     *
     * @group Docx Generator
     *
     * @authenticated
     *
     * @urlParam sk int required ID dari surat keputusan. Contoh: 1
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
     * @param int $sk
     * @return \Illuminate\Http\Response
     */
    public function generateSkKepalaOrganik($sk)
    {
        // Optimize queries with eager loading for pegawai data
        $skDetil = SkDetil::select('surat_sk_detil.*')
            ->with(['pegawai:id,nama,nip,pangkat,gol'])
            ->where('sk_id', $sk)
            ->get();

        $sk = SkBast::where('id', $sk)->first();
        $no = 'SK_' . $sk->oleh . '_' . str_replace("/", "_", $sk->no_surat);

        // Use cached settings
        $setting = $this->getCachedSettings(['grup' => 2]);
        $dipa    = $this->getCachedSettings(['%_DIPA']);

        $templateProcessor = new TemplateProcessor(public_path('templates/format_sk_kepala_organik.docx'));

        // Set template values more efficiently
        $templateValues = [
            'nomor_sk'            => $sk->no_surat,
            'perihal'             => $sk->perihal,
            'perihal_judul'       => strtoupper($sk->perihal),
            'kepada'              => $sk->kepada,
            'kepada_judul'        => strtoupper($sk->kepada),
            'nama_kegiatan'       => $sk->kegiatan,
            'nama_kegiatan_judul' => strtoupper($sk->kegiatan),
            'tahun_anggaran'      => $sk->thn,
            'tahun'               => $sk->thn,
            'tgl_sk'              => \Carbon\Carbon::parse($sk->tanggal)->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y'),
            'kepala_kantor'       => $setting->where('key', 'KEPALA_KANTOR')->first()->value ?? '',
            'nip_kepala'          => 'NIP.' . ($setting->where('key', 'like', 'NIP%')->first()->value ?? ''),
            'satu'                => 'Menunjuk Petugas Lapangan ' . $sk->kegiatan,
            'nomor_dipa'          => $dipa->first()->value ?? '',
            'tanggal_dipa'        => $dipa->skip(1)->first()->value ?? '',
        ];

        foreach ($templateValues as $key => $value) {
            $templateProcessor->setValue($key, $value);
        }

        // Use optimized UU processing
        $uu = $this->processUuData($sk->oleh, $sk->id);
        $templateProcessor->cloneRowAndSetValues('i', $uu);

        // Build lampiran table for organic employees
        $tabelLampiran = [
            [
                'n'    => 'NO',
                'kol2' => 'NAMA',
                'kol3' => 'NIP',
                'kol4' => 'PANGKAT/GOL',
                'kol5' => 'JABATAN KEGIATAN',
            ],
            [
                'n'    => '(1)',
                'kol2' => '(2)',
                'kol3' => '(3)',
                'kol4' => '(4)',
                'kol5' => '(5)',
            ],
        ];

        foreach ($skDetil as $index => $value) {
            $jabatanKegiatan = is_array($value->detil) ? ($value->detil['jabatan_kegiatan'] ?? implode(', ', $value->detil)) : (string)$value->detil;
            $tabelLampiran[] = [
                'n'    => ($index + 1) . ". ",
                'kol2' => $value->pegawai->nama ?? '',
                'kol3' => $value->pegawai->nip ?? '',
                'kol4' => ($value->pegawai->pangkat ?? '') . '/' . ($value->pegawai->gol ?? ''),
                'kol5' => $jabatanKegiatan,
            ];
        }

        $templateProcessor->cloneRowAndSetValues('n', $tabelLampiran);

        $templateProcessor->saveAs(public_path($no . '.docx'));

        return response()->download(public_path($no . '.docx'))->deleteFileAfterSend(true);
    }
}
