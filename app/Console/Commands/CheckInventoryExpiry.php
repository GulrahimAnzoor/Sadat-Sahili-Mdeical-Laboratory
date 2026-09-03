<?php

namespace App\Console\Commands;

use App\Support\LabAlerts;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('inventory:check-expiry')]
#[Description('Send inventory expiry alerts for one month, ten days, and expired items')]
class CheckInventoryExpiry extends Command
{
    public function handle(): int
    {
        $sent = LabAlerts::scanExpiryAlerts();

        $this->info(__(':count expiry alerts sent.', ['count' => $sent]));

        return self::SUCCESS;
    }
}
