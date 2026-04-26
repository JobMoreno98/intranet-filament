<?php

namespace App\Http\Controllers;

use App\Models\Recursos;
use Illuminate\Http\Request;

class RecursosController extends Controller
{
    public function view(Recursos $recurso)
    {
        return view('view', compact('recurso'));
    }
}
