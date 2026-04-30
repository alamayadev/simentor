<?php

namespace App\Exports;

use App\Models\MonitoringKegiatan;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MonitoringKegiatanExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $kegiatanId;
    protected $dynamicHeadings = [];

    public function __construct($kegiatanId)
    {
        $this->kegiatanId = $kegiatanId;
        
        // Collect dynamic columns in constructor to ensure they're available for headings()
        $this->collectDynamicColumns();
    }
    
    private function collectDynamicColumns()
    {
        // First, collect all possible dynamic column names from ALL monitoring data for this kegiatan
        $allMonitoringData = MonitoringKegiatan::where('kegiatan_id', $this->kegiatanId)->get();
        
        $dynamicColumns = [];
        foreach ($allMonitoringData as $item) {
            $detilData = $item->detil_data ?? [];
            if (is_array($detilData)) {
                $dynamicColumns = array_merge($dynamicColumns, array_keys($detilData));
            }
        }
        
        $this->dynamicHeadings = array_unique($dynamicColumns);
        
        // Sort dynamic columns alphabetically for consistent ordering
        sort($this->dynamicHeadings);
    }

    public function collection()
    {
        // Get the main data with relationships for export
        $monitoringData = MonitoringKegiatan::with(['kegiatan', 'kecamatan', 'desa', 'monitoringConfig.detilConfigurations'])
            ->where('kegiatan_id', $this->kegiatanId)
            ->get();
        
        // Flatten the data similar to index function
        $flattenedData = $monitoringData->map(function ($item) {
            $baseData = [
                'id' => $item->id,
                'fungsi' => $item->fungsi,
                'kegiatan_id' => $item->kegiatan_id,
                'kec_id' => $item->kec_id,
                'desa_id' => $item->desa_id,
                'kode_sampel' => $item->kode_sampel,
                'monitoring_kegiatan_config_id' => $item->monitoring_kegiatan_config_id,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'kegiatan' => $item->kegiatan->nama ?? null,
                'nmkec' => $item->kecamatan->nmkec ?? null,
                'nmdesa' => $item->desa->nmdesa ?? null,
            ];
            
            // Flatten detil_data into individual columns
            $detilData = $item->detil_data ?? [];
            foreach ($detilData as $key => $value) {
                $baseData[$key] = $value;
            }
            
            return $baseData;
        });
        
        return $flattenedData;
    }

    public function headings(): array
    {
        // Base headings
        $headings = [
            'id',
            'fungsi',
            'kegiatan_id',
            'kec_id',
            'desa_id',
            'kode_sampel',
            'kegiatan',
            'nmkec',
            'nmdesa',
        ];
        
        // Add dynamic column headings from detil_data
        $headings = array_merge($headings, $this->dynamicHeadings);

        return $headings;
    }

    public function map($monitoringKegiatan): array
    {
        // Base data fields
        $data = [
            $monitoringKegiatan['id'],
            $monitoringKegiatan['fungsi'],
            $monitoringKegiatan['kegiatan_id'],
            $monitoringKegiatan['kec_id'],
            $monitoringKegiatan['desa_id'],
            $monitoringKegiatan['kode_sampel'],
            $monitoringKegiatan['kegiatan'],
            $monitoringKegiatan['nmkec'],
            $monitoringKegiatan['nmdesa'],
        ];
        
        // Add dynamic column values
        foreach ($this->dynamicHeadings as $column) {
            $data[] = $monitoringKegiatan[$column] ?? null;
        }
        
        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
            
            // Auto-size columns for better readability (base columns)
            'A' => ['width' => 10],
            'B' => ['width' => 20],
            'C' => ['width' => 15],
            'D' => ['width' => 15],
            'E' => ['width' => 15],
            'F' => ['width' => 25],
            'G' => ['width' => 20],
            'H' => ['width' => 20],
            'I' => ['width' => 20],
        ];
        
        // Add styles for dynamic columns
        $columnLetter = 'J';
        foreach ($this->dynamicHeadings as $column) {
            $styles[$columnLetter] = ['width' => 20];
            $columnLetter++;
        }
        
        return $styles;
    }

    public function title(): string
    {
        return 'Monitoring Kegiatan ' . $this->kegiatanId;
    }
}