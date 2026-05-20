@extends('layouts.app')
@section('title', 'Admin Dashboard — Next Trade')
@section('page-title', 'Admin Dashboard')
@section('page-subtitle', 'Platform overview and management')

@section('content')
<!-- Stats Row -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
        $diffColor = $activeConfig?->difficulty_color ?? 'cyan';
        $stats = [
            ['label' => 'Total Users',    'value' => $totalUsers,    'icon' => 'users',       'color' => 'cyan',    'sub' => 'registered accounts'],
            ['label' => 'Active Assets',  'value' => $activeAssets,  'icon' => 'database',    'color' => 'emerald', 'sub' => 'tradeable instruments'],
            ['label' => 'Open Trades',    'value' => $openTrades,    'icon' => 'trending-up', 'color' => 'amber',   'sub' => 'currently active'],
            ['label' => 'Settled Today',  'value' => $settledToday,  'icon' => 'check-circle','color' => 'purple',  'sub' => 'trades closed today'],
        ];
    @endphp
    @foreach($stats as $stat)
    <div class="rounded-2xl border p-5 transition-all duration-200 hover:border-{{ $stat['color'] }}-500/30"
         :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl bg-{{ $stat['color'] }}-500/10 border border-{{ $stat['color'] }}-500/20 flex items-center justify-center">
                <svg class="w-4 h-4 text-{{ $stat['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    @switch($stat['icon'])
                        @case('users')<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>@break
                        @case('database')<ellipse cx="12" cy="5" rx="9" ry="3" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12c0 1.66-4.03 3-9 3S3 13.66 3 12"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5v14c0 1.66 4.03 3 9 3s9-1.34 9-3V5"/>@break
                        @case('trending-up')<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>@break
                        @case('check-circle')<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>@break
                    @endswitch
                </svg>
            </div>
        </div>
        <p class="text-xs font-medium mb-1 text-gray-500">{{ $stat['label'] }}</p>
        <p class="text-2xl font-bold" :class="isDark ? 'text-white' : 'text-gray-900'">{{ $stat['value'] }}</p>
        <p class="text-xs mt-1 text-{{ $stat['color'] }}-400">{{ $stat['sub'] }}</p>
    </div>
    @endforeach
</div>

<!-- Users / Bots / Withdrawals quick stats -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="rounded-2xl border p-4" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-xs text-gray-500 mb-1">Active Users</p>
        <p class="text-xl font-bold text-emerald-400">{{ $activeUsers }}</p>
    </div>
    <div class="rounded-2xl border p-4" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-xs text-gray-500 mb-1">Banned Users</p>
        <p class="text-xl font-bold text-red-400">{{ $bannedUsers }}</p>
    </div>
    <div class="rounded-2xl border p-4" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-xs text-gray-500 mb-1">Active Bot Investments</p>
        <p class="text-xl font-bold text-violet-400">{{ $activeBotsCount }}</p>
    </div>
    <div class="rounded-2xl border p-4" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-xs text-gray-500 mb-1">Withdrawals Locked</p>
        <p class="text-xl font-bold text-amber-400">${{ number_format($withdrawalsLocked, 2) }}</p>
    </div>
</div>

<!-- Active Difficulty Banner -->
@if($activeConfig)
<div class="rounded-2xl border border-{{ $diffColor }}-500/30 bg-{{ $diffColor }}-500/5 p-4 mb-6 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-{{ $diffColor }}-500/20 flex items-center justify-center">
            <svg class="w-4 h-4 text-{{ $diffColor }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </div>
        <div>
            <p class="text-sm font-semibold text-{{ $diffColor }}-400">Active Difficulty: {{ $activeConfig->difficulty_label }}</p>
            <p class="text-xs text-gray-500 mt-0.5">
                Win rate: {{ $activeConfig->win_probability }}% &bull;
                Payout: {{ $activeConfig->payout_range }} &bull;
                Candle: {{ $activeConfig->candle_speed_seconds }}s
            </p>
        </div>
    </div>
    <a href="{{ route('admin.trading-engine') }}" class="text-xs px-3 py-1.5 rounded-lg bg-{{ $diffColor }}-500/20 text-{{ $diffColor }}-400 hover:bg-{{ $diffColor }}-500/30 transition-colors font-medium">
        Manage Engine
    </a>
