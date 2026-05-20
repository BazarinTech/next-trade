@extends('layouts.app')
@section('title', 'System Health — Admin')
@section('page-title', 'System Health')
@section('page-subtitle', 'Platform diagnostics and status')

@section('content')

{{-- Status grid --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    @foreach($checks as $check)
    <div class="rounded-2xl border p-5 flex items-center gap-4"
         :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0
            {{ $check['ok'] ? 'bg-emerald-500/15 border border-emerald-500/25' : 'bg-red-500/15 border border-red-500/25' }}">
            @if($check['ok'])
            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            @else
            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            @endif
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">{{ $check['label'] }}</p>
            <p class="text-xs mt-0.5 {{ $check['ok'] ? 'text-emerald-400' : 'text-red-400' }}">{{ $check['note'] }}</p>
        </div>
        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $check['ok'] ? 'bg-emerald-500/15 text-emerald-400' : 'bg-red-500/15 text-red-400' }}">
            {{ $check['ok'] ? 'OK' : 'FAIL' }}
        </span>
    </div>
    @endforeach
</div>

{{-- Info cards --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="rounded-2xl border p-5" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-xs text-gray-500 mb-1">Environment</p>
        <p class="text-sm font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">{{ config('app.env') }}</p>
        <p class="text-xs mt-1 {{ config('app.debug') ? 'text-amber-400' : 'text-emerald-400' }}">
            Debug: {{ config('app.debug') ? 'ON' : 'OFF' }}
        </p>
    </div>

    <div class="rounded-2xl border p-5" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-xs text-gray-500 mb-1">Queue Driver</p>
        <p class="text-sm font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">{{ $queueDriver }}</p>
        <p class="text-xs mt-1 {{ $queueDriver === 'sync' ? 'text-amber-400' : 'text-cyan-400' }}">
            {{ $queueDriver === 'sync' ? 'Synchronous (no worker needed)' : 'Async — run queue:work' }}
        </p>
    </div>

    <div class="rounded-2xl border p-5" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-xs text-gray-500 mb-1">PHP Version</p>
        <p class="text-sm font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">{{ PHP_VERSION }}</p>
        <p class="text-xs mt-1 text-gray-500">Laravel {{ app()->version() }}</p>
    </div>
</div>

{{-- Scheduler / Maintenance --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div class="rounded-2xl border p-5" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Scheduler Cron</p>
        <code class="block text-xs font-mono px-3 py-2 rounded-lg"
              :class="isDark ? 'bg-gray-800 text-cyan-300' : 'bg-gray-100 text-gray-800'">
            * * * * * php {{ base_path('artisan') }} schedule:run >> /dev/null 2>&1
        </code>
        <p class="text-xs text-gray-500 mt-2">Add this to your server crontab to enable scheduled tasks.</p>
    </div>

    <div class="rounded-2xl border p-5" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Last Operations</p>
        <div class="space-y-2">
            <div class="flex justify-between text-xs">
                <span class="text-gray-500">Last cleanup run</span>
                <span :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $lastCleanup ?? 'Never' }}</span>
            </div>
            <div class="flex justify-between text-xs">
                <span class="text-gray-500">Last reconciliation</span>
                <span :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $lastReco ?? 'Never' }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Quick actions --}}
<div class="rounded-2xl border p-5" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Artisan Commands</p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs font-mono">
        @foreach([
            ['nexttrade:health',           'Run full health check'],
            ['nexttrade:cleanup-pending',  'Clean stale deposits and old ticks'],
            ['nexttrade:cleanup-pending --dry-run', 'Preview cleanup (no changes)'],
            ['nexttrade:reconcile',        'Check wallet balance integrity'],
            ['nexttrade:reconcile --fix',  'Fix wallet balance mismatches'],
            ['bots:process-earnings',      'Credit today\'s bot earnings'],
            ['trades:settle',              'Settle all open expired trades'],
            ['market:ticks',               'Generate one round of price ticks'],
            ['admin:make email@example.com','Promote user to Super Admin'],
            ['storage:link',               'Create public storage symlink'],
        ] as [$cmd, $desc])
        <div class="flex items-start gap-2">
            <code class="px-2 py-1 rounded-lg text-cyan-400 flex-shrink-0"
                  :class="isDark ? 'bg-gray-800' : 'bg-gray-100'">php artisan {{ $cmd }}</code>
        </div>
        @endforeach
    </div>
</div>

@endsection
