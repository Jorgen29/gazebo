<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Reconcile pending Xendit invoices as a webhook-delivery backup.
// Default interval: every 5 minutes (within 5–15 min guidance). Adjust here if needed.
Schedule::command('payments:reconcile-xendit')
    ->everyFiveMinutes()
    ->withoutOverlapping();
