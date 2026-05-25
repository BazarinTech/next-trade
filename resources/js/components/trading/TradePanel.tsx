import React, { useState } from 'react';
import { adapter } from '../../lib/trading/engine-adapter';
import type { Trade, TradeAsset } from '../../lib/trading/chart-types';

interface Props {
    asset:          TradeAsset | null;
    currentPrice:   number;
    balance:        number;
    walletMode:     string;
    onTradePlaced:  (trade: Trade, newBalance: number) => void;
}

const EXPIRY_OPTIONS = [
    { seconds: 30,  label: '30s' },
    { seconds: 60,  label: '1m'  },
    { seconds: 120, label: '2m'  },
    { seconds: 300, label: '5m'  },
] as const;

const QUICK_AMOUNTS = [10, 25, 50, 100] as const;

// Profit rate range from the PHP engine (70–95 %)
const MIN_RATE = 0.70;
const MAX_RATE = 0.95;

function fmtPrice(price: number, type: string): string {
    if (type === 'forex') return price.toFixed(5);
    if (price >= 1000)   return '$' + price.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    if (price >= 1)      return '$' + price.toFixed(4);
    return '$' + price.toFixed(6);
}

export function TradePanel({ asset, currentPrice, balance, walletMode, onTradePlaced }: Props) {
    const [stake,     setStake]     = useState(10);
    const [expiry,    setExpiry]    = useState(60);
    const [direction, setDirection] = useState<'buy' | 'sell'>('buy');
    const [placing,   setPlacing]   = useState(false);
    const [error,     setError]     = useState<string | null>(null);

    const minPayout = +(stake * (1 + MIN_RATE)).toFixed(2);
    const maxPayout = +(stake * (1 + MAX_RATE)).toFixed(2);

    async function handlePlace(dir: 'buy' | 'sell') {
        if (!asset) return;
        if (stake <= 0)          { setError('Enter a valid stake amount.'); return; }
        if (stake > balance)     { setError('Insufficient balance.'); return; }
        setError(null);
        setDirection(dir);
        setPlacing(true);
        try {
            const { trade, walletBalance } = await adapter.placeTrade({
                assetId:       asset.id,
                direction:     dir,
                stake,
                expirySeconds: expiry,
            });
            onTradePlaced(trade, walletBalance);
        } catch (e) {
            setError(e instanceof Error ? e.message : 'Failed to place trade.');
        } finally {
            setPlacing(false);
        }
    }

    return (
        <div style={{ height: '100%', width: '100%', overflowY: 'auto', display: 'flex', flexDirection: 'column', background: '#080e1a', borderLeft: '1px solid #1f2937' }}>

            {/* Header */}
            <div className="px-5 py-4 border-b border-gray-800/60">
                <div className="flex items-center justify-between mb-1">
                    <p className="text-sm font-semibold text-white">Place Trade</p>
                    <span
                        className="text-[10px] px-2.5 py-0.5 rounded-full font-bold uppercase border"
                        style={{
                            color:            walletMode === 'demo' ? '#fbbf24' : '#34d399',
                            borderColor:      walletMode === 'demo' ? 'rgba(251,191,36,0.3)' : 'rgba(52,211,153,0.3)',
                            backgroundColor:  walletMode === 'demo' ? 'rgba(251,191,36,0.08)' : 'rgba(52,211,153,0.08)',
                        }}
                    >
                        {walletMode}
                    </span>
                </div>
                {asset ? (
                    <p className="text-xs text-gray-500">
                        {asset.symbol} &mdash; {fmtPrice(currentPrice, asset.type)}
                    </p>
                ) : (
                    <p className="text-xs text-gray-600">Select an asset</p>
                )}
            </div>

            <div className="p-5 flex flex-col gap-4 flex-1">

                {/* Balance */}
                <div className="flex items-center justify-between text-xs">
                    <span className="text-gray-500">Balance</span>
                    <span className="font-mono font-semibold text-white">
                        ${balance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                    </span>
                </div>

                {/* Direction */}
                <div className="grid grid-cols-2 gap-2">
                    <button
                        type="button"
                        onClick={() => setDirection('buy')}
                        className="flex items-center justify-center gap-1.5 py-2.5 rounded-xl border-2 text-sm font-bold transition-all"
                        style={{
                            borderColor:     direction === 'buy' ? '#10b981' : 'rgba(55,65,81,1)',
                            backgroundColor: direction === 'buy' ? 'rgba(16,185,129,0.12)' : 'transparent',
                            color:           direction === 'buy' ? '#10b981' : '#6b7280',
                        }}
                    >
                        <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                        </svg>
                        BUY
                    </button>
                    <button
                        type="button"
                        onClick={() => setDirection('sell')}
                        className="flex items-center justify-center gap-1.5 py-2.5 rounded-xl border-2 text-sm font-bold transition-all"
                        style={{
                            borderColor:     direction === 'sell' ? '#ef4444' : 'rgba(55,65,81,1)',
                            backgroundColor: direction === 'sell' ? 'rgba(239,68,68,0.12)' : 'transparent',
                            color:           direction === 'sell' ? '#ef4444' : '#6b7280',
                        }}
                    >
                        <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                        </svg>
                        SELL
                    </button>
                </div>

                {/* Stake */}
                <div>
                    <label className="block text-[10px] font-semibold text-gray-500 uppercase tracking-widest mb-1.5">
                        Stake (USD)
                    </label>
                    <div className="flex items-center gap-2">
                        <button
                            type="button"
                            onClick={() => setStake(s => Math.max(1, s - 5))}
                            className="w-9 h-9 rounded-xl bg-gray-800 hover:bg-gray-700 text-white font-bold text-lg flex items-center justify-center transition-colors"
                        >−</button>
                        <div className="relative flex-1">
                            <span className="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-500 font-bold select-none">$</span>
                            <input
                                type="number"
                                value={stake}
                                onChange={e => setStake(Math.max(1, +e.target.value || 0))}
                                min={1}
                                step={1}
                                className="w-full pl-7 pr-3 py-2 rounded-xl border bg-gray-800 border-gray-700 text-white text-sm font-mono font-semibold outline-none focus:border-cyan-500 text-center transition-colors"
                            />
                        </div>
                        <button
                            type="button"
                            onClick={() => setStake(s => s + 5)}
                            className="w-9 h-9 rounded-xl bg-gray-800 hover:bg-gray-700 text-white font-bold text-lg flex items-center justify-center transition-colors"
                        >+</button>
                    </div>
                    <div className="flex gap-1.5 mt-2">
                        {QUICK_AMOUNTS.map(q => (
                            <button
                                key={q}
                                type="button"
                                onClick={() => setStake(q)}
                                className="flex-1 text-xs py-1 rounded-lg font-medium border transition-colors"
                                style={{
                                    backgroundColor: stake === q ? 'rgba(6,182,212,0.12)' : 'rgba(31,41,55,0.8)',
                                    borderColor:     stake === q ? 'rgba(6,182,212,0.4)'  : 'rgba(55,65,81,1)',
                                    color:           stake === q ? '#06b6d4' : '#6b7280',
                                }}
                            >
                                ${q}
                            </button>
                        ))}
                    </div>
                </div>

                {/* Expiry */}
                <div>
                    <label className="block text-[10px] font-semibold text-gray-500 uppercase tracking-widest mb-1.5">
                        Expiry
                    </label>
                    <div className="grid grid-cols-4 gap-1.5">
                        {EXPIRY_OPTIONS.map(({ seconds, label }) => (
                            <button
                                key={seconds}
                                type="button"
                                onClick={() => setExpiry(seconds)}
                                className="py-2 rounded-xl border text-xs font-semibold transition-all"
                                style={{
                                    borderColor:     expiry === seconds ? '#06b6d4' : 'rgba(55,65,81,1)',
                                    backgroundColor: expiry === seconds ? 'rgba(6,182,212,0.12)' : 'transparent',
                                    color:           expiry === seconds ? '#06b6d4' : '#6b7280',
                                }}
                            >
                                {label}
                            </button>
                        ))}
                    </div>
                </div>

                {/* Payout estimate */}
                <div className="rounded-xl p-3 border bg-gray-800/50 border-gray-700/60">
                    <div className="flex justify-between text-xs mb-1">
                        <span className="text-gray-500">Potential payout</span>
                        <span className="font-semibold text-emerald-400">
                            ${minPayout.toFixed(2)} – ${maxPayout.toFixed(2)}
                        </span>
                    </div>
                    <div className="flex justify-between text-xs">
                        <span className="text-gray-500">Profit range</span>
                        <span className="text-gray-400">70% – 95% of stake</span>
                    </div>
                </div>

                {/* Error */}
                {error && (
                    <p className="text-xs text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2">
                        {error}
                    </p>
                )}

                {/* Buy / Sell buttons */}
                <div className="grid grid-cols-2 gap-2 mt-auto">
                    <button
                        type="button"
                        disabled={placing || !asset}
                        onClick={() => handlePlace('buy')}
                        className="flex flex-col items-center justify-center py-3.5 rounded-xl text-sm font-bold transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        style={{ backgroundColor: '#10b981', color: '#fff' }}
                    >
                        {placing && direction === 'buy' ? (
                            <svg className="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" opacity="0.25"/>
                                <path fill="currentColor" d="M4 12a8 8 0 018-8v8z" opacity="0.75"/>
                            </svg>
                        ) : (
                            <>
                                <span className="flex items-center gap-1.5">
                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                    </svg>
                                    BUY
                                </span>
                                <span className="text-emerald-100/70 text-[10px] mt-0.5">
                                    Call / Rise
                                </span>
                            </>
                        )}
                    </button>

                    <button
                        type="button"
                        disabled={placing || !asset}
                        onClick={() => handlePlace('sell')}
                        className="flex flex-col items-center justify-center py-3.5 rounded-xl text-sm font-bold transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        style={{ backgroundColor: '#ef4444', color: '#fff' }}
                    >
                        {placing && direction === 'sell' ? (
                            <svg className="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" opacity="0.25"/>
                                <path fill="currentColor" d="M4 12a8 8 0 018-8v8z" opacity="0.75"/>
                            </svg>
                        ) : (
                            <>
                                <span className="flex items-center gap-1.5">
                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                    </svg>
                                    SELL
                                </span>
                                <span className="text-red-100/70 text-[10px] mt-0.5">
                                    Put / Fall
                                </span>
                            </>
                        )}
                    </button>
                </div>
            </div>
        </div>
    );
}
