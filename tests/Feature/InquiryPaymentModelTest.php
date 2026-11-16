<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InquiryPaymentModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_payment_columns_are_removed_from_inquiries(): void
    {
        $this->assertFalse(Schema::hasColumn('inquiries', 'payment_proof'));
        $this->assertFalse(Schema::hasColumn('inquiries', 'payment_requested_at'));
    }

    public function test_inquiry_exposes_payments_and_current_payment_selection(): void
    {
        $inquiry = Inquiry::factory()->create();

        Payment::factory()->create([
            'inquiry_id' => $inquiry->id,
            'status' => Payment::STATUS_SUPERSEDED,
        ]);
        Payment::factory()->create([
            'inquiry_id' => $inquiry->id,
            'status' => Payment::STATUS_SUPERSEDED,
        ]);
        $current = Payment::factory()->create([
            'inquiry_id' => $inquiry->id,
            'status' => Payment::STATUS_PENDING,
        ]);

        $inquiry->refresh();

        $this->assertCount(3, $inquiry->payments);
        $this->assertSame($current->id, $inquiry->currentPayment()?->id);
        $this->assertSame($current->id, $inquiry->latestPayment?->id);
        $this->assertNull($inquiry->latestCurrentPaidPayment());

        $current->update([
            'status' => Payment::STATUS_PAID,
            'paid_at' => now(),
        ]);

        $inquiry->refresh();

        $this->assertNull($inquiry->currentPayment());
        $this->assertSame($current->id, $inquiry->latestCurrentPaidPayment()?->id);
    }
}