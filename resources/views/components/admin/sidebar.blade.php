<!-- Admin Sidebar -->
<aside
    class="w-72 bg-[#162B20] border-r border-[#d8c7a5]/20 flex flex-col shrink-0 min-h-screen text-white shadow-[0_0_0_1px_rgba(184,148,98,0.08)]">
    <div class="h-24 px-6 flex items-center gap-3.5 border-b border-[#d8c7a5]/15">
        <div
            class="w-12 h-12 rounded-xl bg-[#1C3627] border border-[#b89462]/40 text-[#b89462] flex items-center justify-center font-bold text-lg shadow-sm">
            G
        </div>
        <div>
            <h2 class="font-serif text-2xl leading-none text-[#FBF9F5] tracking-[0.08em] uppercase">Gazebo</h2>
            <span class="mt-1 block text-[9px] uppercase tracking-[0.28em] text-[#D8C7A5]">Admin Portal</span>
        </div>
    </div>

    <nav class="p-5 space-y-2 flex-1 text-sm font-medium">
        <a href="{{ route('admin.dashboard') }}"
            class="flex items-center gap-3.5 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-[#233F32] text-white font-semibold border border-[#b89462]/35 shadow-sm' : 'text-[#dfe8e1] hover:text-white hover:bg-white/5' }}">
            <svg class="w-5 h-5 text-[#b89462]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span class="text-base">Dashboard</span>
        </a>

        <a href="{{ route('admin.inquiries') }}"
            class="flex items-center gap-3.5 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.inquiries*') ? 'bg-[#233F32] text-white font-semibold border border-[#b89462]/35 shadow-sm' : 'text-[#dfe8e1] hover:text-white hover:bg-white/5' }}">
            <svg class="w-5 h-5 text-[#b89462]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span class="text-base">Inquiries</span>
        </a>

        <a href="{{ route('admin.venues.index') }}"
            class="flex items-center gap-3.5 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.venues.*') ? 'bg-[#233F32] text-white font-semibold border border-[#b89462]/35 shadow-sm' : 'text-[#dfe8e1] hover:text-white hover:bg-white/5' }}">
            <svg class="w-5 h-5 text-[#b89462]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <span class="text-base">Venues</span>
        </a>
    </nav>
</aside>
