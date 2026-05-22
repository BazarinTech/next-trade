@extends('layouts.app')
@section('title', 'Deposits | Admin')
@section('page-title', 'Deposits')
@section('page-subtitle', 'Monitor all deposit transactions')

@section('content')

{{-- Summary Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-6 gap-3 mb-6">
    <div class="rounded-xl border p-4" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-[10px] text-gray-500 uppercase tracking-wide mb-1">All Pending</p>
        <p class="text-2xl font-bold text-amber-400">{{ $pendingCount }}</p>
        <a href="{{ route('admin.deposits', ['status' => 'pending']) }}" class="text-[10px] text-amber-400/60 hover:text-amber-400 transition-colors">View →</a>
    </div>
    <div class="rounded-xl border p-4 {{ $pendingUsdtCount > 0 ? 'ring-1 ring-cyan-500/30' : '' }}"
         :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-[10px] text-gray-500 uppercase tracking-wide mb-1">USDT Pending</p>
        <p class="text-2xl font-bold text-cyan-400">{{ $pendingUsdtCount }}</p>
        <a href="{{ route('admin.deposits', ['status' => 'pending', 'method' => 'crypto_usdt_trc20']) }}"
           class="text-[10px] text-cyan-400/60 hover:text-cyan-400 transition-colors">Review →</a>
    </div>
    <div class="rounded-xl border p-4" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-[10px] text-gray-500 uppercase tracking-wide mb-1">Successful Today</p>
        <p class="text-2xl font-bold text-emerald-400">{{ $successfulToday }}</p>
    </div>
    <div class="rounded-xl border p-4" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-[10px] text-gray-500 uppercase tracking-wide mb-1">Failed Today</p>
        <p class="text-2xl font-bold text-red-400">{{ $failedToday }}</p>
    </div>
    <div class="rounded-xl border p-4" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-[10px] text-gray-500 uppercase tracking-wide mb-1">KES Today</p>
        <p class="text-xl font-bold" :class="isDark ? 'text-white' : 'text-gray-900'">{{ number_format($totalKesToday, 0) }}</p>
    </div>
    <div class="rounded-xl border p-4" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-[10px] text-gray-500 uppercase tracking-wide mb-1">USD Today</p>
        <p class="text-xl font-bold text-emerald-400">${{ number_format($totalUsdToday, 2) }}</p>
    </div>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('admin.deposits') }}"
      class="rounded-xl border p-4 mb-5 space-y-3"
      :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
        <select name="status"
                class="px-3 py-2 text-xs rounded-lg border bg-transparent focus:outline-none focus:ring-1 focus:ring-cyan-500/50"
                :class="isDark ? 'border-gray-700 text-gray-300' : 'border-gray-300 text-gray-700'">
            <option value="">All Statuses</option>
            @foreach(['pending','successful','failed','cancelled'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>

        <select name="method"
                class="px-3 py-2 text-xs rounded-lg border bg-transparent focus:outline-none focus:ring-1 focus:ring-cyan-500/50"
                :class="isDark ? 'border-gray-700 text-gray-300' : 'border-gray-300 text-gray-700'">
            <option value="">All Methods</option>
            <option value="mpesa_stk" {{ request('method') === 'mpesa_stk' ? 'selected' : '' }}>M-Pesa STK</option>
            <option value="crypto_usdt_trc20" {{ request('method') === 'crypto_usdt_trc20' ? 'selected' : '' }}>USDT TRC20</option>
        </select>

        <select name="provider"
                class="px-3 py-2 text-xs rounded-lg border bg-transparent focus:outline-none focus:ring-1 focus:ring-cyan-500/50"
                :class="isDark ? 'border-gray-700 text-gray-300' : 'border-gray-300 text-gray-700'">
            <option value="">All Providers</option>
            <option value="palpluss" {{ request('provider') === 'palpluss' ? 'selected' : '' }}>PalPluss (M-Pesa)</option>
            <option value="usdt_manual" {{ request('provider') === 'usdt_manual' ? 'selected' : '' }}>USDT Manual</option>
        </select>

        <input type="text" name="email" value="{{ request('email') }}" placeholder="Filter by email"
               class="px-3 py-2 text-xs rounded-lg border bg-transparent focus:outline-none focus:ring-1 focus:ring-cyan-500/50"
               :class="isDark ? 'border-gray-700 text-gray-300 placeholder-gray-600' : 'border-gray-300 text-gray-700'">

        <input type="date" name="date_from" value="{{ request('date_from') }}"
               class="px-3 py-2 text-xs rounded-lg border bg-transparent focus:outline-none focus:ring-1 focus:ring-cyan-500/50"
               :class="isDark ? 'border-gray-700 text-gray-300' : 'border-gray-300 text-gray-700'">

        <input type="date" name="date_to" value="{{ request('date_to') }}"
               class="px-3 py-2 text-xs rounded-lg border bg-transparent focus:outline-none focus:ring-1 focus:ring-cyan-500/50"
               :class="isDark ? 'border-gray-700 text-gray-300' : 'border-gray-300 text-gray-700'">
    </div>

    <div class="flex gap-2">
        <button type="submit"
                class="px-4 py-2 text-xs font-semibold rounded-lg text-white"
                style="background: linear-gradient(135deg,#06b6d4,#0891b2)">
            Apply Filters
        </button>
        @if(request()->hasAny(['status','provider','method','email','date_from','date_to']))
        <a href="{{ route('admin.deposits') }}"
           class="px-4 py-2 text-xs font-medium rounded-lg border transition-colors"
           :class="isDark ? 'border-gray-700 text-gray-400 hover:border-gray-600' : 'border-gray-200 text-gray-600 hover:border-gray-300'">
            Clear
        </a>
        @endif
    </div>
</form>

{{-- Deposits Table --}}
<div class="rounded-2xl border overflow-hidden"
     :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">

    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead>
                <tr class="border-b" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
                    <th class="text-left px-4 py-3 font-semibold text-gray-500 uppercase tracking-wide text-[10px]">User</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500 uppercase tracking-wide text-[10px]">Method</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-500 uppercase tracking-wide text-[10px]">Amount</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-500 uppercase tracking-wide text-[10px]">USD</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500 uppercase tracking-wide text-[10px]">TXID / Receipt</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500 uppercase tracking-wide text-[10px]">Wallet</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500 uppercase tracking-wide text-[10px]">Status</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500 uppercase tracking-wide text-[10px]">Date</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y" :class="isDark ? 'divide-gray-800/40' : 'divide-gray-50'">
                @forelse($deposits as $deposit)
                <tr class="transition-colors {{ $deposit->isUsdtDeposit() && $deposit->isPending() ? 'ring-1 ring-inset ring-cyan-500/10' : '' }}"
                    :class="isDark ? 'hover:bg-gray-800/30' : 'hover:bg-gray-50'">
                    <td class="px-4 py-3">
                        <div>
                            <p class="font-medium" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $deposit->user?->name ?? '—' }}</p>
                            <p class="text-[10px] text-gray-500">{{ $deposit->user?->email ?? '—' }}</p>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1.5">
                            @if($deposit->isUsdtDeposit())
                            <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 flex-shrink-0"></span>
                            @else
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 flex-shrink-0"></span>
                            @endif
                            <span :class="isDark ? 'text-gray-400' : 'text-gray-600'">{{ $deposit->method_label }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-right font-medium" :class="isDark ? 'text-gray-300' : 'text-gray-700'">
                        {{ number_format($deposit->local_amount, 2) }}
                        <span class="text-[10px] text-gray-500">{{ $deposit->local_currency }}</span>
                    </td>
                    <td class="px-4 py-3 text-right font-semibold {{ $deposit->isSuccessful() ? 'text-emerald-400' : '' }}"
                        :class="{{ $deposit->isSuccessful() ? 'false' : 'true' }} ? (isDark ? 'text-gray-300' : 'text-gray-700') : ''">
                        ${{ number_format($deposit->usd_amount, 2) }}
                    </td>
                    <td class="px-4 py-3 max-w-[140px]">
                        @if($deposit->txid)
                        <span class="font-mono text-[10px] truncate block" :class="isDark ? 'text-gray-400' : 'text-gray-600'"
                              title="{{ $deposit->txid }}">
                            {{ Str::limit($deposit->txid, 18) }}
                        </span>
                        @elseif($deposit->mpesa_receipt)
                        <span class="font-mono text-[10px]" :class="isDark ? 'text-gray-400' : 'text-gray-600'">{{ $deposit->mpesa_receipt }}</span>
                        @else
                        <span class="text-[10px] text-gray-600">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="capitalize text-[10px] px-2 py-0.5 rounded-full
                            {{ $deposit->wallet_type === 'live' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20' }}">
                            {{ $deposit->wallet_type }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-[10px] px-2.5 py-1 rounded-full font-semibold
                            @if($deposit->status === 'successful') bg-emerald-500/10 border border-emerald-500/20 text-emerald-400
                            @elseif($deposit->status === 'failed') bg-red-500/10 border border-red-500/20 text-red-400
                            @elseif($deposit->status === 'pending') bg-amber-500/10 border border-amber-500/20 text-amber-400
                            @else bg-gray-500/10 border border-gray-700 text-gray-400
                            @endif">
                            {{ ucfirst($deposit->status) }}
                        </span>
                        @if($deposit->isUsdtDeposit() && $deposit->isPending())
                        <span class="ml-1 text-[9px] text-cyan-400 font-semibold">Review</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-[10px] text-gray-500 whitespace-nowrap">
                        {{ $deposit->created_at->format('d M, H:i') }}
                        @if($deposit->reviewed_at)
                        <br><span class="text-[9px] text-gray-600">Reviewed {{ $deposit->reviewed_at->format('d M') }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.deposits.show', $deposit) }}"
                           class="text-xs font-medium transition-colors {{ $deposit->isUsdtDeposit() && $deposit->isPending() ? 'text-cyan-400 hover:text-cyan-300' : 'text-gray-400 hover:text-gray-300' }}">
                            {{ $deposit->isUsdtDeposit() && $deposit->isPending() ? 'Review →' : 'View →' }}
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-12 text-center text-gray-500 text-sm">
                        No deposits found matching your filters.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($deposits->hasPages())
    <div class="px-4 py-3 border-t" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
        {{ $deposits->links() }}
    </div>
    @endif
</div>

@endsection
