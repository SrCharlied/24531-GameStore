<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireAuth
{
    public function handle(Request $request, Closure $next, ?string $role = null, string ...$extraRoles)
    {
        $isApi = $request->is('api/*') || $request->expectsJson();

        // 1) Determinar el usuario autenticado.
        //    Las rutas API (Sanctum) usan auth('sanctum'); las web usan la sesión clásica.
        if ($isApi) {
            $user = $request->user();
            $rol = $user?->rol;
        } else {
            $rol = $request->session()->get('user.rol');
        }

        // 2) Sin sesión / token → 401 (API) o redirect (web).
        if (!$rol) {
            if ($isApi) {
                return response()->json(['message' => 'No autenticado.'], 401);
            }
            return redirect('/login')
                ->with('error', 'Debes iniciar sesión para continuar.');
        }

        // 3) Verificación de roles cuando aplica.
        //    Permite declarar varios roles en rutas: auth.session:admin,gerente
        if ($role !== null) {
            $allowedRoles = array_filter(array_map(
                'trim',
                array_merge(explode(',', $role), $extraRoles)
            ));
            if (!in_array($rol, $allowedRoles, true)) {
                if ($isApi) {
                    return response()->json(['message' => 'No tienes permiso para acceder a esa sección.'], 403);
                }
                return redirect('/compras')
                    ->with('error', 'No tienes permiso para acceder a esa sección.');
            }
        }

        return $next($request);
    }
}
