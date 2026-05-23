import React, { useCallback, useEffect, useRef, useState } from 'react';
import { TradingChart } from './TradingChart';
import { TradePanel } from './TradePanel';
import { TradesPanel } from './TradesPanel';
import { MobileTradeBar } from './MobileTradeBar';
import { MobileActiveTrades } from './MobileActiveTrades';
import { adapter } from '../../lib/trading/engine-adapter';
import type { Trade, TradeAsset, Timeframe } from '../../lib/trading/chart-types';

interface Props {
    assets:        TradeAsset[];
    activeTrades:  Trade[];
    recentTrades:  Trade[];
    walletBalance: number;
    walletMode:    string;
}

const TIMEFRAMES: { value: Timeframe; label: string }[] = [
    { value: 5,  label: '5s'  },
    { value: 15, label: '15s' },
    { value: 60, label: '1m'  },
];

const EAT: Intl.DateTimeFormatOptions = {
    timeZone: 'Africa/Nairobi', hour12: false, hour: '2-digit', minute: '2-digit',
};

// ─── Reliable breakpoint hooks (matchMedia matches CSS exactly) ───────────────
function useMQ(query: string): boolean {
    const mq = typeof window !== 'undefined' ? window.matchMedia(query) : null;
    const [match, setMatch] = useState(() => mq?.matches ?? false);
    useEffect(() => {
        if (!mq) return;
        setMatch(mq.matches);
        const fn = (e: MediaQueryListEvent) => setMatch(e.matches);
        mq.addEventListener('change', fn);
        return () => mq.removeEventListener('change', fn);
    }, [query]);
    return match;
}

