<?php

namespace App\Http\Controllers;

use App\Models\SurtugDetil;
use App\Models\Uu;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\SimpleType\TblWidth;

class SuratTugasMitraBladeController extends Controller
{
    /**
     * Generate Surat Tugas (Mitra) DOCX using a Blade/HTML template.
     *
     * @param int $id surtug_id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function generate(int $id)
    {
        $records = SurtugDetil::with(['nomor', 'pegawai', 'mitra'])
            ->where('surtug_id', $id)
            ->orderBy('id')
            ->get();

        if ($records->isEmpty()) {
            abort(404, 'Surat tugas tidak ditemukan');
        }

        $header = $records->first();
        $suratMeta = DB::table('surat_tugas')->where('id', $id)->first();
        $settings = DB::table('settings')
            ->where('key', 'KEPALA_KANTOR')
            ->orWhere('key', 'PPK')
            ->orWhere('key', 'like', 'NIP%')
            ->get()
            ->keyBy('key');

        $hari = (int) ($header->hari ?? 0);
        if ($hari <= 1) {
            $tanggalMulai = Carbon::parse($header->tgl_mulai);
            $tanggalSelesai = $tanggalMulai;
        } else {
            $tanggalMulai = Carbon::parse($header->tgl_mulai);
            $tanggalSelesai = (clone $tanggalMulai)->addDays($hari);
        }

        $tanggalPelaksanaan = $tanggalMulai
            ->locale('id')
            ->settings(['formatFunction' => 'translatedFormat'])
            ->format('j F Y');

        if ($tanggalMulai->ne($tanggalSelesai)) {
            $tanggalPelaksanaan .= ' - ' . $tanggalSelesai
                ->locale('id')
                ->settings(['formatFunction' => 'translatedFormat'])
                ->format('j F Y');
        }

        $tanggalSurat = Carbon::parse($header->nomor->tanggal ?? now())
            ->locale('id')
            ->settings(['formatFunction' => 'translatedFormat'])
            ->format('j F Y');

        $menimbangItems = [
            'Bahwa ' . $header->nama_kegiatan . ' merupakan salah satu survei rutin BPS;',
            'Bahwa ' . $header->nama_kegiatan . ' harus dilaksanakan sesuai dengan waktu yang telah ditentukan.',
        ];

        if (! empty($suratMeta->menimbang)) {
            $menimbangItems[] = $suratMeta->menimbang;
        }

        $mengingatItems = Uu::where('jenis', 'KPA')->orderBy('id')->pluck('detil')->toArray();
        if (empty($mengingatItems)) {
            $mengingatItems = [
                'Undang-undang Nomor 16 Tahun 1997 tentang Statistik;',
                'Peraturan Presiden Republik Indonesia Nomor 86 Tahun 2007 tentang Badan Pusat Statistik;',
                'Peraturan Badan Pusat Statistik Nomor 8 Tahun 2020 tentang Organisasi dan Tata Kerja Badan Pusat Statistik Provinsi dan Badan Pusat Statistik Kabupaten/Kota;',
            ];
        }

        $personnel = $records->map(function ($item, $index) {
            return [
                'no'       => $index + 1,
                'nama'     => $item->mitra->nama_lengkap ?? '-',
                'sobat_id' => $item->mitra->sobat_id ?? '-',
                'kecamatan'=> $item->wilayah_kerja ?? '-',
            ];
        });

        $data = [
            'kantor'              => 'BADAN PUSAT STATISTIK KABUPATEN KARAWANG',
            'noSurat'             => $header->nomor->no_surat ?? '',
            'tanggalSurat'        => $tanggalSurat,
            'namaKegiatan'        => $header->nama_kegiatan ?? '',
            'tanggalPelaksanaan'  => $tanggalPelaksanaan,
            'noDipa'              => $header->no_dipa ?? '-',
            'menimbangItems'      => $menimbangItems,
            'mengingatItems'      => $mengingatItems,
            'kepadaText'          => 'Sesuai Lampiran 1',
            'jabatanText'         => 'Mitra Statistik',
            'kepalaNama'          => optional($settings->get('KEPALA_KANTOR'))->value ?? 'Kepala Kantor',
            'kepalaNip'           => optional($settings->first(function ($item) {
                                        return Str::startsWith($item->key, 'NIP');
                                    }))->value ?? 'NIP',
            'lokasi'              => $header->wilayah_kerja ?? '-',
            'personnel'           => $personnel,
        ];

        // Helper to clean Blade HTML into XHTML-ish for PhpWord
        $cleanHtml = function (string $html) {
            $styleBlock = '';
            if (preg_match('/<style.*?>(.*?)<\\/style>/is', $html, $styleMatch)) {
                $styleBlock = '<style>' . $styleMatch[1] . '</style>';
            }
            $html = preg_replace('/<!DOCTYPE.+?>/s', '', $html);
            $html = preg_replace('/<head.*?>.*?<\\/head>/si', '', $html);
            if (preg_match('/<body[^>]*>(.*)<\\/body>/si', $html, $matches)) {
                $html = $matches[1];
            }
            $html = str_ireplace(['<br>', '<br >'], '<br/>', $html);
            $html = preg_replace('/<img([^>]*?)(?<!\\/)>/i', '<img$1/>', $html);
            return $styleBlock . '<div>' . $html . '</div>';
        };

        // Render header
        $headerHtml = View::make('surat_tugas_mitra.header', $data)->render();

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Cambria');
        $phpWord->setDefaultFontSize(12);
        $phpWord->addNumberingStyle('menimbangNumbering', [
            'type'   => 'multilevel',
            'levels' => [
                [
                    'start'   => 1,
                    'format'  => 'decimal',
                    'text'    => '%1.',
                    'left'    => 360,
                    'hanging' => 360,
                ],
            ],
        ]);
        $phpWord->addNumberingStyle('mengingatNumbering', [
            'type'   => 'multilevel',
            'levels' => [
                [
                    'start'   => 1,
                    'format'  => 'decimal',
                    'text'    => '%1.',
                    'left'    => 360,
                    'hanging' => 360,
                ],
            ],
        ]);

        $section = $phpWord->addSection([
            'marginLeft'   => 800,
            'marginRight'  => 800,
            'marginTop'    => 600,
            'marginBottom' => 600,
        ]);

        Html::addHtml($section, $cleanHtml($headerHtml), false, false);
        $section->addTextBreak(1);

        $pTight = ['spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => 1];
        $compactTableStyle = [
            'width'            => 100 * 50,
            'unit'             => 'pct',
            'layout'           => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,
            'cellMarginTop'    => 40,
            'cellMarginBottom' => 40,
            'cellMarginLeft'   => 80,
            'cellMarginRight'  => 80,
        ];

        // Menimbang / Mengingat table with fixed widths
        $table = $section->addTable($compactTableStyle);
        $table->addRow();
        $table->addCell(1500)->addText('Menimbang', null, $pTight);
        $table->addCell(300)->addText(':', null, $pTight);
        $menimbangCell = $table->addCell(8400);
        $numberStyleMenimbang = 'menimbangNumbering';
        $numberStyleMengingat = 'mengingatNumbering';
        foreach ($menimbangItems as $item) {
            $menimbangCell->addListItem($item, 0, null, $numberStyleMenimbang, $pTight + ['alignment' => 'both']);
        }
        $table->addRow();
        $table->addCell(1500)->addText('Mengingat', null, $pTight);
        $table->addCell(300)->addText(':', null, $pTight);
        $mengingatCell = $table->addCell(8400);
        foreach ($mengingatItems as $item) {
            $mengingatCell->addListItem($item, 0, null, $numberStyleMengingat, $pTight + ['alignment' => 'both']);
        }

        $section->addTextBreak(1);
        $section->addText('Memberi Perintah', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

        // Detail table with fixed widths
        $detailTable = $section->addTable($compactTableStyle);
        $detailTable->addRow();
        $detailTable->addCell(1500)->addText('Kepada', null, $pTight);
        $detailTable->addCell(300)->addText(':', null, $pTight);
        $detailTable->addCell(8400)->addText($data['kepadaText'], null, $pTight);

        $detailTable->addRow();
        $detailTable->addCell(1500)->addText('Sobat ID', null, $pTight);
        $detailTable->addCell(300)->addText(':', null, $pTight);
        $detailTable->addCell(8400)->addText($data['kepadaText'], null, $pTight);

        $detailTable->addRow();
        $detailTable->addCell(1500)->addText('Jabatan', null, $pTight);
        $detailTable->addCell(300)->addText(':', null, $pTight);
        $detailTable->addCell(8400)->addText($data['jabatanText'], null, $pTight);

        $detailTable->addRow();
        $detailTable->addCell(1500)->addText('Untuk', null, $pTight);
        $detailTable->addCell(300)->addText(':', null, $pTight);
        $detailTable->addCell(8400)->addText('Melaksanakan ' . $data['namaKegiatan'] . ' di ' . $data['lokasi'] . ' pada tanggal ' . $data['tanggalPelaksanaan'] . ' dengan pembebanan dibebankan pada DIPA BPS Karawang ' . $data['noDipa'] . '.', null, $pTight);

        $section->addTextBreak(1);
        $sigTable = $section->addTable(['width' => 100 * 50, 'unit' => 'pct']);
        $sigTable->addRow();
        $sigTable->addCell(3500)->addText('');
        $sigTable->addCell(300)->addText('');
        $sigTable->addCell(7000)->addText('Karawang, ' . $data['tanggalSurat'] . '<w:br/>Kepala Badan Pusat Statistik<w:br/>Kabupaten Karawang', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section->addTextBreak(1);
        $sigTable = $section->addTable(['width' => 100 * 50, 'unit' => 'pct']);
        $sigTable->addRow();
        $sigTable->addCell(3500)->addText('');
        $sigTable->addCell(300)->addText('');
        $sigTable->addCell(7000)->addText($data['kepalaNama'] . '<w:br/>NIP. ' . $data['kepalaNip'], null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

        $section->addPageBreak();

        // Lampiran with fixed column widths (5%, 45%, 25%, 25%)
        $section->addText('Lampiran 1');
        $section->addText('Surat Tugas No. ' . $data['noSurat']);
        $section->addText('Petugas ' . $data['namaKegiatan'], null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

        $lampiranTable = $section->addTable([
            'width'  => 100 * 50,
            'unit'   => 'pct',
            'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,
            'borderSize' => 6,
            'borderColor'=> '000000',
        ]);

        $cellNo   = ['width' => 5 * 50, 'unit' => TblWidth::PERCENT];
        $cellNama = ['width' => 45 * 50, 'unit' => TblWidth::PERCENT];
        $cellSid  = ['width' => 25 * 50, 'unit' => TblWidth::PERCENT];
        $cellKec  = ['width' => 25 * 50, 'unit' => TblWidth::PERCENT];

        $lampiranTable->addRow();
        $lampiranTable->addCell(null, $cellNo)->addText('No', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $lampiranTable->addCell(null, $cellNama)->addText('Nama');
        $lampiranTable->addCell(null, $cellSid)->addText('Sobat ID');
        $lampiranTable->addCell(null, $cellKec)->addText('Kecamatan');

        foreach ($data['personnel'] as $person) {
            $lampiranTable->addRow();
            $lampiranTable->addCell(null, $cellNo)->addText($person['no'], null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
            $lampiranTable->addCell(null, $cellNama)->addText($person['nama']);
            $lampiranTable->addCell(null, $cellSid)->addText($person['sobat_id']);
            $lampiranTable->addCell(null, $cellKec)->addText($person['kecamatan']);
        }

        $fileName = Str::slug($data['noSurat'] ?? 'surat_tugas', '_') . '_blade_' . time() . '.docx';
        $tempPath = storage_path('app/temp/' . $fileName);
        if (! is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        IOFactory::createWriter($phpWord, 'Word2007')->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }
}
