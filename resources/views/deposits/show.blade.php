@extends('layouts.trading')
@section('title', 'Deposit Status | Next Trade')
@section('page-title', 'Deposit Status')
@section('page-subtitle', 'Track your deposit progress')

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">{{ session('error') }}</div>
@endif
@if(session('info'))
<div class="mb-4 px-4 py-3 rounded-xl bg-blue-500/10 border border-blue-500/20 text-blue-400 text-sm">{{ session('info') }}</div>
@endif

<div class="max-w-xl mx-auto space-y-5"
     @if(!$deposit->isUsdtDeposit())
     x-data="depositStatus({
         status: '{{ $deposit->status }}',
         secondsLeft: {{ $deposit->isPending() && $deposit->manual_refresh_available_at ? max(0, (int) now()->diffInSeconds($deposit->manual_refresh_available_at, false)) : 0 }},
         canRefresh: {{ $deposit->canManualRefresh() ? 'true' : 'false' }}
     })"
     @endif>

    {{-- Status Banner --}}
    <div class="rounded-2xl border overflow-hidden"
         :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">

        {{-- Status header --}}
        <div class="p-5 flex items-center gap-4 border-b" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
            @if($deposit->isSuccessful())
            <div class="w-12 h-12 rounded-full bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            @elseif($deposit->isFailed())
            <div class="w-12 h-12 rounded-full bg-red-500/15 border border-red-500/30 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            @elseif($deposit->status === 'cancelled')
            <div class="w-12 h-12 rounded-full bg-gray-500/15 border border-gray-500/30 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
            </div>
            @else
            <div class="w-12 h-12 rounded-full bg-amber-500/15 border border-amber-500/30 flex items-center justify-center flex-shrink-0">
                @if($deposit->isUsdtDeposit())
                <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                @else
                <svg class="animate-spin w-6 h-6 text-amber-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                @endif
            </div>
            @endif

            <div class="flex-1 min-w-0">
                <p class="text-base font-bold" :class="isDark ? 'text-white' : 'text-gray-900'">
                    @if($deposit->isSuccessful()) Payment Confirmed
                    @elseif($deposit->isFailed() && $deposit->isUsdtDeposit()) Deposit Rejected
                    @elseif($deposit->isFailed()) Payment Failed
                    @elseif($deposit->status === 'cancelled') Payment Cancelled
                    @elseif($deposit->isUsdtDeposit()) Pending Admin Review
                    @else Awaiting Payment
                    @endif
                </p>
                <p class="text-xs text-gray-500 mt-0.5 font-mono">{{ $deposit->account_reference }}</p>
            </div>

            <span class="text-xs px-2.5 py-1 rounded-full font-semibold flex-shrink-0
                @if($deposit->isSuccessful()) bg-emerald-500/10 border border-emerald-500/20 text-emerald-400
                @elseif($deposit->isFailed()) bg-red-500/10 border border-red-500/20 text-red-400
                @elseif($deposit->status === 'cancelled') bg-gray-500/10 border border-gray-700 text-gray-400
                @else bg-amber-500/10 border border-amber-500/20 text-amber-400
                @endif">
                {{ ucfirst($deposit->status) }}
            </span>
        </div>

        {{-- Amounts --}}
        <div class="grid grid-cols-2 divide-x" :class="isDark ? 'divide-gray-800/60' : 'divide-gray-100'">
            <div class="p-4">
                <p class="text-[10px] text-gray-500 mb-0.5">
                    {{ $deposit->isUsdtDeposit() ? 'USDT Amount' : 'KES Amount' }}
                </p>
                <p class="text-lg font-bold" :class="isDark ? 'text-white' : 'text-gray-900'">
                    @if($deposit->isUsdtDeposit())
                        {{ number_format($deposit->local_amount, 2) }} USDT
                    @else
                        KES {{ number_format($deposit->local_amount, 2) }}
                    @endif
                </p>
            </div>
            <div class="p-4">
                <p class="text-[10px] text-gray-500 mb-0.5">USD {{ $deposit->isSuccessful() ? 'Credited' : 'Credit' }}</p>
                <p class="text-lg font-bold {{ $deposit->isSuccessful() ? 'text-emerald-400' : '' }}"
                   :class="{{ $deposit->isSuccessful() ? 'false' : 'true' }} ? (isDark ? 'text-white' : 'text-gray-900') : ''">
                    ${{ number_format($deposit->usd_amount, 2) }}
                </p>
            </div>
        </div>

        {{-- Deposit Details --}}
        <div class="p-4 border-t space-y-3" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
            <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500">Method</span>
                <span class="text-xs font-medium" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $deposit->method_label }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500">Wallet</span>
                <span class="text-xs font-medium capitalize" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $deposit->wallet_type }} Wallet</span>
            </div>

            @if($deposit->isUsdtDeposit())
                @if($deposit->crypto_network)
                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-500">Network</span>
                    <span class="text-xs font-semibold text-cyan-400">{{ $deposit->crypto_network }}</span>
                </div>
                @endif
                @if($deposit->txid)
                <div class="flex justify-between items-start gap-4">
                    <span class="text-xs text-gray-500 flex-shrink-0">TXID</span>
                    <span class="text-xs font-mono text-right break-all" :class="isDark ? 'text-gray-400' : 'text-gray-600'">{{ $deposit->txid }}</span>
                </div>
                @endif
            @else
                @if($deposit->phone)
                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-500">Phone</span>
                    <span class="text-xs font-medium" :class="isDark ? 'text-gray-300' : 'text-gray-700'">+{{ $deposit->phone }}</span>
                </div>
                @endif
            @endif

            <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500">Exchange Rate</span>
                <span class="text-xs font-medium" :class="isDark ? 'text-gray-300' : 'text-gray-700'">
                    @if($deposit->isUsdtDeposit())
                        1 USDT = ${{ number_format($deposit->exchange_rate, 2) }} USD
                    @else
                        1 USD = {{ number_format($deposit->exchange_rate, 2) }} KES
                    @endif
                </span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500">Submitted</span>
                <span class="text-xs font-medium" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $deposit->created_at->format('d M Y, H:i:s') }}</span>
            </div>
            @if($deposit->reviewed_at)
            <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500">Reviewed</span>
                <span class="text-xs font-medium" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $deposit->reviewed_at->format('d M Y, H:i:s') }}</span>
            </div>
            @endif
            @if($deposit->credited_at)
            <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500">Credited At</span>
                <span class="text-xs font-medium text-emerald-400">{{ $deposit->credited_at->format('d M Y, H:i:s') }}</span>
            </div>
            @endif
            @if($deposit->mpesa_receipt)
            <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500">M-Pesa Receipt</span>
                <span class="text-xs font-medium font-mono" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $deposit->mpesa_receipt }}</span>
            </div>
            @endif
        </div>

        {{-- Rejection reason --}}
        @if($deposit->isFailed() && $deposit->rejection_reason)
        <div class="mx-4 mb-4 p-3 rounded-xl bg-red-500/8 border border-red-500/20">
            <p class="text-xs font-semibold text-red-400 mb-1">Rejection Reason</p>
            <p class="text-xs text-red-400/80">{{ $deposit->rejection_reason }}</p>
        </div>
        @endif

        {{-- USDT proof screenshot --}}
        @if($deposit->isUsdtDeposit() && $deposit->proof_url)
        <div class="px-4 pb-4 border-t pt-4" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
            <p class="text-xs font-medium text-gray-500 mb-2">Proof Screenshot</p>
            <a href="{{ $deposit->proof_url }}" target="_blank" rel="noopener">
                <img src="{{ $deposit->proof_url }}"
                     alt="Deposit proof"
                     class="w-full max-h-48 object-contain rounded-xl border"
                     :class="isDark ? 'border-gray-700' : 'border-gray-200'">
            </a>
            <p class="text-[10px] text-gray-600 mt-1 text-center">Click image to view full size</p>
        </div>
        @endif

        {{-- USDT pending info --}}
        @if($deposit->isUsdtDeposit() && $deposit->isPending())
        <div class="p-4 border-t" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
            <div class="flex items-start gap-2.5 p-3 rounded-xl bg-amber-500/5 border border-amber-500/20">
                <svg class="w-4 h-4 text-amber-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-xs text-amber-400/80">Your deposit is under review by our team. Approved deposits are credited within 1–24 hours. No action is required from you.</p>
            </div>
        </div>
        @endif

        {{-- M-Pesa pending actions --}}
        @if(!$deposit->isUsdtDeposit() && $deposit->isPending())
        <div class="p-4 border-t space-y-3" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
            <div class="flex items-start gap-2.5 p-3 rounded-xl"
                 :class="isDark ? 'bg-amber-500/5 border border-amber-500/20' : 'bg-amber-50 border border-amber-200'">
                <svg class="w-4 h-4 text-amber-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-xs text-amber-400/80">Check your phone for the M-Pesa STK push prompt and enter your PIN to complete the payment.</p>
            </div>

            <div x-show="!canRefresh && secondsLeft > 0">
                <p class="text-xs text-gray-500 mb-2 text-center">You can check status in</p>
                <div class="flex justify-center">
                    <div class="flex items-center justify-center w-14 h-14 rounded-full border-2 border-amber-500/30 bg-amber-500/5">
                        <span class="text-xl font-bold text-amber-400 tabular-nums" x-text="secondsLeft"></span>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('deposits.refresh', $deposit) }}"
                  x-show="canRefresh || secondsLeft <= 0"
                  @submit="$el.querySelector('button').disabled = true">
                @csrf
                <button type="submit"
                        class="w-full py-2.5 rounded-xl text-sm font-semibold transition-all"
                        style="background: linear-gradient(135deg,#f59e0b,#d97706); box-shadow: 0 4px 12px rgba(245,158,11,0.25)">
                    <span class="text-white flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Check Payment Status
                    </span>
                </button>
            </form>
        </div>
        @endif

        {{-- Success state --}}
        @if($deposit->isSuccessful())
        <div class="p-4 border-t" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
            <div class="flex items-center gap-2 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20">
                <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <p class="text-xs text-emerald-400">
                    ${{ number_format($deposit->usd_amount, 2) }} has been credited to your {{ $deposit->wallet_type }} wallet.
                </p>
            </div>
        </div>
        @endif
    </div>

    {{-- Navigation --}}
    <div class="flex gap-3">
        <a href="{{ route('wallet.deposit') }}"
           class="flex-1 py-2.5 rounded-xl text-sm font-medium text-center border transition-colors"
           :class="isDark ? 'border-gray-700 text-gray-400 hover:border-gray-600 hover:text-gray-300' : 'border-gray-200 text-gray-600 hover:border-gray-300'">
            ← New Deposit
        </a>
        <a href="{{ route('wallet.index') }}"
           class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-center transition-all"
           :class="isDark ? 'bg-gray-800 text-white hover:bg-gray-700' : 'bg-gray-900 text-white hover:bg-gray-800'">
            Go to Wallet
        </a>
    </div>

</div>

@push('scripts')
<script>
function depositStatus({ status, secondsLeft, canRefresh }) {
    return {
        status,
        secondsLeft,
        canRefresh,
        timer: null,
        init() {
            if (status === 'pending' && secondsLeft > 0) {
                this.timer = setInterval(() => {
                    this.secondsLeft = Math.max(0, this.secondsLeft - 1);
                    if (this.secondsLeft <= 0) {
                        this.canRefresh = true;
                        clearInterval(this.timer);
                    }
                }, 1000);
            }
        },
        destroy() { if (this.timer) clearInterval(this.timer); },
    };
}
</script>
@endpush

@endsection
