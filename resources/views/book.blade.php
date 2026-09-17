<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book {{ $space['title'] }} - The Gazebo Events Place</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Alex+Brush&family=Cormorant+Garamond:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    space: @js($space),
    selectedDate: '{{ $selectedDate }}',
    allBookings: @js($allBookings),
    reservedSchedules: @js($reservedSchedules),
    todayStr: new Date().toISOString().split('T')[0],

    selectedStartTime: '15:00',
    selectedEndTime: '18:00',
    activeImageIndex: 0,
    bookingSubmitted: false,

    modalMonth: new Date().getMonth(),
    modalYear: new Date().getFullYear(),
    showCalendar: false,
    monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],

    allTimeSlots: [
        { value: '08:00', label: '8:00 AM' },
        { value: '09:00', label: '9:00 AM' },
        { value: '10:00', label: '10:00 AM' },
        { value: '11:00', label: '11:00 AM' },
        { value: '12:00', label: '12:00 PM' },
        { value: '13:00', label: '1:00 PM' },
        { value: '14:00', label: '2:00 PM' },
        { value: '15:00', label: '3:00 PM' },
        { value: '16:00', label: '4:00 PM' },
        { value: '17:00', label: '5:00 PM' },
        { value: '18:00', label: '6:00 PM' },
        { value: '19:00', label: '7:00 PM' },
        { value: '20:00', label: '8:00 PM' },
        { value: '21:00', label: '9:00 PM' },
        { value: '22:00', label: '10:00 PM' }
    ],

    get dateBookings() {
        let list = this.reservedSchedules[this.space.id] || [];
        return list.filter(b => b.date === this.selectedDate);
    },

    toMinutes(timeStr) {
        let parts = timeStr.split(':');
        return parseInt(parts[0]) * 60 + parseInt(parts[1]);
    },

    // Check if start time conflicts with bookings (including 1-hr post-booking buffer)
    getStartStatus(timeVal) {
        let reqMin = this.toMinutes(timeVal);

        for (let b of this.dateBookings) {
            let bStart = this.toMinutes(b.start);
            let bEnd = this.toMinutes(b.end);
            let bEndWithBuffer = bEnd + 60; // 1-hour prep time

            // Inside reserved booking bounds
            if (reqMin >= bStart && reqMin < bEnd) {
                return { allowed: false, labelSuffix: ' ─ Scheduled' };
            }
            // Inside 1-hour post-booking prep time
            if (reqMin >= bEnd && reqMin < bEndWithBuffer) {
                return { allowed: false, labelSuffix: ' ─ Prep Time' };
            }
        }
        return { allowed: true, labelSuffix: '' };
    },

    get availableStartOptions() {
        return this.allTimeSlots.map(t => {
            let status = this.getStartStatus(t.value);
            return {
                value: t.value,
                label: t.label + status.labelSuffix,
                allowed: status.allowed
            };
        });
    },

    // Dynamically compute valid End Times based on chosen Start Time
    get availableEndOptions() {
        if (!this.selectedStartTime) return [];

        let startMin = this.toMinutes(this.selectedStartTime);

        // Find the closest upcoming booking start time on the same date
        let nextBookingStartMin = 24 * 60; // default midnight
        for (let b of this.dateBookings) {
            let bStart = this.toMinutes(b.start);
            // 1-hour buffer needed before next booking start
            let maxAllowedEndMin = bStart - 60;

            if (bStart > startMin && maxAllowedEndMin < nextBookingStartMin) {
                nextBookingStartMin = maxAllowedEndMin;
            }
        }

        return this.allTimeSlots.filter(t => {
            let endMin = this.toMinutes(t.value);
            // End time must be strictly after start time and must not breach upcoming booking buffer
            return endMin > startMin && endMin <= nextBookingStartMin;
        });
    },

    get calendarDays() {
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
            let bookings = (this.reservedSchedules[this.space.id] || []).filter(b => b.date === dateStr);

            days.push({ day: i, dateStr: dateStr, isCurrentMonth: true, isPast: isPast, bookings: bookings, isBooked: bookings.length > 0 });
        }
        return days;
    },

    init() {
        this.autoSetValidTimes();

        this.$watch('selectedDate', () => {
            this.autoSetValidTimes();
        });

        this.$watch('selectedStartTime', () => {
            let validEndOptions = this.availableEndOptions;
            if (validEndOptions.length > 0) {
                let currentEndValid = validEndOptions.some(e => e.value === this.selectedEndTime);
                if (!currentEndValid) {
                    this.selectedEndTime = validEndOptions[0].value;
                }
            } else {
                this.selectedEndTime = '';
            }
        });
    },

    autoSetValidTimes() {
        let firstValidStart = this.availableStartOptions.find(o => o.allowed);
        if (firstValidStart) {
            this.selectedStartTime = firstValidStart.value;
            let validEnd = this.availableEndOptions[0];
            if (validEnd) {
                this.selectedEndTime = validEnd.value;
            }
        }
    },

    submitReservation() {
        this.bookingSubmitted = true;
    },

    toggleCalendar() {
        this.showCalendar = !this.showCalendar;
    },

    closeCalendar() {
        this.showCalendar = false;
    },

    formatCurrency(value) {
        const numericValue = Number(value || 0);
        return new Intl.NumberFormat('en-PH', {
            style: 'currency',
            currency: 'PHP',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(numericValue);
    },

    get totalHours() {
        if (!this.selectedStartTime || !this.selectedEndTime) {
            return 0;
        }

        const hours = (this.toMinutes(this.selectedEndTime) - this.toMinutes(this.selectedStartTime)) / 60;
        return hours > 0 ? Number(hours.toFixed(2)) : 0;
    },

    get totalPrice() {
        const priceText = String(this.space?.price ?? '');
        const match = priceText.match(/[\d,]+(?:\.\d+)?/);
        const hourlyRate = match ? Number(match[0].replace(/,/g, '')) : 0;
        return hourlyRate * this.totalHours;
    }
}" <x-header />

<!-- Breadcrumb & Title Header -->
<section class="bg-[#1C3627] text-white py-10 px-6 sm:px-12">
    <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('inquiry.index') }}"
                class="text-[10px] uppercase tracking-widest text-[#B89462] hover:underline flex items-center gap-1 mb-1">
                &larr; Back to Venue Catalog
            </a>
            <h1 class="font-serif text-3xl sm:text-4xl" x-text="'Book ' + space.title"></h1>
        </div>
        <div class="bg-white/10 backdrop-blur-md px-4 py-2 rounded border border-white/20 text-right">
            <span class="text-[10px] text-gray-300 block uppercase">Rate Strategy</span>
            <span class="text-sm font-bold text-[#B89462]" x-text="space.price"></span>
        </div>
    </div>
