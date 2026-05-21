<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferralCommission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminReferralController extends Controller
{
    public function index(Request $request): View
    {
        $query = ReferralCommission::with(['referrer:id,name,email', 'referred:id,name,email', 'deposit'])
            ->latest();

        if ($request->filled('referrer')) {
            $query->whereHas('referrer', fn ($q) =>
                $q->where('email', 'like', '%' . $request->referrer . '%')
                  ->orWhere('name',  'like', '%' . $request->referrer . '%')
            );
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $commissions = $query->paginate(25)->withQueryString();

        $totalPaid       = ReferralCommission::where('status', 'paid')->sum('commission_amount_usd');
        $totalFailed     = ReferralCommission::where('status', 'failed')->count();
        $totalReferrers  = ReferralCommission::where('status', 'paid')->distinct('referrer_id')->count('referrer_id');
        $totalCommissions= ReferralCommission::where('status', 'paid')->count();

        $topReferrers = User::withCount(['referrals as total_referrals'])
            ->withSum(['referralCommissions as total_earned' => fn ($q) =>
                $q->where('status', 'paid')
            ], 'commission_amount_usd')
            ->having('total_referrals', '>', 0)
            ->orderByDesc('total_earned')
            ->limit(10)
            ->get();

        return view('admin.referrals.index', compact(
            'commissions', 'totalPaid', 'totalFailed',
            'totalReferrers', 'totalCommissions', 'topReferrers'
        ));
    }
}
