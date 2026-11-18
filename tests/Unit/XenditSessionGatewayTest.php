<?php

namespace Tests\Unit;

use App\Exceptions\XenditGatewayException;
use App\Services\Xendit\XenditSessionGateway;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class XenditSessionGatewayTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.xendit.secret_key' => 'xnd_development_test_key',
            'services.xendit.currency' => 'PHP',
        ]);
    }

    public function test_it_creates_a_session_with_basic_auth_and_normalizes_the_response(): void
    {
        Http::fake([
            'https://api.xendit.co/sessions' => Http::response([
                'payment_session_id' => 'ps_123',
                'reference_id' => 'inquiry-1-abc',
                'status' => 'ACTIVE',
                'payment_link_url' => 'https://checkout.xendit.co/web/ps_123',
                'expires_at' => '2026-11-18T09:00:00+08:00',
                'payment_request_id' => 'pr_123',
                'payment_method' => ['type' => 'GCASH'],
            ], 200),
        ]);

        $session = (new XenditSessionGateway())->createSession([
            'reference_id' => 'inquiry-1-abc',
            'amount' => 1000,
        ]);

        $this->assertSame('ps_123', $session['payment_session_id']);
        $this->assertSame('ACTIVE', $session['status']);
        $this->assertSame('https://checkout.xendit.co/web/ps_123', $session['payment_link_url']);
        $this->assertSame('pr_123', $session['payment_request_id']);
        $this->assertSame('GCASH', $session['payment_method']);

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://api.xendit.co/sessions'
                && $request->method() === 'POST'
                && $request['reference_id'] === 'inquiry-1-abc'
                && $request->hasHeader('Authorization', 'Basic ' . base64_encode('xnd_development_test_key:'));
        });
    }

    public function test_it_retrieves_and_cancels_sessions(): void
    {
        Http::fake([
            'https://api.xendit.co/sessions/ps_123' => Http::response([
                'payment_session_id' => 'ps_123',
                'status' => 'COMPLETED',
            ], 200),
            'https://api.xendit.co/sessions/ps_123/cancel' => Http::response([
                'payment_session_id' => 'ps_123',
                'status' => 'CANCELED',
            ], 200),
        ]);

        $gateway = new XenditSessionGateway();

        $this->assertSame('COMPLETED', $gateway->getSession('ps_123')['status']);
        $this->assertSame('CANCELED', $gateway->cancelSession('ps_123')['status']);

        Http::assertSentCount(2);
    }

    public function test_it_throws_a_gateway_exception_for_a_failed_request(): void
    {
        Http::fake([
            'https://api.xendit.co/sessions' => Http::response([
                'error_code' => 'INVALID_PAYMENT_CHANNEL',
                'message' => 'Payment channel is not available.',
            ], 400),
        ]);

        $this->expectException(XenditGatewayException::class);
        $this->expectExceptionMessage('Payment channel is not available.');

        (new XenditSessionGateway())->createSession(['reference_id' => 'x']);
    }

    public function test_it_creates_a_refund_with_basic_auth_and_normalizes_the_response(): void
    {
        Http::fake([
            'https://api.xendit.co/refunds' => Http::response([
                'id' => 'rf_123',
                'payment_request_id' => 'pr_123',
                'payment_id' => 'pay_123',
                'reference_id' => 'refund-1-uuid',
                'status' => 'PENDING',
                'failure_code' => null,
                'refund_fee_amount' => 0,
                'created' => '2026-11-18T09:00:00+08:00',
                'updated' => '2026-11-18T09:00:01+08:00',
            ], 200),
        ]);

        $refund = (new XenditSessionGateway())->createRefund([
            'reference_id' => 'refund-1-uuid',
            'payment_request_id' => 'pr_123',
            'amount' => 1000,
            'currency' => 'PHP',
            'reason' => 'CANCELLATION',
        ]);

        $this->assertSame('rf_123', $refund['id']);
        $this->assertSame('pr_123', $refund['payment_request_id']);
        $this->assertSame('pay_123', $refund['payment_id']);
        $this->assertSame('refund-1-uuid', $refund['reference_id']);
        $this->assertSame('PENDING', $refund['status']);
        $this->assertNull($refund['failure_code']);
        $this->assertSame(0, $refund['refund_fee_amount']);
        $this->assertSame('2026-11-18T09:00:00+08:00', $refund['created']);
        $this->assertSame('2026-11-18T09:00:01+08:00', $refund['updated']);

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://api.xendit.co/refunds'
                && $request->method() === 'POST'
                && $request['reference_id'] === 'refund-1-uuid'
                && $request['payment_request_id'] === 'pr_123'
                && $request['amount'] === 1000
                && $request['currency'] === 'PHP'
                && $request['reason'] === 'CANCELLATION'
                && $request->hasHeader('Authorization', 'Basic ' . base64_encode('xnd_development_test_key:'));
        });
    }

    public function test_it_retrieves_and_normalizes_a_refund(): void
    {
        Http::fake([
            'https://api.xendit.co/refunds/rf_123' => Http::response([
                'id' => 'rf_123',
                'payment_request_id' => 'pr_123',
                'status' => 'SUCCEEDED',
                'failure_code' => null,
                'refund_fee_amount' => 50,
                'updated' => '2026-11-18T10:00:00+08:00',
            ], 200),
        ]);

        $refund = (new XenditSessionGateway())->getRefund('rf_123');

        $this->assertSame('rf_123', $refund['id']);
        $this->assertSame('SUCCEEDED', $refund['status']);
        $this->assertSame(50, $refund['refund_fee_amount']);
        $this->assertSame('2026-11-18T10:00:00+08:00', $refund['updated']);
        $this->assertSame('SUCCEEDED', $refund['raw']['status']);

        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.xendit.co/refunds/rf_123'
            && $request->method() === 'GET');
    }

    public function test_refund_methods_map_failed_and_malformed_responses_to_gateway_exceptions(): void
    {
        Http::fake([
            'https://api.xendit.co/refunds' => Http::response([
                'error_code' => 'REJECTED',
                'message' => 'Refund amount exceeds the payment amount.',
            ], 400),
            'https://api.xendit.co/refunds/rf_broken' => Http::response('not json', 200),
        ]);

        $gateway = new XenditSessionGateway();

        try {
            $gateway->createRefund(['payment_request_id' => 'pr_123']);
            $this->fail('A failed refund creation should throw.');
        } catch (XenditGatewayException $exception) {
            $this->assertSame('Refund amount exceeds the payment amount.', $exception->getMessage());
            $this->assertSame(400, $exception->httpStatus());
        }

        try {
            $gateway->getRefund('rf_broken');
            $this->fail('A malformed refund response should throw.');
        } catch (XenditGatewayException $exception) {
            $this->assertSame('Xendit returned an unexpected response.', $exception->getMessage());
        }
    }
}
