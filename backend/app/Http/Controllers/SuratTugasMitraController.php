<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\SimpleType\TblWidth;
use PhpOffice\PhpWord\Style\Paper;
use App\Http\Traits\Terbilang;
use App\Models\SurtugDetil;
use App\Models\Mitra;
use App\Models\Pegawai;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SuratTugasMitraController extends Controller
{
    use Terbilang;

    /**
     * Generate DOCX for Surat Tugas Gabungan (Mitra)
     *
     * Menghasilkan dokumen DOCX untuk surat tugas gabungan mitra berdasarkan ID.
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
    public function generateDocx($id)
    {
        $paper = new Paper();
        $paper->setSize('A4');
        $phpWord = new PhpWord();
        $section = $phpWord->addSection(array('marginLeft' => 800, 'marginRight' => 800,
        'marginTop' => 600, 'marginBottom' => 600,'pageSizeW' => $paper->getWidth(), 'pageSizeH' => $paper->getHeight()));
        $phpWord->setDefaultFontName('Cambria');
        $phpWord->setDefaultFontSize(12);
        $setting = DB::table('settings')->where('key','KEPALA_KANTOR')->orWhere('key','PPK')->orWhere('key','like','NIP'.'%')->get();

        $surtug = SurtugDetil::with('nomor','pegawai','mitra')->where('surtug_id', request()->id)->orderBy('id', 'asc')->get();
        $no_surtug = DB::table('surat_tugas')->where('id',request()->id)->first();
        $no = str_replace("/","_",$no_surtug->no_surat);
        $last_record = SurtugDetil::where('surtug_id', request()->id)->orderBy('id', 'desc')->first();
        $last_data = $last_record->id;

        // return $setting;
        $kantor = "BADAN PUSAT STATISTIK KABUPATEN KARAWANG";

        foreach ($surtug as $item) {
            $hari = $item->hari*1;
            if($hari <= 1){
                $tgl_laksana3 = \Carbon\Carbon::parse($item->tgl_mulai)->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y');
                $tgl_laksana2 = \Carbon\Carbon::parse($item->tgl_mulai)->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y');
            }else{
                $tgl_laksana2 = \Carbon\Carbon::parse($item->tgl_mulai)->addDays($hari);
                $tgl_laksana3 = \Carbon\Carbon::parse($item->tgl_mulai)->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y')." - ".
                                $tgl_laksana2->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y');
            }
            $tgl_surat =\Carbon\Carbon::parse($item->nomor->tanggal)->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y');
            $section->addImage(public_path('images\bps-logo.png'),
            array(
                // 'width'         => 100,
                'height'        => 30,
                'marginTop'     => -2,
                'marginLeft'    => -1,
                'wrappingStyle' => 'behind',
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
            ));
            $phpWord->addTitleStyle(1, ['bold' => true,'size' => 12, 'allCaps' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
            $phpWord->addTitleStyle(2, ['size' => 12, 'allCaps' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
            $section->addTitle($kantor,1);
            $section->addTextBreak(1);
            $section->addTitle('Surat Tugas',1);
            $section->addTitle($item->nomor->no_surat,2);
            $section->addTextBreak(1);

            $table = $section->addTable(array('cellMargin' => 5,'unit' => TblWidth::TWIP));
            $table->addRow();
            $table->addCell(2500)->addText('Menimbang',null,array('cellMargin' => 1000));
            $table->addCell(200)->addText(':');
            $table->addCell(300)->addText('a.');
            $table->addCell(7500)->addText('Bahwa '.$item->nama_kegiatan.' merupakan salah satu survei rutin BPS.');
            $table->addRow();
            $table->addCell(2500)->addText('');
            $table->addCell(200)->addText('');
            $table->addCell(300)->addText('b.');
            $table->addCell(7500)->addText('Bahwa '.$item->nama_kegiatan.' harus dilaksanakan sesuai dengan waktu yang telah ditentukan.');
            if($no_surtug->menimbang == null){
                $section->addTextBreak(1);
            }
            $table = $section->addTable(array('cellMargin' => 5,'unit' => TblWidth::TWIP));
            $table->addRow();
            $table->addCell(2500)->addText('Mengingat');
            $table->addCell(200)->addText(':');
            $table->addCell(300)->addText('1.');
            $table->addCell(7500)->addText('Undang-undang Nomor 16 Tahun 1997 tentang Statistik (Lembaran Negara Republik Indonesia Tahun 1997 Nomor 39);');
            $table->addRow();
            $table->addCell(2500)->addText('');
            $table->addCell(200)->addText('');
            $table->addCell(300)->addText('2.');
            $table->addCell(7500)->addText('Undang-undang Nomor 17 Tahun 2025 tentang Anggaran Pendapatan dan Belanja Negara (Lembaran Negara Republik Indonesia Tahun 2025 Nomor 179);');
            $table->addRow();
            $table->addCell(2500)->addText('');
            $table->addCell(200)->addText('');
            $table->addCell(300)->addText('3.');
            $table->addCell(7500)->addText('Peraturan Pemerintah Nomor 51 Tahun 1999 tentang Penyelenggaraan Statistik (Lembaran Negara Republik Indonesia Tahun 1999 Nomor 96);');
            $table->addRow();
            $table->addCell(2500)->addText('');
            $table->addCell(200)->addText('');
            $table->addCell(300)->addText('4.');
            $table->addCell(7500)->addText('Peraturan Presiden Nomor 86 Tahun 2007 tentang Badan Pusat Statistik sebagaimana telah diubah dengan Peraturan Presiden Nomor 1 tahun 2025 tentang Badan Pusat Statistik (Lembaran Negara Republik Indonesia Tahun 2025 Nomor 4);');
            $table->addRow();
            $table->addCell(2500)->addText('');
            $table->addCell(200)->addText('');
            $table->addCell(300)->addText('5.');
            $table->addCell(7500)->addText('Peraturan Badan Pusat Statistik Nomor 1 Tahun 2023 tentang Pedoman Tata Naskah Dinas Badan Pusat Statistik (Berita Negara Republik Indonesia Tahun 2023 Nomor 65);');
            $table->addRow();
            $table->addCell(2500)->addText('');
            $table->addCell(200)->addText('');
            $table->addCell(300)->addText('6.');
            $table->addCell(7500)->addText('Peraturan Badan Pusat Statistik Nomor 5 Tahun 2023 tentang Organisasi dan Tata Kerja Badan Pusat Statistik Provinsi dan Badan Pusat Statistik Kabupaten/Kota (Berita Negara Republik Indonesia Tahun 2023 Nomor 429).');

            if($no_surtug->menimbang != null){
                $table->addRow();
                $table->addCell(2500)->addText('');
                $table->addCell(200)->addText('');
                $table->addCell(300)->addText('5.');
                $table->addCell(7500)->addText($no_surtug->menimbang);
            }

            // $section->addTextBreak(1);
            $section->addText('Memberi Perintah',null,['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

            $table = $section->addTable(array('cellMargin' => 5,'unit' => TblWidth::TWIP));
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
            $table->addCell(7500)->addText('Melaksanakan '.$item->nama_kegiatan.' di '.$item->wilayah_kerja.' pada tanggal '.$tgl_laksana3.' dengan pembebanan dibebankan pada DIPA BPS Karawang '.$item->no_dipa);

            $section->addTextBreak(1);
            $table = $section->addTable(array('cellMargin' => 5,'unit' => TblWidth::TWIP));
            $table->addRow();
            $table->addCell(3500)->addText('');
            $table->addCell(200)->addText('');
            $table->addCell(6500)->addText('Karawang, '.$tgl_surat.'<w:br/>Kepala Badan Pusat Statistik<w:br/>Kabupaten Karawang',null,['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
            $section->addTextBreak(1);
            $table = $section->addTable(array('cellMargin' => 5,'unit' => TblWidth::TWIP));
            $table->addRow();
            $table->addCell(3500)->addText('');
            $table->addCell(200)->addText('');
            $table->addCell(6500)->addText($setting[1]->value.'<w:br/>NIP. '.$setting[3]->value,null,['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

            if($item->id != $last_data){
                $section->addPageBreak();
            }
        }


        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        try {
            $objWriter->save(public_path($no.'_gabungan.docx'));
        } catch (Exception $e) {
            return $e;
        }

        return response()->download(public_path($no.'_gabungan.docx'))->deleteFileAfterSend(true);
    }
    /**
     * Generate DOCX for Organik Surat Tugas Gabungan
     *
     * Menghasilkan dokumen DOCX untuk surat tugas gabungan organik berdasarkan ID.
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
    public function generateOrganikDocx($id)
    {
        $paper = new Paper();
        $paper->setSize('A4');
        $phpWord = new PhpWord();
        $section = $phpWord->addSection(array('marginLeft' => 800, 'marginRight' => 800,
        'marginTop' => 600, 'marginBottom' => 600,'pageSizeW' => $paper->getWidth(), 'pageSizeH' => $paper->getHeight()));
        $phpWord->setDefaultFontName('Cambria');
        $phpWord->setDefaultFontSize(12);
        $setting = DB::table('settings')->where('key','KEPALA_KANTOR')->orWhere('key','PPK')->orWhere('key','like','NIP'.'%')->get();

        $surtug = SurtugDetil::with('nomor','pegawai','mitra')->where('surtug_id', request()->id)->orderBy('id', 'asc')->get();
        $no_surtug = DB::table('surat_tugas')->where('id',request()->id)->first();
        $no = str_replace("/","_",$no_surtug->no_surat);
        $last_record = SurtugDetil::where('surtug_id', request()->id)->orderBy('id', 'desc')->first();
        $last_data = $last_record->id;

        // return $setting;
        $kantor = "BADAN PUSAT STATISTIK KABUPATEN KARAWANG";

        foreach ($surtug as $item) {
            $hari = $item->hari*1;
            if($hari <= 1){
                $tgl_laksana3 = \Carbon\Carbon::parse($item->tgl_mulai)->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y');
                $tgl_laksana2 = \Carbon\Carbon::parse($item->tgl_mulai)->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y');
            }else{
                $tgl_laksana2 = \Carbon\Carbon::parse($item->tgl_mulai)->addDays($hari);
                $tgl_laksana3 = \Carbon\Carbon::parse($item->tgl_mulai)->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y')." - ".
                                $tgl_laksana2->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y');
            }
            $tgl_surat =\Carbon\Carbon::parse($item->nomor->tanggal)->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y');
            $section->addImage(public_path('images\bps-logo.png'),
            array(
                // 'width'         => 100,
                'height'        => 30,
                'marginTop'     => -2,
                'marginLeft'    => -1,
                'wrappingStyle' => 'behind',
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
            ));
            $phpWord->addTitleStyle(1, ['bold' => true,'size' => 12, 'allCaps' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
            $phpWord->addTitleStyle(2, ['size' => 12, 'allCaps' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
            $section->addTitle($kantor,1);
            $section->addTextBreak(1);
            $section->addTitle('Surat Tugas',1);
            $section->addTitle($item->nomor->no_surat,2);
            $section->addTextBreak(1);

            $table = $section->addTable(array('cellMargin' => 5,'unit' => TblWidth::TWIP));
            $table->addRow();
            $table->addCell(2500)->addText('Menimbang',null,array('cellMargin' => 1000));
            $table->addCell(200)->addText(':');
            $table->addCell(300)->addText('a.');
            $table->addCell(7500)->addText('Bahwa '.$item->nama_kegiatan.' merupakan salah satu survei rutin BPS.');
            $table->addRow();
            $table->addCell(2500)->addText('');
            $table->addCell(200)->addText('');
            $table->addCell(300)->addText('b.');
            $table->addCell(7500)->addText('Bahwa '.$item->nama_kegiatan.' harus dilaksanakan sesuai dengan waktu yang telah ditentukan.');
            if($no_surtug->menimbang == null){
                $section->addTextBreak(1);
            }
            $table = $section->addTable(array('cellMargin' => 5,'unit' => TblWidth::TWIP));
            $table->addRow();
            $table->addCell(2500)->addText('Mengingat');
            $table->addCell(200)->addText(':');
            $table->addCell(300)->addText('1.');
            $table->addCell(7500)->addText('Undang-Undang Nomor 16 Tahun 1997 tentang Statistik (Lembaran Negara Nomor 39 Tahun 1997, Tambahan Lembaran Negara Nomor 3683);');
            $table->addRow();
            $table->addCell(2500)->addText('');
            $table->addCell(200)->addText('');
            $table->addCell(300)->addText('2.');
            $table->addCell(7500)->addText('Peraturan Presiden Republik Indonesia Nomor 86 Tahun 2007 tentang Badan Pusat Statistik;');
            $table->addRow();
            $table->addCell(2500)->addText('');
            $table->addCell(200)->addText('');
            $table->addCell(300)->addText('3.');
            $table->addCell(7500)->addText('Peraturan Pemerintah Nomor 51 Tahun 1999 tentang Penyelenggaraan Statistik (Lembaran Negara Tahun 1999 Nomor 96, Tambahan Lembaran Negara Nomor 3854);');
            $table->addRow();
            $table->addCell(2500)->addText('');
            $table->addCell(200)->addText('');
            $table->addCell(300)->addText('4.');
            $table->addCell(7500)->addText('Peraturan Badan Pusat Statistik Nomor 8 Tahun 2020 tentang Organisasi dan Tata Kerja Badan Pusat Statistik Provinsi dan Badan Pusat Statistik Kabupaten/Kota;');
            if($no_surtug->menimbang != null){
                $table->addRow();
                $table->addCell(2500)->addText('');
                $table->addCell(200)->addText('');
                $table->addCell(300)->addText('5.');
                $table->addCell(7500)->addText($no_surtug->menimbang);
            }

            // $section->addTextBreak(1);
            $section->addText('Memberi Perintah',null,['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

            $table = $section->addTable(array('cellMargin' => 5,'unit' => TblWidth::TWIP));
            $table->addRow();
            $table->addCell(2500)->addText('Kepada');
            $table->addCell(200)->addText(':');
            $table->addCell(7500)->addText($item->pegawai->nama);
            $table->addRow();
            $table->addCell(2500)->addText('NIP');
            $table->addCell(200)->addText(':');
            $table->addCell(7500)->addText($item->pegawai->nip);
            $table->addRow();
            $table->addCell(2500)->addText('Jabatan');
            $table->addCell(200)->addText(':');
            $table->addCell(7500)->addText($item->pegawai->jabatan);
            $table->addRow();
            $table->addCell(2500)->addText('Untuk');
            $table->addCell(200)->addText(':');
            $table->addCell(7500)->addText('Melaksanakan '.$item->nama_kegiatan.' di '.$item->wilayah_kerja.' pada tanggal '.$tgl_laksana3.' dengan pembebanan dibebankan pada DIPA BPS Karawang '.$item->no_dipa);

            $section->addTextBreak(1);
            $table = $section->addTable(array('cellMargin' => 5,'unit' => TblWidth::TWIP));
            $table->addRow();
            $table->addCell(3500)->addText('');
            $table->addCell(200)->addText('');
            $table->addCell(6500)->addText('Karawang, '.$tgl_surat.'<w:br/>Kepala Badan Pusat Statistik<w:br/>Kabupaten Karawang',null,['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
            $section->addTextBreak(1);
            $table = $section->addTable(array('cellMargin' => 5,'unit' => TblWidth::TWIP));
            $table->addRow();
            $table->addCell(3500)->addText('');
            $table->addCell(200)->addText('');
            $table->addCell(6500)->addText($setting[1]->value.'<w:br/>NIP. '.$setting[3]->value,null,['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

            if($item->id != $last_data){
                $section->addPageBreak();
            }
        }


        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        try {
            $objWriter->save(public_path($no.'_gabungan.docx'));
        } catch (Exception $e) {
            return $e;
        }

        return response()->download(public_path($no.'_gabungan.docx'))->deleteFileAfterSend(true);
    }

}
