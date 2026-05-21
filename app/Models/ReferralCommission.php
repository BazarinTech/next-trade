<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralCommission extends Model
{
    protected $fillable = [
        'referrer_id',
        'referred_id',
        'deposit_id',
        'transaction_id',
        'deposit_amount_usd',
        'rate',
        'commission_amount_usd',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'deposit_amount_usd'    => 'float',
            'rate'                  => 'float',
            'commission_amount_usd' => 'float',
        ];
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_id');
    }

    public function deposit(): BelongsTo
    {
        return $this->belongsTo(PaymentDeposit::class, 'deposit_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