export function TradingApp({
    assets,
    activeTrades:  initialActive,
    recentTrades:  initialRecent,
    walletBalance: initialBalance,
    walletMode,
}: Props) {
    const isMobile = useMQ('(max-width: 767px)');  // phone
    const isTablet = useMQ('(max-width: 1023px)'); // phone + tablet

    const [selected,     setSelected]     = useState<TradeAsset | null>(assets[0] ?? null);
    const [timeframe,    setTimeframe]    = useState<Timeframe>(15);
    const [balance,      setBalance]      = useState(initialBalance);
    const [active,       setActive]       = useState<Trade[]>(initialActive);
    const [completed,    setCompleted]    = useState<Trade[]>(initialRecent);
    const [currentPrice, setCurrentPrice] = useState<number>(assets[0]?.price ?? 0);
    const [toast,        setToast]        = useState<{ msg: string; type: 'ok' | 'err' } | null>(null);

    // Panels default: closed on tablet/mobile, open on desktop
    const [leftOpen,  setLeftOpen]  = useState(() => !window.matchMedia('(max-width: 1023px)').matches);
    const [rightOpen, setRightOpen] = useState(true);

    const toastTimer = useRef<ReturnType<typeof setTimeout> | null>(null);

    // Auto-close left panel when switching to tablet/mobile
    useEffect(() => { if (isTablet) setLeftOpen(false); }, [isTablet]);

    // ─── Bootstrap ────────────────────────────────────────────────────────────
    useEffect(() => {
        adapter.setInitialTrades(initialActive, initialRecent, initialBalance);
        adapter.setAsset(assets[0]?.id);
        adapter.startTradePolling();
        const unsubTrade = adapter.subscribeToTrades((newActive, newCompleted, newBalance) => {
            const prevLen = active.length;
            setActive(newActive);
            setCompleted(newCompleted);
            setBalance(newBalance);
            if (prevLen > newActive.length) showToast('Trade settled — check Closed tab.', 'ok');
        });
        const unsubTick = adapter.subscribeToTicks(tick => setCurrentPrice(tick.price));
        return () => { unsubTrade(); unsubTick(); adapter.stopTradePolling(); };
    // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const handleSelectAsset = useCallback((asset: TradeAsset) => {
        setSelected(asset);
        setCurrentPrice(asset.price);
        adapter.setAsset(asset.id);
    }, []);

    const handleTradePlaced = useCallback((trade: Trade, newBalance: number) => {
        setBalance(newBalance);
        setActive(prev => prev.find(t => t.id === trade.id) ? prev : [...prev, trade]);
        showToast(`${trade.direction.toUpperCase()} · ${trade.asset.symbol} · ${trade.expiry_seconds}s`, 'ok');
    }, []);

    function showToast(msg: string, type: 'ok' | 'err') {
        if (toastTimer.current) clearTimeout(toastTimer.current);
        setToast({ msg, type });
        toastTimer.current = setTimeout(() => setToast(null), 4000);
    }

    const assetActiveTrades = active.filter(t => t.asset.id === selected?.id);

    // ─── Toast ────────────────────────────────────────────────────────────────
    const toastEl = toast ? (
        <div style={{
            position: 'absolute', top: 8, right: 8, zIndex: 30, maxWidth: 260,
            display: 'flex', alignItems: 'center', gap: 8,
            padding: '8px 12px', borderRadius: 10, border: '1px solid',
            fontSize: 11, fontWeight: 500, pointerEvents: 'none',
            backdropFilter: 'blur(8px)',
            backgroundColor: toast.type === 'ok' ? 'rgba(16,185,129,0.15)' : 'rgba(239,68,68,0.15)',
            borderColor:     toast.type === 'ok' ? 'rgba(16,185,129,0.4)'  : 'rgba(239,68,68,0.4)',
            color:           toast.type === 'ok' ? '#34d399' : '#f87171',
        }}>
            {toast.msg}
        </div>
    ) : null;

    // ─── Asset selector bar ───────────────────────────────────────────────────
    const assetBar = (
        <div style={{ height: 44, flexShrink: 0, display: 'flex', alignItems: 'center', gap: 2, padding: '0 8px', borderBottom: '1px solid #1f2937', overflowX: 'auto', scrollbarWidth: 'none' }}>
            {assets.map(asset => {
                const sel = selected?.id === asset.id;
                const up  = Number(asset.change_pct) >= 0;
                return (
                    <button key={asset.id} type="button" onClick={() => handleSelectAsset(asset)} style={{
                        display: 'flex', flexDirection: 'column', alignItems: 'flex-start',
                        padding: '5px 10px', borderRadius: 8, border: '1px solid',
                        cursor: 'pointer', flexShrink: 0, background: 'none',
                        borderColor:     sel ? 'rgba(6,182,212,0.45)' : 'rgba(31,41,55,0.9)',
                        backgroundColor: sel ? 'rgba(6,182,212,0.09)' : 'transparent',
                    }}>
                        <span style={{ fontSize: 11, fontWeight: 700, color: sel ? '#22d3ee' : '#e5e7eb', whiteSpace: 'nowrap' }}>{asset.symbol}</span>
                        <span style={{ fontSize: 9, color: up ? '#34d399' : '#f87171', whiteSpace: 'nowrap' }}>
                            {up ? '+' : ''}{Number(asset.change_pct).toFixed(3)}%
                        </span>
                    </button>
                );
            })}
        </div>
    );

    // ─── Timeframe + price bar ────────────────────────────────────────────────
    const timeBar = (
        <div style={{ height: 38, flexShrink: 0, display: 'flex', alignItems: 'center', gap: 8, padding: '0 10px', borderBottom: '1px solid #1f2937', overflowX: 'auto', scrollbarWidth: 'none' }}>
            <div style={{ display: 'flex', gap: 2, flexShrink: 0 }}>
                {TIMEFRAMES.map(tf => (
                    <button key={tf.value} type="button" onClick={() => setTimeframe(tf.value)} style={{
                        padding: '3px 9px', borderRadius: 6, border: '1px solid', cursor: 'pointer',
                        fontSize: 11, fontWeight: 600, background: 'none',
                        borderColor:     timeframe === tf.value ? 'rgba(6,182,212,0.5)' : 'rgba(31,41,55,0.9)',
                        backgroundColor: timeframe === tf.value ? 'rgba(6,182,212,0.1)' : 'transparent',
                        color:           timeframe === tf.value ? '#22d3ee' : '#6b7280',
                    }}>{tf.label}</button>
                ))}
            </div>
            <div style={{ width: 1, height: 16, background: '#1f2937', flexShrink: 0 }}/>
            {selected && (
                <>
                    {!isMobile && <span style={{ fontSize: 11, color: '#6b7280', whiteSpace: 'nowrap', flexShrink: 0 }}>{selected.name}</span>}
                    <span style={{ fontSize: 14, fontWeight: 700, color: 'white', fontVariantNumeric: 'tabular-nums', fontFamily: 'monospace', whiteSpace: 'nowrap', flexShrink: 0 }}>
                        {formatPrice(currentPrice, selected.type)}
                    </span>
                    <span style={{
                        fontSize: 10, fontWeight: 600, padding: '2px 5px', borderRadius: 4, flexShrink: 0,
                        backgroundColor: Number(selected.change_pct) >= 0 ? 'rgba(16,185,129,0.12)' : 'rgba(239,68,68,0.12)',
                        color:           Number(selected.change_pct) >= 0 ? '#34d399' : '#f87171',
                    }}>{Number(selected.change_pct) >= 0 ? '+' : ''}{Number(selected.change_pct).toFixed(3)}%</span>
                </>
            )}
            <div style={{ marginLeft: 'auto', display: 'flex', alignItems: 'center', gap: 4, fontSize: 10, color: '#4b5563', flexShrink: 0 }}>
                <span style={{ width: 6, height: 6, borderRadius: '50%', background: '#22d3ee', display: 'inline-block' }}/>
                {isMobile ? 'Live' : `EAT ${new Date().toLocaleTimeString('en-KE', EAT)}`}
            </div>
        </div>
    );

    // ─── Chart wrapper ────────────────────────────────────────────────────────
    const chart = (
        <TradingChart
            key={`chart-${selected.id}-${timeframe}`}
            asset={selected}
            timeframe={timeframe}
            activeTrades={assetActiveTrades}
        />
    );

    // ─── Guard: no assets seeded yet ─────────────────────────────────────────
    if (!selected) {
        return (
            <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', height: '100%', gap: 12, color: '#6b7280' }}>
                <svg style={{ width: 36, height: 36, color: '#374151' }} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
                <p style={{ fontSize: 13, margin: 0 }}>No trading assets available.</p>
                <p style={{ fontSize: 11, margin: 0, color: '#4b5563' }}>Please contact the administrator.</p>
            </div>
        );
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // MOBILE layout  (< 768px)
    // ═══════════════════════════════════════════════════════════════════════════
    if (isMobile) {
        return (
            <div style={{ display: 'flex', flexDirection: 'column', height: '100%', width: '100%', background: '#030712', overflow: 'hidden', position: 'relative' }}>
                {toastEl}
                {assetBar}
                {timeBar}
                <div style={{ flex: 1, minHeight: 0 }}>{chart}</div>
                <MobileActiveTrades
                    active={active}
                    completed={completed}
                    currentPrice={currentPrice}
                    assetId={selected?.id ?? null}
                />
                <MobileTradeBar
                    asset={selected}
                    currentPrice={currentPrice}
                    balance={balance}
                    walletMode={walletMode}
                    onTradePlaced={handleTradePlaced}
                />
            </div>
        );
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // DESKTOP / TABLET layout  (≥ 768px)
    // ═══════════════════════════════════════════════════════════════════════════
    const toggleBtn: React.CSSProperties = {
        position: 'absolute', top: '50%', transform: 'translateY(-50%)',
        width: 18, height: 40, display: 'flex', alignItems: 'center', justifyContent: 'center',
        background: '#1a2332', border: '1px solid #2d3748', borderRadius: 4,
        cursor: 'pointer', color: '#6b7280', zIndex: 10,
    };

    return (
        <div style={{ display: 'flex', height: '100%', width: '100%', background: '#030712', overflow: 'hidden' }}>

            {/* Left panel — auto-closed on tablet */}
            {!isTablet && (
                <div style={{ width: leftOpen ? 220 : 0, flexShrink: 0, overflow: 'hidden', transition: 'width 0.2s ease' }}>
                    <TradesPanel active={active} completed={completed} currentPrice={currentPrice} assetId={selected?.id ?? null}/>
                </div>
            )}

            {/* Center */}
            <div style={{ flex: 1, minWidth: 0, display: 'flex', flexDirection: 'column', borderLeft: '1px solid #1f2937', borderRight: '1px solid #1f2937', position: 'relative' }}>
                {toastEl}

                {/* Panel toggles — only on true desktop */}
                {!isTablet && (
                    <>
                        <button onClick={() => setLeftOpen(o => !o)} style={{ ...toggleBtn, left: -9 }}>
                            <svg style={{ width: 10, height: 10 }} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d={leftOpen ? 'M15 19l-7-7 7-7' : 'M9 5l7 7-7 7'}/>
                            </svg>
                        </button>
                        <button onClick={() => setRightOpen(o => !o)} style={{ ...toggleBtn, right: -9 }}>
                            <svg style={{ width: 10, height: 10 }} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d={rightOpen ? 'M9 5l7 7-7 7' : 'M15 19l-7-7 7-7'}/>
                            </svg>
                        </button>
                    </>
                )}

                {assetBar}
                {timeBar}
                <div style={{ flex: 1, minHeight: 0 }}>{chart}</div>
            </div>

            {/* Right panel */}
            <div style={{ width: rightOpen ? (isTablet ? 260 : 272) : 0, flexShrink: 0, overflow: 'hidden', transition: 'width 0.2s ease' }}>
                <div style={{ width: isTablet ? 260 : 272, height: '100%' }}>
                    <TradePanel
                        asset={selected}
                        currentPrice={currentPrice}
                        balance={balance}
                        walletMode={walletMode}
                        onTradePlaced={handleTradePlaced}
                    />
                </div>
            </div>

        </div>
    );
}

function formatPrice(price: number, type: string): string {
    if (type === 'forex') return price.toFixed(5);
    if (price >= 1000)   return '$' + price.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    if (price >= 1)      return '$' + price.toFixed(4);
    return '$' + price.toFixed(6);
}
