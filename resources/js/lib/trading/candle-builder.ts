import type { OHLCCandle, PriceTick } from './chart-types';

/**
 * Converts a stream of price ticks into OHLC candlesticks.
 *
 * Rules:
 *  - Each candle covers exactly `timeframe` seconds.
 *  - Within a period: open is fixed, high/low/close update on each tick.
 *  - When a new period starts: open = previous close.
 */
export class CandleBuilder {
    private readonly timeframe: number;
    private current: OHLCCandle | null = null;

    constructor(timeframe: number = 15) {
        this.timeframe = timeframe;
    }

    // ─── Bucket helpers ───────────────────────────────────────────────────────

    private toBucket(isoTime: string): number {
        const ts = Math.floor(new Date(isoTime).getTime() / 1000);
        return Math.floor(ts / this.timeframe) * this.timeframe;
    }

    // ─── Live tick processing ─────────────────────────────────────────────────

    /**
     * Process one tick. Returns the resulting candle and whether it's new.
     * Call `series.update(candle)` after each call — lightweight-charts v5
     * handles both updates to the current candle and new candle creation.
     */
    processTick(tick: PriceTick): { candle: OHLCCandle; isNew: boolean } {
        const price = Number(tick.price);
        const bucket = this.toBucket(tick.time);

        if (!this.current || this.current.time !== bucket) {
            const open = this.current ? this.current.close : price;
            this.current = { time: bucket, open, high: price, low: price, close: price };
            return { candle: { ...this.current }, isNew: true };
        }

        this.current.high  = Math.max(this.current.high, price);
        this.current.low   = Math.min(this.current.low, price);
        this.current.close = price;
        return { candle: { ...this.current }, isNew: false };
    }

    getCurrentCandle(): OHLCCandle | null {
        return this.current ? { ...this.current } : null;
    }

    // ─── Historical load ──────────────────────────────────────────────────────

    /**
     * Build candle history from a sorted (ascending) array of raw ticks.
     * Also initialises `this.current` so live ticks continue correctly.
     * Returns the full candle array including the partial current candle.
     */
    loadHistoricalTicks(ticks: PriceTick[]): OHLCCandle[] {
        this.current = null;

        const map = new Map<number, OHLCCandle>();

        for (const tick of ticks) {
            const price  = Number(tick.price);
            const bucket = this.toBucket(tick.time);

            const existing = map.get(bucket);
            if (!existing) {
                const mapVals = [...map.values()];
                const prev    = mapVals.length > 0 ? mapVals[mapVals.length - 1] : null;
                map.set(bucket, {
                    time:  bucket,
                    open:  prev ? prev.close : price,
                    high:  price,
                    low:   price,
                    close: price,
                });
            } else {
                existing.high  = Math.max(existing.high, price);
                existing.low   = Math.min(existing.low, price);
                existing.close = price;
            }
        }

        const sorted = [...map.values()].sort((a, b) => a.time - b.time);

        if (sorted.length > 0) {
            // The last candle is still "open" — set as current for live updates
            this.current = { ...sorted[sorted.length - 1] };
        }

        return sorted;
    }
}
