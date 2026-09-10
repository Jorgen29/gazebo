<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Gazebo Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Alex+Brush&family=Cormorant+Garamond:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="min-h-screen bg-[#F7F4EE] text-[#1C3627] font-sans antialiased">

    <div class="flex min-h-screen">
        <x-admin.sidebar />

        <div class="flex-1 flex flex-col min-w-0 min-h-screen bg-[#FBF9F5]">
            <x-admin.header />

            <main class="p-6 lg:p-8 space-y-8 flex-1 overflow-y-auto">

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div
                        class="bg-[#FFFDFB] border border-[#E5DDD0] p-6 rounded-2xl shadow-[0_12px_30px_rgba(28,54,39,0.05)] flex items-center justify-between transition-all hover:-translate-y-0.5 hover:shadow-[0_14px_40px_rgba(28,54,39,0.08)]">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-[#6B7E73]">Total Inquiries
                            </p>
                            <h3 class="font-serif text-4xl leading-none text-[#1C3627] mt-2">{{ $totalInquiries }}</h3>
                        </div>
                        <div
                            class="w-12 h-12 bg-[#EAF0EB] border border-[#D7E2D8] rounded-xl flex items-center justify-center text-[#1C3627]">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>

                    <div
                        class="bg-[#FFFDFB] border border-[#E5DDD0] p-6 rounded-2xl shadow-[0_12px_30px_rgba(28,54,39,0.05)] flex items-center justify-between transition-all hover:-translate-y-0.5 hover:shadow-[0_14px_40px_rgba(28,54,39,0.08)]">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-[#6B7E73]">Pending
                                Inquiries</p>
                            <h3 class="font-serif text-4xl leading-none text-[#B89462] mt-2">{{ $pendingInquiries }}
                            </h3>
                        </div>
                        <div
                            class="w-12 h-12 bg-[#F5EFE6] border border-[#E8D9B9] rounded-xl flex items-center justify-center text-[#B89462]">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>

                    <div
                        class="bg-[#FFFDFB] border border-[#E5DDD0] p-6 rounded-2xl shadow-[0_12px_30px_rgba(28,54,39,0.05)] flex items-center justify-between transition-all hover:-translate-y-0.5 hover:shadow-[0_14px_40px_rgba(28,54,39,0.08)]">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-[#6B7E73]">Approved
                                Inquiries</p>
                            <h3 class="font-serif text-4xl leading-none text-[#1C3627] mt-2">{{ $verifiedPayments }}
                            </h3>
                        </div>
                        <div
                            class="w-12 h-12 bg-[#EAF4EE] border border-[#D4E7D9] rounded-xl flex items-center justify-center text-[#1C3627]">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>

                    <div
                        class="bg-[#FFFDFB] border border-[#E5DDD0] p-6 rounded-2xl shadow-[0_12px_30px_rgba(28,54,39,0.05)] flex items-center justify-between transition-all hover:-translate-y-0.5 hover:shadow-[0_14px_40px_rgba(28,54,39,0.08)]">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-[#6B7E73]">Active Venues
                            </p>
                            <h3 class="font-serif text-4xl leading-none text-[#B89462] mt-2">{{ $activeVenues }}</h3>
                        </div>
                        <div
                            class="w-12 h-12 bg-[#F7F1E7] border border-[#E9D7B5] rounded-xl flex items-center justify-center text-[#B89462]">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-[#FFFDFB] border border-[#E5DDD0] rounded-2xl p-6 shadow-[0_12px_30px_rgba(28,54,39,0.05)]">
                    <div class="mb-6 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-[#B89462]">Operations</p>
                            <h3 class="font-serif text-3xl text-[#1C3627] mt-1">Recent Pending Inquiries</h3>
                        </div>
                        <p class="text-sm text-[#6B7E73]">Venue booking requests requiring review</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm border-collapse">
                            <thead>
                                <tr
                                    class="border-b border-[#E5DDD0] text-[#6B7E73] uppercase font-bold text-[10px] tracking-[0.22em]">
                                    <th class="py-4 px-4">Ref ID</th>
                                    <th class="py-4 px-4">Client Name</th>
                                    <th class="py-4 px-4">Venue</th>
                                    <th class="py-4 px-4">Booking Date</th>
                                    <th class="py-4 px-4">Status</th>
                                    <th class="py-4 px-4 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-dashed divide-[#E5DDD0] text-[#1C3627]">
                                @forelse ($recentPendingInquiries as $inquiry)
                                    <tr class="hover:bg-[#F7F4EE] transition-colors">
                                        <td class="py-4 px-4 font-mono text-[#B89462] font-bold text-sm">
                                            #{{ $inquiry->id }}</td>
                                        <td class="py-4 px-4 font-semibold text-[#1C3627]">{{ $inquiry->full_name }}
                                        </td>
                                        <td class="py-4 px-4 font-medium">{{ $inquiry->venue_title }}</td>
                                        <td class="py-4 px-4">
                                            {{ $inquiry->booking_date ? $inquiry->booking_date->format('M d, Y') : 'N/A' }}
                                        </td>
                                        <td class="py-4 px-4">
                                            <span
                                                class="bg-[#F7F1E7] text-[#9B6C2D] border border-[#E9D7B5] px-3 py-1 rounded-full text-[10px] font-semibold uppercase tracking-[0.14em]">
                                                {{ ucfirst($inquiry->status) }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 text-right">
                                            <a href="{{ route('admin.inquiries') }}?status=pending"
                                                class="inline-block bg-[#1C3627] hover:bg-[#2e4a3b] text-white px-4 py-2 rounded-full text-[10px] font-semibold uppercase tracking-[0.18em] transition-all shadow-sm">
                                                View Details
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 px-4 text-center text-[#6B7E73]">
                                            No pending inquiries found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div
                    class="bg-[#FFFDFB] border border-[#E5DDD0] rounded-2xl p-6 shadow-[0_12px_30px_rgba(28,54,39,0.05)]">
                    <div class="mb-6 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-[#B89462]">Upcoming
                                Schedule</p>
                            <h3 class="font-serif text-3xl text-[#1C3627] mt-1">Approved Reservations</h3>
                        </div>
                        <p class="text-sm text-[#6B7E73]">Confirmed bookings that are still upcoming</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm border-collapse">
                            <thead>
                                <tr
                                    class="border-b border-[#E5DDD0] text-[#6B7E73] uppercase font-bold text-[10px] tracking-[0.22em]">
                                    <th class="py-4 px-4">Client Name</th>
                                    <th class="py-4 px-4">Venue</th>
                                    <th class="py-4 px-4">Booking Date</th>
                                    <th class="py-4 px-4">Time</th>
                                    <th class="py-4 px-4">Reference</th>
                                    <th class="py-4 px-4 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-dashed divide-[#E5DDD0] text-[#1C3627]">
                                @forelse ($upcomingApprovedInquiries as $inquiry)
                                    <tr class="hover:bg-[#F7F4EE] transition-colors">
                                        <td class="py-4 px-4 font-semibold text-[#1C3627]">{{ $inquiry->full_name }}
                                        </td>
                                        <td class="py-4 px-4 font-medium">{{ $inquiry->venue_title }}</td>
                                        <td class="py-4 px-4">
                                            {{ $inquiry->booking_date ? $inquiry->booking_date->format('M d, Y') : 'N/A' }}
                                        </td>
                                        <td class="py-4 px-4 font-medium text-[#1C3627]">
                                            {{ $inquiry->start_time ? \Carbon\Carbon::parse($inquiry->start_time)->format('g:i A') : 'N/A' }}
                                            -
                                            {{ $inquiry->end_time ? \Carbon\Carbon::parse($inquiry->end_time)->format('g:i A') : 'N/A' }}
                                        </td>
                                        <td class="py-4 px-4 font-mono text-[#B89462] font-bold text-xs">
                                            {{ $inquiry->booking_reference ?: 'R' . str_pad((string) $inquiry->id, 6, '0', STR_PAD_LEFT) }}
                                        </td>
                                        <td class="py-4 px-4 text-right">
                                            <a href="{{ route('admin.inquiries') }}?status=approved"
                                                class="inline-block bg-[#1C3627] hover:bg-[#2e4a3b] text-white px-4 py-2 rounded-full text-[10px] font-semibold uppercase tracking-[0.18em] transition-all shadow-sm">
                                                View Booking
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 px-4 text-center text-[#6B7E73]">
                                            No upcoming approved inquiries found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </main>
        </div>

        <!-- Session Alerts -->
        @if (session('success'))
            <script>
                Swal.fire({
                    title: 'Welcome Back!',
                    text: "{{ session('success') }}",
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    background: '#ffffff',
                    color: '#111827',
                    customClass: {
                        popup: 'border border-gray-200 rounded-2xl shadow-xl'
                    }
                });
            </script>
        @endif

</body>

</html>
