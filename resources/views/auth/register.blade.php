@extends('Layouts.app')
@section('title', 'Register')

@section('content')
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-logo">
            <img src="{{ asset('cryptologo.png') }}" alt="Secure File Storage" style="max-width: 100px; margin-bottom: 5px;">
            <h1>Secure File Storage System</h1>
            <p>Create a new account</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger"><i class="fas fa-triangle-exclamation"></i> {{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="form-group">
                <label><i class="fas fa-envelope"></i>Enter Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="your@email.com" required>
            </div>
            <div class="form-group">
                <label><i class="fas fa-lock"></i> Password</label>
                <div style="position:relative;">
                    <input type="password" name="password" id="passwordInput" placeholder="Enter a password" required autocomplete="new-password" style="padding-right:48px;">
                    <button type="button" id="toggleRegisterPassword" aria-label="Show password" aria-pressed="false" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); border:none; background:transparent; color:var(--muted); cursor:pointer; padding:6px;">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>

                <div id="strengthMeterWrap" style="margin-top:10px; display:none;">
                    <div style="height:6px; border-radius:4px; background:var(--border); overflow:hidden;">
                        <div id="strengthBar" style="height:100%; width:0%; border-radius:4px; transition:width .2s ease, background .2s ease;"></div>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:6px;">
                        <span id="strengthLabel" style="font-size:0.78rem; font-weight:700;"></span>
                        <span id="strengthScore" class="text-muted" style="font-size:0.75rem;"></span>
                    </div>
                    <ul id="strengthSuggestions" style="margin-top:8px; padding-left:18px; font-size:0.78rem; color:var(--muted);"></ul>
                </div>
            </div>
            <div class="form-group">
                <label><i class="fas fa-check-double"></i> Confirm Password</label>
                <div style="position:relative;">
                    <input type="password" name="password_confirmation" id="passwordConfirmationInput" placeholder="Re-enter password" required autocomplete="new-password" style="padding-right:48px;">
                    <button type="button" id="toggleConfirmPassword" aria-label="Show confirm password" aria-pressed="false" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); border:none; background:transparent; color:var(--muted); cursor:pointer; padding:6px;">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;">
                <i class="fas fa-user-plus"></i> Register
            </button>
        </form>

        <script>
        (function () {
            const input       = document.getElementById('passwordInput');
            const confirmInput = document.getElementById('passwordConfirmationInput');
            const togglePassword = document.getElementById('toggleRegisterPassword');
            const toggleConfirm = document.getElementById('toggleConfirmPassword');
            const passwordIcon = togglePassword.querySelector('i');
            const confirmIcon = toggleConfirm.querySelector('i');
            const wrap        = document.getElementById('strengthMeterWrap');
            const bar         = document.getElementById('strengthBar');
            const label       = document.getElementById('strengthLabel');
            const scoreEl     = document.getElementById('strengthScore');
            const suggestions = document.getElementById('strengthSuggestions');

            const colors = { empty: '#94a3b8', weak: '#ef4444', fair: '#f59e0b', good: '#3b82f6', strong: '#22c55e' };

            let debounceTimer = null;

            togglePassword.addEventListener('click', () => {
                const isHidden = input.type === 'password';
                input.type = isHidden ? 'text' : 'password';
                togglePassword.setAttribute('aria-pressed', String(isHidden));
                togglePassword.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
                passwordIcon.className = isHidden ? 'fas fa-eye-slash' : 'fas fa-eye';
            });

            toggleConfirm.addEventListener('click', () => {
                const isHidden = confirmInput.type === 'password';
                confirmInput.type = isHidden ? 'text' : 'password';
                toggleConfirm.setAttribute('aria-pressed', String(isHidden));
                toggleConfirm.setAttribute('aria-label', isHidden ? 'Hide confirm password' : 'Show confirm password');
                confirmIcon.className = isHidden ? 'fas fa-eye-slash' : 'fas fa-eye';
            });

            input.addEventListener('input', () => {
                const value = input.value;

                if (!value) {
                    wrap.style.display = 'none';
                    return;
                }
                wrap.style.display = 'block';

                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => checkStrength(value), 150);
            });

            async function checkStrength(password) {
                try {
                    const res = await fetch("{{ route('password.strength') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ password }),
                    });
                    const data = await res.json();
                    render(data);
                } catch (e) {
                    // fail silently — the meter is a nice-to-have, not a blocker
                }
            }

            function render(data) {
                bar.style.width = data.score + '%';
                bar.style.background = colors[data.label] || colors.weak;
                label.textContent = data.label.charAt(0).toUpperCase() + data.label.slice(1);
                label.style.color = colors[data.label] || colors.weak;
                scoreEl.textContent = data.score + ' / 100';

                suggestions.innerHTML = '';
                (data.suggestions || []).forEach(s => {
                    const li = document.createElement('li');
                    li.textContent = s;
                    suggestions.appendChild(li);
                });
            }
        })();
        </script>

        <p class="text-center text-muted mt-24">
            Already have an account?
            <a href="{{ route('login') }}" style="color: var(--accent);">Login</a>
        </p>
    </div>
</div>
@endsection