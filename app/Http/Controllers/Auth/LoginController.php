<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function form()
    {
        return view('auth.login');
    }

    public function proses(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($request->only('email','password'))) {
            $request->session()->regenerate();

            logAktivitas('Login', 'Login ke sistem');

            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah'
        ])->withInput();
    }

    public function logout(Request $request)
    {
        logAktivitas('Logout', 'Logout dari sistem');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
