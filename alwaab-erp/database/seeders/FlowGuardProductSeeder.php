<?php

namespace Database\Seeders;

use App\Services\FlowGuardCatalogImporter;
use Illuminate\Database\Seeder;

class FlowGuardProductSeeder extends Seeder
{
    public function run(): void
    {
        app(FlowGuardCatalogImporter::class)->import(replaceDemo: true);
    }
}
