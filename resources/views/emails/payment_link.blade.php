<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
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

        .label {
            font-weight: bold;
            color: #1e293b;
        }

        .button {
            display: inline-block;
            padding: 12px 18px;
            background: #1C3627;
            color: #fff !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <h2>Hello {{ $inquiry->full_name }},</h2>

    <p>Your booking request for <strong>{{ $inquiry->venue_title }}</strong> is ready for payment.</p>

    <div class="card">
        <p><span class="label">Booking reference:</span> {{ $inquiry->booking_reference ?? $inquiry->getBookingReference() }}</p>
        <p><span class="label">Venue:</span> {{ $inquiry->venue_title }}</p>
        <p><span class="label">Schedule:</span> {{ $inquiry->booking_date }} ({{ $inquiry->start_time }} - {{ $inquiry->end_time }})</p>
        <p><span class="label">Amount:</span> {{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</p>
        <p><span class="label">Invoice expires:</span> {{ optional($payment->expires_at)->format('F j, Y g:i A') ?? 'See invoice' }}</p>
    </div>

    @if ($payment->invoice_url)
        <p>
            <a href="{{ $payment->invoice_url }}" class="button">Pay Now</a>
        </p>
        <p style="font-size: 12px; color: #475569;">If the button does not work, open this link: {{ $payment->invoice_url }}</p>
    @else
        <p>Please contact us if your payment link is unavailable.</p>
    @endif

    <p>Once payment is confirmed, your reservation can be approved by our team.</p>

    <p>Best regards,<br>
        <strong>The Gazebo Events Place</strong>
    </p>
</body>

</html>
