<?php

namespace App\Console;

use App\Console\Commands\Fix;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Webkul\Shop\Console\Commands\AbandonedCarts;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Fix::class,
        AbandonedCarts::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('invoice:cron')->dailyAt('3:00');

        $schedule->command('indexer:index --type=price')->dailyAt('00:01');

        $schedule->command('product:price-rule:index')->dailyAt('00:01');

        $schedule->command('abandoned:cart')->everyThreeHours();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
        $this->load(__DIR__ . '/../../packages/Webkul/Shop/src/Console/Commands');
        $this->load(__DIR__ . '/../../packages/Webkul/Core/src/Console/Commands');

        require base_path('routes/console.php');
    }
}
