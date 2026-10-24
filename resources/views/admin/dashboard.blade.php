@extends('layouts.app')

@section('title', 'Admin dashboard')

@section('content')
    <section class="dashboard-page admin-page">
        <div class="page-heading dashboard-heading">
            <div><span class="eyebrow">SBCI ADMINISTRATION</span><h1>Dashboard</h1><p>Student accounts and portal activity in one place.</p></div>
            <span class="status-pill"><span class="note-dot"></span> Administrator access</span>
        </div>

        <div class="metric-grid">
            <article class="metric-card"><span class="metric-icon">◎</span><span class="metric-label">Currently logged-in users</span><strong>{{ $activeUsers }}</strong><small>Active in the last 15 minutes</small></article>
            <article class="metric-card"><span class="metric-icon">▤</span><span class="metric-label">Total accounts</span><strong>{{ $totalAccounts }}</strong><small>Student and administrator accounts</small></article>
            <article class="metric-card"><span class="metric-icon">▱</span><span class="metric-label">Archived accounts</span><strong>{{ $archivedAccounts }}</strong><small>Archived student accounts</small></article>
        </div>

        <div class="admin-grid">
            <section class="panel chart-panel">
                <div class="panel-heading"><div><span class="eyebrow">LAST 7 DAYS</span><h2>Sign-ins and sign-outs</h2></div><div class="chart-legend"><span><i class="legend-login"></i>Log in</span><span><i class="legend-logout"></i>Log out</span></div></div>
                <div class="chart">
                    @php($maxEvents = max(1, $chart->max(fn (array $day): int => max($day['login'], $day['logout']))))
                    @foreach ($chart as $day)
                        <div class="chart-day" title="{{ $day['date'] }}: {{ $day['login'] }} logins, {{ $day['logout'] }} logouts">
                            <div class="chart-bars">
                                <span class="chart-bar chart-bar-login" style="height: {{ max(4, (int) round($day['login'] / $maxEvents * 100)) }}%" aria-label="{{ $day['login'] }} logins"></span>
                                <span class="chart-bar chart-bar-logout" style="height: {{ max(4, (int) round($day['logout'] / $maxEvents * 100)) }}%" aria-label="{{ $day['logout'] }} logouts"></span>
                            </div>
                            <span class="chart-day-label">{{ $day['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </section>
            <section class="panel breakdown-panel">
                <div class="panel-heading"><div><span class="eyebrow">ACTIVE STUDENTS</span><h2>By program</h2></div></div>
                <div class="breakdown-list">
                    @foreach (['BSIT', 'CRIM', 'BSCA', 'BSTM'] as $program)
                        <div class="breakdown-row"><span>{{ $program }}</span><div class="mini-track"><i style="width: {{ $programCounts->get($program, 0) > 0 ? min(100, $programCounts->get($program, 0) * 10) : 0 }}%"></i></div><b>{{ $programCounts->get($program, 0) }}</b></div>
                    @endforeach
                </div>
                <div class="level-summary"><span>Year level</span>@foreach (range(1, 6) as $year)<b>{{ $year }}<small>{{ $yearCounts->get($year, 0) }}</small></b>@endforeach</div>
            </section>
        </div>

        <section class="panel table-panel">
            <div class="panel-heading"><div><span class="eyebrow">STUDENT ACTIVITY</span><h2>Recent sign-ins and sign-outs</h2></div></div>
            <div class="table-wrap">
                <table><thead><tr><th>Account</th><th>Email</th><th>Role</th><th>Activity</th><th>Date and time</th><th>IP address</th></tr></thead>
                    <tbody>
                    @forelse ($activities as $activity)
                        <tr><td>{{ $activity->user_name }}</td><td>{{ $activity->user_email }}</td><td>{{ ucfirst($activity->user_role) }}</td><td><span class="event-pill event-{{ $activity->event }}">{{ ucfirst($activity->event) }}</span></td><td>{{ $activity->occurred_at->format('M j, Y · g:i A') }}</td><td>{{ $activity->ip_address ?? '—' }}</td></tr>
                    @empty
                        <tr><td class="empty-state" colspan="6">Account sign-in activity will appear here.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $activities->links() }}
        </section>

        <section class="panel table-panel">
            <div class="panel-heading"><div><span class="eyebrow">ACCOUNT MANAGEMENT</span><h2>Student accounts</h2></div><span class="table-caption">Most recent 25 accounts</span></div>
            <div class="table-wrap">
                <table><thead><tr><th>Student</th><th>Email</th><th>Program</th><th>Year</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                    @forelse ($accounts as $account)
                        <tr><td>{{ $account->name ?: 'Profile incomplete' }}</td><td>{{ $account->email }}</td><td>{{ $account->program ?? '—' }}</td><td>{{ $account->year_level ?? '—' }}</td>
                            <td><span class="event-pill {{ $account->archived_at ? 'event-logout' : 'event-login' }}">{{ $account->archived_at ? 'Archived' : 'Active' }}</span></td>
                            <td>
                                @if ($account->archived_at)
                                    <form method="POST" action="{{ route('admin.accounts.restore', $account) }}">@csrf @method('PATCH')<button class="text-button" type="submit">Restore</button></form>
                                @else
                                    <form method="POST" action="{{ route('admin.accounts.archive', $account) }}">@csrf @method('PATCH')<button class="text-button text-danger" type="submit">Archive</button></form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td class="empty-state" colspan="6">No student accounts yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $accounts->links() }}
        </section>
    </section>
@endsection
