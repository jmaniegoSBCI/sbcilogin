<?php

namespace App\Services;

use App\Models\ActiveSession;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class LoginActivityRecorder
{
    public function recordLogin(Request $request, User $user): void
    {
        $timestamp = now();
        $snapshot = [
            'user_email' => $user->email,
            'user_name' => $user->name ?: $user->email,
            'user_role' => $user->role,
        ];

        ActiveSession::query()->updateOrCreate(
            ['session_id' => $request->session()->getId()],
            [
                'user_id' => $user->id,
                ...$snapshot,
                'logged_in_at' => $timestamp,
                'last_activity_at' => $timestamp,
                'ended_at' => null,
            ],
        );

        ActivityLog::create([
            'user_id' => $user->id,
            ...$snapshot,
            'event' => ActivityLog::EVENT_LOGIN,
            'occurred_at' => $timestamp,
            'ip_address' => $request->ip(),
        ]);
    }

    public function recordLogout(Request $request, User $user): void
    {
        $timestamp = now();
        ActiveSession::query()
            ->where('session_id', $request->session()->getId())
            ->whereNull('ended_at')
            ->update(['ended_at' => $timestamp, 'last_activity_at' => $timestamp]);

        ActivityLog::create([
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_name' => $user->name ?: $user->email,
            'user_role' => $user->role,
            'event' => ActivityLog::EVENT_LOGOUT,
            'occurred_at' => $timestamp,
            'ip_address' => $request->ip(),
        ]);
    }
}
