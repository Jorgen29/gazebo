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

        .account-title {
            font-weight: bold;
            color: #1e293b;
        }
    </style>
</head>

<body>
    <h2>Hello {{ $inquiry->full_name }},</h2>

    <p>Thank you for your interest in reserving <strong>{{ $inquiry->venue_title }}</strong>!</p>

    <p>Your requested schedule for <strong>{{ $inquiry->booking_date }}</strong> ({{ $inquiry->start_time }} -
        {{ $inquiry->end_time }}) is currently held for you.</p>

    <p>Please send your downpayment / payment to any of the accounts below to secure your booking:</p>

    <div class="card">
        <p class="account-title">📱 GCash Option</p>
        <p><strong>Account Name:</strong> Jorgen Bacolod<br>
            <strong>GCash Number:</strong> 09XX-XXX-XXXX
        </p>

        <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 15px 0;">

        <p class="account-title">🏦 Bank Transfer Option</p>
        <p><strong>Bank:</strong> BDO / BPI<br>
            <strong>Account Name:</strong> Jorgen Bacolod<br>
            <strong>Account Number:</strong> 0012-3456-7890
        </p>
    </div>

    <p><strong>📌 Next Steps:</strong></p>
    <p>Simply **reply to this email with an image/screenshot attachment of your payment receipt**. Once verified, we
        will mark your reservation as <strong>Approved</strong>.</p>

    <p>Best regards,<br>
        <strong>The Gazebo Events Place</strong>
    </p>
</body>

</html>
