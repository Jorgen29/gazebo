<!-- Admin Header with Increased Height (h-20) -->
<header
    class="h-20 bg-[#16221b] border-b border-white/10 px-8 flex items-center justify-between sticky top-0 z-30 text-white">
    <div class="flex items-center gap-3">
        <h1 class="text-base font-bold text-white tracking-widest uppercase">Overview</h1>
    </div>

    <!-- Right Header Controls -->
    <div class="flex items-center gap-5">
        <!-- Notification Button -->
        <button class="p-2.5 text-[#8e9f93] hover:text-white transition-colors relative">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span class="w-2.5 h-2.5 rounded-full bg-[#b89462] absolute top-2.5 right-2.5"></span>
        </button>

        <!-- Profile Dropdown Menu -->
        <div class="relative pl-5 border-l border-white/10" x-data="{ open: false }">
            <button @click="open = !open" @click.outside="open = false"
                class="flex items-center gap-3 hover:opacity-90 transition-opacity focus:outline-none cursor-pointer py-1">
                <div
                    class="w-10 h-10 rounded-full bg-[#233328] border border-[#b89462]/40 text-[#b89462] font-bold text-base flex items-center justify-center shadow-xs">
                    A
                </div>
                <div class="text-left hidden sm:block">
                    <span class="block text-base font-bold text-white leading-tight">Administrator</span>
                    <span class="block text-xs text-[#8e9f93] uppercase tracking-wider mt-0.5">Admin Portal</span>
                </div>
                <svg class="w-5 h-5 text-[#8e9f93] transition-transform duration-200" :class="{ 'rotate-180': open }"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <!-- Dropdown Menu Box -->
            <div x-show="open" x-cloak x-transition
                class="absolute right-0 mt-3 w-56 bg-[#16221b] border border-white/15 rounded-xl shadow-2xl z-50 p-2 text-sm text-white">

                <div class="px-3.5 py-2.5 border-b border-white/10 sm:hidden">
                    <p class="font-bold text-white text-base">Administrator</p>
                    <p class="text-xs text-[#8e9f93]">Admin Portal</p>
                </div>

                <!-- Admin Logout Route -->
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center gap-2.5 px-3.5 py-2.5 rounded-lg font-bold text-red-400 hover:bg-red-500/10 transition-colors text-left cursor-pointer text-sm">
                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
