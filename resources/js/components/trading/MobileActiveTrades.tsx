import React, { useState, useEffect } from 'react';
import type { Trade } from '../../lib/trading/chart-types';

interface Props {
    active:       Trade[];
    completed:    Trade[];
    currentPrice: number;
    assetId:      number | null;
}

function Countdown({ trade }: { trade: Trade }) {
    const [rem, setRem] = useState(trade.time_remaining);
    useEffect(() => {
        setRem(trade.time_remaining);
        if (trade.time_remaining <= 0) return;
        const id = setInterval(() => setRem(r => { if (r <= 1) { clearInterval(id); return 0; } return r - 1; }), 1000);
        return () => clearInterval(id);
    }, [trade.id, trade.time_remaining]);
    if (rem <= 0) return <span style={{ fontSize: 10, color: '#6b7280' }}>Settling…</span>;
    const pct = rem / trade.expiry_seconds;
    const color = rem <= 10 ? '#f87171' : rem <= 20 ? '#fbbf24' : '#22d3ee';
    return (
        <div style={{ display: 'flex', alignItems: 'center', gap: 4 }}>
            {/* mini arc-like bar */}
            <div style={{ width: 28, height: 4, background: 'rgba(31,41,55,0.9)', borderRadius: 20, overflow: 'hidden' }}>
                <div style={{ height: 4, borderRadius: 20, background: color, width: `${pct * 100}%`, transition: 'width 1s linear' }}/>
            </div>
            <span style={{ fontSize: 10, fontWeight: 700, fontFamily: 'monospace', color }}>{rem}s</span>
        </div>
    );
}

function LivePnl({ trade, currentPrice }: { trade: Trade; currentPrice: number }) {
    const isBuy = trade.direction === 'buy';
    const live  = currentPrice || trade.entry_price;
    const pct   = ((live - trade.entry_price) / trade.entry_price) * 100 * (isBuy ? 1 : -1);
    const ahead = pct > 0;
    return (
        <span style={{ fontSize: 10, fontWeight: 700, fontFamily: 'monospace', color: ahead ? '#34d399' : '#f87171' }}>
            {pct >= 0 ? '+' : ''}{pct.toFixed(2)}%
        </span>
    );
}

