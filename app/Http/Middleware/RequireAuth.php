<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireAuth
{
    public function handle(Request $request, Closure $next, ?string $role = null)
    {
        $session = $request->session();

        if (!$session->has('user.rol')) {
            return redirect()->route('login.show')
                ->with('error', 'Debes iniciar sesión para continuar.');
        }

        if ($role !== null && $session->get('user.rol') !== $role) {
            return redirect()->route('compras.index')
                ->with('error', 'No tienes permiso para acceder a esa sección.');
        }

        return $next($request);
    }
}
