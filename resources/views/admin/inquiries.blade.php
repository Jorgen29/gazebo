<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inquiries Management - Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Alex+Brush&family=Cormorant+Garamond:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="min-h-screen bg-[#F7F4EE] text-[#1C3627] font-sans antialiased" x-data="{
    // Search Filter State
    search: '',

    // Filter Logic Function
    matchesSearch(customer, email, venue, status, date) {
        if (!this.search.trim()) return true;
        const q = this.search.toLowerCase().trim();
        return (customer && customer.toLowerCase().includes(q)) ||
            (email && email.toLowerCase().includes(q)) ||
            (venue && venue.toLowerCase().includes(q)) ||
            (status && status.toLowerCase().includes(q)) ||
            (date && date.toLowerCase().includes(q));
    },

    // Receipt Modal State
    modalOpen: false,
    modalReceiptUrl: '',
    modalCustomer: '',
    openReceipt(url, customer) {
        this.modalReceiptUrl = url;
        this.modalCustomer = customer;
        this.modalOpen = true;
    },

    // Action Modal State
    actionModalOpen: false,
    selectedInquiryId: null,
    selectedCustomer: '',
    selectedInquiryStatus: '',
    hasPaymentProof: false,
    hasPaymentRequested: false,
    openActionModal(id, customer, proof, paymentRequested, status = '') {
        this.selectedInquiryId = id;
        this.selectedCustomer = customer;
        this.selectedInquiryStatus = status;
        this.hasPaymentProof = Boolean(proof);
        this.hasPaymentRequested = Boolean(paymentRequested);
        this.actionModalOpen = true;
    }
}">

    <div class="flex min-h-screen">
        <x-admin.sidebar />

        <div class="flex-1 flex flex-col min-w-0 min-h-screen bg-[#FBF9F5]">
            <x-admin.header />

            <main class="p-6 lg:p-8 space-y-6 flex-1 overflow-y-auto">

                <!-- Main Card Container -->
                <div class="bg-[#FFFFFF] border border-[#E5DDD0] rounded-2xl p-6 shadow-xs space-y-6">

                    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-[#B89462]">Guest Records
                            </p>
                            <h1 class="font-serif text-4xl text-[#1C3627] mt-1">Venue Reservation Inquiries</h1>
                            <p class="text-sm text-[#6B7E73] mt-2">Manage customer inquiries, booking schedules, and
                                receipts</p>
                        </div>

                        <div class="relative flex items-center">
                            <input type="text" x-model="search" placeholder="Search name, venue, status, date..."
                                class="bg-[#F7F4EE] text-[#1C3627] placeholder-[#6B7E73] text-xs font-medium rounded-full border border-[#E5DDD0] pl-10 pr-9 py-2.5 w-64 sm:w-80 focus:outline-none focus:ring-2 focus:ring-[#6F927D]/35 focus:bg-white shadow-sm transition-all">
                            <svg class="w-4 h-4 text-[#B89462] absolute left-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <button x-show="search.length > 0" @click="search = ''"
                                class="absolute right-3 text-[#6B7E73] hover:text-[#1C3627]">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr
                                    class="text-[#6B7E73] font-bold uppercase text-[10px] tracking-[0.22em] border-b border-[#E5DDD0]">
                                    <th class="pb-3 px-3">Customer</th>
                                    <th class="pb-3 px-3">Venue</th>
                                    <th class="pb-3 px-3">Status</th>
                                    <th class="pb-3 px-3">Receipt Proof</th>
                                    <th class="pb-3 px-3">Booking Schedule</th>
                                    <th class="pb-3 px-3">Date Submitted</th>
                                    <th class="pb-3 px-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-dashed divide-[#E5DDD0] text-[#1C3627]">
                                @forelse($inquiries as $item)
                                    @php
                                        $formattedBookingDate = $item->booking_date
                                            ? \Carbon\Carbon::parse($item->booking_date)->format('F j, Y')
                                            : 'N/A';
                                    @endphp
                                    <tr class="hover:bg-[#F7F4EE]/60 transition-colors"
                                        x-show="matchesSearch(
                                        '{{ addslashes($item->full_name) }}',
                                        '{{ addslashes($item->email) }}',
                                        '{{ addslashes($item->venue_title) }}',
                                        '{{ addslashes($item->status) }}',
                                        '{{ addslashes($formattedBookingDate) }}'
                                    )">

                                        <td class="py-4 px-3">
                                            <div class="font-bold text-[#1C3627] text-xs">{{ $item->full_name }}</div>
                                            <div class="text-[11px] text-[#6B7E73]">{{ $item->email }}</div>
                                        </td>

                                        <td class="py-4 px-3 font-semibold text-[#1C3627]">
                                            {{ $item->venue_title }}
                                        </td>

                                        <td class="py-4 px-3">
                                            @if ($item->status === 'approved')
                                                <span
                                                    class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-[#EAF4EE] text-[#1C3627] uppercase border border-[#D4E7D9] tracking-[0.14em]">Approved</span>
                                            @elseif($item->status === 'declined')
                                                <span
                                                    class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-[#FCECEC] text-[#7A2B2B] uppercase border border-[#EEC6C6] tracking-[0.14em]">Declined</span>
                                            @elseif($item->status === 'cancelled')
                                                <span
                                                    class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-[#F3EFEA] text-[#5A514A] uppercase border border-[#D9D2CA] tracking-[0.14em]">Cancelled</span>
                                            @else
                                                <span
                                                    class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-[#F7F1E7] text-[#9B6C2D] uppercase border border-[#E9D7B5] tracking-[0.14em]">Pending</span>
                                            @endif
                                        </td>

                                        <td class="py-4 px-3">
                                            @if ($item->payment_proof)
                                                <button type="button"
                                                    @click="openReceipt('{{ asset('storage/' . $item->payment_proof) }}', '{{ addslashes($item->full_name) }}')"
                                                    class="inline-flex items-center gap-1.5 text-[#1C3627] hover:text-[#2E4A3B] font-semibold text-xs underline decoration-[#E5DDD0] underline-offset-2 cursor-pointer">
                                                    <svg class="w-3.5 h-3.5 text-[#B89A62]" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    View Receipt
                                                </button>
                                            @else
                                                <form action="{{ route('admin.inquiries.upload-proof', $item->id) }}"
                                                    method="POST" enctype="multipart/form-data"
                                                    class="flex items-center gap-1.5">
                                                    @csrf
                                                    <input type="file" name="payment_proof" required
                                                        class="text-[10px] text-[#6B7E73] file:mr-2 file:py-1 file:px-2 file:rounded-full file:border-0 file:bg-[#F7F4EE] file:text-[#1C3627] hover:file:bg-[#E5DDD0] cursor-pointer">
                                                    <button type="submit"
                                                        class="px-2.5 py-1.5 bg-[#1C3627] text-white rounded-full text-[10px] font-semibold uppercase tracking-[0.14em] hover:bg-[#2E4A3B]">Upload</button>
                                                </form>
                                            @endif
                                        </td>

                                        <td class="py-4 px-3 font-medium text-[#1C3627]">
                                            {{ $formattedBookingDate }}
                                            <span class="block text-[11px] text-[#B89462] font-semibold">
                                                {{ $item->start_time ? \Carbon\Carbon::parse($item->start_time)->format('g:i A') : '' }}
                                                @if ($item->end_time)
                                                    - {{ \Carbon\Carbon::parse($item->end_time)->format('g:i A') }}
                                                @endif
                                            </span>
                                        </td>

                                        <td class="py-4 px-3 font-medium text-[#6B7E73]">
                                            {{ $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('F j, Y g:i A') : 'N/A' }}
                                        </td>

                                        <td class="py-4 px-3 text-right">
                                            <button type="button"
                                                @click="openActionModal({{ $item->id }}, '{{ addslashes($item->full_name) }}', {{ $item->payment_proof ? 'true' : 'false' }}, {{ $item->payment_requested_at ? 'true' : 'false' }}, '{{ $item->status }}')"
                                                class="w-9 h-9 inline-flex items-center justify-center bg-[#F7F4EE] hover:bg-[#1C3627] text-[#1C3627] hover:text-white rounded-full transition-all border border-[#E5DDD0] shadow-sm">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-8 text-center text-gray-400 font-medium">
                                            No reservation inquiries found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer Pagination -->
                    <div
                        class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-3 border-t border-[#E5DDD0]">
                        <div class="flex items-center gap-3 text-xs text-[#6B7E73]">
                            <select
                                class="bg-[#F7F4EE] border border-[#E5DDD0] text-[#1C3627] text-xs rounded-full px-2.5 py-1.5 font-semibold focus:ring-0">
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                            <span>
                                @if ($inquiries instanceof \Illuminate\Pagination\LengthAwarePaginator)
                                    Showing {{ $inquiries->firstItem() ?? 0 }} to {{ $inquiries->lastItem() ?? 0 }} of
                                    {{ $inquiries->total() }} records
                                @else
                                    Showing 1 to {{ count($inquiries) }} of {{ count($inquiries) }} records
                                @endif
                            </span>
                        </div>

                        <div class="flex items-center gap-1 text-xs">
                            <button
                                class="p-1.5 text-[#B7B7B7] hover:text-[#6B7E73] transition-colors cursor-not-allowed"
                                disabled>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <button
                                class="w-7 h-7 rounded-full bg-[#1C3627] text-white font-bold flex items-center justify-center shadow-sm">1</button>
                            <button class="p-1.5 text-[#6B7E73] hover:text-[#1C3627] transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>

                </div>

            </main>
        </div>

        <!-- Modals -->
        <x-admin.receipt-modal />
        <x-admin.action-modal />

        <!-- SweetAlert Session Alerts -->
        @if (session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: "{{ session('success') }}",
                        confirmButtonColor: '#6F927D',
                        customClass: {
                            popup: 'rounded-2xl',
                            confirmButton: 'rounded-xl text-xs px-4 py-2 font-semibold'
                        }
                    });
                });
            </script>
        @endif

        @if (session('error'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: "{{ session('error') }}",
                        confirmButtonColor: '#6F927D',
                        customClass: {
                            popup: 'rounded-2xl',
                            confirmButton: 'rounded-xl text-xs px-4 py-2 font-semibold'
                        }
                    });
                });
            </script>
        @endif

</body>

</html>
