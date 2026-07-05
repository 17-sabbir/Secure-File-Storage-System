<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $strength = app(PasswordStrengthController::class)->evaluate($request->password);
        if (($strength['score'] ?? 0) < 55) {
            return back()
                ->withErrors(['password' => 'Password is too weak. '.implode(' ', $strength['suggestions'] ?? [])])
                ->withInput();
        }

        User::create([
            'name' => $this->deriveName($request->email),
            'email' => $request->email,
            'password' => User::sha512($request->password), // SHA-512 hash
        ]);

        return redirect()->route('login')->with('success', 'Registration successful! Please login.');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)
            ->where('password', User::sha512($request->password))
            ->first();

        if (! $user) {
            return back()->withErrors(['email' => 'Email or password is incorrect.'])->withInput();
        }

        // ── Two-Factor Authentication ───────────────────────────────────────
        if ($user->two_factor_enabled) {
            $code = (string) random_int(100000, 999999);

            $user->forceFill([
                'two_factor_code' => hash('sha256', $code),
                'two_factor_expires_at' => now()->addMinutes(2),
            ])->save();

            $this->sendOtpEmail($user, $code);

            // Not fully authenticated yet — stash a pending user id only.
            Session::put('2fa_user_id', $user->id);
            Session::put('2fa_attempts', 0);
            Session::put('2fa_last_sent_at', now()->toIso8601String());

            return redirect()->route('2fa.verify');
        }

        $this->completeLogin($user);

        return redirect()->route('dashboard');
    }

    public function showVerify()
    {
        if (! Session::has('2fa_user_id')) {
            return redirect()->route('login');
        }

        $user = User::find(Session::get('2fa_user_id'));

        return view('auth.verify-2fa', ['email' => $user?->email]);
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        $userId = Session::get('2fa_user_id');
        $user = $userId ? User::find($userId) : null;

        if (! $user) {
            return redirect()->route('login')->withErrors(['auth' => 'Session expired. Please login again.']);
        }

        $attempts = (int) Session::get('2fa_attempts', 0);
        if ($attempts >= 2) {
            Session::forget(['2fa_user_id', '2fa_attempts', '2fa_last_sent_at']);

            return redirect()->route('login')->withErrors(['auth' => 'Too many invalid verification attempts. Please login again.']);
        }

        if (! $user->two_factor_expires_at || now()->greaterThan($user->two_factor_expires_at)) {
            Session::forget(['2fa_user_id', '2fa_attempts', '2fa_last_sent_at']);

            return back()->withErrors(['code' => 'This code has expired. Please login again to receive a new one.']);
        }

        if (! hash_equals($user->two_factor_code ?? '', hash('sha256', $request->code))) {
            Session::put('2fa_attempts', $attempts + 1);

            return back()->withErrors(['code' => 'Invalid verification code.']);
        }

        // Code is valid — clear it so it can't be reused.
        $user->forceFill([
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
        ])->save();

        Session::forget(['2fa_user_id', '2fa_attempts', '2fa_last_sent_at']);
        $this->completeLogin($user);

        return redirect()->route('dashboard')->with('success', 'Login verified successfully.');
    }

    public function resend(Request $request)
    {
        $userId = Session::get('2fa_user_id');
        $user = $userId ? User::find($userId) : null;

        if (! $user) {
            return redirect()->route('login');
        }

        $lastSent = Session::get('2fa_last_sent_at');
        if ($lastSent && Carbon::parse($lastSent)->addMinutes(720)->isFuture()) {
            return back()->withErrors(['code' => 'Please wait at least 2 minutes before requesting a new code.']);
        }

        $code = (string) random_int(100000, 999999);

        $user->forceFill([
            'two_factor_code' => hash('sha256', $code),
            'two_factor_expires_at' => now()->addMinutes(2),
        ])->save();

        $this->sendOtpEmail($user, $code);
        Session::put('2fa_attempts', 0);
        Session::put('2fa_last_sent_at', now()->toIso8601String());

        return back()->with('success', 'A new verification code has been sent to your email.');
    }

    public function logout()
    {
        Session::flush();

        return redirect()->route('login')->with('success', 'Logged out successfully.');
    }

    private function completeLogin(User $user): void
    {
        Session::put('user_id', $user->id);
        Session::put('user_email', $user->email);
    }

    private function sendOtpEmail(User $user, string $code): void
    {
        $subject = 'Your verification code';
        $text = "Your Secure File Storage System verification code is: {$code}\n\nThis code expires in 2 minutes. If you didn't request this, you can ignore this email.";
        $html = "<h2>Your verification code</h2><p>Your Secure File Storage System verification code is: <strong>{$code}</strong></p><p>This code expires in 2 minutes.</p><p>If you didn't request this, you can ignore this email.</p>";

        try {
            $resendKey = (string) config('services.resend.key');
            $resendFrom = (string) config('services.resend.from');

            if ($resendKey !== '' && $resendFrom !== '') {
                $response = Http::withToken($resendKey)
                    ->acceptJson()
                    ->post('https://api.resend.com/emails', [
                        'from' => $resendFrom,
                        'to' => [$user->email],
                        'subject' => $subject,
                        'text' => $text,
                        'html' => $html,
                    ]);

                if ($response->successful()) {
                    return;
                }

                report(new \RuntimeException('Resend API failed: '.$response->status().' '.$response->body()));
            }

            // Fallback for local/dev environments when Resend is not configured.
            Mail::raw(
                $text,
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('Your verification code');
                }
            );
        } catch (\Throwable $e) {
            // Never block login flow because of a mail transport issue in dev environments.
            report($e);
        }
    }

    private function deriveName(string $email): string
    {
        $local = strstr($email, '@', true);
        if ($local === false || $local === '') {
            return 'User';
        }

        return ucfirst($local);
    }
}
