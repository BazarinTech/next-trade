@extends('layouts.trading')
@section('title', 'Wallet — Next Trade')
@section('page-title', 'Wallet')
@section('page-subtitle', 'Manage your balances and funds')

@section('content')

{{-- Wallet Mode Toggle --}}
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div class="flex rounded-xl p-1 gap-1"
         :class="isDark ? 'bg-gray-900/80 border border-gray-800/60' : 'bg-gray-100 border border-gray-200'">
        @foreach(['demo', 'live'] as $m)
        <form method="POST" action="{{ route('wallet.mode') }}" class="inline">
            @csrf
            <input type="hidden" name="mode" value="{{ $m }}">
            <button type="submit"
                    class="px-5 py-2 rounded-lg text-sm font-semibold transition-all duration-200 {{ $walletMode === $m ? 'text-white shadow' : '' }}"
                    style="{{ $walletMode === $m ? 'background: linear-gradient(135deg, #06b6d4, #0891b2); box-shadow: 0 2px 10px rgba(6,182,212,0.3);' : '' }}"
                    :class="'{{ $walletMode !== $m ? '' : '' }}' || ('{{ $walletMode !== $m }}' === 'true' ? (isDark ? 'text-gray-400 hover:text-white' : 'text-gray-500 hover:text-gray-900') : '')">
                {{ ucfirst($m) }} Wallet
            </button>
        </form>
        @endforeach
    </div>

    <div class="flex items-center gap-2">
        <a href="{{ route('wallet.deposit') }}"
           class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white transition-all"
           style="background: linear-gradient(135deg, #06b6d4, #0891b2); box-shadow: 0 4px 12px rgba(6,182,212,0.25);">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Deposit
        </a>
        <a href="{{ route('wallet.withdraw') }}"
           class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold border transition-all"
           :class="isDark ? 'text-gray-300 border-gray-700 hover:border-cyan-500/40 bg-gray-900' : 'text-gray-700 border-gray-200 hover:border-cyan-400 bg-white'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
            Withdraw
        </a>
    </div>
</div>

