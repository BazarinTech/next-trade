@extends('layouts.app')

@section('title', 'Dashboard — Next Trade')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Welcome back, {{ auth()->user()->name }}')

@section('content')

{{-- Quick Action Buttons --}}
<div class="flex flex-wrap items-center gap-3 mb-6">
    <a href="{{ route('wallet.deposit') }}"
       class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white transition-all duration-200"
       style="background: linear-gradient(135deg, #06b6d4, #0891b2); box-shadow: 0 4px 12px rgba(6,182,212,0.25);">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Deposit
    </a>
    <a href="{{ route('wallet.withdraw') }}"
       class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold border transition-all duration-200"
       :class="isDark ? 'text-gray-300 border-gray-700 hover:border-cyan-500/50 bg-gray-900' : 'text-gray-700 border-gray-200 hover:border-cyan-400 bg-white'">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
        Withdraw
    </a>
    <a href="{{ route('trade.index') }}"
       class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold border transition-all duration-200"
       :class="isDark ? 'text-cyan-400 border-cyan-500/30 hover:bg-cyan-500/10 bg-cyan-500/5' : 'text-cyan-600 border-cyan-200 hover:bg-cyan-50 bg-white'">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
        Start Trading
    </a>
    <span class="ml-auto flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-full font-medium
        {{ $walletMode === 'demo' ? 'bg-amber-500/10 border border-amber-500/30 text-amber-400' : 'bg-emerald-500/10 border border-emerald-500/30 text-emerald-400' }}">
        <span class="w-1.5 h-1.5 rounded-full {{ $walletMode === 'demo' ? 'bg-amber-400' : 'bg-emerald-400' }} animate-pulse"></span>
        {{ ucfirst($walletMode) }} Mode
    </span>
</div>

