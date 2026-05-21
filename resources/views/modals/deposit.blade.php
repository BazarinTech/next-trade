<div x-data="depositModalData()" style="display:flex;flex-direction:column;">

    {{-- Header --}}
    <div style="padding:16px 20px;border-bottom:1px solid #1f2937;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;position:sticky;top:0;z-index:2;background:#0b1120;">
        <div>
            <h2 style="font-size:15px;font-weight:700;color:white;margin:0;">Deposit Funds</h2>
            <p style="font-size:11px;color:#6b7280;margin:2px 0 0;">{{ $isKenya ? 'USDT TRC20 · M-Pesa' : 'USDT TRC20' }}</p>
        </div>
        <button @click="$store.modal.close()" style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;border-radius:8px;border:1px solid #374151;background:transparent;cursor:pointer;color:#9ca3af;font-size:16px;">&times;</button>
    </div>

    {{-- Tabs (Kenya users only) --}}
    @if($isKenya)
    <div style="padding:12px 20px;border-bottom:1px solid #1f2937;flex-shrink:0;background:#0b1120;position:sticky;top:57px;z-index:1;">
        <div style="display:flex;background:rgba(17,24,39,0.9);border:1px solid #374151;border-radius:10px;padding:3px;gap:3px;">
            <button type="button" @click="tab='usdt'"
                    style="flex:1;padding:7px 0;border-radius:7px;border:none;font-size:12px;font-weight:600;cursor:pointer;transition:all .18s;appearance:none;-webkit-appearance:none;"
                    :style="tab==='usdt' ? {background:'#06b6d4',color:'white',boxShadow:'0 2px 8px rgba(6,182,212,0.3)'} : {background:'transparent',color:'#6b7280'}">
                USDT TRC20
            </button>
            <button type="button" @click="tab='mpesa'"
                    style="flex:1;padding:7px 0;border-radius:7px;border:none;font-size:12px;font-weight:600;cursor:pointer;transition:all .18s;appearance:none;-webkit-appearance:none;"
                    :style="tab==='mpesa' ? {background:'#10b981',color:'white',boxShadow:'0 2px 8px rgba(16,185,129,0.3)'} : {background:'transparent',color:'#6b7280'}">
                M-Pesa
            </button>
        </div>
    </div>
    @endif

    {{-- Body --}}
    <div style="padding:16px 20px;display:flex;flex-direction:column;gap:14px;">

        {{-- Pending deposits --}}
        @foreach($pendingDeposits as $pd)
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;border-radius:10px;background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2);">
            <div style="display:flex;align-items:center;gap:8px;">
                <span style="width:7px;height:7px;border-radius:50%;background:#fbbf24;animation:pulse 2s infinite;flex-shrink:0;"></span>
                <div>
                    <p style="font-size:11px;font-weight:600;color:#fbbf24;margin:0;">Pending {{ $pd->method_label }} Deposit</p>
                    <p style="font-size:10px;color:rgba(251,191,36,0.7);margin:0;">
                        {{ $pd->isUsdtDeposit() ? $pd->local_amount.' USDT' : 'KES '.number_format($pd->local_amount,2) }} · {{ $pd->created_at->diffForHumans() }}
                    </p>
                </div>
            </div>
        </div>
        @endforeach

        {{-- ── USDT TRC20 Panel ───────────────────────────────────────── --}}
        <div x-show="tab === 'usdt'">
            <div style="border-radius:12px;border:1px solid #1f2937;background:rgba(17,24,39,0.6);overflow:hidden;">
                <div style="display:flex;align-items:center;gap:10px;padding:12px 14px;border-bottom:1px solid #1f2937;">
                    <div style="width:36px;height:36px;border-radius:10px;background:rgba(6,182,212,0.1);border:1px solid rgba(6,182,212,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg style="width:16px;height:16px;color:#22d3ee;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p style="font-size:13px;font-weight:700;color:white;margin:0;">USDT Deposit (TRC20)</p>
                        <p style="font-size:10px;color:#6b7280;margin:0;">Manual · Admin reviewed · Global</p>
                    </div>
                </div>

                <div style="padding:14px;display:flex;flex-direction:column;gap:12px;">

                    {{-- Warning --}}
                    <div style="display:flex;align-items:flex-start;gap:8px;padding:10px 12px;border-radius:8px;background:rgba(245,158,11,0.07);border:1px solid rgba(245,158,11,0.2);">
                        <svg style="width:13px;height:13px;color:#fbbf24;margin-top:1px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <p style="font-size:10px;color:rgba(251,191,36,0.85);margin:0;"><strong>Send only USDT on TRC20 network.</strong> Other tokens or networks = permanent loss.</p>
                    </div>

                    {{-- Deposit address --}}
                    <div>
                        <p style="font-size:10px;font-weight:600;color:#6b7280;margin:0 0 6px;text-transform:uppercase;letter-spacing:.05em;">Our USDT TRC20 Address</p>
                        <div style="display:flex;gap:6px;align-items:center;">
                            <div style="flex:1;padding:8px 10px;border-radius:8px;border:1px solid #374151;background:rgba(31,41,55,0.5);font-family:monospace;font-size:10px;color:#d1d5db;word-break:break-all;">
                                {{ config('crypto.usdt_trc20_wallet_address') ?: 'Not configured — contact support' }}
                            </div>
                            @if(config('crypto.usdt_trc20_wallet_address'))
                            <button type="button" @click="copyAddress('{{ config('crypto.usdt_trc20_wallet_address') }}')"
                                    style="flex-shrink:0;padding:8px 10px;border-radius:8px;border:1px solid;font-size:10px;font-weight:600;cursor:pointer;transition:all .15s;appearance:none;-webkit-appearance:none;"
                                    :style="copied ? {borderColor:'rgba(16,185,129,.4)',color:'#34d399',background:'rgba(16,185,129,.08)'} : {borderColor:'#374151',color:'#9ca3af',background:'transparent'}">
                                <span x-text="copied ? '✓ Copied' : 'Copy'"></span>
                            </button>
                            @endif
                        </div>
                    </div>

                    {{-- Form --}}
                    <form method="POST" action="{{ route('deposits.usdt') }}" enctype="multipart/form-data" @submit.prevent="handleUsdtSubmit($el)">
                        @csrf
                        <input type="hidden" name="wallet_type" value="live">

                        {{-- Amount --}}
                        <div style="margin-bottom:10px;">
                            <p style="font-size:10px;font-weight:600;color:#6b7280;margin:0 0 6px;text-transform:uppercase;letter-spacing:.05em;">Amount (USDT)</p>
                            <input type="number" name="amount" x-model.number="usdtAmount" min="1" step="0.01" required placeholder="e.g. 100"
                                   style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid #374151;background:rgba(31,41,55,0.6);color:white;font-size:13px;font-family:monospace;outline:none;box-sizing:border-box;">
                            <div style="display:flex;gap:4px;margin-top:6px;">
                                @foreach([50, 100, 250, 500] as $q)
                                <button type="button" @click="usdtAmount = {{ $q }}"
                                        style="flex:1;padding:6px 0;border-radius:6px;font-size:10px;font-weight:600;cursor:pointer;transition:all .15s;appearance:none;-webkit-appearance:none;"
                                        :style="usdtAmount === {{ $q }} ? {border:'1px solid rgba(6,182,212,.5)',color:'#22d3ee',background:'rgba(6,182,212,.08)'} : {border:'1px solid #374151',color:'#9ca3af',background:'rgba(31,41,55,0.5)'}">
                                    {{ $q }}
                                </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- TXID --}}
                        <div style="margin-bottom:10px;">
                            <p style="font-size:10px;font-weight:600;color:#6b7280;margin:0 0 6px;text-transform:uppercase;letter-spacing:.05em;">Transaction ID (TXID)</p>
                            <input type="text" name="txid" minlength="10" maxlength="255" required placeholder="Paste TXID from your wallet..."
                                   style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid #374151;background:rgba(31,41,55,0.6);color:white;font-size:11px;font-family:monospace;outline:none;box-sizing:border-box;">
                        </div>

                        {{-- Proof upload --}}
                        <div style="margin-bottom:12px;">
                            <p style="font-size:10px;font-weight:600;color:#6b7280;margin:0 0 6px;text-transform:uppercase;letter-spacing:.05em;">Proof Screenshot</p>
                            <div style="border:2px dashed;border-radius:8px;cursor:pointer;transition:all .15s;"
                                 :style="previewUrl ? 'border-color:rgba(6,182,212,.4);background:rgba(6,182,212,.04)' : (isDragging ? 'border-color:rgba(6,182,212,.5)' : 'border-color:#374151')"
                                 @dragover.prevent="isDragging=true" @dragleave="isDragging=false"
                                 @drop.prevent="handleDrop($event)" @click="$refs.proofFile.click()">
                                <input type="file" name="proof" x-ref="proofFile" accept="image/*" class="sr-only" @change="handleFile($event)">
                                <template x-if="!previewUrl">
                                    <div style="display:flex;flex-direction:column;align-items:center;padding:20px 12px;">
                                        <svg style="width:24px;height:24px;color:#4b5563;margin-bottom:6px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <p style="font-size:11px;color:#6b7280;margin:0;">Click or drag screenshot</p>
                                        <p style="font-size:9px;color:#4b5563;margin:4px 0 0;">JPG, PNG, WEBP · Max 4MB</p>
                                    </div>
                                </template>
                                <template x-if="previewUrl">
                                    <div style="position:relative;padding:8px;">
                                        <img :src="previewUrl" style="width:100%;max-height:120px;object-fit:contain;border-radius:6px;">
                                        <button type="button" @click.stop="previewUrl=null;$refs.proofFile.value=''"
                                                style="position:absolute;top:12px;right:12px;width:22px;height:22px;border-radius:50%;background:#ef4444;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;color:white;font-size:12px;appearance:none;-webkit-appearance:none;">&times;</button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Inline message --}}
                        <div x-show="usdtMessage" x-cloak style="margin-bottom:10px;padding:10px 12px;border-radius:8px;font-size:11px;line-height:1.5;"
                             :style="usdtMessage?.type === 'success' ? {background:'rgba(16,185,129,0.08)',border:'1px solid rgba(16,185,129,0.25)',color:'#34d399'} : {background:'rgba(239,68,68,0.08)',border:'1px solid rgba(239,68,68,0.25)',color:'#f87171'}">
                            <span x-text="usdtMessage?.text"></span>
                            <a x-show="usdtMessage?.link" :href="usdtMessage?.link?.url"
                               style="display:inline-block;margin-left:8px;font-weight:700;text-decoration:underline;"
                               x-text="usdtMessage?.link?.label"></a>
                        </div>

                        <button type="submit" :disabled="submitting || !usdtAmount || usdtAmount < 1 || !previewUrl"
                                style="width:100%;padding:11px 0;border-radius:10px;border:none;font-size:13px;font-weight:700;color:white;cursor:pointer;transition:all .15s;background:linear-gradient(135deg,#06b6d4,#0891b2);appearance:none;-webkit-appearance:none;display:flex;align-items:center;justify-content:center;gap:6px;"
                                :style="(submitting || !usdtAmount || !previewUrl) ? {opacity:0.5,cursor:'not-allowed'} : {}">
                            <span x-show="!submitting">Submit for Review</span>
                            <template x-if="submitting">
                                <span style="display:flex;align-items:center;gap:6px;">
                                    <svg style="width:14px;height:14px;animation:nt-spin 1s linear infinite;" fill="none" viewBox="0 0 24 24"><circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    Submitting…
                                </span>
                            </template>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── M-Pesa Panel (Kenya only) ──────────────────────────────── --}}
        @if($isKenya)
        <div x-show="tab === 'mpesa'" x-cloak>
            <div style="border-radius:12px;border:1px solid #1f2937;background:rgba(17,24,39,0.6);overflow:hidden;">
                <div style="display:flex;align-items:center;gap:10px;padding:12px 14px;border-bottom:1px solid #1f2937;">
                    <div style="width:36px;height:36px;border-radius:10px;background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg style="width:16px;height:16px;color:#34d399;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    </div>
                    <div>
                        <p style="font-size:13px;font-weight:700;color:white;margin:0;">M-Pesa STK Push</p>
                        <p style="font-size:10px;color:#6b7280;margin:0;">Instant · Safaricom Kenya</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('deposits.mpesa') }}" style="padding:14px;display:flex;flex-direction:column;gap:12px;" @submit.prevent="handleMpesaSubmit($el)">
                    @csrf
                    <input type="hidden" name="wallet_type" value="live">

                    {{-- Amount --}}
                    <div>
                        <p style="font-size:10px;font-weight:600;color:#6b7280;margin:0 0 6px;text-transform:uppercase;letter-spacing:.05em;">Amount (KES)</p>
                        <input type="number" name="kes_amount" x-model.number="kesAmount" @input="calcUsd()" min="10" max="250000" step="1" required placeholder="e.g. 1000"
                               style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid #374151;background:rgba(31,41,55,0.6);color:white;font-size:13px;font-family:monospace;outline:none;box-sizing:border-box;">
                        <div style="display:flex;gap:4px;margin-top:6px;">
                            @foreach([500, 1000, 2000, 5000] as $q)
                            <button type="button" @click="kesAmount={{ $q }};calcUsd()"
                                    style="flex:1;padding:6px 0;border-radius:6px;font-size:10px;font-weight:600;cursor:pointer;transition:all .15s;appearance:none;-webkit-appearance:none;"
                                    :style="kesAmount === {{ $q }} ? {border:'1px solid rgba(16,185,129,.5)',color:'#34d399',background:'rgba(16,185,129,.08)'} : {border:'1px solid #374151',color:'#9ca3af',background:'rgba(31,41,55,0.5)'}">
                                {{ number_format($q) }}
                            </button>
                            @endforeach
                        </div>
                        <p style="font-size:10px;color:#34d399;margin:6px 0 0;" x-show="kesAmount > 0">≈ $<span x-text="usdPreview.toFixed(2)"></span> USD · Rate: {{ $exchangeRate }} KES/USD</p>
                    </div>

                    {{-- Phone --}}
                    <div>
                        <p style="font-size:10px;font-weight:600;color:#6b7280;margin:0 0 6px;text-transform:uppercase;letter-spacing:.05em;">M-Pesa Number</p>
                        <input type="tel" name="phone" placeholder="0712 345 678" required
                               style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid #374151;background:rgba(31,41,55,0.6);color:white;font-size:13px;outline:none;box-sizing:border-box;">
                        <p style="font-size:10px;color:#6b7280;margin:5px 0 0;">Safaricom number registered with M-Pesa</p>
                    </div>

                    {{-- Info note --}}
                    <div style="display:flex;align-items:flex-start;gap:8px;padding:10px 12px;border-radius:8px;background:rgba(16,185,129,0.06);border:1px solid rgba(16,185,129,0.2);">
                        <svg style="width:13px;height:13px;color:#34d399;margin-top:1px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p style="font-size:10px;color:rgba(52,211,153,0.85);margin:0;line-height:1.5;">An STK push will be sent to your phone. Enter your M-Pesa PIN to complete the payment.</p>
                    </div>

                    {{-- Inline message --}}
                    <div x-show="mpesaMessage" x-cloak style="padding:10px 12px;border-radius:8px;font-size:11px;line-height:1.5;"
                         :style="mpesaMessage?.type === 'success' ? {background:'rgba(16,185,129,0.08)',border:'1px solid rgba(16,185,129,0.25)',color:'#34d399'} : {background:'rgba(239,68,68,0.08)',border:'1px solid rgba(239,68,68,0.25)',color:'#f87171'}">
                        <span x-text="mpesaMessage?.text"></span>
                        <a x-show="mpesaMessage?.link" :href="mpesaMessage?.link?.url"
                           style="display:inline-block;margin-left:8px;font-weight:700;text-decoration:underline;"
                           x-text="mpesaMessage?.link?.label"></a>
                    </div>

                    <button type="submit" :disabled="mpesaSubmitting || !kesAmount || kesAmount < 10"
                            style="width:100%;padding:11px 0;border-radius:10px;border:none;font-size:13px;font-weight:700;color:white;cursor:pointer;background:linear-gradient(135deg,#10b981,#059669);transition:all .15s;appearance:none;-webkit-appearance:none;display:flex;align-items:center;justify-content:center;gap:6px;"
                            :style="(mpesaSubmitting || !kesAmount || kesAmount < 10) ? {opacity:0.5,cursor:'not-allowed'} : {}">
                        <span x-show="!mpesaSubmitting">Send STK Push</span>
                        <template x-if="mpesaSubmitting">
                            <span style="display:flex;align-items:center;gap:6px;">
                                <svg style="width:14px;height:14px;animation:nt-spin 1s linear infinite;" fill="none" viewBox="0 0 24 24"><circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                Sending…
                            </span>
                        </template>
                    </button>
                </form>
            </div>
        </div>
        @endif

    </div>
