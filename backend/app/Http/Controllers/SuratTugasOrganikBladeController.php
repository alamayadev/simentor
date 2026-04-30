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

class SuratTugasOrganikBladeController extends Controller
{
    /**
    * Generate Surat Tugas (Organik) DOCX using Blade + PhpWord tables.
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

        $first = $records->first();
        $suratMeta = DB::table('surat_tugas')->where('id', $id)->first();
        $settings = DB::table('settings')
            ->where('key', 'KEPALA_KANTOR')
            ->orWhere('key', 'PPK')
            ->orWhere('key', 'like', 'NIP%')
            ->get()
            ->keyBy('key');

        $hari = (int) ($first->hari ?? 0);
        $tanggalMulai = Carbon::parse($first->tgl_mulai);
        $tanggalSelesai = $hari <= 1 ? $tanggalMulai : (clone $tanggalMulai)->addDays($hari);

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

        $tanggalSurat = Carbon::parse($first->nomor->tanggal ?? now())
            ->locale('id')
            ->settings(['formatFunction' => 'translatedFormat'])
            ->format('j F Y');

        $menimbangItems = [
            'Bahwa ' . $first->nama_kegiatan . ' merupakan salah satu survei rutin BPS;',
            'Bahwa ' . $first->nama_kegiatan . ' harus dilaksanakan sesuai dengan waktu yang telah ditentukan.',
        ];
        if (! empty($suratMeta->menimbang)) {
            $menimbangItems[] = $suratMeta->menimbang;
        }

        $mengingatItems = Uu::where('jenis', 'Kepala')->orderBy('id')->pluck('detil')->toArray();
        if (empty($mengingatItems)) {
            $mengingatItems = [
                'Undang-undang Nomor 16 Tahun 1997 tentang Statistik;',
                'Peraturan Presiden Republik Indonesia Nomor 86 Tahun 2007 tentang Badan Pusat Statistik;',
                'Peraturan Badan Pusat Statistik Nomor 8 Tahun 2020 tentang Organisasi dan Tata Kerja Badan Pusat Statistik Provinsi dan Badan Pusat Statistik Kabupaten/Kota;',
            ];
        }

        $isPegawai = $first->pegawai_id > 0;

        $personnel = $records->map(function ($item, $index) use ($isPegawai) {
            if ($isPegawai) {
                return [
                    'no'       => $index + 1,
                    'nama'     => $item->pegawai->nama ?? '-',
                    'nip'      => $item->pegawai->nip ?? '-',
                    'jabgol'   => trim(($item->pegawai->jabatan ?? '-') . ' / ' . ($item->pegawai->gol ?? '-')),
                ];
            }
            return [
                'no'       => $index + 1,
                'nama'     => $item->mitra->nama_lengkap ?? '-',
                'sobat_id' => $item->mitra->sobat_id ?? '-',
                'kecamatan'=> $item->wilayah_kerja ?? '-',
            ];
        });

        $data = [
            'kantor'             => 'BADAN PUSAT STATISTIK KABUPATEN KARAWANG',
            'noSurat'            => $first->nomor->no_surat ?? '',
            'tanggalSurat'       => $tanggalSurat,
            'namaKegiatan'       => $first->nama_kegiatan ?? '',
            'tanggalPelaksanaan' => $tanggalPelaksanaan,
            'noDipa'             => $first->no_dipa ?? '-',
            'menimbangItems'     => $menimbangItems,
            'mengingatItems'     => $mengingatItems,
            'kepalaNama'         => optional($settings->get('KEPALA_KANTOR'))->value ?? 'Kepala Kantor',
            'kepalaNip'          => optional($settings->first(function ($item) {
                                        return Str::startsWith($item->key, 'NIP');
                                    }))->value ?? 'NIP',
            'lokasi'             => $first->wilayah_kerja ?? '-',
            'personnel'          => $personnel,
            'isPegawai'          => $isPegawai,
        ];

        // Blade header
        $headerHtml = View::make('surat_tugas_organik.header', $data)->render();

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

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Cambria');
        $phpWord->setDefaultFontSize(12);
        $phpWord->addNumberingStyle('menimbangNumbering', [
            'type'   => 'multilevel',
            'levels' => [['start' => 1, 'format' => 'decimal', 'text' => '%1.', 'left' => 360, 'hanging' => 360]],
        ]);
        $phpWord->addNumberingStyle('mengingatNumbering', [
            'type'   => 'multilevel',
            'levels' => [['start' => 1, 'format' => 'decimal', 'text' => '%1.', 'left' => 360, 'hanging' => 360]],
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

        // Menimbang / Mengingat
        $table = $section->addTable($compactTableStyle);
        $table->addRow();
        $table->addCell(1500)->addText('Menimbang', null, $pTight);
        $table->addCell(300)->addText(':', null, $pTight);
        $menimbangCell = $table->addCell(8400);
        foreach ($menimbangItems as $item) {
            $menimbangCell->addListItem($item, 0, null, 'menimbangNumbering', $pTight + ['alignment' => 'both']);
        }
        $table->addRow();
        $table->addCell(1500)->addText('Mengingat', null, $pTight);
        $table->addCell(300)->addText(':', null, $pTight);
        $mengingatCell = $table->addCell(8400);
        foreach ($mengingatItems as $item) {
            $mengingatCell->addListItem($item, 0, null, 'mengingatNumbering', $pTight + ['alignment' => 'both']);
        }

        $section->addTextBreak(1);
        $section->addText('Memberi Perintah', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

        // Detail table
        $detailTable = $section->addTable($compactTableStyle);
        $detailTable->addRow();
        $detailTable->addCell(1500)->addText('Kepada', null, $pTight);
        $detailTable->addCell(300)->addText(':', null, $pTight);
        $detailTable->addCell(8400)->addText($personnel->count() > 1 ? 'Sesuai Lampiran 1' : ($isPegawai ? ($first->pegawai->nama ?? '-') : ($first->mitra->nama_lengkap ?? '-')), null, $pTight);

        if ($isPegawai) {
            $detailTable->addRow();
            $detailTable->addCell(1500)->addText('NIP', null, $pTight);
            $detailTable->addCell(300)->addText(':', null, $pTight);
            $detailTable->addCell(8400)->addText($personnel->count() > 1 ? 'Sesuai Lampiran 1' : ($first->pegawai->nip ?? '-'), null, $pTight);

            $detailTable->addRow();
            $detailTable->addCell(1500)->addText('Jabatan / Gol', null, $pTight);
            $detailTable->addCell(300)->addText(':', null, $pTight);
            $detailTable->addCell(8400)->addText($personnel->count() > 1 ? 'Sesuai Lampiran 1' : trim(($first->pegawai->jabatan ?? '-') . ' / ' . ($first->pegawai->gol ?? '-')), null, $pTight);
        } else {
            $detailTable->addRow();
            $detailTable->addCell(1500)->addText('Sobat ID', null, $pTight);
            $detailTable->addCell(300)->addText(':', null, $pTight);
            $detailTable->addCell(8400)->addText($personnel->count() > 1 ? 'Sesuai Lampiran 1' : ($first->mitra->sobat_id ?? '-'), null, $pTight);
        }

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

        // Lampiran table with fixed widths
        $lampiranTable = $section->addTable([
            'width'      => 100 * 50,
            'unit'       => 'pct',
            'layout'     => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,
            'borderSize' => 6,
            'borderColor'=> '000000',
        ]);

        if ($isPegawai) {
            $lampiranTable->addRow();
            $lampiranTable->addCell(null, ['width' => 5 * 50, 'unit' => TblWidth::PERCENT])->addText('No', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
            $lampiranTable->addCell(null, ['width' => 45 * 50, 'unit' => TblWidth::PERCENT])->addText('Nama');
            $lampiranTable->addCell(null, ['width' => 25 * 50, 'unit' => TblWidth::PERCENT])->addText('NIP');
            $lampiranTable->addCell(null, ['width' => 25 * 50, 'unit' => TblWidth::PERCENT])->addText('Jabatan / Gol');

            foreach ($personnel as $person) {
                $lampiranTable->addRow();
                $lampiranTable->addCell(null, ['width' => 5 * 50, 'unit' => TblWidth::PERCENT])->addText($person['no'], null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
                $lampiranTable->addCell(null, ['width' => 45 * 50, 'unit' => TblWidth::PERCENT])->addText($person['nama']);
                $lampiranTable->addCell(null, ['width' => 25 * 50, 'unit' => TblWidth::PERCENT])->addText($person['nip']);
                $lampiranTable->addCell(null, ['width' => 25 * 50, 'unit' => TblWidth::PERCENT])->addText($person['jabgol']);
            }
        } else {
            $lampiranTable->addRow();
            $lampiranTable->addCell(null, ['width' => 5 * 50, 'unit' => TblWidth::PERCENT])->addText('No', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
            $lampiranTable->addCell(null, ['width' => 45 * 50, 'unit' => TblWidth::PERCENT])->addText('Nama');
            $lampiranTable->addCell(null, ['width' => 25 * 50, 'unit' => TblWidth::PERCENT])->addText('Sobat ID');
            $lampiranTable->addCell(null, ['width' => 25 * 50, 'unit' => TblWidth::PERCENT])->addText('Kecamatan');

            foreach ($personnel as $person) {
                $lampiranTable->addRow();
                $lampiranTable->addCell(null, ['width' => 5 * 50, 'unit' => TblWidth::PERCENT])->addText($person['no'], null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
                $lampiranTable->addCell(null, ['width' => 45 * 50, 'unit' => TblWidth::PERCENT])->addText($person['nama']);
                $lampiranTable->addCell(null, ['width' => 25 * 50, 'unit' => TblWidth::PERCENT])->addText($person['sobat_id']);
                $lampiranTable->addCell(null, ['width' => 25 * 50, 'unit' => TblWidth::PERCENT])->addText($person['kecamatan']);
            }
        }

        $fileName = Str::slug($data['noSurat'] ?? 'surat_tugas', '_') . '_organik_blade_' . time() . '.docx';
        $tempPath = storage_path('app/temp/' . $fileName);
        if (! is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        IOFactory::createWriter($phpWord, 'Word2007')->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }
}
