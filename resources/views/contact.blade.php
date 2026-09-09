<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact & Inquiries - The Gazebo Events Place</title>

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
    formSubmitted: false,
    formData: {
        name: '',
        email: '',
        phone: '',
        event_type: 'wedding',
        event_date: '',
        guests: '',
        message: ''
    },
    submitForm() {
        // Add your backend submission logic here
        this.formSubmitted = true;
    }
}">

    <!-- Header Component -->
    <x-header />

    <!-- Hero Banner -->
    <section class="relative py-20 px-6 sm:px-12 text-center bg-[#1C3627] text-white">
        <div class="max-w-3xl mx-auto space-y-4">
            <p class="text-[10px] uppercase tracking-[0.3em] text-[#B89462] font-semibold">GET IN TOUCH</p>
            <h1 class="text-3xl sm:text-5xl font-serif">Contact & Venue Inquiries</h1>
            <p class="text-xs sm:text-sm font-light text-gray-300 leading-relaxed max-w-2xl mx-auto">
                Whether you wish to schedule a personal venue tour, check date availability, or request tailored pricing
                packages, our team is here to assist you.
            </p>
        </div>
    </section>

    <!-- Contact & Map Section -->
    <section class="py-12 sm:py-16 px-4 sm:px-8 lg:px-16 max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">

            <!-- Left Info & Map Column (5 Cols) -->
            <div class="lg:col-span-5 space-y-8">
                <div>
                    <p class="text-[10px] uppercase tracking-[0.3em] text-[#B89462] font-semibold mb-1">REACH OUT TO US
                    </p>
                    <h2 class="text-2xl sm:text-3xl font-serif text-[#3b4d3c]">We’d Love to Hear From You</h2>
                    <p class="text-xs text-gray-600 font-light mt-2 leading-relaxed">
                        Our events team responds to inquiries within 24 hours. Visit us during office hours or call us
                        directly.
                    </p>
                </div>

                <!-- Info Cards -->
                <div class="space-y-4">
                    <div class="bg-white p-5 rounded-sm border border-gray-200/80 shadow-sm flex items-start gap-4">
                        <div class="p-3 bg-[#F8F6F0] rounded-full text-[#B89462] shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xs uppercase tracking-wider font-semibold text-[#1C3627]">Our Location</h4>
                            <p class="text-xs text-gray-600 font-light mt-1">123 Celebration Way, Garden District, Metro
                                City</p>
                        </div>
                    </div>

                    <div class="bg-white p-5 rounded-sm border border-gray-200/80 shadow-sm flex items-start gap-4">
                        <div class="p-3 bg-[#F8F6F0] rounded-full text-[#B89462] shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xs uppercase tracking-wider font-semibold text-[#1C3627]">Phone & Mobile
                            </h4>
                            <p class="text-xs text-gray-600 font-light mt-1">+63 (02) 8123-4567 / +63 917 123 4567</p>
                        </div>
                    </div>

                    <div class="bg-white p-5 rounded-sm border border-gray-200/80 shadow-sm flex items-start gap-4">
                        <div class="p-3 bg-[#F8F6F0] rounded-full text-[#B89462] shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xs uppercase tracking-wider font-semibold text-[#1C3627]">Email & Inquiries
                            </h4>
                            <p class="text-xs text-gray-600 font-light mt-1">events@thegazeboplace.com</p>
                        </div>
                    </div>

                    <div class="bg-white p-5 rounded-sm border border-gray-200/80 shadow-sm flex items-start gap-4">
                        <div class="p-3 bg-[#F8F6F0] rounded-full text-[#B89462] shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xs uppercase tracking-wider font-semibold text-[#1C3627]">Office Hours</h4>
                            <p class="text-xs text-gray-600 font-light mt-1">Tuesday – Sunday: 9:00 AM – 6:00
                                PM<br>(Monday: By Appointment Only)</p>
                        </div>
                    </div>
                </div>

                <!-- Embedded Map Embed Container -->
                <div class="bg-white p-2 rounded-sm border border-gray-200/80 shadow-sm overflow-hidden">
                    <iframe class="w-full h-64 rounded-sm border-0"
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3861.802094892476!2d121.0425!3d14.5547!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTTCsDMzJ1E3LjIiTiAxMjHCsDAyJzMzLjAiRQ!5e0!3m2!1sen!2sph!4v1620000000000!5m2!1sen!2sph"
                        allowfullscreen="" loading="lazy">
                    </iframe>
                </div>
            </div>

            <!-- Right Inquiry Form Column (7 Cols) -->
            <div class="lg:col-span-7">
                <div class="bg-white border border-gray-200/80 rounded-sm shadow-md p-6 sm:p-10">
                    <div class="mb-8 space-y-1">
                        <p class="text-[10px] uppercase tracking-[0.3em] text-[#B89462] font-semibold">INQUIRY FORM</p>
                        <h3 class="text-2xl font-serif text-[#3b4d3c]">Send Us a Message</h3>
                        <p class="text-xs text-gray-500 font-light">Fill out the details below and our team will get
                            back to you with custom packages and availability.</p>
                    </div>

                    <!-- Success Message Box -->
                    <div x-show="formSubmitted" x-cloak
                        class="p-6 bg-[#F8F6F0] border border-[#B89462]/40 rounded-sm mb-6 space-y-2">
                        <div class="flex items-center gap-2 text-[#1C3627] font-serif text-lg font-medium">
                            <svg class="w-5 h-5 text-[#B89462]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            Thank You for Reaching Out!
                        </div>
                        <p class="text-xs text-gray-600 font-light leading-relaxed">
                            Your inquiry has been received. One of our event coordinators will contact you shortly to
                            confirm your requested date and answer any questions.
                        </p>
                    </div>

                    <!-- Form -->
                    <form @submit.prevent="submitForm()" class="space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-gray-700 mb-1.5">Full
                                    Name *</label>
                                <input type="text" x-model="formData.name" required placeholder="e.g. Maria Santos"
                                    class="w-full text-xs px-4 py-3 rounded-sm border border-gray-200 focus:outline-none focus:border-[#1C3627] focus:ring-1 focus:ring-[#1C3627] bg-[#FBF9F5]/50">
                            </div>

                            <div>
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-gray-700 mb-1.5">Email
                                    Address *</label>
                                <input type="email" x-model="formData.email" required
                                    placeholder="e.g. maria@example.com"
                                    class="w-full text-xs px-4 py-3 rounded-sm border border-gray-200 focus:outline-none focus:border-[#1C3627] focus:ring-1 focus:ring-[#1C3627] bg-[#FBF9F5]/50">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-gray-700 mb-1.5">Phone
                                    / Mobile Number *</label>
                                <input type="tel" x-model="formData.phone" required
                                    placeholder="e.g. +63 917 000 0000"
                                    class="w-full text-xs px-4 py-3 rounded-sm border border-gray-200 focus:outline-none focus:border-[#1C3627] focus:ring-1 focus:ring-[#1C3627] bg-[#FBF9F5]/50">
                            </div>

                            <div>
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-gray-700 mb-1.5">Event
                                    Type *</label>
                                <select x-model="formData.event_type"
                                    class="w-full text-xs px-4 py-3 rounded-sm border border-gray-200 focus:outline-none focus:border-[#1C3627] focus:ring-1 focus:ring-[#1C3627] bg-[#FBF9F5]/50">
                                    <option value="wedding">Wedding Reception</option>
                                    <option value="grotto_vows">Grotto Ceremony</option>
                                    <option value="debut">18th Debut</option>
                                    <option value="birthday">Birthday Party</option>
                                    <option value="corporate">Corporate Event</option>
                                    <option value="other">Other Social Gathering</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-gray-700 mb-1.5">Target
                                    Event Date</label>
                                <input type="date" x-model="formData.event_date"
                                    class="w-full text-xs px-4 py-3 rounded-sm border border-gray-200 focus:outline-none focus:border-[#1C3627] focus:ring-1 focus:ring-[#1C3627] bg-[#FBF9F5]/50 text-gray-600">
                            </div>

                            <div>
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-gray-700 mb-1.5">Estimated
                                    Guest Count</label>
                                <input type="number" x-model="formData.guests" placeholder="e.g. 150"
                                    class="w-full text-xs px-4 py-3 rounded-sm border border-gray-200 focus:outline-none focus:border-[#1C3627] focus:ring-1 focus:ring-[#1C3627] bg-[#FBF9F5]/50">
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-[11px] font-semibold uppercase tracking-wider text-gray-700 mb-1.5">Message
                                / Special Requests</label>
                            <textarea x-model="formData.message" rows="4"
                                placeholder="Tell us about your event preferences, styling requests, or any specific questions..."
                                class="w-full text-xs px-4 py-3 rounded-sm border border-gray-200 focus:outline-none focus:border-[#1C3627] focus:ring-1 focus:ring-[#1C3627] bg-[#FBF9F5]/50"></textarea>
                        </div>

                        <div class="pt-2">
                            <button type="submit"
                                class="w-full bg-[#1C3627] text-white py-3.5 rounded-full text-xs font-semibold tracking-widest uppercase hover:bg-[#2a4d38] transition-all shadow-md">
                                SUBMIT INQUIRY
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="bg-[#162B20] text-[#E5DDD0] py-8 px-6 sm:px-12 border-t border-[#3B5A45]/30">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <h3 class="font-serif text-lg tracking-[0.18em] uppercase text-white font-medium leading-none">THE
                    GAZEBO</h3>
                <p class="text-[8px] tracking-[0.22em] text-gray-400 uppercase mt-1">EVENTS PLACE</p>
            </div>
            <p class="text-[9px] tracking-[0.25em] text-gray-300 uppercase text-center sm:text-left">
                A BEAUTIFUL PLACE FOR EVERY CELEBRATION
            </p>
            <div class="flex items-center space-x-4 text-gray-300">
                <a href="#" class="hover:text-white transition-colors">
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                        <path
                            d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H7.5v-3H10V9.5C10 7.01 11.49 5.6 13.78 5.6c1.1 0 2.25.2 2.25.2v2.47h-1.27c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.45 3h-2.33v6.8c4.56-.93 8-4.96 8-9.8z" />
                    </svg>
                </a>
                <a href="#" class="hover:text-white transition-colors">
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                        <path
                            d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                    </svg>
                </a>
            </div>
        </div>
    </footer>

</body>

</html>
