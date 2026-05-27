<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DatabaseRole
{
    public static function forAppRole(string $role): string
    {
        return match ($role) {
            'admin' => 'rol_admin',
            'gerente' => 'rol_gerente',
            'vendedor' => 'rol_vendedor',
            'bodega' => 'rol_bodega',
            'auditor' => 'rol_auditor',
            default => throw new InvalidArgumentException('Rol de aplicación no válido.'),
        };
    }

    public static function applyForUser(?Authenticatable $user): void
    {
        $role = $user?->rol;

        if (!$role) {
            throw new InvalidArgumentException('No hay un usuario autenticado para aplicar rol de base de datos.');
        }

        DB::statement('SET LOCAL ROLE ' . self::forAppRole($role));
    }
}
