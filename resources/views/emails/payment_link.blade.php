<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.6;
        }

        .card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 20px;
            border-radius: 8px;
            margin: 15px 0;
        }

        .amount {
            font-size: 24px;
            font-weight: bold;
            color: #1c3627;
        }

        .button {
            display: inline-block;
            background: #1c3627;
            color: #ffffff;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: bold;
            margin-top: 12px;
        }
    </style>
</head>

<body>
    {{-- INFO: Xendit-hosted customer email is deliberately deferred. This application sends the only payment link email so delivery, branding, and content stay under our control. --}}
    <h2>Hello {{ $inquiry->full_name }},</h2>

    <p>Your booking request for <strong>{{ $inquiry->venue_title }}</strong> has been reviewed. Please complete your
        payment using the secure Xendit checkout link below.</p>

    <div class="card">
        <p><strong>Booking reference:</strong> {{ $inquiry->booking_reference ?? $inquiry->getBookingReference() }}</p>
        <p><strong>Venue:</strong> {{ $inquiry->venue_title }}</p>
        <p><strong>Schedule:</strong>
            {{ \Carbon\Carbon::parse($inquiry->booking_date)->format('F j, Y') }},
            {{ \Carbon\Carbon::parse($inquiry->start_time)->format('g:i A') }} -
            {{ \Carbon\Carbon::parse($inquiry->end_time)->format('g:i A') }}
        </p>
        <p><strong>Amount due:</strong> <span class="amount">₱{{ number_format((float) $payment->amount, 2) }}</span></p>
        <p><strong>Payment link expires:</strong>
            {{ $payment->expires_at ? $payment->expires_at->format('F j, Y g:i A') : 'N/A' }}
        </p>

        @if ($payment->payment_link_url)
            <a class="button" href="{{ $payment->payment_link_url }}">Pay securely with Xendit</a>
        @endif
    </div>

    <p>This is the full payment for your booking. Your reservation will be confirmed by our team once the payment is
        received.</p>

    <p>Best regards,<br>
        <strong>The Gazebo Events Place</strong>
    </p>
</body>

</html>
