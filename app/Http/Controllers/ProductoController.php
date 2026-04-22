<?php

namespace App\Http\Controllers;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = [
            [
                'codigo' => 'GS-001',
                'nombre' => 'Malenia Blade of Miquella 1/7',
                'franquicia' => 'Elden Ring',
                'categoria' => 'Coleccion Premium',
                'precio' => 129.99,
                'stock' => 8,
            ],
            [
                'codigo' => 'GS-002',
                'nombre' => 'Tokai Teio Race Day Nendoroid',
                'franquicia' => 'Uma Musume Pretty Derby',
                'categoria' => 'Nendoroid',
                'precio' => 54.50,
                'stock' => 15,
            ],
            [
                'codigo' => 'GS-003',
                'nombre' => 'Raiden Shogun Narukami 1/7',
                'franquicia' => 'Genshin Impact',
                'categoria' => 'Escala 1/7',
                'precio' => 124.80,
                'stock' => 12,
            ],
        ];

        return view('pages.productos', compact('productos'));
    }
}
