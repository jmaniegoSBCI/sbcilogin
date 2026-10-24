@extends('layouts.app')

@section('title', 'Student log in')

@section('content')
    <section class="auth-shell">
        <div class="auth-aside">
            <span class="eyebrow">WELCOME BACK</span>
            <h1>Your next step starts with a sign in.</h1>
            <p>Log in with your email address and password. New to the portal? Create an account first.</p>
            <div class="aside-badge"><span class="note-dot"></span> SBCI student access</div>
        </div>
        <div class="auth-card">
            <span class="eyebrow">STUDENT PORTAL</span>
            <h2>Log in</h2>
            <p class="form-intro">Enter the email and password you used to create your account.</p>
            @if ($errors->any())
                <div class="form-errors" role="alert"><p>Please check the following:</p><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <form action="{{ route('login.store') }}" method="POST" class="form-stack">
                @csrf
                <label for="email">Email address</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
                <label class="check-row"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
                <button class="button button-primary button-full" type="submit">Log in to your account</button>
            </form>
            <p class="auth-switch">Don't have an account? <a href="{{ route('register') }}">Sign up</a></p>
        </div>
    </section>
@endsection
