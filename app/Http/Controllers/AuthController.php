<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        if ($request->session()->has('user.rol')) {
            return $this->redirectByRole($request->session()->get('user.rol'));
        }
        return view('pages.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = DB::selectOne(
            'SELECT ID_Usuario, Username, Password_Hash, Rol
             FROM USUARIO WHERE Username = ?',
            [$request->input('username')]
        );

        if (!$user || !password_verify($request->input('password'), $user->password_hash)) {
            return redirect()->back()
                ->withInput($request->only('username'))
                ->with('error', 'Usuario o contraseña incorrectos.');
        }

        $request->session()->regenerate();
        $request->session()->put('user', [
            'id'       => $user->id_usuario,
            'username' => $user->username,
            'rol'      => $user->rol,
        ]);

        return $this->redirectByRole($user->rol)
            ->with('success', 'Bienvenido, ' . $user->username . '.');
    }

    public function logout(Request $request)
    {
        $username = $request->session()->get('user.username');
        $request->session()->forget('user');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.show')
            ->with('success', $username ? "Sesión cerrada ({$username})." : 'Sesión cerrada.');
    }

    private function redirectByRole(string $rol)
    {
        return $rol === 'admin'
            ? redirect()->route('dashboard')
            : redirect()->route('compras.index');
    }
}
