<?php

namespace App\Console\Commands;

use App\Services\HrDocumentExpiryService;
use Illuminate\Console\Command;

class CheckDocumentExpiry extends Command
{
    protected $signature = 'hr:check-document-expiry';

    protected $description = 'Notify HR managers about employee documents expiring within 30 days';

    public function handle(HrDocumentExpiryService $service): int
    {
        $count = $service->checkAndNotify();

        $this->info("Sent {$count} document expiry notification(s).");

        return self::SUCCESS;
    }
}
