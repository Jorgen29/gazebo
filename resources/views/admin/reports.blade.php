<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales & Inquiry Reports - Gazebo Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="min-h-screen bg-[#F8F9FA] text-[#11231B] antialiased" x-data="{
    loading: true,
    search: '',
    startDate: '{{ request('start_date') }}',
    endDate: '{{ request('end_date') }}',
    totalSales: 0,
    approvedCount: 0,
    pendingInquiriesCount: 0,
    totalInquiriesCount: 0,
    approvedInquiries: [],

    // Unified Calendar Picker States
    dateRangeOpen: false,
    modalMonth: new Date().getMonth(),
    modalYear: new Date().getFullYear(),
    monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],

    get dateRangeDisplay() {
        if (this.startDate && this.endDate) {
            return this.startDate + ' to ' + this.endDate;
        }
        if (this.startDate) {
            return this.startDate + ' (onwards)';
        }
        return 'Select Date Range';
    },

    get modalCalendarDays() {
        const days = [];
        const firstDay = new Date(this.modalYear, this.modalMonth, 1).getDay();
        const daysInMonth = new Date(this.modalYear, this.modalMonth + 1, 0).getDate();

        for (let i = 0; i < firstDay; i++) {
            days.push({ day: '', isCurrentMonth: false });
        }

        for (let d = 1; d <= daysInMonth; d++) {
            const mStr = String(this.modalMonth + 1).padStart(2, '0');
            const dStr = String(d).padStart(2, '0');
            const dateStr = `${this.modalYear}-${mStr}-${dStr}`;
            days.push({
                day: d,
                isCurrentMonth: true,
                dateStr: dateStr
            });
        }
        return days;
    },

    init() {
        this.fetchReports();
    },

    fetchReports() {
        this.loading = true;
        let url = '{{ route('admin.reports') }}';
        let params = new URLSearchParams();
        if (this.startDate) params.append('start_date', this.startDate);
        if (this.endDate) params.append('end_date', this.endDate);
        if (params.toString()) url += '?' + params.toString();

        fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.totalSales = data.totalSales;
                    this.approvedCount = data.approvedCount;
                    this.pendingInquiriesCount = data.pendingInquiriesCount;
                    this.totalInquiriesCount = data.totalInquiriesCount;
                    this.approvedInquiries = data.approvedInquiries;
                }
                this.loading = false;
            })
            .catch(error => {
                console.error('Error fetching reports:', error);
                this.loading = false;
            });
    },

    selectDate(dateStr) {
        if (!this.startDate || (this.startDate && this.endDate)) {
            this.startDate = dateStr;
            this.endDate = '';
        } else if (this.startDate && !this.endDate) {
            if (dateStr < this.startDate) {
                this.startDate = dateStr;
            } else {
                this.endDate = dateStr;
                this.dateRangeOpen = false;
                this.fetchReports();
            }
        }
    },

    matchesSearch(customer, venue, ref, date) {
        if (!this.search.trim()) return true;
        const q = this.search.toLowerCase().trim();
        return (customer && customer.toLowerCase().includes(q)) ||
            (venue && venue.toLowerCase().includes(q)) ||
            (ref && ref.toLowerCase().includes(q)) ||
            (date && date.toLowerCase().includes(q));
    },

    formatCurrency(amount) {
        return '₱' + Number(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },

    exportToExcel() {
        // Filter records using the active search query so the export matches the table
        const filtered = this.approvedInquiries.filter(sale =>
            this.matchesSearch(sale.full_name, sale.venue_title, sale.formatted_ref, sale.formatted_date)
        );

        if (filtered.length === 0) {
            Swal.fire({
                title: 'No Data to Export',
                text: 'There are no approved sales records matching your current filter.',
                icon: 'warning',
                confirmButtonColor: '#1B4332'
            });
            return;
        }

        // Map data to clean Excel columns
        const rows = filtered.map(sale => ({
            'Reference': sale.formatted_ref,
            'Client Name': sale.full_name,
            'Email': sale.email,
            'Venue': sale.venue_title,
            'Event Date': sale.formatted_date,
            'Status': 'Approved',
            'Sale Amount (PHP)': Number(sale.paid_amount)
        }));

        // Calculate filtered total and append summary row
        const filteredTotal = filtered.reduce((sum, sale) => sum + Number(sale.paid_amount), 0);
        rows.push({
            'Reference': '',
            'Client Name': '',
            'Email': '',
            'Venue': '',
            'Event Date': '',
            'Status': 'TOTAL REVENUE',
            'Sale Amount (PHP)': filteredTotal
        });

        // Create worksheet & set column widths
        const worksheet = XLSX.utils.json_to_sheet(rows);
        worksheet['!cols'] = [
            { wch: 16 }, // Reference
            { wch: 24 }, // Client Name
            { wch: 28 }, // Email
            { wch: 24 }, // Venue
            { wch: 16 }, // Event Date
            { wch: 16 }, // Status
            { wch: 20 } // Sale Amount
        ];

        // Build workbook and trigger download
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Approved Sales');

        const dateSuffix = (this.startDate && this.endDate) ?
            `${this.startDate}_to_${this.endDate}` :
            new Date().toISOString().slice(0, 10);

        XLSX.writeFile(workbook, `Gazebo_Sales_Report_${dateSuffix}.xlsx`);
    }
}">

    <div class="flex min-h-screen">
        <x-admin.sidebar />

        <div class="flex-1 flex flex-col min-w-0 min-h-screen bg-[#F8F9FA]">
            <x-admin.header />

            <main class="p-6 lg:p-8 space-y-8 flex-1 overflow-y-auto">

                <!-- Page Header & Filter Controls -->
                <div
                    class="bg-[#FFFFFF] p-6 rounded-2xl shadow-[0_12px_30px_rgba(17,35,27,0.04)] flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#D4A373]">Financial &
                            Operations Analytics</span>
                        <h1 class="text-2xl sm:text-3xl font-bold text-[#11231B] mt-1">Sales & Inquiry Reports</h1>
                        <p class="text-xs text-slate-500 mt-1">Revenue figures strictly reflect <strong
                                class="text-[#1B4332]">Approved</strong> venue reservations.</p>
                    </div>

                    <div class="flex flex-wrap items-end gap-3">
                        <!-- Unified Date Range Picker -->
                        <div class="relative flex flex-col">
                            <label
                                class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Date
                                Range Filter</label>
                            <button type="button" @click="dateRangeOpen = !dateRangeOpen"
                                class="bg-[#F8F9FA] text-[#11231B] text-xs rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#52B788] flex items-center justify-between gap-3 w-64 border border-transparent">
                                <span x-text="dateRangeDisplay"></span>
                                <i class="fa-regular fa-calendar text-slate-400"></i>
                            </button>

                            <!-- Calendar Dropdown -->
                            <div x-show="dateRangeOpen" @click.away="dateRangeOpen = false" x-cloak
                                class="absolute left-0 top-full mt-3 w-80 bg-white text-gray-800 rounded-2xl shadow-2xl border border-gray-200 p-4 z-[100] space-y-3">
                                <div class="flex items-center justify-between border-b pb-2">
                                    <h4 class="text-xs font-semibold uppercase tracking-wider text-[#1C3627]">Filter
                                        Availability</h4>
                                    <template x-if="startDate || endDate">
                                        <button type="button"
                                            @click="startDate = ''; endDate = ''; dateRangeOpen = false; fetchReports();"
                                            class="text-[10px] text-[#E63946] font-semibold hover:underline">Clear</button>
                                    </template>
                                </div>

                                <div class="rounded-2xl border border-[#E5DDD0] bg-[#F7F4EE] p-3">
                                    <div class="flex items-center justify-between mb-3">
                                        <button type="button"
                                            @click="if (modalMonth === 0) { modalMonth = 11; modalYear--; } else { modalMonth--; }"
                                            class="w-8 h-8 rounded-full border border-[#E5DDD0] bg-white flex items-center justify-center text-[#1C3627] hover:bg-[#EAF0EB] transition-all">
                                            <i class="fa-solid fa-chevron-left text-xs"></i>
                                        </button>
                                        <span class="text-xs font-bold text-[#1C3627] uppercase tracking-[0.12em]"
                                            x-text="monthNames[modalMonth].substring(0,3) + ' ' + modalYear"></span>
                                        <button type="button"
                                            @click="if (modalMonth === 11) { modalMonth = 0; modalYear++; } else { modalMonth++; }"
                                            class="w-8 h-8 rounded-full border border-[#E5DDD0] bg-white flex items-center justify-center text-[#1C3627] hover:bg-[#EAF0EB] transition-all">
                                            <i class="fa-solid fa-chevron-right text-xs"></i>
                                        </button>
                                    </div>

                                    <div
                                        class="grid grid-cols-7 gap-1 text-center text-[9px] font-bold uppercase tracking-[0.12em] text-[#6B7E73] mb-2">
                                        <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
                                    </div>

                                    <div class="grid grid-cols-7 gap-1">
                                        <template x-for="(item, idx) in modalCalendarDays" :key="idx">
                                            <div>
                                                <template x-if="item.isCurrentMonth">
                                                    <button type="button" @click="selectDate(item.dateStr)"
                                                        :class="item.dateStr === startDate || item.dateStr === endDate ?
                                                            'bg-[#1C3627] text-white ring-2 ring-[#1C3627]/30' : (
                                                                startDate && endDate && item.dateStr > startDate && item
                                                                .dateStr < endDate ? 'bg-[#EAF0EB] text-[#1C3627]' :
                                                                'bg-white text-[#1C3627] hover:bg-[#EAF0EB]')"
                                                        class="relative w-full h-10 rounded-lg border border-[#E5DDD0] text-[10px] font-semibold transition-all">
                                                        <span x-text="item.day || ''" class="block"></span>
                                                    </button>
                                                </template>
                                                <template x-if="!item.isCurrentMonth">
                                                    <div
                                                        class="w-full h-10 rounded-lg border border-transparent bg-gray-50/50">
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <!-- Excel Export Button -->
                        <button type="button" @click="exportToExcel()"
                            class="bg-[#D4A373] hover:opacity-90 text-[#11231B] text-xs font-semibold px-4 py-2.5 rounded-xl transition-all shadow-sm flex items-center gap-2 h-[38px]">
                            <i class="fa-solid fa-file-excel text-[#11231B]"></i>
                            <span class="text-[#11231B]">Export Excel</span>
                        </button>
                    </div>
                </div>

                <!-- Summary Metric Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div
                        class="bg-[#FFFFFF] p-6 rounded-2xl shadow-[0_12px_30px_rgba(17,35,27,0.05)] flex items-center justify-between transition-all hover:-translate-y-0.5">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Total Approved
                                Sales</p>
                            <h3 class="text-2xl sm:text-3xl font-bold text-[#1B4332] mt-2"
                                x-text="formatCurrency(totalSales)"></h3>
                            <p class="text-[11px] text-[#2D6A4F] mt-1 font-medium">From approved bookings only</p>
                        </div>
                        <div
                            class="w-12 h-12 bg-[#52B788]/15 rounded-xl flex items-center justify-center text-[#1B4332] text-lg shrink-0">
                            <i class="fa-solid fa-wallet"></i>
                        </div>
                    </div>

                    <div
                        class="bg-[#FFFFFF] p-6 rounded-2xl shadow-[0_12px_30px_rgba(17,35,27,0.05)] flex items-center justify-between transition-all hover:-translate-y-0.5">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Approved Sales
                                Count</p>
                            <h3 class="text-2xl sm:text-3xl font-bold text-[#11231B] mt-2" x-text="approvedCount"></h3>
                            <p class="text-[11px] text-slate-500 mt-1">Completed / Confirmed</p>
                        </div>
                        <div
                            class="w-12 h-12 bg-[#2D6A4F]/15 rounded-xl flex items-center justify-center text-[#2D6A4F] text-lg shrink-0">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                    </div>

                    <div
                        class="bg-[#FFFFFF] p-6 rounded-2xl shadow-[0_12px_30px_rgba(17,35,27,0.05)] flex items-center justify-between transition-all hover:-translate-y-0.5">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Pending
                                Inquiries
                            </p>
                            <h3 class="text-2xl sm:text-3xl font-bold text-[#D4A373] mt-2"
                                x-text="pendingInquiriesCount">
                            </h3>
                            <p class="text-[11px] text-slate-500 mt-1">Excluded from sales</p>
                        </div>
                        <div
                            class="w-12 h-12 bg-[#D4A373]/15 rounded-xl flex items-center justify-center text-[#D4A373] text-lg shrink-0">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                    </div>

                    <div
                        class="bg-[#FFFFFF] p-6 rounded-2xl shadow-[0_12px_30px_rgba(17,35,27,0.05)] flex items-center justify-between transition-all hover:-translate-y-0.5">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">All Inquiries
                            </p>
                            <h3 class="text-2xl sm:text-3xl font-bold text-[#11231B] mt-2" x-text="totalInquiriesCount">
                            </h3>
                            <p class="text-[11px] text-slate-500 mt-1">Total platform requests</p>
                        </div>
                        <div
                            class="w-12 h-12 bg-[#11231B]/10 rounded-xl flex items-center justify-center text-[#11231B] text-lg shrink-0">
                            <i class="fa-solid fa-boxes-stacked"></i>
                        </div>
                    </div>
                </div>

                <!-- Approved Sales Breakdown Table -->
                <div class="bg-[#FFFFFF] rounded-2xl p-6 shadow-[0_12px_30px_rgba(17,35,27,0.05)] space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#D4A373]">Verified
                                Revenue</span>
                            <h2 class="text-lg sm:text-xl font-bold text-[#11231B] mt-0.5">Approved Sales Ledger</h2>
                        </div>

                        <!-- Search Input -->
                        <div class="relative flex items-center">
                            <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs absolute left-3.5"></i>
                            <input type="text" x-model="search" placeholder="Search client, venue, reference..."
                                class="bg-[#F8F9FA] text-[#11231B] placeholder-slate-400 text-xs rounded-full pl-9 pr-8 py-2.5 w-full sm:w-72 focus:outline-none focus:ring-2 focus:ring-[#52B788]">
                            <button x-show="search.length > 0" @click="search = ''"
                                class="absolute right-3 text-slate-400 hover:text-[#11231B]">
                                <i class="fa-solid fa-xmark text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm border-collapse">
                            <thead>
                                <tr
                                    class="border-b border-[#E2E8F0] text-slate-400 uppercase font-bold text-[10px] tracking-[0.18em]">
                                    <th class="py-3.5 px-4">Reference</th>
                                    <th class="py-3.5 px-4">Client Name</th>
                                    <th class="py-3.5 px-4">Venue</th>
                                    <th class="py-3.5 px-4">Event Date</th>
                                    <th class="py-3.5 px-4">Status</th>
                                    <th class="py-3.5 px-4 text-right">Sale Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E2E8F0] text-[#11231B]" x-cloak>
                                <template x-if="loading">
                                    <tr>
                                        <td colspan="6" class="py-10 text-center text-slate-400 text-xs">
                                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Loading report data...
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="!loading && approvedInquiries.length === 0">
                                    <tr>
                                        <td colspan="6" class="py-10 text-center text-slate-400 text-xs">
                                            No approved sales records found for the selected criteria.
                                        </td>
                                    </tr>
                                </template>
                                <template x-for="sale in approvedInquiries" :key="sale.id">
                                    <tr class="hover:bg-[#F8F9FA] transition-colors"
                                        x-show="matchesSearch(sale.full_name, sale.venue_title, sale.formatted_ref, sale.formatted_date)">
                                        <td class="py-4 px-4 font-mono text-xs font-bold text-[#D4A373]"
                                            x-text="sale.formatted_ref"></td>
                                        <td class="py-4 px-4">
                                            <div class="font-semibold text-[#11231B] text-xs" x-text="sale.full_name">
                                            </div>
                                            <div class="text-[11px] text-slate-500" x-text="sale.email"></div>
                                        </td>
                                        <td class="py-4 px-4 text-xs font-medium" x-text="sale.venue_title"></td>
                                        <td class="py-4 px-4 text-xs text-slate-600" x-text="sale.formatted_date">
                                        </td>
                                        <td class="py-4 px-4">
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-[#52B788]/15 text-[#1B4332] uppercase tracking-wider">
                                                <i class="fa-solid fa-check text-[9px]"></i>
                                                Approved
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 text-right font-bold text-sm text-[#1B4332]"
                                            x-text="formatCurrency(sale.paid_amount)"></td>
                                    </tr>
                                </template>
                            </tbody>
                            <template x-if="!loading && approvedInquiries.length > 0">
                                <tfoot>
                                    <tr class="bg-[#F8F9FA] font-bold text-[#11231B]">
                                        <td colspan="5"
                                            class="py-4 px-4 text-right text-xs uppercase tracking-wider text-slate-500">
                                            Total Approved Revenue
                                        </td>
                                        <td class="py-4 px-4 text-right text-base text-[#1B4332]"
                                            x-text="formatCurrency(totalSales)">
                                        </td>
                                    </tr>
                                </tfoot>
                            </template>
                        </table>
                    </div>
                </div>

            </main>
        </div>
    </div>

</body>

</html>
