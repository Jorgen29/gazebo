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
    detailsModalOpen: false,
    selectedInquiryId: null,
    selectedCustomer: '',
    selectedInquiryStatus: '',
    selectedVenue: '',
    selectedBookingDate: '',
    selectedTimeRange: '',
    selectedTotalCost: '',
    selectedEmail: '',
    selectedContact: '',
    hasPaymentProof: false,
    hasPaymentRequested: false,
    highlightId: {{ $highlightId ?? 'null' }},
    isHighlighted(id) {
        return this.highlightId !== null && Number(this.highlightId) === Number(id);
    },
    openActionModal(id, customer, proof, paymentRequested, status = '', venue = '', bookingDate = '', timeRange = '', totalCost = '', email = '', contact = '') {
        this.selectedInquiryId = id;
        this.selectedCustomer = customer;
        this.selectedInquiryStatus = status;
        this.selectedVenue = venue;
        this.selectedBookingDate = bookingDate;
        this.selectedTimeRange = timeRange;
        this.selectedTotalCost = totalCost;
        this.selectedEmail = email;
        this.selectedContact = contact;
        this.hasPaymentProof = Boolean(proof);
        this.hasPaymentRequested = Boolean(paymentRequested);
        this.actionModalOpen = true;
    },

    showInquiryDetails(customer, venue, bookingDate, timeRange, status, totalCost, email, contact) {
        this.selectedCustomer = customer;
        this.selectedVenue = venue;
        this.selectedBookingDate = bookingDate;
        this.selectedTimeRange = timeRange;
        this.selectedInquiryStatus = status;
        this.selectedTotalCost = totalCost;
        this.selectedEmail = email;
        this.selectedContact = contact;
        this.detailsModalOpen = true;
    },

    confirmReceiptUpload(form) {
        if (!form) return;

        const fileInput = form.querySelector('input[name=\'payment_proof\']');
        if (!fileInput || !fileInput.files || !fileInput.files.length) {
            return;
        }

        Swal.fire({
            title: 'Confirm receipt upload?',
            text: 'This will attach the payment proof to this inquiry.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, upload',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#1C3627',
            cancelButtonColor: '#d1d5db',
            customClass: {
                popup: 'rounded-2xl',
                confirmButton: 'rounded-xl text-xs px-4 py-2 font-semibold'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
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

                                        $venueRate = \App\Models\Venue::find($item->venue_id)?->price_per_hour ?? 0;
                                        $hours = 0;
                                        if ($item->start_time && $item->end_time) {
                                            $startSeconds = strtotime($item->start_time);
                                            $endSeconds = strtotime($item->end_time);
                                            if ($endSeconds > $startSeconds) {
                                                $hours = ($endSeconds - $startSeconds) / 3600;
                                            }
                                        }
                                        $totalCost = max($hours, 0) * (float) $venueRate;
                                        $totalCostLabel = '₱' . number_format($totalCost, 2);
                                    @endphp
                                    <tr class="transition-colors"
                                        :class="isHighlighted({{ $item->id }}) ?
                                            'bg-[#F7F1E7] ring-1 ring-[#B89462] shadow-[inset_0_0_0_1px_#B89462]' :
                                            'hover:bg-[#F7F4EE]/60'"
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

                                        <td class="py-4 px-3 text-left">
                                            @if ($item->payment_proof)
                                                <div class="flex justify-start w-full">
                                                    <button type="button"
                                                        @click="openReceipt('{{ asset('storage/' . $item->payment_proof) }}', '{{ addslashes($item->full_name) }}')"
                                                        class="inline-flex items-center justify-start gap-1.5 text-[#1C3627] hover:text-[#2E4A3B] font-semibold text-xs underline decoration-[#E5DDD0] underline-offset-2 cursor-pointer">
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
                                                </div>
                                            @else
                                                <form action="{{ route('admin.inquiries.upload-proof', $item->id) }}"
                                                    method="POST" enctype="multipart/form-data"
                                                    class="flex w-full items-center justify-start gap-1.5"
                                                    @submit.prevent="confirmReceiptUpload($event.currentTarget)">
                                                    @csrf
                                                    <input type="file" name="payment_proof" required class="hidden"
                                                        x-ref="receiptFile{{ $item->id }}"
                                                        @change="if ($event.target.files && $event.target.files.length) { $event.target.form.dispatchEvent(new Event('submit', { cancelable: true })); }">
                                                    <button type="button"
                                                        @click="$refs.receiptFile{{ $item->id }}.click()"
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
                                            <div class="flex items-center justify-end">
                                                <button type="button"
                                                    @click="openActionModal({{ $item->id }}, '{{ addslashes($item->full_name) }}', {{ $item->payment_proof ? 'true' : 'false' }}, {{ $item->payment_requested_at ? 'true' : 'false' }}, '{{ $item->status }}', '{{ addslashes($item->venue_title) }}', '{{ addslashes($formattedBookingDate) }}', '{{ addslashes($item->start_time ? \Carbon\Carbon::parse($item->start_time)->format('g:i A') : '') }} - {{ addslashes($item->end_time ? \Carbon\Carbon::parse($item->end_time)->format('g:i A') : '') }}', '{{ addslashes($totalCostLabel) }}', '{{ addslashes($item->email) }}', '{{ addslashes($item->email_contact) }}')"
                                                    class="w-9 h-9 inline-flex items-center justify-center bg-[#F7F4EE] hover:bg-[#1C3627] text-[#1C3627] hover:text-white rounded-full transition-all border border-[#E5DDD0] shadow-sm">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                                    </svg>
                                                </button>
                                            </div>
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

        <div x-show="detailsModalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="bg-white border border-[#E5DDD0] rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl"
                @click.outside="detailsModalOpen = false">
                <div class="flex items-center justify-between border-b border-[#E5DDD0] px-6 py-4">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-[#B89462]">Reservation</p>
                        <h3 class="mt-1 text-lg font-bold text-[#1C3627]">Details</h3>
                    </div>
                    <button @click="detailsModalOpen = false"
                        class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-4 text-sm text-[#1C3627]">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="bg-[#F7F4EE] rounded-xl p-3 border border-[#E5DDD0]">
                            <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-[#6B7E73]">Customer
                            </div>
                            <div class="mt-1 font-semibold" x-text="selectedCustomer"></div>
                        </div>
                        <div class="bg-[#F7F4EE] rounded-xl p-3 border border-[#E5DDD0]">
                            <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-[#6B7E73]">Status</div>
                            <div class="mt-1 font-semibold" x-text="selectedInquiryStatus"></div>
                        </div>
                        <div class="bg-[#F7F4EE] rounded-xl p-3 border border-[#E5DDD0] sm:col-span-2">
                            <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-[#6B7E73]">Venue</div>
                            <div class="mt-1 font-semibold" x-text="selectedVenue"></div>
                        </div>
                        <div class="bg-[#F7F4EE] rounded-xl p-3 border border-[#E5DDD0]">
                            <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-[#6B7E73]">Booking Date
                            </div>
                            <div class="mt-1 font-semibold" x-text="selectedBookingDate"></div>
                        </div>
                        <div class="bg-[#F7F4EE] rounded-xl p-3 border border-[#E5DDD0]">
                            <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-[#6B7E73]">Time</div>
                            <div class="mt-1 font-semibold" x-text="selectedTimeRange"></div>
                        </div>
                        <div class="bg-[#F7F4EE] rounded-xl p-3 border border-[#E5DDD0]">
                            <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-[#6B7E73]">Email</div>
                            <div class="mt-1 font-semibold break-all" x-text="selectedEmail"></div>
                        </div>
                        <div class="bg-[#F7F4EE] rounded-xl p-3 border border-[#E5DDD0]">
                            <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-[#6B7E73]">Contact</div>
                            <div class="mt-1 font-semibold" x-text="selectedContact"></div>
                        </div>
                        <div class="bg-[#F7F4EE] rounded-xl p-3 border border-[#E5DDD0] sm:col-span-2">
                            <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-[#6B7E73]">Total Cost
                            </div>
                            <div class="mt-1 text-base font-bold text-[#1C3627]" x-text="selectedTotalCost"></div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-[#E5DDD0] px-6 py-4 flex justify-end">
                    <button @click="detailsModalOpen = false"
                        class="px-4 py-2 bg-[#1C3627] hover:bg-[#2E4A3B] text-white text-xs font-semibold rounded-xl transition-all">
                        Close
                    </button>
                </div>
            </div>
        </div>

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
