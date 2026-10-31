<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoritoController extends Controller
{
    public function index()
    {
        // Traemos los favoritos del usuario logueado, incluyendo la relación del recurso
        $favoritos = Auth::user()->favoritos()->with('recurso')->get();

        return view('favoritos.index', compact('favoritos'));
    }

    public function toggle(Request $request)
    {
        $request->validate(['recursos_id' => 'required|exists:recursos,id']);

        $favorito = Auth::user()->favoritos()->where('recursos_id', $request->recursos_id)->first();

        if ($favorito) {
            $favorito->delete(); // Si ya existe, lo quita
        } else {
            Auth::user()->favoritos()->create(['recursos_id' => $request->recursos_id]); // Si no existe, lo agrega
        }

        return back(); // Regresa a la vista anterior
    }
}
