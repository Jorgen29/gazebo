<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rules & Policies - The Gazebo Events Place</title>

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

<body class="bg-[#FBF9F5] text-gray-800 antialiased font-sans">

    <!-- Header Component -->
    <x-header />

    <!-- Hero Banner -->
    <section class="relative py-20 px-6 sm:px-12 text-center bg-[#1C3627] text-white">
        <div class="max-w-3xl mx-auto space-y-4">
            <p class="text-[10px] uppercase tracking-[0.3em] text-[#B89462] font-semibold">GUIDELINES</p>
            <h1 class="text-3xl sm:text-5xl font-serif">Venue Rules & Policies</h1>
            <p class="text-xs sm:text-sm font-light text-gray-300 leading-relaxed max-w-2xl mx-auto">
                Please review our standard guidelines, booking conditions, and event policies to ensure a seamless and
                wonderful celebration.
            </p>
        </div>
    </section>

    <!-- Main Content Section -->
    <section class="py-12 sm:py-16 px-4 sm:px-8 lg:px-16 max-w-5xl mx-auto">
        <div class="bg-white border border-gray-200/80 rounded-sm shadow-md p-6 sm:p-12 space-y-10">

            <div class="border-b border-gray-100 pb-6">
                <p class="text-[10px] uppercase tracking-[0.3em] text-[#B89462] font-semibold mb-1">TERMS & CONDITIONS
                </p>
                <h2 class="text-2xl font-serif text-[#3b4d3c]">General Event Policies</h2>
                <p class="text-xs text-gray-500 font-light mt-1">Ensuring a pleasant environment for every guest and
                    successful execution of your event.</p>
            </div>

            <!-- Policies List -->
            <div class="space-y-8">
                <div class="space-y-2">
                    <h3 class="text-base font-serif text-[#1C3627] font-semibold flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#B89462]"></span>
                        1. Reservation Deposit & Confirmation
                    </h3>
                    <p class="text-xs text-gray-600 font-light leading-relaxed pl-3.5">
                        A non-refundable reservation deposit is required to secure your preferred date and venue space.
                        Dates are marked as officially confirmed only after payment proof has been verified and
                        acknowledged by our management team.
                    </p>
                </div>

                <div class="space-y-2">
                    <h3 class="text-base font-serif text-[#1C3627] font-semibold flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#B89462]"></span>
                        2. Payment Submission & Reference Code
                    </h3>
                    <p class="text-xs text-gray-600 font-light leading-relaxed pl-3.5">
                        When submitting GCash or bank transfer payments, please reply directly to your payment
                        instruction email or ensure your screenshot references your unique booking code (e.g.,
                        <strong>Booking Request - R{id}</strong>) so our automated system can process it correctly.
                    </p>
                </div>

                <div class="space-y-2">
                    <h3 class="text-base font-serif text-[#1C3627] font-semibold flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#B89462]"></span>
                        3. Setup, Teardown & Schedule Compliance
                    </h3>
                    <p class="text-xs text-gray-600 font-light leading-relaxed pl-3.5">
                        Suppliers, coordinators, and guests are granted entry to the venue strictly during the agreed
                        schedule specified in your contract. Overtime fees apply for unauthorized extended usage before
                        or after the event.
                    </p>
                </div>

                <div class="space-y-2">
                    <h3 class="text-base font-serif text-[#1C3627] font-semibold flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#B89462]"></span>
                        4. Venue Cleanliness & Prohibited Items
                    </h3>
                    <p class="text-xs text-gray-600 font-light leading-relaxed pl-3.5">
                        To maintain our grounds and safety standards, hazardous materials, open fireworks, and confetti
                        cannons inside enclosed halls are strictly prohibited. Any resulting property damage will incur
                        maintenance charges.
                    </p>
                </div>
            </div>

        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-[#162B20] text-[#E5DDD0] py-8 px-6 sm:px-12 border-t border-[#3B5A45]/30">
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
