@php
    $notifications = \App\Models\Notification::latest()->limit(12)->get();
    $unreadNotificationsCount = $notifications->whereNull('read_at')->count();
@endphp

<!-- Admin Header -->
<header
    class="h-24 bg-[#FBF9F5] border-b border-[#E5DDD0] px-8 flex items-center justify-between sticky top-0 z-30 text-[#1C3627] shadow-sm">
    <div class="flex items-center gap-3">
        <div class="w-2 h-2 rounded-full bg-[#B89462]"></div>
        <h1 class="font-serif text-3xl tracking-[0.08em] uppercase text-[#1C3627]">Overview</h1>
    </div>

    <div class="flex items-center gap-5">
        <div class="relative" x-data="{
            open: false,
            filter: 'all',
            notifications: @js(
    $notifications->map(function ($item) {
        return [
            'id' => $item->id,
            'type' => $item->type,
            'email' => $item->email,
            'subject' => $item->subject,
            'message' => $item->message,
            'read' => (bool) $item->read_at,
            'created_at' => $item->created_at?->diffForHumans(),
        ];
    }),
),
            get visibleNotifications() {
                if (this.filter === 'unread') {
                    return this.notifications.filter(item => !item.read);
                }
        
                if (this.filter === 'read') {
                    return this.notifications.filter(item => item.read);
                }
        
                return this.notifications;
            }
        }">
            <button @click.stop="open = !open" @click.outside="open = false"
                class="p-2.5 text-[#1C3627] hover:text-[#B89462] transition-colors relative rounded-full hover:bg-[#F7F4EE] focus:outline-none cursor-pointer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                @if ($unreadNotificationsCount > 0)
                    <span
                        class="min-w-[1.25rem] h-5 px-1.5 rounded-full bg-[#B89462] text-white text-[10px] font-bold absolute -top-1 -right-1 flex items-center justify-center border-2 border-[#FBF9F5]">
                        {{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}
                    </span>
                @endif
            </button>

            <div x-show="open" x-cloak x-transition
                class="absolute right-0 mt-3 w-[30rem] max-w-[90vw] bg-[#FBF9F5] border border-[#E5DDD0] rounded-2xl shadow-[0_20px_45px_rgba(28,54,39,0.12)] z-50 overflow-hidden">
                <div class="px-4 py-3 border-b border-[#E5DDD0] bg-[#FFFDFB]">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-[#B89462]">Alerts</p>
                            <h3 class="font-serif text-2xl text-[#1C3627] mt-1">Notifications</h3>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-[#6B7E73]">
                            <span x-text="notifications.filter(item => !item.read).length"></span> unread
                        </span>
                    </div>

                    <div class="mt-3 flex items-center gap-2">
                        <button type="button" @click.stop="filter = 'all'"
                            :class="filter === 'all' ? 'bg-[#1C3627] text-white' : 'bg-[#F7F4EE] text-[#1C3627]'"
                            class="flex-1 rounded-full px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-[0.14em] transition-all">
                            All
                        </button>
                        <button type="button" @click.stop="filter = 'unread'"
                            :class="filter === 'unread' ? 'bg-[#1C3627] text-white' : 'bg-[#F7F4EE] text-[#1C3627]'"
                            class="flex-1 rounded-full px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-[0.14em] transition-all">
                            Unread
                        </button>
                        <button type="button" @click.stop="filter = 'read'"
                            :class="filter === 'read' ? 'bg-[#1C3627] text-white' : 'bg-[#F7F4EE] text-[#1C3627]'"
                            class="flex-1 rounded-full px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-[0.14em] transition-all">
                            Read
                        </button>
                    </div>

                    @if ($notifications->whereNull('read_at')->count() > 0)
                        <form method="POST" action="{{ route('admin.notifications.mark-all-read') }}" class="mt-3">
                            @csrf
                            <button type="submit"
                                class="w-full rounded-full border border-[#D7C7A6] bg-[#F7F1E7] text-[#1C3627] px-3 py-2 text-[10px] font-bold uppercase tracking-[0.18em] hover:bg-[#F0E4D2] transition-colors cursor-pointer">
                                Mark all as read
                            </button>
                        </form>
                    @endif
                </div>

                <div class="max-h-80 overflow-y-auto divide-y divide-[#E5DDD0]">
                    <template x-if="visibleNotifications.length === 0">
                        <div class="px-4 py-8 text-center text-sm text-[#6B7E73]">
                            No notifications in this filter.
                        </div>
                    </template>

                    <template x-for="notification in visibleNotifications" :key="notification.id">
                        <button type="button"
                            @click.stop="window.location.href = '{{ route('admin.notifications.open', ['id' => ':id']) }}'.replace(':id', notification.id)"
                            class="w-full text-left px-4 py-3 hover:bg-[#F7F4EE] transition-colors cursor-pointer">
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 flex-shrink-0">
                                    <span
                                        :class="notification.read ? 'bg-[#EAF4EE] text-[#1C3627]' :
                                            'bg-[#F7F1E7] text-[#B89462]'"
                                        class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                    </span>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-[#6B7E73]"
                                            x-text="notification.type.replace('_', ' ')"></p>
                                        <span x-show="!notification.read" class="w-2 h-2 rounded-full bg-[#B89462]"
                                            title="Unread"></span>
                                    </div>

                                    <p class="mt-1 text-sm font-semibold text-[#1C3627]" x-text="notification.subject">
                                    </p>
                                    <p class="mt-1 text-xs text-[#6B7E73] leading-5" x-text="notification.message"></p>

                                    <div
                                        class="mt-2 flex items-center justify-between gap-2 text-[10px] uppercase tracking-[0.14em] text-[#6B7E73] whitespace-nowrap overflow-hidden">
                                        <span class="truncate max-w-[12rem]" x-text="notification.email"></span>
                                        <span class="flex-shrink-0" x-text="notification.created_at"></span>
                                    </div>
                                </div>
                            </div>
                        </button>
                    </template>
                </div>
            </div>
        </div>

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
                        <svg class="w-4 h-4 text-[#7a2b2b]" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
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
