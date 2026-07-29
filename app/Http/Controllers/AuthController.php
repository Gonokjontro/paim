<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $demoUsers = [
            ['role' => 'Admin', 'email' => 'admin@paim.ai', 'password' => 'password', 'desc' => 'Full CRUD, User & System Control'],
            ['role' => 'Manager', 'email' => 'manager@paim.ai', 'password' => 'password', 'desc' => 'Manage Subscriptions, Usage & Accounts'],
            ['role' => 'Viewer', 'email' => 'viewer@paim.ai', 'password' => 'password', 'desc' => 'Read-Only View of Dashboards & Reports'],
        ];

        return view('auth.login', compact('demoUsers'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            if (Auth::user()->status === 'inactive') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your account is deactivated. Please contact your workspace administrator.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'))->with('success', 'Logged in successfully as ' . Auth::user()->name . ' (' . ucfirst(Auth::user()->role) . ').');
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

        return redirect()->route('login')->with('success', 'Logged out successfully.');
    }
}
