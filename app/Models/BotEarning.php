<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BotEarning extends Model
{
    protected $fillable = [
        'user_id', 'wallet_id', 'bot_investment_id', 'bot_plan_id',
        'amount', 'roi_percent', 'earning_date', 'status', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount'       => 'decimal:8',
            'roi_percent'  => 'decimal:4',
            'earning_date' => 'date',
            'metadata'     => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function investment(): BelongsTo
    {
        return $this->belongsTo(BotInvestment::class, 'bot_investment_id');
    }

    public function botPlan(): BelongsTo
    {
        return $this->belongsTo(BotPlan::class);
    }
}
