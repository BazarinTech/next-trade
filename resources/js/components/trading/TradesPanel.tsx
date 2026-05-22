import React, { useState, useEffect } from 'react';
import type { Trade } from '../../lib/trading/chart-types';

interface Props {
    active:       Trade[];
    completed:    Trade[];
    currentPrice: number;
    assetId:      number | null;
}

function TimerBadge({ trade }: { trade: Trade }) {
    const [remaining, setRemaining] = useState(trade.time_remaining);

    useEffect(() => {
        setRemaining(trade.time_remaining);
        if (trade.time_remaining <= 0) return;
        const id = setInterval(() => {
            setRemaining(r => {
                if (r <= 1) { clearInterval(id); return 0; }
                return r - 1;
            });
        }, 1000);
        return () => clearInterval(id);
    }, [trade.id, trade.time_remaining]);

    if (remaining <= 0) return <span style={{ fontSize: 10, color: '#9ca3af' }}>Settling…</span>;
    return (
        <span style={{
            fontSize: 10, fontWeight: 700, fontFamily: 'monospace',
            color: remaining <= 10 ? '#f87171' : '#22d3ee',
        }}>
            {remaining}s
        </span>
    );
}

function LivePnl({ trade, currentPrice }: { trade: Trade; currentPrice: number }) {
    const entry   = trade.entry_price;
    const live    = currentPrice || entry;
    const isBuy   = trade.direction === 'buy';
    const diffPct = ((live - entry) / entry) * 100 * (isBuy ? 1 : -1);
    const ahead   = diffPct >= 0;
    return (
        <span style={{ fontSize: 10, fontWeight: 700, fontFamily: 'monospace', color: ahead ? '#34d399' : '#f87171' }}>
            {diffPct >= 0 ? '+' : ''}{diffPct.toFixed(3)}%
        </span>
    );
}

function resultColor(status: Trade['status']): string {
    const map: Record<string, string> = {
        won: '#34d399', lost: '#f87171', draw: '#fbbf24',
        cancelled: '#9ca3af', open: '#22d3ee',
    };
    return map[status] ?? '#9ca3af';
}

function EmptyState({ icon, label }: { icon: string; label: string }) {
    return (
        <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', padding: '32px 16px', gap: 8 }}>
            <svg style={{ width: 28, height: 28, color: '#374151' }} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d={icon}/>
            </svg>
            <p style={{ fontSize: 11, color: '#4b5563', textAlign: 'center', margin: 0 }}>{label}</p>
        </div>
    );
}

