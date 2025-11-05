<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\SendCollectionReminderJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule collection reminder job to run daily at 9 AM (gentle reminders, no strict deadline enforcement)
Schedule::job(new SendCollectionReminderJob)->dailyAt('09:00');

// Auto-revert job disabled for now - deadlines are informational only
// Schedule::job(new \App\Jobs\ProcessOverdueCollectionsJob)->dailyAt('10:00');
