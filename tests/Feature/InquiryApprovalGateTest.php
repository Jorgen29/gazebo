<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\Payment;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InquiryApprovalGateTest extends TestCase
{
    use RefreshDatabase;

    private function actingAdmin(): User
    {
        $admin = User::factory()->create([
            'email' => 'admin@gmail.com',
            'username' => 'admin',
            'password' => bcrypt('password123'),
        ]);
        $this->actingAs($admin, 'web');

        return $admin;
    }

    private function makeInquiry(): Inquiry
    {
        $venue = Venue::create([
            'title' => 'Gate Venue', 'description' => null, 'capacity' => 10, 'price_per_hour' => 1000,
            'image' => null, 'showcase_images' => [], 'features' => [], 'is_active' => true,
        ]);

        return Inquiry::factory()->create([
            'venue_id' => (string) $venue->id,
            'venue_title' => $venue->title,
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'pending',
        ]);
    }

    public function test_approve_succeeds_when_latest_current_payment_is_paid(): void
    {
        Mail::fake();
        $this->actingAdmin();
        $inquiry = $this->makeInquiry();
        Payment::factory()->paid()->create(['inquiry_id' => $inquiry->id]);

        $this->patch(route('admin.inquiries.update-status', $inquiry->id), ['status' => 'approved'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('approved', $inquiry->fresh()->status);
    }

    public function test_approve_is_rejected_when_latest_current_payment_is_not_paid(): void
    {
        Mail::fake();
        $this->actingAdmin();
        $inquiry = $this->makeInquiry();
        Payment::factory()->create(['inquiry_id' => $inquiry->id, 'status' => Payment::STATUS_PENDING]);

        $this->patch(route('admin.inquiries.update-status', $inquiry->id), ['status' => 'approved'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame('pending', $inquiry->fresh()->status);
    }

    public function test_approve_is_rejected_when_no_payment_exists(): void
    {
        Mail::fake();
        $this->actingAdmin();
        $inquiry = $this->makeInquiry();

        $this->patch(route('admin.inquiries.update-status', $inquiry->id), ['status' => 'approved'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame('pending', $inquiry->fresh()->status);
    }

    public function test_decline_and_cancel_allowed_without_paid_payment(): void
    {
        Mail::fake();
        $this->actingAdmin();
        $inquiry = $this->makeInquiry();

        $this->patch(route('admin.inquiries.update-status', $inquiry->id), ['status' => 'declined'])
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertSame('declined', $inquiry->fresh()->status);

        $inquiry->update(['status' => 'pending']);
        $this->patch(route('admin.inquiries.update-status', $inquiry->id), ['status' => 'cancelled'])
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertSame('cancelled', $inquiry->fresh()->status);
    }
}