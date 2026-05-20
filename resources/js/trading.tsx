import React from 'react';
import ReactDOM from 'react-dom/client';
import { TradingApp } from './components/trading/TradingApp';
import type { Trade, TradeAsset } from './lib/trading/chart-types';

const root = document.getElementById('trading-app');
if (root) {
    const assets        = JSON.parse(root.dataset.assets        ?? '[]') as TradeAsset[];
    const activeTrades  = JSON.parse(root.dataset.activeTrades  ?? '[]') as Trade[];
    const recentTrades  = JSON.parse(root.dataset.recentTrades  ?? '[]') as Trade[];
    const walletBalance = parseFloat(root.dataset.walletBalance ?? '0');
    const walletMode    = root.dataset.walletMode               ?? 'demo';

    ReactDOM.createRoot(root).render(
        <TradingApp
            assets={assets}
            activeTrades={activeTrades}
            recentTrades={recentTrades}
            walletBalance={walletBalance}
            walletMode={walletMode}
        />,
    );
}
