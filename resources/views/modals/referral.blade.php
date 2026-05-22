<div x-data="referralModalData()" style="display:flex;flex-direction:column;">

    {{-- Header --}}
    <div style="padding:16px 20px;border-bottom:1px solid #1f2937;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:2;background:#0b1120;">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:34px;height:34px;border-radius:10px;background:rgba(6,182,212,0.1);border:1px solid rgba(6,182,212,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg style="width:15px;height:15px;color:#22d3ee;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div>
                <h2 style="font-size:15px;font-weight:700;color:white;margin:0;">Invite &amp; Earn</h2>
                <p style="font-size:10px;color:#06b6d4;margin:2px 0 0;">Earn {{ number_format(app(\App\Services\SettingsService::class)->get('referral_commission_rate', 3), 0) }}% on every deposit your referrals make</p>
            </div>
        </div>
        <button @click="$store.modal.close()" style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;border-radius:8px;border:1px solid #374151;background:transparent;cursor:pointer;color:#9ca3af;font-size:16px;">&times;</button>
    </div>

    {{-- Body --}}
    <div style="padding:16px 20px;display:flex;flex-direction:column;gap:16px;">

        {{-- Stats row --}}
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;">
            @foreach([
                ['Total Invited',   $totalInvited,                         '#22d3ee', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                ['Deposited',       $activeCount,                          '#34d399', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['Total Earned',    '$'.number_format($totalEarned, 2),    '#f59e0b', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ] as [$label, $value, $color, $icon])
            <div style="padding:12px;border-radius:12px;border:1px solid #1f2937;background:rgba(17,24,39,0.6);text-align:center;">
                <svg style="width:18px;height:18px;color:{{ $color }};margin:0 auto 6px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/></svg>
                <p style="font-size:14px;font-weight:700;color:{{ $color }};margin:0 0 2px;">{{ $value }}</p>
                <p style="font-size:10px;color:#6b7280;margin:0;">{{ $label }}</p>
            </div>
            @endforeach
        </div>

        {{-- Referral link card --}}
        <div style="border-radius:12px;border:1px solid #1f2937;background:rgba(17,24,39,0.6);overflow:hidden;">
            <div style="padding:12px 16px;border-bottom:1px solid #1f2937;">
                <p style="font-size:12px;font-weight:700;color:white;margin:0;">Your Referral Link</p>
                <p style="font-size:10px;color:#6b7280;margin:3px 0 0;">Share this link | anyone who registers and deposits earns you {{ number_format(app(\App\Services\SettingsService::class)->get('referral_commission_rate', 3), 0) }}% commission.</p>
            </div>
            <div style="padding:14px 16px;display:flex;flex-direction:column;gap:10px;">

                {{-- Code pill --}}
                <div style="display:flex;align-items:center;gap:8px;">
                    <div style="flex:1;padding:10px 14px;border-radius:10px;border:1px solid rgba(6,182,212,0.3);background:rgba(6,182,212,0.06);display:flex;align-items:center;justify-content:space-between;">
                        <div>
                            <p style="font-size:10px;color:#6b7280;margin:0 0 2px;">Referral Code</p>
                            <p style="font-size:16px;font-weight:800;color:#22d3ee;letter-spacing:0.12em;font-family:monospace;margin:0;">{{ auth()->user()->referral_code }}</p>
                        </div>
                        <button @click="copyCode()"
                                style="padding:6px 12px;border-radius:8px;border:1px solid rgba(6,182,212,0.4);background:rgba(6,182,212,0.1);font-size:11px;font-weight:600;color:#22d3ee;cursor:pointer;appearance:none;-webkit-appearance:none;transition:all .15s;white-space:nowrap;"
                                onmouseover="this.style.background='rgba(6,182,212,0.2)'" onmouseout="this.style.background='rgba(6,182,212,0.1)'">
                            <span x-text="codeCopied ? 'Copied!' : 'Copy Code'"></span>
                        </button>
                    </div>
                </div>

                {{-- Full URL --}}
                <div style="display:flex;align-items:center;gap:8px;">
                    <div style="flex:1;padding:8px 12px;border-radius:10px;border:1px solid #1f2937;background:rgba(31,41,55,0.4);display:flex;align-items:center;justify-content:space-between;gap:8px;min-width:0;">
                        <p style="font-size:10px;color:#9ca3af;margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $referralUrl }}</p>
                        <button @click="copyLink()"
                                style="flex-shrink:0;padding:5px 10px;border-radius:7px;border:1px solid #374151;background:rgba(31,41,55,0.8);font-size:10px;font-weight:600;color:#9ca3af;cursor:pointer;appearance:none;-webkit-appearance:none;transition:all .15s;white-space:nowrap;"
                                onmouseover="this.style.color='white'" onmouseout="this.style.color='#9ca3af'">
                            <span x-text="linkCopied ? '✓ Copied' : 'Copy Link'"></span>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        {{-- How it works --}}
        <div style="border-radius:12px;border:1px solid #1f2937;background:rgba(17,24,39,0.6);overflow:hidden;">
            <div style="padding:12px 16px;border-bottom:1px solid #1f2937;">
                <p style="font-size:12px;font-weight:700;color:white;margin:0;">How It Works</p>
            </div>
            <div style="padding:14px 16px;display:flex;flex-direction:column;gap:12px;">
                @php $rate = number_format(app(\App\Services\SettingsService::class)->get('referral_commission_rate', 3), 0); @endphp
                @foreach([
                    ['1', '#06b6d4', 'Share your link', 'Send your referral link or code to friends.'],
                    ['2', '#8b5cf6', 'They register',   'Your friend signs up using your link.'],
                    ['3', '#f59e0b', 'They deposit',     'Every time they recharge their live wallet, you earn ' . $rate . '% of the deposit | instantly credited to your live wallet.'],
                ] as [$step, $color, $title, $desc])
                <div style="display:flex;align-items:flex-start;gap:10px;">
                    <div style="width:24px;height:24px;border-radius:50%;background:{{ $color }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <span style="font-size:11px;font-weight:800;color:black;">{{ $step }}</span>
                    </div>
                    <div>
                        <p style="font-size:12px;font-weight:600;color:#d1d5db;margin:0 0 2px;">{{ $title }}</p>
                        <p style="font-size:10px;color:#6b7280;margin:0;line-height:1.5;">{{ $desc }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Recent commissions --}}
        @if($commissions->count() > 0)
        <div style="border-radius:12px;border:1px solid #1f2937;background:rgba(17,24,39,0.6);overflow:hidden;">
            <div style="padding:12px 16px;border-bottom:1px solid #1f2937;">
                <p style="font-size:12px;font-weight:700;color:white;margin:0;">Recent Commissions</p>
            </div>
            <div style="padding:0 16px;">
                @foreach($commissions as $commission)
                <div style="padding:11px 0;{{ !$loop->last ? 'border-bottom:1px solid rgba(31,41,55,0.6);' : '' }}display:flex;align-items:center;justify-content:space-between;gap:8px;">
                    <div style="display:flex;align-items:center;gap:8px;min-width:0;">
                        <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#06b6d4,#0891b2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <span style="font-size:10px;font-weight:700;color:white;">{{ strtoupper(substr($commission->referred->name ?? '?', 0, 1)) }}</span>
                        </div>
                        <div style="min-width:0;">
                            <p style="font-size:12px;font-weight:600;color:#d1d5db;margin:0 0 1px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $commission->referred->name ?? 'Unknown' }}</p>
                            <p style="font-size:10px;color:#6b7280;margin:0;">deposited ${{ number_format($commission->deposit_amount_usd, 2) }}</p>
                        </div>
                    </div>
                    <div style="text-align:right;flex-shrink:0;">
                        <p style="font-size:13px;font-weight:700;color:#34d399;margin:0;">+${{ number_format($commission->commission_amount_usd, 2) }}</p>
                        <p style="font-size:10px;color:#4b5563;margin:0;">{{ $commission->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div style="height:4px;"></div>
    </div>
</div>

<script>
function referralModalData() {
    return {
        codeCopied: false,
        linkCopied: false,
        copyCode() {
            navigator.clipboard.writeText('{{ auth()->user()->referral_code }}').then(() => {
                this.codeCopied = true;
                setTimeout(() => this.codeCopied = false, 2000);
            });
        },
        copyLink() {
            navigator.clipboard.writeText('{{ $referralUrl }}').then(() => {
                this.linkCopied = true;
                setTimeout(() => this.linkCopied = false, 2000);
            });
        },
    };
}
</script>
