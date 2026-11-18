<?php

namespace Tests\Feature;

use App\Models\Refund;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesBookings;
use Tests\TestCase;

class RefundModelTest extends TestCase
{
    use CreatesBookings;
    use RefreshDatabase;

    public function test_refund_casts_attributes_and_exposes_relations_and_helpers(): void
    {
        $inquiry = $this->makeInquiry($this->makeVenue());
        $admin = $this->makeAdmin();

        $payment = $inquiry->payments()->create([
            'status' => 'paid',
            'amount' => '1500.00',
            'currency' => 'PHP',
        ]);

        $refund = $payment->refunds()->create([
            'reference_id' => 'refund-1-uuid',
            'payment_request_id' => 'pr_123',
            'amount' => '1500.00',
            'currency' => 'PHP',
            'reason' => 'CANCELLATION',
            'note' => 'Customer changed plans.',
            'status' => Refund::STATUS_PENDING,
            'requested_by' => $admin->id,
            'payload' => ['status' => 'PENDING'],
        ]);

        $refund->refresh();

        $this->assertSame('1500.00', $refund->amount);
        $this->assertSame('PENDING', $refund->payload['status']);
        $this->assertTrue($refund->isPending());
        $this->assertFalse($refund->isSucceeded());
        $this->assertTrue($refund->payment->is($payment));
        $this->assertTrue($refund->requestedBy->is($admin));
        $this->assertNull($refund->refunded_at);

        $refund->update([
            'status' => Refund::STATUS_SUCCEEDED,
            'refunded_at' => now(),
        ]);

        $refund->refresh();

        $this->assertTrue($refund->isSucceeded());
        $this->assertFalse($refund->isPending());
        $this->assertTrue($refund->refunded_at->isSameDay(now()));
    }

    public function test_a_refund_survives_the_deletion_of_its_requesting_admin(): void
    {
        $inquiry = $this->makeInquiry($this->makeVenue());
        $admin = $this->makeAdmin();

        $payment = $inquiry->payments()->create([
            'status' => 'paid',
            'amount' => '1000.00',
            'currency' => 'PHP',
        ]);

        $refund = $payment->refunds()->create([
            'reference_id' => 'refund-2-uuid',
            'payment_request_id' => 'pr_456',
            'amount' => '1000.00',
            'currency' => 'PHP',
            'reason' => 'OTHERS',
            'status' => Refund::STATUS_FAILED,
            'requested_by' => $admin->id,
            'payload' => [],
        ]);

        $admin->delete();

        $refund->refresh();

        $this->assertNull($refund->requested_by);
        $this->assertNull($refund->requestedBy);
    }
}
