<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Next Trade'))</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: { cyan: { 400:'#22d3ee',500:'#06b6d4',600:'#0891b2' } },
                    fontFamily: { sans: ['Inter','system-ui','sans-serif'] },
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', system-ui, sans-serif; }
        .glassmorphism { backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); }
        .input-field {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            transition: all 0.2s ease;
        }
        .input-field:focus {
            border-color: #06b6d4;
            box-shadow: 0 0 0 3px rgba(6,182,212,0.1);
            outline: none;
        }
    </style>
    @stack('head')
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen flex flex-col justify-center items-center p-4"
      style="background-image: radial-gradient(ellipse at 20% 50%, rgba(6,182,212,0.05) 0%, transparent 60%), radial-gradient(ellipse at 80% 20%, rgba(6,182,212,0.03) 0%, transparent 50%);">

    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2">
                <div class="w-10 h-10 rounded-xl bg-cyan-500 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <span class="text-xl font-bold text-white">Next<span class="text-cyan-400">Trade</span></span>
            </a>
        </div>

        {{ $slot }}
    </div>

    <p class="mt-8 text-xs text-gray-600">&copy; {{ date('Y') }} NextTrade. All rights reserved.</p>

    @stack('scripts')
</body>
</html>
