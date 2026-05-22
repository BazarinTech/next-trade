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

<form method="POST" action="{{ route('admin.system-settings.update') }}" class="space-y-6" enctype="multipart/form-data">
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
                    @if($setting->key === 'site_logo_url')
                    {{-- Logo upload --}}
                    <div class="flex items-center gap-3">
                        @if($setting->value)
                        <div class="w-10 h-10 rounded-xl overflow-hidden border border-gray-700 flex-shrink-0 bg-gray-800 flex items-center justify-center">
                            <img src="{{ $setting->value }}" alt="Logo" class="w-full h-full object-contain">
                        </div>
                        @endif
                        <div class="flex flex-col gap-1">
                            <label class="flex items-center gap-2 px-3 py-1.5 rounded-xl border border-dashed border-cyan-500/40 bg-cyan-500/5 cursor-pointer hover:bg-cyan-500/10 transition-colors">
                                <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                <span class="text-xs font-medium text-cyan-400">Upload image</span>
                                <input type="file" name="site_logo_file" accept="image/png,image/jpeg,image/svg+xml,image/webp" class="sr-only"
                                       onchange="this.parentElement.querySelector('span').textContent = this.files[0]?.name ?? 'Upload image'">
                            </label>
                            @if($setting->value)
                            <button type="button" onclick="document.getElementById('remove_logo').value='1';this.closest('.flex').querySelector('img')?.remove();this.remove();"
                                    class="text-xs text-red-400 hover:text-red-300 text-left px-1">Remove logo</button>
                            <input type="hidden" id="remove_logo" name="remove_logo" value="0">
                            @endif
                        </div>
                    </div>
                    @elseif($setting->type === 'boolean')
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
