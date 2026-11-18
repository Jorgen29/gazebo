<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use App\Services\RefundStatusSynchronizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class XenditRefundWebhookController extends Controller
{
    public function __construct(
        private readonly RefundStatusSynchronizer $synchronizer
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $event = (string) ($request->input('event') ?? $request->header('x-callback-event', ''));
        $data = $request->input('data');

        if (! is_array($data)) {
            $data = $request->all();
        }

        $refundId = $data['id'] ?? null;

        if (blank($refundId)) {
            return response()->json(['message' => 'Missing refund id.'], 400);
        }

        $status = $data['status'] ?? match (true) {
            str_contains($event, 'succeeded') => 'SUCCEEDED',
            str_contains($event, 'failed') => 'FAILED',
            str_contains($event, 'cancelled'), str_contains($event, 'canceled') => 'CANCELLED',
            str_contains($event, 'pending') => 'PENDING',
            default => null,
        };

        $refund = Refund::query()
            ->where('refund_id', $refundId)
            ->first();

        if (! $refund) {
            Log::warning('Xendit refund webhook referenced an unknown refund.', [
                'event' => $event,
                'refund_id' => $refundId,
            ]);

            return response()->json(['status' => 'ignored']);
        }

        // INFO: Unlike payment.failure, refund.failed targets a specific refund attempt, so it is a
        // real terminal failure that must be stored against that refund.
        $this->synchronizer->apply($refund, $status, $data, $event !== '' ? $event : null);

        return response()->json(['status' => 'ok']);
    }
}
