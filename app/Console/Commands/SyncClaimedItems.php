<?php

namespace App\Console\Commands;

use App\Jobs\SyncClaimedItemsJob;
use Illuminate\Console\Command;

class SyncClaimedItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-claimed-items';

    /**
     * The console command description.
     *
     * @var string
     */
	protected $description = 'Send collection reminders and handle overdue claims.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
		SyncClaimedItemsJob::dispatch();

		$this->info('Collection reminder sync dispatched.');
    }
}
