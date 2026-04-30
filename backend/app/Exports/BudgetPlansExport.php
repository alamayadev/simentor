<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class BudgetPlansExport
{
    private const TEMPLATE_FILE = 'template_perencanaan_anggaran.xlsx';

    private const MONTH_ABBREVIATIONS = ['JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AGU', 'SEP', 'OKT', 'NOV', 'DES'];

    private const FIRST_MONTH_COLUMN_INDEX = 20; // T

    private const LAST_MONTH_COLUMN_INDEX = 31; // AE

    private const SISA_PAGU_COLUMN = 'AF';

    private const LAST_COLUMN = 'AG';

    private array $data;

    private int $year;

    public function __construct(array $exportData, int $year = 0)
    {
        $this->year = $year ?: now()->year;
        $this->data = $this->formatData($exportData);
    }

    public function toSpreadsheet(): Spreadsheet
    {
        $templatePath = public_path(self::TEMPLATE_FILE);
        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Rencana_{$this->year}");

        $styles = [
            'total' => $sheet->getStyle('A9:X9')->exportArray(),
            'program' => $sheet->getStyle('A10:X10')->exportArray(),
            'activity' => $sheet->getStyle('A11:X11')->exportArray(),
            'output' => $sheet->getStyle('A12:X12')->exportArray(),
            'detail' => $sheet->getStyle('A24:X24')->exportArray(),
        ];
        $rowHeight = $sheet->getRowDimension(9)->getRowHeight();

        $this->replaceSummaryColumns($sheet);

        $highestRow = $sheet->getHighestRow();
        if ($highestRow >= 9) {
            $sheet->removeRow(9, $highestRow - 8);
        }

        $this->setReportHeaders($sheet);
        $this->writeRows($sheet, $styles, $rowHeight);

        return $spreadsheet;
    }

    private function formatData(array $exportData): array
    {
        return array_map(function ($row) {
            $description = (string) ($row->formatted_description ?? '');
            $description = preg_replace('/<br\s*\/?>/i', "\n", $description) ?? $description;
            $description = html_entity_decode(strip_tags($description), ENT_QUOTES | ENT_HTML5, 'UTF-8');

            return [
                'description' => trim($description),
                'item_description' => trim((string) ($row->description ?? '')),
                'program_code' => trim((string) ($row->program_code ?? '')),
                'program_name' => trim((string) ($row->program_name ?? '')),
                'activity_code' => trim((string) ($row->activity_code ?? '')),
                'activity_name' => trim((string) ($row->activity_name ?? '')),
                'output_code' => trim((string) ($row->output_code ?? '')),
                'output_name' => trim((string) ($row->output_name ?? '')),
                'account_code' => trim((string) ($row->account_code ?? '')),
                'account_name' => trim((string) ($row->account_name ?? '')),
                'pagu_revisi' => (float) ($row->pagu_revisi ?? 0),
                'months' => array_map('floatval', array_slice($row->months ?? [], 0, 12)),
            ];
        }, $exportData);
    }

    private function replaceSummaryColumns($sheet): void
    {
        foreach (['A1:X1', 'A2:X2', 'A3:X3', 'T7:V7', 'W7:X7'] as $range) {
            if ($this->hasMerge($sheet, $range)) {
                $sheet->unmergeCells($range);
            }
        }

        $sheet->insertNewColumnBefore('W', 9);

        foreach (['A1:AG1', 'A2:AG2', 'A3:AG3', 'T7:AE7', 'AF7:AG7'] as $range) {
            $sheet->mergeCells($range);
        }

        foreach (range(self::FIRST_MONTH_COLUMN_INDEX, self::LAST_MONTH_COLUMN_INDEX) as $columnIndex) {
            $column = Coordinate::stringFromColumnIndex($columnIndex);
            $sheet->getColumnDimension($column)->setWidth(12.42578125);
            $sheet->duplicateStyle($sheet->getStyle('T7'), "{$column}7");
            $sheet->duplicateStyle($sheet->getStyle('T8'), "{$column}8");
        }
    }

    private function setReportHeaders($sheet): void
    {
        $sheet->setCellValue('A1', "LAPORAN RENCANA REALISASI TA {$this->year}");
        $sheet->setCellValue('A3', "Periode Januari s.d. Desember {$this->year}");
        $sheet->setCellValue('T7', "Rencana Realisasi TA {$this->year}");
        $sheet->setCellValue(self::SISA_PAGU_COLUMN.'7', 'SISA PAGU');

        foreach (self::MONTH_ABBREVIATIONS as $index => $month) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex(self::FIRST_MONTH_COLUMN_INDEX + $index).'8', $month);
        }

        $sheet->getStyle('T7:'.self::LAST_COLUMN.'8')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
    }

    private function writeRows($sheet, array $styles, float $rowHeight): void
    {
        $rowNumber = 9;
        $reportRows = $this->buildReportRows();

        foreach ($reportRows as $row) {
            $sheet->getRowDimension($rowNumber)->setRowHeight($rowHeight);
            $this->applyRowStyle($sheet, $rowNumber, $styles[$row['type']] ?? $styles['detail']);
            $this->writeReportRow($sheet, $rowNumber, $row);

            $rowNumber++;
        }
    }

    private function buildReportRows(): array
    {
        $rows = [];
        $reportTotal = $this->summarize($this->data);

        $rows[] = [
            'type' => 'total',
            'label' => 'JUMLAH SELURUHNYA',
            ...$reportTotal,
        ];

        foreach ($this->groupBy($this->data, 'program_code') as $programItems) {
            $program = $programItems[0];
            $rows[] = [
                'type' => 'program',
                'code' => $program['program_code'],
                'label' => $program['program_name'],
                ...$this->summarize($programItems),
            ];

            foreach ($this->groupBy($programItems, 'activity_code') as $activityItems) {
                $activity = $activityItems[0];
                $rows[] = [
                    'type' => 'activity',
                    'code' => $activity['activity_code'],
                    'label' => $activity['activity_name'],
                    ...$this->summarize($activityItems),
                ];

                foreach ($this->groupBy($activityItems, 'output_code') as $outputItems) {
                    $output = $outputItems[0];
                    $rows[] = [
                        'type' => 'output',
                        'code' => $output['output_code'],
                        'label' => $output['output_name'],
                        ...$this->summarize($outputItems),
                    ];

                    foreach ($outputItems as $item) {
                        $plannedTotal = array_sum($item['months']);
                        $rows[] = [
                            'type' => 'detail',
                            'code' => $item['account_code'],
                            'label' => $item['item_description'] ?: $item['description'],
                            'account_name' => $item['account_name'],
                            'pagu_revisi' => $item['pagu_revisi'],
                            'months' => $item['months'],
                            'sisa_pagu' => $item['pagu_revisi'] - $plannedTotal,
                        ];
                    }
                }
            }
        }

        return $rows;
    }

    private function groupBy(array $items, string $key): array
    {
        $groups = [];
        foreach ($items as $item) {
            $groupKey = $item[$key] !== '' ? $item[$key] : '-';
            $groups[$groupKey][] = $item;
        }

        return $groups;
    }

    private function summarize(array $items): array
    {
        $months = array_fill(0, 12, 0.0);
        $paguRevisi = 0.0;

        foreach ($items as $item) {
            $paguRevisi += $item['pagu_revisi'];
            foreach ($item['months'] as $index => $value) {
                $months[$index] += $value;
            }
        }

        return [
            'pagu_revisi' => $paguRevisi,
            'months' => $months,
            'sisa_pagu' => $paguRevisi - array_sum($months),
        ];
    }

    private function applyRowStyle($sheet, int $rowNumber, array $style): void
    {
        $sheet->getStyle("A{$rowNumber}:".self::LAST_COLUMN.$rowNumber)->applyFromArray($style);
        $sheet->getStyle("A{$rowNumber}:P{$rowNumber}")->getAlignment()->setWrapText(true);
        $sheet->getStyle("Q{$rowNumber}:".self::LAST_COLUMN.$rowNumber)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
            ->setVertical(Alignment::VERTICAL_CENTER);
    }

    private function writeReportRow($sheet, int $rowNumber, array $row): void
    {
        $this->mergeCells($sheet, "Q{$rowNumber}:R{$rowNumber}");
        $this->mergeCells($sheet, self::SISA_PAGU_COLUMN.$rowNumber.':'.self::LAST_COLUMN.$rowNumber);

        if ($row['type'] === 'total') {
            $this->mergeCells($sheet, "A{$rowNumber}:P{$rowNumber}");
            $sheet->setCellValue("A{$rowNumber}", $row['label']);
        } elseif ($row['type'] === 'program') {
            $this->mergeCells($sheet, "B{$rowNumber}:C{$rowNumber}");
            $this->mergeCells($sheet, "D{$rowNumber}:P{$rowNumber}");
            $sheet->setCellValue("B{$rowNumber}", $row['code']);
            $sheet->setCellValue("D{$rowNumber}", $row['label']);
        } elseif ($row['type'] === 'activity') {
            $this->mergeCells($sheet, "B{$rowNumber}:H{$rowNumber}");
            $this->mergeCells($sheet, "I{$rowNumber}:P{$rowNumber}");
            $sheet->setCellValue("B{$rowNumber}", $row['code']);
            $sheet->setCellValue("I{$rowNumber}", $row['label']);
        } elseif ($row['type'] === 'output') {
            $this->mergeCells($sheet, "C{$rowNumber}:F{$rowNumber}");
            $this->mergeCells($sheet, "G{$rowNumber}:P{$rowNumber}");
            $sheet->setCellValue("C{$rowNumber}", $row['code']);
            $sheet->setCellValue("G{$rowNumber}", $row['label']);
        } else {
            $this->mergeCells($sheet, "L{$rowNumber}:M{$rowNumber}");
            $this->mergeCells($sheet, "N{$rowNumber}:P{$rowNumber}");
            $label = $row['label'];
            if ($row['account_name'] !== '') {
                $label .= "\n".$row['account_name'];
            }
            $sheet->setCellValue("L{$rowNumber}", $row['code']);
            $sheet->setCellValue("N{$rowNumber}", $label);
        }

        $sheet->setCellValue("Q{$rowNumber}", $row['pagu_revisi']);

        foreach (self::MONTH_ABBREVIATIONS as $index => $_month) {
            $sheet->setCellValue(
                Coordinate::stringFromColumnIndex(self::FIRST_MONTH_COLUMN_INDEX + $index).$rowNumber,
                $row['months'][$index] ?? 0
            );
        }

        $sheet->setCellValue(self::SISA_PAGU_COLUMN.$rowNumber, $row['sisa_pagu']);
    }

    private function mergeCells($sheet, string $range): void
    {
        if (! $this->hasMerge($sheet, $range)) {
            $sheet->mergeCells($range);
        }
    }

    private function hasMerge($sheet, string $range): bool
    {
        return in_array($range, $sheet->getMergeCells(), true);
    }
}
