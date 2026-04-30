<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class PenugasanExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected Collection $data;

    public function __construct(Collection $penugasan)
    {
        $this->data = $penugasan;
    }

    public function collection(): Collection
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'No',
            'Kegiatan',
            'Fungsi',
            'Jabatan Tugas',
            'Nama Mitra',
            'Sobat ID',
            'Volume',
            'Nilai (Rp)',
            'Nilai Pulsa (Rp)',
            'Bulan Bayar',
            'No BAST',
            'Tgl BAST',
            'No SK',
            'Tgl SK',
            'Mulai',
            'Selesai',
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row->kegiatan->nama ?? '-',
            $row->kegiatan->fungsi?->value ?? '-',
            $row->jabatan_tugas ?? '-',
            $row->mitra->nama_lengkap ?? '-',
            $row->mitra->sobat_id ?? '-',
            $row->volume,
            $row->nilai,
            $row->nilai_pulsa,
            $row->bln_bayar ? \Carbon\Carbon::parse($row->bln_bayar)->format('Y-m') : '-',
            $row->no_bast ?? '-',
            $row->tgl_bast ? \Carbon\Carbon::parse($row->tgl_bast)->format('Y-m-d') : '-',
            $row->no_sk ?? '-',
            $row->tgl_sk ? \Carbon\Carbon::parse($row->tgl_sk)->format('Y-m-d') : '-',
            $row->jangka_waktu_mulai ? \Carbon\Carbon::parse($row->jangka_waktu_mulai)->format('Y-m-d') : '-',
            $row->jangka_waktu_selesai ? \Carbon\Carbon::parse($row->jangka_waktu_selesai)->format('Y-m-d') : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Penugasan';
    }
}
