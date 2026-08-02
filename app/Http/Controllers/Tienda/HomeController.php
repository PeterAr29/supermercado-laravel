<?php

namespace App\Http\Controllers\Tienda;

use App\Http\Controllers\Controller;
use App\Models\Producto;

class HomeController extends Controller
{
    public function index()
    {
        // carga productos destacados
        $productos = Producto::latest()->take(8)->get();

        return view('home', compact('productos'));
    }
}
