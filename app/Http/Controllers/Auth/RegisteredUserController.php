<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function __construct(private WalletService $walletService) {}

    public function create(Request $request): View
    {
        // Persist ref code so it survives the form POST
        if ($request->filled('ref')) {
            session(['referral_code' => strtoupper(trim($request->ref))]);
        }
        return view('auth.register', ['refCode' => session('referral_code')]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username', 'alpha_dash'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'country'  => ['nullable', 'string', 'size:2'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Resolve referrer from session or submitted hidden field
        $refCode  = strtoupper(trim($request->input('ref_code', session('referral_code', ''))));
        $referrer = $refCode ? User::where('referral_code', $refCode)->first() : null;

        $user = User::create([
            'name'        => $request->name,
            'username'    => $request->username,
            'email'       => $request->email,
            'phone'       => $request->phone,
            'country'     => $request->country,
            'password'    => Hash::make($request->password),
            'referred_by' => $referrer?->id,
        ]);

        $this->walletService->createDefaultWallets($user);

        session()->forget('referral_code');

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
