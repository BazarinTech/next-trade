// ─── Price ticks from the engine ─────────────────────────────────────────────

export interface PriceTick {
    price: number;
    time: string;   // ISO-8601 string from the PHP engine
    direction: 'up' | 'down' | 'flat';
}

// ─── OHLC candle (lightweight-charts UTCTimestamp = Unix seconds) ─────────────

export interface OHLCCandle {
    time: number;   // Unix seconds (UTCTimestamp)
    open: number;
    high: number;
    low: number;
    close: number;
}

// ─── Asset ───────────────────────────────────────────────────────────────────

export interface TradeAsset {
    id: number;
    symbol: string;
    name: string;
    type: 'crypto' | 'forex' | 'synthetic' | 'stock';
    price: number;
    base_price: number;
    change_pct: number;
}

// ─── Trade as returned by the PHP engine ─────────────────────────────────────

export interface Trade {
    id: number;
    asset: { id: number; symbol: string; type: string; name?: string };
    direction: 'buy' | 'sell';
    stake_amount: number;
    entry_price: number;
    expiry_seconds: number;
    time_remaining: number;
    wallet_type: string;
    opened_at: string;
    expires_at: string;
    status: 'open' | 'won' | 'lost' | 'draw' | 'cancelled';
    exit_price?: number | null;
    profit_loss?: number | null;
    payout?: number | null;
    closed_at?: string | null;
}

// ─── Engine adapter public interface ─────────────────────────────────────────

export type TickCallback = (tick: PriceTick) => void;
export type TradeCallback = (active: Trade[], completed: Trade[]) => void;

export type Timeframe = 5 | 15 | 60;

export interface IEngineAdapter {
    /** Set the currently viewed asset and start polling its ticks */
    setAsset(assetId: number): void;
    /** Fetch raw ticks for historical candle building */
    getHistoricalTicks(assetId: number): Promise<PriceTick[]>;
    /** Build candles from historical ticks */
    getHistoricalCandles(assetId: number, timeframe: Timeframe): Promise<OHLCCandle[]>;
    /** Place a trade through the PHP engine */
    placeTrade(params: PlaceTradeParams): Promise<{ trade: Trade; walletBalance: number }>;
    /** Subscribe to live price ticks for the active asset */
    subscribeToTicks(cb: TickCallback): () => void;
    /** Subscribe to trade state changes */
    subscribeToTrades(cb: TradeCallback): () => void;
    /** Get current active trades (sync) */
    getActiveTrades(): Trade[];
    /** Get completed trades (sync) */
    getCompletedTrades(): Trade[];
    /** Seed initial trade state from server-side PHP render */
    setInitialTrades(active: Trade[], completed: Trade[]): void;
    /** Start polling active trades from /trade/active */
    startTradePolling(): void;
    stopTradePolling(): void;
    destroy(): void;
}

export interface PlaceTradeParams {
    assetId: number;
    direction: 'buy' | 'sell';
    stake: number;
    expirySeconds: number;
}
