<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="themeManager()" :class="{ 'dark': isDark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Next Trade'))</title>
    @php $faviconUrl = app(\App\Services\SettingsService::class)->get('site_logo_url', ''); @endphp
    <link rel="icon" type="image/x-icon" href="{{ $faviconUrl ?: asset('favicon.ico') }}">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        cyan: {
                            50:'#ecfeff',100:'#cffafe',200:'#a5f3fc',
                            300:'#67e8f9',400:'#22d3ee',500:'#06b6d4',
                            600:'#0891b2',700:'#0e7490',800:'#155e75',900:'#164e63',
                        }
                    },
                    fontFamily: { sans: ['Inter','system-ui','sans-serif'] },
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Feather Icons -->
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        * { scrollbar-width: thin; scrollbar-color: #06b6d4 transparent; }
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #06b6d4; border-radius: 2px; }
        body { font-family: 'Inter', system-ui, sans-serif; }
        .sidebar-link { transition: all 0.2s ease; }
        .sidebar-link:hover { transform: translateX(3px); }
        .glassmorphism { backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); }
    </style>
</head>
<body class="min-h-screen transition-colors duration-300"
      :class="isDark ? 'bg-gray-950 text-gray-100' : 'bg-gray-50 text-gray-900'">

    <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">

        <!-- Mobile overlay -->
        <div x-show="sidebarOpen" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 z-20 bg-black/60 lg:hidden"></div>

        @include('components.sidebar')

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            @include('components.navbar')

            <main class="flex-1 overflow-y-auto p-4 lg:p-6 transition-colors duration-300"
                  :class="isDark ? 'bg-gray-950' : 'bg-gray-50'">

                @if(session('success'))
                <div class="mb-4 p-3 rounded-xl border text-sm flex items-center gap-2 bg-emerald-500/10 border-emerald-500/30 text-emerald-400">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('success') }}
                </div>
                @endif
                @if(session('error'))
                <div class="mb-4 p-3 rounded-xl border text-sm flex items-center gap-2 bg-red-500/10 border-red-500/30 text-red-400">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('error') }}
                </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        function themeManager() {
            return {
                theme: localStorage.getItem('nt_theme') || 'dark',
                get isDark() {
                    if (this.theme === 'system') return window.matchMedia('(prefers-color-scheme: dark)').matches;
                    return this.theme === 'dark';
                },
                setTheme(t) {
                    this.theme = t;
                    localStorage.setItem('nt_theme', t);
                }
            }
        }
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof feather !== 'undefined') feather.replace();
        });
    </script>
    <x-toast />
    @stack('scripts')
</body>
</html>
