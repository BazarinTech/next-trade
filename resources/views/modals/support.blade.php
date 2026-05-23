<div style="display:flex;flex-direction:column;">

    {{-- Header --}}
    <div style="padding:16px 20px;border-bottom:1px solid #1f2937;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;position:sticky;top:0;z-index:2;background:#0b1120;">
        <div>
            <h2 style="font-size:15px;font-weight:700;color:white;margin:0;">Support</h2>
            <p style="font-size:11px;color:#6b7280;margin:2px 0 0;">We're here to help you trade with confidence</p>
        </div>
        <button @click="$store.modal.close()" style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;border-radius:8px;border:1px solid #374151;background:transparent;cursor:pointer;color:#9ca3af;font-size:16px;">&times;</button>
    </div>

    {{-- Body --}}
    <div style="padding:16px 20px;display:flex;flex-direction:column;gap:12px;">

        {{-- Chat Support card --}}
        @if($supportChatUrl)
        <a href="{{ $supportChatUrl }}" target="_blank" rel="noopener noreferrer"
           style="display:flex;align-items:center;gap:14px;padding:16px;border-radius:14px;border:1px solid #1f2937;background:rgba(17,24,39,0.6);text-decoration:none;transition:border-color 0.15s,background 0.15s;cursor:pointer;"
           onmouseover="this.style.borderColor='rgba(6,182,212,0.4)';this.style.background='rgba(6,182,212,0.05)'"
           onmouseout="this.style.borderColor='#1f2937';this.style.background='rgba(17,24,39,0.6)'">
            <div style="width:44px;height:44px;border-radius:12px;background:rgba(6,182,212,0.1);border:1px solid rgba(6,182,212,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg style="width:20px;height:20px;color:#22d3ee;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
            <div style="flex:1;min-width:0;">
                <p style="font-size:13px;font-weight:700;color:white;margin:0 0 3px;">Chat with Support</p>
                <p style="font-size:11px;color:#6b7280;margin:0;line-height:1.4;">Message our support team directly for help with your account, deposits, withdrawals, or any trading questions.</p>
            </div>
            <svg style="width:14px;height:14px;color:#4b5563;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
        </a>
        @else
        <div style="display:flex;align-items:center;gap:14px;padding:16px;border-radius:14px;border:1px solid #1f2937;background:rgba(17,24,39,0.3);opacity:0.5;">
            <div style="width:44px;height:44px;border-radius:12px;background:rgba(31,41,55,0.6);border:1px solid #374151;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg style="width:20px;height:20px;color:#4b5563;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
            <div>
                <p style="font-size:13px;font-weight:700;color:#6b7280;margin:0 0 3px;">Chat with Support</p>
                <p style="font-size:11px;color:#4b5563;margin:0;">Not configured yet.</p>
            </div>
        </div>
        @endif

        {{-- Community card --}}
        @if($communityUrl)
        <a href="{{ $communityUrl }}" target="_blank" rel="noopener noreferrer"
           style="display:flex;align-items:center;gap:14px;padding:16px;border-radius:14px;border:1px solid #1f2937;background:rgba(17,24,39,0.6);text-decoration:none;transition:border-color 0.15s,background 0.15s;cursor:pointer;"
           onmouseover="this.style.borderColor='rgba(139,92,246,0.4)';this.style.background='rgba(139,92,246,0.05)'"
           onmouseout="this.style.borderColor='#1f2937';this.style.background='rgba(17,24,39,0.6)'">
            <div style="width:44px;height:44px;border-radius:12px;background:rgba(139,92,246,0.1);border:1px solid rgba(139,92,246,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg style="width:20px;height:20px;color:#a78bfa;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div style="flex:1;min-width:0;">
                <p style="font-size:13px;font-weight:700;color:white;margin:0 0 3px;">Join Trading Community</p>
                <p style="font-size:11px;color:#6b7280;margin:0;line-height:1.4;">Connect with fellow traders, share strategies, get market insights, and grow together.</p>
            </div>
            <svg style="width:14px;height:14px;color:#4b5563;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
        </a>
        @else
        <div style="display:flex;align-items:center;gap:14px;padding:16px;border-radius:14px;border:1px solid #1f2937;background:rgba(17,24,39,0.3);opacity:0.5;">
            <div style="width:44px;height:44px;border-radius:12px;background:rgba(31,41,55,0.6);border:1px solid #374151;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg style="width:20px;height:20px;color:#4b5563;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <p style="font-size:13px;font-weight:700;color:#6b7280;margin:0 0 3px;">Join Trading Community</p>
                <p style="font-size:11px;color:#4b5563;margin:0;">Not configured yet.</p>
            </div>
        </div>
        @endif

        {{-- Footer note --}}
        <p style="font-size:10px;color:#4b5563;text-align:center;margin:4px 0 0;line-height:1.5;">
            Links open in a new tab &nbsp;·&nbsp; Available 24/7
        </p>

    </div>
</div>
