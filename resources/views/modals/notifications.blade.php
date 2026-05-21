<div style="display:flex;flex-direction:column;">

    {{-- Header --}}
    <div style="padding:16px 20px;border-bottom:1px solid #1f2937;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;position:sticky;top:0;z-index:2;background:#0b1120;">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:34px;height:34px;border-radius:10px;background:rgba(6,182,212,0.1);border:1px solid rgba(6,182,212,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg style="width:15px;height:15px;color:#22d3ee;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </div>
            <div>
                <h2 style="font-size:15px;font-weight:700;color:white;margin:0;">Notifications</h2>
                @if($unreadCount > 0)
                <span style="font-size:10px;color:#06b6d4;">{{ $unreadCount }} unread</span>
                @endif
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            @if($unreadCount > 0)
            <button @click="fetch('{{ route('notifications.read-all') }}',{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}}).then(r=>r.json()).then(d=>{if(d.success)$store.modal.open('notifications')})"
                    style="padding:5px 10px;border-radius:8px;border:1px solid rgba(6,182,212,0.3);background:rgba(6,182,212,0.1);font-size:11px;font-weight:600;color:#22d3ee;cursor:pointer;transition:opacity .15s;"
                    onmouseover="this.style.opacity='.75'" onmouseout="this.style.opacity='1'">
                Mark all read
            </button>
            @endif
            <button @click="$store.modal.close()" style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;border-radius:8px;border:1px solid #374151;background:transparent;cursor:pointer;color:#9ca3af;font-size:16px;">&times;</button>
        </div>
    </div>

    {{-- List --}}
    <div style="padding:8px 0;">
        @forelse($notifications as $notif)
        <div x-data="{ read: {{ $notif->isRead() ? 'true' : 'false' }} }"
             style="padding:12px 20px;border-bottom:1px solid rgba(31,41,55,0.5);display:flex;align-items:flex-start;gap:12px;">

            {{-- Dot --}}
            <span :style="read ? 'background:#374151;' : 'background:#06b6d4;'"
                  style="width:8px;height:8px;border-radius:50%;flex-shrink:0;margin-top:5px;display:block;transition:background .2s;"></span>

            {{-- Content --}}
            <div style="flex:1;min-width:0;">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;">
                    <div style="flex:1;min-width:0;">
                        <p :style="read ? 'color:#9ca3af;' : 'color:white;'"
                           style="font-size:13px;font-weight:600;margin:0 0 3px;transition:color .2s;">{{ $notif->title }}</p>
                        <p style="font-size:11px;color:#6b7280;margin:0 0 6px;line-height:1.5;">{{ $notif->message }}</p>
                        <span style="display:inline-block;padding:2px 7px;border-radius:4px;font-size:10px;font-weight:500;background:rgba(75,85,99,0.15);color:#6b7280;">
                            {{ ucwords(str_replace('_', ' ', $notif->type)) }}
                        </span>
                    </div>
                    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:5px;flex-shrink:0;">
                        <span style="font-size:10px;color:#4b5563;white-space:nowrap;">{{ $notif->created_at->diffForHumans() }}</span>
                        <button x-show="!read"
                                @click="fetch('{{ route('notifications.read', $notif) }}',{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}}).then(r=>r.json()).then(d=>{if(d.success)read=true})"
                                style="font-size:10px;font-weight:600;color:#22d3ee;background:transparent;border:none;cursor:pointer;padding:0;transition:color .15s;"
                                onmouseover="this.style.color='#67e8f9'" onmouseout="this.style.color='#22d3ee'">
                            Mark read
                        </button>
                        <span x-show="read" style="font-size:10px;color:#374151;">Read</span>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div style="padding:52px 20px;text-align:center;">
            <div style="width:52px;height:52px;border-radius:16px;background:rgba(75,85,99,0.1);border:1px solid rgba(75,85,99,0.15);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                <svg style="width:22px;height:22px;color:#4b5563;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </div>
            <p style="font-size:13px;font-weight:500;color:#4b5563;margin:0;">No notifications yet</p>
        </div>
        @endforelse
    </div>

</div>
