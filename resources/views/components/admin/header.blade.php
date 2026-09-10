<!-- Admin Header -->
<header
    class="h-24 bg-[#FBF9F5] border-b border-[#E5DDD0] px-8 flex items-center justify-between sticky top-0 z-30 text-[#1C3627] shadow-sm">
    <div class="flex items-center gap-3">
        <div class="w-2 h-2 rounded-full bg-[#B89462]"></div>
        <h1 class="font-serif text-3xl tracking-[0.08em] uppercase text-[#1C3627]">Overview</h1>
    </div>

    <div class="flex items-center gap-5">
        <button
            class="p-2.5 text-[#1C3627] hover:text-[#B89462] transition-colors relative rounded-full hover:bg-[#F7F4EE]">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span class="w-2.5 h-2.5 rounded-full bg-[#B89462] absolute top-2.5 right-2.5"></span>
        </button>

        <div class="relative pl-5 border-l border-[#E5DDD0]" x-data="{ open: false }">
            <button @click="open = !open" @click.outside="open = false"
                class="flex items-center gap-3 hover:opacity-90 transition-opacity focus:outline-none cursor-pointer py-1">
                <div
                    class="w-11 h-11 rounded-full bg-[#1C3627] border border-[#B89462]/40 text-[#F7F4EE] font-bold text-base flex items-center justify-center shadow-sm">
                    A
                </div>
                <div class="text-left hidden sm:block">
                    <span class="block text-base font-semibold text-[#1C3627] leading-tight">Administrator</span>
                    <span class="block text-[9px] uppercase tracking-[0.28em] text-[#6B7E73] mt-0.5">Admin Portal</span>
                </div>
                <svg class="w-5 h-5 text-[#6B7E73] transition-transform duration-200" :class="{ 'rotate-180': open }"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-show="open" x-cloak x-transition
                class="absolute right-0 mt-3 w-56 bg-[#FBF9F5] border border-[#E5DDD0] rounded-xl shadow-xl z-50 p-2 text-sm text-[#1C3627]">
                <div class="px-3.5 py-2.5 border-b border-[#E5DDD0] sm:hidden">
                    <p class="font-semibold text-[#1C3627] text-base">Administrator</p>
                    <p class="text-[9px] uppercase tracking-[0.2em] text-[#6B7E73]">Admin Portal</p>
                </div>

                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center gap-2.5 px-3.5 py-2.5 rounded-lg font-semibold text-[#7a2b2b] hover:bg-[#f9efef] transition-colors text-left cursor-pointer text-sm">
                        <svg class="w-4 h-4 text-[#7a2b2b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
