<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inquiries Management - Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="min-h-screen bg-[#F7F4EE] text-gray-700 font-sans antialiased flex" x-data="{
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
    hasPaymentProof: false,
    hasPaymentRequested: false,
    openActionModal(id, customer, proof, paymentRequested) {
        this.selectedInquiryId = id;
        this.selectedCustomer = customer;
        this.hasPaymentProof = Boolean(proof);
        this.hasPaymentRequested = Boolean(paymentRequested);
        this.actionModalOpen = true;
    }
}">

    <!-- Admin Sidebar Component -->
    <x-admin.sidebar />

    <!-- Main Workspace -->
    <div class="flex-1 flex flex-col min-w-0 min-h-screen">

        <!-- Admin Header Component -->
        <x-admin.header />

        <!-- Main Content Canvas -->
        <main class="p-6 lg:p-8 space-y-6 flex-1 overflow-y-auto">

            <!-- Main Card Container -->
            <div class="bg-[#FFFFFF] border border-[#E5DDD0] rounded-2xl p-6 shadow-xs space-y-6">

                <!-- Header Action Row -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-base font-bold text-gray-900 tracking-tight">Venue Reservation Inquiries</h1>
                        <p class="text-xs text-gray-400 mt-0.5">Manage customer inquiries, booking schedules, and
                            receipts</p>
                    </div>

                    <!-- Search Input with Live Filter -->
                    <div class="relative flex items-center">
                        <input type="text" x-model="search" placeholder="Search name, venue, status, date..."
                            class="bg-[#F7F4EE] text-gray-800 placeholder-gray-400 text-xs font-medium rounded-xl border border-[#E5DDD0] pl-9 pr-8 py-2 w-64 sm:w-80 focus:outline-none focus:ring-2 focus:ring-[#6F927D]/40 focus:bg-white transition-all">
                        <svg class="w-4 h-4 text-[#B89A62] absolute left-3" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <!-- Clear Search Button -->
                        <button x-show="search.length > 0" @click="search = ''"
                            class="absolute right-2.5 text-gray-400 hover:text-gray-600">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr
                                class="text-gray-400 font-bold uppercase text-[11px] tracking-wider border-b border-[#E5DDD0]">
                                <th class="pb-3 px-3">CUSTOMER</th>
                                <th class="pb-3 px-3">VENUE</th>
                                <th class="pb-3 px-3">STATUS</th>
                                <th class="pb-3 px-3">RECEIPT PROOF</th>
                                <th class="pb-3 px-3">BOOKING SCHEDULE</th>
                                <th class="pb-3 px-3">DATE SUBMITTED</th>
                                <th class="pb-3 px-3 text-right">ACTION</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dashed divide-[#E5DDD0] text-gray-600">
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
                                        <div class="font-bold text-[#6F927D] text-xs">{{ $item->full_name }}</div>
                                        <div class="text-[11px] text-gray-400">{{ $item->email }}</div>
                                    </td>

                                    <td class="py-4 px-3 font-semibold text-gray-800">
                                        {{ $item->venue_title }}
                                    </td>

                                    <td class="py-4 px-3">
                                        @if ($item->status === 'approved')
                                            <span
                                                class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 uppercase border border-emerald-200/60">Approved</span>
                                        @elseif($item->status === 'declined')
                                            <span
                                                class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-rose-50 text-rose-700 uppercase border border-rose-200/60">Declined</span>
                                        @else
                                            <span
                                                class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-[#E5DDD0]/60 text-[#B89A62] uppercase border border-[#B89A62]/30">Pending</span>
                                        @endif
                                    </td>

                                    <td class="py-4 px-3">
                                        @if ($item->payment_proof)
                                            <button type="button"
                                                @click="openReceipt('{{ asset('storage/' . $item->payment_proof) }}', '{{ addslashes($item->full_name) }}')"
                                                class="inline-flex items-center gap-1.5 text-[#6F927D] hover:text-[#5b7a67] font-semibold text-xs underline decoration-[#E5DDD0] underline-offset-2 cursor-pointer">
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
                                                    class="text-[10px] text-gray-400 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:bg-[#F7F4EE] file:text-gray-600 hover:file:bg-[#E5DDD0] cursor-pointer">
                                                <button type="submit"
                                                    class="px-2 py-1 bg-[#6F927D] text-white rounded-md text-[10px] font-semibold hover:bg-[#5b7a67]">Upload</button>
                                            </form>
                                        @endif
                                    </td>

                                    <td class="py-4 px-3 font-medium text-gray-700">
                                        {{ $formattedBookingDate }}
                                        <span class="block text-[11px] text-[#B89A62] font-semibold">
                                            {{ $item->start_time ? \Carbon\Carbon::parse($item->start_time)->format('g:i A') : '' }}
                                            @if ($item->end_time)
                                                - {{ \Carbon\Carbon::parse($item->end_time)->format('g:i A') }}
                                            @endif
                                        </span>
                                    </td>

                                    <td class="py-4 px-3 font-medium text-gray-600">
                                        {{ $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('F j, Y g:i A') : 'N/A' }}
                                    </td>

                                    <!-- Hamburger Action Trigger Button -->
                                    <td class="py-4 px-3 text-right">
                                        <button type="button"
                                            @click="openActionModal({{ $item->id }}, '{{ addslashes($item->full_name) }}', {{ $item->payment_proof ? 'true' : 'false' }}, {{ $item->payment_requested_at ? 'true' : 'false' }})"
                                            class="w-8 h-8 inline-flex items-center justify-center bg-[#F7F4EE] hover:bg-[#6F927D] text-gray-600 hover:text-white rounded-lg transition-all shadow-xs">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 6h16M4 12h16M4 18h16" />
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
                    class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2 border-t border-[#E5DDD0]/60">
                    <div class="flex items-center gap-3 text-xs text-gray-500">
                        <select
                            class="bg-[#F7F4EE] border border-[#E5DDD0] text-gray-700 text-xs rounded-lg px-2.5 py-1.5 font-semibold focus:ring-0">
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
                        <button class="p-1.5 text-gray-300 hover:text-gray-500 transition-colors cursor-not-allowed"
                            disabled>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <button
                            class="w-7 h-7 rounded-lg bg-[#6F927D] text-white font-bold flex items-center justify-center shadow-xs">1</button>
                        <button class="p-1.5 text-gray-400 hover:text-gray-600 transition-colors">
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
