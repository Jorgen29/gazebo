<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inquiry;
use App\Models\Venue;
use App\Support\VenuePresenter;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use App\Mail\InquiryStatusNotification;
use App\Mail\NewInquiryNotification;
use App\Mail\PaymentInstructionsMail;
use App\Models\Notification;
use Carbon\Carbon;

class VenueBookingController extends Controller
{
    /**
     * Resolve the requested venue (numeric id or slug) to an active venue.
     */
    private function resolveVenue(?string $space): Venue
    {
        if (filled($space)) {
            $venue = Venue::query()
                ->where('is_active', true)
                ->when(
                    ctype_digit((string) $space),
                    fn ($query) => $query->whereKey((int) $space),
                    fn ($query) => $query->where('slug', (string) $space)
                )
                ->first();

            abort_if($venue === null, 404);

            return $venue;
        }

        $venue = Venue::query()->where('is_active', true)->orderBy('title')->first();

        abort_if($venue === null, 404);

        return $venue;
    }

    /**
     * Approved bookings for a venue, in the shape the booking calendar expects.
     */
    private function approvedBookingsFor(Venue $venue): array
    {
        return Inquiry::query()
            ->where('venue_id', (string) $venue->id)
            ->where('status', 'approved')
            ->get()
            ->map(fn (Inquiry $inquiry) => $this->formatBooking($inquiry))
            ->values()
            ->all();
    }

    private function formatBooking(Inquiry $inquiry): array
    {
        $start = Carbon::parse($inquiry->start_time);
        $end = Carbon::parse($inquiry->end_time);

        return [
            'date' => Carbon::parse($inquiry->booking_date)->format('Y-m-d'),
            'start' => $start->format('H:i'),
            'end' => $end->format('H:i'),
            'label' => $start->format('g:i A') . ' - ' . $end->format('g:i A') . " ({$inquiry->full_name})",
        ];
    }

    public function inquire()
    {
        $spaces = Venue::query()
            ->where('is_active', true)
            ->latest()
            ->get()
            ->map(fn (Venue $venue) => VenuePresenter::toArray($venue))
            ->values()
            ->all();

        $reservedSchedule = $this->approvedSchedule();

        return view('inquire', compact('spaces', 'reservedSchedule'));
    }

    /**
     * Approved bookings for every venue, keyed by venue id.
     */
    private function approvedSchedule(): array
    {
        return Inquiry::query()
            ->where('status', 'approved')
            ->get()
            ->groupBy('venue_id')
            ->map(fn ($inquiries) => $inquiries->map(fn (Inquiry $inquiry) => $this->formatBooking($inquiry))->values()->all())
            ->mapWithKeys(fn ($entries, $key) => [(string) $key => $entries])
            ->all();
    }

    public function showBookingPage(Request $request)
    {
        $venue = $this->resolveVenue($request->query('space'));
        $space = VenuePresenter::toArray($venue);

        $selectedDate = $request->query('date') ?: now()->toDateString();

        $allBookings = $this->approvedBookingsFor($venue);
        $reservedSchedules = [$space['id'] => $allBookings];

        return view('book', compact('space', 'selectedDate', 'allBookings', 'reservedSchedules'));
    }

