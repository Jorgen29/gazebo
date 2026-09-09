<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venues Management - Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="min-h-screen bg-[#F7F4EE] text-gray-700 font-sans antialiased flex" x-data="venuesManagement()">

    <!-- Admin Sidebar Component -->
    <x-admin.sidebar />

    <!-- Main Workspace -->
    <div class="flex-1 flex flex-col min-w-0 min-h-screen">

        <!-- Admin Header Component -->
        <x-admin.header />

        <!-- Main Content Canvas -->
        <main class="p-6 lg:p-8 space-y-6 flex-1 overflow-y-auto">

            <!-- Main Card Container -->
            <div class="bg-[#FFFFFF] border border-[#E5DDD0] rounded-2xl p-6 shadow-xs space-y-6">

                <!-- Header Action Row -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-base font-bold text-gray-900 tracking-tight">Venues Directory</h1>
                        <p class="text-xs text-gray-400 mt-0.5">Manage event spaces, hourly rates, capacities, and
                            showcase galleries</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Filter Select -->
                        <select x-model="statusFilter"
                            class="bg-[#F7F4EE] border border-[#E5DDD0] text-gray-700 text-xs rounded-xl px-3 py-2 font-semibold focus:outline-none focus:ring-2 focus:ring-[#6F927D]/40">
                            <option value="all">All Statuses</option>
                            <option value="active">Active Only</option>
                            <option value="inactive">Inactive Only</option>
                        </select>

                        <!-- Search Bar -->
                        <div class="relative flex items-center">
                            <input type="text" x-model="search" placeholder="Search venue, capacity..."
                                class="bg-[#F7F4EE] text-gray-800 placeholder-gray-400 text-xs font-medium rounded-xl border border-[#E5DDD0] pl-9 pr-8 py-2 w-48 sm:w-64 focus:outline-none focus:ring-2 focus:ring-[#6F927D]/40 focus:bg-white transition-all">
                            <svg class="w-4 h-4 text-[#B89A62] absolute left-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <button x-show="search.length > 0" @click="search = ''"
                                class="absolute right-2.5 text-gray-400 hover:text-gray-600">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Add Venue Button -->
                        <button type="button" @click="openAddModal()"
                            class="bg-[#6F927D] hover:bg-[#5b7a67] text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all shadow-xs flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Add Venue
                        </button>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr
                                class="text-gray-400 font-bold uppercase text-[11px] tracking-wider border-b border-[#E5DDD0]">
                                <th class="pb-3 px-3">VENUE DETAILS</th>
                                <th class="pb-3 px-3">CAPACITY</th>
                                <th class="pb-3 px-3">HOURLY RATE</th>
                                <th class="pb-3 px-3">FEATURES</th>
                                <th class="pb-3 px-3">SHOWCASE</th>
                                <th class="pb-3 px-3">STATUS</th>
                                <th class="pb-3 px-3 text-right">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dashed divide-[#E5DDD0] text-gray-600">
                            <template x-if="loading">
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-400 font-medium">
                                        Loading venues data...
                                    </td>
                                </tr>
                            </template>

                            <template x-if="!loading && filteredVenues.length === 0">
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-400 font-medium">
                                        No venues found in the directory.
                                    </td>
                                </tr>
                            </template>

                            <template x-for="venue in filteredVenues" :key="venue.id">
                                <tr class="hover:bg-[#F7F4EE]/60 transition-colors">
                                    <!-- Venue Info & Thumbnail -->
                                    <td class="py-4 px-3">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-12 h-12 rounded-xl bg-[#F7F4EE] border border-[#E5DDD0] overflow-hidden flex-shrink-0 flex items-center justify-center">
                                                <template x-if="venue.image">
                                                    <img :src="'/storage/' + venue.image" :alt="venue.title"
                                                        class="w-full h-full object-cover">
                                                </template>
                                                <template x-if="!venue.image">
                                                    <svg class="w-6 h-6 text-[#B89A62]" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 v5m-4 0h4" />
                                                    </svg>
                                                </template>
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900 text-xs" x-text="venue.title"></div>
                                                <p class="text-[11px] text-gray-400 line-clamp-1 max-w-xs"
                                                    x-text="venue.description || 'No description provided.'"></p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Capacity -->
                                    <td class="py-4 px-3 font-semibold text-gray-700">
                                        <span class="inline-flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-[#B89A62]" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                            <span x-text="formatNumber(venue.capacity) + ' guests'"></span>
                                        </span>
                                    </td>

                                    <!-- Price -->
                                    <td class="py-4 px-3 font-bold text-[#6F927D]">
                                        ₱<span x-text="formatCurrency(venue.price_per_hour)"></span>
                                        <span class="text-[10px] text-gray-400 font-normal">/ hr</span>
                                    </td>

                                    <!-- Features Count Badge -->
                                    <td class="py-4 px-3">
                                        <button type="button" @click="openFeaturesModal(venue)"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border border-[#E5DDD0] bg-[#F7F4EE] hover:bg-[#6F927D]/10 hover:border-[#6F927D]/40 text-gray-700 text-[11px] font-semibold transition-all cursor-pointer">
                                            <svg class="w-3.5 h-3.5 text-[#B89A62]" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                            <span x-text="getFeaturesCount(venue) + ' Features'"></span>
                                        </button>
                                    </td>

                                    <!-- Showcase Count Badge -->
                                    <td class="py-4 px-3">
                                        <button type="button" @click="openShowcaseModal(venue)"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border border-[#E5DDD0] bg-[#F7F4EE] hover:bg-[#B89A62]/10 hover:border-[#B89A62]/40 text-gray-700 text-[11px] font-semibold transition-all cursor-pointer">
                                            <svg class="w-3.5 h-3.5 text-[#B89A62]" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <span x-text="getShowcaseCount(venue) + ' Photos'"></span>
                                        </button>
                                    </td>

                                    <!-- Status Badge -->
                                    <td class="py-4 px-3">
                                        <span x-show="venue.is_active"
                                            class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 uppercase border border-emerald-200/60">Active</span>
                                        <span x-show="!venue.is_active"
                                            class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-rose-50 text-rose-700 uppercase border border-rose-200/60">Inactive</span>
                                    </td>

                                    <!-- Button Group Actions -->
                                    <td class="py-4 px-3 text-right">
                                        <div
                                            class="inline-flex items-center rounded-xl border border-[#E5DDD0] bg-[#F7F4EE] p-0.5 shadow-xs">
                                            <button type="button" @click="openEditModal(venue)" title="Edit Venue"
                                                class="px-2.5 py-1 rounded-lg text-xs font-medium text-gray-700 hover:bg-white hover:text-[#6F927D] hover:shadow-2xs transition-all flex items-center gap-1 cursor-pointer">
                                                <svg class="w-3.5 h-3.5 text-[#B89A62]" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                <span>Edit</span>
                                            </button>

                                            <div class="h-4 w-px bg-[#E5DDD0] my-auto"></div>

                                            <button type="button" @click="confirmDelete(venue)" title="Delete Venue"
                                                class="px-2.5 py-1 rounded-lg text-xs font-medium text-rose-600 hover:bg-rose-50 hover:shadow-2xs transition-all flex items-center gap-1 cursor-pointer">
                                                <svg class="w-3.5 h-3.5 text-rose-500" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                <span>Delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

            </div>

        </main>
    </div>

    <!-- Venue Form Modal Component -->
    <x-admin.venue-modal />

    <!-- Features View Modal -->
    <div x-show="featuresModalOpen" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/40 backdrop-blur-xs">
        <div @click.outside="featuresModalOpen = false"
            class="bg-white border border-[#E5DDD0] rounded-2xl w-full max-w-md p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b border-[#E5DDD0] pb-3">
                <div>
                    <h3 class="font-bold text-gray-900 text-sm" x-text="selectedVenue?.title + ' - Features'"></h3>
                    <p class="text-[11px] text-gray-400">Included amenities and workspace offerings</p>
                </div>
                <button type="button" @click="featuresModalOpen = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                <template x-if="selectedVenueFeatures.length === 0">
                    <p class="text-xs text-gray-400 italic py-2">No specific features listed for this venue.</p>
                </template>

                <template x-for="(feat, idx) in selectedVenueFeatures" :key="idx">
                    <div
                        class="flex items-center gap-2 p-2.5 rounded-xl bg-[#F7F4EE] border border-[#E5DDD0]/60 text-xs font-semibold text-gray-700">
                        <svg class="w-4 h-4 text-[#6F927D] flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7" />
                        </svg>
                        <span x-text="feat"></span>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Showcase Images View Modal -->
    <div x-show="showcaseModalOpen" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-gray-900/50 backdrop-blur-xs"
        x-data="{ currentSlide: 0 }">
        <div @click.outside="showcaseModalOpen = false"
            class="bg-white border border-[#E5DDD0] rounded-2xl w-full max-w-5xl p-6 shadow-2xl space-y-4">

            <!-- Header -->
            <div class="flex items-center justify-between border-b border-[#E5DDD0] pb-3">
                <div>
                    <h3 class="font-bold text-gray-900 text-base"
                        x-text="selectedVenue?.title + ' - Showcase Gallery'"></h3>
                    <p class="text-xs text-gray-400 mt-0.5">High-resolution venue preview photos</p>
                </div>
                <button type="button" @click="showcaseModalOpen = false"
                    class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Carousel Container -->
            <div class="space-y-4" x-init="$watch('showcaseModalOpen', val => { if (val) currentSlide = 0; })">

                <template x-if="selectedVenueShowcase.length === 0">
                    <div class="py-20 text-center bg-[#F7F4EE] border border-[#E5DDD0] rounded-2xl">
                        <p class="text-xs text-gray-400 font-medium">No showcase gallery images available.</p>
                    </div>
                </template>

                <template x-if="selectedVenueShowcase.length > 0">
                    <div class="space-y-4">
                        <!-- Main Slider View -->
                        <div
                            class="relative aspect-16/9 sm:aspect-21/9 w-full bg-[#111827] rounded-2xl overflow-hidden border border-[#E5DDD0]">

                            <!-- Images -->
                            <template x-for="(imgUrl, idx) in selectedVenueShowcase" :key="idx">
                                <div x-show="currentSlide === idx"
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    class="absolute inset-0 w-full h-full">
                                    <img :src="imgUrl" :alt="'Showcase image ' + (idx + 1)"
                                        class="w-full h-full object-contain">
                                </div>
                            </template>

                            <!-- Counter Badge -->
                            <div
                                class="absolute top-4 right-4 bg-black/60 backdrop-blur-md text-white text-xs font-semibold px-3 py-1 rounded-full border border-white/20">
                                <span x-text="currentSlide + 1"></span> / <span
                                    x-text="selectedVenueShowcase.length"></span>
                            </div>

                            <!-- Previous Button -->
                            <button type="button" x-show="selectedVenueShowcase.length > 1"
                                @click="currentSlide = currentSlide === 0 ? selectedVenueShowcase.length - 1 : currentSlide - 1"
                                class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/80 hover:bg-white text-gray-800 backdrop-blur-md flex items-center justify-center transition-all shadow-md hover:scale-105 cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>

                            <!-- Next Button -->
                            <button type="button" x-show="selectedVenueShowcase.length > 1"
                                @click="currentSlide = currentSlide === selectedVenueShowcase.length - 1 ? 0 : currentSlide + 1"
                                class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/80 hover:bg-white text-gray-800 backdrop-blur-md flex items-center justify-center transition-all shadow-md hover:scale-105 cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>

                        <!-- Thumbnail Selector Bar -->
                        <div x-show="selectedVenueShowcase.length > 1"
                            class="flex items-center gap-3 overflow-x-auto pb-2 scrollbar-thin">
                            <template x-for="(imgUrl, idx) in selectedVenueShowcase" :key="idx">
                                <button type="button" @click="currentSlide = idx"
                                    class="relative w-24 aspect-video rounded-xl overflow-hidden flex-shrink-0 border-2 transition-all cursor-pointer"
                                    :class="currentSlide === idx ?
                                        'border-[#6F927D] ring-2 ring-[#6F927D]/30 opacity-100 scale-105' :
                                        'border-[#E5DDD0] opacity-60 hover:opacity-100'">
                                    <img :src="imgUrl" :alt="'Thumbnail ' + (idx + 1)"
                                        class="w-full h-full object-cover">
                                </button>
                            </template>
                        </div>
                    </div>
                </template>

            </div>
        </div>
    </div>

    <!-- Script Context & State Handler -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('venuesManagement', () => ({
                venues: [],
                loading: true,
                search: '',
                statusFilter: 'all',
                venueModalOpen: false,
                featuresModalOpen: false,
                showcaseModalOpen: false,
                selectedVenue: null,
                selectedVenueFeatures: [],
                selectedVenueShowcase: [],
                isEdit: false,
                formData: {
                    id: null,
                    title: '',
                    price_per_hour: '',
                    capacity: '',
                    is_active: '1',
                    description: '',
                    features: ['']
                },

                routes: {
                    index: "{{ route('admin.venues.index') }}",
                    store: "{{ route('admin.venues.store') }}",
                    update: "{{ url('admin/venues') }}",
                    destroy: "{{ url('admin/venues') }}"
                },

                init() {
                    this.fetchVenues();
                },

                fetchVenues() {
                    this.loading = true;
                    fetch(this.routes.index, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            this.venues = data;
                            this.loading = false;
                        })
                        .catch(err => {
                            console.error('Failed fetching venues:', err);
                            this.loading = false;
                        });
                },

                get filteredVenues() {
                    return this.venues.filter(venue => {
                        const matchesSearch = venue.title.toLowerCase().includes(this.search
                                .toLowerCase()) ||
                            venue.capacity.toString().includes(this.search) ||
                            venue.price_per_hour.toString().includes(this.search);

                        const matchesStatus = this.statusFilter === 'all' ? true :
                            (this.statusFilter === 'active' ? venue.is_active : !venue
                                .is_active);

                        return matchesSearch && matchesStatus;
                    });
                },

                getFeaturesList(venue) {
                    if (!venue || !venue.features) return [];
                    if (Array.isArray(venue.features)) return venue.features.filter(f => f && f
                        .trim() !== '');
                    if (typeof venue.features === 'string') {
                        try {
                            const parsed = JSON.parse(venue.features);
                            return Array.isArray(parsed) ? parsed.filter(f => f && f.trim() !== '') :
                        [];
                        } catch (e) {
                            return [];
                        }
                    }
                    return [];
                },

                getFeaturesCount(venue) {
                    return this.getFeaturesList(venue).length;
                },

                openFeaturesModal(venue) {
                    this.selectedVenue = venue;
                    this.selectedVenueFeatures = this.getFeaturesList(venue);
                    this.featuresModalOpen = true;
                },

                getShowcaseList(venue) {
                    if (!venue) return [];

                    const showcaseData = venue.showcase_images || venue.showcase;
                    if (!showcaseData) return [];

                    let parsed = [];

                    if (Array.isArray(showcaseData)) {
                        parsed = showcaseData;
                    } else if (typeof showcaseData === 'string') {
                        try {
                            parsed = JSON.parse(showcaseData);
                        } catch (e) {
                            console.error('Error parsing showcase JSON:', e);
                            return [];
                        }
                    }

                    if (!Array.isArray(parsed)) return [];

                    return parsed
                        .filter(path => Boolean(path))
                        .map(path => {
                            let cleanPath = String(path).replace(/\\/g, '').trim();

                            if (cleanPath.startsWith('http://') || cleanPath.startsWith(
                                    'https://')) {
                                return cleanPath;
                            }

                            cleanPath = cleanPath.replace(/^\/?(storage\/)?/, '');
                            return `/storage/${cleanPath}`;
                        });
                },

                getShowcaseCount(venue) {
                    return this.getShowcaseList(venue).length;
                },

                openShowcaseModal(venue) {
                    this.selectedVenue = venue;
                    this.selectedVenueShowcase = this.getShowcaseList(venue);
                    this.showcaseModalOpen = true;
                },

                addFeature() {
                    this.formData.features.push('');
                },

                removeFeature(index) {
                    if (this.formData.features.length > 1) {
                        this.formData.features.splice(index, 1);
                    }
                },

                openAddModal() {
                    this.isEdit = false;
                    this.formData = {
                        id: null,
                        title: '',
                        price_per_hour: '',
                        capacity: '',
                        is_active: '1',
                        description: '',
                        features: ['']
                    };
                    this.venueModalOpen = true;
                },

                openEditModal(venue) {
                    this.isEdit = true;
                    const parsedFeatures = this.getFeaturesList(venue);
                    this.formData = {
                        id: venue.id,
                        title: venue.title,
                        price_per_hour: venue.price_per_hour,
                        capacity: venue.capacity,
                        is_active: venue.is_active ? '1' : '0',
                        description: venue.description || '',
                        features: parsedFeatures.length > 0 ? parsedFeatures : ['']
                    };
                    this.venueModalOpen = true;
                },

                confirmDelete(venue) {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: `You are about to delete "${venue.title}".`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#6F927D',
                        cancelButtonColor: '#e11d48',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.deleteVenue(venue.id);
                        }
                    });
                },

                deleteVenue(id) {
                    fetch(`${this.routes.destroy}/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    ?.content || '',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Deleted!', data.message || 'Venue deleted successfully.',
                                    'success');
                                this.fetchVenues();
                            } else {
                                Swal.fire('Error!', data.message || 'Could not delete venue.',
                                    'error');
                            }
                        })
                        .catch(err => {
                            console.error('Delete error:', err);
                            Swal.fire('Error!', 'An error occurred while deleting.', 'error');
                        });
                },

                formatCurrency(value) {
                    const num = parseFloat(value);
                    return isNaN(num) ? '0.00' : num.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                },

                formatNumber(value) {
                    const num = parseInt(value, 10);
                    return isNaN(num) ? '0' : num.toLocaleString('en-US');
                }
            }));
        });
    </script>
</body>

</html>
