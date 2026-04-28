<?php

namespace App\Console\Commands;

use App\Models\Scan;
use App\Services\BarcodeParserService;
use Illuminate\Console\Command;

class BackfillBarcodeFields extends Command
{
    protected $signature = 'scans:backfill-barcodes';
    protected $description = 'Parse existing barcodes and fill pedido_number + package_number fields';

    public function handle(): int
    {
        $total = Scan::whereNull('pedido_number')->count();

        if ($total === 0) {
            $this->info('No scans to backfill.');
            return self::SUCCESS;
        }

        $this->info("Backfilling {$total} scans...");
        $bar = $this->output->createProgressBar($total);

        Scan::whereNull('pedido_number')
            ->chunkById(500, function ($scans) use ($bar) {
                foreach ($scans as $scan) {
                    $parsed = BarcodeParserService::parse($scan->barcode, $scan->scan_type);
                    $scan->update([
                        'pedido_number'  => $parsed['pedido_number'],
                        'package_number' => $parsed['package_number'],
                    ]);
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();
        $this->info('Done.');

        return self::SUCCESS;
    }
}
