<div x-data="depositModalData()" style="display:flex;flex-direction:column;height:100%;">

    {{-- Header --}}
    <div style="padding:16px 20px;border-bottom:1px solid #1f2937;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
        <div>
            <h2 style="font-size:15px;font-weight:700;color:white;margin:0;">Deposit Funds</h2>
            <p style="font-size:11px;color:#6b7280;margin:2px 0 0;">USDT TRC20 · M-Pesa</p>
        </div>
        <button @click="$store.modal.close()" style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;border-radius:8px;border:1px solid #374151;background:transparent;cursor:pointer;color:#9ca3af;font-size:16px;">&times;</button>
    </div>

    {{-- Scrollable body --}}
    <div style="overflow-y:auto;padding:16px 20px;flex:1;display:flex;flex-direction:column;gap:14px;">

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

        {{-- USDT TRC20 section --}}
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
                                style="flex-shrink:0;padding:8px 10px;border-radius:8px;border:1px solid;font-size:10px;font-weight:600;cursor:pointer;transition:all .15s;"
                                :style="copied ? 'border-color:rgba(16,185,129,.4);color:#34d399;background:rgba(16,185,129,.08)' : 'border-color:#374151;color:#9ca3af;background:transparent'">
                            <span x-text="copied ? '✓ Copied' : 'Copy'"></span>
                        </button>
                        @endif
                    </div>
                </div>

                {{-- Form --}}
                <form method="POST" action="{{ route('deposits.usdt') }}" enctype="multipart/form-data" @submit.prevent="handleUsdtSubmit($el)">
                    @csrf

                    {{-- Wallet selector --}}
                    <div style="margin-bottom:10px;">
                        <p style="font-size:10px;font-weight:600;color:#6b7280;margin:0 0 6px;text-transform:uppercase;letter-spacing:.05em;">Credit to Wallet</p>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
                            @foreach(['demo' => ['Demo','#fbbf24','rgba(245,158,11,0.1)','rgba(245,158,11,0.3)'], 'live' => ['Live','#22d3ee','rgba(6,182,212,0.1)','rgba(6,182,212,0.3)']] as $mode => [$label, $clr, $bg, $border])
                            <label style="display:flex;align-items:center;gap:8px;padding:10px;border-radius:8px;border:1px solid;cursor:pointer;transition:all .15s;"
                                   :style="walletType === '{{ $mode }}' ? 'border-color:{{ $border }};background:{{ $bg }}' : 'border-color:#374151;background:transparent'">
                                <input type="radio" name="wallet_type" value="{{ $mode }}" x-model="walletType" style="display:none;">
                                <span style="width:10px;height:10px;border-radius:50%;border:2px solid {{ $clr }};flex-shrink:0;"
                                      :style="walletType === '{{ $mode }}' ? 'background:{{ $clr }}' : 'background:transparent'"></span>
                                <div>
                                    <p style="font-size:11px;font-weight:700;color:{{ $clr }};margin:0;">{{ $label }}</p>
                                    <p style="font-size:9px;color:#6b7280;margin:0;">${{ number_format(${$mode.'Wallet'}->available_balance ?? 0, 2) }}</p>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Amount --}}
                    <div style="margin-bottom:10px;">
                        <p style="font-size:10px;font-weight:600;color:#6b7280;margin:0 0 6px;text-transform:uppercase;letter-spacing:.05em;">Amount (USDT)</p>
                        <input type="number" name="amount" x-model.number="usdtAmount" min="1" step="0.01" required placeholder="e.g. 100"
                               style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid #374151;background:rgba(31,41,55,0.6);color:white;font-size:13px;font-family:monospace;outline:none;box-sizing:border-box;">
                        <div style="display:flex;gap:4px;margin-top:6px;">
                            @foreach([50, 100, 250, 500] as $q)
                            <button type="button" @click="usdtAmount = {{ $q }}"
                                    style="flex:1;padding:4px 0;border-radius:6px;font-size:10px;font-weight:600;cursor:pointer;border:1px solid #374151;color:#9ca3af;background:transparent;transition:all .15s;"
                                    :style="usdtAmount === {{ $q }} ? 'border-color:rgba(6,182,212,.5);color:#22d3ee;background:rgba(6,182,212,.08)' : ''">
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
                                            style="position:absolute;top:12px;right:12px;width:22px;height:22px;border-radius:50%;background:#ef4444;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;color:white;font-size:12px;">&times;</button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <button type="submit" :disabled="submitting || !usdtAmount || usdtAmount < 1 || !previewUrl"
                            style="width:100%;padding:11px 0;border-radius:10px;border:none;font-size:13px;font-weight:700;color:white;cursor:pointer;transition:all .15s;background:linear-gradient(135deg,#06b6d4,#0891b2);"
                            :style="(submitting || !usdtAmount || !previewUrl) ? 'opacity:.5;cursor:not-allowed' : ''">
                        <span x-show="!submitting">Submit for Review</span>
                        <span x-show="submitting" x-cloak>Submitting…</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- M-Pesa section (Kenya only) --}}
        @if($isKenya)
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
            <form method="POST" action="{{ route('deposits.mpesa') }}" style="padding:14px;display:flex;flex-direction:column;gap:10px;" x-data="mpesaDepositData()" @submit="mpesaSubmitting=true">
                @csrf
                <input type="hidden" name="wallet_type" value="{{ $walletMode }}">
                <div>
                    <p style="font-size:10px;font-weight:600;color:#6b7280;margin:0 0 6px;text-transform:uppercase;letter-spacing:.05em;">Amount (KES)</p>
                    <input type="number" name="kes_amount" x-model.number="kesAmount" @input="calcUsd()" min="10" max="250000" step="1" required placeholder="e.g. 1000"
                           style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid #374151;background:rgba(31,41,55,0.6);color:white;font-size:13px;font-family:monospace;outline:none;box-sizing:border-box;">
                    <div style="display:flex;gap:4px;margin-top:6px;">
                        @foreach([500,1000,2000,5000] as $q)
                        <button type="button" @click="kesAmount={{ $q }};calcUsd()"
                                style="flex:1;padding:4px 0;border-radius:6px;font-size:10px;font-weight:600;cursor:pointer;border:1px solid #374151;color:#9ca3af;background:transparent;">
                            {{ number_format($q) }}
                        </button>
                        @endforeach
                    </div>
                    <p style="font-size:10px;color:#34d399;margin:6px 0 0;" x-show="kesAmount > 0">≈ $<span x-text="usdPreview.toFixed(2)"></span> USD · Rate: {{ $exchangeRate }} KES/USD</p>
                </div>
                <div>
                    <p style="font-size:10px;font-weight:600;color:#6b7280;margin:0 0 6px;text-transform:uppercase;letter-spacing:.05em;">M-Pesa Number</p>
                    <input type="tel" name="phone" placeholder="0712 345 678" required
                           style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid #374151;background:rgba(31,41,55,0.6);color:white;font-size:13px;outline:none;box-sizing:border-box;">
                </div>
                <button type="submit" :disabled="mpesaSubmitting || !kesAmount || kesAmount < 10"
                        style="width:100%;padding:11px 0;border-radius:10px;border:none;font-size:13px;font-weight:700;color:white;cursor:pointer;background:linear-gradient(135deg,#10b981,#059669);">
                    <span x-show="!mpesaSubmitting">Send STK Push</span>
                    <span x-show="mpesaSubmitting" x-cloak>Sending…</span>
                </button>
            </form>
        </div>
        @endif

    </div>
