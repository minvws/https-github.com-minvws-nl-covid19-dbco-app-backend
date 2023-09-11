<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Arquivei\LaravelPrometheusExporter\PrometheusExporter;
use Illuminate\Console\Command;
use Throwable;

final class PrometheusWipeStorage extends Command
{
    protected $signature = 'prometheus:wipe-storage';
    protected $description = 'Wipes prometheus metrics from storage';

    public function handle(PrometheusExporter $prometheusExporter): int
    {
        try {
            $prometheusExporter->getPrometheus()->wipeStorage();
        } catch (Throwable $throwable) {
            $this->error('Failed to wipe prometheus metrics from storage; ' . $throwable->getMessage());
            return self::FAILURE;
        }

        $this->info('Successfully wiped prometheus metrics from storage');
        return self::SUCCESS;
    }
}
