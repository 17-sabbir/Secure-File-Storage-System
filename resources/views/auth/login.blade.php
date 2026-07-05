@extends('Layouts.app')
@section('title', 'Login')

@section('content')
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-logo">
              <img src="{{ asset('cryptologo.png') }}" alt="Secure File Storage" style="max-width: 100px; margin-bottom: 5px;">
            <h1>Secure File Storage System</h1>
            <p>Sign in to your account</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger"><i class="fas fa-triangle-exclamation"></i> {{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label><i class="fas fa-envelope"></i> Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="your@email.com" required>
            </div>
            <div class="form-group">
                <label><i class="fas fa-lock"></i> Password</label>
                <div style="position:relative;">
                    <input type="password" name="password" id="loginPasswordInput" placeholder="Enter your password" required style="padding-right:48px;">
                    <button type="button" id="toggleLoginPassword" aria-label="Show password" aria-pressed="false" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); border:none; background:transparent; color:var(--muted); cursor:pointer; padding:6px;">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;">
                <i class="fas fa-right-to-bracket"></i> Login
            </button>
        </form>

        <script>
        (function () {
            const passwordInput = document.getElementById('loginPasswordInput');
            const toggleButton = document.getElementById('toggleLoginPassword');
            const icon = toggleButton.querySelector('i');

            toggleButton.addEventListener('click', () => {
                const isHidden = passwordInput.type === 'password';
                passwordInput.type = isHidden ? 'text' : 'password';
                toggleButton.setAttribute('aria-pressed', String(isHidden));
                toggleButton.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
                icon.className = isHidden ? 'fas fa-eye-slash' : 'fas fa-eye';
            });
        })();
        </script>

        <p class="text-center text-muted mt-24">
            No account yet?
            <a href="{{ route('register') }}" style="color: var(--accent);">Register</a>
        </p>
    </div>
</div>
@endsection