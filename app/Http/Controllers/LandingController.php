<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        file_put_contents(storage_path('logs/nt_debug.txt'), "LandingController::index auth=" . (auth()->check() ? 'true' : 'false') . "\n", FILE_APPEND);

        if (auth()->check()) {
            return redirect()->route('trade.index');
        }

        return view('landing');
    }
}