</div>

<script>
function depositModalData() {
    return {
        walletType: '{{ $walletMode }}',
        usdtAmount: 0,
        submitting: false,
        previewUrl: null,
        isDragging: false,
        copied: false,
        copyAddress(addr) {
            navigator.clipboard.writeText(addr).then(() => { this.copied = true; setTimeout(() => this.copied = false, 2000); });
        },
        handleFile(e) { const f = e.target.files[0]; if (f) { const r = new FileReader(); r.onload = ev => this.previewUrl = ev.target.result; r.readAsDataURL(f); } },
        handleDrop(e) { this.isDragging = false; const f = e.dataTransfer.files[0]; if (f?.type.startsWith('image/')) { this.$refs.proofFile.files = e.dataTransfer.files; this.handleFile({target: this.$refs.proofFile}); } },
        handleUsdtSubmit(form) { if (!this.previewUrl) return; this.submitting = true; form.submit(); },
    };
}
function mpesaDepositData() {
    return {
        kesAmount: 0,
        usdPreview: 0,
        rate: {{ $exchangeRate }},
        mpesaSubmitting: false,
        calcUsd() { this.usdPreview = this.kesAmount > 0 && this.rate > 0 ? Math.round(this.kesAmount / this.rate * 100) / 100 : 0; },
    };
}
</script>
