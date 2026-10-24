<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\LoginActivityRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminLoginController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->role === User::ROLE_ADMIN ? 'admin.dashboard' : 'continue');
        }

        return view('auth.admin-login');
    }

    public function store(Request $request, LoginActivityRecorder $activity): RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->role === User::ROLE_ADMIN ? 'admin.dashboard' : 'continue');
        }

        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $email = mb_strtolower($validated['email']);
        $key = 'admin|'.$email.'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Too many login attempts. Please try again in '.RateLimiter::availableIn($key).' seconds.',
            ]);
        }

        $admin = User::query()
            ->where('email', $email)
            ->where('role', User::ROLE_ADMIN)
            ->whereNull('archived_at')
            ->first();

        if (! $admin || ! Hash::check($validated['password'], $admin->password)) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'email' => 'These credentials do not match an active administrator account.',
            ]);
        }

        RateLimiter::clear($key);
        Auth::login($admin, $request->boolean('remember'));
        $request->session()->regenerate();
        $activity->recordLogin($request, $admin);

        return redirect()->intended(route('admin.dashboard'));
    }
}
