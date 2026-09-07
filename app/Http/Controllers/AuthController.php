<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            Log::channel('security')->info('[USER LOGIN SUCCESS]', [
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'role' => $user->role->value ?? (string) $user->role,
                'store_id' => $user->store_id,
                'branch_id' => $user->branch_id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Welcome back, '.$user->name.'!');
        }

        Log::channel('security')->warning('[USER LOGIN FAILED]', [
            'attempted_email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        Log::channel('security')->info('[USER LOGOUT]', [
            'user_id' => $user?->id,
            'email' => $user?->email,
            'ip' => $request->ip(),
        ]);

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
        $currentUser = Auth::user();

        Log::channel('security')->notice('[DEMO USER SWITCH]', [
            'switched_by_user_id' => $currentUser?->id,
            'target_user_id' => $user->id,
            'target_email' => $user->email,
            'target_role' => $user->role->value ?? (string) $user->role,
            'target_store_id' => $user->store_id,
            'target_branch_id' => $user->branch_id,
            'ip' => $request->ip(),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        $roleLabel = $user->role->label();
        $context = $user->store ? " ({$user->store->name})" : ($user->branch ? " ({$user->branch->name})" : '');

        return redirect()->back()->with('success', "Switched role to: {$user->name} - {$roleLabel}{$context}");
    }
}
