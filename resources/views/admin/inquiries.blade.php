<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

<body class="min-h-screen bg-[#F7F4EE] text-[#1C3627] font-sans antialiased" x-data="inquiriesManagement()">

    <div class="flex min-h-screen">
        <x-admin.sidebar />

        <div class="flex-1 flex flex-col min-w-0 min-h-screen bg-[#FBF9F5]">
            <x-admin.header />

            <main class="p-6 lg:p-8 space-y-6 flex-1 overflow-y-auto">

                <!-- Main Card Container -->
                <div class="bg-[#FFFFFF] border border-[#E5DDD0] rounded-2xl p-6 shadow-xs space-y-6">

                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-[#B89462]">Guest Records
                        </p>
                        <h1 class="font-serif text-4xl text-[#1C3627] mt-1">Venue Reservation Inquiries</h1>
                        <p class="text-sm text-[#6B7E73] mt-2">Manage customer inquiries, Xendit payment sessions, and
                            booking status</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 pt-4 border-t border-[#E5DDD0]">
                            <form method="GET" action="{{ route('admin.inquiries') }}" @submit.prevent>
                                <select name="status" x-model="statusFilter" @change="applyStatusFilter()"
                                    class="bg-[#F7F4EE] border border-[#E5DDD0] text-[#1C3627] text-xs rounded-full px-3 py-2.5 font-semibold focus:outline-none focus:ring-2 focus:ring-[#6F927D]/35 shadow-sm">
                                    <option value="">All statuses</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="declined">Declined</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </form>

                            <a href="{{ url(request()->getRequestUri()) }}" x-ref="refreshLink"
                                @click.prevent="refresh()"
                                class="inline-flex items-center justify-center gap-1.5 bg-[#FFFFFF] border border-[#E5DDD0] text-[#1C3627] text-xs font-semibold rounded-full px-4 py-2.5 hover:bg-[#F7F4EE] focus:outline-none focus:ring-2 focus:ring-[#6F927D]/35 shadow-sm transition-colors">
                                <svg class="w-3.5 h-3.5 text-[#B89462]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Refresh
                            </a>

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
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr
                                    class="text-[#6B7E73] font-bold uppercase text-[10px] tracking-[0.22em] border-b border-[#E5DDD0]">
                                    <th class="pb-3 px-3">Customer</th>
                                    <th class="pb-3 px-3">Venue</th>
                                    <th class="pb-3 px-3">Status</th>
                                    <th class="pb-3 px-3">Payment</th>
                                    <th class="pb-3 px-3">Booking Schedule</th>
                                    <th class="pb-3 px-3">Date Submitted</th>
                                    <th class="pb-3 px-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-dashed divide-[#E5DDD0] text-[#1C3627]">
                                <template x-if="loading">
                                    <tr>
                                        <td colspan="7" class="py-8 text-center text-gray-400 font-medium">
                                            Loading inquiries...
                                        </td>
                                    </tr>
                                </template>

                                <template x-if="!loading && listError">
                                    <tr>
                                        <td colspan="7" class="py-8 text-center text-red-500 font-medium">
                                            Unable to load inquiries. Please refresh the page.
                                        </td>
                                    </tr>
                                </template>

                                <template x-if="!loading && !listError && filteredInquiries.length === 0">
                                    <tr>
                                        <td colspan="7" class="py-8 text-center text-gray-400 font-medium">
                                            No reservation inquiries found.
                                        </td>
                                    </tr>
                                </template>

                                <template x-for="item in filteredInquiries" :key="item.id">
                                    <tr class="transition-colors"
                                        :class="isHighlighted(item.id) ?
                                            'bg-[#F7F1E7] ring-1 ring-[#B89462] shadow-[inset_0_0_0_1px_#B89462]' :
                                            'hover:bg-[#F7F4EE]/60'">

                                        <td class="py-4 px-3">
                                            <div class="font-bold text-[#1C3627] text-xs" x-text="item.full_name"></div>
                                            <div class="text-[11px] text-[#6B7E73]" x-text="item.email"></div>
                                        </td>

                                        <td class="py-4 px-3 font-semibold text-[#1C3627]" x-text="item.venue_title"></td>

                                        <td class="py-4 px-3">
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase border tracking-[0.14em]"
                                                :class="statusBadgeClass(item.status)" x-text="item.status"></span>
                                        </td>

                                        <td class="py-4 px-3">
                                            <template x-if="item.payment">
                                                <div class="space-y-1">
                                                    <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold uppercase border tracking-[0.14em]"
                                                        :class="paymentBadgeClass(item.payment.status)"
                                                        x-text="item.payment.status"></span>
                                                    <div class="text-[11px] font-semibold text-[#1C3627]"
                                                        x-text="item.payment.amount"></div>
                                                    <template x-if="item.payment.expires_at">
                                                        <div class="text-[10px] text-[#6B7E73]">
                                                            Expires <span x-text="item.payment.expires_at"></span>
                                                        </div>
                                                    </template>
                                                    <template x-if="item.payment.payment_link_url">
                                                        <a :href="item.payment.payment_link_url" target="_blank"
                                                            rel="noopener"
                                                            class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#1C3627] underline decoration-[#E5DDD0] underline-offset-2 hover:text-[#2E4A3B]">
                                                            Open payment link
                                                        </a>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="!item.payment">
                                                <span class="text-[11px] text-[#6B7E73] italic">No payment link</span>
                                            </template>
                                        </td>

                                        <td class="py-4 px-3 font-medium text-[#1C3627]">
                                            <span x-text="item.booking_date"></span>
                                            <span class="block text-[11px] text-[#B89462] font-semibold"
                                                x-text="item.time_range"></span>
                                        </td>

                                        <td class="py-4 px-3 font-medium text-[#6B7E73]" x-text="item.submitted_at"></td>

                                        <td class="py-4 px-3 text-right">
                                            <div class="flex items-center justify-end">
                                                <button type="button" @click="openActionModal(item.modal)"
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
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer Pagination -->
                    <div
                        class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-3 border-t border-[#E5DDD0]">
                        <div class="flex items-center gap-3 text-xs text-[#6B7E73]">
                            <span>
                                Showing <span x-text="from ?? 0"></span> to <span x-text="to ?? 0"></span> of
                                <span x-text="total"></span> records
                            </span>
                        </div>

                        <div class="flex items-center gap-1 text-xs">
                            <button type="button" @click="goToPage(currentPage - 1)" :disabled="!prevPageUrl"
                                class="p-1.5 transition-colors"
                                :class="prevPageUrl ? 'text-[#6B7E73] hover:text-[#1C3627]' : 'text-[#B7B7B7] cursor-not-allowed'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>

                            <template x-for="link in numberedLinks" :key="link.label">
                                <button type="button" @click="link.url && goToPageUrl(link.url)"
                                    :disabled="!link.url"
                                    class="w-7 h-7 rounded-full font-bold flex items-center justify-center shadow-sm transition-colors"
                                    :class="link.active ? 'bg-[#1C3627] text-white' : (link.url ? 'text-[#6B7E73] hover:bg-[#F7F4EE]' : 'text-[#B7B7B7] cursor-not-allowed')"
                                    x-text="link.label"></button>
                            </template>

                            <button type="button" @click="goToPage(currentPage + 1)" :disabled="!nextPageUrl"
                                class="p-1.5 transition-colors"
                                :class="nextPageUrl ? 'text-[#6B7E73] hover:text-[#1C3627]' : 'text-[#B7B7B7] cursor-not-allowed'">
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

        <!-- Inquiries List State -->
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('inquiriesManagement', () => ({
                    inquiries: [],
                    loading: true,
                    listError: false,
                    search: '',
                    statusFilter: @js((string) ($status ?? '')),
                    highlightId: @js($highlightId ?? null),
                    currentPage: {{ (int) request()->query('page', 1) }},
                    lastPage: 1,
                    total: 0,
                    from: null,
                    to: null,
                    prevPageUrl: null,
                    nextPageUrl: null,
                    links: [],

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
                    selectedPaymentStatus: '',
                    selectedPaymentAmount: '',
                    selectedPaymentExpiry: '',
                    selectedPaymentLinkUrl: '',
                    selectedRefundStatus: '',
                    selectedRefundAmount: '',
                    selectedRefundFailureCode: '',
                    selectedRefundNote: '',
                    selectedRefundReason: 'CANCELLATION',
                    refundNote: '',
                    refundConfirmed: false,
                    canRefund: false,

                    routes: {
                        index: @js(route('admin.inquiries')),
                    },

                    init() {
                        this.fetchInquiries(this.currentPage);
                    },

                    get filteredInquiries() {
                        return this.inquiries.filter((item) => this.matchesSearch(
                            item.full_name,
                            item.email,
                            item.venue_title,
                            item.status,
                            item.booking_date
                        ));
                    },

                    get numberedLinks() {
                        return this.links.filter((link) => /^\d+$/.test(String(link.label)));
                    },

                    buildUrl(page = 1) {
                        const params = new URLSearchParams();
                        if (this.statusFilter) params.set('status', this.statusFilter);
                        if (page > 1) params.set('page', page);

                        const query = params.toString();

                        return query ? `${this.routes.index}?${query}` : this.routes.index;
                    },

                    fetchInquiries(page = 1) {
                        this.loading = true;
                        this.listError = false;
                        this.currentPage = Math.max(1, Number(page) || 1);

                        fetch(this.buildUrl(this.currentPage), {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then((res) => {
                                if (!res.ok) throw new Error(`Request failed with status ${res.status}`);

                                return res.json();
                            })
                            .then((data) => {
                                this.inquiries = data.data ?? [];
                                this.currentPage = data.current_page ?? 1;
                                this.lastPage = data.last_page ?? 1;
                                this.total = data.total ?? 0;
                                this.from = data.from ?? null;
                                this.to = data.to ?? null;
                                this.prevPageUrl = data.prev_page_url ?? null;
                                this.nextPageUrl = data.next_page_url ?? null;
                                this.links = data.links ?? [];
                                this.loading = false;
                                this.syncUrl();
                            })
                            .catch((err) => {
                                console.error('Failed fetching inquiries:', err);
                                this.inquiries = [];
                                this.loading = false;
                                this.listError = true;
                            });
                    },

                    applyStatusFilter() {
                        this.fetchInquiries(1);
                    },

                    refresh() {
                        this.fetchInquiries(this.currentPage);
                    },

                    goToPage(page) {
                        if (page < 1 || page > this.lastPage) return;

                        this.fetchInquiries(page);
                    },

                    goToPageUrl(url) {
                        if (!url) return;

                        this.fetchInquiries(new URL(url).searchParams.get('page') ?? 1);
                    },

                    syncUrl() {
                        const url = new URL(window.location.href);

                        if (this.statusFilter) {
                            url.searchParams.set('status', this.statusFilter);
                        } else {
                            url.searchParams.delete('status');
                        }

                        if (this.currentPage > 1) {
                            url.searchParams.set('page', this.currentPage);
                        } else {
                            url.searchParams.delete('page');
                        }

                        window.history.replaceState({}, '', url);

                        if (this.$refs.refreshLink) {
                            this.$refs.refreshLink.href = this.buildUrl(this.currentPage);
                        }
                    },

                    matchesSearch(customer, email, venue, status, date) {
                        if (!this.search.trim()) return true;

                        const q = this.search.toLowerCase().trim();

                        return (customer && customer.toLowerCase().includes(q)) ||
                            (email && email.toLowerCase().includes(q)) ||
                            (venue && venue.toLowerCase().includes(q)) ||
                            (status && status.toLowerCase().includes(q)) ||
                            (date && date.toLowerCase().includes(q));
                    },

                    isHighlighted(id) {
                        return this.highlightId !== null && Number(this.highlightId) === Number(id);
                    },

                    statusBadgeClass(status) {
                        const classes = {
                            approved: 'bg-[#EAF4EE] text-[#1C3627] border-[#D4E7D9]',
                            declined: 'bg-[#FCECEC] text-[#7A2B2B] border-[#EEC6C6]',
                            cancelled: 'bg-[#F3EFEA] text-[#5A514A] border-[#D9D2CA]',
                        };

                        return classes[status] ?? 'bg-[#F7F1E7] text-[#9B6C2D] border-[#E9D7B5]';
                    },

                    paymentBadgeClass(status) {
                        const classes = {
                            paid: 'bg-[#EAF4EE] text-[#1C3627] border-[#D4E7D9]',
                            pending: 'bg-[#F7F1E7] text-[#9B6C2D] border-[#E9D7B5]',
                            expired: 'bg-[#FCECEC] text-[#7A2B2B] border-[#EEC6C6]',
                            refunded: 'bg-[#EEF0FA] text-[#3B3F7A] border-[#D5D9F0]',
                        };

                        return classes[status] ?? 'bg-[#F3EFEA] text-[#5A514A] border-[#D9D2CA]';
                    },

                    openActionModal(data) {
                        this.selectedInquiryId = data.id ?? null;
                        this.selectedCustomer = data.customer ?? '';
                        this.selectedInquiryStatus = data.status ?? '';
                        this.selectedVenue = data.venue ?? '';
                        this.selectedBookingDate = data.bookingDate ?? '';
                        this.selectedTimeRange = data.timeRange ?? '';
                        this.selectedTotalCost = data.totalCost ?? '';
                        this.selectedEmail = data.email ?? '';
                        this.selectedContact = data.contact ?? '';
                        this.selectedPaymentStatus = data.paymentStatus ?? '';
                        this.selectedPaymentAmount = data.paymentAmount ?? '';
                        this.selectedPaymentExpiry = data.paymentExpiry ?? '';
                        this.selectedPaymentLinkUrl = data.paymentLinkUrl ?? '';
                        this.selectedRefundStatus = data.refundStatus ?? '';
                        this.selectedRefundAmount = data.refundAmount ?? '';
                        this.selectedRefundFailureCode = data.refundFailureCode ?? '';
                        this.selectedRefundNote = data.refundNote ?? '';
                        this.selectedRefundReason = 'CANCELLATION';
                        this.refundNote = '';
                        this.refundConfirmed = false;
                        this.canRefund = data.canRefund ?? false;
                        this.actionModalOpen = true;
                    },

                    showInquiryDetails() {
                        this.detailsModalOpen = true;
                        this.actionModalOpen = false;
                    }
                }));
            });
        </script>

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
