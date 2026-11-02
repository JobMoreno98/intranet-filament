<?php

namespace App\Support;

class FiltroAvanzado
{
    private const OPERADORES = ['AND', 'OR', 'NOT'];

    /**
     * @return array{filtros: string[], termino: string, campos: string[], descripcion: string}
     */
    public static function construir(array $filas, array $fondos = []): array
    {
        $filtros = [];
        $terminos = [];
        $campos = [];
        $descripcion = [];

        if (! empty($fondos) && ! in_array('Todos', $fondos, true)) {
            $lista = array_map(
                fn ($f) => 'fondo_nombre = "' . self::escapar((string) $f) . '"',
                $fondos
            );
            $filtros[] = '(' . implode(' OR ', $lista) . ')';
        }

        $condiciones = '';

        foreach ($filas as $fila) {
            $termino = trim((string) ($fila['termino'] ?? ''));
            if ($termino === '') {
                continue;
            }

            $campo = (string) ($fila['campo'] ?? 'all');
            $operador = strtoupper((string) ($fila['operador'] ?? 'AND'));
            if (! in_array($operador, self::OPERADORES, true)) {
                $operador = 'AND';
            }

            if ($campo === 'all') {
                $terminos[] = $termino;
                $descripcion[] = $termino;
                continue;
            }

            // Evita inyectar sintaxis de filtro a través del nombre del campo
            if (! preg_match('/^[A-Za-z0-9_]+$/', $campo)) {
                continue;
            }

            $condicion = 'metadata.' . $campo . ' CONTAINS "' . self::escapar($termino) . '"';

            $terminos[] = $termino;
            $campos[] = $campo;
            $descripcion[] = ucfirst($campo) . ': ' . $termino;

            if ($condiciones === '') {
                $condiciones = $operador === 'NOT' ? "NOT {$condicion}" : $condicion;
            } else {
                $condiciones .= ' ' . ($operador === 'NOT' ? 'AND NOT' : $operador) . " {$condicion}";
            }
        }

        if ($condiciones !== '') {
            $filtros[] = "({$condiciones})";
        }

        return [
            'filtros' => $filtros,
            'termino' => implode(' ', $terminos),
            'campos' => array_values(array_unique($campos)),
            'descripcion' => implode(', ', $descripcion),
        ];
    }

    private static function escapar(string $valor): string
    {
        return addcslashes($valor, "\\\"");
    }
}