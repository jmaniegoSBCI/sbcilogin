@extends('layouts.app')

@section('title', 'Create student account')

@section('content')
    <section class="auth-shell">
        <div class="auth-aside">
            <span class="eyebrow">GET STARTED</span>
            <h1>Your student portal account begins here.</h1>
            <p>Register with an email address you can access. You will complete your student details on your first login.</p>
            <div class="aside-badge"><span class="note-dot"></span> Takes just a moment</div>
        </div>
        <div class="auth-card">
            <span class="eyebrow">STUDENT PORTAL</span>
            <h2>Create account</h2>
            <p class="form-intro">Use your email address and create a strong password.</p>
            @if ($errors->any())
                <div class="form-errors" role="alert"><p>Please check the following:</p><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <form action="{{ route('register.store') }}" method="POST" class="form-stack">
                @csrf
                <label for="email">Email address</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required>
                <span class="field-hint">Use at least 8 characters.</span>
                <label for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                <button class="button button-primary button-full" type="submit">Create my account</button>
            </form>
            <p class="auth-switch">Already registered? <a href="{{ route('login') }}">Log in</a></p>
        </div>
    </section>
@endsection
