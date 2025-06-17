<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class MonitorQueue extends Command
{
    protected $signature = 'queue:monitor
                            {--restart : Restart the queue workers}
                            {--clear-failed : Clear all failed jobs}
                            {--stats : Show queue job stats}';

    protected $description = 'Monitor and manage queue workers for Token2049 test';

    public function handle()
    {
        if ($this->option('restart')) {
            $this->info('Restarting queue workers...');
            Artisan::call('queue:restart');
            $this->line('Queue workers restarted.');
        }

        if ($this->option('clear-failed')) {
            $this->info('Clearing failed jobs...');
            Artisan::call('queue:flush');
            $this->line('Failed jobs cleared.');
        }

        if ($this->option('stats')) {
            $this->info('Queue Stats:');

            $failedCount = DB::table('failed_jobs')->count();
            $this->line("• Failed Jobs: $failedCount");

            $this->line('• Recent Failed Jobs:');
            $failed = DB::table('failed_jobs')->orderBy('failed_at', 'desc')->limit(5)->get();
            foreach ($failed as $job) {
                $this->line("  - ID: {$job->id}, Failed At: {$job->failed_at}, Error: " . substr($job->exception, 0, 100) . '...');
            }

            if (Cache::has('illuminate:queue:restart')) {
                $timestamp = Cache::get('illuminate:queue:restart');
                $this->line('• Last Queue Restart Timestamp: ' . date('Y-m-d H:i:s', $timestamp));
            } else {
                $this->line('• No queue restart timestamp found.');
            }
        }

        if (! $this->option('restart') && ! $this->option('clear-failed') && ! $this->option('stats')) {
            $this->call('help', ['command_name' => 'queue:monitor']);
        }
    }
}