</section>

<main class="py-12 px-4 sm:px-8 lg:px-16 max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-8">

    <!-- Left Column: Media & Calendar -->
    <div class="lg:col-span-7 space-y-8">
        <div class="bg-white border border-gray-200 rounded-sm p-4 space-y-3 shadow-sm">
            <h3 class="text-xs font-bold uppercase tracking-wider text-[#1C3627]">Venue Showcase Gallery</h3>
            <div class="relative h-72 sm:h-96 bg-black rounded-sm overflow-hidden">
                <img :src="space.gallery[activeImageIndex]" class="w-full h-full object-cover">
            </div>
            <div class="flex items-center gap-2 overflow-x-auto pt-1">
                <template x-for="(img, idx) in space.gallery" :key="idx">
                    <button @click="activeImageIndex = idx"
                        :class="activeImageIndex === idx ? 'ring-2 ring-[#1C3627]' : 'opacity-60'"
                        class="w-20 h-14 rounded-sm overflow-hidden shrink-0">
                        <img :src="img" class="w-full h-full object-cover">
                    </button>
                </template>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-sm p-6 space-y-4 shadow-sm">
            <h3 class="text-xs font-bold uppercase tracking-wider text-[#1C3627]">Room Specifications & Included
                Amenities</h3>
            <p class="text-xs text-gray-600 leading-relaxed font-light" x-text="space.description"></p>
            <div class="pt-2 border-t border-gray-100">
                <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-gray-700">
                    <template x-for="feature in space.inclusions" :key="feature">
                        <li class="flex items-center gap-2 bg-[#FBF9F5] p-2 rounded border border-gray-100">
                            <span class="text-[#B89462] font-bold">✓</span>
                            <span x-text="feature"></span>
                        </li>
                    </template>
                </ul>
            </div>
        </div>


    </div>

    <!-- Right Column: Booking Form -->
    <div class="lg:col-span-5 space-y-6">
        <div class="bg-white border border-[#B89462]/40 rounded-sm p-6 sm:p-8 shadow-xl space-y-6 sticky top-6">
            <div>
                <span class="text-[10px] uppercase tracking-widest text-[#B89462] font-semibold block">RESERVATION
                    DETAILS</span>
                <h2 class="font-serif text-2xl text-[#3b4d3c]">Select Date & Time Range</h2>
            </div>

            <!-- Schedule Breakdown -->
            <div class="bg-[#FBF9F5] p-3.5 rounded border border-gray-200 space-y-4">
                <div class="hidden">
                    <span class="font-bold text-[#1C3627] block uppercase tracking-wider mb-2">Selected Date</span>
                    <div class="rounded border border-[#E5DDD0] bg-white px-3 py-2.5">
                        <strong class="text-[#B89462]" x-text="selectedDate"></strong>
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
                        <template x-for="(item, idx) in calendarDays" :key="idx">
                            <div>
                                <template x-if="item.isCurrentMonth">
                                    <button type="button" @click="if(!item.isPast){ selectedDate = item.dateStr; }"
                                        :disabled="item.isPast"
                                        :class="item.dateStr ? (selectedDate === item.dateStr && !item.isPast ?
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
                                    <div class="w-full h-14 rounded-xl border border-transparent bg-gray-50/50">
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="rounded border border-gray-200 bg-white p-3">
                    <div class="flex items-center justify-between border-b pb-1.5 mb-2">
                        <span class="text-[10px] font-bold uppercase text-[#1C3627]">Booked Slots</span>
                        <span class="text-[10px] font-semibold text-[#B89462]"
                            x-text="dateBookings.length + ' reserved'"></span>
                    </div>

                    <template x-if="dateBookings.length > 0">
                        <div class="space-y-1.5 pt-1">
                            <template x-for="(b, i) in dateBookings" :key="i">
                                <div
                                    class="bg-red-50 border border-red-200 text-red-800 p-2 rounded text-xs flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-red-500 shrink-0"></span>
                                    <span x-text="b.label"></span>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="dateBookings.length === 0">
                        <div
                            class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-2 rounded text-xs text-center font-medium">
                            ✓ Fully Available on this Date
                        </div>
                    </template>
                </div>
            </div>

            <div x-show="bookingSubmitted" x-cloak
                class="p-4 bg-emerald-50 border border-emerald-200 rounded text-xs text-emerald-800 space-y-1">
                <strong>✓ Reservation Request Logged!</strong>
                <p>Our team will confirm your booking for <span x-text="selectedDate"></span> from <span
                        x-text="selectedStartTime"></span> to <span x-text="selectedEndTime"></span> within 24
                    hours.</p>
            </div>

            <form action="{{ route('venue.submit') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Hidden space details -->
                <input type="hidden" name="venue_id" :value="space.id">
                <input type="hidden" name="venue_title" :value="space.title">

                <input type="hidden" name="booking_date" :value="selectedDate">

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label
                            class="block text-[11px] font-semibold uppercase tracking-wider text-gray-700 mb-1">Start
                            Time *</label>
                        <select name="start_time" x-model="selectedStartTime"
                            class="w-full text-xs px-2.5 py-2.5 rounded-sm border border-gray-200 bg-[#FBF9F5]"
                            required>
                            <template x-for="opt in availableStartOptions" :key="opt.value">
                                <option :value="opt.value" :disabled="!opt.allowed" x-text="opt.label">
                                </option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-700 mb-1">End
                            Time *</label>
                        <select name="end_time" x-model="selectedEndTime"
                            class="w-full text-xs px-2.5 py-2.5 rounded-sm border border-gray-200 bg-[#FBF9F5]"
                            required>
                            <template x-for="opt in availableEndOptions" :key="opt.value">
                                <option :value="opt.value" x-text="opt.label"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-700 mb-1">Full
                        Name *</label>
                    <input type="text" name="full_name" required placeholder="e.g. Maria Santos"
                        class="w-full text-xs px-3.5 py-2.5 rounded-sm border border-gray-200 bg-[#FBF9F5]">
                </div>

                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-700 mb-1">Email
                        *</label>
                    <input type="email" name="email" required placeholder="maria@example.com"
                        class="w-full text-xs px-3.5 py-2.5 rounded-sm border border-gray-200 bg-[#FBF9F5]">
                </div>

                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-700 mb-1">Contact
                        Number *</label>
                    <input type="text" name="email_contact" required placeholder="09171234567"
                        class="w-full text-xs px-3.5 py-2.5 rounded-sm border border-gray-200 bg-[#FBF9F5]">
                </div>

                <div class="rounded border border-[#E5DDD0] bg-[#F7F4EE] p-3 flex items-center justify-between gap-3">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#1C3627]">Reservation
                        Total</span>
                    <span id="booking-total-value" class="text-base font-bold text-[#B89462]"
                        x-text="formatCurrency(totalPrice)"></span>
                </div>

                <button type="submit"
                    class="w-full bg-[#1C3627] text-white py-3.5 rounded-full text-xs font-semibold tracking-widest uppercase hover:bg-[#2a4d38] transition-all shadow-md">
                    CONFIRM & SUBMIT RESERVATION &rarr;
                </button>
            </form>
        </div>
    </div>
