@extends('layouts.app')

@section('title', 'Welcome')

@section('content')
    <section class="hero">
        <div class="hero-copy">
            <span class="eyebrow">YOUR CAMPUS, CONNECTED</span>
            <h1>A better way to stay connected to your <span>student journey.</span></h1>
            <p>Sign in to your SBCI student account, keep your details up to date, and access your student portal.</p>
            <div class="hero-actions">
                <a class="button button-primary" href="{{ route('login') }}">Log in to your account <span aria-hidden="true">→</span></a>
                <a class="button button-secondary" href="{{ route('register') }}">Create a student account</a>
            </div>
            <div class="hero-note"><span class="note-dot"></span> Secure access for SBCI students</div>
        </div>
        <div class="hero-art" aria-hidden="true">
            <div class="art-orbit orbit-one"></div>
            <div class="art-orbit orbit-two"></div>
            <div class="art-card">
                <div class="art-card-top"><span class="brand-mark">S</span><span class="art-status">STUDENT PORTAL</span></div>
                <div class="art-avatar">S</div>
                <div class="art-lines"><i></i><i></i></div>
                <div class="art-details"><span>YOUR NEXT CHAPTER</span><b>Starts here.</b></div>
            </div>
            <span class="art-spark spark-one">✦</span><span class="art-spark spark-two">✧</span>
        </div>
    </section>

    <section class="welcome-features">
        <article><span class="feature-number">01</span><h2>One student account</h2><p>Create your account once, then add your program and year level after your first login.</p></article>
        <article><span class="feature-number">02</span><h2>Made for your campus</h2><p>Keep your student profile together in a clear, simple place.</p></article>
        <article><span class="feature-number">03</span><h2>Secure and accountable</h2><p>Account access and sign-in activity are recorded for campus administration.</p></article>
    </section>
@endsection
