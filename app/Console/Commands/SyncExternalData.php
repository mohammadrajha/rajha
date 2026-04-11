<?php

namespace App\Console\Commands;

use App\Services\ExternalApiService;
use Illuminate\Console\Command;

class SyncExternalData extends Command
{
    protected $signature = 'attendance:sync {--type=all : Sync type (all, instructors, schedules)}';
    protected $description = 'Sync instructors and schedules from external API';

    public function handle(ExternalApiService $service): int
    {
        $type = $this->option('type');

        $this->info("Starting sync: {$type}");

        if ($type === 'all' || $type === 'instructors') {
            $this->info('Syncing instructors...');
            $result = $service->syncInstructors();
            $this->info("Instructors: {$result->status} (synced: {$result->records_synced}, failed: {$result->records_failed})");
        }

        if ($type === 'all' || $type === 'schedules') {
            $this->info('Syncing schedules...');
            $result = $service->syncSchedules();
            $this->info("Schedules: {$result->status} (synced: {$result->records_synced}, failed: {$result->records_failed})");
        }

        $this->info('Sync complete.');
        return Command::SUCCESS;
    }
}
