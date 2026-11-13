<?php

use App\Http\Controllers\Api\XenditWebhookController;
use Illuminate\Support\Facades\Route;

// Xendit callback (x-callback-token verified in controller). Reconciliation is a
// scheduled backup that polls pending invoices (default every 5 minutes).
Route::post('/webhooks/xendit', XenditWebhookController::class)->name('webhooks.xendit');
