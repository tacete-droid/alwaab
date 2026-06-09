<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return back()->withErrors(['email' => __('auth.failed')]);
        }

        $user = $request->user();

        if (! $user->is_active) {
            Auth::logout();
            return back()->withErrors(['email' => __('messages.account_inactive')]);
        }

        $user->update(['last_login_at' => now()]);
        $request->session()->regenerate();
        $request->session()->put('locale', $user->locale?->value ?? 'ar');
        app()->setLocale($user->locale?->value ?? 'ar');

        $intended = $user->hasRole('customer')
            ? route('portal.dashboard')
            : route('dashboard');

        return redirect()->intended($intended);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