    public function storeInquiry(Request $request)
    {
        $validated = $request->validate([
            'venue_id'      => [
                'required',
                'string',
                Rule::exists('venues', 'id')->where('is_active', true),
            ],
            'venue_title'   => 'nullable|string',
            'booking_date'  => 'required|date',
            'start_time'    => 'required',
            'end_time'      => 'required',
            'full_name'     => 'required|string|max:255',
            'email'         => 'required|email|max:255',
            'email_contact' => 'required|string|max:255',
        ]);

        $venue = Venue::query()->findOrFail($validated['venue_id']);

        // The client cannot claim a venue title; always store the canonical one.
        $validated['venue_id'] = (string) $venue->id;
        $validated['venue_title'] = $venue->title;

        $inquiry = Inquiry::create($validated);
        $inquiry->booking_reference = Inquiry::generateBookingReference($inquiry);
        $inquiry->save();

        Notification::create([
            'inquiry_id' => $inquiry->id,
            'type' => 'booking_request',
            'email' => $inquiry->email,
            'subject' => 'New booking request received',
            'message' => 'A client submitted a new venue booking request.',
            'metadata' => [
                'venue_id' => $inquiry->venue_id,
                'venue_title' => $inquiry->venue_title,
                'booking_date' => $inquiry->booking_date,
            ],
        ]);

        // Send email notification to Admin using the inquiry email as the sender identity
        try {
            $adminEmail = config('mail.from.address', env('MAIL_FROM_ADDRESS', 'hello@example.com'));
            Mail::to($adminEmail)->queue(new NewInquiryNotification($inquiry));

            \Log::info('Booking inquiry queued for admin email', [
                'inquiry_id' => $inquiry->id,
                'email' => $inquiry->email,
                'admin_email' => $adminEmail,
                'queue_connection' => config('queue.default'),
            ]);
        } catch (\Exception $e) {
            // Log error if mail server fails
            \Log::error('Mail sending failed: ' . $e->getMessage(), [
                'inquiry_id' => $inquiry->id,
                'email' => $inquiry->email,
            ]);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Your reservation request has been submitted successfully!',
                'inquiry_id' => $inquiry->id,
                'queued' => true,
                'mail' => [
                    'to' => $adminEmail ?? config('mail.from.address', 'hello@example.com'),
                    'from' => $inquiry->email,
                ],
            ]);
        }

        return back()->with('success', 'Your reservation request has been submitted successfully!');
    }

    // Admin Dashboard View: List all submitted inquiries
    public function adminInquiries(Request $request)
    {
        $status = $request->query('status');
        $highlightId = $request->query('highlight_id');

        $query = Inquiry::latest();

        if ($status) {
            $query->where('status', $status);
        }

        $inquiries = $query->paginate(10);

        return view('admin.inquiries', compact('inquiries', 'highlightId'));
    }

    public function markAllNotificationsRead()
    {
        Notification::query()->whereNull('read_at')->update([
            'read_at' => now(),
        ]);

        return redirect()->route('admin.inquiries');
    }

    public function openNotification($id)
    {
        $notification = Notification::findOrFail($id);

        $notification->update([
            'read_at' => $notification->read_at ?? now(),
        ]);

        $highlightId = $notification->inquiry_id ?? null;

        if ($highlightId) {
            return redirect()->route('admin.inquiries', ['highlight_id' => $highlightId]);
        }

        return redirect()->route('admin.inquiries');
    }

    // Send GCash Payment instructions email to customer
    public function sendPaymentEmail($id)
    {
        $inquiry = Inquiry::findOrFail($id);

        if (!$inquiry->email) {
            return back()->with('error', 'Customer email address is missing.');
        }

        if (empty($inquiry->booking_reference)) {
            $inquiry->booking_reference = Inquiry::generateBookingReference($inquiry);
            $inquiry->save();
        }

        Mail::to($inquiry->email)->queue(new PaymentInstructionsMail($inquiry));

        $inquiry->update([
            'payment_requested_at' => now(),
        ]);

        return back()->with('success', 'Payment instruction email sent to customer!');
    }

    // Upload proof of payment manually from admin (when customer replies with screenshot)
    public function uploadPaymentProof(Request $request, $id)
    {
        $request->validate([
            'payment_proof' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $inquiry = Inquiry::findOrFail($id);

        if ($request->hasFile('payment_proof')) {
            $path = $request->file('payment_proof')->store('payment_proofs', 'public');
            $inquiry->update([
                'payment_proof' => $path,
            ]);

            Notification::create([
                'inquiry_id' => $inquiry->id,
                'type' => 'payment_received',
                'email' => $inquiry->email,
                'subject' => 'Payment proof received',
                'message' => 'A client uploaded a payment proof for their booking.',
                'metadata' => [
                    'payment_path' => $path,
                    'booking_reference' => $inquiry->booking_reference,
                ],
            ]);
        }

        return back()->with('success', 'Payment proof attached successfully. You can now approve the booking.');
    }

    // Update Inquiry status
    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:pending,approved,declined,cancelled']);

        $inquiry = Inquiry::findOrFail($id);
        $newStatus = $request->status;
        $inquiry->update(['status' => $newStatus]);

        if (!empty($inquiry->email) && in_array($newStatus, ['approved', 'declined', 'cancelled'], true)) {
            try {
                Mail::to($inquiry->email)->queue(new InquiryStatusNotification($inquiry, $newStatus));
            } catch (\Exception $e) {
                \Log::error('Inquiry status email failed: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Inquiry status updated successfully.');
    }
}
