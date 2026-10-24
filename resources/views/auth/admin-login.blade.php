@extends('layouts.app')

@section('title', 'Administrator log in')

@section('content')
    <section class="auth-shell admin-auth-shell">
        <div class="auth-aside">
            <span class="eyebrow">ADMINISTRATION</span>
            <h1>Campus activity at a glance.</h1>
            <p>Administrator access is separate from student sign in and is limited to authorized accounts.</p>
            <div class="aside-badge"><span class="note-dot"></span> Protected administrator area</div>
        </div>
        <div class="auth-card">
            <span class="eyebrow">SBCI ADMIN</span>
            <h2>Administrator log in</h2>
            <p class="form-intro">Use your administrator email address and password.</p>
            @if ($errors->any())
                <div class="form-errors" role="alert"><p>Please check the following:</p><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <form action="{{ route('admin.login.store') }}" method="POST" class="form-stack">
                @csrf
                <label for="email">Administrator email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
                <label class="check-row"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
                <button class="button button-primary button-full" type="submit">Open admin dashboard</button>
            </form>
            <p class="auth-switch"><a href="{{ route('login') }}">Return to student log in</a></p>
        </div>
    </section>
@endsection
