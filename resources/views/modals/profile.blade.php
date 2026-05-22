<div x-data="profileModalData()">

    {{-- Header --}}
    <div style="padding:16px 20px;border-bottom:1px solid #1f2937;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:2;background:#0b1120;">
        <div>
            <h2 style="font-size:15px;font-weight:700;color:white;margin:0;">Profile</h2>
            <p style="font-size:11px;color:#6b7280;margin:2px 0 0;">Manage your account details</p>
        </div>
        <button @click="$store.modal.close()" style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;border-radius:8px;border:1px solid #374151;background:transparent;cursor:pointer;color:#9ca3af;font-size:16px;">&times;</button>
    </div>

    {{-- Body --}}
    <div style="padding:16px 20px;display:flex;flex-direction:column;gap:16px;">

        {{-- Avatar card --}}
        <div style="display:flex;align-items:center;gap:16px;padding:18px;border-radius:14px;background:rgba(17,24,39,0.6);border:1px solid #1f2937;">
            <div style="position:relative;flex-shrink:0;">
                <div style="width:68px;height:68px;border-radius:18px;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,0.5);background:{{ auth()->user()->avatarGradient }};display:flex;align-items:center;justify-content:center;">
                    <img src="{{ auth()->user()->avatarUrl }}" alt="{{ auth()->user()->initials }}" width="68" height="68"
                         style="width:68px;height:68px;object-fit:cover;display:block;"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                    <span style="display:none;font-size:24px;font-weight:700;color:white;letter-spacing:0.04em;">{{ auth()->user()->initials }}</span>
                </div>
                <div style="position:absolute;bottom:-3px;right:-3px;width:16px;height:16px;border-radius:50%;background:#10b981;border:2px solid #0b1120;"></div>
            </div>
            <div style="min-width:0;">
                <p style="font-size:15px;font-weight:700;color:white;margin:0 0 3px;">{{ auth()->user()->name }}</p>
                <p style="font-size:11px;color:#6b7280;margin:0 0 2px;">&#64;{{ auth()->user()->username }}</p>
                <p style="font-size:11px;color:#22d3ee;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ auth()->user()->email }}</p>
            </div>
        </div>

        {{-- Profile Information --}}
        <div style="border-radius:12px;border:1px solid #1f2937;background:rgba(17,24,39,0.6);overflow:hidden;">
            <div style="padding:12px 16px;border-bottom:1px solid #1f2937;">
                <p style="font-size:12px;font-weight:700;color:white;margin:0;">Profile Information</p>
            </div>
            <form method="POST" action="{{ route('profile.update') }}" style="padding:16px;display:flex;flex-direction:column;gap:12px;" @submit.prevent="handleProfileSave($el)">
                @csrf @method('patch')

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div>
                        <p style="font-size:10px;font-weight:600;color:#6b7280;margin:0 0 6px;text-transform:uppercase;letter-spacing:.05em;">Full Name</p>
                        <input type="text" name="name" value="{{ auth()->user()->name }}" required
                               style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid #374151;background:rgba(31,41,55,0.6);color:white;font-size:12px;outline:none;box-sizing:border-box;"
                               onfocus="this.style.borderColor='rgba(6,182,212,.5)'" onblur="this.style.borderColor='#374151'">
                    </div>
                    <div>
                        <p style="font-size:10px;font-weight:600;color:#6b7280;margin:0 0 6px;text-transform:uppercase;letter-spacing:.05em;">Username</p>
                        <input type="text" name="username" value="{{ auth()->user()->username }}"
                               style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid #374151;background:rgba(31,41,55,0.6);color:white;font-size:12px;outline:none;box-sizing:border-box;"
                               onfocus="this.style.borderColor='rgba(6,182,212,.5)'" onblur="this.style.borderColor='#374151'">
                    </div>
                </div>

                <div>
                    <p style="font-size:10px;font-weight:600;color:#6b7280;margin:0 0 6px;text-transform:uppercase;letter-spacing:.05em;">Email Address</p>
                    <input type="email" name="email" value="{{ auth()->user()->email }}" required
                           style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid #374151;background:rgba(31,41,55,0.6);color:white;font-size:12px;outline:none;box-sizing:border-box;"
                           onfocus="this.style.borderColor='rgba(6,182,212,.5)'" onblur="this.style.borderColor='#374151'">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div>
                        <p style="font-size:10px;font-weight:600;color:#6b7280;margin:0 0 6px;text-transform:uppercase;letter-spacing:.05em;">Phone</p>
                        <input type="tel" name="phone" value="{{ auth()->user()->phone }}"
                               style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid #374151;background:rgba(31,41,55,0.6);color:white;font-size:12px;outline:none;box-sizing:border-box;"
                               onfocus="this.style.borderColor='rgba(6,182,212,.5)'" onblur="this.style.borderColor='#374151'">
                    </div>
                    <div>
                        <p style="font-size:10px;font-weight:600;color:#6b7280;margin:0 0 6px;text-transform:uppercase;letter-spacing:.05em;">Country</p>
                        <select name="country" style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid #374151;background:rgba(31,41,55,0.6);color:white;font-size:12px;outline:none;box-sizing:border-box;">
                            @foreach(['US'=>'United States','GB'=>'United Kingdom','NG'=>'Nigeria','GH'=>'Ghana','KE'=>'Kenya','ZA'=>'South Africa','IN'=>'India','CA'=>'Canada','AU'=>'Australia','DE'=>'Germany','FR'=>'France','JP'=>'Japan','SG'=>'Singapore','AE'=>'UAE','BR'=>'Brazil'] as $code => $name)
                            <option value="{{ $code }}" style="background:#111827;" {{ auth()->user()->country === $code ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Inline message --}}
                <div x-show="profileMsg" x-cloak style="padding:10px 12px;border-radius:8px;font-size:11px;line-height:1.5;display:flex;align-items:flex-start;gap:8px;"
                     :style="profileMsg?.type === 'success' ? {background:'rgba(16,185,129,0.08)',border:'1px solid rgba(16,185,129,0.25)',color:'#34d399'} : {background:'rgba(239,68,68,0.08)',border:'1px solid rgba(239,68,68,0.25)',color:'#f87171'}">
                    <svg style="width:13px;height:13px;flex-shrink:0;margin-top:1px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                         x-show="profileMsg?.type === 'success'"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <svg style="width:13px;height:13px;flex-shrink:0;margin-top:1px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                         x-show="profileMsg?.type === 'error'" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span x-text="profileMsg?.text"></span>
                </div>

                <div>
                    <button type="submit" :disabled="savingProfile"
                            style="padding:9px 20px;border-radius:9px;border:none;font-size:12px;font-weight:700;color:white;cursor:pointer;background:linear-gradient(135deg,#06b6d4,#0891b2);appearance:none;-webkit-appearance:none;display:inline-flex;align-items:center;gap:6px;transition:opacity .15s;"
                            :style="savingProfile ? {opacity:0.6,cursor:'not-allowed'} : {}">
                        <template x-if="savingProfile">
                            <svg style="width:13px;height:13px;animation:nt-spin 1s linear infinite;" fill="none" viewBox="0 0 24 24"><circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        </template>
                        <span x-text="savingProfile ? 'Saving…' : 'Save Changes'"></span>
                    </button>
                </div>
            </form>
        </div>

        {{-- Change Password --}}
        <div style="border-radius:12px;border:1px solid #1f2937;background:rgba(17,24,39,0.6);overflow:hidden;">
            <div style="padding:12px 16px;border-bottom:1px solid #1f2937;">
                <p style="font-size:12px;font-weight:700;color:white;margin:0;">Change Password</p>
            </div>
            <form method="POST" action="{{ route('password.update') }}" style="padding:16px;display:flex;flex-direction:column;gap:12px;" @submit.prevent="handlePasswordUpdate($el)">
                @csrf @method('put')

                @foreach([['current_password','Current Password'],['password','New Password'],['password_confirmation','Confirm Password']] as [$fname, $flabel])
                <div>
                    <p style="font-size:10px;font-weight:600;color:#6b7280;margin:0 0 6px;text-transform:uppercase;letter-spacing:.05em;">{{ $flabel }}</p>
                    <input type="password" name="{{ $fname }}"
                           style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid #374151;background:rgba(31,41,55,0.6);color:white;font-size:12px;outline:none;box-sizing:border-box;"
                           onfocus="this.style.borderColor='rgba(6,182,212,.5)'" onblur="this.style.borderColor='#374151'">
                </div>
                @endforeach

                {{-- Inline message --}}
                <div x-show="passwordMsg" x-cloak style="padding:10px 12px;border-radius:8px;font-size:11px;line-height:1.5;display:flex;align-items:flex-start;gap:8px;"
                     :style="passwordMsg?.type === 'success' ? {background:'rgba(16,185,129,0.08)',border:'1px solid rgba(16,185,129,0.25)',color:'#34d399'} : {background:'rgba(239,68,68,0.08)',border:'1px solid rgba(239,68,68,0.25)',color:'#f87171'}">
                    <svg style="width:13px;height:13px;flex-shrink:0;margin-top:1px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                         x-show="passwordMsg?.type === 'success'"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <svg style="width:13px;height:13px;flex-shrink:0;margin-top:1px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                         x-show="passwordMsg?.type === 'error'" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span x-text="passwordMsg?.text"></span>
                </div>

                <div>
                    <button type="submit" :disabled="savingPassword"
                            style="padding:9px 20px;border-radius:9px;border:none;font-size:12px;font-weight:700;color:white;cursor:pointer;background:linear-gradient(135deg,#06b6d4,#0891b2);appearance:none;-webkit-appearance:none;display:inline-flex;align-items:center;gap:6px;transition:opacity .15s;"
                            :style="savingPassword ? {opacity:0.6,cursor:'not-allowed'} : {}">
                        <template x-if="savingPassword">
                            <svg style="width:13px;height:13px;animation:nt-spin 1s linear infinite;" fill="none" viewBox="0 0 24 24"><circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        </template>
                        <span x-text="savingPassword ? 'Updating…' : 'Update Password'"></span>
                    </button>
                </div>
            </form>
        </div>

        <div style="height:4px;"></div>
    </div>
