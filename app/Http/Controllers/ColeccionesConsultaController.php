<?php

namespace App\Http\Controllers;

use App\Models\ColeccionesConsulta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class ColeccionesConsultaController extends Controller
{
    public function index(Request $request)
    {
        $colecciones = DB::connection('mysql2')
            ->table('colecciones')
            ->select('clave', 'coleccion')
            ->distinct('clave')
            ->when($request->filled('coleccion'), function ($query) use ($request) {
                $query->where('coleccion', 'like', '%' . $request->coleccion . '%');
            })
            ->orderBy('coleccion')
            ->paginate(15);
        /*
        foreach ($colecciones as $key => $item) {
            //$titulo = DB::connection('mysql2')->table($item->tabla)->first();
            dd($item);
            // URL pública temporal
            $item->public_url = URL::temporarySignedRoute(

                'visor.publico',

                now()->addHours(1),

                [
                    'recurso' => $item->clave
                ]
            );
        }
            */
        //dd($colecciones);
        return view('home', compact('colecciones'))->with(['title' => 'Inicio']);
    }

    public function show(Request $request, $id)
    {
        $nombreTabla = DB::connection('mysql2')
            ->table('colecciones')->select('tabla', 'coleccion')
            ->where('clave', $id)
            ->first();

        if (!$nombreTabla) {
            abort(404, "La colección no existe.");
        }

        // 2. Iniciar la consulta sobre esa tabla
        $query = DB::connection('mysql2')->table($nombreTabla->tabla);

        // Buscamos en la configuración qué campos están permitidos para esta tabla
        $camposPermitidos = DB::connection('mysql2')->table('colecciones')
            ->where('tabla', $nombreTabla->tabla)
            ->pluck('campo')
            ->toArray();

        foreach ($request->only($camposPermitidos) as $campo => $valor) {
            if ($valor !== null && $valor !== '') {
                $query->where($campo, 'LIKE', "%{$valor}%");
            }
        }

        $data = $query->paginate(14)->appends($request->all())->onEachSide(0);

        return view('coleccion', [
            'data' => $data,
            'tablaNombre' => $nombreTabla->tabla,
            'id' => $id,
            'title' => $nombreTabla->coleccion
        ]);
    }
}
