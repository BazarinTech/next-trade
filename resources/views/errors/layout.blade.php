<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') — Next Trade</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen flex items-center justify-center p-6">
    <div class="text-center max-w-md">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gray-900 border border-gray-800 mb-6">
            <span class="text-2xl font-bold @yield('color', 'text-cyan-400')">@yield('code')</span>
        </div>
        <h1 class="text-2xl font-bold text-white mb-2">@yield('title')</h1>
        <p class="text-gray-500 text-sm mb-8">@yield('description')</p>
        <div class="flex items-center justify-center gap-3">
            @if(url()->previous() && url()->previous() !== url()->current())
            <a href="{{ url()->previous() }}"
               class="px-5 py-2.5 rounded-xl border border-gray-700 text-gray-400 hover:text-white text-sm font-medium transition-colors">
                Go back
            </a>
            @endif
            <a href="{{ auth()->check() ? route('dashboard') : route('login') }}"
               class="px-5 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-black text-sm font-semibold transition-colors">
                @if(auth()->check()) Dashboard @else Login @endif
            </a>
        </div>
        <p class="mt-8 text-xs text-gray-700">Next<span class="text-cyan-500">Trade</span> · Educational Simulation Platform</p>
    </div>
</body>
</html>
