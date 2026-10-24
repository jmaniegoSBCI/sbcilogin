<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SBCI Student Portal') · SBCI</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header class="site-header">
        <a class="brand" href="{{ route('home') }}" aria-label="SBCI Student Portal home">
            <span class="brand-mark">S</span>
            <span><strong>SBCI</strong><small>STUDENT PORTAL</small></span>
        </a>
        <nav class="site-nav" aria-label="Main navigation">
            @auth
                @if (auth()->user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}">Admin dashboard</a>
                @else
                    <a href="{{ route('continue') }}">My account</a>
                @endif
                <form action="{{ route('logout') }}" method="POST" class="inline-form">
                    @csrf
                    <button class="nav-button" type="submit">Log out</button>
                </form>
            @else
                <a href="{{ route('login') }}">Student log in</a>
                <a class="nav-cta" href="{{ route('register') }}">Create account</a>
            @endauth
        </nav>
    </header>

    <main>
        @if (session('status'))
            <div class="flash-message" role="status">{{ session('status') }}</div>
        @endif
        @yield('content')
    </main>

    <footer class="site-footer">
        <span>SBCI Student Portal</span>
        <a href="{{ route('admin.login') }}">Administrator access</a>
    </footer>
</body>
</html>
