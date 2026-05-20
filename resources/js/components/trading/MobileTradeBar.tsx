import React, { useState } from 'react';
import { adapter } from '../../lib/trading/engine-adapter';
import type { Trade, TradeAsset } from '../../lib/trading/chart-types';

interface Props {
    asset:         TradeAsset | null;
    currentPrice:  number;
    balance:       number;
    walletMode:    string;
    onTradePlaced: (trade: Trade, newBalance: number) => void;
}

const EXPIRY_OPTIONS = [
    { seconds: 30,  label: '30s' },
    { seconds: 60,  label: '1m'  },
    { seconds: 120, label: '2m'  },
    { seconds: 300, label: '5m'  },
] as const;

const QUICK_AMOUNTS = [5, 10, 25, 50] as const;

const MIN_RATE = 0.70;
const MAX_RATE = 0.95;

export function MobileTradeBar({ asset, currentPrice, balance, walletMode, onTradePlaced }: Props) {
    const [stake,   setStake]   = useState(10);
    const [expiry,  setExpiry]  = useState(60);
    const [placing, setPlacing] = useState<'buy' | 'sell' | null>(null);
    const [error,   setError]   = useState<string | null>(null);
    const [expanded, setExpanded] = useState(false);

    const minPayout = +(stake * (1 + MIN_RATE)).toFixed(2);
    const maxPayout = +(stake * (1 + MAX_RATE)).toFixed(2);

    async function handlePlace(dir: 'buy' | 'sell') {
        if (!asset) return;
        if (stake <= 0 || stake > balance) { setError(stake <= 0 ? 'Enter a valid stake.' : 'Insufficient balance.'); return; }
        setError(null);
        setPlacing(dir);
        try {
            const { trade, walletBalance } = await adapter.placeTrade({
                assetId: asset.id, direction: dir, stake, expirySeconds: expiry,
            });
            onTradePlaced(trade, walletBalance);
        } catch (e) {
            setError(e instanceof Error ? e.message : 'Failed to place trade.');
        } finally {
            setPlacing(null);
        }
    }

    const btnBase: React.CSSProperties = {
        flex: 1, display: 'flex', flexDirection: 'column', alignItems: 'center',
        justifyContent: 'center', border: 'none', cursor: 'pointer',
        padding: '14px 8px', gap: 2,
    };

    return (
        <div style={{ background: '#080e1a', borderTop: '1px solid #1f2937', flexShrink: 0 }}>

            {/* Expand/collapse toggle bar */}
            <button
                onClick={() => setExpanded(o => !o)}
                style={{ width: '100%', padding: '6px 16px', background: 'transparent', border: 'none', cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'space-between', borderBottom: expanded ? '1px solid #1f2937' : 'none' }}
            >
                <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                    <span style={{ fontSize: 10, color: '#6b7280' }}>Stake</span>
                    <span style={{ fontSize: 12, fontWeight: 700, color: 'white' }}>${stake}</span>
                    <span style={{ fontSize: 10, color: '#6b7280' }}>·</span>
                    <span style={{ fontSize: 10, color: '#6b7280' }}>Expiry</span>
                    <span style={{ fontSize: 12, fontWeight: 700, color: 'white' }}>
                        {EXPIRY_OPTIONS.find(o => o.seconds === expiry)?.label ?? expiry + 's'}
                    </span>
                    <span style={{ fontSize: 10, color: '#6b7280' }}>·</span>
                    <span style={{ fontSize: 10, color: '#34d399' }}>Payout ${minPayout}–${maxPayout}</span>
                </div>
                <svg style={{ width: 14, height: 14, color: '#6b7280', transition: 'transform 0.2s', transform: expanded ? 'rotate(180deg)' : 'none' }} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            {/* Expandable controls */}
            {expanded && (
                <div style={{ padding: '10px 12px', display: 'flex', flexDirection: 'column', gap: 8 }}>

                    {/* Balance + mode */}
                    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                        <span style={{ fontSize: 10, color: '#6b7280' }}>Balance</span>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                            <span style={{ fontSize: 12, fontWeight: 700, color: 'white', fontFamily: 'monospace' }}>
                                ${balance.toFixed(2)}
                            </span>
                            <span style={{
                                fontSize: 9, fontWeight: 600, padding: '1px 6px', borderRadius: 4, textTransform: 'uppercase',
                                background: walletMode === 'demo' ? 'rgba(251,191,36,0.12)' : 'rgba(52,211,153,0.12)',
                                color:      walletMode === 'demo' ? '#fbbf24' : '#34d399',
                            }}>{walletMode}</span>
                        </div>
                    </div>

                    {/* Stake row */}
                    <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                        <button onClick={() => setStake(s => Math.max(1, s - 5))}
                                style={{ width: 32, height: 32, borderRadius: 8, background: '#1f2937', border: '1px solid #374151', color: 'white', fontSize: 16, cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>−</button>
                        <div style={{ flex: 1, position: 'relative' }}>
                            <span style={{ position: 'absolute', left: 8, top: '50%', transform: 'translateY(-50%)', color: '#6b7280', fontSize: 13, fontWeight: 700 }}>$</span>
                            <input type="number" value={stake} min={1}
                                   onChange={e => setStake(Math.max(1, +e.target.value || 0))}
                                   style={{ width: '100%', height: 32, paddingLeft: 22, paddingRight: 8, borderRadius: 8, background: '#1f2937', border: '1px solid #374151', color: 'white', fontSize: 13, fontWeight: 700, fontFamily: 'monospace', textAlign: 'center', outline: 'none' }}/>
                        </div>
                        <button onClick={() => setStake(s => s + 5)}
                                style={{ width: 32, height: 32, borderRadius: 8, background: '#1f2937', border: '1px solid #374151', color: 'white', fontSize: 16, cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>+</button>
                        <div style={{ display: 'flex', gap: 3, flexShrink: 0 }}>
                            {QUICK_AMOUNTS.map(q => (
                                <button key={q} onClick={() => setStake(q)}
                                        style={{ padding: '4px 7px', borderRadius: 6, fontSize: 10, fontWeight: 600, cursor: 'pointer', border: '1px solid',
                                            background: stake === q ? 'rgba(6,182,212,0.12)' : 'rgba(31,41,55,0.8)',
                                            borderColor: stake === q ? 'rgba(6,182,212,0.5)' : '#374151',
                                            color:       stake === q ? '#22d3ee' : '#6b7280',
                                        }}>${q}</button>
                            ))}
                        </div>
                    </div>

                    {/* Expiry row */}
                    <div style={{ display: 'flex', gap: 4 }}>
                        {EXPIRY_OPTIONS.map(({ seconds, label }) => (
                            <button key={seconds} onClick={() => setExpiry(seconds)}
                                    style={{ flex: 1, padding: '5px 0', borderRadius: 8, fontSize: 11, fontWeight: 600, cursor: 'pointer', border: '1px solid', transition: 'all 0.15s',
                                        background: expiry === seconds ? 'rgba(6,182,212,0.12)' : 'transparent',
                                        borderColor: expiry === seconds ? 'rgba(6,182,212,0.5)' : '#374151',
                                        color:       expiry === seconds ? '#22d3ee' : '#6b7280',
                                    }}>{label}</button>
                        ))}
                    </div>

                    {error && <p style={{ fontSize: 10, color: '#f87171', background: 'rgba(239,68,68,0.08)', border: '1px solid rgba(239,68,68,0.2)', borderRadius: 6, padding: '4px 8px', margin: 0 }}>{error}</p>}
                </div>
            )}

            {/* BUY / SELL buttons — always visible */}
            <div style={{ display: 'flex' }}>
                <button
                    onClick={() => handlePlace('buy')}
                    disabled={!!placing || !asset}
                    style={{ ...btnBase, background: placing === 'buy' ? '#059669' : '#10b981', opacity: placing && placing !== 'buy' ? 0.7 : 1 }}
                >
                    {placing === 'buy' ? (
                        <svg style={{ width: 18, height: 18, color: 'white' }} className="animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" opacity="0.25"/>
                            <path fill="currentColor" d="M4 12a8 8 0 018-8v8z" opacity="0.75"/>
                        </svg>
                    ) : (
                        <>
                            <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                                <svg style={{ width: 16, height: 16, color: 'white' }} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                </svg>
                                <span style={{ fontSize: 16, fontWeight: 700, color: 'white' }}>BUY</span>
                            </div>
                            <span style={{ fontSize: 9, color: 'rgba(255,255,255,0.65)' }}>Call / Rise</span>
                        </>
                    )}
                </button>
                <button
                    onClick={() => handlePlace('sell')}
                    disabled={!!placing || !asset}
                    style={{ ...btnBase, background: placing === 'sell' ? '#dc2626' : '#ef4444', opacity: placing && placing !== 'sell' ? 0.7 : 1 }}
                >
                    {placing === 'sell' ? (
                        <svg style={{ width: 18, height: 18, color: 'white' }} className="animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" opacity="0.25"/>
                            <path fill="currentColor" d="M4 12a8 8 0 018-8v8z" opacity="0.75"/>
                        </svg>
                    ) : (
                        <>
                            <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                                <svg style={{ width: 16, height: 16, color: 'white' }} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                </svg>
                                <span style={{ fontSize: 16, fontWeight: 700, color: 'white' }}>SELL</span>
                            </div>
                            <span style={{ fontSize: 9, color: 'rgba(255,255,255,0.65)' }}>Put / Fall</span>
                        </>
                    )}
                </button>
            </div>
        </div>
    );
}
