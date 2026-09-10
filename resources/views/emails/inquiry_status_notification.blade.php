<!DOCTYPE html>
<html>

<head>
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
    </style>
</head>

<body>
    <h2>Hello {{ $inquiry->full_name }},</h2>

    <p>Your reservation for <strong>{{ $inquiry->venue_title }}</strong> has been <strong>{{ $statusLabel }}</strong>.
    </p>

    <div class="card">
        <p><strong>Venue:</strong> {{ $inquiry->venue_title }}</p>
        <p><strong>Date:</strong> {{ $inquiry->booking_date }}</p>
        <p><strong>Time:</strong> {{ $inquiry->start_time }} - {{ $inquiry->end_time }}</p>
    </div>

    @if ($statusLabel === 'Approved')
        <p>We have approved your reservation. Please continue with the payment instructions sent to you, and reply with
            your proof of payment once completed.</p>
    @elseif ($statusLabel === 'Declined')
        <p>We are unable to move forward with this request at this time. If you would like to discuss another date or
            venue, please contact us.</p>
    @elseif ($statusLabel === 'Cancelled')
        <p>Your reservation has been cancelled and is no longer held for the selected date.</p>
    @endif

    <p>Best regards,<br>
        <strong>The Gazebo Events Place</strong>
    </p>
</body>

</html>
