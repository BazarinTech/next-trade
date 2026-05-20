<div class="rounded-2xl border overflow-hidden bg-gray-900/60 border-gray-800/60">

    <div class="flex border-b border-gray-800/60">
        <button @click="activeTab = 'active'"
                class="flex items-center gap-2 px-5 py-3 text-xs font-semibold border-b-2 transition-colors"
                :class="activeTab === 'active'
                    ? 'border-cyan-500 text-cyan-400'
                    : 'border-transparent text-gray-500 hover:text-white'">
            Active
            <span x-show="activeTrades.length > 0"
                  class="px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-cyan-500/15 text-cyan-400"
                  x-text="activeTrades.length"></span>
        </button>
        <button @click="activeTab = 'recent'"
                class="px-5 py-3 text-xs font-semibold border-b-2 transition-colors"
                :class="activeTab === 'recent'
                    ? 'border-cyan-500 text-cyan-400'
                    : 'border-transparent text-gray-500 hover:text-white'">
            Recent
        </button>
    </div>

    {{-- Active --}}
    <div x-show="activeTab === 'active'" class="overflow-x-auto">
        <template x-if="activeTrades.length === 0">
            <div class="py-8 flex flex-col items-center gap-2">
                <svg class="w-7 h-7 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-gray-500 font-medium">No active trades</p>
                <p class="text-xs text-gray-600">Place a trade using the controls above</p>
            </div>
        </template>
        <template x-if="activeTrades.length > 0">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-800/60">
                        @foreach(['Pair','Dir.','Stake','Entry','Expires','Mode'] as $col)
                        <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <template x-for="trade in activeTrades" :key="trade.id">
                        <tr class="border-b border-gray-800/40 last:border-0 hover:bg-gray-800/30 transition-colors">
                            <td class="px-4 py-2.5">
                                <span class="text-xs font-semibold text-white" x-text="trade.asset.symbol"></span>
                            </td>
                            <td class="px-4 py-2.5">
                                <span class="text-xs font-bold"
                                      :class="trade.direction === 'buy' ? 'text-emerald-400' : 'text-red-400'"
                                      x-text="trade.direction.toUpperCase()"></span>
                            </td>
                            <td class="px-4 py-2.5">
                                <span class="text-xs font-mono text-gray-300" x-text="'$' + trade.stake_amount.toFixed(2)"></span>
                            </td>
                            <td class="px-4 py-2.5">
                                <span class="text-xs font-mono text-gray-500" x-text="fmtPrice(trade.entry_price, trade.asset)"></span>
                            </td>
                            <td class="px-4 py-2.5">
                                <span class="text-xs font-mono font-semibold"
                                      :class="trade.time_remaining <= 10 ? 'text-red-400 animate-pulse' : 'text-cyan-400'"
                                      x-text="trade.time_remaining > 0 ? trade.time_remaining + 's' : 'Settling…'"></span>
                            </td>
                            <td class="px-4 py-2.5">
                                <span class="text-[10px] px-2 py-0.5 rounded-full font-medium"
                                      :class="trade.wallet_type === 'demo' ? 'bg-amber-500/10 text-amber-400' : 'bg-emerald-500/10 text-emerald-400'"
                                      x-text="trade.wallet_type"></span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </template>
    </div>

    {{-- Recent --}}
    <div x-show="activeTab === 'recent'" class="overflow-x-auto">
        <template x-if="recentTrades.length === 0">
            <div class="py-8 flex flex-col items-center gap-2">
                <svg class="w-7 h-7 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-sm text-gray-500 font-medium">No closed trades yet</p>
            </div>
        </template>
        <template x-if="recentTrades.length > 0">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-800/60">
                        @foreach(['Pair','Dir.','Stake','P&L','Result','Closed'] as $col)
                        <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <template x-for="trade in recentTrades" :key="trade.id">
                        <tr class="border-b border-gray-800/40 last:border-0 hover:bg-gray-800/30 transition-colors">
                            <td class="px-4 py-2.5">
                                <span class="text-xs font-semibold text-white" x-text="trade.asset.symbol"></span>
                            </td>
                            <td class="px-4 py-2.5">
                                <span class="text-xs font-bold"
                                      :class="trade.direction === 'buy' ? 'text-emerald-400' : 'text-red-400'"
                                      x-text="trade.direction.toUpperCase()"></span>
                            </td>
                            <td class="px-4 py-2.5">
                                <span class="text-xs font-mono text-gray-500" x-text="'$' + trade.stake_amount.toFixed(2)"></span>
                            </td>
                            <td class="px-4 py-2.5">
                                <span class="text-xs font-mono font-semibold"
                                      :class="(trade.profit_loss || 0) >= 0 ? 'text-emerald-400' : 'text-red-400'"
                                      x-text="((trade.profit_loss || 0) >= 0 ? '+' : '') + '$' + Math.abs(trade.profit_loss || 0).toFixed(2)"></span>
                            </td>
                            <td class="px-4 py-2.5">
                                <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase"
                                      :class="{
                                          'bg-emerald-500/10 text-emerald-400': trade.status === 'won',
                                          'bg-red-500/10 text-red-400': trade.status === 'lost',
                                          'bg-amber-500/10 text-amber-400': trade.status === 'draw',
                                          'bg-gray-500/10 text-gray-400': trade.status === 'cancelled',
                                      }"
                                      x-text="trade.status"></span>
                            </td>
                            <td class="px-4 py-2.5">
                                <span class="text-[10px] text-gray-500"
                                      x-text="trade.closed_at ? new Date(trade.closed_at).toLocaleTimeString() : '—'"></span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </template>
    </div>

</div>
