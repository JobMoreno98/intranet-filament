<?php

namespace App\Http\Controllers;

use App\Models\ColeccionesConsulta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class ColeccionesConsultaController extends Controller
{
    public function index()
    {
        $colecciones = DB::connection('mysql2')
            ->table('colecciones')
            ->select('clave', 'coleccion')
            ->distinct('clave')
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
        return view('home', compact('colecciones'));
    }

    public function show(Request $request, $id)
    {
        $nombreTabla = DB::connection('mysql2')
            ->table('colecciones')
            ->where('clave', $id)
            ->value('tabla');

        if (!$nombreTabla) {
            abort(404, "La colección no existe.");
        }

        // 2. Iniciar la consulta sobre esa tabla
        $query = DB::connection('mysql2')->table($nombreTabla);

        // 3. Aplicar filtros solo si existen en la URL
        // Buscamos en la configuración qué campos están permitidos para esta tabla
        $camposPermitidos = DB::connection('mysql2')->table('colecciones')
            ->where('tabla', $nombreTabla)
            ->pluck('campo')
            ->toArray();

        foreach ($request->only($camposPermitidos) as $campo => $valor) {
            if ($valor !== null && $valor !== '') {
                $query->where($campo, 'LIKE', "%{$valor}%");
            }
        }

        // 4. Ejecutar la paginación
        // .appends(request()->all()) es VITAL para que al cambiar de página en el 
        // paginador de Laravel no se pierdan los filtros aplicados.
        $data = $query->paginate(14)->appends($request->all());

        return view('coleccion', [
            'data' => $data,
            'tablaNombre' => $nombreTabla,
            'id' => $id
        ]);
        //dd($data);
        /*
        foreach ($data as $key => $item) {
            //$titulo = DB::connection('mysql2')->table($item->tabla)->first();
            //dd($item);
            if ($item->carpetaContenido != '/intranet/SinDigitalizar/') {

                $item->titulo = $item->asunto;
                $item->orden = $key;
                // URL pública temporal
                $item->public_url = URL::temporarySignedRoute(

                    'visor.publico',

                    now()->addHours(1),

                    [
                        'recurso' => $item->IdElemento
                    ]
                );
            }
        }*/
        return view('coleccion', compact('data', 'coleccion'));
    }
}
