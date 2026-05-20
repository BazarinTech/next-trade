@extends('layouts.trading')
@section('title', 'Transactions — Next Trade')
@section('page-title', 'Transactions')
@section('page-subtitle', 'Your complete transaction history')

@section('content')

{{-- Filters --}}
<div x-data="{ filtersOpen: false }" class="mb-5">
    <div class="flex flex-wrap items-center gap-3">
        {{-- Search --}}
        <form method="GET" action="{{ route('transactions.index') }}" class="flex flex-1 min-w-48 items-center gap-2">
            <div class="relative flex-1 max-w-sm">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by reference or description…"
                       class="w-full pl-9 pr-4 py-2 rounded-xl text-sm border transition-all"
                       :class="isDark ? 'bg-gray-900/80 border-gray-700 text-white placeholder-gray-600 focus:border-cyan-500' : 'bg-white border-gray-200 text-gray-900 placeholder-gray-400 focus:border-cyan-400'">
            </div>
            <button type="submit" class="px-4 py-2 rounded-xl text-sm font-medium text-white"
                    style="background: linear-gradient(135deg,#06b6d4,#0891b2)">Search</button>
        </form>

        <button @click="filtersOpen = !filtersOpen"
                class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium border transition-all"
                :class="isDark ? 'border-gray-700 text-gray-400 hover:text-white hover:bg-gray-800' : 'border-gray-200 text-gray-600 hover:bg-gray-50'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            Filters
            @if(request()->hasAny(['type','status','wallet_type']))
            <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
            @endif
        </button>

        @if(request()->hasAny(['type','status','wallet_type','search']))
        <a href="{{ route('transactions.index') }}"
           class="text-xs text-red-400 hover:text-red-300 transition-colors flex items-center gap-1">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            Clear filters
        </a>
        @endif
    </div>

    {{-- Expandable filters --}}
    <div x-show="filtersOpen" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="mt-3">
        <form method="GET" action="{{ route('transactions.index') }}"
              class="flex flex-wrap gap-3 p-4 rounded-2xl border"
              :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
            @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif

            {{-- Type filter --}}
            <div class="flex-1 min-w-36">
                <label class="block text-xs font-medium mb-1.5" :class="isDark ? 'text-gray-400' : 'text-gray-600'">Type</label>
                <select name="type"
                        class="w-full px-3 py-2 rounded-xl text-sm border appearance-none"
                        :class="isDark ? 'bg-gray-800 border-gray-700 text-white' : 'bg-gray-50 border-gray-200 text-gray-900'">
                    <option value="" :class="isDark ? 'bg-gray-900' : ''">All Types</option>
                    @foreach(['deposit'=>'Deposit','withdrawal'=>'Withdrawal','trade_profit'=>'Trade Profit','trade_loss'=>'Trade Loss','bot_profit'=>'Bot Profit','bot_investment'=>'Bot Investment','adjustment'=>'Adjustment'] as $val => $label)
                    <option value="{{ $val }}" :class="isDark ? 'bg-gray-900' : ''" {{ request('type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Status filter --}}
            <div class="flex-1 min-w-36">
                <label class="block text-xs font-medium mb-1.5" :class="isDark ? 'text-gray-400' : 'text-gray-600'">Status</label>
                <select name="status"
                        class="w-full px-3 py-2 rounded-xl text-sm border appearance-none"
                        :class="isDark ? 'bg-gray-800 border-gray-700 text-white' : 'bg-gray-50 border-gray-200 text-gray-900'">
                    <option value="" :class="isDark ? 'bg-gray-900' : ''">All Statuses</option>
                    @foreach(['successful','pending','failed','cancelled'] as $s)
                    <option value="{{ $s }}" :class="isDark ? 'bg-gray-900' : ''" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Wallet type filter --}}
            <div class="flex-1 min-w-36">
                <label class="block text-xs font-medium mb-1.5" :class="isDark ? 'text-gray-400' : 'text-gray-600'">Wallet</label>
                <select name="wallet_type"
                        class="w-full px-3 py-2 rounded-xl text-sm border appearance-none"
                        :class="isDark ? 'bg-gray-800 border-gray-700 text-white' : 'bg-gray-50 border-gray-200 text-gray-900'">
                    <option value="" :class="isDark ? 'bg-gray-900' : ''">All Wallets</option>
                    <option value="demo" :class="isDark ? 'bg-gray-900' : ''" {{ request('wallet_type') === 'demo' ? 'selected' : '' }}>Demo</option>
                    <option value="live" :class="isDark ? 'bg-gray-900' : ''" {{ request('wallet_type') === 'live' ? 'selected' : '' }}>Live</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit"
                        class="px-5 py-2 rounded-xl text-sm font-semibold text-white"
                        style="background: linear-gradient(135deg,#06b6d4,#0891b2)">
                    Apply
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="rounded-2xl border overflow-hidden" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
    <div class="flex items-center justify-between px-5 py-4 border-b" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
        <h3 class="text-sm font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">
            Transactions
            <span class="ml-2 text-xs font-normal text-gray-500">({{ $transactions->total() }})</span>
        </h3>
    </div>

    @if($transactions->isEmpty())
    <div class="flex flex-col items-center justify-center py-16 text-center px-4">
        <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-4"
             :class="isDark ? 'bg-gray-800' : 'bg-gray-100'">
            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <p class="text-sm font-semibold text-gray-500 mb-1">No transactions found</p>
        <p class="text-xs text-gray-600">
            @if(request()->hasAny(['type','status','wallet_type','search']))
                Try adjusting your filters.
            @else
                Transactions will appear here as you use the platform.
            @endif
        </p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
                    @foreach(['#','Type','Amount','Wallet','Balance After','Status','Description','Date'] as $col)
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 whitespace-nowrap">{{ $col }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $txn)
                @php $isCredit = $txn->isCredit(); $sc = $txn->getStatusColor(); @endphp
                <tr class="border-b last:border-0 transition-colors group"
                    :class="isDark ? 'border-gray-800/40 hover:bg-gray-800/20' : 'border-gray-50 hover:bg-gray-50'">

                    {{-- Reference --}}
                    <td class="px-5 py-3 text-xs text-gray-500 font-mono">
                        {{ $txn->reference ?? '—' }}
                    </td>

                    {{-- Type --}}
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0
                                {{ $isCredit ? 'bg-emerald-500/10 border border-emerald-500/20' : 'bg-red-500/10 border border-red-500/20' }}">
                                <svg class="w-3 h-3 {{ $isCredit ? 'text-emerald-400' : 'text-red-400' }}"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @if($isCredit)
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                                    @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                                    @endif
                                </svg>
                            </div>
                            <span class="text-xs font-medium whitespace-nowrap" :class="isDark ? 'text-gray-300' : 'text-gray-700'">
                                {{ $txn->getTypeLabel() }}
                            </span>
                        </div>
                    </td>

                    {{-- Amount --}}
                    <td class="px-5 py-3">
                        <span class="text-sm font-bold {{ $isCredit ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $txn->getSignedAmount() }}
                        </span>
                        <span class="text-xs text-gray-500 ml-0.5">{{ $txn->currency }}</span>
                    </td>

                    {{-- Wallet type --}}
                    <td class="px-5 py-3">
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium whitespace-nowrap
                            {{ $txn->wallet->type === 'demo'
                                ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20'
                                : 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' }}">
                            {{ ucfirst($txn->wallet->type) }}
                        </span>
                    </td>

                    {{-- Balance after --}}
                    <td class="px-5 py-3 text-xs text-gray-400 whitespace-nowrap">
                        ${{ number_format((float)$txn->balance_after, 2) }}
                    </td>

                    {{-- Status --}}
                    <td class="px-5 py-3">
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium capitalize
                            bg-{{ $sc }}-500/10 text-{{ $sc }}-400 border border-{{ $sc }}-500/20">
                            {{ $txn->status }}
                        </span>
                    </td>

                    {{-- Description --}}
                    <td class="px-5 py-3 text-xs text-gray-500 max-w-xs truncate">
                        {{ $txn->description ?? '—' }}
                    </td>

                    {{-- Date --}}
                    <td class="px-5 py-3 text-xs text-gray-500 whitespace-nowrap">
                        {{ $txn->created_at->format('M d, Y') }}<br>
                        <span class="text-gray-600">{{ $txn->created_at->format('H:i:s') }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($transactions->hasPages())
    <div class="px-5 py-4 border-t" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
        {{ $transactions->links() }}
    </div>
    @endif
    @endif
</div>

@endsection
