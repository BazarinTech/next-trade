import React, { useState, useEffect } from 'react';
import type { Trade } from '../../lib/trading/chart-types';

interface Props {
    active:     Trade[];
    completed:  Trade[];
    currentPrice: number;
    assetId:    number | null;
}

function fmtMoney(n: number): string {
    return '$' + Math.abs(n).toFixed(2);
}

function TimerCell({ trade }: { trade: Trade }) {
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

    if (remaining <= 0) {
        return <span className="text-xs text-gray-500">Settling…</span>;
    }
    return (
        <span
            className="text-xs font-mono font-semibold"
            style={{ color: remaining <= 10 ? '#f87171' : '#22d3ee' }}
        >
            {remaining}s
        </span>
    );
}

function PnlCell({ trade, currentPrice }: { trade: Trade; currentPrice: number }) {
    const entry    = trade.entry_price;
    const live     = currentPrice || entry;
    const isBuy    = trade.direction === 'buy';
    const ahead    = isBuy ? live > entry : live < entry;
    const diffPct  = ((live - entry) / entry) * 100 * (isBuy ? 1 : -1);
    const color    = ahead ? '#34d399' : '#f87171';

    return (
        <span className="text-xs font-mono font-semibold" style={{ color }}>
            {diffPct >= 0 ? '+' : ''}{diffPct.toFixed(3)}%
        </span>
    );
}

