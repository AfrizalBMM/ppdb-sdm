<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class StatistikKeuanganExport implements FromCollection, ShouldAutoSize
{
    public function __construct(private readonly array $data)
    {
    }

    public function collection(): Collection
    {
        $rows = collect();

        $rows->push(['Laporan Statistik Keuangan', '', '', '', '']);
        $rows->push(['Periode', (string) ($this->data['periodeLabel'] ?? '-'), '', '', '']);
        $rows->push(['Diperbarui', optional($this->data['updatedAt'] ?? null)?->timezone('Asia/Jakarta')->format('d M Y H:i') ?? '-', '', '', '']);
        $rows->push(['', '', '', '', '']);

        $rows->push(['Ringkasan', 'Nilai', '', '', '']);
        $rows->push(['Jumlah Biaya Keseluruhan', (int) ($this->data['jumlahBiayaKeseluruhan'] ?? 0), '', '', '']);
        $rows->push(['Jumlah yang Belum Lunas', (int) ($this->data['jumlahSisaPiutang'] ?? 0), '', '', '']);
        $rows->push(['Jumlah Yang Sudah Lunas', (int) ($this->data['jumlahLunasNominal'] ?? 0), '', '', '']);
        $rows->push(['Persentase Pelunasan (%)', (float) ($this->data['persentasePelunasan'] ?? 0), '', '', '']);
        $rows->push(['Jumlah Uang Masuk Periode', (int) ($this->data['jumlahUangMasukPeriode'] ?? 0), '', '', '']);
        $rows->push(['Jumlah Pendaftar', (int) ($this->data['jumlahPendaftar'] ?? 0), '', '', '']);
        $rows->push(['', '', '', '', '']);

        $rows->push(['Jumlah Biaya per Jenis Pembayaran', '', '', '', '']);
        $rows->push(['Jenis Pembayaran', 'Total Biaya', '', '', '']);
        foreach (($this->data['jumlahBiayaPerJenis'] ?? collect()) as $row) {
            $rows->push([
                ui_label((string) ($row->jenis_biaya ?? '-')),
                (int) ($row->total_jenis ?? 0),
                '',
                '',
                '',
            ]);
        }
        $rows->push(['', '', '', '', '']);

        $rows->push(['Riwayat Pembayaran Hari Ini', '', '', '', '']);
        $rows->push(['Tanggal', 'Waktu', 'Nama', 'No Registrasi', 'Jenis Biaya', 'Nominal', 'Metode']);
        foreach (($this->data['riwayatPembayaranHariIni'] ?? collect()) as $item) {
            $siswa = optional(optional($item->tagihan)->siswa);
            $registration = optional($siswa->registration);
            $rows->push([
                optional($item->tanggal_bayar)->format('d-m-Y') ?? '-',
                optional($item->created_at)->format('H:i') ?? '-',
                $siswa->nama ?? '-',
                $registration->nomor_registrasi ?? '-',
                ui_label(optional(optional($item->tagihan)->biaya)->jenis_biaya ?? '-'),
                (int) $item->nominal_bayar,
                $item->metode ? ucfirst((string) $item->metode) : '-',
            ]);
        }

        return $rows;
    }
}
