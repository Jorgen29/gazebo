<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Gazebo - Events Place</title>

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

    <!-- 1. Hero Section -->
    <section id="home"
        class="relative min-h-[85vh] w-full flex items-center justify-between bg-cover bg-center text-white px-8 sm:px-16 lg:px-24 py-20"
        style="background-image: linear-gradient(to right, rgba(0,0,0,0.65) 0%, rgba(0,0,0,0.3) 50%, rgba(0,0,0,0.4) 100%), url('https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=1920&q=80');">
        <!-- Left Content -->
        <div class="max-w-xl space-y-6">
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-serif leading-tight">
                A Beautiful Place<br>
                for Your Special<br>
                <span
                    class="font-cursive text-6xl sm:text-7xl lg:text-8xl font-normal lowercase block mt-1 -mb-3 text-white">Moments</span>
            </h1>
            <p class="text-[10px] sm:text-xs uppercase tracking-[0.35em] font-medium text-gray-200">
                EVENTS &bull; CELEBRATIONS &bull; MEMORIES
            </p>
            <div class="pt-2">
                <a href="{{ route('inquire') }}"
                    class="inline-flex items-center gap-2 bg-[#48634F]/90 backdrop-blur-md border border-white/20 text-white px-7 py-3 rounded-full text-xs uppercase tracking-widest font-medium hover:bg-[#3b4d3c] transition-all duration-300">
                    INQUIRE NOW &rarr;
                </a>
            </div>
        </div>

        <!-- Bottom Right Tagline -->
        <div class="hidden sm:block absolute bottom-12 right-8 sm:right-16 text-right space-y-1">
            <p class="font-cursive text-4xl sm:text-5xl text-white">More</p>
            <p class="font-cursive text-3xl sm:text-4xl text-white -mt-2">Than a Venue</p>
            <p class="text-[9px] uppercase tracking-[0.3em] text-gray-300 pt-1">
                A PLACE WHERE<br>YOUR STORIES HAPPEN
            </p>
        </div>
    </section>

    <!-- 2. Occasions Section -->
    <section id="events" class="py-20 px-4 sm:px-6 lg:px-8 bg-[#F5F2EB] border-b border-gray-200/60">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <h2 class="text-3xl sm:text-4xl font-serif text-[#3b4d3c] tracking-wide">Perfect for Any Occasion</h2>
            <p class="text-[9px] sm:text-[10px] uppercase tracking-[0.3em] text-gray-500 mt-2 font-semibold">
                A VERSATILE VENUE FOR ALL OF LIFE'S CELEBRATIONS
            </p>
        </div>

        <div
            class="max-w-7xl mx-auto w-full grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 divide-x divide-y sm:divide-y-0 divide-gray-300/70 border-t border-b border-gray-300/70 py-6">
            @php
                $occasions = [
                    [
                        'title' => 'WEDDINGS',
                        'icon' =>
                            'M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z',
                    ],
                    [
                        'title' => 'BIRTHDAYS',
                        'icon' =>
                            'M20 6h-3V4c0-1.11-.89-2-2-2h-6c-1.11 0-2 .89-2 2v2H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zM9 4h6v2H9V4z',
                    ],
                    ['title' => 'DEBUTS', 'icon' => 'M12 2L1 21h22L12 2zm0 3.99L19.53 19H4.47L12 5.99z'],
                    [
                        'title' => "CORPORATE\nEVENTS",
                        'icon' =>
                            'M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10z',
                    ],
                    [
                        'title' => 'ANNIVERSARIES',
                        'icon' =>
                            'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z',
                    ],
                    [
                        'title' => 'CHRISTENINGS',
                        'icon' =>
                            'M12 2c1.1 0 2 .9 2 2s-.9 2-2 2-2-.9-2-2 .9-2 2-2zm9 7h-6v13h-2v-6h-2v6H9V9H3V7h18v2z',
                    ],
                    [
                        'title' => "PRIVATE\nPARTIES",
                        'icon' =>
                            'M21 5H3c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h18c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm-1 12H4V7h16v10z',
                    ],
                    ['title' => 'AND MORE', 'icon' => 'M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z'],
                ];
            @endphp

            @foreach ($occasions as $item)
                <div
                    class="flex flex-col items-center justify-center p-4 text-center group hover:bg-white/40 transition-colors">
                    <div class="w-10 h-10 mb-3 text-[#B89A62] flex items-center justify-center">
                        <svg class="w-7 h-7 fill-current stroke-current" stroke-width="0.5" viewBox="0 0 24 24">
                            <path d="{{ $item['icon'] }}" />
                        </svg>
                    </div>
                    <h3
                        class="text-[10px] tracking-[0.2em] font-semibold text-gray-700 leading-tight whitespace-pre-line">
                        {{ $item['title'] }}</h3>
                </div>
            @endforeach
        </div>
    </section>

    <!-- 3. Spaces Showcase Grid -->
    <section id="spaces" class="py-20 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 w-full">
            @php
                $spaces = [
                    [
                        'title' => 'GROTTO',
                        'subtitle' => "A PEACEFUL SPACE FOR REFLECTION\nAND SPECIAL MOMENTS",
                        'image' =>
                            'https://images.unsplash.com/photo-1545232979-fbf59202c396?auto=format&fit=crop&w=600&q=80',
                    ],
                    [
                        'title' => 'ELEGANT HALLS',
                        'subtitle' => 'A VERSATILE SPACE FOR ANY EVENT',
                        'image' =>
                            'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=600&q=80',
                    ],
                    [
                        'title' => 'BEAUTIFUL SURROUNDINGS',
                        'subtitle' => "A RELAXING ATMOSPHERE FOR\nUNFORGETTABLE MEMORIES",
                        'image' =>
                            'https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?auto=format&fit=crop&w=600&q=80',
                    ],
                    [
                        'title' => 'FLEXIBLE SPACES',
                        'subtitle' => "ADAPTABLE TO BRING YOUR\nVISION TO LIFE",
                        'image' =>
                            'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=600&q=80',
                    ],
                ];
            @endphp

            @foreach ($spaces as $space)
                <div
                    class="group flex flex-col bg-white overflow-hidden shadow-sm border border-gray-200/80 rounded-sm">
                    <div class="overflow-hidden h-64 sm:h-72">
                        <img src="{{ $space['image'] }}" alt="{{ $space['title'] }}"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    </div>
                    <div class="p-5 text-center flex-1 flex flex-col justify-center bg-[#FBF9F5]">
                        <h4 class="font-serif text-base tracking-[0.15em] text-[#3b4d3c] font-semibold uppercase">
                            {{ $space['title'] }}</h4>
                        <p
                            class="text-[8.5px] tracking-[0.2em] text-gray-500 mt-1 font-medium leading-relaxed whitespace-pre-line">
                            {{ $space['subtitle'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- 4. Split CTA Section -->
    <section id="inquire" class="grid grid-cols-1 lg:grid-cols-2">
        <!-- Left Green Banner (Centered Content) -->
        <div
            class="bg-[#1C3627] text-white p-10 sm:p-16 flex flex-col justify-center items-center text-center relative overflow-hidden min-h-[420px]">
            <!-- Leaf Graphic Overlay -->
            <svg class="absolute -left-10 top-1/2 -translate-y-1/2 w-80 h-80 text-[#254633] opacity-50 pointer-events-none"
                viewBox="0 0 24 24" fill="currentColor">
                <path
                    d="M17.5 2.5c-2.5 0-5.5 1.5-7.5 4.5-2 3-2 6.5-1.5 8.5L2 22l2-2.5 6.5-6.5c2 .5 5.5.5 8.5-1.5 3-2 4.5-5 4.5-7.5-.5-1-1.5-1.5-2.5-1.5H17.5zM16 11c-1 1.5-2.5 2-4 2 1-1 2-2.5 3-4s2.5-2 4-2c-1 1-2 2.5-3 4z" />
            </svg>

            <div class="relative z-10 max-w-md space-y-5 flex flex-col items-center">
                <p class="text-[10px] uppercase tracking-[0.3em] text-gray-300 font-semibold">YOUR EVENT. OUR PLACE.</p>
                <h2 class="text-4xl sm:text-5xl font-serif leading-tight">Let's Make It<br>Happen</h2>
                <p class="text-xs font-light text-gray-300 leading-relaxed">
                    Inquire now and schedule a visit to see The Gazebo.<br>
                    We'd be happy to assist you!
                </p>
                <div class="pt-3">
                    <a href="{{ route('inquire') }}"
                        class="inline-flex items-center gap-2 bg-[#B89462] text-white px-8 py-3 rounded-full text-xs font-semibold tracking-widest uppercase hover:bg-[#a38153] transition-all shadow-md">
                        INQUIRE NOW &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Right Image Banner -->
        <div class="relative min-h-[350px] lg:min-h-full bg-cover bg-center flex items-center justify-center p-8"
            style="background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=1200&q=80');">
            <div
                class="flex flex-col items-center justify-center text-center text-white space-y-2 border border-white/20 p-8 backdrop-blur-xs bg-black/20 rounded-sm">
                <svg class="w-10 h-10 text-white" viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M17.5 2.5c-2.5 0-5.5 1.5-7.5 4.5-2 3-2 6.5-1.5 8.5L2 22l2-2.5 6.5-6.5c2 .5 5.5.5 8.5-1.5 3-2 4.5-5 4.5-7.5-.5-1-1.5-1.5-2.5-1.5H17.5zM16 11c-1 1.5-2.5 2-4 2 1-1 2-2.5 3-4s2.5-2 4-2c-1 1-2 2.5-3 4z" />
                </svg>
                <span class="font-serif text-3xl tracking-[0.18em] uppercase leading-none font-medium">THE GAZEBO</span>
                <div class="flex items-center gap-2">
                    <span class="w-5 h-[1px] bg-white/70"></span>
                    <span class="text-[9px] tracking-[0.2em] uppercase font-medium">EVENTS PLACE</span>
                    <span class="w-5 h-[1px] bg-white/70"></span>
                </div>
                <span class="text-[7.5px] tracking-[0.25em] uppercase font-semibold text-gray-200">CELEBRATE &middot;
                    GATHER &middot; BELONG</span>
            </div>
        </div>
    </section>

    <!-- 5. Footer -->
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
