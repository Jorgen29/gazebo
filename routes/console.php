<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Reconcile pending Xendit Payment Sessions as a webhook backstop.
Schedule::command('payments:reconcile-xendit')
    ->everyFiveMinutes()
    ->withoutOverlapping();
