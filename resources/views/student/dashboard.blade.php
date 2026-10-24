@extends('layouts.app')

@section('title', 'Student dashboard')

@section('content')
    <section class="dashboard-page">
        <div class="page-heading dashboard-heading">
            <div><span class="eyebrow">STUDENT PORTAL</span><h1>Welcome, {{ auth()->user()->first_name }}.</h1><p>Your student account is ready. Here are your profile details.</p></div>
            <span class="status-pill"><span class="note-dot"></span> Account active</span>
        </div>
        <div class="profile-summary">
            <div class="profile-avatar">{{ mb_substr(auth()->user()->first_name, 0, 1) }}{{ mb_substr(auth()->user()->last_name, 0, 1) }}</div>
            <div><span class="eyebrow">STUDENT PROFILE</span><h2>{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</h2><p>{{ auth()->user()->email }}</p></div>
        </div>
        <div class="detail-grid">
            <article class="detail-card"><span>Program</span><strong>{{ auth()->user()->program }}</strong><small>Your academic program</small></article>
            <article class="detail-card"><span>Year level</span><strong>{{ auth()->user()->year_level }}{{ ['st', 'nd', 'rd'][auth()->user()->year_level - 1] ?? 'th' }} year</strong><small>Your current year level</small></article>
            <article class="detail-card"><span>Account email</span><strong>{{ auth()->user()->email }}</strong><small>Your login email</small></article>
        </div>
        <p class="privacy-note">Your sign-in and sign-out activity is recorded for campus administration.</p>
    </section>
@endsection
