<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\BotInvestment;
use App\Models\PaymentDeposit;
use App\Models\Trade;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminExportController extends Controller
{
    public function users(): StreamedResponse
    {
        return $this->csv('users_' . now()->format('Ymd'), function () {
            echo "ID,Name,Email,Phone,Country,Admin,Banned,Joined\n";
            User::orderBy('id')->chunk(500, function ($rows) {
                foreach ($rows as $u) {
                    echo implode(',', [
                        $u->id,
                        $this->esc($u->name),
                        $this->esc($u->email),
                        $this->esc($u->phone ?? ''),
                        $this->esc($u->country ?? ''),
                        $u->is_admin  ? 'Yes' : 'No',
                        $u->is_banned ? 'Yes' : 'No',
                        $u->created_at->toDateTimeString(),
                    ]) . "\n";
                }
            });
        });
    }

    public function deposits(): StreamedResponse
    {
        return $this->csv('deposits_' . now()->format('Ymd'), function () {
            echo "ID,User,Email,Method,Status,KES Amount,USD Amount,TXID,Submitted,Credited\n";
            PaymentDeposit::with('user')->orderBy('id')->chunk(500, function ($rows) {
                foreach ($rows as $d) {
                    echo implode(',', [
                        $d->id,
                        $this->esc($d->user?->name ?? ''),
                        $this->esc($d->user?->email ?? ''),
                        $this->esc($d->method),
                        $this->esc($d->status),
                        number_format((float)$d->local_amount, 2),
                        number_format((float)$d->usd_amount, 2),
                        $this->esc($d->txid ?? ''),
                        $d->created_at->toDateTimeString(),
                        $d->credited_at ? $d->credited_at->toDateTimeString() : '',
                    ]) . "\n";
                }
            });
        });
    }

    public function withdrawals(): StreamedResponse
    {
        return $this->csv('withdrawals_' . now()->format('Ymd'), function () {
            echo "ID,User,Email,Method,Status,USD Amount,Crypto Address,Phone,Requested,Completed\n";
            Withdrawal::with('user')->orderBy('id')->chunk(500, function ($rows) {
                foreach ($rows as $w) {
                    echo implode(',', [
                        $w->id,
                        $this->esc($w->user?->name ?? ''),
                        $this->esc($w->user?->email ?? ''),
                        $this->esc($w->method),
                        $this->esc($w->status),
                        number_format((float)$w->usd_amount, 2),
                        $this->esc($w->crypto_address ?? ''),
                        $this->esc($w->phone ?? ''),
                        $w->created_at->toDateTimeString(),
                        $w->completed_at ? $w->completed_at->toDateTimeString() : '',
                    ]) . "\n";
                }
            });
        });
    }

    public function trades(): StreamedResponse
    {
        return $this->csv('trades_' . now()->format('Ymd'), function () {
            echo "ID,User,Asset,Direction,Stake,Profit/Loss,Status,Opened,Closed\n";
            Trade::with(['user', 'tradingAsset'])->orderBy('id')->chunk(500, function ($rows) {
                foreach ($rows as $t) {
                    echo implode(',', [
                        $t->id,
                        $this->esc($t->user?->name ?? ''),
                        $this->esc($t->tradingAsset?->symbol ?? ''),
                        $this->esc($t->direction),
                        number_format((float)$t->stake_amount, 2),
                        number_format((float)$t->profit_loss, 2),
                        $this->esc($t->status),
                        $t->created_at->toDateTimeString(),
                        $t->closed_at ? $t->closed_at->toDateTimeString() : '',
                    ]) . "\n";
                }
            });
        });
    }

    public function bots(): StreamedResponse
    {
        return $this->csv('bot_investments_' . now()->format('Ymd'), function () {
            echo "ID,User,Plan,Amount,Total Earned,Status,Started,Ended\n";
            BotInvestment::with(['user', 'botPlan'])->orderBy('id')->chunk(500, function ($rows) {
                foreach ($rows as $b) {
                    echo implode(',', [
                        $b->id,
                        $this->esc($b->user?->name ?? ''),
                        $this->esc($b->botPlan?->name ?? ''),
                        number_format((float)$b->amount, 2),
                        number_format((float)$b->total_earned, 2),
                        $this->esc($b->status),
                        $b->created_at->toDateTimeString(),
                        $b->ended_at ? $b->ended_at->toDateTimeString() : '',
                    ]) . "\n";
                }
            });
        });
    }

    public function transactions(): StreamedResponse
    {
        return $this->csv('transactions_' . now()->format('Ymd'), function () {
            echo "ID,User,Wallet Type,Type,Amount,Status,Description,Created\n";
            Transaction::with(['user', 'wallet'])->orderBy('id')->chunk(500, function ($rows) {
                foreach ($rows as $t) {
                    echo implode(',', [
                        $t->id,
                        $this->esc($t->user?->name ?? ''),
                        $this->esc($t->wallet?->type ?? ''),
                        $this->esc($t->type),
                        number_format((float)$t->amount, 2),
                        $this->esc($t->status),
                        $this->esc($t->description ?? ''),
                        $t->created_at->toDateTimeString(),
                    ]) . "\n";
                }
            });
        });
    }

    public function auditLogs(): StreamedResponse
    {
        return $this->csv('audit_logs_' . now()->format('Ymd'), function () {
            echo "ID,Admin,Action,Model,Model ID,When\n";
            AdminLog::with('admin')->orderBy('id')->chunk(500, function ($rows) {
                foreach ($rows as $l) {
                    echo implode(',', [
                        $l->id,
                        $this->esc($l->admin?->name ?? 'System'),
                        $this->esc($l->action),
                        $this->esc(class_basename($l->target_type ?? '')),
                        $l->target_id ?? '',
                        $l->created_at->toDateTimeString(),
                    ]) . "\n";
                }
            });
        });
    }

    private function csv(string $filename, callable $generator): StreamedResponse
    {
        return response()->stream($generator, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
            'X-Accel-Buffering'   => 'no',
        ]);
    }

    private function esc(string $value): string
    {
        if (str_contains($value, ',') || str_contains($value, '"') || str_contains($value, "\n")) {
            return '"' . str_replace('"', '""', $value) . '"';
        }
        return $value;
    }
}
