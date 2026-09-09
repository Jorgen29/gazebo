<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Photo Gallery - The Gazebo Events Place</title>

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
    selectedIndex: 0,
    items: [
        { id: 1, category: 'weddings', tag: 'Weddings', title: 'Garden Wedding Ceremony', caption: 'Intimate exchange of vows under the leafy grotto canopy.', url: 'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=1200&q=80' },
        { id: 2, category: 'hall', tag: 'Elegant Hall', title: 'Grand Reception Setup', caption: 'High ceiling chandeliers with glassmorphic ambient lighting.', url: 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=1200&q=80' },
        { id: 3, category: 'debuts', tag: 'Debuts', title: '18th Debut Grand Entrance', caption: 'Custom floral arch and dramatic spotlights.', url: 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=1200&q=80' },
        { id: 4, category: 'grotto', tag: 'Grotto & Garden', title: 'Evening Courtyard Lights', caption: 'Twinkling fairy light walkway across stone pathways.', url: 'https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?auto=format&fit=crop&w=1200&q=80' },
        { id: 5, category: 'weddings', tag: 'Weddings', title: 'Romantic Table Landscape', caption: 'Custom floral arrangements with gold accents and fine linen.', url: 'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?auto=format&fit=crop&w=1200&q=80' },
        { id: 6, category: 'grotto', tag: 'Grotto & Garden', title: 'Grotto Reflection Pool', caption: 'Peaceful garden sanctuary designed for quiet photo sessions.', url: 'https://images.unsplash.com/photo-1545232979-fbf59202c396?auto=format&fit=crop&w=1200&q=80' },
        { id: 7, category: 'debuts', tag: 'Debuts', title: 'Celebratory Banquet Table', caption: 'Customized debutante stage and dessert buffet setup.', url: 'https://images.unsplash.com/photo-1530103862676-de8c9debad1d?auto=format&fit=crop&w=1200&q=80' },
        { id: 8, category: 'hall', tag: 'Elegant Hall', title: 'Ballroom Night Ambiance', caption: 'Climate-controlled ballroom set up for 250 guests.', url: 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1200&q=80' },
        { id: 9, category: 'weddings', tag: 'Weddings', title: 'Bridal Portrait Session', caption: 'Natural lighting highlights along the garden stone corridors.', url: 'https://images.unsplash.com/photo-1527529482837-4698179dc6ce?auto=format&fit=crop&w=1200&q=80' }
    ],
    get filteredItems() {
        if (this.activeFilter === 'all') return this.items;
        return this.items.filter(item => item.category === this.activeFilter);
    },
    openLightbox(index) {
        this.selectedIndex = index;
        this.modalOpen = true;
    },
    nextPhoto() {
        this.selectedIndex = (this.selectedIndex + 1) % this.filteredItems.length;
    },
    prevPhoto() {
        this.selectedIndex = (this.selectedIndex - 1 + this.filteredItems.length) % this.filteredItems.length;
    }
}">

    <!-- Header Component -->
    <x-header />

    <!-- Hero Banner -->
    <section class="relative py-20 px-6 sm:px-12 text-center bg-[#1C3627] text-white">
        <div class="max-w-3xl mx-auto space-y-4">
            <p class="text-[10px] uppercase tracking-[0.3em] text-[#B89462] font-semibold">OUR MEMORIES</p>
            <h1 class="text-3xl sm:text-5xl font-serif">The Gazebo Photo Gallery</h1>
            <p class="text-xs sm:text-sm font-light text-gray-300 leading-relaxed max-w-2xl mx-auto">
                Take a visual tour through our gardens, grand hall, and unforgettable celebrations hosted at our venue.
            </p>
        </div>
    </section>

    <!-- Gallery Section -->
    <section class="py-12 sm:py-16 px-4 sm:px-8 lg:px-16 max-w-7xl mx-auto space-y-10">

        <!-- Filter Bar -->
        <div class="flex items-center justify-center flex-wrap gap-2 sm:gap-4">
            <button @click="activeFilter = 'all'"
                :class="activeFilter === 'all' ? 'bg-[#1C3627] text-white border-[#1C3627]' :
                    'bg-white text-gray-700 border-gray-200 hover:border-[#1C3627]'"
                class="px-5 py-2 rounded-full text-xs font-semibold tracking-wider uppercase border transition-all shadow-sm">
                All Photos
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
                Debuts
            </button>
            <button @click="activeFilter = 'grotto'"
                :class="activeFilter === 'grotto' ? 'bg-[#1C3627] text-white border-[#1C3627]' :
                    'bg-white text-gray-700 border-gray-200 hover:border-[#1C3627]'"
                class="px-5 py-2 rounded-full text-xs font-semibold tracking-wider uppercase border transition-all shadow-sm">
                Grotto & Garden
            </button>
            <button @click="activeFilter = 'hall'"
                :class="activeFilter === 'hall' ? 'bg-[#1C3627] text-white border-[#1C3627]' :
                    'bg-white text-gray-700 border-gray-200 hover:border-[#1C3627]'"
                class="px-5 py-2 rounded-full text-xs font-semibold tracking-wider uppercase border transition-all shadow-sm">
                Elegant Hall
            </button>
        </div>

        <!-- Image Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            <template x-for="(item, index) in filteredItems" :key="item.id">
                <div @click="openLightbox(index)"
                    class="group relative bg-white border border-gray-200/80 rounded-sm overflow-hidden shadow-sm hover:shadow-xl transition-all duration-500 cursor-pointer">

                    <div class="aspect-w-4 aspect-h-3 h-64 overflow-hidden bg-gray-100">
                        <img :src="item.url" :alt="item.title"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                    </div>

                    <!-- Overlay Detail on Hover -->
                    <div
                        class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-5 text-white">
                        <span class="text-[9px] uppercase tracking-widest text-[#B89462] font-semibold"
                            x-text="item.tag"></span>
                        <h4 class="font-serif text-lg leading-snug" x-text="item.title"></h4>
                        <p class="text-[11px] font-light text-gray-300 line-clamp-1 mt-1" x-text="item.caption"></p>
                    </div>

                    <!-- Static Tag Badge -->
                    <div class="absolute top-3 left-3 group-hover:opacity-0 transition-opacity">
                        <span
                            class="bg-white/90 backdrop-blur-sm text-[#1C3627] text-[9px] uppercase tracking-wider px-3 py-1 rounded-full font-semibold border border-gray-200/60 shadow-sm"
                            x-text="item.tag"></span>
                    </div>
                </div>
            </template>
        </div>

    </section>

    <!-- Clean White Lightbox Modal -->
    <div x-show="modalOpen" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 sm:p-8"
        @keydown.escape.window="modalOpen = false" @keydown.left.window="prevPhoto()"
        @keydown.right.window="nextPhoto()">

        <div class="relative w-full max-w-4xl bg-white text-gray-800 rounded-lg shadow-2xl overflow-hidden p-6 sm:p-8 space-y-4 border border-gray-100"
            @click.outside="modalOpen = false">

            <!-- Header -->
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div class="flex items-center gap-3">
                    <span
                        class="text-[9px] uppercase tracking-widest bg-[#1C3627] text-white px-3 py-1 rounded-full font-semibold"
                        x-text="filteredItems[selectedIndex]?.tag"></span>
                    <h3 class="font-serif text-xl text-[#3b4d3c]" x-text="filteredItems[selectedIndex]?.title"></h3>
                </div>
                <button @click="modalOpen = false"
                    class="text-gray-400 hover:text-gray-700 text-3xl font-light transition-colors">&times;</button>
            </div>

            <!-- Main Image View -->
            <div
                class="relative bg-gray-50 rounded-md overflow-hidden border border-gray-200 max-h-[65vh] flex items-center justify-center">
                <img :src="filteredItems[selectedIndex]?.url" class="w-full max-h-[65vh] object-contain">

                <!-- Previous Button -->
                <button @click.stop="prevPhoto()"
                    class="absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/90 hover:bg-white text-gray-800 flex items-center justify-center shadow-md transition-all">
                    &#10094;
                </button>

                <!-- Next Button -->
                <button @click.stop="nextPhoto()"
                    class="absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/90 hover:bg-white text-gray-800 flex items-center justify-center shadow-md transition-all">
                    &#10095;
                </button>
            </div>

            <!-- Footer Caption & Controls -->
            <div class="flex flex-col sm:flex-row items-center justify-between pt-2 border-t border-gray-100 gap-3">
                <p class="text-xs text-gray-600 font-light italic" x-text="filteredItems[selectedIndex]?.caption"></p>
                <div class="flex items-center gap-4 shrink-0">
                    <span class="text-xs text-gray-400"
                        x-text="(selectedIndex + 1) + ' / ' + filteredItems.length"></span>
                    <button @click="modalOpen = false"
                        class="bg-[#1C3627] text-white px-5 py-2 rounded-full text-xs font-semibold uppercase tracking-wider hover:bg-[#2a4d38] transition-all shadow-sm">
                        Close
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- Call to Action Banner -->
    <section class="bg-[#1C3627] text-white py-16 px-6 text-center relative overflow-hidden">
        <div class="relative z-10 max-w-2xl mx-auto space-y-5">
            <p class="text-[10px] uppercase tracking-[0.3em] text-[#B89462] font-semibold">YOUR CELEBRATION AWAITS</p>
            <h2 class="text-3xl sm:text-5xl font-serif">Create Your Own Memories</h2>
            <p class="text-xs font-light text-gray-300 leading-relaxed">
                Contact our team to discuss booking options and schedule your personal venue tour.
            </p>
            <div class="pt-3">
                <a href="/#inquire"
                    class="inline-flex items-center gap-2 bg-[#B89462] text-white px-8 py-3 rounded-full text-xs font-semibold tracking-widest uppercase hover:bg-[#a38153] transition-all shadow-md">
                    INQUIRE NOW &rarr;
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
