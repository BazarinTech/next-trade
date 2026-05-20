<div x-data="withdrawModalData()" style="display:flex;flex-direction:column;height:100%;">

    {{-- Header --}}
    <div style="padding:16px 20px;border-bottom:1px solid #1f2937;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
        <div>
            <h2 style="font-size:15px;font-weight:700;color:white;margin:0;">Withdraw Funds</h2>
            <p style="font-size:11px;color:#6b7280;margin:2px 0 0;">Live wallet only · USDT TRC20 &amp; M-Pesa</p>
        </div>
        <button @click="$store.modal.close()" style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;border-radius:8px;border:1px solid #374151;background:transparent;cursor:pointer;color:#9ca3af;font-size:16px;">&times;</button>
    </div>

    {{-- Scrollable body --}}
    <div style="overflow-y:auto;padding:16px 20px;flex:1;display:flex;flex-direction:column;gap:14px;">

        {{-- Pending withdrawal alerts --}}
        @foreach($pendingWithdrawals as $pw)
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;border-radius:10px;background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2);">
            <div style="display:flex;align-items:center;gap:8px;">
                <span style="width:7px;height:7px;border-radius:50%;background:#fbbf24;animation:pulse 2s infinite;flex-shrink:0;"></span>
                <div>
                    <p style="font-size:11px;font-weight:600;color:#fbbf24;margin:0;">
                        Pending {{ $pw->method === 'mpesa' ? 'M-Pesa' : 'USDT' }} Withdrawal
                        <span style="color:rgba(251,191,36,.6);">({{ $pw->status }})</span>
                    </p>
                    <p style="font-size:10px;color:rgba(251,191,36,0.7);margin:0;">${{ number_format($pw->usd_amount, 2) }} · {{ $pw->created_at->diffForHumans() }}</p>
                </div>
            </div>
            <a href="{{ route('withdrawals.show', $pw) }}" style="font-size:10px;color:#fbbf24;text-decoration:none;font-weight:600;">View →</a>
        </div>
        @endforeach

        {{-- Live wallet balance --}}
        <div style="border-radius:12px;border:1px solid #1f2937;background:rgba(17,24,39,0.6);padding:14px;">
            <p style="font-size:10px;font-weight:600;color:#6b7280;margin:0 0 8px;text-transform:uppercase;letter-spacing:.05em;">Live Wallet Balance</p>
            <div style="display:flex;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                <div>
                    <p style="font-size:22px;font-weight:700;color:white;margin:0;">${{ number_format($liveWallet->available_balance, 2) }}
                        <span style="font-size:11px;font-weight:400;color:#6b7280;">available</span>
                    </p>
                    @if($liveWallet->locked_balance > 0)
                    <p style="font-size:10px;color:#fbbf24;margin:4px 0 0;">${{ number_format($liveWallet->locked_balance, 2) }} locked in pending</p>
                    @endif
                </div>
                <div style="text-align:right;">
                    <p style="font-size:10px;color:#6b7280;margin:0;">Total Balance</p>
                    <p style="font-size:13px;font-weight:600;color:#d1d5db;margin:2px 0 0;">${{ number_format($liveWallet->balance, 2) }}</p>
                </div>
            </div>
            @if($liveWallet->isFrozen())
            <div style="margin-top:10px;padding:8px 10px;border-radius:8px;background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);">
                <p style="font-size:10px;color:#f87171;margin:0;">Your live wallet is frozen. Contact support to withdraw.</p>
            </div>
            @endif
        </div>

        {{-- Method tabs --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
            <button type="button" @click="method = 'usdt'"
                    style="padding:8px;border-radius:10px;border:1px solid;font-size:11px;font-weight:700;cursor:pointer;transition:all .15s;"
                    :style="method === 'usdt' ? 'border-color:rgba(6,182,212,.4);color:#22d3ee;background:rgba(6,182,212,.08)' : 'border-color:#374151;color:#6b7280;background:transparent'">
                USDT (TRC20)
            </button>
            <button type="button" @click="method = 'mpesa'" @if(!$isKenya) disabled @endif
                    style="padding:8px;border-radius:10px;border:1px solid;font-size:11px;font-weight:700;cursor:pointer;transition:all .15s;"
                    :style="method === 'mpesa' ? 'border-color:rgba(16,185,129,.4);color:#34d399;background:rgba(16,185,129,.08)' : 'border-color:#374151;color:{{ $isKenya ? '#6b7280' : '#4b5563' }};background:transparent'"
                    title="{{ $isKenya ? '' : 'Available for Kenya accounts only' }}">
                M-Pesa{{ $isKenya ? '' : ' (KE only)' }}
            </button>
        </div>

        {{-- USDT withdrawal form --}}
        <div x-show="method === 'usdt'">
            <div style="border-radius:12px;border:1px solid #1f2937;background:rgba(17,24,39,0.6);overflow:hidden;">
                <div style="display:flex;align-items:center;gap:10px;padding:12px 14px;border-bottom:1px solid #1f2937;">
                    <div style="width:36px;height:36px;border-radius:10px;background:rgba(6,182,212,0.1);border:1px solid rgba(6,182,212,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg style="width:16px;height:16px;color:#22d3ee;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p style="font-size:13px;font-weight:700;color:white;margin:0;">Withdraw USDT (TRC20)</p>
                        <p style="font-size:10px;color:#6b7280;margin:0;">Sent to your TRON wallet · 1–48 hrs after approval</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('withdrawals.usdt') }}" style="padding:14px;display:flex;flex-direction:column;gap:12px;" @submit.prevent="usdtSubmitting=true;$el.submit()">
                    @csrf
                    {{-- Amount --}}
                    <div>
                        <p style="font-size:10px;font-weight:600;color:#6b7280;margin:0 0 6px;text-transform:uppercase;letter-spacing:.05em;">Amount (USD)</p>
                        <input type="number" name="amount" x-model.number="usdtAmount" min="5" max="100000" step="0.01" required placeholder="0.00"
                               style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid #374151;background:rgba(31,41,55,0.6);color:white;font-size:13px;font-family:monospace;outline:none;box-sizing:border-box;">
                        <div style="display:flex;gap:4px;margin-top:6px;">
                            @foreach([25, 50, 100, 250, 500] as $q)
                            <button type="button" @click="usdtAmount={{ $q }}"
                                    style="flex:1;padding:4px 0;border-radius:6px;font-size:10px;font-weight:600;cursor:pointer;border:1px solid #374151;color:#9ca3af;background:transparent;transition:all .15s;"
                                    :style="usdtAmount === {{ $q }} ? 'border-color:rgba(6,182,212,.5);color:#22d3ee;background:rgba(6,182,212,.08)' : ''">
                                ${{ $q }}
                            </button>
                            @endforeach
                        </div>
                        <p style="font-size:10px;color:#6b7280;margin:6px 0 0;">≈ <span x-text="usdtAmount > 0 ? usdtAmount.toFixed(2) : '0.00'"></span> USDT · Min: $5.00</p>
                    </div>
                    {{-- Address --}}
                    <div>
                        <p style="font-size:10px;font-weight:600;color:#6b7280;margin:0 0 6px;text-transform:uppercase;letter-spacing:.05em;">TRC20 Wallet Address</p>
                        <input type="text" name="crypto_address" minlength="20" maxlength="120" required placeholder="TRC20 address (e.g. T...)"
                               style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid #374151;background:rgba(31,41,55,0.6);color:white;font-size:11px;font-family:monospace;outline:none;box-sizing:border-box;">
                        <p style="font-size:10px;color:#fbbf24;margin:5px 0 0;">⚠ Only TRON (TRC20) addresses. Wrong network = lost funds.</p>
                    </div>
                    <button type="submit" :disabled="usdtSubmitting || usdtAmount <= 0"
                            style="width:100%;padding:11px 0;border-radius:10px;border:none;font-size:13px;font-weight:700;color:black;cursor:pointer;transition:all .15s;background:#22d3ee;"
                            :style="(usdtSubmitting || usdtAmount <= 0) ? 'opacity:.5;cursor:not-allowed' : ''">
                        <span x-show="!usdtSubmitting">Request USDT Withdrawal</span>
                        <span x-show="usdtSubmitting" x-cloak>Submitting…</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- M-Pesa withdrawal form --}}
        @if($isKenya)
        <div x-show="method === 'mpesa'">
            <div style="border-radius:12px;border:1px solid #1f2937;background:rgba(17,24,39,0.6);overflow:hidden;">
                <div style="display:flex;align-items:center;gap:10px;padding:12px 14px;border-bottom:1px solid #1f2937;">
                    <div style="width:36px;height:36px;border-radius:10px;background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg style="width:16px;height:16px;color:#34d399;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    </div>
                    <div>
                        <p style="font-size:13px;font-weight:700;color:white;margin:0;">Withdraw via M-Pesa</p>
                        <p style="font-size:10px;color:#6b7280;margin:0;">Sent to Safaricom · 1–24 hrs after approval</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('withdrawals.mpesa') }}" style="padding:14px;display:flex;flex-direction:column;gap:12px;" @submit.prevent="mpesaSubmitting=true;$el.submit()">
                    @csrf
                    <div>
                        <p style="font-size:10px;font-weight:600;color:#6b7280;margin:0 0 6px;text-transform:uppercase;letter-spacing:.05em;">Amount (USD)</p>
                        <input type="number" name="amount" x-model.number="mpesaAmount" @input="calcKes()" min="1" max="10000" step="0.01" required placeholder="0.00"
                               style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid #374151;background:rgba(31,41,55,0.6);color:white;font-size:13px;font-family:monospace;outline:none;box-sizing:border-box;">
                        <div style="display:flex;gap:4px;margin-top:6px;">
                            @foreach([10, 25, 50, 100, 200] as $q)
                            <button type="button" @click="mpesaAmount={{ $q }};calcKes()"
                                    style="flex:1;padding:4px 0;border-radius:6px;font-size:10px;font-weight:600;cursor:pointer;border:1px solid #374151;color:#9ca3af;background:transparent;transition:all .15s;"
                                    :style="mpesaAmount === {{ $q }} ? 'border-color:rgba(16,185,129,.5);color:#34d399;background:rgba(16,185,129,.08)' : ''">
                                ${{ $q }}
                            </button>
                            @endforeach
                        </div>
                        <p style="font-size:10px;color:#34d399;margin:5px 0 0;" x-show="mpesaAmount > 0">
                            ≈ KES <span x-text="kesPreview.toLocaleString('en-KE',{minimumFractionDigits:2,maximumFractionDigits:2})"></span>
                            <span style="color:#6b7280;">(rate: {{ number_format($exchangeRate, 2) }})</span>
                        </p>
                    </div>
                    <div>
                        <p style="font-size:10px;font-weight:600;color:#6b7280;margin:0 0 6px;text-transform:uppercase;letter-spacing:.05em;">M-Pesa Phone Number</p>
                        <input type="tel" name="phone" required placeholder="0712 345 678"
                               style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid #374151;background:rgba(31,41,55,0.6);color:white;font-size:13px;outline:none;box-sizing:border-box;">
                        <p style="font-size:10px;color:#6b7280;margin:4px 0 0;">Kenyan: 07XXXXXXXX or 01XXXXXXXX</p>
                    </div>
                    <button type="submit" :disabled="mpesaSubmitting || mpesaAmount <= 0"
                            style="width:100%;padding:11px 0;border-radius:10px;border:none;font-size:13px;font-weight:700;color:white;cursor:pointer;background:linear-gradient(135deg,#10b981,#059669);transition:all .15s;"
                            :style="(mpesaSubmitting || mpesaAmount <= 0) ? 'opacity:.5;cursor:not-allowed' : ''">
                        <span x-show="!mpesaSubmitting">Request M-Pesa Withdrawal</span>
                        <span x-show="mpesaSubmitting" x-cloak>Submitting…</span>
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- Recent withdrawals --}}
        @if($recentWithdrawals->isNotEmpty())
        <div style="border-radius:12px;border:1px solid #1f2937;background:rgba(17,24,39,0.6);overflow:hidden;">
            <p style="font-size:11px;font-weight:600;color:#9ca3af;padding:10px 14px;border-bottom:1px solid #1f2937;margin:0;">Recent Withdrawals</p>
            @foreach($recentWithdrawals as $wd)
            <a href="{{ route('withdrawals.show', $wd) }}"
               style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-bottom:1px solid rgba(31,41,55,0.5);text-decoration:none;transition:background .1s;"
               onmouseover="this.style.background='rgba(31,41,55,0.4)'" onmouseout="this.style.background='transparent'">
                <div style="display:flex;align-items:center;gap:8px;">
                    <span style="width:6px;height:6px;border-radius:50%;flex-shrink:0;background:
                        @if($wd->status === 'successful') #34d399
                        @elseif($wd->status === 'failed') #f87171
                        @elseif($wd->status === 'cancelled') #6b7280
                        @else #fbbf24 @endif;"></span>
                    <div>
                        <p style="font-size:11px;font-weight:500;color:#d1d5db;margin:0;">{{ $wd->method_label }}</p>
                        <p style="font-size:10px;color:#6b7280;margin:0;">{{ $wd->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                <div style="text-align:right;">
                    <p style="font-size:11px;font-weight:700;font-family:monospace;color:white;margin:0;">${{ number_format($wd->usd_amount, 2) }}</p>
                    <p style="font-size:10px;color:
                        @if($wd->status === 'successful') #34d399
                        @elseif($wd->status === 'failed') #f87171
                        @else #fbbf24 @endif;margin:0;text-transform:capitalize;">{{ $wd->status }}</p>
                </div>
            </a>
            @endforeach
        </div>
        @endif

    </div>
</div>

<script>
function withdrawModalData() {
    return {
        method: 'usdt',
        usdtAmount: 0,
        mpesaAmount: 0,
        kesPreview: 0,
        rate: {{ $exchangeRate }},
        usdtSubmitting: false,
        mpesaSubmitting: false,
        calcKes() {
            this.kesPreview = this.mpesaAmount > 0 ? Math.round(this.mpesaAmount * this.rate * 100) / 100 : 0;
        },
    };
}
</script>
