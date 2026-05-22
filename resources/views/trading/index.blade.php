@extends('layouts.trading')

@section('title', 'Trade | Next Trade')
@section('overflow', 'hidden')

@section('content')

<div id="trading-app"
     style="height:100%; overflow:hidden;"
     data-assets='{!! json_encode(
         $assets->map(fn($a) => [
             'id'         => $a->id,
             'symbol'     => $a->symbol,
             'name'       => $a->name,
             'type'       => $a->type,
             'price'      => (float) $a->current_price,
             'base_price' => (float) $a->base_price,
             'change_pct' => (float) $a->price_change,
         ]),
         JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT
     ) !!}'
     data-active-trades='{!! json_encode(
         $activeTrades->map(fn($t) => [
             'id'             => $t->id,
             'asset'          => ['id' => $t->tradingAsset->id, 'symbol' => $t->tradingAsset->symbol, 'type' => $t->tradingAsset->type, 'name' => $t->tradingAsset->name],
             'direction'      => $t->direction,
             'stake_amount'   => (float) $t->stake_amount,
             'entry_price'    => (float) $t->entry_price,
             'expiry_seconds' => $t->expiry_seconds,
             'time_remaining' => $t->time_remaining,
             'wallet_type'    => $t->wallet_type,
             'opened_at'      => $t->opened_at->toISOString(),
             'expires_at'     => $t->expires_at->toISOString(),
             'status'         => $t->status,
         ]),
         JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT
     ) !!}'
     data-recent-trades='{!! json_encode(
         $recentTrades->map(fn($t) => [
             'id'           => $t->id,
             'asset'        => ['id' => $t->tradingAsset->id, 'symbol' => $t->tradingAsset->symbol, 'type' => $t->tradingAsset->type, 'name' => $t->tradingAsset->name],
             'direction'    => $t->direction,
             'stake_amount' => (float) $t->stake_amount,
             'entry_price'  => (float) $t->entry_price,
             'exit_price'   => $t->exit_price  ? (float) $t->exit_price  : null,
             'profit_loss'  => $t->profit_loss ? (float) $t->profit_loss : null,
             'payout'       => $t->payout      ? (float) $t->payout      : null,
             'status'       => $t->status,
             'wallet_type'  => $t->wallet_type,
             'closed_at'    => $t->closed_at?->toISOString(),
         ]),
         JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT
     ) !!}'
     data-wallet-balance="{{ $wallet ? (float) $wallet->available_balance : 0 }}"
     data-wallet-mode="{{ $walletMode }}"
>
    {{-- Loading skeleton until React hydrates --}}
    <div style="display:flex; flex-direction:column; align-items:center; gap:12px;">
        <div style="width:32px; height:32px; border:2px solid #06b6d4; border-top-color:transparent; border-radius:50; animation:spin 0.8s linear infinite;"></div>
        <p style="font-size:13px; color:#6b7280;">Loading trading terminal…</p>
    </div>
</div>

@endsection

@push('scripts')
@vite('resources/js/trading.tsx')
@endpush
