<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inquire Venues - The Gazebo Events Place</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Alex+Brush&family=Cormorant+Garamond:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .font-cursive {
            font-family: 'Alex Brush', cursive;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-[#FBF9F5] text-gray-800 antialiased font-sans" x-data="{
    detailModalOpen: false,
    activeSpace: null,
    activeImageIndex: 0,

    // Header Filter State
    dateFilterOpen: false,
    startDate: new Date().toISOString().split('T')[0],
    startTime: '08:00',
    endTime: '17:00',
    todayStr: new Date().toISOString().split('T')[0],

    // Calendar Navigation inside Modal
    modalMonth: new Date().getMonth(),
    modalYear: new Date().getFullYear(),
    selectedCalendarDate: new Date().toISOString().split('T')[0],
    monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],

    reservedSchedule: @js($reservedSchedule ?? []),

    spaces: @js($spaces ?? []),

    getRoomBookingsForDate(spaceId, dateStr) {
        if (!spaceId || !dateStr) return [];
        return (this.reservedSchedule[spaceId] || []).filter(b => b.date === dateStr);
    },

    get modalCalendarDays() {
        let date = new Date(this.modalYear, this.modalMonth, 1);
        let days = [];
        let firstDayIndex = date.getDay();

        for (let i = 0; i < firstDayIndex; i++) {
            days.push({ day: '', dateStr: null, isCurrentMonth: false, isPast: true });
        }

        let lastDay = new Date(this.modalYear, this.modalMonth + 1, 0).getDate();
        for (let i = 1; i <= lastDay; i++) {
            let monthStr = String(this.modalMonth + 1).padStart(2, '0');
            let dayStr = String(i).padStart(2, '0');
            let dateStr = `${this.modalYear}-${monthStr}-${dayStr}`;
            let isPast = dateStr < this.todayStr;
            let bookings = this.activeSpace ? this.getRoomBookingsForDate(this.activeSpace.id, dateStr) : [];

            days.push({ day: i, dateStr: dateStr, isCurrentMonth: true, isPast: isPast, bookings: bookings, isBooked: bookings.length > 0 });
        }
        return days;
    },

    get activeSelectedDateBookings() {
        if (!this.activeSpace || !this.selectedCalendarDate) return [];
        return this.getRoomBookingsForDate(this.activeSpace.id, this.selectedCalendarDate);
    },

    openDetailModal(space) {
        this.activeSpace = space;
        this.activeImageIndex = 0;
        this.syncDateSelection(this.startDate || this.todayStr);
        this.detailModalOpen = true;
    },

    // REDIRECT TO NEW DEDICATED BOOKING PAGE WITH SELECTED SPACE ID & DATE
    proceedToNewPage() {
        let dateParam = this.selectedCalendarDate || '{{ now()->format('Y-m-d') }}';
        let spaceParam = this.activeSpace ? this.activeSpace.id : 'grand-hall';

        // Redirects to /book?space=grand-hall&date=2026-09-15
        window.location.href = `{{ route('venue.book') }}?space=${spaceParam}&date=${dateParam}`;
    },

    get formattedFilterLabel() {
        let dateLabel = this.startDate || 'Select a date';
        let timeLabel = (this.startTime && this.endTime) ? `${this.startTime} – ${this.endTime}` : 'All Hours';
        return `${dateLabel} (${timeLabel})`;
    },

    init() {
        this.startDate = this.todayStr;
        this.selectedCalendarDate = this.startDate;
        const today = new Date(`${this.todayStr}T00:00:00`);
        this.modalMonth = today.getMonth();
        this.modalYear = today.getFullYear();
    },

    syncDateSelection(dateStr) {
        if (!dateStr) return;
        this.startDate = dateStr;
        this.selectedCalendarDate = dateStr;
        const dateObj = new Date(`${dateStr}T00:00:00`);
        this.modalMonth = dateObj.getMonth();
        this.modalYear = dateObj.getFullYear();
    },

    timeToMinutes(value) {
        if (!value) return 0;
        const [hours, minutes] = String(value).split(':').map(Number);
        return (Number(hours) || 0) * 60 + (Number(minutes) || 0);
    },

    getVenueAvailability(space) {
        const businessStart = this.timeToMinutes(this.startTime);
        const businessEnd = this.timeToMinutes(this.endTime);
        const totalDayMinutes = Math.max(0, businessEnd - businessStart);

        if (totalDayMinutes <= 0) {
            return { hasAvailable: false, totalFreeMinutes: 0, label: 'Fully booked' };
        }

        if (!this.startDate) {
            return { hasAvailable: false, totalFreeMinutes: 0, label: 'Select a date' };
        }

        const bookings = this.getRoomBookingsForDate(space.id, this.startDate);
        if (!bookings.length) {
            return {
                hasAvailable: true,
                totalFreeMinutes: totalDayMinutes,
                label: this.formatRemainingTime(totalDayMinutes)
            };
        }

        let occupiedMinutes = 0;
        bookings.forEach((booking) => {
            const bookingStart = this.timeToMinutes(booking.start || booking.start_time || '00:00');
            const bookingEnd = this.timeToMinutes(booking.end || booking.end_time || '23:59');
            const overlapStart = Math.max(bookingStart, businessStart);
            const overlapEnd = Math.min(bookingEnd, businessEnd);
            occupiedMinutes += Math.max(0, overlapEnd - overlapStart);
        });

        const remainingMinutes = Math.max(0, totalDayMinutes - occupiedMinutes);

        return {
            hasAvailable: remainingMinutes > 0,
            totalFreeMinutes: remainingMinutes,
            label: this.formatRemainingTime(remainingMinutes)
        };
    },

    formatRemainingTime(minutes) {
        if (minutes <= 0) return 'Fully booked';

        const hours = Math.floor(minutes / 60);
        const mins = minutes % 60;

        if (hours && mins) return `${hours}h ${mins}m left`;
        if (hours) return `${hours}h left`;
        return `${mins}m left`;
    },

    get filteredSpaces() {
        return (this.spaces || []).filter((space) => {
            const availability = this.getVenueAvailability(space);
            return availability.hasAvailable;
        }).map((space) => ({
            ...space,
            availability: this.getVenueAvailability(space)
        }));
    }
}">

    <x-header />

    <!-- Hero Banner with Filter -->
    <section class="relative z-30 py-14 px-6 sm:px-12 bg-[#1C3627] text-white">
        <div class="max-w-5xl mx-auto text-center space-y-4">
            <p class="text-[10px] uppercase tracking-[0.3em] text-[#B89462] font-semibold">RESERVATIONS & BOOKING</p>
            <h1 class="text-3xl sm:text-5xl font-serif">Inquire Venue Spaces</h1>
            <p class="text-xs sm:text-sm font-light text-gray-300 leading-relaxed max-w-2xl mx-auto">
                Select <strong>"View Details & Calendar"</strong> to inspect space amenities, photos, and monthly
                schedules.
            </p>

            <!-- RESTORED HEADER DROPDOWN FILTER -->
            <div class="pt-6 max-w-3xl mx-auto relative z-30">
                <div
                    class="bg-white/10 backdrop-blur-md border border-white/20 rounded-md p-4 flex flex-col sm:flex-row items-center justify-between gap-4 text-left">
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <div
                            class="w-10 h-10 rounded-full bg-[#B89462]/20 flex items-center justify-center text-[#B89462]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-[9px] uppercase tracking-wider text-gray-300 block">Filtered Availability
                                Window</span>
                            <span class="text-xs sm:text-sm font-semibold text-white"
                                x-text="formattedFilterLabel"></span>
                        </div>
                    </div>

                    <div class="relative w-full sm:w-auto">
                        <button @click="dateFilterOpen = !dateFilterOpen"
                            class="w-full sm:w-auto bg-[#B89462] text-white px-5 py-2.5 rounded-sm text-xs font-semibold uppercase tracking-wider hover:bg-[#a38153] transition-all flex items-center justify-center gap-2">
                            <span>Adjust Filter</span>
                            <svg class="w-4 h-4 transition-transform" :class="dateFilterOpen ? 'rotate-180' : ''"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="dateFilterOpen" @click.away="dateFilterOpen = false" x-cloak
                            class="absolute right-0 top-full mt-3 w-full sm:w-80 bg-white text-gray-800 rounded-sm shadow-2xl border border-gray-200 p-4 z-[100] space-y-3">
                            <h4 class="text-xs font-semibold uppercase tracking-wider text-[#1C3627] border-b pb-2">
                                Filter Availability</h4>

                            <div class="rounded-2xl border border-[#E5DDD0] bg-[#F7F4EE] p-3">
                                <div class="flex items-center justify-between mb-3">
                                    <button type="button"
                                        @click="if (modalMonth === 0) { modalMonth = 11; modalYear--; } else { modalMonth--; }"
                                        class="w-8 h-8 rounded-full border border-[#E5DDD0] bg-white flex items-center justify-center text-[#1C3627] hover:bg-[#EAF0EB] transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 19l-7-7 7-7" />
                                        </svg>
                                    </button>
                                    <span class="text-xs font-bold text-[#1C3627] uppercase tracking-[0.12em]"
                                        x-text="monthNames[modalMonth].substring(0,3) + ' ' + modalYear"></span>
                                    <button type="button"
                                        @click="if (modalMonth === 11) { modalMonth = 0; modalYear++; } else { modalMonth++; }"
                                        class="w-8 h-8 rounded-full border border-[#E5DDD0] bg-white flex items-center justify-center text-[#1C3627] hover:bg-[#EAF0EB] transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
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
                                                <button type="button"
                                                    @click="syncDateSelection(item.dateStr); dateFilterOpen = false"
                                                    :disabled="item.isPast"
                                                    :class="item.dateStr ? (selectedCalendarDate === item.dateStr && !item
                                                            .isPast ?
                                                            'bg-[#1C3627] text-white ring-2 ring-[#1C3627]/30' :
                                                            item.isPast ?
                                                            'bg-gray-100 text-gray-400 cursor-not-allowed' :
                                                            'bg-white text-[#1C3627] hover:bg-[#EAF0EB]') :
                                                        'bg-transparent text-gray-300 cursor-not-allowed'"
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
                </div>
            </div>
        </div>
    </section>

    <!-- Venue Catalog Grid -->
    <section class="relative z-10 py-12 px-4 sm:px-8 lg:px-16 max-w-7xl mx-auto space-y-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <template x-for="space in filteredSpaces" :key="space.id">
                <div
                    class="bg-white border border-gray-200/80 rounded-sm shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between overflow-hidden group">
                    <div>
                        <div class="relative h-56 overflow-hidden">
                            <img :src="space.image" :alt="space.title"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <span
                                class="absolute top-3 left-3 bg-[#1C3627] text-white text-[9px] uppercase tracking-wider font-semibold px-2.5 py-1 rounded-full"
                                x-text="space.type"></span>
                        </div>
                        <div class="p-6 space-y-3">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="font-serif text-xl font-semibold text-[#3b4d3c]" x-text="space.title"></h3>
                                <span class="text-sm font-bold text-[#B89462]" x-text="space.price"></span>
                            </div>
                            <p class="text-xs text-gray-600 font-light leading-relaxed line-clamp-2"
                                x-text="space.description"></p>
                            <div class="flex items-center justify-between gap-2 border-t border-[#E5DDD0] pt-2">
                                <span
                                    class="text-[10px] font-semibold uppercase tracking-[0.12em] text-[#6B7E73]">Availability</span>
                                <span class="text-[10px] font-bold text-[#B89462]"
                                    x-text="space.availability.label"></span>
                            </div>
                        </div>
                    </div>
                    <div class="p-6 pt-0">
                        <button @click="openDetailModal(space)"
                            class="w-full bg-[#1C3627] text-white hover:bg-[#2a4d38] py-3 rounded-full text-xs font-semibold tracking-wider uppercase transition-all shadow-sm flex items-center justify-center gap-2">
                            <span>VIEW DETAILS & CALENDAR</span>
                            <span>&rarr;</span>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </section>

    <!-- VIEW DETAILS MODAL -->
    <div x-show="detailModalOpen" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto bg-black/80 backdrop-blur-md flex items-center justify-center p-3 sm:p-6">
        <div @click.away="detailModalOpen = false"
            class="relative bg-white rounded-sm max-w-5xl w-full overflow-hidden shadow-2xl space-y-0 my-auto">
            <div class="p-4 sm:p-5 bg-[#1C3627] text-white flex items-center justify-between">
                <div>
                    <span
                        class="text-[9px] uppercase tracking-[0.2em] text-[#B89462] font-semibold block">SPECIFICATIONS
                        & AVAILABILITY</span>
                    <h3 class="font-serif text-xl sm:text-2xl font-medium" x-text="activeSpace?.title"></h3>
                </div>
                <button @click="detailModalOpen = false"
                    class="text-white hover:text-[#B89462] text-2xl font-bold leading-none">&times;</button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 max-h-[80vh] overflow-y-auto">
                <!-- Left: Showcase Photos & Amenities -->
                <div class="lg:col-span-7 p-5 sm:p-6 space-y-6 border-b lg:border-b-0 lg:border-r border-gray-200">
                    <div class="space-y-2">
                        <p class="text-[10px] uppercase tracking-wider text-gray-400 font-semibold">Showcase Photos</p>
                        <div
                            class="relative h-64 sm:h-72 bg-[#F3EFEA] rounded-sm overflow-hidden border border-[#E5DDD0]">
                            <template x-if="activeSpace">
                                <div class="relative w-full h-full">
                                    <template x-for="(img, idx) in activeSpace.gallery" :key="idx">
                                        <img x-show="activeImageIndex === idx"
                                            x-transition:enter="transition ease-out duration-400"
                                            x-transition:enter-start="opacity-0 scale-105"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-200"
                                            x-transition:leave-start="opacity-100 scale-100"
                                            x-transition:leave-end="opacity-0 scale-95" :src="img"
                                            class="absolute inset-0 w-full h-full object-cover">
                                    </template>
                                </div>
                            </template>
                        </div>
                        <div class="flex items-center gap-2 overflow-x-auto pt-1 pb-1">
                            <template x-if="activeSpace">
                                <template x-for="(img, idx) in activeSpace.gallery" :key="idx">
                                    <button @click="activeImageIndex = idx"
                                        :class="activeImageIndex === idx ? 'ring-2 ring-[#1C3627] opacity-100' :
                                            'opacity-60 hover:opacity-90'"
                                        class="w-16 h-12 rounded-sm overflow-hidden shrink-0 border border-[#E5DDD0] bg-white transition-all duration-200">
                                        <img :src="img" class="w-full h-full object-cover">
                                    </button>
                                </template>
                            </template>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-[#1C3627]" x-text="activeSpace?.capacity"></span>
                            <span class="text-sm font-bold text-[#B89462]" x-text="activeSpace?.price"></span>
                        </div>
                        <p class="text-xs text-gray-600 font-light leading-relaxed" x-text="activeSpace?.description">
                        </p>
                    </div>

                    <div class="space-y-3 pt-4 border-t border-gray-100">
                        <h4 class="text-[11px] uppercase tracking-wider font-semibold text-[#1C3627]">Included
                            Amenities</h4>
                        <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-gray-700">
                            <template x-if="activeSpace">
                                <template x-for="feature in activeSpace.inclusions" :key="feature">
                                    <li
                                        class="flex items-center gap-2 bg-[#FBF9F5] p-2 rounded border border-gray-100">
                                        <span class="text-[#B89462] font-bold">✓</span>
                                        <span x-text="feature"></span>
                                    </li>
                                </template>
                            </template>
                        </ul>
                    </div>
                </div>

                <!-- Right: Room Calendar & Time Slot Inspector -->
                <div class="lg:col-span-5 p-5 sm:p-6 bg-[#FBF9F5] flex flex-col justify-between space-y-6">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between border-b pb-3">
                            <h4 class="text-xs font-semibold uppercase text-[#1C3627]">Select Date to View Schedule
                            </h4>
                            <div class="flex items-center space-x-1">
                                <!-- Previous Month Button -->
                                <button
                                    @click="if(modalMonth === 0){ modalMonth=11; modalYear--; }else{ modalMonth--; }"
                                    class="w-6 h-6 rounded border bg-white flex items-center justify-center text-xs">&larr;</button>

                                <!-- Month and Year Display -->
                                <span class="text-xs font-semibold px-1 text-gray-700"
                                    x-text="monthNames[modalMonth].substring(0,3) + ' ' + modalYear"></span>

                                <!-- Next Month Button (Fixed) -->
                                <button
                                    @click="if(modalMonth === 11){ modalMonth=0; modalYear++; }else{ modalMonth++; }"
                                    class="w-6 h-6 rounded border bg-white flex items-center justify-center text-xs">&rarr;</button>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-[#E5DDD0] bg-[#F7F4EE] p-4">
                            <div class="flex items-center justify-between mb-3">
                                <button type="button"
                                    @click="if (modalMonth === 0) { modalMonth = 11; modalYear--; } else { modalMonth--; }"
                                    class="w-9 h-9 rounded-full border border-[#E5DDD0] bg-white flex items-center justify-center text-[#1C3627] hover:bg-[#EAF0EB] transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>
                                <span class="text-sm font-bold text-[#1C3627] uppercase tracking-[0.12em]"
                                    x-text="monthNames[modalMonth].substring(0,3) + ' ' + modalYear"></span>
                                <button type="button"
                                    @click="if (modalMonth === 11) { modalMonth = 0; modalYear++; } else { modalMonth++; }"
                                    class="w-9 h-9 rounded-full border border-[#E5DDD0] bg-white flex items-center justify-center text-[#1C3627] hover:bg-[#EAF0EB] transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>

                            <div
                                class="grid grid-cols-7 gap-2 text-center text-[10px] font-bold uppercase tracking-[0.12em] text-[#6B7E73] mb-2">
                                <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
                            </div>

                            <div class="grid grid-cols-7 gap-2">
                                <template x-for="(item, idx) in modalCalendarDays" :key="idx">
                                    <div>
                                        <template x-if="item.isCurrentMonth">
                                            <button type="button"
                                                @click="if (!item.isPast) selectedCalendarDate = item.dateStr"
                                                :disabled="item.isPast"
                                                :class="item.dateStr ? (selectedCalendarDate === item.dateStr && !item.isPast ?
                                                        'bg-[#1C3627] text-white ring-2 ring-[#1C3627]/30' :
                                                        item.isBooked && !item.isPast ?
                                                        'bg-[#FCE9E9] text-[#7A2B2B] hover:bg-[#F7DADA]' :
                                                        'bg-white text-[#1C3627] hover:bg-[#EAF0EB]') :
                                                    'bg-transparent text-gray-300 cursor-not-allowed'"
                                                class="relative w-full h-14 rounded-xl border border-[#E5DDD0] text-xs font-semibold transition-all">
                                                <span x-text="item.day || ''" class="block"></span>
                                                <template x-if="item.isBooked && !item.isPast">
                                                    <span
                                                        class="absolute bottom-1 right-1 text-[9px] rounded-full bg-[#B89462] text-white px-1.5 py-0.5"
                                                        x-text="item.bookings.length"></span>
                                                </template>
                                            </button>
                                        </template>
                                        <template x-if="!item.isCurrentMonth">
                                            <div
                                                class="w-full h-14 rounded-xl border border-transparent bg-gray-50/50">
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Reserved Time Slots List -->
                        <div class="bg-white p-3.5 rounded border border-gray-200 space-y-2">
                            <div class="flex items-center justify-between border-b pb-1.5">
                                <span class="text-[10px] font-bold uppercase text-[#1C3627]">Schedule for:</span>
                                <span class="text-xs font-semibold text-[#B89462]"
                                    x-text="selectedCalendarDate"></span>
                            </div>
                            <div class="space-y-1.5 pt-1 max-h-36 overflow-y-auto">
                                <template x-if="activeSelectedDateBookings.length > 0">
                                    <template x-for="(b, i) in activeSelectedDateBookings" :key="i">
                                        <div
                                            class="bg-red-50 border border-red-200 text-red-800 p-2 rounded text-xs flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-red-500 shrink-0"></span>
                                            <span x-text="b.label"></span>
                                        </div>
                                    </template>
                                </template>
                                <template x-if="activeSelectedDateBookings.length === 0">
                                    <div
                                        class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-2 rounded text-xs text-center font-medium">
                                        ✓ Fully Available on this Date
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- PROCEED TO DEDICATED BOOKING PAGE BUTTON -->
                    <div class="pt-3 border-t border-gray-200">
                        <button @click="proceedToNewPage()"
                            class="w-full bg-[#1C3627] text-white py-3.5 rounded-full text-xs font-semibold tracking-wider uppercase hover:bg-[#2a4d38] transition-all shadow-md flex items-center justify-center gap-2">
                            <span>PROCEED WITH THIS ROOM</span>
                            <span>&rarr;</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
