<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
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
            'email'    => 'required|email',
            'password' => 'required|min:4',
        ]);

        // Check if email already exists
        if (User::where('email', $request->email)->exists()) {
            return back()->withErrors(['email' => 'This email is already registered.'])->withInput();
        }

        User::create([
            'name'     => $this->deriveName($request->email),
            'email'    => $request->email,
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
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)
                    ->where('password', User::sha512($request->password))
                    ->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email or password is incorrect.'])->withInput();
        }

        Session::put('user_id', $user->id);
        Session::put('user_email', $user->email);

        return redirect()->route('dashboard');
    }

    public function logout()
    {
        Session::flush();
        return redirect()->route('login')->with('success', 'Logged out successfully.');
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