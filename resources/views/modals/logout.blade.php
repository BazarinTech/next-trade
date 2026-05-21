<div>

    {{-- Header --}}
    <div style="padding:16px 20px;border-bottom:1px solid #1f2937;display:flex;align-items:center;justify-content:space-between;">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:34px;height:34px;border-radius:10px;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg style="width:15px;height:15px;color:#f87171;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            </div>
            <h2 style="font-size:15px;font-weight:700;color:white;margin:0;">Sign Out</h2>
        </div>
        <button @click="$store.modal.close()" style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;border-radius:8px;border:1px solid #374151;background:transparent;cursor:pointer;color:#9ca3af;font-size:16px;">&times;</button>
    </div>

    {{-- Content --}}
    <div style="padding:28px 20px;display:flex;flex-direction:column;align-items:center;gap:20px;text-align:center;">

        <div style="width:60px;height:60px;border-radius:18px;background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);display:flex;align-items:center;justify-content:center;">
            <svg style="width:26px;height:26px;color:#f87171;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
        </div>

        <div>
            <p style="font-size:15px;font-weight:700;color:white;margin:0 0 8px;">Sign out of Next Trade?</p>
            <p style="font-size:12px;color:#6b7280;margin:0;line-height:1.6;">You'll need to sign back in to access<br>your account and trades.</p>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;width:100%;">
            <button type="button" @click="$store.modal.close()"
                    style="padding:11px 0;border-radius:10px;border:1px solid #374151;background:transparent;font-size:13px;font-weight:600;color:#d1d5db;cursor:pointer;transition:border-color .15s;"
                    onmouseover="this.style.borderColor='rgba(255,255,255,.25)'" onmouseout="this.style.borderColor='#374151'">
                Cancel
            </button>
            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit"
                        style="width:100%;padding:11px 0;border-radius:10px;border:none;font-size:13px;font-weight:700;color:white;cursor:pointer;background:linear-gradient(135deg,#ef4444,#dc2626);transition:opacity .15s;"
                        onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                    Yes, Sign Out
                </button>
            </form>
        </div>

    </div>
</div>
