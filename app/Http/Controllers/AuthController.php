<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $accountKey = 'login:account:'.hash('sha256', Str::lower(trim($credentials['email'])).'|'.$request->ip());
        $ipKey = 'login:ip:'.hash('sha256', (string) $request->ip());
        $retryAfter = 0;

        foreach ([$accountKey => 5, $ipKey => 30] as $key => $limit) {
            if (RateLimiter::tooManyAttempts($key, $limit)) {
                $retryAfter = max($retryAfter, RateLimiter::availableIn($key));
            }
        }

        if ($retryAfter > 0) {
            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$retryAfter} seconds.",
            ])->status(429);
        }

        if (Auth::attempt($credentials)) {
            RateLimiter::clear($accountKey);
            $request->session()->regenerate();

            return redirect()->route('dashboard');
        }

        RateLimiter::hit($accountKey, 60);
        RateLimiter::hit($ipKey, 60);

        return back()->withErrors([
            'email' => 'Invalid login credentials.',
        ])->withInput($request->only('email'));
    }
}
