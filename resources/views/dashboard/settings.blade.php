@extends('layouts.trading')
@section('title', 'Settings | Next Trade')
@section('page-title', 'Settings')
@section('page-subtitle', 'Manage your preferences')

@section('content')
<div class="max-w-2xl">
    <!-- Theme Settings -->
    <div class="rounded-2xl border p-6 mb-4" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <h3 class="text-sm font-semibold mb-4" :class="isDark ? 'text-white' : 'text-gray-900'">Appearance</h3>
        <div class="grid grid-cols-3 gap-3">
            @foreach([['dark','Dark Mode','M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z'],['light','Light Mode','M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z'],['system','System','M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z']] as [$val, $label, $path])
            <button @click="setTheme('{{ $val }}')"
                    class="flex flex-col items-center gap-2 p-4 rounded-xl border transition-all duration-200"
                    :class="theme === '{{ $val }}'
                        ? 'border-cyan-500/50 bg-cyan-500/10 text-cyan-400'
                        : (isDark ? 'border-gray-800 text-gray-500 hover:border-gray-700 hover:text-gray-300' : 'border-gray-200 text-gray-500 hover:border-gray-300')">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $path }}"/>
                </svg>
                <span class="text-xs font-medium">{{ $label }}</span>
            </button>
            @endforeach
        </div>
    </div>

    <!-- Notification Settings -->
    <div class="rounded-2xl border p-6" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <h3 class="text-sm font-semibold mb-4" :class="isDark ? 'text-white' : 'text-gray-900'">Notifications</h3>
        @foreach(['Trade opened/closed','Deposit confirmed','Withdrawal processed','Price alerts'] as $notif)
        <div class="flex items-center justify-between py-3 border-b last:border-0" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
            <span class="text-sm" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $notif }}</span>
            <button x-data="{ on: true }" @click="on = !on"
                    class="relative w-10 h-5 rounded-full transition-all duration-200 flex-shrink-0"
                    :class="on ? 'bg-cyan-500' : (isDark ? 'bg-gray-700' : 'bg-gray-200')">
                <span class="absolute top-0.5 w-4 h-4 rounded-full bg-white shadow transition-all duration-200"
                      :class="on ? 'left-5' : 'left-0.5'"></span>
            </button>
        </div>
        @endforeach
    </div>
</div>
@endsection
