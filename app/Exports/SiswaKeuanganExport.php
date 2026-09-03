<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class SiswaKeuanganExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
        public function registerEvents(): array
        {
            return [
                AfterSheet::class => function(AfterSheet $event) {
                    // Merge cell header baris 1 (A1:A2, B1:B2, C1:C2, D1:D2, E1:E2, I1:I2, J1:J2)
                    $event->sheet->mergeCells('A1:A2');
                    $event->sheet->mergeCells('B1:B2');
                    $event->sheet->mergeCells('C1:C2');
                    $event->sheet->mergeCells('D1:D2');
                    $event->sheet->mergeCells('E1:E2');
                    $event->sheet->mergeCells('I1:I2');
                    $event->sheet->mergeCells('J1:J2');
                    // Merge Jenis pembayaran (F1:H1)
                    $event->sheet->mergeCells('F1:H1');
                    // Bold header
                    $event->sheet->getStyle('A1:J2')->getFont()->setBold(true);
                    // Center header
                    $event->sheet->getStyle('A1:J2')->getAlignment()->setHorizontal('center');
                }
            ];
        }
    public function __construct(private readonly Collection $rows)
    {
    }

    public function headings(): array
    {
        // Baris pertama: header utama (Jenis pembayaran colspan 3)
        // Baris kedua: subkolom P, DU, UDP
        return [
            [
                'no',
                'nama',
                'Jenis Kelamin',
                'No Registrasi',
                'total biaya',
                'Jenis pembayaran', '', '',
                'total Sudah bayar',
                'kekurangan',
            ],
            [
                '', '', '', '', '',
                'P', 'DU', 'UDP',
                '', ''
            ]
        ];
    }

    public function collection(): Collection
    {
        $data = $this->rows->values()->map(function ($item, $index) {
            $registration = $item->registration;
            $tagihan = $item->tagihan ?? collect();
            $totalBiaya = (int) $tagihan->sum('total');
            $totalTerbayar = (int) $tagihan->sum('total_dibayar');
            $totalKekurangan = (int) $tagihan->sum('sisa');

            // Jumlah pembayaran per jenis biaya (P, DU, UDP)
            $bayarP = 0;
            $bayarDU = 0;
            $bayarUDP = 0;
            foreach ($tagihan as $t) {
                $jenis = strtolower(str_replace(' ', '_', optional($t->biaya)->jenis_biaya ?? ''));
                $totalDibayar = isset($t->pembayaran) && is_iterable($t->pembayaran)
                    ? collect($t->pembayaran)->sum('nominal_bayar')
                    : (isset($t->total_dibayar) ? (int) $t->total_dibayar : 0);
                if ($jenis === 'pendaftaran') {
                    $bayarP += $totalDibayar;
                } elseif ($jenis === 'daftar_ulang') {
                    $bayarDU += $totalDibayar;
                } elseif ($jenis === 'udp') {
                    $bayarUDP += $totalDibayar;
                }
            }

            return [
                $index + 1,
                $item->nama ?? '-',
                $item->jenis_kelamin ?? '-',
                optional($registration)->nomor_registrasi ?? '-',
                $totalBiaya,
                $bayarP,
                $bayarDU,
                $bayarUDP,
                $totalTerbayar,
                $totalKekurangan,
            ];
        });

        // Tambahkan baris total kekurangan semua peserta didik
        $totalKekurangan = $data->sum(function($row) {
            return isset($row[9]) ? (int) $row[9] : 0;
        });
        $data->push([
            '', '', '', '', '', '', '', '', 'Total Kekurangan semua peserta didik', number_format($totalKekurangan, 0, ',', '.')
        ]);

        // Jika headings() mengembalikan dua baris (array of array),
        // maka cukup return $data saja, karena heading akan otomatis dihandle oleh WithHeadings
        return $data;
    }
}