</div>

<style>@keyframes nt-spin { to { transform: rotate(360deg); } }</style>

<script>
function profileModalData() {
    return {
        savingProfile: false,
        profileMsg: null,
        savingPassword: false,
        passwordMsg: null,

        async handleProfileSave(form) {
            this.savingProfile = true;
            this.profileMsg = null;
            try {
                const fd = new FormData(form);
                const res = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd,
                });
                const data = await res.json();
                if (res.ok) {
                    this.profileMsg = { type: 'success', text: data.message || 'Profile updated successfully.' };
                    setTimeout(() => $store.modal.open('profile'), 1400);
                } else {
                    const msg = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Update failed. Please try again.');
                    this.profileMsg = { type: 'error', text: msg };
                }
            } catch (_) {
                this.profileMsg = { type: 'error', text: 'Network error. Please try again.' };
            } finally {
                this.savingProfile = false;
            }
        },

        async handlePasswordUpdate(form) {
            this.savingPassword = true;
            this.passwordMsg = null;
            try {
                const fd = new FormData(form);
                const res = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd,
                });
                const data = await res.json();
                if (res.ok) {
                    this.passwordMsg = { type: 'success', text: data.message || 'Password updated successfully.' };
                    form.querySelectorAll('input[type="password"]').forEach(el => el.value = '');
                } else {
                    const msg = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Update failed. Please check your current password.');
                    this.passwordMsg = { type: 'error', text: msg };
                }
            } catch (_) {
                this.passwordMsg = { type: 'error', text: 'Network error. Please try again.' };
            } finally {
                this.savingPassword = false;
            }
        },
    };
}
</script>