{{-- Wallet Cards --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">

    {{-- Demo Wallet --}}
    <div class="rounded-2xl border p-6 relative overflow-hidden transition-all duration-300 {{ $walletMode === 'demo' ? 'ring-2 ring-cyan-500/40' : '' }}"
         :class="isDark ? 'bg-gray-900/70 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'"
         style="background-image: radial-gradient(ellipse at 90% 10%, rgba(245,158,11,0.07) 0%, transparent 60%)">

        <div class="flex items-start justify-between mb-5">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-amber-400">Demo Wallet</p>
                    <p class="text-xs text-gray-500">Practice • No real money</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if($walletMode === 'demo')
                    <span class="flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span>Active
                    </span>
                @endif
                @if($demoWallet->isFrozen())
                    <span class="text-xs px-2.5 py-1 rounded-full bg-red-500/10 border border-red-500/20 text-red-400">Frozen</span>
                @else
                    <span class="text-xs px-2.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">Active</span>
                @endif
            </div>
        </div>

        <div class="mb-5">
            <p class="text-xs text-gray-500 mb-1">Available Balance</p>
            <p class="text-3xl font-bold" :class="isDark ? 'text-white' : 'text-gray-900'">
                ${{ number_format($demoWallet->available_balance, 2) }}
            </p>
            @if($demoWallet->locked_balance > 0)
            <p class="text-xs text-amber-400 mt-1">
                ${{ number_format($demoWallet->locked_balance, 2) }} locked in trades
            </p>
            @endif
        </div>

        <div class="grid grid-cols-2 gap-3 mb-5">
            @foreach([
                ['Total Balance', '$'.number_format($demoWallet->balance,2), 'text-gray-400'],
                ['Locked',        '$'.number_format($demoWallet->locked_balance,2), 'text-amber-400'],
                ['Total Profit',  '$'.number_format($demoWallet->total_profit,2), 'text-emerald-400'],
                ['Total Loss',    '$'.number_format($demoWallet->total_loss,2), 'text-red-400'],
            ] as [$label, $value, $color])
            <div class="rounded-xl p-3" :class="isDark ? 'bg-gray-800/60' : 'bg-gray-50'">
                <p class="text-xs text-gray-500 mb-0.5">{{ $label }}</p>
                <p class="text-sm font-semibold {{ $color }}">{{ $value }}</p>
            </div>
            @endforeach
        </div>

        {{-- Net P&L --}}
        @php $netPnl = $demoWallet->net_pnl; $pnlPositive = bccomp($netPnl,'0',2) >= 0; @endphp
        <div class="rounded-xl p-3 mb-4 border" :class="isDark ? 'bg-gray-800/40 border-gray-700/50' : 'bg-gray-50 border-gray-200'">
            <p class="text-xs text-gray-500 mb-0.5">Net P&L</p>
            <p class="text-base font-bold {{ $pnlPositive ? 'text-emerald-400' : 'text-red-400' }}">
                {{ $pnlPositive ? '+' : '' }}${{ number_format(abs($netPnl), 2) }}
            </p>
        </div>

        {{-- Reset Demo --}}
        <form method="POST" action="{{ route('wallet.demo.reset') }}"
              x-data="{ confirming: false }"
              @submit.prevent="confirming ? $el.submit() : (confirming = true)"
              class="mt-auto">
            @csrf
            <button type="submit"
                    class="w-full py-2.5 rounded-xl text-sm font-semibold border transition-all duration-200"
                    :class="[
                        confirming
                            ? 'border-amber-500/50 text-amber-400 bg-amber-500/10'
                            : (isDark ? 'border-gray-700 text-gray-400 hover:border-amber-500/40 hover:text-amber-400' : 'border-gray-200 text-gray-600 hover:border-amber-400 hover:text-amber-500')
                    ]">
                <span x-show="!confirming">Reset Demo to $10,000</span>
                <span x-show="confirming" x-cloak>Confirm Reset? Click again to confirm</span>
            </button>
        </form>
    </div>

    {{-- Live Wallet --}}
    <div class="rounded-2xl border p-6 relative overflow-hidden transition-all duration-300 {{ $walletMode === 'live' ? 'ring-2 ring-cyan-500/40' : '' }}"
         :class="isDark ? 'bg-gray-900/70 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'"
         style="background-image: radial-gradient(ellipse at 90% 10%, rgba(6,182,212,0.07) 0%, transparent 60%)">

        <div class="flex items-start justify-between mb-5">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-cyan-400">Live Wallet</p>
                    <p class="text-xs text-gray-500">Real funds • Real trades</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if($walletMode === 'live')
                    <span class="flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span>Active
                    </span>
                @endif
                @if($liveWallet->isFrozen())
                    <span class="text-xs px-2.5 py-1 rounded-full bg-red-500/10 border border-red-500/20 text-red-400">Frozen</span>
                @else
                    <span class="text-xs px-2.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">Active</span>
                @endif
            </div>
        </div>

        <div class="mb-5">
            <p class="text-xs text-gray-500 mb-1">Available Balance</p>
            <p class="text-3xl font-bold" :class="isDark ? 'text-white' : 'text-gray-900'">
                ${{ number_format($liveWallet->available_balance, 2) }}
            </p>
            @if($liveWallet->locked_balance > 0)
            <p class="text-xs text-amber-400 mt-1">${{ number_format($liveWallet->locked_balance, 2) }} locked</p>
            @endif
        </div>

        <div class="grid grid-cols-2 gap-3 mb-5">
            @foreach([
                ['Total Deposited', '$'.number_format($liveWallet->total_deposited,2), 'text-cyan-400'],
                ['Total Withdrawn', '$'.number_format($liveWallet->total_withdrawn,2), 'text-gray-400'],
                ['Total Profit',    '$'.number_format($liveWallet->total_profit,2), 'text-emerald-400'],
                ['Total Loss',      '$'.number_format($liveWallet->total_loss,2), 'text-red-400'],
            ] as [$label, $value, $color])
            <div class="rounded-xl p-3" :class="isDark ? 'bg-gray-800/60' : 'bg-gray-50'">
                <p class="text-xs text-gray-500 mb-0.5">{{ $label }}</p>
                <p class="text-sm font-semibold {{ $color }}">{{ $value }}</p>
            </div>
            @endforeach
        </div>

        @php $liveNetPnl = $liveWallet->net_pnl; $livePnlPositive = bccomp($liveNetPnl,'0',2) >= 0; @endphp
        <div class="rounded-xl p-3 mb-4 border" :class="isDark ? 'bg-gray-800/40 border-gray-700/50' : 'bg-gray-50 border-gray-200'">
            <p class="text-xs text-gray-500 mb-0.5">Net P&L</p>
            <p class="text-base font-bold {{ $livePnlPositive ? 'text-emerald-400' : 'text-red-400' }}">
                {{ $livePnlPositive ? '+' : '' }}${{ number_format(abs($liveNetPnl), 2) }}
            </p>
        </div>

        <div class="flex gap-2 mt-auto">
            <a href="{{ route('wallet.deposit') }}"
               class="flex-1 text-center py-2.5 rounded-xl text-sm font-semibold text-white transition-all"
               style="background: linear-gradient(135deg, #06b6d4, #0891b2);">
                Deposit
            </a>
            <a href="{{ route('wallet.withdraw') }}"
               class="flex-1 text-center py-2.5 rounded-xl text-sm font-semibold border transition-all"
               :class="isDark ? 'border-gray-700 text-gray-300 hover:border-cyan-500/40' : 'border-gray-200 text-gray-700 hover:border-cyan-400'">
                Withdraw
            </a>
        </div>
    </div>
</div>

{{-- Recent Deposits --}}
@if($recentDeposits->isNotEmpty())
<div class="rounded-2xl border overflow-hidden mb-5"
     :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
    <div class="flex items-center justify-between p-5 border-b" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
        <h3 class="text-sm font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">Recent Deposits</h3>
        <a href="{{ route('wallet.deposit') }}" class="text-xs text-cyan-400 hover:text-cyan-300 transition-colors">New Deposit →</a>
    </div>
    <div class="divide-y" :class="isDark ? 'divide-gray-800/40' : 'divide-gray-50'">
        @foreach($recentDeposits as $dep)
        <div class="flex items-center justify-between px-5 py-3 transition-colors"
             :class="isDark ? 'hover:bg-gray-800/20' : 'hover:bg-gray-50'">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0
                    @if($dep->isSuccessful()) bg-emerald-500/10 border border-emerald-500/20
                    @elseif($dep->status === 'failed') bg-red-500/10 border border-red-500/20
                    @elseif($dep->status === 'cancelled') bg-gray-500/10 border border-gray-700
                    @else bg-amber-500/10 border border-amber-500/20
                    @endif">
                    @if($dep->isSuccessful())
                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    @elseif($dep->status === 'failed')
                    <svg class="w-3.5 h-3.5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    @elseif($dep->isPending())
                    <svg class="animate-spin w-3.5 h-3.5 text-amber-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    @else
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-medium" :class="isDark ? 'text-white' : 'text-gray-900'">
                        {{ $dep->method_label }} · KES {{ number_format($dep->local_amount, 0) }}
                    </p>
                    <p class="text-[10px] text-gray-500">{{ $dep->created_at->diffForHumans() }} · {{ ucfirst($dep->wallet_type) }} wallet</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm font-semibold {{ $dep->isSuccessful() ? 'text-emerald-400' : '' }}"
                      :class="{{ $dep->isSuccessful() ? 'false' : 'true' }} ? (isDark ? 'text-gray-400' : 'text-gray-500') : ''">
                    ${{ number_format($dep->usd_amount, 2) }}
                </span>
                <a href="{{ route('deposits.show', $dep) }}"
                   class="text-[10px] text-cyan-400 hover:text-cyan-300 transition-colors font-medium whitespace-nowrap">
                    {{ $dep->isPending() ? 'Check →' : 'View →' }}
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Pending Withdrawals --}}
@if($pendingWithdrawals->isNotEmpty())
<div class="rounded-2xl border overflow-hidden mb-5"
     :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
    <div class="flex items-center justify-between p-5 border-b" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
        <h3 class="text-sm font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">Pending Withdrawals</h3>
        <a href="{{ route('wallet.withdraw') }}" class="text-xs text-cyan-400 hover:text-cyan-300 transition-colors">View all →</a>
    </div>
    <div class="divide-y" :class="isDark ? 'divide-gray-800/40' : 'divide-gray-50'">
        @foreach($pendingWithdrawals as $wd)
        <div class="flex items-center justify-between px-5 py-3 transition-colors"
             :class="isDark ? 'hover:bg-gray-800/20' : 'hover:bg-gray-50'">
            <div class="flex items-center gap-3">
                <span class="w-2 h-2 rounded-full flex-shrink-0 bg-{{ $wd->status_color }}-400 animate-pulse"></span>
                <div>
                    <p class="text-xs font-medium" :class="isDark ? 'text-white' : 'text-gray-900'">
                        {{ $wd->method_label }} withdrawal
                        <span class="text-gray-500 capitalize">({{ $wd->status }})</span>
                    </p>
                    <p class="text-[10px] text-gray-500">{{ $wd->created_at->diffForHumans() }} · Funds locked</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm font-semibold text-amber-400">${{ number_format($wd->usd_amount, 2) }}</span>
                <a href="{{ route('withdrawals.show', $wd) }}"
                   class="text-[10px] text-cyan-400 hover:text-cyan-300 transition-colors font-medium whitespace-nowrap">View →</a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Recent Transactions --}}
