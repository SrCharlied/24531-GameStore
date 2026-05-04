<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function humanizeDbError(\Throwable $e): string
    {
        $msg = $e->getMessage();

        // Remove Laravel's debug suffix: "(Connection: pgsql, ..., SQL: ...)"
        $msg = preg_replace('/\s*\(Connection:.*$/s', '', $msg);

        // Cut CONTEXT: section (PL/pgSQL stack trace)
        if (($pos = strpos($msg, 'CONTEXT:')) !== false) {
            $msg = substr($msg, 0, $pos);
        }

        // Cut DETAIL: noise
        if (($pos = strpos($msg, 'DETAIL:')) !== false) {
            $msg = substr($msg, 0, $pos);
        }

        // Take what's after "ERROR:" if present
        if (preg_match('/ERROR:\s*(.+)$/s', $msg, $m)) {
            $msg = trim($m[1]);
        }

        // Map common constraint violations to friendly Spanish
        $constraintMap = [
            'inventario_cantidad_actual_check'    => 'Stock insuficiente en el local seleccionado.',
            'producto_precio_actual_check'        => 'El precio del producto debe ser mayor a 0.',
            'compra_productos_precio_venta_check' => 'El precio de venta debe ser mayor a 0.',
            'compra_productos_cantidad_check'     => 'La cantidad debe ser mayor a 0.',
        ];
        foreach ($constraintMap as $needle => $friendly) {
            if (stripos($msg, $needle) !== false) {
                return $friendly;
            }
        }

        return trim($msg);
    }
}
