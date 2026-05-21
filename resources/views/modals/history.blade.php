<div style="display:flex;flex-direction:column;">

    {{-- Header --}}
    <div style="padding:16px 20px;border-bottom:1px solid #1f2937;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;position:sticky;top:0;z-index:2;background:#0b1120;">
        <div>
            <h2 style="font-size:15px;font-weight:700;color:white;margin:0;">Transaction History</h2>
            <p style="font-size:11px;color:#6b7280;margin:2px 0 0;">Recent 50 transactions</p>
        </div>
        <button @click="$store.modal.close()" style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;border-radius:8px;border:1px solid #374151;background:transparent;cursor:pointer;color:#9ca3af;font-size:16px;">&times;</button>
    </div>

    {{-- Transactions list --}}
    <div>

        @if($transactions->isEmpty())
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:48px 20px;text-align:center;">
            <div style="width:44px;height:44px;border-radius:12px;background:rgba(31,41,55,0.8);display:flex;align-items:center;justify-content:center;margin-bottom:12px;">
                <svg style="width:18px;height:18px;color:#4b5563;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <p style="font-size:12px;color:#6b7280;margin:0;">No transactions yet</p>
        </div>
        @else
        @foreach($transactions as $txn)
        @php $isCredit = $txn->isCredit(); $sc = $txn->getStatusColor(); @endphp
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 16px;border-bottom:1px solid rgba(31,41,55,0.5);transition:background .1s;"
             onmouseover="this.style.background='rgba(31,41,55,0.3)'" onmouseout="this.style.background='transparent'">
            <div style="display:flex;align-items:center;gap:10px;min-width:0;">
                <div style="width:30px;height:30px;border-radius:8px;border:1px solid;display:flex;align-items:center;justify-content:center;flex-shrink:0;
                    {{ $isCredit ? 'background:rgba(16,185,129,.08);border-color:rgba(16,185,129,.2)' : 'background:rgba(239,68,68,.08);border-color:rgba(239,68,68,.2)' }}">
                    <svg style="width:11px;height:11px;color:{{ $isCredit ? '#34d399' : '#f87171' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if($isCredit)
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                        @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                        @endif
                    </svg>
                </div>
                <div style="min-width:0;">
                    <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                        <p style="font-size:11px;font-weight:500;color:#d1d5db;margin:0;white-space:nowrap;">{{ $txn->getTypeLabel() }}</p>
                        <span style="font-size:9px;padding:1px 6px;border-radius:20px;white-space:nowrap;
                            {{ $txn->wallet->type === 'demo' ? 'background:rgba(245,158,11,.1);color:#fbbf24;border:1px solid rgba(245,158,11,.2)' : 'background:rgba(6,182,212,.1);color:#22d3ee;border:1px solid rgba(6,182,212,.2)' }}">
                            {{ ucfirst($txn->wallet->type) }}
                        </span>
                    </div>
                    <p style="font-size:9px;color:#6b7280;margin:2px 0 0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px;">
                        {{ $txn->created_at->format('M d, Y H:i') }}
                        @if($txn->reference) · {{ $txn->reference }} @endif
                    </p>
                </div>
            </div>
            <div style="text-align:right;flex-shrink:0;margin-left:10px;">
                <p style="font-size:12px;font-weight:700;font-family:monospace;color:{{ $isCredit ? '#34d399' : '#f87171' }};margin:0;">{{ $txn->getSignedAmount() }}</p>
                <span style="font-size:9px;padding:1px 5px;border-radius:20px;
                    @if($sc === 'emerald') background:rgba(16,185,129,.1);color:#34d399;
                    @elseif($sc === 'red') background:rgba(239,68,68,.1);color:#f87171;
                    @elseif($sc === 'amber') background:rgba(245,158,11,.1);color:#fbbf24;
                    @else background:rgba(107,114,128,.1);color:#9ca3af; @endif">
                    {{ ucfirst($txn->status) }}
                </span>
            </div>
        </div>
        @endforeach
        @endif

    </div>
</div>
