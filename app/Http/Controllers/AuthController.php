<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

public function login(Request $request)
{
    $request->validate([
        'username' => 'required',
        'password' => 'required',
    ]);

    $credentials = $request->only('username', 'password');
    $credentials['is_active'] = true;

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();

        $user = Auth::user();
        $role = $user->role;

        // Flash untuk SweetAlert di halaman login sebelum redirect
        $redirectUrl = match($role) {
            'admin'       => '/admin/dashboard',
            'koordinator' => '/koordinator/dashboard',
            default       => '/petugas/dashboard',
        };

        session()->flash('login_success', true);
        session()->flash('user_name', $user->name);
        session()->flash('redirect_url', $redirectUrl);

        // Kembali ke halaman login — popup SweetAlert akan redirect otomatis
        return back();
    }

    return back()->withErrors([
        'username' => 'Username atau password salah'
    ])->withInput($request->only('username'));
}
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    public function updateProfil(Request $request)
{
    $user = Auth::user();

    $request->validate([
        'name' => 'required',
        'username' => 'required',
        'no_hp' => 'nullable'
    ]);

    $user->update([
        'name' => $request->name,
        'username' => $request->username,
        'no_hp' => $request->no_hp,
    ]);

    return back()->with('success', 'Profil berhasil diperbarui');
}

public function updatePassword(Request $request)
{
    $user = Auth::user();

    $request->validate([
        'current_password' => 'required',
        'new_password' => 'required|min:6',
        'confirm_password' => 'required|same:new_password',
    ]);

    if (!Hash::check($request->current_password, $user->password)) {
        return back()->withErrors(['current_password' => 'Password lama salah']);
    }

    $user->update([
        'password' => Hash::make($request->new_password)
    ]);

    return back()->with('success', 'Password berhasil diubah');
}
}