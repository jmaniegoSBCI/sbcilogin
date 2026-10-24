<?php

namespace App\Http\Middleware;

use App\Models\ActiveSession;
use App\Models\User;
use App\Services\LoginActivityRecorder;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TrackAuthenticatedActivity
{
    public function __construct(private readonly LoginActivityRecorder $activity) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->role === User::ROLE_STUDENT && $user->archived_at !== null) {
            $this->activity->recordLogout($request, $user);
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'This student account is archived. Please contact an administrator.',
            ]);
        }

        $response = $next($request);

        if ($request->user()) {
            ActiveSession::query()
                ->where('session_id', $request->session()->getId())
                ->whereNull('ended_at')
                ->update(['last_activity_at' => now()]);
        }

        return $response;
    }
}