{{-- Stats Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    {{-- Demo Balance --}}
    <div class="rounded-2xl border p-5 transition-all hover:border-amber-500/30"
         :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <span class="text-xs px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20">Demo</span>
        </div>
        <p class="text-xs text-gray-500 mb-1">Demo Balance</p>
        <p class="text-2xl font-bold" :class="isDark ? 'text-white' : 'text-gray-900'">
            ${{ number_format($demoWallet->available_balance, 2) }}
        </p>
        @if($demoWallet->locked_balance > 0)
        <p class="text-xs mt-1 text-amber-400">${{ number_format($demoWallet->locked_balance,2) }} locked</p>
        @else
        <p class="text-xs mt-1 text-gray-500">Available</p>
        @endif
    </div>

    {{-- Live Balance --}}
    <div class="rounded-2xl border p-5 transition-all hover:border-cyan-500/30"
         :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center">
                <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>
            <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Live</span>
        </div>
        <p class="text-xs text-gray-500 mb-1">Live Balance</p>
        <p class="text-2xl font-bold" :class="isDark ? 'text-white' : 'text-gray-900'">
            ${{ number_format($liveWallet->available_balance, 2) }}
        </p>
        @if($liveWallet->balance == 0)
        <p class="text-xs mt-1 text-gray-500">Fund to start</p>
        @else
        <p class="text-xs mt-1 text-gray-500">Available</p>
        @endif
    </div>

    {{-- Today P&L --}}
    <div class="rounded-2xl border p-5 transition-all"
         :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl {{ $isPnlPositive ? 'bg-emerald-500/10 border border-emerald-500/20' : 'bg-red-500/10 border border-red-500/20' }} flex items-center justify-center">
                <svg class="w-4 h-4 {{ $isPnlPositive ? 'text-emerald-400' : 'text-red-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    @if($isPnlPositive)
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    @else
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                    @endif
                </svg>
            </div>
            <span class="text-xs font-semibold {{ $isPnlPositive ? 'text-emerald-400' : 'text-red-400' }}">
                {{ $isPnlPositive ? '+' : '' }}{{ number_format((float)$todayPnl, 2) }}
            </span>
        </div>
        <p class="text-xs text-gray-500 mb-1">Today's P&L</p>
        <p class="text-2xl font-bold {{ $isPnlPositive ? 'text-emerald-400' : 'text-red-400' }}">
            {{ $isPnlPositive ? '+' : '' }}${{ number_format(abs((float)$todayPnl), 2) }}
        </p>
        <p class="text-xs mt-1 text-gray-500">{{ ucfirst($walletMode) }} wallet</p>
    </div>

    {{-- Active Trades --}}
    <div class="rounded-2xl border p-5 transition-all"
         :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center">
                <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <span class="w-2 h-2 rounded-full bg-purple-400 animate-pulse mt-1"></span>
        </div>
        <p class="text-xs text-gray-500 mb-1">Active Trades</p>
        <p class="text-2xl font-bold" :class="isDark ? 'text-white' : 'text-gray-900'">{{ $activeTrades }}</p>
        <p class="text-xs mt-1 text-gray-500">{{ $activeTrades > 0 ? 'Open positions' : 'No open positions' }}</p>
    </div>
</div>

{{-- Bot Portfolio Summary --}}
<div class="rounded-2xl border p-5 mb-4 transition-all"
     :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center">
                <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 3H7a2 2 0 00-2 2v2M9 3h6M9 3v2m6-2h2a2 2 0 012 2v2m0 0V7m0 4h2m-2 0v6m0 0h2m-2 0a2 2 0 01-2 2h-2m0 0V19m0 2H9m0 0H7a2 2 0 01-2-2v-2m0 0H3m2 0V9m0 0H3m2 0a2 2 0 012-2h2m0 0V5"/><rect x="9" y="9" width="6" height="6" rx="1" stroke-width="1.5"/>
                </svg>
            </div>
            <h3 class="text-sm font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">Bot Portfolio</h3>
        </div>
        <a href="{{ route('bots.index') }}" class="text-xs text-cyan-400 hover:text-cyan-300 transition-colors">Manage bots</a>
    </div>
    <div class="grid grid-cols-3 gap-4 mb-4">
        <div>
            <p class="text-xs text-gray-500 mb-0.5">Active Bots</p>
            <p class="text-xl font-bold text-cyan-400">{{ $activeBotInvestments }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-500 mb-0.5">Today's Earnings</p>
            <p class="text-xl font-bold text-emerald-400">+${{ number_format($todayBotEarnings, 4) }}</p>
        </div>
        <div class="flex items-center justify-end">
            <a href="{{ route('bots.earnings') }}" class="text-xs text-gray-500 hover:text-cyan-400 transition-colors">View earnings →</a>
        </div>
    </div>
    @if($recentBotEarnings->isNotEmpty())
    <div class="space-y-2 border-t pt-3" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
        @foreach($recentBotEarnings as $be)
        <div class="flex items-center justify-between text-xs">
            <span class="text-gray-500">{{ $be->botPlan->name }}</span>
            <span class="font-semibold text-emerald-400">+${{ number_format($be->amount, 4) }}</span>
        </div>
        @endforeach
    </div>
    @elseif($activeBotInvestments === 0)
    <p class="text-xs text-gray-600 border-t pt-3" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
        No active bot investments. <a href="{{ route('bots.index') }}" class="text-cyan-400 hover:text-cyan-300">Start investing →</a>
    </p>
    @endif
</div>

{{-- Chart + Recent Transactions --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-4">

    {{-- Chart Placeholder --}}
    <div class="xl:col-span-2 rounded-2xl border p-5"
         :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">BTC/USDT</h3>
                <p class="text-xs text-gray-500">Bitcoin / Tether</p>
            </div>
            <div class="flex items-center gap-1.5">
                @foreach(['1m','5m','1h','4h','1d'] as $tf)
                <button class="text-xs px-2.5 py-1 rounded-lg font-medium transition-colors {{ $tf === '1h' ? 'bg-cyan-500/15 text-cyan-400 border border-cyan-500/20' : '' }}"
                        :class="'{{ $tf }}' !== '1h' ? (isDark ? 'text-gray-500 hover:text-white hover:bg-gray-800' : 'text-gray-400 hover:bg-gray-100') : ''">
                    {{ $tf }}
                </button>
                @endforeach
            </div>
        </div>
        <div class="relative h-56 rounded-xl overflow-hidden"
             :class="isDark ? 'bg-gray-950/60' : 'bg-gray-50'"
             style="background-image: repeating-linear-gradient(0deg,transparent,transparent 39px,rgba(6,182,212,0.04) 40px),repeating-linear-gradient(90deg,transparent,transparent 79px,rgba(6,182,212,0.04) 80px)">
            <svg class="absolute inset-0 w-full h-full" viewBox="0 0 600 200" preserveAspectRatio="none">
                <defs>
                    <linearGradient id="cg" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#06b6d4" stop-opacity="0.15"/>
                        <stop offset="100%" stop-color="#06b6d4" stop-opacity="0"/>
                    </linearGradient>
                </defs>
                <path d="M0,160 C20,155 40,120 80,100 C120,80 140,130 180,110 C220,90 240,60 280,70 C320,80 340,50 380,40 C420,30 440,60 480,50 C520,40 560,30 600,20 L600,200 L0,200 Z" fill="url(#cg)"/>
                <path d="M0,160 C20,155 40,120 80,100 C120,80 140,130 180,110 C220,90 240,60 280,70 C320,80 340,50 380,40 C420,30 440,60 480,50 C520,40 560,30 600,20" fill="none" stroke="#06b6d4" stroke-width="1.5"/>
                <circle cx="600" cy="20" r="3" fill="#06b6d4"/>
            </svg>
            <div class="absolute top-3 left-3">
                <p class="text-xl font-bold text-cyan-400">$67,842.50</p>
                <p class="text-xs text-emerald-400 font-medium">+2.34% today</p>
            </div>
            <div class="absolute bottom-3 right-3 text-xs text-gray-600">Live chart — Phase 3</div>
        </div>
    </div>

    {{-- Recent Activity --}}
    <div class="rounded-2xl border p-5"
         :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">Recent Activity</h3>
            <a href="{{ route('transactions.index') }}" class="text-xs text-cyan-400 hover:text-cyan-300 transition-colors">View all</a>
        </div>

        @if($recentTransactions->isEmpty())
        <div class="flex flex-col items-center justify-center py-8 text-center">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-2"
                 :class="isDark ? 'bg-gray-800' : 'bg-gray-100'">
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <p class="text-xs text-gray-500">No activity yet</p>
        </div>
        @else
        <div class="space-y-2.5">
            @foreach($recentTransactions as $txn)
            @php $isCredit = $txn->isCredit(); @endphp
            <div class="flex items-center gap-3 p-2.5 rounded-xl transition-colors"
                 :class="isDark ? 'hover:bg-gray-800/50' : 'hover:bg-gray-50'">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0
                    {{ $isCredit ? 'bg-emerald-500/10 border border-emerald-500/20' : 'bg-red-500/10 border border-red-500/20' }}">
                    <svg class="w-3.5 h-3.5 {{ $isCredit ? 'text-emerald-400' : 'text-red-400' }}"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if($isCredit)
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                        @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                        @endif
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium truncate" :class="isDark ? 'text-gray-300' : 'text-gray-700'">
                        {{ $txn->getTypeLabel() }}
                    </p>
                    <p class="text-xs text-gray-500">{{ $txn->created_at->diffForHumans() }}</p>
                </div>
                <span class="text-xs font-bold flex-shrink-0 {{ $isCredit ? 'text-emerald-400' : 'text-red-400' }}">
                    {{ $txn->getSignedAmount() }}
                </span>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- Recent Trades Table --}}
<div class="rounded-2xl border"
     :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
    <div class="flex items-center justify-between p-5 border-b"
         :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
        <h3 class="text-sm font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">Recent Trades</h3>
        <a href="{{ route('trade.index') }}" class="text-xs text-cyan-400 hover:text-cyan-300 transition-colors">View all</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
                    @foreach(['Pair','Type','Amount','Open Price','Close Price','P&L','Status','Time'] as $col)
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 whitespace-nowrap">{{ $col }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @if($recentTrades->isEmpty())
                <tr>
                    <td colspan="8" class="px-5 py-12 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center"
                                 :class="isDark ? 'bg-gray-800' : 'bg-gray-100'">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                            </div>
                            <p class="text-sm text-gray-500 font-medium">No trades yet</p>
                            <a href="{{ route('trade.index') }}" class="text-xs text-cyan-400 hover:text-cyan-300 transition-colors">Open your first trade →</a>
                        </div>
                    </td>
                </tr>
                @else
                @foreach($recentTrades as $trade)
                @php
                    $isWon = $trade->status === 'won';
                    $isDraw = $trade->status === 'draw';
                    $pnlColor = $isWon ? 'text-emerald-400' : ($isDraw ? 'text-amber-400' : 'text-red-400');
                    $statusColor = match($trade->status) {
                        'won'  => 'bg-emerald-500/10 text-emerald-400',
                        'lost' => 'bg-red-500/10 text-red-400',
                        'draw' => 'bg-amber-500/10 text-amber-400',
                        default => 'bg-gray-500/10 text-gray-400',
                    };
                @endphp
                <tr class="border-b last:border-0 transition-colors"
                    :class="isDark ? 'border-gray-800/40 hover:bg-gray-800/30' : 'border-gray-50 hover:bg-gray-50'">
                    <td class="px-5 py-3 text-xs font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">
                        {{ $trade->tradingAsset->symbol }}
                    </td>
                    <td class="px-5 py-3">
                        <span class="text-xs font-bold {{ $trade->direction === 'buy' ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ strtoupper($trade->direction) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-xs font-mono text-gray-500">
                        ${{ number_format($trade->stake_amount, 2) }}
                    </td>
                    <td class="px-5 py-3 text-xs font-mono text-gray-500">
                        {{ $trade->tradingAsset->formatPrice($trade->entry_price) }}
                    </td>
                    <td class="px-5 py-3 text-xs font-mono text-gray-500">
                        {{ $trade->exit_price ? $trade->tradingAsset->formatPrice($trade->exit_price) : '—' }}
                    </td>
                    <td class="px-5 py-3">
                        <span class="text-xs font-semibold font-mono {{ $pnlColor }}">
                            {{ $trade->profit_loss >= 0 ? '+' : '' }}${{ number_format(abs($trade->profit_loss ?? 0), 2) }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase {{ $statusColor }}">
                            {{ $trade->status }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-500">
                        {{ $trade->closed_at?->diffForHumans() ?? '—' }}
                    </td>
                </tr>
                @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>

@endsection
