<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\XenditPaymentSessionWebhookController;
use App\Http\Controllers\Api\XenditRefundWebhookController;
use App\Http\Middleware\VerifyXenditCallbackToken;

Route::prefix('webhooks/xendit')
    ->middleware(VerifyXenditCallbackToken::class)
    ->group(function () {
        Route::post('/payment-sessions', XenditPaymentSessionWebhookController::class)
            ->name('xendit.webhooks.payment-sessions');
        Route::post('/refunds', XenditRefundWebhookController::class)
            ->name('xendit.webhooks.refunds');
    });