</div>
@endif

<!-- Deposit Stats Row -->
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <div class="rounded-2xl border p-5" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-xs text-gray-500 mb-1">Pending Deposits</p>
        <p class="text-2xl font-bold text-amber-400">{{ $depositsPending }}</p>
        <a href="{{ route('admin.deposits', ['status' => 'pending']) }}" class="text-[10px] text-amber-400/60 hover:text-amber-400 transition-colors">View all →</a>
    </div>
    <div class="rounded-2xl border p-5" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-xs text-gray-500 mb-1">Successful Today</p>
        <p class="text-2xl font-bold text-emerald-400">{{ $depositsSuccessToday }}</p>
        <p class="text-[10px] text-gray-600 mt-0.5">deposits credited</p>
    </div>
    <div class="rounded-2xl border p-5" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-xs text-gray-500 mb-1">Failed Today</p>
        <p class="text-2xl font-bold text-red-400">{{ $depositsFailedToday }}</p>
        <p class="text-[10px] text-gray-600 mt-0.5">failed transactions</p>
    </div>
    <div class="rounded-2xl border p-5" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-xs text-gray-500 mb-1">KES Today</p>
        <p class="text-xl font-bold" :class="isDark ? 'text-white' : 'text-gray-900'">{{ number_format($depositKesToday, 0) }}</p>
        <p class="text-[10px] text-gray-600 mt-0.5">shillings collected</p>
    </div>
    <div class="rounded-2xl border p-5" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-xs text-gray-500 mb-1">USD Today</p>
        <p class="text-xl font-bold text-emerald-400">${{ number_format($depositUsdToday, 2) }}</p>
        <p class="text-[10px] text-gray-600 mt-0.5">credited to wallets</p>
    </div>
</div>

<!-- Withdrawal Stats Row -->
<div class="grid grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
    <div class="rounded-2xl border p-5" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-xs text-gray-500 mb-1">Pending Withdrawals</p>
        <p class="text-2xl font-bold text-amber-400">{{ $withdrawalsPending }}</p>
        <a href="{{ route('admin.withdrawals', ['status' => 'pending']) }}" class="text-[10px] text-amber-400/60 hover:text-amber-400 transition-colors">Review →</a>
    </div>
    <div class="rounded-2xl border p-5" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-xs text-gray-500 mb-1">Processing</p>
        <p class="text-2xl font-bold text-blue-400">{{ $withdrawalsProcessing }}</p>
        <p class="text-[10px] text-gray-600 mt-0.5">being sent</p>
    </div>
    <div class="rounded-2xl border p-5" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-xs text-gray-500 mb-1">Done Today</p>
        <p class="text-2xl font-bold text-emerald-400">{{ $withdrawalsSuccessToday }}</p>
        <p class="text-[10px] text-gray-600 mt-0.5">withdrawals completed</p>
    </div>
    <div class="rounded-2xl border p-5" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-xs text-gray-500 mb-1">Rejected Today</p>
        <p class="text-2xl font-bold text-red-400">{{ $withdrawalsRejectedToday }}</p>
        <p class="text-[10px] text-gray-600 mt-0.5">requests rejected</p>
    </div>
    <div class="rounded-2xl border p-5" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-xs text-gray-500 mb-1">USD Out Today</p>
        <p class="text-xl font-bold text-emerald-400">${{ number_format($withdrawalsUsdToday, 2) }}</p>
        <p class="text-[10px] text-gray-600 mt-0.5">paid out</p>
    </div>
    <div class="rounded-2xl border p-5" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-xs text-gray-500 mb-1">Total Locked</p>
        <p class="text-xl font-bold text-amber-400">${{ number_format($withdrawalsLocked, 2) }}</p>
        <p class="text-[10px] text-gray-600 mt-0.5">awaiting payout</p>
    </div>
