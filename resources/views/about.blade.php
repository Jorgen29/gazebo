<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - The Gazebo Events Place</title>

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
    </style>
</head>

<body class="bg-[#FBF9F5] text-gray-800 antialiased font-sans">

    <!-- Header Component -->
    <x-header />

    <!-- 1. Hero Header Banner -->
    <section
        class="relative min-h-[50vh] w-full flex items-center justify-center bg-cover bg-center text-white px-6 py-16"
        style="background-image: linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.65)), url('https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=1920&q=80');">
        <div class="max-w-3xl text-center space-y-4">
            <p class="text-[10px] sm:text-xs uppercase tracking-[0.35em] font-medium text-gray-200">
                OUR STORY &bull; OUR HEAVENLY SPACES
            </p>
            <h1 class="text-4xl sm:text-6xl font-serif leading-tight">
                About The Gazebo
            </h1>
            <p class="font-cursive text-4xl sm:text-5xl text-[#B89462] -mt-2">
                Where stories find their home
            </p>
        </div>
    </section>

    <!-- 2. Story / Introduction Section -->
    <section class="py-20 px-6 sm:px-12 lg:px-24 max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">

            <!-- Left Image Stack -->
            <div class="relative">
                <div class="overflow-hidden rounded-sm shadow-md border border-gray-200">
                    <img src="https://images.unsplash.com/photo-1545232979-fbf59202c396?auto=format&fit=crop&w=1000&q=80"
                        alt="The Gazebo Grounds" class="w-full h-[420px] object-cover">
                </div>
                <!-- Badge Overlay -->
                <div
                    class="absolute -bottom-6 -right-6 hidden sm:flex flex-col items-center justify-center bg-[#1C3627] text-white p-6 shadow-xl border border-[#3b4d3c] rounded-sm max-w-[200px] text-center">
                    <span class="font-serif text-2xl text-[#B89462]">Excellence</span>
                    <span class="text-[8px] uppercase tracking-[0.2em] text-gray-300 mt-1">In Every Detail</span>
                </div>
            </div>

            <!-- Right Story Content -->
            <div class="space-y-6">
                <div>
                    <p class="text-[10px] uppercase tracking-[0.3em] text-[#B89462] font-semibold">WELCOME TO OUR VENUE
                    </p>
                    <h2 class="text-3xl sm:text-4xl font-serif text-[#3b4d3c] mt-2 leading-tight">Crafted for
                        Celebrations, Built for Memories</h2>
                </div>

                <p class="text-sm font-light leading-relaxed text-gray-600">
                    Nestled amidst lush greenery and serene landscapes, <strong class="font-semibold text-gray-800">The
                        Gazebo Events Place</strong> was envisioned as a sanctuary where life’s most meaningful
                    milestones could be celebrated in elegance and peace.
                </p>

                <p class="text-sm font-light leading-relaxed text-gray-600">
                    Whether you are exchanging vows at sunset, celebrating an 18th debut, hosting an intimate family
                    reunion, or staging a grand corporate gathering, our versatile indoor and outdoor spaces provide the
                    ideal backdrop tailored precisely to your vision.
                </p>

                <!-- Core Pillars -->
                <div class="grid grid-cols-3 gap-4 pt-4 border-t border-gray-200">
                    <div class="text-center sm:text-left">
                        <span class="font-serif text-2xl text-[#3b4d3c]">Serene</span>
                        <p class="text-[9px] uppercase tracking-wider text-gray-500 mt-1">Lush Atmosphere</p>
                    </div>
                    <div class="text-center sm:text-left">
                        <span class="font-serif text-2xl text-[#3b4d3c]">Versatile</span>
                        <p class="text-[9px] uppercase tracking-wider text-gray-500 mt-1">Indoor & Outdoor</p>
                    </div>
                    <div class="text-center sm:text-left">
                        <span class="font-serif text-2xl text-[#3b4d3c]">Memorable</span>
                        <p class="text-[9px] uppercase tracking-wider text-gray-500 mt-1">Unmatched Comfort</p>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- 3. Mission & Vision Grid -->
    <section class="bg-[#F5F2EB] py-20 px-6 sm:px-12 lg:px-24 border-y border-gray-200/60">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8">

            <!-- Mission -->
            <div
                class="bg-white p-8 sm:p-12 border border-gray-200/80 shadow-sm flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div
                        class="w-10 h-10 text-[#B89462] flex items-center justify-center bg-[#FBF9F5] rounded-full border border-gray-200">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                            <path
                                d="M12 2L2 7l10 5 10-5-10-5zm0 9L4 7.2v4.8l8 4 8-4V7.2L12 11zm0 5L4 12.2v4.8l8 4 8-4v-4.8L12 16z" />
                        </svg>
                    </div>
                    <h3 class="font-serif text-2xl text-[#3b4d3c]">Our Mission</h3>
                    <p class="text-xs sm:text-sm font-light text-gray-600 leading-relaxed">
                        To provide an extraordinary venue experience through seamless hospitality, scenic spaces, and
                        meticulous attention to detail—ensuring every guest leaves with cherished lifelong memories.
                    </p>
                </div>
            </div>

            <!-- Vision -->
            <div
                class="bg-white p-8 sm:p-12 border border-gray-200/80 shadow-sm flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div
                        class="w-10 h-10 text-[#B89462] flex items-center justify-center bg-[#FBF9F5] rounded-full border border-gray-200">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                            <path
                                d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" />
                        </svg>
                    </div>
                    <h3 class="font-serif text-2xl text-[#3b4d3c]">Our Vision</h3>
                    <p class="text-xs sm:text-sm font-light text-gray-600 leading-relaxed">
                        To be recognized as a premier events venue known for architectural elegance, natural
                        tranquility, and heartfelt service that turns life's standard gatherings into unforgettable
                        occasions.
                    </p>
                </div>
            </div>

        </div>
    </section>

    <!-- 4. Why Choose Us Section -->
    <section class="py-20 px-6 sm:px-12 lg:px-24 max-w-7xl mx-auto">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <p class="text-[10px] uppercase tracking-[0.3em] text-[#B89462] font-semibold">THE GAZEBO EXPERIENCE</p>
            <h2 class="text-3xl sm:text-4xl font-serif text-[#3b4d3c] mt-2">Why Celebrate With Us?</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
            <div class="p-8 bg-white border border-gray-200/80 rounded-sm shadow-sm space-y-3">
                <div class="text-3xl font-serif text-[#B89462]">01</div>
                <h4 class="font-serif text-lg text-[#3b4d3c] uppercase tracking-wide">Tranquil Surroundings</h4>
                <p class="text-xs font-light text-gray-600 leading-relaxed">
                    Escape the busyness of the city and immerse your guests in a peaceful environment framed by natural
                    greenery and soothing architecture.
                </p>
            </div>

            <div class="p-8 bg-white border border-gray-200/80 rounded-sm shadow-sm space-y-3">
                <div class="text-3xl font-serif text-[#B89462]">02</div>
                <h4 class="font-serif text-lg text-[#3b4d3c] uppercase tracking-wide">Tailored Flexibility</h4>
                <p class="text-xs font-light text-gray-600 leading-relaxed">
                    Our indoor halls and outdoor gardens can be configured to suit intimate dinners or expansive
                    multi-themed receptions.
                </p>
            </div>

            <div class="p-8 bg-white border border-gray-200/80 rounded-sm shadow-sm space-y-3">
                <div class="text-3xl font-serif text-[#B89462]">03</div>
                <h4 class="font-serif text-lg text-[#3b4d3c] uppercase tracking-wide">Dedicated Support</h4>
                <p class="text-xs font-light text-gray-600 leading-relaxed">
                    Our team works alongside your chosen event coordinators to ensure smooth preparation and flawless
                    event execution.
                </p>
            </div>
        </div>
    </section>

    <!-- 5. Centered Call to Action Banner -->
    <section class="bg-[#1C3627] text-white py-16 px-6 text-center relative overflow-hidden">
        <div class="relative z-10 max-w-2xl mx-auto space-y-5">
            <p class="text-[10px] uppercase tracking-[0.3em] text-[#B89462] font-semibold">PLAN YOUR EVENT WITH US</p>
            <h2 class="text-3xl sm:text-5xl font-serif">Ready to Create Unforgettable Memories?</h2>
            <p class="text-xs font-light text-gray-300 leading-relaxed">
                Schedule a site visit today or reach out to our team to discuss availability and package details.
            </p>
            <div class="pt-3">
                <a href="/#contact"
                    class="inline-flex items-center gap-2 bg-[#B89462] text-white px-8 py-3 rounded-full text-xs font-semibold tracking-widest uppercase hover:bg-[#a38153] transition-all shadow-md">
                    INQUIRE NOW &rarr;
                </a>
            </div>
        </div>
    </section>

    <!-- 6. Footer -->
    <footer id="contact" class="bg-[#162B20] text-[#E5DDD0] py-8 px-6 sm:px-12 border-t border-[#3B5A45]/30">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
            <!-- Left Title -->
            <div>
                <h3 class="font-serif text-lg tracking-[0.18em] uppercase text-white font-medium leading-none">THE
                    GAZEBO</h3>
                <p class="text-[8px] tracking-[0.22em] text-gray-400 uppercase mt-1">EVENTS PLACE</p>
            </div>

            <!-- Center Tagline -->
            <p class="text-[9px] tracking-[0.25em] text-gray-300 uppercase text-center sm:text-left">
                A BEAUTIFUL PLACE FOR EVERY CELEBRATION
            </p>

            <!-- Right Social Icons -->
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
                <a href="#" class="hover:text-white transition-colors">
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                        <path
                            d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" />
                    </svg>
                </a>
            </div>
        </div>
    </footer>

</body>

</html>
