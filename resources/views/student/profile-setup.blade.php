@extends('layouts.app')

@section('title', 'Complete your student profile')

@section('content')
    <section class="form-page">
        <div class="page-heading">
            <span class="eyebrow">ONE-TIME SETUP</span>
            <h1>Complete your student profile</h1>
            <p>Before you continue, tell us a little about your studies.</p>
        </div>
        <div class="auth-card wide-card">
            <div class="setup-progress"><span></span><div><b>Profile details</b><small>Step 1 of 1</small></div></div>
            @if ($errors->any())
                <div class="form-errors" role="alert"><p>Please check the following:</p><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <form action="{{ route('profile.setup.update') }}" method="POST" class="form-stack">
                @csrf
                <div class="field-grid">
                    <div><label for="first_name">First name</label><input id="first_name" name="first_name" value="{{ old('first_name', auth()->user()->first_name) }}" autocomplete="given-name" required></div>
                    <div><label for="last_name">Last name</label><input id="last_name" name="last_name" value="{{ old('last_name', auth()->user()->last_name) }}" autocomplete="family-name" required></div>
                </div>
                <label for="program">Program</label>
                <select id="program" name="program" required>
                    <option value="">Select your program</option>
                    @foreach (['BSIT' => 'BS Information Technology', 'CRIM' => 'BS Criminology', 'BSCA' => 'BS Customs Administration', 'BSTM' => 'BS Tourism Management'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('program', auth()->user()->program) === $value)>{{ $value }} — {{ $label }}</option>
                    @endforeach
                </select>
                <label for="year_level">Year level</label>
                <select id="year_level" name="year_level" required>
                    <option value="">Select your year level</option>
                    @foreach (range(1, 6) as $year)
                        <option value="{{ $year }}" @selected((string) old('year_level', auth()->user()->year_level) === (string) $year)>{{ $year }}{{ ['st', 'nd', 'rd'][$year - 1] ?? 'th' }} year</option>
                    @endforeach
                </select>
                <button class="button button-primary button-full" type="submit">Save profile and continue</button>
            </form>
        </div>
    </section>
@endsection
