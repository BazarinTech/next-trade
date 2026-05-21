<div x-data="botsModalData()" style="display:flex;flex-direction:column;">

    {{-- Header --}}
    <div style="padding:14px 20px;border-bottom:1px solid #1f2937;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;position:sticky;top:0;z-index:2;background:#0b1120;">
        <div>
            <h2 style="font-size:15px;font-weight:700;color:white;margin:0;" x-show="!selectedPlan">Bot Investments</h2>
            <h2 style="font-size:15px;font-weight:700;color:white;margin:0;" x-show="selectedPlan" x-cloak>
                Invest in <span x-text="selectedPlan?.name"></span>
            </h2>
            <p style="font-size:11px;color:#6b7280;margin:2px 0 0;" x-show="!selectedPlan">Educational simulation · {{ ucfirst($walletMode) }} wallet</p>
            <p style="font-size:11px;color:#6b7280;margin:2px 0 0;" x-show="selectedPlan" x-cloak>{{ ucfirst($walletMode) }} Wallet · ${{ number_format($wallet->available_balance, 2) }} available</p>
        </div>
        <div style="display:flex;align-items:center;gap:6px;">
            <button x-show="selectedPlan" x-cloak @click="selectedPlan = null; amount = 0; investMessage = null;"
                    style="padding:4px 10px;border-radius:7px;border:1px solid #374151;background:transparent;cursor:pointer;color:#9ca3af;font-size:11px;appearance:none;-webkit-appearance:none;">← Back</button>
            <button @click="$store.modal.close()" style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;border-radius:8px;border:1px solid #374151;background:transparent;cursor:pointer;color:#9ca3af;font-size:16px;">&times;</button>
        </div>
    </div>

    {{-- Tab bar (hidden when invest form is open) --}}
    <div x-show="!selectedPlan" style="padding:10px 20px;border-bottom:1px solid #1f2937;flex-shrink:0;">
        <div style="display:flex;background:rgba(17,24,39,0.9);border:1px solid #374151;border-radius:10px;padding:3px;gap:3px;">
            <button type="button" @click="activeTab='plans'"
                    style="flex:1;padding:7px 0;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;transition:all .15s;border:none;appearance:none;-webkit-appearance:none;"
                    :style="activeTab==='plans' ? {background:'rgba(6,182,212,0.15)',color:'#22d3ee',border:'1px solid rgba(6,182,212,0.3)'} : {background:'transparent',color:'#6b7280',border:'1px solid transparent'}">
                Available Plans
            </button>
            <button type="button" @click="activeTab='investments'"
                    style="flex:1;padding:7px 0;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;transition:all .15s;border:none;appearance:none;-webkit-appearance:none;display:flex;align-items:center;justify-content:center;gap:5px;"
                    :style="activeTab==='investments' ? {background:'rgba(6,182,212,0.15)',color:'#22d3ee',border:'1px solid rgba(6,182,212,0.3)'} : {background:'transparent',color:'#6b7280',border:'1px solid transparent'}">
                My Bots
                @if($active->count() > 0)
                <span style="min-width:18px;height:18px;padding:0 4px;border-radius:20px;font-size:9px;font-weight:700;display:inline-flex;align-items:center;justify-content:center;"
                      :style="activeTab==='investments' ? {background:'#06b6d4',color:'black'} : {background:'rgba(6,182,212,0.2)',color:'#22d3ee'}">
                    {{ $active->count() }}
                </span>
                @endif
            </button>
        </div>
    </div>

    {{-- ═══════════════════════════════ PLANS TAB ═══════════════════════════════ --}}
    <div x-show="!selectedPlan && activeTab==='plans'" style="padding:16px 20px;display:flex;flex-direction:column;gap:16px;">

        @if($walletMode !== 'live')
        {{-- Demo mode lock --}}
        <div style="display:flex;align-items:flex-start;gap:12px;padding:14px 16px;border-radius:12px;background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.2);">
            <div style="width:32px;height:32px;border-radius:9px;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg style="width:15px;height:15px;color:#f87171;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <div>
                <p style="font-size:12px;font-weight:700;color:#f87171;margin:0 0 3px;">Live Wallet Required</p>
                <p style="font-size:11px;color:rgba(252,165,165,.75);margin:0;line-height:1.5;">Bot investments can only be made from your <strong style="color:#fca5a5;">live wallet</strong>. Switch to live mode to get started.</p>
            </div>
        </div>
        @else
        {{-- Live mode info --}}
        <div style="display:flex;align-items:flex-start;gap:10px;padding:11px 13px;border-radius:11px;background:rgba(16,185,129,0.06);border:1px solid rgba(16,185,129,0.15);">
            <svg style="width:13px;height:13px;color:#34d399;flex-shrink:0;margin-top:1px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p style="font-size:11px;color:rgba(52,211,153,.9);margin:0;line-height:1.5;"><strong>Live Wallet Active.</strong> Earnings are credited to your live wallet daily.</p>
        </div>
        @endif

        {{-- Available plans --}}
        <div style="display:flex;flex-direction:column;gap:12px;">
            @forelse($plans as $plan)
            @php
                $rcMap  = ['green'=>'#34d399','cyan'=>'#22d3ee','amber'=>'#fbbf24','red'=>'#f87171','purple'=>'#a78bfa','emerald'=>'#34d399'];
                $planClr = $rcMap[$plan->risk_color] ?? '#22d3ee';
            @endphp
            <div style="border-radius:14px;border:1px solid #1f2937;background:rgba(17,24,39,0.6);overflow:hidden;">
                <div style="padding:14px 16px;border-bottom:1px solid #1f2937;display:flex;align-items:center;justify-content:space-between;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:36px;height:36px;border-radius:10px;background:rgba(0,0,0,.25);border:1px solid rgba(255,255,255,.08);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg style="width:16px;height:16px;color:{{ $planClr }};" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 3H7a2 2 0 00-2 2v2M9 3h6M9 3v2m6-2h2a2 2 0 012 2v2M3 10h2m14 0h2M5 5v14a2 2 0 002 2h10a2 2 0 002-2V5"/></svg>
                        </div>
                        <div>
                            <p style="font-size:13px;font-weight:700;color:white;margin:0 0 2px;">{{ $plan->name }}</p>
                            <p style="font-size:10px;color:#6b7280;margin:0;line-height:1.4;">{{ $plan->description }}</p>
                        </div>
                    </div>
                    <span style="font-size:10px;padding:3px 9px;border-radius:20px;font-weight:600;white-space:nowrap;color:{{ $planClr }};background:rgba(0,0,0,.25);border:1px solid rgba(255,255,255,.08);">
                        {{ $plan->risk_label }} Risk
                    </span>
                </div>
                <div style="padding:14px 16px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;">
                    <div>
                        <p style="font-size:10px;color:#6b7280;margin:0 0 4px;">Daily ROI</p>
                        <p style="font-size:18px;font-weight:700;color:{{ $planClr }};margin:0;">{{ $plan->daily_roi_percent }}%</p>
                    </div>
                    <div>
                        <p style="font-size:10px;color:#6b7280;margin:0 0 4px;">Duration</p>
                        <p style="font-size:14px;font-weight:700;color:white;margin:0;">{{ $plan->duration_label }}</p>
                    </div>
                    <div>
                        <p style="font-size:10px;color:#6b7280;margin:0 0 4px;">Min Invest</p>
                        <p style="font-size:14px;font-weight:600;color:#d1d5db;margin:0;">${{ number_format($plan->min_investment, 2) }}</p>
                    </div>
                </div>
                <div style="padding:0 16px 16px;">
                    @if($walletMode === 'live')
                    <button type="button" @click="openInvest({{ json_encode(['id'=>$plan->id,'name'=>$plan->name,'daily_roi'=>(float)$plan->daily_roi_percent,'min'=>(float)$plan->min_investment,'max'=>$plan->max_investment?(float)$plan->max_investment:null,'duration'=>$plan->duration_days]) }})"
                            style="width:100%;padding:11px 0;border-radius:10px;border:none;font-size:13px;font-weight:700;color:black;cursor:pointer;background:{{ $planClr }};transition:opacity .15s;appearance:none;-webkit-appearance:none;"
                            onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                        Invest Now
                    </button>
                    @else
                    <button type="button" disabled
                            style="width:100%;padding:11px 0;border-radius:10px;border:1px solid rgba(75,85,99,0.3);font-size:13px;font-weight:700;color:#4b5563;cursor:not-allowed;background:rgba(31,41,55,0.4);appearance:none;-webkit-appearance:none;display:flex;align-items:center;justify-content:center;gap:6px;">
                        <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Live Wallet Only
                    </button>
                    @endif
                </div>
            </div>
            @empty
            <div style="text-align:center;padding:40px 0;">
                <p style="font-size:13px;color:#4b5563;margin:0;">No active bot plans available.</p>
            </div>
            @endforelse
        </div>
        <div style="height:4px;"></div>
    </div>

    {{-- ══════════════════════════ MY INVESTMENTS TAB ════════════════════════════ --}}
    <div x-show="!selectedPlan && activeTab==='investments'" x-cloak style="padding:16px 20px;display:flex;flex-direction:column;gap:16px;">

        {{-- Portfolio stats --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
            @foreach([
                ['Total Invested', '$'.number_format($totalInvested, 2), '#22d3ee'],
                ['Total Earned',   '$'.number_format($totalEarned, 2),   '#34d399'],
                ["Today's Earning",'$'.number_format($todayEarnings, 2), '#a78bfa'],
                ['Running Bots',   $active->count().' active',           '#fbbf24'],
            ] as [$lbl, $val, $clr])
            <div style="padding:14px;border-radius:10px;border:1px solid #1f2937;background:rgba(17,24,39,0.6);">
                <p style="font-size:10px;color:#6b7280;margin:0 0 5px;text-transform:uppercase;letter-spacing:.04em;">{{ $lbl }}</p>
                <p style="font-size:17px;font-weight:700;color:{{ $clr }};margin:0;">{{ $val }}</p>
            </div>
            @endforeach
        </div>

        {{-- Investment cards --}}
        @if($active->count())
        <div style="display:flex;flex-direction:column;gap:12px;">
            @foreach($active as $inv)
            @php
                $scMap  = ['green'=>'#34d399','cyan'=>'#22d3ee','amber'=>'#fbbf24','red'=>'#f87171','emerald'=>'#34d399','purple'=>'#a78bfa'];
                $invClr = $scMap[$inv->status_color] ?? '#22d3ee';
                $daysLeft = $inv->days_remaining;
                $dailyEst = $inv->daily_earning_amount;
            @endphp
            <div id="inv-card-{{ $inv->id }}" style="border-radius:14px;border:1px solid #1f2937;background:rgba(17,24,39,0.6);overflow:hidden;">

                {{-- Card header --}}
                <div style="padding:12px 16px;border-bottom:1px solid rgba(31,41,55,0.7);display:flex;align-items:center;justify-content:space-between;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div style="width:30px;height:30px;border-radius:8px;background:rgba(0,0,0,.25);border:1px solid rgba(255,255,255,.08);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg style="width:13px;height:13px;color:{{ $invClr }};" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 3H7a2 2 0 00-2 2v2M9 3h6M9 3v2m6-2h2a2 2 0 012 2v2M3 10h2m14 0h2M5 5v14a2 2 0 002 2h10a2 2 0 002-2V5"/></svg>
                        </div>
                        <div>
                            <p style="font-size:13px;font-weight:700;color:white;margin:0;">{{ $inv->botPlan->name }}</p>
                            <p style="font-size:10px;color:#6b7280;margin:0;">Started {{ $inv->started_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                    <span style="font-size:10px;padding:3px 9px;border-radius:20px;font-weight:600;color:{{ $invClr }};background:rgba(0,0,0,.25);border:1px solid rgba(255,255,255,.08);">
                        {{ ucfirst($inv->status) }}
                    </span>
                </div>

                {{-- Stats grid --}}
                <div style="padding:14px 16px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;border-bottom:1px solid rgba(31,41,55,0.7);">
                    <div>
                        <p style="font-size:10px;color:#6b7280;margin:0 0 4px;">Principal</p>
                        <p style="font-size:15px;font-weight:700;color:#22d3ee;margin:0;">${{ number_format($inv->principal_amount, 2) }}</p>
                    </div>
                    <div>
                        <p style="font-size:10px;color:#6b7280;margin:0 0 4px;">Daily</p>
                        <p style="font-size:15px;font-weight:700;color:#34d399;margin:0;">{{ $inv->daily_roi_percent }}%</p>
                        <p style="font-size:10px;color:#34d399;margin:1px 0 0;">≈${{ number_format($dailyEst, 2) }}/d</p>
                    </div>
                    <div>
                        <p style="font-size:10px;color:#6b7280;margin:0 0 4px;">Earned</p>
                        <p style="font-size:15px;font-weight:700;color:#34d399;margin:0;">${{ number_format($inv->total_earned, 2) }}</p>
                    </div>
                </div>

                {{-- Progress --}}
                @if($inv->ends_at)
                <div style="padding:14px 16px;border-bottom:1px solid rgba(31,41,55,0.7);">
                    <div style="display:flex;justify-content:space-between;font-size:10px;margin-bottom:6px;">
                        <span style="color:#6b7280;font-weight:500;text-transform:uppercase;letter-spacing:.04em;">Progress</span>
                        <span style="color:#9ca3af;">
                            @if($daysLeft !== null && $daysLeft > 0)
                                <span style="color:#fbbf24;font-weight:600;">{{ $daysLeft }}d left</span> &nbsp;·&nbsp;
                            @elseif($daysLeft === 0)
                                <span style="color:#34d399;font-weight:600;">Completing today</span> &nbsp;·&nbsp;
                            @endif
                            <span style="font-weight:600;">{{ $inv->progress_percent }}%</span>
                        </span>
                    </div>
                    <div style="width:100%;background:rgba(31,41,55,0.9);border-radius:20px;height:6px;overflow:hidden;">
                        <div style="height:6px;border-radius:20px;background:{{ $invClr }};width:{{ min(100, $inv->progress_percent) }}%;transition:width .3s;box-shadow:0 0 6px {{ $invClr }}66;"></div>
                    </div>
                    <p style="font-size:10px;color:#4b5563;margin:5px 0 0;">Ends {{ $inv->ends_at->format('M d, Y') }}</p>
                </div>
                @endif

                {{-- Footer: wallet tag + cancel --}}
                <div style="padding:12px 16px 14px;">
                    <div x-data="{ cancelling{{ $inv->id }}: false, cancelled{{ $inv->id }}: false, confirmCancel{{ $inv->id }}: false }">

                        {{-- Default row --}}
                        <div x-show="!cancelled{{ $inv->id }} && !confirmCancel{{ $inv->id }}"
                             style="display:flex;align-items:center;justify-content:space-between;gap:8px;">
                            <span style="display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:500;color:#6b7280;padding:3px 9px;border-radius:20px;background:rgba(31,41,55,0.7);border:1px solid rgba(55,65,81,0.6);">
                                <svg style="width:9px;height:9px;opacity:.6;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                {{ ucfirst($inv->wallet_type) }} wallet
                            </span>
                            <button type="button"
                                    @click="confirmCancel{{ $inv->id }} = true"
                                    style="display:inline-flex;align-items:center;gap:5px;padding:6px 13px;border-radius:8px;border:1px solid rgba(239,68,68,0.25);background:rgba(239,68,68,0.06);font-size:11px;font-weight:600;color:#f87171;cursor:pointer;appearance:none;-webkit-appearance:none;transition:all .15s;"
                                    onmouseover="this.style.background='rgba(239,68,68,0.12)';this.style.borderColor='rgba(239,68,68,0.4)'"
                                    onmouseout="this.style.background='rgba(239,68,68,0.06)';this.style.borderColor='rgba(239,68,68,0.25)'">
                                <svg style="width:11px;height:11px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                Cancel Investment
                            </button>
                        </div>

                        {{-- Confirm row --}}
                        <div x-show="!cancelled{{ $inv->id }} && confirmCancel{{ $inv->id }}" x-cloak
                             style="padding:12px 14px;border-radius:10px;background:rgba(239,68,68,0.07);border:1px solid rgba(239,68,68,0.22);">
                            <p style="font-size:11px;color:#fca5a5;margin:0 0 10px;line-height:1.5;">
                                <strong style="color:#f87171;">Cancel this investment?</strong><br>
                                Your principal will be refunded to your {{ $inv->wallet_type }} wallet.
                            </p>
                            <div style="display:flex;gap:8px;">
                                <button type="button" @click="confirmCancel{{ $inv->id }} = false"
                                        style="flex:1;padding:8px 0;border-radius:8px;border:1px solid #374151;background:rgba(31,41,55,0.6);font-size:12px;font-weight:600;color:#9ca3af;cursor:pointer;appearance:none;-webkit-appearance:none;"
                                        onmouseover="this.style.color='white'" onmouseout="this.style.color='#9ca3af'">
                                    Keep It
                                </button>
                                <button type="button"
                                        :disabled="cancelling{{ $inv->id }}"
                                        @click="
                                            cancelling{{ $inv->id }} = true;
                                            fetch('{{ route('bots.cancel', $inv) }}', {
                                                method: 'POST',
                                                headers: {
                                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                                    'Accept': 'application/json',
                                                    'X-Requested-With': 'XMLHttpRequest'
                                                }
                                            })
                                            .then(r => r.json())
                                            .then(d => {
                                                if (d.success) {
                                                    cancelled{{ $inv->id }} = true;
                                                    const card = document.getElementById('inv-card-{{ $inv->id }}');
                                                    if (card) { card.style.opacity = '0'; card.style.transition = 'opacity .3s'; setTimeout(() => card.remove(), 320); }
                                                } else {
                                                    confirmCancel{{ $inv->id }} = false;
                                                }
                                            })
                                            .catch(() => { confirmCancel{{ $inv->id }} = false; })
                                            .finally(() => { cancelling{{ $inv->id }} = false; })
                                        "
                                        style="flex:1;padding:8px 0;border-radius:8px;border:1px solid rgba(239,68,68,0.4);background:rgba(239,68,68,0.14);font-size:12px;font-weight:600;color:#f87171;cursor:pointer;appearance:none;-webkit-appearance:none;display:flex;align-items:center;justify-content:center;gap:5px;"
                                        :style="cancelling{{ $inv->id }} ? {opacity:.5,cursor:'not-allowed'} : {}">
                                    <template x-if="cancelling{{ $inv->id }}">
                                        <svg style="width:11px;height:11px;animation:nt-spin 1s linear infinite;" fill="none" viewBox="0 0 24 24"><circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                    </template>
                                    <span x-text="cancelling{{ $inv->id }} ? 'Cancelling…' : 'Yes, Cancel'"></span>
                                </button>
                            </div>
                        </div>

                        {{-- Success state --}}
                        <div x-show="cancelled{{ $inv->id }}" x-cloak
                             style="display:flex;align-items:center;gap:6px;padding:8px 12px;border-radius:8px;background:rgba(16,185,129,0.07);border:1px solid rgba(16,185,129,0.2);">
                            <svg style="width:13px;height:13px;color:#34d399;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span style="font-size:11px;color:#34d399;font-weight:500;">Investment cancelled — principal refunded.</span>
                        </div>

                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @else
        {{-- Empty state --}}
        <div style="text-align:center;padding:48px 20px;">
            <div style="width:52px;height:52px;border-radius:16px;background:rgba(75,85,99,0.1);border:1px solid rgba(75,85,99,0.15);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                <svg style="width:22px;height:22px;color:#4b5563;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 3H7a2 2 0 00-2 2v2M9 3h6M9 3v2m6-2h2a2 2 0 012 2v2M3 10h2m14 0h2M5 5v14a2 2 0 002 2h10a2 2 0 002-2V5"/></svg>
            </div>
            <p style="font-size:13px;font-weight:500;color:#4b5563;margin:0 0 10px;">No running investments</p>
            <button type="button" @click="activeTab='plans'"
                    style="padding:8px 18px;border-radius:8px;border:1px solid rgba(6,182,212,0.35);background:rgba(6,182,212,0.09);font-size:12px;font-weight:600;color:#22d3ee;cursor:pointer;appearance:none;-webkit-appearance:none;">
                Browse Plans
            </button>
        </div>
        @endif

        <div style="height:4px;"></div>
    </div>

    {{-- ═══════════════════════════ INVEST FORM PANEL ════════════════════════════ --}}
    <div x-show="selectedPlan" x-cloak style="padding:16px 20px;display:flex;flex-direction:column;gap:12px;">

        {{-- Wallet balance --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;border-radius:10px;border:1px solid #1f2937;background:rgba(31,41,55,0.4);">
            <span style="font-size:11px;color:#6b7280;">{{ ucfirst($walletMode) }} Wallet Balance</span>
            <span style="font-size:13px;font-weight:700;color:#22d3ee;">${{ number_format($wallet->available_balance, 2) }}</span>
        </div>

        <form method="POST" action="{{ route('bots.invest') }}" @submit.prevent="handleInvest($el)">
            @csrf
            <input type="hidden" name="bot_plan_id" :value="selectedPlan?.id">

            {{-- Amount --}}
            <div style="margin-bottom:12px;">
                <p style="font-size:10px;font-weight:600;color:#6b7280;margin:0 0 6px;text-transform:uppercase;letter-spacing:.05em;">Investment Amount (USD)</p>
                <input type="number" name="amount" x-model.number="amount" @input="calcEst()"
                       :min="selectedPlan?.min" :max="selectedPlan?.max ?? undefined" step="0.01" required
                       style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid #374151;background:rgba(31,41,55,0.6);color:white;font-size:14px;font-family:monospace;outline:none;box-sizing:border-box;">
                <div style="display:flex;justify-content:space-between;font-size:10px;color:#6b7280;margin-top:4px;">
                    <span>Min: $<span x-text="selectedPlan?.min?.toFixed(2) ?? '—'"></span></span>
                    <span x-show="selectedPlan?.max">Max: $<span x-text="selectedPlan?.max?.toFixed(2) ?? ''"></span></span>
                </div>
            </div>

            {{-- Quick amounts --}}
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:4px;margin-bottom:12px;">
                @foreach([10, 25, 50, 100] as $q)
                <button type="button" @click="amount={{ $q }};calcEst()"
                        style="padding:6px 0;border-radius:6px;font-size:10px;font-weight:600;cursor:pointer;transition:all .15s;appearance:none;-webkit-appearance:none;"
                        :style="amount === {{ $q }} ? {border:'1px solid rgba(6,182,212,.5)',color:'#22d3ee',background:'rgba(6,182,212,.08)'} : {border:'1px solid #374151',color:'#9ca3af',background:'rgba(31,41,55,0.5)'}">
                    ${{ $q }}
                </button>
                @endforeach
            </div>

            {{-- Estimates --}}
            <div style="padding:12px;border-radius:10px;border:1px solid #1f2937;background:rgba(31,41,55,0.4);margin-bottom:14px;">
                <div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:6px;">
                    <span style="color:#6b7280;">Daily ROI</span>
                    <span style="color:#34d399;font-weight:600;" x-text="selectedPlan ? selectedPlan.daily_roi + '%' : '—'"></span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:6px;">
                    <span style="color:#6b7280;">Est. Daily Earning</span>
                    <span style="color:#34d399;font-weight:700;">+$<span x-text="estDaily.toFixed(4)"></span></span>
                </div>
                <template x-if="selectedPlan?.duration">
                    <div style="display:flex;justify-content:space-between;font-size:11px;">
                        <span style="color:#6b7280;">Est. Total (<span x-text="selectedPlan.duration"></span> days)</span>
                        <span style="color:#22d3ee;font-weight:700;">+$<span x-text="estTotal.toFixed(2)"></span></span>
                    </div>
                </template>
            </div>

            {{-- Inline message --}}
            <div x-show="investMessage" x-cloak style="margin-bottom:12px;padding:10px 12px;border-radius:8px;font-size:11px;line-height:1.5;"
                 :style="investMessage?.type === 'success' ? {background:'rgba(16,185,129,0.08)',border:'1px solid rgba(16,185,129,0.25)',color:'#34d399'} : {background:'rgba(239,68,68,0.08)',border:'1px solid rgba(239,68,68,0.25)',color:'#f87171'}">
                <span x-text="investMessage?.text"></span>
            </div>

            <button type="submit" :disabled="investing || !amount || amount <= 0"
                    style="width:100%;padding:12px 0;border-radius:10px;border:none;font-size:13px;font-weight:700;color:black;cursor:pointer;background:linear-gradient(135deg,#06b6d4,#0891b2);transition:all .15s;appearance:none;-webkit-appearance:none;display:flex;align-items:center;justify-content:center;gap:6px;"
                    :style="(investing || !amount || amount <= 0) ? {opacity:0.5,cursor:'not-allowed'} : {}">
                <span x-show="!investing">Confirm Investment</span>
                <template x-if="investing">
                    <span style="display:flex;align-items:center;gap:6px;">
                        <svg style="width:14px;height:14px;animation:nt-spin 1s linear infinite;" fill="none" viewBox="0 0 24 24"><circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        Processing…
                    </span>
                </template>
            </button>
        </form>

    </div>

</div>

<style>@keyframes nt-spin { to { transform: rotate(360deg); } }</style>

<script>
function botsModalData() {
    return {
        activeTab: 'plans',
        selectedPlan: null,
        amount: 0,
        estDaily: 0,
        estTotal: 0,
        investing: false,
        investMessage: null,

        openInvest(plan) {
            this.selectedPlan = plan;
            this.amount = plan.min ?? 0;
            this.investMessage = null;
            this.calcEst();
        },

        calcEst() {
            if (!this.selectedPlan || !this.amount) { this.estDaily = 0; this.estTotal = 0; return; }
            this.estDaily = this.amount * this.selectedPlan.daily_roi / 100;
            this.estTotal = this.selectedPlan.duration ? this.estDaily * this.selectedPlan.duration : 0;
        },

        async handleInvest(form) {
            if (!this.amount || this.amount <= 0) return;
            this.investing = true;
            this.investMessage = null;
            try {
                const fd = new FormData(form);
                const res = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd,
                });
                const data = await res.json();
                if (res.ok) {
                    this.investMessage = { type: 'success', text: data.message ?? 'Investment created successfully.' };
                    setTimeout(() => {
                        this.selectedPlan = null;
                        this.investMessage = null;
                        this.amount = 0;
                        this.activeTab = 'investments';
                        // Reload modal to reflect new investment in My Bots tab
                        $store.modal.open('bots');
                    }, 1800);
                } else {
                    const msg = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Investment failed. Please try again.');
                    this.investMessage = { type: 'error', text: msg };
                }
            } catch (_) {
                this.investMessage = { type: 'error', text: 'Network error. Please try again.' };
            } finally {
                this.investing = false;
            }
        },
    };
}
</script>