export function MobileActiveTrades({ active, completed, currentPrice, assetId }: Props) {
    const [tab, setTab] = useState<'open' | 'history'>('open');
    const [open, setOpen] = useState(false);

    // Auto-open strip when a new trade is placed
    const prevLen = React.useRef(active.length);
    useEffect(() => {
        if (active.length > prevLen.current) setOpen(true);
        prevLen.current = active.length;
    }, [active.length]);

    const totalOpen = active.length;
    const totalDone = completed.length;

    return (
        <div style={{ background: '#080e1a', borderTop: '1px solid #1f2937', flexShrink: 0 }}>

            {/* Header strip — always visible */}
            <button
                onClick={() => setOpen(o => !o)}
                style={{ width: '100%', padding: '6px 14px', background: 'transparent', border: 'none', cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 8 }}
            >
                <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                    <svg style={{ width: 12, height: 12, color: totalOpen ? '#22d3ee' : '#4b5563', flexShrink: 0 }} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span style={{ fontSize: 11, fontWeight: 600, color: '#9ca3af' }}>My Trades</span>
                    {totalOpen > 0 && (
                        <span style={{ fontSize: 9, fontWeight: 700, padding: '1px 6px', borderRadius: 20, background: 'rgba(6,182,212,0.15)', color: '#22d3ee', border: '1px solid rgba(6,182,212,0.3)' }}>
                            {totalOpen} open
                        </span>
                    )}
                    {/* Mini trade pills when collapsed */}
                    {!open && totalOpen > 0 && active.slice(0, 2).map(t => {
                        const isBuy = t.direction === 'buy';
                        return (
                            <div key={t.id} style={{ display: 'flex', alignItems: 'center', gap: 4, padding: '2px 8px', borderRadius: 20, background: isBuy ? 'rgba(52,211,153,0.08)' : 'rgba(239,68,68,0.08)', border: `1px solid ${isBuy ? 'rgba(52,211,153,0.2)' : 'rgba(239,68,68,0.2)'}` }}>
                                <span style={{ fontSize: 9, fontWeight: 700, color: isBuy ? '#34d399' : '#f87171' }}>
                                    {isBuy ? '↑' : '↓'} {t.asset.symbol}
                                </span>
                                <span style={{ fontSize: 9, color: '#6b7280' }}>${t.stake_amount.toFixed(0)}</span>
                            </div>
                        );
                    })}
                    {!open && totalOpen > 2 && (
                        <span style={{ fontSize: 10, color: '#4b5563' }}>+{totalOpen - 2} more</span>
                    )}
                </div>
                <svg style={{ width: 12, height: 12, color: '#4b5563', transition: 'transform 0.2s', transform: open ? 'rotate(180deg)' : 'none', flexShrink: 0 }} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            {/* Expanded panel */}
            {open && (
                <div style={{ borderTop: '1px solid #1f2937' }}>

                    {/* Tab bar */}
                    <div style={{ display: 'flex', borderBottom: '1px solid #1f2937' }}>
                        {(['open', 'history'] as const).map(t => (
                            <button key={t} onClick={() => setTab(t)} style={{
                                flex: 1, padding: '7px 0', fontSize: 11, fontWeight: 600, cursor: 'pointer',
                                background: 'transparent', border: 'none',
                                borderBottom: `2px solid ${tab === t ? '#06b6d4' : 'transparent'}`,
                                color: tab === t ? '#22d3ee' : '#4b5563',
                                display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 5,
                            }}>
                                {t === 'open' ? 'Open' : 'History'}
                                {t === 'open' && totalOpen > 0 && (
                                    <span style={{ fontSize: 9, fontWeight: 700, padding: '1px 5px', borderRadius: 20, background: 'rgba(6,182,212,0.15)', color: '#22d3ee' }}>{totalOpen}</span>
                                )}
                                {t === 'history' && totalDone > 0 && (
                                    <span style={{ fontSize: 9, fontWeight: 700, padding: '1px 5px', borderRadius: 20, background: 'rgba(75,85,99,0.2)', color: '#6b7280' }}>{totalDone}</span>
                                )}
                            </button>
                        ))}
                    </div>

                    {/* Open trades */}
                    {tab === 'open' && (
                        <div style={{ maxHeight: 200, overflowY: 'auto' }}>
                            {active.length === 0 ? (
                                <div style={{ padding: '18px 0', textAlign: 'center' }}>
                                    <p style={{ fontSize: 12, color: '#4b5563', margin: 0 }}>No open trades</p>
                                </div>
                            ) : (
                                <div style={{ display: 'flex', flexDirection: 'column', gap: 0 }}>
                                    {active.map((t, i) => {
                                        const isBuy = t.direction === 'buy';
                                        return (
                                            <div key={t.id} style={{
                                                display: 'flex', alignItems: 'center', gap: 10, padding: '9px 14px',
                                                borderBottom: i < active.length - 1 ? '1px solid rgba(31,41,55,0.7)' : 'none',
                                            }}>
                                                {/* Direction badge */}
                                                <div style={{
                                                    width: 28, height: 28, borderRadius: 8, flexShrink: 0,
                                                    background: isBuy ? 'rgba(52,211,153,0.1)' : 'rgba(239,68,68,0.1)',
                                                    border: `1px solid ${isBuy ? 'rgba(52,211,153,0.25)' : 'rgba(239,68,68,0.25)'}`,
                                                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                                                }}>
                                                    <svg style={{ width: 12, height: 12, color: isBuy ? '#34d399' : '#f87171' }} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d={isBuy ? 'M5 10l7-7m0 0l7 7m-7-7v18' : 'M19 14l-7 7m0 0l-7-7m7 7V3'}/>
                                                    </svg>
                                                </div>

                                                {/* Asset + stake */}
                                                <div style={{ flex: 1, minWidth: 0 }}>
                                                    <div style={{ display: 'flex', alignItems: 'center', gap: 5 }}>
                                                        <span style={{ fontSize: 12, fontWeight: 700, color: 'white' }}>{t.asset.symbol}</span>
                                                        <span style={{ fontSize: 10, fontWeight: 700, color: isBuy ? '#34d399' : '#f87171' }}>{isBuy ? 'BUY' : 'SELL'}</span>
                                                        <span style={{ fontSize: 9, padding: '1px 5px', borderRadius: 4, background: t.wallet_type === 'demo' ? 'rgba(251,191,36,0.1)' : 'rgba(52,211,153,0.1)', color: t.wallet_type === 'demo' ? '#fbbf24' : '#34d399', fontWeight: 600 }}>
                                                            {t.wallet_type}
                                                        </span>
                                                    </div>
                                                    <span style={{ fontSize: 10, color: '#6b7280', fontFamily: 'monospace' }}>${t.stake_amount.toFixed(2)}</span>
                                                </div>

                                                {/* PnL */}
                                                <div style={{ textAlign: 'right', flexShrink: 0 }}>
                                                    {t.asset.id === assetId
                                                        ? <LivePnl trade={t} currentPrice={currentPrice} />
                                                        : <span style={{ fontSize: 10, color: '#4b5563' }}>—</span>
                                                    }
                                                    <div style={{ marginTop: 3 }}>
                                                        <Countdown trade={t} />
                                                    </div>
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            )}
                        </div>
                    )}

                    {/* History tab */}
                    {tab === 'history' && (
                        <div style={{ maxHeight: 200, overflowY: 'auto' }}>
                            {completed.length === 0 ? (
                                <div style={{ padding: '18px 0', textAlign: 'center' }}>
                                    <p style={{ fontSize: 12, color: '#4b5563', margin: 0 }}>No closed trades yet</p>
                                </div>
                            ) : (
                                <div>
                                    {completed.map((t, i) => {
                                        const isBuy = t.direction === 'buy';
                                        const pnl   = t.profit_loss ?? 0;
                                        const won   = t.status === 'won';
                                        const statusColors: Record<string, { bg: string; fg: string }> = {
                                            won:  { bg: 'rgba(16,185,129,0.12)',  fg: '#34d399' },
                                            lost: { bg: 'rgba(239,68,68,0.12)',   fg: '#f87171' },
                                            draw: { bg: 'rgba(251,191,36,0.12)',  fg: '#fbbf24' },
                                        };
                                        const sc = statusColors[t.status] ?? { bg: 'rgba(107,114,128,0.12)', fg: '#9ca3af' };
                                        return (
                                            <div key={t.id} style={{
                                                display: 'flex', alignItems: 'center', gap: 10, padding: '9px 14px',
                                                borderBottom: i < completed.length - 1 ? '1px solid rgba(31,41,55,0.7)' : 'none',
                                            }}>
                                                <div style={{
                                                    width: 28, height: 28, borderRadius: 8, flexShrink: 0,
                                                    background: isBuy ? 'rgba(52,211,153,0.1)' : 'rgba(239,68,68,0.1)',
                                                    border: `1px solid ${isBuy ? 'rgba(52,211,153,0.2)' : 'rgba(239,68,68,0.2)'}`,
                                                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                                                }}>
                                                    <svg style={{ width: 12, height: 12, color: isBuy ? '#34d399' : '#f87171' }} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d={isBuy ? 'M5 10l7-7m0 0l7 7m-7-7v18' : 'M19 14l-7 7m0 0l-7-7m7 7V3'}/>
                                                    </svg>
                                                </div>
                                                <div style={{ flex: 1, minWidth: 0 }}>
                                                    <div style={{ display: 'flex', alignItems: 'center', gap: 5 }}>
                                                        <span style={{ fontSize: 12, fontWeight: 700, color: 'white' }}>{t.asset.symbol}</span>
                                                        <span style={{ fontSize: 10, fontWeight: 700, color: isBuy ? '#34d399' : '#f87171' }}>{isBuy ? 'BUY' : 'SELL'}</span>
                                                    </div>
                                                    <span style={{ fontSize: 10, color: '#6b7280', fontFamily: 'monospace' }}>${t.stake_amount.toFixed(2)}</span>
                                                </div>
                                                <div style={{ textAlign: 'right', flexShrink: 0 }}>
                                                    <span style={{ fontSize: 13, fontWeight: 700, fontFamily: 'monospace', color: pnl >= 0 ? '#34d399' : '#f87171' }}>
                                                        {pnl >= 0 ? '+' : '−'}${Math.abs(pnl).toFixed(2)}
                                                    </span>
                                                    <div style={{ marginTop: 2 }}>
                                                        <span style={{ fontSize: 9, fontWeight: 700, padding: '1px 6px', borderRadius: 20, background: sc.bg, color: sc.fg, textTransform: 'uppercase' }}>
                                                            {t.status}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            )}
                        </div>
                    )}

                </div>
            )}
        </div>
    );
}
