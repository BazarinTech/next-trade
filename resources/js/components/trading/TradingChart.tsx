import React, { useEffect, useRef } from 'react';
import {
    createChart,
    ColorType,
    CrosshairMode,
    LineStyle,
    CandlestickSeries,
    TickMarkType,
    type IChartApi,
    type ISeriesApi,
    type CandlestickData,
    type SeriesType,
    type IPriceLine,
    type CreatePriceLineOptions,
} from 'lightweight-charts';
import { CandleBuilder } from '../../lib/trading/candle-builder';
import { adapter } from '../../lib/trading/engine-adapter';
import type { Trade, TradeAsset, Timeframe } from '../../lib/trading/chart-types';

interface Props {
    asset:        TradeAsset;
    timeframe:    Timeframe;
    activeTrades: Trade[];
}

export function TradingChart({ asset, timeframe, activeTrades }: Props) {
    const containerRef   = useRef<HTMLDivElement>(null);
    const chartRef       = useRef<IChartApi | null>(null);
    const seriesRef      = useRef<ISeriesApi<SeriesType> | null>(null);
    const builderRef     = useRef<CandleBuilder>(new CandleBuilder(timeframe));
    const priceLinesRef  = useRef<Map<number, IPriceLine>>(new Map());
    const unsubTickRef   = useRef<(() => void) | null>(null);
    const loadedRef      = useRef(false);
    const pendingRef     = useRef<import('../../lib/trading/chart-types').PriceTick[]>([]);

    // ─── Chart initialisation (once per asset + timeframe) ────────────────────

    useEffect(() => {
        if (!containerRef.current) return;

        const isForex = asset.type === 'forex';

        const chart = createChart(containerRef.current, {
            autoSize: true,
            layout: {
                background: { type: ColorType.Solid, color: '#030712' },
                textColor:  '#6b7280',
                fontSize:   11,
            },
            grid: {
                vertLines: { color: 'rgba(55,65,81,0.2)' },
                horzLines: { color: 'rgba(55,65,81,0.2)' },
            },
            crosshair: {
                mode:     CrosshairMode.Normal,
                vertLine: {
                    color:     'rgba(6,182,212,0.4)',
                    width:     1,
                    style:     LineStyle.Dotted,
                    labelVisible: true,
                },
                horzLine: {
                    color:     'rgba(6,182,212,0.4)',
                    width:     1,
                    style:     LineStyle.Dotted,
                    labelVisible: true,
                },
            },
            rightPriceScale: {
                borderColor: 'rgba(55,65,81,0.5)',
                textColor:   '#6b7280',
                scaleMargins: { top: 0.1, bottom: 0.1 },
            },
            timeScale: {
                borderColor:    'rgba(55,65,81,0.5)',
                timeVisible:    true,
                secondsVisible: timeframe <= 15,
                rightOffset:    8,
                fixLeftEdge:    false,
                fixRightEdge:   false,
                tickMarkFormatter: (ts: number, markType: TickMarkType) => {
                    const d   = new Date(ts * 1000);
                    const tz  = 'Africa/Nairobi';
                    if (markType === TickMarkType.Year) {
                        return d.toLocaleDateString('en-KE', { timeZone: tz, year: 'numeric' });
                    }
                    if (markType === TickMarkType.Month) {
                        return d.toLocaleDateString('en-KE', { timeZone: tz, month: 'short', year: 'numeric' });
                    }
                    if (markType === TickMarkType.DayOfMonth) {
                        return d.toLocaleDateString('en-KE', { timeZone: tz, month: 'short', day: '2-digit' });
                    }
                    // TickMarkType.Time and TimeWithSeconds — show date + time
                    return d.toLocaleString('en-KE', {
                        timeZone: tz, hour12: false,
                        month: 'short', day: '2-digit',
                        hour: '2-digit', minute: '2-digit',
                        ...(markType === TickMarkType.TimeWithSeconds ? { second: '2-digit' } : {}),
                    });
                },
            },
            handleScroll: { mouseWheel: true, pressedMouseMove: true, horzTouchDrag: true },
            handleScale:  { mouseWheel: true, pinch: true },
            localization: {
                timeFormatter: (ts: number) =>
                    new Date(ts * 1000).toLocaleString('en-KE', {
                        timeZone: 'Africa/Nairobi',
                        hour12:   false,
                        year:     'numeric',
                        month:    'short',
                        day:      '2-digit',
                        hour:     '2-digit',
                        minute:   '2-digit',
                        second:   '2-digit',
                    }),
                priceFormatter: isForex
                    ? (p: number) => p.toFixed(5)
                    : (p: number) => p >= 1000
                        ? '$' + p.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                        : '$' + p.toFixed(p >= 1 ? 4 : 6),
            },
        });

        const series = chart.addSeries(CandlestickSeries, {
            upColor:        '#10b981',
            downColor:      '#ef4444',
            borderUpColor:  '#10b981',
            borderDownColor:'#ef4444',
            wickUpColor:    '#10b981',
            wickDownColor:  '#ef4444',
            priceLineVisible: true,
            priceLineColor:   '#06b6d4',
            priceLineWidth:   1,
            priceLineStyle:   LineStyle.Dashed,
            lastValueVisible: true,
        });

        chartRef.current  = chart;
        seriesRef.current = series;
        builderRef.current = new CandleBuilder(timeframe);
        loadedRef.current  = false;
        pendingRef.current = [];

        // Subscribe to live ticks *before* async history load to buffer any
        // ticks that arrive during the fetch.
        unsubTickRef.current = adapter.subscribeToTicks(tick => {
            if (!loadedRef.current) {
                pendingRef.current.push(tick);
                return;
            }
            if (!seriesRef.current) return;
            const { candle } = builderRef.current.processTick(tick);
            seriesRef.current.update(candle as CandlestickData);
            chartRef.current?.timeScale().scrollToRealTime();
        });

        // Load history, then flush buffered ticks
        adapter.getHistoricalTicks(asset.id).then(ticks => {
            if (!seriesRef.current || !chartRef.current) return;

            const candles = builderRef.current.loadHistoricalTicks(ticks);
            if (candles.length > 0) {
                seriesRef.current.setData(candles as CandlestickData[]);
                // Stretch all historical candles to fill the full chart width
                chartRef.current.timeScale().fitContent();
            }

            loadedRef.current = true;

            // Process any ticks that arrived while we were loading
            for (const tick of pendingRef.current) {
                const { candle } = builderRef.current.processTick(tick);
                seriesRef.current.update(candle as CandlestickData);
            }
            pendingRef.current = [];
            // Keep the latest candle visible at the right edge
            chartRef.current.timeScale().scrollToRealTime();
        });

        return () => {
            unsubTickRef.current?.();
            unsubTickRef.current = null;
            priceLinesRef.current.clear();
            chart.remove();
            chartRef.current  = null;
            seriesRef.current = null;
        };
    // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [asset.id, timeframe]);

    // ─── Sync entry price lines with active trades ────────────────────────────

    useEffect(() => {
        const series = seriesRef.current;
        if (!series) return;

        const currentLines = priceLinesRef.current;
        const activeIds    = new Set(activeTrades.map(t => t.id));

        // Remove lines for settled/switched trades
        currentLines.forEach((line, id) => {
            if (!activeIds.has(id)) {
                series.removePriceLine(line);
                currentLines.delete(id);
            }
        });

        // Add lines for new trades on this asset
        for (const trade of activeTrades) {
            if (currentLines.has(trade.id)) continue;
            const isBuy = trade.direction === 'buy';
            const opts: CreatePriceLineOptions = {
                price:            trade.entry_price,
                color:            isBuy ? '#10b981' : '#ef4444',
                lineWidth:        1,
                lineStyle:        LineStyle.Dashed,
                axisLabelVisible: true,
                title:            isBuy ? '▲ BUY' : '▼ SELL',
            };
            const line = series.createPriceLine(opts);
            currentLines.set(trade.id, line);
        }
    }, [activeTrades]);

    return (
        <div style={{ width: '100%', height: '100%', background: '#030712' }}>
            <div ref={containerRef} style={{ width: '100%', height: '100%' }} />
        </div>
    );
}
