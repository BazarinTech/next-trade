<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Next Trade'))</title>

    @vite(['resources/css/app.css'])
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('modal', {
            active: null,
            loading: false,
            open(name) {
                this.active = name;
                this.loading = true;
                fetch('/modal/' + name, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => { if (!r.ok) throw new Error(r.status); return r.text(); })
                .then(html => {
                    const el = document.getElementById('nt-modal-body');
                    el.innerHTML = html;
                    el.querySelectorAll('script').forEach(s => {
                        const ns = document.createElement('script');
                        ns.textContent = s.textContent;
                        document.head.appendChild(ns);
                        document.head.removeChild(ns);
                    });
                    Alpine.initTree(el);
                    this.loading = false;
                })
                .catch(() => { this.loading = false; this.active = null; });
            },
            close() {
                this.active = null;
                this.loading = false;
                setTimeout(() => {
                    const el = document.getElementById('nt-modal-body');
                    if (el) el.innerHTML = '';
                }, 200);
            }
        });
    });
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        [x-cloak] { display: none !important; }
        html, body { margin: 0; padding: 0; height: 100%; overflow: hidden; background: #030712; color: white; font-family: 'Inter', system-ui, sans-serif; }
        *, *::before, *::after { box-sizing: border-box; }
        * { scrollbar-width: thin; scrollbar-color: rgba(6,182,212,0.35) transparent; }
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(6,182,212,0.35); border-radius: 2px; }
        .nt-nav-link { color: #9ca3af; text-decoration: none; display: flex; align-items: center; gap: 5px; padding: 5px 10px; border-radius: 8px; font-size: 11px; font-weight: 500; transition: color 0.15s, background 0.15s; white-space: nowrap; }
        .nt-nav-link:hover { color: #e5e7eb; background: rgba(31,41,55,0.8); }
        .nt-icon-btn { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px; background: transparent; border: none; cursor: pointer; color: #6b7280; transition: color 0.15s, background 0.15s; }
        .nt-icon-btn:hover { color: #e5e7eb; background: rgba(31,41,55,0.8); }
        .nt-dropdown-item { display: flex; align-items: center; gap: 8px; padding: 9px 14px; font-size: 12px; color: #9ca3af; text-decoration: none; cursor: pointer; background: transparent; border: none; width: 100%; text-align: left; transition: background 0.1s, color 0.1s; }
        .nt-dropdown-item:hover { background: rgba(31,41,55,0.8); color: #e5e7eb; }
        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.5} }
        /* Modal */
        .nt-modal-centering { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; padding:16px; pointer-events:none; }
        .nt-modal-box { position:relative; width:100%; max-width:560px; border-radius:16px; border:1px solid #1f2937; background:#0b1120; overflow:hidden; box-shadow:0 25px 60px rgba(0,0,0,0.85); pointer-events:auto; }
        #nt-modal-body { overflow-y:auto; max-height:88vh; -webkit-overflow-scrolling:touch; overscroll-behavior:contain; }
        @media (max-width: 639px) {
            .nt-modal-centering { padding:0; align-items:flex-end; }
            .nt-modal-box { max-width:100%; border-radius:20px 20px 0 0; }
            #nt-modal-body { max-height:100dvh; }
        }
        /* Mobile responsive */
        @media (max-width: 767px) {
            .nt-nav-links     { display: none !important; }
            .nt-logo-text     { display: none !important; }
            .nt-profile-name  { display: none !important; }
            .nt-mode-switcher { display: none !important; }
            .nt-mobile-menu-btn  { display: block !important; }
            .nt-mobile-spacer    { display: block !important; }
        }
    </style>
</head>
<body>
    @php
        $walletMode = session('wallet_mode', 'demo');
        $navWallet  = null;
        if (auth()->check()) {
            $navWallet = auth()->user()->wallets()->where('type', $walletMode)->first();
        }
    @endphp

    <div x-data="{ sidebarOpen: false, isDark: true }" style="display:flex; flex-direction:column; height:100vh; overflow:hidden;">

        <!-- ── Mobile Sidebar Overlay ─────────────────────────────────── -->
        <div>

            <!-- Backdrop -->
            <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
                 style="position:fixed; inset:0; background:rgba(0,0,0,0.65); z-index:60; backdrop-filter:blur(2px);"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
            </div>

            <!-- Sidebar panel -->
            <div x-show="sidebarOpen" x-cloak
                 style="position:fixed; top:0; left:0; height:100%; width:280px; background:#0b1120; border-right:1px solid #1f2937; z-index:61; display:flex; flex-direction:column; overflow:hidden; box-shadow:4px 0 32px rgba(0,0,0,0.7);"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="-translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="-translate-x-full">

                <!-- Sidebar header: logo + close -->
                <div style="height:56px; display:flex; align-items:center; justify-content:space-between; padding:0 16px; border-bottom:1px solid #1f2937; flex-shrink:0;">
                    <a href="{{ route('trade.index') }}" style="display:flex; align-items:center; gap:8px; text-decoration:none;">
                        <div style="width:28px; height:28px; border-radius:8px; background:#06b6d4; display:flex; align-items:center; justify-content:center;">
                            <svg style="width:15px;height:15px;" fill="none" stroke="white" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </div>
                        <span style="font-size:14px; font-weight:700; color:white;">Next<span style="color:#22d3ee;">Trade</span></span>
                    </a>
                    <button @click="sidebarOpen = false"
                            style="width:32px; height:32px; display:flex; align-items:center; justify-content:center; background:rgba(31,41,55,0.8); border:1px solid #374151; border-radius:8px; cursor:pointer; color:#9ca3af;">
                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- User info -->
                @auth
                <div style="padding:16px; border-bottom:1px solid #1f2937; flex-shrink:0;">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <div style="width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg,#06b6d4,#0891b2); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <span style="font-size:15px; font-weight:700; color:white;">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        </div>
                        <div style="min-width:0;">
                            <p style="font-size:13px; font-weight:600; color:white; margin:0 0 2px 0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ auth()->user()->name }}</p>
                            <p style="font-size:10px; color:#6b7280; margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    @if($navWallet)
                    <div style="margin-top:12px; padding:10px 12px; background:rgba(17,24,39,0.8); border:1px solid #1f2937; border-radius:10px; display:flex; align-items:center; justify-content:space-between;">
                        <div style="display:flex; align-items:center; gap:6px;">
                            <span style="width:7px; height:7px; border-radius:50%; background:{{ $walletMode === 'demo' ? '#fbbf24' : '#22d3ee' }};"></span>
                            <span style="font-size:10px; font-weight:600; color:#6b7280; text-transform:uppercase;">{{ $walletMode }} balance</span>
                        </div>
                        <span style="font-size:14px; font-weight:700; color:white; font-variant-numeric:tabular-nums;">${{ number_format($navWallet->available_balance, 2) }}</span>
                    </div>
                    @endif
                </div>
                @endauth

                <!-- Demo / Live switcher -->
                <div style="padding:12px 16px; border-bottom:1px solid #1f2937; flex-shrink:0;">
                    <p style="font-size:10px; font-weight:600; color:#6b7280; text-transform:uppercase; margin:0 0 8px 0; letter-spacing:0.05em;">Trading Mode</p>
                    <div style="display:flex; gap:6px;">
                        @foreach(['demo','live'] as $m)
                        <form method="POST" action="{{ route('wallet.mode') }}" style="flex:1; margin:0;">
                            @csrf
                            <input type="hidden" name="mode" value="{{ $m }}">
                            <button type="submit" style="width:100%; padding:9px 0; border-radius:10px; font-size:12px; font-weight:700; border:1px solid; cursor:pointer; transition:all 0.15s;
                                {{ $walletMode === $m
                                    ? 'background:linear-gradient(135deg,#06b6d4,#0891b2); color:white; border-color:#06b6d4;'
                                    : 'background:transparent; color:#6b7280; border-color:#374151;' }}">
                                {{ ucfirst($m) }}
                            </button>
                        </form>
                        @endforeach
                    </div>
                </div>

                <!-- Nav links -->
                <nav style="flex:1; overflow-y:auto; padding:10px 10px;">

                    <p style="font-size:10px; font-weight:600; color:#4b5563; text-transform:uppercase; letter-spacing:0.05em; padding:6px 8px 4px;">Trading</p>
                    <a href="{{ route('trade.index') }}" style="display:flex; align-items:center; gap:12px; padding:10px 10px; border-radius:10px; font-size:13px; font-weight:500; color:white; text-decoration:none; background:rgba(6,182,212,0.1); border:1px solid rgba(6,182,212,0.2); margin-bottom:2px;">
                        <svg style="width:15px;height:15px;color:#22d3ee;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        <span style="color:#22d3ee;">Trade</span>
                    </a>
                    <button @click="sidebarOpen = false; $nextTick(() => $store.modal.open('bots'))" style="display:flex; align-items:center; gap:12px; padding:10px 10px; border-radius:10px; font-size:13px; font-weight:500; color:#9ca3af; background:transparent; border:none; cursor:pointer; width:100%; text-align:left; transition:background 0.15s;" onmouseover="this.style.background='rgba(31,41,55,0.8)';this.style.color='white'" onmouseout="this.style.background='transparent';this.style.color='#9ca3af'">
                        <svg style="width:15px;height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H7a2 2 0 00-2 2v2M9 3h6M9 3v2m6-2h2a2 2 0 012 2v2M3 10h2m14 0h2M5 5v14a2 2 0 002 2h10a2 2 0 002-2V5"/></svg>
                        Bots
                    </button>

                    <p style="font-size:10px; font-weight:600; color:#4b5563; text-transform:uppercase; letter-spacing:0.05em; padding:10px 8px 4px;">Wallet</p>
                    <button @click="sidebarOpen = false; $nextTick(() => $store.modal.open('deposit'))" style="display:flex; align-items:center; gap:12px; padding:10px 10px; border-radius:10px; font-size:13px; font-weight:500; color:#9ca3af; background:transparent; border:none; cursor:pointer; width:100%; text-align:left; transition:background 0.15s;" onmouseover="this.style.background='rgba(31,41,55,0.8)';this.style.color='white'" onmouseout="this.style.background='transparent';this.style.color='#9ca3af'">
                        <svg style="width:15px;height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        Deposit
                    </button>
                    <button @click="sidebarOpen = false; $nextTick(() => $store.modal.open('withdraw'))" style="display:flex; align-items:center; gap:12px; padding:10px 10px; border-radius:10px; font-size:13px; font-weight:500; color:#9ca3af; background:transparent; border:none; cursor:pointer; width:100%; text-align:left; transition:background 0.15s;" onmouseover="this.style.background='rgba(31,41,55,0.8)';this.style.color='white'" onmouseout="this.style.background='transparent';this.style.color='#9ca3af'">
                        <svg style="width:15px;height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4M4 12l4-4M4 12l4 4"/></svg>
                        Withdraw
                    </button>
                    <button @click="sidebarOpen = false; $nextTick(() => $store.modal.open('wallet'))" style="display:flex; align-items:center; gap:12px; padding:10px 10px; border-radius:10px; font-size:13px; font-weight:500; color:#9ca3af; background:transparent; border:none; cursor:pointer; width:100%; text-align:left; transition:background 0.15s;" onmouseover="this.style.background='rgba(31,41,55,0.8)';this.style.color='white'" onmouseout="this.style.background='transparent';this.style.color='#9ca3af'">
                        <svg style="width:15px;height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        Wallet
                    </button>

                    <p style="font-size:10px; font-weight:600; color:#4b5563; text-transform:uppercase; letter-spacing:0.05em; padding:10px 8px 4px;">Account</p>
                    <button @click="sidebarOpen = false; $nextTick(() => $store.modal.open('history'))" style="display:flex; align-items:center; gap:12px; padding:10px 10px; border-radius:10px; font-size:13px; font-weight:500; color:#9ca3af; background:transparent; border:none; cursor:pointer; width:100%; text-align:left; transition:background 0.15s;" onmouseover="this.style.background='rgba(31,41,55,0.8)';this.style.color='white'" onmouseout="this.style.background='transparent';this.style.color='#9ca3af'">
                        <svg style="width:15px;height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Transaction History
                    </button>
                    <button @click="sidebarOpen = false; $nextTick(() => $store.modal.open('notifications'))" style="display:flex; align-items:center; gap:12px; padding:10px 10px; border-radius:10px; font-size:13px; font-weight:500; color:#9ca3af; background:transparent; border:none; cursor:pointer; width:100%; text-align:left; transition:background 0.15s;" onmouseover="this.style.background='rgba(31,41,55,0.8)';this.style.color='white'" onmouseout="this.style.background='transparent';this.style.color='#9ca3af'">
                        <svg style="width:15px;height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        Notifications
                    </button>
                    <button @click="sidebarOpen=false;$nextTick(()=>$store.modal.open('profile'))" style="display:flex; align-items:center; gap:12px; padding:10px 10px; border-radius:10px; font-size:13px; font-weight:500; color:#9ca3af; background:transparent; border:none; cursor:pointer; width:100%; text-align:left; transition:background 0.15s;" onmouseover="this.style.background='rgba(31,41,55,0.8)';this.style.color='white'" onmouseout="this.style.background='transparent';this.style.color='#9ca3af'">
                        <svg style="width:15px;height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Profile
                    </button>
                    <button @click="sidebarOpen=false;$nextTick(()=>$store.modal.open('referral'))" style="display:flex; align-items:center; gap:12px; padding:10px 10px; border-radius:10px; font-size:13px; font-weight:500; color:#9ca3af; background:transparent; border:none; cursor:pointer; width:100%; text-align:left; transition:background 0.15s;" onmouseover="this.style.background='rgba(31,41,55,0.8)';this.style.color='white'" onmouseout="this.style.background='transparent';this.style.color='#9ca3af'">
                        <svg style="width:15px;height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        Invite &amp; Earn
                    </button>
                    <button @click="sidebarOpen=false;$nextTick(()=>$store.modal.open('settings'))" style="display:flex; align-items:center; gap:12px; padding:10px 10px; border-radius:10px; font-size:13px; font-weight:500; color:#9ca3af; background:transparent; border:none; cursor:pointer; width:100%; text-align:left; transition:background 0.15s;" onmouseover="this.style.background='rgba(31,41,55,0.8)';this.style.color='white'" onmouseout="this.style.background='transparent';this.style.color='#9ca3af'">
                        <svg style="width:15px;height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Settings
                    </button>
                    @if(auth()->user()?->is_admin)
                    <a href="{{ route('admin.dashboard') }}" style="display:flex; align-items:center; gap:12px; padding:10px 10px; border-radius:10px; font-size:13px; font-weight:500; color:#9ca3af; text-decoration:none; transition:background 0.15s;" onmouseover="this.style.background='rgba(31,41,55,0.8)';this.style.color='white'" onmouseout="this.style.background='transparent';this.style.color='#9ca3af'">
                        <svg style="width:15px;height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Admin Panel
                    </a>
                    @endif
                </nav>

                <!-- Logout -->
                <div style="padding:12px 10px; border-top:1px solid #1f2937; flex-shrink:0;">
                    <button @click="sidebarOpen=false;$nextTick(()=>$store.modal.open('logout'))"
                            style="width:100%; display:flex; align-items:center; gap:12px; padding:10px 10px; border-radius:10px; font-size:13px; font-weight:500; color:#f87171; background:rgba(239,68,68,0.06); border:1px solid rgba(239,68,68,0.15); cursor:pointer; transition:background 0.15s;" onmouseover="this.style.background='rgba(239,68,68,0.12)'" onmouseout="this.style.background='rgba(239,68,68,0.06)'">
                        <svg style="width:15px;height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Logout
                    </button>
                </div>

            </div>

        </div><!-- end sidebar wrapper -->

        <!-- ── Modal overlay ──────────────────────────────────────────── -->
        <div x-show="$store.modal.active !== null" x-cloak
             style="position:fixed;inset:0;z-index:200;"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            <!-- Backdrop -->
            <div @click="$store.modal.close()"
                 style="position:absolute;inset:0;background:rgba(0,0,0,0.78);backdrop-filter:blur(4px);"></div>

            <!-- Centering wrapper | kept in a plain div (never x-show'd) so display:flex is never lost -->
            <div class="nt-modal-centering">
                <div class="nt-modal-box">

                    <!-- Loading spinner -->
                    <div :style="$store.modal.loading ? 'display:flex' : 'display:none'"
                         style="height:180px;flex-direction:column;align-items:center;justify-content:center;gap:10px;">
                        <div style="width:26px;height:26px;border:2px solid #06b6d4;border-top-color:transparent;border-radius:50%;animation:spin 0.8s linear infinite;"></div>
                        <p style="font-size:11px;color:#6b7280;margin:0;">Loading…</p>
                    </div>

                    <!-- Injected content | scroll container via CSS #nt-modal-body rule -->
                    <div id="nt-modal-body"
                         :style="$store.modal.loading ? 'display:none' : 'display:block'"></div>

                </div>
            </div>
        </div>

        <!-- ── Top Navigation Bar ──────────────────────────────────────── -->
        <header style="height:48px; flex-shrink:0; display:flex; align-items:center; padding:0 10px; background:#0b1120; border-bottom:1px solid #1f2937; gap:4px; position:relative; z-index:40;">

            <!-- Logo -->
            <a href="{{ route('trade.index') }}"
               style="display:flex; align-items:center; gap:8px; flex-shrink:0; text-decoration:none; margin-right:12px;">
                <div style="width:28px; height:28px; border-radius:8px; background:#06b6d4; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <svg style="width:15px;height:15px;" fill="none" stroke="white" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <span class="nt-logo-text" style="font-size:13px; font-weight:700; color:white; white-space:nowrap;">Next<span style="color:#22d3ee;">Trade</span></span>
            </a>

            <!-- Mobile hamburger button (visible only on mobile) -->
            <div style="display:none;" class="nt-mobile-menu-btn">
                <button @click="sidebarOpen = true"
                        style="width:34px; height:34px; display:flex; align-items:center; justify-content:center; background:rgba(31,41,55,0.8); border:1px solid #374151; border-radius:8px; cursor:pointer; color:#9ca3af; flex-shrink:0;">
                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>

            <!-- Spacer on mobile (pushes right content to edge) -->
            <div style="flex:1; display:none;" class="nt-mobile-spacer"></div>

            <!-- Quick Nav links (hidden on mobile) -->
            <nav class="nt-nav-links" style="display:flex; align-items:center; gap:1px; flex:1; min-width:0; overflow:hidden;">
                <button @click="$store.modal.open('deposit')"
                   style="display:flex; align-items:center; gap:5px; padding:5px 11px; border-radius:8px; font-size:11px; font-weight:600; color:white; background:linear-gradient(135deg,#06b6d4,#0891b2); border:none; cursor:pointer; white-space:nowrap; flex-shrink:0;">
                    <svg style="width:11px;height:11px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Deposit
                </button>
                <button @click="$store.modal.open('withdraw')" class="nt-nav-link" style="background:none;border:none;cursor:pointer;">
                    <svg style="width:11px;height:11px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4M4 12l4-4M4 12l4 4"/></svg>
                    Withdraw
                </button>
                <button @click="$store.modal.open('history')" class="nt-nav-link" style="background:none;border:none;cursor:pointer;">
                    <svg style="width:11px;height:11px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    History
                </button>
                <button @click="$store.modal.open('bots')" class="nt-nav-link" style="background:none;border:none;cursor:pointer;">
                    <svg style="width:11px;height:11px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H7a2 2 0 00-2 2v2M9 3h6M9 3v2m6-2h2a2 2 0 012 2v2M3 10h2m14 0h2M5 5v14a2 2 0 002 2h10a2 2 0 002-2V5"/></svg>
                    Bots
                </button>
                <button @click="$store.modal.open('wallet')" class="nt-nav-link" style="background:none;border:none;cursor:pointer;">
                    <svg style="width:11px;height:11px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    Wallet
                </button>
            </nav>

            <!-- Right: mode switcher + balance + notifications + profile -->
            <div style="display:flex; align-items:center; gap:6px; flex-shrink:0;">

                <!-- Demo/Live switcher (hidden on mobile | accessible via hamburger menu) -->
                <div class="nt-mode-switcher" style="display:flex; background:rgba(17,24,39,1); border-radius:8px; padding:2px; gap:1px;">
                    @foreach(['demo','live'] as $m)
                    <form method="POST" action="{{ route('wallet.mode') }}" style="margin:0;">
                        @csrf
                        <input type="hidden" name="mode" value="{{ $m }}">
                        <button type="submit"
                                style="padding:3px 10px; border-radius:6px; font-size:10px; font-weight:600; border:none; cursor:pointer;
                                {{ $walletMode === $m ? 'background:#06b6d4; color:white;' : 'background:transparent; color:#6b7280;' }}">
                            {{ ucfirst($m) }}
                        </button>
                    </form>
                    @endforeach
                </div>

                <!-- Balance -->
                @if($navWallet)
                <button @click="$store.modal.open('wallet')"
                   style="display:flex; align-items:center; gap:5px; padding:4px 10px; border-radius:8px; background:rgba(17,24,39,1); border:1px solid #1f2937; cursor:pointer; flex-shrink:0;">
                    <span style="width:6px; height:6px; border-radius:50%; background:{{ $walletMode === 'demo' ? '#fbbf24' : '#22d3ee' }};"></span>
                    <span style="font-size:12px; font-weight:700; color:white; font-variant-numeric:tabular-nums;">${{ number_format($navWallet->available_balance, 2) }}</span>
                </button>
                @endif

                <!-- Notifications -->
                @auth
                @php $bellCount = app(\App\Services\NotificationService::class)->unreadCount(auth()->user()); @endphp
                <button class="nt-icon-btn" @click="$store.modal.open('notifications')" style="position:relative;">
                    <svg style="width:15px;height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    @if($bellCount > 0)
                    <span style="position:absolute; top:0; right:0; min-width:14px; height:14px; background:#06b6d4; border-radius:50%; font-size:8px; font-weight:700; color:black; display:flex; align-items:center; justify-content:center; padding:0 2px;">{{ $bellCount > 99 ? '99+' : $bellCount }}</span>
                    @endif
                </button>
                @endauth

                <!-- Profile dropdown -->
                <div x-data="{ open: false }" style="position:relative;">
                    <button @click="open = !open" @click.away="open = false"
                            style="display:flex; align-items:center; gap:6px; padding:4px 8px; border-radius:8px; background:transparent; border:none; cursor:pointer; transition:background 0.15s;"
                            onmouseover="this.style.background='rgba(31,41,55,0.8)'"
                            onmouseout="this.style.background='transparent'">
                        <div style="width:26px; height:26px; border-radius:50%; background:linear-gradient(135deg,#06b6d4,#0891b2); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <span style="font-size:10px; font-weight:700; color:white;">{{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}</span>
                        </div>
                        <span class="nt-profile-name" style="font-size:12px; color:#d1d5db; white-space:nowrap; max-width:90px; overflow:hidden; text-overflow:ellipsis;">{{ auth()->user()?->name }}</span>
                        <svg style="width:11px;height:11px;color:#6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div x-show="open" x-cloak
                         style="position:absolute; right:0; top:calc(100% + 8px); width:190px; background:#111827; border:1px solid #1f2937; border-radius:12px; z-index:50; overflow:hidden; box-shadow:0 20px 40px rgba(0,0,0,0.7); padding:4px 0;">
                        <div style="padding:10px 14px; border-bottom:1px solid #1f2937; margin-bottom:4px;">
                            <p style="font-size:12px; font-weight:600; color:white; margin:0 0 1px 0;">{{ auth()->user()?->name }}</p>
                            <p style="font-size:10px; color:#6b7280; margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ auth()->user()?->email }}</p>
                        </div>
                        <button @click="open=false;$nextTick(()=>$store.modal.open('profile'))" class="nt-dropdown-item">
                            <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Profile
                        </button>
                        <button @click="open=false;$nextTick(()=>$store.modal.open('referral'))" class="nt-dropdown-item">
                            <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            Invite &amp; Earn
                        </button>
                        <button @click="open=false;$nextTick(()=>$store.modal.open('settings'))" class="nt-dropdown-item">
                            <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Settings
                        </button>
                        @if(auth()->user()?->is_admin)
                        <a href="{{ route('admin.dashboard') }}" class="nt-dropdown-item">
                            <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Admin Panel
                        </a>
                        @endif
                        <div style="border-top:1px solid #1f2937; margin:4px 0;"></div>
                        <button @click="open=false;$nextTick(()=>$store.modal.open('logout'))" class="nt-dropdown-item" style="color:#f87171;">
                            <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Logout
                        </button>
                    </div>
                </div>

            </div>
        </header>

        <!-- ── Page content ───────────────────────────────────────────── -->
        <div style="flex:1; min-height:0; overflow:@yield('overflow', 'auto')">

            @if(session('success'))
            <div style="margin:12px 16px 0; padding:10px 14px; border-radius:10px; font-size:12px; font-weight:500; display:flex; align-items:center; gap:8px; background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.25); color:#34d399;">
                <svg style="width:14px;height:14px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div style="margin:12px 16px 0; padding:10px 14px; border-radius:10px; font-size:12px; font-weight:500; display:flex; align-items:center; gap:8px; background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.25); color:#f87171;">
                <svg style="width:14px;height:14px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('error') }}
            </div>
            @endif

            @yield('content')
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof feather !== 'undefined') feather.replace();
        });
    </script>
    @stack('scripts')
</body>
</html>
