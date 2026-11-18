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
    </style>
</head>

<body>
    @php
        $inquiry = $refund->payment->inquiry;
        $bookingReference = $inquiry->booking_reference ?? $inquiry->getBookingReference();
    @endphp

    <h2>Hello {{ $inquiry->full_name }},</h2>

    <p>Your refund for booking <strong>{{ $bookingReference }}</strong> has been processed successfully.</p>

    <div class="card">
        <p><strong>Booking reference:</strong> {{ $bookingReference }}</p>
        <p><strong>Venue:</strong> {{ $inquiry->venue_title }}</p>
        <p><strong>Refunded amount:</strong> <span class="amount">₱{{ number_format((float) $refund->amount, 2) }}</span></p>
    </div>

    <p>The refund has been returned to the original payment method used for your booking.</p>

    <p>Best regards,<br>
        <strong>The Gazebo Events Place</strong>
    </p>
</body>

</html>
