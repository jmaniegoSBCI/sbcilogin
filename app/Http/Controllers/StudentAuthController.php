<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\LoginActivityRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StudentAuthController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function register(): View
    {
        return view('auth.register');
    }

    public function createAccount(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'email' => mb_strtolower($validated['email']),
            'password' => $validated['password'],
            'role' => User::ROLE_STUDENT,
        ]);

        return redirect()->route('login')->with('status', 'Your account is ready. Log in to complete your student profile.');
    }

    public function store(Request $request, LoginActivityRecorder $activity): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $key = mb_strtolower($validated['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Too many login attempts. Please try again in '.RateLimiter::availableIn($key).' seconds.',
            ]);
        }

        if (! Auth::attempt([
            'email' => mb_strtolower($validated['email']),
            'password' => $validated['password'],
            'archived_at' => null,
            'role' => User::ROLE_STUDENT,
        ], $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'email' => 'These credentials do not match an active student account.',
            ]);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        $activity->recordLogin($request, Auth::user());

        return redirect()->intended(route('continue'));
    }

    public function continue(): RedirectResponse
    {
        $user = Auth::user();

        return $user->profileIsComplete()
            ? redirect()->route('student.dashboard')
            : redirect()->route('profile.setup');
    }

    public function destroy(Request $request, LoginActivityRecorder $activity): RedirectResponse
    {
        $activity->recordLogout($request, Auth::user());
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'You have been logged out.');
    }
}
