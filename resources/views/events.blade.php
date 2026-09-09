<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Celebrations & Events - The Gazebo Events Place</title>

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
    activeFilter: 'all',
    modalOpen: false,
    activeGallery: [],
    activeEventTitle: '',
    activeFaq: null,
    events: [{
            id: 'weddings',
            category: 'weddings',
            categoryName: 'Weddings',
            title: 'Timeless Wedding Receptions',
            tagline: 'Where Forever Begins in Style',
            description: 'From romantic garden vows to elegant grand ballroom receptions, we provide exquisite settings tailored to your love story.',
            cover: 'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=1200&q=80',
            gallery: [
                'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?auto=format&fit=crop&w=1200&q=80'
            ],
            highlights: ['Bridal Suite Included', 'Custom Table & Seating Setup', 'Full AV & Ambient Lighting', 'Dedicated Event Coordinator']
        },
        {
            id: 'debuts',
            category: 'debuts',
            categoryName: 'Debuts & Birthdays',
            title: 'Unforgettable 18th Debuts',
            tagline: 'Step into Royalty for Your 18th Birthday',
            description: 'Celebrate this momentous milestone with grand entrance walkways, customized dance floors, and dramatic stage lighting configurations.',
            cover: 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=1200&q=80',
            gallery: [
                'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1530103862676-de8c9debad1d?auto=format&fit=crop&w=1200&q=80'
            ],
            highlights: ['18 Roses & Candles Layout', 'State-of-the-Art Sound', 'Photo Corner Setup Areas', 'Flexible Stage Arrangements']
        },
        {
            id: 'corporate',
            category: 'corporate',
            categoryName: 'Corporate',
            title: 'Corporate Galas & Conferences',
            tagline: 'Professional Environments for Impactful Events',
            description: 'Host product launches, annual banquets, seminars, and awarding ceremonies in a sleek, climate-controlled venue equipped with modern AV tech.',
            cover: 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1200&q=80',
            gallery: [
                'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1475721027785-f74eccf877e2?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1540575861501-7cf05a4b125a?auto=format&fit=crop&w=1200&q=80'
            ],
            highlights: ['High-Speed Wi-Fi', 'HD Projector & LED Display Ready', 'VIP Lounge & Holding Area', 'Ample On-Site Parking']
        },
        {
            id: 'social',
            category: 'social',
            categoryName: 'Social Gatherings',
            title: 'Anniversaries & Private Parties',
            tagline: 'Celebrate Milestones with Family and Friends',
            description: 'Whether an intimate dinner or a lively anniversary party, our open courtyard and elegant indoor halls fit your preferred gathering style.',
            cover: 'https://images.unsplash.com/photo-1530103862676-de8c9debad1d?auto=format&fit=crop&w=1200&q=80',
            gallery: [
                'https://images.unsplash.com/photo-1530103862676-de8c9debad1d?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1527529482837-4698179dc6ce?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?auto=format&fit=crop&w=1200&q=80'
            ],
            highlights: ['Indoor & Outdoor Options', 'Cocktail Lounge Layouts', 'Custom Lighting Effects', 'Catering Prep Kitchen Access']
        }
    ],
    openModal(eventItem) {
        this.activeGallery = eventItem.gallery;
        this.activeEventTitle = eventItem.title;
        this.modalOpen = true;
    }
}">

    <!-- Header Component -->
    <x-header />

    <!-- Hero Section -->
    <section class="relative py-20 px-6 sm:px-12 text-center bg-[#1C3627] text-white">
        <div class="max-w-3xl mx-auto space-y-4">
            <p class="text-[10px] uppercase tracking-[0.3em] text-[#B89462] font-semibold">THE GAZEBO EVENTS</p>
            <h1 class="text-3xl sm:text-5xl font-serif">Unforgettable Moments, Crafted Perfectly</h1>
            <p class="text-xs sm:text-sm font-light text-gray-300 leading-relaxed max-w-2xl mx-auto">
                Explore the variety of celebrations hosted at The Gazebo. From dream weddings and grand debuts to
                corporate galas, we bring every vision to life.
            </p>
        </div>
    </section>

    <!-- Event Filter & Display Section -->
    <section class="py-12 sm:py-16 px-4 sm:px-8 lg:px-16 max-w-7xl mx-auto">
        <!-- Category Filter Buttons -->
        <div class="flex items-center justify-center flex-wrap gap-2 sm:gap-4 mb-12">
            <button @click="activeFilter = 'all'"
                :class="activeFilter === 'all' ? 'bg-[#1C3627] text-white border-[#1C3627]' :
                    'bg-white text-gray-700 border-gray-200 hover:border-[#1C3627]'"
                class="px-5 py-2 rounded-full text-xs font-semibold tracking-wider uppercase border transition-all shadow-sm">
                All Celebrations
            </button>
            <button @click="activeFilter = 'weddings'"
                :class="activeFilter === 'weddings' ? 'bg-[#1C3627] text-white border-[#1C3627]' :
                    'bg-white text-gray-700 border-gray-200 hover:border-[#1C3627]'"
                class="px-5 py-2 rounded-full text-xs font-semibold tracking-wider uppercase border transition-all shadow-sm">
                Weddings
            </button>
            <button @click="activeFilter = 'debuts'"
                :class="activeFilter === 'debuts' ? 'bg-[#1C3627] text-white border-[#1C3627]' :
                    'bg-white text-gray-700 border-gray-200 hover:border-[#1C3627]'"
                class="px-5 py-2 rounded-full text-xs font-semibold tracking-wider uppercase border transition-all shadow-sm">
                Debuts & Birthdays
            </button>
            <button @click="activeFilter = 'corporate'"
                :class="activeFilter === 'corporate' ? 'bg-[#1C3627] text-white border-[#1C3627]' :
                    'bg-white text-gray-700 border-gray-200 hover:border-[#1C3627]'"
                class="px-5 py-2 rounded-full text-xs font-semibold tracking-wider uppercase border transition-all shadow-sm">
                Corporate
            </button>
            <button @click="activeFilter = 'social'"
                :class="activeFilter === 'social' ? 'bg-[#1C3627] text-white border-[#1C3627]' :
                    'bg-white text-gray-700 border-gray-200 hover:border-[#1C3627]'"
                class="px-5 py-2 rounded-full text-xs font-semibold tracking-wider uppercase border transition-all shadow-sm">
                Social Parties
            </button>
        </div>

        <!-- Events List Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <template x-for="item in events" :key="item.id">
                <div x-show="activeFilter === 'all' || activeFilter === item.category"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="bg-white border border-gray-200/80 rounded-sm shadow-md overflow-hidden flex flex-col justify-between">

                    <div>
                        <!-- Cover Image -->
                        <div class="relative group cursor-pointer overflow-hidden bg-black h-64"
                            @click="openModal(item)">
                            <img :src="item.cover" :alt="item.title"
                                class="w-full h-full object-cover group-hover:scale-105 group-hover:opacity-90 transition-all duration-700">

                            <div class="absolute top-4 left-4">
                                <span
                                    class="text-[9px] uppercase tracking-widest bg-[#1C3627] text-white px-3 py-1 rounded-full font-semibold shadow-md"
                                    x-text="item.categoryName"></span>
                            </div>

                            <!-- Hover Overlay -->
                            <div
                                class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <span
                                    class="bg-[#1C3627] text-white text-xs uppercase tracking-widest px-5 py-2.5 rounded-full border border-white/20 shadow-lg flex items-center gap-2">
                                    <svg class="w-4 h-4 text-[#B89462]" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    View Event Gallery
                                </span>
                            </div>
                        </div>

                        <!-- Details -->
                        <div class="p-6 sm:p-8 space-y-4">
                            <div>
                                <h3 class="text-2xl font-serif text-[#3b4d3c]" x-text="item.title"></h3>
                                <p class="font-cursive text-xl text-[#B89462]" x-text="item.tagline"></p>
                            </div>

                            <p class="text-xs text-gray-600 font-light leading-relaxed" x-text="item.description"></p>

                            <!-- Highlights -->
                            <div class="pt-3 border-t border-gray-100 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <template x-for="highlight in item.highlights" :key="highlight">
                                    <div class="flex items-center space-x-2 text-xs text-gray-700">
                                        <svg class="w-3.5 h-3.5 text-[#B89462] shrink-0" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span class="font-medium text-[10px] uppercase tracking-wider text-gray-600"
                                            x-text="highlight"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Card Actions -->
                    <div class="p-6 sm:p-8 pt-0 flex gap-3">
                        <button @click="openModal(item)" type="button"
                            class="w-1/2 bg-[#1C3627] text-white py-2.5 rounded-full text-xs font-semibold tracking-widest uppercase hover:bg-[#2a4d38] transition-all shadow-sm text-center">
                            View Photos
                        </button>
                        <a href="/#inquire"
                            class="w-1/2 border border-[#3b4d3c] text-[#3b4d3c] py-2.5 rounded-full text-xs font-semibold tracking-widest uppercase hover:bg-[#3b4d3c] hover:text-white transition-all text-center">
                            Inquire Now
                        </a>
                    </div>

                </div>
            </template>
        </div>
    </section>

    <!-- How Planning Works Step-by-Step Section -->
    <section class="py-16 bg-[#F8F6F0] border-y border-gray-200/60 px-6 sm:px-12">
        <div class="max-w-7xl mx-auto space-y-12">
            <div class="text-center max-w-2xl mx-auto space-y-2">
                <p class="text-[10px] uppercase tracking-[0.3em] text-[#B89462] font-semibold">SIMPLE & SEAMLESS</p>
                <h2 class="text-3xl font-serif text-[#3b4d3c]">How to Book Your Event</h2>
                <p class="text-xs text-gray-500 font-light">We make planning your celebration effortless with four
                    simple steps.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-sm border border-gray-200/80 shadow-sm space-y-3 relative">
                    <span class="text-3xl font-serif text-[#B89462] font-bold">01</span>
                    <h4 class="font-serif text-lg text-[#3b4d3c]">Schedule a Tour</h4>
                    <p class="text-xs text-gray-600 font-light leading-relaxed">Book a guided site visit to view our
                        halls, grotto garden, and suites in person.</p>
                </div>
                <div class="bg-white p-6 rounded-sm border border-gray-200/80 shadow-sm space-y-3 relative">
                    <span class="text-3xl font-serif text-[#B89462] font-bold">02</span>
                    <h4 class="font-serif text-lg text-[#3b4d3c]">Reserve Your Date</h4>
                    <p class="text-xs text-gray-600 font-light leading-relaxed">Select your preferred date, layout
                        options, and secure your event slot with a deposit.</p>
                </div>
                <div class="bg-white p-6 rounded-sm border border-gray-200/80 shadow-sm space-y-3 relative">
                    <span class="text-3xl font-serif text-[#B89462] font-bold">03</span>
                    <h4 class="font-serif text-lg text-[#3b4d3c]">Customize Details</h4>
                    <p class="text-xs text-gray-600 font-light leading-relaxed">Work with our team and your chosen
                        suppliers to finalize stage setup and schedules.</p>
                </div>
                <div class="bg-white p-6 rounded-sm border border-gray-200/80 shadow-sm space-y-3 relative">
                    <span class="text-3xl font-serif text-[#B89462] font-bold">04</span>
                    <h4 class="font-serif text-lg text-[#3b4d3c]">Celebrate & Enjoy</h4>
                    <p class="text-xs text-gray-600 font-light leading-relaxed">Relax and create unforgettable memories
                        while our event staff handles venue operations.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQs Accordion Section -->
    <section class="py-16 px-6 sm:px-12 max-w-4xl mx-auto space-y-8">
        <div class="text-center space-y-2">
            <p class="text-[10px] uppercase tracking-[0.3em] text-[#B89462] font-semibold">FREQUENTLY ASKED QUESTIONS
            </p>
            <h2 class="text-3xl font-serif text-[#3b4d3c]">Event Planning FAQs</h2>
        </div>

        <div class="space-y-3">
            <div class="border border-gray-200 rounded-sm bg-white overflow-hidden">
                <button @click="activeFaq = activeFaq === 1 ? null : 1"
                    class="w-full p-4 text-left font-medium text-sm text-[#3b4d3c] flex justify-between items-center">
                    <span>What is the maximum capacity for events?</span>
                    <span class="text-xl" x-text="activeFaq === 1 ? '−' : '+'"></span>
                </button>
                <div x-show="activeFaq === 1" x-cloak
                    class="p-4 pt-0 text-xs text-gray-600 font-light border-t border-gray-100 leading-relaxed">
                    Our Elegant Hall can accommodate up to 300 guests for banquet seating. The Courtyard accommodates up
                    to 200 guests, and the Grotto Garden is perfect for intimate gatherings up to 100 guests.
                </div>
            </div>

            <div class="border border-gray-200 rounded-sm bg-white overflow-hidden">
                <button @click="activeFaq = activeFaq === 2 ? null : 2"
                    class="w-full p-4 text-left font-medium text-sm text-[#3b4d3c] flex justify-between items-center">
                    <span>Do you allow outside caterers and stylists?</span>
                    <span class="text-xl" x-text="activeFaq === 2 ? '−' : '+'"></span>
                </button>
                <div x-show="activeFaq === 2" x-cloak
                    class="p-4 pt-0 text-xs text-gray-600 font-light border-t border-gray-100 leading-relaxed">
                    Yes! We welcome accredited third-party caterers, event stylists, and coordinators. We also have
                    partner vendor packages available for an all-in seamless experience.
                </div>
            </div>

            <div class="border border-gray-200 rounded-sm bg-white overflow-hidden">
                <button @click="activeFaq = activeFaq === 3 ? null : 3"
                    class="w-full p-4 text-left font-medium text-sm text-[#3b4d3c] flex justify-between items-center">
                    <span>How many hours are included in venue rentals?</span>
                    <span class="text-xl" x-text="activeFaq === 3 ? '−' : '+'"></span>
                </button>
                <div x-show="activeFaq === 3" x-cloak
                    class="p-4 pt-0 text-xs text-gray-600 font-light border-t border-gray-100 leading-relaxed">
                    Standard venue packages include 5 hours of event time plus complimentary ingress (setup) and egress
                    (ingress cleanup) time slots. Additional hours can be requested.
                </div>
            </div>
        </div>
    </section>

    <!-- Clean White Image Modal Lightbox -->
    <div x-show="modalOpen" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-sm flex items-center justify-center p-4 sm:p-8"
        @keydown.escape.window="modalOpen = false">

        <div
            class="relative w-full max-w-5xl bg-white text-gray-800 rounded-lg shadow-2xl overflow-hidden p-6 sm:p-8 space-y-6 border border-gray-100">
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                <div>
                    <h3 class="font-serif text-2xl text-[#3b4d3c]" x-text="activeEventTitle"></h3>
                    <p class="text-[10px] uppercase tracking-widest text-[#B89462] font-medium">Event Gallery Photos
                    </p>
                </div>
                <button @click="modalOpen = false"
                    class="text-gray-400 hover:text-gray-700 text-3xl font-light transition-colors">&times;</button>
            </div>

            <!-- Modal Gallery Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 max-h-[60vh] overflow-y-auto pr-2">
                <template x-for="(img, i) in activeGallery" :key="i">
                    <div class="overflow-hidden rounded-md border border-gray-200 group bg-gray-50">
                        <img :src="img"
                            class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-500">
                    </div>
                </template>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end pt-2 border-t border-gray-100">
                <button @click="modalOpen = false"
                    class="bg-[#1C3627] text-white px-6 py-2 rounded-full text-xs font-semibold uppercase tracking-wider hover:bg-[#2a4d38] transition-all shadow-sm">
                    Close Gallery
                </button>
            </div>
        </div>
    </div>

    <!-- Centered Call to Action Banner -->
    <section class="bg-[#1C3627] text-white py-16 px-6 text-center relative overflow-hidden">
        <div class="relative z-10 max-w-2xl mx-auto space-y-5">
            <p class="text-[10px] uppercase tracking-[0.3em] text-[#B89462] font-semibold">PLAN YOUR CELEBRATION</p>
            <h2 class="text-3xl sm:text-5xl font-serif">Let’s Make Your Event Extraordinary</h2>
            <p class="text-xs font-light text-gray-300 leading-relaxed">
                Contact our events team today to inquire about available dates, customized packages, and venue tours.
            </p>
            <div class="pt-3">
                <a href="/#inquire"
                    class="inline-flex items-center gap-2 bg-[#B89462] text-white px-8 py-3 rounded-full text-xs font-semibold tracking-widest uppercase hover:bg-[#a38153] transition-all shadow-md">
                    INQUIRE ABOUT DATES &rarr;
                </a>
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
