<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $demoUsers = User::with(['branch', 'store.branch'])->where('is_active', true)->get();

        return view('auth.login', compact('demoUsers'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'))
                ->with('success', 'Welcome back, ' . Auth::user()->name . '!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'You have been logged out.');
    }

    /**
     * Demo role switcher for testing.
     */
    public function switchUser(Request $request, User $user)
    {
        Auth::login($user);
        $request->session()->regenerate();

        $roleLabel = $user->role->label();
        $context = $user->store ? " ({$user->store->name})" : ($user->branch ? " ({$user->branch->name})" : '');

        return redirect()->back()->with('success', "Switched role to: {$user->name} - {$roleLabel}{$context}");
    }
}
