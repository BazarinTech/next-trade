<div x-data="botsModalData()" style="display:flex;flex-direction:column;">

    {{-- Header --}}
    <div style="padding:16px 20px;border-bottom:1px solid #1f2937;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;position:sticky;top:0;z-index:2;background:#0b1120;">
        <div>
            <h2 style="font-size:15px;font-weight:700;color:white;margin:0;" x-show="!selectedPlan">Bot Investments</h2>
            <h2 style="font-size:15px;font-weight:700;color:white;margin:0;" x-show="selectedPlan" x-cloak>
                Invest in <span x-text="selectedPlan?.name"></span>
            </h2>
            <p style="font-size:11px;color:#6b7280;margin:2px 0 0;" x-show="!selectedPlan">Educational simulation · {{ ucfirst($walletMode) }} wallet</p>
            <p style="font-size:11px;color:#6b7280;margin:2px 0 0;" x-show="selectedPlan" x-cloak>{{ ucfirst($walletMode) }} Wallet · ${{ number_format($wallet->available_balance, 2) }} available</p>
        </div>
        <div style="display:flex;align-items:center;gap:6px;">
            <button x-show="selectedPlan" x-cloak @click="selectedPlan = null; amount = 0;"
                    style="padding:4px 10px;border-radius:7px;border:1px solid #374151;background:transparent;cursor:pointer;color:#9ca3af;font-size:11px;">← Back</button>
            <button @click="$store.modal.close()" style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;border-radius:8px;border:1px solid #374151;background:transparent;cursor:pointer;color:#9ca3af;font-size:16px;">&times;</button>
        </div>
    </div>

    {{-- Plans panel --}}
    <div x-show="!selectedPlan" style="padding:20px;display:flex;flex-direction:column;gap:20px;">

        {{-- Sim warning --}}
        <div style="display:flex;align-items:flex-start;gap:10px;padding:12px 14px;border-radius:12px;background:rgba(245,158,11,0.07);border:1px solid rgba(245,158,11,0.2);">
            <svg style="width:14px;height:14px;color:#fbbf24;flex-shrink:0;margin-top:1px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <p style="font-size:11px;color:rgba(251,191,36,.9);margin:0;line-height:1.5;"><strong>Educational Simulation Only.</strong> All earnings are simulated. No real money involved.</p>
        </div>

        {{-- Portfolio summary --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
            @foreach([
                ['Invested', '$'.number_format($totalInvested,2), '#22d3ee'],
                ['Earned', '$'.number_format($totalEarned,2), '#34d399'],
                ["Today's", '$'.number_format($todayEarnings,2), '#a78bfa'],
                ['Active', $active->count().' bots', '#fbbf24'],
            ] as [$lbl, $val, $clr])
            <div style="padding:14px;border-radius:10px;border:1px solid #1f2937;background:rgba(17,24,39,0.6);">
                <p style="font-size:10px;color:#6b7280;margin:0 0 5px;text-transform:uppercase;letter-spacing:.04em;">{{ $lbl }}</p>
                <p style="font-size:18px;font-weight:700;color:{{ $clr }};margin:0;">{{ $val }}</p>
            </div>
            @endforeach
        </div>

        {{-- Available plans --}}
        <div style="display:flex;flex-direction:column;gap:14px;">
            <p style="font-size:12px;font-weight:700;color:#9ca3af;margin:0;text-transform:uppercase;letter-spacing:.05em;">Available Plans</p>
            @forelse($plans as $plan)
            @php
                $rc = $plan->risk_color;
                $rcMap = ['green'=>'#34d399','cyan'=>'#22d3ee','amber'=>'#fbbf24','red'=>'#f87171','purple'=>'#a78bfa','emerald'=>'#34d399'];
                $planClr = $rcMap[$rc] ?? '#22d3ee';
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
                    <button type="button" @click="openInvest({{ json_encode(['id'=>$plan->id,'name'=>$plan->name,'daily_roi'=>(float)$plan->daily_roi_percent,'min'=>(float)$plan->min_investment,'max'=>$plan->max_investment?(float)$plan->max_investment:null,'duration'=>$plan->duration_days]) }})"
                            style="width:100%;padding:11px 0;border-radius:10px;border:none;font-size:13px;font-weight:700;color:black;cursor:pointer;background:{{ $planClr }};transition:opacity .15s;appearance:none;-webkit-appearance:none;"
                            onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                        Invest Now
                    </button>
                </div>
            </div>
            @empty
            <p style="text-align:center;font-size:12px;color:#6b7280;padding:32px 0;">No active bot plans available.</p>
            @endforelse
        </div>

        {{-- Active investments --}}
        @if($active->count())
        <div style="display:flex;flex-direction:column;gap:12px;">
            <p style="font-size:12px;font-weight:700;color:#9ca3af;margin:0;text-transform:uppercase;letter-spacing:.05em;">Active Investments</p>
            @foreach($active as $inv)
            @php
                $sc2 = $inv->status_color;
                $scMap = ['green'=>'#34d399','cyan'=>'#22d3ee','amber'=>'#fbbf24','red'=>'#f87171','emerald'=>'#34d399'];
                $invClr = $scMap[$sc2] ?? '#22d3ee';
            @endphp
            <div style="border-radius:14px;border:1px solid #1f2937;background:rgba(17,24,39,0.6);padding:16px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
                    <div>
                        <p style="font-size:13px;font-weight:700;color:white;margin:0 0 3px;">{{ $inv->botPlan->name }}</p>
                        <p style="font-size:10px;color:#6b7280;margin:0;">Started {{ $inv->started_at->format('M d, Y') }}</p>
                    </div>
                    <span style="font-size:10px;padding:3px 9px;border-radius:20px;font-weight:600;color:{{ $invClr }};background:rgba(0,0,0,.25);border:1px solid rgba(255,255,255,.08);">{{ ucfirst($inv->status) }}</span>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:14px;">
                    <div>
                        <p style="font-size:10px;color:#6b7280;margin:0 0 3px;">Principal</p>
                        <p style="font-size:14px;font-weight:700;color:#22d3ee;margin:0;">${{ number_format($inv->principal_amount, 2) }}</p>
                    </div>
                    <div>
                        <p style="font-size:10px;color:#6b7280;margin:0 0 3px;">Daily ROI</p>
                        <p style="font-size:14px;font-weight:700;color:#34d399;margin:0;">{{ $inv->daily_roi_percent }}%</p>
                    </div>
                    <div>
                        <p style="font-size:10px;color:#6b7280;margin:0 0 3px;">Earned</p>
                        <p style="font-size:14px;font-weight:700;color:#34d399;margin:0;">${{ number_format($inv->total_earned, 2) }}</p>
                    </div>
                </div>
                @if($inv->ends_at)
                <div style="margin-bottom:12px;">
                    <div style="display:flex;justify-content:space-between;font-size:10px;color:#6b7280;margin-bottom:5px;">
                        <span>Progress</span><span>{{ $inv->progress_percent }}%</span>
                    </div>
                    <div style="width:100%;background:rgba(31,41,55,0.8);border-radius:20px;height:5px;overflow:hidden;">
                        <div style="height:5px;border-radius:20px;background:{{ $invClr }};width:{{ $inv->progress_percent }}%;"></div>
                    </div>
                </div>
                @endif
                <form method="POST" action="{{ route('bots.cancel', $inv) }}" onsubmit="return confirm('Cancel this investment?')" style="margin:0;">
                    @csrf
                    <button type="submit" style="font-size:11px;color:#f87171;background:none;border:none;cursor:pointer;padding:0;font-weight:500;">Cancel Investment</button>
                </form>
            </div>
            @endforeach
        </div>
        @endif

    </div>

    {{-- Invest form panel --}}
    <div x-show="selectedPlan" x-cloak style="padding:16px 20px;display:flex;flex-direction:column;gap:12px;">

        {{-- Wallet balance info --}}
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
                    this.investMessage = { type: 'success', text: data.message };
                    setTimeout(() => { this.selectedPlan = null; this.investMessage = null; this.amount = 0; }, 2500);
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
