<?php

namespace App\Console\Commands;

use App\Services\FlowGuardCatalogImporter;
use Illuminate\Console\Command;

class ImportFlowGuardProducts extends Command
{
    protected $signature = 'products:import-flowguard
                            {--file= : Path to JSON catalog file}
                            {--keep-demo : Keep demo products instead of removing them}';

    protected $description = 'Import FlowGuard product catalog and prices from JSON';

    public function handle(FlowGuardCatalogImporter $importer): int
    {
        $this->info('Importing FlowGuard catalog...');

        try {
            $result = $importer->import(
                jsonPath: $this->option('file'),
                replaceDemo: ! $this->option('keep-demo'),
            );
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Done: {$result['total']} products ({$result['created']} new, {$result['updated']} updated).");

        return self::SUCCESS;
    }
}
