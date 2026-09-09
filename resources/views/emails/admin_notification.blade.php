<h2>New Booking Request</h2>
<p>A customer has submitted a new booking request:</p>

<ul>
    <li><strong>Customer Name:</strong> {{ $inquiry->full_name }}</li>
    <li><strong>Email/Contact:</strong> {{ $inquiry->email_contact }} ({{ $inquiry->email }})</li>
    <li><strong>Venue:</strong> {{ $inquiry->venue_title }}</li>
    <li><strong>Date:</strong> {{ $inquiry->booking_date }}</li>
    <li><strong>Time Slot:</strong> {{ $inquiry->start_time }} - {{ $inquiry->end_time }}</li>
</ul>

<p><a href="{{ route('admin.inquiries') }}">View in Admin Dashboard</a></p>
