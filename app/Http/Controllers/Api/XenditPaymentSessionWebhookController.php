<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentStatusSynchronizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class XenditPaymentSessionWebhookController extends Controller
{
    public function __construct(
        private readonly PaymentStatusSynchronizer $synchronizer
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $event = (string) ($request->input('event') ?? $request->header('x-callback-event', ''));
        $data = $request->input('data');

        if (! is_array($data)) {
            $data = $request->all();
        }

        // INFO: Failure notifications (payment.failure) are observability-only. A single session can
        // have several payment attempts, so a failure must never mark the session or payment as failed.
        if ($event !== '' && str_ends_with($event, '.failure')) {
            Log::info('Xendit payment failure notification received.', [
                'event' => $event,
                'payment_session_id' => $data['payment_session_id'] ?? null,
            ]);

            return response()->json(['status' => 'ignored']);
        }

        $sessionId = $data['payment_session_id'] ?? null;

        if (blank($sessionId)) {
            return response()->json(['message' => 'Missing payment session id.'], 400);
        }

        $status = $data['status'] ?? match (true) {
            str_contains($event, 'completed') => 'COMPLETED',
            str_contains($event, 'expired') => 'EXPIRED',
            str_contains($event, 'canceled'), str_contains($event, 'cancelled') => 'CANCELED',
            default => null,
        };

        $payment = Payment::query()
            ->where('payment_session_id', $sessionId)
            ->first();

        if (! $payment) {
            Log::warning('Xendit webhook referenced an unknown payment session.', [
                'event' => $event,
                'payment_session_id' => $sessionId,
            ]);

            return response()->json(['status' => 'ignored']);
        }

        $this->synchronizer->apply(
            $payment,
            $status,
            $data,
            $event !== '' ? $event : null
        );

        return response()->json(['status' => 'ok']);
    }
}
