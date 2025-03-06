<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\MonitorSites::class
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('sites:monitor')->everyMinute();
    }
} 