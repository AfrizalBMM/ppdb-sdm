<?php

namespace App\Console\Commands;

use App\Models\LogAktivitas;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log as LaravelLog;

class CleanupExportFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exports:cleanup {--days=7 : Jumlah hari, file lebih lama dari ini akan dihapus}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus file export Excel/PDF lama dari public/file/excel dan public/file/pdf.';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        if ($days <= 0) {
            $this->error('Option --days harus bernilai positif.');
            return 1;
        }

        $excelDir = public_path('file' . DIRECTORY_SEPARATOR . 'excel');
        $pdfDir = public_path('file' . DIRECTORY_SEPARATOR . 'pdf');

        File::ensureDirectoryExists($excelDir, 0755, true);
        File::ensureDirectoryExists($pdfDir, 0755, true);

        $checks = [
            'Excel' => $this->isDirAccessible($excelDir),
            'PDF' => $this->isDirAccessible($pdfDir),
        ];

        foreach ($checks as $type => $accessible) {
            $dir = $type === 'Excel' ? $excelDir : $pdfDir;

            if ($accessible) {
                $this->info("$type directory is accessible: $dir");
            } else {
                $this->error("$type directory is not accessible/writable: $dir");
                $this->logAktivitas('Cleanup Export Files', "Failed: $type directory is not accessible/writable: $dir");
                return 1;
            }
        }

        $expiry = now()->subDays($days)->timestamp;
        $totalDeleted = 0;
        $deletedCounts = [
            'excel' => 0,
            'pdf' => 0,
        ];
        $deletedFiles = [
            'excel' => [],
            'pdf' => [],
        ];

        foreach (['excel' => $excelDir, 'pdf' => $pdfDir] as $type => $dir) {
            $files = File::exists($dir) && File::isDirectory($dir) ? File::files($dir) : [];

            foreach ($files as $file) {
                if ($file->getMTime() <= $expiry) {
                    File::delete($file->getPathname());
                    $deletedCounts[$type]++;
                    $totalDeleted++;
                    $deletedFiles[$type][] = $file->getFilename();
                    $this->line("Deleted [$type] file: {$file->getFilename()}");
                }
            }
        }

        $message = sprintf(
            'Exports cleanup complete. Days threshold: %d. Total removed: %d (excel: %d, pdf: %d).',
            $days,
            $totalDeleted,
            $deletedCounts['excel'],
            $deletedCounts['pdf']
        );

        $this->info($message);

        $detail = "Excel files removed: " . implode(', ', $deletedFiles['excel']);
        if (empty($deletedFiles['excel'])) {
            $detail = 'Excel files removed: none';
        }

        $detailPdf = "PDF files removed: " . implode(', ', $deletedFiles['pdf']);
        if (empty($deletedFiles['pdf'])) {
            $detailPdf = 'PDF files removed: none';
        }

        $this->logAktivitas('Cleanup Export Files', $message . ' ' . $detail . ' ' . $detailPdf);
        return 0;
    }

    private function isDirAccessible(string $dir): bool
    {
        return File::exists($dir)
            && File::isDirectory($dir)
            && File::isReadable($dir)
            && File::isWritable($dir);
    }

    private function logAktivitas(string $aksi, ?string $keterangan = null): void
    {
        try {
            LogAktivitas::create([
                'user_id'    => auth()->id(),
                'role'       => auth()->check() ? auth()->user()->role : 'system',
                'aksi'       => $aksi,
                'keterangan' => $keterangan ?? '',
                'ip_address' => app()->runningInConsole() ? 'cli' : request()->ip() ?? 'unknown',
            ]);
        } catch (\Exception $e) {
            LaravelLog::error('Gagal log aktivitas (CleanupExportFiles): ' . $e->getMessage(), [
                'aksi' => $aksi,
                'keterangan' => $keterangan,
            ]);
        }
    }
}
