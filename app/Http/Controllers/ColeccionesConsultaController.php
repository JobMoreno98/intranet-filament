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
            ->distinct()
            ->orderBy('clave')
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

    public function show($id)
    {
        $coleccion = DB::connection('mysql2')->table('colecciones')->where('clave', $id)->value('tabla');
        $data = DB::connection('mysql2')->table($coleccion)->paginate(14);
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
