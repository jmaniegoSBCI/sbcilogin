<?php

namespace App\Http\Controllers;

use App\Models\ActiveSession;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today();
        $chartDays = collect(range(6, 0))->map(fn (int $daysAgo): Carbon => $today->copy()->subDays($daysAgo));
        $chartStart = $today->copy()->subDays(6)->startOfDay();
        $dailyEvents = ActivityLog::query()
            ->where('occurred_at', '>=', $chartStart)
            ->get(['event', 'occurred_at'])
            ->groupBy(fn (ActivityLog $log): string => $log->occurred_at->toDateString());

        $chart = $chartDays->map(function (Carbon $day) use ($dailyEvents): array {
            $events = $dailyEvents->get($day->toDateString(), collect());

            return [
                'label' => $day->format('D'),
                'date' => $day->format('M j'),
                'login' => $events->where('event', ActivityLog::EVENT_LOGIN)->count(),
                'logout' => $events->where('event', ActivityLog::EVENT_LOGOUT)->count(),
            ];
        });

        $students = User::query()->where('role', User::ROLE_STUDENT);
        $programCounts = (clone $students)
            ->whereNull('archived_at')
            ->whereNotNull('program')
            ->get(['program'])
            ->countBy('program');
        $yearCounts = (clone $students)
            ->whereNull('archived_at')
            ->whereNotNull('year_level')
            ->get(['year_level'])
            ->countBy('year_level');

        $activeUsers = ActiveSession::query()
            ->whereNull('ended_at')
            ->where('last_activity_at', '>=', now()->subMinutes(15))
            ->whereHas('user', fn ($query) => $query
                ->whereNull('archived_at'))
            ->distinct('user_id')
            ->count('user_id');

        return view('admin.dashboard', [
            'totalAccounts' => User::query()->count(),
            'archivedAccounts' => User::query()->whereNotNull('archived_at')->count(),
            'activeUsers' => $activeUsers,
            'programCounts' => $programCounts,
            'yearCounts' => $yearCounts,
            'chart' => $chart,
            'activities' => ActivityLog::query()
                ->latest('occurred_at')
                ->paginate(25),
            'accounts' => User::query()
                ->where('role', User::ROLE_STUDENT)
                ->latest()
                ->paginate(25, ['*'], 'accounts_page'),
        ]);
    }

    public function archive(User $user): RedirectResponse
    {
        abort_unless($user->role === User::ROLE_STUDENT, 404);

        if ($user->archived_at === null) {
            $user->archived_at = now();
            $user->save();
        }

        return back()->with('status', 'Student account archived.');
    }

    public function restore(User $user): RedirectResponse
    {
        abort_unless($user->role === User::ROLE_STUDENT, 404);
        $user->archived_at = null;
        $user->save();

        return back()->with('status', 'Student account restored.');
    }
}
