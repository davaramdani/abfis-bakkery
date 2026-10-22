<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Coba login menggunakan guard 'admin'
        if (Auth::guard('admin')->attempt([...$credentials, 'is_admin' => 1])) {
            $request->session()->regenerate();
            // Arahkan ke dashboard admin
            return redirect()->intended(route('admin.dashboard'));
        }

        // Jika gagal, kembalikan dengan error
        return back()->withErrors([
            'email' => 'Email atau Password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        // Logout dari guard 'admin'
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