export function ActiveTrades({ active, completed, currentPrice, assetId }: Props) {
    const [tab, setTab] = useState<'open' | 'history'>('open');

    const tabStyle = (t: 'open' | 'history') => ({
        borderBottomWidth:  2,
        borderBottomStyle: 'solid' as const,
        borderBottomColor: tab === t ? '#06b6d4' : 'transparent',
        color:              tab === t ? '#22d3ee' : '#6b7280',
    });

    const statusBadge = (status: Trade['status']) => {
        const map: Record<Trade['status'], { bg: string; text: string }> = {
            won:       { bg: 'rgba(16,185,129,0.1)',  text: '#34d399' },
            lost:      { bg: 'rgba(239,68,68,0.1)',   text: '#f87171' },
            draw:      { bg: 'rgba(251,191,36,0.1)',  text: '#fbbf24' },
            cancelled: { bg: 'rgba(107,114,128,0.1)', text: '#9ca3af' },
            open:      { bg: 'rgba(6,182,212,0.1)',   text: '#22d3ee' },
        };
        const s = map[status] ?? map.cancelled;
        return (
            <span
                className="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase"
                style={{ backgroundColor: s.bg, color: s.text }}
            >
                {status}
            </span>
        );
    };

    return (
        <div className="rounded-2xl border border-gray-800/60 bg-gray-900/60 overflow-hidden">

            {/* Tabs */}
            <div className="flex border-b border-gray-800/60">
                <button
                    type="button"
                    className="flex items-center gap-2 px-5 py-3 text-xs font-semibold transition-colors"
                    style={tabStyle('open')}
                    onClick={() => setTab('open')}
                >
                    Open
                    {active.length > 0 && (
                        <span className="px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-cyan-500/15 text-cyan-400">
                            {active.length}
                        </span>
                    )}
                </button>
                <button
                    type="button"
                    className="px-5 py-3 text-xs font-semibold transition-colors"
                    style={tabStyle('history')}
                    onClick={() => setTab('history')}
                >
                    History
                </button>
            </div>

            {/* Open trades */}
            {tab === 'open' && (
                <div className="overflow-x-auto">
                    {active.length === 0 ? (
                        <div className="py-10 flex flex-col items-center gap-2">
                            <svg className="w-7 h-7 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p className="text-sm text-gray-500 font-medium">No open trades</p>
                            <p className="text-xs text-gray-600">Place a trade using the panel</p>
                        </div>
                    ) : (
                        <table className="w-full">
                            <thead>
                                <tr className="border-b border-gray-800/60">
                                    {['Pair', 'Dir.', 'Stake', 'Entry', 'Live PnL', 'Expiry', 'Mode'].map(col => (
                                        <th key={col} className="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">
                                            {col}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody>
                                {active.map(trade => (
                                    <tr key={trade.id} className="border-b border-gray-800/40 last:border-0 hover:bg-gray-800/30 transition-colors">
                                        <td className="px-4 py-2.5">
                                            <span className="text-xs font-semibold text-white">{trade.asset.symbol}</span>
                                        </td>
                                        <td className="px-4 py-2.5">
                                            <span
                                                className="text-xs font-bold"
                                                style={{ color: trade.direction === 'buy' ? '#34d399' : '#f87171' }}
                                            >
                                                {trade.direction.toUpperCase()}
                                            </span>
                                        </td>
                                        <td className="px-4 py-2.5">
                                            <span className="text-xs font-mono text-gray-300">
                                                ${trade.stake_amount.toFixed(2)}
                                            </span>
                                        </td>
                                        <td className="px-4 py-2.5">
                                            <span className="text-xs font-mono text-gray-500">
                                                {trade.entry_price.toFixed(
                                                    trade.asset.type === 'forex' ? 5 : 2
                                                )}
                                            </span>
                                        </td>
                                        <td className="px-4 py-2.5">
                                            {trade.asset.id === assetId
                                                ? <PnlCell trade={trade} currentPrice={currentPrice} />
                                                : <span className="text-xs text-gray-600">—</span>
                                            }
                                        </td>
                                        <td className="px-4 py-2.5">
                                            <TimerCell trade={trade} />
                                        </td>
                                        <td className="px-4 py-2.5">
                                            <span
                                                className="text-[10px] px-2 py-0.5 rounded-full font-medium"
                                                style={{
                                                    backgroundColor: trade.wallet_type === 'demo'
                                                        ? 'rgba(251,191,36,0.1)' : 'rgba(52,211,153,0.1)',
                                                    color: trade.wallet_type === 'demo' ? '#fbbf24' : '#34d399',
                                                }}
                                            >
                                                {trade.wallet_type}
                                            </span>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    )}
                </div>
            )}

            {/* History */}
            {tab === 'history' && (
                <div className="overflow-x-auto">
                    {completed.length === 0 ? (
                        <div className="py-10 flex flex-col items-center gap-2">
                            <svg className="w-7 h-7 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p className="text-sm text-gray-500 font-medium">No closed trades yet</p>
                        </div>
                    ) : (
                        <table className="w-full">
                            <thead>
                                <tr className="border-b border-gray-800/60">
                                    {['Pair', 'Dir.', 'Stake', 'Entry', 'Exit', 'P&L', 'Result', 'Closed'].map(col => (
                                        <th key={col} className="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">
                                            {col}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody>
                                {completed.map(trade => {
                                    const pnl = trade.profit_loss ?? 0;
                                    return (
                                        <tr key={trade.id} className="border-b border-gray-800/40 last:border-0 hover:bg-gray-800/30 transition-colors">
                                            <td className="px-4 py-2.5">
                                                <span className="text-xs font-semibold text-white">{trade.asset.symbol}</span>
                                            </td>
                                            <td className="px-4 py-2.5">
                                                <span
                                                    className="text-xs font-bold"
                                                    style={{ color: trade.direction === 'buy' ? '#34d399' : '#f87171' }}
                                                >
                                                    {trade.direction.toUpperCase()}
                                                </span>
                                            </td>
                                            <td className="px-4 py-2.5">
                                                <span className="text-xs font-mono text-gray-500">
                                                    ${trade.stake_amount.toFixed(2)}
                                                </span>
                                            </td>
                                            <td className="px-4 py-2.5">
                                                <span className="text-xs font-mono text-gray-500">
                                                    {trade.entry_price.toFixed(
                                                        trade.asset.type === 'forex' ? 5 : 2
                                                    )}
                                                </span>
                                            </td>
                                            <td className="px-4 py-2.5">
                                                <span className="text-xs font-mono text-gray-500">
                                                    {trade.exit_price
                                                        ? trade.exit_price.toFixed(trade.asset.type === 'forex' ? 5 : 2)
                                                        : '—'}
                                                </span>
                                            </td>
                                            <td className="px-4 py-2.5">
                                                <span
                                                    className="text-xs font-mono font-semibold"
                                                    style={{ color: pnl >= 0 ? '#34d399' : '#f87171' }}
                                                >
                                                    {pnl >= 0 ? '+' : '−'}{fmtMoney(pnl)}
                                                </span>
                                            </td>
                                            <td className="px-4 py-2.5">
                                                {statusBadge(trade.status)}
                                            </td>
                                            <td className="px-4 py-2.5">
                                                <span className="text-[10px] text-gray-500">
                                                    {trade.closed_at
                                                        ? new Date(trade.closed_at).toLocaleTimeString('en-KE', { timeZone: 'Africa/Nairobi', hour12: false })
                                                        : '—'}
                                                </span>
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    )}
                </div>
            )}
        </div>
    );
}
