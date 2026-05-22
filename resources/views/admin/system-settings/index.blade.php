@extends('layouts.app')
@section('title', 'System Settings | Admin')
@section('page-title', 'System Settings')
@section('page-subtitle', 'Platform-wide configuration and feature flags')

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">{{ session('error') }}</div>
@endif

<form method="POST" action="{{ route('admin.system-settings.update') }}" class="space-y-6">
    @csrf

    @foreach($groups as $group => $settings)
    <div class="rounded-2xl border overflow-hidden" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <div class="px-5 py-4 border-b" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
            <p class="text-sm font-semibold capitalize" :class="isDark ? 'text-white' : 'text-gray-900'">{{ str_replace('_', ' ', $group ?? 'General') }}</p>
        </div>
        <div class="divide-y" :class="isDark ? 'divide-gray-800/60' : 'divide-gray-100'">
            @foreach($settings as $setting)
            <div class="px-5 py-4 flex items-center justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium" :class="isDark ? 'text-white' : 'text-gray-900'">{{ $setting->name ?? ucwords(str_replace('_', ' ', $setting->key)) }}</p>
                    @if($setting->description)
                    <p class="text-xs text-gray-500 mt-0.5">{{ $setting->description }}</p>
                    @endif
                    <p class="text-xs font-mono text-gray-600 mt-0.5">{{ $setting->key }}</p>
                </div>
                <div class="flex-shrink-0">
                    @if($setting->type === 'boolean')
                    {{-- Toggle --}}
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="{{ $setting->key }}" value="0">
                        <input type="checkbox" name="{{ $setting->key }}" value="1"
                            {{ $setting->value === '1' ? 'checked' : '' }}
                            class="sr-only peer">
                        <div class="w-11 h-6 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cyan-500 transition-colors"
                            :class="isDark ? 'bg-gray-700' : 'bg-gray-300'"></div>
                    </label>
                    @elseif($setting->type === 'number')
                    <input type="number" name="{{ $setting->key }}" value="{{ $setting->value }}"
                        step="any" min="0"
                        class="w-36 px-3 py-1.5 rounded-xl border text-sm text-right focus:outline-none focus:ring-2 focus:ring-cyan-500/40"
                        :class="isDark ? 'bg-gray-800 border-gray-700 text-white' : 'bg-white border-gray-300 text-gray-900'">
                    @else
                    <input type="text" name="{{ $setting->key }}" value="{{ $setting->value }}"
                        class="w-48 px-3 py-1.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40"
                        :class="isDark ? 'bg-gray-800 border-gray-700 text-white' : 'bg-white border-gray-300 text-gray-900'">
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

    <div class="flex justify-end pt-2">
        <button type="submit" class="px-6 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-black text-sm font-semibold transition-colors">
            Save All Settings
        </button>
    </div>
</form>

@endsection
