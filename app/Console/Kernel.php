<?php

namespace App\Console;

use App\Jobs\MonitorPendingClaimsSlaJob;
use App\Jobs\ProcessOverdueCollectionsJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
	protected function schedule(Schedule $schedule): void
	{
		$schedule->command('app:sync-claimed-items')
			->hourly()
			->runInBackground()
			->onQueue('default');

		$schedule->job(new MonitorPendingClaimsSlaJob())
			->everyTenMinutes()
			->onQueue('sla')
			->runInBackground();

		$schedule->job(new ProcessOverdueCollectionsJob())
			->hourly()
			->onQueue('overdue')
			->runInBackground();
	}

	protected function commands(): void
	{
		$this->load(__DIR__.'/Commands');

		require base_path('routes/console.php');
	}
}






