<header x-data="{ open: false }"
    class="sticky top-0 z-50 w-full bg-[#fdfdfc] border-b border-gray-200 text-gray-800 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20 md:h-24">

            <!-- Brand Logo / Name -->
            <a href="/" class="flex items-center space-x-2 sm:space-x-3">
                <!-- Leaf Icon Graphic -->
                <svg class="w-8 h-8 sm:w-10 sm:h-10 text-[#3b4d3c] shrink-0" viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M17.5 2.5c-2.5 0-5.5 1.5-7.5 4.5-2 3-2 6.5-1.5 8.5L2 22l2-2.5 6.5-6.5c2 .5 5.5.5 8.5-1.5 3-2 4.5-5 4.5-7.5-.5-1-1.5-1.5-2.5-1.5H17.5zM16 11c-1 1.5-2.5 2-4 2 1-1 2-2.5 3-4s2.5-2 4-2c-1 1-2 2.5-3 4z" />
                </svg>
                <div class="flex flex-col items-center justify-center">
                    <span
                        class="font-serif text-2xl sm:text-3xl tracking-[0.15em] text-[#3b4d3c] uppercase leading-none font-medium">
                        The Gazebo
                    </span>
                    <div class="flex items-center gap-1.5 sm:gap-2 mt-1">
                        <span class="w-4 sm:w-6 h-[1px] bg-[#3b4d3c]"></span>
                        <span class="text-[8px] sm:text-[9px] tracking-[0.2em] text-[#3b4d3c] uppercase font-medium">
                            Events Place
                        </span>
                        <span class="w-4 sm:w-6 h-[1px] bg-[#3b4d3c]"></span>
                    </div>
                    <span
                        class="text-[6.5px] sm:text-[7px] tracking-[0.25em] text-gray-500 uppercase mt-0.5 font-semibold">
                        Celebrate &middot; Gather &middot; Belong
                    </span>
                </div>
            </a>

            <!-- Desktop Navigation Links -->
            <nav class="hidden lg:flex items-center space-x-6 xl:space-x-8">
                <a href="{{ request()->is('/') ? '#home' : '/#home' }}"
                    class="text-xs xl:text-sm font-medium transition-colors pb-1 border-b-[1.5px] {{ request()->is('/') ? 'text-[#3b4d3c] border-[#3b4d3c]' : 'text-gray-700 border-transparent hover:text-[#3b4d3c] hover:border-[#3b4d3c]' }}">
                    Home
                </a>
                <a href="{{ route('about') }}"
                    class="text-xs xl:text-sm font-medium transition-colors pb-1 border-b-[1.5px] {{ request()->routeIs('about') ? 'text-[#3b4d3c] border-[#3b4d3c]' : 'text-gray-700 border-transparent hover:text-[#3b4d3c] hover:border-[#3b4d3c]' }}">
                    About
                </a>
                <a href="{{ route('inquire') }}"
                    class="text-xs xl:text-sm font-medium transition-colors pb-1 border-b-[1.5px] {{ request()->routeIs('inquire') ? 'text-[#3b4d3c] border-[#3b4d3c]' : 'text-gray-700 border-transparent hover:text-[#3b4d3c] hover:border-[#3b4d3c]' }}">
                    Our Spaces
                </a>
                <a href="{{ route('events') }}"
                    class="text-xs xl:text-sm font-medium text-gray-700 hover:text-[#3b4d3c] transition-colors pb-1 border-b-[1.5px] border-transparent hover:border-[#3b4d3c]">
                    Events
                </a>
                <a href="{{ route('gallery') }}"
                    class="text-xs xl:text-sm font-medium text-gray-700 hover:text-[#3b4d3c] transition-colors pb-1 border-b-[1.5px] border-transparent hover:border-[#3b4d3c]">
                    Gallery
                </a>
                <a href="{{ route('contact') }}"
                    class="text-xs xl:text-sm font-medium text-gray-700 hover:text-[#3b4d3c] transition-colors pb-1 border-b-[1.5px] border-transparent hover:border-[#3b4d3c]">
                    Contact
                </a>
            </nav>

            <!-- Right Action Area & Mobile Hamburger Button -->
            <div class="flex items-center space-x-3 sm:space-x-4">
                <!-- Desktop CTA -->
                <a href="{{ route('inquire') }}"
                    class="hidden sm:inline-block bg-[#3b4d3c] text-white px-5 sm:px-7 py-2.5 rounded-full text-xs font-semibold tracking-widest uppercase hover:bg-[#2d3b2e] transition-all shadow-sm">
                    Inquire Now
                </a>

                <!-- Mobile Menu Button -->
                <button @click="open = !open" type="button"
                    class="lg:hidden p-2 rounded-md text-gray-700 hover:text-[#3b4d3c] hover:bg-gray-100 focus:outline-none"
                    aria-label="Toggle Menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" style="display: none;" />
                    </svg>
                </button>
            </div>

        </div>
    </div>

    <!-- Mobile Dropdown Navigation -->
    <div x-show="open" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="lg:hidden bg-[#fdfdfc] border-t border-gray-100 px-4 pt-3 pb-6 space-y-3 shadow-lg"
        style="display: none;">

        <a @click="open = false" href="{{ request()->is('/') ? '#home' : '/#home' }}"
            class="block px-3 py-2 rounded-md text-base font-semibold {{ request()->is('/') ? 'text-[#3b4d3c] bg-gray-50' : 'text-gray-700 hover:text-[#3b4d3c] hover:bg-gray-50' }} transition-colors">
            Home
        </a>
        <a @click="open = false" href="{{ route('about') }}"
            class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('about') ? 'text-[#3b4d3c] bg-gray-50 font-semibold' : 'text-gray-700 hover:text-[#3b4d3c] hover:bg-gray-50' }} transition-colors">
            About
        </a>
        <a @click="open = false" href="{{ route('inquire') }}"
            class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('inquire') ? 'text-[#3b4d3c] bg-gray-50 font-semibold' : 'text-gray-700 hover:text-[#3b4d3c] hover:bg-gray-50' }} transition-colors">
            Inquire
        </a>
        <a @click="open = false" href="{{ route('events') }}"
            class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-[#3b4d3c] hover:bg-gray-50 transition-colors">
            Events
        </a>
        <a @click="open = false" href="{{ route('gallery') }}"
            class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-[#3b4d3c] hover:bg-gray-50 transition-colors">
            Gallery
        </a>
        <a @click="open = false" href="{{ route('contact') }}"
            class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-[#3b4d3c] hover:bg-gray-50 transition-colors">
            Contact
        </a>

        <!-- Mobile CTA Button -->
        <div class="pt-2">
            <a @click="open = false" href="{{ route('inquire') }}#inquire"
                class="block w-full text-center bg-[#3b4d3c] text-white py-3 rounded-full text-xs font-semibold tracking-widest uppercase hover:bg-[#2d3b2e] transition-all">
                Inquire Now
            </a>
        </div>
    </div>
</header>
