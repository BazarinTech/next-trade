<div style="display:flex;flex-direction:column;">

    {{-- Header --}}
    <div style="padding:16px 20px;border-bottom:1px solid #1f2937;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;position:sticky;top:0;z-index:2;background:#0b1120;">
        <div>
            <h2 style="font-size:15px;font-weight:700;color:white;margin:0;">Wallet</h2>
            <p style="font-size:11px;color:#6b7280;margin:2px 0 0;">Your demo &amp; live balances</p>
        </div>
        <button @click="$store.modal.close()" style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;border-radius:8px;border:1px solid #374151;background:transparent;cursor:pointer;color:#9ca3af;font-size:16px;">&times;</button>
    </div>

    {{-- Body --}}
    <div style="padding:16px 20px;display:flex;flex-direction:column;gap:14px;">

        {{-- Demo Wallet Card --}}
        <div style="border-radius:12px;border:1px solid #1f2937;background:rgba(17,24,39,0.6);overflow:hidden;{{ $walletMode === 'demo' ? 'border-color:rgba(245,158,11,.3);' : '' }}">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border-bottom:1px solid #1f2937;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <div style="width:32px;height:32px;border-radius:8px;background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg style="width:14px;height:14px;color:#fbbf24;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <p style="font-size:11px;font-weight:700;color:#fbbf24;margin:0;">Demo Wallet</p>
                        <p style="font-size:9px;color:#6b7280;margin:0;">Practice · No real money</p>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:6px;">
                    @if($walletMode === 'demo')
                    <span style="font-size:9px;padding:2px 7px;border-radius:20px;background:rgba(6,182,212,0.1);border:1px solid rgba(6,182,212,0.2);color:#22d3ee;font-weight:600;">Active</span>
                    @endif
                    @if($demoWallet->isFrozen())
                    <span style="font-size:9px;padding:2px 7px;border-radius:20px;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);color:#f87171;">Frozen</span>
                    @endif
                </div>
            </div>
            <div style="padding:14px;">
                <p style="font-size:9px;color:#6b7280;margin:0 0 3px;">Available Balance</p>
                <p style="font-size:24px;font-weight:700;color:white;margin:0;">${{ number_format($demoWallet->available_balance, 2) }}</p>
                @if($demoWallet->locked_balance > 0)
                <p style="font-size:10px;color:#fbbf24;margin:3px 0 0;">${{ number_format($demoWallet->locked_balance, 2) }} locked in trades</p>
                @endif
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-top:10px;">
                    @foreach([
                        ['Total', '$'.number_format($demoWallet->balance,2), '#9ca3af'],
                        ['Profit', '$'.number_format($demoWallet->total_profit,2), '#34d399'],
                        ['Loss', '$'.number_format($demoWallet->total_loss,2), '#f87171'],
                        ['Net P&L', ($demoWallet->net_pnl >= 0 ? '+' : '').'$'.number_format(abs($demoWallet->net_pnl),2), $demoWallet->net_pnl >= 0 ? '#34d399' : '#f87171'],
                    ] as [$lbl, $val, $clr])
                    <div style="padding:8px;border-radius:8px;background:rgba(31,41,55,0.5);">
                        <p style="font-size:9px;color:#6b7280;margin:0 0 2px;">{{ $lbl }}</p>
                        <p style="font-size:12px;font-weight:600;color:{{ $clr }};margin:0;">{{ $val }}</p>
                    </div>
                    @endforeach
                </div>
                {{-- Reset demo --}}
                <div x-data="walletResetData()" style="margin-top:10px;">
                    <div x-show="resetMsg" x-cloak style="margin-bottom:8px;padding:8px 10px;border-radius:8px;font-size:11px;line-height:1.5;"
                         :style="resetMsg?.type === 'success' ? {background:'rgba(16,185,129,0.08)',border:'1px solid rgba(16,185,129,0.25)',color:'#34d399'} : {background:'rgba(239,68,68,0.08)',border:'1px solid rgba(239,68,68,0.25)',color:'#f87171'}">
                        <span x-text="resetMsg?.text"></span>
                    </div>
                    <form method="POST" action="{{ route('wallet.demo.reset') }}" @submit.prevent="handleReset($el)">
                        @csrf
                        <button type="submit" :disabled="resetting"
                                style="width:100%;padding:8px;border-radius:8px;font-size:11px;font-weight:600;cursor:pointer;border:1px solid;transition:all .18s;appearance:none;-webkit-appearance:none;display:flex;align-items:center;justify-content:center;gap:5px;"
                                :style="resetting ? {borderColor:'rgba(245,158,11,.3)',color:'rgba(251,191,36,.5)',background:'rgba(245,158,11,.04)',cursor:'not-allowed'}
                                      : conf ? {borderColor:'rgba(245,158,11,.6)',color:'#fbbf24',background:'rgba(245,158,11,.12)'}
                                      : {borderColor:'rgba(55,65,81,.8)',color:'#6b7280',background:'rgba(31,41,55,0.4)'}">
                            <template x-if="resetting">
                                <span style="display:flex;align-items:center;gap:5px;">
                                    <svg style="width:12px;height:12px;animation:nt-spin 1s linear infinite;" fill="none" viewBox="0 0 24 24"><circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    Resetting…
                                </span>
                            </template>
                            <template x-if="!resetting && conf">
                                <span style="display:flex;align-items:center;gap:5px;">
                                    <svg style="width:11px;height:11px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    Tap again to confirm reset
                                </span>
                            </template>
                            <template x-if="!resetting && !conf">
                                <span style="display:flex;align-items:center;gap:5px;">
                                    <svg style="width:11px;height:11px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    Reset Demo to $10,000
                                </span>
                            </template>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Live Wallet Card --}}
        <div style="border-radius:12px;border:1px solid #1f2937;background:rgba(17,24,39,0.6);overflow:hidden;{{ $walletMode === 'live' ? 'border-color:rgba(6,182,212,.3);' : '' }}">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border-bottom:1px solid #1f2937;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <div style="width:32px;height:32px;border-radius:8px;background:rgba(6,182,212,0.1);border:1px solid rgba(6,182,212,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg style="width:14px;height:14px;color:#22d3ee;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                    <div>
                        <p style="font-size:11px;font-weight:700;color:#22d3ee;margin:0;">Live Wallet</p>
                        <p style="font-size:9px;color:#6b7280;margin:0;">Real funds · Real trades</p>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:6px;">
                    @if($walletMode === 'live')
                    <span style="font-size:9px;padding:2px 7px;border-radius:20px;background:rgba(6,182,212,0.1);border:1px solid rgba(6,182,212,0.2);color:#22d3ee;font-weight:600;">Active</span>
                    @endif
                    @if($liveWallet->isFrozen())
                    <span style="font-size:9px;padding:2px 7px;border-radius:20px;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);color:#f87171;">Frozen</span>
                    @endif
                </div>
            </div>
            <div style="padding:14px;">
                <p style="font-size:9px;color:#6b7280;margin:0 0 3px;">Available Balance</p>
                <p style="font-size:24px;font-weight:700;color:white;margin:0;">${{ number_format($liveWallet->available_balance, 2) }}</p>
                @if($liveWallet->locked_balance > 0)
                <p style="font-size:10px;color:#fbbf24;margin:3px 0 0;">${{ number_format($liveWallet->locked_balance, 2) }} locked</p>
                @endif
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-top:10px;">
                    @foreach([
                        ['Deposited', '$'.number_format($liveWallet->total_deposited,2), '#22d3ee'],
                        ['Withdrawn', '$'.number_format($liveWallet->total_withdrawn,2), '#9ca3af'],
                        ['Profit', '$'.number_format($liveWallet->total_profit,2), '#34d399'],
                        ['Loss', '$'.number_format($liveWallet->total_loss,2), '#f87171'],
                    ] as [$lbl, $val, $clr])
                    <div style="padding:8px;border-radius:8px;background:rgba(31,41,55,0.5);">
                        <p style="font-size:9px;color:#6b7280;margin:0 0 2px;">{{ $lbl }}</p>
                        <p style="font-size:12px;font-weight:600;color:{{ $clr }};margin:0;">{{ $val }}</p>
                    </div>
                    @endforeach
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-top:8px;">
                    <button type="button" @click="$store.modal.open('deposit')"
                            style="padding:8px;border-radius:8px;border:none;font-size:11px;font-weight:700;color:black;cursor:pointer;background:#22d3ee;appearance:none;-webkit-appearance:none;">
                        Deposit
                    </button>
                    <button type="button" @click="$store.modal.open('withdraw')"
                            style="padding:8px;border-radius:8px;border:1px solid #374151;font-size:11px;font-weight:600;color:#d1d5db;cursor:pointer;background:rgba(31,41,55,0.4);transition:all .15s;appearance:none;-webkit-appearance:none;"
                            onmouseover="this.style.borderColor='rgba(6,182,212,.4)'" onmouseout="this.style.borderColor='#374151'">
                        Withdraw
                    </button>
                </div>
            </div>
        </div>

        {{-- Recent Transactions --}}
        @if($recent->isNotEmpty())
        <div style="border-radius:12px;border:1px solid #1f2937;background:rgba(17,24,39,0.6);overflow:hidden;">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-bottom:1px solid #1f2937;">
                <p style="font-size:11px;font-weight:600;color:#9ca3af;margin:0;">Recent Transactions</p>
                <button type="button" @click="$store.modal.open('history')" style="font-size:10px;color:#22d3ee;background:none;border:none;cursor:pointer;padding:0;">View all →</button>
            </div>
            @foreach($recent as $txn)
            @php $isCredit = $txn->isCredit(); @endphp
            <div style="display:flex;align-items:center;justify-content:space-between;padding:9px 14px;border-bottom:1px solid rgba(31,41,55,0.5);">
                <div style="display:flex;align-items:center;gap:8px;">
                    <div style="width:28px;height:28px;border-radius:8px;border:1px solid;display:flex;align-items:center;justify-content:center;flex-shrink:0;
                        {{ $isCredit ? 'background:rgba(16,185,129,.08);border-color:rgba(16,185,129,.2)' : 'background:rgba(239,68,68,.08);border-color:rgba(239,68,68,.2)' }}">
                        <svg style="width:11px;height:11px;color:{{ $isCredit ? '#34d399' : '#f87171' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($isCredit)
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                            @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                            @endif
                        </svg>
                    </div>
                    <div>
                        <p style="font-size:11px;font-weight:500;color:#d1d5db;margin:0;">{{ $txn->getTypeLabel() }}</p>
                        <p style="font-size:9px;color:#6b7280;margin:0;">{{ $txn->created_at->format('M d, H:i') }} · {{ ucfirst($txn->wallet->type) }}</p>
                    </div>
                </div>
                <span style="font-size:12px;font-weight:700;font-family:monospace;color:{{ $isCredit ? '#34d399' : '#f87171' }};">
                    {{ $txn->getSignedAmount() }}
                </span>
            </div>
            @endforeach
        </div>
        @endif

    </div>
</div>

<style>@keyframes nt-spin { to { transform: rotate(360deg); } }</style>

<script>
function walletResetData() {
    return {
        conf: false,
        resetting: false,
        resetMsg: null,
        _confTimer: null,

        async handleReset(form) {
            if (!this.conf) {
                this.conf = true;
                clearTimeout(this._confTimer);
                this._confTimer = setTimeout(() => { this.conf = false; }, 5000);
                return;
            }
            clearTimeout(this._confTimer);
            this.resetting = true;
            this.resetMsg = null;
            try {
                const fd = new FormData(form);
                const res = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd,
                });
                const data = await res.json();
                if (res.ok) {
                    this.resetMsg = { type: 'success', text: data.message };
                    this.conf = false;
                    setTimeout(() => $store.modal.open('wallet'), 1200);
                } else {
                    this.resetMsg = { type: 'error', text: data.message || 'Reset failed. Please try again.' };
                    this.conf = false;
                }
            } catch (_) {
                this.resetMsg = { type: 'error', text: 'Network error. Please try again.' };
                this.conf = false;
            } finally {
                this.resetting = false;
            }
        },
    };
}
</script>
