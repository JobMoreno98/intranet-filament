<?php

namespace Database\Seeders;

use App\Models\TipoAcervo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoAcervoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipo_acervos = [
            [

                'nombre' => 'Audiovisual',
                'esquema' => json_decode('[{"type": "text", "label": "Titulo", "options": [], "variable": "titulo", "is_required": false}, {"type": "text", "label": "Autor", "options": [], "variable": "autor", "is_required": false}, {"type": "text", "label": "Lugar", "options": [], "variable": "lugar", "is_required": false}, {"type": "number", "label": "Año", "options": [], "variable": "anio", "is_required": false}, {"type": "textarea", "label": "Formato", "options": [], "variable": "formato", "is_required": false}, {"type": "textarea", "label": "Notas", "options": [], "variable": "notas", "is_required": false}]'),
                'icono' => 'video-camera'
            ],
            [

                'nombre' => 'Bibliográfico',
                'esquema' => json_decode(
                    '[{"type": "text", "label": "Titulo", "options": [], "variable": "titulo", "is_required": false}, {"type": "text", "label": "Autor", "options": [], "variable": "autor", "is_required": false}, {"type": "number", "label": "Año", "options": [], "variable": "anio", "is_required": false}, {"type": "text", "label": "Lugar", "options": [], "variable": "lugar", "is_required": false}, {"type": "text", "label": "Núm. Inventario", "options": [], "variable": "num_inventario", "is_required": false}, {"type": "textarea", "label": "Descripción", "options": [], "variable": "descripcion", "is_required": false}, {"type": "text", "label": "Volumen", "options": [], "variable": "volumen", "is_required": false}, {"type": "text", "label": "Tipo de Material", "options": [], "variable": "tipo_material", "is_required": false}, {"type": "textarea", "label": "Observaciones", "options": [], "variable": "observaiones_1", "is_required": false}, {"type": "textarea", "label": "Observaciones 2", "options": [], "variable": "observaciones_2", "is_required": false}, {"type": "textarea", "label": "Observaciones 3", "options": [], "variable": "observaciones_3", "is_required": false}]',

                ),
                'icono' => 'book-open'
                
            ],
            [

                'nombre' => 'Cartográfico',
                'esquema' => json_decode(
                    '[{"type": "text", "label": "Titulo", "options": [], "variable": "titulo", "is_required": false}, {"type": "text", "label": "Autor", "options": [], "variable": "autor", "is_required": false}, {"type": "number", "label": "Año", "options": [], "variable": "anio", "is_required": false}, {"type": "text", "label": "Editorial", "options": [], "variable": "editorial", "is_required": false}, {"type": "text", "label": "Lugar", "options": [], "variable": "lugar", "is_required": false}, {"type": "text", "label": "Caja", "options": [], "variable": "caja", "is_required": false}, {"type": "textarea", "label": "Medidas", "options": [], "variable": "medidas", "is_required": false}, {"type": "number", "label": "Número", "options": [], "variable": "numero", "is_required": false}, {"type": "textarea", "label": "Signatura Topográfica", "options": [], "variable": "signatura_topografica", "is_required": false}, {"type": "textarea", "label": "Observaciones", "options": [], "variable": "observaciones", "is_required": false}]',

                ),
                'icono' => 'map'
            ],
            [

                'nombre' => 'Documental',
                'esquema' => json_decode(
                    '[{"type": "text", "label": "Titulo", "options": [], "variable": "titulo", "is_required": false}, {"type": "text", "label": "Autor", "options": [], "variable": "autor", "is_required": false}, {"type": "number", "label": "Año", "options": [], "variable": "anio", "is_required": false}, {"type": "text", "label": "Lugar", "options": [], "variable": "lugar", "is_required": false}, {"type": "text", "label": "Núm. Inventario", "options": [], "variable": "num_inventario", "is_required": false}, {"type": "textarea", "label": "Descripción", "options": [], "variable": "descripcion", "is_required": false}, {"type": "text", "label": "Fojas", "options": [], "variable": "fojas", "is_required": false}, {"type": "text", "label": "Editorial", "options": [], "variable": "editorial", "is_required": false}, {"type": "text", "label": "Clasificación", "options": [], "variable": "clasificacion", "is_required": false}, {"type": "textarea", "label": "Observaciones", "options": [], "variable": "observaciones", "is_required": false}]',

                ),
                'icono' => 'document-text'
            ],
            [
                'nombre' => 'Fotográfico',
                'esquema' => json_decode(
                    '[{"type": "text", "label": "Titulo", "options": [], "variable": "titulo", "is_required": false}, {"type": "text", "label": "Autor", "options": [], "variable": "autor", "is_required": false}, {"type": "number", "label": "Año", "options": [], "variable": "anio", "is_required": false}, {"type": "textarea", "label": "Personajes", "options": [], "variable": "personajes", "is_required": false}, {"type": "text", "label": "Lugar", "options": [], "variable": "lugar", "is_required": false}, {"type": "text", "label": "Proceso", "options": [], "variable": "proceso", "is_required": false}, {"type": "textarea", "label": "Medidas", "options": [], "variable": "medidas", "is_required": false}, {"type": "textarea", "label": "Notas", "options": [], "variable": "notas", "is_required": false}]',
                ),
                'icono' => 'camera'
            ],
            [
                'nombre' => 'Archivo',
                'esquema' => json_decode(
                    '[{"type": "text", "label": "Asunto", "options": [], "variable": "asunto", "is_required": false}, {"type": "number", "label": "Año", "options": [], "variable": "anio", "is_required": false}, {"type": "number", "label": "Año 2", "options": [], "variable": "anio_2", "is_required": false}, {"type": "text", "label": "Caja", "options": [], "variable": "caja", "is_required": false}, {"type": "textarea", "label": "Expediente", "options": [], "variable": "expediente", "is_required": false}, {"type": "textarea", "label": "Fojas", "options": [], "variable": "fojas", "is_required": false}, {"type": "text", "label": "Lugar", "options": [], "variable": "lugar", "is_required": false}, {"type": "text", "label": "Lugar 2", "options": [], "variable": "lugar_2", "is_required": false}, {"type": "text", "label": "Progresivo", "options": [], "variable": "progresivo", "is_required": false}, {"type": "text", "label": "Personaje Principal", "options": [], "variable": "personaje_principal", "is_required": false}, {"type": "text", "label": "Personaje Secundario", "options": [], "variable": "personaje_secundario", "is_required": false}, {"type": "text", "label": "Institución", "options": [], "variable": "institucion", "is_required": false}, {"type": "textarea", "label": "Descripción", "options": [], "variable": "descripcion", "is_required": false}]',

                ),
                'icono' => 'archive-box'
            ],
            [
                'nombre' => 'Hemerográfico',
                'esquema' => json_decode(
                    '[{"type": "text", "label": "Titulo", "options": [], "variable": "titulo", "is_required": false}, {"type": "text", "label": "Subtitulo", "options": [], "variable": "subtitulo", "is_required": false}, {"type": "text", "label": "Director", "options": [], "variable": "director", "is_required": false}, {"type": "text", "label": "Periodicidad", "options": [], "variable": "periodicidad", "is_required": false}, {"type": "number", "label": "Día", "options": [], "variable": "dia", "is_required": false}, {"type": "number", "label": "Mes", "options": [], "variable": "mes", "is_required": false}, {"type": "number", "label": "Año", "options": [], "variable": "anio", "is_required": false}, {"type": "number", "label": "Páginas", "options": [], "variable": "paginas", "is_required": false}, {"type": "text", "label": "Epoca", "options": [], "variable": "epoca", "is_required": false}, {"type": "number", "label": "Número", "options": [], "variable": "numero", "is_required": false}, {"type": "number", "label": "Tomo", "options": [], "variable": "tomo", "is_required": false}, {"type": "text", "label": "Volumen", "options": [], "variable": "volumen", "is_required": false}, {"type": "textarea", "label": "Medidas", "options": [], "variable": "medidas", "is_required": false}, {"type": "textarea", "label": "Observaciones", "options": [], "variable": "observaciones", "is_required": false}]',
                ),
                'icono' => 'newspaper'
            ],
            [

                'nombre' => 'Sonoro',
                'esquema' => json_decode(
                    '[{"type": "text", "label": "Titulo", "options": [], "variable": "titulo", "is_required": false}, {"type": "text", "label": "Autor", "options": [], "variable": "autor", "is_required": false}, {"type": "text", "label": "Lugar", "options": [], "variable": "lugar", "is_required": false}, {"type": "number", "label": "Año", "options": [], "variable": "anio", "is_required": false}, {"type": "text", "label": "Formato", "options": [], "variable": "formato", "is_required": false}, {"type": "textarea", "label": "Notas", "options": [], "variable": "notas", "is_required": false}]',

                ),
                'icono' => 'speaker-wave'
            ],
            [
                'nombre' => 'Objetos',
                'esquema' => json_decode(
                    '[{"type": "textarea", "label": "Descripción", "options": [], "variable": "descripcion", "is_required": false},
                 {"type": "text", "label": "Donante", "options": [], "variable": "donante", "is_required": false}]',
                ),
                'icono' => 'cube'
            ],
        ];

        foreach ($tipo_acervos as $item) {
            TipoAcervo::create(
                $item
            );
        }
    }
}
