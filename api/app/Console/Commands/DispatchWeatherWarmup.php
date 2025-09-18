<?php

namespace App\Console\Commands;

use App\Jobs\RefreshWeatherCache;
use Illuminate\Console\Command;

/**
 * Custom command to dispatch the RefreshWeatherCache job.
 */
class DispatchWeatherWarmup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:warmup-weather';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warmup the weather cache for all users';

    /**
     * Execute the console command.
     * todo would add better error handling if I had time.
     */
    public function handle()
    {
        RefreshWeatherCache::dispatch();
        $this->info('Warmup job dispatched.');
        return self::SUCCESS;
    }
}
