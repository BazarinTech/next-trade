@extends('layouts.trading')
@section('title', 'Deposit — Next Trade')
@section('page-title', 'Deposit Funds')
@section('page-subtitle', 'Add funds to your wallet')

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">{{ session('error') }}</div>
@endif

<div class="max-w-2xl mx-auto space-y-6">

    {{-- Pending Deposits Alert --}}
    @foreach($pendingDeposits as $pd)
    <div class="flex items-center justify-between px-4 py-3 rounded-xl bg-amber-500/10 border border-amber-500/20">
        <div class="flex items-center gap-3">
            <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
            <div>
                <p class="text-xs font-semibold text-amber-400">
                    Pending {{ $pd->method_label }} Deposit
                </p>
                <p class="text-xs text-amber-400/70">
                    {{ $pd->isUsdtDeposit() ? $pd->local_amount.' USDT' : 'KES '.number_format($pd->local_amount,2) }}
                    → ${{ number_format($pd->usd_amount,2) }} · {{ $pd->created_at->diffForHumans() }}
                </p>
            </div>
        </div>
        <a href="{{ route('deposits.show', $pd) }}" class="text-xs text-amber-400 hover:text-amber-300 transition-colors font-medium">View →</a>
    </div>
    @endforeach

    {{-- M-Pesa Section --}}
    @if($isKenya)
    <div x-data="mpesaForm()" class="rounded-2xl border overflow-hidden"
         :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">

        <div class="flex items-center gap-3 p-5 border-b" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/15 border border-emerald-500/20 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold" :class="isDark ? 'text-white' : 'text-gray-900'">M-Pesa STK Push</p>
                <p class="text-xs text-gray-500">Instant deposit via Safaricom M-Pesa</p>
            </div>
            <span class="ml-auto text-xs px-2.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 font-medium">Kenya</span>
        </div>

        <form method="POST" action="{{ route('deposits.mpesa') }}" class="p-5 space-y-4" @submit="submitting = true">
            @csrf

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-2">Deposit to Wallet</label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach(['demo' => ['Demo','amber'], 'live' => ['Live','cyan']] as $mode => [$label, $color])
                    <label class="flex items-center gap-2.5 p-3 rounded-xl border cursor-pointer transition-all"
                           :class="walletType === '{{ $mode }}'
                               ? 'border-{{ $color }}-500/50 bg-{{ $color }}-500/10'
                               : (isDark ? 'border-gray-800 hover:border-gray-700' : 'border-gray-200 hover:border-gray-300')">
                        <input type="radio" name="wallet_type" value="{{ $mode }}" x-model="walletType" class="sr-only">
                        <span class="w-3 h-3 rounded-full border-2 flex-shrink-0 {{ 'border-'.$color.'-400' }}"
                              :class="walletType === '{{ $mode }}' ? 'bg-{{ $color }}-400' : 'bg-transparent'"></span>
                        <div>
                            <p class="text-xs font-semibold text-{{ $color }}-400">{{ $label }} Wallet</p>
                            <p class="text-[10px] text-gray-500">
                                ${{ number_format($$mode.'Wallet'->available_balance, 2) }} available
                            </p>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Amount (KES)</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-gray-500">KES</span>
                    <input type="number" name="kes_amount" x-model.number="kesAmount"
                           min="10" max="250000" step="1" required
                           @input="calcUsd()"
                           placeholder="e.g. 1000"
                           class="w-full pl-12 pr-4 py-2.5 text-sm rounded-xl border bg-transparent focus:outline-none focus:ring-1 focus:ring-emerald-500/50"
                           :class="isDark ? 'border-gray-700 text-white' : 'border-gray-300 text-gray-900'">
                </div>
                <div class="flex justify-between mt-1 text-[10px] text-gray-600">
                    <span>Min: KES 10</span>
                    <span>Max: KES 250,000</span>
                </div>
                @error('kes_amount')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                <div class="grid grid-cols-4 gap-1.5 mt-2">
                    @foreach([500, 1000, 2000, 5000] as $q)
                    <button type="button" @click="setAmount({{ $q }})"
                            class="text-xs py-1.5 rounded-lg border border-gray-700 text-gray-400 hover:border-emerald-500/40 hover:text-emerald-400 transition-colors">
                        {{ number_format($q) }}
                    </button>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-between p-3 rounded-xl border"
                 :class="isDark ? 'border-gray-800 bg-gray-800/30' : 'border-gray-100 bg-gray-50'">
                <div>
                    <p class="text-[10px] text-gray-500 mb-0.5">You will receive (approx.)</p>
                    <p class="text-lg font-bold text-emerald-400">$<span x-text="usdPreview.toFixed(2)"></span> USD</p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] text-gray-500 mb-0.5">Exchange rate</p>
                    <p class="text-xs font-semibold text-gray-400">1 USD = {{ $exchangeRate }} KES</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">M-Pesa Phone Number</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-500">+254</span>
                    <input type="tel" name="phone" value="{{ old('phone') }}"
                           placeholder="0712 345 678"
                           class="w-full pl-12 pr-4 py-2.5 text-sm rounded-xl border bg-transparent focus:outline-none focus:ring-1 focus:ring-emerald-500/50"
                           :class="isDark ? 'border-gray-700 text-white' : 'border-gray-300 text-gray-900'">
                </div>
                <p class="text-[10px] text-gray-600 mt-1">Enter the number registered with M-Pesa (e.g. 0712345678)</p>
                @error('phone')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-start gap-2 text-[10px] text-gray-600 p-3 rounded-xl"
                 :class="isDark ? 'bg-gray-800/40' : 'bg-gray-50'">
                <svg class="w-3.5 h-3.5 text-gray-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span>An M-Pesa STK push will be sent to your phone. Enter your PIN to complete the payment.</span>
            </div>

            <button type="submit"
                    :disabled="submitting || !kesAmount || kesAmount < 10"
                    class="w-full py-3 rounded-xl text-sm font-semibold text-white transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                    style="background: linear-gradient(135deg,#10b981,#059669); box-shadow: 0 4px 12px rgba(16,185,129,0.25)">
                <span x-show="!submitting">Send STK Push</span>
                <span x-show="submitting" x-cloak class="flex items-center justify-center gap-2">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Sending…
                </span>
            </button>
        </form>
    </div>
    @endif

    {{-- USDT TRC20 Section --}}
    <div x-data="usdtForm()" class="rounded-2xl border overflow-hidden"
         :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">

        {{-- Header --}}
        <div class="flex items-center gap-3 p-5 border-b" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
            <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold" :class="isDark ? 'text-white' : 'text-gray-900'">USDT Deposit (TRC20)</p>
                <p class="text-xs text-gray-500">Manual crypto deposit · Admin reviewed</p>
            </div>
            <span class="ml-auto text-xs px-2.5 py-1 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 font-medium">Global</span>
        </div>

        <div class="p-5 space-y-4">

            {{-- Network warning --}}
            <div class="flex items-start gap-2.5 p-3 rounded-xl bg-amber-500/8 border border-amber-500/25">
                <svg class="w-4 h-4 text-amber-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.963-.833-2.732 0L3.072 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <p class="text-xs text-amber-400/90">
                    <span class="font-semibold">Send only USDT on the TRC20 network.</span>
                    Sending any other token or using a different network will result in permanent loss of funds.
                </p>
            </div>

            {{-- Wallet address --}}
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Our USDT TRC20 Deposit Address</label>
                <div class="flex items-center gap-2">
                    <div class="flex-1 px-3 py-2.5 rounded-xl border font-mono text-xs break-all select-all"
                         :class="isDark ? 'border-gray-700 bg-gray-800/50 text-gray-300' : 'border-gray-200 bg-gray-50 text-gray-700'">
                        {{ config('crypto.usdt_trc20_wallet_address') ?: 'Address not configured — contact support' }}
                    </div>
                    @if(config('crypto.usdt_trc20_wallet_address'))
                    <button type="button"
                            @click="copyAddress('{{ config('crypto.usdt_trc20_wallet_address') }}')"
                            class="flex-shrink-0 flex items-center gap-1.5 px-3 py-2.5 rounded-xl border transition-all text-xs font-medium"
                            :class="copied
                                ? 'border-emerald-500/40 text-emerald-400 bg-emerald-500/10'
                                : (isDark ? 'border-gray-700 text-gray-400 hover:border-cyan-500/40 hover:text-cyan-400' : 'border-gray-200 text-gray-600 hover:border-cyan-400')">
                        <svg x-show="!copied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <svg x-show="copied" x-cloak class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                    </button>
                    @endif
                </div>
                <div class="flex items-center gap-4 mt-1.5">
                    <span class="text-[10px] text-gray-500">Network: <span class="text-cyan-400 font-medium">TRC20</span></span>
                    <span class="text-[10px] text-gray-500">Rate: <span class="text-cyan-400 font-medium">1 USDT = $1.00 USD</span></span>
                </div>
            </div>

            {{-- Form --}}
            <form method="POST" action="{{ route('deposits.usdt') }}"
                  enctype="multipart/form-data"
                  @submit.prevent="handleSubmit($el)">
                @csrf

                {{-- Wallet selector --}}
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-400 mb-2">Credit to Wallet</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach(['demo' => ['Demo','amber'], 'live' => ['Live','cyan']] as $mode => [$label, $color])
                        <label class="flex items-center gap-2.5 p-3 rounded-xl border cursor-pointer transition-all"
                               :class="walletType === '{{ $mode }}'
                                   ? 'border-{{ $color }}-500/50 bg-{{ $color }}-500/10'
                                   : (isDark ? 'border-gray-800 hover:border-gray-700' : 'border-gray-200 hover:border-gray-300')">
                            <input type="radio" name="wallet_type" value="{{ $mode }}" x-model="walletType" class="sr-only">
                            <span class="w-3 h-3 rounded-full border-2 flex-shrink-0 {{ 'border-'.$color.'-400' }}"
                                  :class="walletType === '{{ $mode }}' ? 'bg-{{ $color }}-400' : 'bg-transparent'"></span>
                            <div>
                                <p class="text-xs font-semibold text-{{ $color }}-400">{{ $label }} Wallet</p>
                                <p class="text-[10px] text-gray-500">${{ number_format(${$mode.'Wallet'}->available_balance ?? 0, 2) }} available</p>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @error('wallet_type')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- USDT Amount --}}
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Amount (USDT)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-gray-500">USDT</span>
                        <input type="number" name="amount" id="usdt_amount"
                               x-model.number="usdtAmount"
                               @input="calcUsd()"
                               value="{{ old('amount') }}"
                               min="1" step="0.01" required
                               placeholder="e.g. 100"
                               class="w-full pl-14 pr-4 py-2.5 text-sm rounded-xl border bg-transparent focus:outline-none focus:ring-1 focus:ring-cyan-500/50"
                               :class="isDark ? 'border-gray-700 text-white' : 'border-gray-300 text-gray-900'">
                    </div>
                    <p class="text-[10px] text-gray-600 mt-1">Minimum: 1 USDT</p>
                    @error('amount')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                    {{-- Quick amounts --}}
                    <div class="grid grid-cols-4 gap-1.5 mt-2">
                        @foreach([50, 100, 250, 500] as $q)
                        <button type="button" @click="setUsdtAmount({{ $q }})"
                                class="text-xs py-1.5 rounded-lg border border-gray-700 text-gray-400 hover:border-cyan-500/40 hover:text-cyan-400 transition-colors">
                            {{ $q }}
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- USD Preview --}}
                <div class="flex items-center justify-between p-3 rounded-xl border mb-4"
                     :class="isDark ? 'border-gray-800 bg-gray-800/30' : 'border-gray-100 bg-gray-50'">
                    <div>
                        <p class="text-[10px] text-gray-500 mb-0.5">You will receive (after approval)</p>
                        <p class="text-lg font-bold text-cyan-400">$<span x-text="usdPreview.toFixed(2)"></span> USD</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] text-gray-500 mb-0.5">Exchange rate</p>
                        <p class="text-xs font-semibold text-gray-400">1 USDT = $1.00 USD</p>
                    </div>
                </div>

                {{-- TXID --}}
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Transaction ID (TXID)</label>
                    <input type="text" name="txid" value="{{ old('txid') }}"
                           placeholder="e.g. abc123def456..."
                           minlength="10" maxlength="255" required
                           class="w-full px-3 py-2.5 text-sm rounded-xl border bg-transparent focus:outline-none focus:ring-1 focus:ring-cyan-500/50 font-mono"
                           :class="isDark ? 'border-gray-700 text-white placeholder-gray-600' : 'border-gray-300 text-gray-900'">
                    <p class="text-[10px] text-gray-600 mt-1">Paste the TXID from your TRC20 wallet or exchange</p>
                    @error('txid')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Proof upload --}}
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Proof Screenshot</label>

                    <div class="relative border-2 border-dashed rounded-xl transition-all cursor-pointer"
                         :class="[
                             previewUrl ? 'border-cyan-500/40 bg-cyan-500/5' : (isDark ? 'border-gray-700 hover:border-gray-600' : 'border-gray-200 hover:border-gray-300'),
                             isDragging ? 'border-cyan-500/60 bg-cyan-500/10' : ''
                         ]"
                         @dragover.prevent="isDragging = true"
                         @dragleave="isDragging = false"
                         @drop.prevent="handleDrop($event)"
                         @click="$refs.proofInput.click()">

                        <input type="file" name="proof" x-ref="proofInput"
                               accept="image/jpeg,image/jpg,image/png,image/webp"
                               class="sr-only"
                               @change="handleFile($event)">

                        <template x-if="!previewUrl">
                            <div class="flex flex-col items-center justify-center py-8 px-4">
                                <svg class="w-8 h-8 text-gray-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-xs text-gray-500 font-medium">Click or drag to upload screenshot</p>
                                <p class="text-[10px] text-gray-600 mt-1">JPG, PNG, WEBP · Max 4MB</p>
                            </div>
                        </template>

                        <template x-if="previewUrl">
                            <div class="relative p-2">
                                <img :src="previewUrl" class="w-full max-h-48 object-contain rounded-lg">
                                <button type="button"
                                        @click.stop="clearFile()"
                                        class="absolute top-3 right-3 w-6 h-6 rounded-full bg-red-500 flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                                <p class="text-[10px] text-center text-gray-500 mt-1" x-text="fileName"></p>
                            </div>
                        </template>
                    </div>
                    @error('proof')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Review notice --}}
                <div class="flex items-start gap-2 text-[10px] text-gray-600 p-3 rounded-xl mb-4"
                     :class="isDark ? 'bg-gray-800/40' : 'bg-gray-50'">
                    <svg class="w-3.5 h-3.5 text-gray-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Deposits are manually reviewed by our team. Approved deposits are credited within 1–24 hours. Your wallet will <strong class="text-gray-400">not</strong> be credited until an admin approves the deposit.</span>
                </div>

                <button type="submit"
                        :disabled="submitting || !usdtAmount || usdtAmount < 1 || !previewUrl"
                        class="w-full py-3 rounded-xl text-sm font-semibold text-white transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        style="background: linear-gradient(135deg,#06b6d4,#0891b2); box-shadow: 0 4px 12px rgba(6,182,212,0.25)">
                    <span x-show="!submitting">Submit for Review</span>
                    <span x-show="submitting" x-cloak class="flex items-center justify-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Submitting…
                    </span>
                </button>
            </form>
        </div>
    </div>

    {{-- Non-Kenya M-Pesa placeholder --}}
    @if(!$isKenya)
    <div class="rounded-2xl border p-6 text-center opacity-50" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <svg class="w-6 h-6 text-gray-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
        </svg>
        <p class="text-sm font-semibold text-gray-500 mb-1">M-Pesa Not Available</p>
        <p class="text-xs text-gray-600">M-Pesa deposits are available for Kenya accounts only.</p>
    </div>
    @endif

</div>

@push('scripts')
<script>
function mpesaForm() {
    return {
        kesAmount:  {{ old('kes_amount', 0) }},
        usdPreview: 0,
        rate:       {{ $exchangeRate }},
        walletType: '{{ session('wallet_mode', 'demo') }}',
        submitting: false,
        init() { this.calcUsd(); },
        calcUsd() {
            this.usdPreview = this.rate > 0 && this.kesAmount > 0
                ? Math.round(this.kesAmount / this.rate * 100) / 100
                : 0;
        },
        setAmount(val) { this.kesAmount = val; this.calcUsd(); },
    };
}

function usdtForm() {
    return {
        usdtAmount:  {{ old('amount', 0) }},
        usdPreview:  0,
        walletType:  '{{ session('wallet_mode', 'demo') }}',
        submitting:  false,
        previewUrl:  null,
        fileName:    '',
        copied:      false,
        isDragging:  false,

        init() { this.calcUsd(); },

        calcUsd() {
            this.usdPreview = this.usdtAmount > 0 ? Math.round(this.usdtAmount * 100) / 100 : 0;
        },

        setUsdtAmount(val) { this.usdtAmount = val; this.calcUsd(); },

        copyAddress(addr) {
            navigator.clipboard.writeText(addr).then(() => {
                this.copied = true;
                setTimeout(() => { this.copied = false; }, 2000);
            });
        },

        handleFile(event) {
            const file = event.target.files[0];
            if (file) this.setFile(file);
        },

        handleDrop(event) {
            this.isDragging = false;
            const file = event.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                this.$refs.proofInput.files = event.dataTransfer.files;
                this.setFile(file);
            }
        },

        setFile(file) {
            this.fileName = file.name;
            const reader = new FileReader();
            reader.onload = (e) => { this.previewUrl = e.target.result; };
            reader.readAsDataURL(file);
        },

        clearFile() {
            this.previewUrl = null;
            this.fileName = '';
            this.$refs.proofInput.value = '';
        },

        handleSubmit(form) {
            if (!this.previewUrl) return;
            this.submitting = true;
            form.submit();
        },
    };
}
</script>
@endpush

<x-sim-disclaimer />
@endsection