<div class="rounded-2xl border" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
    <div class="flex items-center justify-between p-5 border-b" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
        <h3 class="text-sm font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">Recent Transactions</h3>
        <a href="{{ route('transactions.index') }}" class="text-xs text-cyan-400 hover:text-cyan-300 transition-colors">View all →</a>
    </div>
    @php
        $recentTxns = auth()->user()->transactions()
            ->with('wallet')
            ->latest()
            ->limit(5)
            ->get();
    @endphp
    @if($recentTxns->isEmpty())
    <div class="flex flex-col items-center justify-center py-14 text-center">
        <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-3" :class="isDark ? 'bg-gray-800' : 'bg-gray-100'">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <p class="text-sm font-medium text-gray-500">No transactions yet</p>
        <p class="text-xs text-gray-600 mt-1">Your transaction history will appear here</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
                    @foreach(['Type','Amount','Wallet','Status','Date'] as $col)
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500">{{ $col }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($recentTxns as $txn)
                @php $isCredit = $txn->isCredit(); @endphp
                <tr class="border-b last:border-0 transition-colors"
                    :class="isDark ? 'border-gray-800/40 hover:bg-gray-800/20' : 'border-gray-50 hover:bg-gray-50'">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2.5">
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
                            <div>
                                <p class="text-xs font-medium" :class="isDark ? 'text-white' : 'text-gray-900'">{{ $txn->getTypeLabel() }}</p>
                                <p class="text-xs text-gray-500">{{ $txn->reference }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3">
                        <span class="text-sm font-semibold {{ $isCredit ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $txn->getSignedAmount() }} {{ $txn->currency }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium
                            {{ $txn->wallet->type === 'demo' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' }}">
                            {{ ucfirst($txn->wallet->type) }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        @php $sc = $txn->getStatusColor(); @endphp
                        <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $sc }}-500/10 text-{{ $sc }}-400 border border-{{ $sc }}-500/20 font-medium capitalize">
                            {{ $txn->status }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-500 whitespace-nowrap">
                        {{ $txn->created_at->format('M d, Y H:i') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

@endsection
