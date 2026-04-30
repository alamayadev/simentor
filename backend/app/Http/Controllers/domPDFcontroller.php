<?php
namespace App\Http\Controllers;

use App\Http\Traits\Terbilang;
use App\Models\Kegiatan;
use App\Models\Mitra;
use App\Models\Penugasan;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use iio\libmergepdf\Merger;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DomPDFController extends Controller
{
    use Terbilang;

    private const CACHE_TTL     = 3600; // 1 hour
    private const SETTINGS_KEYS = [
        'PPK', 'NIP_PPK', 'FORMAT_NO_SPK', 'FORMAT_NO_BAST',
        'TAHUN_SPK', 'TAHUN_BAST', 'KODE_RING',
    ];

    private ?Penugasan $penugasan = null;
    private ?Mitra $mitra         = null;
    private ?Kegiatan $kegiatan   = null;

    /**
     * Get cached settings to avoid repeated database queries
     */
    private function getCachedSettings(): array
    {
        return Cache::remember('pdf_settings', self::CACHE_TTL, function () {
            $settings = Setting::whereIn('key', self::SETTINGS_KEYS)
                ->orderBy('tahun', 'desc')
                ->get()
                ->groupBy('key');

            $result = [];
            foreach (self::SETTINGS_KEYS as $key) {
                $result[$key] = $settings->get($key)?->first()?->value;
            }

            return $result;
        });
    }

    /**
     * Initialize penugasan data with proper error handling
     */
    private function initializePenugasan(int $id): void
    {
        $this->penugasan = Penugasan::with(['mitra', 'kegiatan'])->findOrFail($id);

        if (! $this->penugasan->mitra || ! $this->penugasan->kegiatan) {
            throw new ModelNotFoundException('Related mitra or kegiatan not found');
        }

        $this->mitra    = $this->penugasan->mitra;
        $this->kegiatan = $this->penugasan->kegiatan;
    }

    /**
     * Get aggregated penugasan data for the same mitra and month
     */
    private function getAggregatedPenugasan(): \Illuminate\Support\Collection
    {
        return Penugasan::where('mitra_id', $this->penugasan->mitra_id)
            ->whereMonth('bln_bayar', $this->penugasan->bln_bayar->month)
            ->whereYear('bln_bayar', $this->penugasan->bln_bayar->year)
            ->groupBy(['mitra_id', 'bln_bayar'])
            ->selectRaw('mitra_id, bln_bayar, sum(nilai) as total')
            ->get();
    }

    /**
     * Get detailed penugasan data for the same mitra and month
     */
    private function getDetailedPenugasan(): \Illuminate\Database\Eloquent\Collection
    {
        return Penugasan::with(['mitra:id,nama_lengkap,nik,alamat_detail', 'kegiatan:id,nama,satuan,rate_pcl,rate_pml,rate_entri,kode_kegiatan'])
            ->where('mitra_id', $this->penugasan->mitra_id)
            ->whereMonth('bln_bayar', $this->penugasan->bln_bayar->month)
            ->whereYear('bln_bayar', $this->penugasan->bln_bayar->year)
            ->get();
    }

    /**
     * Build settings array with formatted values
     */
    private function buildSettingsArray(): array
    {
        $settings = $this->getCachedSettings();

        return [
            'ppk'           => $settings['PPK'] ?? null,
            'nipPpk'        => $settings['NIP_PPK'] ?? null,
            'nomorSPK'      => $this->formatNomor($this->penugasan->no_sk, $settings['FORMAT_NO_SPK'], $settings['TAHUN_SPK']),
            'nomorBAST'     => $this->formatNomor($this->penugasan->no_bast, $settings['FORMAT_NO_BAST'], $settings['TAHUN_BAST']),
            'tahun'         => $settings['TAHUN_SPK'] ?? null,
            'jadwalMulai'   => $this->penugasan->jangka_waktu_mulai?->locale('id')->translatedFormat('j F Y'),
            'jadwalSelesai' => $this->penugasan->jangka_waktu_selesai?->locale('id')->translatedFormat('j F Y'),
            'kodeRing'      => $settings['KODE_RING'] ?? null,
            'tahunKegiatan' => $this->kegiatan->tahun ?? null,
        ];
    }

    /**
     * Format document number with proper null checking
     */
    private function formatNomor(?int $nomor, ?string $format, ?string $tahun): ?string
    {
        if (! $nomor || ! $format || ! $tahun) {
            return null;
        }

        return sprintf('%04d%s%s', $nomor, $format, $tahun);
    }

    /**
     * Prepare common data for PDF generation
     */
    private function preparePdfData(): array
    {
        return [
            'petugas'   => $this->mitra->nama_lengkap,
            'nik'       => $this->mitra->nik,
            'pekerjaan' => $this->mitra->pekerjaan === 'Lainnya'
                ? "{$this->mitra->pekerjaan} ({$this->mitra->deskripsi_pekerjaan_lain})"
                : $this->mitra->pekerjaan,
            'alamat'    => $this->mitra->alamat_detail,
            'kecamatan' => $this->mitra->keca,
            'kabupaten' => $this->mitra->kab,
        ];
    }
    /**
     * Generate SPK (Surat Perintah Kerja) PDF
     */
    public function generateSPK(Request $request)
    {
        try {
            $request->validate(['id' => 'required|integer|exists:penugasan,id']);

            $this->initializePenugasan($request->id);

            $data              = $this->preparePdfData();
            $data['tgl_sk']    = $this->penugasan->tgl_sk;
            $data['penugasan'] = $this->getDetailedPenugasan();

            // Calculate rate_satuan based on jabatan_tugas
            $data['penugasan']->transform(function ($item) {
                $rate = 0;
                switch ($item->jabatan_tugas) {
                    case 'PCL':
                        $rate = $item->kegiatan->rate_pcl;
                        break;
                    case 'PML':
                        $rate = $item->kegiatan->rate_pml;
                        break;
                    case 'OPERATOR':
                        $rate = $item->kegiatan->rate_entri;
                        break;
                }
                $item->rate_satuan = $rate;
                return $item;
            });

            $aggregatedData = $this->getAggregatedPenugasan();
            $total          = $aggregatedData->first()?->total ?? 0;

            $dateInfo = $this->getDateInfo($this->penugasan->tgl_sk);

            $contents = [
                'settings'  => $this->buildSettingsArray(),
                'data'      => $data,
                'total'     => $total,
                'hari_sk'   => $dateInfo['hari'],
                'tgl_sk'    => $dateInfo['tgl'],
                'bln_sk'    => $dateInfo['bln'],
                'thn_sk'    => $dateInfo['thn'],
                'terbilang' => ucwords($this->pembilang($total) . ' rupiah'),
            ];

            return $this->generateMergedPdf($contents, 'spk');

        } catch (\Exception $e) {
            return $this->handleError($e, 'Failed to generate SPK');
        }
    }

    /**
     * Generate BAST (Berita Acara Serah Terima) PDF
     */
    public function generateBAST(Request $request)
    {
        try {
            $request->validate(['id' => 'required|integer|exists:penugasan,id']);

            $this->initializePenugasan($request->id);

            $data              = $this->preparePdfData();
            $data['tgl_bast']  = $this->penugasan->tgl_bast;
            $data['penugasan'] = $this->getDetailedPenugasan();

            $aggregatedData = $this->getAggregatedPenugasan();
            $total          = $aggregatedData->first()?->total ?? 0;

            $dateInfo = $this->getDateInfo($this->penugasan->tgl_bast);

            $contents = [
                'settings'       => $this->buildSettingsArray(),
                'data'           => $data,
                'total'          => $total,
                'hari_bast'      => $dateInfo['hari'],
                'tgl_bast'       => $dateInfo['tgl'],
                'bln_bast'       => $dateInfo['bln'],
                'thn_bast'       => $dateInfo['thn'],
                'thn_bast_angka' => $dateInfo['thn_angka'],
                'terbilang'      => ucwords($this->pembilang($total) . ' rupiah'),
            ];

            return $this->generateSinglePdf($contents, 'bast');

        } catch (\Exception $e) {
            return $this->handleError($e, 'Failed to generate BAST');
        }
    }

    /**
     * Extract date information for PDF generation
     */
    private function getDateInfo(?string $date): array
    {
        if (! $date) {
            return ['hari' => null, 'tgl' => null, 'bln' => null, 'thn' => null];
        }

        $carbon = Carbon::parse($date);

        return [
            'hari'      => $carbon->locale('id')->translatedFormat('l'),
            'tgl'       => $this->pembilang($carbon->day),
            'bln'       => $carbon->locale('id')->translatedFormat('F'),
            'thn'       => $this->pembilang($carbon->year),
            'thn_angka' => $carbon->year,
        ];
    }

    /**
     * Generate merged PDF (for SPK with attachment)
     */
    private function generateMergedPdf(array $contents, string $type)
    {
        $merger = new Merger();

        // Main document
        $mainPdf = PDF::loadView("template-{$type}", compact('contents'))
            ->setOptions([
                'fontDir'              => public_path('spk-fonts'),
                'fontCache'            => public_path('spk-fonts'),
                'defaultFont'          => 'Poppins',
                'isRemoteEnabled'      => true,
                'isHtml5ParserEnabled' => true,
            ])
            ->setPaper('a4', 'portrait');

        $merger->addRaw($mainPdf->output());

        // Attachment (only for SPK)
        if ($type === 'spk') {
            $attachmentPdf = PDF::loadView('lampiran-spk', compact('contents'))
                ->setPaper('a4', 'landscape');
            $merger->addRaw($attachmentPdf->output());
        }

        $filename = $this->generateFilename($type);

        return response($merger->merge())
            ->withHeaders([
                'Content-Type'        => 'application/pdf',
                'Cache-Control'       => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0',
                'Pragma'              => 'no-cache',
                'Expires'             => '0',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
    }

    /**
     * Generate single PDF (for BAST)
     */
    private function generateSinglePdf(array $contents, string $type)
    {
        $pdf = PDF::loadView("template-{$type}", compact('contents'))
            ->setOptions([
                'isRemoteEnabled'      => true,
                'isHtml5ParserEnabled' => true,
            ])
            ->setPaper('a4', 'portrait');

        $filename = $this->generateFilename($type);

        return response($pdf->output())
            ->withHeaders([
                'Content-Type'        => 'application/pdf',
                'Cache-Control'       => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0',
                'Pragma'              => 'no-cache',
                'Expires'             => '0',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
    }

    /**
     * Generate filename for PDF download
     */
    private function generateFilename(string $type): string
    {
        $nomor = $type === 'spk' ? $this->penugasan->no_sk : $this->penugasan->no_bast;
        $nik   = $this->mitra->nik;

        return sprintf('%s_%04d_%s.pdf', strtoupper($type), $nomor, $nik);
    }

    /**
     * Handle errors gracefully
     */
    private function handleError(\Exception $e, string $message)
    {
        Log::error("{$message}: " . $e->getMessage(), [
            'trace'   => $e->getTraceAsString(),
            'request' => request()->all(),
        ]);

        if (request()->expectsJson()) {
            return response()->json([
                'error'   => $message,
                'message' => $e->getMessage(),
            ], 500);
        }

        return back()->with('error', $message)->withInput();
    }
}
