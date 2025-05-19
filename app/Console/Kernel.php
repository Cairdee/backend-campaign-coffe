<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Jalankan setiap tanggal 1 dan 15 pukul 00:00
        $schedule->command('orders:autodelete')->cron('0 0 1,15 * *');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    /**
     * Daftar semua custom commands.
     */
    protected $commands = [
        \App\Console\Commands\AutoDeleteOldOrders::class,
    ];
}
