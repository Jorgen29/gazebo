<?php

namespace App\Http\Controllers;

use App\Exceptions\XenditGatewayException;
use App\Mail\InquiryStatusNotification;
use App\Mail\NewInquiryNotification;
use App\Models\Inquiry;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Venue;
use App\Services\InquiryAmountCalculator;
use App\Services\InquiryPaymentService;
use App\Services\RefundService;
use App\Support\VenuePresenter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Throwable;

class VenueBookingController extends Controller
{
    public function __construct(
        private readonly InquiryPaymentService $payments,
        private readonly RefundService $refunds
    ) {}

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
            'label' => $start->format('g:i A').' - '.$end->format('g:i A')." ({$inquiry->full_name})",
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
            'venue_id' => [
                'required',
                'string',
                Rule::exists('venues', 'id')->where('is_active', true),
            ],
            'venue_title' => 'nullable|string',
            'booking_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
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
            \Log::error('Mail sending failed: '.$e->getMessage(), [
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

        if (! $request->wantsJson() && ! $request->ajax()) {
            // The list itself is fetched separately so the page shell paints immediately.
            return view('admin.inquiries', compact('highlightId', 'status'));
        }

        $query = Inquiry::query()
            ->with([
                'latestPaymentRecord' => fn ($payment) => $payment->select([
                    'payments.id',
                    'payments.inquiry_id',
                    'payments.status',
                    'payments.amount',
                    'payments.currency',
                    'payments.payment_link_url',
                    'payments.payment_request_id',
                    'payments.expires_at',
                ]),
                'latestPaymentRecord.latestRefundRecord' => fn ($refund) => $refund->select([
                    'refunds.id',
                    'refunds.payment_id',
                    'refunds.status',
                    'refunds.amount',
                    'refunds.failure_code',
                    'refunds.note',
                    'refunds.refunded_at',
                ]),
                'venue:id,price_per_hour',
            ])
            ->latest();

        if ($status) {
            $query->where('status', $status);
        }

        $inquiries = $query->paginate(10)->withQueryString();

        $inquiries->through(fn (Inquiry $inquiry) => $this->formatAdminInquiry($inquiry));

        return response()->json($inquiries);
    }

    /**
     * Shape an inquiry for the admin inquiries list (client-rendered).
     */
    private function formatAdminInquiry(Inquiry $inquiry): array
    {
        $latestPayment = $inquiry->latestPaymentRecord;
        $latestRefund = $latestPayment?->latestRefundRecord;
        $computedAmount = null;

        try {
            $computedAmount = app(InquiryAmountCalculator::class)
                ->calculate($inquiry, $inquiry->venue);
        } catch (Throwable) {
            $computedAmount = null;
        }

        $totalCostLabel = $latestPayment
            ? '₱'.number_format((float) $latestPayment->amount, 2)
            : ($computedAmount !== null ? '₱'.number_format((float) $computedAmount, 2) : '—');

        $refundAmount = $latestRefund?->amount ?? $latestPayment?->amount;

        $canRefund = $latestPayment !== null
            && $latestPayment->isPaid()
            && filled($latestPayment->payment_request_id)
            && ($latestRefund === null || (! $latestRefund->isPending() && ! $latestRefund->isSucceeded()));

        $formattedBookingDate = $inquiry->booking_date
            ? Carbon::parse($inquiry->booking_date)->format('F j, Y')
            : 'N/A';

        $timeRangeLabel = trim(
            ($inquiry->start_time ? Carbon::parse($inquiry->start_time)->format('g:i A') : '').
            ($inquiry->end_time ? ' - '.Carbon::parse($inquiry->end_time)->format('g:i A') : '')
        );

        return [
            'id' => $inquiry->id,
            'full_name' => $inquiry->full_name,
            'email' => $inquiry->email,
            'email_contact' => $inquiry->email_contact,
            'venue_title' => $inquiry->venue_title,
            'status' => $inquiry->status,
            'booking_date' => $formattedBookingDate,
            'start_time' => $inquiry->start_time ? Carbon::parse($inquiry->start_time)->format('g:i A') : '',
            'end_time' => $inquiry->end_time ? Carbon::parse($inquiry->end_time)->format('g:i A') : '',
            'time_range' => $timeRangeLabel,
            'submitted_at' => $inquiry->created_at ? Carbon::parse($inquiry->created_at)->format('F j, Y g:i A') : 'N/A',
            'total_cost' => $totalCostLabel,
            'payment' => $latestPayment ? [
                'status' => $latestPayment->status,
                'amount' => '₱'.number_format((float) $latestPayment->amount, 2),
                'expires_at' => $latestPayment->expires_at?->format('M j, Y g:i A'),
                'payment_link_url' => $latestPayment->payment_link_url,
            ] : null,
            'modal' => [
                'id' => $inquiry->id,
                'customer' => $inquiry->full_name,
                'status' => $inquiry->status,
                'venue' => $inquiry->venue_title,
                'bookingDate' => $formattedBookingDate,
                'timeRange' => $timeRangeLabel,
                'totalCost' => $totalCostLabel,
                'email' => $inquiry->email,
                'contact' => $inquiry->email_contact,
                'paymentStatus' => $latestPayment?->status,
                'paymentAmount' => $latestPayment ? '₱'.number_format((float) $latestPayment->amount, 2) : '',
                'paymentExpiry' => $latestPayment?->expires_at?->format('M j, Y g:i A'),
                'paymentLinkUrl' => $latestPayment?->payment_link_url,
                'refundStatus' => $latestRefund?->status,
                'refundAmount' => $refundAmount !== null ? '₱'.number_format((float) $refundAmount, 2) : null,
                'refundFailureCode' => $latestRefund?->failure_code,
                'refundNote' => $latestRefund?->note,
                'canRefund' => $canRefund,
            ],
        ];
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

    // Send a Xendit hosted payment link to the customer
    public function sendPaymentLink($id)
    {
        $inquiry = Inquiry::findOrFail($id);

        try {
            $this->payments->createSession($inquiry);
        } catch (InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        } catch (XenditGatewayException $exception) {
            return back()->with('error', 'Unable to create the payment link: '.$exception->getMessage());
        } catch (Throwable $exception) {
            \Log::error('Payment session creation failed.', [
                'inquiry_id' => $inquiry->id,
                'error' => $exception->getMessage(),
            ]);

            return back()->with('error', 'Unable to create the payment link. Please try again.');
        }

        return back()->with('success', 'Payment link sent to '.$inquiry->email.'.');
    }

    // Initiate a full refund for the latest paid Xendit payment
    public function refund(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => ['required', Rule::in(Refund::REASONS)],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $inquiry = Inquiry::findOrFail($id);
        $payment = $inquiry->latestPayment();

        if ($payment === null) {
            return back()->with('error', 'This inquiry has no payment to refund.');
        }

        // TODO: Offline/manual refunds are not supported. A payment without a Xendit
        // `payment_request_id` needs a separate audit-only refund path.
        try {
            $this->refunds->initiate(
                $payment,
                $validated['reason'],
                $validated['note'] ?? null,
                $request->user()
            );
        } catch (InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        } catch (XenditGatewayException $exception) {
            return back()->with('error', 'Unable to create the refund: '.$exception->getMessage());
        } catch (Throwable $exception) {
            \Log::error('Refund creation failed.', [
                'inquiry_id' => $inquiry->id,
                'payment_id' => $payment->id,
                'error' => $exception->getMessage(),
            ]);

            return back()->with('error', 'Unable to create the refund. Please try again.');
        }

        return back()->with('success', 'Full refund requested. The payment stays paid until Xendit confirms the refund.');
    }

    // Update Inquiry status
    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:pending,approved,declined,cancelled']);

        $newStatus = $request->status;

        // INFO: Approval requires the latest payment to be paid. Decline/cancel are blocked
        // only while that payment is paid; once a refund succeeds the payment becomes refunded
        // and decline/cancel are allowed. A pending refund still blocks because the payment
        // stays paid until the refund reaches a successful final state.
        $result = DB::transaction(function () use ($id, $newStatus) {
            $inquiry = Inquiry::query()->lockForUpdate()->findOrFail($id);
            $latestPayment = $inquiry->latestPayment();

            if ($newStatus === 'approved' && ! $latestPayment?->isPaid()) {
                return [
                    'ok' => false,
                    'message' => 'This inquiry cannot be approved until its latest payment is paid.',
                ];
            }

            if (in_array($newStatus, ['declined', 'cancelled'], true) && $latestPayment?->isPaid()) {
                return [
                    'ok' => false,
                    'message' => 'This inquiry cannot be declined or cancelled because its latest payment is paid.',
                ];
            }

            if (in_array($newStatus, ['declined', 'cancelled'], true)) {
                $this->payments->cancelActiveSessions($inquiry);
            }

            $inquiry->update(['status' => $newStatus]);

            return ['ok' => true, 'inquiry' => $inquiry];
        });

        if (! $result['ok']) {
            return back()->with('error', $result['message']);
        }

        /** @var Inquiry $inquiry */
        $inquiry = $result['inquiry'];

        if (! empty($inquiry->email) && in_array($newStatus, ['approved', 'declined', 'cancelled'], true)) {
            try {
                Mail::to($inquiry->email)->queue(new InquiryStatusNotification($inquiry, $newStatus));
            } catch (\Exception $e) {
                \Log::error('Inquiry status email failed: '.$e->getMessage());
            }
        }

        return back()->with('success', 'Inquiry status updated successfully.');
    }

    public function reports(Request $request)
    {
        // 1. Fetch approved inquiries from the inquiries table
        $approvedQuery = Inquiry::with(['payments', 'venue'])
            ->where('status', 'approved');

        if ($request->filled('start_date')) {
            $approvedQuery->whereDate('booking_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $approvedQuery->whereDate('booking_date', '<=', $request->end_date);
        }

        $rawApprovedInquiries = $approvedQuery->latest('booking_date')->get();
        $approvedInquiryIds = $rawApprovedInquiries->pluck('id');

        // 2. Strictly sum sales from the payments table where status is paid
        $totalSales = Payment::whereIn('inquiry_id', $approvedInquiryIds)
            ->where('status', Payment::STATUS_PAID)
            ->sum('amount');

        // 3. Map each inquiry and pull its paid amount exclusively from its paid payments relation
        $approvedInquiries = $rawApprovedInquiries->map(function ($sale) {
            $paidPayment = $sale->payments
                ->where('status', Payment::STATUS_PAID)
                ->sortByDesc('id')
                ->first();

            $sale->formatted_ref = $sale->booking_reference ?: 'R' . str_pad((string) $sale->id, 6, '0', STR_PAD_LEFT);
            $sale->formatted_date = $sale->booking_date ? \Carbon\Carbon::parse($sale->booking_date)->format('M d, Y') : 'N/A';

            // Get amount strictly from the payments table record (0.00 if no paid payment exists)
            $sale->paid_amount = $paidPayment ? (float) $paidPayment->amount : 0.00;

            return $sale;
        });

        $approvedCount = $approvedInquiries->count();
        $pendingInquiriesCount = Inquiry::where('status', 'pending')->count();
        $totalInquiriesCount = Inquiry::count();

        // 4. Return JSON if requested via AJAX/fetch (visible in Network tab), otherwise render Blade view
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'totalSales' => (float) $totalSales,
                'approvedCount' => $approvedCount,
                'pendingInquiriesCount' => $pendingInquiriesCount,
                'totalInquiriesCount' => $totalInquiriesCount,
                'approvedInquiries' => $approvedInquiries,
            ]);
        }

        return view('admin.reports', compact(
            'approvedInquiries',
            'totalSales',
            'approvedCount',
            'pendingInquiriesCount',
            'totalInquiriesCount'
        ));
    }
}
