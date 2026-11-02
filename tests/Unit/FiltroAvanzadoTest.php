<?php

namespace Tests\Unit;

use App\Support\FiltroAvanzado;
use PHPUnit\Framework\TestCase;

class FiltroAvanzadoTest extends TestCase
{
    private function fila(string $campo, string $termino, string $operador = 'AND'): array
    {
        return compact('campo', 'termino', 'operador');
    }

    public function test_fila_global_solo_aporta_texto_libre(): void
    {
        $r = FiltroAvanzado::construir([$this->fila('all', 'kuwait')]);

        $this->assertSame([], $r['filtros']);
        $this->assertSame('kuwait', $r['termino']);
        $this->assertSame([], $r['campos']);
    }

    public function test_campo_especifico_genera_filtro_y_texto(): void
    {
        $r = FiltroAvanzado::construir([$this->fila('titulo', 'kuwait')]);

        $this->assertSame(['(metadata.titulo CONTAINS "kuwait")'], $r['filtros']);
        $this->assertSame('kuwait', $r['termino']);
        $this->assertSame(['titulo'], $r['campos']);
        $this->assertSame('Titulo: kuwait', $r['descripcion']);
    }

    public function test_une_filas_con_and_y_or(): void
    {
        $r = FiltroAvanzado::construir([
            $this->fila('titulo', 'a'),
            $this->fila('contenido', 'b', 'OR'),
        ]);

        $this->assertSame(
            ['(metadata.titulo CONTAINS "a" OR metadata.contenido CONTAINS "b")'],
            $r['filtros']
        );
    }

    public function test_not_en_primera_fila(): void
    {
        $r = FiltroAvanzado::construir([$this->fila('titulo', 'a', 'NOT')]);

        $this->assertSame(['(NOT metadata.titulo CONTAINS "a")'], $r['filtros']);
    }

    public function test_not_en_fila_posterior_se_convierte_en_and_not(): void
    {
        $r = FiltroAvanzado::construir([
            $this->fila('titulo', 'a'),
            $this->fila('contenido', 'b', 'NOT'),
        ]);

        $this->assertSame(
            ['(metadata.titulo CONTAINS "a" AND NOT metadata.contenido CONTAINS "b")'],
            $r['filtros']
        );
    }

    public function test_escapa_comillas_y_diagonales_del_termino(): void
    {
        $r = FiltroAvanzado::construir([$this->fila('titulo', 'a"b\\c')]);

        $this->assertSame(['(metadata.titulo CONTAINS "a\\"b\\\\c")'], $r['filtros']);
    }

    public function test_ignora_campos_con_sintaxis_peligrosa(): void
    {
        $r = FiltroAvanzado::construir([$this->fila('titulo OR status', 'x')]);

        $this->assertSame([], $r['filtros']);
        $this->assertSame('', $r['termino']);
    }

    public function test_operador_invalido_se_trata_como_and(): void
    {
        $r = FiltroAvanzado::construir([
            $this->fila('titulo', 'a'),
            $this->fila('contenido', 'b', 'OR 1=1'),
        ]);

        $this->assertSame(
            ['(metadata.titulo CONTAINS "a" AND metadata.contenido CONTAINS "b")'],
            $r['filtros']
        );
    }

    public function test_fila_sin_operador_no_falla(): void
    {
        $r = FiltroAvanzado::construir([['campo' => 'titulo', 'termino' => 'a']]);

        $this->assertSame(['(metadata.titulo CONTAINS "a")'], $r['filtros']);
    }

    public function test_ignora_filas_vacias_pero_conserva_el_cero(): void
    {
        $r = FiltroAvanzado::construir([
            $this->fila('titulo', '   '),
            $this->fila('all', '0'),
        ]);

        $this->assertSame([], $r['filtros']);
        $this->assertSame('0', $r['termino']);
    }

    public function test_fondos(): void
    {
        $todos = FiltroAvanzado::construir([], ['Todos']);
        $this->assertSame([], $todos['filtros']);

        $dos = FiltroAvanzado::construir([], ['Fondo A', 'Fondo B']);
        $this->assertSame(
            ['(fondo_nombre = "Fondo A" OR fondo_nombre = "Fondo B")'],
            $dos['filtros']
        );
    }
}