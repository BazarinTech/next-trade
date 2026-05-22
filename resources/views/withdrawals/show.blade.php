@extends('layouts.trading')
@section('title', 'Withdrawal | Next Trade')
@section('page-title', 'Withdrawal Detail')
@section('page-subtitle', $withdrawal->account_reference)

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    @if(session('success'))
    <div class="px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Status Card --}}
    <div class="rounded-2xl border p-6" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <p class="text-xs text-gray-500 mb-1">Withdrawal Amount</p>
                <p class="text-3xl font-bold" :class="isDark ? 'text-white' : 'text-gray-900'">${{ number_format($withdrawal->usd_amount, 2) }}</p>
                @if($withdrawal->method === 'mpesa')
                <p class="text-sm text-gray-500 mt-1">≈ KES {{ number_format($withdrawal->local_amount, 2) }} · {{ $withdrawal->account_reference }}</p>
                @else
                <p class="text-sm text-gray-500 mt-1">≈ {{ number_format($withdrawal->net_amount, 2) }} USDT · {{ $withdrawal->account_reference }}</p>
                @endif
            </div>
            <div class="text-right">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold
                    bg-{{ $withdrawal->status_color }}-500/15 border border-{{ $withdrawal->status_color }}-500/25 text-{{ $withdrawal->status_color }}-400">
                    @if(in_array($withdrawal->status, ['pending','processing','approved']))
                    <span class="w-1.5 h-1.5 rounded-full bg-{{ $withdrawal->status_color }}-400 animate-pulse"></span>
                    @endif
                    {{ ucfirst($withdrawal->status) }}
                </span>
                <p class="text-xs text-gray-500 mt-2">{{ $withdrawal->method_label }}</p>
            </div>
        </div>
    </div>

    {{-- Rejection Notice --}}
    @if($withdrawal->isRejected() || $withdrawal->isFailed())
    <div class="px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/20">
        <p class="text-xs font-semibold text-red-400 mb-1">
            {{ $withdrawal->isRejected() ? 'Withdrawal Rejected' : 'Withdrawal Failed' }}
        </p>
        <p class="text-xs text-red-400/80">{{ $withdrawal->rejection_reason }}</p>
        <p class="text-xs text-gray-500 mt-1">Your funds have been returned to your available balance.</p>
    </div>
    @endif

    {{-- Success Notice --}}
    @if($withdrawal->isSuccessful())
    <div class="px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20">
        <p class="text-xs font-semibold text-emerald-400 mb-1">Withdrawal Completed</p>
        <p class="text-xs text-emerald-400/80">
            @if($withdrawal->method === 'mpesa')
            Sent to M-Pesa {{ $withdrawal->phone }}
            @else
            Sent to {{ $withdrawal->crypto_address }}
            @endif
        </p>
        @if($withdrawal->completed_at)
        <p class="text-xs text-gray-500 mt-1">Completed {{ $withdrawal->completed_at->diffForHumans() }}</p>
        @endif
    </div>
    @endif

    {{-- Details Grid --}}
    <div class="rounded-2xl border divide-y" :class="isDark ? 'bg-gray-900/60 border-gray-800/60 divide-gray-800/60' : 'bg-white border-gray-200 shadow-sm divide-gray-100'">

        <div class="px-5 py-4 grid grid-cols-2 gap-4">
            <div>
                <p class="text-xs text-gray-500 mb-1">Status</p>
                <p class="text-sm font-semibold text-{{ $withdrawal->status_color }}-400 capitalize">{{ $withdrawal->status }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">Method</p>
                <p class="text-sm font-medium" :class="isDark ? 'text-white' : 'text-gray-900'">{{ $withdrawal->method_label }}</p>
            </div>
        </div>

        <div class="px-5 py-4 grid grid-cols-2 gap-4">
            <div>
                <p class="text-xs text-gray-500 mb-1">Requested</p>
                <p class="text-sm" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $withdrawal->created_at->format('M j, Y H:i') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">USD Amount</p>
                <p class="text-sm font-mono font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">${{ number_format($withdrawal->usd_amount, 2) }}</p>
            </div>
        </div>

        @if($withdrawal->fee_amount > 0)
        <div class="px-5 py-4 grid grid-cols-2 gap-4">
            <div>
                <p class="text-xs text-gray-500 mb-1">Fee</p>
                <p class="text-sm font-mono" :class="isDark ? 'text-gray-300' : 'text-gray-700'">${{ number_format($withdrawal->fee_amount, 2) }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">You Receive</p>
                <p class="text-sm font-mono font-semibold text-emerald-400">${{ number_format($withdrawal->net_amount, 2) }}</p>
            </div>
        </div>
        @endif

        {{-- M-Pesa specific --}}
        @if($withdrawal->method === 'mpesa')
        <div class="px-5 py-4 grid grid-cols-2 gap-4">
            <div>
                <p class="text-xs text-gray-500 mb-1">Phone Number</p>
                <p class="text-sm font-mono" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $withdrawal->phone }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">KES Amount</p>
                <p class="text-sm font-mono" :class="isDark ? 'text-gray-300' : 'text-gray-700'">KES {{ number_format($withdrawal->local_amount, 2) }}</p>
            </div>
        </div>
        @if($withdrawal->provider_reference)
        <div class="px-5 py-4">
            <p class="text-xs text-gray-500 mb-1">Provider Reference</p>
            <p class="text-sm font-mono" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $withdrawal->provider_reference }}</p>
        </div>
        @endif
        @endif

        {{-- USDT specific --}}
        @if($withdrawal->method === 'usdt_trc20')
        <div class="px-5 py-4">
            <p class="text-xs text-gray-500 mb-1">Destination Address</p>
            <p class="text-xs font-mono break-all mt-1" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $withdrawal->crypto_address }}</p>
        </div>
        @if($withdrawal->txid)
        <div class="px-5 py-4">
            <p class="text-xs text-gray-500 mb-1">Transaction ID (TXID)</p>
            <p class="text-xs font-mono break-all mt-1 text-cyan-400">{{ $withdrawal->txid }}</p>
        </div>
        @endif
        @if($withdrawal->provider_reference)
        <div class="px-5 py-4">
            <p class="text-xs text-gray-500 mb-1">Provider Reference</p>
            <p class="text-sm font-mono" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $withdrawal->provider_reference }}</p>
        </div>
        @endif
        @endif

        {{-- Reference --}}
        <div class="px-5 py-4">
            <p class="text-xs text-gray-500 mb-1">Account Reference</p>
            <p class="text-xs font-mono" :class="isDark ? 'text-gray-400' : 'text-gray-600'">{{ $withdrawal->account_reference }}</p>
        </div>
    </div>

    {{-- Timeline --}}
    <div class="rounded-2xl border p-5" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Timeline</p>
        <ol class="relative border-l ml-2" :class="isDark ? 'border-gray-700' : 'border-gray-200'">
            <li class="mb-4 ml-4">
                <div class="absolute w-2.5 h-2.5 rounded-full mt-1.5 -left-1.5 bg-cyan-400"></div>
                <p class="text-xs font-medium" :class="isDark ? 'text-white' : 'text-gray-900'">Withdrawal Requested</p>
                <p class="text-xs text-gray-500">{{ $withdrawal->requested_at?->format('M j, Y H:i') ?? $withdrawal->created_at->format('M j, Y H:i') }}</p>
            </li>
            @if($withdrawal->reviewed_at)
            <li class="mb-4 ml-4">
                <div class="absolute w-2.5 h-2.5 rounded-full mt-1.5 -left-1.5
                    {{ $withdrawal->isRejected() ? 'bg-red-400' : 'bg-blue-400' }}"></div>
                <p class="text-xs font-medium" :class="isDark ? 'text-white' : 'text-gray-900'">
                    {{ $withdrawal->isRejected() ? 'Rejected' : 'Reviewed / Approved' }}
                </p>
                <p class="text-xs text-gray-500">{{ $withdrawal->reviewed_at->format('M j, Y H:i') }}</p>
            </li>
            @endif
            @if($withdrawal->processed_at)
            <li class="mb-4 ml-4">
                <div class="absolute w-2.5 h-2.5 rounded-full mt-1.5 -left-1.5 bg-amber-400"></div>
                <p class="text-xs font-medium" :class="isDark ? 'text-white' : 'text-gray-900'">Payment Processing</p>
                <p class="text-xs text-gray-500">{{ $withdrawal->processed_at->format('M j, Y H:i') }}</p>
            </li>
            @endif
            @if($withdrawal->completed_at)
            <li class="ml-4">
                <div class="absolute w-2.5 h-2.5 rounded-full mt-1.5 -left-1.5
                    {{ $withdrawal->isSuccessful() ? 'bg-emerald-400' : 'bg-red-400' }}"></div>
                <p class="text-xs font-medium" :class="isDark ? 'text-white' : 'text-gray-900'">
                    {{ $withdrawal->isSuccessful() ? 'Completed' : 'Failed' }}
                </p>
                <p class="text-xs text-gray-500">{{ $withdrawal->completed_at->format('M j, Y H:i') }}</p>
            </li>
            @endif
        </ol>
    </div>

    <div class="text-center">
        <a href="{{ route('wallet.withdraw') }}" class="text-xs text-gray-500 hover:text-cyan-400 transition-colors">← Back to Withdrawals</a>
    </div>

</div>
@endsection
