<?php

namespace App\Console\Commands;

use App\Support\DatabaseBackup;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('lab:backup')]
#[Description('Create a laboratory database backup without deleting previous backups')]
class CreateLabBackup extends Command
{
    public function handle(DatabaseBackup $backups): int
    {
        try {
            $name = $backups->create();
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(__('Backup created: :name', ['name' => $name]));

        return self::SUCCESS;
    }
}
