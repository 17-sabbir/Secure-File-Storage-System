@extends('Layouts.app')
@section('title', 'Verify Login')

@section('content')
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-logo">
            <img src="{{ asset('cryptologo.png') }}" alt="Secure File Storage" style="max-width: 100px; margin-bottom: 5px;">
            <h1>Two-Factor Verification</h1>
            <p>Enter the 6-digit code sent to {{ $email }}</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger"><i class="fas fa-triangle-exclamation"></i> {{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('2fa.verify.submit') }}">
            @csrf
            <div class="form-group">
                <label><i class="fas fa-key"></i> Verification Code</label>
                <input
                    type="text"
                    name="code"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="6"
                    placeholder="123456"
                    style="letter-spacing:6px; text-align:center; font-size:1.4rem; font-weight:700;"
                    autofocus
                    required
                >
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;">
                <i class="fas fa-shield-halved"></i> Verify &amp; Continue
            </button>
        </form>

        <form method="POST" action="{{ route('2fa.resend') }}" class="mt-24">
            @csrf
            <p class="text-center text-muted">
                Didn't get a code?
                <button type="submit" style="background:none; border:none; color:var(--accent); cursor:pointer; font-weight:600; padding:0;">
                    Resend code
                </button>
            </p>
        </form>

        <p class="text-center text-muted mt-24" style="font-size:0.75rem;">
            <i class="fas fa-circle-info"></i>
            In local development the code is delivered to <code>storage/logs/laravel.log</code> since <code>MAIL_MAILER=log</code>. Configure SMTP in <code>.env</code> to send real emails.
        </p>
    </div>
</div>
@endsection