</main>

<footer class="bg-[#162B20] text-[#E5DDD0] py-8 text-center text-xs">
    <p>&copy; 2026 The Gazebo Events Place. All Rights Reserved.</p>
</footer>

@if (session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Reservation Submitted!',
                text: '{{ session('success') }}',
                icon: 'success',
                confirmButtonText: 'Okay',
                confirmButtonColor: '#1C3627',
                background: '#ffffff',
                color: '#1f2937',
                customClass: {
                    popup: 'rounded-sm shadow-xl'
                }
            });
        });
    </script>
@endif

@if ($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const errorMessages = @json($errors->all());
            Swal.fire({
                title: 'Please check your details',
                html: '<ul style="text-align:left; padding-left:1.2rem; margin:0;">' + errorMessages.map(
                    function(message) {
                        return '<li>' + message + '</li>';
                    }).join('') + '</ul>',
                icon: 'error',
                confirmButtonText: 'Try Again',
                confirmButtonColor: '#1C3627',
                background: '#ffffff',
                color: '#1f2937',
                customClass: {
                    popup: 'rounded-sm shadow-xl'
                }
            });
        });
    </script>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const bookingForm = document.querySelector('form[action="{{ route('venue.submit') }}"]');

        if (!bookingForm) {
            return;
        }

        bookingForm.addEventListener('submit', function(event) {
            event.preventDefault();

            const totalElement = document.getElementById('booking-total-value');
            const totalText = totalElement ? totalElement.textContent.trim() : '₱0.00';

            Swal.fire({
                title: 'Confirm reservation?',
                html: 'Please confirm your total reservation cost of <strong>' + totalText +
                    '</strong>.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, submit',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#1C3627',
                cancelButtonColor: '#d1d5db',
                customClass: {
                    popup: 'rounded-sm shadow-xl'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData(bookingForm);

                    fetch(bookingForm.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]')?.getAttribute(
                                    'content') || document.querySelector(
                                        'input[name="_token"]')?.value,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData,
                        })
                        .then(async (response) => {
                            const contentType = response.headers.get('content-type') ||
                                '';
                            const payload = contentType.includes('application/json') ?
                                await response.json() : null;

                            if (!response.ok) {
                                throw new Error(payload?.message || 'Request failed.');
                            }

                            Swal.fire({
                                title: 'Reservation Submitted!',
                                text: payload?.message ||
                                    'Your reservation request has been submitted successfully!',
                                icon: 'success',
                                confirmButtonText: 'Okay',
                                confirmButtonColor: '#1C3627',
                                background: '#ffffff',
                                color: '#1f2937',
                                customClass: {
                                    popup: 'rounded-sm shadow-xl'
                                }
                            }).then(() => {
                                bookingForm.reset();
                            });
                        })
                        .catch((error) => {
                            console.error('Booking submit error:', error);
                            Swal.fire({
                                title: 'Request failed',
                                text: error.message ||
                                    'There was a problem submitting your request.',
                                icon: 'error',
                                confirmButtonColor: '#1C3627',
                                customClass: {
                                    popup: 'rounded-sm shadow-xl'
                                }
                            });
                        });
                }
            });
        });
    });
</script>
</body>

</html>