</div>

<style>@keyframes nt-spin { to { transform: rotate(360deg); } }</style>

<script>
function depositModalData() {
    return {
        tab: 'usdt',
        walletType: 'live',
        usdtAmount: 0,
        submitting: false,
        previewUrl: null,
        isDragging: false,
        copied: false,
        kesAmount: 0,
        usdPreview: 0,
        rate: {{ $exchangeRate }},
        mpesaSubmitting: false,
        usdtMessage: null,
        mpesaMessage: null,

        copyAddress(addr) {
            navigator.clipboard.writeText(addr).then(() => { this.copied = true; setTimeout(() => this.copied = false, 2000); });
        },
        handleFile(e) {
            const f = e.target.files[0];
            if (f) { const r = new FileReader(); r.onload = ev => this.previewUrl = ev.target.result; r.readAsDataURL(f); }
        },
        handleDrop(e) {
            this.isDragging = false;
            const f = e.dataTransfer.files[0];
            if (f?.type.startsWith('image/')) { this.$refs.proofFile.files = e.dataTransfer.files; this.handleFile({target: this.$refs.proofFile}); }
        },
        async handleUsdtSubmit(form) {
            if (!this.previewUrl) return;
            this.submitting = true;
            this.usdtMessage = null;
            try {
                const fd = new FormData(form);
                const res = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd,
                });
                const data = await res.json();
                if (res.ok) {
                    this.usdtMessage = { type: 'success', text: data.message, link: data.redirect ? { url: data.redirect, label: 'View deposit →' } : null };
                    this.usdtAmount = 0;
                    this.previewUrl = null;
                    if (this.$refs.proofFile) this.$refs.proofFile.value = '';
                    const txid = form.querySelector('[name="txid"]');
                    if (txid) txid.value = '';
                } else {
                    const msg = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Submission failed. Please try again.');
                    this.usdtMessage = { type: 'error', text: msg, link: null };
                }
            } catch (_) {
                this.usdtMessage = { type: 'error', text: 'Network error. Please try again.', link: null };
            } finally {
                this.submitting = false;
            }
        },
        async handleMpesaSubmit(form) {
            if (!this.kesAmount || this.kesAmount < 10) return;
            this.mpesaSubmitting = true;
            this.mpesaMessage = null;
            try {
                const fd = new FormData(form);
                const res = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd,
                });
                const data = await res.json();
                if (res.ok) {
                    this.mpesaMessage = { type: 'success', text: data.message, link: data.redirect ? { url: data.redirect, label: 'Track status →' } : null };
                    this.kesAmount = 0;
                    this.usdPreview = 0;
                    const phone = form.querySelector('[name="phone"]');
                    if (phone) phone.value = '';
                } else {
                    const msg = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Failed to send STK push. Please try again.');
                    this.mpesaMessage = { type: 'error', text: msg, link: null };
                }
            } catch (_) {
                this.mpesaMessage = { type: 'error', text: 'Network error. Please try again.', link: null };
            } finally {
                this.mpesaSubmitting = false;
            }
        },
        calcUsd() {
            this.usdPreview = this.kesAmount > 0 && this.rate > 0 ? Math.round(this.kesAmount / this.rate * 100) / 100 : 0;
        },
    };
}
</script>
