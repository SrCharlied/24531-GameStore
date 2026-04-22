<?php

namespace App\Http\Controllers;

class CompraController extends Controller
{
    public function index()
    {
        $compras = [
            [
                'numero' => 'CMP-1001',
                'cliente' => 'Carlos Xicara',
                'empleado' => 'Ana Lux',
                'local' => 'GameStore Oakland Mall',
                'fecha' => '2026-03-01 10:15',
                'total' => 129.99,
            ],
            [
                'numero' => 'CMP-1002',
                'cliente' => 'Andrea Morales',
                'empleado' => 'Jorge Sam',
                'local' => 'GameStore Miraflores',
                'fecha' => '2026-03-02 11:20',
                'total' => 109.00,
            ],
            [
                'numero' => 'CMP-1003',
                'cliente' => 'Lucia Velasquez',
                'empleado' => 'Tatiana Barrios',
                'local' => 'GameStore Cayala',
                'fecha' => '2026-03-06 09:30',
                'total' => 139.90,
            ],
        ];

        return view('pages.compras', compact('compras'));
    }
}