</div>

<!-- Bottom Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <!-- Recent Users -->
    <div class="rounded-2xl border" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <div class="flex items-center justify-between p-5 border-b" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
            <h3 class="text-sm font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">Recent Users</h3>
            <a href="{{ route('admin.users') }}" class="text-xs text-cyan-400 hover:text-cyan-300 transition-colors">Manage all</a>
        </div>
        <div class="divide-y" :class="isDark ? 'divide-gray-800/40' : 'divide-gray-50'">
            @forelse($recentUsers as $user)
            <div class="flex items-center gap-3 px-5 py-3">
                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-cyan-500 to-cyan-700 flex items-center justify-center flex-shrink-0">
                    <span class="text-xs font-bold text-white">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium truncate" :class="isDark ? 'text-white' : 'text-gray-900'">{{ $user->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ $user->email }}</p>
                </div>
                @if($user->is_banned)
                <span class="text-[10px] px-2 py-0.5 rounded-full bg-red-500/10 text-red-400 border border-red-500/20 flex-shrink-0">Banned</span>
                @else
                <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex-shrink-0">Active</span>
                @endif
            </div>
            @empty
            <p class="px-5 py-8 text-center text-sm text-gray-500">No users yet.</p>
            @endforelse
        </div>
    </div>

    <!-- Recent Admin Activity -->
    <div class="rounded-2xl border" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <div class="flex items-center justify-between p-5 border-b" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
            <h3 class="text-sm font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">Recent Admin Activity</h3>
            <a href="{{ route('admin.trading-engine') }}" class="text-xs text-cyan-400 hover:text-cyan-300 transition-colors">View all</a>
        </div>
        <div class="divide-y" :class="isDark ? 'divide-gray-800/40' : 'divide-gray-50'">
            @forelse($recentActivity as $log)
            <div class="flex items-start gap-3 px-5 py-3">
                <div class="w-7 h-7 rounded-full bg-gray-800 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <span class="text-xs font-bold text-gray-400">{{ strtoupper(substr($log->admin?->name ?? 'S', 0, 1)) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $log->action_label }}</p>
                    <p class="text-[10px] text-gray-500 mt-0.5">by {{ $log->admin?->name ?? 'System' }} &bull; {{ $log->created_at->diffForHumans() }}</p>
                </div>
            </div>
            @empty
            <p class="px-5 py-8 text-center text-sm text-gray-500">No admin activity yet.</p>
            @endforelse
        </div>
    </div>

</div>

<!-- Recent User Activity -->
<div class="mt-6 rounded-2xl border overflow-hidden" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
    <div class="flex items-center justify-between p-5 border-b" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
        <h3 class="text-sm font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">Recent User Activity</h3>
        <a href="{{ route('admin.audit-logs') }}" class="text-xs text-cyan-400 hover:text-cyan-300 transition-colors">View audit logs →</a>
    </div>
    <div class="divide-y" :class="isDark ? 'divide-gray-800/40' : 'divide-gray-50'">
        @forelse($recentUserActivity as $log)
        <div class="flex items-center gap-3 px-5 py-3">
            <div class="w-7 h-7 rounded-full bg-gray-800 flex items-center justify-center flex-shrink-0">
                <span class="text-xs font-bold text-gray-400">{{ strtoupper(substr($log->user?->name ?? '?', 0, 1)) }}</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-medium" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $log->user?->name ?? 'Guest' }}</p>
                <p class="text-[10px] text-gray-500">{{ ucwords(str_replace('_', ' ', $log->action)) }} {{ $log->description ? '— ' . $log->description : '' }}</p>
            </div>
            <span class="text-[10px] text-gray-600 whitespace-nowrap">{{ $log->created_at->diffForHumans() }}</span>
        </div>
        @empty
        <p class="px-5 py-6 text-center text-sm text-gray-500">No user activity logged yet.</p>
        @endforelse
    </div>
</div>
@endsection