export function TradesPanel({ active, completed, currentPrice, assetId }: Props) {
    const [tab, setTab] = useState<'open' | 'closed' | 'txns'>('open');

    const tabStyle = (t: typeof tab) => ({
        flex: 1, padding: '8px 4px', fontSize: 11, fontWeight: 600, background: 'transparent', border: 'none',
        cursor: 'pointer', transition: 'color 0.15s',
        borderBottom: `2px solid ${tab === t ? '#06b6d4' : 'transparent'}`,
        color: tab === t ? '#22d3ee' : '#6b7280',
    });

    return (
        <div style={{ width: 220, flexShrink: 0, display: 'flex', flexDirection: 'column', height: '100%', background: '#080e1a', overflow: 'hidden' }}>

            {/* Tabs */}
            <div style={{ display: 'flex', borderBottom: '1px solid #1f2937', flexShrink: 0 }}>
                <button style={tabStyle('open')} onClick={() => setTab('open')}>
                    Open{active.length > 0 ? ` (${active.length})` : ''}
                </button>
                <button style={tabStyle('closed')} onClick={() => setTab('closed')}>
                    Closed
                </button>
                <button style={tabStyle('txns')} onClick={() => setTab('txns')}>
                    Txns
                </button>
            </div>

            {/* Scrollable content */}
            <div style={{ flex: 1, overflowY: 'auto', overflowX: 'hidden' }}>

                {/* ── Open trades ───────────────────────────── */}
                {tab === 'open' && (
                    active.length === 0
                        ? <EmptyState
                            icon="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                            label="No open positions"
                          />
                        : <div>
                            {active.map(trade => (
                                <div key={trade.id} style={{ padding: '10px 12px', borderBottom: '1px solid rgba(31,41,55,0.6)' }}>
                                    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 4 }}>
                                        <div style={{ display: 'flex', alignItems: 'center', gap: 5 }}>
                                            <span style={{ fontSize: 12, fontWeight: 700, color: 'white' }}>{trade.asset.symbol}</span>
                                            <span style={{
                                                fontSize: 9, fontWeight: 700, padding: '1px 5px', borderRadius: 4,
                                                background: trade.direction === 'buy' ? 'rgba(16,185,129,0.12)' : 'rgba(239,68,68,0.12)',
                                                color: trade.direction === 'buy' ? '#10b981' : '#ef4444',
                                            }}>
                                                {trade.direction.toUpperCase()}
                                            </span>
                                        </div>
                                        <TimerBadge trade={trade}/>
                                    </div>
                                    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                                        <span style={{ fontSize: 10, color: '#6b7280' }}>
                                            ${trade.stake_amount.toFixed(2)} stake
                                        </span>
                                        {trade.asset.id === assetId
                                            ? <LivePnl trade={trade} currentPrice={currentPrice}/>
                                            : <span style={{ fontSize: 10, color: '#374151' }}>—</span>
                                        }
                                    </div>
                                    <div style={{ marginTop: 3 }}>
                                        <span style={{ fontSize: 9, color: '#374151', fontFamily: 'monospace' }}>
                                            @ {trade.entry_price.toFixed(trade.asset.type === 'forex' ? 5 : 2)}
                                        </span>
                                    </div>
                                </div>
                            ))}
                          </div>
                )}

                {/* ── Closed trades ─────────────────────────── */}
                {tab === 'closed' && (
                    completed.length === 0
                        ? <EmptyState
                            icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                            label="No closed trades yet"
                          />
                        : <div>
                            {completed.map(trade => {
                                const pnl = trade.profit_loss ?? 0;
                                return (
                                    <div key={trade.id} style={{ padding: '10px 12px', borderBottom: '1px solid rgba(31,41,55,0.6)' }}>
                                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 4 }}>
                                            <div style={{ display: 'flex', alignItems: 'center', gap: 5 }}>
                                                <span style={{ fontSize: 12, fontWeight: 700, color: 'white' }}>{trade.asset.symbol}</span>
                                                <span style={{
                                                    fontSize: 9, fontWeight: 700, padding: '1px 5px', borderRadius: 4,
                                                    background: trade.direction === 'buy' ? 'rgba(16,185,129,0.12)' : 'rgba(239,68,68,0.12)',
                                                    color: trade.direction === 'buy' ? '#10b981' : '#ef4444',
                                                }}>
                                                    {trade.direction.toUpperCase()}
                                                </span>
                                            </div>
                                            <span style={{
                                                fontSize: 9, fontWeight: 700, padding: '1px 6px', borderRadius: 4,
                                                background: `${resultColor(trade.status)}20`,
                                                color: resultColor(trade.status),
                                                textTransform: 'uppercase',
                                            }}>
                                                {trade.status}
                                            </span>
                                        </div>
                                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                                            <span style={{ fontSize: 10, color: '#6b7280' }}>
                                                ${trade.stake_amount.toFixed(2)}
                                            </span>
                                            <span style={{ fontSize: 11, fontWeight: 700, fontFamily: 'monospace', color: pnl >= 0 ? '#34d399' : '#f87171' }}>
                                                {pnl >= 0 ? '+' : '−'}${Math.abs(pnl).toFixed(2)}
                                            </span>
                                        </div>
                                        {trade.closed_at && (
                                            <div style={{ marginTop: 2 }}>
                                                <span style={{ fontSize: 9, color: '#374151' }}>
                                                    {new Date(trade.closed_at).toLocaleTimeString('en-KE', { timeZone: 'Africa/Nairobi', hour12: false })}
                                                </span>
                                            </div>
                                        )}
                                    </div>
                                );
                            })}
                          </div>
                )}

                {/* ── Transactions link ─────────────────────── */}
                {tab === 'txns' && (
                    <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', padding: '24px 16px', gap: 12 }}>
                        <svg style={{ width: 28, height: 28, color: '#374151' }} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2M9 12h6M9 16h4"/>
                        </svg>
                        <p style={{ fontSize: 11, color: '#6b7280', textAlign: 'center', margin: 0 }}>Full transaction history</p>
                        <button
                            onClick={() => (window as any).Alpine?.store('modal')?.open('history')}
                            style={{ fontSize: 11, fontWeight: 600, color: '#22d3ee', cursor: 'pointer', display: 'flex', alignItems: 'center', gap: 4, padding: '6px 14px', borderRadius: 8, border: '1px solid rgba(6,182,212,0.3)', background: 'rgba(6,182,212,0.06)' }}>
                            View Transactions
                            <svg style={{ width: 10, height: 10 }} fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                )}

            </div>
        </div>
    );
}
