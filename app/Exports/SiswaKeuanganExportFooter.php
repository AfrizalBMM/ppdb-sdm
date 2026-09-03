<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class SiswaKeuanganExportFooter extends SiswaKeuanganExport implements WithEvents
{
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $data = $this->collection();
                $totalKekurangan = 0;
                foreach ($data as $row) {
                    if (isset($row[9])) {
                        $val = preg_replace('/[^0-9]/', '', $row[9]);
                        $totalKekurangan += (int) $val;
                    }
                }
                $highestRow = $event->sheet->getHighestRow();
                $footerRow = $highestRow + 1;
                $event->sheet->appendRows([
                    [
                        '', '', '', '', '', '', '', '', 'Total Kekurangan semua peserta didik', number_format($totalKekurangan, 0, ',', '.')
                    ]
                ], $event);
                $event->sheet->getStyle("I{$footerRow}:J{$footerRow}")->getFont()->setBold(true);
                $event->sheet->getStyle("J{$footerRow}")->getAlignment()->setHorizontal('right');
            }
        ];
    }
}
