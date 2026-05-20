@php
    $walletMode    = session('wallet_mode', 'demo');
    $navWallet     = null;
    if (auth()->check()) {
        $navWallet = auth()->user()->wallets()->where('type', $walletMode)->first();
    }
@endphp

<!-- Top Navbar -->
<header class="h-16 flex items-center justify-between px-4 lg:px-6 flex-shrink-0 transition-colors duration-300 glassmorphism"
        :class="isDark ? 'bg-gray-900/80 border-b border-gray-800/60' : 'bg-white/80 border-b border-gray-200/60'">

    <!-- Left: Mobile menu + Page title -->
    <div class="flex items-center gap-3">
        <button @click="sidebarOpen = !sidebarOpen"
                class="lg:hidden p-2 rounded-lg transition-colors"
                :class="isDark ? 'text-gray-400 hover:text-white hover:bg-gray-800' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100'">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <div>
            <h1 class="text-sm font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">@yield('page-title', 'Dashboard')</h1>
            <p class="text-xs hidden sm:block text-gray-500">@yield('page-subtitle', '')</p>
        </div>
    </div>

    <!-- Right -->
    <div class="flex items-center gap-1.5 sm:gap-2">

        {{-- Active Wallet Balance Pill --}}
        @if($navWallet)
        <a href="{{ route('wallet.index') }}"
           class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-xl border transition-all duration-200 hover:border-cyan-500/40 group"
           :class="isDark ? 'bg-gray-800/60 border-gray-700/60' : 'bg-gray-50 border-gray-200'">
            <span class="w-1.5 h-1.5 rounded-full {{ $walletMode === 'demo' ? 'bg-amber-400' : 'bg-emerald-400' }} animate-pulse flex-shrink-0"></span>
            <span class="text-xs font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">
                ${{ number_format($navWallet->available_balance, 2) }}
            </span>
            <span class="text-xs {{ $walletMode === 'demo' ? 'text-amber-400' : 'text-cyan-400' }} font-medium">
                {{ ucfirst($walletMode) }}
            </span>
        </a>
        @endif

        <!-- Theme Switcher -->
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" @click.away="open = false"
                    class="p-2 rounded-lg transition-colors"
                    :class="isDark ? 'text-gray-400 hover:text-cyan-400 hover:bg-gray-800' : 'text-gray-500 hover:text-cyan-600 hover:bg-gray-100'">
                <svg x-show="!isDark" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <svg x-show="isDark" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            </button>

            <div x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                 class="absolute right-0 top-full mt-2 w-36 rounded-xl border py-1 z-50 shadow-xl"
                 :class="isDark ? 'bg-gray-900 border-gray-700/60' : 'bg-white border-gray-200'">
                @foreach([['dark','Dark','M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z'],['light','Light','M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z'],['system','System','M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z']] as [$val,$label,$path])
                <button @click="setTheme('{{ $val }}'); open = false"
                        class="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-medium transition-colors"
                        :class="theme === '{{ $val }}' ? 'text-cyan-400' : (isDark ? 'text-gray-400 hover:text-white hover:bg-gray-800' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50')">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $path }}"/>
                    </svg>
                    {{ $label }}
                    <span x-show="theme === '{{ $val }}'" class="ml-auto w-1.5 h-1.5 rounded-full bg-cyan-400"></span>
                </button>
                @endforeach
            </div>
        </div>

        <!-- Notifications Bell -->
        @auth
        @php $bellCount = app(\App\Services\NotificationService::class)->unreadCount(auth()->user()); @endphp
        <div x-data="{ open: false, count: {{ $bellCount }} }" class="relative">
            <button @click="open = !open" @click.away="open = false"
                    class="relative p-2 rounded-lg transition-colors"
                    :class="isDark ? 'text-gray-400 hover:text-white hover:bg-gray-800' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span x-show="count > 0" class="absolute -top-0.5 -right-0.5 min-w-[16px] h-4 px-1 rounded-full bg-cyan-500 text-[9px] font-bold text-black flex items-center justify-center" x-text="count > 99 ? '99+' : count"></span>
            </button>

            <div x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                 class="absolute right-0 top-full mt-2 w-80 rounded-xl border z-50 shadow-xl overflow-hidden"
                 :class="isDark ? 'bg-gray-900 border-gray-700/60' : 'bg-white border-gray-200'">

                <div class="flex items-center justify-between px-4 py-3 border-b" :class="isDark ? 'border-gray-800' : 'border-gray-100'">
                    <p class="text-xs font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">Notifications</p>
                    <div class="flex items-center gap-2">
                        @if($bellCount > 0)
                        <form method="POST" action="{{ route('notifications.read-all') }}">
                            @csrf
                            <button type="submit" class="text-[10px] text-cyan-400 hover:text-cyan-300 transition-colors">Mark all read</button>
                        </form>
                        @endif
                        <a href="{{ route('notifications.index') }}" class="text-[10px] text-gray-500 hover:text-gray-300 transition-colors">View all</a>
                    </div>
                </div>

                <div class="max-h-80 overflow-y-auto divide-y" :class="isDark ? 'divide-gray-800/60' : 'divide-gray-100'">
                    @php $recentNotifs = app(\App\Services\NotificationService::class)->recent(auth()->user(), 8); @endphp
                    @forelse($recentNotifs as $notif)
                    <div class="px-4 py-3 {{ !$notif->isRead() ? ($bellCount > 0 ? 'bg-cyan-500/5' : '') : '' }} hover:bg-gray-500/5 transition-colors">
                        <div class="flex items-start gap-2.5">
                            <span class="w-1.5 h-1.5 rounded-full mt-1.5 flex-shrink-0 {{ !$notif->isRead() ? 'bg-cyan-400' : 'bg-gray-600' }}"></span>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-medium truncate" :class="isDark ? 'text-white' : 'text-gray-900'">{{ $notif->title }}</p>
                                <p class="text-[11px] text-gray-500 mt-0.5 line-clamp-2">{{ $notif->message }}</p>
                                <p class="text-[10px] text-gray-600 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                            </div>
                            @if(!$notif->isRead())
                            <form method="POST" action="{{ route('notifications.read', $notif) }}" class="flex-shrink-0">
                                @csrf
                                <button type="submit" class="text-[10px] text-gray-500 hover:text-cyan-400 transition-colors mt-1">✓</button>
                            </form>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="px-4 py-8 text-center">
                        <p class="text-xs text-gray-500">No notifications yet.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
        @endauth

        <!-- Profile Dropdown -->
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" @click.away="open = false"
                    class="flex items-center gap-2 pl-2 pr-3 py-1.5 rounded-xl transition-colors"
                    :class="isDark ? 'hover:bg-gray-800' : 'hover:bg-gray-100'">
                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-cyan-500 to-cyan-700 flex items-center justify-center flex-shrink-0">
                    <span class="text-xs font-bold text-white">{{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}</span>
                </div>
                <span class="text-xs font-medium hidden sm:block" :class="isDark ? 'text-gray-300' : 'text-gray-700'">
                    {{ auth()->user()?->name }}
                </span>
                <svg class="w-3.5 h-3.5 text-gray-500 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                 class="absolute right-0 top-full mt-2 w-52 rounded-xl border py-1 z-50 shadow-xl"
                 :class="isDark ? 'bg-gray-900 border-gray-700/60' : 'bg-white border-gray-200'">

                <!-- User info -->
                <div class="px-4 py-3 border-b" :class="isDark ? 'border-gray-800' : 'border-gray-100'">
                    <p class="text-xs font-semibold truncate" :class="isDark ? 'text-white' : 'text-gray-900'">{{ auth()->user()?->name }}</p>
                    <p class="text-[11px] truncate text-gray-500">{{ auth()->user()?->email }}</p>
                    @if($navWallet)
                    <div class="mt-2 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full {{ $walletMode === 'demo' ? 'bg-amber-400' : 'bg-cyan-400' }}"></span>
                        <span class="text-xs font-semibold" :class="isDark ? 'text-white' : 'text-gray-800'">
                            ${{ number_format($navWallet->available_balance, 2) }}
                        </span>
                        <span class="text-xs {{ $walletMode === 'demo' ? 'text-amber-400' : 'text-cyan-400' }}">{{ ucfirst($walletMode) }}</span>
                    </div>
                    @endif
                </div>

                <a href="{{ route('profile.edit') }}"
                   class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium transition-colors"
                   :class="isDark ? 'text-gray-400 hover:text-white hover:bg-gray-800' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Profile
                </a>
                <a href="{{ route('wallet.index') }}"
                   class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium transition-colors"
                   :class="isDark ? 'text-gray-400 hover:text-white hover:bg-gray-800' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    Wallet
                </a>
                <a href="{{ route('settings.index') }}"
                   class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium transition-colors"
                   :class="isDark ? 'text-gray-400 hover:text-white hover:bg-gray-800' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Settings
                </a>

                <div class="border-t my-1" :class="isDark ? 'border-gray-800' : 'border-gray-100'"></div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-red-400 hover:text-red-300 transition-colors"
                            :class="isDark ? 'hover:bg-red-500/10' : 'hover:bg-red-50'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Logout
                    </button>
                </form>
            </div>
        </div>

    </div>
</header>
