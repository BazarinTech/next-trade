<div x-data="settingsModalData('{{ $currentTheme }}')">

    {{-- Header --}}
    <div style="padding:16px 20px;border-bottom:1px solid #1f2937;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:2;background:#0b1120;">
        <div>
            <h2 style="font-size:15px;font-weight:700;color:white;margin:0;">Settings</h2>
            <p style="font-size:11px;color:#6b7280;margin:2px 0 0;">Manage your preferences</p>
        </div>
        <button @click="$store.modal.close()" style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;border-radius:8px;border:1px solid #374151;background:transparent;cursor:pointer;color:#9ca3af;font-size:16px;">&times;</button>
    </div>

    {{-- Body --}}
    <div style="padding:16px 20px;display:flex;flex-direction:column;gap:16px;">

        {{-- Appearance --}}
        <div style="border-radius:12px;border:1px solid #1f2937;background:rgba(17,24,39,0.6);overflow:hidden;">
            <div style="padding:12px 16px;border-bottom:1px solid #1f2937;display:flex;align-items:center;justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <div style="width:28px;height:28px;border-radius:8px;background:rgba(6,182,212,0.1);border:1px solid rgba(6,182,212,0.15);display:flex;align-items:center;justify-content:center;">
                        <svg style="width:13px;height:13px;color:#22d3ee;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                    </div>
                    <p style="font-size:12px;font-weight:700;color:white;margin:0;">Appearance</p>
                </div>
                <span x-show="themeSaving" style="font-size:10px;color:#6b7280;display:flex;align-items:center;gap:4px;">
                    <svg style="width:11px;height:11px;animation:nt-spin 1s linear infinite;" fill="none" viewBox="0 0 24 24"><circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    Saving…
                </span>
            </div>
            <div style="padding:16px;">
                <p style="font-size:10px;color:#6b7280;margin:0 0 12px;">Choose how the interface looks to you.</p>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;">
                    @foreach([
                        ['dark',   'Dark',   'M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z'],
                        ['light',  'Light',  'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z'],
                        ['system', 'System', 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                    ] as [$val, $label, $path])
                    <button type="button" @click="setTheme('{{ $val }}')"
                            style="display:flex;flex-direction:column;align-items:center;gap:8px;padding:16px 8px;border-radius:10px;cursor:pointer;transition:all .18s;appearance:none;-webkit-appearance:none;border:1px solid;"
                            :style="theme === '{{ $val }}'
                                ? {borderColor:'rgba(6,182,212,.5)',background:'rgba(6,182,212,.08)',color:'#22d3ee'}
                                : {borderColor:'#1f2937',background:'rgba(31,41,55,0.4)',color:'#4b5563'}">
                        <svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $path }}"/>
                        </svg>
                        <span style="font-size:11px;font-weight:600;">{{ $label }}</span>
                        <span x-show="theme === '{{ $val }}'"
                              style="width:6px;height:6px;border-radius:50%;background:#22d3ee;display:block;"></span>
                        <span x-show="theme !== '{{ $val }}'" x-cloak
                              style="width:6px;height:6px;border-radius:50%;background:transparent;display:block;"></span>
                    </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Notifications --}}
        <div style="border-radius:12px;border:1px solid #1f2937;background:rgba(17,24,39,0.6);overflow:hidden;">
            <div style="padding:12px 16px;border-bottom:1px solid #1f2937;display:flex;align-items:center;gap:8px;">
                <div style="width:28px;height:28px;border-radius:8px;background:rgba(168,85,247,0.1);border:1px solid rgba(168,85,247,0.15);display:flex;align-items:center;justify-content:center;">
                    <svg style="width:13px;height:13px;color:#a78bfa;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </div>
                <p style="font-size:12px;font-weight:700;color:white;margin:0;">Notifications</p>
            </div>
            <div style="padding:0 16px;">
                @foreach([
                    ['trade',      'Trade opened/closed',  'Alerts when a trade is opened or closed',      'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                    ['deposit',    'Deposit confirmed',    'When your deposit is approved and credited',    'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                    ['withdrawal', 'Withdrawal processed', 'Status updates on your withdrawal requests',   'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['price',      'Price alerts',         'Significant market price movement alerts',     'M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z'],
                ] as [$key, $title, $desc, $iconPath])
                <div style="display:flex;align-items:center;justify-content:space-between;padding:13px 0;{{ !$loop->last ? 'border-bottom:1px solid rgba(31,41,55,0.6);' : '' }}">
                    <div style="display:flex;align-items:center;gap:10px;min-width:0;">
                        <div style="width:30px;height:30px;border-radius:8px;background:rgba(31,41,55,0.8);border:1px solid rgba(55,65,81,0.6);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg style="width:13px;height:13px;color:#6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $iconPath }}"/></svg>
                        </div>
                        <div style="min-width:0;">
                            <p style="font-size:12px;font-weight:600;color:#d1d5db;margin:0 0 2px;">{{ $title }}</p>
                            <p style="font-size:10px;color:#4b5563;margin:0;line-height:1.4;">{{ $desc }}</p>
                        </div>
                    </div>
                    <button type="button" @click="toggleNotif('{{ $key }}')"
                            style="position:relative;width:42px;height:24px;border-radius:20px;border:none;cursor:pointer;transition:background .2s;flex-shrink:0;margin-left:12px;appearance:none;-webkit-appearance:none;"
                            :style="notifs['{{ $key }}'] ? {background:'#06b6d4'} : {background:'#1f2937',border:'1px solid #374151'}">
                        <span style="position:absolute;top:3px;width:18px;height:18px;border-radius:50%;background:white;box-shadow:0 1px 4px rgba(0,0,0,.4);transition:left .18s;"
                              :style="notifs['{{ $key }}'] ? {left:'21px'} : {left:'3px'}"></span>
                    </button>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Danger zone --}}
        <div style="border-radius:12px;border:1px solid rgba(239,68,68,0.15);background:rgba(17,24,39,0.6);overflow:hidden;">
            <div style="padding:12px 16px;border-bottom:1px solid rgba(239,68,68,0.1);display:flex;align-items:center;gap:8px;">
                <div style="width:28px;height:28px;border-radius:8px;background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.15);display:flex;align-items:center;justify-content:center;">
                    <svg style="width:13px;height:13px;color:#f87171;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <p style="font-size:12px;font-weight:700;color:#f87171;margin:0;">Account</p>
            </div>
            <div style="padding:14px 16px;display:flex;align-items:center;justify-content:space-between;gap:12px;">
                <div>
                    <p style="font-size:12px;font-weight:600;color:#d1d5db;margin:0 0 2px;">Sign out</p>
                    <p style="font-size:10px;color:#4b5563;margin:0;">End your current session</p>
                </div>
                <button type="button" @click="$store.modal.open('logout')"
                        style="padding:7px 14px;border-radius:8px;border:1px solid rgba(239,68,68,0.3);background:rgba(239,68,68,0.07);font-size:11px;font-weight:600;color:#f87171;cursor:pointer;transition:all .15s;appearance:none;-webkit-appearance:none;white-space:nowrap;"
                        onmouseover="this.style.background='rgba(239,68,68,0.12)'" onmouseout="this.style.background='rgba(239,68,68,0.07)'">
                    Sign Out
                </button>
            </div>
        </div>

        <div style="height:4px;"></div>
    </div>
</div>

<style>@keyframes nt-spin { to { transform: rotate(360deg); } }</style>

<script>
function settingsModalData(initialTheme) {
    return {
        theme: initialTheme || 'dark',
        themeSaving: false,
        notifs: {
            trade:      localStorage.getItem('nt_notif_trade')      !== 'false',
            deposit:    localStorage.getItem('nt_notif_deposit')    !== 'false',
            withdrawal: localStorage.getItem('nt_notif_withdrawal') !== 'false',
            price:      localStorage.getItem('nt_notif_price')      !== 'false',
        },

        async setTheme(val) {
            if (this.themeSaving) return;
            this.theme = val;
            this.applyTheme(val);
            this.themeSaving = true;
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
                await fetch('{{ route('user.theme') }}', {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({ theme: val }),
                });
            } catch (_) {}
            finally { this.themeSaving = false; }
        },

        applyTheme(val) {
            /* Light mode CSS is not yet available — keep dark class always.
               Preference is saved to DB for when light mode is introduced. */
            document.documentElement.classList.add('dark');
        },

        toggleNotif(key) {
            this.notifs[key] = !this.notifs[key];
            localStorage.setItem('nt_notif_' + key, this.notifs[key]);
        },
    };
}
</script>
